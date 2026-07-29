<?php $title = $title ?? 'KKA - Inspektorat Rokan Hilir'; ?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($title) ?></title>
<link rel="icon" href="<?= asset('img/logo-rohil.png') ?>" type="image/png">
<link rel="stylesheet" href="<?= asset('css/style.css') ?>?v=5">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="<?= e($body_class ?? 'app') ?>">
<div class="mobile-backdrop" id="mobileBackdrop"></div>
