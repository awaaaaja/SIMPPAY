<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Daftar" />

        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900">Buat Akun Baru</h2>
            <p class="text-sm text-gray-500 mt-1">Daftar untuk mengakses SIMPPAY</p>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div class="space-y-2">
                <Label for="name">Nama Lengkap</Label>
                <Input
                    id="name"
                    v-model="form.name"
                    type="text"
                    placeholder="Masukkan nama lengkap"
                    required
                    autofocus
                    autocomplete="name"
                />
                <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
            </div>

            <div class="space-y-2">
                <Label for="email">Email</Label>
                <Input
                    id="email"
                    v-model="form.email"
                    type="email"
                    placeholder="Masukkan email"
                    required
                    autocomplete="username"
                />
                <p v-if="form.errors.email" class="text-sm text-destructive">{{ form.errors.email }}</p>
            </div>

            <div class="space-y-2">
                <Label for="password">Password</Label>
                <Input
                    id="password"
                    v-model="form.password"
                    type="password"
                    placeholder="Buat password"
                    required
                    autocomplete="new-password"
                />
                <p v-if="form.errors.password" class="text-sm text-destructive">{{ form.errors.password }}</p>
            </div>

            <div class="space-y-2">
                <Label for="password_confirmation">Konfirmasi Password</Label>
                <Input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    placeholder="Ulangi password"
                    required
                    autocomplete="new-password"
                />
                <p v-if="form.errors.password_confirmation" class="text-sm text-destructive">{{ form.errors.password_confirmation }}</p>
            </div>

            <Button type="submit" class="w-full bg-[#025AB1] hover:bg-[#014A96] text-white" :disabled="form.processing">
                Daftar
            </Button>

            <p class="text-center text-sm text-gray-500">
                Sudah punya akun?
                <Link :href="route('login')" class="text-[#025AB1] hover:underline font-medium">Masuk</Link>
            </p>
        </form>
    </GuestLayout>
</template>
