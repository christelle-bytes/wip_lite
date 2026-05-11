<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';

const props = defineProps({
    logs: Object
});

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getActionBadge = (action) => {
    switch (action.toLowerCase()) {
        case 'created':
        case 'created ': return 'bg-teal-100 text-teal-700';
        case 'updated':
        case 'updated ': return 'bg-amber-100 text-amber-700';
        case 'deleted':
        case 'deleted ': return 'bg-rose-100 text-rose-700';
        default: return 'bg-slate-100 text-slate-600';
    }
};
</script>

<template>
    <Head title="Logs d'activité — Admin" />

    <AuthenticatedLayout>
        <div class="py-6 space-y-8">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">Logs d'activité</h1>
                    <p class="mt-1 text-sm text-slate-500 font-medium">Historique complet des actions effectuées sur la plateforme.</p>
                </div>
            </div>

            <!-- Table Card -->
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                <DataTable
                    :value="logs.data"
                    class="p-datatable-custom"
                    responsiveLayout="stack"
                    breakpoint="960px"
                    :pt="{
                        thead: { class: 'bg-slate-50 border-b border-slate-100' },
                        th: { class: 'px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 border-none bg-transparent' },
                        tbody: { class: 'divide-y divide-slate-50' },
                        tr: { class: 'hover:bg-slate-50/50 transition-colors group' },
                        td: { class: 'px-6 py-4 border-none align-middle' }
                    }"
                >
                    <Column header="Date & Heure">
                        <template #body="{ data }">
                            <span class="text-xs font-bold text-slate-500 italic">{{ formatDate(data.created_at) }}</span>
                        </template>
                    </Column>
                    
                    <Column header="Utilisateur">
                        <template #body="{ data }">
                            <div v-if="data.user" class="flex items-center gap-3">
                                <div class="h-8 w-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500 font-black text-[10px]">
                                    {{ data.user.employee?.first_name?.[0] }}{{ data.user.employee?.last_name?.[0] }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-black text-slate-800 truncate">{{ data.user.employee?.first_name }} {{ data.user.employee?.last_name }}</p>
                                    <p class="text-[10px] font-medium text-slate-400 truncate">{{ data.user.email }}</p>
                                </div>
                            </div>
                            <span v-else class="text-[10px] font-black text-slate-300 italic">Système</span>
                        </template>
                    </Column>

                    <Column header="Action">
                        <template #body="{ data }">
                            <span :class="['rounded-lg px-2.5 py-1 text-[9px] font-black uppercase tracking-widest', getActionBadge(data.action)]">
                                {{ data.action }}
                            </span>
                        </template>
                    </Column>

                    <Column header="Entité">
                        <template #body="{ data }">
                            <span class="text-[10px] font-black text-slate-900 bg-slate-100 px-2 py-0.5 rounded uppercase tracking-wider">
                                {{ data.model_type?.split('\\').pop() }}
                            </span>
                        </template>
                    </Column>

                    <Column field="description" header="Description">
                        <template #body="{ data }">
                            <p class="text-xs font-medium text-slate-600 leading-relaxed">{{ data.description }}</p>
                        </template>
                    </Column>

                    <Column header="Adresse IP">
                        <template #body="{ data }">
                            <span class="text-[10px] font-mono font-bold text-slate-400">{{ data.ip_address || '—' }}</span>
                        </template>
                    </Column>
                </DataTable>

                <!-- Custom Pagination -->
                <div class="px-8 py-6 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                        Page {{ logs.current_page }} sur {{ logs.last_page }}
                    </p>
                    <div class="flex gap-2">
                        <Link v-for="link in logs.links" :key="link.label"
                            :href="link.url || '#'"
                            v-html="link.label"
                            :class="[
                                'px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all',
                                link.active ? 'bg-teal-600 text-white shadow-lg shadow-teal-600/20' : 'bg-white border border-slate-200 text-slate-400 hover:border-teal-500 hover:text-teal-600',
                                !link.url ? 'opacity-30 cursor-not-allowed' : ''
                            ]"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
