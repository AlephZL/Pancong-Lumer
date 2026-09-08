<?php
// admin/modPesanan/konfirmasiPesanan.php — Preview bukti QRIS + Terima/Tolak
// $koneksi tersedia. Akses via ?mod=pesanan&act=konfirmasi&no=PC-...

$no = trim($_GET['no'] ?? $_POST['no'] ?? '');
if ($no === '') {
    header('Location: ad_index.php?mod=pesanan&err=' . urlencode('Nomor antrean tidak valid.'));
    exit;
}

// Ambil order
$stmt = mysqli_prepare($koneksi, "SELECT * FROM orders WHERE no_antrean = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 's', $no);
mysqli_stmt_execute($stmt);
$order = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$order) {
    header('Location: ad_index.php?mod=pesanan&err=' . urlencode('Tiket tidak ditemukan.'));
    exit;
}

// Proses keputusan kasir
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['keputusan'])) {
    $kep = $_POST['keputusan'] === 'terima' ? 'terima' : 'tolak';
    if ($order['status_pesanan'] !== 'pending') {
        header('Location: ad_index.php?mod=pesanan&err=' . urlencode('Tiket sudah diproses sebelumnya.'));
        exit;
    }
    if ($kep === 'terima') {
        $stB = 'verified'; $stP = 'dimasak';
        $u = mysqli_prepare($koneksi, "UPDATE orders SET status_bayar=?, status_pesanan=? WHERE no_antrean=? AND status_pesanan='pending'");
        mysqli_stmt_bind_param($u, 'sss', $stB, $stP, $no);
        mysqli_stmt_execute($u);
        $ok = mysqli_stmt_affected_rows($u) > 0;
        mysqli_stmt_close($u);
        $msg = $ok ? "Tiket $no DITERIMA & mulai DIPANGGANG." : 'Gagal / sudah diproses kasir lain.';
        header('Location: ad_index.php?mod=pesanan&' . ($ok ? 'msg=' : 'err=') . urlencode($msg));
        exit;
    } else {
        $stB = 'rejected'; $stP = 'batal';
        $u = mysqli_prepare($koneksi, "UPDATE orders SET status_bayar=?, status_pesanan=? WHERE no_antrean=? AND status_pesanan='pending'");
        mysqli_stmt_bind_param($u, 'sss', $stB, $stP, $no);
        mysqli_stmt_execute($u);
        $ok = mysqli_stmt_affected_rows($u) > 0;
        mysqli_stmt_close($u);
        $msg = $ok ? "Tiket $no DITOLAK (bukti tidak valid)." : 'Gagal / sudah diproses kasir lain.';
        header('Location: ad_index.php?mod=pesanan&' . ($ok ? 'msg=' : 'err=') . urlencode($msg));
        exit;
    }
}

// Items
$oid = (int)$order['id'];
$items = [];
$rs = mysqli_query($koneksi, "SELECT oi.*, p.nama_menu FROM order_items oi JOIN products p ON p.id=oi.product_id WHERE oi.order_id=$oid ORDER BY oi.id ASC");
while ($r = mysqli_fetch_assoc($rs)) {
    $iid = (int)$r['id'];
    $tn = [];
    $rt = mysqli_query($koneksi, "SELECT t.nama_topping FROM order_item_toppings oit JOIN toppings t ON t.id=oit.topping_id WHERE oit.order_item_id=$iid");
    while ($x = mysqli_fetch_assoc($rt)) $tn[] = $x['nama_topping'];
    $r['tops'] = $tn;
    $items[] = $r;
}
$bukti = !empty($order['bukti_bayar']) ? '../gambar/' . $order['bukti_bayar'] : '';
?>

<a class="btn btn-ghost btn-sm" href="ad_index.php?mod=pesanan">← Kembali ke Live Orders</a>
<span class="eyebrow">Verifikasi pembayaran</span>
<h2 class="page-title">Verifikasi — <?php echo e($order['no_antrean']); ?>
  <span class="status-badge st-<?php echo e($order['status_pesanan']); ?>"><?php echo e(label_status_pesanan($order['status_pesanan'])); ?></span>
</h2>

<?php if ($order['status_pesanan'] !== 'pending'): ?>
  <div class="alert alert-info">Tiket ini sudah berstatus <b><?php echo e(label_status_pesanan($order['status_pesanan'])); ?></b>. Tidak dapat diverifikasi ulang.</div>
<?php endif; ?>

<div class="layout-2">
  <div class="form-card">
    <h3 style="margin-top:0;color:var(--cocoa)">Bukti Pembayaran</h3>
    <?php if ($order['metode_bayar'] === 'tunai'): ?>
      <div class="alert alert-info">Metode <b>TUNAI</b> — tidak ada berkas bukti. Pastikan uang diterima di laci sebelum menekan <b>Terima & Masak</b>.</div>
    <?php elseif ($bukti && is_file(__DIR__ . '/../../gambar/' . $order['bukti_bayar'])): ?>
      <a href="<?php echo e($bukti); ?>" target="_blank"><img class="bukti-full" src="<?php echo e($bukti); ?>" alt="Bukti bayar <?php echo e($order['no_antrean']); ?>"></a>
      <p style="font-size:12px;color:var(--muted)">Klik gambar untuk perbesar di tab baru. Cocokkan <b>nominal <?php echo rupiah($order['total_bayar']); ?></b> & waktu transfer.</p>
    <?php else: ?>
      <div class="alert alert-err">File bukti tidak ditemukan di server. Minta pelanggan menunjukkan bukti dari HP-nya sebelum menerima.</div>
    <?php endif; ?>

    <h3 style="color:var(--cocoa)">Rincian</h3>
    <table class="cart-table">
      <tbody>
        <tr><td style="color:var(--muted)">Pemesan</td><td style="text-align:right"><b><?php echo e($order['nama_pemesan']); ?></b> (<?php echo e($order['no_wa']); ?>)</td></tr>
        <tr><td style="color:var(--muted)">Tipe / Bayar</td><td style="text-align:right"><?php echo $order['tipe_order']==='dine_in'?'Dine-in':'Takeaway'; ?> • <?php echo strtoupper($order['metode_bayar']); ?></td></tr>
        <tr><td style="color:var(--muted)">Waktu</td><td style="text-align:right"><?php echo e(tgl_indo($order['created_at'])); ?></td></tr>
        <?php foreach ($items as $it): ?>
        <tr><td><?php echo e($it['nama_menu']); ?> (<?php echo (int)$it['qty']; ?>x)<br><small style="color:var(--muted)"><?php echo e($it['porsi']); ?><?php echo $it['kematangan'] ? ' • '.e($it['kematangan']) : ''; ?><?php echo $it['tops'] ? ' • '.e(implode(', ',$it['tops'])) : ''; ?></small></td>
        <td style="text-align:right"><b><?php echo rupiah($it['subtotal']); ?></b></td></tr>
        <?php endforeach; ?>
        <tr><td><b>Total</b></td><td style="text-align:right"><b style="color:var(--primary);font-size:17px"><?php echo rupiah($order['total_bayar']); ?></b></td></tr>
      </tbody>
    </table>
  </div>

  <div class="form-card">
    <h3 style="margin-top:0;color:var(--cocoa)">Keputusan Kasir</h3>
    <p style="font-size:13px;color:var(--muted)">Pastikan nominal & keaslian bukti sebelum menerima. Aksi tercatat atas nama <b><?php echo e($_SESSION['nama_lengkap'] ?? 'kasir'); ?></b>.</p>
    <?php if ($order['status_pesanan'] === 'pending'): ?>
    <form method="post" id="formVerifikasi">
      <input type="hidden" name="no" value="<?php echo e($order['no_antrean']); ?>">
      <input type="hidden" name="keputusan" id="inputKeputusan" value="terima">
      <div style="display:grid;gap:10px">
        <button class="btn btn-blue btn-block" type="submit" name="btn_terima" onclick="document.getElementById('inputKeputusan').value='terima'" data-confirm="TERIMA tiket <?php echo e($order['no_antrean']); ?> &amp; mulai DIPANGGANG?">Terima &amp; Mulai Masak</button>
        <button class="btn btn-red btn-block" type="submit" name="btn_tolak" onclick="document.getElementById('inputKeputusan').value='tolak'" data-confirm="TOLAK tiket <?php echo e($order['no_antrean']); ?>? Status bayar rejected &amp; pesanan batal. Pelanggan harus pesan ulang.">Tolak Bukti</button>
      </div>
    </form>
    <?php else: ?>
      <a class="btn btn-ghost btn-block" href="ad_index.php?mod=pesanan">Kembali ke Live Orders</a>
    <?php endif; ?>
    <div class="alert alert-info" style="margin-top:14px;font-size:12px">Alur: <b>Pending → (Terima) Dimasak → Siap Ambil → Selesai</b>. Penolakan mengubah status bayar <i>rejected</i> & pesanan <i>batal</i>.</div>
  </div>
</div>
