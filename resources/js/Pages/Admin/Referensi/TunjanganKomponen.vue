<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Badge } from '@/components/ui/badge';

const props = defineProps({
    tjKaryawan: Array,
    tjFungsional: Array,
    tjVariabel: Array,
    tjTransport: Array,
});

function formatRupiah(val) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
}
</script>

<template>
    <Head title="Tunjangan Komponen" />

    <AuthenticatedLayout>
        <div class="px-4 pt-5 pb-2 sm:px-6 lg:px-8 max-w-[1400px] mx-auto">
            <div class="mb-4">
                <h1 class="text-lg font-semibold" style="color: var(--simppay-text)">Tunjangan Komponen</h1>
                <p class="text-sm mt-0.5" style="color: var(--simppay-text-secondary)">Tabel tunjangan per komponen gaji: jabatan karyawan, fungsional dosen, variabel, dan transportasi</p>
            </div>

            <Tabs default-value="karyawan">
                <TabsList>
                    <TabsTrigger value="karyawan">Jabatan Karyawan ({{ tjKaryawan.length }})</TabsTrigger>
                    <TabsTrigger value="fungsional">Fungsional Dosen ({{ tjFungsional.length }})</TabsTrigger>
                    <TabsTrigger value="variabel">Variabel ({{ tjVariabel.length }})</TabsTrigger>
                    <TabsTrigger value="transport">Transportasi ({{ tjTransport.length }})</TabsTrigger>
                </TabsList>

                <TabsContent value="karyawan" class="mt-3">
                    <p class="text-xs mb-2" style="color: var(--simppay-text-secondary)">Tunjangan jabatan karyawan per golongan ruang (§8)</p>
                    <div class="bg-white rounded-xl overflow-hidden border" style="border-color: var(--simppay-border)">
                        <Table class="table-compact">
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Golongan Ruang</TableHead>
                                    <TableHead class="text-right">Nominal</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="t in tjKaryawan" :key="t.id">
                                    <TableCell>
                                        <Badge variant="outline" class="text-[11px]">{{ t.golongan_ruang?.kode ?? '-' }}</Badge>
                                        <span class="ml-2 text-sm" style="color: var(--simppay-text-secondary)">{{ t.golongan_ruang?.nama ?? '' }}</span>
                                    </TableCell>
                                    <TableCell class="text-right tabular-nums font-medium text-sm">{{ formatRupiah(t.nominal) }}</TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </TabsContent>

                <TabsContent value="fungsional" class="mt-3">
                    <p class="text-xs mb-2" style="color: var(--simppay-text-secondary)">Tunjangan fungsional dosen per golongan ruang (§7)</p>
                    <div class="bg-white rounded-xl overflow-x-auto border" style="border-color: var(--simppay-border)">
                        <Table class="min-w-[600px] table-compact">
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Jabatan Fungsional</TableHead>
                                    <TableHead>Pangkat</TableHead>
                                    <TableHead>Gol. Ruang</TableHead>
                                    <TableHead class="text-right">Angka Kredit</TableHead>
                                    <TableHead class="text-right">Nominal</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="t in tjFungsional" :key="t.id">
                                    <TableCell class="font-medium text-sm">{{ t.jabatan_fungsional }}</TableCell>
                                    <TableCell class="text-sm">{{ t.pangkat }}</TableCell>
                                    <TableCell>
                                        <Badge variant="outline" class="text-[11px]">{{ t.golongan_ruang }}</Badge>
                                    </TableCell>
                                    <TableCell class="text-right tabular-nums text-sm">{{ t.angka_kredit }}</TableCell>
                                    <TableCell class="text-right tabular-nums font-medium text-sm">{{ formatRupiah(t.nominal) }}</TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </TabsContent>

                <TabsContent value="variabel" class="mt-3">
                    <p class="text-xs mb-2" style="color: var(--simppay-text-secondary)">Tunjangan variabel per golongan ruang — nominal maksimum (§9)</p>
                    <div class="bg-white rounded-xl overflow-hidden border" style="border-color: var(--simppay-border)">
                        <Table class="table-compact">
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Golongan Ruang</TableHead>
                                    <TableHead class="text-right">Nominal Maksimum</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="t in tjVariabel" :key="t.id">
                                    <TableCell>
                                        <Badge variant="outline" class="text-[11px]">{{ t.golongan_ruang?.kode ?? '-' }}</Badge>
                                        <span class="ml-2 text-sm" style="color: var(--simppay-text-secondary)">{{ t.golongan_ruang?.nama ?? '' }}</span>
                                    </TableCell>
                                    <TableCell class="text-right tabular-nums font-medium text-sm">{{ formatRupiah(t.nominal_maksimum) }}</TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </TabsContent>

                <TabsContent value="transport" class="mt-3">
                    <p class="text-xs mb-2" style="color: var(--simppay-text-secondary)">Tunjangan transportasi per level struktur dan golongan ruang (§11)</p>
                    <div class="bg-white rounded-xl overflow-x-auto border" style="border-color: var(--simppay-border)">
                        <Table class="min-w-[500px] table-compact">
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Level Struktur</TableHead>
                                    <TableHead>Golongan Range</TableHead>
                                    <TableHead class="text-right">Nominal</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="t in tjTransport" :key="t.id">
                                    <TableCell class="font-medium text-sm">{{ t.level_struktur?.kode ?? '-' }}</TableCell>
                                    <TableCell class="text-sm">{{ t.golongan_range ?? '-' }}</TableCell>
                                    <TableCell class="text-right tabular-nums font-medium text-sm">{{ formatRupiah(t.nominal) }}</TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </TabsContent>
            </Tabs>
        </div>
    </AuthenticatedLayout>
</template>
