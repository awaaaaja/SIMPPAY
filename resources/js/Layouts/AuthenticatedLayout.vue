<script setup>
import { ref, computed } from 'vue';
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
    ChevronDown,
    Menu,
    User,
    LogOut,
    Award,
    BookOpen,
    Hash,
    Shield,
    Bell,
} from '@lucide/vue';

const page = usePage();
const showingNavigationDropdown = ref(false);
const userDropdownOpen = ref(false);
const sidebarCollapsed = ref(localStorage.getItem('sidebar-collapsed') === 'true');

function toggleSidebar() {
    sidebarCollapsed.value = !sidebarCollapsed.value;
    localStorage.setItem('sidebar-collapsed', sidebarCollapsed.value);
}

const currentUser = computed(() => page.props.auth?.user);
const currentRoles = computed(() => page.props.auth?.roles || []);

const prefix = computed(() => {
    if (currentRoles.value.includes('admin')) return 'admin';
    if (currentRoles.value.includes('bpsdm')) return 'bpsdm';
    return 'admin';
});

const roleLabel = computed(() => {
    if (currentRoles.value.includes('admin')) return 'Administrator';
    if (currentRoles.value.includes('bpsdm')) return 'BPSDM';
    if (currentRoles.value.includes('tendik')) return 'Tendik';
    return 'Pegawai';
});

const sidebarNav = computed(() => [
    {
        heading: 'OVERVIEW',
        items: [
            { label: 'Dashboard', route: `${prefix.value}.dashboard`, icon: LayoutDashboard },
        ],
    },
    {
        heading: 'KEPEGAWAIAN',
        items: [
            { label: 'Pegawai', route: `${prefix.value}.pegawai.index`, icon: Users },
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
            { label: 'Proses Payroll', route: `${prefix.value}.payroll-run.index`, icon: FileText },
            { label: 'Pengaturan Payroll', route: `${prefix.value}.payroll-settings.index`, icon: Settings, adminOnly: true },
            { label: 'Kebijakan Kompensasi', route: `${prefix.value}.kebijakan-kompensasi.index`, icon: Shield },
        ],
    },
    {
        heading: 'DATA REFERENSI',
        items: prefix.value === 'admin'
            ? [
                { label: 'Golongan Ruang', route: 'admin.golongan-ruang.index', icon: Hash },
                { label: 'Gaji Pokok Scale', route: 'admin.gaji-pokok-scale.index', icon: Hash },
                { label: 'Level Struktur', route: 'admin.level-struktur.index', icon: Hash },
                { label: 'Klasifikasi Jabatan', route: 'admin.klasifikasi-jabatan-struktural.index', icon: Briefcase },
                { label: 'Jabatan Struktural', route: 'admin.jabatan-struktural-poin.index', icon: Briefcase },
                { label: 'Tunj. Jabatan Karyawan', route: 'admin.tunjangan-jabatan-karyawan-scale.index', icon: Award },
                { label: 'Tunj. Fungsional Dosen', route: 'admin.tunjangan-fungsional-dosen-scale.index', icon: Award },
                { label: 'Tunjangan Variabel', route: 'admin.tunjangan-variabel-scale.index', icon: Award },
                { label: 'Tunjangan Transportasi', route: 'admin.tunjangan-transportasi-scale.index', icon: Award },
                { label: 'Honor SKS Scale', route: 'admin.honor-sks-scale.index', icon: BookOpen },
                { label: 'Beban SKS Jabatan', route: 'admin.beban-sks-jabatan.index', icon: BookOpen },
            ]
            : [
                { label: 'Golongan & Gaji Pokok', route: 'bpsdm.referensi.golongan-gaji-pokok', icon: Hash },
                { label: 'Jabatan Struktural', route: 'bpsdm.referensi.jabatan-struktural', icon: Briefcase },
                { label: 'Tunjangan Komponen', route: 'bpsdm.referensi.tunjangan-komponen', icon: Award },
                { label: 'Dosen & SKS', route: 'bpsdm.referensi.dosen-sks', icon: BookOpen },
            ],
    },
].map(section => ({
    ...section,
    items: section.items.filter(item => !item.adminOnly || prefix.value === 'admin'),
})));

const logoRoute = computed(() => route(`${prefix.value}.dashboard`));

function isActive(routeName) {
    try {
        return route().current(routeName);
    } catch {
        return false;
    }
}

function sectionHasActive(section) {
    return section.items.some(item => isActive(item.route));
}

const openSections = ref(new Set(
    sidebarNav.value.filter(s => sectionHasActive(s)).map(s => s.heading)
));

function toggleSection(heading) {
    if (sidebarCollapsed.value) return;
    const next = new Set(openSections.value);
    if (next.has(heading)) {
        next.delete(heading);
    } else {
        next.add(heading);
    }
    openSections.value = next;
}

function closeUserDropdown() {
    userDropdownOpen.value = false;
}
</script>

<template>
    <div class="min-h-screen" style="background-color: var(--simppay-bg)">
        <!-- Mobile sidebar overlay -->
        <div
            v-if="showingNavigationDropdown"
            class="fixed inset-0 z-40 bg-black/30 lg:hidden"
            @click="showingNavigationDropdown = false"
        />

        <!-- Sidebar -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-50 flex flex-col bg-white border-r transition-all duration-200',
                sidebarCollapsed ? 'w-[68px]' : 'w-60',
                showingNavigationDropdown
                    ? 'translate-x-0'
                    : '-translate-x-full lg:translate-x-0',
            ]"
        >
            <!-- Logo -->
            <div class="flex h-14 items-center gap-2.5 px-4 border-b">
                <Link :href="logoRoute" class="flex items-center gap-2.5 shrink-0">
                    <img src="/images/logo-simppay.png" alt="SIMPPAY" class="h-7 w-7 rounded-md object-cover" />
                    <span v-if="!sidebarCollapsed" class="text-sm font-semibold tracking-tight" style="color: var(--simppay-text)">
                        SIMPPAY
                    </span>
                </Link>
                <button
                    @click="toggleSidebar"
                    class="ml-auto hidden lg:flex items-center justify-center h-6 w-6 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition"
                >
                    <ChevronLeft :class="['h-3.5 w-3.5 transition-transform', sidebarCollapsed && 'rotate-180']" />
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto px-2.5 py-3">
                <div v-for="section in sidebarNav" :key="section.heading" class="mb-2">
                    <button
                        v-if="!sidebarCollapsed"
                        @click="toggleSection(section.heading)"
                        class="flex w-full items-center justify-between rounded-md px-2 py-1 text-[10px] font-semibold uppercase tracking-wider text-gray-400 hover:text-gray-500 transition-colors"
                    >
                        <span>{{ section.heading }}</span>
                        <ChevronDown
                            :class="[
                                'h-3 w-3 transition-transform duration-200',
                                openSections.has(section.heading) ? 'rotate-0' : '-rotate-90',
                            ]"
                        />
                    </button>
                    <p v-else class="px-2 mb-1 text-[10px] font-semibold uppercase tracking-wider text-gray-400 text-center">
                        {{ section.heading.charAt(0) }}
                    </p>

                    <Transition
                        enter-active-class="transition-all duration-200 ease-out"
                        leave-active-class="transition-all duration-150 ease-in"
                        enter-from-class="opacity-0 max-h-0"
                        enter-to-class="opacity-100 max-h-[500px]"
                        leave-from-class="opacity-100 max-h-[500px]"
                        leave-to-class="opacity-0 max-h-0"
                    >
                        <div
                            v-show="sidebarCollapsed || openSections.has(section.heading)"
                            class="space-y-0.5 overflow-hidden"
                        >
                            <Link
                                v-for="item in section.items"
                                :key="item.route"
                                :href="route(item.route)"
                                :class="[
                                    'group flex items-center gap-2.5 rounded-lg px-2.5 py-1.5 text-[13px] font-medium transition-all duration-150',
                                    isActive(item.route)
                                        ? 'bg-primary/8 text-primary'
                                        : 'text-gray-600 hover:bg-gray-50 hover:text-gray-800',
                                ]"
                                :title="sidebarCollapsed ? item.label : undefined"
                            >
                                <div
                                    :class="[
                                        'relative flex h-7 w-7 items-center justify-center rounded-md shrink-0 transition',
                                        isActive(item.route) ? 'bg-primary/10' : 'bg-transparent group-hover:bg-gray-100',
                                    ]"
                                >
                                    <div
                                        v-if="isActive(item.route)"
                                        class="absolute -left-[11px] top-1/2 -translate-y-1/2 h-4 w-[3px] rounded-r-full bg-primary"
                                    />
                                    <component
                                        :is="item.icon"
                                        :class="['h-4 w-4', isActive(item.route) ? 'text-primary' : 'text-gray-400 group-hover:text-gray-600']"
                                        :stroke-width="1.75"
                                    />
                                </div>
                                <span v-if="!sidebarCollapsed">{{ item.label }}</span>
                            </Link>
                        </div>
                    </Transition>
                </div>
            </nav>

            <!-- User section -->
            <div class="border-t px-2.5 py-2 space-y-0.5">
                <Link :href="route('profile.edit')"
                    :class="[
                        'flex items-center gap-2.5 rounded-lg px-2.5 py-1.5 text-[13px] font-medium transition',
                        isActive('profile.edit') ? 'bg-primary/8 text-primary' : 'text-gray-600 hover:bg-gray-50',
                        sidebarCollapsed && 'justify-center',
                    ]"
                >
                    <div :class="['flex h-7 w-7 items-center justify-center rounded-full shrink-0 transition', isActive('profile.edit') ? 'bg-primary/10' : 'bg-gray-100']">
                        <User :class="['h-4 w-4', isActive('profile.edit') ? 'text-primary' : 'text-gray-400']" :stroke-width="1.75" />
                    </div>
                    <span v-if="!sidebarCollapsed">Profil Saya</span>
                </Link>
                <Link :href="route('logout')" method="post" as="button"
                    :class="[
                        'flex items-center gap-2.5 rounded-lg px-2.5 py-1.5 text-[13px] font-medium text-red-600 hover:bg-red-50 transition w-full',
                        sidebarCollapsed && 'justify-center',
                    ]"
                >
                    <div class="flex h-7 w-7 items-center justify-center rounded-full bg-red-50 shrink-0">
                        <LogOut class="h-4 w-4 text-red-500" :stroke-width="1.75" />
                    </div>
                    <span v-if="!sidebarCollapsed">Keluar</span>
                </Link>
            </div>
        </aside>

        <!-- Main content -->
        <div :class="['transition-all duration-200', sidebarCollapsed ? 'lg:ml-[68px]' : 'lg:ml-60']">
            <!-- Topbar -->
            <header class="sticky top-0 z-30 flex h-14 items-center justify-between border-b bg-white px-4 lg:px-6">
                <div class="flex items-center gap-3">
                    <button
                        @click="showingNavigationDropdown = !showingNavigationDropdown"
                        class="flex items-center justify-center h-8 w-8 rounded-md text-gray-500 hover:bg-gray-100 lg:hidden"
                    >
                        <Menu class="h-4 w-4" />
                    </button>
                    <Link :href="logoRoute" class="flex items-center gap-2 lg:hidden">
                        <img src="/images/logo-simppay.png" alt="SIMPPAY" class="h-6 w-6 rounded object-cover" />
                        <span class="text-sm font-semibold">SIMPPAY</span>
                    </Link>
                </div>

                <!-- Right side -->
                <div class="flex items-center gap-2">
                    <!-- User dropdown -->
                    <div class="relative">
                        <button
                            @click="userDropdownOpen = !userDropdownOpen"
                            class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-sm hover:bg-gray-50 transition"
                        >
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10 shrink-0">
                                <span class="text-xs font-semibold text-primary">
                                    {{ currentUser?.name?.charAt(0)?.toUpperCase() || 'U' }}
                                </span>
                            </div>
                            <div class="hidden sm:block text-left">
                                <p class="text-sm font-medium leading-none">{{ currentUser?.name || 'User' }}</p>
                                <p class="text-xs text-muted-foreground mt-0.5">{{ roleLabel }}</p>
                            </div>
                        </button>

                        <Transition
                            enter-active-class="transition ease-out duration-100"
                            enter-from-class="transform opacity-0 scale-95"
                            enter-to-class="transform opacity-100 scale-100"
                            leave-active-class="transition ease-in duration-75"
                            leave-from-class="transform opacity-100 scale-100"
                            leave-to-class="transform opacity-0 scale-95"
                        >
                            <div
                                v-if="userDropdownOpen"
                                class="absolute right-0 mt-1 w-48 rounded-lg border bg-white py-1 shadow-lg z-50"
                                @click="closeUserDropdown"
                            >
                                <Link :href="route('profile.edit')" class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    <User class="h-4 w-4 text-gray-400" />
                                    Profil Saya
                                </Link>
                                <div class="my-1 border-t"></div>
                                <Link :href="route('logout')" method="post" as="button" class="flex w-full items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50">
                                    <LogOut class="h-4 w-4" />
                                    Keluar
                                </Link>
                            </div>
                        </Transition>
                    </div>
                </div>
            </header>

            <!-- Page content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
