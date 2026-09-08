<?php
// admin/modMenu/dt_menu.php — Tabel kelola menu
$rows = [];
$q = "SELECT p.*, c.nama_kategori, c.kode_kategori FROM products p
      JOIN categories c ON c.id=p.category_id ORDER BY c.id, p.id";
$rs = mysqli_query($koneksi, $q);
while ($r = mysqli_fetch_assoc($rs)) $rows[] = $r;
?>
<div class="toolbar">
  <div>
    <span class="eyebrow">Katalog gerai</span>
    <h2 class="page-title">Kelola Menu</h2>
  </div>
  <span class="spacer"></span>
  <a class="btn btn-primary btn-sm" href="ad_index.php?mod=menu&act=tambah">+ Tambah Menu</a>
</div>
<?php if (isset($_GET['msg'])): ?><div class="alert alert-ok"><?php echo e($_GET['msg']); ?></div><?php endif; ?>
<?php if (isset($_GET['err'])): ?><div class="alert alert-err"><?php echo e($_GET['err']); ?></div><?php endif; ?>
<div class="tbl-wrap"><table class="tbl">
<thead><tr><th>Foto</th><th>Menu</th><th>Kategori</th><th>Harga</th><th>Status</th><th>Aksi</th></tr></thead>
<tbody>
<?php foreach ($rows as $r):
  $img = !empty($r['gambar']) && is_file(__DIR__.'/../../gambar/'.$r['gambar'])
    ? '../gambar/'.rawurlencode($r['gambar']) : '';
?>
<tr>
  <td><?php if($img): ?><img class="thumb" src="<?php echo e($img); ?>" alt="<?php echo e($r['nama_menu']); ?>"><?php else: ?><div class="thumb thumb-fallback"><?php echo e(strtoupper(substr(trim($r['nama_menu']),0,1))); ?></div><?php endif; ?></td>
  <td><b><?php echo e($r['nama_menu']); ?></b><br><small style="color:var(--muted)"><?php echo e($r['deskripsi'] ?? ''); ?></small></td>
  <td><span class="tag tag-cat"><?php echo e($r['nama_kategori']); ?></span></td>
  <td><b style="color:var(--primary)"><?php echo rupiah($r['harga_dasar']); ?></b></td>
  <td><?php echo ((int)$r['is_available']===1) ? '<span class="status-badge st-verified">Tersedia</span>' : '<span class="status-badge st-batal">Habis</span>'; ?></td>
  <td style="white-space:nowrap">
    <a class="btn btn-ghost btn-sm" href="ad_index.php?mod=menu&act=ubah&id=<?php echo (int)$r['id']; ?>">Ubah</a>
    <a class="btn btn-danger btn-sm" href="ad_index.php?mod=menu&act=hapus&id=<?php echo (int)$r['id']; ?>" data-confirm="Hapus menu <?php echo e($r['nama_menu']); ?>? Data dihapus permanen dan tidak bisa dibatalkan.">Hapus</a>
  </td>
</tr>
<?php endforeach; ?>
</tbody></table></div>
