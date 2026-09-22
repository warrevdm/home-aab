<?php

declare(strict_types=1);

require __DIR__ . '/auth.php';
if (!function_exists('authSessionStart')) {
    http_response_code(503);
    exit('Centrale login is nog niet volledig geïnstalleerd. Controleer mailing-system/src/config.php en mailing-system/src/auth.php.');
}
authSessionStart();

function loginE(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function safeNext(string $value): string
{
    if ($value === '' || !str_starts_with($value, '/') || str_starts_with($value, '//')) {
        return '/home/';
    }
    $allowed = ['/home/', '/huur-module/', '/mailing-system/'];
    foreach ($allowed as $prefix) {
        if (str_starts_with($value, $prefix)) {
            return $value;
        }
    }
    return '/home/';
}

function provisionFromRental(string $email, string $password): bool
{
    $path = __DIR__ . '/../huur-module/storage/database.sqlite';
    if (!is_file($path) || $email === '' || $password === '') {
        return false;
    }

    try {
        $rentalDb = new PDO('sqlite:' . $path, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $stmt = $rentalDb->prepare(
            'SELECT name, email, password_hash, role, active FROM users WHERE lower(email) = lower(?) LIMIT 1'
        );
        $stmt->execute([trim($email)]);
        $rentalUser = $stmt->fetch();
        if (!$rentalUser || (int) $rentalUser['active'] !== 1
            || !password_verify($password, (string) $rentalUser['password_hash'])) {
            return false;
        }

        $now = authNow();
        $role = (string) $rentalUser['role'] === 'admin' ? 'admin' : 'user';
        $insert = authDb()->prepare(
            'INSERT OR IGNORE INTO users (name,email,password_hash,role,active,created_at,updated_at,password_changed_at)
             VALUES (?,?,?,?,1,?,?,?)'
        );
        $insert->execute([
            (string) $rentalUser['name'],
            (string) $rentalUser['email'],
            (string) $rentalUser['password_hash'],
            $role,
            $now,
            $now,
            $now,
        ]);
        if ($insert->rowCount() > 0) {
            authAudit('account_provisioned_from_rental');
        }
        return true;
    } catch (Throwable $e) {
        error_log('[home] Rental account provisioning failed: ' . $e->getMessage());
        return false;
    }
}

$next = safeNext((string) ($_GET['next'] ?? $_POST['next'] ?? '/home/'));
if (authCurrentUser() !== null) {
    header('Location: ' . $next);
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (!authVerifyCsrf($_POST['csrf_token'] ?? null)) {
        $error = 'Je sessie is verlopen. Herlaad de pagina en probeer opnieuw.';
    } elseif ($email === '' || $password === '') {
        $error = 'Vul je e-mailadres en wachtwoord in.';
    } elseif (authLogin($email, $password)
        || (provisionFromRental($email, $password) && authLogin($email, $password))) {
        header('Location: ' . $next);
        exit;
    } else {
        $error = 'Aanmelden mislukt. Controleer je gegevens of probeer later opnieuw.';
    }
}

header_remove('X-Powered-By');
header('Cache-Control: no-store, private');
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
header("Content-Security-Policy: default-src 'self'; img-src 'self'; style-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'");
?>
<!doctype html>
<html lang="nl-BE">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow,noarchive">
    <title>Inloggen | Aerts Action Bike</title>
    <link rel="stylesheet" href="assets/dashboard.css">
</head>
<body class="login-page">
<main class="login-shell">
    <section class="login-visual">
        <div class="login-visual-copy"><span>Team AAB</span><strong>Eén login.<br>Alles binnen bereik.</strong><p>Veilige toegang tot de interne systemen van Aerts Action Bike.</p></div>
        <div class="wheel" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i></div>
    </section>
    <section class="login-panel">
        <div class="login-card">
            <img src="../mailing-system/assets/aab-logo.svg" alt="Aerts Action Bike" class="login-logo">
            <p class="eyebrow"><span></span>Interne toegang</p>
            <h1>Welkom terug.</h1>
            <p class="intro">Meld je aan met je persoonlijke account.</p>

            <?php if ($error !== ''): ?><div class="alert"><?= loginE($error) ?></div><?php endif; ?>
            <?php if (authUserCount() === 0): ?><div class="alert">Er bestaat nog geen adminaccount. Voer eerst de installatie uit.</div><?php endif; ?>

            <form method="post" autocomplete="on">
                <input type="hidden" name="csrf_token" value="<?= loginE(authCsrfToken()) ?>">
                <input type="hidden" name="next" value="<?= loginE($next) ?>">
                <label for="email">E-mailadres</label>
                <input id="email" name="email" type="email" autocomplete="username" required maxlength="190" placeholder="naam@aertsactionbike.be">
                <label for="password">Wachtwoord</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required maxlength="128" placeholder="Je wachtwoord">
                <button type="submit">Aanmelden <span>→</span></button>
            </form>
            <p class="help">Problemen met aanmelden? Neem contact op met de beheerder.</p>
        </div>
    </section>
</main>
</body>
</html>
