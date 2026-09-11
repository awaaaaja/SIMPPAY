<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { useRoutePrefix } from '@/composables/useRoutePrefix.js';

const prefix = useRoutePrefix();

const props = defineProps({
    levels: Array,
    klasifikasis: Array,
});

const form = useForm({
    level_struktur_id: '',
    nama_jabatan: '',
    total_poin: 0,
    klasifikasi_id: '',
    tunjangan_baru: 0,
});

function submit() {
    form.post(route(`${prefix.value}.jabatan-struktural-poin.store`));
}
</script>

<template>
    <Head title="Tambah Jabatan Struktural Poin" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Tambah Jabatan Struktural Poin</h2>
                <Link :href="route(`${prefix}.jabatan-struktural-poin.index`)" class="text-sm text-gray-600 hover:underline">Kembali</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <Label for="level_struktur_id">Level Struktur</Label>
                            <select id="level_struktur_id" v-model="form.level_struktur_id" class="mt-1 block w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-1 focus:ring-ring" required>
                                <option value="">-- Pilih Level Struktur --</option>
                                <option v-for="item in levels" :key="item.id" :value="item.id">{{ item.kode }} - {{ item.nama }}</option>
                            </select>
                            <p v-if="form.errors.level_struktur_id" class="mt-1 text-sm text-destructive">{{ form.errors.level_struktur_id }}</p>
                        </div>

                        <div>
                            <Label for="nama_jabatan">Nama Jabatan</Label>
                            <Input id="nama_jabatan" v-model="form.nama_jabatan" type="text" class="mt-1" required />
                            <p v-if="form.errors.nama_jabatan" class="mt-1 text-sm text-destructive">{{ form.errors.nama_jabatan }}</p>
                        </div>

                        <div>
                            <Label for="total_poin">Total Poin</Label>
                            <Input id="total_poin" v-model="form.total_poin" type="number" min="0" step="1" class="mt-1" required />
                            <p v-if="form.errors.total_poin" class="mt-1 text-sm text-destructive">{{ form.errors.total_poin }}</p>
                        </div>

                        <div>
                            <Label for="klasifikasi_id">Klasifikasi</Label>
                            <select id="klasifikasi_id" v-model="form.klasifikasi_id" class="mt-1 block w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-1 focus:ring-ring">
                                <option value="">-- Pilih Klasifikasi (Opsional) --</option>
                                <option v-for="item in klasifikasis" :key="item.id" :value="item.id">{{ item.klasifikasi }} - {{ item.level_jabatan }}</option>
                            </select>
                            <p v-if="form.errors.klasifikasi_id" class="mt-1 text-sm text-destructive">{{ form.errors.klasifikasi_id }}</p>
                        </div>

                        <div>
                            <Label for="tunjangan_baru">Tunjangan Baru</Label>
                            <Input id="tunjangan_baru" v-model="form.tunjangan_baru" type="number" min="0" step="1000" class="mt-1" required />
                            <p v-if="form.errors.tunjangan_baru" class="mt-1 text-sm text-destructive">{{ form.errors.tunjangan_baru }}</p>
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <Link :href="route(`${prefix}.jabatan-struktural-poin.index`)" class="rounded-lg border border-input bg-background px-4 py-2 text-sm font-medium hover:bg-accent hover:text-accent-foreground">Batal</Link>
                            <Button type="submit" :disabled="form.processing">Simpan</Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
