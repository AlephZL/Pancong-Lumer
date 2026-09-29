# DOKUMENTASI LENGKAP PROYEK & LAPORAN KEMAJUAN SISTEM
# SISTEM INFORMASI PEMESANAN & POINT OF SALE (POS) PANCONG LUMER GERAI SELINDUNG BARU

> **Catatan Penggunaan Dokumen:**  
> Dokumen ini disusun secara terstruktur, formal, dan komprehensif sebagai basis konten laporan kemajuan (*progress report*), laporan tugas akhir, skripsi, maupun laporan magang/PKL. Seluruh bab dan sub-bab di bawah ini dapat langsung disalin (*copy-paste*) ke dalam template Microsoft Word Laporan Kemajuan yang telah disediakan.

---

## DAFTAR ISI LAPORAN

1. **BAB I: IDENTITAS PROYEK & PENDAHULUAN**
   - 1.1 Identitas Sistem & Data Proyek
   - 1.2 Profil Mitra / Objek Studi Kasus
   - 1.3 Latar Belakang Masalah
   - 1.4 Rumusan Masalah
   - 1.5 Tujuan Pengembangan Sistem
   - 1.6 Batasan Masalah & Ruang Lingkup
   - 1.7 Manfaat Sistem (Multi-Stakeholder)
2. **BAB II: METODOLOGI & LINIMASA PENGEMBANGAN (SDLC)**
   - 2.1 Model Pengembangan Perangkat Lunak (Waterfall / Agile Prototyping)
   - 2.2 Tahapan Pelaksanaan Proyek
   - 2.3 Linimasa & Matriks Jadwal Pengerjaan
3. **BAB III: ANALISIS KEBUTUHAN SISTEM**
   - 3.1 Analisis Aktor & Hak Akses Pengguna
   - 3.2 Analisis Kebutuhan Fungsional (*Functional Requirements* / FR)
   - 3.3 Analisis Kebutuhan Non-Fungsional (*Non-Functional Requirements* / NFR)
4. **BAB IV: PERANCANGAN SISTEM (SISTEM DESAIN & UML)**
   - 4.1 Arsitektur Sistem Global
   - 4.2 Use Case Diagram & Relasi Antar Use Case
   - 4.3 Spesifikasi & Skenario Use Case Rinci (UC-01 s/d UC-18)
   - 4.4 Activity Diagram Alur Bisnis Utama
   - 4.5 Sequence Diagram Interaksi Komponen
   - 4.6 Class Diagram & Struktur Berorientasi Objek
   - 4.7 Perancangan Basis Data (ERD, Relasi Antartabel, Skema 8 Tabel)
   - 4.8 Perancangan Antarmuka Pengguna (UI/UX Design Flow)
5. **BAB V: IMPLEMENTASI SISTEM (HASIL BANGUN)**
   - 5.1 Lingkungan & Spesifikasi Teknologi (*Tech Stack*)
   - 5.2 Struktur Arsitektur Direktori & Modul Proyek
   - 5.3 Implementasi Fitur Sisi Pelanggan (*Front-End Web Ordering*)
   - 5.4 Implementasi Fitur Sisi Staf & Kasir (*Back-End POS & KDS*)
   - 5.5 Mekanisme Kunci & Pengamanan Transaksional Sistem
6. **BAB VI: PENGUJIAN SISTEM & EVALUASI (*TESTING*)**
   - 6.1 Metodologi Pengujian (*Black-Box Testing*)
   - 6.2 Matriks Skenario & Hasil Pengujian Fungsional (TC-01 s/d TC-25)
   - 6.3 Pengujian Non-Fungsional (Keamanan, Konkurensi, Integritas Data)
7. **BAB VII: CAPAIAN KEMAJUAN & KENDALA PENGEMBANGAN**
   - 7.1 Status Realisasi Capaian Proyek (Tabel Milestone & Persentase)
   - 7.2 Kendala Teknis yang Dihadapi & Solusi Penerapannya
   - 7.3 Evaluasi Kemajuan Proyek
8. **BAB VIII: RENCANA TAHAP SELANJUTNYA & KESIMPULAN**
   - 8.1 Rencana Pengembangan Lanjutan (*Next Steps*)
   - 8.2 Kesimpulan Laporan Kemajuan
9. **LAMPIRAN TEKNIS**
   - 9.1 Panduan Instalasi & Operasional Lokal (*Deployment Guide*)
   - 9.2 Kredensial Akun Pengujian & Demonstrasi
   - 9.3 Skema Basis Data DDL SQL Lengkap

---

# BAB I: IDENTITAS PROYEK & PENDAHULUAN

## 1.1 Identitas Sistem & Data Proyek

| Parameter | Keterangan Rinci |
|---|---|
| **Nama Proyek / Sistem** | Sistem Informasi Pemesanan & Point of Sale (POS) Pancong Lumer Selindung Baru |
| **Kode Repositori** | `Pancong-Lumer` |
| **Kategori Sistem** | Web Ordering System + Kasir POS Walk-in + Kitchen Display System (KDS) + Financial Reporting |
| **Arsitektur Aplikasi** | Client-Server berbasis Web (Monolithic Modular, Clean Native PHP & Vanilla JS) |
| **Basis Data** | MySQL / MariaDB Relasional (`db_pancong`, 8 Tabel InnoDB) |
| **Target Pengguna** | Pelanggan Umum (Guest/Tamu), Pelanggan Terdaftar (Customer), Kasir/Admin Gerai, Koki Dapur |
| **Status Kemajuan Saat Ini** | **Milestone Demo 1 Selesai (100% Fitur Utama Terimplementasi & Teruji)** |

## 1.2 Profil Mitra / Objek Studi Kasus
- **Nama Usaha:** Gerai Pancong Lumer Selindung Baru
- **Bidang Usaha:** Kuliner / Makanan Ringan Kekinian (Kue Pancong Aneka Topping Lumer)
- **Lokasi Usaha:** Kawasan Selindung Baru, Kota Pangkalpinang, Provinsi Kepulauan Bangka Belitung.
- **Jam Operasional:** 10.00 – 22.00 WIB (Setiap Hari).
- **Layanan:** *Dine-in* (Makan di tempat) dan *Takeaway* (Bungkus bawa pulang).
- **Metode Pembayaran:** Tunai (*Cash*) dan Non-Tunai QRIS (*Quick Response Code Indonesian Standard*).

## 1.3 Latar Belakang Masalah
Kue pancong lumer merupakan salah satu jajanan tradisional yang mengalami inovasi modern dan digemari oleh berbagai kalangan masyarakat, khususnya remaja dan keluarga di Pangkalpinang. Gerai Pancong Lumer Selindung Baru menyajikan kue pancong dengan fleksibilitas kustomisasi yang sangat tinggi, mencakup varian porsi (1 loyang penuh atau 1/2 loyang), tingkat kematangan adonan (setengah matang, 3/4 matang, matang sempurna), serta kombinasi aneka *topping* manis dan gurih (keju parut, *choco chips*, *oreo crumb*, *red crumb*, susu cokelat, susu putih).

Tingginya minat beli konsumen menimbulkan beberapa permasalahan krusial dalam operasional harian yang masih dijalankan secara konvensional:
1. **Pencatatan Pesanan Manual yang Rawan Kesalahan (*Human Error*):** Pelayan mencatat pesanan di secarik kertas nota. Kustomisasi pesanan yang kompleks (misalnya: varian kematangan berbeda pada tiap pesanan dan multi-topping) sering kali salah terbaca oleh koki pemanggang, berakibat pada salah sajian dan komplain pelanggan.
2. **Kertas Nota Rawan Rusak dan Hilang:** Di area dapur yang bersinggungan dengan minyak, mentega, adonan basah, dan uap panggangan, nota fisik sering basah, kotor, atau tercecer dari antrean.
3. **Ketidakpastian Waktu Antrean bagi Pelanggan:** Pada jam-jam sibuk (*peak hours*, pukul 18.30–21.30 WIB), antrean menumpuk di depan gerai. Pelanggan tidak mengetahui nomor urut berapa yang sedang dipanggang, apakah pesanannya sudah masuk antrean dapur, atau kapan pesanan siap diambil.
4. **Lambatnya Pelayanan Kasir untuk Pembeli Langsung (*Walk-in*):** Kasir harus menghitung manual total harga dan nominal uang kembalian menggunakan kalkulator eksternal, yang memperlambat laju transaksi saat antrean panjang.
5. **Verifikasi Bukti QRIS Manual yang Rawan Kecurangan:** Pelanggan yang membayar menggunakan transfer/QRIS sering kali hanya menunjukkan layar sekilas tanpa ada pencocokan riil di mutasi, atau kasir lupa mencatat nota mana yang sudah lunas.
6. **Sulitnya Rekapitulasi Penjualan & Laporan Omzet Harian:** Pemilik usaha kesulitan memantau omzet harian secara akurat. Rekap nota kertas di akhir shift memakan waktu 1–2 jam, rawan salah hitung, serta sulit memetakan menu mana yang paling laris (*fast-moving*) untuk perencanaan belanja bahan baku.

## 1.4 Rumusan Masalah
Berdasarkan latar belakang tersebut, rumusan masalah dalam pengembangan sistem ini adalah:
1. Bagaimana merancang dan membangun sistem pemesanan mandiri (*self-ordering*) berbasis web yang memudahkan pelanggan memilih menu, porsi, kematangan, dan rekomendasi *topping* secara interaktif?
2. Bagaimana membangun modul Point of Sale (POS) kasir yang cepat, dilengkapi kalkulator kembalian otomatis dan pencatatan transaksi *walk-in*?
3. Bagaimana mengimplementasikan sistem antrean digital (*live order stepper*) yang transparan dan dapat dipantau langsung oleh pelanggan tanpa harus bertanya ke kasir?
4. Bagaimana mengintegrasikan Kitchen Display System (KDS) terpusat untuk memandu staf dapur memperbarui status pemanggangan secara terstruktur?
5. Bagaimana menghasilkan laporan rekapitulasi penjualan dan omzet otomatis yang dapat diekspor langsung ke format Microsoft Excel tanpa bergantung pada pustaka pihak ketiga?

## 1.5 Tujuan Pengembangan Sistem
Tujuan utama yang dicapai melalui proyek ini adalah:
1. Menghasilkan aplikasi web pemesanan terintegrasi yang mengakomodasi pesanan pelanggan online mandiri maupun pemesanan *walk-in* via kasir.
2. Menerapkan fitur kustomisasi menu cerdas (*smart customizer*) yang dilengkapi aturan rekomendasi kombinasi *topping* (*market basket analysis* sederhana) untuk meningkatkan nilai penjualan rata-rata (*cross-selling*).
3. Menerapkan nomor antrean transaksional unik (`PC-YYYYMMDD-XXX`) yang terbit otomatis dan aman dari tabrakan data (*concurrency-safe*).
4. Menyediakan dasbor kasir dan tampilan dapur *real-time* dengan fasilitas verifikasi pembayaran QRIS dan transisi status masak.
5. Membangun modul laporan keuangan dan rekap omzet dengan fitur filter rentang tanggal, analisis 5 menu terlaris, serta ekspor file Microsoft Excel (.xls) berstandar industri.

## 1.6 Batasan Masalah & Ruang Lingkup
Untuk menjaga fokus pengembangan pada target pencapaian kemajuan, batasan sistem ditetapkan sebagai berikut:
1. **Cakupan Bisnis:** Sistem dikhususkan untuk gerai tunggal Pancong Lumer Selindung Baru.
2. **Kanal Akses:** Sistem berbasis web responsif (*mobile-friendly*) yang dapat diakses melalui peramban web pada ponsel pintar, tablet, maupun komputer desktop/laptop.
3. **Pembayaran:**
   - Metode QRIS dilakukan secara statis dengan mengunggah bukti transfer/tangkapan layar (*screenshot*) berformat JPG/PNG dengan validasi server-side MIME type.
   - Metode Tunai dilayani langsung di kasir dengan fasilitas kalkulator kembalian instan.
   - Belum menggunakan *payment gateway* otomatis pihak ketiga (seperti Midtrans/Xendit) untuk memangkas biaya komisi transaksi per pesanan.
4. **Pembaruan Data Antrean:** Menggunakan teknik *client-side polling* berkala (15 detik pada tiket pelanggan, 20 detik pada Live Order kasir) yang ringan tanpa memerlukan konfigurasi server WebSocket yang kompleks.
5. **Autentikasi:** Mendukung dua peran pengguna (*customer* dan *admin*), serta menyediakan akses *guest checkout* (pesanan tamu tanpa wajib mendaftar).

## 1.7 Manfaat Sistem
Sistem ini memberikan manfaat nyata bagi seluruh pihak yang terlibat:
- **Bagi Pelanggan:**
  - Kemudahan memesan dari mana saja tanpa perlu berdesakan di depan kasir.
  - Bebas menentukan kustomisasi porsi, tingkat kematangan, dan varian rasa secara presisi.
  - Transparansi status pesanan melalui tiket digital live (Menunggu Konfirmasi -> Sedang Dipanggang -> Siap Diambil -> Selesai).
- **Bagi Kasir:**
  - Efisiensi pelayanan pembeli langsung lewat POS satu layar sentuh/keyboard shortcut `/`.
  - Kemudahan verifikasi bukti bayar QRIS berdampingan dengan rincian pesanan.
  - Mengurangi risiko selisih kas berkat kalkulator uang kembalian terintegrasi.
- **Bagi Koki Dapur:**
  - Mengeliminasi nota sobek/basah dengan antrean digital yang tersusun urut waktu.
  - Membaca instruksi kematangan dan topping secara jelas dan terstandarisasi.
- **Bagi Pemilik Usaha (*Owner*):**
  - Pemantauan omzet harian secara *real-time* dari mana saja.
  - Keputusan belanja bahan baku lebih terarah berbasis data statistik 5 menu terlaris.
  - Laporan penjualan tersimpan rapi dan dapat diekspor ke Microsoft Excel untuk pembukuan akuntansi.

---

# BAB II: METODOLOGI & LINIMASA PENGEMBANGAN (SDLC)

## 2.1 Model Pengembangan Perangkat Lunak
Sistem ini dikembangkan menggunakan model **Software Development Life Cycle (SDLC) Waterfall yang dimodifikasi (*Iterative Stage / Prototyping Approach*)**. Pendekatan ini dipilih karena spesifikasi kebutuhan gerai makanan telah terdefinisi secara jelas sejak awal, namun membutuhkan iterasi pada desain antarmuka (*UI/UX*) dan penyesuaian alur kerja kasir agar cepat digunakan pada situasi jam sibuk.

Tahapan mencakup:
1. **Analisis Kebutuhan (*Requirements Analysis*):** Observasi langsung operasional gerai, wawancara pemilik gerai mengenai alur adonan pancong, penentuan varian, dan pencatatan transaksi manual.
2. **Perancangan Sistem (*System Design*):** Pembuatan diagram UML (Use Case, Activity, Sequence, Class), pemodelan basis data relasional (ERD & DDL), serta perancangan tata letak visual (*mockup wireframe* responsif).
3. **Implementasi Kode (*Implementation & Coding*):** Penulisan kode program modular menggunakan PHP Native berorientasi keamanan (*prepared statements*, sanitasi input/output), antarmuka CSS kustom modern, dan JavaScript Vanilla untuk manajemen keranjang belanja lokal.
4. **Pengujian Perangkat Lunak (*Testing*):** Pengujian menyeluruh menggunakan metode *Black-Box Testing* untuk memvalidasi seluruh fungsi, uji penanganan berkas bukti transfer, dan validasi integritas transaksi basis data.
5. **Evaluasi Kemajuan & Pemeliharaan (*Progress Review & Hardening*):** Tahap evaluasi berkala untuk memastikan performa sistem stabil, siap demonstrasi, dan siap diimplementasikan di gerai fisik.

## 2.2 Tahapan Pelaksanaan Proyek

```
+--------------------------------------------------------------------------+
| 1. ANALISIS KEBUTUHAN                                                    |
|    - Studi literatur & observasi alur pancong di Selindung Baru          |
|    - Identifikasi kebutuhan fungsional (FR) & non-fungsional (NFR)        |
+------------------------------------+-------------------------------------+
                                     |
                                     v
+--------------------------------------------------------------------------+
| 2. PERANCANGAN SISTEM & BASIS DATA                                       |
|    - Desain UML: Use Case, Activity, Sequence, Class Diagram             |
|    - Desain Relasional DB (8 Tabel: users s.d. order_item_toppings)       |
|    - Wireframing antarmuka pemesanan & POS workstation kasir             |
+------------------------------------+-------------------------------------+
                                     |
                                     v
+--------------------------------------------------------------------------+
| 3. IMPLEMENTASI FITUR (CODING)                                           |
|    - Modul Publik: Katalog, Smart Customizer, Keranjang, Checkout, Tiket |
|    - Modul Admin: POS Walk-in, Live KDS, Verifikasi, CRUD, Laporan Excel |
|    - Keamanan: Prepared statement, MIME check, Bcrypt hashing            |
+------------------------------------+-------------------------------------+
                                     |
                                     v
+--------------------------------------------------------------------------+
| 4. PENGUJIAN SISTEM (TESTING)                                            |
|    - Black-box testing pada 25 skenario uji                              |
|    - Validasi batas input nominal, file upload, dan anti-concurrency     |
+------------------------------------+-------------------------------------+
                                     |
                                     v
+--------------------------------------------------------------------------+
| 5. EVALUASI LAPORAN KEMAJUAN (MILESTONE DEMO 1)                          |
|    - Dokumentasi menyeluruh arsitektur sistem                            |
|    - Penyusunan materi presentasi & persiapan uji coba lapangan (UAT)    |
+--------------------------------------------------------------------------+
```

## 2.3 Linimasa & Matriks Jadwal Pengerjaan

| No | Aktivitas Pengembangan | Minggu 1 | Minggu 2 | Minggu 3 | Minggu 4 | Status Capaian |
|---|---|:---:|:---:|:---:|:---:|:---:|
| 1 | Wawancara, Observasi Gerai & Analisis Kebutuhan | [X] | [ ] | [ ] | [ ] | Selesai (100%) |
| 2 | Perancangan Database Relasional (DDL & Relasi) | [X] | [X] | [ ] | [ ] | Selesai (100%) |
| 3 | Perancangan UI/UX & Pemodelan Diagram UML | [ ] | [X] | [ ] | [ ] | Selesai (100%) |
| 4 | Coding Modul Publik (Katalog, Customizer, Keranjang) | [ ] | [X] | [X] | [ ] | Selesai (100%) |
| 5 | Coding Modul Checkout & Engine Tiket Antrean | [ ] | [ ] | [X] | [ ] | Selesai (100%) |
| 6 | Coding Modul POS Kasir Walk-in & Kalkulator Kembalian | [ ] | [ ] | [X] | [X] | Selesai (100%) |
| 7 | Coding Live Order Kitchen Display & Verifikasi QRIS | [ ] | [ ] | [ ] | [X] | Selesai (100%) |
| 8 | Coding CRUD Menu, Topping, & Rekomendasi Asosiasi | [ ] | [ ] | [ ] | [X] | Selesai (100%) |
| 9 | Coding Laporan Omzet, Top 5 & Generator Excel XML | [ ] | [ ] | [ ] | [X] | Selesai (100%) |
| 10 | Pengujian Black-Box, Hardening & Dokumentasi Laporan | [ ] | [ ] | [ ] | [X] | Selesai (100%) |

---

# BAB III: ANALISIS KEBUTUHAN SISTEM

## 3.1 Analisis Aktor & Hak Akses Pengguna

| ID Aktor | Nama Aktor | Deskripsi Peran | Batasan Hak Akses |
|---|---|---|---|
| **A-01** | **Tamu (*Guest*)** | Pelanggan umum yang membuka situs tanpa melakukan registrasi/login akun. | Menjelajahi katalog menu, pencarian/filter, membuka customizer, kelola keranjang belanja di browser lokal, checkout pesanan mandiri, unggah bukti bayar, menerima dan memantau tiket antrean via kode tiket atau sesi aktif. |
| **A-02** | **Pelanggan (*Customer*)** | Pelanggan yang telah mendaftar dan masuk ke dalam akun terverifikasi. | Memiliki seluruh hak akses Tamu, ditambah: otomatisasi pengisian nama (*prefill*), klaim tiket tamu otomatis ke akun, pelacakan riwayat 5 transaksi terakhir, dan manajemen status sesi. |
| **A-03** | **Kasir / Admin / Dapur** | Petugas gerai dan pengelola operasional yang memiliki hak akses administratif (`users.role = 'admin'`). | Mengoperasikan POS kasir *walk-in*, mengontrol Live Order KDS, memverifikasi keabsahan pembayaran QRIS, memajukan status panggangan, mengelola master menu (CRUD), master topping, aturan rekomendasi *cross-selling*, memantau grafik omzet, ekspor laporan Excel, dan menghapus riwayat transaksi. |
| **A-04** | **Sistem & Basis Data** | Mesin server PHP dan RDBMS MySQL. | Menghitung ulang total belanja di sisi server (*anti-tampering*), menerbitkan nomor antrean thread-safe (`FOR UPDATE`), memvalidasi integritas file bukti bayar, mengeksekusi *commit/rollback* transaksi, dan menghapus fisik berkas. |

## 3.2 Analisis Kebutuhan Fungsional (*Functional Requirements* / FR)

| Kode FR | Nama Kebutuhan Fungsional | Deskripsi Spesifikasi Input, Proses, dan Output |
|---|---|---|
| **FR-01** | Registrasi Akun Pelanggan | **Input:** Nama lengkap, username unik (min 4 karakter), email valid, password (min 6 karakter), dan konfirmasi password.<br>**Proses:** Validasi duplikasi username, hashing password dengan algoritma Bcrypt (`PASSWORD_DEFAULT`), penyimpanan data dengan role `customer`.<br>**Output:** Akun terdaftar dan otomatis login ke sistem. |
| **FR-02** | Otentikasi Multi-Role & Demo 1-Klik | **Input:** Username dan password (atau klik tombol login demo admin/customer).<br>**Proses:** Pengecekan username via prepared statement, verifikasi password via `password_verify` (disertai fallback plaintext untuk migrasi seed awal yang langsung di-upgrade ke Bcrypt), inisialisasi sesi login, pengalihan rute sesuai role.<br>**Output:** Akses ke dasbor admin untuk role `admin` atau beranda pemesanan untuk role `customer`. |
| **FR-03** | Penelusuran Katalog & Filter Menu | **Input:** Kata kunci pencarian dan pilihan tab kategori produk.<br>**Proses:** Query produk aktif yang digabungkan (*JOIN*) dengan tabel kategori, penentuan visual foto/SVG monogram fallback, serta penentuan lencana produk (*Best Seller*, *Lumer Favorit*, *Habis*).<br>**Output:** Tampilan kartu menu interaktif dengan informasi nama, deskripsi, harga dasar, dan tombol kustomisasi. |
| **FR-04** | Kustomisasi Menu & Rekomendasi Topping | **Input:** Pilihan varian porsi (1 Loyang / 1/2 Loyang), pilihan tingkat kematangan (Setengah Matang / 3/4 Matang / Matang Sempurna), centang pilihan multi-topping, dan kuantitas (1–20).<br>**Proses:** Perhitungan dinamis harga dasar (1/2 loyang = 50% pembulatan) ditambah akumulasi harga topping aktif, serta query dinamis tabel `topping_recommendations` untuk menampilkan saran topping pendamping beserta alasan rasanya.<br>**Output:** Tampilan modal kustomisasi dengan rincian subtotal *real-time* yang siap dimasukkan ke keranjang. |
| **FR-05** | Pengelolaan Keranjang Belanja (*Client-Side Cart*) | **Input:** Aksi tambah item, ubah jumlah pesanan (*stepper* +/-), dan hapus item keranjang.<br>**Proses:** Sinkronisasi array objek pesanan ke dalam `localStorage` browser (`pl_cart`), pembaruan angka lencana keranjang pada *header* dan tombol melayang (*FAB*), sinkronisasi tipe pesanan (*takeaway* / *dine-in*).<br>**Output:** Tampilan daftar pesanan keranjang belanja, subtotal per item, estimasi total belanja, dan tombol lanjut ke checkout. |
| **FR-06** | Formulir Checkout & Verifikasi Server-Side | **Input:** Nama pemesan (min 3 huruf), nomor WhatsApp (format regex `08xxxxxxxxxx`), tipe pesanan, metode bayar (Tunai/QRIS), berkas bukti bayar (khusus QRIS), dan data keranjang dalam JSON (`cartJson`).<br>**Proses:** Validasi formulir di client, pengiriman POST, kalkulasi ulang seluruh harga item dari database (mencegah manipulasi harga lewat DevTools), validasi berkas gambar via PHP `finfo` (ekstensi, tipe MIME image/jpeg atau image/png, ukuran maks 2MB), penyimpanan berkas fisik unik `bukti_YmdHis_hash.ext`.<br>**Output:** Transaksi aman siap diproses ke penyimpanan permanen. |
| **FR-07** | Penerbitan Nomor Antrean Thread-Safe | **Input:** Transaksi checkout yang telah tervalidasi.<br>**Proses:** Pembukaan transaksi basis data (`START TRANSACTION`), penguncian baris (`SELECT MAX(urutan_harian) ... FOR UPDATE`) untuk tanggal hari ini, kalkulasi `urutan_harian + 1`, pembentukan nomor antrean berformat `PC-YYYYMMDD-XXX`, penyimpanan *batch* ke tabel `orders`, `order_items`, dan snapshot harga ke `order_item_toppings`, diakhiri perintah `COMMIT`.<br>**Output:** Tiket antrean resmi terbit dan pembersihan keranjang belanja lokal. |
| **FR-08** | Pelacakan Tiket Digital Live & Riwayat | **Input:** Parameter nomor antrean (`?no=PC-...`) atau ID sesi pengguna terdaftar.<br>**Proses:** Pengambilan data status pesanan dan status bayar dari database, penentuan kelas *stepper* antrean (Menunggu -> Sedang Dipanggang -> Siap Diambil -> Selesai), pengaktifan penyegaran otomatis (*auto-refresh*) tiap 15 detik.<br>**Output:** Antarmuka tiket digital dengan animasi status, detail pesanan, rincian biaya, tombol pemesanan baru, serta daftar 5 riwayat pesanan sebelumnya. |
| **FR-09** | Workstation POS Kasir untuk Walk-In | **Input:** Klik kartu menu kasir (atau shortcut keyboard `/` untuk pencarian), modal kustomisasi POS, nama pelanggan (tombol cepat Walk-in, Meja, Bungkus), nomor WA (opsional), tipe pesanan, metode pembayaran (Tunai/QRIS), dan nominal uang tunai diterima.<br>**Proses:** Penyusunan slip pesanan POS kasir, perhitungan otomatis uang kembalian (dengan tombol cepat pecahan 20rb, 50rb, 100rb, atau uang pas), penyimpanan pesanan kasir secara transaksional di mana metode tunai otomatis berstatus `verified` dan `dimasak`.<br>**Output:** Notifikasi pesanan kasir berhasil, terbit tiket antrean langsung masuk dapur tanpa perlu verifikasi ulang. |
| **FR-10** | Verifikasi Pembayaran Bukti QRIS Kasir | **Input:** Klik tombol verifikasi pada daftar Live Orders atau modal KDS, keputusan Terima atau Tolak.<br>**Proses:** Peninjauan gambar bukti bayar (dengan fitur klik perbesar), pencocokan nominal tagihan, eksekusi pembaruan status atomic: Keputusan Terima mengubah `status_bayar = 'verified'` dan `status_pesanan = 'dimasak'`; Keputusan Tolak mengubah `status_bayar = 'rejected'` dan `status_pesanan = 'batal'`. Menggunakan klausul `WHERE status_pesanan = 'pending'` untuk mencegah *double-verification* (race condition).<br>**Output:** Notifikasi hasil verifikasi dan pembaruan antrean kasir. |
| **FR-11** | Pembaruan Status Produksi Dapur (*KDS Tracker*) | **Input:** Klik tombol aksi status: Siap Ambil, Selesai, atau Batalkan Pesanan.<br>**Proses:** Validasi alur transisi status resmi (Dimasak -> Siap Ambil -> Selesai; pembatalan hanya dari status non-selesai), eksekusi query update bersyarat.<br>**Output:** Status pesanan maju secara *real-time* dan terhubung langsung ke tampilan layar tiket pelanggan. |
| **FR-12** | Manajemen Data Menu (*CRUD & Foto*) | **Input:** Formulir data menu: kategori, nama menu, harga dasar, deskripsi, status ketersediaan (*checkbox* tersedia), dan unggah berkas foto menu (JPG/PNG/WEBP maks 2MB).<br>**Proses:** Tambah data menu baru, edit menu lama beserta penggantian berkas foto (foto lama dihapus otomatis), penghapusan menu dengan proteksi integritas (penghapusan ditolak jika menu pernah memiliki riwayat pemesanan di `order_items`).<br>**Output:** Katalog menu terbarui secara instan pada sisi pelanggan maupun POS kasir. |
| **FR-13** | Manajemen Data Topping (*CRUD*) | **Input:** Nama topping, harga topping, dan status ketersediaan.<br>**Proses:** Tambah varian topping baru, perbarui data topping, penghapusan topping dengan pemeriksaan relasi transaksi (penghapusan ditolak jika pernah dipesan).<br>**Output:** Daftar topping aktif langsung menyesuaikan pada modal customizer pemesanan. |
| **FR-14** | Manajemen Aturan Rekomendasi Topping | **Input:** Pilihan topping dasar (*base*), pilihan topping pendamping (*recommended*), dan kalimat rekomendasi rasa (min 5 karakter).<br>**Proses:** Validasi bahwa topping dasar dan rekomendasi tidak boleh sama, pencegahan duplikasi pasangan (*unique pair*), penyimpanan ke tabel `topping_recommendations`.<br>**Output:** Tampilan rekomendasi topping otomatis muncul saat pelanggan mencentang topping dasar di customizer. |
| **FR-15** | Monitoring Dasbor & Laporan Omzet Periode | **Input:** Pemilihan rentang tanggal pencarian (`tgl1` s/d `tgl2`).<br>**Proses:** Validasi format tanggal, pertukaran otomatis jika `tgl1 > tgl2`, agregasi total transaksi, akumulasi omzet bersih (mengecualikan pesanan batal), pemisahan total omzet metode QRIS vs Tunai, query 5 menu terlaris (*Top 5 Best Selling*), dan penampilan 200 riwayat transaksi terkini beserta ringkasan menu.<br>**Output:** Tampilan kartu ringkasan KPI keuangan, grafik peringkat produk terlaris, dan tabel detail riwayat transaksi. |
| **FR-16** | Ekspor Laporan Penjualan ke Excel Multi-Sheet | **Input:** Klik tombol "Export Excel" pada modul laporan.<br>**Proses:** Penarikan data ringkasan, data Top 5, dan rincian transaksi periode terpilih, perakitan dokumen berbasis XML *SpreadsheetML* murni dengan header HTTP `application/vnd.ms-excel`, penyusunan 2 lembar kerja (*Sheet 1: Rekap Penjualan Resmi Bergaris* dan *Sheet 2: Data Transaksi Mentah Tabular untuk Pivot Table*).<br>**Output:** Unduhan berkas file `Rekap_Penjualan_YYYY-MM-DD_sd_YYYY-MM-DD.xls` yang langsung dapat dibuka rapi di Microsoft Excel, WPS Office, LibreOffice, maupun Google Sheets tanpa peringatan error berkas. |
| **FR-17** | Pembersihan Riwayat Transaksi & Berkas Fisik | **Input:** Perintah hapus data: Hapus Satuan (per ID), Hapus per Periode Tanggal, atau Kosongkan Seluruh Riwayat.<br>**Proses:** Seleksi data nama berkas bukti bayar pada tabel `orders` yang akan dihapus, penghapusan berkas gambar fisik dari folder server `gambar/bukti_*` menggunakan fungsi `unlink`, penghapusan baris data transaksi pada database (otomatis menghapus `order_items` dan `order_item_toppings` berkat aturan relasi `ON DELETE CASCADE`).<br>**Output:** Database dan penyimpanan media server bersih dari data sampah pengujian. |
| **FR-18** | Manajemen Sesi & Pengamanan Akses Sistem | **Input:** Aksi login, navigasi antar modul admin, dan aksi logout.<br>**Proses:** Pemusnahan sesi (`session_destroy`), pembersihan cookie autentikasi, proteksi pengalihan halaman jika user non-admin mencoba mengakses direktori `/admin/`, pencegahan serangan *Local File Inclusion* (LFI) melalui sistem *routing whitelist* terpusat.<br>**Output:** Sesi berakhir aman dan pengguna dialihkan ke halaman utama/login. |

## 3.3 Analisis Kebutuhan Non-Fungsional (*Non-Functional Requirements* / NFR)

| Kategori NFR | Parameter | Spesifikasi Teknis Implementasi |
|---|---|---|
| **NFR-01: Keamanan (*Security*)** | SQL Injection Prevention | 100% query dinamis yang memproses input pengguna menggunakan *Prepared Statements* dengan binding parameter (`mysqli_prepare`, `mysqli_stmt_bind_param`). |
| | Cross-Site Scripting (XSS) | Seluruh pencetakan variabel ke antarmuka HTML dibungkus fungsi sanitasi terpusat `e($str)` yang mengimplementasikan `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`. |
| | File Upload Hardening | Berkas bukti transfer diverifikasi melalui pemeriksaan tipe MIME biner nyata menggunakan ekstensi PHP `finfo` (*File Information*), validasi ekstensi berkas (.jpg, .jpeg, .png), pembatasan ukuran maksimal 2MB, serta penggantian nama acak berkas berbasis stempel waktu. |
| | Password Hashing | Menggunakan algoritma standar industri Bcrypt (`PASSWORD_DEFAULT`), dilengkapi mekanisme *auto-upgrade* otomatis saat akun benih lama berbasis plaintext pertama kali berhasil login. |
| **NFR-02: Kinerja (*Performance*)** | Kecepatan Muat Halaman | Waktu respon halaman (*response time*) berada di bawah 800 ms pada koneksi standar lokal, didukung oleh struktur Native PHP tanpa overhead framework besar. |
| | Optimasi Basis Data | Seluruh kolom kunci pencarian dan relasi telah dilengkapi indeks database (`idx_orders_tanggal`, `idx_orders_status`, `idx_items_order`, `idx_products_category`). |
| **NFR-03: Keandalan (*Reliability*)** | Konkurensi Tiket | Mencegah duplikasi nomor antrean melalui pembungkusan transaksi ACID InnoDB dan penguncian baris (`FOR UPDATE`) pada penghitungan nomor harian `urutan_harian`. |
| | Integritas Audit Finansial | Harga topping pada setiap transaksi disimpan sebagai salinan riil (*snapshot*) di tabel `order_item_toppings`, sehingga perubahan harga topping di kemudian hari tidak merusak keabsahan laporan keuangan masa lalu. |
| **NFR-04: Usabilitas (*Usability*)** | Desain Responsif & POS Cepat | Antarmuka berorientasi pengguna (*mobile-first* untuk pelanggan dengan *bottom sheet* modal dan tombol aksi melayang; *desktop-workstation* untuk kasir dengan pintasan keyboard `/` dan kalkulator kembalian). |
| | Notifikasi Terintegrasi | Menghindari penggunaan *pop-up* bawaan peramban (`alert/confirm`) yang mengganggu; digantikan dengan komponen notifikasi melayang (*toast notification*) dan dialog modal konfirmasi modern. |
| **NFR-05: Kompatibilitas** | Format Ekspor Terbuka | Modul ekspor Excel menggunakan standar terbuka XML SpreadsheetML 2003 yang menjamin berkas dapat dibuka sempurna di semua versi Microsoft Excel (2007 hingga 365), WPS Office, LibreOffice Calc, dan Google Sheets tanpa dependensi pustaka Composer (*PhpSpreadsheet*). |

---

# BAB IV: PERANCANGAN SISTEM (SISTEM DESAIN & UML)

## 4.1 Arsitektur Sistem Global

Sistem Pemesanan Pancong Lumer Selindung Baru dibangun menggunakan pola arsitektur **Three-Tier Architecture (Client-Server)** yang disederhanakan:
1. **Tier 1: Presentation Layer (Front-End Interface):**
   - Halaman publik pelanggan: Menampilkan katalog produk, modal customizer porsi/kematangan/topping, keranjang belanja lokal berbasis `localStorage`, formulir checkout, dan tiket status antrean digital live.
   - Halaman privat kasir/admin: Menyediakan workstation POS terintegrasi, papan kontrol Kitchen Display System (KDS) Live Orders, form CRUD master data, dan dasbor analitik omzet.
2. **Tier 2: Application / Business Logic Layer (PHP Server-Side Engines):**
   - Engine Routing & Access Control (`index.php`, `admin/ad_index.php`, `login.php`).
   - Engine Hitung Ulang & Validasi Transaksional (`user/us_checkout.php`, `admin/ad_beranda.php`).
   - Engine Generator Nomor Antrean Thread-Safe.
   - Engine Filter & Generator Ekspor SpreadsheetML XML (`admin/modLaporan/export_excel.php`).
3. **Tier 3: Data Storage Layer (MySQL Relational Database & File Storage):**
   - RDBMS MySQL / MariaDB dengan engine InnoDB (`db_pancong`).
   - Server Directory File Storage (`gambar/` untuk menu dan bukti transfer terverifikasi).

```
[ Pelanggan (Smartphone / Browser) ]      [ Kasir / Koki (Tablet / PC Kasir) ]
                  |                                           |
                  +---------------------+---------------------+
                                        | (HTTP / HTTPS Request)
                                        v
                 +---------------------------------------------+
                 |            WEB SERVER (Apache)              |
                 |  +---------------------------------------+  |
                 |  | PHP Application Routing (Front-End)   |  |
                 |  | - index.php (Katalog, Checkout, Antri)|  |
                 |  | - login.php (Auth multi-role & demo)  |  |
                 |  +---------------------------------------+  |
                 |  | PHP Administrative Core (Back-End)    |  |
                 |  | - ad_beranda.php (POS Walk-In Engine) |  |
                 |  | - dt_pesanan.php (KDS Kitchen Engine) |  |
                 |  | - export_excel.php (XML Spreadsheet)  |  |
                 |  +---------------------------------------+  |
                 |  | Business Logic & Transaction Guard    |  |
                 |  | - Prepared Statements (Anti-SQLi)     |  |
                 |  | - Safe Queue Number (FOR UPDATE)      |  |
                 |  | - Safe Fileinfo MIME Checker          |  |
                 |  +---------------------------------------+  |
                 +----------------------+----------------------+
                                        |
                  +---------------------+---------------------+
                  | (TCP Socket / SQL)                        | (I/O File System)
                  v                                           v
    +---------------------------+               +---------------------------+
    |   DATABASE (MySQL InnoDB) |               |  STORAGE MEDIA (gambar/)  |
    |   - users, categories     |               |  - menu_*.webp / jpg      |
    |   - products, toppings    |               |  - bukti_*.png / jpg      |
    |   - orders, order_items   |               |  - qris.png               |
    |   - order_item_toppings   |               +---------------------------+
    |   - topping_recommendations
    +---------------------------+
```

## 4.2 Use Case Diagram & Relasi Antar Use Case

### Diagram Relasi Aktor & Use Case
Dalam pemodelan Use Case, aktor **Pelanggan** merupakan generalisasi (*generalization*) dari aktor **Tamu**. Artinya, semua fungsi yang dapat diakses oleh Tamu dapat diakses oleh Pelanggan, ditambah fitur personalisasi riwayat akun. Aktor **Kasir/Admin** merupakan aktor terpisah dengan hak akses administratif.

```
       +---------------+
       |     Tamu      |<-------------------+ (Generalization)
       +---------------+                    |
               |                            |
               | (Mengakses)       +-----------------+
               +--------+          |    Pelanggan    |
                        |          +-----------------+
                        v                   |
             ( UC-01: Registrasi Akun )     | (Mengakses)
             ( UC-02: Login Akun )<---------+
             ( UC-03: Lihat Katalog Menu )
             ( UC-04: Kustomisasi Pesanan )
             ( UC-05: Kelola Keranjang )
             ( UC-06: Checkout & Bayar )
                 |          |
                 |          +---<<include>>---> ( Validasi Ulang Harga Server )
                 |          +---<<extend>>----> ( Upload Bukti Transfer QRIS )
                 +--------------<<include>>---> ( UC-07: Terbit Nomor Tiket )
             ( UC-08: Lacak Tiket & Riwayat )


       +--------------------+
       | Kasir / Admin Gerai|
       +--------------------+
               |
               +---> ( UC-09: POS Walk-in Kasir )
               |         |
               |         +---<<include>>---> ( UC-04: Kustomisasi Pesanan )
               |         +---<<include>>---> ( UC-07: Terbit Nomor Tiket )
               |
               +---> ( UC-10: Verifikasi Pembayaran QRIS )
               |         |
               |         +---<<extend>>----> ( UC-11: Update Status Dapur )
               |
               +---> ( UC-11: Update Status Dapur / KDS )
               +---> ( UC-12: Kelola Master Menu - CRUD )
               +---> ( UC-13: Kelola Master Topping - CRUD )
               +---> ( UC-14: Kelola Rekomendasi Topping )
               +---> ( UC-15: Lihat Laporan & Dasbor Omzet )
               |         |
               |         +---<<extend>>----> ( UC-16: Ekspor Rekap Excel )
               |         +---<<extend>>----> ( UC-17: Hapus Riwayat Transaksi )
               |
               +---> ( UC-18: Logout & Pengamanan Sesi )
```

## 4.3 Spesifikasi & Skenario Use Case Rinci (UC-01 s/d UC-18)

### UC-01: Registrasi Akun Pelanggan
- **Aktor Utama:** Tamu (*Guest*)
- **Kondisi Awal (*Pre-condition*):** Pengguna belum login dan membuka halaman pendaftaran `login.php?mode=daftar`.
- **Alur Utama (*Main Flow*):**
  1. Pengguna mengisi nama lengkap, username unik, email, password, dan konfirmasi password.
  2. Pengguna menekan tombol "Daftar Akun Sekarang".
  3. Sistem memvalidasi kelengkapan form dan mengecek ketersediaan username di tabel `users`.
  4. Sistem mengenkripsi password dengan algoritma Bcrypt dan menyimpan data baru dengan role `customer`.
  5. Sistem otomatis menginisialisasi sesi login pengguna dan mengalihkan halaman ke beranda katalog (`index.php`).
- **Alur Alternatif (*Alternative Flow*):**
  - *A1: Username sudah terdaftar:* Sistem menampilkan pesan galat "Username sudah terdaftar, silakan pilih yang lain".
  - *A2: Password dan konfirmasi tidak cocok:* Sistem menampilkan peringatan dan form tetap mempertahankan data yang diinput.
- **Kondisi Akhir (*Post-condition*):** Sesi pengguna aktif sebagai `customer`.

### UC-02: Login Akun Multi-Role & Quick Demo
- **Aktor Utama:** Tamu, Pelanggan, Kasir
- **Kondisi Awal:** Pengguna berada di halaman `login.php`.
- **Alur Utama:**
  1. Pengguna memasukkan username dan password, atau menekan tombol pintas "Login Cepat Demo Kasir / Demo Pelanggan".
  2. Sistem mencari data pengguna di tabel `users`.
  3. Sistem memverifikasi kecocokan password menggunakan `password_verify` (atau mencocokkan password plaintext dan langsung meng-upgrade hash menjadi Bcrypt).
  4. Sistem menyimpan data sesi (`user_id`, `username`, `nama_lengkap`, `role`).
  5. Jika pengguna memiliki antrean pesanan tamu di sesi sebelumnya, sistem otomatis mengklaim kepemilikan tiket tersebut ke `user_id` yang baru login.
  6. Pengguna dengan role `admin` diarahkan ke `admin/ad_index.php`, sedangkan role `customer` diarahkan ke `index.php`.
- **Alur Alternatif:**
  - *A1: Kredensial tidak valid:* Sistem menampilkan pesan galat "Username atau password salah".
- **Kondisi Akhir:** Pengguna terautentikasi dan berada di dasbor sesuai perannya.

### UC-03: Penelusuran Katalog & Filter Menu
- **Aktor Utama:** Tamu, Pelanggan
- **Kondisi Awal:** Pengguna membuka halaman beranda `index.php?page=beranda`.
- **Alur Utama:**
  1. Sistem melakukan query ke tabel `products` dan `categories` untuk menu aktif.
  2. Sistem menampilkan spanduk promo, tab kategori produk, serta kartu menu.
  3. Pengguna dapat mengetikkan kata kunci pencarian pada kotak pencarian atau mengklik tab kategori.
  4. Sistem secara instan menyaring tampilan kartu menu di sisi peramban (*client-side filtering*).
- **Kondisi Akhir:** Pengguna menemukan varian menu pancong yang diinginkan.

### UC-04: Kustomisasi Pesanan (Porsi, Kematangan, Topping & Engine Rekomendasi)
- **Aktor Utama:** Tamu, Pelanggan, Kasir (melalui POS)
- **Kondisi Awal:** Pengguna mengklik tombol "+" atau kartu menu yang tersedia.
- **Alur Utama:**
  1. Sistem membuka modal kustomisasi interaktif (*bottom sheet* pada ponsel).
  2. Pengguna memilih ukuran porsi: "1 Loyang Penuh" atau "1/2 Loyang" (sistem otomatis menghitung penyesuaian harga 50%).
  3. Pengguna menentukan tingkat kematangan adonan: "Setengah Matang (Lumer)", "3/4 Matang", atau "Matang Sempurna".
  4. Pengguna mencentang satu atau beberapa topping tambahan.
  5. Setiap kali topping dasar dipilih, mesin rekomendasi memeriksa aturan di `topping_recommendations` dan memunculkan rekomendasi rasa pendamping beserta alasan kecocokan rasanya.
  6. Pengguna mengatur kuantitas item (1–20), sistem memperbarui subtotal secara *real-time*.
  7. Pengguna menekan tombol "Tambah ke Keranjang".
- **Kondisi Akhir:** Item pesanan dengan atribut kustomisasi tersimpan di keranjang belanja.

### UC-05: Pengelolaan Keranjang Belanja (*Cart Persistence*)
- **Aktor Utama:** Tamu, Pelanggan
- **Kondisi Awal:** Pengguna telah memasukkan minimal satu item ke keranjang.
- **Alur Utama:**
  1. Data pesanan disimpan ke dalam `localStorage` (`pl_cart`).
  2. Sistem menampilkan ringkasan jumlah item dan total belanja pada header, mini-cart samping, serta tombol melayang di layar ponsel.
  3. Pengguna dapat mengubah kuantitas (+/-) atau menghapus item pesanan.
  4. Pengguna dapat memilih opsi penyajian: "Bungkus (Takeaway)" atau "Makan di Tempat (Dine-in)".
- **Kondisi Akhir:** Keranjang siap diproses ke halaman checkout.

### UC-06: Checkout Pemesanan & Pembayaran Mandiri
- **Aktor Utama:** Tamu, Pelanggan
- **Kondisi Awal:** Keranjang belanja berisi item dan pengguna membuka `index.php?page=checkout`.
- **Alur Utama:**
  1. Sistem merender tabel ulasan pesanan dari data keranjang.
  2. Pengguna mengisi nama pemesan dan nomor WhatsApp aktif.
  3. Pengguna memilih metode pembayaran: "QRIS" atau "Tunai di Kasir".
  4. Jika memilih QRIS, sistem menampilkan kode QRIS resmi dan pengguna mengunggah tangkapan layar bukti transfer (JPG/PNG maks 2MB).
  5. Pengguna menekan tombol "Kirim Pesanan & Dapatkan Tiket".
  6. Server memvalidasi seluruh input, mengecek tipe MIME berkas via `finfo`, memindahkan berkas ke folder `gambar/`, dan menghitung ulang harga asli dari database.
  7. Server memulai transaksi database, mengambil nomor antrean berurutan dengan aman (`FOR UPDATE`), dan menyimpan pesanan ke tabel `orders`, `order_items`, dan `order_item_toppings`.
  8. Transaksi di-commit dan sistem mengarahkan browser ke halaman tiket antrean (`?page=antrean&no=PC-...&baru=1`).
- **Alur Alternatif:**
  - *A1: Berkas bukti bukan gambar / ukuran > 2MB:* Sistem membatalkan transaksi dan menampilkan pesan kesalahan.
  - *A2: Harga atau menu tidak valid di server:* Transaksi di-rollback dan pengguna diminta memilih ulang.
- **Kondisi Akhir:** Pesanan tersimpan di database dengan status awal `pending`.

### UC-07: Penerbitan Nomor Tiket Antrean
- **Aktor Utama:** Sistem
- **Kondisi Awal:** Transaksi checkout online atau POS kasir berhasil di-commit.
- **Alur Utama:**
  1. Sistem menghasilkan nomor antrean dengan format baku `PC-YYYYMMDD-XXX` (contoh: `PC-20260923-001`).
  2. Sistem menghapus keranjang belanja di browser pelanggan (`localStorage.removeItem('pl_cart')`) agar tidak terjadi pengiriman ganda.
  3. Sistem memunculkan notifikasi sukses penerbitan tiket.
- **Kondisi Akhir:** Tiket aktif siap dipantau.

### UC-08: Pelacakan Status Antrean Real-Time & Riwayat
- **Aktor Utama:** Tamu, Pelanggan
- **Kondisi Awal:** Pengguna berada di halaman antrean `index.php?page=antrean`.
- **Alur Utama:**
  1. Sistem mendeteksi parameter tiket `?no=` atau memanggil tiket aktif terakhir milik sesi pengguna.
  2. Sistem menampilkan kartu tiket berisi nomor antrean, nama pemesan, tipe sajian, status pembayaran, dan rincian item.
  3. Sistem memvisualisasikan progress bar (*stepper* 4 tahap): Menunggu Konfirmasi -> Sedang Dipanggang -> Siap Diambil -> Selesai.
  4. Halaman memperbarui statusnya secara otomatis setiap 15 detik.
  5. Jika pengguna berstatus login, sistem menampilkan daftar 5 riwayat pesanan sebelumnya.
- **Kondisi Akhir:** Pelanggan mendapatkan kepastian status pemanggangan secara transparan.

### UC-09: Point of Sale (POS) Kasir untuk Pelanggan Walk-In
- **Aktor Utama:** Kasir / Admin Gerai
- **Kondisi Awal:** Kasir telah login dan berada di halaman dasbor `admin/ad_beranda.php`.
- **Alur Utama:**
  1. Kasir melihat katalog menu bergambar pada workstation POS.
  2. Kasir dapat menekan tombol shortcut keyboard `/` untuk mencari menu secara cepat.
  3. Kasir mengklik menu, mengatur kustomisasi pada modal POS, dan item masuk ke slip kasir sebelah kanan.
  4. Kasir memilih preset nama pemesan ("Walk-in", "Meja 1", "Bungkus") atau mengetikkan nama pelanggan.
  5. Kasir memilih metode pembayaran "Tunai" atau "QRIS".
  6. Jika tunai, kasir memasukkan nominal uang yang diterima (atau memilih tombol instan 20rb, 50rb, 100rb, Uang Pas); kalkulator kasir langsung menghitung uang kembalian.
  7. Kasir menekan tombol "Proses Pesanan POS".
  8. Sistem menyimpan transaksi di mana metode tunai otomatis diberi status pembayaran `verified` dan status pesanan `dimasak`.
- **Kondisi Akhir:** Pesanan walk-in langsung masuk ke antrean dapur pemanggangan.

### UC-10: Verifikasi Pembayaran Bukti QRIS Kasir
- **Aktor Utama:** Kasir / Admin Gerai
- **Kondisi Awal:** Terdapat pesanan online baru dengan metode QRIS berstatus `pending`.
- **Alur Utama:**
  1. Kasir membuka halaman `admin/ad_index.php?mod=pesanan` dan mengklik tombol "Verifikasi" pada kartu pesanan terkait.
  2. Sistem menampilkan halaman verifikasi berisi foto bukti transfer, nominal tagihan, dan rincian item.
  3. Kasir mengklik bukti foto untuk memperbesar dan mencocokkan mutasi rekening.
  4. Kasir menekan tombol "Terima Pembayaran" atau "Tolak Pembayaran".
  5. Sistem mengeksekusi update status secara aman (`WHERE status_pesanan = 'pending'`). Jika diterima, status berubah menjadi `verified` dan pesanan menjadi `dimasak`. Jika ditolak, status berubah menjadi `rejected` dan pesanan menjadi `batal`.
- **Kondisi Akhir:** Status pesanan terbarui dan dapur dapat memulai pemanggangan.

### UC-11: Pembaruan Status Produksi Dapur (KDS Tracker)
- **Aktor Utama:** Kasir / Staf Dapur
- **Kondisi Awal:** Pesanan berada dalam status `dimasak` atau `siap_ambil`.
- **Alur Utama:**
  1. Pada papan Live Orders, staf dapur melihat daftar pesanan yang sedang dipanggang.
  2. Setelah pancong matang dan dikemas, staf menekan tombol "Siap Ambil". Sistem memperbarui status menjadi `siap_ambil` (layar pelanggan berubah memanggil pelanggan ke counter).
  3. Saat pesanan diserahkan ke tangan pelanggan, staf menekan tombol "Selesai". Sistem memperbarui status menjadi `selesai` (transaksi masuk ke kalkulasi omzet resmi).
  4. Jika pelanggan membatalkan pesanan sebelum matang, kasir dapat memilih aksi "Batalkan Pesanan".
- **Kondisi Akhir:** Siklus hidup pesanan selesai tercatat di sistem.

### UC-12: Manajemen Data Menu (CRUD)
- **Aktor Utama:** Admin Gerai
- **Kondisi Awal:** Admin membuka menu navigasi `admin/ad_index.php?mod=menu`.
- **Alur Utama:**
  1. Admin dapat melihat daftar seluruh menu beserta harga dan status ketersediaannya.
  2. **Tambah Menu:** Admin mengisi nama, kategori, harga dasar, deskripsi, mengunggah foto menu, dan menekan Simpan.
  3. **Ubah Menu:** Admin mengedit informasi menu dan dapat mengganti foto lama dengan yang baru.
  4. **Hapus Menu:** Admin menekan tombol Hapus. Sistem memvalidasi apakah menu pernah dipesan. Jika belum pernah dipesan, data dan berkas foto dihapus dari server. Jika pernah dipesan, sistem menolak penghapusan demi menjaga integritas data riwayat dan menyarankan admin menonaktifkan status ketersediaan (*is_available = 0*).
- **Kondisi Akhir:** Master data produk selalu mutakhir.

### UC-13: Manajemen Data Topping (CRUD)
- **Aktor Utama:** Admin Gerai
- **Kondisi Awal:** Admin membuka navigasi `admin/ad_index.php?mod=topping`.
- **Alur Utama:**
  1. Admin mengelola varian topping (Keju Parut, Choco Chips, Oreo Crumb, dll).
  2. Admin dapat menambah topping baru, mengubah harga, atau mengatur status aktif/non-aktif.
  3. Penghapusan topping dilindungi oleh pemeriksaan tabel relasi transaksi.
- **Kondisi Akhir:** Varian topping di modal kustomisasi selalu akurat.

### UC-14: Manajemen Aturan Rekomendasi Topping
- **Aktor Utama:** Admin Gerai
- **Kondisi Awal:** Admin membuka tab "Aturan Rekomendasi" pada modul topping.
- **Alur Utama:**
  1. Admin memilih topping dasar (*Base Topping*) dan topping rekomendasi pasangan (*Recommended Topping*).
  2. Admin menuliskan kalimat alasan rekomendasi (contoh: "Keju gurih paling cocok dipadukan Choco Chips manis").
  3. Sistem menyimpan aturan asosiasi unik tersebut ke tabel `topping_recommendations`.
- **Kondisi Akhir:** Mesin rekomendasi otomatis memandu pelanggan saat memesan pancong.

### UC-15: Monitoring Dasbor & Laporan Omzet Periode
- **Aktor Utama:** Admin / Pemilik Gerai
- **Kondisi Awal:** Admin membuka modul `admin/ad_index.php?mod=laporan`.
- **Alur Utama:**
  1. Admin menentukan filter tanggal awal dan akhir (default: hari ini).
  2. Sistem menghitung dan menampilkan kartu indikator kinerja utama (KPI): Total Transaksi, Omzet Bersih Penjualan, Porsi Pembayaran QRIS, Porsi Pembayaran Tunai, dan Jumlah Pesanan Batal.
  3. Sistem menampilkan tabel peringkat 5 Menu Terlaris (*Top 5 Products*) berdasarkan volume pesanan dan perolehan omzet.
  4. Sistem merender tabel 200 transaksi terakhir lengkap dengan ringkasan menu yang dipesan.
- **Kondisi Akhir:** Pemilik memperoleh visibilitas penuh terhadap performa finansial gerai.

### UC-16: Ekspor Rekap Penjualan ke Excel Multi-Sheet
- **Aktor Utama:** Admin Gerai
- **Kondisi Awal:** Admin berada di halaman laporan dengan filter periode yang telah ditentukan.
- **Alur Utama:**
  1. Admin menekan tombol "Export ke Excel".
  2. Script `admin/modLaporan/export_excel.php` memproses data dan menghasilkan output berformat SpreadsheetML XML.
  3. Dokumen terbagi menjadi dua lembar kerja:
     - **Sheet 1 (Rekap Penjualan):** Berisi kop resmi, nama kasir, waktu cetak, kartu rekapitulasi 5 metrik omzet, tabel Top 5 menu, dan rincian transaksi berbingkai rapi siap cetak lanskap.
     - **Sheet 2 (Data Transaksi):** Berisi data tabular bersih tanpa format sel kompleks yang siap diolah untuk *Pivot Table* atau analisis lanjutan.
  4. Berkas terunduh secara otomatis dengan nama file informatif `Rekap_Penjualan_YYYY-MM-DD_sd_YYYY-MM-DD.xls`.
- **Kondisi Akhir:** Laporan keuangan terarsip dalam format Excel standar.

### UC-17: Pembersihan Riwayat Transaksi & Berkas Fisik
- **Aktor Utama:** Admin Gerai
- **Kondisi Awal:** Admin berada di modul laporan atau Live Orders.
- **Alur Utama:**
  1. Admin memilih mode pembersihan data: Hapus Transaksi Tertentu (Single ID), Hapus Seluruh Transaksi dalam Rentang Tanggal, atau Kosongkan Seluruh Riwayat.
  2. Sistem meminta konfirmasi tindakan kepada admin.
  3. Sistem mengambil nama berkas bukti transfer terkait dan menghapusnya dari penyimpanan fisik server (`gambar/bukti_*`).
  4. Sistem mengeksekusi penghapusan data pada tabel `orders` yang secara berantai menghapus baris di `order_items` dan `order_item_toppings` berkat konfigurasi `ON DELETE CASCADE`.
- **Kondisi Akhir:** Data transaksi dan berkas fisik terhapus secara bersih dan aman.

### UC-18: Logout & Pengamanan Sesi
- **Aktor Utama:** Pelanggan, Kasir/Admin
- **Kondisi Awal:** Pengguna berada dalam kondisi login.
- **Alur Utama:**
  1. Pengguna mengklik tombol "Keluar / Logout".
  2. Sistem menghapus seluruh variabel `$_SESSION`, membatalkan cookie sesi di peramban, dan memanggil `session_destroy()`.
  3. Pengguna dialihkan kembali ke halaman beranda publik atau login.
- **Kondisi Akhir:** Sesi pengguna berakhir sepenuhnya.

---

## 4.4 Activity Diagram Alur Bisnis Utama

### ACT-01: Alur Pemesanan & Checkout Mandiri Pelanggan Online

```
+----------------------------------------------------------------------------------------------------------------+
| PELANGGAN                           | SISTEM (FRONT-END & BACK-END)         | DATABASE (MySQL)                 |
+-------------------------------------+---------------------------------------+----------------------------------+
| (O) Start                           |                                       |                                  |
|  |                                  |                                       |                                  |
|  v                                  |                                       |                                  |
| Buka Katalog Pancong                |                                       |                                  |
|  |                                  |                                       |                                  |
|  v                                  |                                       |                                  |
| Klik Menu & Atur Kustomisasi        | Hitung Subtotal Real-Time             |                                  |
| (Porsi, Matang, Multi-Topping)      | Tampilkan Rekomendasi Topping         |                                  |
|  |                                  |  |                                    |                                  |
|  v                                  |  v                                    |                                  |
| Masukkan ke Keranjang Belanja       | Simpan ke localStorage ('pl_cart')    |                                  |
|  |                                  |  |                                    |                                  |
|  v                                  |  v                                    |                                  |
| Buka Halaman Checkout               | Tampilkan Ulasan Item & Total Biaya   |                                  |
|  |                                  |                                       |                                  |
|  v                                  |                                       |                                  |
| Isi Nama, WA, Tipe, & Metode Bayar  |                                       |                                  |
|  |                                  |                                       |                                  |
|  +--------< Metode QRIS? >---------+                                       |                                  |
|  | Ya                               | Tidak (Tunai)                         |                                  |
|  v                                  v                                       |                                  |
| Upload Bukti Transfer               Lewati Upload Berkas                    |                                  |
|  |                                  |                                       |                                  |
|  +----------------+-----------------+                                       |                                  |
|                   |                                                         |                                  |
|                   v                                                         |                                  |
|           Klik Tombol "Bayar"       |                                       |                                  |
|                   |                 |                                       |                                  |
|                   +---------------->| Validasi Form & File Upload (finfo)   |                                  |
|                                     |  |                                    |                                  |
|                                     |  v                                    |                                  |
|                                     | Hitung Ulang Harga Asli Server -------> Ambil Harga Produk & Topping     |
|                                     |  |                                    | (Cek Status is_available)        |
|                                     |  v                                    |                                  |
|                                     | Buka Transaksi Database (ACID) ------> START TRANSACTION                 |
|                                     |  |                                    |  |                               |
|                                     |  v                                    |  v                               |
|                                     | Hitung Nomor Antrean Baru -----------> SELECT MAX(urutan_harian)...      |
|                                     | (Format: PC-YYYYMMDD-XXX)             | FOR UPDATE                       |
|                                     |  |                                    |  |                               |
|                                     |  v                                    |  v                               |
|                                     | Simpan Data Pesanan & Detail --------> INSERT orders, order_items,       |
|                                     |                                       | order_item_toppings              |
|                                     |  |                                    |  |                               |
|                                     |  v                                    |  v                               |
|                                     | Selesai & Commit Transaksi ----------> COMMIT                            |
|                                     |  |                                    |                                  |
|                                     |  v                                    |                                  |
| Halaman Dialihkan ke Tiket Antrean <+ Kosongkan localStorage Keranjang      |                                  |
|  |                                  | Aktifkan Auto-Refresh 15 Detik        |                                  |
|  v                                  |                                       |                                  |
| Pantau Live Status Panggangan       |                                       |                                  |
|  |                                  |                                       |                                  |
| (X) End                             |                                       |                                  |
+----------------------------------------------------------------------------------------------------------------+
```

### ACT-02: Alur Pelayanan Walk-In POS Kasir

```
+----------------------------------------------------------------------------------------------------------------+
| KASIR / OPERATOR POS                | WORKSTATION POS (JS & PHP)            | DATABASE (MySQL)                 |
+-------------------------------------+---------------------------------------+----------------------------------+
| (O) Start                           |                                       |                                  |
|  |                                  |                                       |                                  |
|  v                                  |                                       |                                  |
| Akses Modul POS Kasir               | Render Menu Bergambar & Kategori      |                                  |
|  |                                  |                                       |                                  |
|  v                                  |                                       |                                  |
| Cari Menu (Pintasan Keyboard '/')   | Saring Menu secara Cepat              |                                  |
|  |                                  |                                       |                                  |
|  v                                  |                                       |                                  |
| Klik Menu & Atur Kustomisasi        | Masukkan Item ke Slip Kasir Interaktif|                                  |
|  |                                  | Hitung Subtotal & Grand Total         |                                  |
|  v                                  |                                       |                                  |
| Pilih Preset Nama Pelanggan         |                                       |                                  |
| (Walk-in, Meja, Bungkus)            |                                       |                                  |
|  |                                  |                                       |                                  |
|  v                                  |                                       |                                  |
| Pilih Metode Pembayaran (Tunai/QRIS)|                                       |                                  |
|  |                                  |                                       |                                  |
|  +-------< Pembayaran Tunai? >-----+                                       |                                  |
|  | Ya                               | Tidak (QRIS)                          |                                  |
|  v                                  v                                       |                                  |
| Input Uang Tunai Diterima           Set Status Bayar = 'pending'            |                                  |
| (Kalkulator Hitung Kembalian)       |                                       |                                  |
| Set Status Bayar = 'verified'       |                                       |                                  |
| Set Status Pesanan = 'dimasak'      |                                       |                                  |
|  |                                  |                                       |                                  |
|  +----------------+-----------------+                                       |                                  |
|                   |                                                         |                                  |
|                   v                                                         |                                  |
|           Klik "Proses Pesanan"     |                                       |                                  |
|                   |                 |                                       |                                  |
|                   +---------------->| Jalankan Transaksi Database ---------> START TRANSACTION;                |
|                                     |                                       | Lock Urutan Harian (FOR UPDATE); |
|                                     |                                       | INSERT orders, order_items;      |
|                                     |                                       | COMMIT;                          |
|                                     |  |                                    |                                  |
|                                     |  v                                    |                                  |
| Terima Konfirmasi & Nomor Antrean <-+ Tampilkan Notifikasi Sukses           |                                  |
| Berikan Nomor Antrean ke Pelanggan  | Reset Slip Kasir untuk Pesanan Baru   |                                  |
|  |                                  |                                       |                                  |
| (X) End                             |                                       |                                  |
+----------------------------------------------------------------------------------------------------------------+
```

### ACT-03: Alur Verifikasi Pembayaran & Pemrosesan Dapur (KDS)

```
+----------------------------------------------------------------------------------------------------------------+
| KASIR / STAF DAPUR                  | SISTEM KDS (LIVE ORDERS)              | DATABASE (MySQL)                 |
+-------------------------------------+---------------------------------------+----------------------------------+
| (O) Start                           |                                       |                                  |
|  |                                  |                                       |                                  |
|  v                                  |                                       |                                  |
| Buka Menu Live Orders               | Tampilkan Daftar Pesanan Hari Ini     | Query orders WHERE DATE=CURDATE()|
|  |                                  | (Auto-Refresh tiap 20 detik)          |                                  |
|  v                                  |                                       |                                  |
| Periksa Pesanan Berstatus Pending   | Tampilkan Tombol "Verifikasi"         |                                  |
|  |                                  |                                       |                                  |
|  v                                  |                                       |                                  |
| Buka Halaman Verifikasi Pembayaran  | Render Foto Bukti Transfer QRIS       |                                  |
|  |                                  |                                       |                                  |
|  v                                  |                                       |                                  |
| Cocokkan Bukti & Mutasi Rekening    |                                       |                                  |
|  |                                  |                                       |                                  |
|  +----< Keputusan Verifikasi? >----+                                       |                                  |
|  | Terima                           | Tolak                                 |                                  |
|  v                                  v                                       |                                  |
| Klik "Terima Pembayaran"            Klik "Tolak Pembayaran"                 |                                  |
|  |                                  |                                       |                                  |
|  | (UPDATE: bayar='verified',       | (UPDATE: bayar='rejected',            |                                  |
|  |          pesanan='dimasak')      |          pesanan='batal')             |                                  |
|  |                                  |                                       |                                  |
|  +----------------+-----------------+                                       |                                  |
|                   |                                                         |                                  |
|                   v                                                         |                                  |
|           Dapur Memanggang Pancong  |                                       |                                  |
|                   |                 |                                       |                                  |
|                   v                 |                                       |                                  |
|           Pancong Siap & Dikemas    |                                       |                                  |
|                   |                 |                                       |                                  |
|                   v                 |                                       |                                  |
|           Klik Tombol "Siap Ambil" -+-> UPDATE status_pesanan='siap_ambil' -> Database Terbarui                |
|                   |                 | (Layar Pelanggan Berubah Status)      |                                  |
|                   v                 |                                       |                                  |
|           Pelanggan Ambil Pesanan   |                                       |                                  |
|                   |                 |                                       |                                  |
|                   v                 |                                       |                                  |
|           Klik Tombol "Selesai" ----+-> UPDATE status_pesanan='selesai' ----> Masuk ke Agregasi Omzet Bersih   |
|                   |                 |                                       |                                  |
| (X) End <---------+                 |                                       |                                  |
+----------------------------------------------------------------------------------------------------------------+
```

---

## 4.5 Sequence Diagram Interaksi Komponen

### SEQ-01: Proses Checkout Online Transaksional Pelanggan
Menjelaskan komunikasi berurutan antara Aktor Pelanggan, Objek Antarmuka JavaScript, Skrip Pengendali PHP, Berkas Sistem, dan Tabel Basis Data.

```
Pelanggan       validasi.js       us_checkout.php     koneksi.php     File System        MySQL Database
    |                 |                  |                 |               |                   |
    |--1. Klik Bayar->|                  |                 |               |                   |
    |                 |--2. ValidasiForm>|                 |               |                   |
    |                 |--3. POST Checkout (cartJson, form)->               |                   |
    |                 |                  |                 |               |                   |
    |                 |                  |--4. finfo MIME->|-------------->| (Validasi File)   |
    |                 |                  |                 |               |--File OK--------->|
    |                 |                  |--5. move_uploaded_file--------->| (Simpan bukti_*)  |
    |                 |                  |                 |               |                   |
    |                 |                  |--6. Hitung Ulang Harga Server---------------------->| SELECT products,
    |                 |                  |                 |               |                   | toppings WHERE id
    |                 |                  |<--Harga Terverifikasi-------------------------------|
    |                 |                  |                 |               |                   |
    |                 |                  |--7. START TRANSACTION------------------------------>|
    |                 |                  |--8. Lock Urutan Antrean---------------------------->| SELECT MAX(urutan)
    |                 |                  |                 |               |                   | ... FOR UPDATE
    |                 |                  |<--Nomor Urut Baru (XXX)-----------------------------|
    |                 |                  |                 |               |                   |
    |                 |                  |--9. INSERT orders (no_antrean, total, pending)----->|
    |                 |                  |--10. INSERT order_items (porsi, matang, qty)------->|
    |                 |                  |--11. INSERT order_item_toppings (snapshot harga)--->|
    |                 |                  |--12. COMMIT TRANSACTION---------------------------->|
    |                 |                  |                 |               |                   |
    |<--13. Redirect ke us_antrean.php---|                 |               |                   |
    |                 |                  |                 |               |                   |
    |--14. Muat Tiket>|                  |                 |               |                   |
    |                 |--15. Hapus Keranjang (removeItem 'pl_cart')        |                   |
    |                 |--16. Jadwalkan Auto-Polling (15 detik)             |                   |
```

### SEQ-02: Proses Transaksi POS Walk-In Kasir
Menjelaskan proses saat kasir menginput pesanan langsung dari pelanggan yang datang ke gerai.

```
 Kasir           ad_beranda.php (POS JS)     ad_beranda.php (PHP Handler)       MySQL Database
   |                        |                             |                            |
   |--1. Klik Menu Pancong->|                             |                            |
   |--2. Pilih Kustomisasi->|                             |                            |
   |--3. Masukkan ke Slip-->| (Perbarui Slip & Total)     |                            |
   |--4. Input Uang Tunai-->| (Kalkulasi Kembalian)       |                            |
   |--5. Klik Proses Slip-->|                             |                            |
   |                        |--6. POST aksi=pos (JSON)--->|                            |
   |                        |                             |--7. Verifikasi Data & Harga|
   |                        |                             |--8. START TRANSACTION----->|
   |                        |                             |--9. Lock Urutan Harian---->| FOR UPDATE
   |                        |                             |<--Urutan Terkunci----------|
   |                        |                             |--10. INSERT orders-------->| status='dimasak',
   |                        |                             |                            | bayar='verified'
   |                        |                             |--11. INSERT order_items--->|
   |                        |                             |--12. COMMIT--------------->|
   |                        |<--13. Respon Sukses & No----+                            |
   |<--14. Cetak/Beri Tiket-| (Reset Form POS)            |                            |
```

### SEQ-03: Proses Verifikasi Pembayaran Bukti QRIS oleh Kasir

```
 Kasir          dt_pesanan.php     konfirmasiPesanan.php     MySQL Database
   |                  |                      |                      |
   |--1. Buka Pesanan>|                      |                      |
   |                  |--2. Ambil Pesanan-------------------------->| SELECT * FROM orders WHERE status='pending'
   |                  |<--Daftar Pesanan----------------------------|
   |--3. Klik Verif-->|                      |                      |
   |                  |--4. Buka Halaman---->|                      |
   |                  |                      |--5. Tampilkan Bukti & Detail
   |--6. Klik Terima/Tolak Form------------->|                      |
   |                  |                      |--7. UPDATE orders SET status_bayar=?,
   |                  |                      |     status_pesanan=? WHERE no_antrean=?
   |                  |                      |     AND status_pesanan='pending'------->| (Optimistic Lock)
   |                  |                      |<--Update Berhasil (affected_rows=1)-----|
   |<--8. Notifikasi Berhasil & Redirect-----|                      |
```

---

## 4.6 Class Diagram & Struktur Berorientasi Objek

Berikut adalah spesifikasi perancangan kelas entitas (*data entities*) dan utilitas kontrol yang membangun sistem:

```
+---------------------------------------------------------------------------------+
|                                 <<utility>>                                     |
|                                   Koneksi                                       |
+---------------------------------------------------------------------------------+
| + $DB_HOST: string                                                              |
| + $DB_USER: string                                                              |
| + $DB_PASS: string                                                              |
| + $DB_NAME: string                                                              |
| + $koneksi: mysqli                                                              |
+---------------------------------------------------------------------------------+
| + e(str: string): string                                                        |
| + rupiah(angka: int): string                                                    |
| + tgl_indo(datetime: string): string                                            |
| + label_status_pesanan(status: string): string                                  |
| + label_status_bayar(status: string): string                                    |
| + pastikan_folder_gambar(): string                                              |
+---------------------------------------------------------------------------------+

+-------------------------+            +-----------------------------------------+
|          User           |            |                Category                 |
+-------------------------+            +-----------------------------------------+
| - id: int               |            | - id: int                               |
| - username: string      |            | - kode_kategori: string                 |
| - password: string      |            | - nama_kategori: string                 |
| - nama_lengkap: string  |            +-----------------------------------------+
| - email: string         |                                 | 1
| - role: RoleEnum        |                                 |
| - created_at: datetime  |                                 | memuat
+-------------------------+                                 |
             | 1                                            v *
             |                         +-----------------------------------------+
             | melakukan               |                 Product                 |
             | 0..*                    +-----------------------------------------+
             v                         | - id: int                               |
+-------------------------+            | - category_id: int                      |
|          Order          |            | - nama_menu: string                     |
+-------------------------+            | - deskripsi: string                     |
| - id: int               |            | - harga_dasar: int                      |
| - no_antrean: string    |            | - gambar: string                        |
| - urutan_harian: int    |            | - is_available: bool                    |
| - user_id: int          |            | - created_at: datetime                  |
| - nama_pemesan: string  |            +-----------------------------------------+
| - no_wa: string         |                                 | 1
| - tipe_order: TipeEnum  |                                 |
| - metode_bayar: BayarEnum                                 | dipesan
| - bukti_bayar: string   |                                 |
| - status_bayar: SBayar  |                                 v *
| - status_pesanan: SPesan| 1        * +-----------------------------------------+
| - total_bayar: int      |------------|                OrderItem                |
| - created_at: datetime  | memuat     +-----------------------------------------+
+-------------------------+            | - id: int                               |
                                       | - order_id: int                         |
                                       | - product_id: int                       |
                                       | - porsi: PorsiEnum                      |
                                       | - kematangan: MatangEnum                |
                                       | - qty: int                              |
                                       | - subtotal: int                         |
                                       +-----------------------------------------+
                                                            | 1
                                                            |
                                                            | diberi topping
                                                            | *
                                       +-----------------------------------------+
                                       |            OrderItemTopping             |
                                       +-----------------------------------------+
                                       | - id: int                               |
                                       | - order_item_id: int                    |
                                       | - topping_id: int                       |
                                       | - harga_topping: int (snapshot)         |
                                       +-----------------------------------------+
                                                            | *
                                                            |
                                                            | mereferensi
                                                            | 1
                                       +-----------------------------------------+
                                       |                 Topping                 |
                                       +-----------------------------------------+
                                       | - id: int                               |
                                       | - nama_topping: string                  |
                                       | - harga: int                            |
                                       | - is_available: bool                    |
                                       +-----------------------------------------+
                                         | 1 (base)             | 1 (recommended)
                                         |                      |
                                         +----------+-----------+
                                                    |
                                                    v *
                                       +-----------------------------------------+
                                       |          ToppingRecommendation         |
                                       +-----------------------------------------+
                                       | - id: int                               |
                                       | - base_topping_id: int                  |
                                       | - recommended_topping_id: int           |
                                       | - pesan_alasan: string                  |
                                       +-----------------------------------------+
```

---

## 4.7 Perancangan Basis Data (ERD & Kamus Data 8 Tabel)

Basis data bernama **`db_pancong`** menggunakan mesin penyimpanan **InnoDB** dengan *charset* **`utf8mb4_unicode_ci`**. Terdiri dari 8 tabel relasional yang dinormalisasi hingga bentuk normal ketiga (3NF).

### Kamus Data Detail Tabel

#### 1. Tabel `users`
Menyimpan kredensial otentikasi dan hak akses sistem.
- `id` : INT UNSIGNED, Primary Key, Auto Increment.
- `username` : VARCHAR(50), Not Null, Unique Key (`uq_users_username`).
- `password` : VARCHAR(255), Not Null (Hash Bcrypt).
- `nama_lengkap` : VARCHAR(100), Not Null.
- `email` : VARCHAR(100), Default NULL.
- `role` : ENUM('customer', 'admin'), Not Null, Default 'customer'.
- `created_at` : TIMESTAMP, Not Null, Default CURRENT_TIMESTAMP.

#### 2. Tabel `categories`
Menyimpan kelompok produk.
- `id` : INT UNSIGNED, Primary Key, Auto Increment.
- `kode_kategori` : VARCHAR(30), Not Null, Unique Key (`uq_categories_kode`). Nilai: `1_loyang`, `half_loyang`, `topping`.
- `nama_kategori` : VARCHAR(60), Not Null.

#### 3. Tabel `products`
Menyimpan data master kue pancong.
- `id` : INT UNSIGNED, Primary Key, Auto Increment.
- `category_id` : INT UNSIGNED, Not Null, Foreign Key ke `categories(id)` ON UPDATE CASCADE ON DELETE RESTRICT.
- `nama_menu` : VARCHAR(100), Not Null.
- `deskripsi` : VARCHAR(255), Default NULL.
- `harga_dasar` : INT UNSIGNED, Not Null, Default 0.
- `gambar` : VARCHAR(255), Default NULL (Nama file di direktori `gambar/`).
- `is_available` : TINYINT(1), Not Null, Default 1 (1 = Tersedia, 0 = Habis).
- `created_at` : TIMESTAMP, Not Null, Default CURRENT_TIMESTAMP.

#### 4. Tabel `toppings`
Menyimpan varian topping tambahan.
- `id` : INT UNSIGNED, Primary Key, Auto Increment.
- `nama_topping` : VARCHAR(80), Not Null.
- `harga` : INT UNSIGNED, Not Null, Default 0.
- `is_available` : TINYINT(1), Not Null, Default 1.

#### 5. Tabel `topping_recommendations`
Menyimpan aturan asosiasi rekomendasi rasa (*cross-selling*).
- `id` : INT UNSIGNED, Primary Key, Auto Increment.
- `base_topping_id` : INT UNSIGNED, Not Null, Foreign Key ke `toppings(id)` ON UPDATE CASCADE ON DELETE CASCADE.
- `recommended_topping_id` : INT UNSIGNED, Not Null, Foreign Key ke `toppings(id)` ON UPDATE CASCADE ON DELETE CASCADE.
- `pesan_alasan` : VARCHAR(255), Not Null, Default ''.
- *Index Unique:* `uq_rekom_pair (base_topping_id, recommended_topping_id)`.

#### 6. Tabel `orders`
Menyimpan header transaksi pemesanan dan antrean.
- `id` : INT UNSIGNED, Primary Key, Auto Increment.
- `no_antrean` : VARCHAR(20), Not Null, Unique Key (`uq_orders_antrean`), contoh: `PC-20260923-001`.
- `urutan_harian` : INT UNSIGNED, Not Null, Default 0 (Direset harian).
- `user_id` : INT UNSIGNED, Default NULL, Foreign Key ke `users(id)` ON UPDATE CASCADE ON DELETE SET NULL.
- `nama_pemesan` : VARCHAR(100), Not Null.
- `no_wa` : VARCHAR(20), Not Null.
- `tipe_order` : ENUM('dine_in', 'takeaway'), Not Null, Default 'takeaway'.
- `metode_bayar` : ENUM('qris', 'tunai'), Not Null, Default 'qris'.
- `bukti_bayar` : VARCHAR(255), Default NULL.
- `status_bayar` : ENUM('pending', 'verified', 'rejected'), Not Null, Default 'pending'.
- `status_pesanan` : ENUM('pending', 'dimasak', 'siap_ambil', 'selesai', 'batal'), Not Null, Default 'pending'.
- `total_bayar` : INT UNSIGNED, Not Null, Default 0.
- `created_at` : TIMESTAMP, Not Null, Default CURRENT_TIMESTAMP.
- *Index:* `idx_orders_tanggal (created_at)`, `idx_orders_status (status_pesanan, status_bayar)`.

#### 7. Tabel `order_items`
Menyimpan detail varian menu yang dipesan.
- `id` : INT UNSIGNED, Primary Key, Auto Increment.
- `order_id` : INT UNSIGNED, Not Null, Foreign Key ke `orders(id)` ON UPDATE CASCADE ON DELETE CASCADE.
- `product_id` : INT UNSIGNED, Not Null, Foreign Key ke `products(id)` ON UPDATE CASCADE ON DELETE RESTRICT.
- `porsi` : ENUM('1_loyang', 'half_loyang'), Not Null, Default '1_loyang'.
- `kematangan` : ENUM('matang', 'setengah_matang', 'tiga_perempat'), Default NULL.
- `qty` : INT UNSIGNED, Not Null, Default 1.
- `subtotal` : INT UNSIGNED, Not Null, Default 0.

#### 8. Tabel `order_item_toppings`
Menyimpan detail topping per item dan snapshot harga saat transaksi.
- `id` : INT UNSIGNED, Primary Key, Auto Increment.
- `order_item_id` : INT UNSIGNED, Not Null, Foreign Key ke `order_items(id)` ON UPDATE CASCADE ON DELETE CASCADE.
- `topping_id` : INT UNSIGNED, Not Null, Foreign Key ke `toppings(id)` ON UPDATE CASCADE ON DELETE RESTRICT.
- `harga_topping` : INT UNSIGNED, Not Null, Default 0 (*Audit snapshot*).

---

## 4.8 Perancangan Antarmuka Pengguna (UI/UX Design Flow)

Antarmuka dirancang dengan tema kuliner modern bertajuk *"Warm Pancong Amber"* dengan warna dominan karamel keemasan (`#b31312`, `#ea580c`, `#d97706`), latar belakang krem bersih (`#fcf9f8`), dan tipografi modern *Plus Jakarta Sans*.

### Alur Antarmuka Pengguna:
1. **Layar Publik Pelanggan:**
   - **Katalog Beranda (`us_beranda.php`):** *Header sticky* dengan logo, pencarian menu cepat, *chips* filter kategori, grid kartu menu dengan foto atau monogram visual otomatis, tombol kustomisasi "+", dan keranjang melayang (*floating action button*).
   - **Modal Kustomisasi Menu (Interactive Bottom-Sheet):** Pemilihan porsi loyang, radio button tingkat kematangan (dengan penjelasan visual lumer), daftar checkbox topping dinamis, kotak saran rekomendasi rasa otomatis, pengatur kuantitas (*stepper* +/-), dan tombol "Tambah ke Keranjang".
   - **Formulir Checkout (`us_checkout.php`):** Tiga langkah visual: (1) Ulasan Keranjang, (2) Identitas Pemesan & Pilihan Layanan (Dine-in / Takeaway), (3) Metode Pembayaran (Instruksi QRIS statis & zona *drag-and-drop* upload bukti bayar).
   - **Tiket Antrean Digital (`us_antrean.php`):** Desain tiket perforasi digital, kode antrean besar, indikator status 4 langkah bergerak (*stepper* aktif), estimasi waktu, rincian pesanan lengkap, dan penyegaran otomatis 15 detik.
2. **Layar Administratif Kasir & Dapur:**
   - **Dasbor & POS Workstation Kasir (`ad_beranda.php`):** Layar kasir terintegrasi. Sisi kiri menampilkan grid menu kasir bergambar dengan filter kategori dan pintasan pencarian `/`. Sisi kanan menampilkan slip kasir interaktif, preset nama pemesan cepat, kalkulator uang kembalian tunai, dan tombol eksekusi pesanan instan.
   - **Live Orders Kitchen Display (`dt_pesanan.php`):** Tampilan KDS bergaya kartu kanban dengan filter tab (Aktif Hari Ini, Pending, Dimasak, Siap Ambil, Selesai, Batal). Dilengkapi lencana warna kontras, rincian porsi/kematangan yang jelas bagi koki, tombol aksi status bertahap, dan auto-refresh 20 detik.
   - **Halaman Verifikasi Pembayaran (`konfirmasiPesanan.php`):** Komparasi berdampingan antara gambar bukti bayar QRIS yang diunggah pelanggan (dengan fitur *click-to-zoom*) dan nominal total transaksi yang harus dibayarkan, dilengkapi tombol "Terima Pembayaran" dan "Tolak Pembayaran".
   - **Modul Laporan & Analitik Omzet (`dt_laporan.php`):** Ringkasan kartu metrik omzet, tabel 5 menu terlaris, filter kalender rentang tanggal, tombol pembersihan riwayat, dan tombol ekspor Microsoft Excel (.xls).

---

# BAB V: IMPLEMENTASI SISTEM (HASIL BANGUN)

## 5.1 Lingkungan & Spesifikasi Teknologi (*Tech Stack*)

| Komponen | Spesifikasi / Alat yang Digunakan |
|---|---|
| **Sistem Operasi Pengembangan** | Microsoft Windows 11 Pro 64-bit |
| **Paket Server Lokal** | Laragon 6.0 / Apache 2.4.54 |
| **Bahasa Pemrograman Server** | PHP 8.1 / 8.2 Native (Clean Architecture, tanpa framework pihak ketiga) |
| **Ekstensi PHP Kunci** | `mysqli`, `fileinfo` (MIME checking), `gd` / `mbstring`, `session` |
| **Database Management System** | MySQL 8.0 / MariaDB 10.4 (InnoDB Engine, utf8mb4) |
| **Bahasa Sisi Klien (*Front-End*)** | HTML5 Semantik, CSS3 Modern (CSS Variables, Flexbox, CSS Grid), Vanilla JavaScript (ES6+) |
| **Penyimpanan Klien** | Web Storage API (`localStorage` untuk keranjang belanja & persistensi tipe pesanan) |
| **Font & Ikonografi** | Plus Jakarta Sans (Google Fonts), SVG Vector kustom inline |
| **Editor Kode** | Visual Studio Code & Claude Code CLI |

## 5.2 Struktur Arsitektur Direktori & Modul Proyek

Sistem diorganisasikan secara rapi dengan pemisahan tegas antara modul publik pelanggan dan modul administratif:

```
Pancong-Lumer/
│
├── config/
│   └── koneksi.php           # Konfigurasi DB terpusat, penanganan error 500 ramah,
│                             # helper sanitasi e(), rupiah(), tgl_indo(), label status
│
├── css/
│   └── style.css             # Desain CSS kustom responsif (tema Warm Pancong Amber,
│                             # layout grid, modal bottom sheet, tiket antrean, POS slip)
│
├── js/
│   └── validasi.js           # Engine JavaScript: state localStorage (pl_cart), kalkulasi customizer,
│                             # filter katalog, pintasan keyboard POS '/', dialog modal konfirmasi
│
├── gambar/                   # Direktori penyimpanan media (menu, bukti_*, qris.png, svg fallback)
│
├── user/                     # Sub-Sistem Pemesanan Pelanggan (Front-End)
│   ├── us_header.php         # Navigasi sticky, pencarian, lencana keranjang, status akun
│   ├── us_beranda.php        # Katalog menu, chips kategori, promo hero, modal customizer
│   ├── us_checkout.php       # Review keranjang, form pemesan, upload bukti QRIS, validasi server
│   ├── us_antrean.php        # Tiket digital live, stepper status panggangan, auto-polling 15s
│   └── us_footer.php         # Footer informasi gerai, jam buka, dan bottom navigation mobile
│
├── admin/                    # Sub-Sistem Back-End Staf, Kasir, Dapur, & Pemilik
│   ├── ad_index.php          # Router admin tunggal, proteksi role admin, pemetaan submodul
│   ├── ad_header.php         # Bar atas admin, status kasir login, jam digital live
│   ├── ad_sidebar.php        # Navigasi samping (POS, Live Orders, Menu, Topping, Laporan)
│   ├── ad_footer.php         # Skrip penutup, dialog konfirmasi global
│   ├── ad_beranda.php        # Workstation POS Kasir Walk-in & kalkulator kembalian otomatis
│   │
│   ├── modPesanan/           # Modul Kitchen Display & Verifikasi Pembayaran
│   │   ├── dt_pesanan.php    # Papan antrean Live Orders (auto-refresh 20s, filter chip)
│   │   ├── konfirmasiPesanan.php # Halaman verifikasi bukti transfer QRIS (Terima / Tolak)
│   │   └── updateStatus.php  # Handler POST transisi status dapur (Siap Ambil, Selesai, Batal)
│   │
│   ├── modMenu/              # Modul Manajemen Master Menu Kue Pancong
│   │   ├── dt_menu.php       # Tabel master produk, lencana stok, aksi ubah/hapus
│   │   ├── tambahMenu.php    # Form penambahan menu baru & upload foto produk
│   │   ├── ubahMenu.php      # Form pengubahan data menu & penggantian berkas foto
│   │   └── hapusMenu.php     # Handler penghapusan menu dengan pengecekan integritas relasi
│   │
│   ├── modTopping/           # Modul Manajemen Topping & Mesin Rekomendasi
│   │   ├── dt_topping.php    # Tab ganda: Master Topping dan Aturan Rekomendasi Rasa
│   │   ├── tambahTopping.php # Form penambahan topping baru / penambahan aturan asosiasi
│   │   ├── ubahTopping.php   # Form edit topping atau edit kalimat rekomendasi
│   │   └── hapusTopping.php  # Handler penghapusan topping / aturan rekomendasi
│   │
│   └── modLaporan/           # Modul Rekapitulasi Finansial & Audit Transaksi
│       ├── dt_laporan.php    # Dasbor KPI omzet, Top 5 menu terlaris, filter tanggal
│       ├── export_excel.php  # Generator laporan Excel multi-sheet (SpreadsheetML XML murni)
│       └── hapusLaporan.php  # Pembersihan riwayat transaksi (single, periode, all) & berkas bukti
│
├── index.php                 # Router utama aplikasi publik (whitelist LFI-safe)
├── login.php                 # Autentikasi multi-role, pendaftaran pelanggan, & 1-klik akun demo
├── logout.php                # Pemusnah sesi pengguna aman & penghapus cookie
├── database.sql              # Skrip DDL pembentukan 8 tabel relasional & seed data awal
└── dokumentasi.md            # Dokumentasi komprehensif proyek & laporan kemajuan
```

## 5.3 Implementasi Fitur Sisi Pelanggan (*Front-End Web Ordering*)
1. **Pemesanan Tanpa Hambatan (*Frictionless Ordering*):** Pelanggan dapat langsung memilih menu dan menyelesaikan pesanan sebagai tamu (*guest checkout*) tanpa dipaksa mendaftar akun terlebih dahulu.
2. **Kustomisasi Visual Dinamis:** Modal kustomisasi langsung menghitung selisih harga porsi 1/2 loyang secara matematis (`round(harga_dasar / 2)`) dan menampilkan rekomendasi topping berdasarkan aturan asosiasi yang aktif di database.
3. **Persistensi Keranjang Belanja:** Menggunakan `localStorage` sehingga data keranjang tidak hilang saat halaman disegarkan atau saat berpindah antar halaman kategori.
4. **Tiket Live Stepper:** Menampilkan status kemajuan pemanggangan secara transparan dengan mekanisme *polling* otomatis setiap 15 detik, membebaskan pelanggan dari keharusan bertanya berulang kali kepada kasir.

## 5.4 Implementasi Fitur Sisi Staf & Kasir (*Back-End POS & KDS*)
1. **Workstation POS Kasir Terintegrasi:** Dirancang untuk kecepatan transaksi kasir *walk-in*. Mendukung pengetikan cepat menggunakan keyboard shortcut `/` untuk langsung memfokuskan kursor pada kotak pencarian menu. Dilengkapi kalkulator kembalian otomatis dengan tombol pintas pecahan uang pas, Rp20.000, Rp50.000, dan Rp100.000.
2. **Live Order Kitchen Display (KDS):** Membantu staf dapur melihat antrean aktif yang masuk secara *real-time*. Kartu pesanan menampilkan instruksi porsi, tingkat kematangan, dan varian topping secara jelas dan teratur.
3. **Verifikasi Bukti QRIS Cepat:** Layar verifikasi menampilkan bukti transfer berdampingan dengan nominal tagihan riil dan rincian pesanan, memungkinkan kasir memverifikasi pembayaran hanya dalam sekali klik.
4. **Generator Laporan Excel Multi-Sheet:** Menghasilkan dokumen Excel `.xls` berformat SpreadsheetML XML murni yang menyajikan rekapitulasi formal 5 metrik omzet, tabel Top 5 menu terlaris, serta tabel transaksi mentah untuk analisis pembukuan tanpa ketergantungan library eksternal.

## 5.5 Mekanisme Kunci & Pengamanan Transaksional Sistem

### 1. Algoritma Penerbitan Nomor Antrean Thread-Safe
Untuk mencegah masalah nomor antrean kembar (*race condition*) ketika banyak pelanggan menekan tombol checkout secara bersamaan, sistem menggunakan transaksi database InnoDB dengan penguncian baris (`FOR UPDATE`):
```sql
START TRANSACTION;
SELECT MAX(urutan_harian) AS max_urut 
FROM orders 
WHERE DATE(created_at) = CURDATE() 
FOR UPDATE;
-- Sistem menghitung: urut_baru = max_urut + 1;
-- Format: PC-YYYYMMDD-XXX (contoh: PC-20260923-001)
INSERT INTO orders (no_antrean, urutan_harian, ...) VALUES ('PC-20260923-001', 1, ...);
COMMIT;
```

### 2. Validasi Anti-Tampering Harga di Sisi Server
Sistem tidak pernah mempercayai data harga yang dikirimkan oleh peramban pengguna. Berkas `us_checkout.php` dan `ad_beranda.php` hanya menerima `product_id`, `qty`, `porsi`, dan `topping_id`, kemudian mengambil harga resmi langsung dari database:
```php
// Cuplikan logika hitung ulang server-side
$base = (int)$prod['harga_dasar'];
if ($porsi === 'half_loyang') {
    $base = (int)round($base / 2);
}
$subtotal = ($base + $total_topping_db) * $qty;
$grand_total += $subtotal;
```

### 3. Keamanan Unggah Berkas Bukti Transfer Menggunakan PHP Fileinfo
Sistem mengabaikan ekstensi palsu dengan memeriksa struktur biner asli berkas menggunakan ekstensi `finfo`:
```php
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime  = finfo_file($finfo, $_FILES['bukti_bayar']['tmp_name']);
finfo_close($finfo);

$allowed_mimes = ['image/jpeg', 'image/png'];
if (!in_array($mime, $allowed_mimes, true)) {
    $err = 'Berkas bukti pembayaran harus berupa gambar asli berformat JPG atau PNG.';
}
```

### 4. Hashing Password & Otomatisasi Upgrade Bcrypt
Otentikasi mendukung algoritma standar industri Bcrypt dengan penanganan migrasi benih awal:
```php
function cek_password($input, $hashTersimpan) {
    if (!empty($hashTersimpan) && password_verify($input, $hashTersimpan)) {
        return 'bcrypt';
    }
    // Fallback: seed awal plaintext langsung di-upgrade saat login berhasil
    if (hash_equals((string)$hashTersimpan, (string)$input)) {
        return 'plain';
    }
    return false;
}
```

---

# BAB VI: PENGUJIAN SISTEM & EVALUASI (*TESTING*)

## 6.1 Metodologi Pengujian (*Black-Box Testing*)
Pengujian fungsionalitas sistem dilakukan secara menyeluruh menggunakan metode **Black-Box Testing (Equivalence Partitioning & Boundary Value Analysis)**. Fokus pengujian adalah memastikan seluruh masukan (*input*) menghasilkan keluaran (*output*) dan perubahan status data yang sesuai dengan spesifikasi fungsional tanpa memperhatikan struktur internal kode.

## 6.2 Matriks Skenario & Hasil Pengujian Fungsional

| Kode Uji | Skenario Pengujian | Masukan / Aksi Uji | Hasil yang Diharapkan | Hasil Aktual | Kesimpulan |
|---|---|---|---|---|:---:|
| **TC-01** | Registrasi akun baru dengan data valid | Form diisi: nama "Budi Santoso", user "budis", pass "budi123" | Data tersimpan di `users`, role `customer`, otomatis login | Sesuai ekspektasi | **VALID** |
| **TC-02** | Registrasi dengan username duplikat | Mendaftar dengan username "admin" | Sistem menolak dan memunculkan notifikasi username telah digunakan | Muncul pesan error, form ditolak | **VALID** |
| **TC-03** | Login multi-role (Akun Admin) | Masuk dengan kredensial "admin" / "admin123" | Dialihkan langsung ke dasbor back-end `admin/ad_index.php` | Dialihkan ke dasbor admin | **VALID** |
| **TC-04** | Login multi-role (Akun Pelanggan) | Masuk dengan kredensial "customer" / "customer123" | Dialihkan ke beranda pemesanan `index.php` | Dialihkan ke beranda pelanggan | **VALID** |
| **TC-05** | Pencarian katalog menu | Mengetikkan kata "choco" pada kolom pencarian | Kartu menu menyaring hanya varian Choco Crunchy | Hanya varian Choco yang tampil | **VALID** |
| **TC-06** | Filter kategori menu | Mengklik tab "Pancong 1/2 Loyang" | Grid menu hanya menampilkan menu berkategori 1/2 loyang | Tampilan terfilter instan | **VALID** |
| **TC-07** | Kustomisasi porsi & harga | Memilih opsi porsi "1/2 Loyang" pada menu Rp20.000 | Subtotal dasar otomatis menyesuaikan menjadi Rp10.000 | Subtotal terhitung Rp10.000 | **VALID** |
| **TC-08** | Mesin rekomendasi topping | Mencentang topping "Keju Parut" di customizer | Tampil kotak rekomendasi "Choco Chips" beserta alasannya | Rekomendasi rasa tampil | **VALID** |
| **TC-09** | Manajemen keranjang belanja | Menambah 2 porsi, lalu menaikkan qty menjadi 3 di keranjang | Lencana keranjang terisi 3, subtotal bertambah proporsional | Nilai keranjang terupdate | **VALID** |
| **TC-10** | Checkout Online (Metode QRIS valid) | Isi data valid + upload tangkapan layar bukti transfer PNG 800KB | Berkas tersimpan di `gambar/bukti_*`, tiket `PC-...` terbit, status `pending` | Tiket terbit, file tersimpan | **VALID** |
| **TC-11** | Checkout QRIS tanpa upload bukti | Form diisi lengkap, metode QRIS, berkas bukti dikosongkan | Form ditolak dan memunculkan peringatan wajib unggah bukti | Muncul notifikasi wajib bukti | **VALID** |
| **TC-12** | Checkout upload berkas palsu (.php/.exe diubah ke .jpg) | Mengunggah file script berbahaya yang dinamai `shell.jpg` | Pemeriksaan `finfo` mendeteksi MIME non-gambar dan menolak simpan | Berkas ditolak oleh server | **VALID** |
| **TC-13** | Checkout dengan manipulasi harga via Inspect Element | Mengubah atribut harga di DOM browser sebelum submit | Server mengabaikan harga client dan menghitung ulang dari DB | Total bayar tetap sesuai DB | **VALID** |
| **TC-14** | Pelacakan status tiket live | Membuka `index.php?page=antrean&no=PC-...` | Tiket menampilkan data pemesan, item, dan stepper status aktif | Tiket tampil dengan stepper | **VALID** |
| **TC-15** | Auto-refresh halaman antrean | Membiarkan halaman antrean terbuka selama 15 detik | Halaman melakukan polling otomatis tanpa memunculkan dialog form resubmit | Halaman refresh mulus | **VALID** |
| **TC-16** | POS Kasir: Transaksi Tunai Walk-In | Input pesanan walk-in, pilih Tunai, isi nominal uang Rp50.000 | Tiket terbit dengan status bayar `verified` dan status `dimasak` | Pesanan langsung masuk dapur | **VALID** |
| **TC-17** | POS Kasir: Kalkulator kembalian | Total belanja Rp36.000, klik tombol instan "50.000" | Kalkulator menampilkan uang kembalian Rp14.000 secara otomatis | Kembalian tampil akurat | **VALID** |
| **TC-18** | Verifikasi Pembayaran: Terima Kasir | Klik "Terima Pembayaran" pada pesanan QRIS pending | Status bayar menjadi `verified`, status pesanan menjadi `dimasak` | Data terupdate instan | **VALID** |
| **TC-19** | Verifikasi Pembayaran: Tolak Kasir | Klik "Tolak Pembayaran" pada pesanan transfer palsu | Status bayar menjadi `rejected`, status pesanan menjadi `batal` | Pesanan dibatalkan sistem | **VALID** |
| **TC-20** | KDS: Transisi Siap Ambil & Selesai | Klik "Siap Ambil", lalu klik "Selesai" pada Live Orders | Status pesanan maju bertahap hingga `selesai` dan masuk omzet | Siklus status dapur tuntas | **VALID** |
| **TC-21** | Tambah Master Menu baru | Form menu diisi lengkap + upload gambar menu WEBP 500KB | Data tersimpan di `products`, menu baru muncul di katalog publik | Menu baru langsung aktif | **VALID** |
| **TC-22** | Hapus Menu yang pernah dipesan | Klik Hapus pada menu yang sudah memiliki relasi di `order_items` | Sistem menolak penghapusan dan menyarankan non-aktifkan menu | Ditolak, integritas terjaga | **VALID** |
| **TC-23** | Filter Laporan Omzet Periode | Memilih rentang tanggal 2026-09-01 s/d 2026-09-23 | Menampilkan akumulasi omzet bersih, porsi QRIS vs Tunai, dan Top 5 | Angka rekapitulasi akurat | **VALID** |
| **TC-24** | Ekspor Laporan ke Excel | Klik tombol "Export Excel" pada modul laporan | Berkas `.xls` terunduh, memuat 2 sheet (Rekap & Data) rapi di Excel | File unduh & terbaca rapi | **VALID** |
| **TC-25** | Pembersihan Riwayat Transaksi | Memilih Hapus Transaksi Tertentu pada pesanan selesai | Baris data di `orders` terhapus dan file fisik di `gambar/bukti_*` terhapus | Data DB & berkas terhapus | **VALID** |

## 6.3 Pengujian Non-Fungsional (Keamanan, Konkurensi, Integritas Data)
1. **Uji Keamanan Injeksi SQL (*SQL Injection*):** Memasukkan muatan uji `' OR '1'='1` pada kotak pencarian menu, formulir login, dan parameter URL (`?no=`). Seluruh pengujian menghasilkan penanganan aman karena implementasi 100% *Prepared Statements* dengan binding parameter tipe data eksplisit.
2. **Uji Keamanan Skrip Lintas Situs (*XSS*):** Memasukkan muatan `<script>alert('XSS')</script>` pada nama pemesan saat checkout. Karakter berbahaya berhasil dinetralkan menjadi entitas HTML aman (`&lt;script&gt;...`) oleh fungsi helper terpusat `e()`.
3. **Uji Proteksi *Local File Inclusion* (LFI):** Memanipulasi parameter rute `index.php?page=../../etc/passwd` atau `ad_index.php?mod=../../config/koneksi`. Sistem secara otomatis memblokir akses dan mengembalikan rute ke halaman beranda default berkat sistem *whitelist array* ketat.
4. **Uji Konkurensi Penerbitan Nomor Antrean:** Melakukan simulasi submit pesanan secara simultan. Tidak ditemukan satu pun nomor tiket duplikat karena klausul `FOR UPDATE` berhasil mengantrekan kalkulasi `urutan_harian` secara atomik di dalam transaksi basis data InnoDB.

---

# BAB VII: CAPAIAN KEMAJUAN & KENDALA PENGEMBANGAN

## 7.1 Status Realisasi Capaian Proyek (Tabel Milestone & Persentase)

Berdasarkan sasaran kerja yang ditetapkan pada awal perancangan, capaian pengerjaan sistem hingga fase **Milestone Demo 1** telah berhasil diselesaikan secara penuh dengan rincian persentase sebagai berikut:

| No | Modul / Komponen Sistem | Fitur-Fitur Utama yang Selesai Dikerjakan | Bobot | Persentase Selesai | Status |
|---|---|---|:---:|:---:|:---:|
| 1 | **Modul Basis Data & Arsitektur** | DDL 8 tabel relasional, relasi kunci asing, indexing, transaksi ACID | 15% | 100% | **Selesai Penuh** |
| 2 | **Modul Autentikasi & Akun** | Login multi-role, registrasi customer, 1-klik akun demo, upgrade hash | 10% | 100% | **Selesai Penuh** |
| 3 | **Modul Katalog & Customizer** | Grid menu responsif, filter kategori, modal kustomisasi, rekomendasi rasa | 15% | 100% | **Selesai Penuh** |
| 4 | **Modul Keranjang & Checkout** | Keranjang `localStorage`, form checkout, validasi MIME upload, hitung server | 15% | 100% | **Selesai Penuh** |
| 5 | **Modul Antrean Digital Live** | Tiket perforasi digital, stepper status 4 tahap, auto-refresh polling 15s | 10% | 100% | **Selesai Penuh** |
| 6 | **Modul Workstation POS Kasir** | POS walk-in terintegrasi, shortcut `/`, slip kasir, kalkulator kembalian | 15% | 100% | **Selesai Penuh** |
| 7 | **Modul Kitchen Display (KDS)** | Papan Live Orders, verifikasi bukti bayar QRIS, transisi status masak | 10% | 100% | **Selesai Penuh** |
| 8 | **Modul Master Data (CRUD)** | Manajemen produk kue pancong, upload foto menu, manajemen topping & aturan | 5% | 100% | **Selesai Penuh** |
| 9 | **Modul Laporan & Generator Excel**| Filter rentang tanggal, agregasi omzet bersih, Top 5 menu, ekspor XML 2-sheet | 5% | 100% | **Selesai Penuh** |
| **TOTAL** | **KESELURUHAN SISTEM** | **SELURUH FITUR UTAMA PROYEK (MILESTONE DEMO 1)** | **100%** | **100%** | **SELESAI** |

## 7.2 Kendala Teknis yang Dihadapi & Solusi Penerapannya

Selama tahapan analisis hingga implementasi kode, ditemukan beberapa kendala teknis yang berhasil diatasi melalui solusi arsitektural:

1. **Kendala 1: Potensi Nomor Antrean Ganda pada Jam Sibuk**
   - *Masalah:* Ketika beberapa pelanggan melakukan checkout secara bersamaan di detik yang sama, query pencarian nomor urut terakhir berpotensi menghasilkan nomor antrean yang sama (*duplicate key error*).
   - *Solusi:* Membungkus proses checkout ke dalam transaksi database InnoDB dan menerapkan klausa penguncian baris `SELECT MAX(urutan_harian) ... FOR UPDATE`. Mekanisme ini memaksa transaksi kedua mengantre hingga transaksi pertama selesai melakukan *commit*.
2. **Kendala 2: Kerentanan Manipulasi Harga dari Sisi Klien (*Client-Side Tampering*)**
   - *Masalah:* Karena pesanan disusun menggunakan JavaScript di browser (`localStorage`), pengguna yang memahami Developer Tools peramban dapat dengan mudah mengubah nilai properti harga pada JSON keranjang belanja.
   - *Solusi:* Menerapkan prinsip *Zero Trust Client Data*. Server PHP hanya menerima ID produk, ID topping, dan jumlah kuantitas. Seluruh kalkulasi harga dasar, pembagian porsi 1/2 loyang, dan penambahan harga topping dihitung ulang secara mutlak mengambil data master di database.
3. **Kendala 3: Kerumitan Dependensi Pustaka Pihak Ketiga untuk Ekspor Excel**
   - *Masalah:* Pustaka generator Excel seperti *PhpSpreadsheet* membutuhkan instalasi Composer dengan ukuran direktori *vendor* yang besar (>30 MB) serta konsumsi memori server yang tinggi.
   - *Solusi:* Membangun generator dokumen Excel berbasis format standar terbuka **SpreadsheetML XML 2003**. Generator ini ditulis langsung menggunakan fungsi native PHP (`echo` XML terstruktur) yang sangat ringan (<10 KB), tidak membebani server, dan menghasilkan file `.xls` yang dapat dibuka sempurna di semua aplikasi spreadsheet modern dengan 2 lembar kerja terpisah.
4. **Kendala 4: Pengujian Berulang yang Mengotori Penyimpanan Media Server**
   - *Masalah:* Saat pengujian intensif dilakukan, direktori `gambar/` dipenuhi puluhan berkas tangkapan layar bukti transfer palsu.
   - *Solusi:* Mengintegrasikan fungsi penghapusan berkas fisik (`unlink`) ke dalam modul penghapusan laporan dan pembatalan pesanan. Setiap kali sebuah transaksi dihapus dari database, berkas gambar fisiknya otomatis ikut terhapus dari penyimpanan server.

## 7.3 Evaluasi Kemajuan Proyek
Secara menyeluruh, fungsionalitas sistem telah memenuhi seluruh target operasional yang direncanakan. Sistem mampu menjembatani pemesanan mandiri oleh pelanggan dari ponsel pintar dan pemesanan langsung di kasir melalui workstation POS yang responsif, terhubung secara mulus dengan sistem display dapur tanpa ada pesanan yang terlewat.

---

# BAB VIII: RENCANA TAHAP SELANJUTNYA & KESIMPULAN

## 8.1 Rencana Pengembangan Lanjutan (*Next Steps*)
Untuk menyempurnakan implementasi sistem setelah fase Demo 1 ini, rencana kerja lanjutan yang akan dilaksanakan meliputi:
1. **Pelaksanaan Pengujian Penerimaan Pengguna (*User Acceptance Testing* / UAT):** Melakukan uji coba langsung di gerai fisik Pancong Lumer Selindung Baru bersama pemilik gerai, kasir, dan pelanggan nyata pada situasi jam operasional sesungguhnya.
2. **Integrasi WhatsApp Gateway Otomatis (Fonnte / Wablas API):** Mengirimkan notifikasi pesan WhatsApp otomatis ke nomor pelanggan ketika pesanan telah berstatus "Siap Diambil", sehingga pelanggan yang sedang menunggu di sekitar area gerai dapat langsung menuju counter pengambilan.
3. **Dukungan Pencetakan Struk Thermal Bluetooth (ESC/POS):** Menambahkan fitur cetak struk tiket pesanan dan bukti pembayaran kasir langsung ke printer thermal 58mm/80mm via Web Bluetooth API atau koneksi USB lokal.
4. **Otomatisasi Pembayaran Digital (*Payment Gateway*):** Menjajaki integrasi QRIS dinamis otomatis (seperti Midtrans atau Xendit) yang dapat memverifikasi status pembayaran secara instan via *webhook callback* tanpa memerlukan unggah bukti transfer manual.

## 8.2 Kesimpulan Laporan Kemajuan
Berdasarkan hasil analisis, perancangan, implementasi, dan pengujian yang telah dipaparkan pada laporan kemajuan ini, dapat ditarik beberapa kesimpulan utama:
1. Sistem Informasi Pemesanan & POS Pancong Lumer Selindung Baru berhasil dibangun sebagai solusi digital terintegrasi yang mencakup antarmuka pemesanan mandiri pelanggan, workstation POS kasir cerdas, papan kendali dapur (KDS), dan modul rekapitulasi finansial.
2. Seluruh 18 kebutuhan fungsional (FR-01 s/d FR-18) telah selesai diimplementasikan 100% dan lolos pengujian *Black-Box Testing* pada 25 skenario uji tanpa kegagalan fungsi.
3. Fitur kustomisasi porsi dan tingkat kematangan yang didukung mesin rekomendasi topping (*cross-selling*) berhasil diwujudkan secara interaktif untuk memperkaya pengalaman pelanggan dan meminimalisir salah buat pesanan di dapur.
4. Mekanisme transaksional dengan penguncian baris (`FOR UPDATE`), verifikasi keamanan MIME `finfo`, sanitasi XSS terpusat, dan otentikasi Bcrypt terbukti menjamin integritas dan keamanan data operasional gerai.
5. Sistem telah siap sepenuhnya untuk memasuki tahapan uji coba lapangan (*pilot deployment*) dan demonstrasi laporan kemajuan proyek.

---

# LAMPIRAN TEKNIS

## 9.1 Panduan Instalasi & Operasional Lokal (*Deployment Guide*)
Untuk menjalankan sistem di lingkungan server pengembangan lokal (Laragon / XAMPP):
1. **Penyalinan Berkas:** Salin folder proyek ke dalam direktori root server web:
   - Laragon: `C:\laragon\www\Pancong-Lumer\`
   - XAMPP: `C:\xampp\htdocs\Pancong-Lumer\`
2. **Konfigurasi Database:**
   - Buka phpMyAdmin (`http://localhost/phpmyadmin`) atau terminal MySQL.
   - Buat database baru bernama `db_pancong`:
     ```sql
     CREATE DATABASE db_pancong CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
     ```
   - Import file `database.sql` yang berada di root direktori proyek ke dalam database `db_pancong`.
3. **Pemeriksaan Koneksi:**
   - Buka berkas `config/koneksi.php`. Pastikan konfigurasi host (`localhost`), username (`root`), password (`""`), dan nama database (`db_pancong`) telah sesuai dengan konfigurasi MySQL lokal Anda.
4. **Akses Aplikasi Melalui Peramban:**
   - Antarmuka Publik Pelanggan: `http://localhost/Pancong-Lumer/`
   - Halaman Login Staf / Admin: `http://localhost/Pancong-Lumer/login.php`
   - Dasbor Khusus Admin / Kasir: `http://localhost/Pancong-Lumer/admin/ad_index.php`

## 9.2 Kredensial Akun Pengujian & Demonstrasi
Sistem menyediakan dua akun bawaan yang siap digunakan untuk pengujian dan sidang demonstrasi:

| Peran Akun | Username | Password Default | Keterangan & Hak Akses |
|---|---|---|---|
| **Administrator / Kasir** | `admin` | `admin123` | Akses penuh ke POS Kasir Walk-in, Live Orders KDS, Verifikasi Pembayaran, Manajemen Menu/Topping/Rekomendasi, Laporan Keuangan, dan Ekspor Excel. |
| **Pelanggan Terdaftar** | `customer` | `customer123` | Akses ke katalog pemesanan, kustomisasi menu, pelacakan tiket antrean, dan riwayat 5 transaksi terakhir. |
| **Tamu (*Guest*)** | *(Tanpa Login)* | *(Tanpa Password)* | Langsung mengakses `index.php`, memilih menu, melakukan checkout mandiri, dan mendapatkan nomor tiket antrean digital. |

> *Catatan Pengujian Sidang:* Pada halaman `login.php` telah disediakan tombol pintas **"Login Cepat: Demo Kasir"** dan **"Login Cepat: Demo Pelanggan"** yang memungkinkan penguji/dosen masuk ke akun demo hanya dengan satu kali klik tanpa perlu mengetikkan kredensial.

---
*Dokumen ini disusun secara otomatis dan diverifikasi langsung dari kode sumber sistem aktual (`Pancong-Lumer`) per September 2026.*
