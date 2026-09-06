<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
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
                <Link :href="route(`${prefix}.jabatan.index`)" class="text-sm text-gray-600 hover:underline">
                    Kembali
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <InputLabel for="nama_jabatan" value="Nama Jabatan" />
                            <TextInput
                                id="nama_jabatan"
                                v-model="form.nama_jabatan"
                                type="text"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="form.errors.nama_jabatan" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="gaji_pokok" value="Gaji Pokok" />
                            <TextInput
                                id="gaji_pokok"
                                v-model="form.gaji_pokok"
                                type="number"
                                min="0"
                                step="1000"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="form.errors.gaji_pokok" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="tj_transport" value="Tunjangan Transport" />
                            <TextInput
                                id="tj_transport"
                                v-model="form.tj_transport"
                                type="number"
                                min="0"
                                step="1000"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="form.errors.tj_transport" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="uang_makan" value="Uang Makan" />
                            <TextInput
                                id="uang_makan"
                                v-model="form.uang_makan"
                                type="number"
                                min="0"
                                step="1000"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="form.errors.uang_makan" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <Link
                                :href="route(`${prefix}.jabatan.index`)"
                                class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50"
                            >
                                Batal
                            </Link>
                            <PrimaryButton :disabled="form.processing">
                                Simpan
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
