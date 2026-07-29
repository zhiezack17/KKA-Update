<?php
class RincianController {
    private Auth $auth;
    public function __construct(Auth $auth) { $this->auth = $auth; $auth->require(); }

    /** Pastikan sesi induk milik user (atau admin). Hentikan bila tidak. */
    private function assertSesiOwned(int $sesiId): void {
        $sesi = DB::one('SELECT id, created_by FROM kka_sesi WHERE id = ?', [$sesiId]);
        if (!sesi_is_owned($this->auth, $sesi)) {
            flash('error', 'Anda tidak memiliki akses ke sesi audit ini.');
            redirect('sesi');
        }
    }

    public function store(): void {
        only_post(); csrf_check();
        $sesiId = (int) input('sesi_id');
        $this->assertSesiOwned($sesiId);
        $uraian = trim((string) input('uraian'));
        if (!$sesiId || $uraian === '') {
            flash('error', 'Uraian belanja wajib diisi.');
            redirect('sesi/show?id=' . $sesiId);
        }
        $next = (int) DB::scalar('SELECT COALESCE(MAX(urutan),0)+1 FROM kka_rincian WHERE sesi_id = ?', [$sesiId]);
        DB::insert('kka_rincian', [
            'sesi_id'         => $sesiId,
            'urutan'          => $next,
            'uraian'          => $uraian,
            'pagu_anggaran'   => 0,
            'biaya_dikwitansi'=> parse_money(input('biaya_dikwitansi', 0)),
            'realisasi'       => parse_money(input('realisasi', 0)),
            'penerima'        => trim((string) input('penerima')) ?: null,
            'keterangan'      => trim((string) input('keterangan')) ?: null,
        ]);
        flash('success', 'Rincian belanja ditambahkan.');
        redirect('sesi/show?id=' . $sesiId);
    }

    public function update(): void {
        only_post(); csrf_check();
        $id     = (int) input('id');
        $sesiId = (int) input('sesi_id');
        $this->assertSesiOwned($sesiId);
        $uraian = trim((string) input('uraian'));
        // Pastikan rincian milik sesi yang sesuai (cegah update arbitrary)
        $row = DB::one('SELECT id FROM kka_rincian WHERE id = ? AND sesi_id = ?', [$id, $sesiId]);
        if (!$row) { flash('error', 'Rincian tidak ditemukan.'); redirect('sesi/show?id=' . $sesiId); }
        if ($uraian === '') { flash('error', 'Uraian belanja wajib diisi.'); redirect('sesi/show?id=' . $sesiId); }
        DB::update('kka_rincian', [
            'uraian'          => $uraian,
            'biaya_dikwitansi'=> parse_money(input('biaya_dikwitansi', 0)),
            'realisasi'       => parse_money(input('realisasi', 0)),
            'penerima'        => trim((string) input('penerima')) ?: null,
            'keterangan'      => trim((string) input('keterangan')) ?: null,
        ], ['id' => $id]);
        flash('success', 'Rincian diperbarui.');
        redirect('sesi/show?id=' . $sesiId);
    }

    public function delete(): void {
        only_post(); csrf_check();
        $id = (int) input('id');
        $sesiId = (int) input('sesi_id');
        $this->assertSesiOwned($sesiId);
        // Pastikan rincian milik sesi yang sesuai (cegah delete arbitrary)
        $row = DB::one('SELECT id FROM kka_rincian WHERE id = ? AND sesi_id = ?', [$id, $sesiId]);
        if (!$row) { flash('error', 'Rincian tidak ditemukan.'); redirect('sesi/show?id=' . $sesiId); }
        DB::delete('kka_rincian', ['id' => $id]);
        flash('success', 'Rincian dihapus.');
        redirect('sesi/show?id=' . $sesiId);
    }
}
