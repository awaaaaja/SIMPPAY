<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';

const props = defineProps({
    golongans: Array,
    scales: Array,
});

function formatRupiah(val) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
}

const mkgSteps = [0, 2, 4, 6, 8, 10, 12, 14, 16, 18, 20, 22, 24, 26, 28, 30];

function getScale(golId, mkg) {
    return props.scales.find(s => s.golongan_ruang_id === golId && s.mkg === mkg);
}
</script>

<template>
    <Head title="Golongan & Gaji Pokok" />

    <AuthenticatedLayout>
        <div class="px-4 pt-5 pb-2 sm:px-6 lg:px-8 max-w-[1400px] mx-auto">
            <div class="mb-4">
                <h1 class="text-lg font-semibold" style="color: var(--simppay-text)">Golongan & Gaji Pokok</h1>
                <p class="text-sm mt-0.5" style="color: var(--simppay-text-secondary)">Referensi golongan ruang (I/A — IV/E) dan tabel gaji pokok per masa kerja golongan</p>
            </div>

            <Tabs default-value="golongan">
                <TabsList>
                    <TabsTrigger value="golongan">Golongan Ruang</TabsTrigger>
                    <TabsTrigger value="gaji-pokok">Tabel Gaji Pokok</TabsTrigger>
                </TabsList>

                <TabsContent value="golongan" class="mt-3">
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
                                <TableRow v-for="g in golongans" :key="g.id">
                                    <TableCell class="text-muted-foreground">{{ g.id }}</TableCell>
                                    <TableCell class="font-medium">{{ g.kode }}</TableCell>
                                    <TableCell>{{ g.nama }}</TableCell>
                                    <TableCell class="text-right tabular-nums">{{ g.urutan }}</TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </TabsContent>

                <TabsContent value="gaji-pokok" class="mt-3">
                    <p class="text-xs mb-2" style="color: var(--simppay-text-secondary)">17 golongan × 16 MKG (0—30 tahun). Berdasarkan DOCX "Rumusan Penggajian UA".</p>
                    <div class="bg-white rounded-xl overflow-x-auto border" style="border-color: var(--simppay-border)">
                        <Table class="min-w-[900px] table-compact">
                            <TableHeader>
                                <TableRow>
                                    <TableHead class="sticky left-0 bg-white z-10">MKG</TableHead>
                                    <TableHead v-for="g in golongans" :key="g.id" class="text-center text-xs whitespace-nowrap">
                                        {{ g.kode }}
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="mkg in mkgSteps" :key="mkg">
                                    <TableCell class="sticky left-0 bg-white z-10 font-medium tabular-nums">{{ mkg }} thn</TableCell>
                                    <TableCell v-for="g in golongans" :key="g.id" class="text-center text-xs tabular-nums">
                                        {{ getScale(g.id, mkg) ? formatRupiah(getScale(g.id, mkg).nominal) : '—' }}
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </TabsContent>
            </Tabs>
        </div>
    </AuthenticatedLayout>
</template>
