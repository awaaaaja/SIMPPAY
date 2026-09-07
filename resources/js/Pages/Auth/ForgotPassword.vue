<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({ status: String });

const form = useForm({ email: '' });

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Lupa Password" />

        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900">Lupa Password?</h2>
            <p class="text-sm text-gray-500 mt-1">
                Masukkan email Anda dan kami akan mengirimkan tautan untuk mengatur ulang password.
            </p>
        </div>

        <div v-if="status" class="mb-4 rounded-lg bg-green-50 border border-green-200 p-3 text-sm text-green-700">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div class="space-y-2">
                <Label for="email">Email</Label>
                <Input
                    id="email"
                    v-model="form.email"
                    type="email"
                    placeholder="Masukkan email terdaftar"
                    required
                    autofocus
                    autocomplete="username"
                />
                <p v-if="form.errors.email" class="text-sm text-destructive">{{ form.errors.email }}</p>
            </div>

            <Button type="submit" class="w-full bg-[#025AB1] hover:bg-[#014A96] text-white" :disabled="form.processing">
                Kirim Tautan Reset Password
            </Button>

            <p class="text-center text-sm text-gray-500">
                <Link :href="route('login')" class="text-[#025AB1] hover:underline font-medium">Kembali ke halaman masuk</Link>
            </p>
        </form>
    </GuestLayout>
</template>
