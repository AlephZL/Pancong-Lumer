<?php
// =====================================================
// index.php — Front-End Router (Pelanggan)
// Default: user/us_beranda.php
// =====================================================
session_start();
require_once __DIR__ . '/config/koneksi.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'beranda';

// Whitelist routes (anti-LFI)
$routes = [
    'beranda'  => 'user/us_beranda.php',
    'checkout' => 'user/us_checkout.php',
    'antrean'  => 'user/us_antrean.php',
];

if (!array_key_exists($page, $routes)) {
    $page = 'beranda';
}
$file = __DIR__ . '/' . $routes[$page];
if (!is_file($file)) {
    http_response_code(404);
    die('Halaman tidak ditemukan.');
}

// Layout: header + konten + footer
require_once __DIR__ . '/user/us_header.php';
require_once $file;
require_once __DIR__ . '/user/us_footer.php';
