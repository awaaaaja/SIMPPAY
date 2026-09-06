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
    struktural: Object,
});

const form = useForm({
    nama_struktural: props.struktural.nama_struktural,
    level_struktural: props.struktural.level_struktural ?? '',
    status: props.struktural.status,
});

function submit() {
    form.put(route(`${prefix.value}.struktural.update`, props.struktural.id));
}
</script>

<template>
    <Head title="Edit Struktural" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Edit Struktural</h2>
                <Link :href="route(`${prefix}.struktural.index`)" class="text-sm text-gray-600 hover:underline">Kembali</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <InputLabel for="nama_struktural" value="Nama Struktural" />
                            <TextInput id="nama_struktural" v-model="form.nama_struktural" type="text" class="mt-1 block w-full" required />
                            <InputError :message="form.errors.nama_struktural" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="level_struktural" value="Level" />
                            <TextInput id="level_struktural" v-model="form.level_struktural" type="text" class="mt-1 block w-full" />
                            <InputError :message="form.errors.level_struktural" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="status" value="Status" />
                            <select id="status" v-model="form.status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#025AB1] focus:ring-[#025AB1]">
                                <option value="aktif">Aktif</option>
                                <option value="nonaktif">Nonaktif</option>
                            </select>
                            <InputError :message="form.errors.status" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <Link :href="route(`${prefix}.struktural.index`)" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">Batal</Link>
                            <PrimaryButton :disabled="form.processing">Perbarui</PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
