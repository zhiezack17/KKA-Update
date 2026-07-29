<?php $title = 'Profil Saya - KKA'; $u = $auth->user(); ?>
<?php partial('head', compact('title')); ?>
<?php partial('sidebar'); ?>
<main class="main">
  <div class="topbar"><div class="crumb"><i class="fa-solid fa-user"></i> <b>Profil Saya</b></div></div>
  <div class="content">
    <?php partial('flash'); ?>
    <div class="page-head"><div><h2>Profil & Keamanan Akun</h2></div></div>
    <div class="card" style="max-width:640px">
      <form method="post" action="<?= url('profile/update') ?>">
        <?= csrf_field() ?>
        <div class="field"><label>Email</label><input type="email" value="<?= e($u['email']) ?>" disabled class="input" style="background:var(--slate-100)"></div>
        <div class="field"><label>Nama Lengkap</label><input type="text" name="nama" required class="input" value="<?= e($u['nama']) ?>"></div>
        <div class="row">
          <div class="field"><label>NIP</label><input type="text" name="nip" class="input" value="<?= e((string)$u['nip']) ?>"></div>
          <div class="field"><label>Jabatan</label><input type="text" name="jabatan" class="input" value="<?= e((string)$u['jabatan']) ?>"></div>
        </div>
        <div class="section-title"><i class="fa-solid fa-lock"></i> Ganti Password (opsional)</div>
        <div class="row">
          <div class="field"><label>Password Lama</label><input type="password" name="old_password" class="input"></div>
          <div class="field"><label>Password Baru</label><input type="password" name="new_password" class="input" minlength="6"></div>
        </div>
        <div style="text-align:right"><button class="btn btn-primary" type="submit"><i class="fa-solid fa-save"></i> Simpan</button></div>
      </form>
    </div>
  </div>
</main>
<?php partial('foot'); ?>
