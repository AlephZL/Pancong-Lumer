<?php
// admin/modPesanan/dt_pesanan.php — Live Order Board
// $koneksi tersedia dari ad_index.php

$filter = $_GET['status'] ?? 'aktif';
$allow = ['aktif','pending','dimasak','siap_ambil','selesai','batal','semua'];
if (!in_array($filter, $allow, true)) $filter = 'aktif';

$where = '';
if ($filter === 'aktif') {
    $where = "WHERE o.status_pesanan IN ('pending','dimasak','siap_ambil')";
} elseif ($filter !== 'semua') {
    $f = mysqli_real_escape_string($koneksi, $filter);
    $where = "WHERE o.status_pesanan = '$f'";
}
if ($filter === 'aktif' || $filter === 'semua') {
    $where .= ($where === '' ? 'WHERE ' : ' AND ') . "DATE(o.created_at) = CURDATE()";
}

$orders = [];
$q = "SELECT o.* FROM orders o $where ORDER BY
      FIELD(o.status_pesanan,'pending','dimasak','siap_ambil','selesai','batal'), o.id ASC LIMIT 100";
$rs = mysqli_query($koneksi, $q);
while ($r = mysqli_fetch_assoc($rs)) {
    // items ringkas
    $oid = (int)$r['id'];
    $it = [];
    $ri = mysqli_query($koneksi, "SELECT oi.qty, oi.porsi, oi.kematangan, p.nama_menu FROM order_items oi JOIN products p ON p.id=oi.product_id WHERE oi.order_id=$oid");
    while ($x = mysqli_fetch_assoc($ri)) $it[] = $x;
    $r['items'] = $it;
    $orders[] = $r;
}

function tab($key, $label, $cur) {
    $c = $key === $cur ? 'active' : '';
    return '<a class="chip '.$c.'" href="ad_index.php?mod=pesanan&status='.$key.'">'.$label.'</a>';
}
?>
<span class="eyebrow">Antrean dapur</span>
<h2 class="page-title">Live Orders <small style="font-size:13px;color:var(--muted);font-weight:600">auto-refresh 20 dtk</small></h2>
<p class="page-sub">Pantau tiket aktif hari ini, verifikasi pembayaran, dan majukan status panggangan.</p>

<div class="filter-bar">
  <?php echo tab('aktif','Aktif Hari Ini',$filter); ?>
  <?php echo tab('pending','Pending',$filter); ?>
  <?php echo tab('dimasak','Dimasak',$filter); ?>
  <?php echo tab('siap_ambil','Siap Ambil',$filter); ?>
  <?php echo tab('selesai','Selesai',$filter); ?>
  <?php echo tab('batal','Batal',$filter); ?>
  <?php echo tab('semua','Semua Hari Ini',$filter); ?>
</div>

<?php if (isset($_GET['msg'])): ?>
  <div class="alert alert-ok"><?php echo e($_GET['msg']); ?></div>
<?php endif; ?>
<?php if (isset($_GET['err'])): ?>
  <div class="alert alert-err"><?php echo e($_GET['err']); ?></div>
<?php endif; ?>

<?php if (empty($orders)): ?>
  <div class="empty"><p><b>Tidak ada pesanan pada filter ini.</b><br><span style="font-size:13px">Tiket baru akan muncul otomatis di sini.</span></p></div>
<?php else: ?>
<div class="tbl-wrap"><table class="tbl">
  <thead><tr>
    <th>Antrean / Waktu</th><th>Pemesan</th><th>Item</th><th>Bayar</th><th>Status</th><th>Aksi</th>
  </tr></thead>
  <tbody>
  <?php foreach ($orders as $o): ?>
    <tr>
      <td><b><?php echo e($o['no_antrean']); ?></b><br><small style="color:var(--muted)"><?php echo e(tgl_indo($o['created_at'])); ?></small></td>
      <td><?php echo e($o['nama_pemesan']); ?><br><small style="color:var(--muted)"><?php echo e($o['no_wa']); ?> • <?php echo $o['tipe_order']==='dine_in'?'Dine-in':'Takeaway'; ?></small></td>
      <td><small>
        <?php foreach ($o['items'] as $it): ?>
          • <?php echo e($it['nama_menu']); ?> (<?php echo (int)$it['qty']; ?>x)<br>
        <?php endforeach; ?>
        <b style="color:var(--primary)"><?php echo rupiah($o['total_bayar']); ?></b>
      </small></td>
      <td>
        <div style="display:flex;flex-direction:column;gap:5px;align-items:flex-start">
          <span style="font-weight:700;color:var(--cocoa);font-size:13px"><?php echo $o['metode_bayar']==='qris'?'QRIS':'Tunai'; ?></span>
          <span class="status-badge st-<?php echo e($o['status_bayar']); ?>"><?php echo e(label_status_bayar($o['status_bayar'])); ?></span>
          <?php if (!empty($o['bukti_bayar'])): ?><a href="../gambar/<?php echo rawurlencode($o['bukti_bayar']); ?>" target="_blank" style="font-size:11.5px;font-weight:600;color:var(--primary)">Lihat bukti</a><?php endif; ?>
        </div>
      </td>
      <td><span class="status-badge st-<?php echo e($o['status_pesanan']); ?>"><?php echo e(label_status_pesanan($o['status_pesanan'])); ?></span></td>
      <td style="white-space:nowrap">
        <div style="display:inline-flex;gap:6px;align-items:center">
        <?php if ($o['status_pesanan']==='pending'): ?>
          <a class="btn btn-blue btn-sm" href="ad_index.php?mod=pesanan&act=konfirmasi&no=<?php echo urlencode($o['no_antrean']); ?>">Verifikasi</a>
        <?php endif; ?>
        <?php if ($o['status_pesanan']==='dimasak'): ?>
          <form method="post" action="ad_index.php?mod=pesanan&act=update" style="display:inline;margin:0" data-confirm="Tandai tiket <?php echo e($o['no_antrean']); ?> SIAP DIAMBIL? Pelanggan akan dipanggil.">
            <input type="hidden" name="no" value="<?php echo e($o['no_antrean']); ?>">
            <input type="hidden" name="aksi" value="siap">
            <button class="btn btn-blue btn-sm" type="submit">Siap Ambil</button>
          </form>
        <?php endif; ?>
        <?php if ($o['status_pesanan']==='siap_ambil'): ?>
          <form method="post" action="ad_index.php?mod=pesanan&act=update" style="display:inline;margin:0" data-confirm="Selesaikan tiket <?php echo e($o['no_antrean']); ?>? Status menjadi SELESAI dan masuk omzet.">
            <input type="hidden" name="no" value="<?php echo e($o['no_antrean']); ?>">
            <input type="hidden" name="aksi" value="selesai">
            <button class="btn btn-blue btn-sm" type="submit">Selesai</button>
          </form>
        <?php endif; ?>
        <?php if (in_array($o['status_pesanan'], ['pending','dimasak','siap_ambil'], true)): ?>
          <form method="post" action="ad_index.php?mod=pesanan&act=update" style="display:inline;margin:0" data-confirm="Batalkan pesanan <?php echo e($o['no_antrean']); ?>? Aksi ini tidak bisa dibatalkan.">
            <input type="hidden" name="no" value="<?php echo e($o['no_antrean']); ?>">
            <input type="hidden" name="aksi" value="batal">
            <button class="btn btn-red btn-sm" type="submit">Batal</button>
          </form>
        <?php endif; ?>
        <?php if (in_array($o['status_pesanan'], ['selesai','batal'], true)): ?>
          <form method="post" action="ad_index.php?mod=laporan&amp;act=hapus" style="display:inline;margin:0" data-confirm="Hapus riwayat tiket <?php echo e($o['no_antrean']); ?> secara permanen?">
            <input type="hidden" name="aksi_hapus" value="single">
            <input type="hidden" name="order_id" value="<?php echo (int)$o['id']; ?>">
            <input type="hidden" name="from" value="pesanan">
            <button class="btn btn-red btn-sm" type="submit" style="padding:4px 9px;font-size:11px">Hapus</button>
          </form>
        <?php endif; ?>
        </div>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table></div>
<?php endif; ?>

<script>setTimeout(()=>{ if(!document.querySelector('.modal-backdrop.open')) location.reload(); }, 20000);</script>
