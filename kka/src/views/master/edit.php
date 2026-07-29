<?php $title = 'Edit Master KKA'; ?>
<?php partial('head', compact('title')); ?>
<?php partial('sidebar'); ?>

<main class="main" data-testid="page-master-edit">
  <div class="topbar">
    <div class="crumb">
      <i class="fa-solid fa-folder-tree"></i>
      <a href="<?= url('master') ?>" style="color:var(--slate-500)">Master KKA</a> / <b><?= e($tipeLabel[$m['tipe']]) ?></b>
    </div>
    <div style="display:flex;gap:8px">
      <a href="<?= url('master') ?>" class="btn btn-ghost btn-sm" data-testid="btn-back-master"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
      <a href="<?= url('master/preview?id='.(int)$m['id']) ?>" target="_blank" class="btn btn-outline btn-sm" data-testid="btn-preview"><i class="fa-solid fa-eye"></i> Preview</a>
      <a href="<?= url('master/export?id='.(int)$m['id']) ?>" class="btn btn-accent btn-sm" data-testid="btn-export-xls"><i class="fa-solid fa-file-excel"></i> Export Excel</a>
    </div>
  </div>

  <div class="content">
    <?php partial('flash'); ?>

    <div class="page-head">
      <div>
        <h2><?= e($m['judul']) ?></h2>
        <p><b>Sesi Audit:</b> <?= e($m['objek_audit']) ?> &middot; <?= e($m['desa_nama']) ?> · Kec. <?= e($m['kecamatan_nama']) ?> · S<?= (int)$m['semester'] ?>/<?= (int)$m['tahun_anggaran'] ?></p>
      </div>
    </div>

    <form method="post" action="<?= url('master/update') ?>" data-testid="form-master-edit">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">

      <!-- Identitas -->
      <div class="card" style="margin-bottom:16px">
        <h3 style="margin:0 0 12px;font-size:14px;font-weight:800;color:var(--slate-600);text-transform:uppercase;letter-spacing:1px">Identitas Dokumen</h3>
        <div class="row">
          <div class="field"><label>Judul</label><input type="text" name="judul" class="input" value="<?= e($m['judul']) ?>" data-testid="f-judul"></div>
          <div class="field"><label>Tanggal Dokumen</label><input type="date" name="tanggal_dok" class="input" value="<?= e((string)$m['tanggal_dok']) ?>"></div>
        </div>
        <div class="row">
          <div class="field"><label>No. KKA</label><input type="text" name="no_kka" class="input" value="<?= e((string)$m['no_kka']) ?>"></div>
          <div class="field"><label>No. Ref PKA</label><input type="text" name="ref_pka" class="input" value="<?= e((string)$m['ref_pka']) ?>"></div>
        </div>
      </div>

      <!-- Konten sesuai tipe -->
      <?php if ($m['tipe'] === 'standar'): ?>
        <div class="card" style="margin-bottom:16px">
          <h3 style="margin:0 0 12px;font-size:14px;font-weight:800;color:var(--slate-600);text-transform:uppercase;letter-spacing:1px"><i class="fa-solid fa-file-lines"></i> Narasi Kondisi Lapangan</h3>
          <div class="field">
            <label>Uraian Kondisi Lapangan / Hasil Audit</label>
            <textarea name="narasi" class="textarea" rows="18" placeholder="Ceritakan kondisi lapangan yang ditemui saat audit: kondisi bangunan, temuan, catatan penting, kesesuaian dengan RAB, dsb." data-testid="f-narasi"><?= e((string)$m['narasi']) ?></textarea>
          </div>
        </div>

      <?php elseif ($m['tipe'] === 'fisik'): ?>
        <div class="card" style="margin-bottom:16px;padding:0">
          <div style="padding:14px 18px;border-bottom:1px solid var(--slate-200)">
            <h3 style="margin:0;font-size:14px;font-weight:800;color:var(--slate-600);text-transform:uppercase;letter-spacing:1px"><i class="fa-solid fa-ruler-combined"></i> Tabel Pengukuran Fisik</h3>
            <p style="margin:4px 0 0;font-size:12px;color:var(--slate-500)">Volume dihitung otomatis: <b>Jarak &times; ((Lebar I + Lebar II) / 2) &times; Tebal</b></p>
          </div>
          <div class="table-wrap" style="border:0">
            <table class="table" data-testid="tbl-fisik">
              <thead>
                <tr>
                  <th style="width:40px">No</th>
                  <th style="width:110px">STA</th>
                  <th class="num" style="width:110px">Jarak (m)</th>
                  <th class="num" style="width:110px">Lebar I (m)</th>
                  <th class="num" style="width:110px">Lebar II (m)</th>
                  <th class="num" style="width:110px">Tebal (m)</th>
                  <th class="num" style="width:120px">Volume (m³)</th>
                  <th>Keterangan</th>
                  <th style="width:40px"></th>
                </tr>
              </thead>
              <tbody id="fisik-body">
                <?php $rowsInit = !empty($fisikRows) ? $fisikRows : [['sta'=>'','jarak'=>0,'lebar_i'=>0,'lebar_ii'=>0,'tebal'=>0,'volume'=>0,'keterangan'=>'']]; ?>
                <?php $no=1; foreach ($rowsInit as $r): ?>
                  <tr class="fisik-row">
                    <td class="rn"><?= $no++ ?></td>
                    <td><input type="text" name="sta[]" class="input" value="<?= e((string)($r['sta'] ?? '')) ?>"></td>
                    <td><input type="text" name="jarak[]" class="input num-in" data-money value="<?= e(rtrim(rtrim(number_format((float)($r['jarak']??0),3,',','.'),'0'),',')) ?>"></td>
                    <td><input type="text" name="lebar_i[]" class="input num-in" data-money value="<?= e(rtrim(rtrim(number_format((float)($r['lebar_i']??0),3,',','.'),'0'),',')) ?>"></td>
                    <td><input type="text" name="lebar_ii[]" class="input num-in" data-money value="<?= e(rtrim(rtrim(number_format((float)($r['lebar_ii']??0),3,',','.'),'0'),',')) ?>"></td>
                    <td><input type="text" name="tebal[]" class="input num-in" data-money value="<?= e(rtrim(rtrim(number_format((float)($r['tebal']??0),3,',','.'),'0'),',')) ?>"></td>
                    <td class="vol-cell num" style="font-weight:700;color:var(--emerald-700)"><?= number_format((float)($r['volume']??0),3,',','.') ?></td>
                    <td><input type="text" name="keterangan_baris[]" class="input" value="<?= e((string)($r['keterangan'] ?? '')) ?>"></td>
                    <td><button type="button" class="btn btn-ghost btn-sm js-rm-row" style="color:var(--red-600)"><i class="fa-solid fa-trash"></i></button></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="6" style="text-align:right">JUMLAH VOLUME:</td>
                  <td class="num" id="total-volume" style="font-weight:700;color:var(--emerald-700)">0,000</td>
                  <td colspan="2"></td>
                </tr>
              </tfoot>
            </table>
          </div>
          <div style="padding:12px 18px;border-top:1px solid var(--slate-200);background:var(--slate-50)">
            <button type="button" class="btn btn-outline btn-sm" id="btn-add-row" data-testid="btn-add-row"><i class="fa-solid fa-plus"></i> Tambah Baris</button>
          </div>
        </div>

      <?php else: /* sketsa */ ?>
        <div class="card" style="margin-bottom:16px">
          <h3 style="margin:0 0 12px;font-size:14px;font-weight:800;color:var(--slate-600);text-transform:uppercase;letter-spacing:1px"><i class="fa-solid fa-camera"></i> Foto Lapangan &amp; Catatan Sketsa</h3>
          <div class="field">
            <label>Catatan / Deskripsi Sketsa</label>
            <textarea name="narasi" class="textarea" rows="4" placeholder="cth: Foto tampak depan, samping, kondisi jalan aspal, dsb." data-testid="f-catatan-sketsa"><?= e((string)$m['narasi']) ?></textarea>
          </div>
        </div>
      <?php endif; ?>

      <!-- Tanda tangan -->
      <div class="card" style="margin-bottom:16px">
        <h3 style="margin:0 0 12px;font-size:14px;font-weight:800;color:var(--slate-600);text-transform:uppercase;letter-spacing:1px">Tanda Tangan</h3>
        <div class="row">
          <div class="field"><label>Pendamping Dilapangan</label><input type="text" name="pendamping" class="input" value="<?= e((string)$m['pendamping']) ?>" placeholder="Nama pendamping"></div>
          <div class="field"><label>NIP Pendamping</label><input type="text" name="pendamping_nip" class="input" value="<?= e((string)$m['pendamping_nip']) ?>" placeholder="NIP..."></div>
        </div>
        <div class="row">
          <div class="field"><label>Ketua Tim</label><input type="text" name="ketua_tim" class="input" value="<?= e((string)$m['ketua_tim']) ?>" placeholder="Nama ketua tim"></div>
          <div class="field"><label>NIP Ketua Tim</label><input type="text" name="ketua_tim_nip" class="input" value="<?= e((string)$m['ketua_tim_nip']) ?>" placeholder="NIP..."></div>
        </div>
      </div>

      <div style="display:flex;justify-content:flex-end;gap:8px;margin-bottom:20px">
        <a href="<?= url('master') ?>" class="btn btn-outline">Batal</a>
        <button class="btn btn-primary" type="submit" data-testid="btn-simpan-master-edit"><i class="fa-solid fa-save"></i> Simpan Perubahan</button>
      </div>
    </form>

    <?php if ($m['tipe'] === 'sketsa'): ?>
    <!-- Upload foto (untuk tipe sketsa) - form terpisah supaya tidak konflik dgn form update -->
    <div class="card" style="margin-bottom:16px">
      <h3 style="margin:0 0 12px;font-size:14px;font-weight:800;color:var(--slate-600);text-transform:uppercase;letter-spacing:1px"><i class="fa-solid fa-upload"></i> Upload Foto Lapangan</h3>
      <form method="post" action="<?= url('master/upload-foto') ?>" enctype="multipart/form-data" style="display:flex;gap:8px;align-items:end;margin-bottom:14px" data-testid="form-upload-foto">
        <?= csrf_field() ?>
        <input type="hidden" name="master_id" value="<?= (int)$m['id'] ?>">
        <div class="field" style="flex:1;margin:0"><label>Pilih Foto (JPG/PNG, maks 10 MB)</label><input type="file" name="foto" required class="input" accept="image/*" data-testid="f-foto"></div>
        <div class="field" style="flex:1;margin:0"><label>Keterangan</label><input type="text" name="keterangan" class="input" placeholder="cth: Tampak depan bangunan"></div>
        <button class="btn btn-primary" type="submit" data-testid="btn-upload-foto"><i class="fa-solid fa-upload"></i> Upload Foto</button>
      </form>

      <?php if (empty($foto)): ?>
        <div style="text-align:center;color:var(--slate-500);padding:18px 0;font-size:13px">Belum ada foto.</div>
      <?php else: ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:14px">
          <?php foreach ($foto as $f): ?>
            <div style="border:1px solid var(--slate-200);border-radius:10px;overflow:hidden;background:#fff" data-testid="foto-<?= (int)$f['id'] ?>">
              <a href="<?= url('master/foto?id='.(int)$f['id']) ?>" target="_blank">
                <img src="<?= url('master/foto?id='.(int)$f['id']) ?>" alt="<?= e($f['nama_asli']) ?>" style="width:100%;height:160px;object-fit:cover;display:block">
              </a>
              <div style="padding:10px">
                <div style="font-size:12px;color:var(--slate-600);font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= e($f['nama_asli']) ?></div>
                <?php if (!empty($f['keterangan'])): ?><div style="font-size:11px;color:var(--slate-500);margin-top:2px"><?= e($f['keterangan']) ?></div><?php endif; ?>
                <form method="post" action="<?= url('master/delete-foto') ?>" onsubmit="return confirm('Hapus foto ini?')" style="margin-top:6px;text-align:right">
                  <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$f['id'] ?>"><input type="hidden" name="master_id" value="<?= (int)$m['id'] ?>">
                  <button class="btn btn-ghost btn-sm" style="color:var(--red-600);padding:2px 8px"><i class="fa-solid fa-trash"></i> Hapus</button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>
</main>

<?php if ($m['tipe'] === 'fisik'): ?>
<script>
(function(){
  const body = document.getElementById('fisik-body');
  const totalEl = document.getElementById('total-volume');

  function parseNum(s){ if(!s) return 0; s=String(s).replace(/\./g,'').replace(',','.'); return parseFloat(s)||0; }
  function fmt3(n){ return n.toLocaleString('id-ID',{minimumFractionDigits:3,maximumFractionDigits:3}); }
  function recalc(){
    let total = 0;
    body.querySelectorAll('.fisik-row').forEach((tr, idx) => {
      tr.querySelector('.rn').textContent = idx + 1;
      const [_, sta, jarak, l1, l2, tebal] = tr.querySelectorAll('input');
      const j = parseNum(jarak.value), a = parseNum(l1.value), b = parseNum(l2.value), t = parseNum(tebal.value);
      const vol = j * ((a + b) / 2) * t;
      tr.querySelector('.vol-cell').textContent = fmt3(vol);
      total += vol;
    });
    totalEl.textContent = fmt3(total);
  }

  function addRow(){
    const tr = document.createElement('tr');
    tr.className='fisik-row';
    tr.innerHTML = `
      <td class="rn"></td>
      <td><input type="text" name="sta[]" class="input"></td>
      <td><input type="text" name="jarak[]" class="input num-in" data-money></td>
      <td><input type="text" name="lebar_i[]" class="input num-in" data-money></td>
      <td><input type="text" name="lebar_ii[]" class="input num-in" data-money></td>
      <td><input type="text" name="tebal[]" class="input num-in" data-money></td>
      <td class="vol-cell num" style="font-weight:700;color:var(--emerald-700)">0,000</td>
      <td><input type="text" name="keterangan_baris[]" class="input"></td>
      <td><button type="button" class="btn btn-ghost btn-sm js-rm-row" style="color:var(--red-600)"><i class="fa-solid fa-trash"></i></button></td>`;
    body.appendChild(tr);
    recalc();
  }

  document.getElementById('btn-add-row').addEventListener('click', addRow);
  body.addEventListener('click', function(e){
    const rm = e.target.closest('.js-rm-row');
    if (rm) {
      const rows = body.querySelectorAll('.fisik-row');
      if (rows.length > 1) { rm.closest('tr').remove(); recalc(); }
      else { rm.closest('tr').querySelectorAll('input').forEach(i => i.value=''); recalc(); }
    }
  });
  body.addEventListener('input', function(e){
    if (e.target && e.target.classList.contains('num-in')) recalc();
  });
  recalc();
})();
</script>
<?php endif; ?>

<?php partial('foot'); ?>
