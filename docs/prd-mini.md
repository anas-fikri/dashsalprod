# Product Requirements Document (PRD) Mini - Dashboard Penggajian Industri

## 1. Latar Belakang & Tujuan
Aplikasi **Dashboard Penggajian Industri** dirancang untuk mengolah data gaji final bulanan perusahaan (berbasis upload Excel) dan membandingkannya dengan metrik operasional bulanan (penjualan, volume produksi, dan biaya produksi). 
Aplikasi ini membantu manajemen dalam melakukan analisis hubungan antara beban manpower (tenaga kerja) dengan kinerja bisnis tingkat agregat, serta menghasilkan laporan untuk kebutuhan internal dan Kementerian Perindustrian.

## 2. Ruang Lingkup (Scope)
### Di dalam Lingkup (In Scope)
* **Upload Excel Payroll**: Mengunggah file Excel data gaji bulanan karyawan untuk diproses dan disimpan historinya.
* **Input Metrik Operasional**: Input data ringkasan bulanan mencakup penjualan, volume produksi, dan biaya produksi.
* **Validasi & Pemrosesan Data**: Memvalidasi data payroll (kolom NIK, Nama, Gaji Pokok, Gaji Bruto, Gaji Diterima) serta memetakan posisi karyawan menjadi kategori `production` atau `non-production`.
* **Visualisasi & Tren**: Dashboard tren bulanan yang membandingkan total biaya manpower dengan penjualan dan biaya produksi.
* **Analisis Berbasis AI**: Fitur ringkasan eksekutif dan rekomendasi berbasis AI menggunakan konfigurasi integrasi platform AI lokal.
* **Manajemen Peran**:
  * `admin`: Upload data payroll, input metrik operasional, melihat dashboard/laporan, melihat histori, ekspor data.
  * `operator/user`: Hanya dapat melihat dashboard dan laporan.

### Di luar Lingkup (Out of Scope)
* Tidak menghitung payroll bulanan (hanya mengolah data gaji final yang di-upload).
* Tidak mengelola absensi karyawan.
* Tidak menghasilkan slip gaji individu.

## 3. Alur Pengguna (User Flow)
1. **Login**: Pengguna masuk menggunakan email dan password.
2. **Import Data (Admin)**: 
   * Unggah file payroll periode tertentu (contoh: Maret 2026).
   * Input atau unggah data metrik operasional bulan yang bersangkutan.
3. **Validasi**: Sistem menampilkan ringkasan data sebelum disimpan ke database.
4. **Analisis Dashboard**:
   * Melihat perbandingan total gaji `production` vs `non-production` per departemen.
   * Melihat grafik korelasi antara manpower cost dengan nominal penjualan dan biaya produksi.
5. **AI Insights & Export**: Admin/user memicu AI untuk memberikan analisis terhadap grafik tren, kemudian mengekspor visualisasi/tabel data ke file PDF/Excel.

