<?php $title = 'Buat Master KKA Baru'; ?>
<?php partial('head', compact('title')); ?>
<?php partial('sidebar'); ?>

<main class="main" data-testid="page-master-create">
  <div class="topbar">
    <div class="crumb">
      <i class="fa-solid fa-folder-tree"></i>
      <a href="<?= url('master') ?>" style="color:var(--slate-500)">Master KKA</a> / <b>Buat Baru</b>
    </div>
  </div>

  <div class="content">
    <?php partial('flash'); ?>
    <div class="page-head">
      <div><h2>Buat Dokumen Master KKA</h2><p>Pilih Sesi Audit induk &amp; tipe dokumen yang ingin dibuat.</p></div>
    </div>

    <div class="card" style="max-width:820px">
      <form method="post" action="<?= url('master/store') ?>" data-testid="form-master-create">
        <?= csrf_field() ?>
        <div class="field">
          <label>Sesi Audit Induk <span class="req">*</span></label>
          <select name="sesi_id" required class="select" data-testid="f-sesi">
            <option value="">— Pilih Sesi Audit —</option>
            <?php foreach ($sesiList as $s): ?>
              <option value="<?= (int)$s['id'] ?>" <?= $sesiId==(int)$s['id']?'selected':'' ?>>
                <?= e($s['desa_nama']) ?> · <?= e(mb_strimwidth((string)$s['objek_audit'],0,50,'…')) ?> (S<?= (int)$s['semester'] ?>/<?= (int)$s['tahun_anggaran'] ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="field">
          <label>Tipe Dokumen <span class="req">*</span></label>
          <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:10px" data-testid="f-tipe-group">
            <?php foreach ($tipeLabel as $k => $v):
              $ico = $k==='standar'?'fa-file-lines':($k==='fisik'?'fa-ruler-combined':'fa-camera');
              $col = $k==='standar'?'#2563eb':($k==='fisik'?'#059669':'#d97706');
            ?>
              <label style="border:2px solid <?= $tipe===$k?$col:'var(--slate-200)' ?>;border-radius:10px;padding:14px;cursor:pointer;display:flex;gap:10px;align-items:flex-start;background:<?= $tipe===$k?$col.'0d':'#fff' ?>">
                <input type="radio" name="tipe" value="<?= $k ?>" <?= $tipe===$k?'checked':'' ?> required style="margin-top:3px">
                <div>
                  <div style="display:flex;align-items:center;gap:8px;font-weight:600"><i class="fa-solid <?= $ico ?>" style="color:<?= $col ?>"></i> <?= e($v) ?></div>
                  <small style="color:var(--slate-500);font-size:12px">
                    <?php if ($k==='standar'): ?>Narasi tertulis kondisi lapangan
                    <?php elseif ($k==='fisik'): ?>Tabel pengukuran fisik (STA, Jarak, Lebar, Tebal, Volume)
                    <?php else: ?>Upload foto lapangan (bisa banyak)
                    <?php endif; ?>
                  </small>
                </div>
              </label>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="field">
          <label>Judul Dokumen (opsional)</label>
          <input type="text" name="judul" class="input" placeholder="cth: Pengukuran Rabat Beton Dusun A" data-testid="f-judul">
          <small style="color:var(--slate-500);font-size:12px">Kosongkan untuk memakai nama tipe.</small>
        </div>

        <div class="row">
          <div class="field"><label>No. KKA</label><input type="text" name="no_kka" class="input" placeholder="KKA/..."></div>
          <div class="field"><label>No. Ref PKA</label><input type="text" name="ref_pka" class="input" placeholder="Ref..."></div>
        </div>
        <div class="field">
          <label>Tanggal Dokumen</label>
          <input type="date" name="tanggal_dok" class="input" value="<?= date('Y-m-d') ?>">
        </div>

        <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:14px">
          <a href="<?= url('master') ?>" class="btn btn-outline">Batal</a>
          <button class="btn btn-primary" type="submit" data-testid="btn-simpan-master"><i class="fa-solid fa-save"></i> Lanjut Isi Data</button>
        </div>
      </form>
    </div>
  </div>
</main>

<?php partial('foot'); ?>
