<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

// Ici, on recevra plus tard les données du contrôleur
const props = defineProps({
    stats: Object
});
</script>

<template>
    <Head title="Reporting & Statistiques" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Reporting & Statistiques
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Zone où on va mettre nos cartes de statistiques tout à l'heure -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-gray-900">Bienvenue sur le module de statistiques !</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>