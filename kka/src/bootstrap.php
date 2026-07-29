<?php
/**
 * Bootstrap aplikasi KKA.
 */

declare(strict_types=1);

// Timezone WIB
date_default_timezone_set('Asia/Jakarta');

// Konfigurasi
$cfg = require __DIR__ . '/config.php';
$GLOBALS['cfg'] = $cfg;

// Tentukan base URL otomatis (kompatibel cPanel sub-folder maupun root)
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$basePath   = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
if ($basePath === '/' || $basePath === '.') $basePath = '';
$GLOBALS['app_base_url'] = $basePath;

// Error reporting
if ($cfg['app_debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    ini_set('display_errors', '0');
}

// Autoload sederhana
spl_autoload_register(function (string $class) {
    foreach (['lib','controllers','models'] as $dir) {
        $f = __DIR__ . '/' . $dir . '/' . $class . '.php';
        if (is_file($f)) { require_once $f; return; }
    }
});

// Helpers
require_once __DIR__ . '/lib/Helpers.php';

// Session
session_name($cfg['session_name']);
session_set_cookie_params([
    'lifetime' => $cfg['session_lifetime'],
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

// DB
try {
    DB::init($cfg);
} catch (Throwable $e) {
    // Jika DB belum dikonfigurasi, arahkan ke installer (kecuali sudah di install.php)
    $self = basename($_SERVER['SCRIPT_NAME'] ?? '');
    if ($self !== 'install.php') {
        http_response_code(500);
        echo '<h2 style="font-family:sans-serif">Koneksi database gagal</h2>';
        echo '<p>Silakan jalankan <a href="' . e(url('install.php')) . '">installer</a> atau periksa file <code>.env</code>.</p>';
        if ($cfg['app_debug']) {
            echo '<pre>' . e($e->getMessage()) . '</pre>';
        }
        exit;
    }
}

// Auth
$auth = new Auth();
$GLOBALS['auth'] = $auth;
