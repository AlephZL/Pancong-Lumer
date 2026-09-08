<?php
// admin/ad_header.php — Pembuka HTML + shell admin
// Variabel dari ad_index.php: $judulMod, $mod
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo e($judulMod ?? 'Dasbor'); ?> — Panel Kasir Pancong Lumer</title>
<link rel="stylesheet" href="../css/style.css?v=<?php echo filemtime(__DIR__ . '/../css/style.css'); ?>">
<script src="../js/validasi.js?v=<?php echo filemtime(__DIR__ . '/../js/validasi.js'); ?>" defer></script>
</head>
<body>
<div class="admin-shell">
