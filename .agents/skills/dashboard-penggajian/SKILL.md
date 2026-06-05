---
name: dashboard-penggajian-industri
description: Operations and development skill for the Dashboard Penggajian Industri (Laravel + Vue 3 + Inertia + Tailwind CSS v4) project. Helps agents parse Excel payroll files, setup database schema, and build trends comparisons.
version: 1.0.0
author: Antigravity
tags:
  - laravel
  - inertia
  - vue3
  - excel-processing
  - business-intelligence
---

# Dashboard Penggajian Industri - Agent Skill

Use this skill to quickly bootstrap context and understand the rules, database schemas, Excel structures, and setup commands for the **Dashboard Penggajian Industri** project.

---

## 🎯 Project Overview & Scope
This project is a **Laravel + Inertia + Vue 3** web application designed to compare manpower cost (payroll) with monthly operational performance (sales, production cost, and production volume).

* **Primary Focus**: Agregasi beban manpower, perbandingan efisiensi tenaga kerja terhadap penjualan/biaya produksi, dan dashboard tren bulanan.
* **Out of Scope**: No payroll calculation, no payslip generation, and no attendance management. The system only processes final payroll data uploaded via Excel.

---

## 🏗️ Technical Stack
* **Backend**: Laravel 13 (PHP ^8.3), SQLite (database/database.sqlite)
* **Frontend**: Vue 3 (Composition API), Inertia.js (routing via Laravel web.php)
* **Styling**: Tailwind CSS v4 (integrated via `@tailwindcss/vite`)
* **Libraries**: `phpoffice/phpspreadsheet` (Excel parsing), Chart.js (visualizations), `spatie/laravel-data` (DTOs)

---

## 📁 Key Documentation References
When working on this workspace, refer to these documents:
* [PRD Mini](file:///Users/anasfikri/Documents/Projects/others/dashboard-penggajian-industri/docs/prd-mini.md): Core requirements, user flows, and target features.
* [Architecture MVP](file:///Users/anasfikri/Documents/Projects/others/dashboard-penggajian-industri/docs/architecture-mvp.md): System components, database ERD, and classification definitions.
* [Data Dictionary](file:///Users/anasfikri/Documents/Projects/others/dashboard-penggajian-industri/docs/data-dictionary.md): Mapping columns from Excel payroll to database tables.
* [Template Excel Guide](file:///Users/anasfikri/Documents/Projects/others/dashboard-penggajian-industri/docs/template-excel-mvp.md): Columns format, date conversion formula, and reconciliation logic.
* [Flow Diagram](file:///Users/anasfikri/Documents/Projects/others/dashboard-penggajian-industri/docs/flow-diagram.md): Sequence diagram of imports, validation, and AI analysis.
* [Module Structure](file:///Users/anasfikri/Documents/Projects/others/dashboard-penggajian-industri/docs/module-structure.md): Controller, Model, and Vue Page mappings.

---

## 📊 Excel Payroll Data Dictionary (`GAJI NES ALL DEPT`)
The system parses sheet `TEMP` in the payroll Excel files. Below are the key mappings:

| Excel Column | Data Type | Database Field | Explanation |
|--------------|-----------|----------------|-------------|
| `B (Kode Perushaaan)` | String | `company_code` | `NES` or `NPA`. |
| `C (Month)` | Integer | *Parsed as YYYY-MM* | Excel date serial number (e.g. `46082` representing April 2026). |
| `J (Struktural)` | String | `structural_status` | Must be `STR1` (senior), `STR2` (mid), or `NSTR` (non-structural). |
| `K (NIK)` | String | `nik` | Employee Unique ID. |
| `L (Nama)` | String | `nama` | Full Name. |
| `O (GajiPokok)` | Decimal | `basic_salary` | Basic wage before allowances. |
| `AF (GajiBruto)` | Decimal | `gross_salary` | Gross monthly wage. |
| `AW (GajiDiterima)` | Decimal | `net_salary` | Take-home pay. |

### 🛠️ Date Parsing in PHP
Excel dates are serial numbers representing the count of days since Jan 1, 1900. Parse it as:
```php
$timestamp = ($excelSerial - 25569) * 86400;
$period = date('Y-m', $timestamp);
```

### ⚙️ Production vs Non-Production Logic
Classify `is_production` boolean based on the `sub_department` value:
* **`is_production = true`**: SubDepartemen is `Pabrikasi`, `Elektrikal` (field positions), `Logistik Pabrik`, `Produksi`, or `Maintenance`.
* **`is_production = false`**: SubDepartemen is `Marketing`, `Finance`, `Accounting`, `HRD`, `IT`, or `GA`.

---

## ⚖️ Reconciliation & Verification Rules
1. **Salary Totals Reconciliation**:
   * When importing a payroll file (e.g., entity `NES` for month `2026-03`), calculate `SUM(GajiDiterima)`.
   * Cross-reference this sum with the monthly total on sheet `NES` of `3. Rekap Pembayaran Gaji Karyawan 2026 - Maret.xlsx`.
   * Flag any discrepancies to the user before committing to the DB.
2. **Roster Match Validation**:
   * Validate that the imported NIK is present in the `3 LIST KARYAWAN Maret 2026.xlsx` active roster sheets (`STR1`, `STR2`, `NSTR`).

---

## 🚀 Common Commands for Agent Execution

### Local Setup
Ensure PHP and Node.js are active, then run:
```bash
# Install PHP dependencies
composer install

# Install JS dependencies
npm install

# Initialize local SQLite database
touch database/database.sqlite
php artisan migrate --force

# Generate application key
php artisan key:generate
```

### Running Server
Always start both servers to allow Inertia's Hot Reload to work:
```bash
# Run PHP server (default on port 8000)
php artisan serve

# Run Vite dev server
npm run dev
```

---

## 🤖 Guidelines for AI Agents Coding on this Project
* **Controller Integration**: Ensure all routes return Inertia pages using `Inertia::render('PageName', [...])`.
* **Database Queries**: Optimize queries on `payroll_data` because Excel files contain hundreds of employee rows per period. Use batch inserts.
* **Responsive Styling**: Always use Tailwind v4 grid structures. Follow the premium dark UI aesthetic illustrated in [Mockup HTML](file:///Users/anasfikri/Documents/Projects/others/dashboard-penggajian-industri/docs/mockup-monthly-comparison.html).

