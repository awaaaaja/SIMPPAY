<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { useRoutePrefix } from '@/composables/useRoutePrefix.js';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { AlertTriangle, CheckCircle2, Save, Shield } from '@lucide/vue';

const prefix = useRoutePrefix();
const page = usePage();
const userRoles = page.props.auth?.roles || [];
const isBpsdm = userRoles.includes('bpsdm');

const props = defineProps({
    settings: Object,
    scalesJabatan: Array,
    scalesVariabel: Array,
    logs: Array,
});

const formatRupiah = (val) => {
    if (val === null || val === undefined || val === '') return '-';
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
};

// ─── Payroll Settings Form ───────────────────────────────
const settingsForm = useForm({
    tunjangan_makan_per_hari: props.settings.tunjangan_makan_per_hari ?? '',
    lembur_basis_gaji_dasar: props.settings.lembur_basis_gaji_dasar ?? 'gaji_pokok_saja',
    lembur_jam_kerja_sebulan: props.settings.lembur_jam_kerja_sebulan ?? 120,
    tunjangan_variabel_default_percentage: props.settings.tunjangan_variabel_default_percentage ?? 100,
    bpjs_kesehatan_aktif: props.settings.bpjs_kesehatan_aktif ?? true,
    bpjs_tk_aktif: props.settings.bpjs_tk_aktif ?? false,
    bpjs_tk_kategori_risiko_jkk: props.settings.bpjs_tk_kategori_risiko_jkk ?? '',
});

function saveSettings() {
    settingsForm.put(route(`${prefix.value}.kebijakan-kompensasi.update-settings`), { preserveScroll: true });
}

function isConfirmed(key) {
    return props.settings['is_confirmed_' + key] === true;
}

// ─── Tunjangan Jabatan Karyawan Scale (inline edit) ──────
const tjKaryawan = ref(props.scalesJabatan.map(s => ({
    id: s.id,
    golongan: s.golongan_ruang?.kode || '-',
    golongan_nama: s.golongan_ruang?.nama || '-',
    nominal: Number(s.nominal),
    original: Number(s.nominal),
})));

const tjKaryawanForm = useForm({ scales: [] });

function saveTjKaryawan() {
    const changed = tjKaryawan.value.filter(s => s.nominal !== s.original);
    if (changed.length === 0) return;
    tjKaryawanForm.scales = changed.map(s => ({ id: s.id, nominal: s.nominal }));
    tjKaryawanForm.put(route(`${prefix.value}.kebijakan-kompensasi.update-scales-jabatan`), {
        preserveScroll: true,
        onSuccess: () => {
            tjKaryawan.value.forEach(s => { s.original = s.nominal; });
        },
    });
}

function resetTjKaryawan() {
    tjKaryawan.value.forEach(s => { s.nominal = s.original; });
}

// ─── Tunjangan Variabel Scale (inline edit) ──────────────
const tjVariabel = ref(props.scalesVariabel.map(s => ({
    id: s.id,
    golongan: s.golongan_ruang?.kode || '-',
    golongan_nama: s.golongan_ruang?.nama || '-',
    nominal: Number(s.nominal_maksimum),
    original: Number(s.nominal_maksimum),
})));

const tjVariabelForm = useForm({ scales: [] });

function saveTjVariabel() {
    const changed = tjVariabel.value.filter(s => s.nominal !== s.original);
    if (changed.length === 0) return;
    tjVariabelForm.scales = changed.map(s => ({ id: s.id, nominal_maksimum: s.nominal }));
    tjVariabelForm.put(route(`${prefix.value}.kebijakan-kompensasi.update-scales-variabel`), {
        preserveScroll: true,
        onSuccess: () => {
            tjVariabel.value.forEach(s => { s.original = s.nominal; });
        },
    });
}

function resetTjVariabel() {
    tjVariabel.value.forEach(s => { s.nominal = s.original; });
}
</script>

<template>
    <Head title="Kebijakan Kompensasi" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-[8px] bg-primary/10">
                    <Shield class="h-5 w-5 text-primary" />
                </div>
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">Kebijakan Kompensasi</h2>
                    <p class="text-xs text-muted-foreground">Dikelola bersama Admin &amp; BPSDM — Semua perubahan tercatat di audit log</p>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- ─── Section 1: Pengaturan Payroll (payroll_settings) ─── -->
            <Card>
                <CardHeader>
                    <CardTitle>Pengaturan Payroll</CardTitle>
                    <CardDescription>Threshold global dan pengaturan aktif/tidaknya komponen BPJS.</CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="saveSettings" class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <!-- Tunjangan Makan -->
                            <div class="grid gap-2">
                                <div class="flex items-center gap-2">
                                    <Label>Tj. Makan per Hari (Rp)</Label>
                                    <Badge v-if="!isConfirmed('tunjangan_makan')" variant="outline" class="border-amber-300 bg-amber-50 text-amber-700 text-xs">
                                        <AlertTriangle class="mr-1 h-3 w-3" /> Belum dikonfirmasi
                                    </Badge>
                                    <Badge v-else variant="outline" class="border-emerald-300 bg-emerald-50 text-emerald-700 text-xs">
                                        <CheckCircle2 class="mr-1 h-3 w-3" /> Dikonfirmasi
                                    </Badge>
                                </div>
                                <Input v-model="settingsForm.tunjangan_makan_per_hari" type="number" step="0.01" min="0" placeholder="Kosongkan = belum ditentukan" class="max-w-xs" />
                                <p class="text-xs text-muted-foreground">Kosongkan (NULL) = belum diisi. Isi 0 = tidak ada tunjangan makan.</p>
                            </div>

                            <!-- Lembur Basis -->
                            <div class="grid gap-2">
                                <div class="flex items-center gap-2">
                                    <Label>Basis Gaji Dasar Lembur</Label>
                                    <Badge v-if="!isConfirmed('lembur_basis')" variant="outline" class="border-amber-300 bg-amber-50 text-amber-700 text-xs">
                                        <AlertTriangle class="mr-1 h-3 w-3" /> Belum dikonfirmasi
                                    </Badge>
                                    <Badge v-else variant="outline" class="border-emerald-300 bg-emerald-50 text-emerald-700 text-xs">
                                        <CheckCircle2 class="mr-1 h-3 w-3" /> Dikonfirmasi
                                    </Badge>
                                </div>
                                <Select v-model="settingsForm.lembur_basis_gaji_dasar">
                                    <SelectTrigger class="max-w-xs"><SelectValue /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="gaji_pokok_saja">Gaji Pokok Saja</SelectItem>
                                        <SelectItem value="gaji_pokok_plus_tunjangan_tetap">Gaji Pokok + Tunjangan Tetap</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <!-- Jam Kerja -->
                            <div class="grid gap-2">
                                <div class="flex items-center gap-2">
                                    <Label>Jam Kerja / Bulan</Label>
                                    <Badge v-if="!isConfirmed('lembur_jam')" variant="outline" class="border-amber-300 bg-amber-50 text-amber-700 text-xs">
                                        <AlertTriangle class="mr-1 h-3 w-3" /> Belum dikonfirmasi
                                    </Badge>
                                    <Badge v-else variant="outline" class="border-emerald-300 bg-emerald-50 text-emerald-700 text-xs">
                                        <CheckCircle2 class="mr-1 h-3 w-3" /> Dikonfirmasi
                                    </Badge>
                                </div>
                                <Input v-model="settingsForm.lembur_jam_kerja_sebulan" type="number" min="1" class="max-w-xs" />
                                <p class="text-xs text-muted-foreground">Default 120 jam/bulan sesuai dokumen resmi.</p>
                            </div>

                            <!-- % Tunjangan Variabel -->
                            <div class="grid gap-2">
                                <div class="flex items-center gap-2">
                                    <Label>% Tj. Variabel Default</Label>
                                    <Badge v-if="!isConfirmed('tunjangan_variabel')" variant="outline" class="border-amber-300 bg-amber-50 text-amber-700 text-xs">
                                        <AlertTriangle class="mr-1 h-3 w-3" /> Belum dikonfirmasi
                                    </Badge>
                                    <Badge v-else variant="outline" class="border-emerald-300 bg-emerald-50 text-emerald-700 text-xs">
                                        <CheckCircle2 class="mr-1 h-3 w-3" /> Dikonfirmasi
                                    </Badge>
                                </div>
                                <Input v-model="settingsForm.tunjangan_variabel_default_percentage" type="number" step="0.01" min="0" max="100" class="max-w-xs" />
                            </div>

                            <!-- BPJS Kes -->
                            <div class="grid gap-2">
                                <div class="flex items-center gap-2">
                                    <Label>BPJS Kesehatan</Label>
                                    <Badge v-if="!isConfirmed('bpjs_kes')" variant="outline" class="border-amber-300 bg-amber-50 text-amber-700 text-xs">
                                        <AlertTriangle class="mr-1 h-3 w-3" /> Belum dikonfirmasi
                                    </Badge>
                                    <Badge v-else variant="outline" class="border-emerald-300 bg-emerald-50 text-emerald-700 text-xs">
                                        <CheckCircle2 class="mr-1 h-3 w-3" /> Dikonfirmasi
                                    </Badge>
                                </div>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input v-model="settingsForm.bpjs_kesehatan_aktif" type="checkbox" class="rounded border-gray-300" />
                                    <span class="text-sm">Aktifkan potongan BPJS Kesehatan</span>
                                </label>
                            </div>

                            <!-- BPJS TK -->
                            <div class="grid gap-2">
                                <div class="flex items-center gap-2">
                                    <Label>BPJS Ketenagakerjaan</Label>
                                    <Badge v-if="!isConfirmed('bpjs_tk')" variant="outline" class="border-amber-300 bg-amber-50 text-amber-700 text-xs">
                                        <AlertTriangle class="mr-1 h-3 w-3" /> Belum dikonfirmasi
                                    </Badge>
                                    <Badge v-else variant="outline" class="border-emerald-300 bg-emerald-50 text-emerald-700 text-xs">
                                        <CheckCircle2 class="mr-1 h-3 w-3" /> Dikonfirmasi
                                    </Badge>
                                </div>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input v-model="settingsForm.bpjs_tk_aktif" type="checkbox" class="rounded border-gray-300" />
                                    <span class="text-sm">Aktifkan BPJS Ketenagakerjaan</span>
                                </label>
                            </div>

                            <!-- Kategori Risiko JKK -->
                            <div class="grid gap-2" v-if="settingsForm.bpjs_tk_aktif">
                                <div class="flex items-center gap-2">
                                    <Label>Kategori Risiko JKK</Label>
                                    <Badge v-if="!isConfirmed('bpjs_tk_risiko')" variant="outline" class="border-amber-300 bg-amber-50 text-amber-700 text-xs">
                                        <AlertTriangle class="mr-1 h-3 w-3" /> Belum dikonfirmasi
                                    </Badge>
                                    <Badge v-else variant="outline" class="border-emerald-300 bg-emerald-50 text-emerald-700 text-xs">
                                        <CheckCircle2 class="mr-1 h-3 w-3" /> Dikonfirmasi
                                    </Badge>
                                </div>
                                <Select v-model="settingsForm.bpjs_tk_kategori_risiko_jkk">
                                    <SelectTrigger class="max-w-xs"><SelectValue placeholder="-- Pilih --" /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="sangat_rendah">Sangat Rendah (0,24%)</SelectItem>
                                        <SelectItem value="rendah">Rendah (0,54%)</SelectItem>
                                        <SelectItem value="sedang">Sedang (0,89%)</SelectItem>
                                        <SelectItem value="tinggi">Tinggi (1,27%)</SelectItem>
                                        <SelectItem value="sangat_tinggi">Sangat Tinggi (1,74%)</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <Button type="submit" :disabled="settingsForm.processing">
                                <Save class="mr-1 h-4 w-4" /> Simpan Pengaturan
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <!-- ─── Section 2: Tunjangan Jabatan Karyawan Scale ─── -->
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle>Tunjangan Jabatan Karyawan / Tendik</CardTitle>
                            <CardDescription>Nominal per golongan — SOURCE §8</CardDescription>
                        </div>
                        <div class="flex gap-2" v-if="tjKaryawan.some(s => s.nominal !== s.original)">
                            <Button variant="outline" size="sm" @click="resetTjKaryawan">Reset</Button>
                            <Button size="sm" @click="saveTjKaryawan" :disabled="tjKaryawanForm.processing">
                                <Save class="mr-1 h-4 w-4" /> Simpan
                            </Button>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Golongan</TableHead>
                                    <TableHead>Nama</TableHead>
                                    <TableHead class="text-right">Nominal (Rp)</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="row in tjKaryawan" :key="row.id">
                                    <TableCell class="font-medium">{{ row.golongan }}</TableCell>
                                    <TableCell class="text-muted-foreground">{{ row.golongan_nama }}</TableCell>
                                    <TableCell class="text-right">
                                        <Input
                                            v-model.number="row.nominal"
                                            type="number"
                                            min="0"
                                            class="w-40 text-right"
                                        />
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>

            <!-- ─── Section 3: Tunjangan Variabel Scale ─── -->
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle>Tunjangan Variabel — Nominal Maksimum</CardTitle>
                            <CardDescription>Per golongan — dikalikan % dari Pengaturan Payroll — SOURCE §9</CardDescription>
                        </div>
                        <div class="flex gap-2" v-if="tjVariabel.some(s => s.nominal !== s.original)">
                            <Button variant="outline" size="sm" @click="resetTjVariabel">Reset</Button>
                            <Button size="sm" @click="saveTjVariabel" :disabled="tjVariabelForm.processing">
                                <Save class="mr-1 h-4 w-4" /> Simpan
                            </Button>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Golongan</TableHead>
                                    <TableHead>Nama</TableHead>
                                    <TableHead class="text-right">Nominal Maksimum (Rp)</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="row in tjVariabel" :key="row.id">
                                    <TableCell class="font-medium">{{ row.golongan }}</TableCell>
                                    <TableCell class="text-muted-foreground">{{ row.golongan_nama }}</TableCell>
                                    <TableCell class="text-right">
                                        <Input
                                            v-model.number="row.nominal"
                                            type="number"
                                            min="0"
                                            class="w-40 text-right"
                                        />
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>

            <!-- ─── Audit Log ─── -->
            <Card v-if="logs.length > 0">
                <CardHeader>
                    <CardTitle>Riwayat Perubahan</CardTitle>
                    <CardDescription>50 perubahan terakhir — semua perubahan tercatat beserta role pengubah</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Waktu</TableHead>
                                    <TableHead>Area</TableHead>
                                    <TableHead>Field / Golongan</TableHead>
                                    <TableHead>Nilai Lama</TableHead>
                                    <TableHead>Nilai Baru</TableHead>
                                    <TableHead>Oleh</TableHead>
                                    <TableHead>Role</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="(log, idx) in logs" :key="idx">
                                    <TableCell class="whitespace-nowrap text-xs">{{ new Date(log.at).toLocaleString('id-ID') }}</TableCell>
                                    <TableCell>
                                        <Badge variant="outline" class="text-xs">
                                            {{ log.type === 'payroll_setting' ? 'Pengaturan' : log.type === 'tj_karyawan' ? 'Tj. Jabatan' : 'Tj. Variabel' }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="font-mono text-xs">{{ log.field }}</TableCell>
                                    <TableCell class="font-mono text-xs">{{ log.lama ?? '(kosong)' }}</TableCell>
                                    <TableCell class="font-mono text-xs">{{ log.baru ?? '(kosong)' }}</TableCell>
                                    <TableCell class="text-xs">{{ log.user ?? '-' }}</TableCell>
                                    <TableCell>
                                        <Badge :variant="log.role === 'admin' ? 'default' : 'secondary'" class="text-xs capitalize">
                                            {{ log.role ?? '-' }}
                                        </Badge>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
