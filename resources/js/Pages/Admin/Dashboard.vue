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
        finalized: 'bg-[#176B5B]/10 text-[#176B5B]',
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
            datasets: [
                {
                    label: 'Total Penggajian',
                    data: props.payrollTrend.map((h) => Number(h.total)),
                    backgroundColor: '#176B5B',
                    borderRadius: 6,
                    barThickness: 32,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) =>
                            'Rp ' + ctx.parsed.y.toLocaleString('id-ID'),
                    },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: (v) =>
                            'Rp ' + (v / 1_000_000).toFixed(0) + 'jt',
                    },
                    grid: { color: 'rgba(0,0,0,.04)' },
                    border: { display: false },
                },
                x: {
                    grid: { display: false },
                    border: { display: false },
                },
            },
        },
    });
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <!-- Hero header — editorial, bukan "Welcome back" -->
        <div class="px-4 pt-6 pb-2 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <p class="text-sm text-gray-500">
                Periode aktif:
                <span class="font-medium text-gray-700">
                    {{ stats.periode_aktif ? formatPeriode(stats.periode_aktif) : '-' }}
                </span>
            </p>
            <h1 class="text-2xl font-semibold text-gray-800 mt-1">
                Ringkasan Penggajian
            </h1>
        </div>

        <!-- 4 metric cards -->
        <div class="px-4 py-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <div class="grid grid-cols-2 gap-3 lg:gap-4">
                <!-- Total Pegawai -->
                <div
                    class="bg-white rounded-[12px] p-4 sm:p-5"
                    style="
                        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04),
                            0 8px 24px rgba(0, 0, 0, 0.04);
                    "
                >
                    <div class="flex items-center gap-3 mb-3">
                        <div
                            class="flex h-9 w-9 items-center justify-center rounded-[10px] bg-[#176B5B]/10"
                        >
                            <Users
                                class="h-[18px] w-[18px] text-[#176B5B]"
                                :stroke-width="1.75"
                            />
                        </div>
                        <p class="text-xs font-medium text-gray-500">
                            Total Pegawai
                        </p>
                    </div>
                    <p
                        class="text-2xl font-semibold text-gray-800 tabular-nums"
                    >
                        {{ stats.total_pegawai }}
                    </p>
                    <p class="text-xs text-gray-400 mt-1">Status aktif</p>
                </div>

                <!-- Total Jabatan -->
                <div
                    class="bg-white rounded-[12px] p-4 sm:p-5"
                    style="
                        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04),
                            0 8px 24px rgba(0, 0, 0, 0.04);
                    "
                >
                    <div class="flex items-center gap-3 mb-3">
                        <div
                            class="flex h-9 w-9 items-center justify-center rounded-[10px] bg-[#176B5B]/10"
                        >
                            <Briefcase
                                class="h-[18px] w-[18px] text-[#176B5B]"
                                :stroke-width="1.75"
                            />
                        </div>
                        <p class="text-xs font-medium text-gray-500">
                            Total Jabatan
                        </p>
                    </div>
                    <p
                        class="text-2xl font-semibold text-gray-800 tabular-nums"
                    >
                        {{ stats.total_jabatan }}
                    </p>
                    <p class="text-xs text-gray-400 mt-1">Jabatan aktif</p>
                </div>

                <!-- Total Gaji Bulan Ini -->
                <div
                    class="bg-white rounded-[12px] p-4 sm:p-5"
                    style="
                        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04),
                            0 8px 24px rgba(0, 0, 0, 0.04);
                    "
                >
                    <div class="flex items-center gap-3 mb-3">
                        <div
                            class="flex h-9 w-9 items-center justify-center rounded-[10px] bg-[#176B5B]/10"
                        >
                            <Wallet
                                class="h-[18px] w-[18px] text-[#176B5B]"
                                :stroke-width="1.75"
                            />
                        </div>
                        <p class="text-xs font-medium text-gray-500">
                            Total Penggajian
                        </p>
                    </div>
                    <p
                        class="text-lg sm:text-2xl font-semibold text-gray-800 tabular-nums"
                    >
                        {{
                            formatRupiah(
                                stats.payroll_summary?.total_gaji
                            )
                        }}
                    </p>
                    <p class="text-xs text-gray-400 mt-1">
                        {{
                            stats.payroll_summary
                                ? formatPeriode(stats.payroll_summary.periode)
                                : 'Belum ada data'
                        }}
                    </p>
                </div>

                <!-- Status Payroll -->
                <div
                    class="bg-white rounded-[12px] p-4 sm:p-5"
                    style="
                        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04),
                            0 8px 24px rgba(0, 0, 0, 0.04);
                    "
                >
                    <div class="flex items-center gap-3 mb-3">
                        <div
                            class="flex h-9 w-9 items-center justify-center rounded-[10px] bg-[#176B5B]/10"
                        >
                            <Calendar
                                class="h-[18px] w-[18px] text-[#176B5B]"
                                :stroke-width="1.75"
                            />
                        </div>
                        <p class="text-xs font-medium text-gray-500">
                            Status Payroll
                        </p>
                    </div>
                    <div v-if="stats.payroll_summary">
                        <span
                            :class="[
                                'inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium capitalize',
                                statusColor(stats.payroll_summary.status),
                            ]"
                        >
                            <component
                                :is="
                                    statusIcon(stats.payroll_summary.status)
                                "
                                class="h-3 w-3"
                                :stroke-width="2"
                            />
                            {{ stats.payroll_summary.status }}
                        </span>
                        <p class="text-xs text-gray-400 mt-2">
                            {{ stats.payroll_summary.total_pegawai }} pegawai
                            terproses
                        </p>
                    </div>
                    <p v-else class="text-sm text-gray-400">
                        Belum ada payroll
                    </p>
                </div>
            </div>
        </div>

        <!-- Payroll trend chart + Recent runs -->
        <div
            class="px-4 pb-8 sm:px-6 lg:px-8 max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-4"
        >
            <!-- Trend Chart -->
            <div
                class="lg:col-span-2 bg-white rounded-[12px] p-5"
                style="
                    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04),
                        0 8px 24px rgba(0, 0, 0, 0.04);
                "
            >
                <p class="text-xs font-medium text-gray-500 mb-4">
                    Tren Total Penggajian 6 Bulan Terakhir
                </p>
                <div v-if="payrollTrend?.length" class="h-56">
                    <canvas ref="trendChartRef" />
                </div>
                <div
                    v-else
                    class="h-56 flex items-center justify-center"
                >
                    <p class="text-sm text-gray-400">
                        Belum ada data riwayat payroll
                    </p>
                </div>
            </div>

            <!-- Recent Payroll Runs -->
            <div
                class="bg-white rounded-[12px] p-5"
                style="
                    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04),
                        0 8px 24px rgba(0, 0, 0, 0.04);
                "
            >
                <p class="text-xs font-medium text-gray-500 mb-4">
                    Riwayat Payroll
                </p>
                <div v-if="recentRuns?.length" class="space-y-3">
                    <div
                        v-for="run in recentRuns"
                        :key="run.id"
                        class="flex items-center justify-between"
                    >
                        <div>
                            <p class="text-sm font-medium text-gray-800">
                                {{ formatPeriode(run.periode) }}
                            </p>
                            <p class="text-xs text-gray-400">
                                {{ run.calculated_by || '-' }}
                            </p>
                        </div>
                        <span
                            :class="[
                                'inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium capitalize',
                                statusColor(run.status),
                            ]"
                        >
                            {{ run.status }}
                        </span>
                    </div>
                </div>
                <div
                    v-else
                    class="h-32 flex items-center justify-center"
                >
                    <p class="text-sm text-gray-400">Belum ada riwayat</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
