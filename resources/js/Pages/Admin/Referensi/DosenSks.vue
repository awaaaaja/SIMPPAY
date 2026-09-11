<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Badge } from '@/components/ui/badge';

const props = defineProps({
    honorSks: Array,
    bebanSks: Array,
});

function formatRupiah(val) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
}
</script>

<template>
    <Head title="Dosen & SKS" />

    <AuthenticatedLayout>
        <div class="px-4 pt-5 pb-2 sm:px-6 lg:px-8 max-w-[1400px] mx-auto">
            <div class="mb-4">
                <h1 class="text-lg font-semibold" style="color: var(--simppay-text)">Dosen & SKS</h1>
                <p class="text-sm mt-0.5" style="color: var(--simppay-text-secondary)">Honor SKS per program/strata dan daftar beban SKS jabatan struktural</p>
            </div>

            <Tabs default-value="honor">
                <TabsList>
                    <TabsTrigger value="honor">Honor SKS ({{ honorSks.length }})</TabsTrigger>
                    <TabsTrigger value="beban">Beban SKS Jabatan ({{ bebanSks.length }})</TabsTrigger>
                </TabsList>

                <TabsContent value="honor" class="mt-3">
                    <p class="text-xs mb-2" style="color: var(--simppay-text-secondary)">Honor kelebihan beban SKS dosen per program dan strata (§17).</p>
                    <div class="bg-white rounded-xl overflow-hidden border" style="border-color: var(--simppay-border)">
                        <Table class="table-compact">
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Program</TableHead>
                                    <TableHead>Status Dosen</TableHead>
                                    <TableHead>Strata / Gelar</TableHead>
                                    <TableHead class="text-right">Nilai SKS</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="h in honorSks" :key="h.id">
                                    <TableCell>
                                        <Badge :variant="h.program === 's1' ? 'default' : 'secondary'" class="text-[11px]">{{ h.program === 's1' ? 'S1' : 'S2' }}</Badge>
                                    </TableCell>
                                    <TableCell class="text-sm">{{ h.status_dosen === 'tetap' ? 'Tetap' : 'Tidak Tetap' }}</TableCell>
                                    <TableCell class="font-medium text-sm">{{ h.strata }}</TableCell>
                                    <TableCell class="text-right tabular-nums font-medium text-sm">{{ formatRupiah(h.nilai_sks) }}</TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </TabsContent>

                <TabsContent value="beban" class="mt-3">
                    <p class="text-xs mb-2" style="color: var(--simppay-text-secondary)">Beban SKS mengajar dosen tetap per jabatan struktural (§18). Total selalu 12 SKS.</p>
                    <div class="bg-white rounded-xl overflow-x-auto border" style="border-color: var(--simppay-border)">
                        <Table class="min-w-[600px] table-compact">
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Jabatan Struktural</TableHead>
                                    <TableHead class="text-right">Perkuliahan</TableHead>
                                    <TableHead class="text-right">Penelitian</TableHead>
                                    <TableHead class="text-right">Adm</TableHead>
                                    <TableHead class="text-right">Jabatan</TableHead>
                                    <TableHead class="text-right">Total</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="b in bebanSks" :key="b.id">
                                    <TableCell class="font-medium text-sm">{{ b.nama_jabatan }}</TableCell>
                                    <TableCell class="text-right tabular-nums text-sm">{{ b.sks_perkuliahan }}</TableCell>
                                    <TableCell class="text-right tabular-nums text-sm">{{ b.sks_penelitian }}</TableCell>
                                    <TableCell class="text-right tabular-nums text-sm">{{ b.sks_adm }}</TableCell>
                                    <TableCell class="text-right tabular-nums text-sm">{{ b.sks_jabatan }}</TableCell>
                                    <TableCell class="text-right tabular-nums font-medium text-sm">{{ b.total }}</TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </TabsContent>
            </Tabs>
        </div>
    </AuthenticatedLayout>
</template>
