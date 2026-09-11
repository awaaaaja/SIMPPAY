<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { AlertTriangle, CheckCircle2, Info } from '@lucide/vue';

const props = defineProps({
    settings: Object,
    logs: Array,
});

const s = props.settings;

const form = useForm({
    tunjangan_makan_per_hari: s.tunjangan_makan_per_hari ?? '',
    lembur_basis_gaji_dasar: s.lembur_basis_gaji_dasar ?? 'gaji_pokok_saja',
    lembur_jam_kerja_sebulan: s.lembur_jam_kerja_sebulan ?? 120,
    tunjangan_variabel_default_percentage: s.tunjangan_variabel_default_percentage ?? 100,
    bpjs_kesehatan_aktif: s.bpjs_kesehatan_aktif ?? true,
    bpjs_tk_aktif: s.bpjs_tk_aktif ?? false,
    bpjs_tk_kategori_risiko_jkk: s.bpjs_tk_kategori_risiko_jkk ?? '',
    // UA-2025 Potongan fields
    potongan_bpjs_kes_pct: s.potongan_bpjs_kes_pct ?? 1,
    potongan_bpjs_tk_jkk_pct: s.potongan_bpjs_tk_jkk_pct ?? 0,
    potongan_bpjs_tk_jkm_pct: s.potongan_bpjs_tk_jkm_pct ?? 0,
    potongan_bpjs_tk_jht_pct: s.potongan_bpjs_tk_jht_pct ?? 0,
    potongan_bpjs_tk_jp_pct: s.potongan_bpjs_tk_jp_pct ?? 0,
    potongan_sosial_pct: s.potongan_sosial_pct ?? 0,
    potongan_pendidikan_anak_pct: s.potongan_pendidikan_anak_pct ?? 0,
});

function submit() {
    form.put(route('admin.payroll-settings.update'), {
        preserveScroll: true,
    });
}

function isConfirmed(key) {
    return s['is_confirmed_' + key] === true;
}

function confirmedBadge(key) {
    return isConfirmed(key);
}
</script>

<template>
    <Head title="Pengaturan Payroll" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Pengaturan Payroll</h2>
        </template>

        <div class="space-y-6">
            <!-- Info Banner -->
            <Alert>
                <Info class="h-4 w-4" />
                <AlertDescription>
                    <strong>Mode UA-2025:</strong> Tunjangan dihitung otomatis dari golongan + MKG (§2–§11 Rumusan Penggajian).
                    Potongan diisi di bawah ini (section "Potongan UA-2025").
                    Halaman "Tunjangan Gaji" & "Potongan Gaji" (legacy) <strong>tidak dipakai</strong> di mode ini.
                </AlertDescription>
            </Alert>

            <!-- FORM -->
            <form @submit.prevent="submit">
                <!-- §6 Tunjangan Makan -->
                <Card>
                    <CardHeader>
                        <CardTitle>Tunjangan Makan — SOURCE §6</CardTitle>
                        <CardDescription>
                            Diberikan sesuai kehadiran. Nominal per hari kehadiran.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-4">
                            <div class="grid gap-2">
                                <div class="flex items-center gap-2">
                                    <Label for="tunjangan_makan_per_hari">Tunjangan Makan per Hari (Rp)</Label>
                                    <Badge
                                        v-if="!confirmedBadge('tunjangan_makan')"
                                        variant="outline"
                                        class="border-amber-300 bg-amber-50 text-amber-700 text-xs"
                                    >
                                        <AlertTriangle class="mr-1 h-3 w-3" />
                                        Belum dikonfirmasi PSDM — pakai nilai sementara
                                    </Badge>
                                    <Badge
                                        v-else
                                        variant="outline"
                                        class="border-emerald-300 bg-emerald-50 text-emerald-700 text-xs"
                                    >
                                        <CheckCircle2 class="mr-1 h-3 w-3" />
                                        Sudah dikonfirmasi
                                    </Badge>
                                </div>
                                <Input
                                    id="tunjangan_makan_per_hari"
                                    v-model="form.tunjangan_makan_per_hari"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    placeholder="Kosongkan jika belum ditentukan"
                                    class="max-w-sm"
                                />
                                <p class="text-xs text-muted-foreground">
                                    Kosongkan (NULL) = belum diisi Admin. Isi 0 = memang tidak ada tunjangan makan.
                                </p>
                                <p v-if="form.errors.tunjangan_makan_per_hari" class="text-sm text-destructive">
                                    {{ form.errors.tunjangan_makan_per_hari }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- §16 Lembur -->
                <Card class="mt-6">
                    <CardHeader>
                        <CardTitle>Lembur — SOURCE §16</CardTitle>
                        <CardDescription>
                            Lembur = (Gaji Dasar / Jam Kerja Sebulan) × Jumlah Jam Lembur.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="grid gap-2">
                                <div class="flex items-center gap-2">
                                    <Label for="lembur_basis_gaji_dasar">Basis Gaji Dasar</Label>
                                    <Badge
                                        v-if="!confirmedBadge('lembur_basis')"
                                        variant="outline"
                                        class="border-amber-300 bg-amber-50 text-amber-700 text-xs"
                                    >
                                        <AlertTriangle class="mr-1 h-3 w-3" />
                                        Belum dikonfirmasi PSDM
                                    </Badge>
                                    <Badge
                                        v-else
                                        variant="outline"
                                        class="border-emerald-300 bg-emerald-50 text-emerald-700 text-xs"
                                    >
                                        <CheckCircle2 class="mr-1 h-3 w-3" />
                                        Sudah dikonfirmasi
                                    </Badge>
                                </div>
                                <Select v-model="form.lembur_basis_gaji_dasar">
                                    <SelectTrigger class="max-w-sm">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="gaji_pokok_saja">Gaji Pokok Saja</SelectItem>
                                        <SelectItem value="gaji_pokok_plus_tunjangan_tetap">Gaji Pokok + Tunjangan Tetap</SelectItem>
                                    </SelectContent>
                                </Select>
                                <p class="text-xs text-muted-foreground">
                                    "Gaji Dasar" di formula lembur — apakah murni Gaji Pokok atau termasuk tunjangan tetap.
                                </p>
                            </div>
                            <div class="grid gap-2">
                                <div class="flex items-center gap-2">
                                    <Label for="lembur_jam_kerja_sebulan">Jam Kerja per Bulan</Label>
                                    <Badge
                                        v-if="!confirmedBadge('lembur_jam')"
                                        variant="outline"
                                        class="border-amber-300 bg-amber-50 text-amber-700 text-xs"
                                    >
                                        <AlertTriangle class="mr-1 h-3 w-3" />
                                        Belum dikonfirmasi PSDM
                                    </Badge>
                                    <Badge
                                        v-else
                                        variant="outline"
                                        class="border-emerald-300 bg-emerald-50 text-emerald-700 text-xs"
                                    >
                                        <CheckCircle2 class="mr-1 h-3 w-3" />
                                        Sudah dikonfirmasi
                                    </Badge>
                                </div>
                                <Input
                                    id="lembur_jam_kerja_sebulan"
                                    v-model="form.lembur_jam_kerja_sebulan"
                                    type="number"
                                    min="1"
                                    class="max-w-sm"
                                />
                                <p class="text-xs text-muted-foreground">
                                    Default 120 jam/bulan sesuai dokumen resmi UA.
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- §9 Tunjangan Variabel -->
                <Card class="mt-6">
                    <CardHeader>
                        <CardTitle>Tunjangan Variabel — SOURCE §9</CardTitle>
                        <CardDescription>
                            Nominal maksimum dikalikan persentase ini sampai sistem Penilaian Kinerja tersedia.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-2 max-w-sm">
                            <div class="flex items-center gap-2">
                                <Label for="tunjangan_variabel_default_percentage">Persentase Default (%)</Label>
                                <Badge
                                    v-if="!confirmedBadge('tunjangan_variabel')"
                                    variant="outline"
                                    class="border-amber-300 bg-amber-50 text-amber-700 text-xs"
                                >
                                    <AlertTriangle class="mr-1 h-3 w-3" />
                                    Belum dikonfirmasi PSDM — pakai nilai sementara
                                </Badge>
                                <Badge
                                    v-else
                                    variant="outline"
                                    class="border-emerald-300 bg-emerald-50 text-emerald-700 text-xs"
                                >
                                    <CheckCircle2 class="mr-1 h-3 w-3" />
                                    Sudah dikonfirmasi
                                </Badge>
                            </div>
                            <Input
                                id="tunjangan_variabel_default_percentage"
                                v-model="form.tunjangan_variabel_default_percentage"
                                type="number"
                                step="0.01"
                                min="0"
                                max="100"
                                class="max-w-sm"
                            />
                            <p class="text-xs text-muted-foreground">
                                Ganti default 100% kalau ada kebijakan interim dari PSDM. Misal: 80% = bayar 80% dari nominal maksimum tabel.
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <!-- §13 BPJS Kesehatan -->
                <Card class="mt-6">
                    <CardHeader>
                        <CardTitle>BPJS Kesehatan — SOURCE §13</CardTitle>
                        <CardDescription>
                            Sudah berlaku untuk Tendik dan Dosen dengan TMT &gt; 1 tahun. Iuran 5% (4% institusi + 1% potongan karyawan).
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-2 max-w-sm">
                            <div class="flex items-center gap-2">
                                <Label>Aktif</Label>
                                <Badge
                                    v-if="!confirmedBadge('bpjs_kes')"
                                    variant="outline"
                                    class="border-amber-300 bg-amber-50 text-amber-700 text-xs"
                                >
                                    <AlertTriangle class="mr-1 h-3 w-3" />
                                    Belum dikonfirmasi PSDM
                                </Badge>
                                <Badge
                                    v-else
                                    variant="outline"
                                    class="border-emerald-300 bg-emerald-50 text-emerald-700 text-xs"
                                >
                                    <CheckCircle2 class="mr-1 h-3 w-3" />
                                    Sudah dikonfirmasi
                                </Badge>
                            </div>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input
                                    v-model="form.bpjs_kesehatan_aktif"
                                    type="checkbox"
                                    class="rounded border-gray-300"
                                />
                                <span class="text-sm">Aktifkan potongan BPJS Kesehatan</span>
                            </label>
                        </div>
                    </CardContent>
                </Card>

                <!-- §14 BPJS Ketenagakerjaan -->
                <Card class="mt-6">
                    <CardHeader>
                        <CardTitle>BPJS Ketenagakerjaan — SOURCE §14</CardTitle>
                        <CardDescription>
                            Sebelumnya hanya untuk pegawai tetap Yayasan. Status: "belum realisasi" menurut PDF. Nyalakan jika PSDM konfirmasi sudah jalan.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="grid gap-2">
                                <div class="flex items-center gap-2">
                                    <Label>Aktif</Label>
                                    <Badge
                                        v-if="!confirmedBadge('bpjs_tk')"
                                        variant="outline"
                                        class="border-amber-300 bg-amber-50 text-amber-700 text-xs"
                                    >
                                        <AlertTriangle class="mr-1 h-3 w-3" />
                                        Belum dikonfirmasi PSDM
                                    </Badge>
                                    <Badge
                                        v-else
                                        variant="outline"
                                        class="border-emerald-300 bg-emerald-50 text-emerald-700 text-xs"
                                    >
                                        <CheckCircle2 class="mr-1 h-3 w-3" />
                                        Sudah dikonfirmasi
                                    </Badge>
                                </div>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input
                                        v-model="form.bpjs_tk_aktif"
                                        type="checkbox"
                                        class="rounded border-gray-300"
                                    />
                                    <span class="text-sm">Aktifkan BPJS Ketenagakerjaan</span>
                                </label>
                            </div>
                            <div class="grid gap-2">
                                <div class="flex items-center gap-2">
                                    <Label>Kategori Risiko JKK</Label>
                                    <Badge
                                        v-if="!confirmedBadge('bpjs_tk_risiko')"
                                        variant="outline"
                                        class="border-amber-300 bg-amber-50 text-amber-700 text-xs"
                                    >
                                        <AlertTriangle class="mr-1 h-3 w-3" />
                                        Belum dikonfirmasi PSDM
                                    </Badge>
                                    <Badge
                                        v-else
                                        variant="outline"
                                        class="border-emerald-300 bg-emerald-50 text-emerald-700 text-xs"
                                    >
                                        <CheckCircle2 class="mr-1 h-3 w-3" />
                                        Sudah dikonfirmasi
                                    </Badge>
                                </div>
                                <Select v-model="form.bpjs_tk_kategori_risiko_jkk">
                                    <SelectTrigger class="max-w-sm">
                                        <SelectValue placeholder="-- Pilih Kategori --" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="sangat_rendah">Sangat Rendah (0,24%)</SelectItem>
                                        <SelectItem value="rendah">Rendah (0,54%)</SelectItem>
                                        <SelectItem value="sedang">Sedang (0,89%)</SelectItem>
                                        <SelectItem value="tinggi">Tinggi (1,27%)</SelectItem>
                                        <SelectItem value="sangat_tinggi">Sangat Tinggi (1,74%)</SelectItem>
                                    </SelectContent>
                                </Select>
                                <p class="text-xs text-muted-foreground">
                                    Tarif iuran JKK per kategori risiko. Hanya relevan jika BPJS TK aktif.
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- §6 UA-2025 Potongan -->
                <Card class="mt-6">
                    <CardHeader>
                        <CardTitle>Potongan UA-2025 — SOURCE §6</CardTitle>
                        <CardDescription>
                            Persentase potongan dari formula Rumusan Penggajian UA. Nilai 0% = tidak ada potongan untuk komponen tersebut.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                            <div class="grid gap-2">
                                <Label for="potongan_bpjs_kes_pct">BPJS Kesehatan (% dari Gaji Pokok)</Label>
                                <Input
                                    id="potongan_bpjs_kes_pct"
                                    v-model="form.potongan_bpjs_kes_pct"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    max="100"
                                    class="max-w-sm"
                                />
                                <p class="text-xs text-muted-foreground">PRD §13: 1% potongan karyawan.</p>
                            </div>
                            <div class="grid gap-2">
                                <Label for="potongan_bpjs_tk_jkk_pct">BPJS TK — JKK (% dari Total Income)</Label>
                                <Input
                                    id="potongan_bpjs_tk_jkk_pct"
                                    v-model="form.potongan_bpjs_tk_jkk_pct"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    max="100"
                                    class="max-w-sm"
                                />
                            </div>
                            <div class="grid gap-2">
                                <Label for="potongan_bpjs_tk_jkm_pct">BPJS TK — JKM (% dari Total Income)</Label>
                                <Input
                                    id="potongan_bpjs_tk_jkm_pct"
                                    v-model="form.potongan_bpjs_tk_jkm_pct"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    max="100"
                                    class="max-w-sm"
                                />
                            </div>
                            <div class="grid gap-2">
                                <Label for="potongan_bpjs_tk_jht_pct">BPJS TK — JHT (% dari Total Income)</Label>
                                <Input
                                    id="potongan_bpjs_tk_jht_pct"
                                    v-model="form.potongan_bpjs_tk_jht_pct"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    max="100"
                                    class="max-w-sm"
                                />
                            </div>
                            <div class="grid gap-2">
                                <Label for="potongan_bpjs_tk_jp_pct">BPJS TK — JP (% dari Total Income)</Label>
                                <Input
                                    id="potongan_bpjs_tk_jp_pct"
                                    v-model="form.potongan_bpjs_tk_jp_pct"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    max="100"
                                    class="max-w-sm"
                                />
                            </div>
                            <div class="grid gap-2">
                                <Label for="potongan_sosial_pct">Iuran Sosial (% dari Gaji Pokok)</Label>
                                <Input
                                    id="potongan_sosial_pct"
                                    v-model="form.potongan_sosial_pct"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    max="100"
                                    class="max-w-sm"
                                />
                            </div>
                            <div class="grid gap-2">
                                <Label for="potongan_pendidikan_anak_pct">Iuran Pendidikan Anak (% dari Gaji Pokok)</Label>
                                <Input
                                    id="potongan_pendidikan_anak_pct"
                                    v-model="form.potongan_pendidikan_anak_pct"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    max="100"
                                    class="max-w-sm"
                                />
                            </div>
                        </div>
                        <p class="mt-4 text-xs text-muted-foreground">
                            ✏️ Isi semua persentase, lalu centang "Sudah Dikonfirmasi" untuk mengunci. Perubahan setelah konfirmasi tercatat di audit log.
                        </p>
                    </CardContent>
                </Card>

                <!-- Submit -->
                <div class="flex justify-end mt-6">
                    <Button type="submit" :disabled="form.processing">
                        Simpan Pengaturan
                    </Button>
                </div>
            </form>

            <!-- AUDIT LOG -->
            <Card v-if="logs.length > 0">
                <CardHeader>
                    <CardTitle>Riwayat Perubahan</CardTitle>
                    <CardDescription>50 perubahan terakhir</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b text-left text-muted-foreground">
                                    <th class="pb-2 font-medium">Waktu</th>
                                    <th class="pb-2 font-medium">Field</th>
                                    <th class="pb-2 font-medium">Nilai Lama</th>
                                    <th class="pb-2 font-medium">Nilai Baru</th>
                                    <th class="pb-2 font-medium">Oleh</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="log in logs" :key="log.id" class="border-b last:border-0">
                                    <td class="py-2 whitespace-nowrap">{{ new Date(log.created_at).toLocaleString('id-ID') }}</td>
                                    <td class="py-2">{{ log.field_name }}</td>
                                    <td class="py-2 font-mono text-xs">{{ log.nilai_lama ?? '(kosong)' }}</td>
                                    <td class="py-2 font-mono text-xs">{{ log.nilai_baru ?? '(kosong)' }}</td>
                                    <td class="py-2">{{ log.updated_by?.name ?? '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
