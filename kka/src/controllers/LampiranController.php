<?php
class LampiranController {
    private Auth $auth;
    public function __construct(Auth $auth) { $this->auth = $auth; $auth->require(); }

    public function upload(): void {
        only_post(); csrf_check();
        $cfg = $GLOBALS['cfg'];
        $sesiId = (int) input('sesi_id');
        $sesi = DB::one('SELECT id, created_by FROM kka_sesi WHERE id = ?', [$sesiId]);
        guard_sesi($this->auth, $sesi);

        if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            flash('error', 'Gagal upload file. Periksa ukuran/format.');
            redirect('sesi/show?id=' . $sesiId);
        }
        $f = $_FILES['file'];
        $maxBytes = $cfg['max_upload_mb'] * 1024 * 1024;
        if ($f['size'] > $maxBytes) {
            flash('error', 'Ukuran file melebihi ' . $cfg['max_upload_mb'] . ' MB.');
            redirect('sesi/show?id=' . $sesiId);
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($f['tmp_name']) ?: 'application/octet-stream';
        if (!in_array($mime, $cfg['allowed_mimes'], true)) {
            flash('error', 'Tipe file tidak diizinkan: ' . $mime);
            redirect('sesi/show?id=' . $sesiId);
        }

        $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        $ext = preg_replace('/[^a-z0-9]/', '', $ext);
        $newName = 'lamp_' . $sesiId . '_' . bin2hex(random_bytes(6)) . '.' . $ext;

        if (!is_dir($cfg['upload_dir'])) @mkdir($cfg['upload_dir'], 0775, true);
        $dest = $cfg['upload_dir'] . '/' . $newName;
        if (!move_uploaded_file($f['tmp_name'], $dest)) {
            flash('error', 'Gagal menyimpan file ke server.');
            redirect('sesi/show?id=' . $sesiId);
        }

        DB::insert('kka_lampiran', [
            'sesi_id'    => $sesiId,
            'nama_asli'  => $f['name'],
            'nama_file'  => $newName,
            'mime_type'  => $mime,
            'ukuran'     => (int) $f['size'],
            'keterangan' => trim((string) input('keterangan')) ?: null,
            'uploaded_by'=> $this->auth->id(),
        ]);
        flash('success', 'Lampiran berhasil diunggah.');
        redirect('sesi/show?id=' . $sesiId);
    }

    public function delete(): void {
        only_post(); csrf_check();
        $id = (int) input('id');
        $sesiId = (int) input('sesi_id');
        $row = DB::one('SELECT l.nama_file, l.sesi_id, s.created_by
                        FROM kka_lampiran l JOIN kka_sesi s ON s.id = l.sesi_id
                        WHERE l.id = ?', [$id]);
        if (!sesi_is_owned($this->auth, $row)) {
            flash('error', 'Anda tidak memiliki akses ke lampiran ini.');
            redirect('sesi/show?id=' . $sesiId);
        }
        $p = $GLOBALS['cfg']['upload_dir'] . '/' . $row['nama_file'];
        if (is_file($p)) @unlink($p);
        DB::delete('kka_lampiran', ['id' => $id]);
        flash('success', 'Lampiran dihapus.');
        redirect('sesi/show?id=' . $sesiId);
    }

    public function download(): void {
        $id = (int) input('id');
        $row = DB::one('SELECT l.*, s.created_by
                        FROM kka_lampiran l JOIN kka_sesi s ON s.id = l.sesi_id
                        WHERE l.id = ?', [$id]);
        if (!$row) { http_response_code(404); exit('Tidak ditemukan'); }
        if (!sesi_is_owned($this->auth, $row)) { http_response_code(403); exit('Anda tidak memiliki akses ke file ini.'); }
        $p = $GLOBALS['cfg']['upload_dir'] . '/' . $row['nama_file'];
        if (!is_file($p)) { http_response_code(404); exit('File tidak ditemukan'); }
        // Lepaskan kunci file sesi sebelum streaming file. Tanpa ini, unduhan
        // yang berjalan menahan lock sesi sehingga request lain (klik/back) dari
        // user yang sama ikut terblokir sampai unduhan selesai.
        if (session_status() === PHP_SESSION_ACTIVE) session_write_close();
        header('Content-Type: ' . $row['mime_type']);
        header('Content-Disposition: inline; filename="' . addslashes($row['nama_asli']) . '"');
        header('Content-Length: ' . filesize($p));
        readfile($p);
        exit;
    }
}
