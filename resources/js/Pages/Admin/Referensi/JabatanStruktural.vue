<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Badge } from '@/components/ui/badge';

const props = defineProps({
    levels: Array,
    klasifikasi: Array,
    jabatan: Array,
});

function formatRupiah(val) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
}
</script>

<template>
    <Head title="Jabatan Struktural" />

    <AuthenticatedLayout>
        <div class="px-4 pt-5 pb-2 sm:px-6 lg:px-8 max-w-[1400px] mx-auto">
            <div class="mb-4">
                <h1 class="text-lg font-semibold" style="color: var(--simppay-text)">Jabatan Struktural</h1>
                <p class="text-sm mt-0.5" style="color: var(--simppay-text-secondary)">Level struktur, klasifikasi poin, dan daftar jabatan struktural beserta nilai tunjangannya</p>
            </div>

            <Tabs default-value="jabatan">
                <TabsList>
                    <TabsTrigger value="jabatan">Daftar Jabatan ({{ jabatan.length }})</TabsTrigger>
                    <TabsTrigger value="level">Level Struktur ({{ levels.length }})</TabsTrigger>
                    <TabsTrigger value="klasifikasi">Klasifikasi ({{ klasifikasi.length }})</TabsTrigger>
                </TabsList>

                <TabsContent value="jabatan" class="mt-3">
                    <div class="bg-white rounded-xl overflow-x-auto border" style="border-color: var(--simppay-border)">
                        <Table class="table-compact">
                            <TableHeader>
                                <TableRow>
                                    <TableHead class="w-12">No</TableHead>
                                    <TableHead>Nama Jabatan</TableHead>
                                    <TableHead>Level</TableHead>
                                    <TableHead>Klasifikasi</TableHead>
                                    <TableHead class="text-right">Total Poin</TableHead>
                                    <TableHead class="text-right">Tunjangan</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="(j, i) in jabatan" :key="j.id">
                                    <TableCell class="text-muted-foreground">{{ i + 1 }}</TableCell>
                                    <TableCell class="font-medium text-sm">{{ j.nama_jabatan }}</TableCell>
                                    <TableCell>
                                        <Badge variant="outline" class="text-[11px]">{{ j.level_struktur?.kode ?? '-' }}</Badge>
                                    </TableCell>
                                    <TableCell>
                                        <Badge variant="secondary" class="text-[11px]">K{{ j.klasifikasi?.klasifikasi ?? '-' }}</Badge>
                                    </TableCell>
                                    <TableCell class="text-right tabular-nums font-medium text-sm">{{ j.total_poin }}</TableCell>
                                    <TableCell class="text-right tabular-nums text-sm">{{ formatRupiah(j.tunjangan_baru) }}</TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </TabsContent>

                <TabsContent value="level" class="mt-3">
                    <div class="bg-white rounded-xl overflow-hidden border" style="border-color: var(--simppay-border)">
                        <Table class="table-compact">
                            <TableHeader>
                                <TableRow>
                                    <TableHead class="w-12">No</TableHead>
                                    <TableHead>Kode</TableHead>
                                    <TableHead>Nama</TableHead>
                                    <TableHead class="text-right">Urutan</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="l in levels" :key="l.id">
                                    <TableCell class="text-muted-foreground">{{ l.id }}</TableCell>
                                    <TableCell class="font-medium">{{ l.kode }}</TableCell>
                                    <TableCell>{{ l.nama }}</TableCell>
                                    <TableCell class="text-right tabular-nums">{{ l.urutan }}</TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </TabsContent>

                <TabsContent value="klasifikasi" class="mt-3">
                    <div class="bg-white rounded-xl overflow-hidden border" style="border-color: var(--simppay-border)">
                        <Table class="table-compact">
                            <TableHeader>
                                <TableRow>
                                    <TableHead class="w-12">Klasifikasi</TableHead>
                                    <TableHead>Level Jabatan</TableHead>
                                    <TableHead class="text-right">Poin Min</TableHead>
                                    <TableHead class="text-right">Poin Max</TableHead>
                                    <TableHead class="text-right">Tunj. Min</TableHead>
                                    <TableHead class="text-right">Tunj. Max</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="k in klasifikasi" :key="k.id">
                                    <TableCell>
                                        <Badge class="text-[11px]">K{{ k.klasifikasi }}</Badge>
                                    </TableCell>
                                    <TableCell class="font-medium text-sm">{{ k.level_jabatan }}</TableCell>
                                    <TableCell class="text-right tabular-nums text-sm">{{ k.poin_min }}</TableCell>
                                    <TableCell class="text-right tabular-nums text-sm">{{ k.poin_max }}</TableCell>
                                    <TableCell class="text-right tabular-nums text-sm">{{ formatRupiah(k.tunjangan_min) }}</TableCell>
                                    <TableCell class="text-right tabular-nums text-sm">{{ formatRupiah(k.tunjangan_max) }}</TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </TabsContent>
            </Tabs>
        </div>
    </AuthenticatedLayout>
</template>
