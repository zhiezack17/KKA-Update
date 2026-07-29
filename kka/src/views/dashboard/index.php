<?php $title = 'Dashboard - KKA'; ?>
<?php partial('head', compact('title')); ?>
<?php partial('sidebar'); ?>

<main class="main" data-testid="page-dashboard">
  <div class="topbar">
  <div class="crumb">
    <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Menu">☰</button>
    <i class="fa-solid fa-gauge"></i> <b>Dashboard</b>
  </div>
  <div class="topbar-right">
    <i class="fa-regular fa-calendar"></i> <?= tgl_id(date('Y-m-d')) ?>
  </div>
</div>

  <div class="content">
    <?php partial('flash'); ?>

    <div class="page-head">
      <div>
        <h2>Selamat Datang, <?= e(explode(' ', $auth->user()['nama'])[0]) ?> 👋</h2>
        <p>Ringkasan aktivitas Kertas Kerja Audit Inspektorat Kabupaten Rokan Hilir.</p>
      </div>
      <a href="<?= url('sesi') ?>" class="btn btn-primary" data-testid="dash-go-sesi">
        <i class="fa-solid fa-plus"></i> Buat Sesi Audit
      </a>
    </div>

    <div class="stats-grid">
      <div class="stat" data-testid="stat-sesi">
        <div>
          <div class="label">Total Sesi Audit</div>
          <div class="value"><?= number_format($stats['sesi'], 0, ',', '.') ?></div>
          <div class="sub"><?= $stats['sesi_ty'] ?> sesi di tahun <?= date('Y') ?></div>
        </div>
        <div class="ico"><i class="fa-solid fa-clipboard-list"></i></div>
      </div>
      <div class="stat amber" data-testid="stat-desa">
        <div>
          <div class="label">Desa / Kepenghuluan</div>
          <div class="value"><?= number_format($stats['desa'], 0, ',', '.') ?></div>
          <div class="sub">Tersebar di <?= $stats['kec'] ?> kecamatan</div>
        </div>
        <div class="ico"><i class="fa-solid fa-building-columns"></i></div>
      </div>
      <div class="stat blue" data-testid="stat-anggaran">
        <div>
          <div class="label">Total Pagu Anggaran</div>
          <div class="value money"><?= rupiah($stats['anggaran']) ?></div>
          <div class="sub">Seluruh sesi audit terdata</div>
        </div>
        <div class="ico"><i class="fa-solid fa-money-bill-wave"></i></div>
      </div>
      <div class="stat rose" data-testid="stat-selisih">
        <div>
          <div class="label">Selisih (Kwitansi - Realisasi)</div>
          <div class="value money"><?= rupiah($stats['selisih']) ?></div>
          <div class="sub">Kwitansi: <?= rupiah($stats['dikwitansi']) ?> · Realisasi: <?= rupiah($stats['realisasi']) ?></div>
        </div>
        <div class="ico"><i class="fa-solid fa-scale-balanced"></i></div>
      </div>
    </div>

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:18px">
      <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
          <h3 style="margin:0;font-size:15px;font-weight:800">Desa / Kepenghuluan yang Diaudit</h3>
          <a href="<?= url('sesi') ?>" class="btn btn-ghost btn-sm">Lihat Semua <i class="fa-solid fa-arrow-right"></i></a>
        </div>
        <?php if (empty($perDesa)): ?>
          <div class="empty"><i class="fa-regular fa-folder-open"></i><h4>Belum ada sesi audit</h4><p>Mulai buat sesi audit pertama Anda.</p></div>
        <?php else: ?>
          <div class="list-grid">
            <?php foreach ($perDesa as $d): ?>
              <a href="<?= url('sesi?desa=' . $d['id']) ?>" class="list-card" data-testid="dash-desa-<?= $d['id'] ?>">
                <div class="ico"><i class="fa-solid fa-building-columns"></i></div>
                <div class="main">
                  <div class="title">
                    <?= e($d['desa']) ?>
                    <span class="badge"><?= (int)$d['jumlah'] ?> sesi</span>
                  </div>
                  <div class="meta">
                    <span><i class="fa-solid fa-location-dot"></i> Kec. <?= e($d['kecamatan']) ?></span>
                    <span><i class="fa-solid fa-money-bill-wave"></i> Pagu: <?= rupiah($d['pagu']) ?></span>
                  </div>
                </div>
                <i class="fa-solid fa-chevron-right" style="color:var(--slate-400)"></i>
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="card">
        <h3 style="margin:0 0 14px;font-size:15px;font-weight:800">Sesi per Bidang</h3>
        <div style="display:flex;flex-direction:column;gap:10px">
          <?php
            $jmlArr = array_column($perBidang, 'jumlah');
            $maxJml = $jmlArr ? max(1, max($jmlArr)) : 1;
            foreach ($perBidang as $b):
              $pct = round(((int)$b['jumlah'] / $maxJml) * 100);
          ?>
            <div>
              <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px">
                <span style="color:var(--slate-600);font-weight:600"><?= e(mb_strimwidth($b['nama'],0,46,'…')) ?></span>
                <span style="color:var(--emerald-700);font-weight:700"><?= (int)$b['jumlah'] ?></span>
              </div>
              <div style="height:7px;background:var(--slate-100);border-radius:99px;overflow:hidden">
                <div style="width:<?= $pct ?>%;height:100%;background:linear-gradient(90deg,var(--emerald-600),var(--gold-400));border-radius:99px"></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</main>

<?php partial('foot'); ?>
