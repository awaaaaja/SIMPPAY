<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { useRoutePrefix } from '@/composables/useRoutePrefix.js';

const prefix = useRoutePrefix();

const form = useForm({
    jabatan_fungsional: '',
    angka_kredit: 0,
    pangkat: '',
    golongan_ruang: '',
    nominal: 0,
});

function submit() {
    form.post(route(`${prefix.value}.tunjangan-fungsional-dosen-scale.store`));
}
</script>

<template>
    <Head title="Tambah Tunjangan Fungsional Dosen Scale" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Tambah Tunjangan Fungsional Dosen Scale</h2>
                <Link :href="route(`${prefix}.tunjangan-fungsional-dosen-scale.index`)" class="text-sm text-gray-600 hover:underline">Kembali</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <Label for="jabatan_fungsional">Jabatan Fungsional</Label>
                            <Input id="jabatan_fungsional" v-model="form.jabatan_fungsional" type="text" class="mt-1" required />
                            <p v-if="form.errors.jabatan_fungsional" class="mt-1 text-sm text-destructive">{{ form.errors.jabatan_fungsional }}</p>
                        </div>

                        <div>
                            <Label for="angka_kredit">Angka Kredit</Label>
                            <Input id="angka_kredit" v-model="form.angka_kredit" type="number" min="0" step="1" class="mt-1" required />
                            <p v-if="form.errors.angka_kredit" class="mt-1 text-sm text-destructive">{{ form.errors.angka_kredit }}</p>
                        </div>

                        <div>
                            <Label for="pangkat">Pangkat</Label>
                            <Input id="pangkat" v-model="form.pangkat" type="text" class="mt-1" required />
                            <p v-if="form.errors.pangkat" class="mt-1 text-sm text-destructive">{{ form.errors.pangkat }}</p>
                        </div>

                        <div>
                            <Label for="golongan_ruang">Golongan Ruang</Label>
                            <Input id="golongan_ruang" v-model="form.golongan_ruang" type="text" class="mt-1" required />
                            <p v-if="form.errors.golongan_ruang" class="mt-1 text-sm text-destructive">{{ form.errors.golongan_ruang }}</p>
                        </div>

                        <div>
                            <Label for="nominal">Nominal</Label>
                            <Input id="nominal" v-model="form.nominal" type="number" min="0" step="1000" class="mt-1" required />
                            <p v-if="form.errors.nominal" class="mt-1 text-sm text-destructive">{{ form.errors.nominal }}</p>
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <Link :href="route(`${prefix}.tunjangan-fungsional-dosen-scale.index`)" class="rounded-lg border border-input bg-background px-4 py-2 text-sm font-medium hover:bg-accent hover:text-accent-foreground">Batal</Link>
                            <Button type="submit" :disabled="form.processing">Simpan</Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
