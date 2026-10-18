/* =====================================================================
   app.js — fungsi bersama untuk semua halaman dashboard & auth
   BAB 4 JavaScript II: Web Storage, Promise, Event, PopUp Box
   ===================================================================== */
(function (global) {
  'use strict';

  const UMKM = {
    // Kunci Web Storage dikumpulkan di satu tempat agar tidak salah ketik
    KUNCI: {
      sesi: 'umkm_sesi',               // sessionStorage: hilang saat tab ditutup
      akun: 'umkm_akun',               // localStorage: daftar akun terdaftar
      ingatEmail: 'umkm_ingat_email',  // localStorage: fitur "Ingat email saya"
      namaLapak: 'umkm_nama_lapak',    // localStorage: nama lapak hasil ubah (prompt)
      pesanan: 'umkm_pesanan',         // localStorage: data pesanan
      produk: 'umkm_produk'            // localStorage: data produk
    },

    /* ---------- Web Storage ---------- */
    // Baca JSON dari localStorage. Jika kosong/rusak, kembalikan nilai cadangan.
    baca(kunci, cadangan) {
      try {
        const hasil = JSON.parse(localStorage.getItem(kunci));
        return hasil === null || hasil === undefined ? cadangan : hasil;
      } catch (e) {
        return cadangan;
      }
    },
    // Simpan objek/array ke localStorage sebagai teks JSON.
    // Bisa melempar error (mis. storage penuh), jadi dipanggil di dalam try/catch.
    simpan(kunci, nilai) {
      localStorage.setItem(kunci, JSON.stringify(nilai));
    },
    // Ambil data sesi login dari sessionStorage
    sesi() {
      try { return JSON.parse(sessionStorage.getItem(UMKM.KUNCI.sesi)); }
      catch (e) { return null; }
    },
    namaLapak() {
      const ubahan = localStorage.getItem(UMKM.KUNCI.namaLapak);
      const sesi = UMKM.sesi();
      return ubahan || (sesi && sesi.nama) || 'Dapur Bu Sari';
    },

    /* ---------- Utilitas ---------- */
    rupiah(n) {
      return 'Rp' + Number(n).toLocaleString('id-ID');
    },
    // Promise sederhana: selesai (resolve) setelah ms milidetik.
    // Dipakai bersama await untuk mensimulasikan proses asynchronous.
    tunda(ms) {
      return new Promise(function (resolve) { setTimeout(resolve, ms); });
    },
    // Notifikasi kecil (bukan PopUp Box bawaan) yang hilang otomatis
    notifikasi(pesan, tipe) {
      const warna = { success: '#4ADE80', error: '#FF6B4A', info: '#F5B93A' };
      const n = document.createElement('div');
      n.setAttribute('role', 'status');
      n.textContent = pesan;
      n.style.cssText =
        'position:fixed;right:20px;bottom:20px;z-index:200;max-width:340px;padding:12px 16px;' +
        'background:#14161A;color:#F7EFDD;font-size:14px;border-radius:10px;' +
        'border:1px solid rgba(255,255,255,.12);border-left:3px solid ' + (warna[tipe] || warna.info) + ';' +
        'box-shadow:0 14px 34px -14px rgba(0,0,0,.8)';
      document.body.appendChild(n);
      setTimeout(function () { n.remove(); }, 3200);
    }
  };

  global.UMKM = UMKM;

  /* ---------- EVENT click + POPUP confirm: tombol "Keluar" ---------- */
  document.querySelectorAll('.side nav a[href="login.html"]').forEach(function (a) {
    a.addEventListener('click', function (e) {
      // confirm() mengembalikan true (OK) / false (Cancel)
      if (!confirm('Yakin ingin keluar dari akun?')) {
        e.preventDefault();               // batal pindah halaman
        return;
      }
      sessionStorage.removeItem(UMKM.KUNCI.sesi);   // hapus sesi login
    });
  });
})(window);
