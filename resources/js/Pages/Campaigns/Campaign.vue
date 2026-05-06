<script setup>
import { ref } from 'vue'
const props = defineProps({
    campaigns: Array,
});

const filters = ["Toutes", "Actives", "Inactives", "Terminées"]
const activeFilter = ref("Toutes")
</script>

<template>

    <Head title="Campaignes" />
    <AuthenticatedLayout>
        <pre>{{ campaigns }}</pre>
        <div class="min-h-screen bg-gray-50 p-8">
            <!-- Header & Filtres -->
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
                <div class="flex flex-wrap gap-2">
                    <button v-for="filter in filters" :key="filter" @click="activeFilter = filter" :class="[
                        'px-4 py-2 rounded-full text-sm font-medium transition-colors',
                        activeFilter === filter ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-600 hover:bg-gray-300'
                    ]">
                        {{ filter }} <span class="ml-1 opacity-70">(4)</span>
                    </button>
                </div>

                <button
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg flex items-center font-medium transition-shadow shadow-sm">
                    <span class="mr-2 text-xl">+</span> Créer une campagne
                </button>
            </div>

            <!-- Grille de Campagnes -->

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="camp in campaigns" :key="camp.id"
                    class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between">
                    <div>
                        <!-- Status Badge -->
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="text-lg font-bold text-gray-800 leading-tight pr-4">{{ camp.name }}</h3>
                            <span :class="[
                                'px-2 py-1 rounded text-xs font-semibold',
                                camp.status === 'Active' ? 'bg-green-100 text-green-600' :
                                    camp.status === 'Terminée' ? 'bg-gray-200 text-gray-600' : 'bg-gray-100 text-gray-400'
                            ]">
                                {{ camp.status }}
                            </span>
                        </div>

                        <p class="text-sm text-gray-500 mb-6">{{ camp.description }}</p>

                        <!-- Dates -->
                        <div class="flex items-center text-sm text-gray-500 mb-4">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            {{ camp.start_date }} - {{ camp.end_date }}
                        </div>

                        <!-- Stats -->
                        <div class="flex gap-4 text-xs font-bold mb-8">
                            <div class="flex items-center">
                                <span class="w-2 h-2 rounded-full bg-indigo-900 mr-2"></span>
                                {{ camp.cp_count }} CP
                            </div>
                            <div class="flex items-center">
                                <span class="w-2 h-2 rounded-full bg-blue-500 mr-2"></span>
                                {{ camp.sup_count }} SUP
                            </div>
                            <div class="flex items-center">
                                <span class="w-2 h-2 rounded-full bg-green-500 mr-2"></span>
                                {{ camp.tc_count }} TC
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-2">
                        <button
                            class="flex-1 bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-700 py-2 rounded-lg flex items-center justify-center font-medium transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Voir
                        </button>
                        <button
                            class="px-3 py-2 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                        </button>
                        <button
                            class="px-3 py-2 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 text-orange-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
