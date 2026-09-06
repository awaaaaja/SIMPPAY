<script setup>
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { FileText, Download, Eye, EyeOff } from '@lucide/vue';

const props = defineProps({
    slips: Array,
    periodes: Array,
    pegawai: Object,
    selectedPeriodeId: [String, Number, null],
});

const showNominal = ref(true);

const selectedPeriode = ref(props.selectedPeriodeId || '');

function onPeriodeChange() {
    router.get(route('portal.slip.gaji'), { periode_id: selectedPeriode.value || undefined }, {
        preserveState: true,
        replace: true,
    });
}

function formatPeriode(val) {
    if (!val) return '-';
    const d = new Date(val);
    return d.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
}

function formatShortPeriode(val) {
    if (!val) return '-';
    const d = new Date(val);
    return d.toLocaleDateString('id-ID', { month: 'short', year: 'numeric' });
}

function formatRupiah(val) {
    if (val == null) return '-';
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
}

const selectedSlip = ref(null);

function toggleDetail(slip) {
    if (selectedSlip.value?.id === slip.id) {
        selectedSlip.value = null;
    } else {
        selectedSlip.value = slip;
    }
}
</script>

<template>
    <Head title="Slip Gaji" />

    <PortalLayout>
        <div class="px-4 py-8 sm:px-6 lg:px-8 max-w-4xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-xl font-semibold text-gray-800">Slip Gaji</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Lihat detail gaji per periode</p>
                </div>
                <button
                    @click="showNominal = !showNominal"
                    class="inline-flex items-center gap-1.5 rounded-[10px] border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 transition-colors"
                >
                    <EyeOff v-if="showNominal" class="h-3.5 w-3.5" :stroke-width="1.75" />
                    <Eye v-else class="h-3.5 w-3.5" :stroke-width="1.75" />
                    {{ showNominal ? 'Sembunyikan' : 'Tampilkan' }} Nominal
                </button>
            </div>

            <!-- Periode Dropdown — only finalized -->
            <div class="mb-6">
                <select
                    v-model="selectedPeriode"
                    @change="onPeriodeChange"
                    class="block w-full max-w-xs rounded-[10px] border border-gray-200 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-[#025AB1] focus:ring-1 focus:ring-[#025AB1] focus:outline-none"
                >
                    <option value="">Semua periode finalized</option>
                    <option v-for="p in periodes" :key="p.id" :value="p.id">
                        {{ formatPeriode(p.periode) }}
                    </option>
                </select>
            </div>

            <!-- Empty state -->
            <div v-if="!slips?.length" class="bg-white rounded-[16px] shadow-[0_1px_2px_rgba(0,0,0,.04),0_8px_24px_rgba(0,0,0,.04)] p-8 text-center">
                <FileText class="h-10 w-10 text-gray-300 mx-auto mb-3" :stroke-width="1.5" />
                <p class="text-sm font-medium text-gray-600">Belum ada slip gaji</p>
                <p class="text-xs text-gray-400 mt-1">Slip gaji akan muncul setelah payroll finalisasi</p>
            </div>

            <!-- Slip list -->
            <div v-else class="space-y-3">
                <div
                    v-for="slip in slips"
                    :key="slip.id"
                    class="bg-white rounded-[16px] shadow-[0_1px_2px_rgba(0,0,0,.04),0_8px_24px_rgba(0,0,0,.04)] overflow-hidden"
                >
                    <!-- Card header — §34 -->
                    <button
                        @click="toggleDetail(slip)"
                        class="w-full p-5 flex items-center justify-between text-left hover:bg-[#F8FAF8] transition-colors"
                    >
                        <div>
                            <p class="text-sm font-semibold text-gray-800">{{ formatPeriode(slip.payroll_run?.periode) }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ pegawai?.jabatan?.nama_jabatan || '-' }}</p>
                        </div>
                        <div class="flex items-center gap-4">
                            <p v-if="showNominal" class="text-sm font-semibold text-gray-800 tabular-nums">{{ formatRupiah(slip.total_gaji) }}</p>
                            <p v-else class="text-sm font-semibold text-gray-800">Rp ****</p>
                            <a
                                :href="route('portal.slip-gaji.pdf', slip.id)"
                                target="_blank"
                                @click.stop
                                class="inline-flex items-center px-3 py-1.5 bg-[#025AB1] text-white text-xs font-medium rounded-[8px] hover:bg-[#014A96] transition-colors"
                            >
                                <Download class="h-3.5 w-3.5" :stroke-width="1.75" />
                            </a>
                        </div>
                    </button>

                    <!-- Breakdown — §34: progressive disclosure -->
                    <Transition
                        enter-active-class="transition-all duration-200"
                        enter-from-class="opacity-0 max-h-0"
                        enter-to-class="opacity-100 max-h-96"
                        leave-active-class="transition-all duration-150"
                        leave-from-class="opacity-100 max-h-96"
                        leave-to-class="opacity-0 max-h-0"
                    >
                        <div v-if="selectedSlip?.id === slip.id" class="border-t border-gray-100 px-5 pb-5 pt-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <!-- Pendapatan -->
                                <div>
                                    <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-2">Pendapatan</p>
                                    <dl class="space-y-1.5">
                                        <div class="flex justify-between text-xs">
                                            <dt class="text-gray-500">Gaji Pokok</dt>
                                            <dd class="font-medium text-gray-800 tabular-nums">{{ showNominal ? formatRupiah(slip.gaji_pokok) : 'Rp ****' }}</dd>
                                        </div>
                                        <div class="flex justify-between text-xs">
                                            <dt class="text-gray-500">Tj. Transport</dt>
                                            <dd class="font-medium text-gray-800 tabular-nums">{{ showNominal ? formatRupiah(slip.tj_transport) : 'Rp ****' }}</dd>
                                        </div>
                                        <div class="flex justify-between text-xs">
                                            <dt class="text-gray-500">Uang Makan</dt>
                                            <dd class="font-medium text-gray-800 tabular-nums">{{ showNominal ? formatRupiah(slip.uang_makan) : 'Rp ****' }}</dd>
                                        </div>
                                        <div v-if="Number(slip.total_tunjangan_tambahan) > 0" class="flex justify-between text-xs">
                                            <dt class="text-gray-500">Tunjangan Tambahan</dt>
                                            <dd class="font-medium text-gray-800 tabular-nums">{{ showNominal ? formatRupiah(slip.total_tunjangan_tambahan) : 'Rp ****' }}</dd>
                                        </div>
                                        <div v-if="Number(slip.honor_kelebihan_sks) > 0" class="flex justify-between text-xs">
                                            <dt class="text-gray-500">Honor SKS</dt>
                                            <dd class="font-medium text-gray-800 tabular-nums">{{ showNominal ? formatRupiah(slip.honor_kelebihan_sks) : 'Rp ****' }}</dd>
                                        </div>
                                    </dl>
                                </div>

                                <!-- Potongan -->
                                <div>
                                    <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-2">Potongan</p>
                                    <dl class="space-y-1.5">
                                        <div v-if="Number(slip.potongan_alpha) > 0" class="flex justify-between text-xs">
                                            <dt class="text-gray-500">Alpha</dt>
                                            <dd class="font-medium text-red-600 tabular-nums">{{ showNominal ? formatRupiah(slip.potongan_alpha) : 'Rp ****' }}</dd>
                                        </div>
                                        <div v-if="Number(slip.total_potongan_tambahan) > 0" class="flex justify-between text-xs">
                                            <dt class="text-gray-500">Potongan Lainnya</dt>
                                            <dd class="font-medium text-red-600 tabular-nums">{{ showNominal ? formatRupiah(slip.total_potongan_tambahan) : 'Rp ****' }}</dd>
                                        </div>
                                        <div v-if="!Number(slip.potongan_alpha) && !Number(slip.total_potongan_tambahan)" class="flex justify-between text-xs">
                                            <dt class="text-gray-500 italic">Tidak ada potongan</dt>
                                        </div>
                                    </dl>
                                </div>
                            </div>

                            <!-- Total -->
                            <div class="mt-4 pt-3 border-t border-gray-100 flex justify-between items-center">
                                <span class="text-xs font-semibold text-gray-600">Take Home Pay</span>
                                <span class="text-sm font-semibold text-[#025AB1] tabular-nums">
                                    {{ showNominal ? formatRupiah(slip.total_gaji) : 'Rp ****' }}
                                </span>
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>
