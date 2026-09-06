<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useRoutePrefix } from '@/composables/useRoutePrefix.js';

const prefix = useRoutePrefix();

const props = defineProps({
    strukturals: Object,
    can: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');

function applyFilter() {
    router.get(route(`${prefix.value}.struktural.index`), { search: search.value }, {
        preserveState: true,
        replace: true,
    });
}

function destroy(id) {
    if (confirm('Yakin ingin menghapus struktural ini?')) {
        router.delete(route(`${prefix.value}.struktural.destroy`, id));
    }
}
</script>

<template>
    <Head title="Data Struktural" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Data Struktural</h2>
                <Link
                    v-if="can.create"
                    :href="route(`${prefix}.struktural.create`)"
                    class="inline-flex items-center rounded-md bg-[#025AB1] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#014A96]"
                >
                    Tambah Struktural
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
                        placeholder="Cari nama struktural..."
                        class="rounded-md border-gray-300 shadow-sm focus:border-[#025AB1] focus:ring-[#025AB1]"
                    />
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Nama Struktural</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Level</th>
                                <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                                <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">Pegawai</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="(struktural, index) in strukturals.data" :key="struktural.id" class="hover:bg-gray-50">
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                    {{ (strukturals.current_page - 1) * strukturals.per_page + index + 1 }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                                    {{ struktural.nama_struktural }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                    {{ struktural.level_struktural ?? '-' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-center">
                                    <span
                                        class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                                        :class="struktural.status === 'aktif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                    >
                                        {{ struktural.status }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-center text-sm text-gray-500">
                                    {{ struktural.pegawai_count }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                    <Link
                                        v-if="can.update"
                                        :href="route(`${prefix}.struktural.edit`, struktural.id)"
                                        class="mr-3 text-[#025AB1] hover:underline"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        v-if="can.delete && struktural.pegawai_count === 0"
                                        @click="destroy(struktural.id)"
                                        class="text-red-600 hover:underline"
                                    >
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="strukturals.data.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">
                                    Belum ada data struktural.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="strukturals.last_page > 1" class="mt-4 flex justify-center">
                    <nav class="flex items-center gap-1">
                        <template v-for="link in strukturals.links" :key="link.url">
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
