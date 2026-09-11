<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { useRoutePrefix } from '@/composables/useRoutePrefix.js';

const prefix = useRoutePrefix();

const form = useForm({
    kode: '',
    nama: '',
    urutan: 0,
});

function submit() {
    form.post(route(`${prefix.value}.level-struktur.store`));
}
</script>

<template>
    <Head title="Tambah Level Struktur" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Tambah Level Struktur</h2>
                <Link :href="route(`${prefix}.level-struktur.index`)" class="text-sm text-gray-600 hover:underline">Kembali</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <Label for="kode">Kode</Label>
                            <Input id="kode" v-model="form.kode" type="text" class="mt-1" required />
                            <p v-if="form.errors.kode" class="mt-1 text-sm text-destructive">{{ form.errors.kode }}</p>
                        </div>

                        <div>
                            <Label for="nama">Nama</Label>
                            <Input id="nama" v-model="form.nama" type="text" class="mt-1" required />
                            <p v-if="form.errors.nama" class="mt-1 text-sm text-destructive">{{ form.errors.nama }}</p>
                        </div>

                        <div>
                            <Label for="urutan">Urutan</Label>
                            <Input id="urutan" v-model="form.urutan" type="number" min="0" step="1" class="mt-1" required />
                            <p v-if="form.errors.urutan" class="mt-1 text-sm text-destructive">{{ form.errors.urutan }}</p>
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <Link :href="route(`${prefix}.level-struktur.index`)" class="rounded-lg border border-input bg-background px-4 py-2 text-sm font-medium hover:bg-accent hover:text-accent-foreground">Batal</Link>
                            <Button type="submit" :disabled="form.processing">Simpan</Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
