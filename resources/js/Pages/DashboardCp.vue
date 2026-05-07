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

const isAuthorized = computed(() => userRole.value === 'CP');

onMounted(() => {
    toast.add({
        severity: 'success',
        summary: 'Bienvenue',
        detail: 'Connecté en tant que Chef Plateau',
        life: 3000,
    });
});
</script>

<template>
    <Toast />
    <Head title="Dashboard CP" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Dashboard Chef Plateau
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="mb-3">
                            Vous êtes connecté en tant que Chef Plateau (rôle attendu: <span class="font-mono">CP</span>).
                        </div>

                        <div>
                            <div class="space-y-4">
                                <p>Vous êtes sur le dashboard <span class="font-semibold">Chef Plateau</span>.</p>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <div class="bg-blue-50 p-4 rounded-lg">
                                        <h3 class="font-semibold text-blue-800 mb-2">Planning</h3>
                                        <p class="text-blue-600 text-sm">Consultez les plannings des interventions</p>
                                    </div>
                                    <div class="bg-green-50 p-4 rounded-lg">
                                        <h3 class="font-semibold text-green-800 mb-2">Campagnes</h3>
                                        <p class="text-green-600 text-sm">Suivez vos campagnes en cours</p>
                                    </div>
                                    <div class="bg-purple-50 p-4 rounded-lg">
                                        <h3 class="font-semibold text-purple-800 mb-2">Rapports</h3>
                                        <p class="text-purple-600 text-sm">Téléchargez les rapports d'intervention</p>
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