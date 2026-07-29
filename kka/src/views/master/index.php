<?php $title = 'Master KKA - KKA Inspektorat'; ?>
<?php partial('head', compact('title')); ?>
<?php partial('sidebar'); ?>

<main class="main" data-testid="page-master">
  <div class="topbar">
    <div class="crumb"><i class="fa-solid fa-folder-tree"></i> <b>Master KKA</b></div>
    <div style="display:flex;gap:8px">
      <a href="<?= url('master/download-template') ?>" class="btn btn-outline btn-sm" data-testid="btn-download-template">
        <i class="fa-solid fa-file-arrow-down"></i> Download Template .xls
      </a>
      <a href="<?= url('master/create') ?>" class="btn btn-primary btn-sm" data-testid="btn-master-new">
        <i class="fa-solid fa-plus"></i> Buat Dokumen Baru
      </a>
    </div>
  </div>

  <div class="content">
    <?php partial('flash'); ?>

    <div class="page-head">
      <div>
        <h2>Master KKA</h2>
        <p>Dokumen tambahan untuk melengkapi Sesi Audit &mdash; ada 3 tipe berdasarkan template KKP Master:</p>
      </div>
    </div>

    <!-- Info Card 3 tipe -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:14px;margin-bottom:18px">
      <div class="card" style="border-left:4px solid #2563eb;padding:14px 18px">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px"><i class="fa-solid fa-file-lines" style="font-size:22px;color:#2563eb"></i><b>KKP Standar (Narasi)</b></div>
        <p style="font-size:13px;color:var(--slate-600);margin:0">Menceritakan kondisi lapangan saat audit dalam bentuk narasi tertulis.</p>
      </div>
      <div class="card" style="border-left:4px solid #059669;padding:14px 18px">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px"><i class="fa-solid fa-ruler-combined" style="font-size:22px;color:#059669"></i><b>KKA Fisik (Pengukuran)</b></div>
        <p style="font-size:13px;color:var(--slate-600);margin:0">Tabel pengukuran fisik (STA, Jarak, Lebar I &amp; II, Tebal, Volume) untuk audit jalan / drainase.</p>
      </div>
      <div class="card" style="border-left:4px solid #d97706;padding:14px 18px">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px"><i class="fa-solid fa-camera" style="font-size:22px;color:#d97706"></i><b>Sketsa / Foto Lapangan</b></div>
        <p style="font-size:13px;color:var(--slate-600);margin:0">Upload foto lokasi &amp; sketsa lapangan sebagai pengganti gambar manual.</p>
      </div>
    </div>

    <!-- Filter bar -->
    <form method="get" class="filter-bar">
      <select name="tipe" class="select" data-testid="f-tipe">
        <option value="">— Semua Tipe —</option>
        <?php foreach ($tipeLabel as $k => $v): ?>
          <option value="<?= $k ?>" <?= $tipe===$k?'selected':'' ?>><?= e($v) ?></option>
        <?php endforeach; ?>
      </select>
      <select name="sesi" class="select" data-testid="f-sesi">
        <option value="0">— Semua Sesi Audit —</option>
        <?php foreach ($sesiList as $s): ?>
          <option value="<?= (int)$s['id'] ?>" <?= $sesiId==(int)$s['id']?'selected':'' ?>>
            <?= e($s['desa_nama']) ?> · <?= e(mb_strimwidth((string)$s['objek_audit'],0,40,'…')) ?> (S<?= (int)$s['semester'] ?>/<?= (int)$s['tahun_anggaran'] ?>)
          </option>
        <?php endforeach; ?>
      </select>
      <button class="btn btn-outline btn-sm" type="submit"><i class="fa-solid fa-filter"></i> Filter</button>
      <?php if ($tipe || $sesiId): ?><a href="<?= url('master') ?>" class="btn btn-ghost btn-sm">Reset</a><?php endif; ?>
    </form>

    <!-- List -->
    <?php if (empty($rows)): ?>
      <div class="empty" style="padding:40px 20px">
        <i class="fa-solid fa-folder-open"></i>
        <h4>Belum ada dokumen Master KKA</h4>
        <p>Klik tombol <b>Buat Dokumen Baru</b> untuk memulai.</p>
      </div>
    <?php else: ?>
      <div class="table-wrap">
        <table class="table" data-testid="table-master">
          <thead>
            <tr>
              <th style="width:36px">No</th>
              <th style="width:150px">Tipe</th>
              <th>Judul / Sesi Audit</th>
              <th>Desa / Kepenghuluan</th>
              <th>Tahun</th>
              <th>Dibuat</th>
              <th style="width:180px">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php $no=1; foreach ($rows as $r): ?>
              <tr data-testid="row-master-<?= (int)$r['id'] ?>">
                <td><?= $no++ ?></td>
                <td>
                  <?php $col = $r['tipe']==='standar'?'#2563eb':($r['tipe']==='fisik'?'#059669':'#d97706'); ?>
                  <span style="background:<?= $col ?>1a;color:<?= $col ?>;padding:3px 10px;border-radius:999px;font-size:12px;font-weight:600">
                    <?= e($tipeLabel[$r['tipe']] ?? $r['tipe']) ?>
                  </span>
                </td>
                <td style="font-weight:600"><?= e($r['judul']) ?><br><small style="color:var(--slate-500);font-weight:400"><?= e(mb_strimwidth((string)$r['objek_audit'],0,60,'…')) ?></small></td>
                <td><?= e($r['desa_nama']) ?> · <?= e($r['kecamatan_nama']) ?></td>
                <td>S<?= (int)$r['semester'] ?>/<?= (int)$r['tahun_anggaran'] ?></td>
                <td><?= tgl_id($r['created_at']) ?></td>
                <td style="white-space:nowrap">
                  <a class="btn btn-ghost btn-sm" style="color:#2563eb" title="Preview" href="<?= url('master/preview?id='.(int)$r['id']) ?>" target="_blank" data-testid="btn-preview-<?= (int)$r['id'] ?>"><i class="fa-solid fa-eye"></i></a>
                  <a class="btn btn-ghost btn-sm" style="color:var(--emerald-700)" title="Edit" href="<?= url('master/edit?id='.(int)$r['id']) ?>" data-testid="btn-edit-<?= (int)$r['id'] ?>"><i class="fa-solid fa-pen-to-square"></i></a>
                  <a class="btn btn-ghost btn-sm" style="color:#059669" title="Export Excel" href="<?= url('master/export?id='.(int)$r['id']) ?>" data-testid="btn-export-<?= (int)$r['id'] ?>"><i class="fa-solid fa-file-excel"></i></a>
                  <form method="post" action="<?= url('master/delete') ?>" onsubmit="return confirm('Hapus dokumen ini?')" style="display:inline">
                    <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                    <button class="btn btn-ghost btn-sm" style="color:var(--red-600)" title="Hapus" data-testid="btn-del-<?= (int)$r['id'] ?>"><i class="fa-solid fa-trash"></i></button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</main>

<?php partial('foot'); ?>
