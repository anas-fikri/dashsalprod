# Dashboard Penggajian Industri

Repositori ini menampung dokumentasi awal untuk aplikasi dashboard penggajian industri berbasis upload Excel. Fokusnya bukan menghitung payroll, tetapi mengolah data gaji final agar bisa dibandingkan dengan kinerja operasional bulanan perusahaan.

Fokus aplikasi:
- menampilkan total gaji `production` per departemen
- menampilkan total gaji `non-production` per departemen
- menghubungkan beban manpower dengan `nominal_penjualan` dan `biaya_produksi` per bulan
- menyediakan dashboard tren bulanan untuk membandingkan biaya produksi, penjualan, dan manpower
- menyediakan dashboard dan laporan untuk kebutuhan internal perusahaan dan review Kementerian Perindustrian
- membedakan akses `admin` dan `operator/user`

Ruang lingkup saat ini:
- tidak menghitung payroll
- tidak mengelola absensi
- tidak menghasilkan slip gaji
- hanya mengolah data gaji final yang di-upload user
- hanya memakai data operasional bulanan tingkat agregat untuk penjualan dan produksi

Dokumen awal yang tersedia:
- [PRD mini](./docs/prd-mini.md)
- [Flow diagram](./docs/flow-diagram.md)
- [Arsitektur MVP](./docs/architecture-mvp.md)
- [Stack dan Docker MVP](./docs/stack-and-docker-mvp.md)
- [Template Dashboard MVP](./docs/dashboard-template-mvp.md)
- [Template Excel Payroll MVP](./docs/template-excel-mvp.md)
- [Template Excel Operasional Bulanan MVP](./docs/template-operational-metrics-mvp.md)
- [Struktur modul MVP](./docs/module-structure.md)
- [Data dictionary](./docs/data-dictionary.md)
- [Mockup UI perbandingan bulanan](./docs/mockup-monthly-comparison.html)

Istilah utama:
- `production`: posisi yang berkaitan langsung dengan proses produksi pada pabrik manufaktur
- `non-production`: posisi pendukung yang tidak terlibat langsung pada proses produksi
- `manpower cost`: total beban gaji final yang berasal dari data payroll periode tertentu
- `operational metrics`: ringkasan bulanan yang memuat penjualan, volume produksi, dan biaya produksi

Tujuan MVP:
- menerima upload file Excel payroll
- menerima upload atau input data operasional bulanan
- memvalidasi data
- menyimpan histori import per periode
- menampilkan dashboard agregat per kategori, departemen, dan bulan
- membandingkan `total_gaji`, `nominal_penjualan`, dan `biaya_produksi` antar bulan
- menghasilkan kesimpulan dan bahan analisa berbasis AI
- menyediakan konfigurasi integrasi AI platform lokal
- mengekspor data dan dashboard untuk kebutuhan pelaporan

Role MVP:
- `admin`: dapat login, import data payroll, import data operasional, melihat histori upload, melihat dashboard dan laporan, serta melakukan export
- `operator/user`: dapat login dan hanya melihat dashboard serta laporan

## Akses Halaman Lokal (localhost)

Jalankan aplikasi Laravel di terminal dari root project:

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

Lalu buka halaman:
- http://127.0.0.1:8000
- http://localhost:8000

Catatan:
- jika muncul status `302`, itu normal (redirect ke halaman login atau route default aplikasi).
- pastikan port `8000` tidak dipakai aplikasi lain.

## Akses Halaman via ngrok

Setelah localhost aktif, buka terminal baru dan jalankan:

```bash
ngrok http http://127.0.0.1:8000
```

Alternatif sekali jalan (start localhost + ngrok sekaligus):

```bash
./scripts/start_local_ngrok.sh
```

Opsional ganti port:

```bash
./scripts/start_local_ngrok.sh 8001
```

Ambil URL publik dari output ngrok, contoh:
- https://xxxx-xxxx-xxxx.ngrok-free.dev

Atau cek URL tunnel aktif lewat API lokal ngrok:

```bash
curl http://127.0.0.1:4040/api/tunnels
```

## Troubleshooting ngrok Tidak Bisa Diakses

1. Cek server Laravel sudah jalan:

```bash
curl -I http://127.0.0.1:8000
```

2. Cek ngrok sudah aktif dan ada tunnel:

```bash
curl http://127.0.0.1:4040/api/tunnels
```

3. Validasi konfigurasi ngrok:

```bash
ngrok config check
```

4. Jika masih gagal, restart dua proses ini berurutan:
- hentikan `php artisan serve`
- hentikan `ngrok`
- jalankan ulang Laravel di port `8000`
- jalankan ulang `ngrok http http://127.0.0.1:8000`

5. Jika URL ngrok ganti setelah restart, gunakan URL terbaru dari output ngrok.
