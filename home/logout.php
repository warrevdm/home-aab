<?php

declare(strict_types=1);

require __DIR__ . '/../mailing-system/src/auth.php';
authSessionStart();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !authVerifyCsrf($_POST['csrf_token'] ?? null)) {
    http_response_code(405);
    exit('Ongeldige aanvraag.');
}

authLogout();
header('Location: /home/login.php');
exit;
