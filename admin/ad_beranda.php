<?php
// admin/ad_beranda.php — Dasbor kasir: Kanban + POS walk-in
// $koneksi, $mod tersedia dari ad_index.php

$posErr = '';
$posOk = '';

// --- Data produk, kategori, topping & rekomendasi untuk form POS ---
$prodList = [];
$rs = mysqli_query($koneksi, "SELECT p.id, p.nama_menu, p.deskripsi, p.harga_dasar, p.gambar, p.is_available,
    c.id AS category_id, c.kode_kategori, c.nama_kategori
    FROM products p JOIN categories c ON c.id=p.category_id
    WHERE p.is_available=1 AND c.kode_kategori != 'topping' ORDER BY c.id ASC, p.id ASC");
if ($rs) while ($r = mysqli_fetch_assoc($rs)) $prodList[] = $r;

$categories = [];
$rs = mysqli_query($koneksi, "SELECT id, kode_kategori, nama_kategori FROM categories WHERE kode_kategori != 'topping' ORDER BY id ASC");
if ($rs) while ($r = mysqli_fetch_assoc($rs)) $categories[] = $r;

$topList = [];
$rs = mysqli_query($koneksi, "SELECT id, nama_topping, harga, is_available FROM toppings WHERE is_available=1 ORDER BY id ASC");
if ($rs) while ($r = mysqli_fetch_assoc($rs)) $topList[] = $r;

$rekom = [];
$rs = mysqli_query($koneksi, "SELECT r.base_topping_id, r.pesan_alasan,
             t.id AS rid, t.nama_topping AS rnama, t.harga AS rhrg, t.is_available
      FROM topping_recommendations r
      JOIN toppings t ON t.id = r.recommended_topping_id
      ORDER BY r.base_topping_id ASC");
if ($rs) while ($r = mysqli_fetch_assoc($rs)) {
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

// Visual menu kasir bergambar ala Stitch
function gambar_menu_admin($file) {
    if (!empty($file) && is_file(__DIR__ . '/../gambar/' . $file)) {
        return ['file', '../gambar/' . rawurlencode($file)];
    }
    return ['svg', '../gambar/menu-pancong.svg'];
}
function monogram_menu_admin($nama) {
    $kata = preg_split('/\s+/', trim($nama));
    $m = '';
    foreach (array_slice($kata, 0, 2) as $k) $m .= strtoupper(substr($k, 0, 1));
    return $m !== '' ? $m : 'PL';
}
function kelas_menu_admin() {
    return 'pancong';
}
function badge_menu_admin($nama, $stok, $idx) {
    if (!$stok) return ['Habis', 'bd-grey'];
    $n = strtolower($nama);
    if ($idx === 0 || strpos($n, 'choco') !== false) return ['Best Seller', 'bd-sec'];
    if (strpos($n, 'jumbo') !== false) return ['Hemat Sharing', 'bd-amber'];
    if (strpos($n, 'taro') !== false || strpos($n, 'matcha') !== false) return ['Varian Baru', 'bd-pink'];
    return ['Lumer Favorit', 'bd-amber'];
}

// --- Proses POS ---
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'pos') {
    $nama = trim($_POST['pos_nama'] ?? '');
    $wa   = trim($_POST['pos_wa'] ?? '');
    $tipe = ($_POST['pos_tipe'] ?? 'takeaway') === 'dine_in' ? 'dine_in' : 'takeaway';
    $metode = ($_POST['pos_metode'] ?? 'tunai') === 'qris' ? 'qris' : 'tunai';

    // Mendukung pengiriman via cartJson (interaktif POS) atau array (pid[])
    $cartRaw = trim($_POST['cartJson'] ?? '');
    $parsedCart = !empty($cartRaw) ? json_decode($cartRaw, true) : null;
    if (is_array($parsedCart) && count($parsedCart) > 0) {
        $pids = []; $porsis = []; $matangs = []; $qtys = [];
        $_POST['tops'] = [];
        foreach ($parsedCart as $ciIdx => $ci) {
            $pids[$ciIdx] = (int)($ci['product_id'] ?? 0);
            $porsis[$ciIdx] = ($ci['porsi'] ?? '1_loyang') === 'half_loyang' ? 'half_loyang' : '1_loyang';
            $matangs[$ciIdx] = $ci['kematangan'] ?? null;
            $qtys[$ciIdx] = min(20, max(1, (int)($ci['qty'] ?? 1)));
            $topIds = [];
            if (!empty($ci['toppings']) && is_array($ci['toppings'])) {
                foreach ($ci['toppings'] as $tp) {
                    $tid = is_array($tp) ? (int)($tp['id'] ?? 0) : (int)$tp;
                    if ($tid > 0) $topIds[] = $tid;
                }
            }
            $_POST['tops'][$ciIdx] = $topIds;
        }
    } else {
        $pids = $_POST['pid'] ?? [];
        $porsis = $_POST['porsi'] ?? [];
        $matangs = $_POST['matang'] ?? [];
        $qtys = $_POST['qty'] ?? [];
    }

    if (strlen($nama) < 3) {
        $posErr = 'Nama pembeli minimal 3 huruf.';
    } elseif ($wa !== '' && !preg_match('/^08[0-9]{7,13}$/', $wa)) {
        $posErr = 'No. WA harus diawali 08 bila diisi.';
    } elseif (empty($pids)) {
        $posErr = 'Pilih minimal 1 menu.';
    } else {
        $items = []; $grand = 0; $valid = true;
        foreach ($pids as $idx => $pidRaw) {
            $pid = (int)$pidRaw;
            $qty = min(20, max(1, (int)($qtys[$idx] ?? 1)));
            if ($pid <= 0) continue;
            $stmt = mysqli_prepare($koneksi, "SELECT id, harga_dasar, is_available FROM products WHERE id=? LIMIT 1");
            mysqli_stmt_bind_param($stmt, 'i', $pid);
            mysqli_stmt_execute($stmt);
            $prod = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            mysqli_stmt_close($stmt);
            if (!$prod || (int)$prod['is_available'] !== 1) continue;
            $porsi = ($porsis[$idx] ?? '1_loyang') === 'half_loyang' ? 'half_loyang' : '1_loyang';
            $mat = $matangs[$idx] ?? null;
            if (!in_array($mat, ['matang','setengah_matang','tiga_perempat'], true)) $mat = 'matang';
            $base = (int)$prod['harga_dasar'];
            if ($porsi === 'half_loyang') $base = (int)round($base/2);
            $tops = [];
            $topSum = 0;
            $rawTops = $_POST['tops'][$idx] ?? [];
            if (is_array($rawTops)) {
                foreach (array_unique($rawTops) as $tidRaw) {
                    $tid = (int)$tidRaw;
                    if ($tid <= 0) continue;
                    $st = mysqli_prepare($koneksi, "SELECT id, harga FROM toppings WHERE id=? AND is_available=1 LIMIT 1");
                    mysqli_stmt_bind_param($st, 'i', $tid);
                    mysqli_stmt_execute($st);
                    $tr = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
                    mysqli_stmt_close($st);
                    if (!$tr) continue;
                    $tops[] = ['id'=>(int)$tr['id'],'harga'=>(int)$tr['harga']];
                    $topSum += (int)$tr['harga'];
                }
            }
            $per = $base + $topSum;
            $sub = $per * $qty;
            $grand += $sub;
            $items[] = ['product_id'=>$pid,'porsi'=>$porsi,'kematangan'=>$mat,'qty'=>$qty,'subtotal'=>$sub,'tops'=>$tops];
        }
        if (empty($items)) { $posErr = 'Tidak ada item valid. Cek stok menu.'; $valid = false; }
        if ($valid && $grand <= 0) { $posErr = 'Total tidak valid.'; $valid = false; }

        if ($valid && empty($posErr)) {
            mysqli_begin_transaction($koneksi);
            try {
                $today = date('Y-m-d');
                $q = mysqli_prepare($koneksi, "SELECT COALESCE(MAX(urutan_harian),0) AS mx FROM orders WHERE DATE(created_at)=? FOR UPDATE");
                mysqli_stmt_bind_param($q, 's', $today);
                mysqli_stmt_execute($q);
                $mxr = mysqli_fetch_assoc(mysqli_stmt_get_result($q));
                mysqli_stmt_close($q);
                $urut = ((int)($mxr['mx'] ?? 0)) + 1;
                $no = 'PC-' . date('Ymd') . '-' . str_pad($urut, 3, '0', STR_PAD_LEFT);

                $uid = (int)$_SESSION['user_id'];
                $waSave = $wa !== '' ? $wa : '-';
                // Tunai walk-in: langsung lunas & masak. QRIS walk-in: tetap pending verifikasi.
                $stBayar = ($metode === 'tunai') ? 'verified' : 'pending';
                $stPesan = ($metode === 'tunai') ? 'dimasak' : 'pending';

                $ins = mysqli_prepare($koneksi, "INSERT INTO orders (no_antrean, urutan_harian, user_id, nama_pemesan, no_wa, tipe_order, metode_bayar, bukti_bayar, status_bayar, status_pesanan, total_bayar) VALUES (?, ?, ?, ?, ?, ?, ?, NULL, ?, ?, ?)");
                mysqli_stmt_bind_param($ins, 'siissssssi', $no, $urut, $uid, $nama, $waSave, $tipe, $metode, $stBayar, $stPesan, $grand);
                if (!mysqli_stmt_execute($ins)) throw new Exception('Gagal simpan POS.');
                $orderId = mysqli_insert_id($koneksi);
                mysqli_stmt_close($ins);

                foreach ($items as $it) {
                    $mat = $it['kematangan'];
                    $si = mysqli_prepare($koneksi, "INSERT INTO order_items (order_id, product_id, porsi, kematangan, qty, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
                    mysqli_stmt_bind_param($si, 'iissii', $orderId, $it['product_id'], $it['porsi'], $mat, $it['qty'], $it['subtotal']);
                    if (!mysqli_stmt_execute($si)) throw new Exception('Gagal simpan item POS.');
                    $itemId = mysqli_insert_id($koneksi);
                    mysqli_stmt_close($si);
                    foreach ($it['tops'] as $tp) {
                        $st2 = mysqli_prepare($koneksi, "INSERT INTO order_item_toppings (order_item_id, topping_id, harga_topping) VALUES (?, ?, ?)");
                        mysqli_stmt_bind_param($st2, 'iii', $itemId, $tp['id'], $tp['harga']);
                        if (!mysqli_stmt_execute($st2)) throw new Exception('Gagal simpan topping POS.');
                        mysqli_stmt_close($st2);
                    }
                }
                mysqli_commit($koneksi);
                $posOk = 'POS tersimpan: tiket ' . $no . ' • Total ' . rupiah($grand) . ($metode==='tunai' ? ' • Langsung DIMASAK.' : ' • Menunggu verifikasi QRIS.');
            } catch (Exception $ex) {
                mysqli_rollback($koneksi);
                $posErr = 'Gagal menyimpan POS: ' . $ex->getMessage();
            }
        }
    }
}

// --- Ringkasan Kanban hari ini ---
$today = date('Y-m-d');
$kanban = ['pending'=>0,'dimasak'=>0,'siap_ambil'=>0,'selesai'=>0,'omzet'=>0,'total'=>0];
$stKanban = mysqli_prepare($koneksi, "SELECT status_pesanan, COUNT(*) c, SUM(total_bayar) s FROM orders WHERE DATE(created_at)=? AND status_pesanan != 'batal' GROUP BY status_pesanan");
mysqli_stmt_bind_param($stKanban, 's', $today);
mysqli_stmt_execute($stKanban);
$rs = mysqli_stmt_get_result($stKanban);
if ($rs) while ($r = mysqli_fetch_assoc($rs)) {
    $kanban[$r['status_pesanan']] = (int)$r['c'];
    $kanban['omzet'] += (int)$r['s'];
    $kanban['total'] += (int)$r['c'];
}
mysqli_stmt_close($stKanban);
// Antrean pending terbaru (5)
$antre = [];
$rs = mysqli_query($koneksi, "SELECT no_antrean, nama_pemesan, metode_bayar, total_bayar, created_at FROM orders WHERE status_pesanan='pending' ORDER BY id ASC LIMIT 5");
if ($rs) while ($r = mysqli_fetch_assoc($rs)) $antre[] = $r;

// Target harian kasir (untuk visualisasi progres, bisa diubah)
$targetHarian = 50;
$persenTarget = $targetHarian > 0 ? min(100, (int)round($kanban['total'] / $targetHarian * 100)) : 0;
?>

<section class="hero-admin">
  <img src="../gambar/hero-pancong.svg" alt="" aria-hidden="true" onerror="this.remove()">
  <div class="hero-admin-in">
    <div>
      <span class="eyebrow">Panel operasional kasir</span>
      <h2>Dasbor hari ini</h2>
      <p><?php
        $hariIndo = [1=>'Senin',2=>'Selasa',3=>'Rabu',4=>'Kamis',5=>'Jumat',6=>'Sabtu',7=>'Minggu'];
        $bulanIndo = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
        $tglDasbor = $hariIndo[(int)date('N')] . ', ' . date('d') . ' ' . $bulanIndo[(int)date('n')] . ' ' . date('Y');
        echo e($tglDasbor);
      ?> &middot; Pantau antrean panggang, verifikasi pembayaran, dan catat pesanan walk-in dari satu layar.</p>
    </div>
    <div class="hero-actions">
      <a class="btn btn-primary btn-sm" href="ad_index.php?mod=pesanan">Buka Live Orders<?php if($kanban['pending']>0) echo ' ('.$kanban['pending'].')'; ?></a>
      <a class="btn btn-ghost btn-sm btn-hero-ghost" href="ad_index.php?mod=laporan">Lihat Laporan Omzet</a>
    </div>
  </div>
</section>

<div class="stat-grid">
  <div class="kcard"><span class="k-label">Menunggu verifikasi</span><b><?php echo $kanban['pending']; ?></b><span class="k-sub">tiket pending</span></div>
  <div class="kcard"><span class="k-label">Sedang dipanggang</span><b><?php echo $kanban['dimasak']; ?></b><span class="k-sub">di atas loyang</span></div>
  <div class="kcard"><span class="k-label">Siap diambil</span><b><?php echo $kanban['siap_ambil']; ?></b><span class="k-sub">panggil pelanggan</span></div>
  <div class="kcard accent"><span class="k-label">Omzet hari ini</span><b><?php echo rupiah($kanban['omzet']); ?></b><span class="k-sub"><?php echo $kanban['total']; ?> transaksi (di luar batal)</span>
    <div class="bar-row"><div class="bar-track"><i style="width:<?php echo $persenTarget; ?>%"></i></div><span class="bar-pct"><?php echo $persenTarget; ?>% target</span></div>
  </div>
</div>

<div class="form-card" style="margin-bottom:16px">
  <h3 style="margin-top:0">Aksi cepat</h3>
  <div class="qa-grid">
    <a class="qa-card" href="ad_index.php?mod=pesanan"><span class="qa-num">1</span><span><b>Verifikasi pembayaran</b><small><?php echo $kanban['pending']; ?> tiket menunggu</small></span></a>
    <a class="qa-card" href="#posKasir"><span class="qa-num">2</span><span><b>Input pesanan walk-in</b><small>Tunai / QRIS langsung</small></span></a>
    <a class="qa-card" href="ad_index.php?mod=laporan"><span class="qa-num">3</span><span><b>Rekap omzet</b><small>Riwayat &amp; menu terlaris</small></span></a>
  </div>
</div>

<!-- ============ KDS ALERT: PERLU TINDAKAN ============ -->
<?php if (!empty($antre)): ?>
<div class="kds-alert-card">
  <div class="kds-alert-head">
    <div>
      <span class="eyebrow" style="color:var(--primary)">KDS &middot; Perlu Verifikasi Pembayaran</span>
      <h3 style="margin:2px 0 0;font-size:16px;color:var(--cocoa)">Ada <?php echo count($antre); ?> Tiket Online Menunggu Konfirmasi</h3>
    </div>
    <a class="btn btn-ghost btn-sm" href="ad_index.php?mod=pesanan">Buka Live Orders &rarr;</a>
  </div>
  <div class="kds-alert-grid">
    <?php foreach ($antre as $a): ?>
      <div class="kds-alert-item">
        <div>
          <b><?php echo e($a['no_antrean']); ?></b> &middot; <span class="tag tag-cat"><?php echo $a['metode_bayar']==='qris'?'QRIS':'Tunai'; ?></span>
          <div style="font-size:12px;color:var(--muted);margin-top:2px"><?php echo e($a['nama_pemesan']); ?> &bull; <b><?php echo rupiah($a['total_bayar']); ?></b></div>
        </div>
        <a class="btn btn-blue btn-sm" href="ad_index.php?mod=pesanan&act=konfirmasi&no=<?php echo urlencode($a['no_antrean']); ?>">Verifikasi</a>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php else: ?>
<div class="kds-status-bar">
  <span class="status-dot-green"></span>
  <span><b>KDS Aman:</b> Semua tiket online telah terverifikasi. Siap melayani pelanggan walk-in.</span>
  <a href="ad_index.php?mod=pesanan" style="margin-left:auto;font-size:12px;font-weight:700">Pantau Live Orders &rarr;</a>
</div>
<?php endif; ?>

<!-- ============ OPERATIONAL POS WORKSTATION ============ -->
<div class="pos-workstation" id="posKasir">
  <!-- POS Station Header -->
  <div class="pos-station-head">
    <div>
      <span class="eyebrow">Terminal POS &middot; Selindung Baru</span>
      <h2 style="margin:0;color:var(--cocoa);font-size:22px;display:flex;align-items:center;gap:10px;flex-wrap:wrap">
        POS Kasir &amp; Pesanan Langsung
        <span class="pill pill-open" style="font-size:11px;padding:3px 10px">Siap Melayani</span>
      </h2>
      <p style="margin:4px 0 0;font-size:13px;color:var(--muted)">
        Pilih menu bergambar untuk pesanan walk-in tanpa salah menu. Tunai langsung <b>Masuk Loyang</b>, QRIS masuk <b>Live Orders</b>.
      </p>
    </div>
    <!-- Search Bar -->
    <div class="pos-search-box">
      <input type="text" id="posSearchInput" placeholder="Cari menu cepat (ketik atau tekan '/')..." oninput="filterPosMenu()">
      <button type="button" class="pos-search-clear" id="posSearchClear" onclick="clearPosSearch()" style="display:none" title="Hapus pencarian">✕</button>
    </div>
  </div>

  <?php if ($posErr): ?><div class="alert alert-err" style="margin-bottom:14px"><?php echo e($posErr); ?></div><?php endif; ?>
  <?php if ($posOk): ?><div class="alert alert-ok" style="margin-bottom:14px"><?php echo e($posOk); ?></div><?php endif; ?>

  <!-- POS Split: Catalog Menu Bergambar (Kiri) + Slip Kasir (Kanan) -->
  <div class="pos-split">
    <!-- KOLOM KIRI: KATALOG MENU BERGAMBAR -->
    <div class="pos-catalog-panel">
      <!-- Sticky Category Chips -->
      <nav class="pos-catnav" aria-label="Kategori menu POS">
        <button type="button" class="chip active" data-kat="semua" onclick="filterPosKategori('semua', this)">Semua (<?php echo count($prodList); ?>)</button>
        <?php foreach ($categories as $c): ?>
          <button type="button" class="chip" data-kat="<?php echo e($c['kode_kategori']); ?>" onclick="filterPosKategori('<?php echo e($c['kode_kategori']); ?>', this)"><?php echo e($c['nama_kategori']); ?></button>
        <?php endforeach; ?>
      </nav>

      <!-- Grid Menu Bergambar ala Stitch -->
      <div class="pos-menu-grid" id="posMenuGrid">
        <?php foreach ($prodList as $idx => $p):
          $stok = ((int)$p['is_available'] === 1);
          [$jenisImg, $imgSrc] = gambar_menu_admin($p['gambar'] ?? '');
          [$badgeTxt, $badgeCls] = badge_menu_admin($p['nama_menu'], $stok, $idx);
          $mono = monogram_menu_admin($p['nama_menu']);
          $klsMenu = kelas_menu_admin();
        ?>
          <article class="card pos-card <?php echo $stok ? '' : 'stock-off'; ?>"
                   data-catcode="<?php echo e($p['kode_kategori']); ?>"
                   data-nama="<?php echo e(strtolower($p['nama_menu'])); ?>"
                   data-pid="<?php echo (int)$p['id']; ?>"
                   onclick="handlePosCardClick(<?php echo (int)$p['id']; ?>)">
            <div class="card-img">
              <span class="card-badge <?php echo $badgeCls; ?>"><?php echo e($badgeTxt); ?></span>
              <img src="<?php echo e($imgSrc); ?>" alt="<?php echo e($p['nama_menu']); ?>" loading="lazy" onerror="this.outerHTML='<span class=\'img-fallback <?php echo $klsMenu; ?>\'><?php echo e($mono); ?></span>'">
            </div>
            <div class="card-body">
              <span class="card-cat"><?php echo e($p['nama_kategori']); ?></span>
              <h4 class="card-title" style="margin:0;font-size:15px;line-height:1.3"><?php echo e($p['nama_menu']); ?></h4>
              <p class="card-desc clamp2" style="font-size:11.5px"><?php echo e($p['deskripsi'] ?? ''); ?></p>
              <div class="tag-row">
                <span class="tag tag-lumer">Lumer / Matang</span>
                <span class="tag tag-cat">1 &amp; &frac12; Loyang</span>
              </div>
              <div class="card-foot">
                <div class="price"><?php echo rupiah($p['harga_dasar']); ?><small>harga dasar</small></div>
                <?php if ($stok): ?>
                  <button type="button" class="add-btn" onclick="event.stopPropagation(); openPosCustomizer(<?php echo (int)$p['id']; ?>)" aria-label="Pilih <?php echo e($p['nama_menu']); ?>" title="Atur porsi &amp; topping">+</button>
                <?php else: ?>
                  <span class="badge-off">Habis</span>
                <?php endif; ?>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>

      <div id="posNoMatch" class="empty" style="display:none;padding:32px">
        <p><b>Menu tidak ditemukan.</b><br><span style="font-size:13px">Periksa ejaan kata kunci atau pilih kategori lain.</span></p>
      </div>
    </div>

    <!-- KOLOM KANAN: SLIP PESANAN KASIR & WALK-IN CHECKOUT -->
    <div class="pos-slip-panel">
      <div class="pos-slip-card">
        <div class="pos-slip-head">
          <div>
            <span class="eyebrow" style="color:var(--sec)">Nota Transaksi</span>
            <h3 style="margin:0;font-size:16px;color:var(--cocoa)">Slip Pesanan Walk-in</h3>
          </div>
          <span class="mini-count" id="posSlipCount">0 item</span>
        </div>

        <!-- Daftar Item Terpilih -->
        <div class="pos-slip-items" id="posSlipItems">
          <div class="pos-empty-slip" id="posEmptySlip">
            <b>Belum Ada Menu Dipilih</b>
            <p style="margin:4px 0 0;font-size:12px;color:var(--muted)">Klik kartu menu bergambar di sebelah kiri untuk menambahkan pesanan.</p>
          </div>
        </div>

        <!-- Form Data Pemesan & Pembayaran -->
        <form method="post" id="posForm" onsubmit="return submitPosForm(this)">
          <input type="hidden" name="aksi" value="pos">
          <input type="hidden" name="cartJson" id="posCartJson" value="[]">
          <!-- Hidden inputs kontainer untuk kompatibilitas form -->
          <div id="posHiddenFields"></div>

          <div class="pos-customer-sec">
            <label class="flabel" style="display:flex;justify-content:space-between;align-items:center">
              <span>Nama Pembeli *</span>
              <span style="font-size:11px;color:var(--muted);font-weight:normal">Preset cepat:</span>
            </label>
            <div class="pos-presets">
              <button type="button" class="btn-preset" onclick="setPosNama('Walk-in 1')">Walk-in 1</button>
              <button type="button" class="btn-preset" onclick="setPosNama('Walk-in 2')">Walk-in 2</button>
              <button type="button" class="btn-preset" onclick="setPosNama('Meja 1')">Meja 1</button>
              <button type="button" class="btn-preset" onclick="setPosNama('Meja 2')">Meja 2</button>
              <button type="button" class="btn-preset" onclick="setPosNama('Bungkus')">Bungkus</button>
            </div>
            <input class="fin" name="pos_nama" id="posNama" required placeholder="cth: Walk-in 1 / Budi" autocomplete="off">

            <div style="margin-top:10px">
              <label class="flabel">No. WhatsApp (opsional)</label>
              <input class="fin" name="pos_wa" id="posWa" placeholder="08xx (opsional)" inputmode="tel" autocomplete="off">
            </div>

            <!-- Switcher Tipe Pesanan -->
            <div style="margin-top:10px">
              <label class="flabel">Tipe Pesanan</label>
              <div class="tipe-switch">
                <button type="button" id="posTipeTakeaway" class="on" onclick="setPosTipe('takeaway')">Takeaway (Bungkus)</button>
                <button type="button" id="posTipeDineIn" onclick="setPosTipe('dine_in')">Dine-in (Makan di tempat)</button>
              </div>
              <input type="hidden" name="pos_tipe" id="posTipeVal" value="takeaway">
            </div>

            <!-- Switcher Metode Bayar -->
            <div style="margin-top:10px">
              <label class="flabel">Metode Pembayaran</label>
              <div class="tipe-switch">
                <button type="button" id="posMetodeTunai" class="on" onclick="setPosMetode('tunai')">Tunai (Langsung Lunas)</button>
                <button type="button" id="posMetodeQris" onclick="setPosMetode('qris')">QRIS Walk-in</button>
              </div>
              <input type="hidden" name="pos_metode" id="posMetodeVal" value="tunai">
            </div>

            <!-- Kalkulator Uang Tunai & Kembalian (Khusus Tunai) -->
            <div id="posCashCalculator" class="pos-cash-box">
              <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
                <span class="flabel" style="margin:0">Uang Diterima:</span>
                <span id="posChangeText" style="font-size:12px;font-weight:700;color:var(--ok)">Kembalian: Rp0</span>
              </div>
              <input type="number" id="posCashInput" class="fin" placeholder="Nominal uang diterima (Rp)" min="0" oninput="calcPosChange()">
              <div class="pos-presets" style="margin-top:6px">
                <button type="button" class="btn-preset" onclick="setPosCashPreset('exact')">Uang Pas</button>
                <button type="button" class="btn-preset" onclick="setPosCashPreset(20000)">Rp20.000</button>
                <button type="button" class="btn-preset" onclick="setPosCashPreset(50000)">Rp50.000</button>
                <button type="button" class="btn-preset" onclick="setPosCashPreset(100000)">Rp100.000</button>
              </div>
            </div>
          </div>

          <!-- Rincian Biaya & Total -->
          <div class="pos-slip-summary">
            <div class="row"><span>Subtotal Item</span><span id="posSubtotal">Rp0</span></div>
            <div class="row"><span>Biaya Layanan/Bungkus</span><span class="free-tag">Gratis</span></div>
            <div class="row total"><span>Total Bayar</span><b id="posGrandTotal">Rp0</b></div>
          </div>

          <div style="display:grid;gap:8px;margin-top:14px">
            <button class="btn btn-blue btn-block" type="submit" id="posSubmitBtn" disabled>
              Simpan &amp; Terbitkan Antrean
            </button>
            <button class="btn btn-ghost btn-sm btn-block" type="button" onclick="resetPosCart()">
              Batal / Kosongkan Slip
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- ============ MODAL CUSTOMIZER POS KASIR ============ -->
<div class="modal-backdrop" id="modalPosCustom">
  <div class="modal" role="dialog" aria-modal="true" style="max-width:480px">
    <div class="modal-grip"></div>
    <div class="modal-head">
      <div class="m-emoji" id="mPosEmoji">PL</div>
      <div>
        <h3 id="mPosNama" style="margin:0;font-size:17px">Nama Menu</h3>
        <p id="mPosDesk" style="margin:2px 0 0;font-size:12px;color:var(--muted)">Deskripsi</p>
        <p class="m-price" style="margin:4px 0 0"><b id="mPosHarga">Rp0</b> <span style="color:var(--muted);font-size:12px">&bull; harga dasar</span></p>
      </div>
      <button type="button" class="modal-x" onclick="closePosCustomizer()" aria-label="Tutup">✕</button>
    </div>
    <input type="hidden" id="mPosPid" value="">

    <!-- Porsi -->
    <div class="modal-sec" id="wrapPosPorsi">
      <h4>Ukuran Loyang</h4>
      <div class="opt-grid">
        <label class="opt"><input type="radio" name="pos_porsi" value="1_loyang" checked> 1 Loyang Penuh</label>
        <label class="opt"><input type="radio" name="pos_porsi" value="half_loyang"> &frac12; Loyang <small>(&plusmn;&frac12; harga)</small></label>
      </div>
    </div>

    <!-- Kematangan -->
    <div class="modal-sec" id="wrapPosMatang">
      <h4>Tingkat Kematangan</h4>
      <div class="opt-grid">
        <label class="opt"><input type="radio" name="pos_kematangan" value="setengah_matang" checked> &frac12; Matang Lumer</label>
        <label class="opt"><input type="radio" name="pos_kematangan" value="tiga_perempat"> &frac34; Matang</label>
        <label class="opt"><input type="radio" name="pos_kematangan" value="matang"> Matang Sempurna</label>
      </div>
    </div>

    <!-- Topping -->
    <div class="modal-sec">
      <h4>Topping Tambahan</h4>
      <div class="top-list" id="posTopList"></div>
      <div class="rekom-box" id="posRekomBox">
        <b id="posRekomWhy">Pasangan topping terfavorit</b>
        <div id="posRekomList"></div>
      </div>
    </div>

    <!-- Modal Foot -->
    <div class="modal-foot">
      <div class="qty">
        <button type="button" onclick="chPosQty(-1)">−</button>
        <b id="mPosQty">1</b>
        <button type="button" onclick="chPosQty(1)">+</button>
      </div>
      <div class="total-line"><small id="mPosSatuan">Rp0 / porsi</small><strong id="mPosTotal">Rp0</strong></div>
      <button type="button" class="btn btn-blue" onclick="addPosItemToCart()">Masukkan ke Slip</button>
    </div>
  </div>
</div>

<!-- ============ POS JAVASCRIPT ENGINE ============ -->
<script>
const POS_PRODUCTS = <?php echo json_encode(array_map(function($p){
    [$jenis, $src] = gambar_menu_admin($p['gambar'] ?? '');
    return [
        'id' => (string)$p['id'],
        'nama_menu' => $p['nama_menu'],
        'deskripsi' => $p['deskripsi'] ?? '',
        'harga_dasar' => (int)$p['harga_dasar'],
        'kode_kategori' => $p['kode_kategori'],
        'nama_kategori' => $p['nama_kategori'],
        'gambar_src' => $src,
        'mono' => monogram_menu_admin($p['nama_menu']),
        'kelas' => kelas_menu_admin()
    ];
}, $prodList), JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP); ?>;

const POS_TOPPINGS = <?php echo json_encode(array_map(function($t){
    return [
        'id' => (string)$t['id'],
        'nama_topping' => $t['nama_topping'],
        'harga' => (int)$t['harga'],
        'is_available' => (string)$t['is_available']
    ];
}, $topList), JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP); ?>;

const POS_REKOM = <?php echo json_encode($rekom, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP); ?>;

// State keranjang POS kasir
let posCart = [];
let curPosCustom = null; // { product, qty }

function posRp(n) {
    return 'Rp' + Number(n || 0).toLocaleString('id-ID');
}

function escPos(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, (m) =>
        ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m]));
}

// Filter kategori
function filterPosKategori(kat, btn) {
    document.querySelectorAll('.pos-catnav .chip').forEach(c => c.classList.remove('active'));
    if (btn) btn.classList.add('active');
    
    const searchVal = (document.getElementById('posSearchInput').value || '').toLowerCase().trim();
    let countVis = 0;
    
    document.querySelectorAll('#posMenuGrid .pos-card').forEach(card => {
        const cKat = card.getAttribute('data-catcode');
        const cNama = card.getAttribute('data-nama');
        const matchKat = (kat === 'semua' || cKat === kat);
        const matchSearch = (!searchVal || cNama.indexOf(searchVal) !== -1);
        
        if (matchKat && matchSearch) {
            card.style.display = '';
            countVis++;
        } else {
            card.style.display = 'none';
        }
    });
    
    const emptyMsg = document.getElementById('posNoMatch');
    if (emptyMsg) emptyMsg.style.display = countVis === 0 ? 'block' : 'none';
}

// Pencarian menu
function filterPosMenu() {
    const input = document.getElementById('posSearchInput');
    const clearBtn = document.getElementById('posSearchClear');
    const val = (input.value || '').toLowerCase().trim();
    if (clearBtn) clearBtn.style.display = val ? 'flex' : 'none';
    
    const activeChip = document.querySelector('.pos-catnav .chip.active');
    const activeKat = activeChip ? activeChip.getAttribute('data-kat') : 'semua';
    filterPosKategori(activeKat, activeChip);
}

function clearPosSearch() {
    const input = document.getElementById('posSearchInput');
    if (input) {
        input.value = '';
        input.focus();
    }
    filterPosMenu();
}

// Klik kartu menu
function handlePosCardClick(pid) {
    openPosCustomizer(pid);
}

// Modal customizer POS
function openPosCustomizer(pid) {
    const p = POS_PRODUCTS.find(x => String(x.id) === String(pid));
    if (!p) return;
    
    curPosCustom = { product: p, qty: 1 };
    document.getElementById('mPosNama').textContent = p.nama_menu;
    document.getElementById('mPosDesk').textContent = p.deskripsi || '';
    document.getElementById('mPosHarga').textContent = posRp(p.harga_dasar);
    document.getElementById('mPosPid').value = p.id;

    const em = document.getElementById('mPosEmoji');
    if (em) em.textContent = p.mono || 'PL';

    document.querySelectorAll('input[name="pos_porsi"]').forEach(r => {
        r.checked = (r.value === '1_loyang');
        if (!r._bound) { r.addEventListener('change', updatePosModalTotal); r._bound = true; }
    });
    document.querySelectorAll('input[name="pos_kematangan"]').forEach(r => {
        r.checked = (r.value === 'setengah_matang');
        if (!r._bound) { r.addEventListener('change', updatePosModalTotal); r._bound = true; }
    });
    
    // Render list topping
    const topBox = document.getElementById('posTopList');
    topBox.innerHTML = '';
    POS_TOPPINGS.forEach(t => {
        if (String(t.is_available) !== '1') return;
        const lbl = document.createElement('label');
        lbl.className = 'top-item';
        lbl.innerHTML = `
            <input type="checkbox" name="pos_topping" value="${t.id}" data-nama="${escPos(t.nama_topping)}" data-harga="${t.harga}">
            <span>${escPos(t.nama_topping)}</span><b>+${posRp(t.harga)}</b>`;
        lbl.querySelector('input').addEventListener('change', onPosToppingChange);
        topBox.appendChild(lbl);
    });
    
    document.getElementById('mPosQty').textContent = '1';
    const rBox = document.getElementById('posRekomBox');
    if (rBox) rBox.classList.remove('show');
    const rList = document.getElementById('posRekomList');
    if (rList) rList.innerHTML = '';
    
    updatePosModalTotal();
    
    const m = document.getElementById('modalPosCustom');
    if (m) m.classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closePosCustomizer() {
    const m = document.getElementById('modalPosCustom');
    if (m) m.classList.remove('open');
    document.body.style.overflow = '';
}

function chPosQty(d) {
    if (!curPosCustom) return;
    curPosCustom.qty = Math.min(20, Math.max(1, (curPosCustom.qty || 1) + d));
    document.getElementById('mPosQty').textContent = curPosCustom.qty;
    updatePosModalTotal();
}

function getPosSelectedToppings() {
    return Array.from(document.querySelectorAll('#posTopList input[name="pos_topping"]:checked')).map(c => ({
        id: c.value,
        nama: c.getAttribute('data-nama'),
        harga: parseInt(c.getAttribute('data-harga'), 10) || 0
    }));
}

function onPosToppingChange() {
    updatePosModalTotal();
    renderPosRekom();
}

function renderPosRekom() {
    const box = document.getElementById('posRekomBox');
    const list = document.getElementById('posRekomList');
    if (!box || !list) return;
    
    const chosen = getPosSelectedToppings().map(t => String(t.id));
    const saran = [];
    chosen.forEach(bid => {
        (POS_REKOM[bid] || []).forEach(r => {
            if (chosen.indexOf(String(r.id)) === -1 && !saran.find(s => String(s.id) === String(r.id))) {
                saran.push(r);
            }
        });
    });
    
    if (!saran.length) {
        box.classList.remove('show');
        list.innerHTML = '';
        return;
    }
    
    box.classList.add('show');
    list.innerHTML = '';
    saran.slice(0, 3).forEach(r => {
        const b = document.createElement('button');
        b.type = 'button';
        b.textContent = '+ ' + r.nama + ' (' + posRp(r.harga) + ')';
        b.title = r.alasan || '';
        b.onclick = function() {
            const cb = document.querySelector('#posTopList input[name="pos_topping"][value="' + r.id + '"]');
            if (cb) { cb.checked = true; onPosToppingChange(); }
        };
        list.appendChild(b);
    });
    
    const why = saran[0] && saran[0].alasan ? saran[0].alasan : 'Kombinasi terfavorit pelanggan.';
    document.getElementById('posRekomWhy').textContent = 'Pasangan topping terfavorit: ' + why;
}

function updatePosModalTotal() {
    if (!curPosCustom) return;
    const hd = parseInt(curPosCustom.product.harga_dasar, 10) || 0;
    const porsiChecked = (document.querySelector('input[name="pos_porsi"]:checked') || {}).value || '1_loyang';
    const base = (porsiChecked === 'half_loyang') ? Math.round(hd / 2) : hd;

    const tops = getPosSelectedToppings();
    const per = base + tops.reduce((a, t) => a + t.harga, 0);
    const tot = per * (curPosCustom.qty || 1);

    document.getElementById('mPosSatuan').textContent = posRp(per) + ' / porsi';
    document.getElementById('mPosTotal').textContent = posRp(tot);
}

// Tambah item ke keranjang slip kasir
function addPosItemToCart() {
    if (!curPosCustom) return;
    const porsi = (document.querySelector('input[name="pos_porsi"]:checked') || {}).value || '1_loyang';
    const matang = (document.querySelector('input[name="pos_kematangan"]:checked') || {}).value || 'matang';

    const tops = getPosSelectedToppings();
    const hd = parseInt(curPosCustom.product.harga_dasar, 10) || 0;
    const base = (porsi === 'half_loyang') ? Math.round(hd / 2) : hd;
    const per = base + tops.reduce((a, t) => a + t.harga, 0);
    const qty = curPosCustom.qty || 1;
    
    // Cek apakah item serupa sudah ada di cart (nama, porsi, kematangan, topping sama persis)
    const topKey = tops.map(t => t.id).sort().join(',');
    const existingIndex = posCart.findIndex(item => 
        item.product_id === curPosCustom.product.id &&
        item.porsi === porsi &&
        item.kematangan === matang &&
        item.toppings.map(t => t.id).sort().join(',') === topKey
    );
    
    if (existingIndex > -1) {
        posCart[existingIndex].qty = Math.min(20, posCart[existingIndex].qty + qty);
        posCart[existingIndex].subtotal = posCart[existingIndex].per * posCart[existingIndex].qty;
    } else {
        posCart.push({
            product_id: curPosCustom.product.id,
            nama: curPosCustom.product.nama_menu,
            harga_dasar: base,
            per: per,
            porsi: porsi,
            kematangan: matang,
            qty: qty,
            toppings: tops,
            subtotal: per * qty,
            gambar_src: curPosCustom.product.gambar_src,
            mono: curPosCustom.product.mono,
            kelas: curPosCustom.product.kelas
        });
    }
    
    closePosCustomizer();
    renderPosSlip();
    if (window.plToast) window.plToast('Item masuk ke slip pesanan', 'success');
}

// Render slip pesanan kasir
function renderPosSlip() {
    const wrap = document.getElementById('posSlipItems');
    const emptyNotice = document.getElementById('posEmptySlip');
    const countBadge = document.getElementById('posSlipCount');
    const submitBtn = document.getElementById('posSubmitBtn');
    
    let totalQty = 0;
    let grand = 0;
    
    wrap.innerHTML = '';
    
    if (posCart.length === 0) {
        wrap.appendChild(emptyNotice);
        countBadge.textContent = '0 item';
        submitBtn.disabled = true;
        document.getElementById('posSubtotal').textContent = 'Rp0';
        document.getElementById('posGrandTotal').textContent = 'Rp0';
        calcPosChange();
        syncPosHiddenFields();
        return;
    }
    
    submitBtn.disabled = false;
    
    posCart.forEach((item, idx) => {
        totalQty += item.qty;
        grand += item.subtotal;
        
        const row = document.createElement('div');
        row.className = 'pos-slip-item';
        
        let porsiBadge = item.porsi === 'half_loyang' ? '<span class="tag tag-cat">½ Loyang</span>' : '<span class="tag tag-cat">1 Loyang</span>';
        let matangBadge = '';
        if (item.kematangan === 'setengah_matang') matangBadge = '<span class="tag tag-lumer">½ Matang Lumer</span>';
        else if (item.kematangan === 'tiga_perempat') matangBadge = '<span class="tag tag-lumer">¾ Matang</span>';
        else if (item.kematangan === 'matang') matangBadge = '<span class="tag tag-matang">Matang</span>';
        
        let topsText = '';
        if (item.toppings && item.toppings.length) {
            topsText = '<div class="pos-slip-topps">+ ' + item.toppings.map(t => escPos(t.nama)).join(', ') + '</div>';
        }
        
        row.innerHTML = `
            <img class="pos-slip-img" src="${escPos(item.gambar_src)}" alt="${escPos(item.nama)}" onerror="this.outerHTML='<div class=\\'pos-slip-img thumb-fallback\\'>${escPos(item.mono)}</div>'">
            <div class="pos-slip-desc">
                <h4>${escPos(item.nama)}</h4>
                <div class="pos-slip-tags">${porsiBadge} ${matangBadge}</div>
                ${topsText}
                <div class="pos-slip-actions">
                    <div class="stepper-qty">
                        <button type="button" onclick="chPosSlipQty(${idx}, -1)">−</button>
                        <b>${item.qty}</b>
                        <button type="button" onclick="chPosSlipQty(${idx}, 1)">+</button>
                    </div>
                    <span class="pos-slip-price">${posRp(item.subtotal)}</span>
                    <button type="button" class="pos-item-del" onclick="delPosSlipItem(${idx})" title="Hapus menu ini">✕</button>
                </div>
            </div>`;
        wrap.appendChild(row);
    });
    
    countBadge.textContent = totalQty + ' item';
    document.getElementById('posSubtotal').textContent = posRp(grand);
    document.getElementById('posGrandTotal').textContent = posRp(grand);
    calcPosChange();
    syncPosHiddenFields();
}

function chPosSlipQty(idx, delta) {
    if (!posCart[idx]) return;
    posCart[idx].qty += delta;
    if (posCart[idx].qty <= 0) {
        posCart.splice(idx, 1);
    } else {
        posCart[idx].qty = Math.min(20, posCart[idx].qty);
        posCart[idx].subtotal = posCart[idx].per * posCart[idx].qty;
    }
    renderPosSlip();
}

function delPosSlipItem(idx) {
    if (!posCart[idx]) return;
    posCart.splice(idx, 1);
    renderPosSlip();
}

function resetPosCart() {
    if (posCart.length === 0) return;
    if (confirm('Kosongkan semua menu di slip pesanan ini?')) {
        posCart = [];
        renderPosSlip();
    }
}

// Preset nama pembeli
function setPosNama(n) {
    const inp = document.getElementById('posNama');
    if (inp) {
        inp.value = n;
        inp.focus();
    }
}

// Tipe pesanan
function setPosTipe(t) {
    document.getElementById('posTipeVal').value = t;
    document.getElementById('posTipeTakeaway').classList.toggle('on', t === 'takeaway');
    document.getElementById('posTipeDineIn').classList.toggle('on', t === 'dine_in');
}

// Metode bayar
function setPosMetode(m) {
    document.getElementById('posMetodeVal').value = m;
    document.getElementById('posMetodeTunai').classList.toggle('on', m === 'tunai');
    document.getElementById('posMetodeQris').classList.toggle('on', m === 'qris');
    
    const calcBox = document.getElementById('posCashCalculator');
    if (calcBox) calcBox.style.display = m === 'tunai' ? 'block' : 'none';
}

// Kalkulator kembalian
function calcPosChange() {
    const grand = posCart.reduce((sum, it) => sum + it.subtotal, 0);
    const cashInp = document.getElementById('posCashInput');
    const changeTxt = document.getElementById('posChangeText');
    if (!cashInp || !changeTxt) return;
    
    const cash = parseInt(cashInp.value, 10) || 0;
    if (cash === 0) {
        changeTxt.textContent = 'Kembalian: Rp0';
        changeTxt.style.color = 'var(--muted)';
        return;
    }
    
    const diff = cash - grand;
    if (diff < 0) {
        changeTxt.textContent = 'Kurang: ' + posRp(Math.abs(diff));
        changeTxt.style.color = 'var(--err)';
    } else {
        changeTxt.textContent = 'Kembalian: ' + posRp(diff);
        changeTxt.style.color = 'var(--ok)';
    }
}

function setPosCashPreset(val) {
    const grand = posCart.reduce((sum, it) => sum + it.subtotal, 0);
    const cashInp = document.getElementById('posCashInput');
    if (!cashInp) return;
    
    if (val === 'exact') {
        cashInp.value = grand > 0 ? grand : '';
    } else {
        cashInp.value = val;
    }
    calcPosChange();
}

// Sinkronisasi data form ke input hidden untuk submission
function syncPosHiddenFields() {
    const hJson = document.getElementById('posCartJson');
    if (hJson) hJson.value = JSON.stringify(posCart);
    
    const container = document.getElementById('posHiddenFields');
    if (!container) return;
    container.innerHTML = '';
    
    posCart.forEach((item, idx) => {
        const pId = document.createElement('input');
        pId.type = 'hidden'; pId.name = 'pid[]'; pId.value = item.product_id;
        container.appendChild(pId);
        
        const pPorsi = document.createElement('input');
        pPorsi.type = 'hidden'; pPorsi.name = 'porsi[]'; pPorsi.value = item.porsi;
        container.appendChild(pPorsi);
        
        const pMatang = document.createElement('input');
        pMatang.type = 'hidden'; pMatang.name = 'matang[]'; pMatang.value = item.kematangan || '';
        container.appendChild(pMatang);
        
        const pQty = document.createElement('input');
        pQty.type = 'hidden'; pQty.name = 'qty[]'; pQty.value = item.qty;
        container.appendChild(pQty);
        
        if (item.toppings && item.toppings.length) {
            item.toppings.forEach(tp => {
                const pTop = document.createElement('input');
                pTop.type = 'hidden'; pTop.name = `tops[${idx}][]`; pTop.value = tp.id;
                container.appendChild(pTop);
            });
        }
    });
}

// Validasi & submit form POS
function submitPosForm(form) {
    if (posCart.length === 0) {
        if (window.notif) window.notif('Pesanan Kosong', 'Pilih minimal 1 menu sebelum menerbitkan tiket.', 'warning');
        else alert('Pilih minimal 1 menu sebelum menerbitkan tiket.');
        return false;
    }
    const namaInp = document.getElementById('posNama');
    if (!namaInp || namaInp.value.trim().length < 3) {
        if (window.notif) window.notif('Nama Belum Diisi', 'Masukkan nama pembeli minimal 3 huruf.', 'warning');
        else alert('Masukkan nama pembeli minimal 3 huruf.');
        namaInp.focus();
        return false;
    }
    syncPosHiddenFields();
    return true;
}

// Keyboard shortcuts & backdrop listeners
document.addEventListener('DOMContentLoaded', function() {
    const m = document.getElementById('modalPosCustom');
    if (m) {
        m.addEventListener('click', function(e) {
            if (e.target === m) closePosCustomizer();
        });
    }
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closePosCustomizer();
        if (e.key === '/' && !/INPUT|TEXTAREA|SELECT/.test((document.activeElement || {}).tagName || '')) {
            const s = document.getElementById('posSearchInput');
            if (s) { e.preventDefault(); s.focus(); }
        }
    });
});
</script>
