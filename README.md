# SIMPPAY — Sistem Informasi Penggajian

> Sistem manajemen penggajian terpadu untuk Universitas Adzkia. Dibangun dengan Laravel 12 + Vue 3 + Inertia.js.

---

## Daftar Isi

1. [Tentang SIMPPAY](#tentang-simppay)
2. [Fitur Utama](#fitur-utama)
3. [Instalasi & Menjalankan](#instalasi--menjalankan)
4. [Akun Login](#akun-login)
5. [Panduan Penggunaan per Role](#panduan-penggunaan-per-role)
6. [Arsitektur Aplikasi](#arsitektur-aplikasi)
7. [Struktur Folder](#struktur-folder)
8. [Troubleshooting](#troubleshooting)

---

## Tentang SIMPPAY

**SIMPPAY** (SIMPPAY Payroll) adalah sistem informasi penggajian yang dirancang untuk mengelola data kepegawaian, kehadiran, perhitungan gaji, dan cetak slip gaji di lingkungan universitas.

### Masalah yang Diselesaikan

| Masalah Lama (CI3) | Solusi SIMPPAY (Laravel 12) |
|---|---|
| Query gaji hardcode di banyak file | Semua logic gaji di `PayrollService` |
| Tidak ada role-based access | Spatie Permission: admin, bpsdm, pegawai, tendik |
| Import Excel pakai raw PhpSpreadsheet | Laravel Export/Import class |
| UI admin template generic | Custom UI mengikuti design system Adzkia (#025AB1) |
| Tidak ada AI assistant | AI Chatbot HR + Payroll Assistant |

### Tech Stack

| Layer | Teknologi |
|---|---|
| Backend | Laravel 12, PHP 8.3, MariaDB 10.4 |
| Frontend | Vue 3 (Composition API), Inertia.js |
| UI | shadcn-vue (reka-nova), Tailwind CSS v4, Lucide Icons |
| Auth | Laravel Sanctum + Spatie Permission |
| AI | Laravel AI SDK (`laravel/ai`) |
| Export | Laravel Excel, DomPDF |
| Chart | Chart.js |

---

## Fitur Utama

### 1. Data Master

| Fitur | Fungsi | Siapa yang Bisa |
|---|---|---|
| **Jabatan** | Kelola nama jabatan (Dosen, Tendik, dst) | Admin (CRUD), BPSDM (lihat) |
| **Struktural** | Kelola pangkat/struktural | Admin (CRUD), BPSDM (lihat) |
| **Fungsional** | Kelola fungsi jabatan | Admin (CRUD), BPSDM (lihat) |
| **Pegawai** | Kelola data lengkap pegawai + buat akun login | Admin (CRUD), BPSDM (lihat) |

### 2. Kehadiran

| Fitur | Fungsi | Siapa yang Bisa |
|---|---|---|
| **Rekap Kehadiran** | Input hadir/sakit/alpha per periode | Admin (CRUD) |
| **Import Excel** | Import data kehadiran dari file Excel | Admin |
| **Filter** | Filter berdasarkan periode, jabatan, nama/NIK | Admin, BPSDM |
| **Edit Kehadiran** | Ubah data kehadiran yang sudah ada | Admin |

### 3. Penggajian

| Fitur | Fungsi | Siapa yang Bisa |
|---|---|---|
| **Potongan Gaji** | Kelola komponen potongan gaji (toggle aktif/nonaktif) | Admin (CRUD) |
| **Tunjangan Gaji** | Kelola komponen tunjangan per jabatan/pegawai | Admin (CRUD) |
| **Proses Payroll** | Hitung gaji otomatis per periode | Admin |
| **Finalisasi** | Lock payroll, generate slip gaji | Admin |
| **Void** | Batalkan payroll yang sudah diproses | Admin |

### 4. Slip Gaji & Laporan

| Fitur | Fungsi | Siapa yang Bisa |
|---|---|---|
| **Slip Gaji PDF** | Cetak slip gaji individual | Admin, Pegawai (milik sendiri) |
| **Laporan Gaji PDF** | Rekap gaji seluruh pegawai | Admin, BPSDM |
| **Laporan Absensi PDF** | Rekap kehadiran per periode | Admin, BPSDM |
| **Export Excel** | Export laporan ke Excel | Admin, BPSDM |

### 5. Portal Pegawai

| Fitur | Fungsi | Siapa yang Bisa |
|---|---|---|
| **Dashboard** | Ringkasan gaji + kehadiran | Pegawai, Tendik |
| **Slip Gaji** | Lihat & cetak slip gaji sendiri | Pegawai, Tendik |
| **Riwayat Absensi** | Lihat riwayat kehadiran | Pegawai, Tendik |
| **Profil** | Edit data diri + ganti password + upload foto | Semua role |

### 6. AI Assistant

| Fitur | Fungsi | Siapa yang Bisa |
|---|---|---|
| **Payroll Assistant** | Tanya jumlah gaji per jabatan, rekap data | Admin |
| **HR Chatbot** | Tanya slip gaji sendiri, riwayat absensi | Pegawai, Tendik |

---

## Instalasi & Menjalankan

### Prasyarat

- [XAMPP](https://www.apachefriends.org/) (Apache + MariaDB)
- [Node.js](https://nodejs.org/) v18+
- [Composer](https://getcomposer.org/)

### Langkah Instalasi

```bash
# 1. Clone repository
git clone https://github.com/awaaaaja/SIMPPAY.git
cd SIMPPAY

# 2. Install dependencies PHP
composer install

# 3. Install dependencies JavaScript
npm install

# 4. Copy file environment
cp .env.example .env

# 5. Generate application key
php artisan key:generate

# 6. Jalankan migrate + seed (buat database + data dummy)
php artisan migrate:fresh --seed

# 7. Build frontend assets
npm run build

# 8. Jalankan server (1 perintah)
php artisan serve
```

### Jalankan dengan 1 Perintah Terminal

```bash
php artisan migrate:fresh --seed && npm run build && php artisan serve
```

Aplikasi bisa diakses di: **http://localhost:8000**

### Commands Penting

| Perintah | Fungsi |
|---|---|
| `php artisan serve` | Jalankan server development |
| `npm run dev` | Jalankan Vite dev server (hot reload) |
| `php artisan migrate:fresh --seed` | Reset database + isi data dummy |
| `php artisan test` | Jalankan semua test |
| `npm run build` | Build frontend untuk production |

---

## Akun Login

### Login Page

Akses **http://localhost:8000** → otomatis redirect ke halaman login.

**Cara Login**: Ketik **Username** + **Password**, lalu klik "Masuk".

### Daftar Akun

| Username | Password | Role | Keterangan |
|---|---|---|---|
| `admin` | `password` | Admin | Akses penuh ke semua fitur |
| `bpsdm` | `password` | BPSDM | Akses lihat data (read-only) |
| `ahmad.fauzi` | `password` | Pegawai | Akses portal + slip gaji sendiri |
| `rina.marlina` | `password` | Pegawai | Akses portal + slip gaji sendiri |
| `dedi.kurniawan` | `password` | Pegawai | Akses portal + slip gaji sendiri |
| `siti.nurhaliza` | `password` | Pegawai | Akses portal + slip gaji sendiri |
| `budi.hartono` | `password` | Pegawai | Akses portal + slip gaji sendiri |
| `dewi.anggraini` | `password` | Pegawai | Akses portal + slip gaji sendiri |
| `hendra.wijaya` | `password` | Pegawai | Akses portal + slip gaji sendiri |
| `yuniarti` | `password` | Pegawai | Akses portal + slip gaji sendiri |
| `rudi.santoso` | `password` | Tendik | Akses portal + slip gaji sendiri |
| `eka.putri` | `password` | Tendik | Akses portal + slip gaji sendiri |
| `andi.cahyono` | `password` | Tendik | Akses portal + slip gaji sendiri |
| `maya.sari` | `password` | Tendik | Akses portal + slip gaji sendiri |
| `hasan.basri` | `password` | Tendik | Akses portal + slip gaji sendiri |
| `nurul.hidayah` | `password` | Tendik | Akses portal + slip gaji sendiri |

### Routing Setelah Login

| Role | Redirect Ke | URL |
|---|---|---|
| Admin | Dashboard Admin | `/admin` |
| BPSDM | Dashboard BPSDM | `/bpsdm` |
| Pegawai | Portal Pegawai | `/portal` |
| Tendik | Portal Pegawai | `/portal` |

---

## Panduan Penggunaan per Role

### Admin

Admin memiliki akses penuh ke semua fitur.

#### Sidebar Menu

```
OVERVIEW
  └── Dashboard

PEOPLE
  ├── Pegawai          → Lihat, tambah, edit, hapus, buat akun login
  ├── Jabatan          → Kelola nama jabatan
  ├── Struktural       → Kelola struktural/pangkat
  └── Fungsional       → Kelola fungsi jabatan

ATTENDANCE
  └── Kehadiran        → Input, edit, hapus, import Excel

PAYROLL
  ├── Potongan Gaji    → Kelola komponen potongan
  ├── Tunjangan Gaji   → Kelola komponen tunjangan
  └── Proses Payroll   → Hitung gaji, finalisasi, void

ACCOUNT
  └── Profil Saya      → Edit profil, ganti password
```

#### Alur Proses Gaji

1. **Setup**: Pastikan Jabatan, Struktural, Fungsional sudah terisi
2. **Input Pegawai**: Tambah data pegawai + buat akun login
3. **Input Kehadiran**: Input hadir/sakit/alpha per periode (atau import Excel)
4. **Setup Potongan & Tunjangan**: Aktifkan komponen potongan/tunjangan yang berlaku
5. **Proses Payroll**: Klik "Hitung Gaji" → pilih periode → sistem otomatis hitung
6. **Finalisasi**: Review hasil → klik "Finalisasi" untuk lock data
7. **Cetak Slip**: Klik icon PDF di baris slip gaji

### BPSDM

BPSDM memiliki akses **lihat saja** (read-only) untuk semua data.

#### Sidebar Menu

```
OVERVIEW
  └── Dashboard

PEOPLE
  ├── Pegawai          → Lihat data pegawai
  ├── Jabatan          → Lihat data jabatan
  ├── Struktural       → Lihat data struktural
  └── Fungsional       → Lihat data fungsional

ATTENDANCE
  └── Kehadiran        → Lihat rekap kehadiran

PAYROLL
  ├── Potongan Gaji    → Lihat komponen potongan
  ├── Tunjangan Gaji   → Lihat komponen tunjangan
  └── Proses Payroll   → Lihat rekap payroll

REPORTS
  ├── Laporan Gaji     → Download PDF/Excel
  └── Laporan Absensi  → Download PDF/Excel
```

### Pegawai / Tendik

Pegawai dan Tendik memiliki akses ke **Portal** (personal workspace).

#### Sidebar Menu (Desktop)

```
Dashboard        → Ringkasan gaji + kehadiran
Slip Gaji        → Lihat & cetak slip gaji
Riwayat Absensi  → Lihat riwayat kehadiran
Profil Saya      → Edit data diri, ganti password
```

#### Bottom Navigation (Mobile)

```
Home | Slip | History | Profile
```

---

## Arsitektur Aplikasi

### Diagram Alur

```
┌─────────────┐     ┌──────────────┐     ┌─────────────┐
│   Browser   │────▶│   Laravel    │────▶│   MariaDB   │
│  (Vue/Inertia)   │   (PHP 8.3)  │     │  (Database) │
└─────────────┘     └──────────────┘     └─────────────┘
                          │
                    ┌─────┴─────┐
                    │           │
               ┌────▼───┐ ┌────▼────┐
               │  AI    │ │  Queue  │
               │ Service│ │ (Jobs)  │
               └────────┘ └─────────┘
```

### Flow Autentikasi

```
User Login (username + password)
        │
        ▼
Cek credentials di tabel users
        │
        ▼
Create session + Sanctum token
        │
        ▼
Redirect berdasarkan role:
  ├── admin    → /admin/dashboard
  ├── bpsdm    → /bpsdm/dashboard
  └── pegawai  → /portal/dashboard
```

### Flow Proses Gaji

```
Admin klik "Hitung Gaji"
        │
        ▼
Pilih periode (bulan/tahun)
        │
        ▼
Dispatch CalculatePayrollJob (Queue)
        │
        ▼
Job menghitung untuk setiap pegawai:
  ├── Ambil data kehadiran
  ├── Hitung potongan (alpha, dll)
  ├── Hitung tunjangan
  ├── Hitung gaji bersih
  └── Simpan ke tabel payroll_runs + details
        │
        ▼
Admin review hasil
        │
        ├── Finalisasi → lock, generate slip gaji
        └── Void → batalkan, bisa proses ulang
```

---

## Struktur Folder

```
simppay-laravue/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/           # Controller untuk admin
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── JabatanController.php
│   │   │   │   ├── PegawaiController.php
│   │   │   │   ├── KehadiranController.php
│   │   │   │   ├── PotonganGajiController.php
│   │   │   │   ├── TunjanganGajiController.php
│   │   │   │   └── PayrollRunController.php
│   │   │   ├── Portal/          # Controller untuk pegawai/tendik
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── ProfileController.php
│   │   │   │   └── SlipGajiController.php
│   │   │   └── ProfileController.php
│   │   └── Requests/
│   │       └── Admin/           # Form request validation
│   ├── Models/                  # Model Eloquent
│   ├── Policies/                # Authorization policies
│   └── Services/
│       └── PayrollService.php   # Logic hitung gaji
├── database/
│   ├── migrations/              # Struktur database
│   └── seeders/                 # Data dummy
├── resources/
│   ├── css/
│   │   └── app.css              # Design tokens (warna, spacing, dll)
│   └── js/
│       ├── Layouts/
│       │   ├── AuthenticatedLayout.vue  # Layout admin/bpsdm
│       │   ├── PortalLayout.vue         # Layout portal pegawai
│       │   └── GuestLayout.vue          # Layout login/register
│       ├── Pages/
│       │   ├── Admin/           # Halaman admin
│       │   │   ├── Dashboard/
│       │   │   ├── Jabatan/
│       │   │   ├── Pegawai/
│       │   │   ├── Kehadiran/
│       │   │   ├── PotonganGaji/
│       │   │   ├── TunjanganGaji/
│       │   │   └── PayrollRun/
│       │   ├── Portal/          # Halaman pegawai/tendik
│       │   │   ├── Dashboard.vue
│       │   │   ├── SlipGaji.vue
│       │   │   ├── RiwayatAbsensi.vue
│       │   │   └── Profile.vue
│       │   ├── Auth/            # Halaman login, register
│       │   └── Profile/         # Profil semua role
│       ├── Components/
│       │   └── ui/              # 117 komponen shadcn-vue
│       └── composables/
│           └── useRoutePrefix.js
├── routes/
│   └── web.php                  # Semua route
└── tests/
    └── Feature/                 # 210 test cases
```

---

## Troubleshooting

### Masalah Umum

| Masalah | Solusi |
|---|---|
| Halaman blank/404 | Jalankan `npm run build` atau `npm run dev` |
| Database not found | Jalankan `php artisan migrate:fresh --seed` |
| Port 8000 sudah terpakai | Jalankan `php artisan serve --port=8001` |
| "CSRF token mismatch" | Clear cache: `php artisan cache:clear` |
| Tampilan error 500 | Cek log: `storage/logs/laravel.log` |
| Import Excel gagal | Pastikan file .xlsx, max 10MB |

### Reset Semua Data

```bash
php artisan migrate:fresh --seed
```

Ini akan menghapus semua data dan mengisi ulang dengan data dummy.

### Lihat Semua Route

```bash
php artisan route:list
```

---

## API Routes (Internal)

### Admin Routes (`/admin/*`)

| Method | URL | Fungsi |
|---|---|---|
| GET | `/admin` | Dashboard admin |
| GET/POST | `/admin/pegawai` | CRUD pegawai |
| POST | `/admin/pegawai/{id}/create-account` | Buat akun login |
| GET/POST | `/admin/jabatan` | CRUD jabatan |
| GET/POST | `/admin/struktural` | CRUD struktural |
| GET/POST | `/admin/fungsional` | CRUD fungsional |
| GET/POST | `/admin/kehadiran` | CRUD kehadiran |
| PUT | `/admin/kehadiran/{id}` | Edit kehadiran |
| POST | `/admin/kehadiran/import` | Import Excel |
| GET/POST | `/admin/potongan-gaji` | CRUD potongan |
| PATCH | `/admin/potongan-gaji/{id}/toggle` | Toggle aktif/nonaktif |
| GET/POST | `/admin/tunjangan-gaji` | CRUD tunjangan |
| PATCH | `/admin/tunjangan-gaji/{id}/toggle` | Toggle aktif/nonaktif |
| GET | `/admin/payroll-run` | Daftar payroll |
| POST | `/admin/payroll-run/calculate` | Hitung gaji |
| GET | `/admin/payroll-run/{id}` | Detail payroll |
| POST | `/admin/payroll-run/{id}/finalize` | Finalisasi |
| POST | `/admin/payroll-run/{id}/void` | Batalkan |

### Portal Routes (`/portal/*`)

| Method | URL | Fungsi |
|---|---|---|
| GET | `/portal` | Dashboard pegawai |
| GET | `/portal/slip-gaji` | Daftar slip gaji |
| GET | `/portal/riwayat-absensi` | Riwayat absensi |
| GET | `/portal/profile` | Edit profil |
| PUT | `/portal/profile` | Update profil |
| PUT | `/portal/profile/password` | Ganti password |

### Profile Routes (`/profile`)

| Method | URL | Fungsi |
|---|---|---|
| GET | `/profile` | Edit profil (semua role) |
| PATCH | `/profile` | Update profil |
| PUT | `/profile/password` | Ganti password |

---

## Database

### Tabel Utama

| Tabel | Fungsi |
|---|---|
| `users` | Data akun login (username, email, password, avatar) |
| `roles` | Role: admin, bpsdm, pegawai, tendik |
| `model_has_roles` | Relasi user ↔ role |
| `pegawai` | Data lengkap pegawai (25+ field) |
| `jabatan` | Nama jabatan |
| `struktural` | Struktural/pangkat |
| `fungsional` | Fungsi jabatan |
| `kehadiran` | Rekap kehadiran per periode |
| `potongan_gaji` | Komponen potongan gaji |
| `tunjangan_gaji` | Komponen tunjangan gaji |
| `payroll_runs` | Header proses gaji |
| `payroll_run_details` | Detail gaji per pegawai |
| `slip_gajis` | Data slip gaji |
| `pegawai_anak` | Data anak pegawai |

### ERD (Simplified)

```
users ──────┬──── pegawai
            │       │
            │       ├── jabatan
            │       ├── struktural
            │       ├── fungsional
            │       └── kehadiran
            │
            └── roles

payroll_runs ──── payroll_run_details ──── pegawai
                      │
                      ├── potongan_gaji
                      └── tunjangan_gaji
```

---

## Testing

```bash
# Jalankan semua test
php artisan test

# Jalankan test tertentu
php artisan test --filter=PegawaiTest
php artisan test --filter=PayrollServiceTest

# Lihat coverage
php artisan test --coverage
```

**Status**: 199 passed, 11 skipped (pre-existing PhpSpreadsheet issue)

---

## Deploy ke Production

```bash
# 1. Build frontend
npm run build

# 2. Jalankan migrate
php artisan migrate --force

# 3. Set environment
APP_ENV=production
APP_DEBUG=false

# 4. Cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## License

Proprietary — Universitas Adzkia. All rights reserved.
