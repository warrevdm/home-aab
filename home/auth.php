<?php

declare(strict_types=1);

/*
 * Stabiele auth-bridge voor het AAB teamdashboard.
 *
 * Andere interne applicaties koppelen met /home/auth.php in plaats van
 * rechtstreeks met de interne bestanden van mailing-system. De centrale
 * accountdatabase blijft voorlopig in mailing-system/data/auth.sqlite.
 */

$sharedAuthFile = __DIR__ . '/../mailing-system/src/auth.php';
$sharedConfigFile = __DIR__ . '/../mailing-system/src/config.php';

if (is_file($sharedAuthFile) && is_file($sharedConfigFile)) {
    require_once $sharedAuthFile;
}
