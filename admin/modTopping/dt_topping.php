<?php
// admin/modTopping/dt_topping.php — Master topping + aturan rekomendasi
$sub = $_GET['sub'] ?? 'topping';
if (!in_array($sub, ['topping','rekom'], true)) $sub = 'topping';

$tops = [];
$rs = mysqli_query($koneksi, "SELECT * FROM toppings ORDER BY id ASC");
while ($r = mysqli_fetch_assoc($rs)) $tops[] = $r;

$rekoms = [];
$q = "SELECT r.id, r.pesan_alasan,
             b.nama_topping AS base_nama, b.harga AS base_hrg,
             t.nama_topping AS rec_nama, t.harga AS rec_hrg
      FROM topping_recommendations r
      JOIN toppings b ON b.id = r.base_topping_id
      JOIN toppings t ON t.id = r.recommended_topping_id
      ORDER BY r.id ASC";
$rs = mysqli_query($koneksi, $q);
if ($rs) while ($r = mysqli_fetch_assoc($rs)) $rekoms[] = $r;
?>
<div class="toolbar">
  <div>
    <span class="eyebrow">Pelengkap menu</span>
    <h2 class="page-title">Topping & Rekomendasi</h2>
  </div>
  <span class="spacer"></span>
  <?php if ($sub==='topping'): ?>
    <a class="btn btn-primary btn-sm" href="ad_index.php?mod=topping&act=tambah">+ Tambah Topping</a>
  <?php else: ?>
    <a class="btn btn-primary btn-sm" href="ad_index.php?mod=topping&act=tambah&jenis=rekom">+ Tambah Aturan</a>
  <?php endif; ?>
</div>
<?php if (isset($_GET['msg'])): ?><div class="alert alert-ok"><?php echo e($_GET['msg']); ?></div><?php endif; ?>
<?php if (isset($_GET['err'])): ?><div class="alert alert-err"><?php echo e($_GET['err']); ?></div><?php endif; ?>

<div class="filter-bar">
  <a class="chip <?php echo $sub==='topping'?'active':''; ?>" href="ad_index.php?mod=topping&sub=topping">Master Topping</a>
  <a class="chip <?php echo $sub==='rekom'?'active':''; ?>" href="ad_index.php?mod=topping&sub=rekom">Aturan Rekomendasi (<?php echo count($rekoms); ?>)</a>
</div>

<?php if ($sub === 'topping'): ?>
<div class="tbl-wrap"><table class="tbl">
<thead><tr><th>ID</th><th>Nama Topping</th><th>Harga</th><th>Status</th><th>Aksi</th></tr></thead>
<tbody>
<?php foreach ($tops as $t): ?>
<tr>
  <td>#<?php echo (int)$t['id']; ?></td>
  <td><b><?php echo e($t['nama_topping']); ?></b></td>
  <td><b style="color:var(--primary)"><?php echo rupiah($t['harga']); ?></b></td>
  <td><?php echo ((int)$t['is_available']===1)?'<span class="status-badge st-verified">Tersedia</span>':'<span class="status-badge st-batal">Habis</span>'; ?></td>
  <td style="white-space:nowrap">
    <a class="btn btn-ghost btn-sm" href="ad_index.php?mod=topping&act=ubah&id=<?php echo (int)$t['id']; ?>">Ubah</a>
    <a class="btn btn-danger btn-sm" href="ad_index.php?mod=topping&act=hapus&id=<?php echo (int)$t['id']; ?>" data-confirm="Hapus topping <?php echo e($t['nama_topping']); ?>? Data dihapus permanen.">Hapus</a>
  </td>
</tr>
<?php endforeach; ?>
<?php if (empty($tops)): ?><tr><td colspan="5" style="text-align:center;color:var(--muted)">Belum ada topping.</td></tr><?php endif; ?>
</tbody></table></div>
<?php else: ?>
<div class="alert alert-info" style="font-size:13px">Logika: jika pelanggan memilih <b>Topping Basis</b>, sistem menyarankan <b>Topping Rekomendasi</b> di modal customizer + alasan. Contoh: Keju Parut → Choco Chips.</div>
<div class="tbl-wrap"><table class="tbl">
<thead><tr><th>ID</th><th>Jika Pilih (Basis)</th><th>→ Sarankan</th><th>Alasan</th><th>Aksi</th></tr></thead>
<tbody>
<?php foreach ($rekoms as $r): ?>
<tr>
  <td>#<?php echo (int)$r['id']; ?></td>
  <td><b><?php echo e($r['base_nama']); ?></b> <small>(<?php echo rupiah($r['base_hrg']); ?>)</small></td>
  <td><b style="color:var(--primary)"><?php echo e($r['rec_nama']); ?></b> <small>(<?php echo rupiah($r['rec_hrg']); ?>)</small></td>
  <td><small><?php echo e($r['pesan_alasan']); ?></small></td>
  <td style="white-space:nowrap">
    <a class="btn btn-ghost btn-sm" href="ad_index.php?mod=topping&act=ubah&jenis=rekom&id=<?php echo (int)$r['id']; ?>">Ubah</a>
    <a class="btn btn-danger btn-sm" href="ad_index.php?mod=topping&act=hapus&jenis=rekom&id=<?php echo (int)$r['id']; ?>" data-confirm="Hapus aturan <?php echo e($r['base_nama'].' → '.$r['rec_nama']); ?>? Rekomendasi tidak lagi muncul di customizer.">Hapus</a>
  </td>
</tr>
<?php endforeach; ?>
<?php if (empty($rekoms)): ?><tr><td colspan="5" style="text-align:center;color:var(--muted)">Belum ada aturan. Tambahkan dulu.</td></tr><?php endif; ?>
</tbody></table></div>
<?php endif; ?>
