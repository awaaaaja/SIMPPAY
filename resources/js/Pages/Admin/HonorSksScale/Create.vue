<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { useRoutePrefix } from '@/composables/useRoutePrefix.js';

const prefix = useRoutePrefix();

const form = useForm({
    program: 's1',
    status_dosen: 'tetap',
    strata: '',
    nilai_sks: 0,
});

function submit() {
    form.post(route(`${prefix.value}.honor-sks-scale.store`));
}
</script>

<template>
    <Head title="Tambah Honor SKS Scale" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Tambah Honor SKS Scale</h2>
                <Link :href="route(`${prefix}.honor-sks-scale.index`)" class="text-sm text-gray-600 hover:underline">Kembali</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <Label for="program">Program</Label>
                            <select id="program" v-model="form.program" class="mt-1 block w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-1 focus:ring-ring" required>
                                <option value="s1">S1</option>
                                <option value="s2">S2</option>
                            </select>
                            <p v-if="form.errors.program" class="mt-1 text-sm text-destructive">{{ form.errors.program }}</p>
                        </div>

                        <div>
                            <Label for="status_dosen">Status Dosen</Label>
                            <select id="status_dosen" v-model="form.status_dosen" class="mt-1 block w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-1 focus:ring-ring" required>
                                <option value="tetap">Tetap</option>
                                <option value="tidak_tetap">Tidak Tetap</option>
                            </select>
                            <p v-if="form.errors.status_dosen" class="mt-1 text-sm text-destructive">{{ form.errors.status_dosen }}</p>
                        </div>

                        <div>
                            <Label for="strata">Strata</Label>
                            <Input id="strata" v-model="form.strata" type="text" class="mt-1" required />
                            <p v-if="form.errors.strata" class="mt-1 text-sm text-destructive">{{ form.errors.strata }}</p>
                        </div>

                        <div>
                            <Label for="nilai_sks">Nilai SKS</Label>
                            <Input id="nilai_sks" v-model="form.nilai_sks" type="number" min="0" step="1000" class="mt-1" required />
                            <p v-if="form.errors.nilai_sks" class="mt-1 text-sm text-destructive">{{ form.errors.nilai_sks }}</p>
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <Link :href="route(`${prefix}.honor-sks-scale.index`)" class="rounded-lg border border-input bg-background px-4 py-2 text-sm font-medium hover:bg-accent hover:text-accent-foreground">Batal</Link>
                            <Button type="submit" :disabled="form.processing">Simpan</Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
