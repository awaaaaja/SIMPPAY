<script setup>
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="SIMPPAY" />
    <div class="flex min-h-screen">
        <!-- Left branding panel -->
        <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden bg-[#025AB1]">
            <!-- Subtle pattern overlay -->
            <div class="absolute inset-0 opacity-[0.07]"
                style="background-image: url(&quot;data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E&quot;)" />
            <div class="relative z-10 flex flex-col items-center justify-center w-full px-12 text-white">
                <img src="/images/logo-simppay.png" alt="SIMPPAY" class="w-28 h-28 mb-8 drop-shadow-lg" />
                <h1 class="text-4xl font-bold tracking-tight mb-3">SIMPPAY</h1>
                <p class="text-lg font-medium text-blue-100 mb-2">Sistem Informasi Penggajian</p>
                <p class="text-sm text-blue-200/80 text-center max-w-xs leading-relaxed">Universitas Adzkia</p>
                <div class="mt-12 grid grid-cols-3 gap-6 text-center">
                    <div>
                        <p class="text-2xl font-bold">100%</p>
                        <p class="text-xs text-blue-200/70 mt-1">Akurat</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">Real-time</p>
                        <p class="text-xs text-blue-200/70 mt-1">Data</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">24/7</p>
                        <p class="text-xs text-blue-200/70 mt-1">Akses</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right form panel -->
        <div class="flex w-full lg:w-1/2 items-center justify-center px-6 py-12 bg-[#F6F7F4]">
            <div class="w-full max-w-md">
                <!-- Mobile logo -->
                <div class="lg:hidden flex items-center gap-3 mb-8">
                    <img src="/images/logo-simppay.png" alt="SIMPPAY" class="w-10 h-10" />
                    <span class="text-xl font-bold text-[#025AB1]">SIMPPAY</span>
                </div>

                <slot />
            </div>
        </div>
    </div>
</template>
