# SPRINTS.md — SIMPPAY-Laravel

12 sprint, masing-masing diasumsikan 1 unit kerja agent (bisa lintas beberapa sesi). Setiap sprint punya **Definition of Done (DoD)** eksplisit — sprint berikutnya tidak boleh mulai sebelum DoD sprint sebelumnya lulas (kecuali dependency-nya memang paralel, ditandai).

---

## Sprint 0 — Setup Proyek & Fondasi
**Tujuan:** Skeleton Laravel 12 + Vue 3/Inertia siap dikembangkan di XAMPP, tooling terpasang.

- Install Laravel 12 di `htdocs` XAMPP, set `.env` (DB MariaDB bawaan XAMPP, 2 koneksi: default + `legacy` untuk migrasi data lama).
- Install Breeze starter kit varian **Vue + Inertia** (`php artisan breeze:install vue`) sebagai fondasi auth+SPA, lalu install & konfigurasi: spatie/laravel-permission, Sanctum, Laravel Excel, laravel-dompdf, laravel/ai, Pest.
- `npm install` dependency frontend (Vue 3, Inertia, Tailwind sudah dari Breeze) + tambahan: Motion, Chart.js, Floating UI, Tom Select, FilePond, Lucide.
- Setup Pint (code style), setup `.env.example` lengkap termasuk kredensial DB XAMPP default dan variabel AI (`AI_BASE_URL`/`AI_API_KEY`/`AI_MODEL`).
- `HandleInertiaRequests` middleware: share `auth.user` + role ke semua halaman Vue (lihat DESIGN.md §9).
- Route group dasar `/admin`, `/bpsdm`, `/portal` dengan middleware role (kosong dulu, cuma dashboard placeholder).
- `RoleSeeder`: buat 4 role (`admin`, `pegawai`, `bpsdm`, `tendik`) via spatie.
- Setup CI dasar (opsional, kalau ada — minimal script `composer test`).

**DoD:** Apache XAMPP + `npm run dev` jalan bersamaan, `/admin`, `/bpsdm`, `/portal` bisa diakses (dashboard placeholder Vue, masih kosong tapi ter-render lewat Inertia), `php artisan test` jalan tanpa error (walau 0 test riil), `RoleSeeder` berhasil dijalankan.

---

## Sprint 1 — Auth & Otorisasi Multi-Role
**Tujuan:** FR-01 s/d FR-05 (PRD §5.1).

- Migration `users` (custom, dengan `username`, `legacy_password_hash`, `legacy_password_migrated`).
- Login pakai `username` (bukan email) — custom Fortify/Breeze provider atau custom `LoginController`.
- Redirect pasca-login berdasarkan role: admin→`/admin`, bpsdm→`/bpsdm`, pegawai/tendik→`/portal`.
- Middleware `EnsureRoleMatchesGuard`.
- Lazy password rehash: cek `legacy_password_hash` saat login, verify manual (implementasi verifikasi sesuai algoritma hash CI3 lama — **cek dulu source `Login.php` asli**, jangan asumsi md5 polos), kalau cocok → set `password` bcrypt baru, `legacy_password_migrated = true`, hapus `legacy_password_hash`.
- Rate limiting login (Laravel `RateLimiter`, 5x/menit).
- Ganti password (semua role) — form + validasi.

**DoD:** Feature test: login sukses per role → redirect benar; login gagal → rate limited setelah 5x; lazy rehash test (user dengan `legacy_password_hash` bisa login lalu field ter-migrate); ganti password test.

---

## Sprint 2 — Data Master: Jabatan, Struktural, Fungsional, Pegawai
**Tujuan:** FR-06 s/d FR-13 (PRD §5.2, §5.3).

- Migration + Model + Controller (`Admin/JabatanController`, dst) + Vue Page (`Index.vue` modal form): `Jabatan`, `Struktural`, `Fungsional`.
- Migration + Model + `Admin/PegawaiController` + Vue Page `Pegawai/Index.vue` & `Show.vue` (field lengkap sesuai DESIGN.md §3), relasi `pegawai_anak` ditampilkan di tab Keluarga.
- Upload foto (`photo`, `foto_sk`) via FilePond (Vue wrapper, UI/UX doc §21), validasi tipe/ukuran di FormRequest.
- Import/Export Excel `Pegawai` (Laravel Excel Import/Export class, endpoint terpisah non-Inertia untuk upload/download file — bukan inline).
- Guard hapus `Jabatan` jika masih dipakai pegawai aktif.
- Soft delete pegawai.
- `PegawaiPolicy`: admin full akses, bpsdm read-only, pegawai/tendik hanya lihat data sendiri.
- Route `/bpsdm/pegawai` HANYA daftar `index`/`show` (lihat DESIGN.md §9 — bukan resource penuh).
- **UI/UX**: employee directory layout (UI/UX doc §19), employee profile page (UI/UX doc §20) dengan tabs (Overview, Kepegawaian, Keluarga, Kehadiran, Payroll, SKS, Documents). Filter bar: Search, Jabatan, Status, Struktural, Fungsional — semua filter lewat query string + `router.get` Inertia (server-side).

**DoD:** CRUD pegawai lengkap via `/admin/pegawai` berfungsi; `/bpsdm/pegawai` read-only (tombol create/edit/delete tidak render di Vue, DAN route mutasi 404 kalau diakses paksa via URL karena memang tidak didaftarkan — test ini eksplisit); import/export Excel test dengan file sample; authorization test 4 role; employee directory & profile sesuai UI/UX design system.

---

## Sprint 3 — Absensi/Kehadiran
**Tujuan:** FR-14 s/d FR-17 (PRD §5.4).

- Migration + Model + `Admin/KehadiranController` + Vue Page `Kehadiran/Index.vue`, kolom `periode` bertipe date.
- Input satuan (modal form Vue + `useForm`) + input batch (Import Excel dengan template kolom: NIK, hadir, sakit, alpha).
- Constraint unik `(pegawai_id, periode)` — test insert duplikat harus gagal dengan pesan jelas (ditampilkan sebagai Inertia validation error di form).
- Filter tabel: bulan/tahun/pegawai/jabatan (query string, server-side).

**DoD:** Input absensi satuan & batch berfungsi; constraint unik teruji; filter berfungsi di tabel Vue dengan pagination server-side.

---

## Sprint 4 — Setting Potongan & Tunjangan (Generalisasi Menu Broken Lama)
**Tujuan:** FR-18 s/d FR-21 (PRD §5.5) — ini secara langsung menutup 15 broken link di sistem lama.

- Migration + Model + `Admin/PotonganGajiController` + Vue Page `PotonganGaji/Index.vue` (dengan flag `is_alpha_penalty`).
- Migration + Model + `Admin/TunjanganGajiController` + Vue Page `TunjanganGaji/Index.vue` (dengan `target_tipe`: semua/jabatan/pegawai).
- Seeder data awal: minimal 1 baris potongan Alpha (`is_alpha_penalty = true`) supaya formula dasar tidak error kalau kosong.
- Toggle aktif/nonaktif inline.

**DoD:** CRUD berfungsi; test bahwa hanya 1 baris `potongan_gaji` boleh punya `is_alpha_penalty = true` per waktu (atau didefinisikan sebagai "yang aktif pertama dipakai" — putuskan & dokumentasikan perilakunya di komentar kode + DESIGN.md kalau berbeda dari draft awal).

---

## Sprint 5 — PayrollService (Core Business Logic) — **Sprint Paling Kritis**
**Tujuan:** FR-22 s/d FR-25 (PRD §5.6), formula PRD §6, kontrak DESIGN.md §4.

- **Sebelum coding:** baca ulang source asli `Data_Penggajian.php`, `Laporan_Gaji.php`, `Slip_Gaji.php` dari SIMPPAY CI3 (kalau tersedia di repo/upload) baris-per-baris untuk memastikan tidak ada edge case tersembunyi (rounding, pembulatan, pengecualian jabatan tertentu) yang tidak tercatat di dokumen analisis.
- Buat `PayrollCalculationResult` DTO.
- Implementasi `PayrollService::calculate()` — hitung 1 pegawai 1 periode, sesuai formula PRD §6 persis.
- Implementasi `PayrollService::calculateBatch()` dipanggil dari `CalculatePayrollJob` (queued), simpan ke `payroll_runs` + `payroll_details`.
- Implementasi `finalize()` dan `void()`.
- Migration `payroll_runs`, `payroll_details`.
- `Admin/PayrollRunController` + Vue Page `PayrollRun/Index.vue` (overview) & `Show.vue` (tabel + `PayrollDetailDrawer`) dengan action Hitung Gaji / Finalize / Void (tombol POST via `router.post`, konfirmasi lewat Modal sebelum submit untuk Finalize/Void karena destruktif/terkunci).
- Event `PayrollFinalized` + Listener dasar (placeholder notifikasi).

**DoD:** Unit test `PayrollServiceTest` dengan **data historis riil** (diberikan Yang Mulia atau diekstrak dari dump lama) membuktikan hasil identik dengan sistem lama untuk minimal 3 periode berbeda (PRD §8 acceptance criteria — ini wajib, bukan opsional); test idempotency `calculateBatch` (jalan 2x sebelum finalize = replace, bukan duplikat); test bahwa `finalize()` mengunci run dari re-calculate; test bahwa hasil komponen tidak pernah negatif (clamp ke 0).

---

## Sprint 6 — SKS Dosen & Honor Kelebihan SKS
**Tujuan:** FR-26 s/d FR-31 (PRD §5.7).

- Migration + Model + `Admin/TahunAkademikController` + Vue Page `TahunAkademik/Index.vue` (action "Aktifkan" yang otomatis nonaktifkan yang lain — service-enforced di server, bukan cuma disable tombol di Vue).
- Migration + Model + `Admin/DosenSksController` + Vue Page `DosenSks/Index.vue`, dengan job/command untuk insert default SKS ke dosen baru saat tahun akademik baru dibuat/diaktifkan.
- Migration + Model + `Admin/HonorSksController` + Vue Page `HonorSks/Index.vue` untuk `KategoriHonorSks`/`HonorSks` (dengan model event `updating` yang otomatis insert ke `HonorSksLog`), plus tab/tombol "Lihat Log" (read-only di UI, tabel audit).
- `SksService::hitungHonorKelebihan()` — formula PRD §5.7 FR-31, dipakai `PayrollService` untuk komponen `honor_kelebihan_sks`.

**DoD:** Test bahwa hanya 1 tahun akademik aktif dalam satu waktu; test insert default SKS untuk dosen baru; test bahwa update `HonorSks` SELALU menghasilkan baris baru di `HonorSksLog` (tidak bisa silent update — test ini eksplisit mengecek log-nya ada); integrasi dengan `PayrollService` (dosen dengan kelebihan SKS mendapat komponen honor di slip gaji).

---

## Sprint 7 — Laporan, Cetak PDF, Export Excel
**Tujuan:** FR-32 s/d FR-35 (PRD §5.8).

- Blade template PDF (`resources/views/pdf/`): slip-gaji, laporan-gaji, laporan-absensi — pakai DomPDF.
- Tombol "Cetak" di Vue (`PayrollDetailDrawer`, slip individual) dan di halaman laporan (agregat) — link `<a target="_blank">` ke route yang me-return response PDF langsung dari server (bukan lewat Inertia visit, karena Inertia tidak dipakai untuk response non-HTML).
- Export Excel: `LaporanGajiExport`, `LaporanAbsensiExport` (class terpisah, Laravel Excel).
- Policy slip gaji: admin/bpsdm bisa cetak siapa saja, pegawai/tendik hanya diri sendiri (`SlipGajiPolicy`) — **wajib** test ini karena ini exact scenario kebocoran privilege dari sistem lama.

**DoD:** PDF ter-generate dengan data benar (breakdown dari `payroll_details.breakdown_json`); export Excel test (baca ulang file hasil export, cocokkan isi); authorization test slip gaji per role, termasuk test negatif (pegawai A tidak bisa akses slip pegawai B via URL manipulation).

---

## Sprint 8 — Portal Self-Service Pegawai/Tendik
**Tujuan:** FR-36 s/d FR-38 (PRD §5.9).

- `Portal/DashboardController` + Vue Page `Portal/Dashboard.vue`: info pegawai, slip terbaru, grafik 6 bulan terakhir (Chart.js) — layout sesuai UI/UX doc §32-§33 (personal workspace, greeting, latest salary card, employment info, 6-month chart). Data agregat 6 bulan dihitung di controller, dikirim sebagai props siap-pakai.
- `Portal/SlipGajiController` + Vue Page `Portal/SlipGaji.vue`: pilih periode (dropdown hanya yang `finalized`, query di controller), tampil + tombol cetak — layout sesuai UI/UX doc §34 (breakdown pendapatan/potongan, action download PDF/print). Privacy toggle untuk nominal (state lokal Vue `ref`, tidak perlu roundtrip server).
- `Portal/ProfileController` + Vue Page `Portal/Profile.vue`: ganti password.
- `PortalLayout.vue` terpisah dari `AdminLayout.vue`/`BpsdmLayout.vue` (tidak reuse) — mobile: bottom navigation (Home, Slip, History, Profile) sesuai UI/UX doc §35.
- Design tokens: primary #176B5B, bg #F6F7F4, tipografi Inter, spacing 4px basis.

**DoD:** Login sebagai pegawai/tendik → dashboard tampil data benar; slip gaji hanya periode finalized yang muncul di dropdown; ganti password berfungsi; test bahwa portal tidak bisa akses route `/admin`/`/bpsdm` (403, karena middleware role berbeda dan route mutasi admin memang tidak ada di group `pegawai,tendik`); mobile responsive dengan bottom navigation; sesuai UI/UX design system §32-§35.

---

## Sprint 9 — REST API & Webhook (Integrasi Ekosistem)
**Tujuan:** FR-44 s/d FR-46 (PRD §5.11), kontrak DESIGN.md §6.

- Setup Sanctum, personal access token untuk service-to-service.
- Seluruh endpoint di tabel DESIGN.md §6 (`Controllers/Api/V1/*`), pakai API Resource classes untuk transform response.
- `POST /api/v1/penggajian/process` — trigger job async, return `job_id`, endpoint terpisah untuk polling status job.
- `WebhookController` + `VerifyWebhookSignature` middleware (HMAC), `ProcessAbsensiWebhookJob` (idempotent — cek dulu apakah data absensi periode tsb sudah ada sebelum insert/update).

**DoD:** Test setiap endpoint API (happy path + auth failure + scope failure); test webhook signature invalid ditolak; test webhook idempotent (kirim payload sama 2x, hasil di DB tidak duplikat).

---

## Sprint 10 — Modul AI (`laravel/ai`)
**Tujuan:** FR-39 s/d FR-43 (PRD §5.10), aturan §5 AGENTS.md, arsitektur DESIGN.md §8b.

- `composer require laravel/ai`, publish & migrate (tabel `agent_conversations`, `agent_conversation_messages`), setup provider di `config/ai.php` (driver `openai-compatible` mengarah ke endpoint OpenRouter, plus 1 provider cadangan untuk failover).
- `php artisan make:agent PayrollAssistantAgent` (FR-39) + Tool classes (`GetTotalGajiByJabatan`, `GetTotalGajiByPeriode`, `GetDistribusiGajiPerJabatan`) — masing-masing `schema()` sempit, `handle()` query via `PayrollService`.
- `php artisan make:agent AnomalyReviewAgent --structured` (FR-40) dipanggil dari `DetectPayrollAnomaliesJob` (scheduled setelah `PayrollFinalized` event) — filter numerik deviasi >30% (config-able) dilakukan di PHP dulu, agent cuma menulis narasi terstruktur (`ringkasan`, `kemungkinan_penyebab`, `rekomendasi_tindakan`) ke `payroll_anomalies.catatan_ai`.
- `php artisan make:agent ReportGeneratorAgent --structured` (FR-41) — dipanggil on-demand dari action "Generate Insight AI".
- `php artisan make:agent HrChatbotAgent` (FR-42), implementasi `Conversational` + `RemembersConversations`, Tool `GetSlipGajiMilikSendiri`/`GetRiwayatAbsensiPegawai` menerima `Pegawai` via **constructor agent**, bukan argumen schema — endpoint `/api/v1/ai/chat` panggil `(new HrChatbotAgent($request->user()->pegawai))->forUser($request->user())->prompt(...)`.
- Fallback: setiap controller yang memanggil agent wajib try-catch generik → response jelas "fitur AI sementara tidak tersedia", **bukan** error 500 yang crash halaman lain.

**DoD:** Semua test AI pakai `Agent::fake()`/`preventStrayPrompts()` (jangan hit API asli di test suite); test fallback ketika fake agent dikonfigurasi throw exception; test struktural bahwa `HrChatbotAgent` untuk pegawai A tidak punya tool yang bisa mengembalikan data pegawai B (assert lewat konstruksi Tool, bukan cuma lewat isi jawaban teks); test Anomaly Detection dengan data dummy yang sengaja dibuat anomali dan yang normal, verifikasi hanya yang anomali yang masuk tabel dan `catatan_ai` terisi hasil fake structured output.

---

## Sprint 11 — Migrasi Data Legacy
**Tujuan:** DESIGN.md §7, command `simppay:migrate-legacy`.

- Setup koneksi DB kedua (`legacy`) di `config/database.php`.
- `LegacyMigrationService` + `MigrateLegacyDataCommand` (`--dry-run` dan `--commit`).
- Migrasi berurutan sesuai DESIGN.md §7 (jabatan → struktural/fungsional → pegawai+users → pegawai_anak → kehadiran).
- Laporan dry-run: baris berhasil vs gagal + alasan, dalam bentuk yang mudah direview manusia (tabel console atau CSV).
- Verifikasi manual format `data_kehadiran.bulan` dari sample data nyata sebelum menulis parser (jangan asumsi).

**DoD:** Dry-run terhadap data sample/dump lama menghasilkan laporan akurat; commit idempotent (jalan 2x tidak duplikat, tes eksplisit); minimal 95% baris pegawai lama berhasil termapping otomatis (sisanya di laporan gagal untuk direview manual — ini realistis, bukan target 100% palsu).

---

## Sprint 12 — QA Akhir, Regression, & Deploy Prep
**Tujuan:** PRD §8 seluruh acceptance criteria, siap production.

- Full regression: jalankan seluruh test suite, pastikan hijau semua.
- Verifikasi 0 broken link — audit seluruh menu sidebar admin/bpsdm/portal terhadap route yang benar-benar ada.
- Verifikasi formula gaji identik dengan sistem lama (re-run acceptance test Sprint 5 dengan dataset final).
- Review keamanan dasar: rate limiting, CSRF (Inertia otomatis mengirim token CSRF dari cookie Laravel bawaan, tapi verifikasi tetap dilakukan — cek header `X-XSRF-TOKEN` terkirim di tiap request Inertia POST/PUT/DELETE), validasi upload file, HMAC webhook.
- Siapkan `.env.production.example`, dokumentasi deploy singkat (queue worker perlu jalan sebagai daemon/supervisor untuk `CalculatePayrollJob` dan job AI).
- Seed data produksi awal (role admin pertama, dsb) via seeder yang aman dijalankan sekali di production.
- **UI/UX audit**: jalankan checklist UI/UX doc §78 (no emoji, no excessive gradient, no glassmorphism, consistent icons, sidebar hierarchy, command search, responsive, accessibility, loading/empty/error states, motion bukan dekoratif).

**DoD:** Seluruh checklist PRD §8 tercentang; seluruh checklist UI/UX doc §78 tercentang; `php artisan test` 100% pass; tidak ada `dd()`/`dump()`/debug code tersisa; queue worker terkonfigurasi dan terdokumentasi.
