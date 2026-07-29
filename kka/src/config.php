<?php
/**
 * Loader konfigurasi sederhana dari file .env
 * Tanpa dependency eksternal (kompatibel shared hosting cPanel).
 */

function kka_load_env(string $path): array {
    if (!is_file($path)) {
        return [];
    }
    $env = [];
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || $line[0] === ';') {
            continue;
        }
        if (!str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        // Hilangkan quote pembungkus
        if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
            $value = substr($value, 1, -1);
        }
        $env[$key] = $value;
    }
    return $env;
}

$rootDir   = dirname(__DIR__);
$envFile   = $rootDir . '/.env';
$envSample = $rootDir . '/.env.example';
$env       = kka_load_env(is_file($envFile) ? $envFile : $envSample);

return [
    'root_dir'        => $rootDir,
    'app_name'        => $env['APP_NAME']        ?? 'Kertas Kerja Audit',
    'app_url'         => $env['APP_URL']         ?? 'https://arsipdigital-inspektorat.com/kka',
    'app_env'         => $env['APP_ENV']         ?? 'production',
    'app_debug'       => filter_var($env['APP_DEBUG'] ?? 'false', FILTER_VALIDATE_BOOLEAN),
    'db_host'         => $env['DB_HOST']         ?? '127.0.0.1',
    'db_port'         => (int)($env['DB_PORT']   ?? 3306),
    'db_name'         => $env['DB_NAME']         ?? 'kka_db',
    'db_user'         => $env['DB_USER']         ?? 'kka_user',
    'db_pass'         => $env['DB_PASS']         ?? 'hGJS3NcxKTY47EiH',
    'admin_email'     => $env['ADMIN_EMAIL']     ?? 'admin@inspektorat-rohil.go.id',
    'admin_password'  => $env['ADMIN_PASSWORD']  ?? 'Admin@2026',
    'admin_nama'      => $env['ADMIN_NAMA']      ?? 'Administrator',
    'session_name'    => $env['SESSION_NAME']    ?? 'kka_sess',
    'session_lifetime'=> (int)($env['SESSION_LIFETIME'] ?? 7200),
    'upload_dir'      => $rootDir . '/public/uploads',
    'allowed_mimes'   => [
        'application/pdf',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'image/jpeg', 'image/png', 'image/webp', 'image/gif',
    ],
    'max_upload_mb'   => 10,
];
