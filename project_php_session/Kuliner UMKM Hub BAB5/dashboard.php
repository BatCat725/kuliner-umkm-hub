<?php
session_start();
// Halaman ini hanya untuk pengguna yang sudah punya SESSION
if (empty($_SESSION['nama'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard — Kuliner UMKM Hub</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<style>button.btn{font-family:inherit;cursor:pointer}</style>
</head>
<body>

<div class="dash">
  <aside class="side">
    <a href="index.php" class="brand" style="color:var(--chili)">Kuliner<span style="color:var(--leaf)">UMKM</span></a>
    <div style="padding:0 12px 18px;font-size:12px;color:var(--ink-soft)">Masuk sebagai<br><b style="color:var(--leaf)"><?= htmlspecialchars($_SESSION['nama']) ?></b></div>
    <nav>
      <a href="dashboard.php" class="active">Ringkasan</a>
      <a href="pesanan.php">Pesanan</a>
      <a href="produk.php">Produk Saya</a>
      <a href="#">Pelanggan</a>
      <a href="#">Pengaturan Lapak</a>
      <a href="login.php?logout=1" style="margin-top:20px;color:var(--chili)">Keluar</a>
    </nav>
  </aside>

  <main class="main">
    <div class="dash-head">
      <div>
        <h1 id="salam">Halo, Dapur Bu Sari</h1>
        <p>Berikut ringkasan lapakmu hari ini, <span id="tanggal">Rabu 23 September</span>.</p>
      </div>
      <div style="display:flex;gap:10px;flex-wrap:wrap">
        <button type="button" class="btn btn-ghost" id="btnNama">Ubah nama lapak</button>
        <button type="button" class="btn btn-ghost" id="btnMuat">Muat ulang data</button>
      </div>
    </div>

    <div class="kpis">
      <div class="kpi"><span>Pesanan hari ini</span><b id="kPesanan">23</b><div class="delta" id="dPesanan">+4 dari kemarin</div></div>
      <div class="kpi"><span>Pendapatan hari ini</span><b id="kUang">Rp1,2 jt</b><div class="delta" id="dUang">+12%</div></div>
      <div class="kpi"><span>Produk aktif</span><b id="kProduk">16</b><div class="delta" id="dProduk">2 stok menipis</div></div>
      <div class="kpi"><span>Rating usaha</span><b id="kRating">4,8</b><div class="delta" id="dRating">dari 312 ulasan</div></div>
    </div>
    <div class="table-card">
      <h3>Pesanan terbaru</h3>
      <table>
        <thead>
          <tr><th>Pemesan</th><th>Produk</th><th>Total</th><th>Status</th></tr>
        </thead>
        <tbody id="tbodyTerbaru">
          <tr><td>Rina W.</td><td>Nasi Kotak Ayam (10)</td><td>Rp150.000</td><td><span class="status pending">Diproses</span></td></tr>
          <tr><td>Kantor Amanah</td><td>Nasi Kotak Campur (25)</td><td>Rp375.000</td><td><span class="status pending">Diproses</span></td></tr>
          <tr><td>Budi S.</td><td>Rendang Sekotak</td><td>Rp45.000</td><td><span class="status done">Selesai</span></td></tr>
          <tr><td>Sinta P.</td><td>Nasi Kotak Vegetarian (5)</td><td>Rp75.000</td><td><span class="status done">Selesai</span></td></tr>
        </tbody>
      </table>
    </div>
  </main>
</div>

<script src="app.js"></script>
<script src="dashboard.js"></script>
</body>
</html>
