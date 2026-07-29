<?php
class Auth {
    private ?array $user = null;

    public function __construct() {
        if (!empty($_SESSION['uid'])) {
            $this->user = DB::one(
                'SELECT id, nama, email, role, nip, jabatan, is_active FROM kka_users WHERE id = ? LIMIT 1',
                [$_SESSION['uid']]
            );
            if (!$this->user || (int)$this->user['is_active'] === 0) {
                $this->logout();
            }
        }
    }

    public function check(): bool { return $this->user !== null; }
    public function user(): ?array { return $this->user; }
    public function id(): ?int { return $this->user['id'] ?? null; }
    public function isAdmin(): bool { return ($this->user['role'] ?? '') === 'admin'; }

    public function attempt(string $email, string $password): bool {
        $u = DB::one('SELECT * FROM kka_users WHERE email = ? LIMIT 1', [strtolower(trim($email))]);
        if (!$u || (int)$u['is_active'] === 0) return false;
        if (!password_verify($password, $u['password_hash'])) return false;
        session_regenerate_id(true);
        $_SESSION['uid'] = (int)$u['id'];
        $this->user = $u;
        return true;
    }

    public function logout(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        $this->user = null;
    }

    public function require(): void {
        if (!$this->check()) {
            flash('error', 'Silakan login terlebih dahulu.');
            redirect('login');
        }
    }

    public function requireAdmin(): void {
        $this->require();
        if (!$this->isAdmin()) {
            http_response_code(403);
            exit('Akses ditolak. Hanya untuk Administrator.');
        }
    }
}
