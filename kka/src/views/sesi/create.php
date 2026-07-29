<?php $title = 'Buat Sesi Audit Baru - KKA'; ?>
<?php partial('head', compact('title')); ?>
<?php partial('sidebar'); ?>

<main class="main" data-testid="page-sesi-create">
  <div class="topbar">
    <div class="crumb">
      <i class="fa-solid fa-clipboard-list"></i>
      <a href="<?= url('sesi') ?>" style="color:var(--slate-500)">Sesi Audit</a> /
      <b>Buat Sesi Baru</b>
    </div>
  </div>

  <div class="content">
    <?php partial('flash'); ?>
    <div class="page-head">
      <div>
        <h2>Buat Sesi Audit Baru</h2>
        <p>Isi identitas KKA. Rincian belanja & lampiran dokumen ditambahkan setelah sesi disimpan.</p>
      </div>
    </div>

    <div class="card" style="max-width:880px">
      <form method="post" action="<?= url('sesi/store') ?>" data-testid="form-sesi">
        <?= csrf_field() ?>
        <div class="field">
          <label>Pilih Desa / Kepenghuluan <span class="req">*</span></label>
          <select name="desa_id" required class="select" data-testid="f-desa">
            <option value="">— Pilih Desa —</option>
            <?php foreach ($desa as $d): ?><option value="<?= $d['id'] ?>"><?= e($d['nama']) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="field">
          <label>Objek Audit <span class="req">*</span></label>
          <input type="text" name="objek_audit" required class="input" placeholder="cth: Kantor Kepenghuluan Salak" data-testid="f-objek">
        </div>
        <div class="row">
          <div class="field">
            <label>Masa Audit (Semester) <span class="req">*</span></label>
            <select name="semester" required class="select">
              <option value="1">Semester 1</option>
              <option value="2">Semester 2</option>
            </select>
          </div>
          <div class="field">
            <label>Tahun Anggaran <span class="req">*</span></label>
            <input type="number" name="tahun_anggaran" required class="input" value="<?= (int)date('Y') ?>" min="2020" max="2099">
          </div>
        </div>

        <div class="field">
          <label>Bidang <span class="req">*</span></label>
          <select name="bidang_id" required class="select" id="bidang-sel" data-testid="f-bidang">
            <option value="">— Pilih Bidang —</option>
            <?php foreach ($bidang as $b): ?><option value="<?= $b['id'] ?>"><?= e($b['nama']) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="field">
          <label>Sub Bidang</label>
          <select name="sub_bidang_id" class="select" id="sub-bidang-sel">
            <option value="">— Pilih Bidang dulu —</option>
          </select>
        </div>
        <div class="field">
          <label>Kegiatan</label>
          <input type="text" name="kegiatan" class="input" placeholder="cth: 1. Honorarium">
        </div>
        <div class="field">
          <label>Pagu Anggaran (Rp) <span class="req">*</span></label>
          <input type="text" name="pagu_anggaran" required class="input" data-money placeholder="0" data-testid="f-pagu-sesi">
          <small style="color:var(--slate-500);font-size:12px">Total anggaran untuk kegiatan ini. Rincian belanja di bawah bisa terdiri dari beberapa item dan tidak akan menambah pagu.</small>
        </div>

        <div class="row">
          <div class="field">
            <label>No. KKA</label>
            <input type="text" name="no_kka" class="input" placeholder="01/KKA/...">
          </div>
          <div class="field">
            <label>Ref. PKA</label>
            <input type="text" name="ref_kka" class="input" placeholder="Ref...">
          </div>
        </div>

        <div class="row">
          <div class="field">
            <label>Dibuat Oleh (Auditor)</label>
            <input type="text" name="dibuat_oleh" class="input" placeholder="Nama auditor" value="<?= e($auth->user()['nama']) ?>">
          </div>
          <div class="field">
            <label>Tanggal Dibuat</label>
            <input type="date" name="tanggal_dibuat" class="input" value="<?= date('Y-m-d') ?>">
          </div>
        </div>
        <div class="row">
          <div class="field">
            <label>Direview Oleh (Ketua Tim)</label>
            <input type="text" name="direview_oleh" class="input" placeholder="Nama Ketua Tim">
          </div>
          <div class="field">
            <label>Tanggal Review</label>
            <input type="date" name="tanggal_review" class="input">
          </div>
        </div>
        <div class="row">
          <div class="field">
            <label>Diketahui Oleh (Dalnis / Pengendali Teknis)</label>
            <input type="text" name="dievaluasi_oleh" class="input" placeholder="Nama Dalnis / Pengendali Teknis">
          </div>
          <div class="field">
            <label>Tanggal Diketahui</label>
            <input type="date" name="tanggal_evaluasi" class="input">
          </div>
        </div>

        <?php if (!empty($auditors)): ?>
        <div class="field">
          <label><i class="fa-solid fa-users"></i> Bagikan ke Auditor Lain (opsional)</label>
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

        <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:14px">
          <a href="<?= url('sesi') ?>" class="btn btn-outline">Batal</a>
          <button type="submit" class="btn btn-primary" data-testid="btn-simpan-sesi"><i class="fa-solid fa-save"></i> Simpan Sesi</button>
        </div>
      </form>
    </div>
  </div>
</main>

<script>
  document.getElementById('bidang-sel').addEventListener('change', async function(){
    const v = this.value, sel = document.getElementById('sub-bidang-sel');
    sel.innerHTML = '<option value="">— Memuat... —</option>';
    if (!v) { sel.innerHTML = '<option value="">— Pilih Bidang dulu —</option>'; return; }
    try {
      const r = await fetch('<?= url('sesi/sub-bidang') ?>?bidang_id=' + v);
      const d = await r.json();
      sel.innerHTML = '<option value="">— (opsional) —</option>' + d.map(x => `<option value="${x.id}">${x.nama}</option>`).join('');
    } catch(e){ sel.innerHTML = '<option value="">Gagal memuat</option>'; }
  });
</script>

<?php partial('foot'); ?>
