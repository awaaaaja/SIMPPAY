<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Button } from '@/components/ui/button';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({ status: String });

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');
</script>

<template>
    <GuestLayout>
        <Head title="Verifikasi Email" />

        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900">Verifikasi Email</h2>
            <p class="text-sm text-gray-500 mt-1">
                Terima kasih telah mendaftar! Sebelum melanjutkan, silakan verifikasi alamat email Anda dengan mengklik tautan yang kami kirimkan.
            </p>
        </div>

        <div v-if="verificationLinkSent" class="mb-4 rounded-lg bg-green-50 border border-green-200 p-3 text-sm text-green-700">
            Tautan verifikasi baru telah dikirim ke alamat email yang Anda daftarkan.
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <Button type="submit" class="w-full bg-[#025AB1] hover:bg-[#014A96] text-white" :disabled="form.processing">
                Kirim Ulang Email Verifikasi
            </Button>

            <div class="flex items-center justify-between text-sm">
                <Link :href="route('logout')" method="post" as="button" class="text-[#025AB1] hover:underline font-medium">
                    Keluar
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
