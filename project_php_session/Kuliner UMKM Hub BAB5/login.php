<?php
session_start();
// LOGOUT: hapus session, lalu kembali ke halaman login
if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_destroy();
    header('Location: login.php');
    exit;
}
// Form dikirim: nama pengguna disimpan di SESSION, email di COOKIE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['nama']  = trim($_POST['nama'] ?? '');
    $_SESSION['email'] = trim($_POST['email'] ?? '');
    if (isset($_POST['ingat'])) {
        setcookie('ingat_email', $_SESSION['email'], time() + 60 * 60 * 24 * 30, '/');
    } else {
        setcookie('ingat_email', '', time() - 3600, '/');
    }
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Masuk — Kuliner UMKM Hub</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="auth-shell">
  <div class="auth-side">
    <a href="index.php" class="brand" style="color:#F7EFDD;font-size:22px">Kuliner<span style="color:var(--turmeric)">UMKM</span></a>
    <div>
      <h2>Lapak usahamu <span class="grad-text">menunggu pesanan</span> berikutnya.</h2>
      <p>Masuk untuk memantau pesanan, memperbarui menu, dan melihat performa usaha kulinermu hari ini.</p>
    </div>
  </div>
  <div class="auth-main">
    <div class="auth-card">
      <a href="index.php" class="brand" style="font-size:20px;color:var(--chili)">Kuliner<span style="color:var(--leaf)">UMKM</span></a>
      <h1>Selamat datang kembali</h1>
      <div class="sub">Masuk ke akun usaha kulinermu.</div>

      <form id="formLogin" method="post" action="login.php" novalidate>
        <input type="hidden" name="nama" id="namaPengguna">
        <div class="field">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" placeholder="nama@usahakamu.com" required>
        </div>
        <div class="field">
          <label for="pass">Kata sandi</label>
          <input type="password" id="pass" placeholder="••••••••" required>
        </div>
        <label style="display:flex;align-items:center;gap:8px;font-size:13px;margin:0 0 16px;cursor:pointer">
          <input type="checkbox" id="ingat" name="ingat" style="display:inline-block;width:auto;margin:0"> Ingat email saya
        </label>
        <button type="submit" class="btn btn-solid btn-block">Masuk</button>
      </form>

      <div class="auth-foot">Belum punya lapak? <a href="register.php">Daftar di sini</a></div>
    </div>
  </div>
</div>

<script src="app.js"></script>
<script src="auth.js"></script>
</body>
</html>
