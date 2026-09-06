<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useRoutePrefix } from '@/composables/useRoutePrefix.js';

const prefix = useRoutePrefix();

const props = defineProps({
    potongans: Object,
    can: Object,
});

const showCreateModal = ref(false);
const editingPotongan = ref(null);

const createForm = useForm({
    nama_potongan: '',
    tipe: 'nominal',
    nilai: 0,
    is_alpha_penalty: false,
    aktif: true,
});

function openCreate() {
    editingPotongan.value = null;
    createForm.reset();
    createForm.is_alpha_penalty = false;
    createForm.aktif = true;
    showCreateModal.value = true;
}

function openEdit(potongan) {
    editingPotongan.value = potongan;
    createForm.nama_potongan = potongan.nama_potongan;
    createForm.tipe = potongan.tipe;
    createForm.nilai = potongan.nilai;
    createForm.is_alpha_penalty = potongan.is_alpha_penalty;
    createForm.aktif = potongan.aktif;
    showCreateModal.value = true;
}

function submitForm() {
    if (editingPotongan.value) {
        createForm.patch(route(`${prefix.value}.potongan-gaji.update`, editingPotongan.value.id), {
            onSuccess: () => {
                showCreateModal.value = false;
                createForm.reset();
                editingPotongan.value = null;
            },
        });
    } else {
        createForm.post(route(`${prefix.value}.potongan-gaji.store`), {
            onSuccess: () => {
                showCreateModal.value = false;
                createForm.reset();
            },
        });
    }
}

function toggleAktif(id) {
    router.patch(route(`${prefix.value}.potongan-gaji.toggle`, id), {}, { preserveState: true });
}

function destroy(id) {
    if (confirm('Yakin ingin menghapus potongan gaji ini?')) {
        router.delete(route(`${prefix.value}.potongan-gaji.destroy`, id));
    }
}

function formatRupiah(val) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
}
</script>

<template>
    <Head title="Potongan Gaji" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-lg text-gray-800 leading-tight">Potongan Gaji</h2>
                <button v-if="can.create" @click="openCreate"
                    class="inline-flex items-center px-4 py-2 bg-[#D40C14] text-white text-sm font-medium rounded-[10px] hover:bg-[#A30A10] transition-colors">
                    <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Tambah Potongan
                </button>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white rounded-[16px] shadow-[0_1px_2px_rgba(0,0,0,.04),0_8px_24px_rgba(0,0,0,.04)] overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Potongan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Alpha Penalty</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="p in potongans.data" :key="p.id" class="hover:bg-[#F8FAF8] transition-colors">
                                <td class="px-6 py-4 text-sm text-gray-800">{{ p.nama_potongan }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 capitalize">{{ p.tipe }}</td>
                                <td class="px-6 py-4 text-sm text-gray-800 text-right font-variant-numeric:tabular-nums">
                                    {{ p.tipe === 'persentase' ? p.nilai + '%' : formatRupiah(p.nilai) }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span v-if="p.is_alpha_penalty"
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-[#D40C14]/10 text-[#D40C14]">
                                        Ya
                                    </span>
                                    <span v-else class="text-gray-400 text-xs">-</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button @click="toggleAktif(p.id)"
                                        class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors"
                                        :class="p.aktif ? 'bg-[#D40C14]' : 'bg-gray-300'">
                                        <span class="inline-block h-3.5 w-3.5 rounded-full bg-white transition-transform"
                                            :class="p.aktif ? 'translate-x-[18px]' : 'translate-x-[3px]'" />
                                    </button>
                                </td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <button v-if="can.update" @click="openEdit(p)"
                                        class="text-[#D40C14] hover:text-[#A30A10] text-sm font-medium">
                                        Edit
                                    </button>
                                    <button v-if="can.delete" @click="destroy(p.id)"
                                        class="text-red-500 hover:text-red-700 text-sm font-medium">
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="potongans.data.length === 0">
                                <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-400">
                                    Belum ada data potongan gaji.
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div v-if="potongans.last_page > 1" class="px-6 py-3 border-t border-gray-100 flex items-center justify-between">
                        <p class="text-sm text-gray-500">
                            Menampilkan {{ potongans.from }}-{{ potongans.to }} dari {{ potongans.total }} data
                        </p>
                        <div class="flex gap-1">
                            <button v-for="link in potongans.links" :key="link.url"
                                @click="link.url && router.get(link.url, {}, { preserveState: true, replace: true })"
                                :disabled="!link.url"
                                class="px-3 py-1 text-sm rounded-[10px] transition-colors"
                                :class="link.active ? 'bg-[#D40C14] text-white' : 'text-gray-600 hover:bg-gray-100'"
                                v-html="link.label" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 z-50 overflow-y-auto" @click.self="showCreateModal = false">
            <div class="fixed inset-0 bg-black/30" @click="showCreateModal = false" />
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="bg-white rounded-[18px] shadow-xl w-full max-w-md p-6 relative">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">
                        {{ editingPotongan ? 'Edit Potongan Gaji' : 'Tambah Potongan Gaji' }}
                    </h3>
                    <form @submit.prevent="submitForm" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Potongan</label>
                            <input v-model="createForm.nama_potongan" type="text"
                                class="w-full rounded-[10px] border-gray-300 focus:border-[#D40C14] focus:ring-[#D40C14] text-sm" />
                            <p v-if="createForm.errors.nama_potongan" class="text-red-500 text-xs mt-1">{{ createForm.errors.nama_potongan }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tipe</label>
                                <select v-model="createForm.tipe"
                                    class="w-full rounded-[10px] border-gray-300 focus:border-[#D40C14] focus:ring-[#D40C14] text-sm">
                                    <option value="nominal">Nominal (Rp)</option>
                                    <option value="persentase">Persentase (%)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nilai</label>
                                <input v-model.number="createForm.nilai" type="number" step="0.01" min="0"
                                    class="w-full rounded-[10px] border-gray-300 focus:border-[#D40C14] focus:ring-[#D40C14] text-sm" />
                                <p v-if="createForm.errors.nilai" class="text-red-500 text-xs mt-1">{{ createForm.errors.nilai }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <input v-model="createForm.is_alpha_penalty" type="checkbox" id="is_alpha"
                                class="rounded border-gray-300 text-[#D40C14] focus:ring-[#D40C14]" />
                            <label for="is_alpha" class="text-sm text-gray-700">Alpha Penalty (potongan khusus alpha)</label>
                        </div>
                        <div class="flex items-center gap-2">
                            <input v-model="createForm.aktif" type="checkbox" id="aktif"
                                class="rounded border-gray-300 text-[#D40C14] focus:ring-[#D40C14]" />
                            <label for="aktif" class="text-sm text-gray-700">Aktif</label>
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" @click="showCreateModal = false"
                                class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 rounded-[10px] hover:bg-gray-100 transition-colors">
                                Batal
                            </button>
                            <button type="submit" :disabled="createForm.processing"
                                class="px-4 py-2 text-sm font-medium text-white bg-[#D40C14] rounded-[10px] hover:bg-[#A30A10] transition-colors disabled:opacity-50">
                                {{ createForm.processing ? 'Menyimpan...' : 'Simpan' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
