<script setup>
import { ref, computed, onMounted } from "vue";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Tag from "primevue/tag";
import Select from "primevue/select";
import FloatLabel from "primevue/floatlabel";
import AuthenticatedLayout from "../../Layouts/AuthenticatedLayout.vue";
import { Link } from "@inertiajs/vue3";


const props = defineProps({
    supervisors: { type: Array, default: () => [] },
    allPeriods: { type: Array, default: () => [] },
    selectedPeriod: { type: Object, default: null },
    auth: Object,
});

const expandedRows = ref([]);

// Sélection
const selectedPeriod = ref(props.selectedPeriod);

// Options sécurisées
const periodOptions = computed(() => {
    return Array.isArray(props.allPeriods) ? props.allPeriods : [];
});

const getFilteredEntries = (supervisor) => {
    return supervisor.timesheet?.flatMap(ts => ts.entries || []) || [];
};

const formatHours = (v) =>
    v ? `${Math.floor(v)}h${Math.round((v % 1) * 60).toString().padStart(2, "0")}` : "0h00";

const formatDate = (d) =>
    d ? new Date(d).toLocaleDateString("fr-FR", { day: "2-digit", month: "2-digit" }) : "-";

console.log("Superviseurs reçus :", props);
</script>

<template>
    <AuthenticatedLayout>
        <div class="p-6 space-y-6">
            <!-- Barre d'outils -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <div class="flex flex-wrap justify-between items-end gap-6">
                    <div>
                        <h1 class="text-3xl font-semibold text-slate-800">
                            Reporting Superviseurs
                        </h1>
                        <p class="text-slate-500 mt-1">
                            Visualisation détaillée des heures par période
                        </p>
                    </div>

                    <div class="flex items-center gap-4">
                        <Link
                            :href="route('entry.sup')"
                            class="text-sm font-medium text-slate-600 hover:text-blue-600 transition-colors flex items-center gap-2"
                        >
                            <i class="pi pi-pencil"></i>
                            Saisie Superviseurs
                        </Link>

                        <FloatLabel variant="on" class="min-w-80">
                            <Select
                                v-model="selectedPeriod"
                                :options="periodOptions"
                                optionLabel="label"
                                placeholder="Sélectionner une période"
                                class="w-full"
                            />
                        </FloatLabel>
                    </div>
                </div>
            </div>

            <!-- Tableau Principal -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <DataTable
                    v-model:expandedRows="expandedRows"
                    :value="props.supervisors"
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
                                <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-blue-100 to-slate-100 flex items-center justify-center font-semibold text-slate-700 border border-slate-200 shadow-sm">
                                    {{ data.first_name?.[0] }}{{ data.last_name?.[0] }}
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-800 leading-tight">
                                        {{ data.first_name }} {{ data.last_name }}
                                    </p>
                                    <p class="text-xs text-slate-500">
                                        {{ data.matricule }}
                                    </p>
                                </div>
                            </div>
                        </template>
                    </Column>

                    <Column field="position.name" header="Poste" style="min-width: 12rem" />

                    <Column header="Total Période" style="min-width: 11rem">
                        <template #body="{ data }">
                            <div class="text-lg font-semibold text-blue-600">
                                {{
                                    formatHours(
                                        getFilteredEntries(data).reduce(
                                            (acc, curr) => acc + (curr?.total_hours || 0),
                                            0
                                        )
                                    )
                                }}
                            </div>
                        </template>
                    </Column>

                    <!-- Expansion -->
                    <template #expansion="{ data }">
                        <div class="p-6 bg-slate-50 border-t border-slate-100">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <i class="pi pi-calendar text-blue-600 text-xl"></i>
                                    <span class="font-semibold text-slate-700">
                                        Période : 
                                        {{ selectedPeriod?.label || "Toutes les périodes" }}
                                    </span>
                                </div>
                                <span class="text-xs text-slate-500">
                                    {{ getFilteredEntries(data).length }} jour(s) saisis
                                </span>
                            </div>

                            <DataTable
                                :value="getFilteredEntries(data)"
                                class="p-datatable-sm rounded-xl overflow-hidden border border-slate-200"
                                stripedRows
                            >
                                <Column field="date" header="Date" style="width: 120px">
                                    <template #body="sp">
                                        {{ formatDate(sp.data.date) }}
                                    </template>
                                </Column>
                                <Column field="check_in" header="Heure d'entrée" />
                                <Column field="check_out" header="Heure de sortie" />
                                <Column header="Durée" style="width: 130px">
                                    <template #body="sp">
                                        <span class="font-medium">
                                            {{ formatHours(sp.data.total_hours) }}
                                        </span>
                                    </template>
                                </Column>
                                <Column header="Heures supplémentaires">
                                    <template #body="sp">
                                        <Tag
                                            v-if="sp.data.overtime_hours > 0"
                                            :value="`+${formatHours(sp.data.overtime_hours)}`"
                                            severity="warn"
                                            class="font-medium"
                                        />
                                        <span v-else class="text-slate-300 text-sm">-</span>
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