# Flow Diagram - Dashboard Penggajian Industri

Dokumen ini memetakan diagram alur pemrosesan data payroll dan metrik operasional pada aplikasi.

```mermaid
sequenceDiagram
    actor Admin
    participant System as Sistem Dashboard (Laravel)
    database DB as Database (SQLite)
    participant AI as Local AI Service

    Admin->>System: Login ke Sistem
    System-->>Admin: Dashboard Utama
    
    rect rgb(240, 240, 240)
        note right of Admin: Alur Upload Payroll
        Admin->>System: Upload GAJI_NES_MAR_2026.xlsx (Sheet TEMP)
        System->>System: Parse data via PhpSpreadsheet
        System->>System: Jalankan validasi kolom & konversi tanggal serial
        System->>System: Hitung total pengeluaran payroll & petakan is_production
        System-->>Admin: Tampilkan preview data & status rekonsiliasi
        Admin->>System: Konfirmasi Simpan
        System->>DB: Simpan ke `import_histories` & `payroll_data`
        System-->>Admin: Pesan Berhasil
    end

    rect rgb(240, 240, 240)
        note right of Admin: Alur Input Metrik Operasional
        Admin->>System: Input Metrik Bulanan (Penjualan, Volume, Biaya)
        System->>DB: Simpan ke `operational_metrics` untuk periode terkait
        System-->>Admin: Pesan Berhasil
    end

    rect rgb(245, 245, 255)
        note right of Admin: Alur Analisis & Laporan
        Admin->>System: Buka Dashboard Perbandingan Bulanan
        System->>DB: Query total manpower cost vs operational metrics
        System->>System: Generate grafik tren bulanan
        System->>AI: Kirim data tabel ringkasan bulanan untuk analisis
        AI-->>System: Kembalikan kesimpulan & rekomendasi AI
        System-->>Admin: Tampilkan dashboard lengkap dengan AI Insights
    end
