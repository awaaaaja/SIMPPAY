<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { useRoutePrefix } from '@/composables/useRoutePrefix.js';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

const prefix = useRoutePrefix();

const props = defineProps({
    kehadiran: Object,
    pegawais: Array,
});

const k = props.kehadiran;

const form = useForm({
    pegawai_id: k.pegawai_id,
    periode: k.periode ? k.periode.substring(0, 7) : '',
    hadir: k.hadir,
    sakit: k.sakit,
    alpha: k.alpha,
});

function submit() {
    form.put(route(`${prefix.value}.kehadiran.update`, k.id));
}
</script>

<template>
    <Head title="Edit Kehadiran" />

    <AuthenticatedLayout>
        <div class="px-4 pt-6 pb-2 sm:px-6 lg:px-8 max-w-2xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-800">Edit Kehadiran</h1>
                    <p class="mt-1 text-sm text-muted-foreground">
                        {{ k.pegawai?.nama_pegawai ?? '-' }} — {{ k.periode ? new Date(k.periode).toLocaleDateString('id-ID', { month: 'long', year: 'numeric' }) : '' }}
                    </p>
                </div>
                <Link :href="route(`${prefix}.kehadiran.index`)" class="text-sm text-muted-foreground hover:underline">Kembali</Link>
            </div>

            <div class="bg-white rounded-[12px] p-6"
                style="box-shadow: 0 1px 2px rgba(0,0,0,.04), 0 8px 24px rgba(0,0,0,.04);">
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="space-y-1.5">
                        <Label>Pegawai</Label>
                        <Select v-model="form.pegawai_id">
                            <SelectTrigger class="w-full"><SelectValue placeholder="-- Pilih Pegawai --" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="p in pegawais" :key="p.id" :value="p.id">
                                    {{ p.nama_pegawai }} ({{ p.nik }})
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors.pegawai_id" class="text-sm text-destructive">{{ form.errors.pegawai_id }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <Label>Periode</Label>
                        <Input v-model="form.periode" type="month" required />
                        <p v-if="form.errors.periode" class="text-sm text-destructive">{{ form.errors.periode }}</p>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div class="space-y-1.5">
                            <Label>Hadir</Label>
                            <Input v-model.number="form.hadir" type="number" min="0" required />
                        </div>
                        <div class="space-y-1.5">
                            <Label>Sakit</Label>
                            <Input v-model.number="form.sakit" type="number" min="0" required />
                        </div>
                        <div class="space-y-1.5">
                            <Label>Alpha</Label>
                            <Input v-model.number="form.alpha" type="number" min="0" required />
                        </div>
                    </div>

                    <div class="flex items-center gap-4 pt-4">
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Menyimpan...' : 'Perbarui' }}
                        </Button>
                        <Link :href="route(`${prefix}.kehadiran.index`)" class="text-sm text-muted-foreground hover:underline">Batal</Link>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
