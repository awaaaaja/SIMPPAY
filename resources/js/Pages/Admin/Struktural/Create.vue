<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { useRoutePrefix } from '@/composables/useRoutePrefix.js';

const prefix = useRoutePrefix();

const form = useForm({
    nama_struktural: '',
    level_struktural: '',
    status: 'aktif',
});

function submit() {
    form.post(route(`${prefix.value}.struktural.store`));
}
</script>

<template>
    <Head title="Tambah Struktural" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Tambah Struktural</h2>
                <Link :href="route(`${prefix}.struktural.index`)" class="text-sm text-gray-600 hover:underline">Kembali</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <Label for="nama_struktural">Nama Struktural</Label>
                            <Input id="nama_struktural" v-model="form.nama_struktural" type="text" class="mt-1" required />
                            <p v-if="form.errors.nama_struktural" class="mt-1 text-sm text-destructive">{{ form.errors.nama_struktural }}</p>
                        </div>

                        <div>
                            <Label for="level_struktural">Level</Label>
                            <Input id="level_struktural" v-model="form.level_struktural" type="text" class="mt-1" />
                            <p v-if="form.errors.level_struktural" class="mt-1 text-sm text-destructive">{{ form.errors.level_struktural }}</p>
                        </div>

                        <div>
                            <Label>Status</Label>
                            <Select v-model="form.status">
                                <SelectTrigger class="mt-1 w-full">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="aktif">Aktif</SelectItem>
                                    <SelectItem value="nonaktif">Nonaktif</SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.status" class="mt-1 text-sm text-destructive">{{ form.errors.status }}</p>
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <Link :href="route(`${prefix}.struktural.index`)" class="rounded-lg border border-input bg-background px-4 py-2 text-sm font-medium hover:bg-accent hover:text-accent-foreground">Batal</Link>
                            <Button type="submit" :disabled="form.processing">Simpan</Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
