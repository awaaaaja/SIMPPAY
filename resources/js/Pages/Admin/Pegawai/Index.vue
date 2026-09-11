<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useRoutePrefix } from '@/composables/useRoutePrefix.js';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import {
    AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent,
    AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Eye, Trash2, Plus } from '@lucide/vue';

const prefix = useRoutePrefix();

const props = defineProps({
    pegawais: Object,
    can: Object,
    filters: Object,
});

const ALL = '__all__';
const search = ref(props.filters.search || '');
const status_pegawai = ref(props.filters.status_pegawai || ALL);

function applyFilter() {
    router.get(route(`${prefix.value}.pegawai.index`), {
        search: search.value,
        status_pegawai: status_pegawai.value === ALL ? '' : status_pegawai.value,
    }, {
        preserveState: true,
        replace: true,
    });
}

function doDelete(id) {
    router.delete(route(`${prefix.value}.pegawai.destroy`, id));
}

function statusVariant(status) {
    const map = { aktif: 'default', nonaktif: 'destructive', pensiun: 'outline' };
    return map[status] || 'outline';
}
</script>

<template>
    <Head title="Data Pegawai" />

    <AuthenticatedLayout>
        <div class="px-4 pt-6 pb-2 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-800">Pegawai</h1>
                    <p class="mt-1 text-sm text-muted-foreground">{{ pegawais.total }} data</p>
                </div>
                <Button v-if="can.create" as-child>
                    <Link :href="route(`${prefix}.pegawai.create`)">
                        <Plus class="h-4 w-4" /> Tambah Pegawai
                    </Link>
                </Button>
            </div>

            <!-- Filter bar -->
            <div class="mb-6 flex flex-wrap items-end gap-3">
                <div class="space-y-1">
                    <Label>Search</Label>
                    <Input v-model="search" @keyup.enter="applyFilter" placeholder="Nama atau NIK..." class="w-48" />
                </div>
                <div class="space-y-1">
                    <Label>Status</Label>
                    <Select v-model="status_pegawai" @update:model-value="applyFilter">
                        <SelectTrigger class="w-36">
                            <SelectValue placeholder="Semua" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem :value="ALL">Semua</SelectItem>
                            <SelectItem value="aktif">Aktif</SelectItem>
                            <SelectItem value="nonaktif">Nonaktif</SelectItem>
                            <SelectItem value="pensiun">Pensiun</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-[12px] overflow-hidden"
                style="box-shadow: 0 1px 2px rgba(0,0,0,.04), 0 8px 24px rgba(0,0,0,.04);">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="w-12">No</TableHead>
                            <TableHead>Nama</TableHead>
                            <TableHead>NIK</TableHead>
                            <TableHead class="text-center">Status</TableHead>
                            <TableHead class="text-right">Aksi</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="(pegawai, index) in pegawais.data" :key="pegawai.id">
                            <TableCell class="text-muted-foreground">
                                {{ (pegawais.current_page - 1) * pegawais.per_page + index + 1 }}
                            </TableCell>
                            <TableCell>
                                <Link :href="route(`${prefix}.pegawai.show`, pegawai.id)" class="font-medium text-foreground hover:text-primary">
                                    {{ pegawai.nama_pegawai }}
                                </Link>
                            </TableCell>
                            <TableCell class="text-muted-foreground">{{ pegawai.nik }}</TableCell>
                            <TableCell class="text-center">
                                <Badge :variant="statusVariant(pegawai.status_pegawai)" class="capitalize">
                                    {{ pegawai.status_pegawai }}
                                </Badge>
                            </TableCell>
                            <TableCell class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <Button variant="ghost" size="icon-sm" as-child>
                                        <Link :href="route(`${prefix}.pegawai.show`, pegawai.id)">
                                            <Eye class="h-3.5 w-3.5" />
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
                                                <AlertDialogTitle>Hapus Pegawai</AlertDialogTitle>
                                                <AlertDialogDescription>
                                                    Yakin ingin menghapus "{{ pegawai.nama_pegawai }}"? Data yang dihapus tidak dapat dikembalikan.
                                                </AlertDialogDescription>
                                            </AlertDialogHeader>
                                            <AlertDialogFooter>
                                                <AlertDialogCancel>Batal</AlertDialogCancel>
                                                <AlertDialogAction @click="doDelete(pegawai.id)">Hapus</AlertDialogAction>
                                            </AlertDialogFooter>
                                        </AlertDialogContent>
                                    </AlertDialog>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="pegawais.data.length === 0">
                            <TableCell colspan="5" class="text-center py-12 text-muted-foreground">
                                Belum ada data pegawai.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Pagination -->
            <div v-if="pegawais.last_page > 1" class="mt-4 flex justify-center">
                <nav class="flex items-center gap-1">
                    <template v-for="link in pegawais.links" :key="link.url">
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
