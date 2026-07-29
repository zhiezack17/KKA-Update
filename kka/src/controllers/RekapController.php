<?php
class RekapController {
    private Auth $auth;
    public function __construct(Auth $auth) { $this->auth = $auth; $auth->require(); }

    public function index(): void {
        $tahun = (int) input('tahun', 0);
        $desaId = (int) input('desa', 0);

        $where = '1=1'; $p = [];
        if ($tahun > 0) { $where .= ' AND s.tahun_anggaran = ?'; $p[] = $tahun; }
        if ($desaId > 0){ $where .= ' AND s.desa_id = ?'; $p[] = $desaId; }

        // Isolasi data: auditor hanya melihat rekap miliknya, admin melihat semua
        [$ow, $op] = owner_where($this->auth);
        $where .= $ow; $p = array_merge($p, $op);

        // Direkap PER DESA (digabung lintas tahun) supaya satu desa tampil satu baris.
        // Kolom Tahun menampilkan daftar tahun yang tergabung. Gunakan filter Tahun
        // untuk melihat satu tahun tertentu.
        $rows = DB::all("
            SELECT d.id AS desa_id, d.nama AS desa, k.nama AS kecamatan,
                   GROUP_CONCAT(DISTINCT s.tahun_anggaran ORDER BY s.tahun_anggaran DESC SEPARATOR ', ') AS tahun_list,
                   SUM(s.pagu_anggaran)                AS pagu,
                   COALESCE(SUM(rinc.dikwitansi_sesi),0) AS dikwitansi,
                   COALESCE(SUM(rinc.realisasi_sesi),0)  AS realisasi,
                   COUNT(DISTINCT s.id)                AS jumlah_sesi
            FROM kka_sesi s
            JOIN kka_desa d ON d.id = s.desa_id
            JOIN kka_kecamatan k ON k.id = d.kecamatan_id
            LEFT JOIN (
                SELECT sesi_id,
                       SUM(biaya_dikwitansi) AS dikwitansi_sesi,
                       SUM(realisasi) AS realisasi_sesi
                FROM kka_rincian
                GROUP BY sesi_id
            ) rinc ON rinc.sesi_id = s.id
            WHERE $where
            GROUP BY d.id, d.nama, k.nama
            ORDER BY d.nama ASC
        ", $p);

        $totals = [
            'pagu'      => array_sum(array_column($rows, 'pagu')),
            'dikwitansi'=> array_sum(array_column($rows, 'dikwitansi')),
            'realisasi' => array_sum(array_column($rows, 'realisasi')),
        ];
        $totals['selisih'] = $totals['dikwitansi'] - $totals['realisasi'];

        $desa = DB::all('SELECT id, nama FROM kka_desa ORDER BY nama');
        $tahuns = DB::all("SELECT DISTINCT tahun_anggaran AS t FROM kka_sesi s WHERE 1=1 $ow ORDER BY t DESC", $op);

        view('rekap/index', compact('rows','totals','desa','tahuns','tahun','desaId'));
    }
}
