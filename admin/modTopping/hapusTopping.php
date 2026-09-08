<?php
// admin/modTopping/hapusTopping.php — Hapus topping ATAU aturan
$jenis = ($_GET['jenis'] ?? '') === 'rekom' ? 'rekom' : 'topping';
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: ad_index.php?mod=topping&err=' . urlencode('ID tidak valid.'));
    exit;
}

if ($jenis === 'rekom') {
    $del = mysqli_prepare($koneksi, "DELETE FROM topping_recommendations WHERE id=?");
    mysqli_stmt_bind_param($del, 'i', $id);
    if (mysqli_stmt_execute($del)) {
        mysqli_stmt_close($del);
        header('Location: ad_index.php?mod=topping&sub=rekom&msg=' . urlencode('Aturan #'.$id.' dihapus.'));
    } else {
        mysqli_stmt_close($del);
        header('Location: ad_index.php?mod=topping&sub=rekom&err=' . urlencode('Gagal menghapus aturan.'));
    }
    exit;
}

// Topping: tolak jika dipakai order_item_toppings; hapus aturan terkait otomatis via CASCADE
$stmt = mysqli_prepare($koneksi, "SELECT COUNT(*) AS c FROM order_item_toppings WHERE topping_id=?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$c = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
if ($c && (int)$c['c'] > 0) {
    header('Location: ad_index.php?mod=topping&err=' . urlencode('Topping tidak bisa dihapus: sudah dipakai '.$c['c'].'x transaksi. Nonaktifkan saja.'));
    exit;
}
$stmt = mysqli_prepare($koneksi, "SELECT nama_topping FROM toppings WHERE id=? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$tp = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
if (!$tp) {
    header('Location: ad_index.php?mod=topping&err=' . urlencode('Topping tidak ditemukan.'));
    exit;
}
$del = mysqli_prepare($koneksi, "DELETE FROM toppings WHERE id=?");
mysqli_stmt_bind_param($del, 'i', $id);
if (mysqli_stmt_execute($del)) {
    mysqli_stmt_close($del);
    header('Location: ad_index.php?mod=topping&msg=' . urlencode('Topping "'.$tp['nama_topping'].'" dihapus (aturan terkait ikut terhapus).'));
} else {
    mysqli_stmt_close($del);
    header('Location: ad_index.php?mod=topping&err=' . urlencode('Gagal menghapus topping.'));
}
exit;
