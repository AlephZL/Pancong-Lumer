<?php
// admin/modLaporan/dt_laporan.php — Riwayat + rekap omzet + filter tanggal
$tgl1 = $_GET['tgl1'] ?? date('Y-m-d');
$tgl2 = $_GET['tgl2'] ?? date('Y-m-d');
// Validasi format tanggal
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl1)) $tgl1 = date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl2)) $tgl2 = date('Y-m-d');
if ($tgl1 > $tgl2) { $tmp = $tgl1; $tgl1 = $tgl2; $tgl2 = $tmp; }

// Ringkasan periode
$sum = ['n'=>0,'omzet'=>0,'qris'=>0,'tunai'=>0,'batal'=>0];
$st = mysqli_prepare($koneksi, "SELECT COUNT(*) n, COALESCE(SUM(CASE WHEN status_pesanan!='batal' THEN total_bayar ELSE 0 END),0) omzet,
    COALESCE(SUM(CASE WHEN metode_bayar='qris' AND status_pesanan!='batal' THEN total_bayar ELSE 0 END),0) qris,
    COALESCE(SUM(CASE WHEN metode_bayar='tunai' AND status_pesanan!='batal' THEN total_bayar ELSE 0 END),0) tunai,
    COALESCE(SUM(CASE WHEN status_pesanan='batal' THEN 1 ELSE 0 END),0) batal
    FROM orders WHERE DATE(created_at) BETWEEN ? AND ?");
mysqli_stmt_bind_param($st, 'ss', $tgl1, $tgl2);
mysqli_stmt_execute($st);
$sum = array_merge($sum, mysqli_fetch_assoc(mysqli_stmt_get_result($st)) ?: []);
mysqli_stmt_close($st);

// Top menu periode
$topMenu = [];
$q = "SELECT p.nama_menu, SUM(oi.qty) AS qty, SUM(oi.subtotal) AS omzet
      FROM order_items oi
      JOIN products p ON p.id = oi.product_id
      JOIN orders o ON o.id = oi.order_id
      WHERE DATE(o.created_at) BETWEEN '$tgl1' AND '$tgl2' AND o.status_pesanan != 'batal'
      GROUP BY p.id, p.nama_menu ORDER BY qty DESC LIMIT 5";
$rs = mysqli_query($koneksi, $q);
if ($rs) while ($r = mysqli_fetch_assoc($rs)) $topMenu[] = $r;

// Daftar transaksi
$rows = [];
$st = mysqli_prepare($koneksi, "SELECT o.*, (SELECT GROUP_CONCAT(CONCAT(p.nama_menu,' (',oi.qty,'x)') SEPARATOR ', ') FROM order_items oi JOIN products p ON p.id=oi.product_id WHERE oi.order_id=o.id) AS ringkas
    FROM orders o WHERE DATE(o.created_at) BETWEEN ? AND ? ORDER BY o.id DESC LIMIT 200");
mysqli_stmt_bind_param($st, 'ss', $tgl1, $tgl2);
mysqli_stmt_execute($st);
$rs = mysqli_stmt_get_result($st);
while ($r = mysqli_fetch_assoc($rs)) $rows[] = $r;
mysqli_stmt_close($st);
?>
<span class="eyebrow">Rekap periode</span>
<h2 class="page-title">Riwayat & Omzet</h2>
<p class="page-sub">Filter tanggal untuk melihat transaksi, perbandingan QRIS / Tunai, menu terlaris, dan kelola pembersihan riwayat.</p>

<?php if (isset($_GET['msg'])): ?>
  <div class="alert alert-ok" style="margin:12px 0"><?php echo e($_GET['msg']); ?></div>
<?php endif; ?>
<?php if (isset($_GET['err'])): ?>
  <div class="alert alert-err" style="margin:12px 0"><?php echo e($_GET['err']); ?></div>
<?php endif; ?>

<div class="toolbar">
  <form method="get" action="ad_index.php" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
    <input type="hidden" name="mod" value="laporan">
    <label style="font-size:13px;font-weight:700">Dari <input class="fin" type="date" name="tgl1" value="<?php echo e($tgl1); ?>" style="width:auto"></label>
    <label style="font-size:13px;font-weight:700">Sampai <input class="fin" type="date" name="tgl2" value="<?php echo e($tgl2); ?>" style="width:auto"></label>
    <button class="btn btn-primary btn-sm" type="submit">Tampilkan</button>
    <a class="btn btn-ghost btn-sm" href="ad_index.php?mod=laporan">Hari Ini</a>
  </form>
  <span class="spacer"></span>
  <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
    <a class="btn btn-blue btn-sm" href="ad_index.php?mod=laporan&amp;act=export&amp;tgl1=<?php echo urlencode($tgl1); ?>&amp;tgl2=<?php echo urlencode($tgl2); ?>">Cetak Rekap Excel</a>
    <?php if (!empty($rows)): ?>
    <form method="post" action="ad_index.php?mod=laporan&amp;act=hapus" style="display:inline;margin:0" data-confirm="Hapus SEMUA transaksi (<?php echo count($rows); ?> tiket) pada periode <?php echo e($tgl1); ?> s/d <?php echo e($tgl2); ?>? Seluruh data transaksi dan rinciannya akan dihapus permanen.">
      <input type="hidden" name="aksi_hapus" value="periode">
      <input type="hidden" name="tgl1" value="<?php echo e($tgl1); ?>">
      <input type="hidden" name="tgl2" value="<?php echo e($tgl2); ?>">
      <button class="btn btn-red btn-sm" type="submit">Hapus Riwayat Periode Ini</button>
    </form>
    <?php endif; ?>
    <?php if ($sum['n'] > 0): ?>
    <form method="post" action="ad_index.php?mod=laporan&amp;act=hapus" style="display:inline;margin:0" data-confirm="PERINGATAN: Anda akan menghapus SELURUH riwayat transaksi di sistem secara permanen! Data yang dihapus tidak dapat dipulihkan kembali. Lanjutkan?">
      <input type="hidden" name="aksi_hapus" value="semua">
      <input type="hidden" name="tgl1" value="<?php echo e($tgl1); ?>">
      <input type="hidden" name="tgl2" value="<?php echo e($tgl2); ?>">
      <button class="btn btn-danger btn-sm" type="submit" style="color:#b91c1c;border-color:rgba(185,28,28,.3)">Bersihkan Semua</button>
    </form>
    <?php endif; ?>
  </div>
</div>

<div class="stat-grid">
  <div class="kcard"><span class="k-label">Transaksi</span><b><?php echo (int)$sum['n']; ?></b><span class="k-sub">Batal: <?php echo (int)$sum['batal']; ?></span></div>
  <div class="kcard accent"><span class="k-label">Omzet (di luar batal)</span><b><?php echo rupiah($sum['omzet']); ?></b><span class="k-sub"><?php echo e($tgl1); ?> s/d <?php echo e($tgl2); ?></span></div>
  <div class="kcard"><span class="k-label">QRIS</span><b><?php echo rupiah($sum['qris']); ?></b><span class="k-sub">pembayaran digital</span></div>
  <div class="kcard"><span class="k-label">Tunai</span><b><?php echo rupiah($sum['tunai']); ?></b><span class="k-sub">bayar di kasir</span></div>
</div>

<?php if ($topMenu): ?>
<div class="form-card" style="margin-bottom:16px">
  <span class="eyebrow">Menu terlaris</span>
  <h4 style="margin:0 0 8px;color:var(--cocoa)">Top 5 Menu Periode Ini</h4>
  <?php foreach ($topMenu as $i => $t): ?>
    <div style="display:flex;justify-content:space-between;font-size:13px;padding:6px 0;border-top:1px solid var(--highest)">
      <span><b>#<?php echo $i+1; ?></b> <?php echo e($t['nama_menu']); ?> <small style="color:var(--muted)">(<?php echo (int)$t['qty']; ?> terjual)</small></span>
      <b style="color:var(--primary)"><?php echo rupiah($t['omzet']); ?></b>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="tbl-wrap"><table class="tbl">
<thead><tr><th>Antrean</th><th>Waktu</th><th>Pemesan</th><th>Ringkasan</th><th>Bayar</th><th>Total</th><th>Status</th><th style="text-align:center">Aksi</th></tr></thead>
<tbody>
<?php foreach ($rows as $o): ?>
<tr>
  <td><b><?php echo e($o['no_antrean']); ?></b></td>
  <td><small><?php echo e(tgl_indo($o['created_at'])); ?></small></td>
  <td><?php echo e($o['nama_pemesan']); ?><br><small style="color:var(--muted)"><?php echo $o['tipe_order']==='dine_in'?'Dine-in':'Takeaway'; ?></small></td>
  <td><small><?php echo e($o['ringkas'] ?? '-'); ?></small></td>
  <td>
    <small><b><?php echo $o['metode_bayar']==='qris'?'QRIS':'Tunai'; ?></b></small><br>
    <span class="status-badge st-<?php echo e($o['status_bayar']); ?>" style="font-size:10.5px;padding:3px 10px;margin-top:3px"><?php echo e(label_status_bayar($o['status_bayar'])); ?></span>
  </td>
  <td><b style="color:var(--primary)"><?php echo rupiah($o['total_bayar']); ?></b></td>
  <td><span class="status-badge st-<?php echo e($o['status_pesanan']); ?>"><?php echo e(label_status_pesanan($o['status_pesanan'])); ?></span></td>
  <td style="white-space:nowrap;text-align:center">
    <form method="post" action="ad_index.php?mod=laporan&amp;act=hapus" style="display:inline;margin:0" data-confirm="Hapus riwayat transaksi tiket <?php echo e($o['no_antrean']); ?> (<?php echo e($o['nama_pemesan']); ?>)? Tindakan ini permanen.">
      <input type="hidden" name="aksi_hapus" value="single">
      <input type="hidden" name="order_id" value="<?php echo (int)$o['id']; ?>">
      <input type="hidden" name="tgl1" value="<?php echo e($tgl1); ?>">
      <input type="hidden" name="tgl2" value="<?php echo e($tgl2); ?>">
      <button type="submit" class="btn btn-red btn-sm" style="padding:4px 10px;font-size:11px">Hapus</button>
    </form>
  </td>
</tr>
<?php endforeach; ?>
<?php if (empty($rows)): ?><tr><td colspan="8" style="text-align:center;color:var(--muted)">Tidak ada transaksi pada periode ini.</td></tr><?php endif; ?>
</tbody></table></div>
