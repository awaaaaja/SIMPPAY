<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useRoutePrefix } from '@/composables/useRoutePrefix.js';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Calculator, FileDown, FileText } from '@lucide/vue';

const prefix = useRoutePrefix();

const props = defineProps({
    runs: Object,
    can: Object,
});

const showCalcModal = ref(false);
const calcForm = useForm({ periode: '', formula_version: 'legacy' });

function submitCalc() {
    calcForm.post(route(`${prefix.value}.payroll-run.calculate`), {
        onSuccess: () => { showCalcModal.value = false; calcForm.reset(); },
    });
}

function formatPeriode(val) {
    if (!val) return '-';
    return new Date(val).toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
}

function formatRupiah(val) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
}

function statusVariant(status) {
    const map = { draft: 'secondary', calculated: 'outline', finalized: 'default', void: 'destructive' };
    return map[status] || 'secondary';
}
</script>

<template>
    <Head title="Payroll Run" />

    <AuthenticatedLayout>
        <div class="px-4 pt-6 pb-2 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-semibold text-gray-800">Payroll Run</h1>
                <Dialog v-if="can.create" v-model:open="showCalcModal">
                    <DialogTrigger as-child>
                        <Button><Calculator class="h-4 w-4" /> Hitung Gaji</Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Hitung Gaji</DialogTitle>
                            <DialogDescription>
                                Menghitung gaji seluruh pegawai aktif untuk periode ini.
                            </DialogDescription>
                        </DialogHeader>
                        <form @submit.prevent="submitCalc" class="space-y-4">
                            <div class="space-y-1">
                                <Label>Periode (Bulan Tahun)</Label>
                                <Input v-model="calcForm.periode" type="month" required />
                                <p v-if="calcForm.errors.periode" class="text-sm text-destructive">{{ calcForm.errors.periode }}</p>
                            </div>
                            <div class="space-y-1">
                                <Label>Formula</Label>
                                <Select v-model="calcForm.formula_version">
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="legacy">Legacy (Sistem Lama)</SelectItem>
                                        <SelectItem value="ua-2025">UA 2025 (Rumusan Baru)</SelectItem>
                                    </SelectContent>
                                </Select>
                                <p class="text-xs text-muted-foreground">Default: Legacy. Pilih UA 2025 hanya untuk pilot terbatas.</p>
                            </div>
                            <DialogFooter>
                                <Button type="button" variant="outline" @click="showCalcModal = false">Batal</Button>
                                <Button type="submit" :disabled="calcForm.processing">
                                    {{ calcForm.processing ? 'Menghitung...' : 'Hitung Gaji' }}
                                </Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>

            <div class="bg-white rounded-[12px] overflow-hidden"
                style="box-shadow: 0 1px 2px rgba(0,0,0,.04), 0 8px 24px rgba(0,0,0,.04);">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Periode</TableHead>
                            <TableHead>Formula</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Dihitung Oleh</TableHead>
                            <TableHead>Difinalisasi Oleh</TableHead>
                            <TableHead class="text-right">Aksi</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="run in runs.data" :key="run.id">
                            <TableCell class="font-medium">{{ formatPeriode(run.periode) }}</TableCell>
                            <TableCell>
                                <Badge v-if="run.formula_version === 'ua-2025'" variant="default" class="bg-blue-600 hover:bg-blue-700 text-xs">
                                    UA 2025
                                </Badge>
                                <span v-else class="text-muted-foreground text-xs">Legacy</span>
                            </TableCell>
                            <TableCell>
                                <Badge :variant="statusVariant(run.status)" class="capitalize">{{ run.status }}</Badge>
                            </TableCell>
                            <TableCell class="text-muted-foreground">{{ run.calculated_by?.name || '-' }}</TableCell>
                            <TableCell class="text-muted-foreground">{{ run.finalized_by?.name || '-' }}</TableCell>
                            <TableCell class="text-right">
                                <Button variant="ghost" size="sm" @click="router.get(route(`${prefix}.payroll-run.show`, run.id))">
                                    Detail
                                </Button>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="runs.data.length === 0">
                            <TableCell colspan="6" class="text-center py-8 text-muted-foreground">Belum ada payroll run.</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <div v-if="runs.last_page > 1" class="mt-4 flex items-center justify-between">
                <p class="text-sm text-muted-foreground">Menampilkan {{ runs.from }}-{{ runs.to }} dari {{ runs.total }} data</p>
                <nav class="flex items-center gap-1">
                    <template v-for="link in runs.links" :key="link.url">
                        <Button v-if="link.url" variant="ghost" size="sm"
                            :class="link.active ? 'bg-primary text-primary-foreground hover:bg-primary/90' : ''"
                            @click="router.get(link.url, {}, { preserveState: true, replace: true })" v-html="link.label" />
                        <span v-else class="px-3 py-2 text-sm text-gray-400">...</span>
                    </template>
                </nav>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
