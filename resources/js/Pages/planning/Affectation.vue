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
        <!-- Header -->
        <div class="page-header">
            <div class="back-link" @click="router.get('/planning')">
                <svg
                    width="16"
                    height="16"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path d="m15 18-6-6 6-6" />
                </svg>
                Retour aux plannings
            </div>
    </div>
        <div class="affectation-page px-8 py-6">
            <div class="page-header flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-semibold">
                        Affectation de planning
                    </h1>
                    <p class="text-sm text-slate-500">
                        Gérer les assignations de planning aux employés.
                    </p>
                </div>
            </div>

            <div
                v-if="isAdminOrCP"
                class="mb-6 bg-white p-6 rounded-lg shadow-sm border border-slate-200"
            >
                <h2 class="text-lg font-medium mb-4">Nouvelle affectation</h2>
                <form
                    @submit.prevent="submitAssignment"
                    class="grid gap-4 md:grid-cols-3 items-end"
                >
                    <div>
                        <label class="block text-sm font-medium text-slate-700"
                            >Modèle de planning</label
                        >
                        <select
                            v-model="form.planning_model_id"
                            class="mt-1 block w-full rounded border-slate-300 bg-white p-2 text-sm"
                        >
                            <option value="" disabled>Choisir un modèle</option>
                            <option
                                v-for="model in props.planningModels"
                                :key="model.id"
                                :value="model.id"
                            >
                                {{ model.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700"
                            >Employé</label
                        >
                        <select
                            v-model="form.employee_id"
                            class="mt-1 block w-full rounded border-slate-300 bg-white p-2 text-sm"
                        >
                            <option value="" disabled>
                                Choisir un employé
                            </option>
                            <option
                                v-for="employee in props.employees"
                                :key="employee.id"
                                :value="employee.id"
                            >
                                {{ employee.first_name }}
                                {{ employee.last_name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700"
                            >Date de début</label
                        >
                        <input
                            type="date"
                            v-model="form.start_date"
                            class="mt-1 block w-full rounded border-slate-300 bg-white p-2 text-sm"
                        />
                    </div>

                    <div class="md:col-span-3 flex justify-end">
                        <Button
                            label="Affecter"
                            type="submit"
                            class="p-button-sm"
                        />
                    </div>
                </form>
            </div>

            <div
                class="bg-white p-6 rounded-lg shadow-sm border border-slate-200"
            >
                <h2 class="text-lg font-medium mb-4">
                    Assignations existantes
                </h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead>
                            <tr
                                class="border-b border-slate-200 text-slate-600"
                            >
                                <th class="py-3 px-4">Employé</th>
                                <th class="py-3 px-4">Planning</th>
                                <th class="py-3 px-4">Début</th>
                                <th class="py-3 px-4">Statut</th>
                                <th class="py-3 px-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="displayedAssignments.length === 0">
                                <td
                                    colspan="5"
                                    class="py-4 px-4 text-center text-slate-500"
                                >
                                    Aucune assignation disponible.
                                </td>
                            </tr>
                            <tr
                                v-for="assignment in displayedAssignments"
                                :key="assignment.id"
                                class="border-b border-slate-200 hover:bg-slate-50"
                            >
                                <td class="py-3 px-4">
                                    {{ employeeName(assignment.employee) }}
                                </td>
                                <td class="py-3 px-4">
                                    {{ assignment.planning_model?.name ?? "—" }}
                                </td>
                                <td class="py-3 px-4">
                                    {{
                                        new Date(
                                            assignment.start_date,
                                        ).toLocaleDateString("fr-FR")
                                    }}
                                </td>
                                <td class="py-3 px-4">
                                    <Tag
                                        :value="
                                            statusLabel[assignment.status] ||
                                            assignment.status
                                        "
                                        severity="info"
                                    />
                                </td>
                                <td class="py-3 px-4 space-x-2">
                                    <Button
                                        v-if="
                                            assignment.status === 'en attente'
                                        "
                                        label="Supprimer"
                                        severity="danger"
                                        text
                                        rounded
                                        @click="deleteAssignment(assignment)"
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.affectation-page {
    max-width: 1200px;
    margin: 0 auto;
}
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
</style>
