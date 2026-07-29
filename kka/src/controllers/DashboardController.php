<?php
class DashboardController {
    private Auth $auth;
    public function __construct(Auth $auth) { $this->auth = $auth; $auth->require(); }

    public function index(): void {
        // Isolasi data: auditor hanya melihat statistik miliknya, admin melihat semua
        [$ow, $op] = owner_where($this->auth);

        $stats = [
            'desa'    => (int) DB::scalar('SELECT COUNT(*) FROM kka_desa'),
            'kec'     => (int) DB::scalar('SELECT COUNT(*) FROM kka_kecamatan'),
            'sesi'    => (int) DB::scalar("SELECT COUNT(*) FROM kka_sesi s WHERE 1=1 $ow", $op),
            'sesi_ty' => (int) DB::scalar("SELECT COUNT(*) FROM kka_sesi s WHERE s.tahun_anggaran = ? $ow", array_merge([(int)date('Y')], $op)),
            'anggaran'=> (float) DB::scalar("SELECT COALESCE(SUM(s.pagu_anggaran),0) FROM kka_sesi s WHERE 1=1 $ow", $op),
            'dikwitansi'=>(float) DB::scalar("SELECT COALESCE(SUM(r.biaya_dikwitansi),0) FROM kka_rincian r JOIN kka_sesi s ON s.id = r.sesi_id WHERE 1=1 $ow", $op),
            'realisasi'=>(float) DB::scalar("SELECT COALESCE(SUM(r.realisasi),0) FROM kka_rincian r JOIN kka_sesi s ON s.id = r.sesi_id WHERE 1=1 $ow", $op),
        ];
        $stats['selisih'] = $stats['dikwitansi'] - $stats['realisasi'];

        // Ringkasan per DESA yang diaudit (bukan daftar sesi berulang).
        // Hanya desa yang punya sesi yang tampil; klik -> daftar sesi desa itu.
        $perDesa = DB::all("
            SELECT d.id, d.nama AS desa, k.nama AS kecamatan,
                   COUNT(s.id)                         AS jumlah,
                   COALESCE(SUM(s.pagu_anggaran),0)    AS pagu,
                   MAX(s.tahun_anggaran)               AS tahun_terakhir
            FROM kka_desa d
            JOIN kka_kecamatan k ON k.id = d.kecamatan_id
            JOIN kka_sesi s ON s.desa_id = d.id
            WHERE 1=1 $ow
            GROUP BY d.id, d.nama, k.nama
            ORDER BY jumlah DESC, d.nama ASC
        ", $op);

        $perBidang = DB::all("
            SELECT b.nama, COUNT(s.id) AS jumlah
            FROM kka_bidang b
            LEFT JOIN kka_sesi s ON s.bidang_id = b.id $ow
            GROUP BY b.id, b.nama ORDER BY b.urutan
        ", $op);

        view('dashboard/index', compact('stats','perDesa','perBidang'));
    }
}
