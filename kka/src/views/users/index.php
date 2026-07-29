<?php $title = 'Manajemen Pengguna - KKA'; ?>
<?php partial('head', compact('title')); ?>
<?php partial('sidebar'); ?>

<main class="main" data-testid="page-users">
  <div class="topbar">
    <div class="crumb"><i class="fa-solid fa-users-gear"></i> <b>Manajemen Pengguna</b></div>
    <div class="topbar-right"><?= count($users) ?> akun</div>
  </div>
  <div class="content">
    <?php partial('flash'); ?>
    <div class="page-head">
      <div><h2>Manajemen Pengguna</h2><p>Kelola akun Auditor dan Administrator KKA.</p></div>
      <button class="btn btn-primary" onclick="document.getElementById('m-user').style.display='flex'" data-testid="btn-add-user"><i class="fa-solid fa-user-plus"></i> Tambah Pengguna</button>
    </div>

    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr><th>Nama</th><th>Email</th><th>NIP</th><th>Jabatan</th><th>Role</th><th>Status</th><th>Aksi</th></tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
            <tr data-testid="user-row-<?= $u['id'] ?>">
              <td style="font-weight:600"><?= e($u['nama']) ?></td>
              <td><?= e($u['email']) ?></td>
              <td><?= e($u['nip'] ?: '-') ?></td>
              <td><?= e($u['jabatan'] ?: '-') ?></td>
              <td><span class="badge role-<?= e($u['role']) ?>"><?= e(strtoupper($u['role'])) ?></span></td>
              <td><?= $u['is_active']?'<span class="badge">Aktif</span>':'<span class="badge slate">Nonaktif</span>' ?></td>
              <td>
                <button class="btn btn-outline btn-sm" onclick='openEdit(<?= json_encode($u, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>)' data-testid="edit-user-<?= $u['id'] ?>"><i class="fa-solid fa-pen"></i></button>
                <?php if ($u['id'] !== $auth->id()): ?>
                <form method="post" action="<?= url('users/delete') ?>" onsubmit="return confirm('Hapus pengguna <?= e($u['nama']) ?>?')" style="display:inline">
                  <?= csrf_field() ?><input type="hidden" name="id" value="<?= $u['id'] ?>">
                  <button class="btn btn-outline btn-sm" style="color:var(--red-600);border-color:#fecaca"><i class="fa-solid fa-trash"></i></button>
                </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<div class="modal-bg" id="m-user" style="display:none">
  <div class="modal" onclick="event.stopPropagation()">
    <div class="modal-head"><h3>Tambah Pengguna</h3><button class="x" onclick="document.getElementById('m-user').style.display='none'">×</button></div>
    <form method="post" action="<?= url('users/store') ?>" data-testid="form-add-user">
      <?= csrf_field() ?>
      <div class="modal-body">
        <div class="field"><label>Nama Lengkap <span class="req">*</span></label><input type="text" name="nama" required class="input"></div>
        <div class="field"><label>Email <span class="req">*</span></label><input type="email" name="email" required class="input"></div>
        <div class="row">
          <div class="field"><label>NIP</label><input type="text" name="nip" class="input"></div>
          <div class="field"><label>Jabatan</label><input type="text" name="jabatan" class="input" placeholder="cth: Auditor Muda"></div>
        </div>
        <div class="row">
          <div class="field"><label>Role <span class="req">*</span></label>
            <select name="role" required class="select"><option value="auditor">Auditor</option><option value="admin">Administrator</option></select>
          </div>
          <div class="field"><label>Password <span class="req">*</span></label><input type="password" name="password" required class="input" minlength="6"></div>
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-outline" onclick="document.getElementById('m-user').style.display='none'">Batal</button>
        <button class="btn btn-primary" type="submit"><i class="fa-solid fa-save"></i> Simpan</button>
      </div>
    </form>
  </div>
</div>

<div class="modal-bg" id="m-edit" style="display:none">
  <div class="modal" onclick="event.stopPropagation()">
    <div class="modal-head"><h3>Edit Pengguna</h3><button class="x" onclick="document.getElementById('m-edit').style.display='none'">×</button></div>
    <form method="post" action="<?= url('users/update') ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="id" id="e-id">
      <div class="modal-body">
        <div class="field"><label>Nama</label><input type="text" name="nama" id="e-nama" required class="input"></div>
        <div class="row">
          <div class="field"><label>NIP</label><input type="text" name="nip" id="e-nip" class="input"></div>
          <div class="field"><label>Jabatan</label><input type="text" name="jabatan" id="e-jab" class="input"></div>
        </div>
        <div class="row">
          <div class="field"><label>Role</label>
            <select name="role" id="e-role" class="select"><option value="auditor">Auditor</option><option value="admin">Administrator</option></select>
          </div>
          <div class="field"><label>Status</label>
            <select name="is_active" id="e-act" class="select"><option value="1">Aktif</option><option value="0">Nonaktif</option></select>
          </div>
        </div>
        <div class="field"><label>Password Baru <small style="color:var(--slate-500)">(kosongkan jika tidak diganti)</small></label><input type="password" name="password" class="input" minlength="6"></div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-outline" onclick="document.getElementById('m-edit').style.display='none'">Batal</button>
        <button class="btn btn-primary" type="submit"><i class="fa-solid fa-save"></i> Simpan</button>
      </div>
    </form>
  </div>
</div>

<script>
function openEdit(u){
  document.getElementById('e-id').value = u.id;
  document.getElementById('e-nama').value = u.nama;
  document.getElementById('e-nip').value = u.nip || '';
  document.getElementById('e-jab').value = u.jabatan || '';
  document.getElementById('e-role').value = u.role;
  document.getElementById('e-act').value = u.is_active;
  document.getElementById('m-edit').style.display = 'flex';
}
</script>

<?php partial('foot'); ?>
