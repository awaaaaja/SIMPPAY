<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useRoutePrefix } from '@/composables/useRoutePrefix.js';
import { Table, TableBody, TableCell, TableEmpty, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent,
    AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Pencil, Trash2 } from '@lucide/vue';

const prefix = useRoutePrefix();

const props = defineProps({
    jabatans: Object,
    can: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
function applyFilter() {
    router.get(route(`${prefix.value}.jabatan.index`), { search: search.value }, {
        preserveState: true,
        replace: true,
    });
}

function formatRupiah(val) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
}

function doDelete(id) {
    router.delete(route(`${prefix.value}.jabatan.destroy`, id));
}
</script>

<template>
    <Head title="Data Jabatan" />

    <AuthenticatedLayout>
        <div class="px-4 pt-6 pb-2 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-semibold text-gray-800">Data Jabatan</h1>
                <Button v-if="can.create" as-child>
                    <Link :href="route(`${prefix}.jabatan.create`)">Tambah Jabatan</Link>
                </Button>
            </div>

            <!-- Search -->
            <div class="mb-4">
                <Input
                    v-model="search"
                    @keyup.enter="applyFilter"
                    placeholder="Cari nama jabatan..."
                    class="max-w-sm"
                />
            </div>

            <!-- Table -->
            <div class="bg-white rounded-[12px] overflow-hidden"
                style="box-shadow: 0 1px 2px rgba(0,0,0,.04), 0 8px 24px rgba(0,0,0,.04);">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="w-12">No</TableHead>
                            <TableHead>Nama Jabatan</TableHead>
                            <TableHead class="text-right">Gaji Pokok</TableHead>
                            <TableHead class="text-right">Tj. Transport</TableHead>
                            <TableHead class="text-right">Uang Makan</TableHead>
                            <TableHead class="text-center">Pegawai Aktif</TableHead>
                            <TableHead class="text-right">Aksi</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="(jabatan, index) in jabatans.data" :key="jabatan.id">
                            <TableCell class="text-muted-foreground">
                                {{ (jabatans.current_page - 1) * jabatans.per_page + index + 1 }}
                            </TableCell>
                            <TableCell class="font-medium">{{ jabatan.nama_jabatan }}</TableCell>
                            <TableCell class="text-right tabular-nums">{{ formatRupiah(jabatan.gaji_pokok) }}</TableCell>
                            <TableCell class="text-right tabular-nums">{{ formatRupiah(jabatan.tj_transport) }}</TableCell>
                            <TableCell class="text-right tabular-nums">{{ formatRupiah(jabatan.uang_makan) }}</TableCell>
                            <TableCell class="text-center text-muted-foreground">{{ jabatan.active_pegawai_count }}</TableCell>
                            <TableCell class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <Button v-if="can.update" variant="ghost" size="icon-sm" as-child>
                                        <Link :href="route(`${prefix}.jabatan.edit`, jabatan.id)">
                                            <Pencil class="h-3.5 w-3.5" />
                                        </Link>
                                    </Button>
                                    <AlertDialog v-if="can.delete && jabatan.active_pegawai_count === 0">
                                        <AlertDialogTrigger as-child>
                                            <Button variant="destructive" size="icon-sm">
                                                <Trash2 class="h-3.5 w-3.5" />
                                            </Button>
                                        </AlertDialogTrigger>
                                        <AlertDialogContent>
                                            <AlertDialogHeader>
                                                <AlertDialogTitle>Hapus Jabatan</AlertDialogTitle>
                                                <AlertDialogDescription>
                                                    Yakin ingin menghapus jabatan "{{ jabatan.nama_jabatan }}"? Tindakan ini tidak dapat dibatalkan.
                                                </AlertDialogDescription>
                                            </AlertDialogHeader>
                                            <AlertDialogFooter>
                                                <AlertDialogCancel>Batal</AlertDialogCancel>
                                                <AlertDialogAction @click="doDelete(jabatan.id)">Hapus</AlertDialogAction>
                                            </AlertDialogFooter>
                                        </AlertDialogContent>
                                    </AlertDialog>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="jabatans.data.length === 0">
                            <TableCell colspan="7" class="text-center py-12 text-muted-foreground">
                                Belum ada data jabatan.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Pagination -->
            <div v-if="jabatans.last_page > 1" class="mt-4 flex justify-center">
                <nav class="flex items-center gap-1">
                    <template v-for="link in jabatans.links" :key="link.url">
                        <span v-if="!link.url" class="px-3 py-2 text-sm text-gray-400">...</span>
                        <Button
                            v-else
                            variant="ghost"
                            size="sm"
                            :class="link.active ? 'bg-primary text-primary-foreground hover:bg-primary/90' : ''"
                            as-child
                        >
                            <Link :href="link.url" v-html="link.label" />
                        </Button>
                    </template>
                </nav>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
