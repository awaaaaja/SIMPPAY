# DESIGN.md — SIMPPAY UI/UX DESIGN SYSTEM

## 0. STATUS DOKUMEN

Dokumen ini adalah pedoman UI/UX untuk SIMPPAY Laravel.

Dokumen ini melengkapi DESIGN.md teknis yang sudah ada.

UI/UX wajib mengikuti PRD.md dan tidak boleh mengubah business rule, database schema, role permission, PayrollService, API contract, atau arsitektur Laravel yang sudah ditetapkan.

Tujuan utama desain:

- terlihat seperti produk software payroll modern, bukan template admin dashboard generik
- kuat untuk pekerjaan administratif yang padat data
- interaktif tanpa menjadi dekoratif berlebihan
- cepat dipahami oleh Admin, BPSDM, Pegawai dan Tendik
- responsif dari desktop sampai mobile
- memiliki visual hierarchy yang jelas
- menggunakan motion secara fungsional
- tidak menggunakan emoji sebagai elemen UI
- tidak menggunakan dekorasi AI-generated yang tidak memiliki fungsi
- tidak menggunakan glassmorphism berlebihan
- tidak menggunakan gradient sebagai dekorasi utama
- tidak membuat setiap section menjadi card
- tidak menggunakan icon berbeda-beda style
- tidak membuat dashboard terlihat seperti template SaaS generik

---

# 1. DESIGN DIRECTION

## 1.1 Visual Concept

Nama arah visual:

**Editorial Payroll Operations**

SIMPPAY harus terasa seperti gabungan:

- payroll management system
- financial operations dashboard
- HR information system
- data administration workspace

Bukan seperti:

- landing page startup
- crypto dashboard
- template Tailwind generik
- AI dashboard penuh gradient
- dashboard dengan terlalu banyak glass effect

Visual harus memberikan kesan:

**precise, calm, trustworthy, structured, operational.**

---

# 2. CORE DESIGN PRINCIPLES

## 2.1 Data First

Data payroll adalah pusat interface.

Jangan membuat dekorasi lebih dominan daripada informasi.

Prioritas visual:

1. angka payroll
2. status proses
3. anomaly
4. periode
5. pegawai
6. tindakan yang dapat dilakukan
7. insight tambahan

---

## 2.2 Hierarchy Over Decoration

Gunakan hierarchy melalui:

- ukuran typography
- spacing
- weight
- border
- alignment
- background tone
- position

Bukan menggunakan:

- gradient besar
- glow
- shadow berlebihan
- pattern dekoratif
- floating blobs
- excessive animation

---

## 2.3 Progressive Disclosure

Jangan tampilkan semua informasi sekaligus.

Contoh:

Tabel payroll:

```text
Pegawai
Jabatan
Periode
Take Home Pay
Status
Action
```

Ketika row dibuka:

```text
Gaji Pokok
Transport
Uang Makan
Tunjangan
Potongan
Alpha
Honor SKS
Total
```

Dengan demikian interface tetap padat tetapi tidak melelahkan.

---

## 2.4 Contextual Actions

Action harus muncul dekat dengan objek yang dimodifikasi.

Contoh:

Payroll Run:

```text
Draft
   ↓
Calculate
   ↓
Calculated
   ↓
Review
   ↓
Finalize
```

Jangan menaruh seluruh action sebagai toolbar global.

---

# 3. BRAND CHARACTER

## 3.1 Personality

SIMPPAY harus terasa:

- professional
- intelligent
- institutional
- precise
- modern
- restrained
- reliable

Hindari:

- playful
- cartoon
- futuristic neon
- cyberpunk
- excessive AI visual
- decorative dashboard

---

# 4. COLOR SYSTEM

Gunakan warna sebagai semantic system.

## 4.1 Base

Primary background:

```text
#F6F7F4
```

Surface:

```text
#FFFFFF
```

Elevated surface:

```text
#FCFCFA
```

Primary text:

```text
#171918
```

Secondary text:

```text
#626865
```

Muted:

```text
#969D99
```

Border:

```text
#E3E6E3
```

Strong border:

```text
#CDD2CE
```

---

## 4.2 Primary Accent

Gunakan warna hijau gelap sebagai identitas operasional.

```text
Primary:
#176B5B

Primary Hover:
#125548

Primary Soft:
#E7F2EF
```

Jangan gunakan primary sebagai background seluruh halaman.

Gunakan sebagai:

- active navigation
- primary button
- progress
- selected state
- important KPI
- focus state

---

## 4.3 Semantic Colors

Success:

```text
#197A55
#E7F4EE
```

Warning:

```text
#A66A00
#FFF3D6
```

Danger:

```text
#B43B3B
#FBEAEA
```

Info:

```text
#3568A8
#EAF1FA
```

Anomaly:

```text
#8A4B2A
#F8ECE5
```

---

# 5. TYPOGRAPHY

Gunakan typography yang memiliki karakter editorial tetapi tetap sangat readable.

Recommended:

```text
Primary:
Inter
```

Alternative:

```text
IBM Plex Sans
```

Untuk angka payroll:

gunakan tabular numerals.

Contoh:

```css
font-variant-numeric: tabular-nums;
```

Angka:

```text
Rp 12.450.000
Rp 9.850.000
Rp 7.250.000
```

harus memiliki alignment numerik yang konsisten.

---

# 6. SPACING SYSTEM

Gunakan basis 4px.

```text
4
8
12
16
20
24
32
40
48
64
80
```

Default page padding desktop:

```text
32px
```

Tablet:

```text
24px
```

Mobile:

```text
16px
```

---

# 7. BORDER RADIUS

SIMPPAY tidak menggunakan radius ekstrem.

Recommended:

```text
button: 10px
input: 10px
small card: 12px
panel: 16px
modal: 18px
avatar: 999px
```

Jangan menggunakan:

```text
rounded-full
```

untuk seluruh component.

---

# 8. SHADOW

Shadow harus sangat subtle.

Default:

```css
box-shadow:
0 1px 2px rgba(0,0,0,.04),
0 8px 24px rgba(0,0,0,.04);
```

Hover:

```css
box-shadow:
0 10px 30px rgba(0,0,0,.07);
```

Jangan menggunakan shadow besar pada seluruh card.

---

# 9. LAYOUT SYSTEM

## 9.1 Desktop Admin

```text
┌─────────────────────────────────────────────────────────────┐
│ Sidebar │ Topbar                                             │
│         ├───────────────────────────────────────────────────┤
│         │ Page Header                                       │
│         │                                                   │
│         │ Main Workspace                                    │
│         │                                                   │
│         │ Data / Charts / Panels                            │
│         │                                                   │
└─────────┴───────────────────────────────────────────────────┘
```

Sidebar:

```text
240px
```

Collapsed:

```text
72px
```

Main content:

```text
max-width: 1600px
```

---

# 10. SIDEBAR

Sidebar bukan sekadar daftar menu.

Harus memiliki hierarchy.

Contoh:

```text
SIMPPAY
Payroll Operations

OVERVIEW
Dashboard

PEOPLE
Pegawai
Jabatan
Struktural
Fungsional

ATTENDANCE
Kehadiran

PAYROLL
Payroll Run
Slip Gaji
Tunjangan
Potongan

ACADEMIC
Tahun Akademik
SKS Dosen
Honor SKS

REPORTS
Laporan Gaji
Laporan Absensi

INTELLIGENCE
Anomaly
AI Assistant
```

Active item:

```text
background: primary-soft
color: primary
font-weight: 600
```

Tambahkan indicator kecil di sisi kiri active item.

Jangan menggunakan icon besar.

---

# 11. TOPBAR

Topbar terdiri dari:

```text
Breadcrumb
Page context
Search
Notification
Profile
```

Contoh:

```text
Payroll
/
Payroll Run
/
September 2026
```

Global search:

```text
Search SIMPPAY...
```

Shortcut:

```text
Ctrl K
```

Search dapat mencari:

- pegawai
- NIK
- jabatan
- payroll period
- report
- menu

---

# 12. COMMAND SEARCH

Implementasikan command/search interface.

Trigger:

```text
Ctrl + K
```

atau:

```text
/
```

Modal:

```text
┌──────────────────────────────────────┐
│ Search SIMPPAY...                    │
├──────────────────────────────────────┤
│                                       │
│ Recent                                │
│                                       │
│ Payroll September 2026                │
│ Pegawai: Ahmad Fauzan                 │
│ Laporan Gaji                          │
│ Kehadiran September                   │
│                                       │
└──────────────────────────────────────┘
```

Gunakan keyboard navigation.

Support:

```text
Arrow Up
Arrow Down
Enter
Escape
```

Tidak perlu membuat command palette seperti aplikasi developer.

Fokus pada navigasi data.

---

# 13. DASHBOARD ADMIN

Dashboard bukan kumpulan 10 card KPI.

Gunakan layout editorial.

## Hero Header

```text
Payroll Operations
September 2026

Status:
Calculated

Last updated:
09:42
```

Action:

```text
Review Payroll
```

---

## KPI Layout

Gunakan 4 metric utama:

```text
Total Payroll
Rp 1.84B

Employees
428

Finalized
401

Needs Review
7
```

KPI card tidak boleh memiliki icon besar.

Angka menjadi fokus utama.

---

# 14. PAYROLL OVERVIEW

Gunakan split layout:

```text
┌──────────────────────────────┬─────────────────────┐
│ Payroll Trend                │ Payroll Status       │
│                              │                     │
│     chart                    │ Finalized    94%    │
│                              │ Calculated    4%    │
│                              │ Draft         2%    │
└──────────────────────────────┴─────────────────────┘
```

Chart menggunakan Chart.js.

Chart tidak menggunakan 3D.

Tidak menggunakan donut chart untuk setiap angka.

---

# 15. PAYROLL RUN PAGE

Ini adalah salah satu halaman terpenting.

Header:

```text
Payroll Run
September 2026

Calculated
428 employees
```

Actions:

```text
Recalculate
Finalize
Export
```

Jika finalized:

```text
FINALIZED
This payroll run is locked.
```

Action edit tidak boleh muncul.

---

# 16. PAYROLL PROGRESS

Ketika batch payroll berjalan:

```text
Calculating September Payroll

████████████████░░░░ 82%

351 / 428 employees

Estimated remaining:
12 seconds
```

Progress harus hidup.

Gunakan Motion untuk:

- progress animation
- number transition
- row completion
- status transition

Jangan menggunakan spinner sebagai satu-satunya feedback.

---

# 17. PAYROLL TABLE

Table harus terasa seperti financial software.

Column:

```text
Employee
Position
Base Salary
Allowances
Deductions
Take Home Pay
Status
```

Angka align right.

Nama align left.

Status menggunakan compact badge.

Row hover:

```text
background: #F8FAF8
```

Click row:

membuka detail drawer.

---

# 18. PAYROLL DETAIL DRAWER

Jangan selalu pindah halaman.

Klik row:

```text
┌──────────────────────────────────────┐
│ Ahmad Fauzan                    ×    │
│ Dosen                               │
├──────────────────────────────────────┤
│ Payroll September 2026              │
│                                      │
│ Gaji Pokok            Rp 8.000.000  │
│ Transport             Rp   500.000  │
│ Uang Makan            Rp   750.000  │
│ Tunjangan             Rp 1.250.000  │
│ Potongan              Rp  -450.000  │
│                                      │
│ Take Home Pay         Rp10.050.000  │
├──────────────────────────────────────┤
│ View Full Slip    Print              │
└──────────────────────────────────────┘
```

Gunakan Floating UI untuk positioning contextual panel jika diperlukan.

---

# 19. EMPLOYEE DIRECTORY

Gunakan layout data-first.

Header:

```text
Pegawai
428 employees

[Import Excel]
[Tambah Pegawai]
```

Filter bar:

```text
Search
Jabatan
Status
Struktural
Fungsional
```

Filter harus bisa disimpan sebagai URL query.

Contoh:

```text
pegawai?jabatan=dosen&status=aktif
```

---

# 20. EMPLOYEE PROFILE

Jangan menggunakan profile page seperti social media.

Gunakan administrative dossier.

```text
Ahmad Fauzan
NIDN XXXXXXXX

Dosen
Aktif
```

Tabs:

```text
Overview
Kepegawaian
Keluarga
Kehadiran
Payroll
SKS
Documents
```

Profile header tetap visible ketika scrolling.

---

# 21. PHOTO UPLOAD

Employee photo upload:

```text
┌──────────────────────────────┐
│                              │
│        Upload photo          │
│                              │
│   JPG / PNG • max 2 MB       │
│                              │
└──────────────────────────────┘
```

Gunakan FilePond untuk feedback upload.

Jangan membuat upload area terlalu dekoratif.

---

# 22. ATTENDANCE PAGE

Kehadiran harus terasa seperti operational grid.

Header:

```text
Kehadiran
September 2026
```

Filter:

```text
Period
Jabatan
Pegawai
```

Summary:

```text
Hadir
92.4%

Sakit
2.1%

Alpha
5.5%
```

Table:

```text
Pegawai | Hadir | Sakit | Alpha | Status
```

Inline edit diperbolehkan sesuai requirement.

---

# 23. INLINE EDITING

Inline edit digunakan untuk:

- absensi
- SKS
- tunjangan
- potongan

Interaction:

```text
hover
↓
field becomes editable
↓
change
↓
save
↓
subtle confirmation
```

Jangan membuat seluruh table menjadi form.

---

# 24. TUNJANGAN & POTONGAN

Gunakan segmented filter:

```text
All
Active
Inactive
By Position
By Employee
```

Toggle active:

```text
● Active
○ Inactive
```

Perubahan harus memberikan immediate visual feedback.

---

# 25. SKS DOSEN

Gunakan spreadsheet-like interaction.

Columns:

```text
Dosen
SKS Maksimal
SKS Terpakai
SKS Beban
Kelebihan
Honor
```

Kelebihan SKS diberi visual emphasis hanya ketika:

```text
SKS Terpakai > SKS Maksimal
```

Jangan memberi warning pada seluruh row.

---

# 26. REPORTING

Report page menggunakan workspace layout.

```text
┌─────────────────────────────────────────────┐
│ Laporan Gaji                                │
│                                             │
│ Periode    Jabatan    Pegawai              │
│                                             │
│ [Generate] [Export Excel] [Print PDF]       │
├─────────────────────────────────────────────┤
│                                             │
│ Result                                      │
│                                             │
│ Table                                       │
│                                             │
└─────────────────────────────────────────────┘
```

Filter berada di atas data.

Bukan modal.

---

# 27. REPORT INSIGHT

Jika AI Report Generator tersedia:

```text
Payroll Insight

Payroll September meningkat 4.8%
dibanding Agustus.

Peningkatan terutama berasal dari
perubahan komponen tunjangan.
```

Insight harus selalu dilabeli:

```text
AI-generated insight
```

AI insight bukan pengganti laporan numerik.

---

# 28. ANOMALY CENTER

Anomaly harus menjadi workspace tersendiri.

Header:

```text
Payroll Anomalies
7 items need review
```

List:

```text
Ahmad Fauzan
Take-home pay +38%
vs 6-month baseline

[Review]
```

Severity:

```text
Review
Elevated
High
```

Jangan gunakan warna merah untuk semua anomaly.

---

# 29. ANOMALY DETAIL

Drawer:

```text
Payroll anomaly

Ahmad Fauzan
September 2026

Deviation
+38%

Historical range
Rp 8.2M - Rp 9.4M

Current
Rp 12.1M
```

Kemudian:

```text
Possible causes

1.
Additional allowance

2.
Attendance adjustment

3.
Honor SKS
```

AI-generated explanation:

```text
AI Review
```

Action:

```text
Mark reviewed
Open payroll
```

---

# 30. AI PAYROLL ASSISTANT

AI tidak boleh menjadi visual utama dashboard.

Gunakan sebagai contextual assistant.

Trigger:

```text
Ask Payroll
```

atau:

```text
Ctrl + J
```

Panel:

```text
┌───────────────────────────────────────────┐
│ Payroll Assistant                    ×    │
├───────────────────────────────────────────┤
│                                           │
│ You                                      │
│ Berapa total gaji dosen September?       │
│                                           │
│ Assistant                                │
│ Total payroll dosen adalah Rp 842 juta.  │
│                                           │
│ Breakdown                                │
│ 124 employees                            │
│ Average: Rp 6.79 juta                    │
│                                           │
│ [Open report]                             │
├───────────────────────────────────────────┤
│ Ask about payroll...                  ↑   │
└───────────────────────────────────────────┘
```

AI response harus selalu dapat membuka sumber data terkait.

---

# 31. AI CHATBOT PEGAWAI

Portal employee memiliki AI assistant yang hanya dapat mengakses data user tersebut.

Suggested prompts:

```text
Tampilkan slip gaji terbaru saya
```

```text
Berapa total potongan bulan ini?
```

```text
Bagaimana riwayat gaji saya 6 bulan terakhir?
```

Jangan menampilkan prompt seperti:

```text
Tanya apa saja
```

karena scope chatbot sangat terbatas.

---

# 32. PORTAL PEGAWAI

Portal bukan miniatur admin panel.

Gunakan desain personal workspace.

Header:

```text
Selamat datang,
Ahmad Fauzan

Dosen
```

Primary information:

```text
Take Home Pay
Rp 10.050.000

September 2026
Finalized
```

---

# 33. PORTAL DASHBOARD

Layout:

```text
┌──────────────────────────────────────────┐
│ Greeting                                 │
├───────────────────┬──────────────────────┤
│ Latest Salary     │ Employment Info      │
│ Rp 10.05M         │ Dosen                │
│ September         │ Aktif                │
├───────────────────┴──────────────────────┤
│ Salary History                           │
│                                          │
│ Chart                                    │
│                                          │
└──────────────────────────────────────────┘
```

Chart menampilkan 6 bulan terakhir sesuai PRD.

---

# 34. SLIP GAJI

Slip gaji online:

```text
September 2026
FINALIZED

Take Home Pay
Rp 10.050.000
```

Breakdown:

```text
Pendapatan
Gaji Pokok
Transport
Uang Makan
Tunjangan
Honor SKS

Potongan
Alpha
BPJS
Lainnya
```

Action:

```text
Download PDF
Print
```

---

# 35. MOBILE PORTAL

Mobile tidak menggunakan sidebar desktop.

Gunakan:

```text
Header
Content
Bottom navigation
```

Bottom navigation:

```text
Home
Slip
History
Profile
```

AI assistant dapat menjadi floating contextual action.

Jangan membuat floating button terlalu besar.

---

# 36. MOBILE TABLE

Admin table pada mobile tidak dipaksa menjadi tabel desktop.

Transform menjadi:

```text
Ahmad Fauzan
Dosen

Take Home Pay
Rp 10.050.000

Finalized

View details →
```

Gunakan stacked data card hanya untuk mobile.

---

# 37. MODAL

Modal digunakan untuk:

- confirmation
- destructive action
- focused form
- small workflow

Modal tidak digunakan untuk:

- halaman kompleks
- report panjang
- employee profile penuh

Jika konten kompleks, gunakan drawer/page.

---

# 38. CONFIRMATION UX

Finalize payroll:

```text
Finalize September 2026?

After finalization:
• payroll becomes locked
• historical snapshot cannot be edited
• recalculation requires a new run or void

[Cancel]
[Finalize Payroll]
```

Tidak boleh:

```text
Are you sure?
```

tanpa konteks.

---

# 39. DESTRUCTIVE ACTION

Void payroll:

warna danger hanya pada action.

Confirmation wajib meminta reason:

```text
Reason for void

[____________________________]

Cancel
Void Payroll
```

---

# 40. TOAST

Toast harus ringkas.

Success:

```text
Payroll finalized
```

Error:

```text
Unable to finalize payroll
```

Detail error disediakan lewat:

```text
View details
```

Jangan membuat toast menjadi paragraph panjang.

---

# 41. TOOLTIP

Tooltip digunakan untuk:

- icon-only button
- unfamiliar action
- truncated data
- explanation singkat

Gunakan Floating UI.

Tooltip harus muncul saat:

- hover
- keyboard focus

Escape harus menutup tooltip.

---

# 42. SELECT & FILTER

Gunakan Tom Select untuk:

- Pegawai
- Jabatan
- Periode
- Tahun Akademik
- kategori

Fitur:

- search
- keyboard navigation
- clear
- selected state

Jangan menggunakan native select untuk daftar pegawai yang panjang.

---

# 43. TABLE INTERACTION

Table wajib mendukung:

- sorting
- filtering
- pagination
- row hover
- row detail
- column alignment
- empty state
- loading state

Jika memungkinkan:

- column visibility
- saved filter
- export

---

# 44. EMPTY STATE

Jangan:

```text
No data.
```

Gunakan contextual explanation.

Contoh:

```text
No finalized payroll

There are no finalized payroll runs
for the selected period.

[Change period]
```

---

# 45. LOADING STATE

Gunakan skeleton yang mengikuti struktur konten.

Jangan menggunakan satu spinner besar di tengah halaman.

Contoh:

```text
████████████████
████████

██████████████████████
████████████
```

Untuk action cepat:

gunakan button loading state.

---

# 46. ERROR STATE

Error page harus actionable.

```text
Payroll data could not be loaded

The payroll service is temporarily
unavailable.

[Try again]
```

Jika AI gagal:

```text
AI Assistant is temporarily unavailable.

Payroll data and standard reports
remain available.

[Close]
```

Sesuai requirement fallback AI pada PRD.

---

# 47. MOTION SYSTEM

Gunakan Motion secara selektif.

Motion dipakai untuk:

### Page enter

```text
opacity: 0 → 1
translateY: 8px → 0
duration: 240ms
```

### Card reveal

Gunakan stagger ringan:

```text
40ms
```

### Drawer

```text
translateX: 24px → 0
opacity: 0 → 1
```

### Modal

```text
scale: .98 → 1
opacity: 0 → 1
```

### Number update

Gunakan numeric transition.

### Progress

Gunakan spring atau tween.

---

# 48. MOTION RULES

Dilarang:

- animation setiap scroll
- infinite floating objects
- excessive bounce
- rotating icons tanpa fungsi
- parallax berlebihan
- animated gradient background
- text scramble
- cursor-following decoration
- animation yang mengganggu table

Motion harus menjelaskan state change.

---

# 49. REDUCED MOTION

Wajib menghormati:

```css
@media (prefers-reduced-motion: reduce)
```

Ketika aktif:

- disable entrance animation
- disable parallax
- disable spring
- disable decorative transitions

Tetap pertahankan state transition yang penting.

---

# 50. CHART SYSTEM

Gunakan Chart.js.

Chart:

### Payroll history

Line chart.

### Payroll distribution

Bar chart.

### Attendance

Stacked bar.

### Salary composition

Horizontal bar.

Hindari:

- 3D pie
- radial chart untuk semuanya
- chart dekoratif
- chart tanpa label

Tooltip chart harus menampilkan:

```text
September 2026

Take Home Pay
Rp 10.050.000
```

---

# 51. DATA VISUALIZATION RULE

Setiap chart harus menjawab satu pertanyaan.

Contoh:

```text
Bagaimana payroll berubah?
```

Line chart.

```text
Jabatan mana memiliki payroll terbesar?
```

Bar chart.

```text
Bagaimana status payroll?
```

Compact status distribution.

Jangan membuat chart hanya untuk memenuhi ruang kosong.

---

# 52. ICON SYSTEM

Gunakan Lucide.

Style:

```text
stroke-based
1.75px - 2px
```

Ukuran:

```text
16px
18px
20px
24px
```

Jangan mencampur:

```text
Lucide
FontAwesome
Heroicons
Bootstrap Icons
```

dalam satu interface.

---

# 53. BUTTON SYSTEM

Primary:

```text
Finalize Payroll
Generate Report
Tambah Pegawai
```

Secondary:

```text
Export
Print
Cancel
```

Tertiary:

```text
View details
Open report
```

Destructive:

```text
Void payroll
Delete
```

Button tidak boleh menggunakan gradient.

---

# 54. STATUS SYSTEM

Payroll:

```text
Draft
Calculated
Finalized
Void
```

Attendance:

```text
Complete
Incomplete
```

AI anomaly:

```text
Review
Elevated
High
Resolved
```

Status harus konsisten di seluruh aplikasi.

---

# 55. ACCESSIBILITY

Wajib:

- keyboard navigation
- visible focus state
- semantic buttons
- ARIA labels
- sufficient contrast
- tooltip keyboard accessible
- modal focus trap
- Escape close
- table header semantics

Tidak boleh menggunakan:

```text
<div onclick="">
```

untuk action utama.

---

# 56. RESPONSIVE BREAKPOINTS

```text
sm: 640px
md: 768px
lg: 1024px
xl: 1280px
2xl: 1536px
```

Behavior:

### Desktop

Sidebar + content.

### Tablet

Collapsible sidebar.

### Mobile

Bottom navigation untuk portal.

Admin mobile menggunakan drawer navigation.

---

# 57. PERFORMANCE

UI harus tetap cepat meskipun data besar.

Wajib:

- lazy render chart
- paginate tables
- debounce search
- avoid unnecessary Inertia round-trips (use partial reloads/`only` prop instead of full page visits where possible)
- lazy load heavy components
- tree-shake icon imports
- avoid loading animation libraries on every page jika tidak diperlukan

Motion hanya di-load pada interface yang membutuhkannya.

---

# 58. LIBRARY STACK

## Required

### Motion

Untuk:

- transition
- spring
- stagger
- scroll reveal
- number animation

Package:

```bash
npm install motion
```

---

### Chart.js

Untuk:

- payroll history
- attendance chart
- payroll distribution

Package:

```bash
npm install chart.js
```

---

### Floating UI

Untuk:

- tooltip
- popover
- contextual menu
- dropdown positioning

Package:

```bash
npm install @floating-ui/dom
```

Jika implementasi membutuhkan interaction abstraction yang lebih kompleks, gunakan package sesuai kebutuhan runtime.

---

### Tom Select

Untuk:

- employee search
- position search
- filters
- academic year selection

Package:

```bash
npm install tom-select
```

---

### FilePond

Untuk:

- employee photo
- SK document upload
- upload progress
- validation feedback

Package:

```bash
npm install filepond
```

---

### Lucide

Untuk icon system.

Gunakan package yang sesuai dengan Blade/JS integration yang dipilih.

Jangan menginstall beberapa icon library sekaligus.

---

# 59. LIBRARY USAGE RULE

Library tidak boleh digunakan hanya karena terlihat menarik.

Setiap library harus memiliki fungsi nyata.

```text
Motion
→ state transition

Chart.js
→ data visualization

Floating UI
→ contextual positioning

Tom Select
→ complex selection

FilePond
→ upload interaction

Lucide
→ icon system
```

Jika native CSS/Vue reactivity (`ref`/`computed`) sudah cukup, jangan menambahkan library.

---

# 60. VUE + INERTIA CUSTOMIZATION (revisi — stack diganti dari Filament ke Vue 3 + Inertia.js)

Vue 3 + Inertia.js adalah foundation Admin/BPSDM/Portal (bukan lagi Filament — lihat DESIGN.md §1 untuk keputusan stack final).

Karena UI sekarang 100% custom (bukan komponen Filament bawaan), design tokens & aturan di dokumen ini **wajib** diterapkan langsung di komponen Vue (`resources/js/Components/ui/*`), bukan lewat theme-override framework admin pihak ketiga. Ini justru memberi kontrol penuh yang lebih ketat kepada dokumen ini — tidak ada alasan "keterbatasan Filament" untuk menyimpang dari spesifikasi manapun di bawah.

Customization/implementasi dilakukan pada:

- design tokens (warna, tipografi, spacing) sebagai CSS variables global + Tailwind config
- komponen primitif reusable (Button, Input, Select, Modal, Drawer, Skeleton, Table)
- navigation (sidebar Admin/BPSDM, bottom nav Portal)
- table appearance & behavior (server-side pagination via Inertia, row hover, klik→drawer)
- form appearance (validasi inline dari Inertia errors)
- dashboard layout (editorial, bukan generic widget grid)
- modal/drawer behavior (motion sesuai §-motion di dokumen ini)

Controller & data-flow logic tetap mengikuti architecture DESIGN.md (Controller mengirim props terstruktur ke Vue Page via `Inertia::render`).

---

# 61. BPSDM PANEL

BPSDM adalah read-only mirror Admin.

UI harus secara visual menunjukkan:

```text
BPSDM
Read only
```

Create/Edit/Delete tidak boleh tampil.

Namun authorization tetap harus dilakukan server-side.

UI hiding bukan security mechanism.

---

# 62. ADMIN DASHBOARD WIDGETS

Default dashboard hanya:

```text
Payroll overview
Payroll status
Recent anomalies
Recent activity
```

Tidak lebih dari kebutuhan.

Jangan membuat 12 KPI cards.

---

# 63. RECENT ACTIVITY

Gunakan timeline sederhana.

```text
09:42
Payroll September finalized

09:31
12 anomalies reviewed

09:10
Attendance imported

08:54
Payroll calculation completed
```

Timeline menjadi informasi operational, bukan dekorasi.

---

# 64. INTERACTION DETAILS

## Hover

Hover digunakan untuk:

- reveal secondary actions
- row highlight
- button feedback
- table affordance

## Focus

Focus harus lebih jelas daripada hover.

## Active

Active state harus memiliki perubahan visual yang nyata.

## Disabled

Disabled harus tetap terbaca.

Jangan membuat disabled opacity terlalu rendah.

---

# 65. PAGE TRANSITION

Navigasi antar halaman harus terasa continuous.

Inertia navigation (`<Link>`/`router.visit`) dipakai untuk semua perpindahan halaman:

- tampilkan progress bar tipis
- pertahankan sidebar
- jangan reload seluruh UI secara visual
- gunakan subtle content transition

Progress bar:

```text
height: 2px
```

Jangan menggunakan loading overlay fullscreen untuk navigasi biasa.

---

# 66. NO AI SLOP RULES

Interface dilarang menggunakan pola berikut hanya demi terlihat modern:

```text
AI
AI
AI
AI
```

Dilarang:

- glowing purple gradients
- neon blue/purple
- glass cards everywhere
- random sparkles
- floating blobs
- fake AI brain graphics
- generic "Powered by AI" hero
- giant centered dashboard title
- excessive rounded cards
- meaningless metric cards
- decorative 3D objects
- emoji icons

AI hanya muncul ketika memang menyediakan functionality.

---

# 67. NO GENERIC DASHBOARD RULE

Jangan membuat:

```text
Welcome back, Admin!

[Revenue]
[Users]
[Growth]
[Activity]
```

tanpa konteks payroll.

Gunakan:

```text
Payroll Operations
September 2026

428 employees
Rp 1.84B payroll
401 finalized
7 require review
```

Dashboard harus terasa spesifik terhadap SIMPPAY.

---

# 68. DESIGN TOKENS

Buat centralized tokens.

Contoh:

```css
:root {
    --simppay-bg: #F6F7F4;
    --simppay-surface: #FFFFFF;
    --simppay-text: #171918;
    --simppay-muted: #626865;
    --simppay-border: #E3E6E3;

    --simppay-primary: #176B5B;
    --simppay-primary-soft: #E7F2EF;

    --simppay-success: #197A55;
    --simppay-warning: #A66A00;
    --simppay-danger: #B43B3B;
    --simppay-info: #3568A8;

    --radius-sm: 10px;
    --radius-md: 12px;
    --radius-lg: 16px;
    --radius-xl: 18px;
}
```

Semua component harus mengambil warna dari token.

Jangan hardcode warna berbeda-beda di setiap component.

---

# 69. COMPONENT INVENTORY

Buat reusable component:

```text
SimppayButton
SimppayBadge
SimppayMetric
SimppayPanel
SimppayTable
SimppayDrawer
SimppayModal
SimppayEmptyState
SimppaySkeleton
SimppayToast
SimppayTooltip
SimppayFilterBar
SimppaySearch
SimppayTimeline
SimppayPayrollBreakdown
SimppayAnomalyCard
SimppayChart
```

Jangan copy-paste style component ke setiap page.

---

# 70. COMPONENT COMPOSITION

Example:

```text
PayrollPage
 ├── PageHeader
 ├── PayrollSummary
 │    ├── Metric
 │    ├── Metric
 │    └── Metric
 ├── PayrollFilters
 ├── PayrollTable
 │    └── PayrollRow
 └── PayrollDetailDrawer
      └── PayrollBreakdown
```

---

# 71. VUE TABLE RULES (revisi — bukan lagi Filament table)

Tabel data (Pegawai, Kehadiran, PayrollDetail, dst) di Vue harus:

- pagination selalu server-side lewat Inertia (`router.get(url, { preserveState: true, preserveScroll: true })`), JANGAN fetch seluruh dataset lalu paginate di client
- filter/search dikirim sebagai query string dan diproses di server (Eloquent), bukan `.filter()` di JS atas data yang sudah di-load penuh
- preserve authorization: props `can.create/update/delete` dari server menentukan tombol apa yang render, TAPI request mutasi tetap divalidasi ulang oleh Policy di server (props Vue tidak pernah jadi satu-satunya penjaga)
- preserve policy behavior: perilaku BPSDM read-only harus identik hasilnya baik dari UI maupun dari akses URL/route langsung

Jangan membangun ulang virtualization/infinite-scroll kompleks dengan JavaScript custom kalau pagination server-side biasa sudah cukup untuk volume data SIMPPAY (ratusan-ribuan baris, bukan jutaan).

---

# 72. VUE/INERTIA PORTAL RULES (revisi — bukan lagi Livewire)

Portal Pegawai/Tendik (Vue + Inertia):

- data sensitif (nominal gaji) tetap dihitung & diformat di server, Vue hanya menampilkan — jangan kirim raw breakdown lalu hitung ulang di client
- gunakan Vue reactivity (`ref`/`computed`) untuk interaksi lokal (privacy toggle, tab switching), tidak perlu Alpine karena sudah full Vue
- gunakan library JS (Chart.js, dst) hanya di tempat yang benar-benar menambah kejelasan (grafik 6 bulan), bukan dekorasi
- hindari mengirim dataset besar ke browser — endpoint portal hanya mengirim data milik pegawai yang login, sudah diagregasi di server
- debounce search (kalau ada) minimal 300ms
- lazy-load section berat (mis. riwayat panjang) via Inertia partial reload / `defer` prop, bukan blocking initial page load

---

# 73. ALPINE + MOTION

Use Alpine for:

```text
open/close
tabs
drawer state
modal state
local filter state
keyboard interaction
```

Use Motion for:

```text
actual animation
spring
stagger
complex transition
```

Jangan membuat Alpine mengandung seluruh application state.

---

# 74. DATA PRIVACY UX

Sensitive payroll information harus tidak mudah terekspos.

Pada portal:

```text
Rp ••••••••
```

optional privacy toggle:

```text
Show amount
```

Untuk mobile, profile dan payroll detail harus tetap private.

---

# 75. PRINT EXPERIENCE

Print PDF tidak boleh terlihat seperti screenshot web.

PDF harus:

- clean
- white background
- institutional
- readable
- proper hierarchy
- table-friendly

Web UI dan PDF memiliki design treatment berbeda.

---

# 76. DARK MODE

Dark mode boleh disiapkan secara architecture, tetapi bukan prioritas MVP.

Jika diimplementasikan:

- jangan sekadar invert colors
- chart colors harus berubah
- border harus tetap terlihat
- payroll amount tetap high contrast

Default MVP:

```text
Light mode
```

---

# 77. QUALITY BAR

Sebuah page dianggap selesai jika:

- hierarchy jelas
- tidak terlihat seperti template
- responsive
- keyboard accessible
- loading state tersedia
- empty state tersedia
- error state tersedia
- permission sesuai
- animation tidak mengganggu
- tidak ada emoji
- tidak ada decorative gradient berlebihan
- tidak ada duplicated component style
- data mudah dipindai
- action mudah ditemukan
- mobile tidak sekadar mengecilkan desktop

---

# 78. ACCEPTANCE CHECKLIST UI/UX

## Global

- [ ] No emoji
- [ ] No excessive gradients
- [ ] No excessive glassmorphism
- [ ] No generic SaaS dashboard
- [ ] No meaningless decorative animation
- [ ] Consistent Lucide icon system
- [ ] Consistent typography
- [ ] Consistent spacing
- [ ] Consistent status colors

## Admin

- [ ] Sidebar hierarchy implemented
- [ ] Command search implemented
- [ ] Payroll overview implemented
- [ ] Payroll table implemented
- [ ] Payroll detail drawer implemented
- [ ] Anomaly center implemented
- [ ] Report workspace implemented
- [ ] Responsive Vue/Inertia UI implemented

## BPSDM

- [ ] Read-only visual treatment
- [ ] No create/edit/delete action
- [ ] Server authorization remains active

## Portal

- [ ] Personal dashboard
- [ ] Six-month salary chart
- [ ] Finalized-only slip selection
- [ ] PDF print
- [ ] Password page
- [ ] Mobile bottom navigation

## Interaction

- [ ] Motion transitions
- [ ] Loading skeleton
- [ ] Progress feedback
- [ ] Drawer transitions
- [ ] Keyboard navigation
- [ ] Tooltip
- [ ] Searchable select
- [ ] Toast
- [ ] Empty state
- [ ] Error state

---

# 79. IMPLEMENTATION PRIORITY

Implement UI in this order:

```text
1. Design tokens
2. Typography
3. Navigation
4. Buttons / badges / inputs
5. Table system
6. Filter system
7. Dashboard
8. Payroll Run
9. Employee Directory
10. Employee Profile
11. Attendance
12. Payroll detail drawer
13. Reports
14. Anomaly Center
15. AI Assistant
16. Employee Portal
17. Mobile optimization
18. Motion polish
```

Do not begin with decorative animation.

First make the information architecture excellent.

---

# 80. FINAL DESIGN COMMAND

The AI coding agent must interpret this document as a product design system, not as a suggestion list.

Do not generate a generic admin template.

Do not blindly add cards.

Do not use emoji.

Do not add decorative AI visuals.

Do not introduce React, Svelte, or any other frontend framework alongside Vue — the stack is Laravel 12 + Vue 3 + Inertia.js, decided explicitly. Stay within it.

Do not silently reach for Filament or Livewire again — that plan was superseded; the operative architecture is DESIGN.md's Vue/Inertia one.

Do not modify the database to accommodate UI preferences.

Do not modify PayrollService logic for visual requirements.

Build a distinctive, operational, data-dense payroll interface with restrained visual design, meaningful interaction, clear hierarchy, responsive behavior and purposeful motion.

Every visual decision must answer one question:

**Does this make SIMPPAY easier, faster, clearer, or safer to operate?**

If the answer is no, remove it.