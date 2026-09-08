<?php
// admin/ad_sidebar.php — Navigasi panel kasir (profesional, minim ikon)
// $koneksi, $mod, $judulMod tersedia dari ad_index.php
$namaKasir = $_SESSION['nama_lengkap'] ?? 'Kasir';

// Badge pesanan menunggu konfirmasi
$jmlPending = 0;
$rs = @mysqli_query($koneksi, "SELECT COUNT(*) AS c FROM orders WHERE status_pesanan='pending'");
if ($rs) { $r = mysqli_fetch_assoc($rs); $jmlPending = (int)($r['c'] ?? 0); }

function nav_active($key, $mod) { return $key === $mod ? 'active' : ''; }
$inisialKasir = strtoupper(substr(trim($namaKasir), 0, 1));
?>
<aside class="sidebar">
  <div class="sbrand">
    <img class="sbrand-photo" src="../gambar/menu-pancong.svg" alt="Pancong Lumer" onerror="this.remove()">
    <span class="sbrand-txt"><b>Pancong Lumer</b><small>Panel Kasir &middot; Selindung Baru</small></span>
  </div>
  <div class="snav-label">Operasional</div>
  <a class="slink <?php echo nav_active('beranda',$mod); ?>" href="ad_index.php?mod=beranda"><span class="slink-txt">Dasbor &amp; POS</span></a>
  <a class="slink <?php echo nav_active('pesanan',$mod); ?>" href="ad_index.php?mod=pesanan"><span class="slink-txt">Live Orders</span><?php if($jmlPending>0) echo '<span class="cnt">'.$jmlPending.'</span>'; ?></a>
  <div class="snav-label">Katalog</div>
  <a class="slink <?php echo nav_active('menu',$mod); ?>" href="ad_index.php?mod=menu"><span class="slink-txt">Kelola Menu</span></a>
  <a class="slink <?php echo nav_active('topping',$mod); ?>" href="ad_index.php?mod=topping"><span class="slink-txt">Topping &amp; Rekomendasi</span></a>
  <a class="slink <?php echo nav_active('laporan',$mod); ?>" href="ad_index.php?mod=laporan"><span class="slink-txt">Riwayat &amp; Omzet</span></a>
  <div style="flex:1"></div>
  <div class="snav-label">Lainnya</div>
  <a class="slink" href="../index.php" target="_blank" rel="noopener"><span class="slink-txt">Lihat Web Pelanggan</span></a>
  <a class="slink" href="../logout.php"><span class="slink-txt">Keluar (<?php echo e(strtok($namaKasir,' ')); ?>)</span></a>
</aside>

<div class="admin-main">
  <div class="topbar">
    <b class="topbar-title"><?php echo e($judulMod ?? 'Dasbor'); ?></b>
    <span class="pill pill-open"><span class="status-dot"></span>Gerai Buka &middot; 10.00&ndash;22.00</span>
    <span style="flex:1"></span>
    <span class="clock" id="jamLive">&hellip;</span>
    <span class="topbar-user"><span class="avatar"><?php echo e($inisialKasir ?: 'K'); ?></span><span style="max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?php echo e($namaKasir); ?></span></span>
  </div>
  <div class="admin-body">
