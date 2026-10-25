/* =====================================================================
   pesanan.js — Halaman Pesanan
   PopUp Box : prompt() + alert() (ubah status cepat), confirm() (buang perubahan)
   Event     : click, dblclick, submit, input, change, keyup, keydown
   Web Storage: localStorage (data pesanan)
   Promise + async/await: muatDariStorage(), simpanKeStorage()
   ===================================================================== */
(function () {
  'use strict';

  const $ = function (id) { return document.getElementById(id); };
  const KUNCI = UMKM.KUNCI.pesanan;
  const STATUS = ['Diproses', 'Selesai', 'Dibatalkan'];
  const KELAS = { Diproses: 'pending', Selesai: 'done', Dibatalkan: 'cancel' };

  // Data awal (dipakai sekali, saat localStorage masih kosong)
  const DATA_AWAL = [
    { id: 'o1', pemesan: 'Rina W.',       produk: 'Nasi Kotak Ayam',       jumlah: 10, harga: 15000, status: 'Diproses' },
    { id: 'o2', pemesan: 'Kantor Amanah', produk: 'Nasi Kotak Campur',     jumlah: 25, harga: 15000, status: 'Diproses' },
    { id: 'o3', pemesan: 'Budi S.',       produk: 'Rendang Sekotak',       jumlah: 1,  harga: 45000, status: 'Selesai' },
    { id: 'o4', pemesan: 'Sinta P.',      produk: 'Nasi Kotak Vegetarian', jumlah: 5,  harga: 15000, status: 'Selesai' }
  ];

  let data = [];            // seluruh pesanan
  let idUbah = null;        // null = mode tambah, selain itu = id yang sedang diubah
  let idHapus = null;       // id yang menunggu konfirmasi hapus
  let adaPerubahan = false; // true jika isi form sudah diubah pengguna

  const tbody = $('tbody');
  const dlgForm = $('dlgForm');
  const dlgHapus = $('dlgHapus');
  const form = $('form');

  /* ---------- PROMISE: baca & tulis localStorage ---------- */
  // Dibungkus Promise + setTimeout untuk mensimulasikan akses ke server.
  function muatDariStorage() {
    return new Promise(function (resolve, reject) {
      setTimeout(function () {
        try {
          let d = UMKM.baca(KUNCI, null);
          if (!Array.isArray(d)) {              // pertama kali: isi data awal
            d = DATA_AWAL;
            UMKM.simpan(KUNCI, d);
          }
          resolve(d);
        } catch (err) {
          reject(err);
        }
      }, 400);
    });
  }

  function simpanKeStorage(d) {
    return new Promise(function (resolve, reject) {
      setTimeout(function () {
        try {
          UMKM.simpan(KUNCI, d);
          resolve();
        } catch (err) {
          reject(err);                          // mis. storage penuh
        }
      }, 300);
    });
  }

  // Simpan data baru. Jika berhasil, data lama diganti; jika gagal, tampilkan alert.
  async function terapkan(dataBaru, pesanSukses) {
    try {
      await simpanKeStorage(dataBaru);
      data = dataBaru;
      render();
      if (pesanSukses) UMKM.notifikasi(pesanSukses, 'success');
      return true;
    } catch (err) {
      alert('Gagal menyimpan data: ' + err.message);
      return false;
    }
  }

  /* ---------- Tampilkan tabel + ringkasan ---------- */
  function sel(teks, kelas) {
    const td = document.createElement('td');
    td.textContent = teks;
    if (kelas) td.className = kelas;
    return td;
  }

  function buatBaris(p) {
    const tr = document.createElement('tr');
    tr.dataset.id = p.id;
    tr.appendChild(sel(p.pemesan));
    tr.appendChild(sel(p.produk));
    tr.appendChild(sel(p.jumlah, 'num'));
    tr.appendChild(sel(UMKM.rupiah(p.jumlah * p.harga), 'num'));

    const tdStatus = document.createElement('td');
    tdStatus.dataset.kol = 'status';
    tdStatus.title = 'Klik dua kali untuk mengubah status';
    const badge = document.createElement('span');
    badge.className = 'status ' + KELAS[p.status];
    badge.textContent = p.status;
    tdStatus.appendChild(badge);
    tr.appendChild(tdStatus);

    const tdAksi = document.createElement('td');
    tdAksi.className = 'aksi';
    [['ubah', 'Ubah', 'btn btn-ghost btn-sm'], ['hapus', 'Hapus', 'btn btn-danger btn-sm']].forEach(function (a) {
      const b = document.createElement('button');
      b.type = 'button';
      b.className = a[2];
      b.dataset.aksi = a[0];
      b.textContent = a[1];
      tdAksi.appendChild(b);
    });
    tr.appendChild(tdAksi);
    return tr;
  }

  function render() {
    const kata = $('cari').value.trim().toLowerCase();
    const status = $('filterStatus').value;

    const tampil = data.filter(function (p) {
      const cocokStatus = !status || p.status === status;
      const cocokKata = !kata || (p.pemesan + ' ' + p.produk).toLowerCase().includes(kata);
      return cocokStatus && cocokKata;
    });

    tbody.textContent = '';
    if (tampil.length === 0) {
      const tr = document.createElement('tr');
      const td = sel('Tidak ada pesanan yang cocok.', 'kosong');
      td.colSpan = 6;
      tr.appendChild(td);
      tbody.appendChild(tr);
    }
    tampil.forEach(function (p) { tbody.appendChild(buatBaris(p)); });

    $('judulTabel').textContent = 'Daftar pesanan (' + tampil.length + ')';
    $('kTotal').textContent = data.length;
    $('kProses').textContent = data.filter(function (p) { return p.status === 'Diproses'; }).length;
    $('kSelesai').textContent = data.filter(function (p) { return p.status === 'Selesai'; }).length;
    $('kUang').textContent = UMKM.rupiah(
      data.filter(function (p) { return p.status === 'Selesai'; })
          .reduce(function (jml, p) { return jml + p.jumlah * p.harga; }, 0)
    );
  }

  /* ---------- Form tambah / ubah ---------- */
  function hitungTotal() {
    const jml = parseInt($('fJumlah').value, 10) || 0;
    const hrg = Number($('fHarga').value) || 0;
    $('fTotal').textContent = UMKM.rupiah(jml * hrg);
  }

  function bukaForm(p) {
    idUbah = p ? p.id : null;
    $('formJudul').textContent = p ? 'Ubah pesanan' : 'Tambah pesanan';
    $('btnSimpan').textContent = p ? 'Simpan perubahan' : 'Simpan pesanan';
    $('fPemesan').value = p ? p.pemesan : '';
    $('fProduk').value = p ? p.produk : '';
    $('fJumlah').value = p ? p.jumlah : 1;
    $('fHarga').value = p ? p.harga : 15000;
    $('fStatus').value = p ? p.status : 'Diproses';
    $('formError').textContent = '';
    adaPerubahan = false;
    hitungTotal();
    dlgForm.showModal();
    $('fPemesan').focus();
  }

  function validasi(p) {
    if (!p.pemesan) return 'Nama pemesan wajib diisi.';
    if (!p.produk) return 'Nama produk wajib diisi.';
    if (!Number.isInteger(p.jumlah) || p.jumlah < 1) return 'Jumlah minimal 1.';
    if (!(p.harga >= 0)) return 'Harga tidak boleh negatif.';
    return '';
  }

  // POPUP confirm: tanya dulu bila ada isian yang belum disimpan
  function bolehTutupForm() {
    return !adaPerubahan || confirm('Perubahan belum disimpan. Tutup form dan buang perubahan?');
  }

  // EVENT click: tombol tambah
  $('btnTambah').addEventListener('click', function () { bukaForm(null); });

  // EVENT input: total & penanda "ada perubahan" diperbarui setiap ketikan
  form.addEventListener('input', function () {
    adaPerubahan = true;
    hitungTotal();
  });

  // EVENT click: tombol Batal
  $('btnBatal').addEventListener('click', function () {
    if (bolehTutupForm()) dlgForm.close();
  });

  // EVENT cancel (tombol Esc pada <dialog>): ikut konfirmasi yang sama
  dlgForm.addEventListener('cancel', function (e) {
    if (!bolehTutupForm()) e.preventDefault();
  });

  // EVENT submit: simpan pesanan
  form.addEventListener('submit', async function (e) {
    e.preventDefault();
    const p = {
      pemesan: $('fPemesan').value.trim(),
      produk: $('fProduk').value.trim(),
      jumlah: parseInt($('fJumlah').value, 10),
      harga: Number($('fHarga').value),
      status: $('fStatus').value
    };
    const pesanError = validasi(p);
    $('formError').textContent = pesanError;
    if (pesanError) return;

    const tombol = $('btnSimpan');
    const teksAwal = tombol.textContent;
    tombol.disabled = true;
    tombol.textContent = 'Menyimpan...';

    let dataBaru, pesan;
    if (idUbah) {
      dataBaru = data.map(function (x) { return x.id === idUbah ? Object.assign({}, x, p) : x; });
      pesan = 'Pesanan ' + p.pemesan + ' diperbarui.';
    } else {
      p.id = 'o' + Date.now();
      dataBaru = [p].concat(data);              // pesanan terbaru di paling atas
      pesan = 'Pesanan ' + p.pemesan + ' ditambahkan.';
    }

    const ok = await terapkan(dataBaru, pesan);
    tombol.disabled = false;
    tombol.textContent = teksAwal;
    if (ok) { adaPerubahan = false; dlgForm.close(); }
  });

  /* ---------- EVENT click pada tabel (event delegation): Ubah / Hapus ---------- */
  tbody.addEventListener('click', function (e) {
    const btn = e.target.closest('button[data-aksi]');
    if (!btn) return;
    const p = data.find(function (x) { return x.id === btn.closest('tr').dataset.id; });
    if (!p) return;

    if (btn.dataset.aksi === 'ubah') {
      bukaForm(p);
    } else {
      idHapus = p.id;
      $('hapusTeks').textContent = 'Pesanan ' + p.pemesan + ' (' + p.produk + ') akan dihapus dan tidak bisa dikembalikan.';
      dlgHapus.showModal();
    }
  });

  /* ---------- EVENT dblclick + POPUP prompt/alert: ubah status cepat ---------- */
  tbody.addEventListener('dblclick', function (e) {
    const td = e.target.closest('td');
    if (!td || td.classList.contains('aksi')) return;
    const p = data.find(function (x) { return x.id === td.parentNode.dataset.id; });
    if (!p) return;

    if (td.dataset.kol === 'status') {
      const isian = prompt('Ubah status pesanan ' + p.pemesan + '.\nKetik: Diproses, Selesai, atau Dibatalkan', p.status);
      if (isian === null) return;               // Cancel
      const cocok = STATUS.find(function (s) { return s.toLowerCase() === isian.trim().toLowerCase(); });
      if (!cocok) {
        alert('Status tidak valid. Gunakan: Diproses, Selesai, atau Dibatalkan.');
        return;
      }
      terapkan(
        data.map(function (x) { return x.id === p.id ? Object.assign({}, x, { status: cocok }) : x; }),
        'Status ' + p.pemesan + ' menjadi ' + cocok + '.'
      );
    } else {
      bukaForm(p);                              // dblclick di kolom lain: buka form ubah
    }
  });

  /* ---------- Hapus pesanan ---------- */
  $('btnHapusBatal').addEventListener('click', function () { dlgHapus.close(); });
  $('btnHapusYa').addEventListener('click', async function () {
    const ok = await terapkan(data.filter(function (x) { return x.id !== idHapus; }), 'Pesanan dihapus.');
    if (ok) dlgHapus.close();
  });

  /* ---------- EVENT keyup & change: cari dan filter ---------- */
  $('cari').addEventListener('keyup', render);
  $('filterStatus').addEventListener('change', render);

  /* ---------- EVENT keydown: tekan "/" untuk langsung mencari ---------- */
  document.addEventListener('keydown', function (e) {
    const sedangMengetik = /^(INPUT|TEXTAREA|SELECT)$/.test(document.activeElement.tagName);
    if (e.key === '/' && !sedangMengetik && !dlgForm.open && !dlgHapus.open) {
      e.preventDefault();                       // cegah karakter "/" ikut terketik
      $('cari').focus();
    }
  });

  /* ---------- ASYNC/AWAIT saat halaman dimuat ---------- */
  async function mulai() {
    const tr = document.createElement('tr');
    const td = sel('Memuat data pesanan...');
    td.colSpan = 6;
    tr.appendChild(td);
    tbody.appendChild(tr);
    try {
      data = await muatDariStorage();
    } catch (err) {
      data = [];
      alert('Gagal memuat data: ' + err.message);
    }
    render();
  }
  window.addEventListener('load', mulai);
})();
