<script setup>
import { ref } from "vue";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Tag from "primevue/tag";
import AuthenticatedLayout from "../../Layouts/AuthenticatedLayout.vue";
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    telecon: Array,
    auth: Object,
    currentMonth: String,
});

const expandedRows = ref([]);

// Formattage simple
const formatH = (v) =>
    v
        ? `${Math.floor(v)}h${Math.round((v % 1) * 60)
              .toString()
              .padStart(2, "0")}`
        : "0h00";
const formatD = (d) =>
    new Date(d).toLocaleDateString("fr-FR", {
        day: "2-digit",
        month: "2-digit",
    });

// Récupère toutes les entrées des timesheets
const getEntries = (employee) => employee.timesheet.flatMap((ts) => ts.entries);

// Calcul du total pour l'employé
const getTotal = (employee) => {
    // Vérifie si timesheet existe et n'est pas vide
    if (!employee.timesheet || employee.timesheet.length === 0) return 0;

    const entries = employee.timesheet.flatMap((ts) => ts.entries || []);
    return entries.reduce((acc, curr) => {
        const val = parseFloat(curr.total_hours);
        return acc + (isNaN(val) ? 0 : val);
    }, 0);
};
console.log(props.telecon);
</script>

<template>
    <AuthenticatedLayout>
        <div class="p-4 bg-gray-50 min-h-screen">
            <div
                class="max-w-5xl mx-auto bg-white shadow-sm rounded-lg border border-gray-200"
            >
                <DataTable
                    :value="props.telecon"
                    v-model:expandedRows="expandedRows"
                    dataKey="id"
                    class="p-datatable-sm"
                >
                    <template #header>
                        <div class="flex justify-between items-center p-2">
                            <span class="text-lg font-bold text-gray-700"
                                >Reporting : {{ props.currentMonth }}</span
                            >
                            <Tag
                                :value="props.telecon.length + ' Employés'"
                                severity="secondary"
                            />
                        </div>
                        <div>
                            <Link
                                :href="route('entry.telecon')"
                                class="block p-2 hover:text-slate-800 rounded"
                                >Saisir des heures</Link
                            >
                        </div>
                    </template>

                    <Column expander style="width: 3rem" />

                    <Column header="Collaborateur">
                        <template #body="{ data }">
                            <div class="font-medium">
                                {{ data.first_name }} {{ data.last_name }}
                            </div>
                            <div class="text-xs text-gray-400">
                                {{ data.matricule }}
                            </div>
                        </template>
                    </Column>

                    <Column field="position.name" header="Poste" />

                    <Column header="Total Heures">
                        <template #body="{ data }">
                            <span class="font-bold text-blue-600">{{
                                formatH(getTotal(data))
                            }}</span>
                        </template>
                    </Column>

                    <!-- Détails à l'ouverture -->
                    <template #expansion="{ data }">
                        <div class="p-3 bg-gray-50">
                            <DataTable
                                :value="getEntries(data)"
                                class="p-datatable-sm border rounded"
                            >
                                <Column field="date" header="Date">
                                    <template #body="sp">{{
                                        formatD(sp.data.date)
                                    }}</template>
                                </Column>
                                <Column field="check_in" header="In" />
                                <Column field="check_out" header="Out" />
                                <Column header="Total">
                                    <template #body="sp">{{
                                        formatH(sp.data.total_hours)
                                    }}</template>
                                </Column>
                                <Column header="H.S">
                                    <template #body="sp">
                                        <span
                                            v-if="sp.data.overtime_hours > 0"
                                            class="text-orange-500 font-bold"
                                        >
                                            +{{
                                                formatH(sp.data.overtime_hours)
                                            }}
                                        </span>
                                        <span v-else class="text-gray-300"
                                            >-</span
                                        >
                                    </template>
                                </Column>
                            </DataTable>
                        </div>
                    </template>
                </DataTable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Style minimal pour supprimer les bordures inutiles */
:deep(.p-datatable-header) {
    border-bottom: 1px solid #edf2f7;
}
:deep(.p-datatable-thead > tr > th) {
    font-size: 0.75rem;
    color: #a0aec0;
}
</style>
