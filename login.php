<?php
// =====================================================
// login.php — Autentikasi multi-role (customer & admin)
// + Demo 1-klik untuk sidang akademik
// Desain: adaptasi Stitch "Halaman Login & Masuk Akun"
// (top-bar + hero foto + kartu form + role-switch)
// =====================================================
session_start();
require_once __DIR__ . '/config/koneksi.php';

// Jika sudah login, arahkan sesuai role
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin/ad_index.php');
    } else {
        header('Location: index.php');
    }
    exit;
}

$error = '';
$mode  = isset($_GET['mode']) && $_GET['mode'] === 'daftar' ? 'daftar' : 'masuk';

// --- Helper: ambil user by username ---
function ambil_user($koneksi, $username) {
    $stmt = mysqli_prepare($koneksi, "SELECT id, username, password, nama_lengkap, email, role FROM users WHERE username = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 's', $username);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    return $row;
}

// --- Helper: verifikasi password (bcrypt + fallback plaintext migrasi) ---
function cek_password($input, $hashTersimpan) {
    if (!empty($hashTersimpan) && password_verify($input, $hashTersimpan)) {
        return 'bcrypt';
    }
    // Fallback: seed awal masih plaintext (auto-upgrade ke bcrypt saat sukses)
    if (hash_equals((string)$hashTersimpan, (string)$input)) {
        return 'plain';
    }
    return false;
}

function set_login($row) {
    $_SESSION['user_id']      = $row['id'];
    $_SESSION['username']     = $row['username'];
    $_SESSION['nama_lengkap'] = $row['nama_lengkap'];
    $_SESSION['role']         = $row['role'];
}

// --- Demo 1-klik: ?demo=admin | ?demo=customer ---
if (isset($_GET['demo'])) {
    $target = $_GET['demo'] === 'admin' ? 'admin' : 'customer';
    $username = $target === 'admin' ? 'admin' : 'customer';
    $row = ambil_user($koneksi, $username);
    // Jika akun demo belum ada (DB belum di-seed), buat otomatis
    if (!$row) {
        $passHash = password_hash($target === 'admin' ? 'admin123' : 'customer123', PASSWORD_DEFAULT);
        $nama = $target === 'admin' ? 'Kasir Gerai Selindung Baru' : 'Pelanggan Demo';
        $email = $target === 'admin' ? 'kasir@panconglumer.test' : 'pelanggan@panconglumer.test';
        $stmt = mysqli_prepare($koneksi, "INSERT INTO users (username, password, nama_lengkap, email, role) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'sssss', $username, $passHash, $nama, $email, $target);
        if (mysqli_stmt_execute($stmt)) {
            $row = ambil_user($koneksi, $username);
        }
        mysqli_stmt_close($stmt);
    }
    if ($row) {
        set_login($row);
        header('Location: ' . ($row['role'] === 'admin' ? 'admin/ad_index.php' : 'index.php'));
        exit;
    } else {
        $error = 'Akun demo tidak ditemukan. Import database.sql terlebih dahulu.';
    }
}

// --- Proses login manual ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'masuk') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } else {
        $row = ambil_user($koneksi, $username);
        $cek = $row ? cek_password($password, $row['password']) : false;
        if ($row && $cek) {
            // Auto-upgrade hash plaintext lama ke bcrypt
            if ($cek === 'plain') {
                $baru = password_hash($password, PASSWORD_DEFAULT);
                $stmt = mysqli_prepare($koneksi, "UPDATE users SET password = ? WHERE id = ?");
                mysqli_stmt_bind_param($stmt, 'si', $baru, $row['id']);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
            set_login($row);
            header('Location: ' . ($row['role'] === 'admin' ? 'admin/ad_index.php' : 'index.php'));
            exit;
        } else {
            $error = 'Username atau password salah.';
        }
    }
}

// --- Proses daftar customer baru ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'daftar') {
    $username = trim($_POST['username'] ?? '');
    $nama     = trim($_POST['nama_lengkap'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $p1       = $_POST['password'] ?? '';
    $p2       = $_POST['password2'] ?? '';
    if (strlen($username) < 4 || strlen($nama) < 3 || strlen($p1) < 6) {
        $error = 'Username min. 4 huruf, nama min. 3 huruf, password min. 6 karakter.';
    } elseif ($p1 !== $p2) {
        $error = 'Konfirmasi password tidak sama.';
    } elseif (ambil_user($koneksi, $username)) {
        $error = 'Username sudah dipakai. Pilih yang lain.';
    } else {
        $hash = password_hash($p1, PASSWORD_DEFAULT);
        $role = 'customer';
        $stmt = mysqli_prepare($koneksi, "INSERT INTO users (username, password, nama_lengkap, email, role) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'sssss', $username, $hash, $nama, $email, $role);
        if (mysqli_stmt_execute($stmt)) {
            $row = ambil_user($koneksi, $username);
            mysqli_stmt_close($stmt);
            set_login($row);
            header('Location: index.php');
            exit;
        } else {
            $error = 'Pendaftaran gagal. Coba lagi.';
            mysqli_stmt_close($stmt);
        }
    }
    $mode = 'daftar';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Masuk Akun — Pancong Lumer x Chicken Roll</title>
<link rel="stylesheet" href="css/style.css?v=<?php echo filemtime(__DIR__ . '/css/style.css'); ?>">
<script src="js/validasi.js?v=<?php echo filemtime(__DIR__ . '/js/validasi.js'); ?>" defer></script>
</head>
<body>
<div class="login-wrap">
  <header class="login-top">
    <a class="back-btn" href="index.php" aria-label="Kembali ke katalog">&#8592;</a>
    <span class="gerai-pill">Gerai Resmi Selindung</span>
    <span class="login-hours">Buka Setiap Hari: 10.00&ndash;22.00 WIB</span>
  </header>

  <main class="login-main">
    <!-- Kiri: hero foto + benefit (ala Stitch, tanpa ikon font) -->
    <div class="auth-visual">
      <div class="hero-photo-card">
        <img src="gambar/login-hero.jpg" alt="Pancong lumer cokelat keju hangat khas gerai Selindung Baru" loading="eager" onerror="this.closest('.hero-photo-card').classList.add('no-photo');this.remove()">
        <div class="hero-shade"></div>
        <div class="hero-badges">
          <span class="hero-badge">Paling Laris #1</span>
          <span class="hero-badge hero-badge-amber">Lumer Meleleh</span>
        </div>
        <div class="hero-cap">
          <b>Pancong Choco Keju Lumer</b>
          <p>Lumeran cokelat hangat berpadu renyahnya choco &amp; keju melimpah.</p>
        </div>
      </div>
      <div class="benefit-grid">
        <div class="benefit">
          <span class="benefit-mark bm-sec">Rp</span>
          <div><b>Diskon Rp5.000</b><br><small>Pesanan pertama</small></div>
        </div>
        <div class="benefit">
          <span class="benefit-mark bm-amber">Live</span>
          <div><b>Live Antrean</b><br><small>Pantau dari WA</small></div>
        </div>
        <div class="benefit">
          <span class="benefit-mark bm-pink">Poin</span>
          <div><b>Poin Mania</b><br><small>Tukar gratisan</small></div>
        </div>
      </div>
    </div>

    <!-- Kanan: kartu form autentikasi -->
    <div class="auth-card">
      <div class="auth-brand">
        <span class="auth-mark">PL</span>
        <h1>Pancong Lumer x Chicken Roll</h1>
      </div>
      <h2 class="auth-title"><?php echo $mode === 'daftar' ? 'Buat Akun Pelanggan' : 'Selamat Datang, Kuliner Lovers!'; ?></h2>
      <p class="auth-sub">Gerai Selindung Baru &bull; Manisnya Lumer, Gurihnya Renyah</p>

      <?php if ($error): ?>
        <div class="alert alert-err" style="margin-top:14px"><?php echo e($error); ?></div>
      <?php endif; ?>

      <?php if ($mode === 'daftar'): ?>
      <form method="post" onsubmit="return validasiAdmin(this)" style="margin-top:18px">
        <input type="hidden" name="aksi" value="daftar">
        <div class="form-grid">
          <div class="full"><label class="flabel">Nama Lengkap *</label><input class="fin" name="nama_lengkap" required placeholder="cth: Budi Santoso"></div>
          <div><label class="flabel">Username *</label><input class="fin" name="username" required minlength="4" placeholder="min. 4 huruf"></div>
          <div><label class="flabel">Email</label><input class="fin" type="email" name="email" placeholder="nama@email.com"></div>
          <div><label class="flabel">Password *</label><input class="fin" type="password" name="password" required minlength="6"></div>
          <div><label class="flabel">Ulangi Password *</label><input class="fin" type="password" name="password2" required></div>
        </div>
        <button class="btn btn-primary btn-block" style="margin-top:16px" type="submit">Daftar &amp; Masuk</button>
        <div class="auth-foot"><p>Sudah punya akun? <a href="login.php"><b>Masuk</b></a></p></div>
      </form>
      <?php else: ?>
      <div class="role-switch" role="tablist" aria-label="Pilih peran masuk">
        <button type="button" id="roleCustomerBtn" class="on" onclick="switchRole('customer')">Pelanggan</button>
        <button type="button" id="roleStaffBtn" onclick="switchRole('staff')">Kasir / Dapur (Staf)</button>
      </div>

      <div id="panelCustomer">
        <form method="post">
          <input type="hidden" name="aksi" value="masuk">
          <div class="field">
            <label class="flabel" for="login-user">Email atau Username</label>
            <div class="input-wrap"><input id="login-user" name="username" required placeholder="contoh: @email.com / username" autocomplete="username"></div>
          </div>
          <div class="field">
            <label class="flabel" for="login-pass">Kata Sandi</label>
            <div class="input-wrap">
              <input id="login-pass" type="password" name="password" required placeholder="Masukkan kata sandi" autocomplete="current-password">
              <button type="button" class="show-btn" onclick="togglePass('login-pass',this)" aria-label="Tampilkan kata sandi" aria-pressed="false"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg></button>
            </div>
          </div>
          <button class="btn btn-primary btn-block" style="margin-top:16px" type="submit">Masuk Sekarang</button>
        </form>
        <div class="auth-foot">
          <p>Belum punya akun? <a href="login.php?mode=daftar"><b>Daftar Sekarang</b></a></p>
          <div><a class="guest-link" href="index.php">Lanjut Pesan sebagai Tamu (Guest Checkout)</a></div>
        </div>
      </div>

      <div id="panelStaff" hidden>
        <form method="post">
          <input type="hidden" name="aksi" value="masuk">
          <div class="field">
            <label class="flabel" for="staff-user">ID Karyawan / Username</label>
            <div class="input-wrap"><input id="staff-user" name="username" required placeholder="cth: admin / kasir-01" autocomplete="username"></div>
          </div>
          <div class="field">
            <label class="flabel" for="staff-pass">Password</label>
            <div class="input-wrap">
              <input id="staff-pass" type="password" name="password" required placeholder="Masukkan password" autocomplete="current-password">
              <button type="button" class="show-btn" onclick="togglePass('staff-pass',this)" aria-label="Tampilkan kata sandi" aria-pressed="false"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg></button>
            </div>
          </div>
          <p class="staff-hint">Akses khusus untuk membuka terminal POS kasir dan layar kitchen.</p>
          <button class="btn btn-primary btn-block" style="margin-top:12px" type="submit">Buka Dashboard Terminal Gerai</button>
        </form>
        <div class="auth-foot">
          <div><button type="button" class="guest-link" style="border:0;background:none;cursor:pointer;font-family:inherit" onclick="switchRole('customer')">Kembali ke Mode Pemesanan Pelanggan</button></div>
        </div>
      </div>
      <?php endif; ?>

      <div class="login-demo" id="demo">
        <span class="eyebrow">Demo sidang 1-klik</span>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
          <a class="btn btn-amber btn-sm" href="login.php?demo=customer">Login sebagai Customer</a>
          <a class="btn btn-ghost btn-sm" href="login.php?demo=admin">Login sebagai Admin</a>
        </div>
        <p class="login-demo-cred">customer / customer123 &nbsp;&bull;&nbsp; admin / admin123</p>
      </div>
      <p class="login-back"><a href="index.php">&larr; Kembali ke katalog tanpa login</a></p>
    </div>
  </main>

  <footer class="login-foot">
    <div class="login-foot-in">
      <span>Enkripsi privasi pelanggan terjamin</span>
      <span class="sep">&bull;</span>
      <span>Selindung Baru, Depan SD 23</span>
      <span class="sep">&bull;</span>
      <span>Buka Setiap Hari: 10.00&ndash;22.00 WIB</span>
    </div>
  </footer>
</div>

<script>
function switchRole(role) {
  var c = document.getElementById('roleCustomerBtn'),
      s = document.getElementById('roleStaffBtn'),
      pc = document.getElementById('panelCustomer'),
      ps = document.getElementById('panelStaff');
  if (!c || !s || !pc || !ps) return;
  var isC = role !== 'staff';
  c.classList.toggle('on', isC);
  s.classList.toggle('on', !isC);
  pc.hidden = !isC;
  ps.hidden = isC;
}
var EYE_ON = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>';
var EYE_OFF = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/><path d="M3 3l18 18"/></svg>';
function togglePass(id, btn) {
  var p = document.getElementById(id);
  if (!p) return;
  var show = p.type === 'password';
  p.type = show ? 'text' : 'password';
  if (btn) {
    btn.innerHTML = show ? EYE_OFF : EYE_ON;
    btn.setAttribute('aria-label', show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
    btn.setAttribute('aria-pressed', show ? 'true' : 'false');
    btn.classList.toggle('is-on', show);
  }
}
</script>
</body>
</html>
