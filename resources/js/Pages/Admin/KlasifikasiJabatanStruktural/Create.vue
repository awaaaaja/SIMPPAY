<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { useRoutePrefix } from '@/composables/useRoutePrefix.js';

const prefix = useRoutePrefix();

const form = useForm({
    klasifikasi: 1,
    poin_min: 0,
    poin_max: 0,
    level_jabatan: '',
    tunjangan_min: 0,
    tunjangan_max: 0,
});

function submit() {
    form.post(route(`${prefix.value}.klasifikasi-jabatan-struktural.store`));
}
</script>

<template>
    <Head title="Tambah Klasifikasi Jabatan Struktural" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Tambah Klasifikasi Jabatan Struktural</h2>
                <Link :href="route(`${prefix}.klasifikasi-jabatan-struktural.index`)" class="text-sm text-gray-600 hover:underline">Kembali</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <Label for="klasifikasi">Klasifikasi</Label>
                            <Input id="klasifikasi" v-model="form.klasifikasi" type="number" min="1" max="6" step="1" class="mt-1" required />
                            <p v-if="form.errors.klasifikasi" class="mt-1 text-sm text-destructive">{{ form.errors.klasifikasi }}</p>
                        </div>

                        <div>
                            <Label for="poin_min">Poin Min</Label>
                            <Input id="poin_min" v-model="form.poin_min" type="number" min="0" step="1" class="mt-1" required />
                            <p v-if="form.errors.poin_min" class="mt-1 text-sm text-destructive">{{ form.errors.poin_min }}</p>
                        </div>

                        <div>
                            <Label for="poin_max">Poin Max</Label>
                            <Input id="poin_max" v-model="form.poin_max" type="number" min="0" step="1" class="mt-1" required />
                            <p v-if="form.errors.poin_max" class="mt-1 text-sm text-destructive">{{ form.errors.poin_max }}</p>
                        </div>

                        <div>
                            <Label for="level_jabatan">Level Jabatan</Label>
                            <Input id="level_jabatan" v-model="form.level_jabatan" type="text" class="mt-1" required />
                            <p v-if="form.errors.level_jabatan" class="mt-1 text-sm text-destructive">{{ form.errors.level_jabatan }}</p>
                        </div>

                        <div>
                            <Label for="tunjangan_min">Tunjangan Min</Label>
                            <Input id="tunjangan_min" v-model="form.tunjangan_min" type="number" min="0" step="1000" class="mt-1" required />
                            <p v-if="form.errors.tunjangan_min" class="mt-1 text-sm text-destructive">{{ form.errors.tunjangan_min }}</p>
                        </div>

                        <div>
                            <Label for="tunjangan_max">Tunjangan Max</Label>
                            <Input id="tunjangan_max" v-model="form.tunjangan_max" type="number" min="0" step="1000" class="mt-1" required />
                            <p v-if="form.errors.tunjangan_max" class="mt-1 text-sm text-destructive">{{ form.errors.tunjangan_max }}</p>
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <Link :href="route(`${prefix}.klasifikasi-jabatan-struktural.index`)" class="rounded-lg border border-input bg-background px-4 py-2 text-sm font-medium hover:bg-accent hover:text-accent-foreground">Batal</Link>
                            <Button type="submit" :disabled="form.processing">Simpan</Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
