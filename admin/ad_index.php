<?php
// =====================================================
// admin/ad_index.php — Back-End Router (Admin/Kasir)
// Routes: ?mod=beranda|menu|topping|pesanan|laporan & ?act=...
// Proteksi: role admin, redirect ke login.php
// =====================================================
session_start();
require_once __DIR__ . '/../config/koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$mod = isset($_GET['mod']) ? $_GET['mod'] : 'beranda';
$act = isset($_GET['act']) ? $_GET['act'] : '';

// Whitelist router (anti-LFI)
$ROUTES = [
    'beranda' => ['' => 'ad_beranda.php'],
    'menu'    => [
        ''       => 'modMenu/dt_menu.php',
        'tambah' => 'modMenu/tambahMenu.php',
        'ubah'   => 'modMenu/ubahMenu.php',
        'hapus'  => 'modMenu/hapusMenu.php',
    ],
    'topping' => [
        ''       => 'modTopping/dt_topping.php',
        'tambah' => 'modTopping/tambahTopping.php',
        'ubah'   => 'modTopping/ubahTopping.php',
        'hapus'  => 'modTopping/hapusTopping.php',
    ],
    'pesanan' => [
        ''           => 'modPesanan/dt_pesanan.php',
        'konfirmasi' => 'modPesanan/konfirmasiPesanan.php',
        'update'     => 'modPesanan/updateStatus.php',
    ],
    'laporan' => [
        ''       => 'modLaporan/dt_laporan.php',
        'export' => 'modLaporan/export_excel.php',
        'hapus'  => 'modLaporan/hapusLaporan.php',
    ],
];

if (!isset($ROUTES[$mod])) $mod = 'beranda';
if (!array_key_exists($act, $ROUTES[$mod])) $act = '';

$konten = __DIR__ . '/' . $ROUTES[$mod][$act];
if (!is_file($konten)) {
    $konten = __DIR__ . '/ad_beranda.php';
    $mod = 'beranda';
}

// Download file (seperti export Excel) langsung diproses tanpa wrapper HTML header/sidebar/footer
if ($mod === 'laporan' && $act === 'export') {
    require $konten;
    exit;
}

// Judul halaman untuk topbar (profesional, tanpa emoji)
$JUDUL = [
    'beranda' => 'Dasbor & POS',
    'pesanan' => 'Live Orders',
    'menu'    => 'Kelola Menu',
    'topping' => 'Topping & Rekomendasi',
    'laporan' => 'Riwayat & Omzet',
];
$judulMod = $JUDUL[$mod] ?? 'Dasbor & POS';

// Layout: header (html+head+shell open) + sidebar + konten + footer
require_once __DIR__ . '/ad_header.php';
require_once __DIR__ . '/ad_sidebar.php';
require $konten;
require_once __DIR__ . '/ad_footer.php';
