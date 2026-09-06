<script setup>
import { ref, computed } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
    LayoutDashboard,
    Users,
    Briefcase,
    CreditCard,
    FileText,
    BarChart3,
    Settings,
    ChevronLeft,
    Menu,
} from '@lucide/vue';

const page = usePage();
const showingNavigationDropdown = ref(false);
const sidebarCollapsed = ref(localStorage.getItem('sidebar-collapsed') === 'true');

function toggleSidebar() {
    sidebarCollapsed.value = !sidebarCollapsed.value;
    localStorage.setItem('sidebar-collapsed', sidebarCollapsed.value);
}

const prefix = computed(() => {
    const roles = page.props.auth?.roles || [];
    if (roles.includes('admin')) return 'admin';
    if (roles.includes('bpsdm')) return 'bpsdm';
    return 'admin';
});

const sidebarNav = computed(() => [
    {
        heading: 'OVERVIEW',
        items: [
            { label: 'Dashboard', route: `${prefix.value}.dashboard`, icon: LayoutDashboard },
        ],
    },
    {
        heading: 'PEOPLE',
        items: [
            { label: 'Pegawai', route: `${prefix.value}.pegawai.index`, icon: Users },
            { label: 'Jabatan', route: `${prefix.value}.jabatan.index`, icon: Briefcase },
            { label: 'Struktural', route: `${prefix.value}.struktural.index`, icon: Settings },
            { label: 'Fungsional', route: `${prefix.value}.fungsional.index`, icon: Settings },
        ],
    },
    {
        heading: 'ATTENDANCE',
        items: [
            { label: 'Kehadiran', route: `${prefix.value}.kehadiran.index`, icon: BarChart3 },
        ],
    },
    {
        heading: 'PAYROLL',
        items: [
            { label: 'Potongan Gaji', route: `${prefix.value}.potongan-gaji.index`, icon: CreditCard },
            { label: 'Tunjangan Gaji', route: `${prefix.value}.tunjangan-gaji.index`, icon: CreditCard },
            { label: 'Proses Payroll', route: `${prefix.value}.payroll-run.index`, icon: FileText },
        ],
    },
]);

function isActive(routeName) {
    try {
        return route().current(routeName);
    } catch {
        return false;
    }
}

const logoRoute = computed(() => route(`${prefix.value}.dashboard`));
</script>

<template>
    <div class="min-h-screen" style="background-color: var(--simppay-bg, #F6F7F4)">
        <!-- Mobile sidebar overlay -->
        <div
            v-if="showingNavigationDropdown"
            class="fixed inset-0 z-40 bg-black/30 lg:hidden"
            @click="showingNavigationDropdown = false"
        />

        <!-- Sidebar -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-50 flex flex-col bg-white border-r border-gray-100 transition-all duration-200',
                sidebarCollapsed ? 'w-[68px]' : 'w-60',
                showingNavigationDropdown
                    ? 'translate-x-0'
                    : '-translate-x-full lg:translate-x-0',
            ]"
        >
            <!-- Logo -->
            <div class="flex h-16 items-center gap-2.5 px-4 border-b border-gray-100">
                <Link :href="logoRoute" class="flex items-center gap-2.5 shrink-0">
                    <div class="flex h-8 w-8 items-center justify-center rounded-[8px] bg-[#176B5B]">
                        <span class="text-white text-sm font-bold">S</span>
                    </div>
                    <span
                        v-if="!sidebarCollapsed"
                        class="text-sm font-semibold text-gray-800 tracking-tight"
                    >
                        SIMPPAY
                    </span>
                </Link>
                <button
                    @click="toggleSidebar"
                    class="ml-auto hidden lg:flex items-center justify-center h-7 w-7 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition"
                >
                    <ChevronLeft
                        :class="['h-4 w-4 transition-transform', sidebarCollapsed && 'rotate-180']"
                    />
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-5">
                <div v-for="section in sidebarNav" :key="section.heading">
                    <p
                        v-if="!sidebarCollapsed"
                        class="px-2 mb-1.5 text-[10px] font-semibold uppercase tracking-wider text-gray-400"
                    >
                        {{ section.heading }}
                    </p>
                    <div class="space-y-0.5">
                        <Link
                            v-for="item in section.items"
                            :key="item.route"
                            :href="route(item.route)"
                            :class="[
                                'group flex items-center gap-2.5 rounded-[10px] px-2.5 py-2 text-sm font-medium transition-all duration-150',
                                isActive(item.route)
                                    ? 'bg-[#176B5B]/8 text-[#176B5B]'
                                    : 'text-gray-600 hover:bg-gray-50 hover:text-gray-800',
                            ]"
                            :title="sidebarCollapsed ? item.label : undefined"
                        >
                            <div
                                :class="[
                                    'relative flex h-8 w-8 items-center justify-center rounded-[8px] shrink-0 transition',
                                    isActive(item.route)
                                        ? 'bg-[#176B5B]/10'
                                        : 'bg-transparent group-hover:bg-gray-100',
                                ]"
                            >
                                <!-- Active indicator -->
                                <div
                                    v-if="isActive(item.route)"
                                    class="absolute -left-[13px] top-1/2 -translate-y-1/2 h-5 w-[3px] rounded-r-full bg-[#176B5B]"
                                />
                                <component
                                    :is="item.icon"
                                    :class="[
                                        'h-[18px] w-[18px]',
                                        isActive(item.route)
                                            ? 'text-[#176B5B]'
                                            : 'text-gray-400 group-hover:text-gray-600',
                                    ]"
                                    :stroke-width="1.75"
                                />
                            </div>
                            <span v-if="!sidebarCollapsed">{{ item.label }}</span>
                        </Link>
                    </div>
                </div>
            </nav>

            <!-- User -->
            <div class="border-t border-gray-100 px-3 py-3">
                <Dropdown align="top" width="48">
                    <template #trigger>
                        <button
                            :class="[
                                'flex items-center gap-2.5 w-full rounded-[10px] px-2.5 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 transition',
                                sidebarCollapsed && 'justify-center',
                            ]"
                        >
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-full bg-[#176B5B]/10 text-[#176B5B] text-xs font-semibold shrink-0"
                            >
                                {{ $page.props.auth.user.name?.charAt(0) }}
                            </div>
                            <span v-if="!sidebarCollapsed" class="truncate">
                                {{ $page.props.auth.user.name }}
                            </span>
                        </button>
                    </template>
                    <template #content>
                        <DropdownLink :href="route('profile.edit')">
                            Profile
                        </DropdownLink>
                        <DropdownLink
                            :href="route('logout')"
                            method="post"
                            as="button"
                        >
                            Log Out
                        </DropdownLink>
                    </template>
                </Dropdown>
            </div>
        </aside>

        <!-- Main content -->
        <div
            :class="[
                'transition-all duration-200',
                sidebarCollapsed ? 'lg:ml-[68px]' : 'lg:ml-60',
            ]"
        >
            <!-- Top bar (mobile) -->
            <div class="sticky top-0 z-30 flex h-14 items-center gap-3 border-b border-gray-100 bg-white px-4 lg:hidden">
                <button
                    @click="showingNavigationDropdown = !showingNavigationDropdown"
                    class="flex items-center justify-center h-9 w-9 rounded-md text-gray-500 hover:bg-gray-100"
                >
                    <Menu class="h-5 w-5" />
                </button>
                <Link :href="logoRoute" class="flex items-center gap-2">
                    <div class="flex h-7 w-7 items-center justify-center rounded-[6px] bg-[#176B5B]">
                        <span class="text-white text-xs font-bold">S</span>
                    </div>
                    <span class="text-sm font-semibold text-gray-800">SIMPPAY</span>
                </Link>
            </div>

            <!-- Page content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
