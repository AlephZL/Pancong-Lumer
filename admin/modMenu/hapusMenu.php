<?php
// admin/modMenu/hapusMenu.php — Hapus + bersihkan file foto (tolak jika dipakai order)
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: ad_index.php?mod=menu&err=' . urlencode('ID tidak valid.'));
    exit;
}
// Cek keterpakaian
$stmt = mysqli_prepare($koneksi, "SELECT COUNT(*) AS c FROM order_items WHERE product_id=?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$c = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
if ($c && (int)$c['c'] > 0) {
    header('Location: ad_index.php?mod=menu&err=' . urlencode('Menu tidak bisa dihapus: sudah dipakai '.$c['c'].'x transaksi. Nonaktifkan saja (stok habis).'));
    exit;
}
$stmt = mysqli_prepare($koneksi, "SELECT gambar, nama_menu FROM products WHERE id=? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$prod = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
if (!$prod) {
    header('Location: ad_index.php?mod=menu&err=' . urlencode('Menu tidak ditemukan.'));
    exit;
}
$del = mysqli_prepare($koneksi, "DELETE FROM products WHERE id=?");
mysqli_stmt_bind_param($del, 'i', $id);
if (mysqli_stmt_execute($del)) {
    mysqli_stmt_close($del);
    if (!empty($prod['gambar'])) {
        $f = pastikan_folder_gambar() . $prod['gambar'];
        if (is_file($f)) @unlink($f);
    }
    header('Location: ad_index.php?mod=menu&msg=' . urlencode('Menu "'.$prod['nama_menu'].'" dihapus.'));
} else {
    mysqli_stmt_close($del);
    header('Location: ad_index.php?mod=menu&err=' . urlencode('Gagal menghapus.'));
}
exit;
