<?php $title = 'Detail Sesi - ' . $sesi['objek_audit'] . ' - KKA'; ?>
<?php partial('head', compact('title')); ?>
<?php partial('sidebar'); ?>

<main class="main" data-testid="page-sesi-show">
  <div class="topbar">
    <div class="crumb">
      <i class="fa-solid fa-clipboard-list"></i>
      <a href="<?= url('sesi') ?>" style="color:var(--slate-500)">Sesi Audit</a> /
      <b><?= e(mb_strimwidth($sesi['objek_audit'],0,42,'…')) ?></b>
    </div>
    <div style="display:flex;gap:8px">
      <a href="<?= url('print/sesi?id='.$sesi['id']) ?>" class="btn btn-outline btn-sm" target="_blank" data-testid="btn-print"><i class="fa-solid fa-print"></i> Cetak / Preview</a>
      <a href="<?= url('export/sesi?id='.$sesi['id']) ?>" class="btn btn-accent btn-sm" data-testid="btn-export"><i class="fa-solid fa-file-excel"></i> Export Excel</a>
    </div>
  </div>

  <div class="content">
    <?php partial('flash'); ?>

    <div class="page-head">
      <div>
        <h2><?= e($sesi['objek_audit']) ?></h2>
        <p><?= e($sesi['desa_nama']) ?> · Kec. <?= e($sesi['kecamatan_nama']) ?> · Semester <?= (int)$sesi['semester'] ?> / <?= (int)$sesi['tahun_anggaran'] ?></p>
      </div>
      <a href="<?= url('sesi/edit?id='.$sesi['id']) ?>" class="btn btn-outline" data-testid="btn-edit-sesi"><i class="fa-solid fa-pen"></i> Edit Identitas</a>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:18px">
      <div class="card">
        <h3 style="margin:0 0 12px;font-size:14px;font-weight:800;color:var(--slate-600);text-transform:uppercase;letter-spacing:1px">Identitas KKA</h3>
        <dl class="kv">
          <dt>No. KKA</dt>     <dd><?= e($sesi['no_kka'] ?: '-') ?></dd>
          <dt>Ref. KKA</dt>    <dd><?= e($sesi['ref_kka'] ?: '-') ?></dd>
          <dt>Bidang</dt>      <dd><?= e($sesi['bidang_nama']) ?></dd>
          <dt>Sub Bidang</dt>  <dd><?= e($sesi['sub_bidang_nama'] ?: '-') ?></dd>
          <dt>Kegiatan</dt>    <dd><?= e($sesi['kegiatan'] ?: '-') ?></dd>
          <dt>Pagu Anggaran</dt><dd style="font-weight:700;color:var(--emerald-700)" data-testid="sesi-pagu"><?= rupiah($sesi['pagu_anggaran']) ?></dd>
        </dl>
      </div>
      <div class="card">
        <h3 style="margin:0 0 12px;font-size:14px;font-weight:800;color:var(--slate-600);text-transform:uppercase;letter-spacing:1px">Tanda Tangan</h3>
        <dl class="kv">
          <dt>Dibuat oleh</dt> <dd><?= e($sesi['dibuat_oleh'] ?: '-') ?></dd>
          <dt>Tanggal Dibuat</dt> <dd><?= tgl_id($sesi['tanggal_dibuat']) ?></dd>
          <dt>Direview oleh (Ketua Tim)</dt> <dd><?= e($sesi['direview_oleh'] ?: '-') ?></dd>
          <dt>Tanggal Review</dt> <dd><?= tgl_id($sesi['tanggal_review']) ?></dd>
          <dt>Diketahui oleh (Dalnis)</dt> <dd><?= e($sesi['dievaluasi_oleh'] ?: '-') ?></dd>
          <dt>Tanggal Diketahui</dt> <dd><?= tgl_id($sesi['tanggal_evaluasi']) ?></dd>
        </dl>
      </div>
    </div>

    <?php if (!empty($sharedWith)): ?>
    <div class="card" style="margin-bottom:18px">
      <h3 style="margin:0 0 10px;font-size:14px;font-weight:800;color:var(--slate-600);text-transform:uppercase;letter-spacing:1px"><i class="fa-solid fa-users"></i> Dibagikan Kepada</h3>
      <div style="display:flex;flex-wrap:wrap;gap:8px">
        <?php foreach ($sharedWith as $sw): ?>
          <span style="background:#f1f5f9;border:1px solid var(--slate-200);padding:4px 12px;border-radius:999px;font-size:13px;font-weight:500"><i class="fa-solid fa-user" style="color:var(--slate-400)"></i> <?= e($sw['nama']) ?><?php if (!empty($sw['jabatan'])): ?> <small style="color:var(--slate-500)">— <?= e($sw['jabatan']) ?></small><?php endif; ?></span>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- RINCIAN -->
    <div class="section-title"><i class="fa-solid fa-list"></i> Rincian Belanja</div>
    <div class="card" style="padding:0">
      <div class="table-wrap" style="border:0">
        <table class="table">
          <thead>
            <tr>
              <th style="width:36px">No</th>
              <th>Uraian / Rincian Belanja</th>
              <th class="num">Biaya Dikwitansi</th>
              <th class="num">Realisasi</th>
              <th class="num">Selisih</th>
              <th>Penerima</th>
              <th>Keterangan</th>
              <th style="width:78px">Aksi</th>
            </tr>
          </thead>
          <tbody data-testid="tbody-rincian">
            <?php if (empty($rincian)): ?>
              <tr><td colspan="8" style="text-align:center;color:var(--slate-500);padding:36px">Belum ada rincian. Tambahkan di formulir bawah.</td></tr>
            <?php else: $no=1; foreach ($rincian as $r): $sel = (float)$r['biaya_dikwitansi'] - (float)$r['realisasi']; ?>
              <tr data-testid="rincian-row-<?= $r['id'] ?>"
                  data-rincian-id="<?= (int)$r['id'] ?>"
                  data-uraian="<?= e($r['uraian']) ?>"
                  data-kwi="<?= (float)$r['biaya_dikwitansi'] ?>"
                  data-real="<?= (float)$r['realisasi'] ?>"
                  data-penerima="<?= e((string)$r['penerima']) ?>"
                  data-keterangan="<?= e((string)$r['keterangan']) ?>">
                <td><?= $no++ ?></td>
                <td><?= e($r['uraian']) ?></td>
                <td class="num"><?= rupiah($r['biaya_dikwitansi']) ?></td>
                <td class="num"><?= rupiah($r['realisasi']) ?></td>
                <td class="num" style="color:<?= $sel>0?'var(--red-600)':'var(--emerald-700)' ?>;font-weight:700"><?= rupiah($sel) ?></td>
                <td><?= e($r['penerima'] ?: '-') ?></td>
                <td><?= e($r['keterangan'] ?: '-') ?></td>
                <td style="white-space:nowrap">
                  <button type="button" class="btn btn-ghost btn-sm js-edit-rincian" style="color:var(--emerald-700);padding:5px 8px" title="Edit" data-testid="edit-rincian-<?= $r['id'] ?>"><i class="fa-solid fa-pen-to-square"></i></button>
                  <form method="post" action="<?= url('rincian/delete') ?>" onsubmit="return confirm('Hapus rincian ini?')" style="display:inline">
                    <?= csrf_field() ?><input type="hidden" name="id" value="<?= $r['id'] ?>"><input type="hidden" name="sesi_id" value="<?= $sesi['id'] ?>">
                    <button class="btn btn-ghost btn-sm" style="color:var(--red-600);padding:5px 8px" title="Hapus" data-testid="del-rincian-<?= $r['id'] ?>"><i class="fa-solid fa-trash"></i></button>
                  </form>
                </td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
          <?php if (!empty($rincian)): ?>
          <tfoot>
            <tr>
              <td colspan="2">JUMLAH <span style="font-weight:500;color:var(--slate-500);font-size:12px">(Pagu: <?= rupiah($sesi['pagu_anggaran']) ?>)</span></td>
              <td class="num"><?= rupiah($totals['dikwitansi']) ?></td>
              <td class="num"><?= rupiah($totals['realisasi']) ?></td>
              <td class="num" style="color:<?= $totals['selisih']>0?'var(--red-600)':'var(--emerald-700)' ?>"><?= rupiah($totals['selisih']) ?></td>
              <td colspan="3"></td>
            </tr>
          </tfoot>
          <?php endif; ?>
        </table>
      </div>

      <!-- Form tambah rincian -->
      <form method="post" action="<?= url('rincian/store') ?>" style="padding:16px 18px;border-top:1px solid var(--slate-200);background:var(--slate-50)" data-testid="form-rincian">
        <?= csrf_field() ?>
        <input type="hidden" name="sesi_id" value="<?= $sesi['id'] ?>">
        <div style="display:grid;grid-template-columns:2fr 1fr 1fr 1.2fr 1.2fr auto;gap:8px;align-items:end">
          <div class="field" style="margin:0"><label>Uraian Belanja <span class="req">*</span></label><input type="text" name="uraian" required class="input" placeholder="cth: Pembayaran honor" data-testid="r-uraian"></div>
          <div class="field" style="margin:0"><label>Dikwitansi</label><input type="text" name="biaya_dikwitansi" class="input" data-money placeholder="0"></div>
          <div class="field" style="margin:0"><label>Realisasi</label><input type="text" name="realisasi" class="input" data-money placeholder="0" data-testid="r-realisasi"></div>
          <div class="field" style="margin:0"><label>Penerima</label><input type="text" name="penerima" class="input" placeholder="Nama penerima"></div>
          <div class="field" style="margin:0"><label>Keterangan</label><input type="text" name="keterangan" class="input" placeholder="-"></div>
          <button class="btn btn-primary" type="submit" data-testid="btn-tambah-rincian"><i class="fa-solid fa-plus"></i> Tambah</button>
        </div>
      </form>
    </div>

    <!-- Kesimpulan & Sumber Data -->
    <div class="section-title"><i class="fa-solid fa-pen-to-square"></i> Kesimpulan & Sumber Data</div>
    <div class="card">
      <form method="post" action="<?= url('sesi/update') ?>" data-testid="form-kesimpulan">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= $sesi['id'] ?>">
        <input type="hidden" name="desa_id" value="<?= $sesi['desa_id'] ?>">
        <input type="hidden" name="bidang_id" value="<?= $sesi['bidang_id'] ?>">
        <input type="hidden" name="sub_bidang_id" value="<?= e((string)$sesi['sub_bidang_id']) ?>">
        <input type="hidden" name="objek_audit" value="<?= e($sesi['objek_audit']) ?>">
        <input type="hidden" name="kegiatan" value="<?= e((string)$sesi['kegiatan']) ?>">
        <input type="hidden" name="pagu_anggaran" value="<?= number_format((float)$sesi['pagu_anggaran'],0,',','.') ?>">
        <input type="hidden" name="semester" value="<?= (int)$sesi['semester'] ?>">
        <input type="hidden" name="tahun_anggaran" value="<?= (int)$sesi['tahun_anggaran'] ?>">
        <input type="hidden" name="no_kka" value="<?= e((string)$sesi['no_kka']) ?>">
        <input type="hidden" name="ref_kka" value="<?= e((string)$sesi['ref_kka']) ?>">
        <input type="hidden" name="dibuat_oleh" value="<?= e((string)$sesi['dibuat_oleh']) ?>">
        <input type="hidden" name="tanggal_dibuat" value="<?= e((string)$sesi['tanggal_dibuat']) ?>">
        <input type="hidden" name="direview_oleh" value="<?= e((string)$sesi['direview_oleh']) ?>">
        <input type="hidden" name="tanggal_review" value="<?= e((string)$sesi['tanggal_review']) ?>">
        <input type="hidden" name="dievaluasi_oleh" value="<?= e((string)$sesi['dievaluasi_oleh']) ?>">
        <input type="hidden" name="tanggal_evaluasi" value="<?= e((string)$sesi['tanggal_evaluasi']) ?>">
        <div class="row">
          <div class="field">
            <label>Kesimpulan Audit</label>
            <textarea name="kesimpulan" class="textarea" placeholder="Tuliskan kesimpulan audit..." data-testid="f-kesimpulan"><?= e((string)$sesi['kesimpulan']) ?></textarea>
          </div>
          <div class="field">
            <label>Sumber Data</label>
            <textarea name="sumber_data" class="textarea" placeholder="cth: SPP, kwitansi, daftar hadir..."><?= e((string)$sesi['sumber_data']) ?></textarea>
          </div>
        </div>
        <div style="text-align:right"><button class="btn btn-primary" type="submit"><i class="fa-solid fa-save"></i> Simpan Kesimpulan</button></div>
      </form>
    </div>

    <!-- Lampiran -->
    <div class="section-title"><i class="fa-solid fa-paperclip"></i> Lampiran (PDF / Excel / Gambar)</div>
    <div class="card">
      <form method="post" enctype="multipart/form-data" action="<?= url('lampiran/upload') ?>" style="display:flex;gap:8px;align-items:end;margin-bottom:14px" data-testid="form-lampiran">
        <?= csrf_field() ?>
        <input type="hidden" name="sesi_id" value="<?= $sesi['id'] ?>">
        <div class="field" style="flex:1;margin:0"><label>Pilih File (maks 10 MB)</label><input type="file" name="file" required class="input" accept=".pdf,.xls,.xlsx,.jpg,.jpeg,.png,.webp,.gif" data-testid="lamp-file"></div>
        <div class="field" style="flex:1;margin:0"><label>Keterangan</label><input type="text" name="keterangan" class="input" placeholder="cth: Kwitansi honor januari"></div>
        <button class="btn btn-primary" type="submit"><i class="fa-solid fa-upload"></i> Upload</button>
      </form>
      <?php if (empty($lampiran)): ?>
        <div style="text-align:center;color:var(--slate-500);padding:18px 0;font-size:13px">Belum ada lampiran.</div>
      <?php else: ?>
        <div class="list-grid">
          <?php foreach ($lampiran as $l): ?>
            <div class="list-card">
              <div class="ico"><i class="fa-solid <?= str_contains((string)$l['mime_type'],'pdf')?'fa-file-pdf':(str_contains((string)$l['mime_type'],'image')?'fa-file-image':'fa-file-excel') ?>"></i></div>
              <div class="main">
                <div class="title"><?= e($l['nama_asli']) ?></div>
                <div class="meta">
                  <span><i class="fa-regular fa-calendar"></i> <?= tgl_id($l['created_at']) ?></span>
                  <span><i class="fa-solid fa-weight-hanging"></i> <?= number_format($l['ukuran']/1024, 1) ?> KB</span>
                  <?php if ($l['keterangan']): ?><span><i class="fa-solid fa-comment"></i> <?= e($l['keterangan']) ?></span><?php endif; ?>
                </div>
              </div>
              <div class="actions">
                <a class="btn btn-outline btn-sm" href="<?= url('lampiran/download?id='.$l['id']) ?>" target="_blank"><i class="fa-solid fa-download"></i></a>
                <form method="post" action="<?= url('lampiran/delete') ?>" onsubmit="return confirm('Hapus lampiran ini?')" style="display:inline">
                  <?= csrf_field() ?><input type="hidden" name="id" value="<?= $l['id'] ?>"><input type="hidden" name="sesi_id" value="<?= $sesi['id'] ?>">
                  <button class="btn btn-outline btn-sm" style="color:var(--red-600);border-color:#fecaca"><i class="fa-solid fa-trash"></i></button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</main>

<!-- Modal Edit Rincian -->
<div id="modalEditRincian" class="kka-modal" role="dialog" aria-modal="true" aria-labelledby="modalEditTitle" hidden>
  <div class="kka-modal__backdrop" data-close-modal></div>
  <div class="kka-modal__box">
    <div class="kka-modal__head">
      <h3 id="modalEditTitle"><i class="fa-solid fa-pen-to-square"></i> Edit Rincian Belanja</h3>
      <button type="button" class="kka-modal__x" data-close-modal aria-label="Tutup"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form method="post" action="<?= url('rincian/update') ?>" id="formEditRincian">
      <?= csrf_field() ?>
      <input type="hidden" name="id" id="er-id">
      <input type="hidden" name="sesi_id" value="<?= (int)$sesi['id'] ?>">
      <div class="kka-modal__body">
        <div class="field">
          <label>Uraian Belanja <span class="req">*</span></label>
          <input type="text" name="uraian" id="er-uraian" required class="input" data-testid="er-uraian">
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
          <div class="field">
            <label>Biaya Dikwitansi (Rp)</label>
            <input type="text" name="biaya_dikwitansi" id="er-kwi" class="input" data-money placeholder="0" data-testid="er-kwi">
          </div>
          <div class="field">
            <label>Realisasi (Rp)</label>
            <input type="text" name="realisasi" id="er-real" class="input" data-money placeholder="0" data-testid="er-real">
          </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
          <div class="field">
            <label>Penerima</label>
            <input type="text" name="penerima" id="er-penerima" class="input" placeholder="Nama penerima">
          </div>
          <div class="field">
            <label>Keterangan</label>
            <input type="text" name="keterangan" id="er-keterangan" class="input" placeholder="-">
          </div>
        </div>
      </div>
      <div class="kka-modal__foot">
        <button type="button" class="btn btn-ghost" data-close-modal>Batal</button>
        <button type="submit" class="btn btn-primary" data-testid="er-save"><i class="fa-solid fa-check"></i> Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>

<style>
.kka-modal{position:fixed;inset:0;z-index:9999;display:flex;align-items:center;justify-content:center;padding:16px}
.kka-modal[hidden]{display:none}
.kka-modal__backdrop{position:absolute;inset:0;background:rgba(15,23,42,.55);backdrop-filter:blur(3px)}
.kka-modal__box{position:relative;background:#fff;border-radius:14px;box-shadow:0 30px 60px -20px rgba(0,0,0,.4);width:100%;max-width:640px;max-height:90vh;overflow:hidden;display:flex;flex-direction:column}
.kka-modal__head{display:flex;align-items:center;justify-content:space-between;padding:16px 22px;border-bottom:1px solid #e2e8f0;background:linear-gradient(90deg,#f0fdf4,#ecfeff)}
.kka-modal__head h3{margin:0;font-size:16px;color:var(--emerald-700);font-weight:700}
.kka-modal__x{background:transparent;border:0;cursor:pointer;font-size:18px;color:#64748b;padding:6px}
.kka-modal__x:hover{color:#0f172a}
.kka-modal__body{padding:20px 22px;overflow-y:auto}
.kka-modal__foot{display:flex;justify-content:flex-end;gap:8px;padding:14px 22px;border-top:1px solid #e2e8f0;background:#f8fafc}
</style>

<script>
(function(){
  var modal = document.getElementById('modalEditRincian');
  if (!modal) return;
  function openModal(){ modal.hidden = false; document.body.style.overflow='hidden'; }
  function closeModal(){ modal.hidden = true; document.body.style.overflow=''; }

  // Format angka jadi format ID (titik ribuan)
  function fmt(v){ v = Math.round(Number(v)||0); return v.toLocaleString('id-ID'); }

  document.querySelectorAll('.js-edit-rincian').forEach(function(btn){
    btn.addEventListener('click', function(){
      var tr = btn.closest('tr');
      if (!tr) return;
      document.getElementById('er-id').value        = tr.dataset.rincianId || '';
      document.getElementById('er-uraian').value    = tr.dataset.uraian || '';
      document.getElementById('er-kwi').value       = fmt(tr.dataset.kwi);
      document.getElementById('er-real').value      = fmt(tr.dataset.real);
      document.getElementById('er-penerima').value  = tr.dataset.penerima || '';
      document.getElementById('er-keterangan').value= tr.dataset.keterangan || '';
      openModal();
      setTimeout(function(){ document.getElementById('er-uraian').focus(); }, 50);
    });
  });

  modal.querySelectorAll('[data-close-modal]').forEach(function(el){
    el.addEventListener('click', closeModal);
  });
  document.addEventListener('keydown', function(e){
    if (e.key === 'Escape' && !modal.hidden) closeModal();
  });
})();
</script>

<?php partial('foot'); ?>
