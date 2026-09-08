<?php
// user/us_antrean.php — Tiket digital + stepper status live (Stitch-styled)
// $koneksi tersedia. Mendukung ?no=PC-... untuk tiket spesifik.

$no = trim($_GET['no'] ?? '');
$baru = isset($_GET['baru']);
$order = null;
$items = [];

// Cari tiket: prioritas ?no=, lalu pesanan terakhir milik user login, lalu cookie sesi
if ($no !== '') {
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM orders WHERE no_antrean = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 's', $no);
    mysqli_stmt_execute($stmt);
    $order = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
} elseif (isset($_SESSION['user_id'])) {
    $uid = (int)$_SESSION['user_id'];
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $uid);
    mysqli_stmt_execute($stmt);
    $order = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    if ($order) $no = $order['no_antrean'];
}

// Riwayat milik user login (5 terakhir)
$riwayat = [];
if (isset($_SESSION['user_id'])) {
    $uid = (int)$_SESSION['user_id'];
    $stmt = mysqli_prepare($koneksi, "SELECT no_antrean, status_pesanan, total_bayar, created_at FROM orders WHERE user_id = ? ORDER BY id DESC LIMIT 5");
    mysqli_stmt_bind_param($stmt, 'i', $uid);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    while ($r = mysqli_fetch_assoc($rs)) $riwayat[] = $r;
    mysqli_stmt_close($stmt);
}

if ($order) {
    $oid = (int)$order['id'];
    $q = "SELECT oi.*, p.nama_menu FROM order_items oi JOIN products p ON p.id = oi.product_id WHERE oi.order_id = ? ORDER BY oi.id ASC";
    $stmt = mysqli_prepare($koneksi, $q);
    mysqli_stmt_bind_param($stmt, 'i', $oid);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    while ($r = mysqli_fetch_assoc($rs)) {
        $iid = (int)$r['id'];
        $st = mysqli_prepare($koneksi, "SELECT t.nama_topping FROM order_item_toppings oit JOIN toppings t ON t.id = oit.topping_id WHERE oit.order_item_id = ?");
        mysqli_stmt_bind_param($st, 'i', $iid);
        mysqli_stmt_execute($st);
        $rr = mysqli_stmt_get_result($st);
        $tn = [];
        while ($x = mysqli_fetch_assoc($rr)) $tn[] = $x['nama_topping'];
        mysqli_stmt_close($st);
        $r['tops'] = $tn;
        $items[] = $r;
    }
    mysqli_stmt_close($stmt);
}

// Stepper state
function step_class($orderStatus, $stepIdx) {
    // urutan: 0 pending, 1 dimasak, 2 siap_ambil, 3 selesai
    $map = ['pending'=>0,'dimasak'=>1,'siap_ambil'=>2,'selesai'=>3,'batal'=>-1];
    $cur = $map[$orderStatus] ?? 0;
    if ($cur === -1) return $stepIdx === 0 ? 'now' : '';
    if ($stepIdx < $cur) return 'done';
    if ($stepIdx === $cur) return $cur === 3 ? 'done' : 'now';
    return '';
}
function label_matang($k) {
    if ($k==='setengah_matang') return '1/2 Matang Lumer';
    if ($k==='tiga_perempat') return '3/4 Matang';
    if ($k==='matang') return 'Matang Sempurna';
    return '—';
}
?>

<div class="section-head">
  <div>
    <span class="eyebrow">Tiket antrean digital</span>
    <h2>Tiket Antrean Digital</h2>
    <p>Tunjukkan nomor ini ke kasir saat pengambilan.</p>
  </div>
  <a class="btn btn-ghost btn-sm" href="index.php">← Kembali ke Katalog</a>
</div>

<?php if ($baru && $order): ?>
  <div class="alert alert-ok">Pesanan diterima! Tiket <b><?php echo e($order['no_antrean']); ?></b> telah diterbitkan. Halaman ini otomatis refresh tiap 15 detik.</div>
  <script>
  // Pesanan baru sukses → kosongkan keranjang lokal agar tidak ter-checkout dua kali
  try {
    localStorage.removeItem('pl_cart');
    if (window.updateBadge) window.updateBadge();
  } catch (e) {}
  </script>
<?php endif; ?>

<?php if (!$order): ?>
  <div class="empty">
    <span class="eyebrow" style="display:block;margin-bottom:8px">Tiket antrean</span>
    <h3 style="color:var(--cocoa);margin:8px 0 4px">Belum ada tiket ditemukan</h3>
    <p>Buat pesanan dulu, atau masukkan nomor antrean kamu.</p>
    <form method="get" action="index.php" style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap;margin-top:12px">
      <input type="hidden" name="page" value="antrean">
      <input class="fin" name="no" placeholder="cth: PC-20260907-001" style="max-width:260px" autocomplete="off">
      <button class="btn btn-primary" type="submit">Cari Tiket</button>
    </form>
    <p style="margin-top:14px"><a class="btn btn-amber" href="index.php">Pesan Sekarang</a></p>
  </div>
<?php else: ?>
<div class="split" style="grid-template-columns:1fr">
<div class="ticket">
  <div class="ticket-head">
    <small style="letter-spacing:.08em;opacity:.8">PANCONG LUMER x CHICKEN ROLL • SELINDUNG BARU</small>
    <div class="no"><?php echo e($order['no_antrean']); ?></div>
    <div>
      <span class="status-badge st-<?php echo e($order['status_pesanan']); ?>"><?php echo e(label_status_pesanan($order['status_pesanan'])); ?></span>
      <span class="status-badge st-<?php echo e($order['status_bayar']); ?>" style="margin-left:6px"><?php echo e(label_status_bayar($order['status_bayar'])); ?></span>
    </div>
  </div>
  <div class="ticket-body">
    <div class="queue-note">
      <span>Estimasi panggangan <b>±10–15 menit</b>. Status diperbarui otomatis oleh kasir.</span>
    </div>

    <div class="stepper">
      <?php
      $steps = ['Menunggu Konfirmasi','Sedang Dipanggang','Siap Diambil','Selesai'];
      foreach ($steps as $i => $s):
        $cls = step_class($order['status_pesanan'], $i);
      ?>
      <div class="step <?php echo $cls; ?>"><div class="dot"><?php echo $cls==='done'?'✓':($i+1); ?></div><?php echo $s; ?></div>
      <?php endforeach; ?>
    </div>

    <?php if ($order['status_pesanan']==='batal'): ?>
      <div class="alert alert-err">Pesanan ini dibatalkan. Hubungi kasir untuk info lanjut.</div>
    <?php elseif ($order['status_bayar']==='rejected'): ?>
      <div class="alert alert-err">Bukti bayar ditolak kasir. Silakan hubungi kasir / buat pesanan ulang dengan bukti yang benar.</div>
    <?php elseif ($order['status_pesanan']==='siap_ambil'): ?>
      <div class="alert alert-ok">Pesanan <b>SIAP DIAMBIL</b>! Tunjukkan tiket ini ke kasir. Terima kasih!</div>
    <?php elseif ($order['status_bayar']==='pending' && $order['metode_bayar']==='qris'): ?>
      <div class="alert alert-info">Bukti QRIS kamu sedang diverifikasi kasir. Tetap di halaman ini ya.</div>
    <?php endif; ?>

    <table class="cart-table">
      <tbody>
        <tr><td style="color:var(--muted)">Pemesan</td><td style="text-align:right"><b><?php echo e($order['nama_pemesan']); ?></b> (<?php echo e($order['no_wa']); ?>)</td></tr>
        <tr><td style="color:var(--muted)">Tipe</td><td style="text-align:right"><?php echo $order['tipe_order']==='dine_in'?'Dine-in':'Takeaway'; ?> • <?php echo $order['metode_bayar']==='qris'?'QRIS':'Tunai'; ?></td></tr>
        <tr><td style="color:var(--muted)">Waktu pesan</td><td style="text-align:right"><?php echo e(tgl_indo($order['created_at'])); ?></td></tr>
      </tbody>
    </table>

    <h4 style="margin:16px 0 8px;color:var(--cocoa)"><span class="eyebrow">Rincian pesanan</span>Rincian Item</h4>
    <table class="cart-table">
      <thead><tr><th>Item</th><th style="text-align:center">Qty</th><th style="text-align:right">Subtotal</th></tr></thead>
      <tbody>
      <?php foreach ($items as $it): ?>
        <tr>
          <td><b><?php echo e($it['nama_menu']); ?></b><br>
            <small style="color:var(--muted)"><?php echo $it['porsi']==='half_loyang'?'1/2 Loyang':'1 Loyang'; ?><?php echo $it['kematangan'] ? ' • '.e(label_matang($it['kematangan'])) : ''; ?><?php echo $it['tops'] ? '<br>Topping: '.e(implode(', ', $it['tops'])) : ''; ?></small></td>
          <td style="text-align:center"><?php echo (int)$it['qty']; ?>x</td>
          <td style="text-align:right"><b><?php echo rupiah($it['subtotal']); ?></b></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <div class="mini-sum">
      <div class="row"><span>Subtotal</span><span><?php echo rupiah($order['total_bayar']); ?></span></div>
      <div class="row"><span>Biaya layanan</span><span class="free-tag">Gratis</span></div>
      <div class="row total"><span>Total</span><b><?php echo rupiah($order['total_bayar']); ?></b></div>
    </div>

    <div style="display:flex;gap:8px;margin-top:16px;flex-wrap:wrap">
      <button class="btn btn-ghost" onclick="location.reload()">Refresh Status</button>
      <a class="btn btn-amber" href="index.php">Pesan Lagi</a>
    </div>
    <p style="font-size:11px;color:var(--muted);text-align:center">Auto-refresh tiap 15 detik • Simpan/screenshot tiket ini.</p>
  </div>
</div>

<?php if ($riwayat): ?>
<div class="form-card" style="max-width:560px;margin:20px auto 0">
  <span class="eyebrow">Riwayat akun</span>
  <h4 style="margin:0 0 10px;color:var(--cocoa)">Pesanan Terakhir Kamu</h4>
  <?php foreach ($riwayat as $rw): ?>
    <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-top:1px solid var(--highest);font-size:13px;gap:8px">
      <a href="index.php?page=antrean&no=<?php echo urlencode($rw['no_antrean']); ?>"><b><?php echo e($rw['no_antrean']); ?></b></a>
      <span class="status-badge st-<?php echo e($rw['status_pesanan']); ?>"><?php echo e(label_status_pesanan($rw['status_pesanan'])); ?></span>
      <b><?php echo rupiah($rw['total_bayar']); ?></b>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
</div>

<script>
// Auto-refresh status tiap 15 detik (tanpa ?baru agar toast tak berulang)
setTimeout(function(){
  var u = new URL(window.location.href);
  u.searchParams.delete('baru');
  window.location.href = u.toString();
}, 15000);
</script>
<?php endif; ?>
