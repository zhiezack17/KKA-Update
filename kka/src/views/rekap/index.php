<?php $title = 'Rekap KKA - Per Desa'; ?>
<?php partial('head', compact('title')); ?>
<?php partial('sidebar'); ?>

<main class="main" data-testid="page-rekap">
  <div class="topbar">
    <div class="crumb"><i class="fa-solid fa-chart-column"></i> <b>Rekap per Desa</b></div>
    <a href="<?= url('export/rekap?' . http_build_query([
        'tahun'=>$tahun,'bidang'=>$bidId,'sub_bidang'=>$subBidId,'kecamatan'=>$kecId
    ])) ?>" class="btn btn-accent btn-sm" data-testid="btn-export-rekap"><i class="fa-solid fa-file-excel"></i> Export Excel</a>
  </div>

  <div class="content">
    <?php partial('flash'); ?>
    <div class="page-head">
      <div>
        <h2 style="text-transform:uppercase;letter-spacing:.5px">Rekap Kertas Kerja Audit &mdash; Per Desa</h2>
        <p>Ringkasan pagu, realisasi, dan kwitansi &mdash; dikelompokkan per <b>Sub Bidang</b> &middot; Kecamatan &middot; Tahun. Gunakan filter di bawah untuk memilih Bidang &amp; periode.</p>
      </div>
    </div>

    <form method="get" class="filter-bar" data-testid="rekap-filter">
      <select name="bidang" class="select" onchange="this.form.submit()" data-testid="f-bidang">
        <option value="0">— Semua Bidang —</option>
        <?php foreach ($bidang as $b): ?><option value="<?= $b['id'] ?>" <?= $bidId==$b['id']?'selected':'' ?>><?= e($b['nama']) ?></option><?php endforeach; ?>
      </select>
      <select name="sub_bidang" class="select" data-testid="f-subbidang">
        <option value="0">— Semua Sub Bidang —</option>
        <?php foreach ($subBidang as $sb): ?><option value="<?= $sb['id'] ?>" <?= $subBidId==$sb['id']?'selected':'' ?>><?= e($sb['nama']) ?></option><?php endforeach; ?>
      </select>
      <select name="kecamatan" class="select" data-testid="f-kecamatan">
        <option value="0">— Semua Kecamatan —</option>
        <?php foreach ($kecamatan as $k): ?><option value="<?= $k['id'] ?>" <?= $kecId==$k['id']?'selected':'' ?>><?= e($k['nama']) ?></option><?php endforeach; ?>
      </select>
      <select name="tahun" class="select" data-testid="f-tahun">
        <option value="0">— Semua Tahun —</option>
        <?php foreach ($tahuns as $t): ?><option value="<?= $t['t'] ?>" <?= $tahun==$t['t']?'selected':'' ?>><?= $t['t'] ?></option><?php endforeach; ?>
      </select>
      <button class="btn btn-outline btn-sm" type="submit"><i class="fa-solid fa-filter"></i> Terapkan</button>
      <?php if ($tahun||$bidId||$subBidId||$kecId): ?><a href="<?= url('rekap') ?>" class="btn btn-ghost btn-sm">Reset</a><?php endif; ?>
    </form>

    <?php if ($bidangNama || $subBidangNama || $kecamatanNama || $tahun): ?>
    <div class="card" style="padding:12px 18px;margin-bottom:14px;background:linear-gradient(90deg,#f0fdf4,#ecfeff);border-left:4px solid var(--emerald-700)">
      <div style="display:flex;flex-wrap:wrap;gap:14px;font-size:13px">
        <?php if ($bidangNama): ?><div><b>Bidang</b>: <?= e($bidangNama) ?></div><?php endif; ?>
        <?php if ($subBidangNama): ?><div><b>Sub Bidang</b>: <?= e($subBidangNama) ?></div><?php endif; ?>
        <?php if ($kecamatanNama): ?><div><b>Kecamatan</b>: <?= e($kecamatanNama) ?></div><?php endif; ?>
        <?php if ($tahun): ?><div><b>Tahun Anggaran</b>: <?= (int)$tahun ?></div><?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

    <div class="stats-grid">
      <div class="stat blue">
        <div><div class="label">Total Pagu Anggaran</div><div class="value money" data-testid="stat-pagu"><?= rupiah($totals['pagu']) ?></div></div>
        <div class="ico"><i class="fa-solid fa-coins"></i></div>
      </div>
      <div class="stat">
        <div><div class="label">Total Realisasi</div><div class="value money" data-testid="stat-realisasi"><?= rupiah($totals['realisasi']) ?></div></div>
        <div class="ico"><i class="fa-solid fa-circle-check"></i></div>
      </div>
      <div class="stat">
        <div><div class="label">Total Biaya Dikwitansi</div><div class="value money" data-testid="stat-dikwitansi"><?= rupiah($totals['dikwitansi']) ?></div></div>
        <div class="ico"><i class="fa-solid fa-receipt"></i></div>
      </div>
      <div class="stat amber">
        <div><div class="label">Selisih (Realisasi &minus; Dikwitansi)</div><div class="value money" data-testid="stat-selisih"><?= rupiah($totals['selisih']) ?></div></div>
        <div class="ico"><i class="fa-solid fa-scale-balanced"></i></div>
      </div>
    </div>

    <div class="chart-card">
      <h3>Grafik Pagu, Realisasi &amp; Dikwitansi per Sub Bidang</h3>
      <?php if (empty($rows)): ?>
        <div class="empty" style="margin:14px 0 0"><i class="fa-regular fa-chart-bar"></i><h4>Belum ada data rekap</h4><p>Coba ubah filter atau tambahkan Sesi Audit terlebih dahulu.</p></div>
      <?php else: ?>
        <div style="position:relative;height:360px"><canvas id="chartRekap" data-testid="rekap-chart"></canvas></div>
      <?php endif; ?>
    </div>

    <?php if (!empty($rows)): ?>
    <div class="section-title"><i class="fa-solid fa-table"></i> Tabel Rekap</div>
    <div class="table-wrap">
      <table class="table" data-testid="rekap-table">
        <thead>
          <tr>
            <th style="width:36px">No</th>
            <th>Sub Bidang</th>
            <th>Kecamatan</th>
            <th style="width:60px">Tahun</th>
            <th class="num" style="width:70px">Jumlah Sesi</th>
            <th class="num">Pagu (Rp)</th>
            <th class="num">Realisasi (Rp)</th>
            <th class="num">Dikwitansi (Rp)</th>
            <th class="num">Selisih (Rp)</th>
            <th>Keterangan</th>
          </tr>
        </thead>
        <tbody>
          <?php $no=1; foreach ($rows as $r): $sel = (float)$r['realisasi']-(float)$r['dikwitansi']; ?>
            <tr>
              <td><?= $no++ ?></td>
              <td style="font-weight:600"><?= e($r['sub_bidang']) ?></td>
              <td><?= e($r['kecamatan']) ?></td>
              <td><?= (int)$r['tahun'] ?></td>
              <td class="num"><?= (int)$r['jumlah_sesi'] ?></td>
              <td class="num"><?= rupiah($r['pagu']) ?></td>
              <td class="num"><?= rupiah($r['realisasi']) ?></td>
              <td class="num"><?= rupiah($r['dikwitansi']) ?></td>
              <td class="num" style="color:<?= $sel<0?'var(--red-600)':'var(--emerald-700)' ?>;font-weight:700"><?= rupiah($sel) ?></td>
              <td>&mdash;</td>
            </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="5">JUMLAH</td>
            <td class="num"><?= rupiah($totals['pagu']) ?></td>
            <td class="num"><?= rupiah($totals['realisasi']) ?></td>
            <td class="num"><?= rupiah($totals['dikwitansi']) ?></td>
            <td class="num" style="color:<?= $totals['selisih']<0?'var(--red-600)':'var(--emerald-700)' ?>"><?= rupiah($totals['selisih']) ?></td>
            <td></td>
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
  'label' => $r['sub_bidang'] . ' (' . $r['kecamatan'] . ' ' . $r['tahun'] . ')',
  'pagu' => (float)$r['pagu'],
  'real' => (float)$r['realisasi'],
  'kwi'  => (float)$r['dikwitansi'],
  'sel'  => (float)$r['realisasi'] - (float)$r['dikwitansi'],
], $rows)) ?>;
new Chart(document.getElementById('chartRekap'), {
  type:'bar',
  data:{ labels:data.map(d=>d.label),
    datasets:[
      {label:'Pagu',       data:data.map(d=>d.pagu), backgroundColor:'#2563eb', borderRadius:6},
      {label:'Realisasi',  data:data.map(d=>d.real), backgroundColor:'#10b981', borderRadius:6},
      {label:'Dikwitansi', data:data.map(d=>d.kwi),  backgroundColor:'#f97316', borderRadius:6},
      {label:'Selisih',    data:data.map(d=>d.sel),  backgroundColor:'#facc15', borderRadius:6},
    ]
  },
  options:{
    responsive:true, maintainAspectRatio:false,
    plugins:{ legend:{position:'bottom'}, tooltip:{ callbacks:{ label: ctx => ctx.dataset.label+': Rp '+ctx.raw.toLocaleString('id-ID') }} },
    scales:{
      x:{ ticks:{ maxRotation:60, minRotation:30, autoSkip:false, font:{size:10} } },
      y:{ beginAtZero:true, ticks:{ callback: v => 'Rp '+v.toLocaleString('id-ID') }}
    }
  }
});
</script>
<?php endif; ?>
<?php partial('foot'); ?>
