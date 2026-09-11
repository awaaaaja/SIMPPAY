<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { Chart, registerables } from 'chart.js';
import {
    Users,
    Briefcase,
    Wallet,
    Calendar,
    Clock,
    CheckCircle,
    XCircle,
    AlertCircle,
} from '@lucide/vue';

Chart.register(...registerables);

const props = defineProps({
    stats: Object,
    recentRuns: Array,
    payrollTrend: Array,
});

const trendChartRef = ref(null);

function formatRupiah(val) {
    if (val == null) return '-';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(val);
}

function formatPeriode(val) {
    if (!val) return '-';
    const [y, m] = val.split('-');
    const d = new Date(Number(y), Number(m) - 1);
    return d.toLocaleDateString('id-ID', { month: 'short', year: 'numeric' });
}

function statusColor(status) {
    const map = {
        draft: 'bg-gray-100 text-gray-600',
        calculated: 'bg-amber-50 text-amber-700',
        finalized: 'bg-blue-50 text-blue-700',
        void: 'bg-red-50 text-red-600',
    };
    return map[status] || 'bg-gray-100 text-gray-600';
}

function statusIcon(status) {
    const map = {
        draft: AlertCircle,
        calculated: Clock,
        finalized: CheckCircle,
        void: XCircle,
    };
    return map[status] || AlertCircle;
}

onMounted(() => {
    if (!trendChartRef.value || !props.payrollTrend?.length) return;

    new Chart(trendChartRef.value, {
        type: 'bar',
        data: {
            labels: props.payrollTrend.map((h) => formatPeriode(h.periode)),
            datasets: [{
                label: 'Total Penggajian',
                data: props.payrollTrend.map((h) => Number(h.total)),
                backgroundColor: '#1468C4',
                borderRadius: 4,
                barThickness: 28,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => 'Rp ' + ctx.parsed.y.toLocaleString('id-ID'),
                    },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: (v) => 'Rp ' + (v / 1_000_000).toFixed(0) + 'jt',
                        font: { size: 11 },
                    },
                    grid: { color: 'rgba(0,0,0,.04)' },
                    border: { display: false },
                },
                x: {
                    grid: { display: false },
                    border: { display: false },
                    ticks: { font: { size: 11 } },
                },
            },
        },
    });
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="px-4 pt-5 pb-2 sm:px-6 lg:px-8 max-w-[1400px] mx-auto">
            <!-- Page header -->
            <div class="mb-4">
                <h1 class="text-lg font-semibold" style="color: var(--simppay-text)">Ringkasan Penggajian</h1>
                <p class="text-sm mt-0.5" style="color: var(--simppay-text-secondary)">
                    Periode aktif:
                    <span class="font-medium" style="color: var(--simppay-text)">
                        {{ stats.periode_aktif ? formatPeriode(stats.periode_aktif) : '-' }}
                    </span>
                </p>
            </div>

            <!-- Stat cards — compact 4-col -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
                <div class="bg-white rounded-xl p-4 border" style="border-color: var(--simppay-border)">
                    <div class="flex items-center gap-2.5 mb-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/8">
                            <Users class="h-4 w-4 text-primary" :stroke-width="1.75" />
                        </div>
                        <p class="text-xs font-medium" style="color: var(--simppay-text-secondary)">Total Pegawai</p>
                    </div>
                    <p class="text-xl font-semibold tabular-nums" style="color: var(--simppay-text)">{{ stats.total_pegawai }}</p>
                </div>

                <div class="bg-white rounded-xl p-4 border" style="border-color: var(--simppay-border)">
                    <div class="flex items-center gap-2.5 mb-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/8">
                            <Briefcase class="h-4 w-4 text-primary" :stroke-width="1.75" />
                        </div>
                        <p class="text-xs font-medium" style="color: var(--simppay-text-secondary)">Total Jabatan</p>
                    </div>
                    <p class="text-xl font-semibold tabular-nums" style="color: var(--simppay-text)">{{ stats.total_jabatan }}</p>
                </div>

                <div class="bg-white rounded-xl p-4 border" style="border-color: var(--simppay-border)">
                    <div class="flex items-center gap-2.5 mb-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/8">
                            <Wallet class="h-4 w-4 text-primary" :stroke-width="1.75" />
                        </div>
                        <p class="text-xs font-medium" style="color: var(--simppay-text-secondary)">Total Penggajian</p>
                    </div>
                    <p class="text-base sm:text-xl font-semibold tabular-nums" style="color: var(--simppay-text)">
                        {{ formatRupiah(stats.payroll_summary?.total_gaji) }}
                    </p>
                    <p class="text-xs mt-0.5" style="color: var(--simppay-text-secondary)">
                        {{ stats.payroll_summary ? formatPeriode(stats.payroll_summary.periode) : 'Belum ada data' }}
                    </p>
                </div>

                <div class="bg-white rounded-xl p-4 border" style="border-color: var(--simppay-border)">
                    <div class="flex items-center gap-2.5 mb-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/8">
                            <Calendar class="h-4 w-4 text-primary" :stroke-width="1.75" />
                        </div>
                        <p class="text-xs font-medium" style="color: var(--simppay-text-secondary)">Status Payroll</p>
                    </div>
                    <div v-if="stats.payroll_summary">
                        <span :class="['inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium capitalize', statusColor(stats.payroll_summary.status)]">
                            <component :is="statusIcon(stats.payroll_summary.status)" class="h-3 w-3" :stroke-width="2" />
                            {{ stats.payroll_summary.status }}
                        </span>
                        <p class="text-xs mt-1" style="color: var(--simppay-text-secondary)">
                            {{ stats.payroll_summary.total_pegawai }} pegawai
                        </p>
                    </div>
                    <p v-else class="text-sm" style="color: var(--simppay-text-secondary)">Belum ada payroll</p>
                </div>
            </div>

            <!-- Chart + History -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 mb-6">
                <!-- Trend Chart -->
                <div class="lg:col-span-2 bg-white rounded-xl p-4 border" style="border-color: var(--simppay-border)">
                    <p class="text-xs font-medium mb-3" style="color: var(--simppay-text-secondary)">
                        Tren Total Penggajian 6 Bulan Terakhir
                    </p>
                    <div v-if="payrollTrend?.length" class="h-48">
                        <canvas ref="trendChartRef" />
                    </div>
                    <div v-else class="h-48 flex items-center justify-center">
                        <p class="text-sm" style="color: var(--simppay-text-secondary)">Belum ada data riwayat payroll</p>
                    </div>
                </div>

                <!-- Recent Payroll Runs -->
                <div class="bg-white rounded-xl p-4 border" style="border-color: var(--simppay-border)">
                    <p class="text-xs font-medium mb-3" style="color: var(--simppay-text-secondary)">Riwayat Payroll</p>
                    <div v-if="recentRuns?.length" class="space-y-2.5">
                        <div v-for="run in recentRuns" :key="run.id" class="flex items-center justify-between py-1.5 border-b last:border-0" style="border-color: var(--simppay-border)">
                            <div>
                                <p class="text-sm font-medium" style="color: var(--simppay-text)">{{ formatPeriode(run.periode) }}</p>
                                <p class="text-xs" style="color: var(--simppay-text-secondary)">{{ run.calculated_by || '-' }}</p>
                            </div>
                            <span :class="['inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium capitalize', statusColor(run.status)]">
                                {{ run.status }}
                            </span>
                        </div>
                    </div>
                    <div v-else class="h-24 flex items-center justify-center">
                        <p class="text-sm" style="color: var(--simppay-text-secondary)">Belum ada riwayat</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
