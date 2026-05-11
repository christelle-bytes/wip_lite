<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Mon Profil" />

    <AuthenticatedLayout>
        <div class="py-6 max-w-4xl mx-auto space-y-10">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">Paramètres du Profil</h1>
                    <p class="mt-1 text-sm text-slate-500 font-medium">Gérez vos informations personnelles et la sécurité de votre compte.</p>
                </div>
            </div>

            <div class="grid gap-10">
                <!-- Section Informations Personnelles -->
                <div class="bg-white rounded-[40px] border border-slate-100 shadow-sm overflow-hidden">
                    <div class="px-8 py-6 bg-slate-50 border-b border-slate-100 flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-teal-500/10 flex items-center justify-center text-teal-600">
                            <i class="pi pi-user text-lg"></i>
                        </div>
                        <h2 class="text-xl font-black text-slate-900 tracking-tight">Informations Personnelles</h2>
                    </div>
                    <div class="p-10">
                        <UpdateProfileInformationForm
                            :must-verify-email="mustVerifyEmail"
                            :status="status"
                            class="max-w-2xl"
                        />
                    </div>
                </div>

                <!-- Section Sécurité -->
                <div class="bg-white rounded-[40px] border border-slate-100 shadow-sm overflow-hidden">
                    <div class="px-8 py-6 bg-slate-50 border-b border-slate-100 flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-teal-500/10 flex items-center justify-center text-teal-600">
                            <i class="pi pi-lock text-lg"></i>
                        </div>
                        <h2 class="text-xl font-black text-slate-900 tracking-tight">Sécurité & Mot de Passe</h2>
                    </div>
                    <div class="p-10">
                        <div v-if="$page.props.auth.user.must_change_password" class="mb-8 p-4 bg-rose-50 border border-rose-100 rounded-2xl flex items-start gap-4">
                            <i class="pi pi-exclamation-triangle text-rose-500 mt-1"></i>
                            <div>
                                <p class="text-sm font-black text-rose-800 uppercase tracking-wider">Action requise</p>
                                <p class="text-sm text-rose-700 font-medium">Vous utilisez actuellement un mot de passe temporaire. Pour des raisons de sécurité, vous devez le modifier pour continuer.</p>
                            </div>
                        </div>
                        <UpdatePasswordForm class="max-w-2xl" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
