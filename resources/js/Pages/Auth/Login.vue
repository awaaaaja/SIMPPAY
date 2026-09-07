<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    username: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Masuk" />

        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900">Selamat Datang</h2>
            <p class="text-sm text-gray-500 mt-1">Masuk ke akun SIMPPAY Anda</p>
        </div>

        <div v-if="status" class="mb-4 rounded-lg bg-green-50 border border-green-200 p-3 text-sm text-green-700">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div class="space-y-2">
                <Label for="username">Username</Label>
                <Input
                    id="username"
                    v-model="form.username"
                    type="text"
                    placeholder="Masukkan username"
                    required
                    autofocus
                    autocomplete="username"
                />
                <p v-if="form.errors.username" class="text-sm text-destructive">{{ form.errors.username }}</p>
            </div>

            <div class="space-y-2">
                <Label for="password">Password</Label>
                <Input
                    id="password"
                    v-model="form.password"
                    type="password"
                    placeholder="Masukkan password"
                    required
                    autocomplete="current-password"
                />
                <p v-if="form.errors.password" class="text-sm text-destructive">{{ form.errors.password }}</p>
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" v-model="form.remember" class="rounded border-gray-300 text-[#025AB1] focus:ring-[#025AB1]" />
                    <span class="text-sm text-gray-600">Ingat saya</span>
                </label>

                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-sm text-[#025AB1] hover:underline font-medium"
                >
                    Lupa password?
                </Link>
            </div>

            <Button type="submit" class="w-full bg-[#025AB1] hover:bg-[#014A96] text-white" :disabled="form.processing">
                Masuk
            </Button>
        </form>
    </GuestLayout>
</template>
