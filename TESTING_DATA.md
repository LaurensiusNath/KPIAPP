# Testing Data Setup - KPI App

## 📋 Overview

Seeder ini membuat data testing yang lengkap untuk KPI App dengan struktur organisasi yang realistis.

## 🎯 Data yang Dibuat

### Users (Total: 26)

- **1 Admin**: admin@kpiapp.test
- **5 Team Leaders**: tl1@kpiapp.test - tl5@kpiapp.test
- **20 Staff**: staff1@kpiapp.test - staff20@kpiapp.test (4 staff per divisi)

### Divisions (5 Divisi)

1. IT & Technology
2. Marketing & Sales
3. Human Resources
4. Finance & Accounting
5. Operations & Logistics

### Period

- **Semester 2, 2025** (Active)
- Months: 7-11 (July - November)

### KPIs

Setiap staff memiliki 5 KPI:

1. Kualitas Pekerjaan (25%)
2. Produktivitas (25%)
3. Kolaborasi Tim (20%)
4. Inisiatif & Inovasi (15%)
5. Ketepatan Waktu (15%)

### KPI Values

- Setiap KPI memiliki nilai untuk bulan 7-11
- Score random antara 3-5 (realistic)
- Semua sudah di-submit (is_submitted = true)
- Dilengkapi dengan catatan evaluasi

### Appraisals

- Setiap staff memiliki 1 appraisal
- Berbagai status untuk testing:
    - **25% (5 staff)**: Pending TL (belum ada submit)
    - **25% (5 staff)**: Pending HRD (TL sudah submit, HRD belum)
    - **25% (5 staff)**: Submitted HRD (keduanya sudah submit, belum finalized)
    - **25% (5 staff)**: Finalized (complete)

## 🚀 Cara Menggunakan

### Opsi 1: Fresh Install (Recommended untuk testing)

```bash
php artisan kpi:setup-testing --fresh
```

⚠️ **WARNING**: Ini akan menghapus SEMUA data existing!

### Opsi 2: Tambah ke Database Existing

```bash
php artisan db:seed --class=TestingSeeder
```

## 🔑 Login Credentials

**Password untuk semua akun**: `password`

### Admin

- Email: admin@kpiapp.test
- Password: password

### Team Leaders

- tl1@kpiapp.test (IT & Technology)
- tl2@kpiapp.test (Marketing & Sales)
- tl3@kpiapp.test (Human Resources)
- tl4@kpiapp.test (Finance & Accounting)
- tl5@kpiapp.test (Operations & Logistics)
- Password: password

### Staff (Examples)

- staff1@kpiapp.test (IT & Technology)
- staff5@kpiapp.test (Marketing & Sales)
- staff9@kpiapp.test (Human Resources)
- staff13@kpiapp.test (Finance & Accounting)
- staff17@kpiapp.test (Operations & Logistics)
- Password: password

## 🧪 Skenario Testing yang Dapat Dilakukan

### 1. Admin Dashboard

- Login sebagai admin@kpiapp.test
- Lihat overview semua divisi
- Cek analytics per divisi
- Review appraisals yang pending

### 2. Team Leader Dashboard

- Login sebagai tl1@kpiapp.test (atau TL lainnya)
- Lihat dashboard divisi
- Review KPI values staff
- Submit appraisal yang pending
- Download reports

### 3. User Dashboard

- Login sebagai staff1@kpiapp.test (atau staff lainnya)
- Lihat KPI pribadi
- Cek trend bulanan
- Download laporan analytics

### 4. Testing Appraisal Workflow

```
staff1  → Pending TL      → TL belum submit
staff2  → Pending TL      → TL belum submit
staff3  → Pending TL      → TL belum submit
staff4  → Pending TL      → TL belum submit
staff5  → Pending TL      → TL belum submit
staff6  → Pending HRD     → TL sudah, HRD belum
staff7  → Pending HRD     → TL sudah, HRD belum
staff8  → Pending HRD     → TL sudah, HRD belum
staff9  → Pending HRD     → TL sudah, HRD belum
staff10 → Pending HRD     → TL sudah, HRD belum
staff11 → Finalized       → Keduanya sudah submit
staff12 → Finalized       → Keduanya sudah submit
staff13 → Finalized       → Keduanya sudah submit
staff14 → Finalized       → Keduanya sudah submit
staff15 → Finalized       → Keduanya sudah submit
staff16 → Finalized       → Complete
staff17 → Finalized       → Complete
staff18 → Finalized       → Complete
staff19 → Finalized       → Complete
staff20 → Finalized       → Complete
```

### 5. Testing KPI Management

- Edit KPI items untuk staff
- Isi nilai KPI bulanan
- Lihat chart performance
- Export PDF reports

## 📊 Database Statistics

Setelah seeding, database akan berisi:

- **Users**: 26
- **Divisions**: 5
- **Periods**: 1 (active)
- **KPIs**: 100 (20 staff × 5 KPI)
- **KPI Values**: 500 (100 KPI × 5 months)
- **Appraisals**: 20 (1 per staff)

## 🔄 Reset Testing Data

Untuk reset dan setup ulang:

```bash
php artisan kpi:setup-testing --fresh
```

## 📝 Notes

1. Semua password di-encrypt dengan `Crypt::encryptString()` sesuai custom auth provider
2. Data KPI values sudah realistic dengan score 3-5
3. Appraisal status bervariasi untuk testing berbagai workflow
4. Setiap divisi memiliki team leader dan 4 staff
5. Period Semester 2, 2025 aktif dengan data bulan 7-11

## 🐛 Troubleshooting

### Error: Foreign Key Constraint

Pastikan menjalankan dengan `--fresh` flag untuk clean slate:

```bash
php artisan kpi:setup-testing --fresh
```

### Periode Tidak Aktif

Cek table periods, pastikan ada 1 record dengan `is_active = true`

### User Tidak Bisa Login

Password untuk semua akun adalah `password` (plain text, sistem akan encrypt otomatis)
