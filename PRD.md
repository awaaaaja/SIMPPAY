# PRD — SIMPPAY-Laravel
### Sistem Informasi Penggajian Karyawan Universitas Adzkia (Rewrite CI3 → Laravel 12 + Vue 3/Inertia)

Versi: 1.0
Status: Draft final — siap eksekusi AI Agent
Sumber: Analisis reverse-engineering SIMPPAY (CodeIgniter 3.x) — 30 controller, 9 model, ~15 tabel real.

---

## 1. Latar Belakang & Masalah

SIMPPAY eksisting (CI3) punya masalah struktural yang jadi alasan migrasi:

1. **Logic gaji dihitung di view**, bukan di service/model → tidak testable, rawan salah hitung, tidak bisa diaudit.
2. **Query gaji di-copy-paste** ke 3 tempat berbeda (`Data_Penggajian`, `Laporan_Gaji`, `Slip_Gaji`) → 1 perubahan formula = 3 tempat harus diubah manual.
3. **~15 sidebar menu broken** (link ke controller yang tidak ada): tunjangan struktural/jabfung/transport/makan/bpjs/keluarga/tambahan/khusus, data_lembur, 5 menu potongan, gaji_pokok, kemajuan_perkuliahan.
4. **~10 tabel dipakai di kode tapi tidak ada di SQL dump** (`gaji_dasar`, `tunjangan_gaji`, `data_struktural`, `data_fungsional`, `dosen`, `set_sks_dosen`, `tahun_akademik`, `honor_kelebihan_sks`, `kategori_honor_kelebihan_sks`, `honor_kelebihan_sks_log`) → skema riil vs skema terdokumentasi tidak sinkron, migrasi wajib rekonsiliasi data produksi dulu.
5. **`data_pegawai.jabatan` adalah string**, bukan FK numerik ke `data_jabatan` → rawan typo, tidak ada integritas referensial.
6. **`data_kehadiran.bulan` disimpan sebagai string "MMYYYY"** → tidak bisa di-sort kronologis dengan benar, tidak query-friendly.
7. **4 role (Admin, Pegawai, BPSDM, Tendik) dicek manual via `session('hak_akses')`** dengan switch-case di controller → tidak ada middleware/policy terpusat, gampang bocor akses kalau ada controller baru yang lupa dicek.
8. Tidak ada API, tidak ada queue/job, tidak ada test otomatis, tidak ada integrasi AI.

## 2. Tujuan Proyek

1. Rewrite penuh ke **Laravel 12 + PHP 8.3**, frontend **Vue 3 + Inertia.js** (bukan Filament), environment development di **XAMPP** lokal, database tetap MariaDB (dibundel XAMPP, compatible), dengan skema final yang **merekonsiliasi** seluruh tabel "hantu" (dipakai di kode tapi tak ada di dump) jadi migration resmi.
2. Pindahkan **seluruh logic penggajian ke `PayrollService`** (single source of truth), dipakai bersama oleh: tampilan admin, laporan, slip gaji, export Excel, dan API — tidak ada lagi query duplikat.
3. Ganti switch-case akses manual dengan **Spatie Laravel-Permission** (role: `admin`, `pegawai`, `bpsdm`, `tendik`) + Laravel Policy per model.
4. Selesaikan seluruh menu yang tadinya broken link (tunjangan & potongan granular) menjadi modul CRUD nyata, di-generalisasi jadi tabel `tunjangan_gaji` & `potongan_gaji` yang sudah tervalidasi ada di kode lama.
5. Sediakan **REST API (Sanctum)** agar SIMPPAY bisa terintegrasi dengan SIMPegawai, SIM Keuangan, SIM Absensi, SIM BPJS, SIM Pajak sesuai roadmap ekosistem di dokumen analisis asli.
6. Tambahkan **modul AI** (chatbot HR, deteksi anomali gaji/absensi, generate laporan naratif) sesuai use-case yang sudah direncanakan di analisis asli, pakai OpenAI-compatible SDK.
7. Migrasi data produksi lama (CI3 `password` md5/plaintext lama) ke `bcrypt` Laravel dengan strategi *lazy rehash saat login pertama*, tanpa memaksa reset password massal.

## 3. Non-Tujuan (Out of Scope v1)

- Tidak membangun ulang SIMPegawai/SIM Keuangan/SIM Absensi/SIM BPJS/SIM Pajak — SIMPPAY hanya menyediakan API/webhook untuk mereka konsumsi nanti (kontrak API disiapkan, implementasi integrasi nyata menyusul).
- Tidak migrasi ke microservices — tetap monolith Laravel modular per domain.
- Tidak build native mobile app — cukup API-ready + web responsif.
- Fitur AI predictive analytics (forecast turnover/budget) masuk v2, bukan MVP.

## 4. Target Pengguna & Peran

| Role (kode lama `hak_akses`) | Role Laravel (Spatie) | Deskripsi | Akses |
|---|---|---|---|
| 1 — Admin | `admin` | HR/keuangan penuh | Full CRUD semua modul, hitung gaji, cetak, export, setting master data |
| 2 — Pegawai | `pegawai` | Dosen/karyawan biasa | Self-service: lihat & cetak slip gaji sendiri, ganti password |
| 3 — BPSDM | `bpsdm` | Unit pengawas SDM | Read-only mirror Admin (tanpa tombol create/update/delete), plus cetak laporan |
| 4 — Tendik | `tendik` | Tenaga kependidikan | Mirror Pegawai — self-service saja |

## 5. Modul Fungsional (Functional Requirements)

Setiap modul di bawah **wajib** py dengan Service Layer terpisah dari Controller, dan **wajib** punya Feature Test (Pest) minimal happy-path + 1 authorization test.

### 5.1 Autentikasi & Otorisasi
- FR-01: Login via username + password (bukan email — sesuai sistem lama, username unik per pegawai/NIK).
- FR-02: Setelah login sukses, redirect berdasarkan role ke dashboard masing-masing (`/admin`, `/bpsdm`, atau `/portal` untuk `pegawai`/`tendik` — semuanya halaman Vue lewat Inertia, dibedakan lewat route group + layout, bukan Filament panel/Livewire terpisah).
- FR-03: Ganti password mandiri (semua role), validasi password lama + konfirmasi password baru (min 8 char, wajib campuran huruf-angka).
- FR-04: Lazy password rehash — jika hash lama terdeteksi (format md5/plain), verifikasi manual lalu re-hash ke bcrypt saat login sukses pertama kali pasca migrasi.
- FR-05: Rate limiting login (5x gagal → lockout 1 menit), log percobaan login ke tabel audit.

### 5.2 Data Master Pegawai
- FR-06: CRUD data pegawai (Admin only) dengan seluruh field dari skema lama (identitas, kepegawaian, keluarga, jabatan struktural/fungsional) — lihat §7 Data Model untuk field lengkap.
- FR-07: Upload foto pegawai (`photo`) dan foto SK jabatan (`foto_sk`) — disimpan di `storage/app/public`, validasi tipe (jpg/png) & ukuran maks 2MB.
- FR-08: Import Excel massal (template disediakan) + export Excel seluruh data pegawai (ganti PhpSpreadsheet manual → **Laravel Excel**).
- FR-09: `jabatan` di-refactor jadi **foreign key** (`jabatan_id`) ke tabel `data_jabatan`, bukan string bebas. Migrasi data lama: mapping string `nama_jabatan` → cari/insert ke `data_jabatan` otomatis via seeder migrasi satu-kali.
- FR-10: Soft delete pegawai (jangan hard delete — riwayat gaji harus tetap valid secara historis).

### 5.3 Data Jabatan
- FR-11: CRUD jabatan: nama, gaji pokok, tunjangan transport, uang makan.
- FR-12: Validasi unik `nama_jabatan`.
- FR-13: Tidak bisa dihapus jika masih dipakai pegawai aktif (soft-guard, tampilkan pesan jumlah pegawai terkait).

### 5.4 Absensi / Kehadiran
- FR-14: Input absensi per pegawai per periode (bulan+tahun sebagai `date` kolom `periode` tipe `YYYY-MM-01`, **bukan** string "MMYYYY" seperti lama) — hadir/sakit/alpha (angka, integer ≥ 0).
- FR-15: Input bisa satuan atau batch (upload Excel absensi bulanan).
- FR-16: View absensi per periode dengan filter bulan/tahun/pegawai/jabatan.
- FR-17: Constraint unik: satu pegawai hanya boleh 1 baris absensi per periode (`unique(pegawai_id, periode)`).

### 5.5 Setting Potongan & Tunjangan (generalisasi menu broken lama)
- FR-18: Tabel `potongan_gaji` (generik): nama potongan (Alpha, BPJS, Sosial, Pendidikan Anak, UJKS, Koperasi), nominal atau persentase, aktif/nonaktif.
- FR-19: Tabel `tunjangan_gaji` (generik, menggantikan 10 menu broken lama — struktural/fungsional/transport/makan/bpjs/keluarga/tambahan/variabel/khusus/lembur): kategori, target (per jabatan atau per pegawai), nominal.
- FR-20: Semua potongan & tunjangan granular ini **opsional dan additive** terhadap formula dasar (lihat §6 Formula Gaji) — tidak menggantikan field bawaan `data_jabatan`.
- FR-21: CRUD via inline-edit di Vue (modal/`router.patch` partial, tanpa reload halaman penuh) — mengikuti UX sistem lama yang sudah familiar untuk user.

### 5.6 Perhitungan & Transaksi Gaji (Core Business — PayrollService)
- FR-22: `PayrollService::calculate(Pegawai $pegawai, Periode $periode)` mengembalikan breakdown lengkap (gaji pokok, transport, makan, seluruh tunjangan aktif, seluruh potongan aktif termasuk potongan alpha, total akhir) — **satu-satunya tempat** logic ini boleh ada.
- FR-23: `PayrollService::calculateBatch(Periode $periode)` — hitung untuk seluruh pegawai aktif sekaligus, dijalankan sebagai **Queue Job** (`CalculatePayrollJob`) supaya tidak timeout untuk data besar, progress bisa dipantau (Laravel Horizon opsional, minimal `job_batches`).
- FR-24: Hasil hitung disimpan sebagai snapshot di tabel `payroll_runs` / `payroll_details` (bukan dihitung ulang tiap kali dibuka) — supaya laporan historis tidak berubah kalau setting tunjangan/potongan diubah di kemudian hari. Re-calculate eksplisit harus dikonfirmasi user (karena akan overwrite snapshot periode tsb).
- FR-25: Status per payroll run: `draft` → `calculated` → `finalized` (setelah finalized, tidak bisa diedit lagi tanpa membuat run baru / void + reason).

### 5.7 SKS Dosen & Honor Kelebihan SKS
- FR-26: Master tahun akademik (kode, tahun, semester, tanggal mulai/berakhir, status aktif — hanya 1 tahun akademik aktif dalam satu waktu, di-enforce di service level).
- FR-27: Set SKS per dosen per tahun akademik: SKS maksimal, SKS terpakai, SKS beban. Insert default otomatis untuk dosen baru saat tahun akademik baru dibuat.
- FR-28: Inline edit SKS per dosen (mengikuti UX lama).
- FR-29: Kategori honor kelebihan SKS (master) + nominal honor per kategori.
- FR-30: Setiap perubahan nominal honor **wajib tercatat di log** (`honor_kelebihan_sks_log`: honor lama, honor baru, siapa yang ubah, kapan) — audit trail wajib, tidak boleh silent update.
- FR-31: Honor kelebihan SKS dihitung: `(SKS terpakai - SKS maksimal) × honor per kategori`, jika hasil negatif = 0 (tidak ada kelebihan).

### 5.8 Laporan & Cetak
- FR-32: Laporan gaji (filter periode, jabatan, pegawai) — versi cetak (PDF via DomPDF) dan versi layar.
- FR-33: Laporan absensi (filter periode) — cetak PDF.
- FR-34: Slip gaji per pegawai (individual, bisa dicetak Admin/BPSDM untuk siapapun, bisa dicetak Pegawai/Tendik untuk dirinya sendiri saja — di-enforce via Policy).
- FR-35: Export Excel: data pegawai, laporan gaji, laporan absensi — pakai Laravel Excel dengan Export class per jenis laporan (bukan raw PhpSpreadsheet inline).

### 5.9 Portal Self-Service (Pegawai & Tendik)
- FR-36: Dashboard ringkas: info pegawai, slip gaji bulan terbaru, riwayat 6 bulan terakhir (grafik) — layout sesuai UI/UX doc §32-§33 (personal workspace, greeting, latest salary, employment info, 6-month chart).
- FR-37: Lihat & cetak slip gaji sendiri per periode manapun yang sudah `finalized` — layout sesuai UI/UX doc §34 (breakdown pendapatan/potongan, action download PDF/print).
- FR-38: Ganti password sendiri — sesuai UI/UX doc §37-§38 (modal untuk focused form, bukan halaman terpisah).

### 5.10 Modul AI (baru, sesuai roadmap dokumen asli §15)
- FR-39: **AI Payroll Assistant** — endpoint chat (`POST /api/v1/ai/payroll-query`) yang menerima pertanyaan bahasa natural ("berapa total gaji bulan Januari untuk jabatan Dosen?"), AI menerjemahkan ke query terstruktur (bukan raw SQL dari user — pakai *function calling* dengan whitelist query yang aman) lalu format jawaban.
- FR-40: **AI Anomaly Detection** — job terjadwal (bulanan, setelah payroll run finalized) yang membandingkan gaji & absensi pegawai vs histori 6 bulan terakhir, flag deviasi > threshold (default 30%, configurable), simpan ke tabel `payroll_anomalies` untuk direview Admin.
- FR-41: **AI Report Generator** — generate ringkasan naratif dari data laporan gaji periode tertentu (insight: kenaikan/penurunan, distribusi per jabatan) sebagai pelengkap laporan tabel/PDF, bukan pengganti.
- FR-42: **AI Chatbot HR** (self-service, role pegawai/tendik) — jawab pertanyaan seputar slip gaji sendiri saja (di-scope ketat by `Auth::id()`, AI tidak boleh mengakses data pegawai lain).
- FR-43: Semua modul AI wajib punya **fallback non-AI** (kalau API AI down/limit habis, fitur non-AI seperti lihat slip tetap berjalan normal) — AI adalah *enhancement*, bukan dependency kritis.

### 5.11 API (Sanctum, untuk integrasi ekosistem)
- FR-44: Endpoint API sesuai tabel di dokumen analisis asli §15 (`/api/v1/pegawai`, `/api/v1/absensi`, `/api/v1/penggajian`, dst) — versi resmi ada di DESIGN.md §6.
- FR-45: Autentikasi API pakai Sanctum token (personal access token untuk integrasi server-to-server dengan SIM lain).
- FR-46: Webhook masuk (`POST /webhook/absensi`) dari SIM Absensi eksternal — validasi signature (HMAC shared secret), idempotent (tidak duplikat kalau webhook dikirim ulang).

## 6. Formula Gaji (Business Rule — Kritis, jangan diubah tanpa approval)

Formula dasar (dari sistem lama, dipertahankan 100%):

```
Gaji Pokok      = data_jabatan.gaji_pokok (via jabatan_id pegawai)
Tj. Transport   = data_jabatan.tj_transport
Uang Makan      = data_jabatan.uang_makan
Potongan Alpha  = jumlah_hari_alpha × nominal_potongan_alpha_aktif

Total Dasar     = Gaji Pokok + Tj. Transport + Uang Makan − Potongan Alpha
```

Formula final (v2, dengan generalisasi tunjangan/potongan granular baru):

```
Total Tunjangan Tambahan = SUM(tunjangan_gaji aktif yang berlaku untuk pegawai/jabatan ini)
Total Potongan Tambahan  = SUM(potongan_gaji aktif selain Alpha yang berlaku untuk pegawai ini)
Honor Kelebihan SKS      = MAX(0, sks_terpakai − sks_maksimal) × honor_per_kategori   [khusus dosen]

TOTAL GAJI = Total Dasar + Total Tunjangan Tambahan + Honor Kelebihan SKS − Total Potongan Tambahan
```

Aturan wajib:
- Tidak boleh ada nilai negatif di komponen manapun — jika hasil pengurangan < 0, clamp ke 0 dan log warning.
- Semua nominal disimpan sebagai `decimal(15,2)`, tidak pernah `float`/`double`.
- Perhitungan **wajib** menyimpan breakdown per komponen (bukan cuma total akhir) di `payroll_details` — untuk keperluan audit dan tampilan slip gaji yang rinci.

## 7. Data Model Ringkas

Field lengkap ada di DESIGN.md §3 (migration files). Ringkasan entitas utama:

| Entitas | Keterangan |
|---|---|
| `users` | Login (username, password, role via Spatie) — **dipisah** dari `pegawai` (1 user bisa jadi 1 pegawai, tapi struktur auth generik Laravel) |
| `pegawai` | Data master pegawai (dulu `data_pegawai`), FK `jabatan_id`, FK `user_id` |
| `jabatan` | Dulu `data_jabatan` |
| `struktural`, `fungsional` | Dulu `data_struktural`, `data_fungsional` |
| `kehadiran` | Dulu `data_kehadiran`, FK `pegawai_id`, kolom `periode` bertipe date |
| `potongan_gaji` | Master potongan generik |
| `tunjangan_gaji` | Master tunjangan generik (baru, menggantikan 10 menu broken) |
| `tahun_akademik` | Master tahun akademik/semester |
| `dosen_sks` | Dulu `set_sks_dosen` |
| `kategori_honor_sks`, `honor_sks`, `honor_sks_log` | Dulu `kategori_honor_kelebihan_sks`, `honor_kelebihan_sks`, `honor_kelebihan_sks_log` |
| `payroll_runs`, `payroll_details` | **Baru** — snapshot hasil hitung gaji per periode (menggantikan cara lama "hitung ulang tiap dibuka") |
| `payroll_anomalies` | **Baru** — hasil AI Anomaly Detection |

## 8. Kriteria Sukses (Acceptance Criteria Global)

- [ ] Seluruh 23 modul di tabel Ringkasan Modul dokumen analisis asli (§11) punya padanan fungsional di Laravel, tidak ada regresi fitur.
- [ ] 0 broken link — seluruh menu sidebar mengarah ke route yang benar-benar ada dan berfungsi.
- [ ] Formula gaji identik hasilnya dengan sistem lama untuk data historis (validasi: jalankan `PayrollService` untuk 3 periode data lama, bandingkan total dengan hasil di sistem CI3 — toleransi 0, harus sama persis).
- [ ] Semua 4 role bisa login dan hanya melihat menu/data sesuai kewenangannya (tidak ada horizontal/vertical privilege escalation — divalidasi via Pest authorization test per modul).
- [ ] Test coverage minimal: setiap Service class dan setiap FormRequest punya Feature/Unit test.
- [ ] Modul AI berjalan dengan fallback graceful ketika API key AI tidak tersedia (tidak crash aplikasi).
- [ ] Migrasi data dari database CI3 lama ke skema baru berjalan lewat 1 Artisan command (`php artisan simppay:migrate-legacy`) yang idempotent (bisa dijalankan ulang tanpa duplikasi).
- [ ] **UI/UX sesuai `DESIGN.md — SIMPPAY UI-UX Design System.md`**: design tokens (#176B5B primary), sidebar hierarchy, command search (Ctrl+K), editorial layout, progressive disclosure, no emoji, no excessive gradient, no generic SaaS dashboard, no glassmorphism — lihat checklist lengkap di §78 dokumen UI/UX.

## 9. Asumsi & Risiko

| Risiko | Mitigasi |
|---|---|
| Data produksi lama tidak konsisten (jabatan string typo, bulan format aneh) | Command migrasi legacy wajib punya *dry-run* mode + laporan baris yang gagal dimapping, direview manual sebelum commit |
| Password lama pakai hashing lemah (kemungkinan md5/plaintext, khas CI3 lama) | Lazy rehash saat login pertama (FR-04), tidak reset password massal |
| Formula gaji ternyata ada exception tak terdokumentasi di kode (mis. rounding khusus) | Wajib baca ulang source `Data_Penggajian.php`, `Laporan_Gaji.php`, `Slip_Gaji.php` asli baris-per-baris sebelum menulis `PayrollService` — jangan asumsi dari dokumen analisis saja |
| Tim/agent AI tergoda bikin skema "ideal" yang menyimpang dari kebutuhan riil field-field administratif kepegawaian Indonesia (NIDN, NUPTK, dll) | DESIGN.md §3 wajib mempertahankan seluruh field yang sudah ada di ERD asli, tidak boleh dihapus tanpa alasan eksplisit |

## 10. Dokumen Terkait

- `DESIGN.md` — arsitektur, skema database lengkap (migration-ready), struktur folder Laravel 12 + Vue/Inertia, kontrak API.
- `DESIGN.md — SIMPPAY UI-UX Design System.md` — pedoman UI/UX lengkap: design tokens, warna, tipografi, spacing, komponen, layout per halaman, motion system, aksesibilitas, responsive breakpoints. **Wajib diikuti** saat implementasi Vue Pages/Components untuk area Admin, BPSDM, dan Portal.
- `AGENTS.md` — aturan main untuk AI coding agent (OpenCode) yang mengerjakan proyek ini.
- `SPRINTS.md` — breakdown 12 sprint dari setup sampai deploy.
- `PROMPTS.md` — prompt siap-pakai per sprint untuk dijalankan agent.
