<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import { User, Camera, Lock, CheckCircle } from '@lucide/vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

const props = defineProps({
    user: Object,
    roles: Array,
    status: String,
});

const avatarInput = ref(null);
const avatarPreview = ref(props.user.avatar ? `/storage/${props.user.avatar}` : null);

const profileForm = useForm({
    name: props.user.name,
    email: props.user.email,
    avatar: null,
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

function selectAvatar() {
    avatarInput.value.click();
}

function onAvatarChange(e) {
    const file = e.target.files[0];
    if (!file) return;
    profileForm.avatar = file;
    avatarPreview.value = URL.createObjectURL(file);
}

function updateProfile() {
    profileForm.transform((data) => ({
        ...data,
        _method: 'patch',
    })).post(route('profile.update'), {
        preserveScroll: true,
        forceFormData: true,
    });
}

function updatePassword() {
    passwordForm.put(route('profile.password.update'), {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
        onError: () => {
            if (passwordForm.errors.password) {
                passwordForm.reset('password', 'password_confirmation');
                passwordInput.value?.focus();
            }
            if (passwordForm.errors.current_password) {
                passwordForm.reset('current_password');
                currentPasswordInput.value?.focus();
            }
        },
    });
}
</script>

<template>
    <Head title="Profil Saya" />

    <AuthenticatedLayout>
        <div class="p-4 sm:p-6 lg:p-8 max-w-3xl mx-auto space-y-6">
            <!-- Success message -->
            <div v-if="$page.props.flash?.success"
                class="flex items-center gap-2 rounded-[12px] bg-emerald-50 border border-emerald-200 p-4 text-sm text-emerald-700">
                <CheckCircle class="h-4 w-4 shrink-0" />
                {{ $page.props.flash.success }}
            </div>

            <!-- Profile Info -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-base">Informasi Profil</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="updateProfile" class="space-y-6">
                        <!-- Avatar -->
                        <div class="flex items-center gap-6">
                            <div class="relative group">
                                <div @click="selectAvatar"
                                    class="flex h-20 w-20 items-center justify-center rounded-full overflow-hidden cursor-pointer border-2 border-dashed border-gray-200 hover:border-[#025AB1] transition-colors">
                                    <img v-if="avatarPreview" :src="avatarPreview" class="h-full w-full object-cover" alt="Avatar" />
                                    <User v-else class="h-8 w-8 text-gray-300" :stroke-width="1.5" />
                                </div>
                                <button type="button" @click="selectAvatar"
                                    class="absolute bottom-0 right-0 flex h-7 w-7 items-center justify-center rounded-full bg-[#025AB1] text-white shadow-sm hover:bg-[#014A96] transition">
                                    <Camera class="h-3.5 w-3.5" :stroke-width="2" />
                                </button>
                                <input ref="avatarInput" type="file" accept="image/*" class="hidden" @change="onAvatarChange" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-foreground">Foto Profil</p>
                                <p class="text-xs text-muted-foreground">JPG atau PNG, maks 2MB</p>
                            </div>
                        </div>

                        <!-- Name -->
                        <div class="space-y-1.5">
                            <Label for="name">Nama Lengkap</Label>
                            <Input id="name" v-model="profileForm.name" />
                            <p v-if="profileForm.errors.name" class="text-sm text-destructive">{{ profileForm.errors.name }}</p>
                        </div>

                        <!-- Email -->
                        <div class="space-y-1.5">
                            <Label for="email">Email</Label>
                            <Input id="email" v-model="profileForm.email" type="email" />
                            <p v-if="profileForm.errors.email" class="text-sm text-destructive">{{ profileForm.errors.email }}</p>
                        </div>

                        <!-- Username (read-only) -->
                        <div class="space-y-1.5">
                            <Label for="username">Username</Label>
                            <Input id="username" :value="user.username" disabled class="bg-gray-50 text-muted-foreground" />
                        </div>

                        <!-- Role -->
                        <div class="space-y-1.5">
                            <Label>Role</Label>
                            <div class="flex gap-2">
                                <span v-for="role in roles" :key="role"
                                    class="inline-flex items-center rounded-full bg-[#025AB1]/10 px-2.5 py-0.5 text-xs font-medium text-[#025AB1]">
                                    {{ role.toUpperCase() }}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <Button type="submit" :disabled="profileForm.processing">
                                {{ profileForm.processing ? 'Menyimpan...' : 'Simpan Profil' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <!-- Change Password -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-base flex items-center gap-2">
                        <Lock class="h-4 w-4" />
                        Ganti Password
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="updatePassword" class="space-y-4">
                        <div class="space-y-1.5">
                            <Label for="current_password">Password Saat Ini</Label>
                            <Input id="current_password" ref="currentPasswordInput"
                                v-model="passwordForm.current_password" type="password" autocomplete="current-password" />
                            <p v-if="passwordForm.errors.current_password" class="text-sm text-destructive">{{ passwordForm.errors.current_password }}</p>
                        </div>

                        <div class="space-y-1.5">
                            <Label for="password">Password Baru</Label>
                            <Input id="password" ref="passwordInput"
                                v-model="passwordForm.password" type="password" autocomplete="new-password" />
                            <p v-if="passwordForm.errors.password" class="text-sm text-destructive">{{ passwordForm.errors.password }}</p>
                        </div>

                        <div class="space-y-1.5">
                            <Label for="password_confirmation">Konfirmasi Password Baru</Label>
                            <Input id="password_confirmation"
                                v-model="passwordForm.password_confirmation" type="password" autocomplete="new-password" />
                        </div>

                        <div class="flex items-center gap-4">
                            <Button type="submit" variant="outline" :disabled="passwordForm.processing">
                                {{ passwordForm.processing ? 'Menyimpan...' : 'Ubah Password' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
