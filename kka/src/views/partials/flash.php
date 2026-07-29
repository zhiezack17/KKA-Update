<?php $flashes = flash_pull(); ?>
<?php if (!empty($flashes)): ?>
  <div class="flash-stack" data-testid="flash-stack">
    <?php foreach ($flashes as $f): ?>
      <div class="flash flash-<?= e($f['type']) ?>">
        <i class="fa-solid <?= $f['type']==='success'?'fa-circle-check':($f['type']==='error'?'fa-circle-exclamation':'fa-circle-info') ?>"></i>
        <span><?= e($f['msg']) ?></span>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
