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
        <div class="p-6 space-y-6">
            <!-- En-tête amélioré -->
            <div
                class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6"
            >
                <div class="flex flex-wrap justify-between items-end gap-6">
                    <div>
                        <h1 class="text-3xl font-semibold text-slate-800">
                            Reporting Téléconseillers
                        </h1>
                        <p class="text-slate-500 mt-1">
                            {{ props.currentMonth }} • Synthèse des heures
                            saisies
                        </p>
                    </div>

                    <div class="flex items-center gap-4">
                        <Tag
                            :value="props.telecon.length + ' Téléconseillers'"
                            severity="secondary"
                            class="text-sm px-4 py-2"
                        />

                        <Link
                            :href="route('entry.telecon')"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition-all shadow-sm"
                        >
                            <i class="pi pi-plus"></i>
                            Saisir des heures
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Tableau Principal -->
            <div
                class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden"
            >
                <DataTable
                    :value="props.telecon"
                    v-model:expandedRows="expandedRows"
                    dataKey="id"
                    class="p-datatable-sm"
                    stripedRows
                    rowHover
                >
                    <Column expander style="width: 4rem" />

                    <!-- Collaborateur -->
                    <Column header="Collaborateur" style="min-width: 18rem">
                        <template #body="{ data }">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-11 h-11 rounded-2xl bg-gradient-to-br from-indigo-100 to-blue-100 flex items-center justify-center font-semibold text-slate-700 border border-slate-200 shadow-sm"
                                >
                                    {{ data.first_name?.[0]
                                    }}{{ data.last_name?.[0] }}
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-800">
                                        {{ data.first_name }}
                                        {{ data.last_name }}
                                    </p>
                                    <p class="text-xs text-slate-500">
                                        {{ data.matricule }}
                                    </p>
                                </div>
                            </div>
                        </template>
                    </Column>

                    <Column
                        field="position.name"
                        header="Poste"
                        style="min-width: 12rem"
                    />

                    <Column header="Total Heures" style="min-width: 12rem">
                        <template #body="{ data }">
                            <div class="text-xl font-semibold text-blue-600">
                                {{ formatH(getTotal(data)) }}
                            </div>
                        </template>
                    </Column>

                    <!-- Expansion -->
                    <template #expansion="{ data }">
                        <div class="p-6 bg-slate-50 border-t border-slate-100">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <i
                                        class="pi pi-list-check text-blue-600"
                                    ></i>
                                    <span class="font-semibold text-slate-700">
                                        Détail des saisies
                                    </span>
                                </div>
                                <span class="text-sm text-slate-500">
                                    {{ getEntries(data).filter(Boolean)?.length }} jour(s)
                                </span>
                            </div>

                            <DataTable
                                v-if="getEntries(data).filter(Boolean)?.length != 0"
                                :value="getEntries(data)"
                                class="p-datatable-sm rounded-xl border border-slate-200 overflow-hidden"
                                stripedRows
                            >
                                <Column
                                    field="date"
                                    header="Date"
                                    style="width: 110px"
                                >
                                    <template #body="sp">
                                        {{ formatD(sp.data.date) }}
                                    </template>
                                </Column>
                                <Column field="check_in" header="Entrée" />
                                <Column field="check_out" header="Sortie" />
                                <Column header="Durée" style="width: 130px">
                                    <template #body="sp">
                                        <span
                                            class="font-medium text-slate-700"
                                        >
                                            {{ formatH(sp.data.total_hours) }}
                                        </span>
                                    </template>
                                </Column>
                                <Column header="Heures Supplémentaires">
                                    <template #body="sp">
                                        <Tag
                                            v-if="sp.data.overtime_hours > 0"
                                            :value="`+${formatH(sp.data.overtime_hours)}`"
                                            severity="warn"
                                        />
                                        <span v-else class="text-slate-300"
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
:deep(.p-datatable-thead > tr > th) {
    background-color: #f8fafc;
    color: #475569;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    padding: 1.1rem 1rem;
}

:deep(.p-datatable-tbody > tr:hover) {
    background-color: #f8fafc;
}

:deep(.p-tag) {
    font-size: 0.75rem;
    padding: 0.35rem 0.75rem;
    border-radius: 9999px;
}
</style>
