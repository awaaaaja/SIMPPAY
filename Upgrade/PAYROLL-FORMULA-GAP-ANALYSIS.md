# PAYROLL-FORMULA-GAP-ANALYSIS.md — SIMPPAY-Laravel

Perbandingan formula gaji yang **sudah dibangun/direncanakan** di PRD.md §6 & DESIGN.md §3-§4 (sistem lama SIMPPAY CI3 yang sedang di-migrasi) vs **Rumusan Penggajian Universitas Adzkia** resmi (lihat `RUMUSAN-PENGGAJIAN-UA-SOURCE.md`). Dokumen ini adalah dasar untuk keputusan Yang Mulia — apa yang harus diubah, seberapa besar dampaknya, dan urutan pengerjaan yang aman.

**Kesimpulan singkat di depan:** formula gaji SIMPPAY yang sudah dibangun (warisan sistem CI3 lama) jauh lebih sederhana daripada rumusan resmi UA yang sekarang jadi acuan. ~70% komponen di rumusan resmi **belum ada sama sekali** di skema yang sudah dibangun. Ini **BUKAN** bug atau kesalahan implementasi — sistem lama memang belum pernah mengimplementasikan skema kompensasi baru ini (dokumen resminya baru disahkan 1 Agustus 2025). Perlakukan ini sebagai **fitur besar baru**, bukan "perbaikan formula", supaya ekspektasi risiko & effort-nya tepat.

---

## 1. Perbandingan Komponen Pendapatan

| Komponen | Status di sistem yang sudah dibangun | Status di Rumusan Resmi UA | Gap |
|---|---|---|---|
| Gaji Pokok | Flat per `jabatan_id` (1 angka per jabatan) | Tabel 2D: **Golongan/Ruang (I.a–IV.e) × MKG (0-30 thn, interval 2 tahun)** — lihat SOURCE §4 | **BESAR** — butuh tabel referensi baru sepenuhnya, `pegawai` butuh kolom `golongan_ruang` + `mkg` atau `tmt` (tanggal mulai kerja untuk hitung MKG otomatis) |
| Tunjangan Transport | Flat per `jabatan_id` | Berdasarkan **Level Struktur** (Strategis A/B,C / Operasional A/B,C/D) × untuk Operasional D dipecah lagi per rentang golongan, **dipengaruhi kehadiran** (wajib 26 hari/bulan) — lihat SOURCE §11 | **BESAR** — butuh tabel referensi baru + logic pro-rata kehadiran |
| Uang Makan | Flat per `jabatan_id` | Berdasarkan kehadiran, nominal per hari **belum diketahui** (lihat SOURCE §21 poin 1) | **SEDANG** — perlu klarifikasi nominal dulu sebelum implementasi, tapi mekanismenya (kehadiran-based) sudah searah dengan skema lama (`tunjangan_gaji` generik bisa menampung asal nominalnya benar) |
| Tunjangan Struktural | **Tidak ada di sistem lama sama sekali** (skema `tunjangan_gaji` generik ada tapi belum ada data isi untuk ini) | Sistem poin faktor (6 faktor → total 1000 poin) → rentang poin → 6 klasifikasi jabatan → tunjangan min-max per klasifikasi, **plus daftar eksplisit ~50 posisi struktural dengan poin & nominal pasti** — lihat SOURCE §10 | **BESAR** — perlu tabel klasifikasi jabatan struktural + assignment poin per posisi. Untungnya dokumen sumber sudah kasih nominal pasti per posisi (§10c), jadi TIDAK perlu hitung ulang poin faktor real-time — cukup tabel referensi statis |
| Tunjangan Fungsional Dosen | **Tidak ada** | Tabel per Jabatan Fungsional (Asisten Ahli/Lektor/Lektor Kepala/Guru Besar) × sub-golongan — lihat SOURCE §7 | **SEDANG** — tabel referensi statis, cukup jelas |
| Tunjangan Jabatan Karyawan/Tendik | **Tidak ada** | Tabel per Golongan (I.a–IV.e), 17 baris — lihat SOURCE §8 | **SEDANG** — tabel referensi statis |
| Tunjangan Keluarga (istri/suami + anak) | **Tidak ada** — skema `pegawai` lama malah **tidak mendukung status kawin/pasangan sebagai data terstruktur untuk perhitungan** (field `status_kawin` cuma enum, tidak dipakai kalkulasi) | 10% gapok (istri/suami, aturan salah satu jika 2 pegawai menikah), 2% gapok per anak maks 3 anak, syarat usia <21 atau bukti masih sekolah — lihat SOURCE §5 | **BESAR** — butuh field baru + logic (bandingkan gapok 2 pasangan yang sama-sama pegawai UA, validasi usia anak dari `pegawai_anak` yang sudah ada skemanya) |
| Tunjangan Variabel | **Tidak ada** | Tabel per Golongan, **dikalikan persentase penilaian kinerja** — sistem penilaian kinerja itu sendiri belum ada di scope manapun | **BESAR + BLOCKED** — perlu modul Penilaian Kinerja dulu (di luar scope PRD saat ini), atau untuk MVP: field manual `persentase_kinerja` per periode yang diinput manual Admin (bukan otomatis dari sistem penilaian) |
| BPJS Kesehatan | **Tidak ada** | Berlaku otomatis untuk pegawai TMT > 1 tahun, nominal/skema belum jelas (SOURCE §21 poin 3) | **KECIL-SEDANG** (tergantung klarifikasi) |
| BPJS Ketenagakerjaan | **Tidak ada** | **Belum direalisasikan secara institusional**, tapi field-nya sudah muncul di data riil (dan double — komponen pendapatan & potongan, lihat SOURCE §14) | **PERLU KLARIFIKASI DULU** sebelum implementasi — jangan asumsi |
| Lembur | **Tidak ada** | Formula eksplisit: `(Gaji Dasar / 120 jam) × jumlah jam lembur` — lihat SOURCE §16 | **SEDANG** — formula jelas, tinggal input jam lembur manual per periode |
| Honor Kelebihan SKS | **Ada**, tapi model generik 1 kategori flat (`kategori_honor_sks`/`honor_sks`) | **3 tabel independen**: Dosen Tetap Prog. S1, Dosen Tidak Tetap, Dosen Prog. S2 — masing-masing bertingkat per strata (Profesor/S3/S2) — lihat SOURCE §17 | **SEDANG-BESAR** — skema lama terlalu sederhana, perlu field tambahan: strata dosen (`gelar`) + program yang diajar (S1/S2) + status kepegawaian dosen (tetap/tidak tetap) untuk menentukan tabel mana yang dipakai |
| Beban SKS per Jabatan | **Tidak ada** (skema lama `dosen_sks` cuma simpan `sks_maksimal` bebas per dosen tanpa aturan) | Tabel wajib 12 SKS per dosen, **komposisinya ditentukan oleh jabatan** (Rektor dst) — lihat SOURCE §18 | **SEDANG** — `sks_maksimal` seharusnya di-derive dari jabatan (kalau bukan dosen biasa), bukan input manual bebas |
| Penyesuaian | **Tidak ada** | Ada di format resmi, mekanismenya tidak jelas (manual override) — SOURCE §21 poin 7 | **KECIL** — cukup 1 field manual per payroll_detail, admin isi manual kalau perlu |
| Rapel | **Tidak ada** | Ada di format resmi, mekanismenya tidak jelas — SOURCE §21 poin 7 | **KECIL** — sama seperti Penyesuaian |

## 2. Perbandingan Komponen Potongan

| Komponen | Status sistem lama | Status Rumusan Resmi | Gap |
|---|---|---|---|
| Alpha | Ada (`is_alpha_penalty` flag) | **Tidak muncul eksplisit** di format resmi §19 — kemungkinan sudah "terserap" ke logic Tunjangan Transportasi/Makan yang pro-rata kehadiran, bukan potongan terpisah | **PERLU KLARIFIKASI** — apakah potongan Alpha lama masih relevan atau justru sudah digantikan mekanisme pro-rata kehadiran di tunjangan |
| Makan (potongan) | Belum ada pembeda potongan makan vs tunjangan makan | Ada sebagai baris potongan terpisah dari tunjangan makan (lihat data XLSX kolom `mkn` di bagian potongan) | **KECIL** — kemungkinan ini potongan konsumsi harian yang dipotong dari gaji (beda dari tunjangan makan yang ditambahkan) — 2 arah berlawanan, jangan digabung |
| BPJS / BPJS-TK (potongan) | `potongan_gaji` generik sudah menampung "BPJS" | Ada, tapi kompleksitas: BPJS TK muncul sebagai 2 kolom berbeda (lihat §1 baris BPJS Ketenagakerjaan di atas) | **SEDANG** — perlu klarifikasi dulu |
| Pendidikan Anak | Sudah ada di `potongan_gaji` sistem lama sebagai contoh nama potongan | Ada, konsisten | **KECIL** — skema generik lama sudah cukup |
| Sosial Bersama | Sudah ada contoh ("Sosial") di skema lama | Ada, dan dari data XLSX kadang dibayarkan sebagai entri terpisah (bukan potongan reguler bulanan) | **KECIL-SEDANG** |
| UJKS, KKB, BTN/BNS | UJKS & Koperasi(KKB?) sudah disebut contoh di skema lama | Ada, BTN/BNS (kemungkinan cicilan pinjaman bank/koperasi) **belum ada** di skema lama sama sekali, dan dari data XLSX nilainya bisa BESAR (1,1 juta untuk 1 pegawai) — kemungkinan cicilan pinjaman personal | **KECIL secara skema** (tabel generik `potongan_gaji` bisa menampung), tapi **nominalnya per-pegawai-spesifik** (bukan flat per semua pegawai seperti asumsi `potongan_gaji` sekarang yang lebih ke arah "aktif untuk semua/jabatan") — mirip kasus `tunjangan_gaji.target_tipe = pegawai` yang sudah ada, tapi untuk **potongan** belum ada `target_tipe` serupa |
| Lain-lain | Tidak ada | Ada, generik | **KECIL** |
| Arisan | Tidak ada sama sekali di skema lama | Muncul di data riil XLSX sebagai entri terpisah, bukan bagian dari 9 kategori resmi di §19 | **KECIL** — kemungkinan bukan bagian formula gaji resmi, cuma kebiasaan transfer bareng gaji. Tandai sebagai "administrative side-payment", bukan bagian `PayrollService` inti |

## 3. Dampak ke Skema Database (`DESIGN.md` §3)

Perubahan yang **kemungkinan besar diperlukan** (final keputusan tetap di tangan Yang Mulia, ini rekomendasi urutan risiko rendah→tinggi):

1. **Tabel referensi baru (statis, additive, risiko RENDAH — tidak mengubah tabel yang sudah ada):**
   - `golongan_ruang` (kode: I.a–IV.e) + `gaji_pokok_scale` (golongan_ruang × mkg → nominal) — untuk §4
   - `level_struktur` (Strategis A/B/C, Operasional A/B/C/D) — untuk §11 transport
   - `klasifikasi_jabatan_struktural` (1-6) + tunjangan min-max — untuk §10b
   - `jabatan_struktural_poin` (~50 baris posisi eksplisit dari §10c, dengan poin & nominal final — TIDAK perlu hitung real-time dari 6 faktor, cukup tabel statis hasil akhir)
   - `tunjangan_fungsional_dosen_scale`, `tunjangan_jabatan_karyawan_scale`, `tunjangan_variabel_scale` — tabel referensi per golongan/jabatan fungsional
   - `honor_sks_s1_tetap`, `honor_sks_s1_tidak_tetap`, `honor_sks_s2` (atau 1 tabel dengan kolom `program` + `status_dosen`) — pengganti `kategori_honor_sks`/`honor_sks` generik lama
   - `beban_sks_per_jabatan` — pengganti asumsi `sks_maksimal` bebas per dosen

2. **Kolom baru di tabel `pegawai` (risiko SEDANG — additive column, tidak menghapus yang lama):**
   - `golongan_ruang_id` (FK ke tabel baru di atas)
   - `tmt` (tanggal mulai kerja, kalau belum ada — untuk hitung MKG otomatis)
   - `level_struktur_id` / `jabatan_struktural_poin_id` (nullable, hanya untuk yang punya jabatan struktural)
   - `strata_pendidikan` (untuk dosen: Profesor/S3/S2 — dipakai formula Honor SKS)
   - `program_mengajar` (S1/S2, untuk dosen — dipakai pilih tabel Honor SKS mana)

3. **Perluasan `PayrollCalculationResult` DTO (risiko TINGGI karena ini jantung sistem — lihat AGENTS.md §4):**
   Dari ~5 komponen (gapok, transport, makan, potongan alpha, tunjangan/potongan generik) menjadi berpotensi ~15+ komponen sesuai format resmi §19. Setiap komponen baru **wajib** breakdown terpisah di `payroll_details.breakdown_json`, bukan digabung jadi satu angka.

4. **`potongan_gaji` perlu `target_tipe` (mirip `tunjangan_gaji` yang sudah punya semua/jabatan/pegawai)** — supaya potongan personal (BTN/BNS, cicilan) bisa per-pegawai, bukan cuma "aktif untuk semua".

## 4. Rekomendasi Pendekatan — JANGAN Big-Bang

Mengingat ukuran gap ini dan riwayat bug sebelumnya, rekomendasi tegas:

1. **JANGAN langsung modifikasi `PayrollService` yang sudah ada dan sudah ditest.** Formula lama (§6 PRD saat ini) kemungkinan masih dipakai untuk data historis/testing yang sudah divalidasi — jangan sampai rusak.
2. Bangun ini sebagai **engine formula versi baru** (mis. namakan internal `PayrollFormulaVersion` atau tag `payroll_runs.formula_version = 'ua-2025'` vs versi lama `'legacy'`) — additive, bukan menimpa.
3. **Klarifikasi 8 poin di SOURCE §21 ke Yang Mulia/pihak PSDM UA dulu** sebelum menulis satu baris kode formula baru — beberapa poin (Tunjangan Variabel butuh sistem penilaian kinerja, BPJS TK ambigu) bisa mengubah scope signifikan kalau salah asumsi.
4. Implementasi tabel referensi statis (golongan_ruang, klasifikasi struktural, dst) **duluan dan terpisah** dari logic kalkulasi — supaya bisa diverifikasi Admin (lewat halaman CRUD biasa) bahwa angka-angkanya persis sama dengan dokumen resmi, sebelum dipakai kalkulasi apa pun.
5. Pakai **3 contoh data riil di SOURCE §20 sebagai acceptance test wajib** — `PayrollFormulaUaTest` harus menghasilkan angka identik persis (Jumlah, jml Pot, THP) untuk ketiga pegawai contoh tersebut sebelum formula baru dianggap benar.
6. Rollout bertahap: aktifkan formula baru untuk **1 periode/1 kelompok kecil pegawai dulu** (mis. cuma Tendik, atau cuma 1 unit) sebagai pilot, bandingkan manual dengan slip gaji yang biasa dibuat manual/Excel, baru setelah cocok terus-menerus beberapa periode baru dilepas ke semua pegawai.

Lihat `PAYROLL-UPGRADE-PROMPT.md` untuk prompt eksekusi bertahap yang mengikuti rekomendasi ini.
