# PAYROLL-UPGRADE-PROMPT.md — SIMPPAY-Laravel

Prompt siap-pakai untuk mengimplementasikan Rumusan Penggajian UA resmi ke `PayrollService`, mengikuti rekomendasi di `PAYROLL-FORMULA-GAP-ANALYSIS.md`. Ini task **paling berisiko** di seluruh proyek — formula gaji riil, dipakai untuk bayar gaji sungguhan. Dipecah jadi beberapa prompt bertahap, JANGAN digabung jadi satu eksekusi besar.

---

## Prompt A — Tabel Referensi Statis Dulu (risiko rendah, TIDAK menyentuh PayrollService)

```
Baca PRD.md, DESIGN.md, AGENTS.md, dan RUMUSAN-PENGGAJIAN-UA-SOURCE.md serta PAYROLL-FORMULA-GAP-ANALYSIS.md (taruh ketiga file terakhir ini di root repo) sebelum mulai.

KONTEKS: Universitas Adzkia punya Rumusan Penggajian resmi baru (disahkan 1 Agustus 2025) yang jauh lebih detail dari formula sederhana yang sudah dibangun di PayrollService saat ini. RUMUSAN-PENGGAJIAN-UA-SOURCE.md adalah sumber data lengkapnya (tabel gaji pokok, tunjangan struktural, dst). Task ini TAHAP 1 dari upgrade besar — HANYA membuat tabel referensi statis, TIDAK menyentuh PayrollService, TIDAK menyentuh payroll_runs/payroll_details yang sudah ada.

ATURAN KERAS:
1. JANGAN edit app/Services/PayrollService.php di task ini sama sekali — itu prompt terpisah nanti (Prompt B) setelah tabel referensi ini diverifikasi manual oleh Yang Mulia.
2. Branch terpisah (git checkout -b payroll-ua-formula-reference).
3. Setiap tabel referensi = migration + model + Controller CRUD + Vue Page (ikuti pola yang sudah ada di modul lain — lihat DESIGN.md §5), supaya Admin bisa VERIFIKASI VISUAL angkanya cocok dengan dokumen resmi sebelum dipakai kalkulasi apa pun.
4. Isi data lewat seeder yang sumbernya PERSIS menyalin angka dari RUMUSAN-PENGGAJIAN-UA-SOURCE.md — sertakan komentar di seeder yang merujuk ke section mana di SOURCE.md untuk setiap tabel, supaya bisa diaudit.

BUAT TABEL BERIKUT (migration + model + seeder + Controller CRUD + Vue Page, satu per satu, commit terpisah per tabel):
1. `golongan_ruang` (kode: I.a s/d IV.e, urutan) — referensi SOURCE.md §4.
2. `gaji_pokok_scale` (golongan_ruang_id, mkg [0,2,4...30], nominal) — 17 golongan × 16 baris MKG = data dari tabel besar SOURCE.md §4. INI TABEL PALING BESAR (272 baris) — pastikan semua terisi, JANGAN ada yang kelewat, silangkan manual dengan tabel sumber setelah seed.
3. `level_struktur` (Strategis A/B/C, Operasional A/B/C/D) — SOURCE.md §11.
4. `tunjangan_transportasi_scale` (level_struktur_id ATAU golongan_ruang range untuk Operasional D, nominal) — SOURCE.md §11.
5. `klasifikasi_jabatan_struktural` (1-6, rentang poin, tunjangan min, tunjangan max) — SOURCE.md §10b.
6. `jabatan_struktural_poin` (nama jabatan spesifik seperti "Rektor", "Wakil Rektor 1", dst — ~50 baris, level_struktur, total_poin, klasifikasi_id, tunjangan_baru) — SOURCE.md §10c, salin PERSIS termasuk yang totalnya 0 untuk "Dosen tetap dan karyawan tanpa jabatan struktural".
7. `tunjangan_fungsional_dosen_scale` (jabatan_fungsional, angka_kredit, golongan_ruang, nominal) — SOURCE.md §7.
8. `tunjangan_jabatan_karyawan_scale` (golongan_ruang_id, nominal) — SOURCE.md §8, 17 baris.
9. `tunjangan_variabel_scale` (golongan_ruang_id, nominal_maksimum) — SOURCE.md §9, 17 baris. CATAT di kolom deskripsi/komentar bahwa ini nominal MAKSIMUM, pencairan riil = nominal × persentase kinerja (belum ada sistemnya, lihat GAP-ANALYSIS §1).
10. `honor_sks_scale` (program: S1/S2, status_dosen: tetap/tidak_tetap, strata: Profesor/S3/S2, nilai_sks) — SOURCE.md §17, gabungkan 3 tabel jadi 1 dengan kolom pembeda program+status_dosen.
11. `beban_sks_jabatan` (nama_jabatan, sks_perkuliahan, sks_penelitian, sks_adm, sks_jabatan, total wajib selalu 12) — SOURCE.md §18.

Setelah semua tabel dibuat dan diseed, jalankan php artisan test + npm run build (gerbang seperti biasa), lalu SIAPKAN halaman Vue read-only sederhana yang menampilkan seluruh tabel ini berdampingan dengan catatan sumbernya, supaya saya (Yang Mulia) bisa scan cepat dan konfirmasi tidak ada angka yang salah ketik sebelum lanjut ke Prompt B.

Laporkan: jumlah baris per tabel yang berhasil di-seed, dan link/screenshot halaman verifikasi. JANGAN lanjut ke integrasi PayrollService sebelum saya konfirmasi tabel-tabel ini benar.
```

---

## Prompt B — Field Tambahan di `pegawai` (setelah Prompt A dikonfirmasi)

```
Baca ulang RUMUSAN-PENGGAJIAN-UA-SOURCE.md dan PAYROLL-FORMULA-GAP-ANALYSIS.md §3 poin 2. Tabel referensi dari Prompt A sudah dikonfirmasi benar oleh Yang Mulia.

Task ini: tambah kolom baru ke tabel `pegawai` (migration ADDITIVE — tambah kolom, JANGAN hapus/rename kolom yang sudah ada, JANGAN sentuh data pegawai yang sudah ada selain menambah kolom baru yang defaultnya NULL/nullable):
- golongan_ruang_id (FK nullable ke golongan_ruang)
- tmt (date, nullable — tanggal mulai kerja untuk hitung MKG, kalau field serupa sudah ada di skema pakai yang itu, jangan duplikat)
- jabatan_struktural_poin_id (FK nullable ke jabatan_struktural_poin — pegawai tanpa jabatan struktural = NULL)
- strata_pendidikan (enum nullable: profesor/s3/s2/lainnya — khusus dosen)
- program_mengajar (enum nullable: s1/s2 — khusus dosen)

Update Vue Page Pegawai/Show.vue (tab Kepegawaian) untuk menampilkan & mengedit field baru ini — JANGAN ubah tab/field yang sudah ada, cuma tambah.

Field-field ini BELUM dipakai kalkulasi apapun di task ini (PayrollService masih pakai formula lama) — murni persiapan data. Jalankan gerbang test seperti biasa. Commit terpisah dari Prompt A.

Laporkan hasil dan tunggu konfirmasi sebelum Prompt C.
```

---

## Prompt C — Engine Formula Baru (VERSIONED, TIDAK menimpa yang lama)

```
Baca PayrollService.php yang ada sekarang SELURUHNYA dulu sebelum menulis apapun — paham persis bagaimana calculate()/calculateBatch()/finalize()/void() bekerja sekarang, karena kode lama ini TIDAK BOLEH RUSAK.

Baca RUMUSAN-PENGGAJIAN-UA-SOURCE.md §20 (3 contoh data riil terverifikasi) dan PAYROLL-FORMULA-GAP-ANALYSIS.md §4 (strategi versioning).

STRATEGI: buat PayrollService versi baru sebagai KELAS BARU TERPISAH (`app/Services/Payroll/PayrollUaFormulaService.php`), BUKAN mengubah/menimpa PayrollService.php yang sudah ada. Tambah kolom `formula_version` (enum: 'legacy', 'ua-2025', default 'legacy') di `payroll_runs` — supaya bisa pilih engine mana yang dipakai per run, dan data historis lama tetap terhitung dengan engine lama persis seperti sebelumnya.

PayrollUaFormulaService::calculate(Pegawai $pegawai, Carbon $periode) mengembalikan DTO baru (PayrollUaCalculationResult) dengan breakdown SEMUA komponen dari format resmi (RUMUSAN-PENGGAJIAN-UA-SOURCE.md §19):
Gaji Pokok (dari gaji_pokok_scale via golongan_ruang+mkg pegawai), Tunjangan Jabatan (karyawan, dari tunjangan_jabatan_karyawan_scale — HANYA untuk non-dosen), Tunjangan Fungsional (dosen, dari tunjangan_fungsional_dosen_scale — HANYA untuk dosen), Tunjangan Struktural (dari jabatan_struktural_poin pegawai kalau ada, else 0), Tunjangan Variabel (dari tunjangan_variabel_scale × persentase_kinerja — untuk MVP, persentase_kinerja input manual per pegawai per periode karena sistem penilaian kinerja belum ada, DEFAULT 100% kalau belum diisi, TAPI catat asumsi ini jelas di kode+dokumentasi), Tunjangan Istri/Suami (10% gapok, HANYA jika status_kawin=kawin DAN bukan pasangan yang gapoknya lebih rendah — implementasikan aturan "salah satu pasangan" dari SOURCE §5 baru kalau ada data pasangan yang juga pegawai UA, kalau tidak bisa dideteksi otomatis, beri field manual override "berhak_tunjangan_pasangan" yang di-set Admin), Tunjangan Anak (2% gapok per anak dari pegawai_anak, maks 3, filter usia <21 tahun ATAU flag "masih_sekolah" manual), Tunjangan Makan (nominal per hari BELUM ada di data — pakai placeholder 0 dan TODO comment jelas, JANGAN mengarang angka), BPJS TK+Kesehatan (placeholder, tandai belum diimplementasi penuh karena masih ambigu — lihat GAP-ANALYSIS), Tunjangan Transportasi (dari tunjangan_transportasi_scale berdasar level_struktur/golongan pegawai, pro-rata terhadap kehadiran/26 hari — linear: nominal × (hari_hadir/26), clamp maksimal 100%), Penyesuaian (manual input per payroll_detail, default 0), Lembur (formula (gaji_pokok/120) × jam_lembur_input_manual), Honor Kelebihan SKS (dari honor_sks_scale berdasar program+status_dosen+strata pegawai, dikali MAX(0, sks_terpakai - sks_maksimal_dari_beban_sks_jabatan)), Rapel (manual input, default 0).

Total = SUM semua komponen pendapatan di atas − SUM semua komponen potongan (potongan_gaji yang sudah ada, generalisasi tetap dipakai apa adanya, TIDAK diubah skemanya di task ini).

WAJIB clamp semua komponen ke minimal 0 (tidak boleh negatif), simpan breakdown_json lengkap per komponen (bukan cuma total).

TEST WAJIB (acceptance test, tidak boleh diskip): buat PayrollUaFormulaServiceTest dengan 3 kasus dari RUMUSAN-PENGGAJIAN-UA-SOURCE.md §20 (Zulfikar, Muhammad Thoriq, Desi Rosalina) sebagai data seed test — hasil Jumlah(gross)/jml Pot/THP HARUS identik persis dengan angka di dokumen sumber (toleransi 0). Kalau ada komponen yang membuat angka tidak cocok (misal karena field belum lengkap seperti tunjangan makan), CATAT DENGAN JELAS di laporan komponen mana yang menyebabkan selisih dan berapa besar selisihnya — JANGAN memaksakan angka cocok dengan cara mengarang nilai yang tidak ada sumbernya.

JANGAN integrasikan engine baru ini ke UI produksi (PayrollRunController/Vue Page) dulu di task ini — itu Prompt D setelah engine ini lolos acceptance test 3 kasus di atas dan dikonfirmasi Yang Mulia.

Laporkan hasil test 3 kasus secara rinci (angka per komponen, bukan cuma total match/tidak), dan daftar asumsi/placeholder yang perlu dikonfirmasi (rujuk PAYROLL-FORMULA-GAP-ANALYSIS.md §21 SOURCE atau list ulang di sini kalau ada asumsi baru yang kamu buat).
```

---

## Prompt D — Integrasi ke UI (HANYA setelah Prompt C lolos 3 acceptance test & dikonfirmasi)

```
Baca hasil Prompt C yang sudah dikonfirmasi. Task ini: integrasikan PayrollUaFormulaService ke UI produksi TANPA menghapus jalur formula lama.

Di PayrollRunController/Vue Page PayrollRun/Index.vue: tambah pilihan "Formula" (Legacy / UA 2025) saat membuat payroll run baru — default tetap Legacy (JANGAN ubah default, supaya tidak ada perubahan perilaku tak terduga untuk user yang belum sadar ada opsi baru). Payroll run yang sudah ada (lama) TIDAK terpengaruh sama sekali oleh perubahan ini.

Tambahkan indikator visual jelas di tabel PayrollRun kalau suatu run pakai formula UA 2025 (badge/label), supaya Admin/BPSDM tidak bingung kenapa breakdown-nya beda dari run lama.

Rollout pilot yang disarankan: JANGAN langsung jadikan formula UA 2025 default untuk semua pegawai. Tanya saya dulu: mau pilot ke 1 unit/kelompok kecil dulu, atau langsung semua pegawai untuk periode berikutnya? Tunggu jawaban sebelum menyarankan langkah rollout lebih lanjut.

Jalankan gerbang test seperti biasa. Laporkan hasil dan screenshot.
```

---

## Catatan Penting untuk Semua Prompt di Atas

- Kalau di tengah jalan agent (atau Yang Mulia) menemukan bahwa salah satu angka di `RUMUSAN-PENGGAJIAN-UA-SOURCE.md` ternyata salah salin dari sumber asli, PERBAIKI SOURCE.md dulu (dengan catatan koreksi), baru lanjut — jangan biarkan sumber data acuan sendiri salah.
- 8 poin "perlu klarifikasi" di SOURCE.md §21 sebaiknya ditanyakan ke pihak PSDM/Ketua Tim Manajemen Kompensasi UA (Ranti Mustika Putri, M.T., sesuai §0) SEBELUM Prompt C dikerjakan penuh — beberapa poin (terutama Tunjangan Variabel yang butuh sistem penilaian kinerja) bisa mengubah scope signifikan.
- Setelah semua prompt ini selesai dan formula UA 2025 sudah jadi default, PRD.md §6 dan DESIGN.md §3-§4 perlu di-update mencerminkan formula final — jangan biarkan dokumen desain jadi basi/tidak sesuai kode.
