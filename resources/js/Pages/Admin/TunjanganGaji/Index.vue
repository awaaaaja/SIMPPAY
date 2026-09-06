<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { useRoutePrefix } from '@/composables/useRoutePrefix.js';

const prefix = useRoutePrefix();

const props = defineProps({
    tunjangans: Object,
    jabatans: Array,
    pegawais: Array,
    filters: Object,
    can: Object,
});

const targetTipe = ref(props.filters.target_tipe || '');

function applyFilter() {
    router.get(route(`${prefix.value}.tunjangan-gaji.index`), {
        target_tipe: targetTipe.value,
    }, {
        preserveState: true,
        replace: true,
    });
}

const showCreateModal = ref(false);
const editingTunjangan = ref(null);

const createForm = useForm({
    nama_tunjangan: '',
    target_tipe: 'semua',
    jabatan_id: '',
    pegawai_id: '',
    nominal: 0,
    aktif: true,
});

function openCreate() {
    editingTunjangan.value = null;
    createForm.reset();
    createForm.target_tipe = 'semua';
    createForm.aktif = true;
    showCreateModal.value = true;
}

function openEdit(tunjangan) {
    editingTunjangan.value = tunjangan;
    createForm.nama_tunjangan = tunjangan.nama_tunjangan;
    createForm.target_tipe = tunjangan.target_tipe;
    createForm.jabatan_id = tunjangan.jabatan_id || '';
    createForm.pegawai_id = tunjangan.pegawai_id || '';
    createForm.nominal = tunjangan.nominal;
    createForm.aktif = tunjangan.aktif;
    showCreateModal.value = true;
}

function submitForm() {
    if (editingTunjangan.value) {
        createForm.patch(route(`${prefix.value}.tunjangan-gaji.update`, editingTunjangan.value.id), {
            onSuccess: () => {
                showCreateModal.value = false;
                createForm.reset();
                editingTunjangan.value = null;
            },
        });
    } else {
        createForm.post(route(`${prefix.value}.tunjangan-gaji.store`), {
            onSuccess: () => {
                showCreateModal.value = false;
                createForm.reset();
            },
        });
    }
}

function toggleAktif(id) {
    router.patch(route(`${prefix.value}.tunjangan-gaji.toggle`, id), {}, { preserveState: true });
}

function destroy(id) {
    if (confirm('Yakin ingin menghapus tunjangan gaji ini?')) {
        router.delete(route(`${prefix.value}.tunjangan-gaji.destroy`, id));
    }
}

function formatRupiah(val) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
}

function targetLabel(item) {
    if (item.target_tipe === 'semua') return 'Semua Pegawai';
    if (item.target_tipe === 'jabatan') return item.jabatan?.nama_jabatan || '-';
    return item.pegawai?.nama_pegawai || '-';
}
</script>

<template>
    <Head title="Tunjangan Gaji" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-lg text-gray-800 leading-tight">Tunjangan Gaji</h2>
                <button v-if="can.create" @click="openCreate"
                    class="inline-flex items-center px-4 py-2 bg-[#025AB1] text-white text-sm font-medium rounded-[10px] hover:bg-[#014A96] transition-colors">
                    <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Tambah Tunjangan
                </button>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Filter -->
                <div class="mb-4 flex items-center gap-3">
                    <select v-model="targetTipe" @change="applyFilter"
                        class="rounded-[10px] border-gray-300 text-sm focus:border-[#025AB1] focus:ring-[#025AB1]">
                        <option value="">Semua Target</option>
                        <option value="semua">Semua Pegawai</option>
                        <option value="jabatan">Per Jabatan</option>
                        <option value="pegawai">Per Pegawai</option>
                    </select>
                </div>

                <div class="bg-white rounded-[16px] shadow-[0_1px_2px_rgba(0,0,0,.04),0_8px_24px_rgba(0,0,0,.04)] overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Tunjangan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Target</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Nominal</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="t in tunjangans.data" :key="t.id" class="hover:bg-[#F8FAF8] transition-colors">
                                <td class="px-6 py-4 text-sm text-gray-800">{{ t.nama_tunjangan }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ targetLabel(t) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-800 text-right font-variant-numeric:tabular-nums">
                                    {{ formatRupiah(t.nominal) }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button @click="toggleAktif(t.id)"
                                        class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors"
                                        :class="t.aktif ? 'bg-[#025AB1]' : 'bg-gray-300'">
                                        <span class="inline-block h-3.5 w-3.5 rounded-full bg-white transition-transform"
                                            :class="t.aktif ? 'translate-x-[18px]' : 'translate-x-[3px]'" />
                                    </button>
                                </td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <button v-if="can.update" @click="openEdit(t)"
                                        class="text-[#025AB1] hover:text-[#014A96] text-sm font-medium">
                                        Edit
                                    </button>
                                    <button v-if="can.delete" @click="destroy(t.id)"
                                        class="text-red-500 hover:text-red-700 text-sm font-medium">
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="tunjangans.data.length === 0">
                                <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-400">
                                    Belum ada data tunjangan gaji.
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div v-if="tunjangans.last_page > 1" class="px-6 py-3 border-t border-gray-100 flex items-center justify-between">
                        <p class="text-sm text-gray-500">
                            Menampilkan {{ tunjangans.from }}-{{ tunjangans.to }} dari {{ tunjangans.total }} data
                        </p>
                        <div class="flex gap-1">
                            <button v-for="link in tunjangans.links" :key="link.url"
                                @click="link.url && router.get(link.url, {}, { preserveState: true, replace: true })"
                                :disabled="!link.url"
                                class="px-3 py-1 text-sm rounded-[10px] transition-colors"
                                :class="link.active ? 'bg-[#025AB1] text-white' : 'text-gray-600 hover:bg-gray-100'"
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
                        {{ editingTunjangan ? 'Edit Tunjangan Gaji' : 'Tambah Tunjangan Gaji' }}
                    </h3>
                    <form @submit.prevent="submitForm" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Tunjangan</label>
                            <input v-model="createForm.nama_tunjangan" type="text"
                                class="w-full rounded-[10px] border-gray-300 focus:border-[#025AB1] focus:ring-[#025AB1] text-sm" />
                            <p v-if="createForm.errors.nama_tunjangan" class="text-red-500 text-xs mt-1">{{ createForm.errors.nama_tunjangan }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Target</label>
                            <select v-model="createForm.target_tipe"
                                class="w-full rounded-[10px] border-gray-300 focus:border-[#025AB1] focus:ring-[#025AB1] text-sm">
                                <option value="semua">Semua Pegawai</option>
                                <option value="jabatan">Per Jabatan</option>
                                <option value="pegawai">Per Pegawai</option>
                            </select>
                        </div>
                        <div v-if="createForm.target_tipe === 'jabatan'">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan</label>
                            <select v-model="createForm.jabatan_id"
                                class="w-full rounded-[10px] border-gray-300 focus:border-[#025AB1] focus:ring-[#025AB1] text-sm">
                                <option value="">Pilih Jabatan</option>
                                <option v-for="j in jabatans" :key="j.id" :value="j.id">{{ j.nama_jabatan }}</option>
                            </select>
                            <p v-if="createForm.errors.jabatan_id" class="text-red-500 text-xs mt-1">{{ createForm.errors.jabatan_id }}</p>
                        </div>
                        <div v-if="createForm.target_tipe === 'pegawai'">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pegawai</label>
                            <select v-model="createForm.pegawai_id"
                                class="w-full rounded-[10px] border-gray-300 focus:border-[#025AB1] focus:ring-[#025AB1] text-sm">
                                <option value="">Pilih Pegawai</option>
                                <option v-for="p in pegawais" :key="p.id" :value="p.id">{{ p.nama_pegawai }} ({{ p.nik }})</option>
                            </select>
                            <p v-if="createForm.errors.pegawai_id" class="text-red-500 text-xs mt-1">{{ createForm.errors.pegawai_id }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nominal (Rp)</label>
                            <input v-model.number="createForm.nominal" type="number" step="0.01" min="0"
                                class="w-full rounded-[10px] border-gray-300 focus:border-[#025AB1] focus:ring-[#025AB1] text-sm" />
                            <p v-if="createForm.errors.nominal" class="text-red-500 text-xs mt-1">{{ createForm.errors.nominal }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <input v-model="createForm.aktif" type="checkbox" id="aktif"
                                class="rounded border-gray-300 text-[#025AB1] focus:ring-[#025AB1]" />
                            <label for="aktif" class="text-sm text-gray-700">Aktif</label>
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" @click="showCreateModal = false"
                                class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 rounded-[10px] hover:bg-gray-100 transition-colors">
                                Batal
                            </button>
                            <button type="submit" :disabled="createForm.processing"
                                class="px-4 py-2 text-sm font-medium text-white bg-[#025AB1] rounded-[10px] hover:bg-[#014A96] transition-colors disabled:opacity-50">
                                {{ createForm.processing ? 'Menyimpan...' : 'Simpan' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
