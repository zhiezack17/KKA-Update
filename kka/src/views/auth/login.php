<?php $title = 'Login - KKA Inspektorat Rokan Hilir'; $body_class = 'login'; ?>
<?php partial('head', compact('title','body_class')); ?>

<div class="login-card" data-testid="login-card">
  <div class="login-left">
    <div class="logos">
      <div class="lg"><img src="<?= asset('img/logo-rohil.png') ?>" alt="Rohil"></div>
      <div class="lg"><img src="<?= asset('img/logo-inspektorat.png') ?>" alt="Inspektorat"></div>
      <div class="txt">
        <b>Pemerintah Kabupaten Rokan Hilir</b>
        Inspektorat Daerah
      </div>
    </div>

    <div class="hero">
      <h2>Kertas Kerja <span class="gold">Audit Digital</span><br>Inspektorat Rohil</h2>
      <p>Sistem dokumentasi audit pengeluaran keuangan kepenghuluan berbasis digital — aman, terstruktur, dan terintegrasi.</p>
      <div class="feats">
        <div><i class="fa-solid fa-shield-halved"></i> Akses internal khusus auditor & admin</div>
        <div><i class="fa-solid fa-file-pdf"></i> Cetak KKA siap tanda tangan</div>
        <div><i class="fa-solid fa-chart-column"></i> Rekapitulasi anggaran vs realisasi per desa</div>
      </div>
    </div>

    <div class="foot">© <?= date('Y') ?> Inspektorat Kabupaten Rokan Hilir · arsipdigital-inspektorat.com</div>
  </div>

  <div class="login-right">
    <h1>Masuk ke Akun</h1>
    <p class="sub">Gunakan kredensial yang diberikan oleh Administrator.</p>

    <?php partial('flash'); ?>

    <form method="post" action="<?= url('login') ?>" data-testid="login-form" autocomplete="off">
      <?= csrf_field() ?>
      <div class="field">
        <label>Email <span class="req">*</span></label>
        <input type="email" name="email" required class="input" placeholder="nama@inspektorat-rohil.go.id"
               value="<?= e($_POST['email'] ?? '') ?>" autofocus data-testid="login-email">
      </div>
      <div class="field">
        <label>Password <span class="req">*</span></label>
        <input type="password" name="password" required class="input" placeholder="••••••••" data-testid="login-password">
      </div>
      <button type="submit" class="btn btn-primary" data-testid="login-submit">
        <i class="fa-solid fa-right-to-bracket"></i> Masuk
      </button>
    </form>

    <div class="help">
      Lupa password? Hubungi Administrator kantor.
    </div>
  </div>
</div>

<?php partial('foot'); ?>
