<?php $title = 'Sesi Audit - KKA'; ?>
<?php partial('head', compact('title')); ?>
<?php partial('sidebar'); ?>

<main class="main" data-testid="page-sesi">
  <div class="topbar">
    <div class="crumb"><i class="fa-solid fa-clipboard-list"></i> <b>Sesi Audit KKA</b></div>
    <div class="topbar-right"><?= count($sesi) ?> sesi</div>
  </div>

  <div class="content">
    <?php partial('flash'); ?>

    <div class="page-head">
      <div>
        <h2>Sesi Audit KKA</h2>
        <p>Dikelompokkan per Bidang. Klik salah satu bidang untuk melihat sub bidang &amp; sesinya.</p>
      </div>
      <a href="<?= url('sesi/create') ?>" class="btn btn-primary" data-testid="btn-sesi-baru">
        <i class="fa-solid fa-plus"></i> Sesi Baru
      </a>
    </div>

    <form method="get" class="filter-bar">
      <input type="text" name="q" value="<?= e($q) ?>" placeholder="🔍 Cari objek audit / no. KKA..." class="input" data-testid="filter-sesi-q">
      <select name="desa" class="select" data-testid="filter-sesi-desa">
        <option value="0">— Semua Desa —</option>
        <?php foreach ($desa as $d): ?><option value="<?= $d['id'] ?>" <?= $desaId==$d['id']?'selected':'' ?>><?= e($d['nama']) ?></option><?php endforeach; ?>
      </select>
      <select name="bidang" class="select">
        <option value="0">— Semua Bidang —</option>
        <?php foreach ($bidang as $b): ?><option value="<?= $b['id'] ?>" <?= $bidId==$b['id']?'selected':'' ?>><?= e(mb_strimwidth($b['nama'],0,42,'…')) ?></option><?php endforeach; ?>
      </select>
      <select name="tahun" class="select">
        <option value="0">— Semua Tahun —</option>
        <?php foreach ($tahuns as $t): ?><option value="<?= $t['t'] ?>" <?= $tahun==$t['t']?'selected':'' ?>><?= $t['t'] ?></option><?php endforeach; ?>
      </select>
      <button class="btn btn-outline btn-sm" type="submit"><i class="fa-solid fa-filter"></i> Filter</button>
      <?php if ($q||$tahun||$desaId||$bidId): ?><a href="<?= url('sesi') ?>" class="btn btn-ghost btn-sm">Reset</a><?php endif; ?>
    </form>

    <style>
      .bidang-akordeon{display:flex;flex-direction:column;gap:10px;margin-top:4px}
      .bidang-group{background:#fff;border:1px solid var(--slate-200,#e2e8f0);border-radius:12px;overflow:hidden}
      .bidang-group>summary{list-style:none;cursor:pointer;display:flex;align-items:center;justify-content:space-between;gap:10px;padding:14px 16px;font-weight:800;color:var(--slate-700,#334155)}
      .bidang-group>summary::-webkit-details-marker{display:none}
      .bidang-group>summary:hover{background:var(--slate-50,#f8fafc)}
      .bidang-group>summary .bidang-name{display:flex;align-items:center;gap:9px}
      .bidang-group>summary .chev{transition:transform .15s ease;color:var(--slate-400,#94a3b8)}
      .bidang-group[open]>summary .chev{transform:rotate(90deg)}
      .bidang-group[open]>summary{border-bottom:1px solid var(--slate-100,#f1f5f9)}
      .bidang-body{padding:8px 14px 14px}
      .bidang-empty{padding:12px 4px;color:var(--slate-400,#94a3b8);font-size:13px}
      .sub-title{display:flex;align-items:center;gap:8px;font-size:12px;font-weight:800;color:var(--emerald-700,#047857);text-transform:uppercase;letter-spacing:.03em;margin:12px 2px 6px}
      .badge-count{background:var(--emerald-50,#ecfdf5);color:var(--emerald-700,#047857);border:1px solid var(--emerald-200,#a7f3d0);font-weight:800;font-size:12px;padding:2px 10px;border-radius:99px;white-space:nowrap}
    </style>

    <?php
      // Kelompokkan sesi (hasil query, sudah terfilter kepemilikan) menurut bidang.
      $byBidang = [];
      foreach ($sesi as $s) { $byBidang[(int)$s['bidang_id']][] = $s; }
      // Buka otomatis bila sedang mencari/memfilter, supaya hasilnya langsung terlihat.
      $autoOpen = ($q !== '' || $tahun || $desaId || $bidId);
    ?>

    <?php if (empty($bidang)): ?>
      <div class="empty">
        <i class="fa-regular fa-folder-open"></i>
        <h4>Belum ada data bidang</h4>
      </div>
    <?php else: ?>
      <div class="bidang-akordeon">
        <?php $i = 1; foreach ($bidang as $b):
          $items = $byBidang[(int)$b['id']] ?? [];
          $open  = ($autoOpen && $items) ? 'open' : '';
        ?>
          <details class="bidang-group" <?= $open ?> data-testid="bidang-group-<?= $b['id'] ?>">
            <summary>
              <span class="bidang-name">
                <i class="fa-solid fa-chevron-right chev"></i>
                <?= $i++ ?>. <?= e($b['nama']) ?>
              </span>
              <span class="badge-count"><?= count($items) ?> sesi</span>
            </summary>
            <div class="bidang-body">
              <?php if (empty($items)): ?>
                <div class="bidang-empty"><i class="fa-regular fa-circle"></i> Belum ada sesi audit pada bidang ini.</div>
              <?php else:
                // Kelompokkan lagi per sub bidang di dalam bidang.
                $bySub = [];
                foreach ($items as $it) { $key = $it['sub_bidang'] ?: '__none__'; $bySub[$key][] = $it; }
                // Sub bidang yang punya nama tampil dulu, yang tanpa sub bidang di akhir.
                uksort($bySub, fn($a,$c)=> $a==='__none__' ? 1 : ($c==='__none__' ? -1 : strcmp($a,$c)));
              ?>
                <?php foreach ($bySub as $subName => $subItems): ?>
                  <?php if ($subName !== '__none__'): ?>
                    <div class="sub-title"><i class="fa-solid fa-folder-tree"></i> <?= e($subName) ?></div>
                  <?php endif; ?>
                  <div class="list-grid">
                    <?php foreach ($subItems as $s): ?>
                      <div class="list-card" data-testid="sesi-item-<?= $s['id'] ?>">
                        <div class="ico"><i class="fa-solid fa-file-lines"></i></div>
                        <div class="main" onclick="window.location='<?= url('sesi/show?id='.$s['id']) ?>'" style="cursor:pointer">
                          <div class="title">
                            <?= e($s['objek_audit']) ?>
                            <span class="badge"><?= e($s['desa']) ?></span>
                          </div>
                          <div class="meta">
                            <span><i class="fa-regular fa-calendar"></i> Semester <?= (int)$s['semester'] ?> / <?= (int)$s['tahun_anggaran'] ?></span>
                            <span><i class="fa-solid fa-hashtag"></i> No. KKA: <?= e($s['no_kka'] ?: '-') ?></span>
                            <span><i class="fa-solid fa-user-tie"></i> <?= e($s['dibuat_oleh'] ?: '-') ?></span>
                          </div>
                        </div>
                        <div class="actions">
                          <a class="btn btn-outline btn-sm" href="<?= url('sesi/show?id='.$s['id']) ?>" title="Detail"><i class="fa-solid fa-eye"></i></a>
                          <form method="post" action="<?= url('sesi/delete') ?>" onsubmit="return confirm('Hapus sesi audit ini? Semua rincian &amp; lampiran akan ikut terhapus.')" style="display:inline">
                            <?= csrf_field() ?><input type="hidden" name="id" value="<?= $s['id'] ?>">
                            <button class="btn btn-outline btn-sm" style="color:var(--red-600);border-color:#fecaca" title="Hapus" data-testid="del-sesi-<?= $s['id'] ?>"><i class="fa-solid fa-trash"></i></button>
                          </form>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </details>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</main>

<?php partial('foot'); ?>
