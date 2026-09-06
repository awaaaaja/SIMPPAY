<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { useRoutePrefix } from '@/composables/useRoutePrefix.js';

const prefix = useRoutePrefix();

const props = defineProps({
    kehadirans: Object,
    jabatans: Array,
    filters: Object,
    can: Object,
});

const search = ref(props.filters.search || '');
const periode = ref(props.filters.periode || '');
const jabatanId = ref(props.filters.jabatan_id || '');

function applyFilter() {
    router.get(route(`${prefix.value}.kehadiran.index`), {
        search: search.value,
        periode: periode.value,
        jabatan_id: jabatanId.value,
    }, {
        preserveState: true,
        replace: true,
    });
}

function formatPeriode(val) {
    if (!val) return '-';
    const d = new Date(val);
    return d.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
}

const showCreateModal = ref(false);
const showImportModal = ref(false);

const createForm = useForm({
    pegawai_id: '',
    periode: '',
    hadir: 0,
    sakit: 0,
    alpha: 0,
});

function submitCreate() {
    createForm.post(route(`${prefix.value}.kehadiran.store`), {
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
        },
    });
}

const importForm = useForm({
    file: null,
    periode: '',
});

function submitImport() {
    importForm.post(route(`${prefix.value}.kehadiran.import`), {
        onSuccess: () => {
            showImportModal.value = false;
            importForm.reset();
        },
    });
}

function onFileChange(e) {
    importForm.file = e.target.files[0];
}

function destroy(id) {
    if (confirm('Yakin ingin menghapus data kehadiran ini?')) {
        router.delete(route(`${prefix.value}.kehadiran.destroy`, id));
    }
}
</script>

<template>
    <Head title="Kehadiran" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">Kehadiran</h2>
                    <p class="mt-1 text-sm text-gray-500">{{ kehadirans.total }} data kehadiran</p>
                </div>
                <div class="flex gap-2">
                    <a v-if="periode" :href="route(`${prefix}.laporan-absensi.pdf`, { periode: periode })" target="_blank"
                        class="inline-flex items-center rounded-md border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                        <svg class="w-4 h-4 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        Cetak PDF
                    </a>
                    <a v-if="periode" :href="route(`${prefix}.laporan-absensi.export`, { periode: periode })"
                        class="inline-flex items-center rounded-md border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                        <svg class="w-4 h-4 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Export Excel
                    </a>
                    <template v-if="can.create">
                        <button
                            @click="showImportModal = true"
                            class="inline-flex items-center rounded-md border border-[#025AB1] bg-white px-4 py-2 text-sm font-semibold text-[#025AB1] shadow-sm hover:bg-[#FEF2F2]"
                        >
                            Import Excel
                        </button>
                        <button
                            @click="showCreateModal = true"
                            class="inline-flex items-center rounded-md bg-[#025AB1] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#014A96]"
                        >
                            Tambah Data
                        </button>
                    </template>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Filter bar -->
                <div class="mb-6 flex flex-wrap items-end gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Periode</label>
                        <input
                            v-model="periode"
                            @change="applyFilter"
                            type="month"
                            class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-[#025AB1] focus:ring-[#025AB1]"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Jabatan</label>
                        <select v-model="jabatanId" @change="applyFilter" class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-[#025AB1] focus:ring-[#025AB1]">
                            <option value="">Semua</option>
                            <option v-for="j in jabatans" :key="j.id" :value="j.id">{{ j.nama_jabatan }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Pegawai</label>
                        <input
                            v-model="search"
                            @keyup.enter="applyFilter"
                            type="text"
                            placeholder="Nama atau NIK..."
                            class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-[#025AB1] focus:ring-[#025AB1]"
                        />
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Pegawai</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">NIK</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Jabatan</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Hadir</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Sakit</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Alpha</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Periode</th>
                                <th v-if="can.create" class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="(k, index) in kehadirans.data" :key="k.id" class="hover:bg-[#F8FAF8]">
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                    {{ (kehadirans.current_page - 1) * kehadirans.per_page + index + 1 }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                                    {{ k.pegawai?.nama_pegawai ?? '-' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                    {{ k.pegawai?.nik ?? '-' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                    {{ k.pegawai?.jabatan?.nama_jabatan ?? '-' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-gray-900" style="font-variant-numeric: tabular-nums;">
                                    {{ k.hadir }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-gray-900" style="font-variant-numeric: tabular-nums;">
                                    {{ k.sakit }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-gray-900" style="font-variant-numeric: tabular-nums;">
                                    {{ k.alpha }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                    {{ formatPeriode(k.periode) }}
                                </td>
                                <td v-if="can.delete" class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                    <button
                                        @click="destroy(k.id)"
                                        class="text-red-600 hover:underline"
                                    >
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="kehadirans.data.length === 0">
                                <td :colspan="can.create ? 9 : 8" class="px-6 py-12 text-center text-sm text-gray-500">
                                    Belum ada data kehadiran. Gunakan "Tambah Data" atau "Import Excel" untuk menambahkan data.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="kehadirans.last_page > 1" class="mt-4 flex justify-center">
                    <nav class="flex items-center gap-1">
                        <template v-for="link in kehadirans.links" :key="link.url">
                            <span v-if="!link.url" class="px-3 py-2 text-sm text-gray-400">...</span>
                            <Link
                                v-else
                                :href="link.url"
                                class="rounded-md px-3 py-2 text-sm"
                                :class="link.active ? 'bg-[#025AB1] text-white' : 'text-gray-700 hover:bg-gray-100'"
                                v-html="link.label"
                            />
                        </template>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Create Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
                <h3 class="mb-4 text-lg font-semibold text-gray-900">Tambah Data Kehadiran</h3>
                <form @submit.prevent="submitCreate">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Pegawai ID</label>
                        <input
                            v-model="createForm.pegawai_id"
                            type="number"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#025AB1] focus:ring-[#025AB1]"
                        />
                        <p v-if="createForm.errors.pegawai_id" class="mt-1 text-sm text-red-600">{{ createForm.errors.pegawai_id }}</p>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Periode</label>
                        <input
                            v-model="createForm.periode"
                            type="date"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#025AB1] focus:ring-[#025AB1]"
                        />
                        <p v-if="createForm.errors.periode" class="mt-1 text-sm text-red-600">{{ createForm.errors.periode }}</p>
                    </div>
                    <div class="mb-4 grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Hadir</label>
                            <input
                                v-model.number="createForm.hadir"
                                type="number"
                                min="0"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#025AB1] focus:ring-[#025AB1]"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Sakit</label>
                            <input
                                v-model.number="createForm.sakit"
                                type="number"
                                min="0"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#025AB1] focus:ring-[#025AB1]"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Alpha</label>
                            <input
                                v-model.number="createForm.alpha"
                                type="number"
                                min="0"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#025AB1] focus:ring-[#025AB1]"
                            />
                        </div>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button
                            type="button"
                            @click="showCreateModal = false"
                            class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="createForm.processing"
                            class="rounded-md bg-[#025AB1] px-4 py-2 text-sm font-semibold text-white hover:bg-[#014A96]"
                        >
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Import Modal -->
        <div v-if="showImportModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
                <h3 class="mb-4 text-lg font-semibold text-gray-900">Import Kehadiran dari Excel</h3>
                <form @submit.prevent="submitImport">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Periode</label>
                        <input
                            v-model="importForm.periode"
                            type="date"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#025AB1] focus:ring-[#025AB1]"
                        />
                        <p v-if="importForm.errors.periode" class="mt-1 text-sm text-red-600">{{ importForm.errors.periode }}</p>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">File Excel (.xlsx/.xls)</label>
                        <input
                            type="file"
                            accept=".xlsx,.xls"
                            @change="onFileChange"
                            required
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-3 file:rounded-md file:border-0 file:bg-[#025AB1] file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-[#014A96]"
                        />
                        <p v-if="importForm.errors.file" class="mt-1 text-sm text-red-600">{{ importForm.errors.file }}</p>
                    </div>
                    <div class="mb-4 rounded-md bg-gray-50 p-3 text-xs text-gray-600">
                        <p class="font-medium">Format kolom Excel:</p>
                        <p>nik | hadir | sakit | alpha</p>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button
                            type="button"
                            @click="showImportModal = false"
                            class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="importForm.processing"
                            class="rounded-md bg-[#025AB1] px-4 py-2 text-sm font-semibold text-white hover:bg-[#014A96]"
                        >
                            Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
