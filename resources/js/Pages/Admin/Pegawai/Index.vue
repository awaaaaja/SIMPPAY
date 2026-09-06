<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { useRoutePrefix } from '@/composables/useRoutePrefix.js';

const prefix = useRoutePrefix();

const props = defineProps({
    pegawais: Object,
    jabatans: Array,
    strukturals: Array,
    fungsionals: Array,
    can: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const jabatan_id = ref(props.filters.jabatan_id || '');
const status_pegawai = ref(props.filters.status_pegawai || '');
const struktural_id = ref(props.filters.struktural_id || '');
const fungsional_id = ref(props.filters.fungsional_id || '');

function applyFilter() {
    router.get(route(`${prefix.value}.pegawai.index`), {
        search: search.value,
        jabatan_id: jabatan_id.value,
        status_pegawai: status_pegawai.value,
        struktural_id: struktural_id.value,
        fungsional_id: fungsional_id.value,
    }, {
        preserveState: true,
        replace: true,
    });
}

function destroy(id) {
    if (confirm('Yakin ingin menghapus pegawai ini? Data yang dihapus tidak dapat dikembalikan.')) {
        router.delete(route(`${prefix.value}.pegawai.destroy`, id));
    }
}

const statusColors = {
    aktif: 'bg-green-100 text-green-800',
    nonaktif: 'bg-red-100 text-red-800',
    pensiun: 'bg-yellow-100 text-yellow-800',
};
</script>

<template>
    <Head title="Data Pegawai" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">Pegawai</h2>
                    <p class="mt-1 text-sm text-gray-500">{{ pegawais.total }} employees</p>
                </div>
                <Link
                    v-if="can.create"
                    :href="route(`${prefix}.pegawai.create`)"
                    class="inline-flex items-center rounded-md bg-[#176B5B] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#145a4c]"
                >
                    Tambah Pegawai
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Filter bar -->
                <div class="mb-6 flex flex-wrap items-end gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Search</label>
                        <input
                            v-model="search"
                            @keyup.enter="applyFilter"
                            type="text"
                            placeholder="Nama atau NIK..."
                            class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-[#176B5B] focus:ring-[#176B5B]"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Jabatan</label>
                        <select v-model="jabatan_id" @change="applyFilter" class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-[#176B5B] focus:ring-[#176B5B]">
                            <option value="">Semua</option>
                            <option v-for="j in jabatans" :key="j.id" :value="j.id">{{ j.nama_jabatan }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select v-model="status_pegawai" @change="applyFilter" class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-[#176B5B] focus:ring-[#176B5B]">
                            <option value="">Semua</option>
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Nonaktif</option>
                            <option value="pensiun">Pensiun</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Struktural</label>
                        <select v-model="struktural_id" @change="applyFilter" class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-[#176B5B] focus:ring-[#176B5B]">
                            <option value="">Semua</option>
                            <option v-for="s in strukturals" :key="s.id" :value="s.id">{{ s.nama_struktural }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fungsional</label>
                        <select v-model="fungsional_id" @change="applyFilter" class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-[#176B5B] focus:ring-[#176B5B]">
                            <option value="">Semua</option>
                            <option v-for="f in fungsionals" :key="f.id" :value="f.id">{{ f.nama_fungsional }}</option>
                        </select>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Nama</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">NIK</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Jabatan</th>
                                <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Struktural</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Fungsional</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="(pegawai, index) in pegawais.data" :key="pegawai.id" class="cursor-pointer hover:bg-[#F8FAF8]">
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                    {{ (pegawais.current_page - 1) * pegawais.per_page + index + 1 }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-left">
                                    <Link :href="route(`${prefix}.pegawai.show`, pegawai.id)" class="text-sm font-medium text-gray-900 hover:text-[#176B5B]">
                                        {{ pegawai.nama_pegawai }}
                                    </Link>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ pegawai.nik }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                    {{ pegawai.jabatan?.nama_jabatan ?? '-' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-center">
                                    <span
                                        class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                                        :class="statusColors[pegawai.status_pegawai]"
                                    >
                                        {{ pegawai.status_pegawai }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                    {{ pegawai.struktural?.nama_struktural ?? '-' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                    {{ pegawai.fungsional?.nama_fungsional ?? '-' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                    <Link
                                        :href="route(`${prefix}.pegawai.show`, pegawai.id)"
                                        class="mr-3 text-[#176B5B] hover:underline"
                                    >
                                        Lihat
                                    </Link>
                                    <button
                                        v-if="can.delete"
                                        @click.stop="destroy(pegawai.id)"
                                        class="text-red-600 hover:underline"
                                    >
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="pegawais.data.length === 0">
                                <td colspan="8" class="px-6 py-12 text-center text-sm text-gray-500">
                                    Belum ada data pegawai.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="pegawais.last_page > 1" class="mt-4 flex justify-center">
                    <nav class="flex items-center gap-1">
                        <template v-for="link in pegawais.links" :key="link.url">
                            <span v-if="!link.url" class="px-3 py-2 text-sm text-gray-400">...</span>
                            <Link
                                v-else
                                :href="link.url"
                                class="rounded-md px-3 py-2 text-sm"
                                :class="link.active ? 'bg-[#176B5B] text-white' : 'text-gray-700 hover:bg-gray-100'"
                                v-html="link.label"
                            />
                        </template>
                    </nav>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
