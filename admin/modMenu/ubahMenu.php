<?php
// admin/modMenu/ubahMenu.php
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$stmt = mysqli_prepare($koneksi, "SELECT * FROM products WHERE id=? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$prod = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
if (!$prod) { header('Location: ad_index.php?mod=menu&err='.urlencode('Menu tidak ditemukan.')); exit; }

$cats = [];
$rs = mysqli_query($koneksi, "SELECT id, nama_kategori FROM categories WHERE kode_kategori != 'topping' ORDER BY id");
while ($r = mysqli_fetch_assoc($rs)) $cats[] = $r;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan'])) {
    $cat = (int)($_POST['category_id'] ?? 0);
    $nama = trim($_POST['nama_menu'] ?? '');
    $desk = trim($_POST['deskripsi'] ?? '');
    $hrg = max(0, (int)($_POST['harga_dasar'] ?? 0));
    $av = isset($_POST['is_available']) ? 1 : 0;
    if ($cat <= 0 || strlen($nama) < 3 || $hrg <= 0) {
        $err = 'Kategori, nama, dan harga valid wajib diisi.';
    } else {
        $gambar = $prod['gambar'];
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] !== UPLOAD_ERR_NO_FILE) {
            $f = $_FILES['gambar'];
            if ($f['error'] === UPLOAD_ERR_OK && $f['size'] <= 2*1024*1024) {
                $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg','jpeg','png','webp'], true)) {
                    $dir = pastikan_folder_gambar();
                    $baru = 'menu_' . date('YmdHis') . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
                    if (move_uploaded_file($f['tmp_name'], $dir . $baru)) {
                        if ($gambar && is_file($dir . $gambar)) @unlink($dir . $gambar);
                        $gambar = $baru;
                    }
                }
            }
        }
        $st = mysqli_prepare($koneksi, "UPDATE products SET category_id=?, nama_menu=?, deskripsi=?, harga_dasar=?, gambar=?, is_available=? WHERE id=?");
        mysqli_stmt_bind_param($st, 'issisii', $cat, $nama, $desk, $hrg, $gambar, $av, $id);
        if (mysqli_stmt_execute($st)) {
            mysqli_stmt_close($st);
            header('Location: ad_index.php?mod=menu&msg=' . urlencode('Menu "'.$nama.'" diperbarui.'));
            exit;
        } else { $err = 'Gagal memperbarui.'; mysqli_stmt_close($st); }
        $prod = array_merge($prod, ['category_id'=>$cat,'nama_menu'=>$nama,'deskripsi'=>$desk,'harga_dasar'=>$hrg,'gambar'=>$gambar,'is_available'=>$av]);
    }
}
?>
<a class="btn btn-ghost btn-sm" href="ad_index.php?mod=menu">← Kembali</a>
<span class="eyebrow">Katalog gerai</span>
<h2 class="page-title">Ubah Menu</h2>
<?php if (!empty($err)): ?><div class="alert alert-err"><?php echo e($err); ?></div><?php endif; ?>
<div class="form-card" style="max-width:640px">
<form method="post" enctype="multipart/form-data" onsubmit="return validasiAdmin(this)">
  <input type="hidden" name="id" value="<?php echo (int)$prod['id']; ?>">
  <div class="form-grid">
    <div><label class="flabel">Kategori *</label><select class="fin" name="category_id" required>
      <?php foreach($cats as $c): ?><option value="<?php echo (int)$c['id']; ?>" <?php echo $c['id']==$prod['category_id']?'selected':''; ?>><?php echo e($c['nama_kategori']); ?></option><?php endforeach; ?>
    </select></div>
    <div><label class="flabel">Harga Dasar (Rp) *</label><input class="fin" type="number" name="harga_dasar" min="1000" step="500" required value="<?php echo (int)$prod['harga_dasar']; ?>"></div>
    <div class="full"><label class="flabel">Nama Menu *</label><input class="fin" name="nama_menu" required value="<?php echo e($prod['nama_menu']); ?>"></div>
    <div class="full"><label class="flabel">Deskripsi</label><textarea class="fin" name="deskripsi" rows="2"><?php echo e($prod['deskripsi']); ?></textarea></div>
    <div><label class="flabel">Ganti Foto</label><input class="fin" type="file" name="gambar" accept=".jpg,.jpeg,.png,.webp">
      <?php if(!empty($prod['gambar'])): ?><small style="color:var(--muted)">Saat ini: <?php echo e($prod['gambar']); ?></small><?php endif; ?></div>
    <div><label class="flabel">Ketersediaan</label><label style="font-size:14px"><input type="checkbox" name="is_available" <?php echo ((int)$prod['is_available']===1)?'checked':''; ?>> Tersedia dijual</label></div>
  </div>
  <button class="btn btn-primary" style="margin-top:16px" type="submit" name="simpan">Simpan Perubahan</button>
</form>
</div>
