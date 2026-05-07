<script setup>
import { ref, computed } from "vue";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Tag from "primevue/tag";
import Select from "primevue/select"; // Ou Dropdown selon votre version de PrimeVue
import FloatLabel from "primevue/floatlabel";
import AuthenticatedLayout from "../../Layouts/AuthenticatedLayout.vue";
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    supervisor: Array, // Reçoit les données groupées/filtrées du contrôleur
    auth: Object,
});

const expandedRows = ref([]);

// 1. Extraire la liste unique des mois présents dans les données pour le filtre
const monthOptions = computed(() => {
    const months = new Set();
    props.supervisor.forEach((sup) => {
        sup.timesheet.forEach((ts) => {
            ts.entries.forEach((entry) => {
                const date = new Date(entry.date);
                const monthLabel = date.toLocaleDateString("fr-FR", {
                    month: "long",
                    year: "numeric",
                });
                months.add(monthLabel);
            });
        });
    });
    return Array.from(months).map((m) => ({ label: m, value: m }));
});

const selectedMonth = ref(monthOptions.value[0] || null);

// 2. Filtrer les entrées selon le mois sélectionné
const getFilteredEntries = (employee) => {
    const allEntries = employee.timesheet.flatMap((ts) => ts.entries);
    if (!selectedMonth.value) return allEntries;

    return allEntries.filter((entry) => {
        const dateLabel = new Date(entry.date).toLocaleDateString("fr-FR", {
            month: "long",
            year: "numeric",
        });
        return dateLabel === selectedMonth.value.value;
    });
};

// Fonctions de formatage
const formatHours = (v) =>
    v
        ? `${Math.floor(v)}h${Math.round((v % 1) * 60)
              .toString()
              .padStart(2, "0")}`
        : "0h00";
const formatDate = (d) =>
    new Date(d).toLocaleDateString("fr-FR", {
        day: "2-digit",
        month: "2-digit",
    });
</script>

<template>
    <AuthenticatedLayout>
        <div class="p-6 space-y-6">
            <!-- Barre d'outils minimaliste -->
            <div
                class="flex justify-between items-end bg-white p-4 rounded-xl shadow-sm border border-gray-100"
            >
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">
                        Reporting Superviseurs
                    </h1>
                    <p class="text-gray-500 text-sm">
                        Visualisation des heures par période
                    </p>
                    <Link :href="route('entry.telecon')" class="block p-2 hover:bg-slate-800 rounded"
                        >Saisie des heures des téléconseillers</Link
                    >
                    <Link :href="route('entry.sup')" class="block p-2 hover:bg-slate-800 rounded"
                        >Saisie des heures des superviseurs</Link
                    >

                </div>

                <FloatLabel variant="on">
                    <Select
                        v-model="selectedMonth"
                        :options="monthOptions"
                        optionLabel="label"
                        placeholder="Choisir un mois"
                        class="w-56"
                    />
                </FloatLabel>
            </div>

            <!-- Tableau Principal -->
            <div
                class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden"
            >
                <DataTable
                    v-model:expandedRows="expandedRows"
                    :value="props.supervisor"
                    dataKey="id"
                    class="p-datatable-sm"
                    stripedRows
                >
                    <Column expander style="width: 3rem" />

                    <Column header="Collaborateur">
                        <template #body="{ data }">
                            <div class="flex flex-col">
                                <span class="font-semibold text-gray-700"
                                    >{{ data.first_name }}
                                    {{ data.last_name }}</span
                                >
                                <span class="text-xs text-gray-400">{{
                                    data.matricule
                                }}</span>
                            </div>
                        </template>
                    </Column>

                    <Column field="position.name" header="Poste" />

                    <Column header="Total Mois">
                        <template #body="{ data }">
                            <b class="text-blue-600">
                                {{
                                    formatHours(
                                        getFilteredEntries(data).reduce(
                                            (acc, curr) =>
                                                acc + curr.total_hours,
                                            0,
                                        ),
                                    )
                                }}
                            </b>
                        </template>
                    </Column>

                    <!-- Expansion : Détail des journées du mois -->
                    <template #expansion="{ data }">
                        <div class="p-4 bg-slate-50 border-y border-gray-200">
                            <div class="mb-3 flex items-center gap-2">
                                <i class="pi pi-calendar text-blue-500"></i>
                                <span class="font-bold text-gray-600"
                                    >Détails de {{ selectedMonth?.label }}</span
                                >
                            </div>

                            <DataTable
                                :value="getFilteredEntries(data)"
                                class="p-datatable-sm rounded-lg overflow-hidden border"
                            >
                                <Column field="date" header="Date">
                                    <template #body="sp">{{
                                        formatDate(sp.data.date)
                                    }}</template>
                                </Column>
                                <Column field="check_in" header="Entrée" />
                                <Column field="check_out" header="Sortie" />
                                <Column header="Heures">
                                    <template #body="sp">{{
                                        formatHours(sp.data.total_hours)
                                    }}</template>
                                </Column>
                                <Column header="Overtime">
                                    <template #body="sp">
                                        <Tag
                                            v-if="sp.data.overtime_hours > 0"
                                            :value="
                                                '+' +
                                                formatHours(
                                                    sp.data.overtime_hours,
                                                )
                                            "
                                            severity="warn"
                                        />
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
:deep(.p-datatable-thead > tr > th) {
    background-color: #f8fafc;
    color: #64748b;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.025em;
}

:deep(.p-tag) {
    font-size: 0.7rem;
    padding: 0.2rem 0.5rem;
}
</style>
