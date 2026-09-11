<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import {
    LayoutDashboard,
    FileText,
    Clock,
    User,
    LogOut,
    Menu,
    X,
} from '@lucide/vue';

const mobileMenuOpen = ref(false);

const navigation = [
    { name: 'Dashboard', route: 'portal.dashboard', icon: LayoutDashboard },
    { name: 'Slip Gaji', route: 'portal.slip.gaji', icon: FileText },
    { name: 'Riwayat Absensi', route: 'portal.riwayat.absensi', icon: Clock },
];

const bottomNav = [
    { name: 'Home', route: 'portal.dashboard', icon: LayoutDashboard },
    { name: 'Slip', route: 'portal.slip.gaji', icon: FileText },
    { name: 'History', route: 'portal.riwayat.absensi', icon: Clock },
    { name: 'Profile', route: 'portal.profile', icon: User },
];

function isActive(routeName) {
    return route().current(routeName);
}
</script>

<template>
    <div class="min-h-screen" style="background-color: var(--simppay-bg)">
        <!-- Desktop Sidebar -->
        <div class="hidden lg:fixed lg:inset-y-0 lg:flex lg:w-60 lg:flex-col">
            <div class="flex grow flex-col overflow-y-auto bg-white border-r px-3 pb-3">
                <div class="flex h-14 shrink-0 items-center gap-2 px-1 border-b">
                    <img src="/images/logo-simppay.png" alt="SIMPPAY" class="h-7 w-7 rounded-md object-cover" />
                    <span class="text-sm font-semibold" style="color: var(--simppay-text)">SIMPPAY</span>
                </div>
                <nav class="flex flex-1 flex-col mt-3">
                    <ul role="list" class="flex flex-1 flex-col gap-y-0.5">
                        <li v-for="item in navigation" :key="item.route">
                            <Link
                                :href="route(item.route)"
                                :class="[
                                    isActive(item.route)
                                        ? 'bg-primary/8 text-primary'
                                        : 'text-gray-600 hover:bg-gray-50',
                                    'group flex items-center gap-2.5 rounded-lg px-2.5 py-1.5 text-[13px] font-medium transition-colors',
                                ]"
                            >
                                <div
                                    :class="[
                                        'flex h-7 w-7 items-center justify-center rounded-md shrink-0 transition',
                                        isActive(item.route) ? 'bg-primary/10' : 'bg-transparent group-hover:bg-gray-100',
                                    ]"
                                >
                                    <component
                                        :is="item.icon"
                                        :class="['h-4 w-4', isActive(item.route) ? 'text-primary' : 'text-gray-400 group-hover:text-gray-600']"
                                        :stroke-width="1.75"
                                    />
                                </div>
                                {{ item.name }}
                            </Link>
                        </li>
                    </ul>
                </nav>
                <div class="border-t pt-2 mt-2">
                    <Link :href="route('logout')" method="post" as="button"
                        class="flex items-center gap-2.5 rounded-lg px-2.5 py-1.5 text-[13px] font-medium text-red-600 hover:bg-red-50 w-full transition-colors">
                        <div class="flex h-7 w-7 items-center justify-center rounded-full bg-red-50 shrink-0">
                            <LogOut class="h-4 w-4 text-red-500" :stroke-width="1.75" />
                        </div>
                        Keluar
                    </Link>
                </div>
            </div>
        </div>

        <!-- Mobile Header -->
        <div class="lg:hidden sticky top-0 z-40 flex h-14 items-center justify-between border-b bg-white px-4">
            <div class="flex items-center gap-2">
                <button
                    @click="mobileMenuOpen = !mobileMenuOpen"
                    class="inline-flex items-center justify-center rounded-md p-1.5 text-gray-500 hover:bg-gray-100"
                >
                    <Menu v-if="!mobileMenuOpen" class="h-4 w-4" :stroke-width="1.75" />
                    <X v-else class="h-4 w-4" :stroke-width="1.75" />
                </button>
                <span class="text-sm font-semibold" style="color: var(--simppay-text)">SIMPPAY</span>
            </div>
        </div>

        <!-- Mobile slide-over menu -->
        <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            leave-active-class="transition-opacity duration-200"
            leave-to-class="opacity-0"
        >
            <div v-if="mobileMenuOpen" class="fixed inset-0 z-50 lg:hidden" @click="mobileMenuOpen = false">
                <div class="fixed inset-0 bg-black/20" />
                <div class="fixed inset-y-0 left-0 w-60 bg-white shadow-lg z-50" @click.stop>
                    <div class="flex h-14 items-center gap-2 border-b px-4">
                        <img src="/images/logo-simppay.png" alt="SIMPPAY" class="h-7 w-7 rounded-md object-cover" />
                        <span class="text-sm font-semibold" style="color: var(--simppay-text)">SIMPPAY</span>
                    </div>
                    <nav class="flex flex-col px-2.5 py-3">
                        <ul role="list" class="flex flex-col gap-y-0.5">
                            <li v-for="item in navigation" :key="item.route">
                                <Link
                                    :href="route(item.route)"
                                    @click="mobileMenuOpen = false"
                                    :class="[
                                        isActive(item.route)
                                            ? 'bg-primary/8 text-primary'
                                            : 'text-gray-600 hover:bg-gray-50',
                                        'group flex items-center gap-2.5 rounded-lg px-2.5 py-1.5 text-[13px] font-medium transition-colors',
                                    ]"
                                >
                                    <div
                                        :class="[
                                            'flex h-7 w-7 items-center justify-center rounded-md shrink-0 transition',
                                            isActive(item.route) ? 'bg-primary/10' : 'bg-transparent group-hover:bg-gray-100',
                                        ]"
                                    >
                                        <component
                                            :is="item.icon"
                                            :class="['h-4 w-4', isActive(item.route) ? 'text-primary' : 'text-gray-400']"
                                            :stroke-width="1.75"
                                        />
                                    </div>
                                    {{ item.name }}
                                </Link>
                            </li>
                        </ul>
                        <div class="mt-auto pt-3 border-t">
                            <Link :href="route('logout')" method="post" as="button"
                                class="flex items-center gap-2.5 rounded-lg px-2.5 py-1.5 text-[13px] font-medium text-red-600 hover:bg-red-50 w-full transition-colors">
                                <div class="flex h-7 w-7 items-center justify-center rounded-full bg-red-50 shrink-0">
                                    <LogOut class="h-4 w-4 text-red-500" :stroke-width="1.75" />
                                </div>
                                Keluar
                            </Link>
                        </div>
                    </nav>
                </div>
            </div>
        </Transition>

        <!-- Main content area -->
        <div class="lg:pl-60">
            <!-- Desktop top bar -->
            <div class="hidden lg:flex lg:h-14 lg:items-center lg:justify-end lg:border-b lg:bg-white lg:px-6">
                <div class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10">
                        <span class="text-xs font-semibold text-primary">
                            {{ $page.props.auth.user?.name?.charAt(0)?.toUpperCase() || 'U' }}
                        </span>
                    </div>
                    <span class="text-sm font-medium" style="color: var(--simppay-text)">{{ $page.props.auth.user?.name }}</span>
                </div>
            </div>

            <!-- Page content -->
            <main class="pb-20 lg:pb-0">
                <slot />
            </main>
        </div>

        <!-- Mobile Bottom Navigation -->
        <nav class="fixed inset-x-0 bottom-0 z-40 border-t bg-white lg:hidden">
            <ul role="list" class="flex items-center justify-around">
                <li v-for="item in bottomNav" :key="item.route">
                    <Link
                        :href="route(item.route)"
                        :class="[
                            isActive(item.route) ? 'text-primary' : 'text-gray-400',
                            'flex flex-col items-center gap-0.5 px-3 py-2 text-[10px] font-medium',
                        ]"
                    >
                        <component
                            :is="item.icon"
                            :class="[isActive(item.route) ? 'text-primary' : 'text-gray-400', 'h-5 w-5']"
                            :stroke-width="1.75"
                        />
                        {{ item.name }}
                    </Link>
                </li>
            </ul>
        </nav>
    </div>
</template>
