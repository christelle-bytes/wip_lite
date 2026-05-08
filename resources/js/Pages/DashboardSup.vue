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

const isAuthorized = computed(() => userRole.value?.name?.toUpperCase() === 'SUP');

onMounted(() => {
    toast.add({
        severity: 'success',
        summary: 'Bienvenue',
        detail: 'Connecté en tant que Superviseur',
        life: 3000,
    });
});
</script>

<template>
    <Toast />
    <Head title="Dashboard Superviseur" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Dashboard Superviseur
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="mb-3">
                            Vous êtes connecté en tant que Superviseur (rôle attendu: <span class="font-mono">SUP</span>).
                        </div>

                        <div>
                            <div class="space-y-4">
                                <p>Vous êtes sur le dashboard <span class="font-semibold">Superviseur</span>.</p>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <div class="bg-orange-50 p-4 rounded-lg">
                                        <h3 class="font-semibold text-orange-800 mb-2">Timesheets</h3>
                                        <p class="text-orange-600 text-sm">Validez les feuilles de temps</p>
                                    </div>
                                    <div class="bg-blue-50 p-4 rounded-lg">
                                        <h3 class="font-semibold text-blue-800 mb-2">Planning</h3>
                                        <p class="text-blue-600 text-sm">Gérez les plannings des techniciens</p>
                                    </div>
                                    <div class="bg-green-50 p-4 rounded-lg">
                                        <h3 class="font-semibold text-green-800 mb-2">Employés</h3>
                                        <p class="text-green-600 text-sm">Suivez les performances des équipes</p>
                                    </div>
                                    <div class="bg-purple-50 p-4 rounded-lg">
                                        <h3 class="font-semibold text-purple-800 mb-2">Campagnes</h3>
                                        <p class="text-purple-600 text-sm">Supervisez les campagnes en cours</p>
                                    </div>
                                    <div class="bg-red-50 p-4 rounded-lg">
                                        <h3 class="font-semibold text-red-800 mb-2">Notifications</h3>
                                        <p class="text-red-600 text-sm">Gérez les alertes et notifications</p>
                                    </div>
                                    <div class="bg-yellow-50 p-4 rounded-lg">
                                        <h3 class="font-semibold text-yellow-800 mb-2">Rapports</h3>
                                        <p class="text-yellow-600 text-sm">Générez des rapports d'activité</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <Button label="Actualiser" icon="pi pi-refresh" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>