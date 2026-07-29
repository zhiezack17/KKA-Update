<?php $title = 'Manajemen Desa - KKA'; ?>
<?php partial('head', compact('title')); ?>
<?php partial('sidebar'); ?>

<main class="main" data-testid="page-desa">
  <div class="topbar">
    <div class="crumb"><i class="fa-solid fa-building-columns"></i> <b>Manajemen Desa</b></div>
    <div class="topbar-right"><?= count($desa) ?> desa ditampilkan</div>
  </div>

  <div class="content">
    <?php partial('flash'); ?>

    <div class="page-head">
      <div>
        <h2>Manajemen Desa / Kepenghuluan</h2>
        <p>Kelola daftar desa beserta kecamatan yang akan diaudit.</p>
      </div>
      <?php if ($auth->isAdmin()): ?>
      <div style="display:flex;gap:8px">
        <button class="btn btn-outline" onclick="document.getElementById('m-kec').style.display='flex'" data-testid="btn-tambah-kec"><i class="fa-solid fa-map"></i> Tambah Kecamatan</button>
        <button class="btn btn-primary" onclick="document.getElementById('m-desa').style.display='flex'" data-testid="btn-tambah-desa"><i class="fa-solid fa-plus"></i> Tambah Desa</button>
      </div>
      <?php endif; ?>
    </div>

    <form method="get" class="filter-bar">
      <input type="text" name="q" value="<?= e($q) ?>" placeholder="🔍 Cari desa..." class="input" data-testid="filter-desa-q">
      <select name="kecamatan" class="select" data-testid="filter-desa-kec">
        <option value="0">— Semua Kecamatan —</option>
        <?php foreach ($kecamatan as $k): ?>
          <option value="<?= $k['id'] ?>" <?= $kec == $k['id'] ? 'selected':'' ?>><?= e($k['nama']) ?></option>
        <?php endforeach; ?>
      </select>
      <button class="btn btn-outline btn-sm" type="submit"><i class="fa-solid fa-filter"></i> Filter</button>
      <?php if ($q || $kec): ?><a href="<?= url('desa') ?>" class="btn btn-ghost btn-sm">Reset</a><?php endif; ?>
    </form>

    <?php if (empty($desa)): ?>
      <div class="empty"><i class="fa-regular fa-folder-open"></i><h4>Tidak ada desa ditemukan</h4></div>
    <?php else: ?>
      <div class="tag-grid">
        <?php foreach ($desa as $d): ?>
          <div class="tag-card" data-testid="desa-item-<?= $d['id'] ?>">
            <div class="ico"><i class="fa-solid fa-building"></i></div>
            <div class="info">
              <div class="nm" title="<?= e($d['nama']) ?>"><?= e($d['nama']) ?></div>
              <div class="kec">Kec. <?= e($d['kecamatan']) ?></div>
            </div>
            <?php if ($auth->isAdmin()): ?>
            <form method="post" action="<?= url('desa/delete') ?>" onsubmit="return confirm('Hapus desa <?= e($d['nama']) ?>?')" style="display:inline">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= $d['id'] ?>">
              <button class="del" type="submit" title="Hapus" data-testid="del-desa-<?= $d['id'] ?>"><i class="fa-solid fa-trash"></i></button>
            </form>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</main>

<?php if ($auth->isAdmin()): ?>
<div class="modal-bg" id="m-desa" style="display:none">
  <div class="modal" onclick="event.stopPropagation()">
    <div class="modal-head">
      <h3>Tambah Desa</h3>
      <button class="x" onclick="document.getElementById('m-desa').style.display='none'">×</button>
    </div>
    <form method="post" action="<?= url('desa/store') ?>" data-testid="form-add-desa">
      <?= csrf_field() ?>
      <div class="modal-body">
        <div class="field">
          <label>Kecamatan <span class="req">*</span></label>
          <select name="kecamatan_id" required class="select">
            <option value="">— Pilih Kecamatan —</option>
            <?php foreach ($kecamatan as $k): ?>
              <option value="<?= $k['id'] ?>"><?= e($k['nama']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field">
          <label>Nama Desa / Kepenghuluan <span class="req">*</span></label>
          <input type="text" name="nama" required class="input" placeholder="cth: Salak">
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-outline" onclick="document.getElementById('m-desa').style.display='none'">Batal</button>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Simpan</button>
      </div>
    </form>
  </div>
</div>

<div class="modal-bg" id="m-kec" style="display:none">
  <div class="modal" onclick="event.stopPropagation()">
    <div class="modal-head">
      <h3>Tambah Kecamatan</h3>
      <button class="x" onclick="document.getElementById('m-kec').style.display='none'">×</button>
    </div>
    <form method="post" action="<?= url('kecamatan/store') ?>">
      <?= csrf_field() ?>
      <div class="modal-body">
        <div class="field">
          <label>Nama Kecamatan <span class="req">*</span></label>
          <input type="text" name="nama" required class="input" placeholder="cth: Bangko">
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-outline" onclick="document.getElementById('m-kec').style.display='none'">Batal</button>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Simpan</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<?php partial('foot'); ?>
