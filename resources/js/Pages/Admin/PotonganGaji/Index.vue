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
import { Badge } from '@/components/ui/badge';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger } from '@/components/ui/alert-dialog';
import { Plus, Pencil, Trash2 } from '@lucide/vue';

const prefix = useRoutePrefix();

const props = defineProps({
    potongans: Object,
    can: Object,
});

const showCreateModal = ref(false);
const editingPotongan = ref(null);

const createForm = useForm({
    nama_potongan: '',
    tipe: 'nominal',
    nilai: 0,
    is_alpha_penalty: false,
    aktif: true,
});

function openCreate() {
    editingPotongan.value = null;
    createForm.reset();
    createForm.is_alpha_penalty = false;
    createForm.aktif = true;
    showCreateModal.value = true;
}

function openEdit(potongan) {
    editingPotongan.value = potongan;
    createForm.nama_potongan = potongan.nama_potongan;
    createForm.tipe = potongan.tipe;
    createForm.nilai = potongan.nilai;
    createForm.is_alpha_penalty = potongan.is_alpha_penalty;
    createForm.aktif = potongan.aktif;
    showCreateModal.value = true;
}

function submitForm() {
    if (editingPotongan.value) {
        createForm.patch(route(`${prefix.value}.potongan-gaji.update`, editingPotongan.value.id), {
            onSuccess: () => { showCreateModal.value = false; createForm.reset(); editingPotongan.value = null; },
        });
    } else {
        createForm.post(route(`${prefix.value}.potongan-gaji.store`), {
            onSuccess: () => { showCreateModal.value = false; createForm.reset(); },
        });
    }
}

function toggleAktif(id) {
    router.patch(route(`${prefix.value}.potongan-gaji.toggle`, id), {}, { preserveState: true });
}

function doDelete(id) {
    router.delete(route(`${prefix.value}.potongan-gaji.destroy`, id));
}

function formatRupiah(val) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
}
</script>

<template>
    <Head title="Potongan Gaji" />

    <AuthenticatedLayout>
        <div class="px-4 pt-6 pb-2 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-semibold text-gray-800">Potongan Gaji</h1>
                <Button v-if="can.create" @click="openCreate"><Plus class="h-4 w-4" /> Tambah Potongan</Button>
            </div>

            <div class="bg-white rounded-[12px] overflow-hidden" style="box-shadow: 0 1px 2px rgba(0,0,0,.04), 0 8px 24px rgba(0,0,0,.04);">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Nama Potongan</TableHead>
                            <TableHead>Tipe</TableHead>
                            <TableHead class="text-right">Nilai</TableHead>
                            <TableHead class="text-center">Alpha Penalty</TableHead>
                            <TableHead class="text-center">Status</TableHead>
                            <TableHead class="text-right">Aksi</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="p in potongans.data" :key="p.id">
                            <TableCell class="font-medium">{{ p.nama_potongan }}</TableCell>
                            <TableCell class="capitalize text-muted-foreground">{{ p.tipe }}</TableCell>
                            <TableCell class="text-right tabular-nums">
                                {{ p.tipe === 'persentase' ? p.nilai + '%' : formatRupiah(p.nilai) }}
                            </TableCell>
                            <TableCell class="text-center">
                                <Badge v-if="p.is_alpha_penalty" variant="default" class="text-xs">Ya</Badge>
                                <span v-else class="text-muted-foreground text-xs">-</span>
                            </TableCell>
                            <TableCell class="text-center">
                                <Switch :checked="p.aktif" @update:checked="toggleAktif(p.id)" />
                            </TableCell>
                            <TableCell class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <Button v-if="can.update" variant="ghost" size="icon-sm" @click="openEdit(p)">
                                        <Pencil class="h-3.5 w-3.5" />
                                    </Button>
                                    <AlertDialog v-if="can.delete">
                                        <AlertDialogTrigger as-child>
                                            <Button variant="destructive" size="icon-sm"><Trash2 class="h-3.5 w-3.5" /></Button>
                                        </AlertDialogTrigger>
                                        <AlertDialogContent>
                                            <AlertDialogHeader>
                                                <AlertDialogTitle>Hapus Potongan</AlertDialogTitle>
                                                <AlertDialogDescription>Yakin ingin menghapus potongan gaji ini?</AlertDialogDescription>
                                            </AlertDialogHeader>
                                            <AlertDialogFooter>
                                                <AlertDialogCancel>Batal</AlertDialogCancel>
                                                <AlertDialogAction @click="doDelete(p.id)">Hapus</AlertDialogAction>
                                            </AlertDialogFooter>
                                        </AlertDialogContent>
                                    </AlertDialog>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="potongans.data.length === 0">
                            <TableCell colspan="6" class="text-center py-8 text-muted-foreground">Belum ada data potongan gaji.</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <div v-if="potongans.last_page > 1" class="mt-4 flex items-center justify-between">
                <p class="text-sm text-muted-foreground">Menampilkan {{ potongans.from }}-{{ potongans.to }} dari {{ potongans.total }} data</p>
                <nav class="flex items-center gap-1">
                    <template v-for="link in potongans.links" :key="link.url">
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
                    <DialogTitle>{{ editingPotongan ? 'Edit Potongan Gaji' : 'Tambah Potongan Gaji' }}</DialogTitle>
                </DialogHeader>
                <form @submit.prevent="submitForm" class="space-y-4">
                    <div class="space-y-1">
                        <Label>Nama Potongan</Label>
                        <Input v-model="createForm.nama_potongan" type="text" />
                        <p v-if="createForm.errors.nama_potongan" class="text-sm text-destructive">{{ createForm.errors.nama_potongan }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <Label>Tipe</Label>
                            <Select v-model="createForm.tipe">
                                <SelectTrigger><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="nominal">Nominal (Rp)</SelectItem>
                                    <SelectItem value="persentase">Persentase (%)</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-1">
                            <Label>Nilai</Label>
                            <Input v-model.number="createForm.nilai" type="number" step="0.01" min="0" />
                            <p v-if="createForm.errors.nilai" class="text-sm text-destructive">{{ createForm.errors.nilai }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <Switch id="is_alpha" :checked="createForm.is_alpha_penalty" @update:checked="createForm.is_alpha_penalty = $event" />
                        <Label for="is_alpha" class="cursor-pointer">Alpha Penalty (potongan khusus alpha)</Label>
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
