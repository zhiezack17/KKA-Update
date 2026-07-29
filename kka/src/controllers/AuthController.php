<?php
class AuthController {
    private Auth $auth;
    public function __construct(Auth $auth) { $this->auth = $auth; }

    public function home(): void {
        if ($this->auth->check()) redirect('dashboard');
        redirect('login');
    }

    public function login(): void {
        if ($this->auth->check()) redirect('dashboard');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_check();
            $email = (string)input('email', '');
            $pwd   = (string)input('password', '');
            if ($email === '' || $pwd === '') {
                flash('error', 'Email dan password wajib diisi.');
            } elseif ($this->auth->attempt($email, $pwd)) {
                flash('success', 'Selamat datang, ' . $this->auth->user()['nama'] . '!');
                redirect('dashboard');
            } else {
                flash('error', 'Email atau password salah, atau akun dinonaktifkan.');
            }
        }
        view('auth/login');
    }

    public function logout(): void {
        $this->auth->logout();
        flash('success', 'Anda telah keluar.');
        redirect('login');
    }
}
