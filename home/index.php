<?php

declare(strict_types=1);

require __DIR__ . '/../mailing-system/src/auth.php';
$user = authRequireLogin();

header_remove('X-Powered-By');
header('Cache-Control: no-store, private');
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
header("Content-Security-Policy: default-src 'self'; img-src 'self'; style-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'");

function homeE(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function homeHasRentalAccess(string $email): bool
{
    $path = __DIR__ . '/../huur-module/storage/database.sqlite';
    if (!is_file($path)) {
        return false;
    }

    try {
        $pdo = new PDO('sqlite:' . $path, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $stmt = $pdo->prepare('SELECT 1 FROM users WHERE lower(email) = lower(?) AND active = 1 LIMIT 1');
        $stmt->execute([$email]);
        return (bool) $stmt->fetchColumn();
    } catch (Throwable $e) {
        error_log('[home] Rental access check failed: ' . $e->getMessage());
        return false;
    }
}

$hasRentalAccess = homeHasRentalAccess((string) $user['email']);
$firstName = trim(explode(' ', trim((string) $user['name']))[0] ?? 'collega');
?>
<!doctype html>
<html lang="nl-BE">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow,noarchive">
    <title>Teamdashboard | Aerts Action Bike</title>
    <link rel="stylesheet" href="assets/dashboard.css">
</head>
<body>
<header class="topbar">
    <a class="brand" href="/home/" aria-label="Aerts Action Bike teamdashboard">
        <img src="../mailing-system/assets/aab-logo.svg" alt="Aerts Action Bike">
        <span>Teamdashboard</span>
    </a>
    <div class="account">
        <span class="account-copy"><strong><?= homeE((string) $user['name']) ?></strong><small><?= homeE((string) $user['email']) ?></small></span>
        <form method="post" action="logout.php">
            <input type="hidden" name="csrf_token" value="<?= homeE(authCsrfToken()) ?>">
            <button type="submit" class="logout">Uitloggen</button>
        </form>
    </div>
</header>

<main class="shell">
    <section class="hero">
        <p class="eyebrow"><span></span>Interne omgeving</p>
        <h1>Goedemiddag, <?= homeE($firstName) ?>.</h1>
        <p>Alles wat je nodig hebt voor de dagelijkse werking, vanuit één veilige login.</p>
    </section>

    <section class="section-head">
        <div><p class="kicker">Systemen</p><h2>Waar wil je verdergaan?</h2></div>
        <span class="session"><i></i>Sessie actief</span>
    </section>

    <section class="apps" aria-label="Interne systemen">
        <?php if ($hasRentalAccess): ?>
            <a class="app-card app-card--green" href="/huur-module/">
                <span class="app-icon" aria-hidden="true">↗</span>
                <span class="app-number">01</span>
                <span class="app-copy"><strong>Verhuur</strong><small>Planning, fietsen, reservaties en contracten</small></span>
                <span class="app-action">Open systeem <b>→</b></span>
            </a>
        <?php else: ?>
            <div class="app-card app-card--disabled" aria-disabled="true">
                <span class="app-icon" aria-hidden="true">×</span>
                <span class="app-number">01</span>
                <span class="app-copy"><strong>Verhuur</strong><small>Geen toegang voor dit account</small></span>
                <span class="app-action">Vraag toegang aan</span>
            </div>
        <?php endif; ?>

        <a class="app-card app-card--dark" href="/mailing-system/">
            <span class="app-icon" aria-hidden="true">✉</span>
            <span class="app-number">02</span>
            <span class="app-copy"><strong>Klantcommunicatie</strong><small>Afhaalmails en communicatie-overzicht</small></span>
            <span class="app-action">Open systeem <b>→</b></span>
        </a>

        <?php if (($user['role'] ?? '') === 'admin'): ?>
            <a class="app-card app-card--light" href="/mailing-system/admin.php">
                <span class="app-icon" aria-hidden="true">⚙</span>
                <span class="app-number">03</span>
                <span class="app-copy"><strong>Beheer</strong><small>Accounts, rechten en activiteit</small></span>
                <span class="app-action">Open beheer <b>→</b></span>
            </a>
        <?php endif; ?>
    </section>

    <footer><span>Aerts Action Bike · interne werkomgeving</span><span>Persoonlijke accounts · beveiligde sessie</span></footer>
</main>
</body>
</html>
