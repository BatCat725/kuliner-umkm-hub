/* =====================================================================
   auth.js — Login & Register
   PopUp Box : alert(), confirm()
   Event     : load, submit, input, change
   Web Storage: localStorage (akun, ingat email), sessionStorage (sesi)
   Promise + async/await: cariAkun(), simpanAkun()
   ===================================================================== */
(function () {
  'use strict';

  const K = UMKM.KUNCI;
  const polaEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;   // pola email sederhana

  // Simpan sesi login (sessionStorage: hilang saat tab ditutup)
  function mulaiSesi(email, nama) {
    sessionStorage.setItem(K.sesi, JSON.stringify({ email: email, nama: nama }));
  }

  /* ---------- PROMISE: cari akun di localStorage ---------- */
  // Mensimulasikan permintaan ke server (0,5 detik).
  // resolve(akun | null) -> proses selesai, reject(error) -> proses gagal
  function cariAkun(email) {
    return new Promise(function (resolve, reject) {
      setTimeout(function () {
        try {
          const daftar = UMKM.baca(K.akun, []);
          resolve(daftar.find(function (a) { return a.email === email; }) || null);
        } catch (err) {
          reject(err);
        }
      }, 500);
    });
  }

  /* ---------- PROMISE: simpan akun baru ---------- */
  function simpanAkun(akun) {
    return new Promise(function (resolve, reject) {
      setTimeout(function () {
        try {
          const daftar = UMKM.baca(K.akun, []);
          if (daftar.some(function (a) { return a.email === akun.email; })) {
            reject(new Error('Email sudah terdaftar. Silakan masuk.'));   // ditolak
            return;
          }
          daftar.push(akun);
          UMKM.simpan(K.akun, daftar);
          resolve(akun);                                                   // berhasil
        } catch (err) {
          reject(err);
        }
      }, 600);
    });
  }

  /* =================== HALAMAN LOGIN =================== */
  const formLogin = document.getElementById('formLogin');
  if (formLogin) {
    const email = document.getElementById('email');
    const pass = document.getElementById('pass');
    const ingat = document.getElementById('ingat');
    const tombol = formLogin.querySelector('button[type="submit"]');

    // EVENT load: isi otomatis email yang pernah disimpan (localStorage)
    window.addEventListener('load', function () {
      const tersimpan = localStorage.getItem(K.ingatEmail);
      if (tersimpan) {
        email.value = tersimpan;
        ingat.checked = true;
        pass.focus();
      } else {
        email.focus();
      }
    });

    // EVENT change: hapus email tersimpan jika "Ingat email saya" dimatikan
    ingat.addEventListener('change', function () {
      if (!ingat.checked) localStorage.removeItem(K.ingatEmail);
    });

    // EVENT submit: validasi -> alert, lalu proses async
    formLogin.addEventListener('submit', async function (e) {
      e.preventDefault();                       // cegah form reload halaman
      const em = email.value.trim().toLowerCase();

      if (!polaEmail.test(em)) {
        alert('Format email tidak valid.');     // POPUP alert
        email.focus();
        return;
      }
      if (pass.value.length < 8) {
        alert('Kata sandi minimal 8 karakter.');
        pass.focus();
        return;
      }

      tombol.disabled = true;
      tombol.textContent = 'Memeriksa...';
      try {
        const akun = await cariAkun(em);        // await: tunggu Promise selesai
        let nama;

        if (akun) {
          nama = akun.namaUsaha;
        } else {
          // POPUP confirm: akun belum ada, tawarkan akun demo atau daftar
          const lanjutDemo = confirm(
            'Email ini belum terdaftar.\n\n' +
            'OK = masuk sebagai akun demo (Dapur Bu Sari)\n' +
            'Cancel = buat akun baru'
          );
          if (!lanjutDemo) {
            window.location.href = 'register.php';
            return;
          }
          nama = 'Dapur Bu Sari';
        }

        // Web Storage: ingat email (localStorage) + sesi (sessionStorage)
        if (ingat.checked) localStorage.setItem(K.ingatEmail, em);
        mulaiSesi(em, nama);

        alert('Selamat datang, ' + nama + '!');
        document.getElementById('namaPengguna').value = nama;
        formLogin.submit();                     // kirim ke login.php (PHP menyimpan SESSION)
      } catch (err) {
        alert('Gagal masuk: ' + err.message);   // Promise ditolak -> catch
      } finally {
        tombol.disabled = false;                // selalu dijalankan
        tombol.textContent = 'Masuk';
      }
    });
  }

  /* =================== HALAMAN REGISTER =================== */
  const formDaftar = document.getElementById('formDaftar');
  if (formDaftar) {
    const nama = document.getElementById('nama');
    const kategori = document.getElementById('kategori');
    const email2 = document.getElementById('email2');
    const pass2 = document.getElementById('pass2');
    const kekuatan = document.getElementById('kekuatan');
    const tombol = formDaftar.querySelector('button[type="submit"]');

    window.addEventListener('load', function () { nama.focus(); });

    // EVENT input: indikator kekuatan kata sandi, berubah setiap ketikan
    pass2.addEventListener('input', function () {
      const p = pass2.value;
      if (!p) { kekuatan.textContent = ''; return; }
      let skor = 0;
      if (p.length >= 8) skor++;
      if (/[A-Z]/.test(p) && /[a-z]/.test(p)) skor++;
      if (/\d/.test(p)) skor++;
      if (/[^A-Za-z0-9]/.test(p)) skor++;
      const label = ['Lemah', 'Lemah', 'Sedang', 'Kuat', 'Sangat kuat'][skor];
      const warna = ['#FF6B4A', '#FF6B4A', '#F5B93A', '#4ADE80', '#4ADE80'][skor];
      kekuatan.textContent = 'Kekuatan kata sandi: ' + label;
      kekuatan.style.color = warna;
    });

    // EVENT submit: validasi -> alert, simpan lewat Promise
    formDaftar.addEventListener('submit', async function (e) {
      e.preventDefault();
      const dataBaru = {
        namaUsaha: nama.value.trim(),
        kategori: kategori.value,
        email: email2.value.trim().toLowerCase()
        // Kata sandi sengaja TIDAK disimpan di localStorage (tidak aman).
        // Pemeriksaan sandi yang benar dilakukan server (PHP) di bab berikutnya.
      };

      if (dataBaru.namaUsaha.length < 3) { alert('Nama usaha minimal 3 karakter.'); nama.focus(); return; }
      if (!dataBaru.kategori) { alert('Pilih kategori usaha terlebih dahulu.'); kategori.focus(); return; }
      if (!polaEmail.test(dataBaru.email)) { alert('Format email tidak valid.'); email2.focus(); return; }
      if (pass2.value.length < 8) { alert('Kata sandi minimal 8 karakter.'); pass2.focus(); return; }

      tombol.disabled = true;
      tombol.textContent = 'Membuat lapak...';
      try {
        await simpanAkun(dataBaru);
        mulaiSesi(dataBaru.email, dataBaru.namaUsaha);
        alert('Lapak "' + dataBaru.namaUsaha + '" berhasil dibuat!');
        formDaftar.submit();                    // kirim ke register.php (PHP menyimpan SESSION)
      } catch (err) {
        alert(err.message);                     // contoh: email sudah terdaftar
      } finally {
        tombol.disabled = false;
        tombol.textContent = 'Buat Lapak';
      }
    });
  }
})();
