<script setup>
import { ref, watch, computed } from "vue";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Tag from "primevue/tag";
import Select from "primevue/select";
import FloatLabel from "primevue/floatlabel";
import AuthenticatedLayout from "../../Layouts/AuthenticatedLayout.vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    timesheets: Array,
    allPeriods: Array,
    selectedPeriod: Object,
    employee: Object,
});

const expandedRows = ref([]);

// Sélection de la période
const selectedPeriod = ref(props.selectedPeriod);

// Options sécurisées pour le sélecteur
const periodOptions = computed(() => {
    return Array.isArray(props.allPeriods) ? props.allPeriods : [];
});

// Watcher pour déclencher la navigation lors du changement de période
watch(selectedPeriod, (newPeriod) => {
    if (newPeriod) {
        router.get(route('index.times'), {
            start_date: newPeriod.period_start,
            end_date: newPeriod.period_end
        }, { preserveState: true, replace: true });
    } else {
        router.get(route('index.times'), {}, { preserveState: true, replace: true });
    }
});

// Formatage simple
const formatH = (v) =>
    v
        ? `${Math.floor(v)}h${Math.round((v % 1) * 60)
              .toString()
              .padStart(2, "0")}`
        : "0h00";

const formatDate = (d) =>
    new Date(d).toLocaleDateString("fr-FR", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric"
    });

const getStatusSeverity = (status) => {
    const s = status?.toLowerCase();
    if (s === "validé" || s === "validated") return "success";
    if (s === "en attente" || s === "submitted" || s === "pending") return "warn";
    return "secondary";
};

// Calcul du total d'heures pour toutes les timesheets affichées
const totalHoursOverall = computed(() => {
    return props.timesheets.reduce((acc, ts) => {
        const tsTotal = ts.entries.reduce((tsAcc, entry) => tsAcc + parseFloat(entry.total_hours || 0), 0);
        return acc + tsTotal;
    }, 0);
});
</script>

<template>
    <AuthenticatedLayout>
        <div class="p-6 space-y-6">
            <!-- En-tête -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <div class="flex flex-wrap justify-between items-center gap-6">
                    <div>
                        <h1 class="text-3xl font-semibold text-slate-800">
                            Mes Heures
                        </h1>
                        <p class="text-slate-500 mt-1">
                            Consultez le récapitulatif de vos heures travaillées.
                        </p>
                    </div>

                    <div class="flex items-center gap-4">
                        <FloatLabel variant="on" class="min-w-64">
                            <Select
                                v-model="selectedPeriod"
                                :options="periodOptions"
                                optionLabel="label"
                                placeholder="Toutes les périodes"
                                showClear
                                class="w-full border-slate-200 shadow-none focus:ring-0 text-sm font-semibold text-slate-700 rounded-xl"
                            />
                        </FloatLabel>

                        <div class="px-4 py-2 bg-blue-50 text-blue-700 rounded-xl border border-blue-100 flex items-center gap-2">
                            <i class="pi pi-clock"></i>
                            <span class="font-bold text-lg">{{ formatH(totalHoursOverall) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Liste des périodes/timesheets -->
            <div v-if="props.timesheets.length > 0" class="space-y-6">
                <div v-for="ts in props.timesheets" :key="ts.id" class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <!-- Header de la période -->
                    <div class="p-4 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
                        <div class="flex items-center gap-4">
                            <div class="px-3 py-1 bg-white border border-slate-200 rounded-lg text-sm font-bold text-slate-700">
                                {{ formatDate(ts.period_start) }} → {{ formatDate(ts.period_end) }}
                            </div>
                            <Tag :value="ts.status" :severity="getStatusSeverity(ts.status)" class="text-[10px] uppercase font-bold" />
                        </div>
                        <div v-if="ts.validator" class="text-xs text-slate-500 flex items-center gap-2">
                            <i class="pi pi-check-circle text-emerald-500"></i>
                            Validé par {{ ts.validator.first_name }} {{ ts.validator.last_name }}
                        </div>
                    </div>

                    <!-- Tableau des entrées -->
                    <DataTable :value="ts.entries" class="p-datatable-sm" stripedRows>
                        <Column field="date" header="Date">
                            <template #body="{ data }">
                                {{ formatDate(data.date) }}
                            </template>
                        </Column>
                        <Column field="check_in" header="Entrée" />
                        <Column field="check_out" header="Sortie" />
                        <Column field="break_duration" header="Pause">
                            <template #body="{ data }">
                                {{ data.break_duration }} min
                            </template>
                        </Column>
                        <Column header="Total">
                            <template #body="{ data }">
                                <span class="font-bold text-slate-700">{{ formatH(data.total_hours) }}</span>
                            </template>
                        </Column>
                        <Column header="H. Suppl.">
                            <template #body="{ data }">
                                <Tag v-if="data.overtime_hours > 0" :value="`+${formatH(data.overtime_hours)}`" severity="warn" />
                                <span v-else class="text-slate-300">-</span>
                            </template>
                        </Column>
                        <Column field="comment" header="Commentaire">
                            <template #body="{ data }">
                                <span class="text-xs italic text-slate-500">{{ data.comment || '-' }}</span>
                            </template>
                        </Column>
                    </DataTable>
                </div>
            </div>

            <!-- État vide -->
            <div v-else class="bg-white rounded-2xl shadow-sm border border-slate-200 p-12 text-center">
                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-400">
                    <i class="pi pi-calendar-times text-3xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-slate-800">Aucune heure enregistrée</h3>
                <p class="text-slate-500">Aucune entrée n'a été trouvée pour la période sélectionnée.</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
