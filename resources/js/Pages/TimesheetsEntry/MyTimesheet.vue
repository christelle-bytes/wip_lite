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
        weekday: 'long',
        day: "2-digit",
        month: "short",
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
        <div class="py-6 space-y-8">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 px-6">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">Mes Heures</h1>
                    <p class="mt-1 text-sm text-slate-500 font-medium">Consultez le récapitulatif détaillé de vos heures travaillées.</p>
                </div>

                <div class="flex items-center gap-4">
                    <FloatLabel variant="on" class="min-w-64">
                        <Select
                            v-model="selectedPeriod"
                            :options="periodOptions"
                            optionLabel="label"
                            placeholder="Toutes les périodes"
                            showClear
                            class="w-full"
                        />
                    </FloatLabel>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 px-6">
                <div class="bg-white p-6 rounded-[32px] border border-slate-100 shadow-sm flex items-center gap-4">
                    <div class="h-12 w-12 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center text-xl shadow-sm">
                        <i class="pi pi-clock"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total cumulé</p>
                        <p class="text-2xl font-black text-slate-900">{{ formatH(totalHoursOverall) }}</p>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-[32px] border border-slate-100 shadow-sm flex items-center gap-4">
                    <div class="h-12 w-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shadow-sm">
                        <i class="pi pi-calendar"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Périodes</p>
                        <p class="text-2xl font-black text-slate-900">{{ props.timesheets.length }}</p>
                    </div>
                </div>
            </div>

            <!-- Liste des périodes/timesheets -->
            <div v-if="props.timesheets.length > 0" class="px-6 space-y-8">
                <div v-for="ts in props.timesheets" :key="ts.id" class="bg-white rounded-[40px] border border-slate-100 p-8 shadow-sm">
                    <!-- Header de la période -->
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-6">
                        <div class="flex items-center gap-4">
                            <div class="h-10 w-10 rounded-2xl bg-slate-900 text-white flex items-center justify-center shadow-sm">
                                <i class="pi pi-calendar-plus"></i>
                            </div>
                            <div>
                                <h2 class="text-xl font-black text-slate-900">
                                    Période du {{ new Date(ts.period_start).toLocaleDateString('fr-FR') }} au {{ new Date(ts.period_end).toLocaleDateString('fr-FR') }}
                                </h2>
                                <div class="flex items-center gap-3 mt-1">
                                    <Tag :value="ts.status" :severity="getStatusSeverity(ts.status)" class="text-[10px] uppercase font-black px-3 py-1" />
                                    <span v-if="ts.validator" class="text-xs text-slate-400 font-medium flex items-center gap-1">
                                        <i class="pi pi-check-circle text-emerald-500"></i>
                                        Validé par {{ ts.validator.first_name }} {{ ts.validator.last_name }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="px-6 py-3 bg-slate-50 rounded-2xl border border-slate-100">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Période</p>
                            <p class="text-xl font-black text-teal-600">{{ formatH(ts.entries.reduce((acc, e) => acc + parseFloat(e.total_hours || 0), 0)) }}</p>
                        </div>
                    </div>

                    <!-- Tableau des entrées -->
                    <DataTable :value="ts.entries" class="rounded-2xl border border-slate-50 overflow-hidden" rowHover>
                        <Column field="date" header="Date" style="min-width: 15rem">
                            <template #body="{ data }">
                                <span class="font-black text-slate-700 capitalize">{{ formatDate(data.date) }}</span>
                            </template>
                        </Column>
                        <Column header="Horaires" style="min-width: 10rem">
                            <template #body="{ data }">
                                <span class="font-medium text-slate-600">{{ data.check_in }} - {{ data.check_out }}</span>
                            </template>
                        </Column>
                        <Column header="Pause">
                            <template #body="{ data }">
                                <span class="text-slate-500">{{ data.break_duration }} min</span>
                            </template>
                        </Column>
                        <Column header="Durée Totale">
                            <template #body="{ data }">
                                <span class="font-black text-slate-900">{{ formatH(data.total_hours) }}</span>
                            </template>
                        </Column>
                        <Column header="H. Suppl.">
                            <template #body="{ data }">
                                <Tag v-if="data.overtime_hours > 0" :value="`+${formatH(data.overtime_hours)}`" severity="warn" class="text-[10px] font-black" />
                                <span v-else class="text-slate-300">-</span>
                            </template>
                        </Column>
                        <Column field="comment" header="Commentaire">
                            <template #body="{ data }">
                                <span class="text-xs italic text-slate-400 font-medium">{{ data.comment || '-' }}</span>
                            </template>
                        </Column>
                    </DataTable>
                </div>
            </div>

            <!-- État vide -->
            <div v-else class="mx-6 bg-white rounded-[40px] border border-slate-100 p-20 text-center shadow-sm">
                <div class="w-20 h-20 bg-slate-50 rounded-3xl flex items-center justify-center mx-auto mb-6 text-slate-200 shadow-inner">
                    <i class="pi pi-calendar-times text-4xl"></i>
                </div>
                <h3 class="text-2xl font-black text-slate-900 mb-2">Aucune heure enregistrée</h3>
                <p class="text-slate-500 font-medium">Aucune entrée n'a été trouvée pour la période sélectionnée.</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
