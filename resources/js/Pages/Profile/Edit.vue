<script setup>
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PartnerLayout from '@/Layouts/PartnerLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const page = usePage();
const user = computed(() => page.props.auth?.user);

const LayoutComponent = computed(() => {
    const role = user.value?.role;
    if (role === 'ADMIN' || role === 'SUPER_ADMIN') {
        return AdminLayout;
    }
    if (role === 'PARTNER') {
        return PartnerLayout;
    }
    return AppLayout;
});
</script>

<template>
    <Head title="Profil Akun" />

    <component :is="LayoutComponent">
        <div class="py-8 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Header Profile Identity -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl bg-primary/10 border-2 border-primary/20 text-primary font-black text-2xl flex items-center justify-center uppercase shrink-0">
                        {{ user?.name ? user.name.slice(0, 2).toUpperCase() : 'MM' }}
                    </div>
                    <div>
                        <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">{{ user?.name }}</h1>
                        <p class="text-xs text-slate-500 mt-0.5">
                            {{ user?.email }}
                            <span v-if="user?.phone_number" class="text-slate-400"> &bull; {{ user.phone_number }}</span>
                            <span class="text-slate-400"> &bull; Role: </span>
                            <span class="font-bold text-primary">{{ user?.role }}</span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-xs">
                <UpdateProfileInformationForm
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                    class="max-w-xl"
                />
            </div>

            <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-xs">
                <UpdatePasswordForm class="max-w-xl" />
            </div>

            <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-xs">
                <DeleteUserForm class="max-w-xl" />
            </div>
        </div>
    </component>
</template>
