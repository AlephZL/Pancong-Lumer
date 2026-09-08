<?php
// admin/modMenu/tambahMenu.php
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
        $err = 'Kategori, nama (min 3 huruf), dan harga valid wajib diisi.';
    } else {
        $gambar = null;
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] !== UPLOAD_ERR_NO_FILE) {
            $f = $_FILES['gambar'];
            if ($f['error'] === UPLOAD_ERR_OK && $f['size'] <= 2*1024*1024) {
                $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg','jpeg','png','webp'], true)) {
                    $dir = pastikan_folder_gambar();
                    $gambar = 'menu_' . date('YmdHis') . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
                    if (!move_uploaded_file($f['tmp_name'], $dir . $gambar)) $gambar = null;
                }
            }
        }
        $st = mysqli_prepare($koneksi, "INSERT INTO products (category_id, nama_menu, deskripsi, harga_dasar, gambar, is_available) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($st, 'issisi', $cat, $nama, $desk, $hrg, $gambar, $av);
        if (mysqli_stmt_execute($st)) {
            mysqli_stmt_close($st);
            header('Location: ad_index.php?mod=menu&msg=' . urlencode('Menu "'.$nama.'" ditambahkan.'));
            exit;
        } else { $err = 'Gagal menyimpan.'; mysqli_stmt_close($st); }
    }
}
?>
<a class="btn btn-ghost btn-sm" href="ad_index.php?mod=menu">← Kembali</a>
<span class="eyebrow">Katalog gerai</span>
<h2 class="page-title">Tambah Menu</h2>
<p class="page-sub">Lengkapi kategori, harga dasar, dan foto. Menu langsung tampil di katalog pelanggan.</p>
<?php if (!empty($err)): ?><div class="alert alert-err"><?php echo e($err); ?></div><?php endif; ?>
<div class="form-card" style="max-width:640px">
<form method="post" enctype="multipart/form-data" onsubmit="return validasiAdmin(this)">
  <div class="form-grid">
    <div><label class="flabel">Kategori *</label><select class="fin" name="category_id" required>
      <option value="">— pilih —</option>
      <?php foreach($cats as $c): ?><option value="<?php echo (int)$c['id']; ?>"><?php echo e($c['nama_kategori']); ?></option><?php endforeach; ?>
    </select></div>
    <div><label class="flabel">Harga Dasar (Rp) *</label><input class="fin" type="number" name="harga_dasar" min="1000" step="500" required placeholder="18000"></div>
    <div class="full"><label class="flabel">Nama Menu *</label><input class="fin" name="nama_menu" required placeholder="cth: Pancong Choco Crunchy"></div>
    <div class="full"><label class="flabel">Deskripsi</label><textarea class="fin" name="deskripsi" rows="2" placeholder="Bahan & ciri khas…"></textarea></div>
    <div><label class="flabel">Foto (JPG/PNG ≤2MB)</label><input class="fin" type="file" name="gambar" accept=".jpg,.jpeg,.png,.webp"></div>
    <div><label class="flabel">Ketersediaan</label><label style="font-size:14px"><input type="checkbox" name="is_available" checked> Tersedia dijual</label></div>
  </div>
  <button class="btn btn-primary" style="margin-top:16px" type="submit" name="simpan">Simpan Menu</button>
</form>
</div>
