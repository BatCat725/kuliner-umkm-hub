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
<title>Produk Saya — Kuliner UMKM Hub</title>
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
      <a href="dashboard.php">Ringkasan</a>
      <a href="pesanan.php">Pesanan</a>
      <a href="produk.php" class="active">Produk Saya</a>
      <a href="#">Pelanggan</a>
      <a href="#">Pengaturan Lapak</a>
      <a href="login.php?logout=1" class="logout">Keluar</a>
    </nav>
  </aside>

  <main class="main">
    <div class="dash-head">
      <div>
        <h1>Produk Saya</h1>
        <p>Kelola menu yang dijual di lapakmu: harga, kategori, dan stok.</p>
      </div>
      <a href="#tambah" class="btn btn-solid">+ Tambah Produk</a>
    </div>

    <div class="kpis">
      <div class="kpi"><span>Total produk</span><b id="kTotal">8</b></div>
      <div class="kpi"><span>Stok menipis</span><b id="kMenipis">2</b></div>
      <div class="kpi"><span>Stok habis</span><b id="kHabis">1</b></div>
      <div class="kpi"><span>Nilai stok</span><b id="kNilai">Rp2.909.000</b></div>
    </div>

    <form class="toolbar" id="formFilter" method="get" action="produk.php">
      <input type="search" id="cariProduk" name="q" class="input grow" placeholder="Cari nama produk" aria-label="Cari produk">
      <select id="filterKategori" name="kategori" class="input" aria-label="Filter kategori">
        <option value="">Semua kategori</option>
          <option>Nasi &amp; Lauk</option>
          <option>Katering</option>
          <option>Minuman</option>
          <option>Kue &amp; Kudapan</option>
          <option>Frozen Food</option>
          <option>Bumbu &amp; Sambal</option>
          <option>Oleh-oleh</option>
      </select>
    </form>

    <div class="table-card">
      <h3 id="judulProduk">Daftar produk (8)</h3>
      <div class="table-scroll">
        <table>
          <thead>
            <tr><th>Produk</th><th>Kategori</th><th>Harga</th><th>Stok</th><th>Status</th><th>Aksi</th></tr>
          </thead>
          <tbody id="tbodyProduk">
<!-- READ: tiap baris = satu produk. Atribut data-id disiapkan untuk CRUD nanti. -->
          <tr data-id="p1">
            <td>Nasi Kotak Ayam</td><td>Nasi &amp; Lauk</td><td class="num">Rp15.000</td><td class="num">40</td>
            <td><span class="status done">Tersedia</span></td>
            <td class="aksi"><a href="#ubah" class="btn btn-ghost btn-sm">Ubah</a><a href="#hapus" class="btn btn-danger btn-sm">Hapus</a></td>
          </tr>
          <tr data-id="p2">
            <td>Nasi Kotak Campur</td><td>Katering</td><td class="num">Rp15.000</td><td class="num">60</td>
            <td><span class="status done">Tersedia</span></td>
            <td class="aksi"><a href="#ubah" class="btn btn-ghost btn-sm">Ubah</a><a href="#hapus" class="btn btn-danger btn-sm">Hapus</a></td>
          </tr>
          <tr data-id="p3">
            <td>Nasi Kotak Vegetarian</td><td>Nasi &amp; Lauk</td><td class="num">Rp15.000</td><td class="num">4</td>
            <td><span class="status pending">Menipis</span></td>
            <td class="aksi"><a href="#ubah" class="btn btn-ghost btn-sm">Ubah</a><a href="#hapus" class="btn btn-danger btn-sm">Hapus</a></td>
          </tr>
          <tr data-id="p4">
            <td>Rendang Sekotak</td><td>Nasi &amp; Lauk</td><td class="num">Rp45.000</td><td class="num">12</td>
            <td><span class="status done">Tersedia</span></td>
            <td class="aksi"><a href="#ubah" class="btn btn-ghost btn-sm">Ubah</a><a href="#hapus" class="btn btn-danger btn-sm">Hapus</a></td>
          </tr>
          <tr data-id="p5">
            <td>Wedang Jahe Botol</td><td>Minuman</td><td class="num">Rp8.000</td><td class="num">25</td>
            <td><span class="status done">Tersedia</span></td>
            <td class="aksi"><a href="#ubah" class="btn btn-ghost btn-sm">Ubah</a><a href="#hapus" class="btn btn-danger btn-sm">Hapus</a></td>
          </tr>
          <tr data-id="p6">
            <td>Sambal Bawang 150 gr</td><td>Bumbu &amp; Sambal</td><td class="num">Rp22.000</td><td class="num">0</td>
            <td><span class="status cancel">Habis</span></td>
            <td class="aksi"><a href="#ubah" class="btn btn-ghost btn-sm">Ubah</a><a href="#hapus" class="btn btn-danger btn-sm">Hapus</a></td>
          </tr>
          <tr data-id="p7">
            <td>Brownies Kukus</td><td>Kue &amp; Kudapan</td><td class="num">Rp35.000</td><td class="num">3</td>
            <td><span class="status pending">Menipis</span></td>
            <td class="aksi"><a href="#ubah" class="btn btn-ghost btn-sm">Ubah</a><a href="#hapus" class="btn btn-danger btn-sm">Hapus</a></td>
          </tr>
          <tr data-id="p8">
            <td>Nugget Ayam Frozen</td><td>Frozen Food</td><td class="num">Rp28.000</td><td class="num">18</td>
            <td><span class="status done">Tersedia</span></td>
            <td class="aksi"><a href="#ubah" class="btn btn-ghost btn-sm">Ubah</a><a href="#hapus" class="btn btn-danger btn-sm">Hapus</a></td>
          </tr>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<!-- CREATE: form tambah produk (dibuka oleh tombol "+ Tambah Produk") -->
<div class="modal" id="tambah">
  <a href="#" class="modal-bg" aria-label="Tutup"></a>
  <form class="modal-box" id="formTambah" method="post" action="#">
    <div class="modal-head"><h3>Tambah produk</h3></div>
    <div class="modal-body">
      <div class="field">
        <label for="tNama">Nama produk</label>
        <input type="text" id="tNama" name="nama" placeholder="Contoh: Nasi Kotak Ayam" maxlength="80" required>
      </div>
      <div class="field">
        <label for="tKategori">Kategori</label>
        <select id="tKategori" name="kategori" required>
          <option>Nasi &amp; Lauk</option>
          <option>Katering</option>
          <option>Minuman</option>
          <option>Kue &amp; Kudapan</option>
          <option>Frozen Food</option>
          <option>Bumbu &amp; Sambal</option>
          <option>Oleh-oleh</option>
        </select>
      </div>
      <div class="field-row">
        <div class="field">
          <label for="tHarga">Harga (Rp)</label>
          <input type="number" id="tHarga" name="harga" min="0" step="500" value="15000" required>
        </div>
        <div class="field">
          <label for="tStok">Stok</label>
          <input type="number" id="tStok" name="stok" min="0" step="1" value="10" required>
        </div>
      </div>
    </div>
    <div class="modal-foot">
      <a href="#" class="btn btn-ghost">Batal</a>
      <button type="submit" class="btn btn-solid">Simpan produk</button>
    </div>
  </form>
</div>

<!-- UPDATE: form ubah produk (contoh terisi data produk pertama) -->
<div class="modal" id="ubah">
  <a href="#" class="modal-bg" aria-label="Tutup"></a>
  <form class="modal-box" id="formUbah" method="post" action="#">
    <input type="hidden" name="id" value="p1">
    <div class="modal-head"><h3>Ubah produk</h3></div>
    <div class="modal-body">
      <div class="field">
        <label for="uNama">Nama produk</label>
        <input type="text" id="uNama" name="nama" value="Nasi Kotak Ayam" maxlength="80" required>
      </div>
      <div class="field">
        <label for="uKategori">Kategori</label>
        <select id="uKategori" name="kategori" required>
          <option selected>Nasi &amp; Lauk</option>
          <option>Katering</option>
          <option>Minuman</option>
          <option>Kue &amp; Kudapan</option>
          <option>Frozen Food</option>
          <option>Bumbu &amp; Sambal</option>
          <option>Oleh-oleh</option>
        </select>
      </div>
      <div class="field-row">
        <div class="field">
          <label for="uHarga">Harga (Rp)</label>
          <input type="number" id="uHarga" name="harga" min="0" step="500" value="15000" required>
        </div>
        <div class="field">
          <label for="uStok">Stok</label>
          <input type="number" id="uStok" name="stok" min="0" step="1" value="40" required>
        </div>
      </div>
    </div>
    <div class="modal-foot">
      <a href="#" class="btn btn-ghost">Batal</a>
      <button type="submit" class="btn btn-solid">Simpan perubahan</button>
    </div>
  </form>
</div>

<!-- DELETE: konfirmasi hapus produk -->
<div class="modal" id="hapus">
  <a href="#" class="modal-bg" aria-label="Tutup"></a>
  <form class="modal-box" method="post" action="#">
    <input type="hidden" name="id" value="p1">
    <div class="modal-head"><h3>Hapus produk?</h3></div>
    <div class="modal-body">
      <p>Produk yang dipilih akan dihapus dari lapakmu dan tidak bisa dikembalikan.</p>
    </div>
    <div class="modal-foot">
      <a href="#" class="btn btn-ghost">Batal</a>
      <button type="submit" class="btn btn-danger">Hapus produk</button>
    </div>
  </form>
</div>

<script src="app.js"></script>
<script src="produk.js"></script>
</body>
</html>