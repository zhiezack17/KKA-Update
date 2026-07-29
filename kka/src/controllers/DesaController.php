<?php
class DesaController {
    private Auth $auth;
    public function __construct(Auth $auth) { $this->auth = $auth; $auth->require(); }

    public function index(): void {
        $q   = trim((string) input('q', ''));
        $kec = (int) input('kecamatan', 0);

        $where = '1=1'; $params = [];
        if ($q !== '')   { $where .= ' AND d.nama LIKE ?'; $params[] = "%$q%"; }
        if ($kec > 0)    { $where .= ' AND d.kecamatan_id = ?'; $params[] = $kec; }

        $desa = DB::all("
            SELECT d.id, d.nama, k.nama AS kecamatan, k.id AS kec_id
            FROM kka_desa d JOIN kka_kecamatan k ON k.id = d.kecamatan_id
            WHERE $where ORDER BY d.nama ASC
        ", $params);
        $kecamatan = DB::all('SELECT * FROM kka_kecamatan ORDER BY nama');

        view('desa/index', compact('desa','kecamatan','q','kec'));
    }

    public function store(): void {
        $this->auth->requireAdmin(); only_post(); csrf_check();
        $kec = (int) input('kecamatan_id');
        $nama = trim((string) input('nama'));
        if (!$kec || $nama === '') {
            flash('error', 'Kecamatan dan nama desa wajib diisi.');
        } else {
            try {
                DB::insert('kka_desa', ['kecamatan_id' => $kec, 'nama' => $nama]);
                flash('success', 'Desa berhasil ditambahkan.');
            } catch (Throwable $e) {
                flash('error', 'Gagal menyimpan: ' . $e->getMessage());
            }
        }
        redirect('desa');
    }

    public function update(): void {
        $this->auth->requireAdmin(); only_post(); csrf_check();
        $id = (int) input('id');
        $kec = (int) input('kecamatan_id');
        $nama = trim((string) input('nama'));
        if (!$id || !$kec || $nama === '') {
            flash('error', 'Data tidak valid.');
        } else {
            DB::update('kka_desa', ['kecamatan_id' => $kec, 'nama' => $nama], ['id' => $id]);
            flash('success', 'Data desa diperbarui.');
        }
        redirect('desa');
    }

    public function delete(): void {
        $this->auth->requireAdmin(); only_post(); csrf_check();
        $id = (int) input('id');
        $used = (int) DB::scalar('SELECT COUNT(*) FROM kka_sesi WHERE desa_id = ?', [$id]);
        if ($used > 0) {
            flash('error', 'Desa ini sudah dipakai pada ' . $used . ' sesi audit, tidak bisa dihapus.');
        } else {
            DB::delete('kka_desa', ['id' => $id]);
            flash('success', 'Desa dihapus.');
        }
        redirect('desa');
    }

    public function storeKec(): void {
        $this->auth->requireAdmin(); only_post(); csrf_check();
        $nama = trim((string) input('nama'));
        if ($nama === '') {
            flash('error', 'Nama kecamatan wajib diisi.');
        } else {
            try {
                DB::insert('kka_kecamatan', ['nama' => $nama]);
                flash('success', 'Kecamatan ditambahkan.');
            } catch (Throwable $e) {
                flash('error', 'Kecamatan sudah ada atau gagal disimpan.');
            }
        }
        redirect('desa');
    }
}
