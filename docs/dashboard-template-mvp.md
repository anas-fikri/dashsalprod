# Template Dashboard MVP - Dashboard Penggajian Industri

Dokumen ini memetakan tata letak (layout) dan visualisasi data yang disajikan pada dashboard utama aplikasi.

## 1. Kartu Indikator Kinerja Utama (KPI Cards)
Di bagian paling atas, dashboard menampilkan 4 kartu status ringkasan bulanan (berdasarkan periode yang dipilih):
1. **Total Manpower Cost**: Akumulasi seluruh gaji karyawan (`GajiDiterima`) untuk bulan tersebut.
2. **Rasio Manpower vs Penjualan**: `Total Manpower Cost / Nominal Penjualan` (Target: < 15%).
3. **Rasio Manpower vs Biaya Produksi**: `Total Gaji Karyawan Produksi / Total Biaya Produksi`.
4. **Produktivitas Manpower**: `Nominal Penjualan / Jumlah Karyawan`.

---

## 2. Visualisasi Grafik Utama (Charts Area)
Dashboard memuat dua grafik interaktif menggunakan Chart.js:
* **Grafik Tren Perbandingan Bulanan (Combo Chart)**:
  * Batang (Bar): Menunjukkan `Nominal Penjualan` dan `Biaya Produksi` per bulan.
  * Garis (Line) pada sumbu Y sekunder: Menunjukkan `Total Manpower Cost` per bulan.
  * *Tujuan*: Melihat apakah peningkatan manpower cost diikuti oleh pertumbuhan penjualan yang proporsional.
* **Grafik Distribusi Manpower (Doughnut Chart)**:
  * Menampilkan porsi total biaya manpower untuk kategori `Production` vs `Non-Production`.

---

## 3. Panel Ringkasan AI (AI Insights Panel)
Panel ini menampilkan hasil analisis otomatis yang di-generate dari model AI berdasarkan tren data bulanan:
* **Status Efisiensi**: Mengidentifikasi apakah rasio biaya tenaga kerja terhadap penjualan meningkat (indikasi inefisiensi).
* **Rekomendasi Operasional**: Saran berbasis data, misalnya penyesuaian porsi lembur jika biaya produksi naik tajam tetapi volume produksi konstan.

---

## 4. Tabel Rincian Departemen
Tabel statis di bagian bawah menampilkan distribusi gaji per departemen (Marketing, Elektrikal, Pabrikasi, HRD, dll.) beserta jumlah karyawan aktif, total gaji pokok, dan total potongan.

