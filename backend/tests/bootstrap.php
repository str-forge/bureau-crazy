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
// Physically remove the test cache directory before the Symfony kernel boots
// for tests. This ensures that all PHP 8.3 attributes are freshly loaded on
// every test run.
if (isset($_SERVER['APP_ENV']) && $_SERVER['APP_ENV'] === 'test') {
    $testCacheDir = dirname(__DIR__) . '/var/cache/test';
    if (is_dir($testCacheDir)) {
        shell_exec(sprintf('rm -rf %s', escapeshellarg($testCacheDir)));
    }
}
