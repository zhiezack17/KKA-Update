<?php $title = 'KKA - ' . $sesi['desa_nama']; ?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title><?= e($title) ?></title>
<style>
  @page { size: A4 portrait; margin: 12mm 12mm 14mm 12mm; }
  *{box-sizing:border-box}
  body{font-family:'Times New Roman',serif;color:#000;font-size:10.5pt;margin:0;background:#f1f5f9}
  .page{
    background:#fff;
    width:210mm; min-height:297mm;
    margin:14px auto;
    padding:12mm 12mm 22mm 12mm;
    box-shadow:0 8px 24px rgba(0,0,0,.12);
    position:relative;
  }
  .kop{
    display:flex;gap:10px;align-items:flex-start;
    border-bottom:3px double #000;
    padding-bottom:8px;margin-bottom:10px;
    min-height:72px;
  }
  .kop .logo{width:64px;height:64px;display:grid;place-items:center;flex-shrink:0;margin-top:-2px}
  .kop .logo img{max-width:100%;max-height:100%;object-fit:contain;display:block}
  .kop .center{flex:1;text-align:center;line-height:1.3;padding-top:2px}
  .kop .center .l1{font-size:11.5pt;font-weight:bold;letter-spacing:.3px}
  .kop .center .l2{font-size:16pt;font-weight:bold;letter-spacing:1px;margin:1px 0}
  .kop .center .l3{font-size:9pt}
  h1{text-align:center;font-size:13pt;margin:6px 0 2px;text-decoration:underline}
  h2{text-align:center;font-size:11pt;margin:0 0 10px;font-weight:normal}
  .id-table{width:100%;border-collapse:collapse;margin-bottom:8px;font-size:9.5pt}
  .id-table td{padding:1px 3px;vertical-align:top;line-height:1.3}
  .id-table td.lbl{width:32mm;font-weight:bold;white-space:nowrap}
  .id-table td.sep{width:5px}
  .id-table td.val-left{width:70mm}
  .id-table td.lbl-r{width:30mm;font-weight:bold;white-space:nowrap;padding-left:4px}
  .id-table td.val-right{width:auto;padding-left:2px}
  .id-table .sub-r{font-weight:normal;font-size:8pt;color:#333}
  table.data{width:100%;border-collapse:collapse;margin-top:4px;font-size:9pt}
  table.data th, table.data td{border:1px solid #000;padding:4px 5px;vertical-align:top}
  table.data thead th{background:#dcfce7;text-align:center;font-weight:bold;font-size:9pt}
  table.data .num{text-align:right;font-variant-numeric:tabular-nums;white-space:nowrap}
  table.data tfoot td{font-weight:bold;background:#f3f4f6}
  .sec{margin-top:10px;font-size:10pt}
  .sec .ttl{font-weight:bold;text-decoration:underline;margin-bottom:3px}
  .sec .isi{min-height:20px;text-align:justify}
  .page-footer{
    position:absolute;
    left:12mm;right:12mm;bottom:8mm;
    padding-top:5px;
    border-top:1px dashed #999;
    display:flex;justify-content:space-between;
    color:#666;font-size:8pt;font-style:italic;
  }
  .toolbar{
    position:sticky;top:0;z-index:10;
    background:linear-gradient(180deg,#064e3b 0%,#047857 100%);color:#fff;
    padding:10px 22px;display:flex;justify-content:space-between;align-items:center;
    font-family:'Plus Jakarta Sans',sans-serif;
  }
  .toolbar .info{font-size:13px;color:#bbf7d0}
  .toolbar .info b{color:#fff}
  .toolbar button, .toolbar a{padding:8px 16px;border-radius:8px;background:#facc15;color:#022c22;border:0;font-weight:700;cursor:pointer;text-decoration:none;font-size:13px;margin-left:6px}
  .toolbar a.alt{background:#1e293b;color:#fff}
  .toolbar a.alt:hover{background:#0f172a}
  @media print{
    .toolbar{display:none}
    body{background:#fff}
    .page{box-shadow:none;margin:0;width:auto;min-height:0;padding:0}
    .page-footer{position:fixed;bottom:6mm;left:12mm;right:12mm}
  }
</style>
</head>
<body>
<div class="toolbar no-print">
  <div class="info">Preview Cetak KKA · Format <b>A4 Portrait</b> · Tekan tombol <b>Cetak</b> untuk print fisik / simpan PDF.</div>
  <div>
    <a class="alt" href="<?= url('sesi/show?id='.$sesi['id']) ?>">← Kembali</a>
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
  <h1>KERTAS KERJA AUDIT (KKA)</h1>
  <h2>PENGELUARAN KEUANGAN KEPENGHULUAN — Tahun Anggaran <?= (int)$sesi['tahun_anggaran'] ?></h2>
  <?php
    $tgl_buat   = !empty($sesi['tanggal_dibuat'])  ? date('d/m/y', strtotime($sesi['tanggal_dibuat']))  : '..../..../....';
    $tgl_review = !empty($sesi['tanggal_review']) ? date('d/m/y', strtotime($sesi['tanggal_review'])) : '..../..../....';
    $nama_buat   = $sesi['dibuat_oleh']   ?: '...........................';
    $nama_review = $sesi['direview_oleh'] ?: '...........................';
  ?>
  <table class="id-table">
    <tr>
      <td class="lbl">Kepenghuluan / Desa</td><td class="sep">:</td><td class="val-left"><?= e($sesi['desa_nama']) ?> (Kec. <?= e($sesi['kecamatan_nama']) ?>)</td>
      <td class="lbl-r">No. KKA</td><td class="sep">:</td><td class="val-right"><?= e($sesi['no_kka'] ?: '-') ?></td>
    </tr>
    <tr>
      <td class="lbl">Objek Audit</td><td class="sep">:</td><td class="val-left"><?= e($sesi['objek_audit']) ?></td>
      <td class="lbl-r">Ref. KKA</td><td class="sep">:</td><td class="val-right"><?= e($sesi['ref_kka'] ?: '-') ?></td>
    </tr>
    <tr>
      <td class="lbl">Masa Audit</td><td class="sep">:</td><td class="val-left">Semester <?= (int)$sesi['semester'] ?> Tahun <?= (int)$sesi['tahun_anggaran'] ?></td>
      <td class="lbl-r">Disusun oleh <span class="sub-r">(Auditor)</span></td><td class="sep">:</td><td class="val-right"><?= e($nama_buat) ?></td>
    </tr>
    <tr>
      <td class="lbl">Bidang</td><td class="sep">:</td><td class="val-left" style="font-size:8.5pt"><?= e($sesi['bidang_nama']) ?></td>
      <td class="lbl-r">Tgl/Paraf</td><td class="sep">:</td><td class="val-right"><?= e($tgl_buat) ?></td>
    </tr>
    <tr>
      <td class="lbl">Sub Bidang</td><td class="sep">:</td><td class="val-left" style="font-size:8.5pt"><?= e($sesi['sub_bidang_nama'] ?: '-') ?></td>
      <td class="lbl-r">Direview oleh <span class="sub-r">(Ketua Tim)</span></td><td class="sep">:</td><td class="val-right"><?= e($nama_review) ?></td>
    </tr>
    <tr>
      <td class="lbl">Kegiatan</td><td class="sep">:</td><td class="val-left"><?= e($sesi['kegiatan'] ?: '-') ?></td>
      <td class="lbl-r">Tgl/Paraf</td><td class="sep">:</td><td class="val-right"><?= e($tgl_review) ?></td>
    </tr>
    <tr>
      <td class="lbl">Pagu Anggaran</td><td class="sep">:</td><td colspan="4" style="font-weight:bold">Rp <?= number_format((float)$sesi['pagu_anggaran'],0,',','.') ?></td>
    </tr>
  </table>
  <table class="data">
    <thead>
      <tr>
        <th style="width:22px">No</th>
        <th>Uraian / Rincian Belanja</th>
        <th style="width:78px">Biaya Dikwitansi (Rp)</th>
        <th style="width:78px">Realisasi (Rp)</th>
        <th style="width:78px">Selisih (Rp)</th>
        <th style="width:85px">Penerima</th>
        <th style="width:95px">Keterangan</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($rincian)): ?>
        <tr><td colspan="7" style="text-align:center;padding:20px;color:#666">- Belum ada rincian -</td></tr>
      <?php else: $no=1; foreach ($rincian as $r): $sel=(float)$r['biaya_dikwitansi']-(float)$r['realisasi']; ?>
        <tr>
          <td style="text-align:center"><?= $no++ ?></td>
          <td><?= e($r['uraian']) ?></td>
          <td class="num"><?= number_format($r['biaya_dikwitansi'],0,',','.') ?></td>
          <td class="num"><?= number_format($r['realisasi'],0,',','.') ?></td>
          <td class="num"><?= number_format($sel,0,',','.') ?></td>
          <td><?= e($r['penerima'] ?: '-') ?></td>
          <td><?= e($r['keterangan'] ?: '-') ?></td>
        </tr>
      <?php endforeach; endif; ?>
    </tbody>
    <?php if (!empty($rincian)): ?>
    <tfoot>
      <tr>
        <td colspan="2" style="text-align:center">JUMLAH</td>
        <td class="num"><?= number_format($totals['dikwitansi'],0,',','.') ?></td>
        <td class="num"><?= number_format($totals['realisasi'],0,',','.') ?></td>
        <td class="num"><?= number_format($totals['selisih'],0,',','.') ?></td>
        <td colspan="2"></td>
      </tr>
    </tfoot>
    <?php endif; ?>
  </table>
  <div class="sec">
    <div class="ttl">KESIMPULAN AUDIT:</div>
    <div class="isi"><?= nl2br(e($sesi['kesimpulan'] ?: '-')) ?></div>
  </div>
  <div class="sec">
    <div class="ttl">SUMBER DATA:</div>
    <div class="isi"><?= nl2br(e($sesi['sumber_data'] ?: '-')) ?></div>
  </div>
  <div class="page-footer">
    <div>Portal Arsip Digital · Inspektorat Kab. Rokan Hilir</div>
    <div>Dicetak: <?= date('d/m/Y H:i') ?></div>
    <div>KKA #<?= (int)$sesi['id'] ?></div>
  </div>
</div>
</body>
</html>
