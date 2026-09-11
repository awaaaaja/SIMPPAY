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
});

const form = useForm({
    level_struktur_id: '',
    keterangan: '',
    golongan_range: '',
    nominal: 0,
});

function submit() {
    form.post(route(`${prefix.value}.tunjangan-transportasi-scale.store`));
}
</script>

<template>
    <Head title="Tambah Tunjangan Transportasi Scale" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Tambah Tunjangan Transportasi Scale</h2>
                <Link :href="route(`${prefix}.tunjangan-transportasi-scale.index`)" class="text-sm text-gray-600 hover:underline">Kembali</Link>
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
                            <Label for="keterangan">Keterangan</Label>
                            <Input id="keterangan" v-model="form.keterangan" type="text" class="mt-1" required />
                            <p v-if="form.errors.keterangan" class="mt-1 text-sm text-destructive">{{ form.errors.keterangan }}</p>
                        </div>

                        <div>
                            <Label for="golongan_range">Golongan Range</Label>
                            <Input id="golongan_range" v-model="form.golongan_range" type="text" class="mt-1" />
                            <p v-if="form.errors.golongan_range" class="mt-1 text-sm text-destructive">{{ form.errors.golongan_range }}</p>
                        </div>

                        <div>
                            <Label for="nominal">Nominal</Label>
                            <Input id="nominal" v-model="form.nominal" type="number" min="0" step="1000" class="mt-1" required />
                            <p v-if="form.errors.nominal" class="mt-1 text-sm text-destructive">{{ form.errors.nominal }}</p>
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <Link :href="route(`${prefix}.tunjangan-transportasi-scale.index`)" class="rounded-lg border border-input bg-background px-4 py-2 text-sm font-medium hover:bg-accent hover:text-accent-foreground">Batal</Link>
                            <Button type="submit" :disabled="form.processing">Simpan</Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
