<?php
class PrintController {
    private Auth $auth;
    public function __construct(Auth $auth) { $this->auth = $auth; $auth->require(); }

    public function sesi(): void {
        $id = (int) input('id');
        $sesi = DB::one('
            SELECT s.*, d.nama AS desa_nama, k.nama AS kecamatan_nama,
                   b.nama AS bidang_nama, sb.nama AS sub_bidang_nama
            FROM kka_sesi s
            JOIN kka_desa d ON d.id = s.desa_id
            JOIN kka_kecamatan k ON k.id = d.kecamatan_id
            JOIN kka_bidang b ON b.id = s.bidang_id
            LEFT JOIN kka_sub_bidang sb ON sb.id = s.sub_bidang_id
            WHERE s.id = ?', [$id]);
        if (!$sesi) { http_response_code(404); exit('Sesi tidak ditemukan'); }
        if (!sesi_is_owned($this->auth, $sesi)) { http_response_code(403); exit('Anda tidak memiliki akses ke data audit ini.'); }
        $rincian = DB::all('SELECT * FROM kka_rincian WHERE sesi_id = ? ORDER BY urutan, id', [$id]);
        $totals = [
            'pagu'      => (float) $sesi['pagu_anggaran'],
            'pagu_rinci'=> array_sum(array_column($rincian, 'pagu_anggaran')),
            'dikwitansi'=> array_sum(array_column($rincian, 'biaya_dikwitansi')),
            'realisasi' => array_sum(array_column($rincian, 'realisasi')),
        ];
        $totals['selisih'] = $totals['realisasi'] - $totals['dikwitansi'];
        view('print/sesi', compact('sesi','rincian','totals'));
    }

    public function exportExcel(): void {
        $id = (int) input('id');
        $sesi = DB::one('
            SELECT s.*, d.nama AS desa_nama, k.nama AS kecamatan_nama,
                   b.nama AS bidang_nama, sb.nama AS sub_bidang_nama
            FROM kka_sesi s
            JOIN kka_desa d ON d.id = s.desa_id
            JOIN kka_kecamatan k ON k.id = d.kecamatan_id
            JOIN kka_bidang b ON b.id = s.bidang_id
            LEFT JOIN kka_sub_bidang sb ON sb.id = s.sub_bidang_id
            WHERE s.id = ?', [$id]);
        if (!$sesi) { http_response_code(404); exit('Sesi tidak ditemukan'); }
        if (!sesi_is_owned($this->auth, $sesi)) { http_response_code(403); exit('Anda tidak memiliki akses ke data audit ini.'); }
        // Lepaskan kunci sesi sebelum membangun & mengirim file Excel (operasi
        // berat/read-only) agar tidak menahan request lain dari user yang sama.
        if (session_status() === PHP_SESSION_ACTIVE) session_write_close();
        $rincian = DB::all('SELECT * FROM kka_rincian WHERE sesi_id = ? ORDER BY urutan, id', [$id]);

        $filename = 'KKA_' . preg_replace('/[^A-Za-z0-9]/', '_', $sesi['desa_nama']) . '_S' . $sesi['semester'] . '_' . $sesi['tahun_anggaran'] . '.xls';
        $this->outputExcelHeader($filename);
        $this->renderSesiExcel($sesi, $rincian);
        exit;
    }

    public function exportRekap(): void {
        $tahun    = (int) input('tahun', 0);
        $bidId    = (int) input('bidang', 0);
        $subBidId = (int) input('sub_bidang', 0);
        $kecId    = (int) input('kecamatan', 0);

        $where = '1=1'; $p = [];
        if ($tahun > 0)     { $where .= ' AND s.tahun_anggaran = ?';   $p[] = $tahun; }
        if ($bidId > 0)     { $where .= ' AND s.bidang_id = ?';         $p[] = $bidId; }
        if ($subBidId > 0)  { $where .= ' AND s.sub_bidang_id = ?';     $p[] = $subBidId; }
        if ($kecId > 0)     { $where .= ' AND d.kecamatan_id = ?';      $p[] = $kecId; }

        // Isolasi data: auditor hanya mengekspor rekap miliknya, admin semua
        [$ow, $op] = owner_where($this->auth);
        $where .= $ow; $p = array_merge($p, $op);
        // Lepaskan kunci sesi sebelum membangun & mengirim file rekap (berat/read-only).
        if (session_status() === PHP_SESSION_ACTIVE) session_write_close();

        // Rekap per (Sub Bidang, Kecamatan, Tahun) - konsisten dengan tampilan layar
        $rows = DB::all("
            SELECT
                COALESCE(sb.nama, '(Tanpa Sub Bidang)') AS sub_bidang,
                COALESCE(b.nama, '')                    AS bidang,
                k.nama                                  AS kecamatan,
                s.tahun_anggaran                        AS tahun,
                SUM(s.pagu_anggaran)                    AS pagu,
                COALESCE(SUM(rinc.dikwitansi_sesi),0)   AS dikwitansi,
                COALESCE(SUM(rinc.realisasi_sesi),0)    AS realisasi,
                COUNT(DISTINCT s.id)                    AS jumlah_sesi
            FROM kka_sesi s
            JOIN kka_desa d ON d.id = s.desa_id
            JOIN kka_kecamatan k ON k.id = d.kecamatan_id
            JOIN kka_bidang b ON b.id = s.bidang_id
            LEFT JOIN kka_sub_bidang sb ON sb.id = s.sub_bidang_id
            LEFT JOIN (
                SELECT sesi_id,
                       SUM(biaya_dikwitansi) AS dikwitansi_sesi,
                       SUM(realisasi) AS realisasi_sesi
                FROM kka_rincian
                GROUP BY sesi_id
            ) rinc ON rinc.sesi_id = s.id
            WHERE $where
            GROUP BY sub_bidang, bidang, k.nama, s.tahun_anggaran
            ORDER BY b.urutan, sb.nama, k.nama, s.tahun_anggaran DESC
        ", $p);

        $bidangNama = $bidId > 0 ? (DB::scalar('SELECT nama FROM kka_bidang WHERE id = ?', [$bidId]) ?: '') : 'SEMUA BIDANG';
        $filename = 'Rekap_KKA_' . ($tahun ?: 'semua') . '_' . date('Ymd_His') . '.xls';
        $this->outputExcelHeader($filename);
        $this->renderRekapExcel($rows, $bidangNama, $tahun);
        exit;
    }

    private function outputExcelHeader(string $filename): void {
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo "\xEF\xBB\xBF"; // BOM UTF-8 agar excel baca akurat
    }

    private function renderSesiExcel(array $s, array $rincian): void {
        // Disusun agar SAMA dengan tampilan Preview Cetak KKA (print/sesi.php):
        // kop, tabel identitas 2 kolom, tabel rincian, kesimpulan, sumber data,
        // dan blok tanda tangan 2 kolom (Auditor & Ketua Tim). Blok "Diketahui
        // oleh (Dalnis)" dihilangkan sesuai kebijakan reviu (hanya Ketua Tim).
        $tahun = (int)$s['tahun_anggaran'];
        echo '<html><head><meta charset="utf-8"></head>';
        echo '<body style="font-family:Times New Roman,serif;color:#000">';

        // KOP
        echo '<div style="text-align:center;line-height:1.3">'
           . '<div style="font-size:13pt;font-weight:bold">PEMERINTAH KABUPATEN ROKAN HILIR</div>'
           . '<div style="font-size:18pt;font-weight:bold">INSPEKTORAT</div>'
           . '<div style="font-size:10pt">Komplek Perkantoran Batu 6 Jl. Lintas Pesisir Sungai Rokan, Kec. Bangko - Bagansiapiapi</div>'
           . '<div style="font-size:10pt">Telp. (0767) 2700270 &middot; Email: inspektorat@rohilkab.go.id</div>'
           . '</div>';
        echo '<div style="border-bottom:3px double #000;margin:6px 0 12px"></div>';

        echo '<h2 style="text-align:center;margin:6px 0 2px;text-decoration:underline">KERTAS KERJA AUDIT (KKA)</h2>';
        echo '<h3 style="text-align:center;font-weight:normal;margin:0 0 12px">PENGELUARAN KEUANGAN KEPENGHULUAN &mdash; Tahun Anggaran ' . $tahun . '</h3>';

        // Tabel identitas 2 kolom (kiri & kanan), mengikuti layout preview cetak
        $lbl = 'font-weight:bold;white-space:nowrap';
        echo '<table cellpadding="3" style="width:100%;border-collapse:collapse;font-size:11pt">';
        $idRow = function(string $l1, string $v1, string $l2, string $v2) use ($lbl) {
            echo '<tr>'
               . '<td style="' . $lbl . '">' . e($l1) . '</td><td>:</td><td>' . e($v1) . '</td>'
               . '<td style="' . $lbl . '">' . e($l2) . '</td><td>:</td><td>' . e($v2) . '</td>'
               . '</tr>';
        };
        $idRow('Kepenghuluan / Desa', $s['desa_nama'] . ' (Kec. ' . $s['kecamatan_nama'] . ')', 'No. KKA', $s['no_kka'] ?: '-');
        $idRow('Bidang', $s['bidang_nama'], 'Ref. PKA', $s['ref_kka'] ?: '-');
        $idRow('Sub Bidang', $s['sub_bidang_nama'] ?: '-', 'Masa Audit', 'Semester ' . (int)$s['semester'] . ' Tahun ' . $tahun);
        $idRow('Kegiatan', $s['kegiatan'] ?: '-', 'Objek Audit', $s['objek_audit'] ?? '-');
        echo '<tr><td style="' . $lbl . '">Pagu Anggaran</td><td>:</td>'
           . '<td colspan="4" style="font-weight:bold">Rp ' . number_format((float)$s['pagu_anggaran'],0,',','.') . '</td></tr>';
        echo '</table><br>';

        // Tabel rincian
        echo '<table border="1" cellpadding="6" style="border-collapse:collapse;font-family:Arial;width:100%">';
        echo '<thead><tr style="background:#dcfce7">
                <th>No</th><th>Uraian / Rincian Belanja</th>
                <th>Pagu Anggaran (Rp)</th><th>Realisasi (Rp)</th><th>Biaya Dikwitansi (Rp)</th><th>Selisih (Rp)</th>
                <th>Penerima</th><th>Keterangan</th>
              </tr></thead><tbody>';
        $tp=$tk=$tr=$ts=0; $no=1;
        if (empty($rincian)) {
            echo '<tr><td colspan="8" align="center">- Belum ada rincian -</td></tr>';
        }
        foreach ($rincian as $r) {
            $sel = (float)$r['realisasi'] - (float)$r['biaya_dikwitansi'];
            $tp += (float)$r['pagu_anggaran'];
            $tk += (float)$r['biaya_dikwitansi'];
            $tr += (float)$r['realisasi']; $ts += $sel;
            echo '<tr>
                <td align="center">' . ($no++) . '</td>
                <td>' . e($r['uraian']) . '</td>
                <td align="right">' . number_format($r['pagu_anggaran'],0,',','.') . '</td>
                <td align="right">' . number_format($r['realisasi'],0,',','.') . '</td>
                <td align="right">' . number_format($r['biaya_dikwitansi'],0,',','.') . '</td>
                <td align="right">' . number_format($sel,0,',','.') . '</td>
                <td>' . e($r['penerima'] ?: '-') . '</td>
                <td>' . e($r['keterangan'] ?: '-') . '</td>
              </tr>';
        }
        echo '<tr style="background:#f3f4f6;font-weight:bold">
                <td colspan="2" align="center">JUMLAH</td>
                <td align="right">' . number_format($tp,0,',','.') . '</td>
                <td align="right">' . number_format($tr,0,',','.') . '</td>
                <td align="right">' . number_format($tk,0,',','.') . '</td>
                <td align="right">' . number_format($ts,0,',','.') . '</td>
                <td colspan="2"></td>
              </tr>';
        echo '</tbody></table><br>';

        echo '<p style="font-size:11pt"><b><u>KESIMPULAN AUDIT:</u></b><br>' . nl2br(e($s['kesimpulan'] ?: '-')) . '</p>';
        echo '<p style="font-size:11pt"><b><u>SUMBER DATA:</u></b><br>' . nl2br(e($s['sumber_data'] ?: '-')) . '</p>';

        // Tanda tangan 2 kolom (Auditor & Ketua Tim) - Dalnis dihilangkan
        echo '<br><br><table cellpadding="6" style="width:100%;font-size:11pt;text-align:center">';
        echo '<tr>'
           . '<td width="50%"><b>Disusun oleh,</b><br>Auditor<br><i>' . e(tgl_id($s['tanggal_dibuat'])) . '</i><br><br><br><br><b><u>' . e($s['dibuat_oleh'] ?: '...........................') . '</u></b></td>'
           . '<td width="50%"><b>Direview oleh,</b><br>Ketua Tim<br><i>' . e(tgl_id($s['tanggal_review'])) . '</i><br><br><br><br><b><u>' . e($s['direview_oleh'] ?: '...........................') . '</u></b></td>'
           . '</tr>';
        echo '</table>';

        echo '</body></html>';
    }

    private function renderRekapExcel(array $rows, string $bidangNama = 'SEMUA BIDANG', int $tahun = 0): void {
        echo '<html><head><meta charset="utf-8"></head><body style="font-family:Arial">';
        echo '<h2 style="text-align:center;margin:0 0 4px">REKAP KERTAS KERJA AUDIT - PER DESA</h2>';
        echo '<div style="text-align:center;margin-bottom:12px;font-size:12px">Inspektorat Kabupaten Rokan Hilir</div>';
        echo '<table cellpadding="4" style="margin-bottom:10px;font-size:12px">';
        echo '<tr><td><b>Bidang</b></td><td>:</td><td>' . e($bidangNama) . '</td></tr>';
        if ($tahun > 0) {
            echo '<tr><td><b>Tahun Anggaran</b></td><td>:</td><td>' . $tahun . '</td></tr>';
        }
        echo '</table>';
        echo '<table border="1" cellpadding="6" style="border-collapse:collapse">';
        echo '<thead><tr style="background:#10b981;color:white">
                <th>No</th><th>Sub Bidang</th><th>Kecamatan</th><th>Tahun</th>
                <th>Jumlah Sesi</th><th>Pagu (Rp)</th><th>Realisasi (Rp)</th>
                <th>Dikwitansi (Rp)</th><th>Selisih (Rp)</th><th>Keterangan</th>
              </tr></thead><tbody>';
        $tp=$tk=$tr=$ts=0; $no=1;
        if (empty($rows)) {
            echo '<tr><td colspan="10" align="center">- Belum ada data -</td></tr>';
        }
        foreach ($rows as $r) {
            $sel = (float)$r['realisasi'] - (float)$r['dikwitansi'];
            $tp += (float)$r['pagu']; $tk += (float)$r['dikwitansi'];
            $tr += (float)$r['realisasi']; $ts += $sel;
            echo '<tr>
                <td align="center">' . ($no++) . '</td>
                <td>' . e($r['sub_bidang']) . '</td>
                <td>' . e($r['kecamatan']) . '</td>
                <td align="center">' . (int)$r['tahun'] . '</td>
                <td align="center">' . (int)$r['jumlah_sesi'] . '</td>
                <td align="right">' . number_format($r['pagu'],0,',','.') . '</td>
                <td align="right">' . number_format($r['realisasi'],0,',','.') . '</td>
                <td align="right">' . number_format($r['dikwitansi'],0,',','.') . '</td>
                <td align="right">' . number_format($sel,0,',','.') . '</td>
                <td>&mdash;</td>
              </tr>';
        }
        echo '<tr style="background:#f0f0f0;font-weight:bold">
                <td colspan="5" align="center">JUMLAH</td>
                <td align="right">' . number_format($tp,0,',','.') . '</td>
                <td align="right">' . number_format($tr,0,',','.') . '</td>
                <td align="right">' . number_format($tk,0,',','.') . '</td>
                <td align="right">' . number_format($ts,0,',','.') . '</td>
                <td></td>
              </tr>';
        echo '</tbody></table></body></html>';
    }
}
