<?php
// admin/modTopping/ubahTopping.php — Ubah topping ATAU aturan (?jenis=rekom)
$jenis = ($_GET['jenis'] ?? '') === 'rekom' ? 'rekom' : 'topping';
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id <= 0) {
    header('Location: ad_index.php?mod=topping' . ($jenis==='rekom'?'&sub=rekom':'') . '&err=' . urlencode('ID tidak valid.'));
    exit;
}

$tops = [];
$rs = mysqli_query($koneksi, "SELECT id, nama_topping FROM toppings ORDER BY id ASC");
while ($r = mysqli_fetch_assoc($rs)) $tops[] = $r;

if ($jenis === 'rekom') {
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM topping_recommendations WHERE id=? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    if (!$data) { header('Location: ad_index.php?mod=topping&sub=rekom&err='.urlencode('Aturan tidak ditemukan.')); exit; }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan'])) {
        $base = (int)($_POST['base_topping_id'] ?? 0);
        $rec  = (int)($_POST['recommended_topping_id'] ?? 0);
        $alasan = trim($_POST['pesan_alasan'] ?? '');
        if ($base <= 0 || $rec <= 0) $err = 'Pilih topping basis & rekomendasi.';
        elseif ($base === $rec) $err = 'Basis dan rekomendasi tidak boleh sama.';
        elseif (strlen($alasan) < 5) $err = 'Alasan minimal 5 huruf.';
        else {
            $st = mysqli_prepare($koneksi, "UPDATE topping_recommendations SET base_topping_id=?, recommended_topping_id=?, pesan_alasan=? WHERE id=?");
            mysqli_stmt_bind_param($st, 'iisi', $base, $rec, $alasan, $id);
            if (mysqli_stmt_execute($st)) {
                mysqli_stmt_close($st);
                header('Location: ad_index.php?mod=topping&sub=rekom&msg=' . urlencode('Aturan #'.$id.' diperbarui.'));
                exit;
            } else {
                $err = (mysqli_errno($koneksi) == 1062) ? 'Pasangan aturan ini sudah ada.' : 'Gagal memperbarui.';
                mysqli_stmt_close($st);
            }
        }
        $data = array_merge($data, ['base_topping_id'=>$base??$data['base_topping_id'],'recommended_topping_id'=>$rec??$data['recommended_topping_id'],'pesan_alasan'=>$alasan??$data['pesan_alasan']]);
    }
} else {
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM toppings WHERE id=? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    if (!$data) { header('Location: ad_index.php?mod=topping&err='.urlencode('Topping tidak ditemukan.')); exit; }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan'])) {
        $nama = trim($_POST['nama_topping'] ?? '');
        $hrg = max(0, (int)($_POST['harga'] ?? 0));
        $av = isset($_POST['is_available']) ? 1 : 0;
        if (strlen($nama) < 3) $err = 'Nama topping minimal 3 huruf.';
        else {
            $st = mysqli_prepare($koneksi, "UPDATE toppings SET nama_topping=?, harga=?, is_available=? WHERE id=?");
            mysqli_stmt_bind_param($st, 'siii', $nama, $hrg, $av, $id);
            if (mysqli_stmt_execute($st)) {
                mysqli_stmt_close($st);
                header('Location: ad_index.php?mod=topping&msg=' . urlencode('Topping "'.$nama.'" diperbarui.'));
                exit;
            } else { $err = 'Gagal memperbarui.'; mysqli_stmt_close($st); }
        }
        $data = array_merge($data, ['nama_topping'=>$nama??$data['nama_topping'],'harga'=>$hrg??$data['harga'],'is_available'=>$av??$data['is_available']]);
    }
}
?>
<a class="btn btn-ghost btn-sm" href="ad_index.php?mod=topping<?php echo $jenis==='rekom'?'&sub=rekom':''; ?>">← Kembali</a>
<span class="eyebrow">Pelengkap menu</span>
<h2 class="page-title"><?php echo $jenis==='rekom' ? 'Ubah Aturan #'.(int)$id : 'Ubah Topping'; ?></h2>
<?php if (!empty($err)): ?><div class="alert alert-err"><?php echo e($err); ?></div><?php endif; ?>
<div class="form-card" style="max-width:600px">
<?php if ($jenis === 'rekom'): ?>
<form method="post" onsubmit="return validasiAdmin(this)">
  <input type="hidden" name="id" value="<?php echo (int)$id; ?>">
  <label class="flabel">Jika pelanggan pilih (Basis) *</label>
  <select class="fin" name="base_topping_id" required style="margin-bottom:12px">
    <?php foreach($tops as $t): ?><option value="<?php echo (int)$t['id']; ?>" <?php echo $t['id']==$data['base_topping_id']?'selected':''; ?>><?php echo e($t['nama_topping']); ?></option><?php endforeach; ?>
  </select>
  <label class="flabel">Maka sarankan *</label>
  <select class="fin" name="recommended_topping_id" required style="margin-bottom:12px">
    <?php foreach($tops as $t): ?><option value="<?php echo (int)$t['id']; ?>" <?php echo $t['id']==$data['recommended_topping_id']?'selected':''; ?>><?php echo e($t['nama_topping']); ?></option><?php endforeach; ?>
  </select>
  <label class="flabel">Pesan Alasan *</label>
  <textarea class="fin" name="pesan_alasan" rows="2" required><?php echo e($data['pesan_alasan']); ?></textarea>
  <button class="btn btn-primary" style="margin-top:14px" type="submit" name="simpan">Simpan Perubahan</button>
</form>
<?php else: ?>
<form method="post" onsubmit="return validasiAdmin(this)">
  <input type="hidden" name="id" value="<?php echo (int)$id; ?>">
  <div class="form-grid">
    <div class="full"><label class="flabel">Nama Topping *</label><input class="fin" name="nama_topping" required value="<?php echo e($data['nama_topping']); ?>"></div>
    <div><label class="flabel">Harga (Rp) *</label><input class="fin" type="number" name="harga" min="0" step="500" required value="<?php echo (int)$data['harga']; ?>"></div>
    <div><label class="flabel">Status</label><label style="font-size:14px"><input type="checkbox" name="is_available" <?php echo ((int)$data['is_available']===1)?'checked':''; ?>> Tersedia</label></div>
  </div>
  <button class="btn btn-primary" style="margin-top:14px" type="submit" name="simpan">Simpan Perubahan</button>
</form>
<?php endif; ?>
</div>
