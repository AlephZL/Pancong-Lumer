<?php
// user/us_beranda.php — Katalog ala Stitch + Modal Customizer + Mini-cart aside
// $koneksi tersedia dari index.php

// --- Ambil kategori (kecuali topping) ---
$categories = [];
$res = mysqli_query($koneksi, "SELECT id, kode_kategori, nama_kategori FROM categories ORDER BY id ASC");
while ($r = mysqli_fetch_assoc($res)) { $categories[] = $r; }

// --- Ambil produk + kode kategori ---
$products = [];
$q = "SELECT p.id, p.category_id, p.nama_menu, p.deskripsi, p.harga_dasar, p.gambar, p.is_available,
             c.kode_kategori, c.nama_kategori
      FROM products p JOIN categories c ON c.id = p.category_id
      ORDER BY c.id ASC, p.id ASC";
$res = mysqli_query($koneksi, $q);
while ($r = mysqli_fetch_assoc($res)) { $products[] = $r; }

// --- Ambil topping aktif ---
$toppings = [];
$res = mysqli_query($koneksi, "SELECT id, nama_topping, harga, is_available FROM toppings ORDER BY id ASC");
while ($r = mysqli_fetch_assoc($res)) { $toppings[] = $r; }

// --- Ambil aturan rekomendasi (join nama+hrg) ---
$rekom = []; // base_id => [ {id,nama,harga,alasan} ]
$q = "SELECT r.base_topping_id, r.pesan_alasan,
             t.id AS rid, t.nama_topping AS rnama, t.harga AS rhrg, t.is_available
      FROM topping_recommendations r
      JOIN toppings t ON t.id = r.recommended_topping_id
      ORDER BY r.base_topping_id ASC";
$res = mysqli_query($koneksi, $q);
while ($r = mysqli_fetch_assoc($res)) {
    if ((int)$r['is_available'] !== 1) continue;
    $bid = (string)$r['base_topping_id'];
    if (!isset($rekom[$bid])) $rekom[$bid] = [];
    $rekom[$bid][] = [
        'id' => (string)$r['rid'],
        'nama' => $r['rnama'],
        'harga' => (int)$r['rhrg'],
        'alasan' => $r['pesan_alasan'],
    ];
}

// Visual menu: foto asli bila ada, SVG generik bila belum, monogram elegan terakhir
function gambar_menu($nama, $file) {
    if (!empty($file)) return ['file', 'gambar/' . rawurlencode($file)];
    $n = strtolower($nama);
    if (strpos($n, 'roll') !== false || strpos($n, 'jumbo') !== false) return ['svg', 'gambar/menu-roll.svg'];
    return ['svg', 'gambar/menu-pancong.svg'];
}
function monogram_menu($nama) {
    $kata = preg_split('/\s+/', trim($nama));
    $m = '';
    foreach (array_slice($kata, 0, 2) as $k) $m .= strtoupper(substr($k, 0, 1));
    return $m !== '' ? $m : 'PL';
}
function kelas_menu($nama) {
    $n = strtolower($nama);
    return (strpos($n, 'roll') !== false) ? 'roll' : 'pancong';
}

// Badge ala Stitch per produk
function badge_menu($nama, $stok, $idx) {
    if (!$stok) return ['Habis', 'bd-grey'];
    $n = strtolower($nama);
    if ($idx === 0 || strpos($n, 'choco') !== false) return ['Best Seller', 'bd-sec'];
    if (strpos($n, 'jumbo') !== false) return ['Hemat Sharing', 'bd-amber'];
    if (strpos($n, 'taro') !== false || strpos($n, 'matcha') !== false) return ['Varian Baru', 'bd-pink'];
    if (strpos($n, 'roll') !== false) return ['Crispy Gurih', 'bd-grey'];
    return ['Lumer Favorit', 'bd-amber'];
}

?>

<!-- Search hidden sinkron header (JS hook lama #q tetap ada) -->
<input type="hidden" id="q" value="">

<!-- ============ DUAL PROMO HERO (bergambar) ============ -->
<section class="hero-grid">
  <div class="banner b1">
    <img class="banner-photo" src="gambar/hero-pancong.svg" alt="Pancong lumer di atas loyang panas" loading="eager" onerror="this.closest('.banner').classList.add('no-photo');this.remove()">
    <div class="banner-body">
      <span class="banner-flag">Promo hari ini</span>
      <h2>Beli 3 Loyang, Gratis 1 Topping</h2>
      <p>Pancong setengah matang lumer + topping bebas. Syarat &amp; ketentuan berlaku di kasir.</p>
    </div>
    <div class="banner-cta"><a href="#katalog">Klaim promo</a><a class="ghost" href="index.php?page=checkout">Lihat keranjang</a></div>
  </div>
  <div class="banner b2">
    <img class="banner-photo" src="gambar/hero-roll.svg" alt="Chicken roll crispy dengan saus" loading="lazy" onerror="this.closest('.banner').classList.add('no-photo');this.remove()">
    <div class="banner-body">
      <span class="banner-flag">Varian baru</span>
      <h2>Taro &amp; Matcha Red Crumb Lumer</h2>
      <p>Krim manis seimbang + crumble renyah, dipanggang dadakan.</p>
    </div>
    <div class="banner-cta"><a href="#katalog">Coba sekarang</a></div>
  </div>
</section>

<!-- ============ STICKY CATEGORY CHIPS ============ -->
<nav class="catnav" id="katalog" aria-label="Kategori menu">
  <button class="chip active" data-kat="semua" onclick="filterKategori('semua', this)">Semua</button>
  <?php foreach ($categories as $c): if ($c['kode_kategori']==='topping') continue; ?>
    <button class="chip" data-kat="<?php echo e($c['kode_kategori']); ?>" onclick="filterKategori('<?php echo e($c['kode_kategori']); ?>', this)"><?php echo e($c['nama_kategori']); ?></button>
  <?php endforeach; ?>
</nav>

<div class="section-head">
  <div>
    <span class="eyebrow">Katalog gerai</span>
    <h2>Daftar Menu Favorit <span class="count-note" id="menuCount">(<?php echo count($products); ?> Pilihan)</span></h2>
    <p>Pilih tombol tambah untuk atur porsi, kematangan &amp; topping.</p>
  </div>
  <span class="sort-link">Urutkan: Terlaris</span>
</div>

<!-- ============ SPLIT: KATALOG + MINI-CART ============ -->
<div class="split">
  <div>
    <?php if (empty($products)): ?>
      <div class="empty"><p><b>Belum ada menu hari ini.</b><br><span style="font-size:13px">Silakan kembali lagi — loyang selalu panas setiap 10.00&ndash;22.00.</span></p></div>
    <?php else: ?>
    <div class="grid-menu">
      <?php foreach ($products as $idx => $p):
        $stok = ((int)$p['is_available'] === 1);
        $isRoll = ($p['kode_kategori'] === 'chicken_roll');
        [$jenisImg, $imgSrc] = gambar_menu($p['nama_menu'], $p['gambar'] ?? '');
        [$badgeTxt, $badgeCls] = badge_menu($p['nama_menu'], $stok, $idx);
        $mono = monogram_menu($p['nama_menu']);
        $klsMenu = kelas_menu($p['nama_menu']);
      ?>
      <article class="card <?php echo $stok?'':'stock-off'; ?>" data-catcode="<?php echo e($p['kode_kategori']); ?>" data-nama="<?php echo e(strtolower($p['nama_menu'])); ?>">
        <div class="card-img">
          <span class="card-badge <?php echo $badgeCls; ?>"><?php echo e($badgeTxt); ?></span>
          <?php if ($jenisImg === 'file'): ?>
            <img src="<?php echo e($imgSrc); ?>" alt="<?php echo e($p['nama_menu']); ?>" loading="lazy" onerror="this.outerHTML='<span class=\'img-fallback <?php echo $klsMenu; ?>\'><?php echo e($mono); ?></span>'">
          <?php else: ?>
            <img src="<?php echo e($imgSrc); ?>" alt="<?php echo e($p['nama_menu']); ?>" loading="lazy" onerror="this.outerHTML='<span class=\'img-fallback <?php echo $klsMenu; ?>\'><?php echo e($mono); ?></span>'">
          <?php endif; ?>
        </div>
        <div class="card-body">
          <span class="card-cat"><?php echo e($p['nama_kategori']); ?></span>
          <h3 class="card-title"><?php echo e($p['nama_menu']); ?></h3>
          <p class="card-desc clamp2"><?php echo e($p['deskripsi'] ?? ''); ?></p>
          <div class="tag-row">
            <?php if ($isRoll): ?>
              <span class="tag tag-matang">Crispy gurih</span>
              <span class="tag tag-cat">Regular / Jumbo</span>
            <?php else: ?>
              <span class="tag tag-lumer">Setengah matang lumer</span>
              <span class="tag tag-matang">Matang sempurna</span>
            <?php endif; ?>
          </div>
          <div class="card-foot">
            <div class="price"><?php echo rupiah($p['harga_dasar']); ?><small>harga dasar / loyang</small></div>
            <?php if ($stok): ?>
              <button class="add-btn" onclick="openCustomizer(<?php echo (int)$p['id']; ?>)" aria-label="Pilih <?php echo e($p['nama_menu']); ?>">+</button>
            <?php else: ?>
              <span class="badge-off">Stok habis</span>
            <?php endif; ?>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Info gerai -->
    <div class="store-card">
      <img src="gambar/menu-roll.svg" alt="Chicken roll crispy" loading="lazy" style="width:120px;flex:none;border-radius:14px" onerror="this.remove()">
      <div style="flex:1;min-width:220px">
        <span class="eyebrow">Gerai Selindung Baru</span>
        <b style="color:var(--cocoa);font-size:16px">Dimasak dadakan di atas loyang panas</b>
        <p style="margin:4px 0 0;font-size:13px;color:var(--muted)">Buka setiap hari 10.00&ndash;22.00 WIB &middot; Dine-in &amp; Takeaway &middot; QRIS / Tunai di kasir.</p>
      </div>
      <a class="btn btn-ghost" href="index.php?page=checkout">Ke checkout</a>
    </div>
  </div>

  <!-- ============ MINI-CART ASIDE ============ -->
  <aside class="cart-aside">
    <div class="mini-cart">
      <div class="mini-head">
        <h3>Keranjang</h3>
        <span class="mini-count" id="miniCount">0 item</span>
      </div>
      <div class="tipe-switch" id="tipeSwitch">
        <button type="button" id="tipeTakeaway" class="on" onclick="setTipe('takeaway')">Takeaway</button>
        <button type="button" id="tipeDineIn" onclick="setTipe('dine_in')">Makan di tempat</button>
      </div>
      <div class="queue-note">
        <span>Estimasi antrean <b>10&ndash;15 mnt</b>. Tiket <b>PC-YYYYMMDD-XXX</b> terbit otomatis setelah bayar.</span>
      </div>
      <div class="mini-items" id="miniItems">
        <div class="mini-empty"><p><b>Keranjang masih kosong.</b><br>Pilih menu favoritmu dulu.</p></div>
      </div>
      <div class="mini-sum" id="miniSum" style="display:none">
        <div class="row"><span>Subtotal</span><span id="miniSub">Rp0</span></div>
        <div class="row"><span>Biaya layanan</span><span class="free-tag">Gratis</span></div>
        <div class="row total"><span>Total</span><b id="miniTotal">Rp0</b></div>
      </div>
      <a class="mini-cta" id="miniCta" href="index.php?page=checkout">Lanjut ke checkout</a>
      <p class="mini-note">Harga dihitung ulang di server saat bayar.</p>
    </div>
  </aside>
</div>

<!-- ============ MODAL CUSTOMIZER (bottom-sheet) ============ -->
<div class="modal-backdrop" id="modalCustom">
  <div class="modal" role="dialog" aria-modal="true">
    <div class="modal-grip"></div>
    <div class="modal-head">
      <div class="m-emoji" id="mEmoji">PL</div>
      <div>
        <h3 id="mNama">Nama Menu</h3>
        <p id="mDesk">Deskripsi</p>
        <p class="m-price"><b id="mHarga">Rp0</b> <span style="color:var(--muted)">• harga dasar</span></p>
      </div>
      <button class="modal-x" onclick="closeCustomizer()" aria-label="Tutup">✕</button>
    </div>
    <input type="hidden" id="mPid" value="">

    <div class="modal-sec" id="wrapPorsi">
      <h4>Ukuran loyang</h4>
      <div class="opt-grid">
        <label class="opt"><input type="radio" name="porsi" value="1_loyang" checked> 1 Loyang Penuh</label>
        <label class="opt"><input type="radio" name="porsi" value="half_loyang"> 1/2 Loyang <small>(±½ harga)</small></label>
      </div>
    </div>

    <div class="modal-sec" id="wrapMatang">
      <h4>Tingkat kematangan</h4>
      <div class="opt-grid">
        <label class="opt"><input type="radio" name="kematangan" value="setengah_matang" checked> 1/2 Matang Lumer</label>
        <label class="opt"><input type="radio" name="kematangan" value="tiga_perempat"> 3/4 Matang</label>
        <label class="opt"><input type="radio" name="kematangan" value="matang"> Matang Sempurna</label>
      </div>
    </div>

    <div class="modal-sec">
      <h4>Topping tambahan</h4>
      <div class="top-list" id="topList"></div>
      <div class="rekom-box" id="rekomBox">
        <b id="rekomWhy">Pasangan topping terfavorit</b>
        <div id="rekomList"></div>
      </div>
    </div>

    <div class="modal-foot">
      <div class="qty">
        <button type="button" onclick="chQty(-1)">−</button>
        <b id="mQty">1</b>
        <button type="button" onclick="chQty(1)">+</button>
      </div>
      <div class="total-line"><small id="mSatuan">Rp0 / porsi</small><strong id="mTotal">Rp0</strong></div>
      <button class="btn btn-primary" onclick="tambahKeranjang()">+ Keranjang</button>
    </div>
  </div>
</div>

<script>
// Data untuk validasi.js (di-render server-side, aman karena angka/string ter-escape)
window.PL_PRODUCTS = <?php echo json_encode(array_map(function($p){
    return ['id'=>(string)$p['id'],'nama_menu'=>$p['nama_menu'],'deskripsi'=>$p['deskripsi'],
            'harga_dasar'=>(int)$p['harga_dasar'],'kode_kategori'=>$p['kode_kategori']];
}, $products), JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP); ?>;
window.PL_TOPPINGS = <?php echo json_encode(array_map(function($t){
    return ['id'=>(string)$t['id'],'nama_topping'=>$t['nama_topping'],
            'harga'=>(int)$t['harga'],'is_available'=>(string)$t['is_available']];
}, $toppings), JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP); ?>;
window.PL_REKOM = <?php echo json_encode($rekom, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP); ?>;
</script>
