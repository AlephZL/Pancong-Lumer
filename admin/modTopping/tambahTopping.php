<?php
// admin/modTopping/tambahTopping.php — Tambah topping ATAU aturan (?jenis=rekom)
$jenis = ($_GET['jenis'] ?? '') === 'rekom' ? 'rekom' : 'topping';

$tops = [];
$rs = mysqli_query($koneksi, "SELECT id, nama_topping FROM toppings ORDER BY id ASC");
while ($r = mysqli_fetch_assoc($rs)) $tops[] = $r;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan'])) {
    if ($jenis === 'rekom') {
        $base = (int)($_POST['base_topping_id'] ?? 0);
        $rec  = (int)($_POST['recommended_topping_id'] ?? 0);
        $alasan = trim($_POST['pesan_alasan'] ?? '');
        if ($base <= 0 || $rec <= 0) {
            $err = 'Pilih topping basis & rekomendasi.';
        } elseif ($base === $rec) {
            $err = 'Basis dan rekomendasi tidak boleh sama.';
        } elseif (strlen($alasan) < 5) {
            $err = 'Alasan minimal 5 huruf (ditampilkan ke pelanggan).';
        } else {
            $st = mysqli_prepare($koneksi, "INSERT INTO topping_recommendations (base_topping_id, recommended_topping_id, pesan_alasan) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($st, 'iis', $base, $rec, $alasan);
            if (mysqli_stmt_execute($st)) {
                mysqli_stmt_close($st);
                header('Location: ad_index.php?mod=topping&sub=rekom&msg=' . urlencode('Aturan rekomendasi ditambahkan.'));
                exit;
            } else {
                $err = (mysqli_errno($koneksi) == 1062) ? 'Pasangan aturan ini sudah ada.' : 'Gagal menyimpan aturan.';
                mysqli_stmt_close($st);
            }
        }
    } else {
        $nama = trim($_POST['nama_topping'] ?? '');
        $hrg = max(0, (int)($_POST['harga'] ?? 0));
        $av = isset($_POST['is_available']) ? 1 : 0;
        if (strlen($nama) < 3) { $err = 'Nama topping minimal 3 huruf.'; }
        else {
            $st = mysqli_prepare($koneksi, "INSERT INTO toppings (nama_topping, harga, is_available) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($st, 'sii', $nama, $hrg, $av);
            if (mysqli_stmt_execute($st)) {
                mysqli_stmt_close($st);
                header('Location: ad_index.php?mod=topping&msg=' . urlencode('Topping "'.$nama.'" ditambahkan.'));
                exit;
            } else { $err = 'Gagal menyimpan.'; mysqli_stmt_close($st); }
        }
    }
}
?>
<a class="btn btn-ghost btn-sm" href="ad_index.php?mod=topping<?php echo $jenis==='rekom'?'&sub=rekom':''; ?>">← Kembali</a>
<span class="eyebrow">Pelengkap menu</span>
<h2 class="page-title"><?php echo $jenis==='rekom' ? 'Tambah Aturan Rekomendasi' : 'Tambah Topping'; ?></h2>
<p class="page-sub"><?php echo $jenis==='rekom' ? 'Atur pasangan topping yang disarankan otomatis di customizer.' : 'Tambah varian topping baru beserta harganya.'; ?></p>
<?php if (!empty($err)): ?><div class="alert alert-err"><?php echo e($err); ?></div><?php endif; ?>
<div class="form-card" style="max-width:600px">
<?php if ($jenis === 'rekom'): ?>
<form method="post" onsubmit="return validasiAdmin(this)">
  <label class="flabel">Jika pelanggan pilih (Basis) *</label>
  <select class="fin" name="base_topping_id" required style="margin-bottom:12px">
    <option value="">— pilih —</option>
    <?php foreach($tops as $t): ?><option value="<?php echo (int)$t['id']; ?>"><?php echo e($t['nama_topping']); ?></option><?php endforeach; ?>
  </select>
  <label class="flabel">Maka sarankan (Rekomendasi) *</label>
  <select class="fin" name="recommended_topping_id" required style="margin-bottom:12px">
    <option value="">— pilih —</option>
    <?php foreach($tops as $t): ?><option value="<?php echo (int)$t['id']; ?>"><?php echo e($t['nama_topping']); ?></option><?php endforeach; ?>
  </select>
  <label class="flabel">Pesan Alasan *</label>
  <textarea class="fin" name="pesan_alasan" rows="2" required placeholder="cth: Keju gurih paling cocok dipadukan Choco Chips manis."></textarea>
  <button class="btn btn-primary" style="margin-top:14px" type="submit" name="simpan">Simpan Aturan</button>
</form>
<?php else: ?>
<form method="post" onsubmit="return validasiAdmin(this)">
  <div class="form-grid">
    <div class="full"><label class="flabel">Nama Topping *</label><input class="fin" name="nama_topping" required placeholder="cth: Keju Parut"></div>
    <div><label class="flabel">Harga (Rp) *</label><input class="fin" type="number" name="harga" min="0" step="500" required value="5000"></div>
    <div><label class="flabel">Status</label><label style="font-size:14px"><input type="checkbox" name="is_available" checked> Tersedia</label></div>
  </div>
  <button class="btn btn-primary" style="margin-top:14px" type="submit" name="simpan">Simpan Topping</button>
</form>
<?php endif; ?>
</div>
