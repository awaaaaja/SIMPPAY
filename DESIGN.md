# DESIGN.md — SIMPPAY-Laravel
Arsitektur teknis lengkap. Dokumen ini **normatif** — AI agent wajib mengikuti nama tabel/kolom/kelas persis seperti di sini kecuali PRD/AGENTS menyatakan lain.

---

## 1. Stack Teknologi (keputusan final)

| Layer | Pilihan | Alasan |
|---|---|---|
| Framework | **Laravel 12** | Keputusan eksplisit Yang Mulia (ganti dari rencana awal Laravel 11 — tidak ada breaking change relevan untuk proyek ini, jadi langsung pakai versi terbaru) |
| PHP | 8.3 (minimum Laravel 12; ikuti versi PHP yang dibundel XAMPP — cek `php -v` di XAMPP, upgrade PHP bundling XAMPP kalau masih di bawah 8.3) | Kompatibilitas Laravel 12 |
| Environment Dev | **XAMPP** (Apache + MariaDB/MySQL + PHP) di lokal Yang Mulia | Keputusan eksplisit — proyek dijalankan di `htdocs` XAMPP untuk development, bukan Docker/Laravel Sail/Valet |
| Database | MariaDB (dibundel XAMPP) | Tetap kompatibel dengan skema di §3, tidak perlu install DB server terpisah |
| Frontend Admin/BPSDM/Portal | **Vue 3 + Inertia.js** (satu arsitektur untuk ketiganya, dibedakan lewat route group + layout, BUKAN Filament) | Keputusan eksplisit Yang Mulia — SPA-feel penuh dengan Vue, kontrol UI 100% custom (penting karena ada Design System UI/UX yang detail dan spesifik), tanpa terikat konvensi/batasan komponen Filament |
| Auth | **Laravel 12 starter kit "Vue + Inertia"** (scaffolding awal) → disesuaikan multi-guard/role | Starter kit resmi Laravel 12 sudah menyediakan Inertia+Vue+auth pages siap pakai sebagai titik awal, tinggal disesuaikan ke username-based login (bukan email) |
| Roles/Permission | **spatie/laravel-permission** | Standar industri; di sisi Vue, hak akses dikirim sebagai shared Inertia prop (`auth.user.roles`/`can`) supaya UI bisa menyesuaikan (sembunyikan tombol create/edit untuk BPSDM), TAPI otorisasi sesungguhnya tetap di server (Policy + middleware), tidak pernah hanya mengandalkan UI hiding |
| API Auth | **Laravel Sanctum** — dipakai untuk 2 hal: (1) autentikasi SPA Inertia via session cookie (`sanctum/csrf-cookie` + stateful domain), (2) token personal access untuk integrasi server-to-server (SIMPegawai, dst) | Satu paket untuk dua kebutuhan berbeda — standar untuk kombinasi Laravel+Vue+Inertia SPA |
| Excel Import/Export | **maatwebsite/excel** (Laravel Excel) | Pengganti resmi phpoffice/phpspreadsheet manual |
| PDF (cetak) | **barryvdh/laravel-dompdf** | Ringan, cukup untuk laporan tabular & slip gaji, di-generate server-side dan di-stream ke tab baru dari tombol Vue |
| Queue | Database driver (MVP, jalan gampang di atas XAMPP tanpa Redis) → Redis (produksi jika load tinggi) | `payroll_runs` batch dan AI job dieksekusi async |
| AI SDK | **laravel/ai** (Laravel AI SDK resmi — bukan paket pihak ketiga) | First-party, punya konsep Agent class + Tool dengan schema (jadi whitelist function-calling secara natural), structured output, conversation memory per-user, failover antar provider, dan testing fakes bawaan — cocok persis dengan kebutuhan modul AI di PRD §5.10. Mendukung provider OpenAI-compatible (termasuk OpenRouter) via driver `openai-compatible`, jadi tetap kompatibel dengan setup OpenRouter yang sudah dipakai di OpenCode |
| Testing | **Pest** (backend) + **Vitest** (unit test komponen Vue, opsional tapi disarankan untuk komponen kompleks seperti PayrollTable/PayrollBreakdown) | Pest standar Laravel modern; Vitest ringan & cepat untuk komponen Vue 3 |
| Frontend build | **Vite** + **Vue 3** (Composition API, `<script setup>`) + **Inertia.js** + **Tailwind CSS** | Kombinasi resmi Laravel untuk SPA-feel tanpa membangun REST API terpisah untuk tiap halaman UI — data dikirim langsung sebagai props ke komponen Vue lewat Inertia response, cocok untuk UI/UX Design System yang detail di dokumen terpisah |

---

## 2. Struktur Folder Laravel (final)

```
simppay-laravel/                              # ditempatkan di C:\xampp\htdocs\simppay-laravel (dev lokal)
├── app/
│   ├── Console/Commands/
│   │   ├── MigrateLegacyDataCommand.php     # php artisan simppay:migrate-legacy
│   │   └── SeedDefaultSksCommand.php
│   ├── Enums/
│   │   ├── PayrollStatus.php                # Draft, Calculated, Finalized, Void
│   │   ├── JenisKelamin.php
│   │   └── StatusKepegawaian.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/LoginController.php               # Inertia::render('Auth/Login'), custom guard username
│   │   │   ├── Admin/DashboardController.php          # Inertia::render('Admin/Dashboard', [...])
│   │   │   ├── Admin/PegawaiController.php            # index/create/store/edit/update/destroy — dipakai admin & bpsdm (policy beda)
│   │   │   ├── Admin/JabatanController.php
│   │   │   ├── Admin/StrukturalController.php
│   │   │   ├── Admin/FungsionalController.php
│   │   │   ├── Admin/KehadiranController.php
│   │   │   ├── Admin/PotonganGajiController.php
│   │   │   ├── Admin/TunjanganGajiController.php
│   │   │   ├── Admin/TahunAkademikController.php
│   │   │   ├── Admin/DosenSksController.php
│   │   │   ├── Admin/HonorSksController.php
│   │   │   ├── Admin/PayrollRunController.php         # index/show/store(hitung)/finalize/void
│   │   │   ├── Admin/PayrollAnomalyController.php
│   │   │   ├── Admin/LaporanController.php            # laporan gaji/absensi + export + cetak
│   │   │   ├── Portal/DashboardController.php         # Pegawai/Tendik
│   │   │   ├── Portal/SlipGajiController.php
│   │   │   ├── Portal/ProfileController.php           # ganti password
│   │   │   ├── Api/V1/PegawaiController.php
│   │   │   ├── Api/V1/AbsensiController.php
│   │   │   ├── Api/V1/PenggajianController.php
│   │   │   ├── Api/V1/AiController.php
│   │   │   └── WebhookController.php              # POST /webhook/absensi
│   │   ├── Middleware/
│   │   │   ├── EnsureRoleMatchesGuard.php             # dipakai per route group (admin/bpsdm/portal)
│   │   │   ├── HandleInertiaRequests.php              # share auth.user + roles + permissions ke SEMUA halaman Vue
│   │   │   └── VerifyWebhookSignature.php
│   │   ├── Requests/
│   │   │   ├── StorePegawaiRequest.php / UpdatePegawaiRequest.php
│   │   │   ├── StoreKehadiranRequest.php
│   │   │   └── CalculatePayrollRequest.php
│   │   └── Resources/                              # API Resources (JSON transformers, dipakai API §6 DAN sebagai shape props Inertia)
│   │       ├── PegawaiResource.php
│   │       ├── PayrollDetailResource.php
│   │       └── SlipGajiResource.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Pegawai.php
│   │   ├── Jabatan.php
│   │   ├── Struktural.php
│   │   ├── Fungsional.php
│   │   ├── Kehadiran.php
│   │   ├── PotonganGaji.php
│   │   ├── TunjanganGaji.php
│   │   ├── TahunAkademik.php
│   │   ├── DosenSks.php
│   │   ├── KategoriHonorSks.php
│   │   ├── HonorSks.php
│   │   ├── HonorSksLog.php
│   │   ├── PayrollRun.php
│   │   ├── PayrollDetail.php
│   │   └── PayrollAnomaly.php
│   ├── Policies/
│   │   ├── PegawaiPolicy.php
│   │   ├── PayrollPolicy.php
│   │   └── SlipGajiPolicy.php                      # Pegawai hanya boleh lihat slip miliknya sendiri
│   ├── Services/
│   │   ├── PayrollService.php                      # Jantung sistem — lihat §4
│   │   ├── AbsensiService.php
│   │   ├── SksService.php
│   │   └── LegacyMigrationService.php               # Dipakai command migrate-legacy
│   ├── Ai/                                          # laravel/ai — lihat §9
│   │   ├── Agents/
│   │   │   ├── PayrollAssistantAgent.php            # FR-39
│   │   │   ├── AnomalyReviewAgent.php                # FR-40 (dipanggil dari job, non-conversational)
│   │   │   ├── ReportGeneratorAgent.php              # FR-41 (structured output)
│   │   │   └── HrChatbotAgent.php                    # FR-42 (Conversational, scoped per pegawai)
│   │   └── Tools/
│   │       ├── GetTotalGajiByJabatan.php
│   │       ├── GetTotalGajiByPeriode.php
│   │       ├── GetDistribusiGajiPerJabatan.php
│   │       ├── GetSlipGajiMilikSendiri.php           # HANYA dipakai HrChatbotAgent, scoped Auth::id()
│   │       └── GetRiwayatAbsensiPegawai.php
│   ├── Events/
│   │   ├── PayrollFinalized.php
│   │   └── AbsensiRecorded.php
│   ├── Listeners/
│   │   ├── NotifyPayrollFinalized.php
│   │   └── DispatchAnomalyCheck.php
│   ├── Jobs/
│   │   ├── CalculatePayrollJob.php
│   │   ├── DetectPayrollAnomaliesJob.php
│   │   ├── GenerateExcelExportJob.php
│   │   └── ProcessAbsensiWebhookJob.php
│   └── Exports/
│       ├── PegawaiExport.php
│       ├── LaporanGajiExport.php
│       └── LaporanAbsensiExport.php
├── database/
│   ├── migrations/                                  # lihat §3 — urutan penting (FK dependencies)
│   ├── seeders/
│   │   ├── RoleSeeder.php                           # admin, pegawai, bpsdm, tendik
│   │   ├── JabatanSeeder.php
│   │   └── DemoDataSeeder.php
│   └── factories/                                    # 1 factory per model utama, wajib untuk Pest
├── routes/
│   ├── web.php             # SEMUA route Inertia: auth, admin/*, bpsdm/*, portal/* (role middleware per group)
│   ├── api.php             # /api/v1/* (Sanctum token, untuk integrasi eksternal + endpoint AI)
│   └── webhooks.php        # /webhook/*
├── resources/
│   ├── views/
│   │   ├── app.blade.php   # root template tunggal yang di-mount Inertia (@inertia)
│   │   └── pdf/            # Blade templates untuk DomPDF (slip-gaji, laporan-gaji, laporan-absensi) — TIDAK lewat Inertia, response langsung PDF
│   └── js/
│       ├── app.js                          # bootstrap Inertia + Vue
│       ├── Layouts/
│       │   ├── AdminLayout.vue             # sidebar admin (hierarchy sesuai UI/UX doc), dipakai halaman /admin/*
│       │   ├── BpsdmLayout.vue             # sama seperti AdminLayout tapi styling read-only + tanpa tombol mutasi (server tetap yang menolak, ini cuma visual)
│       │   ├── PortalLayout.vue            # bottom navigation mobile (UI/UX doc §35), dipakai halaman /portal/*
│       │   └── AuthLayout.vue
│       ├── Pages/
│       │   ├── Auth/Login.vue
│       │   ├── Admin/Dashboard.vue
│       │   ├── Admin/Pegawai/Index.vue     # directory + filter bar (UI/UX doc §19)
│       │   ├── Admin/Pegawai/Show.vue      # profile page + tabs (UI/UX doc §20)
│       │   ├── Admin/Jabatan/Index.vue
│       │   ├── Admin/Struktural/Index.vue
│       │   ├── Admin/Fungsional/Index.vue
│       │   ├── Admin/Kehadiran/Index.vue
│       │   ├── Admin/PotonganGaji/Index.vue
│       │   ├── Admin/TunjanganGaji/Index.vue
│       │   ├── Admin/TahunAkademik/Index.vue
│       │   ├── Admin/DosenSks/Index.vue
│       │   ├── Admin/HonorSks/Index.vue
│       │   ├── Admin/PayrollRun/Index.vue  # payroll overview (UI/UX doc §? — cek dokumen UI/UX)
│       │   ├── Admin/PayrollRun/Show.vue   # payroll table + detail drawer
│       │   ├── Admin/PayrollAnomaly/Index.vue  # anomaly center
│       │   ├── Admin/Laporan/Gaji.vue
│       │   ├── Admin/Laporan/Absensi.vue
│       │   ├── Portal/Dashboard.vue
│       │   ├── Portal/SlipGaji.vue
│       │   └── Portal/Profile.vue
│       └── Components/
│           ├── ui/                          # Button, Input, Select (Tom Select wrapper), Modal, Drawer, Skeleton, dst — design-system primitives
│           ├── payroll/PayrollSummary.vue
│           ├── payroll/PayrollTable.vue
│           ├── payroll/PayrollRow.vue
│           ├── payroll/PayrollDetailDrawer.vue
│           ├── payroll/PayrollBreakdown.vue
│           └── charts/ (wrapper Chart.js sesuai UI/UX doc)
└── tests/
    ├── Feature/
    │   ├── Auth/LoginTest.php
    │   ├── Payroll/CalculatePayrollTest.php
    │   ├── Payroll/PayrollAuthorizationTest.php     # role isolation
    │   └── Api/PegawaiApiTest.php
    ├── Unit/
    │   └── Services/PayrollServiceTest.php
    └── (opsional) resources/js/**/*.test.js         # Vitest untuk komponen Vue kompleks
```

**Catatan arsitektur:** BPSDM TIDAK punya folder Controller/Page terpisah — controller & Vue Page yang sama dipakai untuk Admin dan BPSDM (mis. `Admin/PegawaiController` + `Pages/Admin/Pegawai/Index.vue` dipakai di route `/admin/pegawai` maupun `/bpsdm/pegawai`), dibedakan lewat: (1) `EnsureRoleMatchesGuard` middleware per route group, (2) Policy yang mengembalikan `false` untuk aksi create/update/delete kalau role bpsdm, (3) prop Inertia `can: { create: bool, update: bool, delete: bool }` yang dikirim controller ke Vue supaya tombol aksi otomatis hilang di tampilan BPSDM. Poin (2) adalah yang benar-benar menegakkan keamanan; poin (3) murni kosmetik dan **tidak boleh** jadi satu-satunya lapis proteksi (lihat AGENTS.md §4 & §6 soal authorization test wajib per role).

---

## 3. Skema Database (migration-ready)

Urutan migrasi (harus dijaga karena FK):
`jabatan` → `struktural`/`fungsional` → `users` → `pegawai` → `kehadiran` → `potongan_gaji` → `tunjangan_gaji` → `tahun_akademik` → `dosen_sks` → `kategori_honor_sks` → `honor_sks` → `honor_sks_log` → `payroll_runs` → `payroll_details` → `payroll_anomalies`.

### `jabatan` (dulu `data_jabatan`)
```php
Schema::create('jabatan', function (Blueprint $table) {
    $table->id();
    $table->string('nama_jabatan')->unique();
    $table->decimal('gaji_pokok', 15, 2)->default(0);
    $table->decimal('tj_transport', 15, 2)->default(0);
    $table->decimal('uang_makan', 15, 2)->default(0);
    $table->timestamps();
    $table->softDeletes();
});
```

### `struktural` (dulu `data_struktural`)
```php
Schema::create('struktural', function (Blueprint $table) {
    $table->id();
    $table->string('nama_struktural');
    $table->string('level_struktural')->nullable();
    $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
    $table->timestamps();
});
```

### `fungsional` (dulu `data_fungsional`)
```php
Schema::create('fungsional', function (Blueprint $table) {
    $table->id();
    $table->string('nama_fungsional');
    $table->decimal('angka_kredit', 8, 2)->default(0);
    $table->string('pangkat')->nullable();
    $table->string('golongan')->nullable();
    $table->timestamps();
});
```

### `users` (Laravel default + username)
```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('username')->unique();     // login field, BUKAN email
    $table->string('email')->nullable()->unique();
    $table->string('password');
    $table->string('legacy_password_hash')->nullable(); // simpan hash CI3 lama utk lazy-verify, dihapus setelah rehash sukses
    $table->boolean('legacy_password_migrated')->default(false);
    $table->rememberToken();
    $table->timestamps();
});
```
Roles ditambahkan via `spatie/laravel-permission` (tabel `roles`, `model_has_roles`, dst — generate via package migration bawaan, jangan bikin manual).

### `pegawai` (dulu `data_pegawai`) — field lengkap dipertahankan dari ERD asli
```php
Schema::create('pegawai', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('jabatan_id')->nullable()->constrained('jabatan')->nullOnDelete(); // FK, bukan string lagi
    $table->foreignId('struktural_id')->nullable()->constrained('struktural')->nullOnDelete();
    $table->foreignId('fungsional_id')->nullable()->constrained('fungsional')->nullOnDelete();
    $table->string('nik')->unique();
    $table->string('nama_pegawai');
    $table->enum('jenis_kelamin', ['L', 'P']);
    $table->date('tanggal_masuk')->nullable();
    $table->enum('status_pegawai', ['aktif', 'nonaktif', 'pensiun'])->default('aktif');
    $table->string('photo')->nullable();
    $table->string('ktp')->nullable();
    $table->string('nidn')->nullable();         // khusus dosen
    $table->string('id_ptk')->nullable();
    $table->string('nuptk')->nullable();
    $table->string('email')->nullable();
    $table->string('agama')->nullable();
    $table->string('kewarganegaraan')->nullable();
    $table->string('suku')->nullable();
    $table->text('alamat')->nullable();
    $table->date('tgl_lahir')->nullable();
    $table->string('no_hp')->nullable();
    $table->string('jurusan')->nullable();
    $table->string('bidang_keahlian')->nullable();
    $table->string('no_sk')->nullable();
    $table->date('tgl_sk')->nullable();
    $table->string('foto_sk')->nullable();
    $table->enum('status_dosen', ['dosen', 'bukan_dosen'])->nullable();
    $table->string('ikatan_kerja')->nullable();  // tetap/kontrak/honorer
    $table->enum('status_kawin', ['belum_kawin', 'kawin', 'cerai'])->nullable();
    // data pasangan
    $table->string('nama_sm')->nullable();
    $table->string('nip_sm')->nullable();
    $table->string('nohp_sm')->nullable();
    $table->string('pekerjaan_sm')->nullable();
    $table->string('nama_ibu')->nullable();
    // jabatan struktural tambahan (masa jabatan spesifik, terpisah dari struktural_id master)
    $table->string('masa_jabatan')->nullable();
    $table->date('tgl_sk_jabatan')->nullable();
    // data anak (di-normalisasi jadi tabel terpisah `pegawai_anak` jika > 1 anak — lihat catatan di bawah)
    $table->timestamps();
    $table->softDeletes();
});
```
**Catatan desain:** field data-anak (`nama_anak`, `tempat_tgl`, `jenis_kelamin_anak`, `anak_ke`, `pekerjaan_anak`) di skema lama cuma 1 baris per pegawai (tidak mendukung multi-anak). Di skema baru, **normalisasi jadi tabel `pegawai_anak`** (`pegawai_id`, `nama_anak`, `tempat_tanggal_lahir`, `jenis_kelamin`, `anak_ke`, `pekerjaan`) — perbaikan struktural, bukan penghilangan data. Wajib dikonfirmasi ke user sebelum migrasi data lama (lihat AGENTS.md §Aturan Perubahan Skema).

### `kehadiran` (dulu `data_kehadiran`)
```php
Schema::create('kehadiran', function (Blueprint $table) {
    $table->id();
    $table->foreignId('pegawai_id')->constrained('pegawai')->cascadeOnDelete();
    $table->date('periode');            // selalu tanggal 1 di bulan tsb, MIGRASI dari string "MMYYYY"
    $table->unsignedSmallInteger('hadir')->default(0);
    $table->unsignedSmallInteger('sakit')->default(0);
    $table->unsignedSmallInteger('alpha')->default(0);
    $table->timestamps();
    $table->unique(['pegawai_id', 'periode']);
});
```

### `potongan_gaji`
```php
Schema::create('potongan_gaji', function (Blueprint $table) {
    $table->id();
    $table->string('nama_potongan');       // Alpha, BPJS, Sosial, Pend. Anak, UJKS, Koperasi, dst
    $table->enum('tipe', ['nominal', 'persentase'])->default('nominal');
    $table->decimal('nilai', 15, 2);       // nominal Rp atau % (0-100) tergantung tipe
    $table->boolean('is_alpha_penalty')->default(false); // true HANYA utk potongan Alpha (dipakai khusus di formula dasar)
    $table->boolean('aktif')->default(true);
    $table->timestamps();
});
```

**Constraint `is_alpha_penalty`:** Hanya boleh 1 baris `is_alpha_penalty=true` aktif pada satu waktu. Di-enforce di model `PotonganGaji::booted()` — saat save record dengan `is_alpha_penalty=true`, semua record lain otomatis di-unset. Alasan: `PayrollService::calculate()` mengambil potongan alpha penalty sebagai satu-satunya sumber potongan alpha; lebih dari 1 record aktif menciptakan ambiguitas. Pendekatan ini lebih aman daripada validasi reject (mencegah error di user experience saat admin toggle).

### `tunjangan_gaji`
```php
Schema::create('tunjangan_gaji', function (Blueprint $table) {
    $table->id();
    $table->string('nama_tunjangan');      // Struktural, Fungsional, Transport, Makan, BPJS, Keluarga, Tambahan, Variabel, Khusus, Lembur
    $table->enum('target_tipe', ['semua', 'jabatan', 'pegawai'])->default('semua');
    $table->foreignId('jabatan_id')->nullable()->constrained('jabatan')->nullOnDelete();
    $table->foreignId('pegawai_id')->nullable()->constrained('pegawai')->nullOnDelete();
    $table->decimal('nominal', 15, 2);
    $table->boolean('aktif')->default(true);
    $table->timestamps();
});
```

### `tahun_akademik`
```php
Schema::create('tahun_akademik', function (Blueprint $table) {
    $table->id();
    $table->string('kode_tahun')->unique();
    $table->string('tahun');
    $table->enum('semester', ['ganjil', 'genap']);
    $table->date('tanggal_mulai');
    $table->date('tanggal_berakhir');
    $table->boolean('aktif')->default(false); // service layer enforce hanya 1 yang aktif
    $table->timestamps();
});
```

### `dosen_sks` (dulu `set_sks_dosen`; kolom `dosen_id` diarahkan ke `pegawai_id` — tabel `dosen` terpisah di kode lama tidak perlu dipertahankan karena dosen = pegawai dengan `status_dosen = 'dosen'`)
```php
Schema::create('dosen_sks', function (Blueprint $table) {
    $table->id();
    $table->foreignId('pegawai_id')->constrained('pegawai')->cascadeOnDelete();
    $table->foreignId('tahun_akademik_id')->constrained('tahun_akademik')->cascadeOnDelete();
    $table->unsignedSmallInteger('sks_maksimal')->default(0);
    $table->unsignedSmallInteger('sks_terpakai')->default(0);
    $table->unsignedSmallInteger('sks_beban')->default(0);
    $table->timestamps();
    $table->unique(['pegawai_id', 'tahun_akademik_id']);
});
```

### `kategori_honor_sks`
```php
Schema::create('kategori_honor_sks', function (Blueprint $table) {
    $table->id();
    $table->string('nama_kategori');
    $table->timestamps();
});
```

### `honor_sks`
```php
Schema::create('honor_sks', function (Blueprint $table) {
    $table->id();
    $table->foreignId('kategori_honor_sks_id')->constrained('kategori_honor_sks')->cascadeOnDelete();
    $table->decimal('honor', 15, 2);
    $table->foreignId('last_updated_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamps();
});
```

### `honor_sks_log`
```php
Schema::create('honor_sks_log', function (Blueprint $table) {
    $table->id();
    $table->foreignId('honor_sks_id')->constrained('honor_sks')->cascadeOnDelete();
    $table->decimal('honor_lama', 15, 2);
    $table->decimal('honor_baru', 15, 2);
    $table->foreignId('updated_by')->constrained('users');
    $table->timestamp('updated_at');
});
```

### `payroll_runs` (BARU — snapshot per periode)
```php
Schema::create('payroll_runs', function (Blueprint $table) {
    $table->id();
    $table->date('periode');   // tanggal 1 bulan tsb
    $table->enum('status', ['draft', 'calculated', 'finalized', 'void'])->default('draft');
    $table->foreignId('calculated_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamp('calculated_at')->nullable();
    $table->foreignId('finalized_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamp('finalized_at')->nullable();
    $table->text('void_reason')->nullable();
    $table->timestamps();
    $table->unique('periode'); // 1 run aktif per periode; void lalu buat baru kalau perlu ulang
});
```

### `payroll_details` (BARU — breakdown per pegawai per run)
```php
Schema::create('payroll_details', function (Blueprint $table) {
    $table->id();
    $table->foreignId('payroll_run_id')->constrained('payroll_runs')->cascadeOnDelete();
    $table->foreignId('pegawai_id')->constrained('pegawai')->cascadeOnDelete();
    $table->decimal('gaji_pokok', 15, 2);
    $table->decimal('tj_transport', 15, 2);
    $table->decimal('uang_makan', 15, 2);
    $table->decimal('potongan_alpha', 15, 2);
    $table->decimal('total_tunjangan_tambahan', 15, 2)->default(0);
    $table->decimal('total_potongan_tambahan', 15, 2)->default(0);
    $table->decimal('honor_kelebihan_sks', 15, 2)->default(0);
    $table->decimal('total_gaji', 15, 2);
    $table->json('breakdown_json')->nullable(); // detail per item tunjangan/potongan untuk slip gaji rinci
    $table->timestamps();
    $table->unique(['payroll_run_id', 'pegawai_id']);
});
```

### `payroll_anomalies` (BARU — AI Anomaly Detection, FR-40)
```php
Schema::create('payroll_anomalies', function (Blueprint $table) {
    $table->id();
    $table->foreignId('payroll_detail_id')->constrained('payroll_details')->cascadeOnDelete();
    $table->string('tipe');                 // 'gaji_deviasi', 'absensi_deviasi'
    $table->decimal('nilai_sebelumnya', 15, 2)->nullable();
    $table->decimal('nilai_sekarang', 15, 2)->nullable();
    $table->decimal('persentase_deviasi', 8, 2)->nullable();
    $table->text('catatan_ai')->nullable(); // ringkasan naratif dari AI
    $table->enum('status_review', ['pending', 'dikonfirmasi', 'diabaikan'])->default('pending');
    $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamps();
});
```

### `pegawai_anak` (BARU — normalisasi data anak, lihat catatan di §`pegawai`)
```php
Schema::create('pegawai_anak', function (Blueprint $table) {
    $table->id();
    $table->foreignId('pegawai_id')->constrained('pegawai')->cascadeOnDelete();
    $table->string('nama_anak');
    $table->string('tempat_tanggal_lahir')->nullable();
    $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
    $table->unsignedTinyInteger('anak_ke')->nullable();
    $table->string('pekerjaan')->nullable();
    $table->timestamps();
});
```

---

## 4. `PayrollService` — Kontrak Kelas (wajib diikuti persis)

```php
namespace App\Services;

final class PayrollService
{
    /**
     * Hitung gaji SATU pegawai untuk SATU periode. Tidak menyimpan ke DB —
     * murni kalkulasi. Dipanggil oleh calculateBatch() dan oleh preview
     * (mis. tampilan "simulasi" sebelum finalize).
     */
    public function calculate(Pegawai $pegawai, Carbon $periode): PayrollCalculationResult;

    /**
     * Hitung untuk SEMUA pegawai aktif pada periode tsb, simpan sebagai
     * PayrollRun + PayrollDetail (status: calculated). Idempotent — jika
     * run untuk periode ini sudah ada dan belum finalized, akan di-replace
     * (hapus detail lama, insert baru), BUKAN duplikat.
     * Dipanggil dari dalam CalculatePayrollJob, bukan langsung dari controller.
     */
    public function calculateBatch(Carbon $periode, User $calculatedBy): PayrollRun;

    /**
     * Kunci run — setelah ini tidak bisa di-calculateBatch ulang tanpa void dulu.
     */
    public function finalize(PayrollRun $run, User $finalizedBy): PayrollRun;

    /**
     * Batalkan run yang salah (misal ada koreksi data setelah finalize).
     * WAJIB alasan. Run lama jadi status 'void', TIDAK dihapus (audit trail).
     */
    public function void(PayrollRun $run, string $reason, User $voidedBy): PayrollRun;
}
```

`PayrollCalculationResult` adalah DTO (bisa `readonly class` PHP 8.3) berisi seluruh komponen di §6 PRD — dipakai baik untuk simpan ke `payroll_details` maupun untuk render slip gaji, laporan, dan export, **tanpa** query ulang atau logic duplikat di Controller/View manapun. Ini adalah perbaikan langsung atas masalah #1 dan #2 di PRD §1.

---

## 5. Modul Admin/BPSDM — Controller + Vue Page (Inertia)

Pola standar tiap modul: `Controller@index` mengembalikan `Inertia::render('Admin/<Modul>/Index', ['items' => ..., 'filters' => ..., 'can' => [...]])` dengan pagination server-side (jangan kirim seluruh tabel ke browser — lihat UI/UX doc §72 soal menghindari payload besar ke browser). Create/update lewat `Inertia::render` form page ATAU modal di halaman index (pakai `useForm()` dari `@inertiajs/vue3`) — pilih modal untuk data ringkas (Jabatan, PotonganGaji), form page terpisah untuk data kompleks (Pegawai, dengan banyak tab).

| Controller | Model | Vue Page | Fitur khusus |
|---|---|---|---|
| `PegawaiController` | `Pegawai` | `Admin/Pegawai/Index.vue` + `Show.vue` (tabs) | Import/export Excel (endpoint terpisah, download/upload biasa bukan Inertia visit), upload foto via FilePond, tab Keluarga menampilkan relasi `pegawai_anak` |
| `JabatanController` | `Jabatan` | `Admin/Jabatan/Index.vue` (modal form) | Guard hapus jika ada pegawai aktif terkait — tampilkan pesan error dari server di modal |
| `StrukturalController` / `FungsionalController` | `Struktural` / `Fungsional` | `Admin/Struktural/Index.vue` / `Admin/Fungsional/Index.vue` | CRUD standar + toggle aktif/nonaktif (PATCH partial, pakai `router.patch` Inertia) |
| `KehadiranController` | `Kehadiran` | `Admin/Kehadiran/Index.vue` | Import Excel batch, filter periode/jabatan (query string, `router.get` dengan `preserveState`) |
| `PotonganGajiController` / `TunjanganGajiController` | — | `Admin/PotonganGaji/Index.vue` / `Admin/TunjanganGaji/Index.vue` | Toggle aktif inline |
| `TahunAkademikController` | `TahunAkademik` | `Admin/TahunAkademik/Index.vue` | Action "Aktifkan" (POST) yang otomatis menonaktifkan tahun lain (service-enforced di server, BUKAN cuma di Vue) |
| `DosenSksController` | `DosenSks` | `Admin/DosenSks/Index.vue` | Inline edit (modal), filter per tahun akademik |
| `HonorSksController` | `HonorSks` | `Admin/HonorSks/Index.vue` | Setiap update memicu insert ke `HonorSksLog` (Model event `updating`); tab/tombol terpisah untuk lihat log |
| `PayrollRunController` | `PayrollRun` | `Admin/PayrollRun/Index.vue` (payroll overview) + `Show.vue` (payroll table + `PayrollDetailDrawer`) | Action "Hitung Gaji" (dispatch `CalculatePayrollJob`, polling status via Inertia partial reload atau endpoint status kecil), "Finalize", "Void"; klik baris pegawai membuka drawer breakdown (bukan halaman baru — sesuai UI/UX doc, progressive disclosure) |
| `PayrollAnomalyController` | `PayrollAnomaly` | `Admin/PayrollAnomaly/Index.vue` (anomaly center) | Read-mostly, action "Konfirmasi"/"Abaikan" untuk `status_review` |

Route BPSDM (`/bpsdm/*`) memakai **controller & Vue Page yang sama** dengan Admin — lihat catatan arsitektur di akhir §2. Jangan membuat duplikat Controller/Page untuk BPSDM.

---

## 6. Kontrak REST API (`/api/v1/*`, Sanctum)

| Endpoint | Method | Auth | Deskripsi |
|---|---|---|---|
| `/api/v1/pegawai` | GET | token | List pegawai (paginated, filter jabatan/status) |
| `/api/v1/pegawai/{id}` | GET | token | Detail 1 pegawai |
| `/api/v1/pegawai` | POST | token (scope `pegawai:write`) | Create — untuk sync dari SIMPegawai |
| `/api/v1/pegawai/{id}` | PUT | token (scope `pegawai:write`) | Update |
| `/api/v1/absensi` | GET/POST | token | Data absensi per periode |
| `/api/v1/penggajian` | GET | token | Hasil `PayrollDetail` per periode (query param `periode=YYYY-MM`) |
| `/api/v1/penggajian/process` | POST | token (scope `payroll:process`), role admin only | Trigger `CalculatePayrollJob` async, return `job_id` untuk polling status |
| `/api/v1/laporan/gaji` | GET | token | Data laporan gaji (JSON, untuk BI/Power BI/Metabase) |
| `/api/v1/slip-gaji/{nik}` | GET | token, **atau** session Sanctum SPA pegawai untuk NIK miliknya sendiri | Slip gaji individual |
| `/api/v1/potongan`, `/api/v1/tunjangan` | GET/POST | token | Setting granular |
| `/api/v1/ai/payroll-query` | POST | token / session | FR-39 |
| `/api/v1/ai/anomalies` | GET | token, role admin | FR-40 |
| `/api/v1/ai/report` | POST | token, role admin | FR-41 |
| `/api/v1/ai/chat` | POST | session (pegawai/tendik), scope ketat ke data sendiri | FR-42 |
| `/webhook/absensi` | POST | HMAC signature header `X-Signature` | Terima data absensi real-time dari SIM Absensi eksternal, dispatch `ProcessAbsensiWebhookJob` |

Semua response sukses: `{"data": ..., "meta": {...}}`. Semua error: `{"message": "...", "errors": {...}}` (format standar Laravel validation/exception).

---

## 7. Strategi Migrasi Data Legacy

`php artisan simppay:migrate-legacy --dry-run` lalu `php artisan simppay:migrate-legacy --commit`:

1. Baca DB CI3 lama via koneksi kedua (`config/database.php` connection `legacy`).
2. Migrasi urut: `data_jabatan` → `jabatan`, `data_struktural` → `struktural`, `data_fungsional` → `fungsional`.
3. Migrasi `data_pegawai` → `pegawai` + `users`: mapping `jabatan` (string) ke `jabatan_id` (cari exact match `nama_jabatan`, jika tidak ketemu → log ke laporan dry-run, JANGAN auto-create jabatan baru diam-diam).
4. Normalisasi data anak: 1 baris `data_pegawai` lama → 1 baris `pegawai_anak` baru (jika field `nama_anak` tidak kosong).
5. Migrasi `data_kehadiran` → `kehadiran`: parse string `"MMYYYY"` → `date` (`Carbon::createFromFormat('mMY', ...)` atau format yang sesuai setelah verifikasi sample data asli — **wajib cek beberapa baris nyata dulu**, jangan asumsi format).
6. Password: pindahkan hash lama ke `legacy_password_hash`, `password` diisi placeholder invalid (tidak bisa dipakai login) sampai user login pertama kali dan lolos verifikasi lazy-rehash (FR-04).
7. Mode `--dry-run` WAJIB menghasilkan laporan CSV/tabel: jumlah baris berhasil, jumlah baris gagal + alasan, tanpa menulis apapun ke DB baru.
8. Command idempotent: jalankan `--commit` dua kali tidak boleh duplikasi (pakai `updateOrCreate` dengan natural key seperti `nik`).

---

## 8b. Modul AI — Arsitektur Agent (`laravel/ai`)

Instalasi: `composer require laravel/ai`, lalu `php artisan vendor:publish --provider="Laravel\Ai\AiServiceProvider"` dan `php artisan migrate` (membuat tabel `agent_conversations` & `agent_conversation_messages` bawaan paket — dipakai untuk conversation memory `HrChatbotAgent`).

Provider di `config/ai.php` diarahkan ke driver `openai-compatible` (mengikuti setup OpenRouter yang sudah dipakai Yang Mulia di OpenCode) atau provider resmi (`openai`, `anthropic`, dst) — pilih salah satu sebagai default, dengan **failover** ke provider kedua supaya fitur AI tidak total mati kalau satu provider down (lihat FR-43).

```php
// config/ai.php (potongan relevan)
'providers' => [
    'openrouter' => [
        'driver' => 'openai-compatible',
        'url' => env('AI_BASE_URL'),
        'key' => env('AI_API_KEY'),
        'models' => ['text' => ['default' => env('AI_MODEL')]],
    ],
],
```

### 9.1 `PayrollAssistantAgent` (FR-39, Admin)

```php
#[Provider(['openrouter', 'openai'])]   // failover otomatis kalau provider utama gagal
class PayrollAssistantAgent implements Agent, HasTools
{
    use Promptable;

    public function instructions(): string
    {
        return 'Kamu asisten data payroll untuk Admin SIMPPAY. Jawab HANYA berdasarkan hasil pemanggilan tools yang tersedia — jangan mengarang angka. Selalu sebutkan periode data yang dipakai.';
    }

    public function tools(): iterable
    {
        return [
            new GetTotalGajiByJabatan,
            new GetTotalGajiByPeriode,
            new GetDistribusiGajiPerJabatan,
        ];
    }
}
```

Setiap `Tool` (mis. `GetTotalGajiByJabatan`) punya `schema()` yang membatasi argumen yang boleh dikirim model (nama jabatan/ID, periode) dan `handle()` yang memanggil `PayrollDetail::query()`/`PayrollService` secara terkontrol — **ini yang mewujudkan aturan "whitelist query, bukan SQL bebas" di AGENTS.md §5**, karena model tidak pernah menyusun query, hanya memilih tool + argumen sesuai schema. Endpoint `POST /api/v1/ai/payroll-query` cukup memanggil `(new PayrollAssistantAgent)->prompt($request->input('question'))`.

### 9.2 `AnomalyReviewAgent` (FR-40, dipanggil dari `DetectPayrollAnomaliesJob`)

Bukan agent percakapan — dipanggil sekali per `PayrollDetail` yang lolos filter deviasi >30% (filter numerik dilakukan di PHP/`AnomalyDetectionService` dulu, agent **hanya** dipakai untuk menulis `catatan_ai` naratif). Gunakan `HasStructuredOutput` supaya hasilnya langsung berupa field terstruktur untuk disimpan ke `payroll_anomalies.catatan_ai`:

```php
class AnomalyReviewAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function schema(JsonSchema $schema): array
    {
        return [
            'ringkasan' => $schema->string()->required(),
            'kemungkinan_penyebab' => $schema->string()->required(),
            'rekomendasi_tindakan' => $schema->string()->required(),
        ];
    }
}
```
Dipanggil via `->queue()` di dalam job supaya tidak memblokir proses finalize payroll.

### 9.3 `ReportGeneratorAgent` (FR-41, on-demand)

`HasStructuredOutput` juga, dengan skema `ringkasan`, `insight[]` (array of string), `distribusi_per_jabatan[]` (array object). Dipanggil dari action "Generate Insight AI" di `PayrollRunController`/halaman laporan (endpoint POST terpisah, hasilnya di-fetch dari Vue lewat `router.reload` partial atau `axios` kecil ke `/api/v1/ai/report`) — hasilnya ditampilkan sebagai pelengkap di atas tabel/PDF laporan, **bukan** menggantikan data tabel.

### 9.4 `HrChatbotAgent` (FR-42, Pegawai/Tendik — scope paling kritis)

```php
class HrChatbotAgent implements Agent, Conversational, HasTools
{
    use Promptable, RemembersConversations;

    public function __construct(public Pegawai $pegawai) {}   // WAJIB constructor injection, bukan ambil dari request bebas

    public function instructions(): string
    {
        return "Kamu asisten HR untuk pegawai {$this->pegawai->nama_pegawai}. Kamu HANYA boleh menjawab tentang data milik pegawai ini sendiri. Tolak dengan sopan permintaan data pegawai lain.";
    }

    public function tools(): iterable
    {
        return [
            new GetSlipGajiMilikSendiri($this->pegawai),   // Tool menerima $pegawai di constructor, query di-hardcode where pegawai_id = $this->pegawai->id — TIDAK menerima pegawai_id sebagai argumen dari model
            new GetRiwayatAbsensiPegawai($this->pegawai),
        ];
    }
}
```

Controller: `(new HrChatbotAgent($request->user()->pegawai))->forUser($request->user())->prompt(...)`. **Poin kritis** (lihat AGENTS.md §5): `Tool` untuk chatbot ini menerima `Pegawai $pegawai` lewat constructor dari sisi server (bukan sebagai parameter schema yang bisa diisi model), sehingga secara struktural mustahil model meminta data pegawai lain — bukan sekadar instruksi prompt yang bisa di-*jailbreak*.

### 9.5 Fallback (FR-43)

Setiap route yang memanggil agent dibungkus try-catch generik di controller (bukan di dalam Agent) yang menangkap exception AI SDK (mis. kehabisan quota/provider down setelah failover pun gagal) dan mengembalikan response jelas `{"message": "Fitur AI sementara tidak tersedia, coba lagi nanti."}` tanpa membuat request lain (lihat slip gaji, dsb) ikut gagal.

### 9.6 Testing

Semua test AI pakai fake bawaan paket, **tidak pernah** memanggil API asli di test suite:
```php
PayrollAssistantAgent::fake(['Total gaji jabatan Dosen bulan Januari: Rp 120.000.000']);
HrChatbotAgent::fake()->preventStrayPrompts(); // pastikan tidak ada prompt tak terduga lolos tanpa fake
```

## 9. Konfigurasi Routing Multi-Area (Vue + Inertia)

Tidak ada "panel" terpisah seperti Filament — satu aplikasi Laravel, route dikelompokkan per role dengan middleware, dan Vue Layout yang berbeda dipilih di tiap Page component lewat `defineOptions({ layout: AdminLayout })` (atau `BpsdmLayout`/`PortalLayout`).

```php
// routes/web.php
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::resource('pegawai', Admin\PegawaiController::class);
    // ...seluruh resource modul admin
});

Route::middleware(['auth', 'role:bpsdm'])->prefix('bpsdm')->name('bpsdm.')->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('pegawai', [Admin\PegawaiController::class, 'index'])->name('pegawai.index'); // hanya route GET, tidak ada resource penuh
    Route::get('pegawai/{pegawai}', [Admin\PegawaiController::class, 'show'])->name('pegawai.show');
    // ...seluruh modul lain, HANYA route baca (index/show), route mutasi (store/update/destroy) TIDAK didaftarkan sama sekali untuk group ini
});

Route::middleware(['auth', 'role:pegawai,tendik'])->prefix('portal')->name('portal.')->group(function () {
    Route::get('/', [Portal\DashboardController::class, 'index'])->name('dashboard');
    Route::get('slip-gaji', [Portal\SlipGajiController::class, 'index'])->name('slip-gaji');
    Route::get('profile', [Portal\ProfileController::class, 'index'])->name('profile');
    Route::put('profile/password', [Portal\ProfileController::class, 'updatePassword'])->name('profile.password');
});
```

Login tunggal (`/login`, Vue page `Auth/Login.vue`) redirect ke area sesuai role user setelah autentikasi berhasil (`admin`→`/admin`, `bpsdm`→`/bpsdm`, `pegawai`/`tendik`→`/portal`) via `Auth::user()->hasRole()` check di `LoginController`, bukan 2+ form login terpisah.

`HandleInertiaRequests` middleware (`app/Http/Middleware/HandleInertiaRequests.php`) men-share data berikut ke **setiap** halaman Vue tanpa perlu di-pass manual per controller:
```php
public function share(Request $request): array
{
    return [
        ...parent::share($request),
        'auth' => [
            'user' => $request->user(),
            'roles' => $request->user()?->getRoleNames(),
        ],
        'flash' => [
            'success' => fn () => $request->session()->get('success'),
            'error' => fn () => $request->session()->get('error'),
        ],
    ];
}
```
Catatan keamanan: `role:bpsdm` yang hanya mendaftarkan route baca (bukan `Route::resource` penuh lalu di-guard di controller) berarti kalaupun ada bug di Policy, route mutasi untuk BPSDM **secara fisik tidak ada** — 404, bukan 403. Ini lapis proteksi tambahan di atas Policy (defense in depth), bukan pengganti Policy — Policy tetap wajib ada dan ditest (lihat AGENTS.md §6).
