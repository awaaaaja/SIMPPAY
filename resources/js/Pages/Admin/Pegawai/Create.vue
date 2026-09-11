<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import FileUpload from '@/Components/FileUpload.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { useRoutePrefix } from '@/composables/useRoutePrefix.js';
import { ref } from 'vue';

const prefix = useRoutePrefix();

const props = defineProps({
    golongans: Array,
    jabatanStrukturalPoin: Array,
});

const createUser = ref(false);

const form = useForm({
    nik: '',
    nama_pegawai: '',
    jenis_kelamin: 'L',
    tanggal_masuk: '',
    tmt: '',
    status_pegawai: 'aktif',
    jabatan_struktural_poin_id: '',
    golongan_ruang_id: '',
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
    strata_pendidikan: '',
    program_mengajar: '',
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
    create_user: false,
    user_username: '',
    user_password: '',
    user_role: 'pegawai',
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
                                <Label for="nik">NIK</Label>
                                <Input id="nik" v-model="form.nik" type="text" class="mt-1" required />
                                <p v-if="form.errors.nik" class="mt-1 text-sm text-destructive">{{ form.errors.nik }}</p>
                            </div>
                            <div>
                                <Label for="nama_pegawai">Nama Lengkap</Label>
                                <Input id="nama_pegawai" v-model="form.nama_pegawai" type="text" class="mt-1" required />
                                <p v-if="form.errors.nama_pegawai" class="mt-1 text-sm text-destructive">{{ form.errors.nama_pegawai }}</p>
                            </div>
                            <div>
                                <Label>Jenis Kelamin</Label>
                                <Select v-model="form.jenis_kelamin">
                                    <SelectTrigger class="mt-1 w-full"><SelectValue /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="L">Laki-laki</SelectItem>
                                        <SelectItem value="P">Perempuan</SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.jenis_kelamin" class="mt-1 text-sm text-destructive">{{ form.errors.jenis_kelamin }}</p>
                            </div>
                            <div>
                                <Label for="tgl_lahir">Tanggal Lahir</Label>
                                <Input id="tgl_lahir" v-model="form.tgl_lahir" type="date" class="mt-1" />
                                <p v-if="form.errors.tgl_lahir" class="mt-1 text-sm text-destructive">{{ form.errors.tgl_lahir }}</p>
                            </div>
                            <div>
                                <Label for="email">Email</Label>
                                <Input id="email" v-model="form.email" type="email" class="mt-1" />
                                <p v-if="form.errors.email" class="mt-1 text-sm text-destructive">{{ form.errors.email }}</p>
                            </div>
                            <div>
                                <Label for="no_hp">No. HP</Label>
                                <Input id="no_hp" v-model="form.no_hp" type="text" class="mt-1" />
                                <p v-if="form.errors.no_hp" class="mt-1 text-sm text-destructive">{{ form.errors.no_hp }}</p>
                            </div>
                            <div class="col-span-2">
                                <Label for="alamat">Alamat</Label>
                                <textarea id="alamat" v-model="form.alamat" rows="3" class="mt-1 flex w-full rounded-lg border border-input bg-transparent px-2.5 py-1 text-base transition-colors focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-3 md:text-sm placeholder:text-muted-foreground"></textarea>
                                <p v-if="form.errors.alamat" class="mt-1 text-sm text-destructive">{{ form.errors.alamat }}</p>
                            </div>
                        </div>

                        <!-- Kepegawaian -->
                        <h3 class="pt-4 text-lg font-semibold text-gray-800">Kepegawaian</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <Label>Jabatan Struktural</Label>
                                <Select v-model="form.jabatan_struktural_poin_id">
                                    <SelectTrigger class="mt-1 w-full"><SelectValue placeholder="-- Pilih --" /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="j in jabatanStrukturalPoin" :key="j.id" :value="j.id">{{ j.nama_jabatan }}</SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.jabatan_struktural_poin_id" class="mt-1 text-sm text-destructive">{{ form.errors.jabatan_struktural_poin_id }}</p>
                            </div>
                            <div>
                                <Label>Golongan Ruang</Label>
                                <Select v-model="form.golongan_ruang_id">
                                    <SelectTrigger class="mt-1 w-full"><SelectValue placeholder="-- Pilih --" /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="g in golongans" :key="g.id" :value="g.id">{{ g.kode }}</SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.golongan_ruang_id" class="mt-1 text-sm text-destructive">{{ form.errors.golongan_ruang_id }}</p>
                            </div>
                            <div>
                                <Label for="tanggal_masuk">Tanggal Masuk</Label>
                                <Input id="tanggal_masuk" v-model="form.tanggal_masuk" type="date" class="mt-1" />
                                <p v-if="form.errors.tanggal_masuk" class="mt-1 text-sm text-destructive">{{ form.errors.tanggal_masuk }}</p>
                            </div>
                            <div>
                                <Label for="tmt">TMT (Tanggal Mulai Kerja)</Label>
                                <Input id="tmt" v-model="form.tmt" type="date" class="mt-1" />
                                <p v-if="form.errors.tmt" class="mt-1 text-sm text-destructive">{{ form.errors.tmt }}</p>
                            </div>
                            <div>
                                <Label>Status</Label>
                                <Select v-model="form.status_pegawai">
                                    <SelectTrigger class="mt-1 w-full"><SelectValue /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="aktif">Aktif</SelectItem>
                                        <SelectItem value="nonaktif">Nonaktif</SelectItem>
                                        <SelectItem value="pensiun">Pensiun</SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.status_pegawai" class="mt-1 text-sm text-destructive">{{ form.errors.status_pegawai }}</p>
                            </div>
                            <div>
                                <Label for="ikatan_kerja">Ikatan Kerja</Label>
                                <Input id="ikatan_kerja" v-model="form.ikatan_kerja" type="text" class="mt-1" placeholder="tetap/kontrak/honorer" />
                                <p v-if="form.errors.ikatan_kerja" class="mt-1 text-sm text-destructive">{{ form.errors.ikatan_kerja }}</p>
                            </div>
                            <div>
                                <Label>Status Dosen</Label>
                                <Select v-model="form.status_dosen">
                                    <SelectTrigger class="mt-1 w-full"><SelectValue placeholder="-- Pilih --" /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="dosen">Dosen</SelectItem>
                                        <SelectItem value="bukan_dosen">Bukan Dosen</SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.status_dosen" class="mt-1 text-sm text-destructive">{{ form.errors.status_dosen }}</p>
                            </div>
                            <div>
                                <Label>Strata Pendidikan</Label>
                                <Select v-model="form.strata_pendidikan">
                                    <SelectTrigger class="mt-1 w-full"><SelectValue placeholder="-- Pilih --" /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="profesor">Profesor</SelectItem>
                                        <SelectItem value="s3">S.3 / Dr</SelectItem>
                                        <SelectItem value="s2">S.2 / Magister</SelectItem>
                                        <SelectItem value="lainnya">Lainnya</SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.strata_pendidikan" class="mt-1 text-sm text-destructive">{{ form.errors.strata_pendidikan }}</p>
                            </div>
                            <div>
                                <Label>Program Mengajar</Label>
                                <Select v-model="form.program_mengajar">
                                    <SelectTrigger class="mt-1 w-full"><SelectValue placeholder="-- Pilih --" /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="s1">Program S1</SelectItem>
                                        <SelectItem value="s2">Program S2</SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.program_mengajar" class="mt-1 text-sm text-destructive">{{ form.errors.program_mengajar }}</p>
                            </div>
                            <div>
                                <Label for="nidn">NIDN</Label>
                                <Input id="nidn" v-model="form.nidn" type="text" class="mt-1" />
                                <p v-if="form.errors.nidn" class="mt-1 text-sm text-destructive">{{ form.errors.nidn }}</p>
                            </div>
                            <div>
                                <Label for="nuptk">NUPTK</Label>
                                <Input id="nuptk" v-model="form.nuptk" type="text" class="mt-1" />
                                <p v-if="form.errors.nuptk" class="mt-1 text-sm text-destructive">{{ form.errors.nuptk }}</p>
                            </div>
                            <div>
                                <Label for="no_sk">No. SK</Label>
                                <Input id="no_sk" v-model="form.no_sk" type="text" class="mt-1" />
                                <p v-if="form.errors.no_sk" class="mt-1 text-sm text-destructive">{{ form.errors.no_sk }}</p>
                            </div>
                            <div>
                                <Label for="tgl_sk">Tanggal SK</Label>
                                <Input id="tgl_sk" v-model="form.tgl_sk" type="date" class="mt-1" />
                                <p v-if="form.errors.tgl_sk" class="mt-1 text-sm text-destructive">{{ form.errors.tgl_sk }}</p>
                            </div>
                        </div>

                        <!-- Keluarga -->
                        <h3 class="pt-4 text-lg font-semibold text-gray-800">Keluarga</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <Label>Status Kawin</Label>
                                <Select v-model="form.status_kawin">
                                    <SelectTrigger class="mt-1 w-full"><SelectValue placeholder="-- Pilih --" /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="belum_kawin">Belum Kawin</SelectItem>
                                        <SelectItem value="kawin">Kawin</SelectItem>
                                        <SelectItem value="cerai">Cerai</SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.status_kawin" class="mt-1 text-sm text-destructive">{{ form.errors.status_kawin }}</p>
                            </div>
                            <div>
                                <Label for="nama_sm">Nama Pasangan</Label>
                                <Input id="nama_sm" v-model="form.nama_sm" type="text" class="mt-1" />
                                <p v-if="form.errors.nama_sm" class="mt-1 text-sm text-destructive">{{ form.errors.nama_sm }}</p>
                            </div>
                            <div>
                                <Label for="nama_ibu">Nama Ibu</Label>
                                <Input id="nama_ibu" v-model="form.nama_ibu" type="text" class="mt-1" />
                                <p v-if="form.errors.nama_ibu" class="mt-1 text-sm text-destructive">{{ form.errors.nama_ibu }}</p>
                            </div>
                        </div>

                        <!-- Akun User -->
                        <h3 class="pt-4 text-lg font-semibold text-gray-800">Akun User</h3>
                        <div class="space-y-4">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" v-model="createUser" @change="form.create_user = createUser"
                                    class="h-4 w-4 rounded border-gray-300 text-[#025AB1] focus:ring-[#025AB1]" />
                                <span class="text-sm text-gray-700">Buat akun login untuk pegawai ini</span>
                            </label>
                            <div v-if="createUser" class="grid grid-cols-2 gap-4 pl-6 border-l-2 border-[#025AB1]/20">
                                <div>
                                    <Label for="user_username">Username</Label>
                                    <Input id="user_username" v-model="form.user_username" type="text" class="mt-1" placeholder="Default: NIK" />
                                    <p v-if="form.errors.user_username" class="mt-1 text-sm text-destructive">{{ form.errors.user_username }}</p>
                                </div>
                                <div>
                                    <Label for="user_password">Password</Label>
                                    <Input id="user_password" v-model="form.user_password" type="text" class="mt-1" placeholder="Default: password123" />
                                    <p v-if="form.errors.user_password" class="mt-1 text-sm text-destructive">{{ form.errors.user_password }}</p>
                                </div>
                                <div>
                                    <Label>Role</Label>
                                    <Select v-model="form.user_role">
                                        <SelectTrigger class="mt-1 w-full"><SelectValue /></SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="pegawai">Pegawai</SelectItem>
                                            <SelectItem value="tendik">Tendik</SelectItem>
                                            <SelectItem value="bpsdm">BPSDM</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>
                        </div>

                        <!-- Upload -->
                        <h3 class="pt-4 text-lg font-semibold text-gray-800">Foto</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <Label>Foto Pegawai</Label>
                                <FileUpload v-model="form.photo" label-idle="Seret foto ke sini atau <span class='filepond--label-action'>Browse</span>" />
                                <p v-if="form.errors.photo" class="mt-1 text-sm text-destructive">{{ form.errors.photo }}</p>
                            </div>
                            <div>
                                <Label>Foto SK Jabatan</Label>
                                <FileUpload v-model="form.foto_sk" label-idle="Seret foto SK ke sini atau <span class='filepond--label-action'>Browse</span>" />
                                <p v-if="form.errors.foto_sk" class="mt-1 text-sm text-destructive">{{ form.errors.foto_sk }}</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4">
                            <Link :href="route(`${prefix}.pegawai.index`)" class="rounded-lg border border-input bg-background px-4 py-2 text-sm font-medium hover:bg-accent hover:text-accent-foreground">Batal</Link>
                            <Button type="submit" :disabled="form.processing">Simpan</Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
