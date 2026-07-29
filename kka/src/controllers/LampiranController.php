<?php
class LampiranController {
    private Auth $auth;
    public function __construct(Auth $auth) { $this->auth = $auth; $auth->require(); }

    public function upload(): void {
        only_post(); csrf_check();
        $cfg = $GLOBALS['cfg'];
        $sesiId = (int) input('sesi_id');

        try {
            // ==========================================================
            // Wrap seluruh logika upload dalam try/catch supaya fatal error
            // (folder tidak writable, ekstensi finfo tidak ada, dsb) tidak
            // muncul sebagai HTTP 500 blank tanpa pesan. Setiap kegagalan
            // dicatat ke error_log dan pengguna diarahkan balik dengan
            // pesan flash yang jelas.
            // ==========================================================

            $sesi = DB::one('SELECT id, created_by FROM kka_sesi WHERE id = ?', [$sesiId]);
            guard_sesi($this->auth, $sesi);

            // Cek error upload level PHP dulu
            if (empty($_FILES['file']) || !isset($_FILES['file']['error'])) {
                throw new RuntimeException('Tidak ada file yang dikirim. Pastikan Anda memilih file lebih dulu.');
            }
            $err = (int) $_FILES['file']['error'];
            if ($err !== UPLOAD_ERR_OK) {
                throw new RuntimeException($this->uploadErrorMessage($err));
            }
            $f = $_FILES['file'];

            // Cek ukuran
            $maxBytes = (int)($cfg['max_upload_mb'] ?? 10) * 1024 * 1024;
            if ((int)$f['size'] > $maxBytes) {
                throw new RuntimeException('Ukuran file (' . round($f['size']/1024/1024, 2) . ' MB) melebihi batas ' . ($cfg['max_upload_mb'] ?? 10) . ' MB.');
            }
            if ((int)$f['size'] === 0) {
                throw new RuntimeException('File kosong (0 byte). Silakan pilih file yang benar.');
            }

            // Deteksi MIME - fallback ke ekstensi jika finfo tidak tersedia
            $mime = $this->detectMime($f);
            $allowed = $cfg['allowed_mimes'] ?? [];
            if (!empty($allowed) && !in_array($mime, $allowed, true)) {
                throw new RuntimeException('Tipe file tidak diizinkan (' . $mime . '). Hanya PDF/Excel/gambar (JPG/PNG/WEBP/GIF).');
            }

            // Pastikan direktori upload ada & writable
            $uploadDir = $cfg['upload_dir'];
            if (!is_dir($uploadDir)) {
                if (!@mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
                    throw new RuntimeException('Folder uploads tidak dapat dibuat di server. Silakan hubungi admin server (permission ' . $uploadDir . ').');
                }
            }
            if (!is_writable($uploadDir)) {
                throw new RuntimeException('Folder uploads tidak bisa ditulisi. Set permission 775 pada folder ' . basename($uploadDir) . '.');
            }

            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            $ext = preg_replace('/[^a-z0-9]/', '', $ext) ?: 'bin';
            $newName = 'lamp_' . $sesiId . '_' . bin2hex(random_bytes(6)) . '.' . $ext;

            $dest = $uploadDir . '/' . $newName;
            if (!@move_uploaded_file($f['tmp_name'], $dest)) {
                throw new RuntimeException('Gagal memindahkan file ke folder tujuan. Cek permission folder uploads.');
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
            flash('success', 'Lampiran "' . $f['name'] . '" berhasil diunggah.');
            redirect('sesi/show?id=' . $sesiId);

        } catch (Throwable $ex) {
            // Log detail (nama file, error) supaya bisa diinvestigasi di error_log server
            error_log('[LampiranUpload] sesi=' . $sesiId
                    . ' file=' . ($_FILES['file']['name'] ?? '-')
                    . ' size=' . ($_FILES['file']['size'] ?? 0)
                    . ' err=' . ($_FILES['file']['error'] ?? '-')
                    . ' msg=' . $ex->getMessage());
            flash('error', 'Upload gagal: ' . $ex->getMessage());
            redirect('sesi/show?id=' . $sesiId);
        }
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

    /* ==========================================================
     * HELPERS
     * ========================================================== */

    /** Deteksi MIME dengan urutan: finfo ext > mime_content_type > pemetaan ekstensi. */
    private function detectMime(array $f): string {
        // Coba finfo dulu (paling akurat)
        if (class_exists('finfo')) {
            try {
                $fi = new finfo(FILEINFO_MIME_TYPE);
                $mime = $fi->file($f['tmp_name']);
                if ($mime) return $mime;
            } catch (Throwable $e) { /* lanjut ke fallback */ }
        }
        // Fallback: mime_content_type() (butuh ekstensi fileinfo juga tapi API lebih permisif)
        if (function_exists('mime_content_type')) {
            $mime = @mime_content_type($f['tmp_name']);
            if ($mime) return $mime;
        }
        // Fallback terakhir: pemetaan ekstensi manual
        $ext = strtolower(pathinfo($f['name'] ?? '', PATHINFO_EXTENSION));
        $map = [
            'pdf'  => 'application/pdf',
            'xls'  => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'jpg'  => 'image/jpeg', 'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'webp' => 'image/webp',
            'gif'  => 'image/gif',
        ];
        return $map[$ext] ?? 'application/octet-stream';
    }

    /** Terjemahkan kode error PHP upload ke pesan yang berguna. */
    private function uploadErrorMessage(int $code): string {
        switch ($code) {
            case UPLOAD_ERR_INI_SIZE:
                return 'File melebihi upload_max_filesize di php.ini (' . ini_get('upload_max_filesize') . '). Naikkan di aaPanel → PHP → Config.';
            case UPLOAD_ERR_FORM_SIZE:
                return 'File melebihi ukuran maksimum yang diizinkan form.';
            case UPLOAD_ERR_PARTIAL:
                return 'File hanya terunggah sebagian. Coba ulangi.';
            case UPLOAD_ERR_NO_FILE:
                return 'Tidak ada file yang diunggah.';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Folder tmp PHP tidak ada. Cek konfigurasi server (upload_tmp_dir).';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Gagal menulis file ke disk. Cek permission tmp folder.';
            case UPLOAD_ERR_EXTENSION:
                return 'Upload dihentikan oleh ekstensi PHP.';
            default:
                return 'Error upload tidak diketahui (kode ' . $code . ').';
        }
    }
}
