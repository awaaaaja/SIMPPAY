<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { useRoutePrefix } from '@/composables/useRoutePrefix.js';

const prefix = useRoutePrefix();

const props = defineProps({
    item: Object,
});

const form = useForm({
    program: props.item.program,
    status_dosen: props.item.status_dosen,
    strata: props.item.strata,
    nilai_sks: props.item.nilai_sks,
});

function submit() {
    form.put(route(`${prefix.value}.honor-sks-scale.update`, props.item.id));
}
</script>

<template>
    <Head title="Edit Honor SKS Scale" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Edit Honor SKS Scale</h2>
                <Link :href="route(`${prefix}.honor-sks-scale.index`)" class="text-sm text-gray-600 hover:underline">Kembali</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <Label for="program">Program</Label>
                            <select id="program" v-model="form.program" class="mt-1 block w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" required>
                                <option value="">Pilih Program</option>
                                <option value="S1" :selected="form.program === 'S1'">S1</option>
                                <option value="S2" :selected="form.program === 'S2'">S2</option>
                                <option value="S3" :selected="form.program === 'S3'">S3</option>
                            </select>
                            <p v-if="form.errors.program" class="mt-1 text-sm text-destructive">{{ form.errors.program }}</p>
                        </div>

                        <div>
                            <Label for="status_dosen">Status Dosen</Label>
                            <select id="status_dosen" v-model="form.status_dosen" class="mt-1 block w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" required>
                                <option value="">Pilih Status Dosen</option>
                                <option value="Tetap" :selected="form.status_dosen === 'Tetap'">Tetap</option>
                                <option value="Tidak Tetap" :selected="form.status_dosen === 'Tidak Tetap'">Tidak Tetap</option>
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
                            <Button type="submit" :disabled="form.processing">Perbarui</Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
