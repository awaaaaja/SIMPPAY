<script setup>
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Clock, CheckCircle, XCircle, AlertTriangle } from '@lucide/vue';

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

        <div class="px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-6">
                <h1 class="text-xl font-semibold text-gray-900">Riwayat Absensi</h1>
                <p class="mt-1 text-sm text-gray-500">Rekam kehadiran bulanan Anda</p>
            </div>

            <!-- Filter -->
            <div class="mb-4">
                <input
                    type="month"
                    v-model="bulanFilter"
                    class="rounded-[10px] border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-[#176B5B] focus:ring-1 focus:ring-[#176B5B] focus:outline-none"
                />
            </div>

            <!-- Summary Cards -->
            <div class="mb-6 grid grid-cols-3 gap-3">
                <div class="rounded-[12px] bg-white p-4 shadow-sm">
                    <div class="flex items-center gap-2">
                        <CheckCircle class="h-4 w-4 text-[#176B5B]" :stroke-width="1.75" />
                        <span class="text-xs text-gray-500">Hadir</span>
                    </div>
                    <p class="mt-1 text-lg font-semibold text-gray-900" style="font-variant-numeric: tabular-nums">
                        {{ filteredRiwayat.reduce((s, r) => s + (r.hadir || 0), 0) }}
                    </p>
                </div>
                <div class="rounded-[12px] bg-white p-4 shadow-sm">
                    <div class="flex items-center gap-2">
                        <AlertTriangle class="h-4 w-4 text-amber-500" :stroke-width="1.75" />
                        <span class="text-xs text-gray-500">Sakit</span>
                    </div>
                    <p class="mt-1 text-lg font-semibold text-gray-900" style="font-variant-numeric: tabular-nums">
                        {{ filteredRiwayat.reduce((s, r) => s + (r.sakit || 0), 0) }}
                    </p>
                </div>
                <div class="rounded-[12px] bg-white p-4 shadow-sm">
                    <div class="flex items-center gap-2">
                        <XCircle class="h-4 w-4 text-red-500" :stroke-width="1.75" />
                        <span class="text-xs text-gray-500">Alpha</span>
                    </div>
                    <p class="mt-1 text-lg font-semibold text-gray-900" style="font-variant-numeric: tabular-nums">
                        {{ filteredRiwayat.reduce((s, r) => s + (r.alpha || 0), 0) }}
                    </p>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-hidden rounded-[16px] bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Periode</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Hadir</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Sakit</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Alpha</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Hari</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-if="filteredRiwayat.length === 0">
                                <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-400">
                                    Belum ada data kehadiran
                                </td>
                            </tr>
                            <tr v-for="row in filteredRiwayat" :key="row.id" class="hover:bg-[#F8FAF8] transition-colors">
                                <td class="px-4 py-3 text-sm text-gray-700">{{ formatBulan(row.periode) }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 text-right" style="font-variant-numeric: tabular-nums">{{ row.hadir }}</td>
                                <td class="px-4 py-3 text-sm text-amber-600 text-right" style="font-variant-numeric: tabular-nums">{{ row.sakit }}</td>
                                <td class="px-4 py-3 text-sm text-red-600 text-right" style="font-variant-numeric: tabular-nums">{{ row.alpha }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 text-right font-medium" style="font-variant-numeric: tabular-nums">{{ totalHari(row) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>
