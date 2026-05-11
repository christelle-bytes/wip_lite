<script setup>
import { computed } from "vue";
import { router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Button from "primevue/button";

const props = defineProps({
    assignments: Array,
});

const pendingAssignments = computed(() => {
    return (props.assignments ?? []).filter(a => a.status === 'en attente');
});

function formatDate(date) {
    if (!date) return "—";
    return new Date(date).toLocaleDateString("fr-FR");
}

function employeeName(employee) {
    if (!employee) return "—";
    return `${employee.first_name} ${employee.last_name}`;
}

function changeStatus(assignment, status) {
    router.patch(route("planning-assignments.changeStatus", assignment.id), {
        status: status,
    });
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="py-6 space-y-8">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <button @click="router.get('/planning')" class="h-8 w-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-teal-50 hover:text-teal-600 transition-all">
                            <i class="pi pi-arrow-left text-xs"></i>
                        </button>
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Retour aux plannings</span>
                    </div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">Validation des Plannings</h1>
                    <p class="mt-1 text-sm text-slate-500 font-medium">Approuvez ou rejetez les demandes d'affectation en attente.</p>
                </div>
            </div>

            <!-- Stats Summary -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm flex items-center gap-4">
                    <div class="h-12 w-12 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center text-xl shadow-sm">
                        <i class="pi pi-clock"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">En attente</p>
                        <p class="text-2xl font-black text-slate-900">{{ pendingAssignments.length }}</p>
                    </div>
                </div>
            </div>

            <!-- Content Card -->
            <div class="bg-white rounded-[40px] border border-slate-100 p-8 shadow-sm space-y-8 overflow-hidden">
                <div class="flex items-center gap-3">
                    <div class="h-8 w-8 rounded-xl bg-slate-900 text-white flex items-center justify-center text-sm shadow-sm">
                        <i class="pi pi-check-square"></i>
                    </div>
                    <h2 class="text-xl font-black text-slate-900 tracking-tight">Demandes à traiter</h2>
                </div>

                <div class="overflow-x-auto rounded-3xl border border-slate-50">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-slate-50/50">
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Collaborateur</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Modèle Demandé</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Période</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-if="pendingAssignments.length === 0">
                                <td colspan="4" class="px-6 py-12 text-center text-slate-400 italic">Aucune demande en attente de validation.</td>
                            </tr>
                            <tr v-for="assignment in pendingAssignments" :key="assignment.id" class="hover:bg-slate-50/30 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="h-10 w-10 rounded-xl bg-slate-900 flex items-center justify-center text-white font-black text-xs shadow-lg shadow-slate-900/10">
                                            {{ assignment.employee?.first_name?.[0] }}{{ assignment.employee?.last_name?.[0] }}
                                        </div>
                                        <div>
                                            <p class="font-black text-slate-800 leading-tight">{{ employeeName(assignment.employee) }}</p>
                                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Superviseur</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-bold text-teal-600 bg-teal-50 px-3 py-1.5 rounded-lg border border-teal-100">
                                        {{ assignment.planning_model?.name }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-slate-500 font-medium">
                                    Du {{ formatDate(assignment.start_date) }} au {{ formatDate(assignment.end_date) }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Button label="Rejeter" icon="pi pi-times" @click="changeStatus(assignment, 'suspendu')"
                                            class="p-button-text p-button-danger font-black text-[10px] uppercase tracking-widest hover:bg-rose-50 rounded-xl px-4 py-2" />
                                        <Button label="Approuver" icon="pi pi-check" @click="changeStatus(assignment, 'validé')"
                                            class="bg-emerald-600 border-none text-white px-5 py-2.5 rounded-xl font-black text-[10px] uppercase tracking-widest shadow-lg shadow-emerald-600/20 hover:bg-emerald-700 transition-all" />
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
