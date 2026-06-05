# Template Excel Operasional Bulanan MVP - Dashboard Penggajian Industri

Dokumen ini menjelaskan struktur data dan templat Excel untuk mengimpor data **Metrik Operasional Bulanan**.

## 1. Format File Excel
Metrik operasional diunggah menggunakan format file Excel sederhana (`.xlsx`) dengan struktur baris dan kolom yang teratur.

### Kolom yang Diperlukan:
1. **`A (Periode)`**: Format teks `YYYY-MM` (contoh: `2026-03` untuk Maret 2026).
2. **`B (Nominal Penjualan)`**: Nilai penjualan bruto perusahaan pada bulan tersebut (dalam Rupiah).
3. **`C (Volume Produksi)`**: Total unit barang yang berhasil diproduksi (misalnya dalam pcs atau ton).
4. **`D (Biaya Produksi)`**: Total biaya yang dikeluarkan oleh pabrik untuk aktivitas produksi (tidak termasuk biaya penjualan/admin).

---

## 2. Contoh Data Tabel

| Periode (A) | Nominal Penjualan (B) | Volume Produksi (C) | Biaya Produksi (D) |
|-------------|-----------------------|---------------------|--------------------|
| 2026-01     | 12500000000           | 150000              | 8500000000         |
| 2026-02     | 13200000000           | 165000              | 9000000000         |
| 2026-03     | 14000000000           | 170000              | 9200000000         |

---

## 3. Logika Validasi Sistem
Saat admin mengunggah metrik operasional bulanan:
* Sistem harus memverifikasi bahwa kolom **Periode** berformat valid `YYYY-MM`.
* Kolom angka penjualan, volume, dan biaya harus berupa bilangan bulat positif (numeric).
* Jika ada data ganda (duplicate) untuk periode yang sama, sistem harus menanyakan kepada admin apakah ingin menimpa (overwrite) atau mengabaikan data baru tersebut.

