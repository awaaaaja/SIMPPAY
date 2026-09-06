<script setup>
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { CheckCircle, XCircle, AlertTriangle } from '@lucide/vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Input } from '@/components/ui/input';

const props = defineProps({
    pegawai: Object,
});

const riwayat = computed(() => props.pegawai?.kehadiran || []);
const bulanFilter = ref('');

const filteredRiwayat = computed(() => {
    let data = riwayat.value;
    if (bulanFilter.value) {
        data = data.filter(r => r.periode?.startsWith(bulanFilter.value));
    }
    return data.sort((a, b) => (b.periode || '').localeCompare(a.periode || ''));
});

function formatBulan(val) {
    if (!val) return '-';
    const d = new Date(val);
    return d.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
}

function totalHari(row) {
    return (row.hadir || 0) + (row.sakit || 0) + (row.alpha || 0);
}
</script>

<template>
    <PortalLayout>
        <Head title="Riwayat Absensi" />

        <div class="px-4 py-6 sm:px-6 lg:px-8 max-w-4xl mx-auto">
            <div class="mb-6">
                <h1 class="text-xl font-semibold text-foreground">Riwayat Absensi</h1>
                <p class="mt-1 text-sm text-muted-foreground">Rekam kehadiran bulanan Anda</p>
            </div>

            <!-- Filter -->
            <div class="mb-4">
                <Input v-model="bulanFilter" type="month" class="max-w-xs" />
            </div>

            <!-- Summary Cards -->
            <div class="mb-6 grid grid-cols-3 gap-3">
                <div class="rounded-[12px] bg-white p-4"
                    style="box-shadow: 0 1px 2px rgba(0,0,0,.04), 0 8px 24px rgba(0,0,0,.04);">
                    <div class="flex items-center gap-2">
                        <CheckCircle class="h-4 w-4 text-primary" :stroke-width="1.75" />
                        <span class="text-xs text-muted-foreground">Hadir</span>
                    </div>
                    <p class="mt-1 text-lg font-semibold text-foreground tabular-nums">
                        {{ filteredRiwayat.reduce((s, r) => s + (r.hadir || 0), 0) }}
                    </p>
                </div>
                <div class="rounded-[12px] bg-white p-4"
                    style="box-shadow: 0 1px 2px rgba(0,0,0,.04), 0 8px 24px rgba(0,0,0,.04);">
                    <div class="flex items-center gap-2">
                        <AlertTriangle class="h-4 w-4 text-amber-500" :stroke-width="1.75" />
                        <span class="text-xs text-muted-foreground">Sakit</span>
                    </div>
                    <p class="mt-1 text-lg font-semibold text-foreground tabular-nums">
                        {{ filteredRiwayat.reduce((s, r) => s + (r.sakit || 0), 0) }}
                    </p>
                </div>
                <div class="rounded-[12px] bg-white p-4"
                    style="box-shadow: 0 1px 2px rgba(0,0,0,.04), 0 8px 24px rgba(0,0,0,.04);">
                    <div class="flex items-center gap-2">
                        <XCircle class="h-4 w-4 text-red-500" :stroke-width="1.75" />
                        <span class="text-xs text-muted-foreground">Alpha</span>
                    </div>
                    <p class="mt-1 text-lg font-semibold text-foreground tabular-nums">
                        {{ filteredRiwayat.reduce((s, r) => s + (r.alpha || 0), 0) }}
                    </p>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-[16px] overflow-hidden"
                style="box-shadow: 0 1px 2px rgba(0,0,0,.04), 0 8px 24px rgba(0,0,0,.04);">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Periode</TableHead>
                            <TableHead class="text-right">Hadir</TableHead>
                            <TableHead class="text-right">Sakit</TableHead>
                            <TableHead class="text-right">Alpha</TableHead>
                            <TableHead class="text-right">Total Hari</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-if="filteredRiwayat.length === 0">
                            <TableCell colspan="5" class="text-center py-8 text-muted-foreground">
                                Belum ada data kehadiran
                            </TableCell>
                        </TableRow>
                        <TableRow v-for="row in filteredRiwayat" :key="row.id">
                            <TableCell>{{ formatBulan(row.periode) }}</TableCell>
                            <TableCell class="text-right tabular-nums">{{ row.hadir }}</TableCell>
                            <TableCell class="text-right tabular-nums text-amber-600">{{ row.sakit }}</TableCell>
                            <TableCell class="text-right tabular-nums text-red-600">{{ row.alpha }}</TableCell>
                            <TableCell class="text-right tabular-nums font-medium">{{ totalHari(row) }}</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </PortalLayout>
</template>
