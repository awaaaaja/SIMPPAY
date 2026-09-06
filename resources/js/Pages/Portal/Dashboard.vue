<script setup>
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { Head } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import { Chart, registerables } from 'chart.js';
import { Wallet, Briefcase, TrendingUp } from '@lucide/vue';

Chart.register(...registerables);

const props = defineProps({
    pegawai: Object,
    latestSlip: Object,
    payrollHistory: Array,
});

const chartRef = ref(null);
const showNominal = ref(true);

function formatRupiah(val) {
    if (val == null) return '-';
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
}

function formatPeriode(val) {
    if (!val) return '-';
    const d = new Date(val);
    return d.toLocaleDateString('id-ID', { month: 'short', year: 'numeric' });
}

function getGreeting() {
    const hour = new Date().getHours();
    if (hour < 11) return 'Selamat pagi';
    if (hour < 15) return 'Selamat siang';
    if (hour < 18) return 'Selamat sore';
    return 'Selamat malam';
}

onMounted(() => {
    if (!chartRef.value || !props.payrollHistory?.length) return;

    const labels = props.payrollHistory.map(h => formatPeriode(h.periode));
    const data = props.payrollHistory.map(h => Number(h.total_gaji));

    new Chart(chartRef.value, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Take Home Pay',
                data,
                borderColor: '#D40C14',
                backgroundColor: 'rgba(23, 107, 91, 0.08)',
                fill: true,
                tension: 0.3,
                pointBackgroundColor: '#D40C14',
                pointRadius: 4,
                pointHoverRadius: 6,
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
                        callback: (v) => 'Rp ' + (v / 1_000_000).toFixed(0) + 'M',
                    },
                    grid: { color: 'rgba(0,0,0,.04)' },
                },
                x: {
                    grid: { display: false },
                },
            },
        },
    });
});
</script>

<template>
    <Head title="Portal Dashboard" />

    <PortalLayout>
        <div class="px-4 py-8 sm:px-6 lg:px-8 max-w-5xl mx-auto">
            <!-- Greeting — §32 -->
            <div class="mb-8">
                <p class="text-sm text-gray-500">{{ getGreeting() }},</p>
                <h1 class="text-2xl font-semibold text-gray-800">{{ pegawai?.nama_pegawai }}</h1>
                <p class="text-sm text-gray-500 mt-0.5">{{ pegawai?.jabatan?.nama_jabatan || '-' }}</p>
            </div>

            <!-- Cards row — §33 -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                <!-- Latest Salary -->
                <div class="bg-white rounded-[16px] shadow-[0_1px_2px_rgba(0,0,0,.04),0_8px_24px_rgba(0,0,0,.04)] p-5">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-[10px] bg-[#D40C14]/10">
                            <Wallet class="h-4.5 w-4.5 text-[#D40C14]" :stroke-width="1.75" />
                        </div>
                        <p class="text-xs font-medium text-gray-500">Take Home Pay</p>
                    </div>
                    <p v-if="showNominal" class="text-xl font-semibold text-gray-800 tabular-nums">
                        {{ formatRupiah(latestSlip?.total_gaji) }}
                    </p>
                    <p v-else class="text-xl font-semibold text-gray-800">Rp ****</p>
                    <div class="flex items-center gap-2 mt-1">
                        <p class="text-xs text-gray-500">{{ formatPeriode(latestSlip?.periode) }}</p>
                        <span v-if="latestSlip" class="inline-flex items-center rounded-full bg-[#D40C14]/10 px-2 py-0.5 text-[10px] font-medium text-[#D40C14]">
                            Finalized
                        </span>
                    </div>
                    <button
                        @click="showNominal = !showNominal"
                        class="mt-3 text-xs text-[#D40C14] hover:underline"
                    >
                        {{ showNominal ? 'Sembunyikan nominal' : 'Tampilkan nominal' }}
                    </button>
                </div>

                <!-- Employment Info -->
                <div class="bg-white rounded-[16px] shadow-[0_1px_2px_rgba(0,0,0,.04),0_8px_24px_rgba(0,0,0,.04)] p-5">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-[10px] bg-[#D40C14]/10">
                            <Briefcase class="h-4.5 w-4.5 text-[#D40C14]" :stroke-width="1.75" />
                        </div>
                        <p class="text-xs font-medium text-gray-500">Informasi Kepegawaian</p>
                    </div>
                    <dl class="space-y-2">
                        <div class="flex justify-between">
                            <dt class="text-xs text-gray-500">Jabatan</dt>
                            <dd class="text-xs font-medium text-gray-800">{{ pegawai?.jabatan?.nama_jabatan || '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-xs text-gray-500">Status</dt>
                            <dd class="text-xs font-medium text-gray-800 capitalize">{{ pegawai?.status_pegawai || '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-xs text-gray-500">NIK</dt>
                            <dd class="text-xs font-medium text-gray-800">{{ pegawai?.nik || '-' }}</dd>
                        </div>
                        <div v-if="pegawai?.struktural?.nama_struktural" class="flex justify-between">
                            <dt class="text-xs text-gray-500">Struktural</dt>
                            <dd class="text-xs font-medium text-gray-800">{{ pegawai.struktural.nama_struktural }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Salary History Chart — §33, 6-month chart -->
            <div class="bg-white rounded-[16px] shadow-[0_1px_2px_rgba(0,0,0,.04),0_8px_24px_rgba(0,0,0,.04)] p-5">
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex h-9 w-9 items-center justify-center rounded-[10px] bg-[#D40C14]/10">
                        <TrendingUp class="h-4.5 w-4.5 text-[#D40C14]" :stroke-width="1.75" />
                    </div>
                    <p class="text-xs font-medium text-gray-500">Riwayat Gaji 6 Bulan Terakhir</p>
                </div>
                <div v-if="payrollHistory?.length" class="h-64">
                    <canvas ref="chartRef" />
                </div>
                <div v-else class="h-64 flex items-center justify-center">
                    <p class="text-sm text-gray-400">Belum ada data riwayat gaji</p>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>
