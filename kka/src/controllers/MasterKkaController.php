<?php
/**
 * MasterKkaController - kelola dokumen Master KKA (3 tipe):
 *  - standar : KKP Standar Narasi (menceritakan kondisi lapangan)
 *  - fisik   : KKA Pengukuran Fisik (jalan, drainase - STA, Jarak, Lebar I & II, Tebal, Volume)
 *  - sketsa  : Sketsa / Foto Lapangan (upload foto sebagai pengganti gambar manual)
 *
 * Setiap dokumen terhubung ke Sesi Audit yang sudah ada.
 */
class MasterKkaController {
    private Auth $auth;
    private const TIPE_LABEL = [
        'standar' => 'KKP Standar (Narasi Audit)',
        'fisik'   => 'KKA Fisik (Pengukuran)',
        'sketsa'  => 'KKA Sketsa / Foto Lapangan',
    ];

    public function __construct(Auth $auth) {
        $this->auth = $auth;
        $auth->require();
    }

    /* -------------------- LIST -------------------- */
    public function index(): void {
        $tipe   = (string) input('tipe', '');
        $sesiId = (int) input('sesi', 0);

        $where = '1=1'; $p = [];
        if ($tipe !== '' && isset(self::TIPE_LABEL[$tipe])) {
            $where .= ' AND m.tipe = ?'; $p[] = $tipe;
        }
        if ($sesiId > 0) { $where .= ' AND m.sesi_id = ?'; $p[] = $sesiId; }

        [$ow, $op] = owner_where($this->auth);
        $where .= $ow; $p = array_merge($p, $op);

        $rows = DB::all("
            SELECT m.*, s.objek_audit, s.tahun_anggaran, s.semester,
                   d.nama AS desa_nama, k.nama AS kecamatan_nama
            FROM kka_master m
            JOIN kka_sesi s ON s.id = m.sesi_id
            JOIN kka_desa d ON d.id = s.desa_id
            JOIN kka_kecamatan k ON k.id = d.kecamatan_id
            WHERE $where
            ORDER BY m.created_at DESC
        ", $p);

        // Kandidat sesi untuk dropdown filter
        [$owS, $opS] = owner_where($this->auth);
        $sesiList = DB::all("
            SELECT s.id, s.objek_audit, s.tahun_anggaran, s.semester, d.nama AS desa_nama
            FROM kka_sesi s JOIN kka_desa d ON d.id = s.desa_id
            WHERE 1=1 $owS ORDER BY s.created_at DESC LIMIT 200
        ", $opS);

        $tipeLabel = self::TIPE_LABEL;
        view('master/index', compact('rows','tipeLabel','tipe','sesiId','sesiList'));
    }

    /* -------------------- CREATE -------------------- */
    public function create(): void {
        $sesiId = (int) input('sesi', 0);
        $tipe   = (string) input('tipe', 'standar');
        if (!isset(self::TIPE_LABEL[$tipe])) $tipe = 'standar';

        [$owS, $opS] = owner_where($this->auth);
        $sesiList = DB::all("
            SELECT s.id, s.objek_audit, s.tahun_anggaran, s.semester, d.nama AS desa_nama
            FROM kka_sesi s JOIN kka_desa d ON d.id = s.desa_id
            WHERE 1=1 $owS ORDER BY s.created_at DESC LIMIT 200
        ", $opS);

        $tipeLabel = self::TIPE_LABEL;
        view('master/create', compact('sesiList','tipe','sesiId','tipeLabel'));
    }

    public function store(): void {
        only_post(); csrf_check();
        $sesiId = (int) input('sesi_id');
        $tipe   = (string) input('tipe');
        if (!isset(self::TIPE_LABEL[$tipe])) {
            flash('error','Tipe Master KKA tidak valid.');
            redirect('master');
        }
        $sesi = DB::one('SELECT id, created_by FROM kka_sesi WHERE id = ?', [$sesiId]);
        guard_sesi($this->auth, $sesi, 'master');

        $judul = trim((string) input('judul'));
        if ($judul === '') $judul = self::TIPE_LABEL[$tipe];

        $id = DB::insert('kka_master', [
            'sesi_id'         => $sesiId,
            'tipe'            => $tipe,
            'judul'           => $judul,
            'no_kka'          => trim((string) input('no_kka')) ?: null,
            'ref_pka'         => trim((string) input('ref_pka')) ?: null,
            'narasi'          => null,
            'pendamping'      => null,
            'ketua_tim'       => null,
            'pendamping_nip'  => null,
            'ketua_tim_nip'   => null,
            'tanggal_dok'     => input('tanggal_dok') ?: null,
            'created_by'      => $this->auth->id(),
        ]);
        flash('success','Dokumen Master KKA dibuat. Silakan isi datanya.');
        redirect('master/edit?id=' . $id);
    }

    /* -------------------- EDIT / DETAIL -------------------- */
    public function edit(): void {
        $id = (int) input('id');
        $m = $this->loadMaster($id);
        if (!$m) { flash('error','Dokumen tidak ditemukan.'); redirect('master'); }

        $fisikRows = ($m['tipe'] === 'fisik')
            ? DB::all('SELECT * FROM kka_master_fisik WHERE master_id = ? ORDER BY urutan, id', [$id])
            : [];
        $foto = ($m['tipe'] === 'sketsa')
            ? DB::all('SELECT * FROM kka_master_foto WHERE master_id = ? ORDER BY urutan, id', [$id])
            : [];

        $tipeLabel = self::TIPE_LABEL;
        view('master/edit', compact('m','fisikRows','foto','tipeLabel'));
    }

    public function update(): void {
        only_post(); csrf_check();
        $id = (int) input('id');
        $m = $this->loadMaster($id);
        if (!$m) { flash('error','Dokumen tidak ditemukan.'); redirect('master'); }

        $data = [
            'judul'           => trim((string) input('judul')) ?: self::TIPE_LABEL[$m['tipe']],
            'no_kka'          => trim((string) input('no_kka')) ?: null,
            'ref_pka'         => trim((string) input('ref_pka')) ?: null,
            'narasi'          => trim((string) input('narasi')) ?: null,
            'pendamping'      => trim((string) input('pendamping')) ?: null,
            'ketua_tim'       => trim((string) input('ketua_tim')) ?: null,
            'pendamping_nip'  => trim((string) input('pendamping_nip')) ?: null,
            'ketua_tim_nip'   => trim((string) input('ketua_tim_nip')) ?: null,
            'tanggal_dok'     => input('tanggal_dok') ?: null,
        ];
        DB::update('kka_master', $data, ['id' => $id]);

        // Simpan baris fisik jika tipe = fisik
        if ($m['tipe'] === 'fisik') {
            DB::q('DELETE FROM kka_master_fisik WHERE master_id = ?', [$id]);
            $sta   = (array) input('sta', []);
            $jarak = (array) input('jarak', []);
            $l1    = (array) input('lebar_i', []);
            $l2    = (array) input('lebar_ii', []);
            $tebal = (array) input('tebal', []);
            $ket   = (array) input('keterangan_baris', []);
            foreach ($sta as $i => $sVal) {
                $sVal = trim((string)$sVal);
                $jr = parse_money($jarak[$i] ?? 0);
                $lI = parse_money($l1[$i] ?? 0);
                $lII= parse_money($l2[$i] ?? 0);
                $tb = parse_money($tebal[$i] ?? 0);
                if ($sVal === '' && $jr==0 && $lI==0 && $lII==0 && $tb==0) continue;
                // Volume = jarak * ((lebar_I + lebar_II) / 2) * tebal
                $vol = $jr * (($lI + $lII) / 2) * $tb;
                DB::insert('kka_master_fisik', [
                    'master_id'  => $id,
                    'urutan'     => $i + 1,
                    'sta'        => $sVal ?: null,
                    'jarak'      => $jr,
                    'lebar_i'    => $lI,
                    'lebar_ii'   => $lII,
                    'tebal'      => $tb,
                    'volume'     => $vol,
                    'keterangan' => trim((string)($ket[$i] ?? '')) ?: null,
                ]);
            }
        }

        flash('success','Master KKA diperbarui.');
        redirect('master/edit?id=' . $id);
    }

    public function delete(): void {
        only_post(); csrf_check();
        $id = (int) input('id');
        $m = $this->loadMaster($id);
        if (!$m) { flash('error','Dokumen tidak ditemukan.'); redirect('master'); }

        // Hapus foto fisik
        $foto = DB::all('SELECT nama_file FROM kka_master_foto WHERE master_id = ?', [$id]);
        foreach ($foto as $f) {
            $p = $GLOBALS['cfg']['upload_dir'] . '/master/' . $f['nama_file'];
            if (is_file($p)) @unlink($p);
        }
        DB::delete('kka_master', ['id' => $id]);
        flash('success','Master KKA dihapus.');
        redirect('master');
    }

    /* -------------------- FOTO (untuk tipe sketsa) -------------------- */
    public function uploadFoto(): void {
        only_post(); csrf_check();
        $masterId = (int) input('master_id');
        $m = $this->loadMaster($masterId);
        if (!$m) { flash('error','Dokumen tidak ditemukan.'); redirect('master'); }

        if (empty($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            flash('error','Gagal upload foto: ' . ($_FILES['foto']['error'] ?? 'unknown'));
            redirect('master/edit?id=' . $masterId);
        }
        $f = $_FILES['foto'];
        if ($f['size'] > 10 * 1024 * 1024) {
            flash('error','Ukuran foto melebihi 10 MB.');
            redirect('master/edit?id=' . $masterId);
        }
        $mime = @mime_content_type($f['tmp_name']) ?: 'application/octet-stream';
        if (!in_array($mime, ['image/jpeg','image/png','image/webp','image/gif'], true)) {
            flash('error','Tipe file harus foto (JPG/PNG/WEBP/GIF). Terdeteksi: ' . $mime);
            redirect('master/edit?id=' . $masterId);
        }

        $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        $ext = preg_replace('/[^a-z0-9]/', '', $ext) ?: 'jpg';
        $newName = 'mfoto_' . $masterId . '_' . bin2hex(random_bytes(6)) . '.' . $ext;

        $dirMaster = $GLOBALS['cfg']['upload_dir'] . '/master';
        if (!is_dir($dirMaster)) @mkdir($dirMaster, 0775, true);
        $dest = $dirMaster . '/' . $newName;
        if (!move_uploaded_file($f['tmp_name'], $dest)) {
            flash('error','Gagal menyimpan file ke server.');
            redirect('master/edit?id=' . $masterId);
        }

        $next = (int) DB::scalar('SELECT COALESCE(MAX(urutan),0)+1 FROM kka_master_foto WHERE master_id = ?', [$masterId]);
        DB::insert('kka_master_foto', [
            'master_id'  => $masterId,
            'urutan'     => $next,
            'nama_asli'  => $f['name'],
            'nama_file'  => $newName,
            'mime_type'  => $mime,
            'ukuran'     => (int) $f['size'],
            'keterangan' => trim((string) input('keterangan')) ?: null,
        ]);
        flash('success','Foto berhasil diunggah.');
        redirect('master/edit?id=' . $masterId);
    }

    public function deleteFoto(): void {
        only_post(); csrf_check();
        $id = (int) input('id');
        $masterId = (int) input('master_id');
        $m = $this->loadMaster($masterId);
        if (!$m) { flash('error','Dokumen tidak ditemukan.'); redirect('master'); }

        $row = DB::one('SELECT nama_file FROM kka_master_foto WHERE id = ? AND master_id = ?', [$id, $masterId]);
        if ($row) {
            $p = $GLOBALS['cfg']['upload_dir'] . '/master/' . $row['nama_file'];
            if (is_file($p)) @unlink($p);
            DB::delete('kka_master_foto', ['id' => $id]);
            flash('success','Foto dihapus.');
        }
        redirect('master/edit?id=' . $masterId);
    }

    public function foto(): void {
        // Stream 1 foto (untuk <img src=...>)
        $id = (int) input('id');
        $row = DB::one('SELECT f.*, m.sesi_id, s.created_by
                        FROM kka_master_foto f
                        JOIN kka_master m ON m.id = f.master_id
                        JOIN kka_sesi s ON s.id = m.sesi_id
                        WHERE f.id = ?', [$id]);
        if (!$row) { http_response_code(404); exit('Not found'); }
        if (!sesi_is_owned($this->auth, $row)) { http_response_code(403); exit('Forbidden'); }
        $p = $GLOBALS['cfg']['upload_dir'] . '/master/' . $row['nama_file'];
        if (!is_file($p)) { http_response_code(404); exit('File missing'); }
        if (session_status() === PHP_SESSION_ACTIVE) session_write_close();
        header('Content-Type: ' . ($row['mime_type'] ?: 'application/octet-stream'));
        header('Content-Length: ' . filesize($p));
        header('Cache-Control: private, max-age=3600');
        readfile($p);
        exit;
    }

    /* -------------------- PREVIEW / PRINT -------------------- */
    public function preview(): void {
        $id = (int) input('id');
        $m = $this->loadMaster($id);
        if (!$m) { http_response_code(404); exit('Tidak ditemukan'); }

        $fisikRows = ($m['tipe'] === 'fisik')
            ? DB::all('SELECT * FROM kka_master_fisik WHERE master_id = ? ORDER BY urutan, id', [$id])
            : [];
        $foto = ($m['tipe'] === 'sketsa')
            ? DB::all('SELECT * FROM kka_master_foto WHERE master_id = ? ORDER BY urutan, id', [$id])
            : [];

        view('master/preview', compact('m','fisikRows','foto'));
    }

    public function export(): void {
        $id = (int) input('id');
        $m = $this->loadMaster($id);
        if (!$m) { http_response_code(404); exit('Tidak ditemukan'); }
        if (session_status() === PHP_SESSION_ACTIVE) session_write_close();

        $fisikRows = ($m['tipe'] === 'fisik')
            ? DB::all('SELECT * FROM kka_master_fisik WHERE master_id = ? ORDER BY urutan, id', [$id])
            : [];
        $filename = 'MasterKKA_' . $m['tipe'] . '_' . preg_replace('/[^A-Za-z0-9]/','_', $m['desa_nama']) . '_' . date('Ymd') . '.xls';
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo "\xEF\xBB\xBF";
        $this->renderMasterExcel($m, $fisikRows);
        exit;
    }

    /* -------------------- DOWNLOAD TEMPLATE .XLS -------------------- */
    public function downloadTemplate(): void {
        $templatePath = $GLOBALS['cfg']['root_dir'] . '/public/uploads/master/KKP_MASTER.xls';
        if (!is_file($templatePath)) {
            http_response_code(404);
            exit('File template belum tersedia. Silakan admin upload dulu file KKP_MASTER.xls ke folder /uploads/master/.');
        }
        if (session_status() === PHP_SESSION_ACTIVE) session_write_close();
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="KKP_MASTER.xls"');
        header('Content-Length: ' . filesize($templatePath));
        readfile($templatePath);
        exit;
    }

    /* -------------------- HELPERS -------------------- */
    private function loadMaster(int $id): ?array {
        $m = DB::one("
            SELECT m.*, s.desa_id, s.created_by, s.objek_audit, s.tahun_anggaran, s.semester,
                   s.kegiatan, s.no_kka AS sesi_no_kka, s.pagu_anggaran,
                   s.dibuat_oleh, s.tanggal_dibuat, s.direview_oleh, s.tanggal_review,
                   b.nama AS bidang_nama, sb.nama AS sub_bidang_nama,
                   d.nama AS desa_nama, k.nama AS kecamatan_nama
            FROM kka_master m
            JOIN kka_sesi s ON s.id = m.sesi_id
            JOIN kka_desa d ON d.id = s.desa_id
            JOIN kka_kecamatan k ON k.id = d.kecamatan_id
            JOIN kka_bidang b ON b.id = s.bidang_id
            LEFT JOIN kka_sub_bidang sb ON sb.id = s.sub_bidang_id
            WHERE m.id = ?
        ", [$id]);
        if (!$m) return null;
        if (!sesi_is_owned($this->auth, ['created_by' => $m['created_by'], 'sesi_id' => $m['sesi_id']])) {
            http_response_code(403);
            exit('Akses ditolak.');
        }
        return $m;
    }

    private function renderMasterExcel(array $m, array $fisikRows): void {
        echo '<html><head><meta charset="utf-8"></head><body style="font-family:Times New Roman,serif">';
        echo '<div style="text-align:center"><b>INSPEKTORAT KABUPATEN ROKAN HILIR</b></div>';
        echo '<div style="text-align:center;font-size:14pt;font-weight:bold;text-decoration:underline;margin:8px 0">' . e(self::TIPE_LABEL[$m['tipe']]) . '</div>';
        echo '<table cellpadding="4" style="width:100%;font-size:11pt">';
        echo '<tr><td><b>Objek Audit</b></td><td>:</td><td>' . e($m['objek_audit']) . '</td>'
           . '<td><b>No. KKA</b></td><td>:</td><td>' . e($m['no_kka'] ?: $m['sesi_no_kka'] ?: '-') . '</td></tr>';
        echo '<tr><td><b>Kepenghuluan</b></td><td>:</td><td>' . e($m['desa_nama']) . ' (Kec. ' . e($m['kecamatan_nama']) . ')</td>'
           . '<td><b>No. Ref PKA</b></td><td>:</td><td>' . e($m['ref_pka'] ?: '-') . '</td></tr>';
        echo '<tr><td><b>Tahun Anggaran</b></td><td>:</td><td>' . (int)$m['tahun_anggaran'] . '</td>'
           . '<td><b>Dibuat oleh</b></td><td>:</td><td>' . e($m['dibuat_oleh'] ?: '-') . '</td></tr>';
        echo '<tr><td><b>Kegiatan</b></td><td>:</td><td colspan="4">' . e($m['kegiatan'] ?: '-') . '</td></tr>';
        echo '</table><br>';

        if ($m['tipe'] === 'standar') {
            echo '<div style="min-height:400px;white-space:pre-wrap;font-size:11pt">' . e((string)$m['narasi']) . '</div>';
        } elseif ($m['tipe'] === 'fisik') {
            echo '<table border="1" cellpadding="6" style="border-collapse:collapse;width:100%;font-size:11pt">';
            echo '<thead><tr style="background:#dcfce7">
                    <th rowspan="2">No</th><th rowspan="2">STA</th><th rowspan="2">Jarak (m)</th>
                    <th colspan="2">Lebar (m)</th><th rowspan="2">Tebal (m)</th>
                    <th rowspan="2">Volume (m&sup3;)</th><th rowspan="2">Keterangan</th></tr>
                    <tr><th>I</th><th>II</th></tr></thead><tbody>';
            $no=1; $tVol=0;
            foreach ($fisikRows as $r) {
                $tVol += (float)$r['volume'];
                echo '<tr>
                    <td align="center">' . ($no++) . '</td>
                    <td align="center">' . e($r['sta']) . '</td>
                    <td align="right">' . number_format($r['jarak'],3,',','.') . '</td>
                    <td align="right">' . number_format($r['lebar_i'],3,',','.') . '</td>
                    <td align="right">' . number_format($r['lebar_ii'],3,',','.') . '</td>
                    <td align="right">' . number_format($r['tebal'],3,',','.') . '</td>
                    <td align="right"><b>' . number_format($r['volume'],3,',','.') . '</b></td>
                    <td>' . e($r['keterangan']) . '</td>
                  </tr>';
            }
            echo '<tr style="background:#f3f4f6;font-weight:bold">
                    <td colspan="6" align="center">JUMLAH VOLUME</td>
                    <td align="right">' . number_format($tVol,3,',','.') . '</td>
                    <td></td>
                  </tr>';
            echo '</tbody></table>';
        }

        // Tanda tangan
        echo '<br><br><table cellpadding="6" style="width:100%;font-size:11pt;text-align:center">';
        $tgl = $m['tanggal_dok'] ? tgl_id($m['tanggal_dok']) : 'Bagansiapiapi, ...........';
        echo '<tr><td colspan="2"><i>' . e($tgl) . '</i></td></tr>';
        echo '<tr><td><b>Pendamping Dilapangan</b><br><br><br><br><b><u>' . e($m['pendamping'] ?: '.......................') . '</u></b><br>' . e($m['pendamping_nip'] ? 'NIP. ' . $m['pendamping_nip'] : '') . '</td>'
           . '<td><b>Ketua Tim</b><br><br><br><br><b><u>' . e($m['ketua_tim'] ?: '.......................') . '</u></b><br>' . e($m['ketua_tim_nip'] ? 'NIP. ' . $m['ketua_tim_nip'] : '') . '</td></tr>';
        echo '</table></body></html>';
    }
}
