<?php
// admin/modLaporan/hapusLaporan.php — Hapus riwayat transaksi (salah satu, per periode, atau seluruh)
// Hanya dapat diakses oleh admin

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$aksi = $_POST['aksi_hapus'] ?? $_GET['aksi_hapus'] ?? '';
$tgl1 = $_POST['tgl1'] ?? $_GET['tgl1'] ?? date('Y-m-d');
$tgl2 = $_POST['tgl2'] ?? $_GET['tgl2'] ?? date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl1)) $tgl1 = date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl2)) $tgl2 = date('Y-m-d');
if ($tgl1 > $tgl2) { $tmp = $tgl1; $tgl1 = $tgl2; $tgl2 = $tmp; }

$from = $_POST['from'] ?? $_GET['from'] ?? 'laporan';
$redirectUrl = ($from === 'pesanan')
    ? 'ad_index.php?mod=pesanan'
    : "ad_index.php?mod=laporan&tgl1=" . urlencode($tgl1) . "&tgl2=" . urlencode($tgl2);

function hapus_file_bukti($namaFile) {
    if (!empty($namaFile)) {
        $path = pastikan_folder_gambar() . $namaFile;
        if (is_file($path)) @unlink($path);
    }
}

// 1. Hapus salah satu transaksi
if ($aksi === 'single') {
    $orderId = (int)($_POST['order_id'] ?? $_GET['order_id'] ?? 0);
    if ($orderId <= 0) {
        header("Location: $redirectUrl&err=" . urlencode('ID transaksi tidak valid.'));
        exit;
    }

    // Ambil data bukti bayar dan no antrean
    $st = mysqli_prepare($koneksi, "SELECT id, no_antrean, bukti_bayar FROM orders WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($st, 'i', $orderId);
    mysqli_stmt_execute($st);
    $order = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
    mysqli_stmt_close($st);

    if (!$order) {
        header("Location: $redirectUrl&err=" . urlencode('Transaksi tidak ditemukan atau sudah dihapus.'));
        exit;
    }

    // Hapus bukti bayar fisik jika ada
    hapus_file_bukti($order['bukti_bayar']);

    // Hapus order (CASCADE menghapus order_items & order_item_toppings)
    $del = mysqli_prepare($koneksi, "DELETE FROM orders WHERE id = ?");
    mysqli_stmt_bind_param($del, 'i', $orderId);
    if (mysqli_stmt_execute($del)) {
        mysqli_stmt_close($del);
        header("Location: $redirectUrl&msg=" . urlencode('Transaksi tiket ' . $order['no_antrean'] . ' berhasil dihapus permanen.'));
    } else {
        mysqli_stmt_close($del);
        header("Location: $redirectUrl&err=" . urlencode('Gagal menghapus transaksi dari database.'));
    }
    exit;
}

// 2. Hapus riwayat periode terpilih
if ($aksi === 'periode') {
    // Ambil semua bukti bayar pada periode ini
    $st = mysqli_prepare($koneksi, "SELECT bukti_bayar FROM orders WHERE DATE(created_at) BETWEEN ? AND ?");
    mysqli_stmt_bind_param($st, 'ss', $tgl1, $tgl2);
    mysqli_stmt_execute($st);
    $res = mysqli_stmt_get_result($st);
    $count = 0;
    while ($r = mysqli_fetch_assoc($res)) {
        $count++;
        hapus_file_bukti($r['bukti_bayar']);
    }
    mysqli_stmt_close($st);

    if ($count === 0) {
        header("Location: $redirectUrl&err=" . urlencode('Tidak ada transaksi pada periode terpilih untuk dihapus.'));
        exit;
    }

    $del = mysqli_prepare($koneksi, "DELETE FROM orders WHERE DATE(created_at) BETWEEN ? AND ?");
    mysqli_stmt_bind_param($del, 'ss', $tgl1, $tgl2);
    if (mysqli_stmt_execute($del)) {
        $affected = mysqli_stmt_affected_rows($del);
        mysqli_stmt_close($del);
        header("Location: $redirectUrl&msg=" . urlencode("Berhasil menghapus $affected transaksi pada periode $tgl1 s/d $tgl2."));
    } else {
        mysqli_stmt_close($del);
        header("Location: $redirectUrl&err=" . urlencode('Gagal menghapus transaksi periode terpilih.'));
    }
    exit;
}

// 3. Hapus seluruh riwayat transaksi di sistem
if ($aksi === 'semua') {
    // Ambil semua bukti bayar
    $res = mysqli_query($koneksi, "SELECT bukti_bayar FROM orders");
    if ($res) {
        while ($r = mysqli_fetch_assoc($res)) {
            hapus_file_bukti($r['bukti_bayar']);
        }
    }

    $del = mysqli_query($koneksi, "DELETE FROM orders");
    if ($del) {
        $affected = mysqli_affected_rows($koneksi);
        header("Location: $redirectUrl&msg=" . urlencode("Seluruh riwayat transaksi ($affected tiket) telah dibersihkan secara permanen."));
    } else {
        header("Location: $redirectUrl&err=" . urlencode('Gagal membersihkan riwayat transaksi.'));
    }
    exit;
}

header("Location: $redirectUrl");
exit;
