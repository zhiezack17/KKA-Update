<?php $title = 'Rekap per Desa - KKA'; ?>
<?php partial('head', compact('title')); ?>
<?php partial('sidebar'); ?>

<main class="main" data-testid="page-rekap">
  <div class="topbar">
    <div class="crumb"><i class="fa-solid fa-chart-column"></i> <b>Rekap per Desa</b></div>
    <a href="<?= url('export/rekap?' . http_build_query(['tahun'=>$tahun,'desa'=>$desaId])) ?>" class="btn btn-accent btn-sm"><i class="fa-solid fa-file-excel"></i> Export Excel</a>
  </div>

  <div class="content">
    <?php partial('flash'); ?>
    <div class="page-head">
      <div>
        <h2>Rekap Pengeluaran Desa</h2>
        <p>Ringkasan pagu anggaran dan realisasi per desa (gabungan semua tahun). Pakai filter Tahun untuk melihat satu tahun tertentu.</p>
      </div>
    </div>

    <form method="get" class="filter-bar">
      <select name="desa" class="select">
        <option value="0">— Semua Desa —</option>
        <?php foreach ($desa as $d): ?><option value="<?= $d['id'] ?>" <?= $desaId==$d['id']?'selected':'' ?>><?= e($d['nama']) ?></option><?php endforeach; ?>
      </select>
      <select name="tahun" class="select">
        <option value="0">— Semua Tahun —</option>
        <?php foreach ($tahuns as $t): ?><option value="<?= $t['t'] ?>" <?= $tahun==$t['t']?'selected':'' ?>><?= $t['t'] ?></option><?php endforeach; ?>
      </select>
      <button class="btn btn-outline btn-sm" type="submit"><i class="fa-solid fa-filter"></i> Filter</button>
      <?php if ($tahun||$desaId): ?><a href="<?= url('rekap') ?>" class="btn btn-ghost btn-sm">Reset</a><?php endif; ?>
    </form>

    <div class="stats-grid">
      <div class="stat blue">
        <div><div class="label">Total Pagu Anggaran</div><div class="value money"><?= rupiah($totals['pagu']) ?></div></div>
        <div class="ico"><i class="fa-solid fa-coins"></i></div>
      </div>
      <div class="stat">
        <div><div class="label">Total Biaya Dikwitansi</div><div class="value money"><?= rupiah($totals['dikwitansi']) ?></div></div>
        <div class="ico"><i class="fa-solid fa-receipt"></i></div>
      </div>
      <div class="stat">
        <div><div class="label">Total Realisasi</div><div class="value money"><?= rupiah($totals['realisasi']) ?></div></div>
        <div class="ico"><i class="fa-solid fa-circle-check"></i></div>
      </div>
      <div class="stat amber">
        <div><div class="label">Total Selisih (Kwitansi - Realisasi)</div><div class="value money"><?= rupiah($totals['selisih']) ?></div></div>
        <div class="ico"><i class="fa-solid fa-scale-balanced"></i></div>
      </div>
    </div>

    <div class="chart-card">
      <h3>Grafik Pagu, Kwitansi &amp; Realisasi per Desa</h3>
      <?php if (empty($rows)): ?>
        <div class="empty" style="margin:14px 0 0"><i class="fa-regular fa-chart-bar"></i><h4>Belum ada data rekap</h4></div>
      <?php else: ?>
        <div style="position:relative;height:360px"><canvas id="chartRekap" data-testid="rekap-chart"></canvas></div>
      <?php endif; ?>
    </div>

    <?php if (!empty($rows)): ?>
    <div class="section-title"><i class="fa-solid fa-table"></i> Tabel Rekap</div>
    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th style="width:36px">No</th>
            <th>Desa / Kepenghuluan</th>
            <th>Kecamatan</th>
            <th>Tahun</th>
            <th class="num">Jumlah Sesi</th>
            <th class="num">Pagu</th>
            <th class="num">Dikwitansi</th>
            <th class="num">Realisasi</th>
            <th class="num">Selisih</th>
          </tr>
        </thead>
        <tbody>
          <?php $no=1; foreach ($rows as $r): $sel = (float)$r['dikwitansi']-(float)$r['realisasi']; ?>
            <tr>
              <td><?= $no++ ?></td>
              <td style="font-weight:600"><?= e($r['desa']) ?></td>
              <td><?= e($r['kecamatan']) ?></td>
              <td><?= e($r['tahun_list']) ?></td>
              <td class="num"><?= (int)$r['jumlah_sesi'] ?></td>
              <td class="num"><?= rupiah($r['pagu']) ?></td>
              <td class="num"><?= rupiah($r['dikwitansi']) ?></td>
              <td class="num"><?= rupiah($r['realisasi']) ?></td>
              <td class="num" style="color:<?= $sel>0?'var(--red-600)':'var(--emerald-700)' ?>;font-weight:700"><?= rupiah($sel) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr><td colspan="5">JUMLAH</td>
            <td class="num"><?= rupiah($totals['pagu']) ?></td>
            <td class="num"><?= rupiah($totals['dikwitansi']) ?></td>
            <td class="num"><?= rupiah($totals['realisasi']) ?></td>
            <td class="num" style="color:<?= $totals['selisih']>0?'var(--red-600)':'var(--emerald-700)' ?>"><?= rupiah($totals['selisih']) ?></td>
          </tr>
        </tfoot>
      </table>
    </div>
    <?php endif; ?>
  </div>
</main>

<?php if (!empty($rows)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const data = <?= json_encode(array_map(fn($r)=>[
  'label' => $r['desa'],
  'pagu' => (float)$r['pagu'],
  'kwi'  => (float)$r['dikwitansi'],
  'real' => (float)$r['realisasi'],
  'sel' => (float)$r['dikwitansi'] - (float)$r['realisasi'],
], $rows)) ?>;
new Chart(document.getElementById('chartRekap'), {
  type:'bar',
  data:{ labels:data.map(d=>d.label),
    datasets:[
      {label:'Pagu', data:data.map(d=>d.pagu), backgroundColor:'#2563eb', borderRadius:6},
      {label:'Dikwitansi', data:data.map(d=>d.kwi), backgroundColor:'#f97316', borderRadius:6},
      {label:'Realisasi', data:data.map(d=>d.real), backgroundColor:'#10b981', borderRadius:6},
      {label:'Selisih', data:data.map(d=>d.sel), backgroundColor:'#facc15', borderRadius:6},
    ]
  },
  options:{
    responsive:true, maintainAspectRatio:false,
    plugins:{ legend:{position:'bottom'}, tooltip:{ callbacks:{ label: ctx => ctx.dataset.label+': Rp '+ctx.raw.toLocaleString('id-ID') }} },
    scales:{ y:{ beginAtZero:true, ticks:{ callback: v => 'Rp '+v.toLocaleString('id-ID') }} }
  }
});
</script>
<?php endif; ?>
<?php partial('foot'); ?>
