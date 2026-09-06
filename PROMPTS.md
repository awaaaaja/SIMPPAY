# PROMPTS.md — SIMPPAY-Laravel

Prompt siap-copy-paste ke OpenCode (atau agent lain) per sprint. Setiap prompt sudah mengasumsikan agent akan membaca `PRD.md`, `DESIGN.md`, `AGENTS.md`, `SPRINTS.md` di root repo — jadi prompt-nya singkat & merujuk, bukan mengulang seluruh isi dokumen (biar tidak boros konteks).

Cara pakai: taruh ke-4 file (`PRD.md`, `DESIGN.md`, `AGENTS.md`, `SPRINTS.md`) di root repo, lalu jalankan prompt sprint yang sesuai secara berurutan, satu sprint = satu sesi (atau lebih kalau perlu di-pecah).

---

### Prompt Sprint 0 — Setup

```
Baca PRD.md, DESIGN.md, AGENTS.md, dan SPRINTS.md di root repo ini.

Kerjakan Sprint 0 (Setup Proyek & Fondasi) dari SPRINTS.md, ikuti alur THINKING→BUILD→EKSEKUSI→REVIEW→PERBAIKI di AGENTS.md §1.

Ikuti stack teknologi persis seperti DESIGN.md §1 (Laravel 12, PHP 8.3, environment dev XAMPP, Vue 3 + Inertia.js — BUKAN Filament/Livewire, spatie/laravel-permission, Sanctum, Laravel Excel, laravel-dompdf, laravel/ai, Pest). Install lewat Breeze starter kit varian Vue (`php artisan breeze:install vue`), bukan setup Inertia manual dari nol.

Setelah selesai, jalankan `php artisan test` dan tunjukkan hasilnya, lalu laporkan checklist DoD Sprint 0 satu per satu (centang/tidak) — jangan klaim selesai kalau ada yang belum terverifikasi.
```

---

### Prompt Sprint 1 — Auth & Otorisasi

```
Baca PRD.md §5.1, DESIGN.md §2 (User model) dan §8 (multi-panel), AGENTS.md.

Kerjakan Sprint 1 (Auth & Otorisasi Multi-Role) dari SPRINTS.md.

PENTING soal lazy password rehash (FR-04): kalau source code CI3 asli (Login.php / ModelPenggajian.php) tersedia di repo atau di-upload, baca dulu algoritma hashing password yang dipakai sebelum implementasi verify-nya — jangan asumsi md5 polos tanpa cek. Kalau source tidak tersedia, implementasikan dengan asumsi eksplisit yang didokumentasikan di komentar kode, dan beri tahu saya asumsi apa yang dipakai supaya bisa saya verifikasi.

Tulis Feature test untuk: login sukses per role (4 role) dengan redirect yang benar, rate limiting 5x gagal, lazy rehash flow, ganti password.

Laporkan hasil test dan DoD Sprint 1.
```

---

### Prompt Sprint 2 — Data Master

```
Baca PRD.md §5.2-§5.3, DESIGN.md §3 (skema pegawai/jabatan/struktural/fungsional/pegawai_anak) dan §5 (Controller + Vue Page per modul), dan DESIGN.md — SIMPPAY UI-UX Design System.md §19 (employee directory) dan §20 (employee profile).

Kerjakan Sprint 2 dari SPRINTS.md: Migration + Model + Controller (Inertia) + Vue Page untuk Jabatan, Struktural, Fungsional, Pegawai (dengan seluruh field DESIGN.md §3 — JANGAN ada field yang hilang dari ERD asli tanpa alasan), relasi pegawai_anak ditampilkan di tab Keluarga, upload foto via FilePond (UI/UX doc §21), import/export Excel via Laravel Excel, guard hapus jabatan yang masih dipakai, soft delete pegawai, PegawaiPolicy 4-role, route /bpsdm/pegawai hanya daftar route baca (lihat DESIGN.md §9).

UI/UX: employee directory layout (§19), employee profile page (§20) dengan tabs (Overview, Kepegawaian, Keluarga, Kehadiran, Payroll, SKS, Documents). Filter bar: Search, Jabatan, Status, Struktural, Fungsional. Design tokens: primary #176B5B, bg #F6F7F4, spacing 4px basis, Inter font, Lucide icons.

Ingat: panel bpsdm harus mirror read-only — pastikan canCreate()/canEdit()/canDelete() di-override false, DAN tulis test yang membuktikan akses paksa via URL tetap ditolak (bukan cuma tombol UI yang disembunyikan).

Tulis authorization test 4 role + test import/export Excel dengan file sample. Laporkan hasil test dan DoD Sprint 2.
```

---

### Prompt Sprint 3 — Absensi

```
Baca PRD.md §5.4, DESIGN.md §3 (tabel kehadiran).

Kerjakan Sprint 3 dari SPRINTS.md: Migration+Model+Controller(Inertia)+Vue Page Kehadiran, input satuan & batch (Import Excel), constraint unik (pegawai_id, periode), filter tabel bulan/tahun/pegawai/jabatan (server-side via query string).

Perhatikan: kolom periode bertipe date (bukan string "MMYYYY" seperti sistem lama) — ini perbaikan struktural yang disengaja, jangan dikembalikan ke format lama.

Tulis test constraint unik dan test filter. Laporkan hasil test dan DoD Sprint 3.
```

---

### Prompt Sprint 4 — Setting Potongan & Tunjangan

```
Baca PRD.md §5.5 (perhatikan ini menutup 15 broken link di sistem lama — lihat PRD §1 poin 3), DESIGN.md §3 (potongan_gaji, tunjangan_gaji).

Kerjakan Sprint 4 dari SPRINTS.md: Migration+Model+Controller(Inertia)+Vue Page PotonganGaji (dengan flag is_alpha_penalty) dan TunjanganGaji (dengan target_tipe semua/jabatan/pegawai), seeder minimal 1 baris potongan Alpha aktif, toggle aktif/nonaktif inline (PATCH partial via router.patch).

Putuskan dan dokumentasikan (di komentar kode + tambahkan catatan singkat ke DESIGN.md §3 kalau perlu) perilaku kalau ada lebih dari 1 baris is_alpha_penalty=true — apakah dicegah di validasi (hanya boleh 1 aktif) atau diambil yang pertama. Pilih pendekatan yang paling aman (mencegah ambiguitas), lalu jalan.

Laporkan hasil test dan DoD Sprint 4.
```

---

### Prompt Sprint 5 — PayrollService (KRITIS)

```
Ini sprint paling kritis di seluruh proyek. Baca PRD.md §5.6 dan §6 (formula gaji — WAJIB dipahami persis, jangan modifikasi), DESIGN.md §4 (kontrak kelas PayrollService).

SEBELUM MULAI CODING: kalau source code asli SIMPPAY CI3 (Data_Penggajian.php, Laporan_Gaji.php, Slip_Gaji.php) tersedia di repo/upload, baca baris-per-baris dulu untuk cek ada tidaknya edge case tersembunyi (pembulatan, pengecualian). Laporkan temuan sebelum lanjut coding kalau ada perilaku yang tidak tercatat di PRD/DESIGN.

Kerjakan Sprint 5 dari SPRINTS.md secara lengkap: PayrollCalculationResult DTO, PayrollService::calculate()/calculateBatch()/finalize()/void(), migration payroll_runs+payroll_details, CalculatePayrollJob (queued), PayrollRunController + Vue Page (Index/Show) dengan action Hitung Gaji/Finalize/Void, event PayrollFinalized.

ATURAN KERAS dari AGENTS.md §4: rumus gaji HANYA boleh ada di PayrollService, tidak di Controller/View/Resource manapun.

Kalau saya sudah sediakan data historis (dump gaji periode lama beserta hasil totalnya), gunakan itu untuk PayrollServiceTest sebagai acceptance test — hasil harus identik persis (toleransi 0). Kalau belum saya sediakan, buat dulu test dengan data yang jelas terhitung manual di komentar test (supaya bisa saya verifikasi manual), dan ingatkan saya untuk menyediakan data historis riil sebelum sprint ini dianggap benar-benar final.

Laporkan hasil test dan DoD Sprint 5 satu per satu.
```

---

### Prompt Sprint 6 — SKS Dosen & Honor

```
Baca PRD.md §5.7, DESIGN.md §3 (tahun_akademik, dosen_sks, kategori_honor_sks, honor_sks, honor_sks_log).

Kerjakan Sprint 6 dari SPRINTS.md: TahunAkademik (action Aktifkan yang service-enforced hanya 1 aktif), DosenSks (default SKS otomatis untuk dosen baru), KategoriHonorSks/HonorSks (model event updating yang WAJIB insert ke HonorSksLog setiap perubahan — audit trail tidak boleh silent), SksService::hitungHonorKelebihan() sesuai formula PRD FR-31, integrasikan ke PayrollService (komponen honor_kelebihan_sks).

Tulis test yang eksplisit mengecek HonorSksLog benar-benar terisi setiap kali HonorSks diubah — ini requirement audit yang tidak boleh gagal diam-diam.

Laporkan hasil test dan DoD Sprint 6.
```

---

### Prompt Sprint 7 — Laporan, Cetak, Export

```
Baca PRD.md §5.8, DESIGN.md §4 (PayrollCalculationResult dipakai ulang di sini, jangan hitung ulang manual).

Kerjakan Sprint 7 dari SPRINTS.md: Blade PDF templates (slip-gaji, laporan-gaji, laporan-absensi) via DomPDF (response langsung, bukan lewat Inertia), tombol Cetak di Vue yang membuka route PDF di tab baru, LaporanGajiExport/LaporanAbsensiExport (Laravel Excel), SlipGajiPolicy (admin/bpsdm bebas, pegawai/tendik hanya diri sendiri).

PENTING: tulis test negatif eksplisit — pegawai A mencoba akses/cetak slip gaji pegawai B (baik lewat route langsung maupun manipulasi parameter) harus ditolak 403. Ini scenario kebocoran privilege yang paling gampang lolos kalau tidak ditest eksplisit.

Laporkan hasil test dan DoD Sprint 7.
```

---

### Prompt Sprint 8 — Portal Self-Service

```
Baca PRD.md §5.9, DESIGN.md §2 (Portal folder structure — Controller + Vue Page), dan DESIGN.md — SIMPPAY UI-UX Design System.md §32-§35 (portal layout).

Kerjakan Sprint 8 dari SPRINTS.md: Portal/DashboardController + Vue Page Dashboard.vue (info pegawai + slip terbaru + grafik 6 bulan Chart.js) sesuai §32-§33 (personal workspace, greeting, latest salary card, employment info, 6-month chart). Portal/SlipGajiController + Vue Page SlipGaji.vue (dropdown hanya periode finalized) sesuai §34 (breakdown pendapatan/potongan, download PDF/print). Privacy toggle untuk nominal (state lokal Vue). Portal/ProfileController + Vue Page Profile.vue untuk ganti password. PortalLayout.vue terpisah dari AdminLayout/BpsdmLayout — mobile: bottom navigation (Home, Slip, History, Profile) sesuai §35. Design tokens: primary #176B5B, bg #F6F7F4, spacing 4px basis.

Tulis test bahwa user role pegawai/tendik yang mencoba akses /admin atau /bpsdm mendapat 403, dan bahwa dropdown periode di SlipGaji tidak menampilkan payroll_run berstatus draft/calculated (belum finalized).

Laporkan hasil test dan DoD Sprint 8.
```

---

### Prompt Sprint 9 — REST API & Webhook

```
Baca PRD.md §5.11, DESIGN.md §6 (kontrak API lengkap).

Kerjakan Sprint 9 dari SPRINTS.md: setup Sanctum, seluruh endpoint di tabel DESIGN.md §6 dengan API Resource classes, endpoint process-async dengan job_id polling, WebhookController + VerifyWebhookSignature (HMAC) + ProcessAbsensiWebhookJob (idempotent).

Tulis test untuk tiap endpoint (happy path, auth failure, scope failure), test webhook signature invalid ditolak, dan test idempotency webhook (payload sama dikirim 2x, DB tidak duplikat).

Laporkan hasil test dan DoD Sprint 9.
```

---

### Prompt Sprint 10 — Modul AI

```
Baca PRD.md §5.10, DESIGN.md §8b (arsitektur Agent/Tool laravel/ai lengkap dengan contoh kode), AGENTS.md §5 (batasan data sensitif & AI — WAJIB dipatuhi, terutama scope struktural HR Chatbot via constructor injection dan larangan raw SQL generation dari LLM).

Kerjakan Sprint 10 dari SPRINTS.md menggunakan package resmi laravel/ai (composer require laravel/ai — BUKAN openai-php/laravel atau library lain): PayrollAssistantAgent + Tool classes (whitelist lewat schema Tool, bukan SQL bebas), AnomalyReviewAgent (structured output) + DetectPayrollAnomaliesJob (scheduled setelah PayrollFinalized, threshold 30% config-able), ReportGeneratorAgent (structured output), HrChatbotAgent (Conversational + RemembersConversations, Tool menerima Pegawai via constructor agent bukan argumen schema), semua dengan fallback try-catch di controller yang tidak crash halaman utama kalau API AI down. Setup config/ai.php dengan provider openai-compatible (OpenRouter) plus failover ke provider kedua.

Di test suite, pakai Agent::fake()/preventStrayPrompts() bawaan laravel/ai (jangan hit API asli) — test fallback ketika fake dikonfigurasi throw exception, test bahwa Tool yang dipakai HrChatbotAgent untuk pegawai A secara struktural tidak punya cara mengambil data pegawai B (bukan cuma test "AI menolak menjawab", tapi test bahwa Tool-nya memang di-construct dengan pegawai yang benar dan query di dalamnya hardcoded ke pegawai itu).

Laporkan hasil test dan DoD Sprint 10.
```

---

### Prompt Sprint 11 — Migrasi Data Legacy

```
Baca PRD.md §9 (risiko data lama), DESIGN.md §7 (strategi migrasi lengkap).

Kerjakan Sprint 11 dari SPRINTS.md: koneksi DB legacy kedua, LegacyMigrationService + MigrateLegacyDataCommand (--dry-run dan --commit), migrasi berurutan jabatan→struktural/fungsional→pegawai+users→pegawai_anak→kehadiran.

PENTING: kalau dump/akses database CI3 lama tersedia, cek dulu beberapa baris nyata kolom data_kehadiran.bulan untuk konfirmasi format string sebelum menulis parser tanggal — jangan asumsi "MMYYYY" seragam tanpa verifikasi. Kalau dump belum tersedia, beri tahu saya bahwa sprint ini butuh akses data legacy dulu sebelum bisa dianggap selesai, dan siapkan struktur command-nya saja dengan test memakai data dummy yang meniru kemungkinan format.

Laporkan laporan dry-run (jumlah baris sukses/gagal) dan DoD Sprint 11.
```

---

### Prompt Sprint 12 — QA Akhir & Deploy Prep

```
Baca PRD.md §8 (seluruh acceptance criteria).

Kerjakan Sprint 12 dari SPRINTS.md: full regression test, audit 0 broken link (cek seluruh menu sidebar admin/bpsdm/portal terhadap route yang benar-benar ada dan berfungsi — bandingkan dengan daftar 15 broken link asli di dokumen analisis untuk memastikan semua sudah tertutup), re-verifikasi formula gaji identik dengan sistem lama, review keamanan dasar, siapkan .env.production.example dan dokumentasi deploy singkat (termasuk kebutuhan queue worker daemon untuk job payroll & AI), seed data admin pertama untuk production.

Berikan laporan akhir dalam bentuk checklist PRD §8, item per item, centang/tidak, dengan bukti (nama test/command yang membuktikannya) — bukan klaim tanpa bukti.
```

---

## Prompt Umum untuk Sesi Ad-Hoc (di luar sprint terjadwal)

```
Baca PRD.md, DESIGN.md, AGENTS.md, dan DESIGN.md — SIMPPAY UI-UX Design System.md di root repo sebelum mengerjakan apapun.

[jelaskan task spesifik di sini]

Ikuti alur THINKING→BUILD→EKSEKUSI→REVIEW→PERBAIKI (AGENTS.md §1). Kalau task ini menyentuh logic gaji, wajib lewat PayrollService, tidak boleh ada rumus baru di tempat lain (AGENTS.md §4). Kalau task ini butuh perubahan skema, ikuti AGENTS.md §3. Kalau task ini menyentuh UI/UX, ikuti DESIGN.md — SIMPPAY UI-UX Design System.md — design tokens, tipografi, spacing, komponen, layout. Jangan membuat UI generic/admin template. Tulis/update test yang relevan, jangan tandai selesai sebelum test hijau.
```
