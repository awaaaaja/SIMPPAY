<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useRoutePrefix } from '@/composables/useRoutePrefix.js';

const prefix = useRoutePrefix();

const props = defineProps({
    run: Object,
    can: Object,
});

const selectedDetail = ref(null);

function formatPeriode(val) {
    if (!val) return '-';
    const d = new Date(val);
    return d.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
}

function formatRupiah(val) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
}

function statusBadge(status) {
    const map = {
        draft: 'bg-gray-100 text-gray-700',
        calculated: 'bg-blue-100 text-blue-700',
        finalized: 'bg-[#176B5B]/10 text-[#176B5B]',
        void: 'bg-red-100 text-red-700',
    };
    return map[status] || 'bg-gray-100 text-gray-700';
}

function finalize() {
    if (confirm('Yakin ingin memfinalisasi payroll run ini? Setelah difinalisasi, tidak bisa dihitung ulang.')) {
        router.post(route(`${prefix.value}.payroll-run.finalize`, props.run.id));
    }
}

function openVoidModal() {
    voidReason.value = '';
    showVoidModal.value = true;
}

const showVoidModal = ref(false);
const voidReason = ref('');
const voidForm = useForm({ void_reason: '' });

function submitVoid() {
    voidForm.void_reason = voidReason.value;
    voidForm.post(route(`${prefix.value}.payroll-run.void`, props.run.id), {
        onSuccess: () => {
            showVoidModal.value = false;
        },
    });
}

function openDrawer(detail) {
    selectedDetail.value = detail;
}

function closeDrawer() {
    selectedDetail.value = null;
}

const grandTotal = props.run.details?.reduce((sum, d) => sum + parseFloat(d.total_gaji), 0) || 0;
</script>

<template>
    <Head title="Detail Payroll Run" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <button @click="router.get(route(`${prefix}.payroll-run.index`))"
                        class="text-sm text-gray-500 hover:text-[#176B5B] mb-1 transition-colors">
                        Kembali
                    </button>
                    <h2 class="font-semibold text-lg text-gray-800 leading-tight">
                        Payroll {{ formatPeriode(run.periode) }}
                    </h2>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize"
                        :class="statusBadge(run.status)">
                        {{ run.status }}
                    </span>
                    <a :href="route(`${prefix}.laporan-gaji.pdf`, { periode: run.periode?.substring(0, 7) })"
                        target="_blank"
                        class="inline-flex items-center px-3 py-2 bg-white border border-gray-200 text-gray-700 text-sm font-medium rounded-[10px] hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4 mr-1.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                        Cetak PDF
                    </a>
                    <a :href="route(`${prefix}.laporan-gaji.export`, { periode: run.periode?.substring(0, 7) })"
                        class="inline-flex items-center px-3 py-2 bg-white border border-gray-200 text-gray-700 text-sm font-medium rounded-[10px] hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4 mr-1.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Export Excel
                    </a>
                    <button v-if="can.update && run.status === 'calculated'" @click="finalize"
                        class="inline-flex items-center px-4 py-2 bg-[#176B5B] text-white text-sm font-medium rounded-[10px] hover:bg-[#145a4c] transition-colors">
                        Finalize
                    </button>
                    <button v-if="can.update && run.status === 'finalized'" @click="openVoidModal"
                        class="inline-flex items-center px-4 py-2 bg-red-500 text-white text-sm font-medium rounded-[10px] hover:bg-red-600 transition-colors">
                        Void
                    </button>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Summary -->
                <div class="grid grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-[16px] shadow-[0_1px_2px_rgba(0,0,0,.04),0_8px_24px_rgba(0,0,0,.04)] p-4">
                        <p class="text-xs text-gray-500 mb-1">Total Pegawai</p>
                        <p class="text-2xl font-semibold text-gray-800">{{ run.details?.length || 0 }}</p>
                    </div>
                    <div class="bg-white rounded-[16px] shadow-[0_1px_2px_rgba(0,0,0,.04),0_8px_24px_rgba(0,0,0,.04)] p-4">
                        <p class="text-xs text-gray-500 mb-1">Total Gaji</p>
                        <p class="text-2xl font-semibold text-gray-800">{{ formatRupiah(grandTotal) }}</p>
                    </div>
                    <div class="bg-white rounded-[16px] shadow-[0_1px_2px_rgba(0,0,0,.04),0_8px_24px_rgba(0,0,0,.04)] p-4">
                        <p class="text-xs text-gray-500 mb-1">Dihitung Oleh</p>
                        <p class="text-sm font-medium text-gray-800">{{ run.calculated_by?.name || '-' }}</p>
                    </div>
                    <div class="bg-white rounded-[16px] shadow-[0_1px_2px_rgba(0,0,0,.04),0_8px_24px_rgba(0,0,0,.04)] p-4">
                        <p class="text-xs text-gray-500 mb-1">Difinalisasi Oleh</p>
                        <p class="text-sm font-medium text-gray-800">{{ run.finalized_by?.name || '-' }}</p>
                    </div>
                </div>

                <!-- Void reason -->
                <div v-if="run.status === 'void' && run.void_reason"
                    class="mb-6 bg-red-50 border border-red-200 rounded-[16px] p-4">
                    <p class="text-sm font-medium text-red-800">Alasan Void:</p>
                    <p class="text-sm text-red-700">{{ run.void_reason }}</p>
                </div>

                <!-- Table -->
                <div class="bg-white rounded-[16px] shadow-[0_1px_2px_rgba(0,0,0,.04),0_8px_24px_rgba(0,0,0,.04)] overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pegawai</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jabatan</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Gaji Pokok</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Tj. Transport</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Uang Makan</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Pot. Alpha</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Tunjangan</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Potongan</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Gaji</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Detail</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Slip</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="detail in run.details" :key="detail.id"
                                class="hover:bg-[#F8FAF8] transition-colors cursor-pointer"
                                @click="openDrawer(detail)">
                                <td class="px-6 py-4 text-sm text-gray-800">{{ detail.pegawai?.nama_pegawai }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ detail.pegawai?.jabatan?.nama_jabatan }}</td>
                                <td class="px-6 py-4 text-sm text-gray-800 text-right">{{ formatRupiah(detail.gaji_pokok) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-800 text-right">{{ formatRupiah(detail.tj_transport) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-800 text-right">{{ formatRupiah(detail.uang_makan) }}</td>
                                <td class="px-6 py-4 text-sm text-red-600 text-right">{{ formatRupiah(detail.potongan_alpha) }}</td>
                                <td class="px-6 py-4 text-sm text-[#176B5B] text-right">+{{ formatRupiah(detail.total_tunjangan_tambahan) }}</td>
                                <td class="px-6 py-4 text-sm text-red-600 text-right">-{{ formatRupiah(detail.total_potongan_tambahan) }}</td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-800 text-right">{{ formatRupiah(detail.total_gaji) }}</td>
                                <td class="px-6 py-4 text-center">
                                    <svg class="w-4 h-4 text-gray-400 mx-auto" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <a :href="route(`${prefix}.slip-gaji.pdf`, detail.id)" target="_blank" @click.stop
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium text-[#176B5B] bg-[#176B5B]/10 rounded-[6px] hover:bg-[#176B5B]/20 transition-colors">
                                        Cetak
                                    </a>
                                </td>
                            </tr>
                            <tr v-if="!run.details || run.details.length === 0">
                                <td colspan="11" class="px-6 py-8 text-center text-sm text-gray-400">
                                    Belum ada data detail.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- PayrollDetailDrawer -->
        <div v-if="selectedDetail" class="fixed inset-0 z-50 overflow-hidden" @click.self="closeDrawer">
            <div class="absolute inset-0 bg-black/30" @click="closeDrawer" />
            <div class="absolute right-0 top-0 h-full w-full max-w-md bg-white shadow-xl transform transition-transform">
                <div class="flex flex-col h-full">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                        <h3 class="font-semibold text-gray-800">Breakdown Gaji</h3>
                        <button @click="closeDrawer" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </div>
                    <div class="flex-1 overflow-y-auto px-6 py-4 space-y-6">
                        <!-- Pegawai info -->
                        <div>
                            <p class="text-sm font-medium text-gray-800">{{ selectedDetail.pegawai?.nama_pegawai }}</p>
                            <p class="text-xs text-gray-500">{{ selectedDetail.pegawai?.nik }} - {{ selectedDetail.pegawai?.jabatan?.nama_jabatan }}</p>
                        </div>

                        <!-- Pendapatan -->
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Pendapatan</p>
                            <div class="space-y-1">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Gaji Pokok</span>
                                    <span class="text-gray-800">{{ formatRupiah(selectedDetail.gaji_pokok) }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Tj. Transport</span>
                                    <span class="text-gray-800">{{ formatRupiah(selectedDetail.tj_transport) }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Uang Makan</span>
                                    <span class="text-gray-800">{{ formatRupiah(selectedDetail.uang_makan) }}</span>
                                </div>
                                <div v-if="selectedDetail.breakdown_json?.tunjangan?.length" class="pt-1 border-t border-gray-50">
                                    <div v-for="(t, i) in selectedDetail.breakdown_json.tunjangan" :key="i" class="flex justify-between text-sm">
                                        <span class="text-gray-600">+ {{ t.nama }}</span>
                                        <span class="text-[#176B5B]">{{ formatRupiah(t.nominal) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Potongan -->
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Potongan</p>
                            <div class="space-y-1">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Potongan Alpha</span>
                                    <span class="text-red-600">{{ formatRupiah(selectedDetail.potongan_alpha) }}</span>
                                </div>
                                <div v-if="selectedDetail.breakdown_json?.potongan?.length" class="pt-1 border-t border-gray-50">
                                    <div v-for="(p, i) in selectedDetail.breakdown_json.potongan" :key="i" class="flex justify-between text-sm">
                                        <span class="text-gray-600">- {{ p.nama }}</span>
                                        <span class="text-red-600">{{ formatRupiah(p.nilai) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total -->
                        <div class="pt-4 border-t border-gray-200">
                            <div class="flex justify-between">
                                <span class="text-sm font-semibold text-gray-800">Total Gaji</span>
                                <span class="text-lg font-bold text-gray-800">{{ formatRupiah(selectedDetail.total_gaji) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Void Modal -->
        <div v-if="showVoidModal" class="fixed inset-0 z-50 overflow-y-auto" @click.self="showVoidModal = false">
            <div class="fixed inset-0 bg-black/30" @click="showVoidModal = false" />
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="bg-white rounded-[18px] shadow-xl w-full max-w-md p-6 relative">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Batalkan Payroll Run</h3>
                    <form @submit.prevent="submitVoid" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Alasan Void <span class="text-red-500">*</span></label>
                            <textarea v-model="voidReason" rows="3" required
                                class="w-full rounded-[10px] border-gray-300 focus:border-[#176B5B] focus:ring-[#176B5B] text-sm"
                                placeholder="Jelaskan alasan pembatalan..." />
                            <p v-if="voidForm.errors.void_reason" class="text-red-500 text-xs mt-1">{{ voidForm.errors.void_reason }}</p>
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" @click="showVoidModal = false"
                                class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 rounded-[10px] hover:bg-gray-100 transition-colors">
                                Batal
                            </button>
                            <button type="submit" :disabled="voidForm.processing"
                                class="px-4 py-2 text-sm font-medium text-white bg-red-500 rounded-[10px] hover:bg-red-600 transition-colors disabled:opacity-50">
                                {{ voidForm.processing ? 'Membatalkan...' : 'Batalkan' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
