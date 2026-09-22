<?php

declare(strict_types=1);

require __DIR__ . '/auth.php';
if (!function_exists('authSessionStart')) {
    http_response_code(503);
    exit('Centrale login is nog niet volledig geïnstalleerd. Controleer mailing-system/src/config.php en mailing-system/src/auth.php.');
}
authSessionStart();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !authVerifyCsrf($_POST['csrf_token'] ?? null)) {
    http_response_code(405);
    exit('Ongeldige aanvraag.');
}

authLogout();
header('Location: /home/login.php');
exit;
