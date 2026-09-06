<script setup>
import PortalLayout from '@/Layouts/PortalLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import { User } from '@lucide/vue';

const props = defineProps({
    user: Object,
});

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('portal.profile.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value.focus();
            }
        },
    });
};
</script>

<template>
    <Head title="Profile" />

    <PortalLayout>
        <div class="px-4 py-8 sm:px-6 lg:px-8 max-w-2xl mx-auto">
            <div class="mb-6">
                <h1 class="text-xl font-semibold text-gray-800">Profile</h1>
                <p class="text-sm text-gray-500 mt-1">Kelola akun dan password Anda</p>
            </div>

            <!-- User Info Card -->
            <div class="bg-white rounded-[16px] shadow-[0_1px_2px_rgba(0,0,0,.04),0_8px_24px_rgba(0,0,0,.04)] p-6 mb-6">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-[#025AB1]/10">
                        <User class="h-6 w-6 text-[#025AB1]" :stroke-width="1.75" />
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-800">{{ user.name }}</p>
                        <p class="text-xs text-gray-500">{{ user.email }}</p>
                    </div>
                </div>
            </div>

            <!-- Password Form -->
            <div class="bg-white rounded-[16px] shadow-[0_1px_2px_rgba(0,0,0,.04),0_8px_24px_rgba(0,0,0,.04)] p-6">
                <h2 class="text-sm font-semibold text-gray-800 mb-4">Ganti Password</h2>

                <form @submit.prevent="updatePassword" class="space-y-4">
                    <div>
                        <InputLabel for="current_password" value="Password Saat Ini" />
                        <TextInput
                            id="current_password"
                            ref="currentPasswordInput"
                            v-model="form.current_password"
                            type="password"
                            class="mt-1 block w-full"
                            autocomplete="current-password"
                        />
                        <InputError :message="form.errors.current_password" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="password" value="Password Baru" />
                        <TextInput
                            id="password"
                            ref="passwordInput"
                            v-model="form.password"
                            type="password"
                            class="mt-1 block w-full"
                            autocomplete="new-password"
                        />
                        <InputError :message="form.errors.password" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="password_confirmation" value="Konfirmasi Password Baru" />
                        <TextInput
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            class="mt-1 block w-full"
                            autocomplete="new-password"
                        />
                        <InputError :message="form.errors.password_confirmation" class="mt-2" />
                    </div>

                    <div class="flex items-center gap-4">
                        <PrimaryButton :disabled="form.processing">Simpan</PrimaryButton>
                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p v-if="form.recentlySuccessful" class="text-sm text-gray-600">Tersimpan.</p>
                        </Transition>
                    </div>
                </form>
            </div>
        </div>
    </PortalLayout>
</template>
