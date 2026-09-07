<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import FileUpload from '@/Components/FileUpload.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useRoutePrefix } from '@/composables/useRoutePrefix.js';

const prefix = useRoutePrefix();

const props = defineProps({
    jabatans: Array,
    strukturals: Array,
    fungsionals: Array,
});

const form = useForm({
    nik: '',
    nama_pegawai: '',
    jenis_kelamin: 'L',
    tanggal_masuk: '',
    status_pegawai: 'aktif',
    jabatan_id: '',
    struktural_id: '',
    fungsional_id: '',
    email: '',
    no_hp: '',
    alamat: '',
    tgl_lahir: '',
    ktp: '',
    nidn: '',
    id_ptk: '',
    nuptk: '',
    agama: '',
    kewarganegaraan: '',
    suku: '',
    jurusan: '',
    bidang_keahlian: '',
    no_sk: '',
    tgl_sk: '',
    status_dosen: '',
    ikatan_kerja: '',
    status_kawin: '',
    nama_sm: '',
    nip_sm: '',
    nohp_sm: '',
    pekerjaan_sm: '',
    nama_ibu: '',
    masa_jabatan: '',
    tgl_sk_jabatan: '',
    photo: null,
    foto_sk: null,
});

function submit() {
    form.post(route(`${prefix.value}.pegawai.store`));
}
</script>

<template>
    <Head title="Tambah Pegawai" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Tambah Pegawai</h2>
                <Link :href="route(`${prefix}.pegawai.index`)" class="text-sm text-gray-600 hover:underline">Kembali</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Identitas -->
                        <h3 class="text-lg font-semibold text-gray-800">Identitas</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <InputLabel for="nik" value="NIK" />
                                <TextInput id="nik" v-model="form.nik" type="text" class="mt-1 block w-full" required />
                                <InputError :message="form.errors.nik" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="nama_pegawai" value="Nama Lengkap" />
                                <TextInput id="nama_pegawai" v-model="form.nama_pegawai" type="text" class="mt-1 block w-full" required />
                                <InputError :message="form.errors.nama_pegawai" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="jenis_kelamin" value="Jenis Kelamin" />
                                <select id="jenis_kelamin" v-model="form.jenis_kelamin" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#025AB1] focus:ring-[#025AB1]">
                                    <option value="L">Laki-laki</option>
                                    <option value="P">Perempuan</option>
                                </select>
                                <InputError :message="form.errors.jenis_kelamin" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="tgl_lahir" value="Tanggal Lahir" />
                                <TextInput id="tgl_lahir" v-model="form.tgl_lahir" type="date" class="mt-1 block w-full" />
                                <InputError :message="form.errors.tgl_lahir" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="email" value="Email" />
                                <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full" />
                                <InputError :message="form.errors.email" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="no_hp" value="No. HP" />
                                <TextInput id="no_hp" v-model="form.no_hp" type="text" class="mt-1 block w-full" />
                                <InputError :message="form.errors.no_hp" class="mt-2" />
                            </div>
                            <div class="col-span-2">
                                <InputLabel for="alamat" value="Alamat" />
                                <textarea id="alamat" v-model="form.alamat" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#025AB1] focus:ring-[#025AB1]"></textarea>
                                <InputError :message="form.errors.alamat" class="mt-2" />
                            </div>
                        </div>

                        <!-- Kepegawaian -->
                        <h3 class="pt-4 text-lg font-semibold text-gray-800">Kepegawaian</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <InputLabel for="jabatan_id" value="Jabatan" />
                                <select id="jabatan_id" v-model="form.jabatan_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#025AB1] focus:ring-[#025AB1]">
                                    <option value="">-- Pilih --</option>
                                    <option v-for="j in jabatans" :key="j.id" :value="j.id">{{ j.nama_jabatan }}</option>
                                </select>
                                <InputError :message="form.errors.jabatan_id" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="struktural_id" value="Struktural" />
                                <select id="struktural_id" v-model="form.struktural_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#025AB1] focus:ring-[#025AB1]">
                                    <option value="">-- Pilih --</option>
                                    <option v-for="s in strukturals" :key="s.id" :value="s.id">{{ s.nama_struktural }}</option>
                                </select>
                                <InputError :message="form.errors.struktural_id" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="fungsional_id" value="Fungsional" />
                                <select id="fungsional_id" v-model="form.fungsional_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#025AB1] focus:ring-[#025AB1]">
                                    <option value="">-- Pilih --</option>
                                    <option v-for="f in fungsionals" :key="f.id" :value="f.id">{{ f.nama_fungsional }}</option>
                                </select>
                                <InputError :message="form.errors.fungsional_id" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="tanggal_masuk" value="Tanggal Masuk" />
                                <TextInput id="tanggal_masuk" v-model="form.tanggal_masuk" type="date" class="mt-1 block w-full" />
                                <InputError :message="form.errors.tanggal_masuk" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="status_pegawai" value="Status" />
                                <select id="status_pegawai" v-model="form.status_pegawai" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#025AB1] focus:ring-[#025AB1]">
                                    <option value="aktif">Aktif</option>
                                    <option value="nonaktif">Nonaktif</option>
                                    <option value="pensiun">Pensiun</option>
                                </select>
                                <InputError :message="form.errors.status_pegawai" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="ikatan_kerja" value="Ikatan Kerja" />
                                <TextInput id="ikatan_kerja" v-model="form.ikatan_kerja" type="text" class="mt-1 block w-full" placeholder="tetap/kontrak/honorer" />
                                <InputError :message="form.errors.ikatan_kerja" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="status_dosen" value="Status Dosen" />
                                <select id="status_dosen" v-model="form.status_dosen" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#025AB1] focus:ring-[#025AB1]">
                                    <option value="">-- Pilih --</option>
                                    <option value="dosen">Dosen</option>
                                    <option value="bukan_dosen">Bukan Dosen</option>
                                </select>
                                <InputError :message="form.errors.status_dosen" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="nidn" value="NIDN" />
                                <TextInput id="nidn" v-model="form.nidn" type="text" class="mt-1 block w-full" />
                                <InputError :message="form.errors.nidn" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="nuptk" value="NUPTK" />
                                <TextInput id="nuptk" v-model="form.nuptk" type="text" class="mt-1 block w-full" />
                                <InputError :message="form.errors.nuptk" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="no_sk" value="No. SK" />
                                <TextInput id="no_sk" v-model="form.no_sk" type="text" class="mt-1 block w-full" />
                                <InputError :message="form.errors.no_sk" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="tgl_sk" value="Tanggal SK" />
                                <TextInput id="tgl_sk" v-model="form.tgl_sk" type="date" class="mt-1 block w-full" />
                                <InputError :message="form.errors.tgl_sk" class="mt-2" />
                            </div>
                        </div>

                        <!-- Keluarga -->
                        <h3 class="pt-4 text-lg font-semibold text-gray-800">Keluarga</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <InputLabel for="status_kawin" value="Status Kawin" />
                                <select id="status_kawin" v-model="form.status_kawin" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#025AB1] focus:ring-[#025AB1]">
                                    <option value="">-- Pilih --</option>
                                    <option value="belum_kawin">Belum Kawin</option>
                                    <option value="kawin">Kawin</option>
                                    <option value="cerai">Cerai</option>
                                </select>
                                <InputError :message="form.errors.status_kawin" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="nama_sm" value="Nama Pasangan" />
                                <TextInput id="nama_sm" v-model="form.nama_sm" type="text" class="mt-1 block w-full" />
                                <InputError :message="form.errors.nama_sm" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="nama_ibu" value="Nama Ibu" />
                                <TextInput id="nama_ibu" v-model="form.nama_ibu" type="text" class="mt-1 block w-full" />
                                <InputError :message="form.errors.nama_ibu" class="mt-2" />
                            </div>
                        </div>

                        <!-- Upload -->
                        <h3 class="pt-4 text-lg font-semibold text-gray-800">Foto</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <InputLabel value="Foto Pegawai" />
                                <FileUpload
                                    v-model="form.photo"
                                    label-idle="Seret foto ke sini atau <span class='filepond--label-action'>Browse</span>"
                                />
                                <InputError :message="form.errors.photo" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel value="Foto SK Jabatan" />
                                <FileUpload
                                    v-model="form.foto_sk"
                                    label-idle="Seret foto SK ke sini atau <span class='filepond--label-action'>Browse</span>"
                                />
                                <InputError :message="form.errors.foto_sk" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4">
                            <Link :href="route(`${prefix}.pegawai.index`)" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">Batal</Link>
                            <PrimaryButton :disabled="form.processing">Simpan</PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
