<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { useRoutePrefix } from '@/composables/useRoutePrefix.js';

const prefix = useRoutePrefix();

const form = useForm({
    nama_jabatan: '',
    sks_perkuliahan: 0,
    sks_penelitian: 0,
    sks_adm: 0,
    sks_jabatan: 0,
});

function submit() {
    form.post(route(`${prefix.value}.beban-sks-jabatan.store`));
}
</script>

<template>
    <Head title="Tambah Beban SKS Jabatan" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Tambah Beban SKS Jabatan</h2>
                <Link :href="route(`${prefix}.beban-sks-jabatan.index`)" class="text-sm text-gray-600 hover:underline">Kembali</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <Label for="nama_jabatan">Nama Jabatan</Label>
                            <Input id="nama_jabatan" v-model="form.nama_jabatan" type="text" class="mt-1" required />
                            <p v-if="form.errors.nama_jabatan" class="mt-1 text-sm text-destructive">{{ form.errors.nama_jabatan }}</p>
                        </div>

                        <div>
                            <Label for="sks_perkuliahan">SKS Perkuliahan</Label>
                            <Input id="sks_perkuliahan" v-model="form.sks_perkuliahan" type="number" min="0" step="1" class="mt-1" required />
                            <p v-if="form.errors.sks_perkuliahan" class="mt-1 text-sm text-destructive">{{ form.errors.sks_perkuliahan }}</p>
                        </div>

                        <div>
                            <Label for="sks_penelitian">SKS Penelitian</Label>
                            <Input id="sks_penelitian" v-model="form.sks_penelitian" type="number" min="0" step="1" class="mt-1" required />
                            <p v-if="form.errors.sks_penelitian" class="mt-1 text-sm text-destructive">{{ form.errors.sks_penelitian }}</p>
                        </div>

                        <div>
                            <Label for="sks_adm">SKS Admin</Label>
                            <Input id="sks_adm" v-model="form.sks_adm" type="number" min="0" step="1" class="mt-1" required />
                            <p v-if="form.errors.sks_adm" class="mt-1 text-sm text-destructive">{{ form.errors.sks_adm }}</p>
                        </div>

                        <div>
                            <Label for="sks_jabatan">SKS Jabatan</Label>
                            <Input id="sks_jabatan" v-model="form.sks_jabatan" type="number" min="0" step="1" class="mt-1" required />
                            <p v-if="form.errors.sks_jabatan" class="mt-1 text-sm text-destructive">{{ form.errors.sks_jabatan }}</p>
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <Link :href="route(`${prefix}.beban-sks-jabatan.index`)" class="rounded-lg border border-input bg-background px-4 py-2 text-sm font-medium hover:bg-accent hover:text-accent-foreground">Batal</Link>
                            <Button type="submit" :disabled="form.processing">Simpan</Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
