<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useRoutePrefix } from '@/composables/useRoutePrefix.js';

const prefix = useRoutePrefix();

const props = defineProps({
    runs: Object,
    can: Object,
});

const showCalcModal = ref(false);
const calcForm = useForm({
    periode: '',
});

function submitCalc() {
    calcForm.post(route(`${prefix.value}.payroll-run.calculate`), {
        onSuccess: () => {
            showCalcModal.value = false;
            calcForm.reset();
        },
    });
}

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
</script>

<template>
    <Head title="Payroll Run" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-lg text-gray-800 leading-tight">Payroll Run</h2>
                <button v-if="can.create" @click="showCalcModal = true"
                    class="inline-flex items-center px-4 py-2 bg-[#176B5B] text-white text-sm font-medium rounded-[10px] hover:bg-[#145a4c] transition-colors">
                    <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    Hitung Gaji
                </button>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white rounded-[16px] shadow-[0_1px_2px_rgba(0,0,0,.04),0_8px_24px_rgba(0,0,0,.04)] overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Periode</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dihitung Oleh</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Difinalisasi Oleh</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="run in runs.data" :key="run.id" class="hover:bg-[#F8FAF8] transition-colors">
                                <td class="px-6 py-4 text-sm text-gray-800 font-medium">{{ formatPeriode(run.periode) }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize"
                                        :class="statusBadge(run.status)">
                                        {{ run.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ run.calculated_by?.name || '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ run.finalized_by?.name || '-' }}</td>
                                <td class="px-6 py-4 text-right">
                                    <button @click="router.get(route(`${prefix}.payroll-run.show`, run.id))"
                                        class="text-[#176B5B] hover:text-[#145a4c] text-sm font-medium">
                                        Detail
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="runs.data.length === 0">
                                <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-400">
                                    Belum ada payroll run.
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div v-if="runs.last_page > 1" class="px-6 py-3 border-t border-gray-100 flex items-center justify-between">
                        <p class="text-sm text-gray-500">
                            Menampilkan {{ runs.from }}-{{ runs.to }} dari {{ runs.total }} data
                        </p>
                        <div class="flex gap-1">
                            <button v-for="link in runs.links" :key="link.url"
                                @click="link.url && router.get(link.url, {}, { preserveState: true, replace: true })"
                                :disabled="!link.url"
                                class="px-3 py-1 text-sm rounded-[10px] transition-colors"
                                :class="link.active ? 'bg-[#176B5B] text-white' : 'text-gray-600 hover:bg-gray-100'"
                                v-html="link.label" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hitung Gaji Modal -->
        <div v-if="showCalcModal" class="fixed inset-0 z-50 overflow-y-auto" @click.self="showCalcModal = false">
            <div class="fixed inset-0 bg-black/30" @click="showCalcModal = false" />
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="bg-white rounded-[18px] shadow-xl w-full max-w-md p-6 relative">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Hitung Gaji</h3>
                    <form @submit.prevent="submitCalc" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Periode (Bulan Tahun)</label>
                            <input v-model="calcForm.periode" type="month" required
                                class="w-full rounded-[10px] border-gray-300 focus:border-[#176B5B] focus:ring-[#176B5B] text-sm" />
                            <p v-if="calcForm.errors.periode" class="text-red-500 text-xs mt-1">{{ calcForm.errors.periode }}</p>
                        </div>
                        <p class="text-xs text-gray-500">
                            Akan menghitung gaji seluruh pegawai aktif untuk periode ini.
                            Jika sudah ada run yang belum finalized untuk periode ini, data lama akan diganti.
                        </p>
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" @click="showCalcModal = false"
                                class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 rounded-[10px] hover:bg-gray-100 transition-colors">
                                Batal
                            </button>
                            <button type="submit" :disabled="calcForm.processing"
                                class="px-4 py-2 text-sm font-medium text-white bg-[#176B5B] rounded-[10px] hover:bg-[#145a4c] transition-colors disabled:opacity-50">
                                {{ calcForm.processing ? 'Menghitung...' : 'Hitung Gaji' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
