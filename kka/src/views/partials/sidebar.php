<?php
$auth = $GLOBALS['auth'];
$user = $auth->user();
$current = $_SERVER['REQUEST_URI'] ?? '';
function nav_active($needle, $current) {
    return str_contains($current, $needle) ? ' active' : '';
}
?>
<aside class="sidebar" data-testid="sidebar">
  <div class="brand">
    <div class="brand-logo">
      <img src="<?= asset('img/logo-rohil.png') ?>" alt="Rohil">
    </div>
    <div class="brand-text">
      <h1>KKA</h1>
      <p>Inspektorat Rokan Hilir</p>
    </div>

    <button class="sidebar-close" id="sidebarCloseBtn" aria-label="Tutup menu">
      <i class="fa-solid fa-xmark"></i>
    </button>
  </div>

  <nav class="nav">
    <a href="<?= url('dashboard') ?>" class="nav-item<?= nav_active('/dashboard', $current) ?>" data-testid="nav-dashboard">
      <i class="fa-solid fa-gauge"></i><span>Dashboard</span>
    </a>
    <a href="<?= url('sesi') ?>" class="nav-item<?= nav_active('/sesi', $current) ?>" data-testid="nav-sesi">
      <i class="fa-solid fa-clipboard-list"></i><span>Sesi Audit</span>
    </a>
    <a href="<?= url('desa') ?>" class="nav-item<?= nav_active('/desa', $current) ?>" data-testid="nav-desa">
      <i class="fa-solid fa-building-columns"></i><span>Manajemen Desa</span>
    </a>
    <a href="<?= url('rekap') ?>" class="nav-item<?= nav_active('/rekap', $current) ?>" data-testid="nav-rekap">
      <i class="fa-solid fa-chart-column"></i><span>Rekap per Desa</span>
    </a>
    <?php if ($auth->isAdmin()): ?>
      <div class="nav-section">Admin</div>
      <a href="<?= url('users') ?>" class="nav-item<?= nav_active('/users', $current) ?>" data-testid="nav-users">
        <i class="fa-solid fa-users-gear"></i><span>Manajemen Pengguna</span>
      </a>
    <?php endif; ?>
  </nav>

  <div class="sidebar-foot">
    <a href="<?= url('profile') ?>" class="userbox" data-testid="userbox">
      <div class="avatar"><?= e(strtoupper(mb_substr($user['nama'] ?? 'U', 0, 1))) ?></div>
      <div class="info">
        <div class="name"><?= e($user['nama']) ?></div>
        <div class="email"><?= e($user['email']) ?></div>
      </div>
    </a>
    <a class="logout" href="<?= url('logout') ?>" data-testid="logout-btn">
      <i class="fa-solid fa-right-from-bracket"></i> Keluar
    </a>
  </div>
</aside>