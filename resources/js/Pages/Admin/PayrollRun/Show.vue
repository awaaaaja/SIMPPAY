<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useRoutePrefix } from '@/composables/useRoutePrefix.js';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger } from '@/components/ui/alert-dialog';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetDescription, SheetFooter } from '@/components/ui/sheet';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ArrowLeft, FileText, FileDown, X } from '@lucide/vue';

const prefix = useRoutePrefix();

const props = defineProps({
    run: Object,
    can: Object,
});

const selectedDetail = ref(null);
const showVoidModal = ref(false);
const voidReason = ref('');
const voidForm = useForm({ void_reason: '' });

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

function finalize() {
    router.post(route(`${prefix.value}.payroll-run.finalize`, props.run.id));
}

function submitVoid() {
    voidForm.void_reason = voidReason.value;
    voidForm.post(route(`${prefix.value}.payroll-run.void`, props.run.id), {
        onSuccess: () => { showVoidModal.value = false; },
    });
}

const grandTotal = props.run.details?.reduce((sum, d) => sum + parseFloat(d.total_gaji), 0) || 0;
</script>

<template>
    <Head title="Detail Payroll Run" />

    <AuthenticatedLayout>
        <div class="px-4 pt-6 pb-2 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <Button variant="ghost" size="sm" class="mb-1 -ml-2" as-child>
                        <a :href="route(`${prefix}.payroll-run.index`)"><ArrowLeft class="h-4 w-4" /> Kembali</a>
                    </Button>
                    <h1 class="text-2xl font-semibold text-gray-800">Payroll {{ formatPeriode(run.periode) }}</h1>
                </div>
                <div class="flex items-center gap-2">
                    <Badge :variant="statusVariant(run.status)" class="capitalize text-sm">{{ run.status }}</Badge>
                    <Button variant="outline" size="sm" as-child>
                        <a :href="route(`${prefix}.laporan-gaji.pdf`, { periode: run.periode?.substring(0, 7) })" target="_blank">
                            <FileText class="h-4 w-4" /> PDF
                        </a>
                    </Button>
                    <Button variant="outline" size="sm" as-child>
                        <a :href="route(`${prefix}.laporan-gaji.export`, { periode: run.periode?.substring(0, 7) })">
                            <FileDown class="h-4 w-4" /> Excel
                        </a>
                    </Button>
                    <AlertDialog v-if="can.update && run.status === 'calculated'">
                        <AlertDialogTrigger as-child>
                            <Button size="sm">Finalize</Button>
                        </AlertDialogTrigger>
                        <AlertDialogContent>
                            <AlertDialogHeader>
                                <AlertDialogTitle>Finalisasi Payroll?</AlertDialogTitle>
                                <AlertDialogDescription>
                                    Setelah difinalisasi, tidak bisa dihitung ulang.
                                </AlertDialogDescription>
                            </AlertDialogHeader>
                            <AlertDialogFooter>
                                <AlertDialogCancel>Batal</AlertDialogCancel>
                                <AlertDialogAction @click="finalize">Finalize</AlertDialogAction>
                            </AlertDialogFooter>
                        </AlertDialogContent>
                    </AlertDialog>
                    <Button v-if="can.update && run.status === 'finalized'" variant="destructive" size="sm"
                        @click="showVoidModal = true">Void</Button>
                </div>
            </div>

            <!-- Summary -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-[12px] p-4" style="box-shadow: 0 1px 2px rgba(0,0,0,.04), 0 8px 24px rgba(0,0,0,.04);">
                    <p class="text-xs text-muted-foreground mb-1">Total Pegawai</p>
                    <p class="text-2xl font-semibold text-foreground">{{ run.details?.length || 0 }}</p>
                </div>
                <div class="bg-white rounded-[12px] p-4" style="box-shadow: 0 1px 2px rgba(0,0,0,.04), 0 8px 24px rgba(0,0,0,.04);">
                    <p class="text-xs text-muted-foreground mb-1">Total Gaji</p>
                    <p class="text-2xl font-semibold text-foreground">{{ formatRupiah(grandTotal) }}</p>
                </div>
                <div class="bg-white rounded-[12px] p-4" style="box-shadow: 0 1px 2px rgba(0,0,0,.04), 0 8px 24px rgba(0,0,0,.04);">
                    <p class="text-xs text-muted-foreground mb-1">Dihitung Oleh</p>
                    <p class="text-sm font-medium text-foreground">{{ run.calculated_by?.name || '-' }}</p>
                </div>
                <div class="bg-white rounded-[12px] p-4" style="box-shadow: 0 1px 2px rgba(0,0,0,.04), 0 8px 24px rgba(0,0,0,.04);">
                    <p class="text-xs text-muted-foreground mb-1">Difinalisasi Oleh</p>
                    <p class="text-sm font-medium text-foreground">{{ run.finalized_by?.name || '-' }}</p>
                </div>
            </div>

            <div v-if="run.status === 'void' && run.void_reason" class="mb-6 bg-destructive/10 border border-destructive/20 rounded-[12px] p-4">
                <p class="text-sm font-medium text-destructive">Alasan Void:</p>
                <p class="text-sm text-destructive/80">{{ run.void_reason }}</p>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-[12px] overflow-hidden" style="box-shadow: 0 1px 2px rgba(0,0,0,.04), 0 8px 24px rgba(0,0,0,.04);">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Pegawai</TableHead>
                            <TableHead>Jabatan</TableHead>
                            <TableHead class="text-right">Gaji Pokok</TableHead>
                            <TableHead class="text-right">Tj. Transport</TableHead>
                            <TableHead class="text-right">Uang Makan</TableHead>
                            <TableHead class="text-right">Pot. Alpha</TableHead>
                            <TableHead class="text-right">Tunjangan</TableHead>
                            <TableHead class="text-right">Potongan</TableHead>
                            <TableHead class="text-right">Total Gaji</TableHead>
                            <TableHead class="text-center">Slip</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="detail in run.details" :key="detail.id" class="cursor-pointer"
                            @click="selectedDetail = detail">
                            <TableCell class="font-medium">{{ detail.pegawai?.nama_pegawai }}</TableCell>
                            <TableCell class="text-muted-foreground">{{ detail.pegawai?.jabatan?.nama_jabatan }}</TableCell>
                            <TableCell class="text-right tabular-nums">{{ formatRupiah(detail.gaji_pokok) }}</TableCell>
                            <TableCell class="text-right tabular-nums">{{ formatRupiah(detail.tj_transport) }}</TableCell>
                            <TableCell class="text-right tabular-nums">{{ formatRupiah(detail.uang_makan) }}</TableCell>
                            <TableCell class="text-right tabular-nums text-destructive">{{ formatRupiah(detail.potongan_alpha) }}</TableCell>
                            <TableCell class="text-right tabular-nums text-primary">+{{ formatRupiah(detail.total_tunjangan_tambahan) }}</TableCell>
                            <TableCell class="text-right tabular-nums text-destructive">-{{ formatRupiah(detail.total_potongan_tambahan) }}</TableCell>
                            <TableCell class="text-right tabular-nums font-semibold">{{ formatRupiah(detail.total_gaji) }}</TableCell>
                            <TableCell class="text-center">
                                <Button variant="ghost" size="icon-sm" as-child @click.stop>
                                    <a :href="route(`${prefix}.slip-gaji.pdf`, detail.id)" target="_blank">
                                        <FileText class="h-3.5 w-3.5" />
                                    </a>
                                </Button>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="!run.details?.length">
                            <TableCell colspan="10" class="text-center py-8 text-muted-foreground">Belum ada data detail.</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>

        <!-- Detail Drawer (Sheet) -->
        <Sheet v-model:open="selectedDetail">
            <SheetContent class="w-full sm:max-w-md">
                <SheetHeader>
                    <SheetTitle>Breakdown Gaji</SheetTitle>
                    <SheetDescription v-if="selectedDetail">
                        {{ selectedDetail.pegawai?.nama_pegawai }} — {{ selectedDetail.pegawai?.nik }}
                    </SheetDescription>
                </SheetHeader>
                <div v-if="selectedDetail" class="space-y-6 px-4 py-4">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-muted-foreground mb-2">Pendapatan</p>
                        <div class="space-y-1.5">
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Gaji Pokok</span>
                                <span class="tabular-nums">{{ formatRupiah(selectedDetail.gaji_pokok) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Tj. Transport</span>
                                <span class="tabular-nums">{{ formatRupiah(selectedDetail.tj_transport) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Uang Makan</span>
                                <span class="tabular-nums">{{ formatRupiah(selectedDetail.uang_makan) }}</span>
                            </div>
                            <div v-if="selectedDetail.breakdown_json?.tunjangan?.length" class="pt-1 border-t border-border">
                                <div v-for="(t, i) in selectedDetail.breakdown_json.tunjangan" :key="i" class="flex justify-between text-sm">
                                    <span class="text-muted-foreground">+ {{ t.nama }}</span>
                                    <span class="text-primary tabular-nums">{{ formatRupiah(t.nominal) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-muted-foreground mb-2">Potongan</p>
                        <div class="space-y-1.5">
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Potongan Alpha</span>
                                <span class="text-destructive tabular-nums">{{ formatRupiah(selectedDetail.potongan_alpha) }}</span>
                            </div>
                            <div v-if="selectedDetail.breakdown_json?.potongan?.length" class="pt-1 border-t border-border">
                                <div v-for="(p, i) in selectedDetail.breakdown_json.potongan" :key="i" class="flex justify-between text-sm">
                                    <span class="text-muted-foreground">- {{ p.nama }}</span>
                                    <span class="text-destructive tabular-nums">{{ formatRupiah(p.nilai) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="pt-4 border-t border-border flex justify-between">
                        <span class="text-sm font-semibold">Total Gaji</span>
                        <span class="text-lg font-bold tabular-nums">{{ formatRupiah(selectedDetail.total_gaji) }}</span>
                    </div>
                </div>
            </SheetContent>
        </Sheet>

        <!-- Void Modal -->
        <Dialog v-model:open="showVoidModal">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Batalkan Payroll Run</DialogTitle>
                </DialogHeader>
                <form @submit.prevent="submitVoid" class="space-y-4">
                    <div class="space-y-1">
                        <Label>Alasan Void <span class="text-destructive">*</span></Label>
                        <Input v-model="voidReason" placeholder="Jelaskan alasan pembatalan..." required />
                        <p v-if="voidForm.errors.void_reason" class="text-sm text-destructive">{{ voidForm.errors.void_reason }}</p>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="showVoidModal = false">Batal</Button>
                        <Button type="submit" variant="destructive" :disabled="voidForm.processing">
                            {{ voidForm.processing ? 'Membatalkan...' : 'Batalkan' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AuthenticatedLayout>
</template>
