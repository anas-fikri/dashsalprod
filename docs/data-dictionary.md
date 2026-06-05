# Data Dictionary - Dashboard Penggajian Industri

Dokumen ini mendefinisikan struktur kolom dari file import Excel payroll (`GAJI NES ALL DEPT MAR 2026 - Anas edited joined.xlsx` - Sheet `TEMP`) dan bagaimana data tersebut dipetakan ke tabel `payroll_data`.

## Pemetaan Kolom Excel ke Database

| No | Kolom Excel (Sheet TEMP) | Tipe Data | Deskripsi / Contoh Nilai | Pemetaan Kolom DB (`payroll_data`) | Catatan Validasi |
|----|--------------------------|-----------|--------------------------|-------------------------------------|------------------|
| 1  | `Kode Perushaaan`        | String    | `NES`, `NPA`             | `company_code`                      | Wajib diisi. |
| 2  | `Month`                  | Integer   | `46082` (Excel Serial)   | *Diolah menjadi Periode YYYY-MM*    | Excel serial `46082` mewakili bulan April 2026. Konversi ke tanggal riil diperlukan. |
| 3  | `GRUP`                   | String    | `0100 - Struktural 1`    | `group_name`                        | Berisi nama grup struktural atau fungsional. |
| 4  | `Kode group`             | String    | `0100`                   | `group_code`                        | Kode unik grup. |
| 5  | `Kode SubDepartemen`     | String    | `140000`                 | `sub_department_code` (opsional)    | Kode departemen internal. |
| 6  | `SubDepartemen`          | String    | `Marketing`, `Elektrikal`| `sub_department`                    | Digunakan untuk membedakan fungsi produksi vs non-produksi. |
| 7  | `Kode Jabatan`           | String    | `440`                    | `job_title_code` (opsional)         | Kode unik untuk jabatan. |
| 8  | `Jabatan`                | String    | `Kadept. Marketing`      | `job_title`                         | Nama posisi/jabatan karyawan. |
| 9  | `Struktural`             | String    | `STR1`, `STR2`, `NSTR`   | `structural_status`                 | Harus divalidasi agar hanya berisi salah satu dari tiga nilai ini. |
| 10 | `NIK`                    | String    | `193080129`              | `nik`                               | Kunci unik karyawan (Primary Key logis per periode). |
| 11 | `Nama`                   | String    | `Akhmad Hisyam`          | `nama`                              | Nama lengkap karyawan. |
| 12 | `GajiPokok`              | Decimal   | `5000000`                | `basic_salary`                      | Nominal gaji pokok sebelum tunjangan. |
| 13 | `GajiBruto`              | Decimal   | `6500000`                | `gross_salary`                      | Total gaji kotor (gaji pokok + tunjangan). |
| 14 | `GajiDiterima`           | Decimal   | `5800000`                | `net_salary`                        | Gaji bersih setelah potongan (take-home pay). |

## Logika Pemetaan Kategori Produksi (`is_production`)
Untuk menentukan apakah karyawan masuk dalam kategori `production` atau `non-production` (`is_production` = true / false), sistem menggunakan pemetaan berbasis `sub_department` atau `job_title`:
* **is_production = true**: Jika `sub_department` bernilai `Pabrikasi`, `Elektrikal` (untuk posisi lapangan), `Logistik Pabrik`, `Produksi`, `Maintenance`, dll.
* **is_production = false**: Jika `sub_department` bernilai `Marketing`, `Finance`, `Accounting`, `HRD`, `IT`, `GA` (General Affairs).

*Aturan pemetaan ini perlu di-hardcode dalam class service Laravel (misalnya `App\Services\PayrollParserService`) atau dibuat dinamis melalui tabel konfigurasi.*

