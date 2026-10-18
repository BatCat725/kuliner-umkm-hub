/* =====================================================================
   dashboard.js — Halaman Ringkasan
   PopUp Box : prompt() + alert() (ubah nama lapak)
   Event     : load, click, mouseover, mouseout
   Web Storage: localStorage (nama lapak, daftar pesanan)
   Async/await + Promise + Fetch: muatRingkasan()
   ===================================================================== */
(function () {
  'use strict';

  const K = UMKM.KUNCI;
  const $ = function (id) { return document.getElementById(id); };
  const KELAS_STATUS = { Diproses: 'pending', Selesai: 'done', Dibatalkan: 'cancel' };

  function isiSalam() {
    $('salam').textContent = 'Halo, ' + UMKM.namaLapak();
  }

  /* ---------- Tabel "Pesanan terbaru" dari localStorage ---------- */
  // Data dibuat di halaman Pesanan (pesanan.js). Jika belum ada, tabel bawaan HTML dipakai.
  function renderTerbaru() {
    const daftar = UMKM.baca(K.pesanan, []);
    if (!Array.isArray(daftar) || daftar.length === 0) return;

    const tbody = $('tbodyTerbaru');
    tbody.textContent = '';
    daftar.slice(0, 4).forEach(function (p) {
      const tr = document.createElement('tr');
      const produk = p.produk + (p.jumlah > 1 ? ' (' + p.jumlah + ')' : '');
      [p.pemesan, produk, UMKM.rupiah(p.jumlah * p.harga)].forEach(function (teks) {
        const td = document.createElement('td');
        td.textContent = teks;            // textContent: aman dari HTML injection
        tr.appendChild(td);
      });
      const tdStatus = document.createElement('td');
      const badge = document.createElement('span');
      badge.className = 'status ' + (KELAS_STATUS[p.status] || 'pending');
      badge.textContent = p.status;
      tdStatus.appendChild(badge);
      tr.appendChild(tdStatus);
      tbody.appendChild(tr);
    });
  }

  /* ---------- FETCH + ASYNC/AWAIT: ambil data ringkasan (JSON) ---------- */
  function isiKpi(d) {
    $('kPesanan').textContent = d.pesananHariIni;
    $('dPesanan').textContent = d.selisihPesanan;
    $('kUang').textContent = d.pendapatan;
    $('dUang').textContent = d.selisihPendapatan;
    $('kProduk').textContent = d.produkAktif;
    $('dProduk').textContent = d.selisihProduk;
    $('kRating').textContent = d.rating;
    $('dRating').textContent = d.selisihRating;
  }

  // async function selalu mengembalikan Promise; await menunggu hasilnya
  async function muatRingkasan(tampilNotif) {
    try {
      // fetch() mengembalikan Promise berisi Response dari server
      const respons = await fetch('data/ringkasan.json', { cache: 'no-store' });
      if (!respons.ok) throw new Error('HTTP ' + respons.status);   // 404, 500, dst.
      const data = await respons.json();                            // Response -> objek JS
      isiKpi(data);
      if (tampilNotif) UMKM.notifikasi('Data ringkasan berhasil dimuat ulang.', 'success');
    } catch (err) {
      // Gagal (mis. dibuka lewat file:// atau server mati): tampilan bawaan tetap dipakai
      console.warn('Fetch ringkasan gagal:', err.message);
      if (tampilNotif) UMKM.notifikasi('Gagal memuat data (' + err.message + '). Memakai data bawaan.', 'error');
    }
  }

  /* ---------- EVENT load: jalan saat halaman selesai dimuat ---------- */
  window.addEventListener('load', function () {
    $('tanggal').textContent = new Date().toLocaleDateString('id-ID', {
      weekday: 'long', day: 'numeric', month: 'long'
    });
    isiSalam();
    renderTerbaru();
    muatRingkasan(false);
  });

  /* ---------- EVENT click + POPUP prompt: ubah nama lapak ---------- */
  $('btnNama').addEventListener('click', function () {
    const baru = prompt('Masukkan nama lapak baru:', UMKM.namaLapak());
    if (baru === null) return;                  // Cancel ditekan -> prompt mengembalikan null

    const bersih = baru.trim();
    if (bersih.length < 3) {
      alert('Nama lapak minimal 3 karakter.');
      return;
    }
    localStorage.setItem(K.namaLapak, bersih);  // simpan permanen
    isiSalam();
    UMKM.notifikasi('Nama lapak diubah menjadi "' + bersih + '".', 'success');
  });

  /* ---------- EVENT click: muat ulang data dengan fetch ---------- */
  $('btnMuat').addEventListener('click', async function () {
    const tombol = $('btnMuat');
    tombol.disabled = true;
    tombol.textContent = 'Memuat...';
    await UMKM.tunda(400);                      // jeda singkat agar status "Memuat" terlihat
    await muatRingkasan(true);
    tombol.disabled = false;
    tombol.textContent = 'Muat ulang data';
  });

  /* ---------- EVENT mouseover / mouseout: kartu KPI terangkat ---------- */
  document.querySelectorAll('.kpi').forEach(function (kpi) {
    kpi.style.transition = 'transform .15s ease';
    kpi.addEventListener('mouseover', function () { kpi.style.transform = 'translateY(-3px)'; });
    kpi.addEventListener('mouseout', function () { kpi.style.transform = 'none'; });
  });
})();
