<?php
// user/us_footer.php — Footer + FAB cart + bottom nav (profesional, minim ikon)
$active = $active ?? ($page ?? 'beranda');
$isLoginF = isset($_SESSION['user_id']);
?>
</main>

<!-- Floating cart bar (mobile) -->
<div class="fab-bar" id="fabBar">
  <div class="fab-info">
    <span class="fab-ic">P</span>
    <span><small id="fabCount">0 item</small><b id="fabTotal">Rp0</b></span>
  </div>
  <button type="button" onclick="location.href='index.php?page=checkout'">Lihat keranjang</button>
</div>

<!-- Bottom nav (mobile) -->
<nav class="bottom-nav" aria-label="Navigasi utama">
  <a href="index.php?page=beranda" class="<?php echo $active==='beranda'?'active':''; ?>">Menu</a>
  <a href="index.php?page=checkout" class="<?php echo $active==='checkout'?'active':''; ?>">Keranjang<span class="ndot" id="navDot" style="display:none"></span></a>
  <a href="index.php?page=antrean" class="<?php echo $active==='antrean'?'active':''; ?>">Antrean</a>
  <?php if ($isLoginF): ?>
    <a href="logout.php">Keluar</a>
  <?php else: ?>
    <a href="login.php">Masuk</a>
  <?php endif; ?>
</nav>

<footer class="footer">
  <div class="container">
    <div style="display:flex;gap:12px;align-items:flex-start;min-width:220px;flex:1">
      <img src="gambar/menu-pancong.svg" alt="Pancong lumer" loading="lazy" style="width:64px;flex:none;border-radius:12px" onerror="this.remove()">
      <div>
        <h4>Pancong Lumer</h4>
        <p>Gerai Selindung Baru, Pangkalpinang.<br>Pancong lumer manis &amp; gurih,<br>dimasak dadakan di atas loyang panas.</p>
      </div>
    </div>
    <div>
      <h4>Jam Operasional</h4>
      <ul>
        <li>Senin &ndash; Minggu</li>
        <li>10.00 &ndash; 22.00 WIB</li>
      </ul>
    </div>
    <div>
      <h4>Cara Pesan</h4>
      <ul>
        <li>1. Pilih menu &amp; topping</li>
        <li>2. Checkout &amp; bayar QRIS / Tunai</li>
        <li>3. Terima tiket PC-YYYYMMDD-XXX</li>
        <li>4. Pantau status panggangan</li>
      </ul>
    </div>
  </div>
  <div class="copy">&copy; 2026 Pancong Lumer Selindung Baru &middot; Tugas Capstone Proyek TI</div>
</footer>
</body>
</html>
