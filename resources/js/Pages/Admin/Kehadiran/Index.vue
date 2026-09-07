<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useRoutePrefix } from '@/composables/useRoutePrefix.js';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import {
    AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent,
    AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Plus, Upload, FileDown, FileText, Trash2, Pencil } from '@lucide/vue';

const prefix = useRoutePrefix();

const props = defineProps({
    kehadirans: Object,
    jabatans: Array,
    pegawais: Array,
    filters: Object,
    can: Object,
});

const ALL = '__all__';
const search = ref(props.filters.search || '');
const periode = ref(props.filters.periode || '');
const jabatanId = ref(props.filters.jabatan_id || ALL);
const showCreateModal = ref(false);
const showImportModal = ref(false);

function applyFilter() {
    router.get(route(`${prefix.value}.kehadiran.index`), {
        search: search.value,
        periode: periode.value,
        jabatan_id: jabatanId.value === ALL ? '' : jabatanId.value,
    }, {
        preserveState: true,
        replace: true,
    });
}

function formatPeriode(val) {
    if (!val) return '-';
    const d = new Date(val);
    return d.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
}

const createForm = useForm({
    pegawai_id: '',
    periode: '',
    hadir: 0,
    sakit: 0,
    alpha: 0,
});

function submitCreate() {
    createForm.post(route(`${prefix.value}.kehadiran.store`), {
        onSuccess: () => { showCreateModal.value = false; createForm.reset(); },
    });
}

const importForm = useForm({ file: null, periode: '' });

function submitImport() {
    importForm.post(route(`${prefix.value}.kehadiran.import`), {
        onSuccess: () => { showImportModal.value = false; importForm.reset(); },
    });
}

function onFileChange(e) { importForm.file = e.target.files[0]; }

function doDelete(id) {
    router.delete(route(`${prefix.value}.kehadiran.destroy`, id));
}
</script>

<template>
    <Head title="Kehadiran" />

    <AuthenticatedLayout>
        <div class="px-4 pt-6 pb-2 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-800">Kehadiran</h1>
                    <p class="mt-1 text-sm text-muted-foreground">{{ kehadirans.total }} data kehadiran</p>
                </div>
                <div class="flex items-center gap-2">
                    <Button v-if="periode" variant="outline" size="sm" as-child>
                        <a :href="route(`${prefix}.laporan-absensi.pdf`, { periode })" target="_blank">
                            <FileText class="h-4 w-4" /> PDF
                        </a>
                    </Button>
                    <Button v-if="periode" variant="outline" size="sm" as-child>
                        <a :href="route(`${prefix}.laporan-absensi.export`, { periode })">
                            <FileDown class="h-4 w-4" /> Excel
                        </a>
                    </Button>
                    <template v-if="can.create">
                        <Dialog v-model:open="showImportModal">
                            <DialogTrigger as-child>
                                <Button variant="outline" size="sm">
                                    <Upload class="h-4 w-4" /> Import
                                </Button>
                            </DialogTrigger>
                            <DialogContent>
                                <DialogHeader>
                                    <DialogTitle>Import Kehadiran dari Excel</DialogTitle>
                                </DialogHeader>
                                <form @submit.prevent="submitImport" class="space-y-4">
                                    <div class="space-y-1">
                                        <Label>Periode</Label>
                                        <Input v-model="importForm.periode" type="date" required />
                                        <p v-if="importForm.errors.periode" class="text-sm text-destructive">{{ importForm.errors.periode }}</p>
                                    </div>
                                    <div class="space-y-1">
                                        <Label>File Excel (.xlsx/.xls)</Label>
                                        <input type="file" accept=".xlsx,.xls" @change="onFileChange" required
                                            class="block w-full text-sm text-muted-foreground file:mr-3 file:rounded-lg file:border-0 file:bg-primary file:px-4 file:py-2 file:text-sm file:font-semibold file:text-primary-foreground hover:file:bg-primary/90" />
                                        <p v-if="importForm.errors.file" class="text-sm text-destructive">{{ importForm.errors.file }}</p>
                                    </div>
                                    <div class="rounded-lg bg-muted p-3 text-xs text-muted-foreground">
                                        <p class="font-medium">Format kolom Excel:</p>
                                        <p>nik | hadir | sakit | alpha</p>
                                    </div>
                                    <DialogFooter>
                                        <Button type="button" variant="outline" @click="showImportModal = false">Batal</Button>
                                        <Button type="submit" :disabled="importForm.processing">Import</Button>
                                    </DialogFooter>
                                </form>
                            </DialogContent>
                        </Dialog>

                        <Dialog v-model:open="showCreateModal">
                            <DialogTrigger as-child>
                                <Button size="sm">
                                    <Plus class="h-4 w-4" /> Tambah Data
                                </Button>
                            </DialogTrigger>
                            <DialogContent>
                                <DialogHeader>
                                    <DialogTitle>Tambah Data Kehadiran</DialogTitle>
                                </DialogHeader>
                                <form @submit.prevent="submitCreate" class="space-y-4">
                                    <div class="space-y-1">
                                        <Label>Pegawai</Label>
                                        <Select v-model="createForm.pegawai_id">
                                            <SelectTrigger class="w-full"><SelectValue placeholder="-- Pilih Pegawai --" /></SelectTrigger>
                                            <SelectContent>
                                                <SelectItem v-for="p in pegawais" :key="p.id" :value="p.id">
                                                    {{ p.nama_pegawai }} ({{ p.nik }})
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <p v-if="createForm.errors.pegawai_id" class="text-sm text-destructive">{{ createForm.errors.pegawai_id }}</p>
                                    </div>
                                    <div class="space-y-1">
                                        <Label>Periode</Label>
                                        <Input v-model="createForm.periode" type="date" required />
                                        <p v-if="createForm.errors.periode" class="text-sm text-destructive">{{ createForm.errors.periode }}</p>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4">
                                        <div class="space-y-1">
                                            <Label>Hadir</Label>
                                            <Input v-model.number="createForm.hadir" type="number" min="0" required />
                                        </div>
                                        <div class="space-y-1">
                                            <Label>Sakit</Label>
                                            <Input v-model.number="createForm.sakit" type="number" min="0" required />
                                        </div>
                                        <div class="space-y-1">
                                            <Label>Alpha</Label>
                                            <Input v-model.number="createForm.alpha" type="number" min="0" required />
                                        </div>
                                    </div>
                                    <DialogFooter>
                                        <Button type="button" variant="outline" @click="showCreateModal = false">Batal</Button>
                                        <Button type="submit" :disabled="createForm.processing">Simpan</Button>
                                    </DialogFooter>
                                </form>
                            </DialogContent>
                        </Dialog>
                    </template>
                </div>
            </div>

            <!-- Filter bar -->
            <div class="mb-6 flex flex-wrap items-end gap-3">
                <div class="space-y-1">
                    <Label>Periode</Label>
                    <Input v-model="periode" @change="applyFilter" type="month" class="w-44" />
                </div>
                <div class="space-y-1">
                    <Label>Jabatan</Label>
                    <Select v-model="jabatanId" @update:model-value="applyFilter">
                        <SelectTrigger class="w-44">
                            <SelectValue placeholder="Semua" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem :value="ALL">Semua</SelectItem>
                            <SelectItem v-for="j in jabatans" :key="j.id" :value="String(j.id)">
                                {{ j.nama_jabatan }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div class="space-y-1">
                    <Label>Pegawai</Label>
                    <Input v-model="search" @keyup.enter="applyFilter" placeholder="Nama atau NIK..." class="w-48" />
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-[12px] overflow-hidden"
                style="box-shadow: 0 1px 2px rgba(0,0,0,.04), 0 8px 24px rgba(0,0,0,.04);">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="w-12">No</TableHead>
                            <TableHead>Pegawai</TableHead>
                            <TableHead>NIK</TableHead>
                            <TableHead>Jabatan</TableHead>
                            <TableHead class="text-right">Hadir</TableHead>
                            <TableHead class="text-right">Sakit</TableHead>
                            <TableHead class="text-right">Alpha</TableHead>
                            <TableHead>Periode</TableHead>
                            <TableHead v-if="can.create" class="text-right">Aksi</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="(k, index) in kehadirans.data" :key="k.id">
                            <TableCell class="text-muted-foreground">
                                {{ (kehadirans.current_page - 1) * kehadirans.per_page + index + 1 }}
                            </TableCell>
                            <TableCell class="font-medium">{{ k.pegawai?.nama_pegawai ?? '-' }}</TableCell>
                            <TableCell class="text-muted-foreground">{{ k.pegawai?.nik ?? '-' }}</TableCell>
                            <TableCell class="text-muted-foreground">{{ k.pegawai?.jabatan?.nama_jabatan ?? '-' }}</TableCell>
                            <TableCell class="text-right tabular-nums">{{ k.hadir }}</TableCell>
                            <TableCell class="text-right tabular-nums">{{ k.sakit }}</TableCell>
                            <TableCell class="text-right tabular-nums">{{ k.alpha }}</TableCell>
                            <TableCell class="text-muted-foreground">{{ formatPeriode(k.periode) }}</TableCell>
                            <TableCell v-if="can.create" class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <Link v-if="can.update" :href="route(`${prefix}.kehadiran.edit`, k.id)"
                                        class="inline-flex items-center justify-center h-8 w-8 rounded-md text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition">
                                        <Pencil class="h-3.5 w-3.5" />
                                    </Link>
                                    <AlertDialog v-if="can.delete">
                                    <AlertDialogTrigger as-child>
                                        <Button variant="destructive" size="icon-sm">
                                            <Trash2 class="h-3.5 w-3.5" />
                                        </Button>
                                    </AlertDialogTrigger>
                                    <AlertDialogContent>
                                        <AlertDialogHeader>
                                            <AlertDialogTitle>Hapus Kehadiran</AlertDialogTitle>
                                            <AlertDialogDescription>
                                                Yakin ingin menghapus data kehadiran ini? Tindakan ini tidak dapat dibatalkan.
                                            </AlertDialogDescription>
                                        </AlertDialogHeader>
                                        <AlertDialogFooter>
                                            <AlertDialogCancel>Batal</AlertDialogCancel>
                                            <AlertDialogAction @click="doDelete(k.id)">Hapus</AlertDialogAction>
                                        </AlertDialogFooter>
                                    </AlertDialogContent>
                                    </AlertDialog>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="kehadirans.data.length === 0">
                            <TableCell :colspan="can.create ? 9 : 8" class="text-center py-12 text-muted-foreground">
                                Belum ada data kehadiran. Gunakan "Tambah Data" atau "Import Excel" untuk menambahkan data.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Pagination -->
            <div v-if="kehadirans.last_page > 1" class="mt-4 flex justify-center">
                <nav class="flex items-center gap-1">
                    <template v-for="link in kehadirans.links" :key="link.url">
                        <span v-if="!link.url" class="px-3 py-2 text-sm text-gray-400">...</span>
                        <Button v-else variant="ghost" size="sm"
                            :class="link.active ? 'bg-primary text-primary-foreground hover:bg-primary/90' : ''"
                            as-child>
                            <Link :href="link.url" v-html="link.label" />
                        </Button>
                    </template>
                </nav>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
