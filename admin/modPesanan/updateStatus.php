<?php
// admin/modPesanan/updateStatus.php — Ubah status dapur (POST only, PRG)
// Aksi: siap (dimasak->siap_ambil), selesai (siap_ambil->selesai), batal

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ad_index.php?mod=pesanan');
    exit;
}

$no = trim($_POST['no'] ?? '');
$aksi = $_POST['aksi'] ?? '';

$transisi = [
    'siap'    => ['dari' => 'dimasak',    'ke' => 'siap_ambil'],
    'selesai' => ['dari' => 'siap_ambil', 'ke' => 'selesai'],
    'batal'   => ['dari' => ['pending','dimasak','siap_ambil'], 'ke' => 'batal'],
];

if ($no === '' || !isset($transisi[$aksi])) {
    header('Location: ad_index.php?mod=pesanan&err=' . urlencode('Aksi tidak valid.'));
    exit;
}

// Ambil status saat ini (lock implisit via UPDATE ... WHERE status=)
$stmt = mysqli_prepare($koneksi, "SELECT status_pesanan FROM orders WHERE no_antrean=? LIMIT 1");
mysqli_stmt_bind_param($stmt, 's', $no);
mysqli_stmt_execute($stmt);
$cur = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$cur) {
    header('Location: ad_index.php?mod=pesanan&err=' . urlencode('Tiket tidak ditemukan.'));
    exit;
}

$dari = $transisi[$aksi]['dari'];
$ke = $transisi[$aksi]['ke'];
$boleh = is_array($dari) ? in_array($cur['status_pesanan'], $dari, true) : ($cur['status_pesanan'] === $dari);

if (!$boleh) {
    header('Location: ad_index.php?mod=pesanan&err=' . urlencode("Tiket $no berstatus " . label_status_pesanan($cur['status_pesanan']) . ", tidak bisa diubah ke aksi ini."));
    exit;
}

if (is_array($dari)) {
    // batal: update dari salah satu status aktif
    $u = mysqli_prepare($koneksi, "UPDATE orders SET status_pesanan=? WHERE no_antrean=? AND status_pesanan IN ('pending','dimasak','siap_ambil')");
    mysqli_stmt_bind_param($u, 'ss', $ke, $no);
} else {
    $u = mysqli_prepare($koneksi, "UPDATE orders SET status_pesanan=? WHERE no_antrean=? AND status_pesanan=?");
    mysqli_stmt_bind_param($u, 'sss', $ke, $no, $dari);
}
mysqli_stmt_execute($u);
$affected = mysqli_stmt_affected_rows($u);
mysqli_stmt_close($u);

if ($affected > 0) {
    $pesan = [
        'siap' => "Tiket $no: SIAP DIAMBIL. Silakan panggil pelanggan.",
        'selesai' => "Tiket $no: SELESAI dan masuk omzet.",
        'batal' => "Tiket $no: DIBATALKAN.",
    ][$aksi];
    header('Location: ad_index.php?mod=pesanan&msg=' . urlencode($pesan));
} else {
    header('Location: ad_index.php?mod=pesanan&err=' . urlencode("Tiket $no sudah diproses kasir lain / status berubah."));
}
exit;
