<?php
/**
 * Helper umum.
 */

function e(?string $s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = ''): string {
    $base = rtrim($GLOBALS['app_base_url'] ?? '', '/');
    if ($path === '') return $base . '/';
    if (str_starts_with($path, '/')) return $base . $path;
    return $base . '/' . $path;
}

function asset(string $path): string {
    return url('assets/' . ltrim($path, '/'));
}

function redirect(string $path): void {
    header('Location: ' . url($path));
    exit;
}

function csrf_token(): string {
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(24));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string {
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
    $token = $_POST['_csrf'] ?? '';
    if (!hash_equals(csrf_token(), (string)$token)) {
        http_response_code(419);
        exit('CSRF token tidak valid. Silakan refresh halaman.');
    }
}

function flash(string $type, string $msg): void {
    $_SESSION['_flash'][] = ['type' => $type, 'msg' => $msg];
}

function flash_pull(): array {
    $f = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $f;
}

function rupiah($val, bool $withSymbol = true): string {
    $val = (float)$val;
    $out = number_format($val, 0, ',', '.');
    return $withSymbol ? 'Rp ' . $out : $out;
}

function parse_money($v): float {
    // Hanya nilai numeric asli (int/float dari kode) yang boleh langsung dicast.
    // String seperti "800.000" is_numeric()==true tapi maksudnya 800 ribu, bukan 800.
    if (is_int($v) || is_float($v)) return (float)$v;
    $v = preg_replace('/[^\d,.\-]/', '', (string)$v);
    // hilangkan separator ribuan (titik) lalu ganti koma jadi titik
    $v = str_replace('.', '', $v);
    $v = str_replace(',', '.', $v);
    return $v === '' ? 0 : (float)$v;
}

function tgl_id(?string $date): string {
    if (!$date) return '-';
    $ts = strtotime($date);
    if (!$ts) return '-';
    $bulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    return date('j', $ts) . ' ' . $bulan[(int)date('n', $ts)] . ' ' . date('Y', $ts);
}

function view(string $tpl, array $data = []): void {
    $cfg = $GLOBALS['cfg'];
    $auth = $GLOBALS['auth'] ?? null;
    extract($data, EXTR_SKIP);
    $file = $cfg['root_dir'] . '/src/views/' . $tpl . '.php';
    if (!is_file($file)) {
        http_response_code(500);
        exit('Template tidak ditemukan: ' . e($tpl));
    }
    require $file;
}

function partial(string $tpl, array $data = []): void {
    view('partials/' . $tpl, $data);
}

function only_post(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Method not allowed');
    }
}

function input(string $key, $default = null) {
    $v = $_POST[$key] ?? $_GET[$key] ?? $default;
    if (is_string($v)) $v = trim($v);
    return $v;
}

/**
 * ==========================================================
 * Isolasi data antar-pengguna (data ownership).
 * Admin melihat SEMUA data; auditor hanya melihat data
 * miliknya sendiri (kolom kka_sesi.created_by).
 * ==========================================================
 */

/**
 * Fragmen WHERE untuk membatasi query pada data milik user (kecuali admin).
 * Termasuk sesi yang DIBAGIKAN kepada user lewat tabel kka_sesi_share.
 * $col adalah kolom created_by pada tabel kka_sesi ber-alias (mis. "s.created_by").
 */
function owner_where($auth, string $col = 's.created_by'): array {
    if ($auth && $auth->isAdmin()) return ['', []];
    $uid = $auth ? (int) $auth->id() : 0;
    $alias = strpos($col, '.') !== false ? substr($col, 0, strpos($col, '.')) : $col;
    return [
        " AND ($col = ? OR $alias.id IN (SELECT sesi_id FROM kka_sesi_share WHERE user_id = ?))",
        [$uid, $uid],
    ];
}

/**
 * True jika user boleh mengakses sesi (admin, pemilik, ATAU penerima berbagi).
 * $sesi harus memuat 'created_by' dan salah satu dari 'sesi_id' atau 'id'
 * (id sesi). Bila keduanya ada, 'sesi_id' diutamakan agar aman untuk baris
 * hasil JOIN (mis. lampiran yang punya id sendiri).
 */
function sesi_is_owned($auth, ?array $sesi): bool {
    if (!$sesi || !$auth) return false;
    if ($auth->isAdmin()) return true;
    $uid = (int) $auth->id();
    if ((int) ($sesi['created_by'] ?? 0) === $uid) return true;
    $sid = (int) ($sesi['sesi_id'] ?? $sesi['id'] ?? 0);
    if ($sid > 0) {
        return (bool) DB::scalar(
            'SELECT 1 FROM kka_sesi_share WHERE sesi_id = ? AND user_id = ? LIMIT 1',
            [$sid, $uid]
        );
    }
    return false;
}

/** Hentikan akses (redirect) bila sesi tidak ada atau bukan milik user. */
function guard_sesi($auth, ?array $sesi, string $redirectTo = 'sesi'): void {
    if (!$sesi) {
        flash('error', 'Sesi audit tidak ditemukan.');
        redirect($redirectTo);
    }
    if (!sesi_is_owned($auth, $sesi)) {
        http_response_code(403);
        flash('error', 'Anda tidak memiliki akses ke data audit ini.');
        redirect($redirectTo);
    }
}
