<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useRoutePrefix } from '@/composables/useRoutePrefix.js';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { ArrowLeft, Pencil, UserPlus, CheckCircle } from '@lucide/vue';

const prefix = useRoutePrefix();

const props = defineProps({
    pegawai: Object,
    can: Object,
    roles: Array,
});

const showAccountModal = ref(false);
const accountForm = useForm({
    username: props.pegawai.nik,
    password: '',
    role: 'pegawai',
});

function createAccount() {
    accountForm.post(route(`${prefix.value}.pegawai.createAccount`, props.pegawai.id), {
        onSuccess: () => { showAccountModal.value = false; accountForm.reset(); },
    });
}

function formatRupiah(val) {
    if (!val) return '-';
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
}

function formatDate(val) {
    if (!val) return '-';
    return new Date(val).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
}

function statusVariant(status) {
    const map = { aktif: 'default', nonaktif: 'destructive', pensiun: 'outline' };
    return map[status] || 'outline';
}

const fields = {
    overview: [
        { label: 'NIK', key: 'nik' },
        { label: 'Jenis Kelamin', key: 'jenis_kelamin', format: (v) => v === 'L' ? 'Laki-laki' : 'Perempuan' },
        { label: 'Tanggal Masuk', key: 'tanggal_masuk', format: formatDate },
        { label: 'Ikatan Kerja', key: 'ikatan_kerja' },
        { label: 'Email', key: 'email' },
        { label: 'No. HP', key: 'no_hp' },
        { label: 'Alamat', key: 'alamat', full: true },
    ],
    kepegawaian: [
        { label: 'Jabatan', key: 'jabatan.nama_jabatan' },
        { label: 'Struktural', key: 'struktural.nama_struktural' },
        { label: 'Fungsional', key: 'fungsional.nama_fungsional' },
        { label: 'Status Dosen', key: 'status_dosen' },
        { label: 'NIDN', key: 'nidn' },
        { label: 'NUPTK', key: 'nuptk' },
        { label: 'No. SK', key: 'no_sk' },
        { label: 'Tanggal SK', key: 'tgl_sk', format: formatDate },
        { label: 'Masa Jabatan', key: 'masa_jabatan' },
    ],
    keluarga: [
        { label: 'Nama Pasangan', key: 'nama_sm' },
        { label: 'NIP Pasangan', key: 'nip_sm' },
        { label: 'No. HP Pasangan', key: 'nohp_sm' },
        { label: 'Pekerjaan Pasangan', key: 'pekerjaan_sm' },
        { label: 'Nama Ibu', key: 'nama_ibu' },
    ],
};

function resolve(obj, path) {
    return path.split('.').reduce((o, k) => o?.[k], obj);
}
</script>

<template>
    <Head :title="`Profil — ${pegawai.nama_pegawai}`" />

    <AuthenticatedLayout>
        <div class="px-4 pt-6 pb-2 sm:px-6 lg:px-8 max-w-4xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <Button variant="ghost" size="sm" as-child>
                    <Link :href="route(`${prefix}.pegawai.index`)">
                        <ArrowLeft class="h-4 w-4" /> Kembali
                    </Link>
                </Button>
                <div class="flex items-center gap-2">
                    <Dialog v-if="can.update && !pegawai.user" v-model:open="showAccountModal">
                        <DialogTrigger as-child>
                            <Button variant="outline" size="sm">
                                <UserPlus class="h-4 w-4" /> Buat Akun
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>Buat Akun Login</DialogTitle>
                            </DialogHeader>
                            <form @submit.prevent="createAccount" class="space-y-4">
                                <div class="space-y-1.5">
                                    <Label>Username</Label>
                                    <Input v-model="accountForm.username" placeholder="Default: NIK" />
                                    <p v-if="accountForm.errors.username" class="text-sm text-destructive">{{ accountForm.errors.username }}</p>
                                </div>
                                <div class="space-y-1.5">
                                    <Label>Password</Label>
                                    <Input v-model="accountForm.password" type="text" placeholder="Kosongkan untuk default: password123" />
                                    <p v-if="accountForm.errors.password" class="text-sm text-destructive">{{ accountForm.errors.password }}</p>
                                </div>
                                <div class="space-y-1.5">
                                    <Label>Role</Label>
                                    <Select v-model="accountForm.role">
                                        <SelectTrigger class="w-full"><SelectValue /></SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="r in roles" :key="r" :value="r">{{ r.charAt(0).toUpperCase() + r.slice(1) }}</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <DialogFooter>
                                    <Button type="button" variant="outline" @click="showAccountModal = false">Batal</Button>
                                    <Button type="submit" :disabled="accountForm.processing">Buat Akun</Button>
                                </DialogFooter>
                            </form>
                        </DialogContent>
                    </Dialog>
                    <Badge v-if="pegawai.user" variant="default" class="bg-emerald-100 text-emerald-700">
                        <CheckCircle class="h-3 w-3 mr-1" /> Akun Aktif
                    </Badge>
                    <Button v-if="can.update" variant="outline" size="sm" as-child>
                        <Link :href="route(`${prefix}.pegawai.edit`, pegawai.id)">
                            <Pencil class="h-4 w-4" /> Edit
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Profile header -->
            <div class="bg-white rounded-[12px] p-6 mb-4"
                style="box-shadow: 0 1px 2px rgba(0,0,0,.04), 0 8px 24px rgba(0,0,0,.04);">
                <div class="flex items-start gap-6">
                    <div class="h-20 w-20 flex-shrink-0 rounded-full bg-muted overflow-hidden">
                        <img v-if="pegawai.photo" :src="'/storage/' + pegawai.photo" :alt="pegawai.nama_pegawai"
                            class="h-full w-full object-cover" />
                        <div v-else class="flex h-full w-full items-center justify-center text-2xl font-bold text-muted-foreground">
                            {{ pegawai.nama_pegawai.charAt(0) }}
                        </div>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-foreground">{{ pegawai.nama_pegawai }}</h2>
                        <p v-if="pegawai.nidn" class="text-sm text-muted-foreground">NIDN {{ pegawai.nidn }}</p>
                        <p class="mt-1 text-sm text-muted-foreground">
                            {{ pegawai.jabatan?.nama_jabatan ?? '-' }}
                            <Badge :variant="statusVariant(pegawai.status_pegawai)" class="ml-2 capitalize">
                                {{ pegawai.status_pegawai }}
                            </Badge>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="bg-white rounded-[12px] overflow-hidden"
                style="box-shadow: 0 1px 2px rgba(0,0,0,.04), 0 8px 24px rgba(0,0,0,.04);">
                <Tabs default-value="overview" class="p-6">
                    <TabsList variant="line" class="mb-6">
                        <TabsTrigger value="overview">Overview</TabsTrigger>
                        <TabsTrigger value="kepegawaian">Kepegawaian</TabsTrigger>
                        <TabsTrigger value="keluarga">Keluarga</TabsTrigger>
                    </TabsList>

                    <TabsContent v-for="(items, tabKey) in fields" :key="tabKey" :value="tabKey">
                        <div v-if="tabKey === 'keluarga'" class="mb-6">
                            <p class="text-sm font-medium text-muted-foreground mb-1">Status Kawin</p>
                            <p class="text-sm text-foreground">{{ pegawai.status_kawin ?? '-' }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div v-for="f in items" :key="f.key" :class="f.full ? 'col-span-2' : ''">
                                <p class="text-sm font-medium text-muted-foreground">{{ f.label }}</p>
                                <p class="text-sm text-foreground">{{ f.format ? f.format(resolve(pegawai, f.key)) : (resolve(pegawai, f.key) ?? '-') }}</p>
                            </div>
                        </div>

                        <!-- Anak table (keluarga tab only) -->
                        <div v-if="tabKey === 'keluarga'" class="mt-6">
                            <p class="text-sm font-semibold text-foreground mb-3">Anak</p>
                            <div v-if="pegawai.anak?.length">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead class="w-12">No</TableHead>
                                            <TableHead>Nama</TableHead>
                                            <TableHead>TTL</TableHead>
                                            <TableHead>JK</TableHead>
                                            <TableHead>Ke-</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        <TableRow v-for="(anak, i) in pegawai.anak" :key="anak.id">
                                            <TableCell class="text-muted-foreground">{{ i + 1 }}</TableCell>
                                            <TableCell>{{ anak.nama_anak }}</TableCell>
                                            <TableCell class="text-muted-foreground">{{ anak.tempat_tanggal_lahir ?? '-' }}</TableCell>
                                            <TableCell class="text-muted-foreground">{{ anak.jenis_kelamin ?? '-' }}</TableCell>
                                            <TableCell class="text-muted-foreground">{{ anak.anak_ke ?? '-' }}</TableCell>
                                        </TableRow>
                                    </TableBody>
                                </Table>
                            </div>
                            <p v-else class="text-sm text-muted-foreground">Belum ada data anak.</p>
                        </div>
                    </TabsContent>
                </Tabs>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
