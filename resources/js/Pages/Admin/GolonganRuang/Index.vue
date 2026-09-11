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
    items: Object,
    can: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
function applyFilter() {
    router.get(route(`${prefix.value}.golongan-ruang.index`), { search: search.value }, {
        preserveState: true,
        replace: true,
    });
}

function doDelete(id) {
    router.delete(route(`${prefix.value}.golongan-ruang.destroy`, id));
}
</script>

<template>
    <Head title="Data Golongan Ruang" />

    <AuthenticatedLayout>
        <div class="px-4 pt-6 pb-2 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-semibold text-gray-800">Data Golongan Ruang</h1>
                <Button v-if="can.create" as-child>
                    <Link :href="route(`${prefix}.golongan-ruang.create`)">Tambah Golongan Ruang</Link>
                </Button>
            </div>

            <div class="mb-4">
                <Input
                    v-model="search"
                    @keyup.enter="applyFilter"
                    placeholder="Cari kode atau nama..."
                    class="max-w-sm"
                />
            </div>

            <div class="bg-white rounded-[12px] overflow-hidden"
                style="box-shadow: 0 1px 2px rgba(0,0,0,.04), 0 8px 24px rgba(0,0,0,.04);">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="w-12">No</TableHead>
                            <TableHead>Kode</TableHead>
                            <TableHead>Nama</TableHead>
                            <TableHead>Urutan</TableHead>
                            <TableHead class="text-right">Aksi</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="(item, index) in items.data" :key="item.id">
                            <TableCell class="text-muted-foreground">
                                {{ (items.current_page - 1) * items.per_page + index + 1 }}
                            </TableCell>
                            <TableCell class="font-medium">{{ item.kode }}</TableCell>
                            <TableCell>{{ item.nama }}</TableCell>
                            <TableCell class="text-muted-foreground">{{ item.urutan }}</TableCell>
                            <TableCell class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <Button v-if="can.update" variant="ghost" size="icon-sm" as-child>
                                        <Link :href="route(`${prefix}.golongan-ruang.edit`, item.id)">
                                            <Pencil class="h-3.5 w-3.5" />
                                        </Link>
                                    </Button>
                                    <AlertDialog v-if="can.delete">
                                        <AlertDialogTrigger as-child>
                                            <Button variant="destructive" size="icon-sm">
                                                <Trash2 class="h-3.5 w-3.5" />
                                            </Button>
                                        </AlertDialogTrigger>
                                        <AlertDialogContent>
                                            <AlertDialogHeader>
                                                <AlertDialogTitle>Hapus Golongan Ruang</AlertDialogTitle>
                                                <AlertDialogDescription>
                                                    Yakin ingin menghapus "{{ item.kode }}"? Tindakan ini tidak dapat dibatalkan.
                                                </AlertDialogDescription>
                                            </AlertDialogHeader>
                                            <AlertDialogFooter>
                                                <AlertDialogCancel>Batal</AlertDialogCancel>
                                                <AlertDialogAction @click="doDelete(item.id)">Hapus</AlertDialogAction>
                                            </AlertDialogFooter>
                                        </AlertDialogContent>
                                    </AlertDialog>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="items.data.length === 0">
                            <TableCell colspan="5" class="text-center py-12 text-muted-foreground">
                                Belum ada data golongan ruang.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <div v-if="items.last_page > 1" class="mt-4 flex justify-center">
                <nav class="flex items-center gap-1">
                    <template v-for="link in items.links" :key="link.url">
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
