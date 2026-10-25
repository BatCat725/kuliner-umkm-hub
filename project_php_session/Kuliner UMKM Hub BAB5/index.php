<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Kuliner UMKM Hub</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<style>
/* Pelengkap agar halaman ini berdiri sendiri di atas style.css bawaan */
button.btn{font-family:inherit;cursor:pointer}
.btn-sm{padding:6px 14px;font-size:12px}

/* ===== BAB 3: DOM landing page + Toast ===== */
[hidden]{display:none!important}

/* Navbar berubah saat halaman di-scroll (class .scrolled ditambah lewat JS) */
.nav{transition:box-shadow .2s ease,background .2s ease}
.nav.scrolled{background:rgba(8,9,11,.92);box-shadow:0 12px 30px -18px rgba(0,0,0,.9)}
.fav-badge{
  font-family:'JetBrains Mono',monospace;font-size:13px;
  color:var(--chili);background:rgba(255,107,74,.1);
  border:1px solid rgba(255,107,74,.3);
  padding:6px 12px;border-radius:999px;
}

/* Chip kategori (tombol) */
.cat-list button{
  border:1px solid var(--line);background:var(--glass);
  padding:9px 16px;border-radius:999px;
  font-size:13px;font-family:inherit;color:var(--ink-soft);
  cursor:pointer;transition:background .15s ease,color .15s ease;
}
.cat-list button:hover{background:var(--glass-hi);color:var(--ink)}
.cat-list button.active{background:var(--grad);border-color:transparent;color:#0A0D0B;font-weight:600}
.cat-list button:focus-visible,.fav:focus-visible,.toast button:focus-visible{outline:2px solid var(--leaf);outline-offset:2px}

/* Info filter dan kartu lapak */
.filter-info{
  display:flex;align-items:center;gap:14px;flex-wrap:wrap;
  margin-bottom:18px;font-size:14px;color:var(--ink-soft);
}
.stall-foot{display:flex;align-items:center;justify-content:space-between;gap:10px}
.stall-foot .price{font-size:14px}
.stall-foot .fav{flex:none}
.fav{
  border:1px solid var(--line);background:var(--glass);color:var(--ink-soft);
  padding:6px 12px;border-radius:999px;
  font-size:12px;font-family:inherit;cursor:pointer;
  transition:background .15s ease,color .15s ease,border-color .15s ease;
}
.fav:hover{background:var(--glass-hi);color:var(--ink)}
.fav.active{color:var(--chili);border-color:rgba(255,107,74,.45);background:rgba(255,107,74,.1)}
.empty-state{grid-column:1/-1;text-align:center;color:var(--ink-soft);padding:40px 0}

/* Toast / Snackbar */
.toast-wrap{
  position:fixed;right:24px;bottom:24px;z-index:100;
  display:flex;flex-direction:column;gap:10px;
  width:min(360px,calc(100% - 32px));
  pointer-events:none;
}
.toast{
  pointer-events:auto;
  display:flex;align-items:flex-start;gap:12px;
  padding:14px 16px;
  background:var(--bg-2);color:var(--ink);
  border:1px solid var(--line);border-left:3px solid var(--turmeric);
  border-radius:12px;
  box-shadow:0 18px 40px -16px rgba(0,0,0,.8);
  font-size:14px;
  animation:toast-in .22s ease-out;
}
.toast-icon{
  flex:none;width:20px;height:20px;border-radius:50%;
  display:flex;align-items:center;justify-content:center;
  font-family:'JetBrains Mono',monospace;font-size:12px;font-weight:600;
  color:#0A0D0B;background:var(--turmeric);
}
.toast-success{border-left-color:var(--leaf)}
.toast-success .toast-icon{background:var(--leaf)}
.toast-error{border-left-color:var(--chili)}
.toast-error .toast-icon{background:var(--chili)}
.toast-msg{flex:1;line-height:1.45}
.toast-action,.toast-close{
  background:none;border:none;font-family:inherit;cursor:pointer;
}
.toast-action{color:var(--leaf);font-weight:600;font-size:13px;padding:0 4px}
.toast-close{color:var(--ink-soft);font-size:18px;line-height:1;padding:0 2px}
.toast-close:hover{color:var(--ink)}
.toast.out{animation:toast-out .2s ease-in forwards}
@keyframes toast-in{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:none}}
@keyframes toast-out{to{opacity:0;transform:translateY(8px)}}
@media (prefers-reduced-motion:reduce){.toast,.toast.out{animation:none}}
@media(max-width:640px){.nav-links{display:none}}
@media(max-width:520px){
  .nav .wrap{padding:0 16px;gap:8px}
  .nav .wrap>div{gap:8px!important}
  .nav .brand{font-size:17px}
  .nav .btn{padding:8px 12px;font-size:12.5px;white-space:nowrap}
  .fav-badge{padding:5px 9px;font-size:12px}
}
@media(max-width:360px){.fav-badge{display:none}.nav .brand{font-size:16px}}
@media(max-width:480px){.toast-wrap{right:16px;left:16px;bottom:16px;width:auto}}
</style>
</head>
<body>

<header class="nav">
  <div class="wrap">
    <a href="index.php" class="brand">Kuliner<span>UMKM</span></a>
    <nav class="nav-links">
      <a href="#jelajah">Jelajah</a>
      <a href="#kategori">Kategori</a>
      <a href="#tentang">Tentang</a>
    </nav>
    <div style="display:flex;gap:12px;align-items:center">
      <span class="fav-badge" id="favBadge" title="Lapak favorit" hidden>♥ <b id="favCount">0</b></span>
      <?php if (!empty($_SESSION['nama'])): ?>
        <span class="fav-badge">Halo, <?= htmlspecialchars($_SESSION['nama']) ?></span>
        <a href="dashboard.php" class="btn btn-ghost">Dashboard</a>
        <a href="login.php?logout=1" class="btn btn-solid">Logout</a>
      <?php else: ?>
        <a href="login.php" class="btn btn-ghost">Masuk</a>
        <a href="register.php" class="btn btn-solid">Daftar Usaha</a>
      <?php endif; ?>
    </div>
  </div>
</header>

<section class="hero">
  <div class="wrap">
    <div class="eyebrow">Untuk pelaku usaha kuliner rumahan &amp; UMKM</div>
    <h1>Dapur rumahan, warung kaki lima, sampai katering kecil — <span class="grad-text">satu etalase digital.</span></h1>
    <p>Kuliner UMKM Hub membantu pelaku usaha kuliner mikro dan kecil memajang produk, menerima pesanan, dan dilihat pembeli di sekitar mereka, tanpa biaya sewa lapak digital yang mahal.</p>
    <div class="hero-actions">
      <a href="register.php" class="btn btn-solid">Buka Lapak Gratis</a>
      <a href="#jelajah" class="btn btn-ghost">Lihat UMKM Terdaftar</a>
    </div>
    <div class="stat-row">
      <div class="stat"><b data-target="1240">1.240</b><span>UMKM kuliner aktif</span></div>
      <div class="stat"><b data-target="18">18</b><span>Kota terjangkau</span></div>
      <div class="stat"><b data-target="0" data-suffix="%">0%</b><span>Komisi tahun pertama</span></div>
    </div>
  </div>
</section>

<section class="section" id="jelajah">
  <div class="wrap">
    <h2>Baru buka lapak minggu ini</h2>
    <p class="lead">Sebagian usaha kuliner yang baru bergabung dan siap menerima pesanan.</p>
    <div class="filter-info" id="filterInfo" hidden>
      <span id="filterText"></span>
      <button type="button" class="btn btn-ghost btn-sm" id="btnResetFilter">Tampilkan semua</button>
    </div>
    <div class="stall-grid" id="stallGrid">
      <noscript><p class="lead">Aktifkan JavaScript untuk melihat daftar lapak.</p></noscript>
    </div>
  </div>
</section>

<section class="section" id="kategori">
  <div class="wrap">
    <h2>Cari berdasarkan kategori</h2>
    <p class="lead">Telusuri usaha kuliner sesuai kebutuhanmu hari ini. Klik kategori untuk menyaring daftar lapak.</p>
    <div class="cat-list" id="catList">
      <button type="button" class="active" data-kategori="" aria-pressed="true">Semua</button>
      <button type="button" data-kategori="Nasi &amp; Lauk" aria-pressed="false">Nasi &amp; Lauk</button>
      <button type="button" data-kategori="Katering" aria-pressed="false">Katering</button>
      <button type="button" data-kategori="Minuman" aria-pressed="false">Minuman</button>
      <button type="button" data-kategori="Kue &amp; Kudapan" aria-pressed="false">Kue &amp; Kudapan</button>
      <button type="button" data-kategori="Frozen Food" aria-pressed="false">Frozen Food</button>
      <button type="button" data-kategori="Bumbu &amp; Sambal" aria-pressed="false">Bumbu &amp; Sambal</button>
      <button type="button" data-kategori="Oleh-oleh" aria-pressed="false">Oleh-oleh</button>
    </div>
  </div>
</section>

<section class="section" id="tentang" style="border-bottom:none">
  <div class="wrap">
    <h2>Dibuat untuk pelaku usaha, bukan hanya pembeli</h2>
    <p class="lead" style="margin-bottom:0">Setiap pemilik usaha punya dashboard sendiri untuk mengatur produk, memantau pesanan masuk, dan melihat perkembangan usahanya dari waktu ke waktu.</p>
  </div>
</section>

<footer>
  <div class="wrap">© 2026 Kuliner UMKM Hub — Prototipe tugas praktikum.</div>
</footer>

<script>
/* Toast / Snackbar — JavaScript murni
   Pemakaian:
     showToast('Pesan berhasil disimpan', 'success');
     showToast('Terjadi kesalahan', { type: 'error', duration: 5000 });
     showToast('Item dihapus', { type: 'info', action: { label: 'Urungkan', onClick: function () { ... } } });
   type: 'success' | 'error' | 'info' (default 'info'). duration dalam ms (0 = tidak hilang otomatis). */
(function (global) {
  'use strict';

  var MAX_TOAST = 3;
  var ICON = { success: '✓', error: '✕', info: 'i' };
  var wrap = null;

  function ensureWrap() {
    if (wrap && document.body.contains(wrap)) return wrap;
    wrap = document.createElement('div');
    wrap.className = 'toast-wrap';
    wrap.setAttribute('role', 'status');
    wrap.setAttribute('aria-live', 'polite');
    document.body.appendChild(wrap);
    return wrap;
  }

  function dismiss(t) {
    if (!t || t._gone) return;
    t._gone = true;
    clearTimeout(t._timer);
    t.classList.add('out');
    function done() { if (t.parentNode) t.parentNode.removeChild(t); }
    t.addEventListener('animationend', done);
    setTimeout(done, 350);   // cadangan bila animasi dimatikan
  }

  function showToast(message, opts) {
    if (typeof opts === 'string') opts = { type: opts };
    opts = opts || {};
    var type = ICON[opts.type] ? opts.type : 'info';
    var duration = typeof opts.duration === 'number' ? opts.duration : 3500;

    var container = ensureWrap();
    while (container.children.length >= MAX_TOAST) {
      container.removeChild(container.firstChild);   // buang yang paling lama
    }

    var t = document.createElement('div');
    t.className = 'toast toast-' + type;
    if (type === 'error') t.setAttribute('role', 'alert');

    var icon = document.createElement('span');
    icon.className = 'toast-icon';
    icon.setAttribute('aria-hidden', 'true');
    icon.textContent = ICON[type];

    var msg = document.createElement('span');
    msg.className = 'toast-msg';
    msg.textContent = message;          // textContent: aman dari HTML injection

    t.appendChild(icon);
    t.appendChild(msg);

    if (opts.action && opts.action.label) {
      var act = document.createElement('button');
      act.type = 'button';
      act.className = 'toast-action';
      act.textContent = opts.action.label;
      act.addEventListener('click', function () {
        if (typeof opts.action.onClick === 'function') opts.action.onClick();
        dismiss(t);
      });
      t.appendChild(act);
    }

    var close = document.createElement('button');
    close.type = 'button';
    close.className = 'toast-close';
    close.setAttribute('aria-label', 'Tutup notifikasi');
    close.textContent = '×';
    close.addEventListener('click', function () { dismiss(t); });
    t.appendChild(close);

    container.appendChild(t);

    function start() { if (duration > 0) t._timer = setTimeout(function () { dismiss(t); }, duration); }
    t.addEventListener('mouseenter', function () { clearTimeout(t._timer); });   // jeda saat disorot
    t.addEventListener('mouseleave', start);
    start();

    return t;
  }

  global.showToast = showToast;
})(window);
</script>
<script>
/* Manipulasi DOM pada Landing Page (index.php) — JavaScript murni
   1. Membuat kartu lapak secara dinamis (createElement, appendChild)
   2. Menyaring lapak berdasarkan kategori (classList, hidden, textContent)
   3. Menganimasikan angka statistik (textContent, IntersectionObserver)
   4. Menyimpan lapak favorit + badge di navbar (toggle class, atribut, textContent)
   5. Navbar berubah saat di-scroll (classList.toggle) */
(function () {
  'use strict';

  var STALLS = [
    { id: 'l1', nama: 'Dapur Bu Sari',     kategori: 'Katering',       tag: 'Katering Rumahan', desk: 'Nasi kotak dan lauk harian untuk kantor, minimal pesan 10 porsi.',          harga: 'Mulai Rp15.000/porsi' },
    { id: 'l2', nama: 'Wedang Pak Joko',   kategori: 'Minuman',        tag: 'Minuman',          desk: 'Wedang jahe dan bandrek racikan sendiri, dikirim dalam botol kaca.',        harga: 'Mulai Rp8.000' },
    { id: 'l3', nama: 'Gorengan Bu Tini',  kategori: 'Kue & Kudapan',  tag: 'Kudapan',          desk: 'Gorengan pesanan matang di tempat, cocok untuk acara dan arisan.',          harga: 'Mulai Rp2.500/pcs' },
    { id: 'l4', nama: 'Pecel Bu Lastri',   kategori: 'Nasi & Lauk',    tag: 'Nasi & Lauk',      desk: 'Nasi pecel dengan sayur segar dan bumbu kacang tumbuk setiap pagi.',        harga: 'Mulai Rp12.000' },
    { id: 'l5', nama: 'Sambal Bu Rini',    kategori: 'Bumbu & Sambal', tag: 'Sambal',           desk: 'Sambal bawang dan terasi dalam kemasan 150 gr, tahan hingga dua minggu.',   harga: 'Mulai Rp22.000' },
    { id: 'l6', nama: 'Nugget Rumahan Ani', kategori: 'Frozen Food',   tag: 'Frozen Food',      desk: 'Nugget ayam dan sayur tanpa pengawet, dikirim beku dalam kotak.',           harga: 'Mulai Rp28.000' }
  ];

  var FAV_KEY = 'umkm_favorit';
  var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function $(sel) { return document.querySelector(sel); }
  function el(tag, cls, text) {
    var n = document.createElement(tag);
    if (cls) n.className = cls;
    if (text !== undefined) n.textContent = text;
    return n;
  }
  function toast(msg, opts) { if (window.showToast) window.showToast(msg, opts); }

  /* ---------- Favorit (disimpan di localStorage) ---------- */
  function loadFav() {
    try {
      var r = JSON.parse(localStorage.getItem(FAV_KEY));
      if (Array.isArray(r)) return r;
    } catch (e) { /* abaikan */ }
    return [];
  }
  function saveFav() {
    try { localStorage.setItem(FAV_KEY, JSON.stringify(favs)); } catch (e) { /* abaikan */ }
  }

  var favs = loadFav();
  var kategoriAktif = '';

  var grid = $('#stallGrid');
  var filterInfo = $('#filterInfo');
  var filterText = $('#filterText');
  var emptyState = null;

  /* ---------- DOM 1: buat kartu lapak ---------- */
  function buatKartu(s) {
    var card = el('article', 'stall');
    card.dataset.id = s.id;
    card.dataset.kategori = s.kategori;

    card.appendChild(el('div', 'tag', s.tag));
    card.appendChild(el('h3', '', s.nama));
    card.appendChild(el('p', '', s.desk));

    var foot = el('div', 'stall-foot');
    foot.appendChild(el('div', 'price', s.harga));
    var btn = el('button', 'fav');
    btn.type = 'button';
    foot.appendChild(btn);
    card.appendChild(foot);

    gayaFav(btn, s.nama, favs.indexOf(s.id) > -1);
    return card;
  }

  function gayaFav(btn, nama, on) {
    btn.classList.toggle('active', on);
    btn.setAttribute('aria-pressed', String(on));
    btn.setAttribute('aria-label', (on ? 'Hapus dari favorit: ' : 'Simpan ke favorit: ') + nama);
    btn.textContent = on ? '♥ Tersimpan' : '♡ Simpan';
  }

  function renderLapak() {
    grid.textContent = '';
    STALLS.forEach(function (s) { grid.appendChild(buatKartu(s)); });
    emptyState = el('p', 'empty-state', 'Belum ada lapak di kategori ini. Jadilah yang pertama membuka lapak!');
    emptyState.hidden = true;
    grid.appendChild(emptyState);
  }

  /* ---------- DOM 4: favorit + badge navbar ---------- */
  function updateBadge() {
    var badge = $('#favBadge');
    if (!badge) return;
    badge.hidden = favs.length === 0;
    $('#favCount').textContent = favs.length;
  }

  function setFav(id, on) {
    var i = favs.indexOf(id);
    if (on && i === -1) favs.push(id);
    if (!on && i > -1) favs.splice(i, 1);
    saveFav();
    var card = grid.querySelector('[data-id="' + id + '"]');
    var s = STALLS.filter(function (x) { return x.id === id; })[0];
    if (card && s) gayaFav(card.querySelector('.fav'), s.nama, on);
    updateBadge();
  }

  grid.addEventListener('click', function (e) {
    var btn = e.target.closest('.fav');
    if (!btn) return;
    var id = btn.closest('.stall').dataset.id;
    var s = STALLS.filter(function (x) { return x.id === id; })[0];
    var jadi = favs.indexOf(id) === -1;
    setFav(id, jadi);
    if (jadi) {
      toast(s.nama + ' disimpan ke favorit', 'success');
    } else {
      toast(s.nama + ' dihapus dari favorit', {
        type: 'info',
        action: { label: 'Urungkan', onClick: function () { setFav(id, true); } }
      });
    }
  });

  /* ---------- DOM 2: filter kategori ---------- */
  function terapkanFilter() {
    var chips = document.querySelectorAll('#catList button');
    var jumlah = 0;

    chips.forEach(function (c) {
      var aktif = c.dataset.kategori === kategoriAktif;
      c.classList.toggle('active', aktif);
      c.setAttribute('aria-pressed', String(aktif));
    });

    grid.querySelectorAll('.stall').forEach(function (card) {
      var tampil = !kategoriAktif || card.dataset.kategori === kategoriAktif;
      card.hidden = !tampil;
      if (tampil) jumlah++;
    });

    emptyState.hidden = jumlah > 0;
    filterInfo.hidden = !kategoriAktif;
    filterText.textContent = 'Menampilkan ' + jumlah + ' lapak kategori ' + kategoriAktif;

    if (kategoriAktif && jumlah === 0) {
      toast('Belum ada lapak di kategori ' + kategoriAktif, 'info');
    }
  }

  $('#catList').addEventListener('click', function (e) {
    var btn = e.target.closest('button[data-kategori]');
    if (!btn) return;
    kategoriAktif = btn.dataset.kategori;
    terapkanFilter();
    if (kategoriAktif) {
      $('#jelajah').scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' });
    }
  });

  $('#btnResetFilter').addEventListener('click', function () {
    kategoriAktif = '';
    terapkanFilter();
  });

  /* ---------- DOM 3: animasi angka statistik ---------- */
  function format(n, b) { return n.toLocaleString('id-ID') + (b.dataset.suffix || ''); }

  function hitung(b) {
    var target = Number(b.dataset.target);
    var mulai = null, durasi = 1200;
    function langkah(ts) {
      if (mulai === null) mulai = ts;
      var p = Math.min((ts - mulai) / durasi, 1);
      b.textContent = format(Math.round(target * (1 - Math.pow(1 - p, 3))), b);
      if (p < 1) requestAnimationFrame(langkah);
    }
    requestAnimationFrame(langkah);
  }

  var angka = document.querySelectorAll('.stat b[data-target]');
  if (angka.length && 'IntersectionObserver' in window && !reduceMotion) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting) { hitung(en.target); io.unobserve(en.target); }
      });
    }, { threshold: 0.5 });
    angka.forEach(function (b) { b.textContent = format(0, b); io.observe(b); });
  }
  // Tanpa IntersectionObserver / reduced motion: angka tetap tampil sesuai HTML.

  /* ---------- DOM 5: navbar saat scroll ---------- */
  var nav = $('.nav');
  function saatScroll() { nav.classList.toggle('scrolled', window.scrollY > 8); }
  window.addEventListener('scroll', saatScroll, { passive: true });
  saatScroll();

  /* ---------- Mulai ---------- */
  renderLapak();
  terapkanFilter();
  updateBadge();

  // Toast sambutan, sekali per sesi browser
  try {
    if (!sessionStorage.getItem('umkm_welcome')) {
      sessionStorage.setItem('umkm_welcome', '1');
      setTimeout(function () { toast('Selamat datang di Kuliner UMKM Hub!', 'success'); }, 600);
    }
  } catch (e) { /* sessionStorage diblokir: lewati */ }
})();
</script>
</body>
</html>