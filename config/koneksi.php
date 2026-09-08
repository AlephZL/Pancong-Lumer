<?php
// =====================================================
// config/koneksi.php
// Koneksi database terpusat (mysqli_connect)
// DB: db_pancong | Host: localhost | User: root | Pass: (kosong)
// =====================================================

$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = "";
$DB_NAME = "db_pancong";

$koneksi = @mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if (!$koneksi) {
    // Gagal koneksi: tampilkan halaman ramah tanpa membocorkan kredensial.
    // Saat sidang, pastikan MySQL Laragon aktif & database.sql sudah diimport.
    http_response_code(500);
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Koneksi Gagal — Pancong Lumer</title>
        <style>
            body{font-family:'Plus Jakarta Sans',system-ui,sans-serif;background:#fcf9f8;color:#1b1b1c;
                display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;padding:24px}
            .box{background:#fff;border:1px solid #e5e2e1;border-radius:16px;padding:32px;max-width:520px;
                box-shadow:0 8px 24px -4px rgba(59,30,8,.08);text-align:center}
            h1{color:#b31312;font-size:22px;margin:0 0 12px}
            code{background:#f6f3f2;padding:2px 8px;border-radius:8px;font-size:13px}
            ol{text-align:left;font-size:14px;line-height:1.8}
        </style>
    </head>
    <body>
        <div class="box">
            <h1>Gagal Terhubung ke Database</h1>
            <p style="font-size:14px;line-height:1.7">
                Sistem tidak dapat terhubung ke MySQL. Ini bukan error kode program,
                melainkan layanan database belum jalan / database belum dibuat.
            </p>
            <ol>
                <li>Nyalakan <b>MySQL</b> di Laragon.</li>
                <li>Buat database <code>db_pancong</code>.</li>
                <li>Import file <code>database.sql</code> via phpMyAdmin.</li>
                <li>Refresh halaman ini.</li>
            </ol>
            <p style="font-size:12px;color:#5b403c">Detail teknis (log server saja): <?php echo htmlspecialchars(mysqli_connect_error() ?? 'unknown'); ?></p>
        </div>
    </body>
    </html>
    <?php
    exit;
}

mysqli_set_charset($koneksi, "utf8mb4");

// -----------------------------------------------------
// Helper global (dipakai semua modul)
// -----------------------------------------------------

/** Escape output HTML (anti-XSS). */
function e($str) {
    return htmlspecialchars((string)($str ?? ''), ENT_QUOTES, 'UTF-8');
}

/** Format rupiah: 18000 -> Rp18.000 */
function rupiah($angka) {
    return 'Rp' . number_format((int)$angka, 0, ',', '.');
}

/** Tanggal Indonesia singkat: 2026-09-07 -> 07 Sep 2026 */
function tgl_indo($datetime) {
    if (empty($datetime)) return '-';
    $bulan = [1=>'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    $ts = strtotime($datetime);
    if (!$ts) return e($datetime);
    return date('d', $ts) . ' ' . $bulan[(int)date('n', $ts)] . ' ' . date('Y H:i', $ts);
}

/** Label status pesanan untuk badge. */
function label_status_pesanan($s) {
    $map = [
        'pending'    => 'Menunggu Konfirmasi',
        'dimasak'    => 'Sedang Dipanggang',
        'siap_ambil' => 'Siap Diambil',
        'selesai'    => 'Selesai',
        'batal'      => 'Dibatalkan',
    ];
    return $map[$s] ?? $s;
}

/** Label status bayar. */
function label_status_bayar($s) {
    $map = ['pending' => 'Menunggu Pembayaran', 'verified' => 'Terverifikasi', 'rejected' => 'Ditolak'];
    return $map[$s] ?? $s;
}

/** Pastikan folder upload tersedia. */
function pastikan_folder_gambar() {
    // index.php di root, admin/ad_index.php di subfolder — cari root via __DIR__.
    $kandidat = [
        __DIR__ . '/../gambar/',
        __DIR__ . '/../../gambar/',
        dirname(__DIR__) . '/gambar/',
    ];
    foreach ($kandidat as $dir) {
        if (is_dir(dirname($dir))) {
            if (!is_dir($dir)) @mkdir($dir, 0775, true);
            return rtrim($dir, '/\\') . DIRECTORY_SEPARATOR;
        }
    }
    return __DIR__ . '/../gambar/';
}
