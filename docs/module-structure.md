# Struktur Modul MVP - Dashboard Penggajian Industri

Dokumen ini memetakan struktur file dan folder untuk aplikasi Dashboard Penggajian Industri berbasis Laravel + Inertia.js (Vue 3).

## 1. File & Folder Penting Backend (Laravel)

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php            # Menangani login/logout admin & user
│   │   ├── DashboardController.php       # Menghitung agregasi & data chart bulanan
│   │   ├── PayrollImportController.php   # Endpoint unggah excel & simpan data payroll
│   │   └── OperationalMetricController.php # Input & edit metrik operasional bulanan
│   └── Middleware/
│       └── HandleInertiaRequests.php     # Membagikan data global (auth/flash) ke Vue 3
├── Models/
│   ├── User.php                          # Model untuk tabel users (Role: admin, operator)
│   ├── ImportHistory.php                 # Model histori impor data Excel/Input
│   ├── PayrollData.php                   # Model detail baris payroll
│   └── OperationalMetric.php             # Model metrik penjualan/produksi bulanan
└── Services/
    ├── ExcelParserService.php            # Service class pembaca Excel via PhpSpreadsheet
    └── ManpowerAnalysisService.php       # Service class kalkulator korelasi manpower vs penjualan
```

---

## 2. Struktur Modul Frontend (Vue 3 + Inertia)

Semua halaman frontend terletak di folder `resources/` dan menyatu dalam routing Laravel (melalui view bridge Inertia).

```
resources/
├── css/
│   └── app.css                           # Setup import Tailwind CSS v4
├── js/
│   ├── app.js                            # Inisialisasi Inertia App & Vue Instance
│   ├── Components/
│   │   ├── Sidebar.vue                   # Navigasi utama
│   │   ├── ChartMonthlyComparison.vue    # Visualisasi Chart.js tren bulanan
│   │   └── SummaryWidget.vue             # Card ringkasan total biaya / rasio
│   └── Pages/
│       ├── Auth/
│       │   └── Login.vue                 # Halaman login modern
│       ├── Dashboard/
│       │   └── Index.vue                 # Halaman utama tren & AI Insight
│       ├── Import/
│       │   ├── Upload.vue                # UI unggah Excel payroll & metrik
│       │   └── Preview.vue               # UI validasi & review data hasil parse
│       └── History/
│           └── Index.vue                 # Histori upload & log aktivitas
```

---

## 3. Alur Routing (Web.php)

File `routes/web.php` mengelola hak akses berbasis Inertia route:

```php
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController.class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController.class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController.class, 'logout']);
    Route::get('/', [DashboardController.class, 'index'])->name('dashboard');

    // Hak akses admin saja
    Route::middleware('role:admin')->group(function () {
        Route::get('/import', [PayrollImportController.class, 'showForm']);
        Route::post('/import/payroll', [PayrollImportController.class, 'importPayroll']);
        Route::post('/import/operational', [OperationalMetricController.class, 'store']);
        Route::get('/history', [PayrollImportController.class, 'history']);
    });
});
```

