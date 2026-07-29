<?php
class UserController {
    private Auth $auth;
    public function __construct(Auth $auth) { $this->auth = $auth; $auth->require(); }

    public function index(): void {
        $this->auth->requireAdmin();
        $users = DB::all('SELECT id, nama, email, role, nip, jabatan, is_active, created_at FROM kka_users ORDER BY created_at DESC');
        view('users/index', compact('users'));
    }

    public function store(): void {
        $this->auth->requireAdmin(); only_post(); csrf_check();
        $email = strtolower(trim((string) input('email')));
        $nama  = trim((string) input('nama'));
        $pass  = (string) input('password');
        $role  = input('role') === 'admin' ? 'admin' : 'auditor';

        if ($nama === '' || $email === '' || strlen($pass) < 6) {
            flash('error', 'Nama, email, dan password (min 6 karakter) wajib diisi.');
            redirect('users');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Email tidak valid.'); redirect('users');
        }
        try {
            DB::insert('kka_users', [
                'nama'          => $nama,
                'email'         => $email,
                'password_hash' => password_hash($pass, PASSWORD_BCRYPT),
                'role'          => $role,
                'nip'           => trim((string) input('nip')) ?: null,
                'jabatan'       => trim((string) input('jabatan')) ?: null,
                'is_active'     => 1,
            ]);
            flash('success', 'Pengguna baru berhasil dibuat.');
        } catch (Throwable $e) {
            flash('error', 'Email sudah dipakai atau gagal disimpan.');
        }
        redirect('users');
    }

    public function update(): void {
        $this->auth->requireAdmin(); only_post(); csrf_check();
        $id = (int) input('id');
        if (!$id) { flash('error', 'Data tidak valid.'); redirect('users'); }

        $data = [
            'nama'      => trim((string) input('nama')),
            'role'      => input('role') === 'admin' ? 'admin' : 'auditor',
            'nip'       => trim((string) input('nip')) ?: null,
            'jabatan'   => trim((string) input('jabatan')) ?: null,
            'is_active' => (int) input('is_active', 1) === 1 ? 1 : 0,
        ];
        $pass = (string) input('password');
        if ($pass !== '') {
            if (strlen($pass) < 6) { flash('error','Password minimal 6 karakter.'); redirect('users'); }
            $data['password_hash'] = password_hash($pass, PASSWORD_BCRYPT);
        }
        DB::update('kka_users', $data, ['id' => $id]);
        flash('success', 'Data pengguna diperbarui.');
        redirect('users');
    }

    public function delete(): void {
        $this->auth->requireAdmin(); only_post(); csrf_check();
        $id = (int) input('id');
        if ($id === $this->auth->id()) {
            flash('error', 'Tidak bisa menghapus akun sendiri.');
        } else {
            DB::delete('kka_users', ['id' => $id]);
            flash('success', 'Pengguna dihapus.');
        }
        redirect('users');
    }

    public function profile(): void {
        view('users/profile');
    }

    public function updateProfile(): void {
        only_post(); csrf_check();
        $id = $this->auth->id();
        $data = [
            'nama'    => trim((string) input('nama')),
            'nip'     => trim((string) input('nip')) ?: null,
            'jabatan' => trim((string) input('jabatan')) ?: null,
        ];
        $oldPass = (string) input('old_password');
        $newPass = (string) input('new_password');
        if ($newPass !== '') {
            $u = DB::one('SELECT password_hash FROM kka_users WHERE id = ?', [$id]);
            if (!$u || !password_verify($oldPass, $u['password_hash'])) {
                flash('error', 'Password lama salah.'); redirect('profile');
            }
            if (strlen($newPass) < 6) { flash('error','Password baru minimal 6 karakter.'); redirect('profile'); }
            $data['password_hash'] = password_hash($newPass, PASSWORD_BCRYPT);
        }
        DB::update('kka_users', $data, ['id' => $id]);
        flash('success', 'Profil diperbarui.');
        redirect('profile');
    }
}
