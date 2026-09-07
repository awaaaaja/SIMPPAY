<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useRoutePrefix } from '@/composables/useRoutePrefix.js';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger } from '@/components/ui/alert-dialog';
import { Plus, Pencil, Trash2 } from '@lucide/vue';

const prefix = useRoutePrefix();

const props = defineProps({
    tunjangans: Object,
    jabatans: Array,
    pegawais: Array,
    filters: Object,
    can: Object,
});

const targetTipe = ref(props.filters.target_tipe || '');

function applyFilter() {
    router.get(route(`${prefix.value}.tunjangan-gaji.index`), { target_tipe: targetTipe.value }, {
        preserveState: true, replace: true,
    });
}

const showCreateModal = ref(false);
const editingTunjangan = ref(null);

const createForm = useForm({
    nama_tunjangan: '',
    target_tipe: 'semua',
    jabatan_id: '',
    pegawai_id: '',
    nominal: 0,
    aktif: true,
});

function openCreate() {
    editingTunjangan.value = null;
    createForm.reset();
    createForm.target_tipe = 'semua';
    createForm.aktif = true;
    showCreateModal.value = true;
}

function openEdit(tunjangan) {
    editingTunjangan.value = tunjangan;
    createForm.nama_tunjangan = tunjangan.nama_tunjangan;
    createForm.target_tipe = tunjangan.target_tipe;
    createForm.jabatan_id = tunjangan.jabatan_id || '';
    createForm.pegawai_id = tunjangan.pegawai_id || '';
    createForm.nominal = tunjangan.nominal;
    createForm.aktif = tunjangan.aktif;
    showCreateModal.value = true;
}

function submitForm() {
    if (editingTunjangan.value) {
        createForm.patch(route(`${prefix.value}.tunjangan-gaji.update`, editingTunjangan.value.id), {
            onSuccess: () => { showCreateModal.value = false; createForm.reset(); editingTunjangan.value = null; },
        });
    } else {
        createForm.post(route(`${prefix.value}.tunjangan-gaji.store`), {
            onSuccess: () => { showCreateModal.value = false; createForm.reset(); },
        });
    }
}

function toggleAktif(id) {
    router.patch(route(`${prefix.value}.tunjangan-gaji.toggle`, id), {}, { preserveState: true });
}

function doDelete(id) {
    router.delete(route(`${prefix.value}.tunjangan-gaji.destroy`, id));
}

function formatRupiah(val) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
}

function targetLabel(item) {
    if (item.target_tipe === 'semua') return 'Semua Pegawai';
    if (item.target_tipe === 'jabatan') return item.jabatan?.nama_jabatan || '-';
    return item.pegawai?.nama_pegawai || '-';
}
</script>

<template>
    <Head title="Tunjangan Gaji" />

    <AuthenticatedLayout>
        <div class="px-4 pt-6 pb-2 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-semibold text-gray-800">Tunjangan Gaji</h1>
                <Button v-if="can.create" @click="openCreate"><Plus class="h-4 w-4" /> Tambah Tunjangan</Button>
            </div>

            <!-- Filter -->
            <div class="mb-4">
                <Select v-model="targetTipe" @update:model-value="applyFilter">
                    <SelectTrigger class="w-48"><SelectValue placeholder="Semua Target" /></SelectTrigger>
                    <SelectContent>
                        <SelectItem value="">Semua Target</SelectItem>
                        <SelectItem value="semua">Semua Pegawai</SelectItem>
                        <SelectItem value="jabatan">Per Jabatan</SelectItem>
                        <SelectItem value="pegawai">Per Pegawai</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <div class="bg-white rounded-[12px] overflow-hidden" style="box-shadow: 0 1px 2px rgba(0,0,0,.04), 0 8px 24px rgba(0,0,0,.04);">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Nama Tunjangan</TableHead>
                            <TableHead>Target</TableHead>
                            <TableHead class="text-right">Nominal</TableHead>
                            <TableHead class="text-center">Status</TableHead>
                            <TableHead class="text-right">Aksi</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="t in tunjangans.data" :key="t.id">
                            <TableCell class="font-medium">{{ t.nama_tunjangan }}</TableCell>
                            <TableCell class="text-muted-foreground">{{ targetLabel(t) }}</TableCell>
                            <TableCell class="text-right tabular-nums">{{ formatRupiah(t.nominal) }}</TableCell>
                            <TableCell class="text-center">
                                <Switch :checked="t.aktif" @update:checked="toggleAktif(t.id)" />
                            </TableCell>
                            <TableCell class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <Button v-if="can.update" variant="ghost" size="icon-sm" @click="openEdit(t)">
                                        <Pencil class="h-3.5 w-3.5" />
                                    </Button>
                                    <AlertDialog v-if="can.delete">
                                        <AlertDialogTrigger as-child>
                                            <Button variant="destructive" size="icon-sm"><Trash2 class="h-3.5 w-3.5" /></Button>
                                        </AlertDialogTrigger>
                                        <AlertDialogContent>
                                            <AlertDialogHeader>
                                                <AlertDialogTitle>Hapus Tunjangan</AlertDialogTitle>
                                                <AlertDialogDescription>Yakin ingin menghapus tunjangan gaji ini?</AlertDialogDescription>
                                            </AlertDialogHeader>
                                            <AlertDialogFooter>
                                                <AlertDialogCancel>Batal</AlertDialogCancel>
                                                <AlertDialogAction @click="doDelete(t.id)">Hapus</AlertDialogAction>
                                            </AlertDialogFooter>
                                        </AlertDialogContent>
                                    </AlertDialog>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="tunjangans.data.length === 0">
                            <TableCell colspan="5" class="text-center py-8 text-muted-foreground">Belum ada data tunjangan gaji.</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <div v-if="tunjangans.last_page > 1" class="mt-4 flex items-center justify-between">
                <p class="text-sm text-muted-foreground">Menampilkan {{ tunjangans.from }}-{{ tunjangans.to }} dari {{ tunjangans.total }} data</p>
                <nav class="flex items-center gap-1">
                    <template v-for="link in tunjangans.links" :key="link.url">
                        <Button v-if="link.url" variant="ghost" size="sm"
                            :class="link.active ? 'bg-primary text-primary-foreground hover:bg-primary/90' : ''"
                            @click="router.get(link.url, {}, { preserveState: true, replace: true })" v-html="link.label" />
                        <span v-else class="px-3 py-2 text-sm text-gray-400">...</span>
                    </template>
                </nav>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <Dialog v-model:open="showCreateModal">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{{ editingTunjangan ? 'Edit Tunjangan Gaji' : 'Tambah Tunjangan Gaji' }}</DialogTitle>
                </DialogHeader>
                <form @submit.prevent="submitForm" class="space-y-4">
                    <div class="space-y-1">
                        <Label>Nama Tunjangan</Label>
                        <Input v-model="createForm.nama_tunjangan" type="text" />
                        <p v-if="createForm.errors.nama_tunjangan" class="text-sm text-destructive">{{ createForm.errors.nama_tunjangan }}</p>
                    </div>
                    <div class="space-y-1">
                        <Label>Target</Label>
                        <Select v-model="createForm.target_tipe">
                            <SelectTrigger><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="semua">Semua Pegawai</SelectItem>
                                <SelectItem value="jabatan">Per Jabatan</SelectItem>
                                <SelectItem value="pegawai">Per Pegawai</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div v-if="createForm.target_tipe === 'jabatan'" class="space-y-1">
                        <Label>Jabatan</Label>
                        <Select v-model="createForm.jabatan_id">
                            <SelectTrigger><SelectValue placeholder="Pilih Jabatan" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">Pilih Jabatan</SelectItem>
                                <SelectItem v-for="j in jabatans" :key="j.id" :value="String(j.id)">{{ j.nama_jabatan }}</SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="createForm.errors.jabatan_id" class="text-sm text-destructive">{{ createForm.errors.jabatan_id }}</p>
                    </div>
                    <div v-if="createForm.target_tipe === 'pegawai'" class="space-y-1">
                        <Label>Pegawai</Label>
                        <Select v-model="createForm.pegawai_id">
                            <SelectTrigger><SelectValue placeholder="Pilih Pegawai" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">Pilih Pegawai</SelectItem>
                                <SelectItem v-for="p in pegawais" :key="p.id" :value="String(p.id)">{{ p.nama_pegawai }} ({{ p.nik }})</SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="createForm.errors.pegawai_id" class="text-sm text-destructive">{{ createForm.errors.pegawai_id }}</p>
                    </div>
                    <div class="space-y-1">
                        <Label>Nominal (Rp)</Label>
                        <Input v-model.number="createForm.nominal" type="number" step="0.01" min="0" />
                        <p v-if="createForm.errors.nominal" class="text-sm text-destructive">{{ createForm.errors.nominal }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <Switch id="aktif" :checked="createForm.aktif" @update:checked="createForm.aktif = $event" />
                        <Label for="aktif" class="cursor-pointer">Aktif</Label>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="showCreateModal = false">Batal</Button>
                        <Button type="submit" :disabled="createForm.processing">
                            {{ createForm.processing ? 'Menyimpan...' : 'Simpan' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AuthenticatedLayout>
</template>
