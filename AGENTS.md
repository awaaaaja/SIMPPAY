# AGENTS.md — SIMPPAY-Laravel

Instruksi operasional untuk AI coding agent (OpenCode) yang mengerjakan repo ini. Baca file ini **sebelum** menyentuh kode di setiap sesi baru. Ikuti bersama `PRD.md`, `DESIGN.md`, dan `DESIGN.md — SIMPPAY UI-UX Design System.md` — dokumen ini adalah "cara kerja", tiga dokumen lain adalah "apa yang dikerjakan".

---

## 1. Alur Kerja Wajib (stage-gated)

Setiap task (baik dari `SPRINTS.md` maupun ad-hoc dari user) **wajib** melalui 5 tahap ini, secara eksplisit, jangan loncat:

1. **THINKING** — baca ulang PRD/DESIGN bagian terkait, cek kode yang sudah ada (`grep`/`glob`), rencanakan file apa saja yang akan dibuat/diubah, dan urutan eksekusi. Tulis rencana singkat sebelum mulai coding.
2. **BUILD** — implementasi. Migration → Model → Service → Policy → Controller (Inertia) → Route → Vue Page/Component. Urutan ini penting: jangan bikin Controller sebelum Service-nya ada, dan jangan bikin Vue Page sebelum Controller-nya mengirim shape props yang jelas.
3. **EKSEKUSI** — jalankan: `php artisan migrate:fresh --seed` di environment testing, jalankan `php artisan test` (Pest), pastikan tidak ada error, jalankan linter (`./vendor/bin/pint`).
4. **REVIEW** — cek ulang terhadap acceptance criteria di PRD §8 dan requirement spesifik sprint terkait. Cek apakah ada modul lama yang jadi patah (regresi) karena perubahan ini.
5. **PERBAIKI** — kalau ada yang gagal di REVIEW, perbaiki di tahap ini, JANGAN lanjut ke task berikutnya sebelum semua test hijau.

Jangan pernah menandai sebuah sprint/task selesai kalau test belum lulus atau ada TODO yang disembunyikan diam-diam di kode.

---

## 2. Aturan Sumber Kebenaran (Source of Truth)

- `PRD.md` = **apa** yang harus dibangun dan **kenapa**. Kalau ada requirement yang ambigu, PRD menang.
- `DESIGN.md` (teknis) = **bagaimana** strukturnya — nama tabel/kolom/kelas/endpoint di sana **normatif**, jangan diganti nama seenaknya (mis. jangan bikin tabel `employees` kalau DESIGN.md bilang `pegawai`) kecuali ada alasan teknis kuat, dan kalau iya, **update DESIGN.md juga** di commit yang sama.
- `DESIGN.md — SIMPPAY UI-UX Design System.md` = **bagaimana tampilannya** — design tokens, warna (#176B5B primary), tipografi (Inter), spacing (4px base), komponen, layout per halaman, motion, aksesibilitas. **Wajib diikuti** saat implementasi Vue Pages/Components (Admin, BPSDM, Portal) dan PDF templates. Jangan membuat UI generic/admin template — ikuti design system ini. Karena stack sekarang Vue 3 + Inertia (bukan Filament/Livewire), seluruh komponen UI dibangun custom sesuai dokumen ini — tidak ada "keterbatasan framework admin" yang bisa dijadikan alasan menyimpang.
- Dokumen asli hasil analisis SIMPPAY CI3 (yang jadi sumber PRD/DESIGN ini) adalah rujukan untuk **business rule** (khususnya formula gaji di PRD §6) — kalau ragu soal detail perilaku lama, formula gaji adalah bagian paling kritis dan **tidak boleh diubah** tanpa konfirmasi eksplisit ke Yang Mulia.
- Kalau kamu (agent) menemukan requirement di PRD yang ternyata tidak bisa diimplementasikan seperti tertulis (mis. constraint teknis Laravel/Vue/Inertia), **jangan diam-diam menyimpang** — tulis catatan di komentar/commit message, dan kalau perubahannya signifikan, tanyakan dulu.

---

## 3. Aturan Perubahan Skema

- Skema di DESIGN.md §3 sudah final untuk MVP. Kalau butuh kolom/tabel tambahan saat implementasi (hal wajar), **tambahkan migration baru**, jangan edit migration lama yang sudah pernah dijalankan/di-commit.
- Perubahan struktural (normalisasi tabel, split/merge kolom) — seperti contoh `pegawai_anak` yang sudah diputuskan di DESIGN.md — harus **didokumentasikan alasannya** di DESIGN.md, bukan cuma di kode.
- **Tidak boleh** menghapus field yang ada di ERD asli (dokumen analisis SIMPPAY CI3) tanpa alasan eksplisit tertulis — field kepegawaian Indonesia (NIDN, NUPTK, NIK, dst) semuanya punya fungsi administratif riil meskipun terlihat berlebihan.

---

## 4. Aturan Kode

- **PayrollService adalah satu-satunya tempat logic hitung gaji boleh ada.** Kalau kamu menemukan dirimu menulis rumus gaji (perkalian/penjumlahan komponen gaji) di Controller, komponen Vue, atau di mana pun di frontend — STOP, itu salah, panggil `PayrollService` di server, kirim hasilnya sebagai props sudah-jadi ke Vue.
- Semua nominal uang: `decimal(15,2)` di DB, gunakan `Brick\Money` atau minimal cast `decimal:2` di Model — **jangan pernah** `float`/`double` untuk uang.
- Setiap Service class wajib final class + constructor injection (bukan Facade/helper acak) supaya testable.
- Setiap FormRequest wajib punya `authorize()` yang benar-benar cek permission (bukan `return true;` asal-asalan) kecuali route tersebut memang publik.
- Setiap modul CRUD baru wajib: Migration + Model (dengan relasi & cast lengkap) + Policy + Factory + minimal 1 Feature test (happy path) + 1 authorization test (role yang salah harus ditolak).
- Import/export Excel **wajib** pakai Laravel Excel Export/Import class terstruktur, bukan raw PhpSpreadsheet inline di controller (ini salah satu alasan migrasi — lihat PRD §1).
- Jangan copy-paste query yang sama ke >1 tempat (ini masalah #2 di PRD §1 yang justru sedang diperbaiki). Kalau ada logic query yang dipakai di 2+ tempat, ekstrak ke method di Model (scope) atau Service.
- AI Service classes (§`Services/Ai/`) wajib punya try-catch dengan fallback yang jelas — kegagalan API AI **tidak boleh** melempar exception yang bikin halaman utama (bukan fitur AI) ikut error (lihat PRD FR-43).

### 4a. Aturan UI/UX (from DESIGN.md — SIMPPAY UI-UX Design System.md)

- **Design tokens wajib dipakai** — `--simppay-primary: #176B5B`, `--simppay-bg: #F6F7F4`, `--simppay-surface: #FFFFFF`. Jangan hardcode warna di luar token.
- **Tipografi**: Inter (atau IBM Plex Sans sebagai alternatif). Angka payroll pakai `font-variant-numeric: tabular-nums`.
- **Spacing**: basis 4px. Page padding desktop: 32px, tablet: 24px, mobile: 16px.
- **Border radius**: button/input: 10px, small card: 12px, panel: 16px, modal: 18px. Jangan `rounded-full` di semua component.
- **Shadow**: subtle — `0 1px 2px rgba(0,0,0,.04), 0 8px 24px rgba(0,0,0,.04)`. Hover: `0 10px 30px rgba(0,0,0,.07)`.
- **Icon system**: Lucide saja. Jangan mencampur Lucide/FontAwesome/Heroicons.
- **Sidebar**: hierarchy (OVERVIEW, PEOPLE, ATTENDANCE, PAYROLL, ACADEMIC, REPORTS, INTELLIGENCE). Active item: primary-soft bg + primary color + font-weight 600 + indicator kiri.
- **Dashboard**: editorial layout, bukan 10 KPI cards. Hero header + 4 metric utama + payroll overview + recent activity timeline.
- **Tabel**: angka align right, nama align left. Row hover: `#F8FAF8`. Click row → drawer, bukan halaman baru.
- **Progressive disclosure**: tabel ringkas dulu, detail saat row dibuka.
- **Motion**: page enter `opacity 0→1, translateY 8→0, 240ms`. Card stagger 40ms. Drawer: `translateX 24→0`. Number transition untuk animasi angka.
- **Loading**: skeleton mengikuti struktur konten, bukan spinner besar.
- **Empty state**: contextual explanation, bukan "No data."
- **No emoji**: tidak boleh ada emoji di UI, di mana pun.
- **No AI slop**: tidak boleh glowing purple gradient, neon, glass cards everywhere, floating blobs, fake AI brain graphics.
- **No generic dashboard**: "Welcome back, Admin!" tidak boleh — gunakan konteks payroll (periode aktif, jumlah karyawan, status payroll).
- **Chart**: Chart.js saja. No 3D. No radial chart untuk semuanya. Setiap chart harus menjawab 1 pertanyaan.
- **Library stack**: Vue 3 (Composition API), Inertia.js, Motion (npm install motion), Chart.js, Floating UI, Tom Select, FilePond, Lucide. Jangan install library hanya karena terlihat menarik.
- **Vue/Inertia customization**: bangun komponen UI primitif sendiri (`resources/js/Components/ui/`) sesuai design tokens — jangan pakai tabel/komponen bawaan yang tidak sesuai desain, tapi juga jangan membangun ulang hal yang server-side pagination Inertia sudah handle (lihat UI/UX doc §71).
- **BPSDM area**: read-only visual treatment — create/edit/delete tidak boleh tampil di Vue (kontrol lewat prop `can` dari controller), TAPI authorization sesungguhnya tetap server-side (Policy + route group yang cuma daftar route GET — lihat DESIGN.md §9), jangan pernah mengandalkan hanya penyembunyian tombol di Vue.
- **Portal**: personal workspace, bukan miniatur admin. Mobile: bottom navigation (Home, Slip, History, Profile). Privacy toggle untuk nominal gaji.
- **PDF**: clean, white background, institutional, readable. Bukan screenshot web.

## 5. Batasan Data Sensitif & AI

Modul AI dibangun dengan **`laravel/ai`** (Laravel AI SDK resmi, bukan library pihak ketiga) — lihat DESIGN.md §8b untuk arsitektur `Agent`/`Tool` lengkap. Aturan berikut wajib dipegang saat implementasi:

- Modul AI Chatbot HR (FR-42, `HrChatbotAgent`) untuk role pegawai/tendik **wajib** di-scope secara struktural, bukan cuma lewat instruksi prompt: `Tool` yang dipakai chatbot ini (`GetSlipGajiMilikSendiri`, `GetRiwayatAbsensiPegawai`) menerima objek `Pegawai` lewat **constructor** dari sisi server saat agent dibuat, bukan sebagai argumen schema yang bisa diisi model. Jangan pernah membuat Tool untuk chatbot ini yang menerima `pegawai_id`/`nik` sebagai parameter yang bisa dipilih bebas oleh model — itu membuka celah satu prompt "tampilkan juga punya NIK X" untuk bocor data pegawai lain.
- AI Payroll Assistant (FR-39, `PayrollAssistantAgent`) memakai `HasTools` dengan `Tool` class yang masing-masing punya `schema()` sempit (jabatan/periode saja) dan `handle()` yang query lewat `PayrollService`/scope Model — model LLM **tidak pernah** menyusun SQL sendiri, hanya memilih tool + argumen sesuai schema yang kamu definisikan. Ini yang dimaksud "whitelist" — diwujudkan lewat desain Tool, bukan lewat instruksi prompt yang bisa diabaikan model.
- `AnomalyReviewAgent`/`ReportGeneratorAgent` (FR-40, FR-41) memakai `HasStructuredOutput` — jangan minta agent mengembalikan teks bebas yang di-parse manual, definisikan `schema()` supaya hasilnya predictable dan gampang divalidasi sebelum disimpan.
- Jangan kirim data pegawai mentah (NIK, gaji individual per orang, dst) ke API AI eksternal lebih dari yang perlu untuk menjawab pertanyaan spesifik yang diajukan — agregasi di server (via Tool) dulu sebisa mungkin sebelum hasilnya masuk ke context model.
- Pakai fitur **failover** bawaan SDK (atribut `#[Provider([...])]` atau argumen `provider:` array) supaya modul AI tetap jalan kalau satu provider kena rate limit/down — bukan alasan untuk skip try-catch fallback di controller (FR-43 tetap wajib, failover SDK dan fallback aplikasi adalah dua lapis berbeda).
- Test AI **wajib** pakai fake bawaan (`Agent::fake()`, `preventStrayPrompts()`) — jangan pernah memanggil API AI asli di test suite (biaya, flaky, dan lambat).

## 6. Testing

- Pakai Pest, bukan PHPUnit style lama.
- Setiap sprint (lihat SPRINTS.md) punya Definition of Done yang eksplisit menyebut test apa yang harus ada — jangan skip.
- Test authorization (role isolation) adalah kelas test tersendiri (`*AuthorizationTest.php`) yang tidak boleh dianggap "opsional" — ini me-mitigasi masalah #7 di PRD (switch-case akses manual yang rawan bocor di sistem lama).
- Test kebenaran formula gaji (`PayrollServiceTest`) wajib pakai data historis riil (dari data yang di-provide Yang Mulia) untuk memverifikasi hasil identik dengan sistem lama, bukan cuma data dummy acak — lihat PRD §8 acceptance criteria.

## 7. Gaya Komunikasi saat Melapor ke User

- Laporkan progres secara langsung dan ringkas — tidak perlu banyak basa-basi, cukup: apa yang selesai, apa hasil testnya, apa langkah berikutnya.
- Kalau ada keputusan teknis yang setara (mis. pilih antara 2 pendekaan yang sama validnya), **putuskan sendiri** yang terbaik dan jalan — jangan tanya balik kecuali keputusannya berdampak besar/tidak reversibel (mis. mengubah skema yang sudah dipakai data produksi, atau requirement PRD yang ambigu secara bisnis).
- Deliverable harus lengkap dan siap-eksekusi (kode yang benar-benar jalan, bukan potongan/pseudo-code), sesuai gaya kerja yang sudah ditetapkan.

## 8. Command Cheat Sheet

Proyek dikembangkan di **XAMPP** (Windows/Linux/Mac) — taruh project di `htdocs` (mis. `C:\xampp\htdocs\simppay-laravel`), Apache+MariaDB dijalankan lewat XAMPP Control Panel, akses via `http://localhost/simppay-laravel/public` (atau setup Virtual Host XAMPP mengarah ke folder `public/` supaya URL bersih `http://simppay-laravel.test`).

```bash
# Setup awal — Laravel 12 dengan starter kit Vue + Inertia resmi
composer create-project laravel/laravel simppay-laravel
cd simppay-laravel
composer require laravel/breeze --dev
php artisan breeze:install vue        # scaffold auth + Inertia + Vue 3 + Tailwind resmi

composer require spatie/laravel-permission laravel/sanctum \
  maatwebsite/excel barryvdh/laravel-dompdf laravel/ai

npm install
npm install motion chart.js @floating-ui/dom tom-select filepond vue-filepond lucide-vue-next

php artisan vendor:publish --provider="Laravel\Ai\AiServiceProvider"
php artisan make:agent PayrollAssistantAgent
php artisan make:agent HrChatbotAgent
php artisan make:agent ReportGeneratorAgent --structured
php artisan make:agent AnomalyReviewAgent --structured
php artisan make:tool GetTotalGajiByJabatan

# .env: sesuaikan DB_* ke kredensial MariaDB bawaan XAMPP (default root, tanpa password)
# DB_CONNECTION=mysql / DB_HOST=127.0.0.1 / DB_PORT=3306 / DB_DATABASE=simppay / DB_USERNAME=root / DB_PASSWORD=

php artisan migrate:fresh --seed
php artisan test
./vendor/bin/pint

php artisan simppay:migrate-legacy --dry-run
php artisan simppay:migrate-legacy --commit

php artisan queue:work   # untuk CalculatePayrollJob, dsb — jalankan di terminal terpisah saat development
npm run dev              # Vite dev server untuk Vue (hot reload), terminal terpisah lagi
```

Tiga proses paralel yang wajib jalan saat development lokal di XAMPP: (1) Apache dari XAMPP Control Panel (atau `php artisan serve` sebagai alternatif tanpa Apache), (2) `php artisan queue:work`, (3) `npm run dev`.
