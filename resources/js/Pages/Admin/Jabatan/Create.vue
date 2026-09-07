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
    gaji_pokok: 0,
    tj_transport: 0,
    uang_makan: 0,
});

function submit() {
    form.post(route(`${prefix.value}.jabatan.store`));
}
</script>

<template>
    <Head title="Tambah Jabatan" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Tambah Jabatan</h2>
                <Link :href="route(`${prefix}.jabatan.index`)" class="text-sm text-gray-600 hover:underline">Kembali</Link>
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
                            <Label for="gaji_pokok">Gaji Pokok</Label>
                            <Input id="gaji_pokok" v-model="form.gaji_pokok" type="number" min="0" step="1000" class="mt-1" required />
                            <p v-if="form.errors.gaji_pokok" class="mt-1 text-sm text-destructive">{{ form.errors.gaji_pokok }}</p>
                        </div>

                        <div>
                            <Label for="tj_transport">Tunjangan Transport</Label>
                            <Input id="tj_transport" v-model="form.tj_transport" type="number" min="0" step="1000" class="mt-1" required />
                            <p v-if="form.errors.tj_transport" class="mt-1 text-sm text-destructive">{{ form.errors.tj_transport }}</p>
                        </div>

                        <div>
                            <Label for="uang_makan">Uang Makan</Label>
                            <Input id="uang_makan" v-model="form.uang_makan" type="number" min="0" step="1000" class="mt-1" required />
                            <p v-if="form.errors.uang_makan" class="mt-1 text-sm text-destructive">{{ form.errors.uang_makan }}</p>
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <Link :href="route(`${prefix}.jabatan.index`)" class="rounded-lg border border-input bg-background px-4 py-2 text-sm font-medium hover:bg-accent hover:text-accent-foreground">Batal</Link>
                            <Button type="submit" :disabled="form.processing">Simpan</Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
