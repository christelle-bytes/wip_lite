<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';

import { usePage } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import Button from 'primevue/button';
import Toast from 'primevue/toast';

const toast = useToast();
const page = usePage();

const userRole = computed(() => {
    return page.props?.auth?.user?.role;
});

const isAuthorized = computed(() => userRole.value === 'ADMIN');

onMounted(() => {
    toast.add({
        severity: 'success',
        summary: 'Bienvenue',
        detail: 'Connecté en tant qu\'Administrateur',
        life: 3000,
    });
});
</script>

<template>
    <Toast />
    <Head title="Dashboard Admin" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Dashboard Admin
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
<div class="mb-3">
                            Vous êtes connecté en tant qu'Administrateur (rôle attendu: <span class="font-mono">ADMIN</span>).
                        </div>

                                                <div>
                            Vous êtes sur le dashboard <span class="font-semibold">Admin</span>.
                        </div>

                        <div class="mt-4">
                            <Button label="OK" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
