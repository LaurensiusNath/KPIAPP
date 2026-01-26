# 📚 KPI Application - Dokumentasi Lengkap

## 📋 Daftar Isi

1. [Database Structure](#database-structure)
2. [Models](#models)
3. [Services Architecture](#services-architecture)
4. [Service Details](#service-details)

---

## 🗄️ Database Structure

### **Urutan Eksekusi Migration:**

```
1. 0001_01_01_000000_create_users_table.php
2. 0001_01_01_000001_create_cache_table.php
3. 0001_01_01_000002_create_jobs_table.php
4. 2025_11_12_083205_create_divisions_table.php
5. 2025_11_12_091057_add_division_id_to_users_table.php
6. 2025_11_16_133143_create_periods_table.php
7. 2025_11_16_133231_create_kpis_table.php
8. 2025_11_16_133303_create_kpi_values_table.php
9. 2025_11_21_070259_create_appraisals_table.php
```

### **Tabel Utama:**

#### **1. users**

- Primary table untuk autentikasi dan user management
- Kolom: id, name, email, password, role, division_id, remember_token, timestamps
- Role: 'admin', 'team-leader', 'user'
- Relasi: belongsTo Division, hasOne Division (as leader), hasMany KpiValues

#### **2. divisions**

- Tabel untuk organisasi departemen/divisi
- Kolom: id, name (unique), leader_id (FK ke users), timestamps
- Constraint: leader_id RESTRICT ON DELETE
- Relasi: belongsTo User (leader), hasMany Users, hasMany Appraisals

#### **3. periods**

- Tabel untuk periode semester penilaian
- Kolom: id, year, semester (1/2), is_active (boolean), timestamps
- Constraint: unique(year, semester), CHECK semester IN (1,2)
- Relasi: hasMany Kpis, hasMany KpiValues

#### **4. kpis**

- Tabel untuk KPI items yang dimiliki user per periode
- Kolom: id, user_id, period_id, title, weight (decimal 5,2), criteria_scale (JSON), timestamps
- Total weight per user per period harus = 100%
- Relasi: belongsTo User, belongsTo Period, hasMany KpiValues

#### **5. kpi_values**

- Tabel untuk nilai penilaian bulanan
- Kolom: id, kpi_id (RESTRICT), user_id, evaluator_id, division_id, period_id, month (1-12), score (1-5), note, is_submitted, timestamps
- Index: (user_id, period_id, month)
- Relasi: belongsTo Kpi, belongsTo User, belongsTo Evaluator, belongsTo Division, belongsTo Period

#### **6. appraisals**

- Tabel untuk appraisal semester final
- Kolom: id, user_id, evaluator_id, division_id, period_id, final_score, comment_teamleader, comment_hrd, is_finalized, teamleader_submitted_at, hrd_submitted_at, timestamps
- Constraint: unique(user_id, period_id)
- Relasi: belongsTo User, belongsTo Evaluator, belongsTo Division, belongsTo Period

---

## 🎯 Models

### **User Model** (`App\Models\User`)

**Extends:** Authenticatable  
**Traits:** HasFactory, Notifiable

**Fillable:**

```php
['name', 'email', 'password', 'role', 'division_id']
```

**Relationships:**

- `division()` - BelongsTo Division
- `leading()` - HasOne Division (sebagai leader)
- `kpiValues()` - HasMany KpiValue

**Casts:**

- `email_verified_at` => datetime

---

### **Division Model** (`App\Models\Division`)

**Fillable:**

```php
['name', 'leader_id']
```

**Relationships:**

- `leader()` - BelongsTo User (leader_id)
- `users()` - HasMany User (anggota divisi, exclude leader)
- `appraisals()` - HasMany Appraisal

**Business Logic:**

- `users()` otomatis exclude leader_id dari hasil query

---

### **Period Model** (`App\Models\Period`)

**Fillable:**

```php
['year', 'semester', 'is_active']
```

**Relationships:**

- `kpis()` - HasMany Kpi
- `kpiValues()` - HasMany KpiValue

**Casts:**

- `is_active` => boolean

**Business Rules:**

- Hanya 1 period yang `is_active = true` pada satu waktu
- Semester: 1 (Jan-Jun) atau 2 (Jul-Dec)
- Unique constraint: (year, semester)

---

### **Kpi Model** (`App\Models\Kpi`)

**Fillable:**

```php
['user_id', 'period_id', 'title', 'weight', 'criteria_scale']
```

**Relationships:**

- `user()` - BelongsTo User
- `period()` - BelongsTo Period
- `values()` / `kpiValues()` - HasMany KpiValue

**Casts:**

- `weight` => decimal:2
- `criteria_scale` => array (JSON)

**Business Rules:**

- Total weight untuk 1 user dalam 1 period = 100%
- criteria_scale: JSON object dengan key 1-5 untuk deskripsi kriteria

---

### **KpiValue Model** (`App\Models\KpiValue`)

**Fillable:**

```php
['kpi_id', 'user_id', 'evaluator_id', 'period_id', 'division_id',
 'month', 'score', 'note', 'is_submitted']
```

**Relationships:**

- `kpi()` - BelongsTo Kpi (RESTRICT on delete)
- `user()` - BelongsTo User (yang dinilai)
- `evaluator()` - BelongsTo User (team leader penilai)
- `period()` - BelongsTo Period
- `division()` - BelongsTo Division

**Casts:**

- `score` => integer
- `is_submitted` => boolean

**Business Rules:**

- score: 1-5 (sesuai criteria_scale di KPI)
- month: 1-12
- Evaluasi window: tanggal 21-25 setiap bulan

---

### **Appraisal Model** (`App\Models\Appraisal`)

**Fillable:**

```php
['user_id', 'evaluator_id', 'division_id', 'period_id',
 'final_score', 'comment_teamleader', 'comment_hrd',
 'is_finalized', 'teamleader_submitted_at', 'hrd_submitted_at']
```

**Relationships:**

- `user()` - BelongsTo User
- `evaluator()` - BelongsTo User
- `division()` - BelongsTo Division
- `period()` - BelongsTo Period

**Casts:**

- `final_score` => decimal:2
- `is_finalized` => boolean
- `teamleader_submitted_at` => datetime
- `hrd_submitted_at` => datetime

**Business Rules:**

- Unique per (user_id, period_id)
- Finalized ketika TL dan HRD sudah submit

---

## 🏗️ Services Architecture

### **Arsitektur Layer:**

```
┌─────────────────────────────────────────┐
│         Livewire Components             │ ← UI Layer
├─────────────────────────────────────────┤
│         Services (Business Logic)        │
│  ┌──────────────┬──────────────────┐    │
│  │ Core Services│  Role Services   │    │
│  │              │  - Admin/        │    │
│  │              │  - TeamLeader/   │    │
│  │              │  - User/         │    │
│  └──────────────┴──────────────────┘    │
├─────────────────────────────────────────┤
│         Models (Data Layer)             │
├─────────────────────────────────────────┤
│         Database (PostgreSQL)           │
└─────────────────────────────────────────┘
```

### **Service Categories:**

#### **1. Core Services** (Digunakan oleh semua role)

- `UserService` - User CRUD & management
- `DivisionService` - Division CRUD & leader management
- `PeriodService` - Period management & window validation
- `KpiService` - KPI CRUD & weight validation
- `KpiValueService` - Monthly evaluation submission
- `AppraisalService` - Semester appraisal management
- `UserAnalyticsService` - User performance analytics
- `DivisionAnalyticsService` - Division performance analytics

#### **2. Admin Services** (`App\Services\Admin\`)

- `AdminUserService` - User listing dengan filter/sort
- `AdminDivisionService` - Division listing dengan filter
- `AdminPeriodService` - Period management untuk admin
- `AdminDashboardService` - Dashboard data untuk admin
- `AdminAppraisalService` - Appraisal management untuk HRD

#### **3. Team Leader Services** (`App\Services\TeamLeader\`)

- `TeamLeaderKpiItemService` - KPI item management helper
- `TeamLeaderKpiMonthlyEvaluationService` - Monthly eval helper
- `TeamLeaderDashboardService` - Dashboard data untuk TL
- `TeamLeaderAppraisalService` - Appraisal submission untuk TL
- `TeamLeaderAnalyticsContextService` - Analytics context builder

---

## 📖 Service Details

### 🔵 **UserService**

**Location:** `App\Services\UserService`

**Fungsi Utama:**

```php
// User CRUD
createUser(array $userData): void
updateUser(int $userId, array $userData): void
findUserById(int $userId): User
findUserWithDivisionById(int $userId): User
loadDivision(User $user): User

// Leader Assignment
getAvailableLeaders(?int $divisionId = null): Collection
isAvailableLeaderId(int $userId, ?int $divisionId = null): bool
getLeaderByDivision(int $divisionId): ?User

// Division Assignment
getUsersByDivision(int $divisionId): LengthAwarePaginator
getAvailableUsers(): Collection
assignUserToDivision(int $userId, int $divisionId): void
removeUserFromDivision(int $userId): void
```

**Digunakan di:**

- `Admin\Users\Index` - User listing
- `Admin\Users\Create` - Create user
- `Admin\Users\Edit` - Edit user
- `Admin\Divisions\Create` - Pilih leader
- `Admin\Divisions\Edit` - Change leader
- `TeamLeader\Users\Index` - View team members
- Semua dashboard untuk load user data

**Business Rules:**

- Password di-encrypt dengan `Crypt::encryptString()`
- Available leaders: role='user' dan division_id=null atau sama dengan divisi target
- User assignment ke division otomatis update role menjadi 'team-leader' jika dijadikan leader

---

### 🔵 **DivisionService**

**Location:** `App\Services\DivisionService`

**Fungsi Utama:**

```php
getAllDivisions(): Collection
findDivisionById(int $id): Division
createDivision(array $divisionData): void
deleteDivision(int $id): void
changeLeader(int $divisionId, int $newLeaderId): void
```

**Digunakan di:**

- `Admin\Divisions\Index` - Division listing
- `Admin\Divisions\Create` - Create division
- `Admin\Divisions\Edit` - Edit division & change leader
- `Admin\Divisions\Delete` - Delete division

**Business Rules:**

- **createDivision**: Transaction untuk create division + promote leader
    - User dipilih sebagai leader → role jadi 'team-leader'
    - User dipilih sebagai leader → division_id = division.id
- **deleteDivision**: Transaction untuk cleanup
    - Demote leader → role kembali 'user'
    - Set semua user.division_id = null
    - Delete division
- **changeLeader**: Transaction
    - Demote old leader → role 'user'
    - Promote new leader → role 'team-leader' + division_id update

---

### 🔵 **PeriodService**

**Location:** `App\Services\PeriodService`

**Fungsi Utama:**

```php
createPeriod(int $year, int $semester): Period
setActivePeriod(Period $period): Period
setActivePeriodById(int $periodId): Period
getActivePeriod(): ?Period
findById(int $periodId): ?Period
loadKpiCount(Period $period): Period

// Window Validation
isCurrentWindowForKpiCreation(Period $period): bool
isCurrentWindowForAppraisal(Period $period): bool
```

**Digunakan di:**

- `Admin\Periods\Index` - Period listing
- `Admin\Periods\Create` - Create period
- `Admin\Periods\Activate` - Set active period
- `TeamLeader\Users\KpiItems` - Check KPI creation window
- `TeamLeader\Users\MonthlyEvaluation` - Validate eval window
- Semua dashboard - Get active period

**Business Rules:**

- **Unique constraint**: (year, semester) - prevent duplicate
- **Only 1 active**: `setActivePeriod()` deactivate all, activate selected
- **KPI Creation Window**:
    - Semester 1: 1-10 Januari
    - Semester 2: 1-10 Juli
- **Appraisal Window**:
    - Semester 1: 25-31 Juni
    - Semester 2: 25-31 Desember

---

### 🔵 **KpiService**

**Location:** `App\Services\KpiService`

**Constructor Dependencies:**

- `PeriodService` - Window validation
- `DatabaseManager` - Transaction management

**Fungsi Utama:**

```php
// Retrieval
getKpisByUserAndPeriod(User $user, Period $period): Collection
getTotalWeightForUserPeriod(User $user, Period $period): float

// Single KPI Operations
createKpi(array $data, User $actor): Kpi
updateKpi(Kpi $kpi, array $data, User $actor): Kpi
deleteKpi(Kpi $kpi, User $actor): bool

// Bulk Operations
createKpiBulk(User $targetUser, Period $period, array $items, User $actor): Collection
updateKpiBulk(User $targetUser, Period $period, array $items, User $actor, array $explicitRemovedIds = []): Collection
```

**Digunakan di:**

- `TeamLeader\Users\KpiItems` - Create/Update/Delete KPI
- `TeamLeader\Users\MonthlyEvaluation` - Get KPIs for evaluation
- `User\Dashboard` - View own KPIs
- `Admin\Reports` - KPI reporting

**Business Rules:**

- **Authorization**: Hanya team-leader yang bisa manage KPI anggotanya
- **Window Validation**: Operasi hanya di KPI creation window
- **Weight Rules**:
    - Total weight per user per period = 100%
    - Incremental add: allow selama total ≤ 100%
    - Bulk create/update: WAJIB total = 100%
- **Delete Protection**: Tidak bisa hapus KPI yang sudah punya kpi_values
- **criteria_scale**: JSON dengan key 1-5, contoh:
    ```json
    {
        "1": "Sangat Kurang",
        "2": "Kurang",
        "3": "Cukup",
        "4": "Baik",
        "5": "Sangat Baik"
    }
    ```

**Validations:**

- `assertTeamLeaderForMember()` - Check TL dan sama divisi
- `decodeCriteriaScale()` - Validate JSON format

---

### 🔵 **KpiValueService**

**Location:** `App\Services\KpiValueService`

**Fungsi Utama:**

```php
// Validations
isEvaluationWindow(?Carbon $now = null): bool
ensureTeamLeader(User $tl): void
ensureSameDivision(User $tl, User $user): void
ensureActivePeriod(?Period $period): void
ensurePeriodMatchesCurrentDate(Period $period, ?Carbon $now = null): void
alreadySubmitted(User $user, Period $period, int $month): bool

// Retrievals
getMembersForTeamLeader(User $tl): Collection
getUserKpisForPeriod(User $user, Period $period): Collection
getMonthlyValues(User $user, Period $period, int $month): Collection
getOrCreateValue(Kpi $kpi, User $user, User $evaluator, Period $period, int $month): KpiValue

// Main Operation
submitMonthlyEvaluation(User $user, User $tl, array $scores, array $notes): array
```

**Digunakan di:**

- `TeamLeader\Users\MonthlyEvaluation` - Submit monthly scores
- `TeamLeader\Dashboard` - Check evaluation status

**Business Rules:**

- **Evaluation Window**: Tanggal 21-25 setiap bulan
- **Period Validation**:
    - Semester 1 hanya untuk bulan 1-6
    - Semester 2 hanya untuk bulan 7-12
- **Submission Rules**:
    - Semua KPI harus diberi nilai
    - Tidak bisa submit ulang untuk bulan yang sama
    - Score: 1-5 (integer)
- **Authorization**: Team Leader hanya bisa nilai anggota di divisinya

**Return Format `submitMonthlyEvaluation()`:**

```php
[
    'success' => true|false,
    'message' => 'Status message'
]
```

---

### 🔵 **AppraisalService**

**Location:** `App\Services\AppraisalService`

**Fungsi Utama:**

```php
// Retrievals
findAppraisalForUserAndPeriod(int $userId, int $periodId): ?Appraisal
getSemesterMonths(Period $period): array

// Division Analytics
getDivisionAppraisalSummary(Division $division, Period $period): array
getDivisionTrendSeries(Division $division, Period $period): array
getStaffAppraisalList(Division $division, Period $period): array

// Staff Detail
getStaffAppraisalDetail(User $user, Period $period): array
getSemesterSummary(int $userId, int $periodId): array

// Submission
saveTeamLeaderAppraisal(int $userId, int $periodId, array $data): array
saveHrdAppraisal(int $userId, int $periodId, array $data): array
finalizeIfCompleted(Appraisal $appraisal): void

// Helper for Admin
getPeriodsForIndex(): Collection
getUsersForIndex(): Collection
getUsersForIndexPaginated(int $perPage = 10): LengthAwarePaginator
getAppraisalsForPeriod(?int $periodId): Collection
```

**Digunakan di:**

- `TeamLeader\Users\Appraisal` - TL submit appraisal
- `Admin\Appraisals\Index` - HRD submit appraisal
- `Admin\Appraisals\Show` - View appraisal detail
- `TeamLeader\Dashboard` - Division appraisal summary
- `Admin\Dashboard` - Overall appraisal statistics

**Business Rules:**

- **Semester Months**:
    - Semester 1: [1,2,3,4,5,6]
    - Semester 2: [7,8,9,10,11,12]
- **Weighted Average Calculation**:
    ```
    final_score = Σ(score × weight) / Σ(weight)
    ```
- **Submission Flow**:
    1. Team Leader submit → `teamleader_submitted_at` set
    2. HRD submit → `hrd_submitted_at` set
    3. Auto finalize ketika keduanya sudah submit
- **Validations**:
    - TL: hanya bisa submit untuk anggota divisinya
    - HRD: bisa submit untuk semua user
    - Comment minimal 10 karakter
    - Tidak bisa submit ulang

**Return Format getDivisionAppraisalSummary():**

```php
[
    'division' => Division,
    'period' => Period,
    'months' => [1,2,3,4,5,6], // or [7,8,9,10,11,12]
    'staff_count' => int,
    'monthly_averages' => [
        1 => 4.25,
        2 => 4.50,
        // ...
    ],
    'overall_average' => 4.35
]
```

---

### 🔵 **UserAnalyticsService**

**Location:** `App\Services\UserAnalyticsService`

**Fungsi Utama:**

```php
getMonthsForPeriod(Period $period): array
getUserMonthlyAverage(User $user, Period $period, int $month): ?float
getUserMonthlyKpiBreakdown(User $user, Period $period, int $month): array
getTrendSeries(User $user, Period $period): array
```

**Digunakan di:**

- `User\Dashboard\Index` - User analytics chart
- `TeamLeader\Users\Analytics` - View member analytics

**Business Rules:**

- **Weighted Average**: Menggunakan KPI weight untuk perhitungan
    ```
    average = Σ(score × weight) / Σ(weight)
    ```
- **Monthly Breakdown**: Include criteria_label dari criteria_scale

**Return Format getUserMonthlyKpiBreakdown():**

```php
[
    [
        'id' => 1,
        'title' => 'Customer Satisfaction',
        'weight' => 30.00,
        'score' => 4.00,
        'criteria_label' => 'Baik',
        'criteria_scale' => [...],
        'note' => 'Good performance'
    ],
    // ...
]
```

**Return Format getTrendSeries():**

```php
[
    [
        'month' => 1,
        'label' => 'Januari',
        'average' => 4.25
    ],
    // ...
]
```

---

### 🔵 **DivisionAnalyticsService**

**Location:** `App\Services\DivisionAnalyticsService`

**Fungsi Utama:**

```php
getMonthsForPeriod(Period $period): array
getDivisionMonthlyAverage(Division $division, Period $period, int $month): ?float
getDivisionUserMonthlyScores(Division $division, Period $period, int $month): Collection
getDivisionTrendSeries(Division $division, Period $period): array
```

**Digunakan di:**

- `TeamLeader\Dashboard` - Division performance chart
- `Admin\Dashboard` - All divisions comparison
- `Admin\Analytics\Division` - Division analytics detail

**Business Rules:**

- **Aggregate Calculation**: Weighted average dari semua staff di division
- **User Comparison**: Menampilkan performa individual dalam division

**Return Format getDivisionUserMonthlyScores():**

```php
[
    [
        'id' => 1,
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'avg_score' => 4.25
    ],
    // ...
]
```

---

### 🔵 **Admin Services**

#### **AdminUserService**

**Location:** `App\Services\Admin\AdminUserService`

**Fungsi:**

```php
paginateForIndex(array $filters, int $excludeUserId): LengthAwarePaginator
getDivisionsForFilter(): Collection
```

**Digunakan di:**

- `Admin\Users\Index` - User listing dengan filter

**Features:**

- Search: name, email (case-insensitive)
- Filter: division_id
- Sort: name_asc, name_desc, latest
- Pagination: 20 per page
- Exclude admin user dari listing

---

#### **AdminDivisionService**

**Location:** `App\Services\Admin\AdminDivisionService`

**Fungsi:**

```php
paginateForIndex(array $filters, int $perPage = 20): LengthAwarePaginator
```

**Digunakan di:**

- `Admin\Divisions\Index` - Division listing

**Features:**

- Search: division name, leader name
- With count: jumlah users per division
- Ordered by: name ascending
- Pagination: 20 per page

---

### 🔵 **Team Leader Services**

#### **TeamLeaderKpiItemService**

**Location:** `App\Services\TeamLeader\TeamLeaderKpiItemService`

**Fungsi:**

```php
ensureActorCanManageUser(User $actor, User $user): void
getActivePeriod(PeriodService $periodService): ?Period
isCreationWindowOpen(PeriodService $periodService, Period $period): bool
buildPlanItemsFromExistingKpis(Collection $existingKpis): array
newPlanRow(): array
calculateTotalWeight(array $items): float
getTotalWeightForUserPeriod(KpiService $kpiService, User $user, Period $period): float
```

**Digunakan di:**

- `TeamLeader\Users\KpiItems` - KPI planning form

**Purpose:**

- Helper service untuk UI KPI planning
- Transform data antara model dan Livewire component
- Validation helper untuk authorization

**buildPlanItemsFromExistingKpis() Return:**

```php
[
    [
        'id' => 1,
        'title' => 'Customer Satisfaction',
        'weight' => '30.00',
        'scale' => [
            1 => 'Very Poor',
            2 => 'Poor',
            3 => 'Average',
            4 => 'Good',
            5 => 'Excellent'
        ],
        'deleted' => false
    ],
    // ...
]
```

---

## 🎨 Service Usage Flow

### **Flow 1: Create KPI (Team Leader)**

```
TeamLeader Component
    ↓
TeamLeaderKpiItemService (validation & data prep)
    ↓
KpiService.createKpiBulk()
    ↓ (check)
PeriodService.isCurrentWindowForKpiCreation()
    ↓ (validate)
- Total weight = 100%
- Window period valid
    ↓ (save)
Database Transaction
```

### **Flow 2: Monthly Evaluation (Team Leader)**

```
TeamLeader Component
    ↓
KpiValueService.submitMonthlyEvaluation()
    ↓ (validate)
- Evaluation window (21-25)
- Period matches current date
- Not already submitted
    ↓ (get)
KpiService.getKpisByUserAndPeriod()
    ↓ (save all)
Database Transaction → KpiValue records
```

### **Flow 3: Semester Appraisal (Team Leader → HRD)**

```
1. Team Leader:
   TeamLeader Component
       ↓
   AppraisalService.getSemesterSummary()
       ↓ (calculate weighted average)
   AppraisalService.saveTeamLeaderAppraisal()
       ↓
   Database → teamleader_submitted_at

2. HRD/Admin:
   Admin Component
       ↓
   AppraisalService.getStaffAppraisalDetail()
       ↓
   AppraisalService.saveHrdAppraisal()
       ↓
   Database → hrd_submitted_at
       ↓ (auto)
   finalizeIfCompleted() → is_finalized = true
```

### **Flow 4: Division Management (Admin)**

```
Admin Component
    ↓
DivisionService.createDivision()
    ↓ (transaction)
1. Create Division record
2. UserService → promote leader
   - role = 'team-leader'
   - division_id = division.id
    ↓
Commit Transaction
```

---

## 📊 Analytics Calculation

### **Individual User Score (Monthly)**

```php
// Per KPI
score = user_input (1-5)

// Monthly Average (Weighted)
monthly_avg = Σ(kpi_value.score × kpi.weight) / Σ(kpi.weight)
```

### **Individual User Score (Semester)**

```php
// Per KPI (6 months average)
kpi_avg = Σ(monthly_scores) / count(months_with_data)

// Semester Final Score (Weighted)
semester_score = Σ(kpi_avg × kpi.weight) / Σ(kpi.weight)
```

### **Division Score (Monthly)**

```php
// Aggregate all staff in division
division_monthly_avg = Σ(user_monthly_avg × kpi.weight) / Σ(kpi.weight)
// across all staff
```

### **Division Score (Semester)**

```php
// Average of 6 months
division_semester_avg = Σ(division_monthly_avg) / 6
```

---

## 🔐 Authorization Matrix

| Action                  | Admin | Team Leader       | User |
| ----------------------- | ----- | ----------------- | ---- |
| Manage Users            | ✅    | ❌                | ❌   |
| Manage Divisions        | ✅    | ❌                | ❌   |
| Manage Periods          | ✅    | ❌                | ❌   |
| Create KPI              | ❌    | ✅ (own division) | ❌   |
| Monthly Evaluation      | ❌    | ✅ (own division) | ❌   |
| Submit TL Appraisal     | ❌    | ✅ (own division) | ❌   |
| Submit HRD Appraisal    | ✅    | ❌                | ❌   |
| View Own Analytics      | ✅    | ✅                | ✅   |
| View Division Analytics | ✅    | ✅ (own division) | ❌   |
| View All Analytics      | ✅    | ❌                | ❌   |

---

## 🕐 Time-based Windows

### **KPI Creation Window**

- Semester 1 (Year): 1-10 Januari
- Semester 2 (Year): 1-10 Juli

### **Monthly Evaluation Window**

- Setiap bulan: 21-25

### **Semester Appraisal Window**

- Semester 1: 25-31 Juni
- Semester 2: 25-31 Desember

---

## 🔄 Data Flow Diagram

```
┌─────────────┐
│   Period    │ (is_active=true, semester, year)
└──────┬──────┘
       │
       ├──────────────────┐
       │                  │
       ▼                  ▼
┌─────────────┐    ┌─────────────┐
│     KPI     │    │  KPI Value  │
│ (weight=100)│◄───│  (monthly)  │
└──────┬──────┘    └──────┬──────┘
       │                  │
       │                  │
       ▼                  ▼
┌─────────────┐    ┌─────────────┐
│    User     │◄───│  Appraisal  │
│  (role)     │    │  (semester) │
└──────┬──────┘    └─────────────┘
       │
       ▼
┌─────────────┐
│  Division   │
│  (leader)   │
└─────────────┘
```

---

## 🎯 Key Business Rules Summary

1. **Weight Management**
    - Total KPI weight per user per period = 100%
    - Incremental creation allowed (< 100%)
    - Finalization requires exact 100%

2. **Period Management**
    - Only 1 active period at a time
    - Unique (year, semester) combination

3. **Evaluation Flow**
    - Monthly: TL → KpiValues (21-25 each month)
    - Semester: TL → Appraisal → HRD → Finalized

4. **Division Hierarchy**
    - 1 Division = 1 Leader (team-leader role)
    - N Members (user role)
    - Leader can manage members' KPIs

5. **Data Integrity**
    - Cannot delete KPI with values (RESTRICT)
    - Cannot delete Division with users (CASCADE handled in service)
    - Appraisal auto-finalized when both TL & HRD submit

---

**Generated:** 2026-01-18  
**Version:** 1.0  
**Last Updated:** Migration optimization completed
