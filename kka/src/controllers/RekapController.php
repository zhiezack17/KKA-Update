<?php
class RekapController {
    private Auth $auth;
    public function __construct(Auth $auth) { $this->auth = $auth; $auth->require(); }

    public function index(): void {
        $tahun      = (int) input('tahun', 0);
        $bidId      = (int) input('bidang', 0);
        $subBidId   = (int) input('sub_bidang', 0);
        $kecId      = (int) input('kecamatan', 0);

        $where = '1=1'; $p = [];
        if ($tahun > 0)     { $where .= ' AND s.tahun_anggaran = ?';   $p[] = $tahun; }
        if ($bidId > 0)     { $where .= ' AND s.bidang_id = ?';         $p[] = $bidId; }
        if ($subBidId > 0)  { $where .= ' AND s.sub_bidang_id = ?';     $p[] = $subBidId; }
        if ($kecId > 0)     { $where .= ' AND d.kecamatan_id = ?';      $p[] = $kecId; }

        // Isolasi data: auditor hanya melihat rekap miliknya, admin melihat semua
        [$ow, $op] = owner_where($this->auth);
        $where .= $ow; $p = array_merge($p, $op);

        // Rekap dikelompokkan per (Sub Bidang, Kecamatan, Tahun).
        // Ini mengikuti template rekap Inspektorat: satu baris = satu Sub Bidang
        // per Kecamatan per Tahun Anggaran, sinkron dengan input Sesi Audit.
        $rows = DB::all("
            SELECT
                COALESCE(sb.id, 0)                    AS sub_bidang_id,
                COALESCE(sb.nama, '(Tanpa Sub Bidang)') AS sub_bidang,
                COALESCE(b.id, 0)                     AS bidang_id,
                COALESCE(b.nama, '')                  AS bidang,
                k.id                                  AS kecamatan_id,
                k.nama                                AS kecamatan,
                s.tahun_anggaran                      AS tahun,
                SUM(s.pagu_anggaran)                  AS pagu,
                COALESCE(SUM(rinc.dikwitansi_sesi),0) AS dikwitansi,
                COALESCE(SUM(rinc.realisasi_sesi),0)  AS realisasi,
                COUNT(DISTINCT s.id)                  AS jumlah_sesi
            FROM kka_sesi s
            JOIN kka_desa d ON d.id = s.desa_id
            JOIN kka_kecamatan k ON k.id = d.kecamatan_id
            JOIN kka_bidang b ON b.id = s.bidang_id
            LEFT JOIN kka_sub_bidang sb ON sb.id = s.sub_bidang_id
            LEFT JOIN (
                SELECT sesi_id,
                       SUM(biaya_dikwitansi) AS dikwitansi_sesi,
                       SUM(realisasi)        AS realisasi_sesi
                FROM kka_rincian
                GROUP BY sesi_id
            ) rinc ON rinc.sesi_id = s.id
            WHERE $where
            GROUP BY sub_bidang_id, sub_bidang, bidang_id, bidang, k.id, k.nama, s.tahun_anggaran
            ORDER BY b.urutan, sb.nama, k.nama, s.tahun_anggaran DESC
        ", $p);

        $totals = [
            'pagu'      => array_sum(array_column($rows, 'pagu')),
            'dikwitansi'=> array_sum(array_column($rows, 'dikwitansi')),
            'realisasi' => array_sum(array_column($rows, 'realisasi')),
        ];
        // Selisih = Realisasi - Dikwitansi (sesuai kebijakan baru)
        $totals['selisih'] = $totals['realisasi'] - $totals['dikwitansi'];

        // Data untuk dropdown filter
        $bidang    = DB::all('SELECT id, nama FROM kka_bidang ORDER BY urutan');
        $subBidang = $bidId > 0
            ? DB::all('SELECT id, nama FROM kka_sub_bidang WHERE bidang_id = ? ORDER BY nama', [$bidId])
            : [];
        $kecamatan = DB::all('SELECT id, nama FROM kka_kecamatan ORDER BY nama');
        $tahuns    = DB::all("SELECT DISTINCT tahun_anggaran AS t FROM kka_sesi s WHERE 1=1 $ow ORDER BY t DESC", $op);

        // Nama Bidang & Sub Bidang yang dipilih (untuk header)
        $bidangNama    = $bidId > 0    ? (DB::scalar('SELECT nama FROM kka_bidang WHERE id = ?', [$bidId])    ?: '') : '';
        $subBidangNama = $subBidId > 0 ? (DB::scalar('SELECT nama FROM kka_sub_bidang WHERE id = ?', [$subBidId]) ?: '') : '';
        $kecamatanNama = $kecId > 0    ? (DB::scalar('SELECT nama FROM kka_kecamatan WHERE id = ?', [$kecId]) ?: '') : '';

        view('rekap/index', compact(
            'rows','totals','bidang','subBidang','kecamatan','tahuns',
            'tahun','bidId','subBidId','kecId',
            'bidangNama','subBidangNama','kecamatanNama'
        ));
    }
}
