<script setup>
import { computed, ref } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Button from "primevue/button";
import Tag from "primevue/tag";

const props = defineProps({
    planningModels: Array,
    employees: Array,
    assignments: Array,
    auth: Object,
});

const isAdminOrCP = computed(() => {
    const role = props.auth?.user?.role?.name;
    return role === "Admin" || role === "CP";
});

const form = useForm({
    planning_model_id: "",
    employee_id: "",
    start_date: "",
    end_date: "", // Ajout de la date de fin
});

const statusLabel = {
    "en attente": "En attente",
    validé: "Validé",
    suspendu: "Suspendu",
    terminé: "Terminé",
};

const displayedAssignments = computed(() => props.assignments ?? []);

function employeeName(employee) {
    if (!employee) return "—";
    return [employee.first_name, employee.last_name].filter(Boolean).join(" ");
}

function submitAssignment() {
    form.post(route("planning-assignments.store"), {
        onSuccess: () => {
            form.reset();
        },
        onError: () => {
            alert("Erreur lors de la création de l’affectation.");
        },
    });
}

function deleteAssignment(assignment) {
    if (!confirm("Supprimer cette affectation ?")) {
        return;
    }

    router.delete(route("planning-assignments.destroy", assignment.id), {
        onError: () => alert("Impossible de supprimer cette affectation."),
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
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">Affectations de Planning</h1>
                    <p class="mt-1 text-sm text-slate-500 font-medium">Attribuez des modèles de temps de travail à vos collaborateurs.</p>
                </div>
            </div>

            <!-- Form Section -->
            <div v-if="isAdminOrCP" class="bg-white rounded-[32px] border border-slate-100 p-8 shadow-sm space-y-6">
                <div class="flex items-center gap-3 mb-2">
                    <div class="h-8 w-8 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-sm shadow-sm">
                        <i class="pi pi-plus-circle"></i>
                    </div>
                    <h2 class="text-xl font-black text-slate-900 tracking-tight">Nouvelle affectation</h2>
                </div>

                <form @submit.prevent="submitAssignment" class="grid gap-6 md:grid-cols-4 items-end">
                    <div class="flex flex-col gap-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Modèle de planning</label>
                        <select v-model="form.planning_model_id" class="w-full p-3 rounded-xl border-slate-200 bg-slate-50 focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 transition-all text-sm font-medium">
                            <option value="" disabled>Choisir un modèle</option>
                            <option v-for="model in props.planningModels" :key="model.id" :value="model.id">{{ model.name }}</option>
                        </select>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Collaborateur</label>
                        <select v-model="form.employee_id" class="w-full p-3 rounded-xl border-slate-200 bg-slate-50 focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 transition-all text-sm font-medium">
                            <option value="" disabled>Choisir un superviseur</option>
                            <option v-for="employee in props.employees" :key="employee.id" :value="employee.id">{{ employee.name }}</option>
                        </select>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Date de début</label>
                        <input type="date" v-model="form.start_date" class="w-full p-3 rounded-xl border-slate-200 bg-slate-50 focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 transition-all text-sm font-medium" />
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Date de fin</label>
                        <input type="date" v-model="form.end_date" class="w-full p-3 rounded-xl border-slate-200 bg-slate-50 focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 transition-all text-sm font-medium" />
                    </div>

                    <div class="md:col-span-4 flex justify-end pt-2">
                        <Button label="Affecter le planning" type="submit" :loading="form.processing"
                            class="bg-teal-600 border-none text-white px-8 py-3 rounded-xl font-black text-xs uppercase tracking-widest shadow-lg shadow-teal-600/20 hover:bg-teal-700 transition-all" />
                    </div>
                </form>
            </div>

            <!-- List Section -->
            <div class="bg-white rounded-[40px] border border-slate-100 p-8 shadow-sm space-y-8 overflow-hidden">
                <div class="flex items-center gap-3">
                    <div class="h-8 w-8 rounded-xl bg-slate-900 text-white flex items-center justify-center text-sm shadow-sm">
                        <i class="pi pi-list"></i>
                    </div>
                    <h2 class="text-xl font-black text-slate-900 tracking-tight">Assignations existantes</h2>
                </div>

                <div class="overflow-x-auto rounded-3xl border border-slate-50">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-slate-50/50">
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Employé</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Planning</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Période</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">Statut</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-if="displayedAssignments.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center text-slate-400 italic">Aucune assignation disponible.</td>
                            </tr>
                            <tr v-for="assignment in displayedAssignments" :key="assignment.id" class="hover:bg-slate-50/30 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-8 w-8 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center font-black text-[10px]">
                                            {{ assignment.employee?.first_name?.[0] }}{{ assignment.employee?.last_name?.[0] }}
                                        </div>
                                        <span class="font-black text-slate-700">{{ employeeName(assignment.employee) }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-bold text-teal-600">{{ assignment.planning_model?.name ?? "—" }}</td>
                                <td class="px-6 py-4 text-slate-500 font-medium">
                                    Du {{ new Date(assignment.start_date).toLocaleDateString("fr-FR") }}
                                    <span v-if="assignment.end_date"> au {{ new Date(assignment.end_date).toLocaleDateString("fr-FR") }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span :class="['rounded-full px-3 py-1 text-[9px] font-black uppercase tracking-widest', 
                                        assignment.status === 'validé' ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 
                                        assignment.status === 'en attente' ? 'bg-amber-50 text-amber-600 border border-amber-100' : 
                                        'bg-slate-50 text-slate-400 border border-slate-200']">
                                        {{ statusLabel[assignment.status] || assignment.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <Button v-if="assignment.status === 'en attente'" icon="pi pi-trash" severity="danger" text rounded @click="deleteAssignment(assignment)"
                                        class="hover:bg-rose-50 transition-all" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
