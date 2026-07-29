<?php $tipeLabel = ['standar' => 'KKP STANDAR', 'fisik' => 'KKA FISIK (PENGUKURAN)', 'sketsa' => 'KKA SKETSA / FOTO LAPANGAN']; ?>
<?php $title = 'Preview - ' . ($tipeLabel[$m['tipe']] ?? 'Master KKA'); ?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title><?= e($title) ?></title>
<style>
  @page { size: A4 portrait; margin: 12mm 12mm 14mm 12mm; }
  *{box-sizing:border-box}
  body{font-family:'Times New Roman',serif;color:#000;font-size:11pt;margin:0;background:#f1f5f9}
  .page{background:#fff;width:210mm;min-height:297mm;margin:14px auto;padding:12mm;box-shadow:0 8px 24px rgba(0,0,0,.12);position:relative}
  .kop{display:flex;gap:10px;align-items:flex-start;border-bottom:3px double #000;padding-bottom:8px;margin-bottom:10px}
  .kop .logo{width:64px;height:64px;flex-shrink:0}
  .kop .logo img{max-width:100%;max-height:100%;object-fit:contain}
  .kop .center{flex:1;text-align:center;line-height:1.3}
  .kop .center .l1{font-size:12pt;font-weight:bold}
  .kop .center .l2{font-size:16pt;font-weight:bold}
  .kop .center .l3{font-size:9.5pt}
  h1{text-align:center;font-size:14pt;margin:8px 0 2px;text-decoration:underline}
  .id-table{width:100%;border-collapse:collapse;margin-bottom:10px;font-size:10.5pt}
  .id-table td{padding:2px 4px;vertical-align:top}
  .id-table td.lbl{width:32mm;font-weight:bold;white-space:nowrap}
  .id-table td.sep{width:5px}
  .id-table td.lbl-r{width:28mm;font-weight:bold;white-space:nowrap;padding-left:4px}
  table.data{width:100%;border-collapse:collapse;font-size:10.5pt}
  table.data th, table.data td{border:1px solid #000;padding:5px 6px;vertical-align:top}
  table.data thead th{background:#dcfce7;text-align:center;font-weight:bold}
  table.data .num{text-align:right;font-variant-numeric:tabular-nums;white-space:nowrap}
  table.data tfoot td{font-weight:bold;background:#f3f4f6}
  .narasi{min-height:280px;white-space:pre-wrap;text-align:justify;line-height:1.6;padding:8px 0}
  .foto-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin-top:8px}
  .foto-item{border:1px solid #999;padding:6px;text-align:center;page-break-inside:avoid}
  .foto-item img{max-width:100%;max-height:200px;object-fit:contain;display:block;margin:0 auto}
  .foto-item .cap{font-size:9pt;color:#333;margin-top:4px}
  .ttd-wrap{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:32px;text-align:center;font-size:10.5pt}
  .ttd-wrap b{display:block;margin-bottom:80px}
  .ttd-wrap u{font-weight:bold}
  .toolbar{position:sticky;top:0;z-index:10;background:linear-gradient(180deg,#064e3b,#047857);color:#fff;padding:10px 22px;display:flex;justify-content:space-between;align-items:center;font-family:'Plus Jakarta Sans',sans-serif}
  .toolbar .info{font-size:13px;color:#bbf7d0}
  .toolbar button,.toolbar a{padding:8px 16px;border-radius:8px;background:#facc15;color:#022c22;border:0;font-weight:700;cursor:pointer;text-decoration:none;font-size:13px;margin-left:6px}
  .toolbar a.alt{background:#1e293b;color:#fff}
  @media print{
    .toolbar{display:none}
    body{background:#fff}
    .page{box-shadow:none;margin:0;width:auto;min-height:0;padding:0}
  }
</style>
</head>
<body>
<div class="toolbar no-print">
  <div class="info">Preview A4 · <b><?= e($tipeLabel[$m['tipe']]) ?></b></div>
  <div>
    <a class="alt" href="<?= url('master/edit?id='.(int)$m['id']) ?>">← Kembali</a>
    <a href="<?= url('master/export?id='.(int)$m['id']) ?>">📥 Excel</a>
    <button onclick="window.print()">🖨 Cetak</button>
  </div>
</div>

<div class="page">
  <div class="kop">
    <div class="logo"><img src="<?= asset('img/logo-rohil.png') ?>" alt="Rohil"></div>
    <div class="center">
      <div class="l1">PEMERINTAH KABUPATEN ROKAN HILIR</div>
      <div class="l2">INSPEKTORAT</div>
      <div class="l3">Komplek Perkantoran Batu 6 Jl. Lintas Pesisir Sungai Rokan, Kec. Bangko - Bagansiapiapi</div>
      <div class="l3">Telp. (0767) 2700270 · Email: inspektorat@rohilkab.go.id</div>
    </div>
    <div class="logo"><img src="<?= asset('img/logo-inspektorat.png') ?>" alt="Inspektorat"></div>
  </div>

  <h1><?= e($tipeLabel[$m['tipe']]) ?></h1>

  <table class="id-table">
    <tr>
      <td class="lbl">Objek Audit</td><td class="sep">:</td><td><?= e($m['objek_audit']) ?></td>
      <td class="lbl-r">No. KKA</td><td class="sep">:</td><td><?= e($m['no_kka'] ?: ($m['sesi_no_kka'] ?: '-')) ?></td>
    </tr>
    <tr>
      <td class="lbl">Kepenghuluan</td><td class="sep">:</td><td><?= e($m['desa_nama']) ?> (Kec. <?= e($m['kecamatan_nama']) ?>)</td>
      <td class="lbl-r">No. Ref PKA</td><td class="sep">:</td><td><?= e($m['ref_pka'] ?: '-') ?></td>
    </tr>
    <tr>
      <td class="lbl">Masa Audit</td><td class="sep">:</td><td>Semester <?= (int)$m['semester'] ?> Tahun <?= (int)$m['tahun_anggaran'] ?></td>
      <td class="lbl-r">Dibuat oleh</td><td class="sep">:</td><td><?= e($m['dibuat_oleh'] ?: '-') ?></td>
    </tr>
    <tr>
      <td class="lbl">Tahun Anggaran</td><td class="sep">:</td><td><?= (int)$m['tahun_anggaran'] ?></td>
      <td class="lbl-r">Tanggal</td><td class="sep">:</td><td><?= e(tgl_id($m['tanggal_dok'] ?: $m['tanggal_dibuat'])) ?></td>
    </tr>
    <tr>
      <td class="lbl">Bidang</td><td class="sep">:</td><td style="font-size:9.5pt"><?= e($m['bidang_nama']) ?></td>
      <td class="lbl-r">Direview</td><td class="sep">:</td><td><?= e($m['direview_oleh'] ?: '-') ?></td>
    </tr>
    <tr>
      <td class="lbl">Kegiatan</td><td class="sep">:</td><td colspan="4"><?= e($m['kegiatan'] ?: '-') ?></td>
    </tr>
  </table>

  <?php if ($m['tipe'] === 'standar'): ?>
    <div class="narasi"><?= nl2br(e($m['narasi'] ?: '- Belum ada narasi -')) ?></div>

  <?php elseif ($m['tipe'] === 'fisik'): ?>
    <table class="data">
      <thead>
        <tr>
          <th rowspan="2" style="width:8mm">No</th>
          <th rowspan="2" style="width:22mm">STA</th>
          <th rowspan="2" style="width:22mm">Jarak (m)</th>
          <th colspan="2">Lebar (m)</th>
          <th rowspan="2" style="width:22mm">Tebal (m)</th>
          <th rowspan="2" style="width:25mm">Volume (m³)</th>
          <th rowspan="2">Keterangan</th>
        </tr>
        <tr><th style="width:18mm">I</th><th style="width:18mm">II</th></tr>
      </thead>
      <tbody>
        <?php if (empty($fisikRows)): ?>
          <tr><td colspan="8" style="text-align:center;padding:20px;color:#666">- Belum ada data pengukuran -</td></tr>
        <?php else: $no=1; $tVol=0; foreach ($fisikRows as $r): $tVol += (float)$r['volume']; ?>
          <tr>
            <td style="text-align:center"><?= $no++ ?></td>
            <td style="text-align:center"><?= e((string)$r['sta']) ?></td>
            <td class="num"><?= number_format($r['jarak'],3,',','.') ?></td>
            <td class="num"><?= number_format($r['lebar_i'],3,',','.') ?></td>
            <td class="num"><?= number_format($r['lebar_ii'],3,',','.') ?></td>
            <td class="num"><?= number_format($r['tebal'],3,',','.') ?></td>
            <td class="num"><?= number_format($r['volume'],3,',','.') ?></td>
            <td><?= e((string)$r['keterangan']) ?></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
      <?php if (!empty($fisikRows)): ?>
      <tfoot>
        <tr>
          <td colspan="6" style="text-align:right">JUMLAH VOLUME:</td>
          <td class="num"><?= number_format($tVol,3,',','.') ?></td>
          <td></td>
        </tr>
      </tfoot>
      <?php endif; ?>
    </table>

  <?php else: /* sketsa */ ?>
    <?php if (!empty($m['narasi'])): ?>
      <div style="margin-bottom:10px;font-size:10.5pt;text-align:justify"><?= nl2br(e($m['narasi'])) ?></div>
    <?php endif; ?>
    <?php if (empty($foto)): ?>
      <div style="text-align:center;padding:60px 0;color:#666;border:1px dashed #999">- Belum ada foto lapangan -</div>
    <?php else: ?>
      <div class="foto-grid">
        <?php foreach ($foto as $f): ?>
          <div class="foto-item">
            <img src="<?= url('master/foto?id='.(int)$f['id']) ?>" alt="<?= e($f['nama_asli']) ?>">
            <div class="cap"><b><?= e($f['nama_asli']) ?></b><?php if (!empty($f['keterangan'])): ?><br><?= e($f['keterangan']) ?><?php endif; ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>

  <!-- Tanda tangan -->
  <div class="ttd-wrap">
    <div>
      <div><i><?= e($m['tanggal_dok'] ? tgl_id($m['tanggal_dok']) : 'Bagansiapiapi, ...........') ?></i></div>
      <b>Pendamping Dilapangan,</b>
      <u><?= e($m['pendamping'] ?: '.......................') ?></u>
      <?php if (!empty($m['pendamping_nip'])): ?><div>NIP. <?= e($m['pendamping_nip']) ?></div><?php endif; ?>
    </div>
    <div>
      <div style="visibility:hidden">.</div>
      <b>Ketua Tim,</b>
      <u><?= e($m['ketua_tim'] ?: '.......................') ?></u>
      <?php if (!empty($m['ketua_tim_nip'])): ?><div>NIP. <?= e($m['ketua_tim_nip']) ?></div><?php endif; ?>
    </div>
  </div>
</div>
</body>
</html>
