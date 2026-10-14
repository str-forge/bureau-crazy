<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

if (file_exists(dirname(__DIR__) . '/config/bootstrap.php')) {
    require dirname(__DIR__) . '/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');
}

// ============================================================================
// AUTOMATISIERUNG (DX): Beseitigung veralteter Validierungs- & Entity-Metadaten
// ============================================================================
// Löscht das Test-Cache-Verzeichnis physisch vom Datenträger, noch bevor der
// Symfony-Kernel für die Tests hochfährt. Dadurch wird erzwungen, dass alle
// PHP 8.3 Attribute bei jedem Testlauf zu 100% frisch eingelesen werden.
if (isset($_SERVER['APP_ENV']) && $_SERVER['APP_ENV'] === 'test') {
    $testCacheDir = dirname(__DIR__) . '/var/cache/test';
    if (is_dir($testCacheDir)) {
        shell_exec(sprintf('rm -rf %s', escapeshellarg($testCacheDir)));
    }
}
