<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import {
    LayoutDashboard,
    FileText,
    Clock,
    User,
    LogOut,
    Menu,
    X,
} from '@lucide/vue';

const page = usePage();
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
    <div class="min-h-screen" style="background-color: var(--simppay-bg, #F6F7F4)">
        <!-- Desktop Sidebar -->
        <div class="hidden lg:fixed lg:inset-y-0 lg:flex lg:w-64 lg:flex-col">
            <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-white border-r border-gray-100 px-6 pb-4">
                <div class="flex h-16 shrink-0 items-center gap-2">
                    <ApplicationLogo class="block h-8 w-auto fill-current text-[#025AB1]" />
                    <span class="text-sm font-semibold text-gray-800">SIMPPAY</span>
                </div>
                <nav class="flex flex-1 flex-col">
                    <ul role="list" class="flex flex-1 flex-col gap-y-1">
                        <li v-for="item in navigation" :key="item.route">
                            <Link
                                :href="route(item.route)"
                                :class="[
                                    isActive(item.route)
                                        ? 'bg-[#025AB1]/10 text-[#025AB1] font-semibold'
                                        : 'text-gray-600 hover:bg-gray-50 hover:text-gray-800',
                                    'group flex gap-x-3 rounded-[10px] p-2.5 text-sm leading-6 transition-colors',
                                ]"
                            >
                                <component
                                    :is="item.icon"
                                    :class="[
                                        isActive(item.route) ? 'text-[#025AB1]' : 'text-gray-400 group-hover:text-gray-600',
                                        'h-5 w-5 shrink-0',
                                    ]"
                                    :stroke-width="1.75"
                                />
                                {{ item.name }}
                            </Link>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>

        <!-- Mobile Header -->
        <div class="lg:hidden sticky top-0 z-40 flex h-14 items-center gap-x-4 border-b border-gray-100 bg-white px-4 shadow-sm">
            <button
                @click="mobileMenuOpen = !mobileMenuOpen"
                class="inline-flex items-center justify-center rounded-[10px] p-2 text-gray-500 hover:bg-gray-100"
            >
                <Menu v-if="!mobileMenuOpen" class="h-5 w-5" :stroke-width="1.75" />
                <X v-else class="h-5 w-5" :stroke-width="1.75" />
            </button>
            <div class="flex items-center gap-2">
                <ApplicationLogo class="h-7 w-auto fill-current text-[#025AB1]" />
                <span class="text-sm font-semibold text-gray-800">SIMPPAY</span>
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
                <div
                    class="fixed inset-y-0 left-0 w-64 bg-white shadow-lg z-50"
                    @click.stop
                >
                    <div class="flex h-14 items-center gap-2 border-b border-gray-100 px-6">
                        <ApplicationLogo class="h-7 w-auto fill-current text-[#025AB1]" />
                        <span class="text-sm font-semibold text-gray-800">SIMPPAY</span>
                    </div>
                    <nav class="flex flex-1 flex-col px-4 py-4">
                        <ul role="list" class="flex flex-1 flex-col gap-y-1">
                            <li v-for="item in navigation" :key="item.route">
                                <Link
                                    :href="route(item.route)"
                                    @click="mobileMenuOpen = false"
                                    :class="[
                                        isActive(item.route)
                                            ? 'bg-[#025AB1]/10 text-[#025AB1] font-semibold'
                                            : 'text-gray-600 hover:bg-gray-50 hover:text-gray-800',
                                        'group flex gap-x-3 rounded-[10px] p-2.5 text-sm leading-6 transition-colors',
                                    ]"
                                >
                                    <component
                                        :is="item.icon"
                                        :class="[
                                            isActive(item.route) ? 'text-[#025AB1]' : 'text-gray-400 group-hover:text-gray-600',
                                            'h-5 w-5 shrink-0',
                                        ]"
                                        :stroke-width="1.75"
                                    />
                                    {{ item.name }}
                                </Link>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </Transition>

        <!-- Main content area -->
        <div class="lg:pl-64">
            <!-- Desktop top bar -->
            <div class="hidden lg:flex lg:h-16 lg:items-center lg:justify-end lg:border-b lg:border-gray-100 lg:bg-white lg:px-8">
                <div class="relative ms-3">
                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <span class="inline-flex rounded-md">
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-[10px] border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none"
                                >
                                    {{ $page.props.auth.user.name }}
                                    <svg class="-me-0.5 ms-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </span>
                        </template>
                        <template #content>
                            <DropdownLink :href="route('portal.profile')">
                                Profile
                            </DropdownLink>
                            <DropdownLink :href="route('logout')" method="post" as="button">
                                Log Out
                            </DropdownLink>
                        </template>
                    </Dropdown>
                </div>
            </div>

            <!-- Page content -->
            <main class="pb-20 lg:pb-0">
                <slot />
            </main>
        </div>

        <!-- Mobile Bottom Navigation — §35 -->
        <nav class="fixed inset-x-0 bottom-0 z-40 border-t border-gray-100 bg-white lg:hidden">
            <ul role="list" class="flex items-center justify-around">
                <li v-for="item in bottomNav" :key="item.route">
                    <Link
                        :href="route(item.route)"
                        :class="[
                            isActive(item.route)
                                ? 'text-[#025AB1]'
                                : 'text-gray-400',
                            'flex flex-col items-center gap-0.5 px-3 py-2 text-[10px] font-medium',
                        ]"
                    >
                        <component
                            :is="item.icon"
                            :class="[isActive(item.route) ? 'text-[#025AB1]' : 'text-gray-400', 'h-5 w-5']"
                            :stroke-width="1.75"
                        />
                        {{ item.name }}
                    </Link>
                </li>
            </ul>
        </nav>
    </div>
</template>
