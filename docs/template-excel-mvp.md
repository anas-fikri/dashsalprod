# Panduan Template Excel MVP - Dashboard Penggajian Industri

Dokumen ini menjelaskan struktur file Excel yang digunakan dalam sistem, serta bagaimana data di dalamnya diolah untuk validasi dan konsistensi.

## 1. Struktur File Payroll (`GAJI NES ALL DEPT MAR 2026 - Anas edited joined.xlsx`)
File ini merupakan sumber utama data transaksi gaji karyawan per bulan. Format yang didukung adalah format tabel datar (flat table) pada sheet `TEMP`.

### Kolom Penting:
* **`A (No)`**: Nomor urut.
* **`B (Kode Perushaaan)`**: Kode entitas hukum (`NES` atau `NPA`).
* **`C (Month)`**: Tanggal dalam format serial Excel (contoh: `46082` untuk April 2026).
* **`J (Struktural)`**: Klasifikasi tingkatan (`STR1`, `STR2`, `NSTR`).
* **`K (NIK)`**: Nomor Induk Karyawan unik.
* **`L (Nama)`**: Nama karyawan.
* **`O (GajiPokok)`**, **`AF (GajiBruto)`**, **`AW (GajiDiterima)`**: Nilai keuangan utama.

---

## 2. Struktur Database Roster Karyawan (`3 LIST KARYAWAN Maret 2026.xlsx`)
File ini digunakan untuk keperluan pencocokan data (reconcile) status kepegawaian aktif.
* **Sheet `STR1`**: Daftar karyawan kelompok struktural tingkat 1 (Kadept, Manager).
* **Sheet `STR2`**: Daftar karyawan kelompok struktural tingkat 2 (Kabag, Karu, PM).
* **Sheet `NSTR`**: Karyawan pelaksana (Operator, Welder, Teknisi, dll.).
* **Sheet `PKL` / `OS` / `HARIAN`**: Karyawan magang, outsourcing, atau harian.

Sistem dapat mencocokkan apakah NIK dari file Payroll terdaftar di roster aktif ini untuk memverifikasi validitas data karyawan.

---

## 3. File Rekap Agregat (`3. Rekap Pembayaran Gaji Karyawan 2026 - Maret.xlsx`)
File ini berisi ringkasan agregat nilai payroll bulanan per departemen untuk entitas `NES` dan `NPA`.
* **Sheet `Rekap All 2026`**: Akumulasi biaya SDM bulanan seluruh entitas.
* **Sheet `NES`**: Biaya per departemen khusus untuk PT Nobi Elektrika Sejahtera.
* **Sheet `NPA`**: Biaya per departemen khusus untuk PT Nobi Putra Angkasa.

### Logika Rekonsiliasi (Reconciliation Logic):
Saat admin mengunggah file payroll (misalnya untuk entitas `NES` bulan Maret 2026), sistem harus menghitung total `GajiDiterima` untuk seluruh karyawan `NES` dan mencocokkannya dengan nilai pada sheet `NES` kolom `Maret` baris total. 
* Jika selisih antara data detail yang diunggah dengan rekap agregat > 0, sistem harus memunculkan peringatan (warning) rekonsiliasi agar admin memverifikasi ulang file Excel tersebut.

---

## 4. Konversi Tanggal Serial Excel di PHP/Laravel
Excel menyimpan tanggal sebagai jumlah hari sejak `1 Januari 1900`. 
Di Laravel, Anda dapat menggunakan helper atau package PhpSpreadsheet untuk mengonversinya secara otomatis, atau menggunakan rumus manual di PHP:

```php
// Contoh konversi serial number Excel ke format YYYY-MM
public function convertExcelDate($serial) {
    if (is_numeric($serial)) {
        // Excel menganggap tahun 1900 sebagai tahun kabisat (bug sejarah), 
        // jadi kita kurangi 1 hari jika tanggal setelah 1 Maret 1900.
        $utcDays = $serial - 25569;
        $timestamp = $utcDays * 86400;
        return date('Y-m', $timestamp);
    }
    return null;
}
```

