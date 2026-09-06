<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useRoutePrefix } from '@/composables/useRoutePrefix.js';

const prefix = useRoutePrefix();

const props = defineProps({
    fungsionals: Object,
    can: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');

function applyFilter() {
    router.get(route(`${prefix.value}.fungsional.index`), { search: search.value }, {
        preserveState: true,
        replace: true,
    });
}

function destroy(id) {
    if (confirm('Yakin ingin menghapus fungsional ini?')) {
        router.delete(route(`${prefix.value}.fungsional.destroy`, id));
    }
}
</script>

<template>
    <Head title="Data Fungsional" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Data Fungsional</h2>
                <Link
                    v-if="can.create"
                    :href="route(`${prefix}.fungsional.create`)"
                    class="inline-flex items-center rounded-md bg-[#025AB1] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#014A96]"
                >
                    Tambah Fungsional
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-4">
                    <input
                        v-model="search"
                        @keyup.enter="applyFilter"
                        type="text"
                        placeholder="Cari nama fungsional..."
                        class="rounded-md border-gray-300 shadow-sm focus:border-[#025AB1] focus:ring-[#025AB1]"
                    />
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Nama Fungsional</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Pangkat</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Golongan</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Angka Kredit</th>
                                <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">Pegawai</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="(fungsional, index) in fungsionals.data" :key="fungsional.id" class="hover:bg-gray-50">
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                    {{ (fungsionals.current_page - 1) * fungsionals.per_page + index + 1 }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                                    {{ fungsional.nama_fungsional }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                    {{ fungsional.pangkat ?? '-' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                    {{ fungsional.golongan ?? '-' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-gray-900" style="font-variant-numeric: tabular-nums;">
                                    {{ fungsional.angka_kredit }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-center text-sm text-gray-500">
                                    {{ fungsional.pegawai_count }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                    <Link
                                        v-if="can.update"
                                        :href="route(`${prefix}.fungsional.edit`, fungsional.id)"
                                        class="mr-3 text-[#025AB1] hover:underline"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        v-if="can.delete && fungsional.pegawai_count === 0"
                                        @click="destroy(fungsional.id)"
                                        class="text-red-600 hover:underline"
                                    >
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="fungsionals.data.length === 0">
                                <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500">
                                    Belum ada data fungsional.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="fungsionals.last_page > 1" class="mt-4 flex justify-center">
                    <nav class="flex items-center gap-1">
                        <template v-for="link in fungsionals.links" :key="link.url">
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
    </AuthenticatedLayout>
</template>
