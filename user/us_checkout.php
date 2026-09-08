<?php
// user/us_checkout.php — Review keranjang, form pemesan, bayar, antrean (Stitch-styled)
// $koneksi tersedia dari index.php. Keranjang dikirim via hidden cartJson (localStorage).

$err = '';
$ok  = '';

// Prefill jika login
$prefNama = $_SESSION['nama_lengkap'] ?? '';
$prefWa   = '';

// --- PROSES CHECKOUT ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'checkout') {
    $nama   = trim($_POST['nama_pemesan'] ?? '');
    $wa     = trim($_POST['no_wa'] ?? '');
    $tipe   = ($_POST['tipe_order'] ?? 'takeaway') === 'dine_in' ? 'dine_in' : 'takeaway';
    $metode = ($_POST['metode_bayar'] ?? 'qris') === 'tunai' ? 'tunai' : 'qris';
    $cartRaw = $_POST['cartJson'] ?? '[]';
    $cart = json_decode($cartRaw, true);

    // Validasi dasar
    if (strlen($nama) < 3) {
        $err = 'Nama pemesan minimal 3 huruf.';
    } elseif (!preg_match('/^08[0-9]{7,13}$/', $wa)) {
        $err = 'No. WA harus diawali 08, 9–15 digit.';
    } elseif (!is_array($cart) || count($cart) === 0) {
        $err = 'Keranjang masih kosong. Pilih menu dulu di katalog.';
    } else {
        // Hitung ulang server-side (anti-tampering harga)
        $items = [];
        $grand = 0;
        $valid = true;
        foreach ($cart as $ci) {
            $pid = (int)($ci['product_id'] ?? 0);
            $qty = min(20, max(1, (int)($ci['qty'] ?? 1)));
            $porsi = ($ci['porsi'] ?? '1_loyang') === 'half_loyang' ? 'half_loyang' : '1_loyang';
            $matang = $ci['kematangan'] ?? null;
            if (!in_array($matang, ['matang','setengah_matang','tiga_perempat', null, ''], true)) $matang = null;
            if ($matang === '') $matang = null;
            $topIds = [];
            if (!empty($ci['toppings']) && is_array($ci['toppings'])) {
                foreach ($ci['toppings'] as $t) {
                    $tid = (int)(is_array($t) ? ($t['id'] ?? 0) : $t);
                    if ($tid > 0) $topIds[] = $tid;
                }
            }
            // Ambil produk
            $stmt = mysqli_prepare($koneksi, "SELECT id, category_id, nama_menu, harga_dasar, is_available,
                (SELECT kode_kategori FROM categories WHERE id = products.category_id) AS kode
                FROM products WHERE id = ? LIMIT 1");
            mysqli_stmt_bind_param($stmt, 'i', $pid);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $prod = mysqli_fetch_assoc($res);
            mysqli_stmt_close($stmt);
            if (!$prod || (int)$prod['is_available'] !== 1) { $valid = false; $err = 'Ada menu yang sudah tidak tersedia. Ulangi pilihan.'; break; }

            $isRoll = ($prod['kode'] === 'chicken_roll');
            if ($isRoll) { $porsi = '1_loyang'; $matang = null; }
            $base = (int)$prod['harga_dasar'];
            if (!$isRoll && $porsi === 'half_loyang') $base = (int)round($base / 2);

            // Harga topping dari DB
            $tops = [];
            $topSum = 0;
            foreach (array_unique($topIds) as $tid) {
                $st = mysqli_prepare($koneksi, "SELECT id, nama_topping, harga, is_available FROM toppings WHERE id = ? LIMIT 1");
                mysqli_stmt_bind_param($st, 'i', $tid);
                mysqli_stmt_execute($st);
                $rs = mysqli_stmt_get_result($st);
                $tr = mysqli_fetch_assoc($rs);
                mysqli_stmt_close($st);
                if (!$tr || (int)$tr['is_available'] !== 1) continue;
                $tops[] = ['id' => (int)$tr['id'], 'harga' => (int)$tr['harga']];
                $topSum += (int)$tr['harga'];
            }
            $per = $base + $topSum;
            $sub = $per * $qty;
            $grand += $sub;
            $items[] = ['product_id'=>$pid,'porsi'=>$porsi,'kematangan'=>$matang,'qty'=>$qty,
                        'subtotal'=>$sub,'tops'=>$tops];
        }

        if ($valid) {
            if ($grand <= 0) { $err = 'Total tidak valid.'; $valid = false; }
        }

        // Upload bukti QRIS
        $namaFile = null;
        if ($valid && $metode === 'qris') {
            if (!isset($_FILES['bukti_bayar']) || $_FILES['bukti_bayar']['error'] === UPLOAD_ERR_NO_FILE) {
                $err = 'Wajib unggah bukti transfer QRIS (JPG/PNG, maks 2MB).'; $valid = false;
            } else {
                $f = $_FILES['bukti_bayar'];
                if ($f['error'] !== UPLOAD_ERR_OK) { $err = 'Upload gagal (kode '.$f['error'].').'; $valid = false; }
                else {
                    $allow = ['image/jpeg'=>'jpg','image/png'=>'png','image/jpg'=>'jpg'];
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = $finfo ? finfo_file($finfo, $f['tmp_name']) : '';
                    if ($finfo) finfo_close($finfo);
                    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
                    if (!isset($allow[$mime]) && !in_array($ext, ['jpg','jpeg','png'], true)) {
                        $err = 'Bukti harus file JPG/PNG.'; $valid = false;
                    } elseif ($f['size'] > 2*1024*1024) {
                        $err = 'Ukuran bukti maks 2MB.'; $valid = false;
                    } else {
                        $dir = pastikan_folder_gambar();
                        $namaFile = 'bukti_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . ($allow[$mime] ?? $ext);
                        if (!move_uploaded_file($f['tmp_name'], $dir . $namaFile)) {
                            $err = 'Gagal menyimpan bukti. Periksa folder gambar/ writable.'; $valid = false;
                        }
                    }
                }
            }
        }

        // Simpan transaksional + nomor antrean thread-safe
        if ($valid) {
            mysqli_begin_transaction($koneksi);
            try {
                $today = date('Y-m-d');
                // Kunci baris hari ini agar urutan tidak ganda (row-lock)
                $q = mysqli_prepare($koneksi, "SELECT COALESCE(MAX(urutan_harian),0) AS mx FROM orders WHERE DATE(created_at) = ? FOR UPDATE");
                mysqli_stmt_bind_param($q, 's', $today);
                mysqli_stmt_execute($q);
                $rs = mysqli_stmt_get_result($q);
                $row = mysqli_fetch_assoc($rs);
                mysqli_stmt_close($q);
                $urut = ((int)($row['mx'] ?? 0)) + 1;
                $no = 'PC-' . date('Ymd') . '-' . str_pad($urut, 3, '0', STR_PAD_LEFT);

                $uid = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
                $stBayar = 'pending';      // QRIS & tunai online: menunggu verifikasi kasir
                $stPesan = 'pending';

                if ($uid) {
                    $ins = mysqli_prepare($koneksi, "INSERT INTO orders (no_antrean, urutan_harian, user_id, nama_pemesan, no_wa, tipe_order, metode_bayar, bukti_bayar, status_bayar, status_pesanan, total_bayar) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    mysqli_stmt_bind_param($ins, 'siisssssssi', $no, $urut, $uid, $nama, $wa, $tipe, $metode, $namaFile, $stBayar, $stPesan, $grand);
                } else {
                    $ins = mysqli_prepare($koneksi, "INSERT INTO orders (no_antrean, urutan_harian, user_id, nama_pemesan, no_wa, tipe_order, metode_bayar, bukti_bayar, status_bayar, status_pesanan, total_bayar) VALUES (?, ?, NULL, ?, ?, ?, ?, ?, ?, ?, ?)");
                    mysqli_stmt_bind_param($ins, 'sisssssssi', $no, $urut, $nama, $wa, $tipe, $metode, $namaFile, $stBayar, $stPesan, $grand);
                }
                if (!mysqli_stmt_execute($ins)) throw new Exception('Gagal menyimpan pesanan.');
                $orderId = mysqli_insert_id($koneksi);
                mysqli_stmt_close($ins);

                foreach ($items as $it) {
                    $mat = $it['kematangan']; // bisa null
                    $si = mysqli_prepare($koneksi, "INSERT INTO order_items (order_id, product_id, porsi, kematangan, qty, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
                    mysqli_stmt_bind_param($si, 'iissii', $orderId, $it['product_id'], $it['porsi'], $mat, $it['qty'], $it['subtotal']);
                    if (!mysqli_stmt_execute($si)) throw new Exception('Gagal menyimpan item.');
                    $itemId = mysqli_insert_id($koneksi);
                    mysqli_stmt_close($si);
                    foreach ($it['tops'] as $tp) {
                        $st2 = mysqli_prepare($koneksi, "INSERT INTO order_item_toppings (order_item_id, topping_id, harga_topping) VALUES (?, ?, ?)");
                        mysqli_stmt_bind_param($st2, 'iii', $itemId, $tp['id'], $tp['harga']);
                        if (!mysqli_stmt_execute($st2)) throw new Exception('Gagal menyimpan topping.');
                        mysqli_stmt_close($st2);
                    }
                }
                mysqli_commit($koneksi);
                // Sukses → ke halaman antrean + kosongkan keranjang via flag
                header('Location: index.php?page=antrean&no=' . urlencode($no) . '&baru=1');
                exit;
            } catch (Exception $ex) {
                mysqli_rollback($koneksi);
                if (!empty($namaFile)) @unlink(pastikan_folder_gambar() . $namaFile);
                // Retry sekali jika nomor ganda (race condition)
                if (strpos($ex->getMessage(), 'Duplicate') !== false || mysqli_errno($koneksi) == 1062) {
                    $err = 'Antrean penuh sesaat, silakan tekan Bayar sekali lagi.';
                } else {
                    $err = 'Gagal memproses pesanan. Coba lagi. (' . e($ex->getMessage()) . ')';
                }
            }
        }
    }
}

// Cek file QRIS statis milik gerai
$qrisImg = is_file(__DIR__ . '/../gambar/qris.png') ? 'gambar/qris.png' : '';
?>

<div class="section-head">
  <div>
    <span class="eyebrow">Checkout pesanan</span>
    <h2>Checkout Pesanan</h2>
    <p>Periksa kembali, isi data, pilih bayar, terima tiket antrean.</p>
  </div>
  <a class="btn btn-ghost btn-sm" href="index.php">← Tambah Menu</a>
</div>

<?php if ($err): ?><div class="alert alert-err"><?php echo e($err); ?></div><?php endif; ?>

<form method="post" enctype="multipart/form-data" onsubmit="return validasiCheckout()">
<input type="hidden" name="aksi" value="checkout">
<input type="hidden" name="cartJson" id="cartJson" value="[]">

<div class="split">
  <!-- Kiri: keranjang + data -->
  <div>
    <div class="form-card" style="margin-bottom:16px">
      <span class="eyebrow">Langkah 1</span>
      <h3>Keranjang Kamu</h3>
      <div class="tbl-wrap" style="box-shadow:none;border:0">
      <table class="cart-table">
        <thead><tr><th>Menu & Kustom</th><th style="text-align:center">Qty</th><th style="text-align:right">Subtotal</th><th></th></tr></thead>
        <tbody id="cartBody"></tbody>
      </table>
      </div>
      <div class="mini-sum">
        <div class="row"><span>Subtotal</span><span id="miniSubCo">Rp0</span></div>
        <div class="row"><span>Biaya layanan</span><span class="free-tag">Gratis</span></div>
        <div class="row total"><span>Total Bayar</span><b><span id="grandTotal">Rp0</span></b></div>
      </div>
      <p style="font-size:12px;color:var(--muted);margin:8px 0 0">Harga dihitung ulang di server saat bayar (anti-tampering).</p>
    </div>

    <div class="form-card" style="margin-bottom:16px">
      <span class="eyebrow">Langkah 2</span>
      <h3>Data Pemesan & Tipe</h3>
      <div class="tipe-switch" style="margin-bottom:14px">
        <button type="button" id="tipeTakeaway" class="on" onclick="setTipe('takeaway')">Takeaway</button>
        <button type="button" id="tipeDineIn" onclick="setTipe('dine_in')">Makan di Tempat</button>
      </div>
      <!-- Radio aktual untuk submit (disinkronkan oleh setTipe via JS) -->
      <div style="display:none">
        <label><input type="radio" name="tipe_order" value="takeaway" checked> Takeaway</label>
        <label><input type="radio" name="tipe_order" value="dine_in"> Dine-in</label>
      </div>
      <div class="form-grid">
        <div><label class="flabel">Nama Pemesan *</label><input class="fin" name="nama_pemesan" value="<?php echo e($prefNama); ?>" placeholder="cth: Budi Santoso"><div class="ferr" id="err_nama_pemesan"></div></div>
        <div><label class="flabel">No. WhatsApp *</label><input class="fin" name="no_wa" inputmode="numeric" placeholder="cth: 081271234567"><div class="ferr" id="err_no_wa"></div></div>
      </div>
    </div>
  </div>

  <!-- Kanan: bayar (sticky ala Stitch, tetap tampil di mobile) -->
  <aside class="pay-aside">
    <div class="mini-cart pay-sticky">
      <div class="mini-head">
        <div><span class="eyebrow">Langkah 3</span><h3 style="margin:0">Pembayaran</h3></div>
      </div>
      <div class="pay-grid">
        <label class="pay-card"><input type="radio" name="metode_bayar" value="qris" checked onchange="togglePay()"> <b>QRIS</b><br><small>Scan & unggah bukti transfer</small></label>
        <label class="pay-card"><input type="radio" name="metode_bayar" value="tunai" onchange="togglePay()"> <b>Tunai</b><br><small>Bayar di kasir gerai</small></label>
      </div>

      <div id="qrisArea">
        <div class="qris-box">
          <small style="opacity:.8;letter-spacing:.05em">QRIS STATIS GERAI</small>
          <?php if ($qrisImg): ?>
            <img src="<?php echo e($qrisImg); ?>" alt="QRIS Gerai" style="width:170px;border-radius:12px;margin:10px auto">
          <?php else: ?>
            <div class="qris-qr">PANCONG LUMER<br>SELINDUNG BARU<br><span style="font-size:11px">NMID: ID1024XXXXXX</span></div>
          <?php endif; ?>
          <div style="font-size:13px">a.n. Pancong Lumer Selindung Baru</div>
          <div style="font-size:11px;opacity:.75">Admin dapat mengganti dengan menaruh file <b>gambar/qris.png</b></div>
        </div>
        <div id="buktiWrap" style="margin-top:12px">
          <label class="flabel">Unggah Bukti Transfer * (JPG/PNG, maks 2MB)</label>
          <input class="fin" type="file" name="bukti_bayar" accept=".jpg,.jpeg,.png">
          <div class="ferr" id="err_bukti_bayar"></div>
        </div>
      </div>

      <div class="queue-note" style="margin-top:14px">
        <span>Setelah bayar kamu menerima <b>tiket PC-YYYYMMDD-XXX</b>: <i>Menunggu → Dipanggang → Siap Diambil</i>.</span>
      </div>
      <button class="mini-cta" id="btnBayar" type="submit">Buat Pesanan & Ambil Antrean</button>
      <p class="mini-note">Tanpa login pun bisa pesan. <a href="login.php"><b>Masuk</b></a> untuk riwayat otomatis.</p>
    </div>
  </aside>
</div>
</form>

<script>
// Angka #miniSubCo disinkronkan langsung dari renderCheckoutTable() di validasi.js.
// Tidak perlu observer tambahan di sini.
</script>
