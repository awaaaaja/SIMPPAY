<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useRoutePrefix } from '@/composables/useRoutePrefix.js';

const prefix = useRoutePrefix();

const props = defineProps({
    pegawai: Object,
    can: Object,
});

const activeTab = ref('overview');

const tabs = [
    { id: 'overview', label: 'Overview' },
    { id: 'kepegawaian', label: 'Kepegawaian' },
    { id: 'keluarga', label: 'Keluarga' },
];

function formatRupiah(val) {
    if (!val) return '-';
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
}

function formatDate(val) {
    if (!val) return '-';
    return new Date(val).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
}

const statusColors = {
    aktif: 'bg-green-100 text-green-800',
    nonaktif: 'bg-red-100 text-red-800',
    pensiun: 'bg-yellow-100 text-yellow-800',
};
</script>

<template>
    <Head :title="`Profil — ${pegawai.nama_pegawai}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <Link :href="route(`${prefix}.pegawai.index`)" class="text-sm text-gray-600 hover:underline">
                    &larr; Kembali ke Daftar
                </Link>
                <div v-if="can.update" class="flex gap-2">
                    <Link
                        :href="route(`${prefix}.pegawai.edit`, pegawai.id)"
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50"
                    >
                        Edit
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <!-- Profile Header -->
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <div class="flex items-start gap-6">
                        <div class="h-20 w-20 flex-shrink-0 rounded-full bg-gray-200 overflow-hidden">
                            <img
                                v-if="pegawai.photo"
                                :src="'/storage/' + pegawai.photo"
                                :alt="pegawai.nama_pegawai"
                                class="h-full w-full object-cover"
                            />
                            <div v-else class="flex h-full w-full items-center justify-center text-2xl font-bold text-gray-400">
                                {{ pegawai.nama_pegawai.charAt(0) }}
                            </div>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">{{ pegawai.nama_pegawai }}</h3>
                            <p v-if="pegawai.nidn" class="text-sm text-gray-500">NIDN {{ pegawai.nidn }}</p>
                            <p class="mt-1 text-sm text-gray-600">
                                {{ pegawai.jabatan?.nama_jabatan ?? '-' }}
                                <span
                                    class="ml-2 inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                                    :class="statusColors[pegawai.status_pegawai]"
                                >
                                    {{ pegawai.status_pegawai }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <!-- Tabs -->
                    <div class="mt-6 border-b border-gray-200">
                        <nav class="flex gap-6">
                            <button
                                v-for="tab in tabs"
                                :key="tab.id"
                                @click="activeTab = tab.id"
                                class="border-b-2 pb-3 text-sm font-medium transition"
                                :class="activeTab === tab.id
                                    ? 'border-[#176B5B] text-[#176B5B]'
                                    : 'border-transparent text-gray-500 hover:text-gray-700'"
                            >
                                {{ tab.label }}
                            </button>
                        </nav>
                    </div>

                    <!-- Tab: Overview -->
                    <div v-if="activeTab === 'overview'" class="mt-6 space-y-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <span class="text-sm font-medium text-gray-500">NIK</span>
                                <p class="text-sm text-gray-900">{{ pegawai.nik }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500">Jenis Kelamin</span>
                                <p class="text-sm text-gray-900">{{ pegawai.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500">Tanggal Masuk</span>
                                <p class="text-sm text-gray-900">{{ formatDate(pegawai.tanggal_masuk) }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500">Ikatan Kerja</span>
                                <p class="text-sm text-gray-900">{{ pegawai.ikatan_kerja ?? '-' }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500">Email</span>
                                <p class="text-sm text-gray-900">{{ pegawai.email ?? '-' }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500">No. HP</span>
                                <p class="text-sm text-gray-900">{{ pegawai.no_hp ?? '-' }}</p>
                            </div>
                            <div class="col-span-2">
                                <span class="text-sm font-medium text-gray-500">Alamat</span>
                                <p class="text-sm text-gray-900">{{ pegawai.alamat ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Tab: Kepegawaian -->
                    <div v-if="activeTab === 'kepegawaian'" class="mt-6 space-y-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <span class="text-sm font-medium text-gray-500">Jabatan</span>
                                <p class="text-sm text-gray-900">{{ pegawai.jabatan?.nama_jabatan ?? '-' }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500">Struktural</span>
                                <p class="text-sm text-gray-900">{{ pegawai.struktural?.nama_struktural ?? '-' }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500">Fungsional</span>
                                <p class="text-sm text-gray-900">{{ pegawai.fungsional?.nama_fungsional ?? '-' }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500">Status Dosen</span>
                                <p class="text-sm text-gray-900">{{ pegawai.status_dosen ?? '-' }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500">NIDN</span>
                                <p class="text-sm text-gray-900">{{ pegawai.nidn ?? '-' }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500">NUPTK</span>
                                <p class="text-sm text-gray-900">{{ pegawai.nuptk ?? '-' }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500">No. SK</span>
                                <p class="text-sm text-gray-900">{{ pegawai.no_sk ?? '-' }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500">Tanggal SK</span>
                                <p class="text-sm text-gray-900">{{ formatDate(pegawai.tgl_sk) }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500">Masa Jabatan</span>
                                <p class="text-sm text-gray-900">{{ pegawai.masa_jabatan ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Tab: Keluarga -->
                    <div v-if="activeTab === 'keluarga'" class="mt-6 space-y-6">
                        <div>
                            <h4 class="text-sm font-semibold text-gray-700">Status Kawin</h4>
                            <p class="text-sm text-gray-900">{{ pegawai.status_kawin ?? '-' }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <span class="text-sm font-medium text-gray-500">Nama Pasangan</span>
                                <p class="text-sm text-gray-900">{{ pegawai.nama_sm ?? '-' }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500">NIP Pasangan</span>
                                <p class="text-sm text-gray-900">{{ pegawai.nip_sm ?? '-' }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500">No. HP Pasangan</span>
                                <p class="text-sm text-gray-900">{{ pegawai.nohp_sm ?? '-' }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500">Pekerjaan Pasangan</span>
                                <p class="text-sm text-gray-900">{{ pegawai.pekerjaan_sm ?? '-' }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500">Nama Ibu</span>
                                <p class="text-sm text-gray-900">{{ pegawai.nama_ibu ?? '-' }}</p>
                            </div>
                        </div>

                        <!-- Anak -->
                        <div>
                            <h4 class="mb-3 text-sm font-semibold text-gray-700">Anak</h4>
                            <div v-if="pegawai.anak && pegawai.anak.length > 0" class="overflow-hidden rounded-lg border border-gray-200">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">No</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Nama</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">TTL</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">JK</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Ke-</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        <tr v-for="(anak, i) in pegawai.anak" :key="anak.id">
                                            <td class="px-4 py-2 text-sm text-gray-500">{{ i + 1 }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-900">{{ anak.nama_anak }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-500">{{ anak.tempat_tanggal_lahir ?? '-' }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-500">{{ anak.jenis_kelamin ?? '-' }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-500">{{ anak.anak_ke ?? '-' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <p v-else class="text-sm text-gray-500">Belum ada data anak.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
