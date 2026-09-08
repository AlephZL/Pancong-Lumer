<?php
// admin/modLaporan/export_excel.php — Rekapitulasi Laporan Penjualan & Omzet Excel
// Kompatibel penuh dengan Microsoft Excel, WPS, LibreOffice, dan Google Sheets

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$tgl1 = $_GET['tgl1'] ?? date('Y-m-d');
$tgl2 = $_GET['tgl2'] ?? date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl1)) $tgl1 = date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl2)) $tgl2 = date('Y-m-d');
if ($tgl1 > $tgl2) { $tmp = $tgl1; $tgl1 = $tgl2; $tgl2 = $tmp; }

$namaKasir = $_SESSION['nama_lengkap'] ?? 'Admin Kasir';
$waktuUnduh = date('d-m-Y H:i:s');

// 1. Ringkasan Periode
$sum = ['n'=>0, 'omzet'=>0, 'qris'=>0, 'tunai'=>0, 'batal'=>0, 'sukses'=>0];
$st = mysqli_prepare($koneksi, "SELECT COUNT(*) AS n,
    COALESCE(SUM(CASE WHEN status_pesanan!='batal' THEN total_bayar ELSE 0 END),0) AS omzet,
    COALESCE(SUM(CASE WHEN metode_bayar='qris' AND status_pesanan!='batal' THEN total_bayar ELSE 0 END),0) AS qris,
    COALESCE(SUM(CASE WHEN metode_bayar='tunai' AND status_pesanan!='batal' THEN total_bayar ELSE 0 END),0) AS tunai,
    COALESCE(SUM(CASE WHEN status_pesanan='batal' THEN 1 ELSE 0 END),0) AS batal,
    COALESCE(SUM(CASE WHEN status_pesanan!='batal' THEN 1 ELSE 0 END),0) AS sukses
    FROM orders WHERE DATE(created_at) BETWEEN ? AND ?");
mysqli_stmt_bind_param($st, 'ss', $tgl1, $tgl2);
mysqli_stmt_execute($st);
$sum = array_merge($sum, mysqli_fetch_assoc(mysqli_stmt_get_result($st)) ?: []);
mysqli_stmt_close($st);

// 2. Top 5 Menu Terlaris
$topMenu = [];
$q = "SELECT p.nama_menu, SUM(oi.qty) AS qty, SUM(oi.subtotal) AS omzet
      FROM order_items oi
      JOIN products p ON p.id = oi.product_id
      JOIN orders o ON o.id = oi.order_id
      WHERE DATE(o.created_at) BETWEEN '$tgl1' AND '$tgl2' AND o.status_pesanan != 'batal'
      GROUP BY p.id, p.nama_menu ORDER BY qty DESC LIMIT 5";
$rs = mysqli_query($koneksi, $q);
if ($rs) while ($r = mysqli_fetch_assoc($rs)) $topMenu[] = $r;

// 3. Daftar Seluruh Transaksi
$rows = [];
$st = mysqli_prepare($koneksi, "SELECT o.*, 
    (SELECT GROUP_CONCAT(CONCAT(p.nama_menu, ' (', oi.qty, 'x)') SEPARATOR '; ') 
     FROM order_items oi 
     JOIN products p ON p.id=oi.product_id 
     WHERE oi.order_id=o.id) AS ringkas
    FROM orders o 
    WHERE DATE(o.created_at) BETWEEN ? AND ? 
    ORDER BY o.id ASC");
mysqli_stmt_bind_param($st, 'ss', $tgl1, $tgl2);
mysqli_stmt_execute($st);
$rs = mysqli_stmt_get_result($st);
while ($r = mysqli_fetch_assoc($rs)) $rows[] = $r;
mysqli_stmt_close($st);

function xmlEscape($val) {
    return htmlspecialchars((string)$val, ENT_XML1, 'UTF-8');
}

// Set HTTP Headers untuk unduhan Excel
$filename = "Rekap_Penjualan_Pancong_Lumer_{$tgl1}_sd_{$tgl2}.xls";
header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
header("Pragma: public");

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<?mso-application progid="Excel.Sheet"?>' . "\n";
?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org/TR/REC-html40">
 <Styles>
  <Style ss:ID="Default" ss:Name="Normal">
   <Alignment ss:Vertical="Center"/>
   <Font ss:FontName="Calibri" ss:Size="11" ss:Color="#1B1B1C"/>
  </Style>
  <Style ss:ID="sTitle">
   <Font ss:FontName="Calibri" ss:Size="16" ss:Bold="1" ss:Color="#3B1E08"/>
  </Style>
  <Style ss:ID="sSubtitle">
   <Font ss:FontName="Calibri" ss:Size="11" ss:Italic="1" ss:Color="#5B403C"/>
  </Style>
  <Style ss:ID="sSection">
   <Font ss:FontName="Calibri" ss:Size="12" ss:Bold="1" ss:Color="#1A56DB"/>
   <Interior ss:Color="#EEF4FF" ss:Pattern="Solid"/>
  </Style>
  <Style ss:ID="sHeader">
   <Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#906F6B"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#906F6B"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#906F6B"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#906F6B"/>
   </Borders>
   <Font ss:FontName="Calibri" ss:Size="11" ss:Bold="1" ss:Color="#FFFFFF"/>
   <Interior ss:Color="#1A56DB" ss:Pattern="Solid"/>
  </Style>
  <Style ss:ID="sCell">
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E5E2E1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E5E2E1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E5E2E1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E5E2E1"/>
   </Borders>
  </Style>
  <Style ss:ID="sCellCenter">
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E5E2E1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E5E2E1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E5E2E1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E5E2E1"/>
   </Borders>
  </Style>
  <Style ss:ID="sNumber">
   <Alignment ss:Horizontal="Right" ss:Vertical="Center"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E5E2E1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E5E2E1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E5E2E1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E5E2E1"/>
   </Borders>
   <NumberFormat ss:Format="#,##0"/>
  </Style>
  <Style ss:ID="sTotalRow">
   <Alignment ss:Horizontal="Right" ss:Vertical="Center"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Double" ss:Weight="2" ss:Color="#1B1B1C"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#1B1B1C"/>
   </Borders>
   <Font ss:FontName="Calibri" ss:Size="11" ss:Bold="1" ss:Color="#1B1B1C"/>
   <Interior ss:Color="#F0EDED" ss:Pattern="Solid"/>
   <NumberFormat ss:Format="#,##0"/>
  </Style>
  <Style ss:ID="sTotalLabel">
   <Alignment ss:Horizontal="Right" ss:Vertical="Center"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Double" ss:Weight="2" ss:Color="#1B1B1C"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#1B1B1C"/>
   </Borders>
   <Font ss:FontName="Calibri" ss:Size="11" ss:Bold="1" ss:Color="#1B1B1C"/>
   <Interior ss:Color="#F0EDED" ss:Pattern="Solid"/>
  </Style>
 </Styles>
 <Worksheet ss:Name="Rekap Penjualan">
  <Table>
   <!-- Definisi Lebar Kolom yang Kompak & Pas di Layar (Total ~1045px) -->
   <Column ss:Index="1" ss:Width="30"/>
   <Column ss:Index="2" ss:Width="105"/>
   <Column ss:Index="3" ss:Width="120"/>
   <Column ss:Index="4" ss:Width="115"/>
   <Column ss:Index="5" ss:Width="95"/>
   <Column ss:Index="6" ss:Width="75"/>
   <Column ss:Index="7" ss:Width="65"/>
   <Column ss:Index="8" ss:Width="85"/>
   <Column ss:Index="9" ss:Width="85"/>
   <Column ss:Index="10" ss:Width="175"/>
   <Column ss:Index="11" ss:Width="95"/>

   <!-- Header Title -->
   <Row ss:Height="24">
    <Cell ss:MergeAcross="10" ss:StyleID="sTitle"><Data ss:Type="String">REKAPITULASI PENJUALAN &amp; OMZET — PANCONG LUMER X CHICKEN ROLL</Data></Cell>
   </Row>
   <Row ss:Height="16">
    <Cell ss:MergeAcross="10" ss:StyleID="sSubtitle"><Data ss:Type="String">Periode Laporan: <?php echo xmlEscape($tgl1); ?> s/d <?php echo xmlEscape($tgl2); ?> | Kasir: <?php echo xmlEscape($namaKasir); ?> | Diunduh Pada: <?php echo xmlEscape($waktuUnduh); ?></Data></Cell>
   </Row>
   <Row ss:Height="8"/>

   <!-- SECTION 1: RINGKASAN OMZET (1 + 4 + 2 + 4 = 11 Kolom) -->
   <Row ss:Height="20">
    <Cell ss:MergeAcross="10" ss:StyleID="sSection"><Data ss:Type="String">A. RINGKASAN METRIK OPERASIONAL</Data></Cell>
   </Row>
   <Row ss:Height="20">
    <Cell ss:StyleID="sHeader"><Data ss:Type="String">No</Data></Cell>
    <Cell ss:MergeAcross="3" ss:StyleID="sHeader"><Data ss:Type="String">Indikator Metrik</Data></Cell>
    <Cell ss:MergeAcross="1" ss:StyleID="sHeader"><Data ss:Type="String">Nilai / Jumlah</Data></Cell>
    <Cell ss:MergeAcross="3" ss:StyleID="sHeader"><Data ss:Type="String">Keterangan</Data></Cell>
   </Row>
   <Row>
    <Cell ss:StyleID="sCellCenter"><Data ss:Type="Number">1</Data></Cell>
    <Cell ss:MergeAcross="3" ss:StyleID="sCell"><Data ss:Type="String">Total Transaksi Masuk</Data></Cell>
    <Cell ss:MergeAcross="1" ss:StyleID="sNumber"><Data ss:Type="Number"><?php echo (int)$sum['n']; ?></Data></Cell>
    <Cell ss:MergeAcross="3" ss:StyleID="sCell"><Data ss:Type="String">Keseluruhan tiket pesanan walk-in &amp; online</Data></Cell>
   </Row>
   <Row>
    <Cell ss:StyleID="sCellCenter"><Data ss:Type="Number">2</Data></Cell>
    <Cell ss:MergeAcross="3" ss:StyleID="sCell"><Data ss:Type="String">Pesanan Selesai / Valid</Data></Cell>
    <Cell ss:MergeAcross="1" ss:StyleID="sNumber"><Data ss:Type="Number"><?php echo (int)$sum['sukses']; ?></Data></Cell>
    <Cell ss:MergeAcross="3" ss:StyleID="sCell"><Data ss:Type="String">Transaksi sukses (di luar pesanan batal)</Data></Cell>
   </Row>
   <Row>
    <Cell ss:StyleID="sCellCenter"><Data ss:Type="Number">3</Data></Cell>
    <Cell ss:MergeAcross="3" ss:StyleID="sCell"><Data ss:Type="String">Pesanan Batal / Ditolak</Data></Cell>
    <Cell ss:MergeAcross="1" ss:StyleID="sNumber"><Data ss:Type="Number"><?php echo (int)$sum['batal']; ?></Data></Cell>
    <Cell ss:MergeAcross="3" ss:StyleID="sCell"><Data ss:Type="String">Tiket yang dibatalkan kasir atau ditolak</Data></Cell>
   </Row>
   <Row>
    <Cell ss:StyleID="sCellCenter"><Data ss:Type="Number">4</Data></Cell>
    <Cell ss:MergeAcross="3" ss:StyleID="sCell"><Data ss:Type="String">Pembayaran QRIS (Digital)</Data></Cell>
    <Cell ss:MergeAcross="1" ss:StyleID="sNumber"><Data ss:Type="Number"><?php echo (int)$sum['qris']; ?></Data></Cell>
    <Cell ss:MergeAcross="3" ss:StyleID="sCell"><Data ss:Type="String">Nominal terverifikasi QRIS bank/e-wallet</Data></Cell>
   </Row>
   <Row>
    <Cell ss:StyleID="sCellCenter"><Data ss:Type="Number">5</Data></Cell>
    <Cell ss:MergeAcross="3" ss:StyleID="sCell"><Data ss:Type="String">Pembayaran Tunai (Cash)</Data></Cell>
    <Cell ss:MergeAcross="1" ss:StyleID="sNumber"><Data ss:Type="Number"><?php echo (int)$sum['tunai']; ?></Data></Cell>
    <Cell ss:MergeAcross="3" ss:StyleID="sCell"><Data ss:Type="String">Uang tunai diterima di laci kasir gerai</Data></Cell>
   </Row>
   <Row ss:Height="20">
    <Cell ss:StyleID="sTotalRow"><Data ss:Type="String"></Data></Cell>
    <Cell ss:MergeAcross="3" ss:StyleID="sTotalLabel"><Data ss:Type="String">TOTAL OMZET BERSIH (RP):</Data></Cell>
    <Cell ss:MergeAcross="1" ss:StyleID="sTotalRow"><Data ss:Type="Number"><?php echo (int)$sum['omzet']; ?></Data></Cell>
    <Cell ss:MergeAcross="3" ss:StyleID="sTotalLabel"><Data ss:Type="String">Dihitung dari seluruh transaksi valid</Data></Cell>
   </Row>
   <Row ss:Height="10"/>

   <!-- SECTION 2: TOP MENU (1 + 5 + 2 + 3 = 11 Kolom) -->
   <?php if (!empty($topMenu)): ?>
   <Row ss:Height="20">
    <Cell ss:MergeAcross="10" ss:StyleID="sSection"><Data ss:Type="String">B. TOP 5 MENU TERLARIS PERIODE INI</Data></Cell>
   </Row>
   <Row ss:Height="20">
    <Cell ss:StyleID="sHeader"><Data ss:Type="String">Peringkat</Data></Cell>
    <Cell ss:MergeAcross="4" ss:StyleID="sHeader"><Data ss:Type="String">Nama Varian Menu</Data></Cell>
    <Cell ss:MergeAcross="1" ss:StyleID="sHeader"><Data ss:Type="String">Porsi Terjual</Data></Cell>
    <Cell ss:MergeAcross="2" ss:StyleID="sHeader"><Data ss:Type="String">Kontribusi Omzet (Rp)</Data></Cell>
   </Row>
   <?php foreach ($topMenu as $idx => $tm): ?>
   <Row>
    <Cell ss:StyleID="sCellCenter"><Data ss:Type="String">#<?php echo $idx + 1; ?></Data></Cell>
    <Cell ss:MergeAcross="4" ss:StyleID="sCell"><Data ss:Type="String"><?php echo xmlEscape($tm['nama_menu']); ?></Data></Cell>
    <Cell ss:MergeAcross="1" ss:StyleID="sNumber"><Data ss:Type="Number"><?php echo (int)$tm['qty']; ?></Data></Cell>
    <Cell ss:MergeAcross="2" ss:StyleID="sNumber"><Data ss:Type="Number"><?php echo (int)$tm['omzet']; ?></Data></Cell>
   </Row>
   <?php endforeach; ?>
   <Row ss:Height="10"/>
   <?php endif; ?>

   <!-- SECTION 3: TABEL TRANSAKSI LENGKAP (11 Kolom) -->
   <Row ss:Height="20">
    <Cell ss:MergeAcross="10" ss:StyleID="sSection"><Data ss:Type="String">C. RINCIAN DATA TRANSAKSI PENJUALAN</Data></Cell>
   </Row>
   <Row ss:Height="22">
    <Cell ss:StyleID="sHeader"><Data ss:Type="String">No</Data></Cell>
    <Cell ss:StyleID="sHeader"><Data ss:Type="String">No. Antrean</Data></Cell>
    <Cell ss:StyleID="sHeader"><Data ss:Type="String">Waktu Transaksi</Data></Cell>
    <Cell ss:StyleID="sHeader"><Data ss:Type="String">Nama Pemesan</Data></Cell>
    <Cell ss:StyleID="sHeader"><Data ss:Type="String">No. WhatsApp</Data></Cell>
    <Cell ss:StyleID="sHeader"><Data ss:Type="String">Tipe Order</Data></Cell>
    <Cell ss:StyleID="sHeader"><Data ss:Type="String">Metode Bayar</Data></Cell>
    <Cell ss:StyleID="sHeader"><Data ss:Type="String">Status Bayar</Data></Cell>
    <Cell ss:StyleID="sHeader"><Data ss:Type="String">Status Pesanan</Data></Cell>
    <Cell ss:StyleID="sHeader"><Data ss:Type="String">Rincian Menu &amp; Topping</Data></Cell>
    <Cell ss:StyleID="sHeader"><Data ss:Type="String">Total Bayar (Rp)</Data></Cell>
   </Row>

   <?php 
   $noUrut = 1;
   foreach ($rows as $r): 
       $tipeLabel = $r['tipe_order'] === 'dine_in' ? 'Dine-in' : 'Takeaway';
       $metodeLabel = strtoupper($r['metode_bayar']);
   ?>
   <Row>
    <Cell ss:StyleID="sCellCenter"><Data ss:Type="Number"><?php echo $noUrut++; ?></Data></Cell>
    <Cell ss:StyleID="sCellCenter"><Data ss:Type="String"><?php echo xmlEscape($r['no_antrean']); ?></Data></Cell>
    <Cell ss:StyleID="sCellCenter"><Data ss:Type="String"><?php echo xmlEscape($r['created_at']); ?></Data></Cell>
    <Cell ss:StyleID="sCell"><Data ss:Type="String"><?php echo xmlEscape($r['nama_pemesan']); ?></Data></Cell>
    <Cell ss:StyleID="sCellCenter"><Data ss:Type="String"><?php echo xmlEscape($r['no_wa']); ?></Data></Cell>
    <Cell ss:StyleID="sCellCenter"><Data ss:Type="String"><?php echo xmlEscape($tipeLabel); ?></Data></Cell>
    <Cell ss:StyleID="sCellCenter"><Data ss:Type="String"><?php echo xmlEscape($metodeLabel); ?></Data></Cell>
    <Cell ss:StyleID="sCellCenter"><Data ss:Type="String"><?php echo xmlEscape($r['status_bayar']); ?></Data></Cell>
    <Cell ss:StyleID="sCellCenter"><Data ss:Type="String"><?php echo xmlEscape($r['status_pesanan']); ?></Data></Cell>
    <Cell ss:StyleID="sCell"><Data ss:Type="String"><?php echo xmlEscape($r['ringkas'] ?: '-'); ?></Data></Cell>
    <Cell ss:StyleID="sNumber"><Data ss:Type="Number"><?php echo (int)$r['total_bayar']; ?></Data></Cell>
   </Row>
   <?php endforeach; ?>

   <?php if (empty($rows)): ?>
   <Row>
    <Cell ss:MergeAcross="10" ss:StyleID="sCellCenter"><Data ss:Type="String">Tidak ada transaksi yang tercatat pada rentang tanggal ini.</Data></Cell>
   </Row>
   <?php endif; ?>

   <!-- Grand Total Row -->
   <Row ss:Height="22">
    <Cell ss:MergeAcross="9" ss:StyleID="sTotalLabel"><Data ss:Type="String">TOTAL KESELURUHAN (RP):</Data></Cell>
    <Cell ss:StyleID="sTotalRow"><Data ss:Type="Number"><?php echo (int)$sum['omzet']; ?></Data></Cell>
   </Row>
  </Table>

  <!-- WorksheetOptions: Memaksa Excel Selalu Terbuka Mulai dari Kolom A & Baris 1 (Mencegah Layar Terpotong) -->
  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
   <PageSetup>
    <Layout x:Orientation="Landscape"/>
    <Header x:Margin="0.3"/>
    <Footer x:Margin="0.3"/>
    <PageMargins x:Bottom="0.5" x:Left="0.5" x:Right="0.5" x:Top="0.5"/>
   </PageSetup>
   <FitToPage/>
   <Print>
    <FitWidth>1</FitWidth>
    <FitHeight>0</FitHeight>
    <ValidPrinterInfo/>
   </Print>
   <Selected/>
   <TopRowVisible>0</TopRowVisible>
   <LeftColumnVisible>0</LeftColumnVisible>
   <ActiveRow>0</ActiveRow>
   <ActiveColumn>0</ActiveColumn>
   <Panes>
    <Pane>
     <Number>3</Number>
     <ActiveRow>0</ActiveRow>
     <ActiveCol>0</ActiveCol>
    </Pane>
   </Panes>
   <ProtectObjects>False</ProtectObjects>
   <ProtectScenarios>False</ProtectScenarios>
  </WorksheetOptions>
 </Worksheet>

 <!-- =========================================================
      SHEET 2: DATA TRANSAKSI (Tabel Murni Tanpa Kop untuk Pivot/Filter)
      ========================================================= -->
 <Worksheet ss:Name="Data Transaksi">
  <Table>
   <Column ss:Index="1" ss:Width="30"/>
   <Column ss:Index="2" ss:Width="105"/>
   <Column ss:Index="3" ss:Width="120"/>
   <Column ss:Index="4" ss:Width="115"/>
   <Column ss:Index="5" ss:Width="95"/>
   <Column ss:Index="6" ss:Width="75"/>
   <Column ss:Index="7" ss:Width="65"/>
   <Column ss:Index="8" ss:Width="85"/>
   <Column ss:Index="9" ss:Width="85"/>
   <Column ss:Index="10" ss:Width="175"/>
   <Column ss:Index="11" ss:Width="95"/>

   <Row ss:Height="22">
    <Cell ss:StyleID="sHeader"><Data ss:Type="String">No</Data></Cell>
    <Cell ss:StyleID="sHeader"><Data ss:Type="String">No. Antrean</Data></Cell>
    <Cell ss:StyleID="sHeader"><Data ss:Type="String">Waktu Transaksi</Data></Cell>
    <Cell ss:StyleID="sHeader"><Data ss:Type="String">Nama Pemesan</Data></Cell>
    <Cell ss:StyleID="sHeader"><Data ss:Type="String">No. WhatsApp</Data></Cell>
    <Cell ss:StyleID="sHeader"><Data ss:Type="String">Tipe Order</Data></Cell>
    <Cell ss:StyleID="sHeader"><Data ss:Type="String">Metode Bayar</Data></Cell>
    <Cell ss:StyleID="sHeader"><Data ss:Type="String">Status Bayar</Data></Cell>
    <Cell ss:StyleID="sHeader"><Data ss:Type="String">Status Pesanan</Data></Cell>
    <Cell ss:StyleID="sHeader"><Data ss:Type="String">Rincian Menu &amp; Topping</Data></Cell>
    <Cell ss:StyleID="sHeader"><Data ss:Type="String">Total Bayar (Rp)</Data></Cell>
   </Row>

   <?php 
   $noRaw = 1;
   foreach ($rows as $r): 
       $tipeLabel = $r['tipe_order'] === 'dine_in' ? 'Dine-in' : 'Takeaway';
       $metodeLabel = strtoupper($r['metode_bayar']);
   ?>
   <Row>
    <Cell ss:StyleID="sCellCenter"><Data ss:Type="Number"><?php echo $noRaw++; ?></Data></Cell>
    <Cell ss:StyleID="sCellCenter"><Data ss:Type="String"><?php echo xmlEscape($r['no_antrean']); ?></Data></Cell>
    <Cell ss:StyleID="sCellCenter"><Data ss:Type="String"><?php echo xmlEscape($r['created_at']); ?></Data></Cell>
    <Cell ss:StyleID="sCell"><Data ss:Type="String"><?php echo xmlEscape($r['nama_pemesan']); ?></Data></Cell>
    <Cell ss:StyleID="sCellCenter"><Data ss:Type="String"><?php echo xmlEscape($r['no_wa']); ?></Data></Cell>
    <Cell ss:StyleID="sCellCenter"><Data ss:Type="String"><?php echo xmlEscape($tipeLabel); ?></Data></Cell>
    <Cell ss:StyleID="sCellCenter"><Data ss:Type="String"><?php echo xmlEscape($metodeLabel); ?></Data></Cell>
    <Cell ss:StyleID="sCellCenter"><Data ss:Type="String"><?php echo xmlEscape($r['status_bayar']); ?></Data></Cell>
    <Cell ss:StyleID="sCellCenter"><Data ss:Type="String"><?php echo xmlEscape($r['status_pesanan']); ?></Data></Cell>
    <Cell ss:StyleID="sCell"><Data ss:Type="String"><?php echo xmlEscape($r['ringkas'] ?: '-'); ?></Data></Cell>
    <Cell ss:StyleID="sNumber"><Data ss:Type="Number"><?php echo (int)$r['total_bayar']; ?></Data></Cell>
   </Row>
   <?php endforeach; ?>

   <Row ss:Height="22">
    <Cell ss:MergeAcross="9" ss:StyleID="sTotalLabel"><Data ss:Type="String">TOTAL KESELURUHAN (RP):</Data></Cell>
    <Cell ss:StyleID="sTotalRow"><Data ss:Type="Number"><?php echo (int)$sum['omzet']; ?></Data></Cell>
   </Row>
  </Table>

  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
   <PageSetup>
    <Layout x:Orientation="Landscape"/>
   </PageSetup>
   <TopRowVisible>0</TopRowVisible>
   <LeftColumnVisible>0</LeftColumnVisible>
   <ActiveRow>0</ActiveRow>
   <ActiveColumn>0</ActiveColumn>
   <Panes>
    <Pane>
     <Number>3</Number>
     <ActiveRow>0</ActiveRow>
     <ActiveCol>0</ActiveCol>
    </Pane>
   </Panes>
   <ProtectObjects>False</ProtectObjects>
   <ProtectScenarios>False</ProtectScenarios>
  </WorksheetOptions>
 </Worksheet>

</Workbook>
<?php
exit;