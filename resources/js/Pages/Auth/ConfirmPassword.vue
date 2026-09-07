<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({ password: '' });

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Konfirmasi Password" />

        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900">Konfirmasi Password</h2>
            <p class="text-sm text-gray-500 mt-1">
                Ini adalah area aman dari aplikasi. Silakan masukkan password Anda untuk melanjutkan.
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div class="space-y-2">
                <Label for="password">Password</Label>
                <Input
                    id="password"
                    v-model="form.password"
                    type="password"
                    placeholder="Masukkan password"
                    required
                    autofocus
                    autocomplete="current-password"
                />
                <p v-if="form.errors.password" class="text-sm text-destructive">{{ form.errors.password }}</p>
            </div>

            <Button type="submit" class="w-full bg-[#025AB1] hover:bg-[#014A96] text-white" :disabled="form.processing">
                Konfirmasi
            </Button>
        </form>
    </GuestLayout>
</template>
