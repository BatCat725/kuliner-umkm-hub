/* =====================================================================
   produk.js — Halaman Produk Saya
   PopUp Box : confirm() (hapus), prompt() + alert() (ubah stok cepat)
   Event     : click, dblclick, submit, input, change, keydown
   Web Storage: localStorage (data produk)
   Promise + async/await: simpanKeStorage()
   ===================================================================== */
(function () {
  'use strict';

  const $ = function (id) { return document.getElementById(id); };
  const KUNCI = UMKM.KUNCI.produk;
  const BATAS_MENIPIS = 5;      // stok 1..5 = Menipis, 0 = Habis
  let data = [];

  const tbody = $('tbodyProduk');

  /* ---------- Data awal diambil dari baris tabel HTML (sekali saja) ---------- */
  function bacaDariHTML() {
    return Array.prototype.map.call(tbody.querySelectorAll('tr[data-id]'), function (tr) {
      const td = tr.children;
      return {
        id: tr.dataset.id,
        nama: td[0].textContent.trim(),
        kategori: td[1].textContent.trim(),
        harga: Number(td[2].textContent.replace(/\D/g, '')),   // "Rp15.000" -> 15000
        stok: Number(td[3].textContent.replace(/\D/g, ''))
      };
    });
  }

  function muat() {
    const tersimpan = UMKM.baca(KUNCI, null);
    if (Array.isArray(tersimpan)) return tersimpan;
    const awal = bacaDariHTML();
    try { UMKM.simpan(KUNCI, awal); } catch (e) { /* abaikan */ }
    return awal;
  }

  /* ---------- PROMISE: simpan ke localStorage ---------- */
  function simpanKeStorage(d) {
    return new Promise(function (resolve, reject) {
      setTimeout(function () {
        try { UMKM.simpan(KUNCI, d); resolve(); }
        catch (err) { reject(err); }
      }, 300);
    });
  }

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
  function statusStok(stok) {
    if (stok <= 0) return { kelas: 'cancel', teks: 'Habis' };
    if (stok <= BATAS_MENIPIS) return { kelas: 'pending', teks: 'Menipis' };
    return { kelas: 'done', teks: 'Tersedia' };
  }

  function sel(teks, kelas) {
    const td = document.createElement('td');
    td.textContent = teks;
    if (kelas) td.className = kelas;
    return td;
  }

  function buatBaris(p) {
    const tr = document.createElement('tr');
    tr.dataset.id = p.id;
    tr.appendChild(sel(p.nama));
    tr.appendChild(sel(p.kategori));
    tr.appendChild(sel(UMKM.rupiah(p.harga), 'num'));

    const tdStok = sel(p.stok, 'num');
    tdStok.dataset.kol = 'stok';
    tdStok.title = 'Klik dua kali untuk mengubah stok';
    tr.appendChild(tdStok);

    const st = statusStok(p.stok);
    const tdStatus = document.createElement('td');
    const badge = document.createElement('span');
    badge.className = 'status ' + st.kelas;
    badge.textContent = st.teks;
    tdStatus.appendChild(badge);
    tr.appendChild(tdStatus);

    // Tautan #ubah / #hapus tetap dipakai agar modal CSS :target tetap berfungsi
    const tdAksi = document.createElement('td');
    tdAksi.className = 'aksi';
    [['ubah', 'Ubah', 'btn btn-ghost btn-sm'], ['hapus', 'Hapus', 'btn btn-danger btn-sm']].forEach(function (a) {
      const link = document.createElement('a');
      link.href = '#' + a[0];
      link.className = a[2];
      link.dataset.aksi = a[0];
      link.textContent = a[1];
      tdAksi.appendChild(link);
    });
    tr.appendChild(tdAksi);
    return tr;
  }

  function render() {
    const kata = $('cariProduk').value.trim().toLowerCase();
    const kategori = $('filterKategori').value;
    const tampil = data.filter(function (p) {
      return (!kategori || p.kategori === kategori) && (!kata || p.nama.toLowerCase().includes(kata));
    });

    tbody.textContent = '';
    if (tampil.length === 0) {
      const tr = document.createElement('tr');
      const td = sel('Tidak ada produk yang cocok.', 'kosong');
      td.colSpan = 6;
      tr.appendChild(td);
      tbody.appendChild(tr);
    }
    tampil.forEach(function (p) { tbody.appendChild(buatBaris(p)); });

    $('judulProduk').textContent = 'Daftar produk (' + tampil.length + ')';
    $('kTotal').textContent = data.length;
    $('kMenipis').textContent = data.filter(function (p) { return p.stok > 0 && p.stok <= BATAS_MENIPIS; }).length;
    $('kHabis').textContent = data.filter(function (p) { return p.stok <= 0; }).length;
    $('kNilai').textContent = UMKM.rupiah(
      data.reduce(function (jml, p) { return jml + p.harga * p.stok; }, 0)
    );
  }

  function tutupModal() { window.location.hash = ''; }

  function validasi(p) {
    if (p.nama.length < 2) return 'Nama produk minimal 2 karakter.';
    if (!(p.harga >= 0)) return 'Harga tidak boleh negatif.';
    if (!Number.isInteger(p.stok) || p.stok < 0) return 'Stok harus berupa bilangan bulat 0 atau lebih.';
    return '';
  }

  /* ---------- EVENT submit: tambah produk ---------- */
  $('formTambah').addEventListener('submit', async function (e) {
    e.preventDefault();
    const p = {
      id: 'p' + Date.now(),
      nama: $('tNama').value.trim(),
      kategori: $('tKategori').value,
      harga: Number($('tHarga').value),
      stok: parseInt($('tStok').value, 10)
    };
    const err = validasi(p);
    if (err) { alert(err); return; }            // POPUP alert

    if (await terapkan(data.concat([p]), 'Produk "' + p.nama + '" ditambahkan.')) {
      this.reset();
      tutupModal();
    }
  });

  /* ---------- EVENT submit: ubah produk ---------- */
  $('formUbah').addEventListener('submit', async function (e) {
    e.preventDefault();
    const id = this.elements.id.value;
    const p = {
      nama: $('uNama').value.trim(),
      kategori: $('uKategori').value,
      harga: Number($('uHarga').value),
      stok: parseInt($('uStok').value, 10)
    };
    const err = validasi(p);
    if (err) { alert(err); return; }

    const baru = data.map(function (x) { return x.id === id ? Object.assign({}, x, p) : x; });
    if (await terapkan(baru, 'Produk "' + p.nama + '" diperbarui.')) tutupModal();
  });

  /* ---------- EVENT click (delegation): Ubah & Hapus ---------- */
  tbody.addEventListener('click', function (e) {
    const link = e.target.closest('a[data-aksi]');
    if (!link) return;
    const p = data.find(function (x) { return x.id === link.closest('tr').dataset.id; });
    if (!p) return;

    if (link.dataset.aksi === 'ubah') {
      // Isi form ubah dengan data produk; modal dibuka oleh href="#ubah"
      $('formUbah').elements.id.value = p.id;
      $('uNama').value = p.nama;
      $('uKategori').value = p.kategori;
      $('uHarga').value = p.harga;
      $('uStok').value = p.stok;
    } else {
      // Hapus dengan POPUP confirm: modal #hapus tidak dibuka (preventDefault)
      e.preventDefault();
      if (confirm('Hapus produk "' + p.nama + '"?\nTindakan ini tidak bisa dibatalkan.')) {
        terapkan(data.filter(function (x) { return x.id !== p.id; }), 'Produk "' + p.nama + '" dihapus.');
      }
    }
  });

  /* ---------- EVENT dblclick + POPUP prompt/alert: ubah stok cepat ---------- */
  tbody.addEventListener('dblclick', function (e) {
    const td = e.target.closest('td[data-kol="stok"]');
    if (!td) return;
    const p = data.find(function (x) { return x.id === td.parentNode.dataset.id; });
    if (!p) return;

    const isian = prompt('Stok baru untuk "' + p.nama + '":', p.stok);
    if (isian === null) return;                 // Cancel
    const stok = Number(isian.trim());
    if (isian.trim() === '' || !Number.isInteger(stok) || stok < 0) {
      alert('Stok harus berupa bilangan bulat 0 atau lebih.');
      return;
    }
    terapkan(
      data.map(function (x) { return x.id === p.id ? Object.assign({}, x, { stok: stok }) : x; }),
      'Stok "' + p.nama + '" menjadi ' + stok + '.'
    );
  });

  /* ---------- EVENT input & change & submit: cari dan filter ---------- */
  $('cariProduk').addEventListener('input', render);
  $('filterKategori').addEventListener('change', render);
  $('formFilter').addEventListener('submit', function (e) { e.preventDefault(); render(); });

  /* ---------- EVENT keydown: Esc menutup modal ---------- */
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && window.location.hash) tutupModal();
  });

  /* ---------- Mulai ---------- */
  data = muat();
  render();
})();
