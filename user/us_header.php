<?php
// user/us_header.php — Top App Bar ala Stitch + search + badge keranjang
// Dipanggil dari index.php (session sudah start).
$isLogin = isset($_SESSION['user_id']);
$namaUser = $isLogin ? $_SESSION['nama_lengkap'] : '';
$active = $page ?? 'beranda';
$inisial = $isLogin ? strtoupper(substr(trim($namaUser), 0, 1)) : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pancong Lumer — Selindung Baru</title>
<link rel="stylesheet" href="css/style.css?v=<?php echo filemtime(__DIR__ . '/../css/style.css'); ?>">
<script src="js/validasi.js?v=<?php echo filemtime(__DIR__ . '/../js/validasi.js'); ?>" defer></script>
</head>
<body>
<header class="navbar">
  <div class="nav-inner">
    <a class="brand" href="index.php">
      <span class="brand-mark" style="font-family:var(--serif);font-style:italic;font-weight:700;font-size:20px">P</span>
      <span class="brand-text">
        <span class="brand-title">Pancong Lumer
          <span class="open-badge"><span class="status-dot"></span>BUKA • 10–22</span>
        </span>
        <span class="brand-sub">Selindung Baru &middot; Pangkalpinang</span>
      </span>
    </a>
    <div class="hsearch">
      <input id="qDesktop" class="js-search" placeholder="Cari choco, keju, topping…" oninput="cariMenu(this.value);syncSearch(this.value,this)" autocomplete="off">
      <kbd>/</kbd>
    </div>
    <div class="nav-actions">
      <a class="antrean-btn" href="index.php?page=antrean">Antrean</a>
      <button type="button" class="cart-btn" onclick="location.href='index.php?page=checkout'">Keranjang <span class="cart-label" id="cartTotalHead">Rp0</span><span id="cartCount" class="cart-badge" style="display:none">0</span></button>
      <?php if ($isLogin): ?>
        <span class="user-chip" title="<?php echo e($namaUser); ?>"><span class="avatar"><?php echo e($inisial ?: 'P'); ?></span><span style="max-width:90px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?php echo e(strtok($namaUser, ' ')); ?></span></span>
        <a class="btn btn-ghost btn-sm" href="logout.php">Keluar</a>
      <?php else: ?>
        <a class="btn btn-primary btn-sm" href="login.php">Masuk</a>
      <?php endif; ?>
    </div>
  </div>
  <div class="container">
    <div class="msearch">
      <input id="qMobile" class="js-search" placeholder="Cari menu lumer…" oninput="cariMenu(this.value);syncSearch(this.value,this)" autocomplete="off">
    </div>
  </div>
</header>
<main class="container page">
