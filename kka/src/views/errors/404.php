<?php $title = '404 - Halaman Tidak Ditemukan'; ?>
<?php partial('head', compact('title')); ?>
<div style="text-align:center;padding:80px 24px;font-family:'Plus Jakarta Sans',sans-serif">
  <h1 style="font-size:72px;margin:0;color:var(--emerald-700)">404</h1>
  <p style="color:var(--slate-500);font-size:16px">Halaman tidak ditemukan.</p>
  <a href="<?= url('dashboard') ?>" class="btn btn-primary" style="margin-top:14px">Kembali ke Dashboard</a>
</div>
</body></html>
