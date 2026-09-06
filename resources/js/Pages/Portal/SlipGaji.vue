<script setup>
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { FileText, Download, Eye, EyeOff } from '@lucide/vue';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Button } from '@/components/ui/button';

const props = defineProps({
    slips: Array,
    periodes: Array,
    pegawai: Object,
    selectedPeriodeId: [String, Number, null],
});

const showNominal = ref(true);
const selectedPeriode = ref(props.selectedPeriodeId ? String(props.selectedPeriodeId) : '');

function onPeriodeChange(val) {
    selectedPeriode.value = val;
    router.get(route('portal.slip.gaji'), { periode_id: val || undefined }, {
        preserveState: true,
        replace: true,
    });
}

function formatPeriode(val) {
    if (!val) return '-';
    const d = new Date(val);
    return d.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
}

function formatRupiah(val) {
    if (val == null) return '-';
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
}

const selectedSlip = ref(null);

function toggleDetail(slip) {
    selectedSlip.value = selectedSlip.value?.id === slip.id ? null : slip;
}
</script>

<template>
    <Head title="Slip Gaji" />

    <PortalLayout>
        <div class="px-4 py-8 sm:px-6 lg:px-8 max-w-4xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-xl font-semibold text-foreground">Slip Gaji</h1>
                    <p class="text-sm text-muted-foreground mt-0.5">Lihat detail gaji per periode</p>
                </div>
                <Button variant="outline" size="sm" @click="showNominal = !showNominal">
                    <EyeOff v-if="showNominal" class="h-3.5 w-3.5" />
                    <Eye v-else class="h-3.5 w-3.5" />
                    {{ showNominal ? 'Sembunyikan' : 'Tampilkan' }} Nominal
                </Button>
            </div>

            <!-- Periode filter -->
            <div class="mb-6">
                <Select :model-value="selectedPeriode" @update:model-value="onPeriodeChange">
                    <SelectTrigger class="w-full max-w-xs">
                        <SelectValue placeholder="Semua periode finalized" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="">Semua periode finalized</SelectItem>
                        <SelectItem v-for="p in periodes" :key="p.id" :value="String(p.id)">
                            {{ formatPeriode(p.periode) }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <!-- Empty state -->
            <div v-if="!slips?.length" class="bg-white rounded-[16px] p-8 text-center"
                style="box-shadow: 0 1px 2px rgba(0,0,0,.04), 0 8px 24px rgba(0,0,0,.04);">
                <FileText class="h-10 w-10 text-muted-foreground/40 mx-auto mb-3" :stroke-width="1.5" />
                <p class="text-sm font-medium text-muted-foreground">Belum ada slip gaji</p>
                <p class="text-xs text-muted-foreground/60 mt-1">Slip gaji akan muncul setelah payroll finalisasi</p>
            </div>

            <!-- Slip list -->
            <div v-else class="space-y-3">
                <div v-for="slip in slips" :key="slip.id"
                    class="bg-white rounded-[16px] overflow-hidden"
                    style="box-shadow: 0 1px 2px rgba(0,0,0,.04), 0 8px 24px rgba(0,0,0,.04);">
                    <button @click="toggleDetail(slip)"
                        class="w-full p-5 flex items-center justify-between text-left hover:bg-muted/50 transition-colors">
                        <div>
                            <p class="text-sm font-semibold text-foreground">{{ formatPeriode(slip.payroll_run?.periode) }}</p>
                            <p class="text-xs text-muted-foreground mt-0.5">{{ pegawai?.jabatan?.nama_jabatan || '-' }}</p>
                        </div>
                        <div class="flex items-center gap-4">
                            <p v-if="showNominal" class="text-sm font-semibold text-foreground tabular-nums">{{ formatRupiah(slip.total_gaji) }}</p>
                            <p v-else class="text-sm font-semibold text-foreground">Rp ****</p>
                            <a :href="route('portal.slip-gaji.pdf', slip.id)" target="_blank" @click.stop
                                class="inline-flex items-center px-3 py-1.5 bg-primary text-primary-foreground text-xs font-medium rounded-[8px] hover:bg-primary/90 transition-colors">
                                <Download class="h-3.5 w-3.5" :stroke-width="1.75" />
                            </a>
                        </div>
                    </button>

                    <!-- Breakdown -->
                    <Transition enter-active-class="transition-all duration-200" enter-from-class="opacity-0 max-h-0"
                        enter-to-class="opacity-100 max-h-96" leave-active-class="transition-all duration-150"
                        leave-from-class="opacity-100 max-h-96" leave-to-class="opacity-0 max-h-0">
                        <div v-if="selectedSlip?.id === slip.id" class="border-t border-border px-5 pb-5 pt-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <!-- Pendapatan -->
                                <div>
                                    <p class="text-[10px] font-semibold uppercase tracking-wider text-muted-foreground mb-2">Pendapatan</p>
                                    <dl class="space-y-1.5">
                                        <div class="flex justify-between text-xs">
                                            <dt class="text-muted-foreground">Gaji Pokok</dt>
                                            <dd class="font-medium text-foreground tabular-nums">{{ showNominal ? formatRupiah(slip.gaji_pokok) : 'Rp ****' }}</dd>
                                        </div>
                                        <div class="flex justify-between text-xs">
                                            <dt class="text-muted-foreground">Tj. Transport</dt>
                                            <dd class="font-medium text-foreground tabular-nums">{{ showNominal ? formatRupiah(slip.tj_transport) : 'Rp ****' }}</dd>
                                        </div>
                                        <div class="flex justify-between text-xs">
                                            <dt class="text-muted-foreground">Uang Makan</dt>
                                            <dd class="font-medium text-foreground tabular-nums">{{ showNominal ? formatRupiah(slip.uang_makan) : 'Rp ****' }}</dd>
                                        </div>
                                        <div v-if="Number(slip.total_tunjangan_tambahan) > 0" class="flex justify-between text-xs">
                                            <dt class="text-muted-foreground">Tunjangan Tambahan</dt>
                                            <dd class="font-medium text-foreground tabular-nums">{{ showNominal ? formatRupiah(slip.total_tunjangan_tambahan) : 'Rp ****' }}</dd>
                                        </div>
                                        <div v-if="Number(slip.honor_kelebihan_sks) > 0" class="flex justify-between text-xs">
                                            <dt class="text-muted-foreground">Honor SKS</dt>
                                            <dd class="font-medium text-foreground tabular-nums">{{ showNominal ? formatRupiah(slip.honor_kelebihan_sks) : 'Rp ****' }}</dd>
                                        </div>
                                    </dl>
                                </div>

                                <!-- Potongan -->
                                <div>
                                    <p class="text-[10px] font-semibold uppercase tracking-wider text-muted-foreground mb-2">Potongan</p>
                                    <dl class="space-y-1.5">
                                        <div v-if="Number(slip.potongan_alpha) > 0" class="flex justify-between text-xs">
                                            <dt class="text-muted-foreground">Alpha</dt>
                                            <dd class="font-medium text-destructive tabular-nums">{{ showNominal ? formatRupiah(slip.potongan_alpha) : 'Rp ****' }}</dd>
                                        </div>
                                        <div v-if="Number(slip.total_potongan_tambahan) > 0" class="flex justify-between text-xs">
                                            <dt class="text-muted-foreground">Potongan Lainnya</dt>
                                            <dd class="font-medium text-destructive tabular-nums">{{ showNominal ? formatRupiah(slip.total_potongan_tambahan) : 'Rp ****' }}</dd>
                                        </div>
                                        <div v-if="!Number(slip.potongan_alpha) && !Number(slip.total_potongan_tambahan)" class="flex justify-between text-xs">
                                            <dt class="text-muted-foreground italic">Tidak ada potongan</dt>
                                        </div>
                                    </dl>
                                </div>
                            </div>

                            <!-- Total -->
                            <div class="mt-4 pt-3 border-t border-border flex justify-between items-center">
                                <span class="text-xs font-semibold text-muted-foreground">Take Home Pay</span>
                                <span class="text-sm font-semibold text-primary tabular-nums">
                                    {{ showNominal ? formatRupiah(slip.total_gaji) : 'Rp ****' }}
                                </span>
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>
