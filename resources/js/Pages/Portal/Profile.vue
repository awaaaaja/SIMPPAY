<script setup>
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import { User } from '@lucide/vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';

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
                <h1 class="text-xl font-semibold text-foreground">Profile</h1>
                <p class="text-sm text-muted-foreground mt-1">Kelola akun dan password Anda</p>
            </div>

            <!-- User Info Card -->
            <div class="bg-white rounded-[16px] p-6 mb-6"
                style="box-shadow: 0 1px 2px rgba(0,0,0,.04), 0 8px 24px rgba(0,0,0,.04);">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10">
                        <User class="h-6 w-6 text-primary" :stroke-width="1.75" />
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-foreground">{{ user.name }}</p>
                        <p class="text-xs text-muted-foreground">{{ user.email }}</p>
                    </div>
                </div>
            </div>

            <!-- Password Form -->
            <div class="bg-white rounded-[16px] p-6"
                style="box-shadow: 0 1px 2px rgba(0,0,0,.04), 0 8px 24px rgba(0,0,0,.04);">
                <h2 class="text-sm font-semibold text-foreground mb-4">Ganti Password</h2>

                <form @submit.prevent="updatePassword" class="space-y-4">
                    <div class="space-y-1">
                        <Label for="current_password">Password Saat Ini</Label>
                        <Input id="current_password" ref="currentPasswordInput"
                            v-model="form.current_password" type="password" autocomplete="current-password" />
                        <p v-if="form.errors.current_password" class="text-sm text-destructive">{{ form.errors.current_password }}</p>
                    </div>

                    <div class="space-y-1">
                        <Label for="password">Password Baru</Label>
                        <Input id="password" ref="passwordInput"
                            v-model="form.password" type="password" autocomplete="new-password" />
                        <p v-if="form.errors.password" class="text-sm text-destructive">{{ form.errors.password }}</p>
                    </div>

                    <div class="space-y-1">
                        <Label for="password_confirmation">Konfirmasi Password Baru</Label>
                        <Input id="password_confirmation"
                            v-model="form.password_confirmation" type="password" autocomplete="new-password" />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button type="submit" :disabled="form.processing">Simpan</Button>
                        <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                            <p v-if="form.recentlySuccessful" class="text-sm text-muted-foreground">Tersimpan.</p>
                        </Transition>
                    </div>
                </form>
            </div>
        </div>
    </PortalLayout>
</template>
