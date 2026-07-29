<?php $title = 'Edit Sesi Audit - KKA'; ?>
<?php partial('head', compact('title')); ?>
<?php partial('sidebar'); ?>

<main class="main" data-testid="page-sesi-edit">
  <div class="topbar">
    <div class="crumb">
      <i class="fa-solid fa-clipboard-list"></i>
      <a href="<?= url('sesi/show?id='.$sesi['id']) ?>" style="color:var(--slate-500)"><?= e(mb_strimwidth($sesi['objek_audit'],0,32,'…')) ?></a> /
      <b>Edit</b>
    </div>
  </div>
  <div class="content">
    <?php partial('flash'); ?>
    <div class="page-head"><div><h2>Edit Identitas Sesi Audit</h2></div></div>
    <div class="card" style="max-width:880px">
      <form method="post" action="<?= url('sesi/update') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= $sesi['id'] ?>">
        <div class="field">
          <label>Desa <span class="req">*</span></label>
          <select name="desa_id" required class="select">
            <?php foreach ($desa as $d): ?><option value="<?= $d['id'] ?>" <?= $sesi['desa_id']==$d['id']?'selected':'' ?>><?= e($d['nama']) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="field"><label>Objek Audit <span class="req">*</span></label><input type="text" name="objek_audit" required class="input" value="<?= e($sesi['objek_audit']) ?>"></div>
        <div class="row">
          <div class="field"><label>Semester</label>
            <select name="semester" class="select">
              <option value="1" <?= $sesi['semester']==1?'selected':'' ?>>Semester 1</option>
              <option value="2" <?= $sesi['semester']==2?'selected':'' ?>>Semester 2</option>
            </select>
          </div>
          <div class="field"><label>Tahun Anggaran</label><input type="number" name="tahun_anggaran" class="input" value="<?= (int)$sesi['tahun_anggaran'] ?>"></div>
        </div>
        <div class="field"><label>Bidang</label>
          <select name="bidang_id" class="select" id="bidang-sel">
            <?php foreach ($bidang as $b): ?><option value="<?= $b['id'] ?>" <?= $sesi['bidang_id']==$b['id']?'selected':'' ?>><?= e($b['nama']) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="field"><label>Sub Bidang</label>
          <select name="sub_bidang_id" class="select" id="sub-bidang-sel">
            <option value="">— (opsional) —</option>
            <?php foreach ($subBidang as $sb): ?><option value="<?= $sb['id'] ?>" <?= $sesi['sub_bidang_id']==$sb['id']?'selected':'' ?>><?= e($sb['nama']) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="field"><label>Kegiatan</label><input type="text" name="kegiatan" class="input" value="<?= e((string)$sesi['kegiatan']) ?>"></div>
        <div class="field">
          <label>Pagu Anggaran (Rp) <span class="req">*</span></label>
          <input type="text" name="pagu_anggaran" required class="input" data-money value="<?= number_format((float)$sesi['pagu_anggaran'],0,',','.') ?>" placeholder="0">
          <small style="color:var(--slate-500);font-size:12px">Total anggaran untuk kegiatan ini (nilai plafon).</small>
        </div>
        <div class="row">
          <div class="field"><label>No. KKA</label><input type="text" name="no_kka" class="input" value="<?= e((string)$sesi['no_kka']) ?>"></div>
          <div class="field"><label>Ref. KKA</label><input type="text" name="ref_kka" class="input" value="<?= e((string)$sesi['ref_kka']) ?>"></div>
        </div>
        <div class="row">
          <div class="field"><label>Dibuat Oleh</label><input type="text" name="dibuat_oleh" class="input" value="<?= e((string)$sesi['dibuat_oleh']) ?>"></div>
          <div class="field"><label>Tanggal Dibuat</label><input type="date" name="tanggal_dibuat" class="input" value="<?= e((string)$sesi['tanggal_dibuat']) ?>"></div>
        </div>
        <div class="row">
          <div class="field"><label>Direview Oleh (Ketua Tim)</label><input type="text" name="direview_oleh" class="input" value="<?= e((string)$sesi['direview_oleh']) ?>"></div>
          <div class="field"><label>Tanggal Review</label><input type="date" name="tanggal_review" class="input" value="<?= e((string)$sesi['tanggal_review']) ?>"></div>
        </div>
        <div class="row">
          <div class="field"><label>Diketahui Oleh (Dalnis)</label><input type="text" name="dievaluasi_oleh" class="input" value="<?= e((string)$sesi['dievaluasi_oleh']) ?>"></div>
          <div class="field"><label>Tanggal Diketahui</label><input type="date" name="tanggal_evaluasi" class="input" value="<?= e((string)$sesi['tanggal_evaluasi']) ?>"></div>
        </div>
        <div class="row">
          <div class="field"><label>Kesimpulan</label><textarea name="kesimpulan" class="textarea"><?= e((string)$sesi['kesimpulan']) ?></textarea></div>
          <div class="field"><label>Sumber Data</label><textarea name="sumber_data" class="textarea"><?= e((string)$sesi['sumber_data']) ?></textarea></div>
        </div>
        <?php if (!empty($canShare) && !empty($auditors)): ?>
        <div class="field">
          <label><i class="fa-solid fa-users"></i> Bagikan ke Auditor Lain</label>
          <p style="font-size:12px;color:var(--slate-500);margin:2px 0 8px">Auditor yang dicentang dapat <b>melihat &amp; mengerjakan</b> sesi ini. Admin selalu melihat semua sesi.</p>
          <input type="hidden" name="manage_shares" value="1">
          <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:6px;max-height:220px;overflow:auto;border:1px solid var(--slate-200);border-radius:8px;padding:10px">
            <?php foreach ($auditors as $a): ?>
              <label style="display:flex;align-items:center;gap:8px;font-weight:500;cursor:pointer">
                <input type="checkbox" name="shared_users[]" value="<?= (int)$a['id'] ?>" <?= in_array((int)$a['id'], $sharedIds, true) ? 'checked' : '' ?>>
                <span><?= e($a['nama']) ?><?php if (!empty($a['jabatan'])): ?> <small style="color:var(--slate-500)">— <?= e($a['jabatan']) ?></small><?php endif; ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>
        <div style="display:flex;justify-content:flex-end;gap:8px">
          <a href="<?= url('sesi/show?id='.$sesi['id']) ?>" class="btn btn-outline">Batal</a>
          <button class="btn btn-primary" type="submit"><i class="fa-solid fa-save"></i> Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>
</main>
<script>
  document.getElementById('bidang-sel').addEventListener('change', async function(){
    const v = this.value, sel = document.getElementById('sub-bidang-sel');
    sel.innerHTML='<option value="">Memuat…</option>';
    const r = await fetch('<?= url('sesi/sub-bidang') ?>?bidang_id='+v);
    const d = await r.json();
    sel.innerHTML='<option value="">— (opsional) —</option>' + d.map(x=>`<option value="${x.id}">${x.nama}</option>`).join('');
  });
</script>
<?php partial('foot'); ?>
