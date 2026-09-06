<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { useRoutePrefix } from '@/composables/useRoutePrefix.js';

const prefix = useRoutePrefix();

const props = defineProps({
    fungsional: Object,
});

const form = useForm({
    nama_fungsional: props.fungsional.nama_fungsional,
    angka_kredit: props.fungsional.angka_kredit,
    pangkat: props.fungsional.pangkat ?? '',
    golongan: props.fungsional.golongan ?? '',
});

function submit() {
    form.put(route(`${prefix.value}.fungsional.update`, props.fungsional.id));
}
</script>

<template>
    <Head title="Edit Fungsional" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Edit Fungsional</h2>
                <Link :href="route(`${prefix}.fungsional.index`)" class="text-sm text-gray-600 hover:underline">Kembali</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <InputLabel for="nama_fungsional" value="Nama Fungsional" />
                            <TextInput id="nama_fungsional" v-model="form.nama_fungsional" type="text" class="mt-1 block w-full" required />
                            <InputError :message="form.errors.nama_fungsional" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="angka_kredit" value="Angka Kredit" />
                            <TextInput id="angka_kredit" v-model="form.angka_kredit" type="number" min="0" step="0.01" class="mt-1 block w-full" required />
                            <InputError :message="form.errors.angka_kredit" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="pangkat" value="Pangkat" />
                            <TextInput id="pangkat" v-model="form.pangkat" type="text" class="mt-1 block w-full" />
                            <InputError :message="form.errors.pangkat" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="golongan" value="Golongan" />
                            <TextInput id="golongan" v-model="form.golongan" type="text" class="mt-1 block w-full" />
                            <InputError :message="form.errors.golongan" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <Link :href="route(`${prefix}.fungsional.index`)" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">Batal</Link>
                            <PrimaryButton :disabled="form.processing">Perbarui</PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
