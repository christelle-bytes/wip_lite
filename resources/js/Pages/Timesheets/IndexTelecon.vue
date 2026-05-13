<script setup>
import { ref, onMounted, computed } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

// PrimeVue Components
import Button from "primevue/button";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import InputText from "primevue/inputtext";
import IconField from "primevue/iconfield";
import InputIcon from "primevue/inputicon";
import Dialog from "primevue/dialog";
import Tag from "primevue/tag";
import MultiSelect from "primevue/multiselect";
import DatePicker from "primevue/datepicker";
import { FilterMatchMode, FilterOperator } from "@primevue/core/api";

const props = defineProps({
    timesheets: Array,
    telecon: Array, // Reçu du contrôleur (les TCs assignés)
    planning: Array,
    auth: Object,
});

// --- Logique de Données ---
// On formate les téléconseillers pour le MultiSelect du Dialog
const formattedTelecon = computed(() => {
    return (props.telecon || []).map((item) => ({
        ...item,
        fullName: `${item.first_name} ${item.last_name}`,
    }));
});

const visible = ref(false);
const filters = ref();

const form = useForm({
    employee_id: [], // Tableau car MultiSelect
    period_start: null,
    period_end: null,
});

const submit = () => {
    // Formatage des dates pour MySQL avant envoi
    const formatDateForDB = (date) => {
        if (!date) return null;
        const d = new Date(date);
        return d.toISOString().split('T')[0];
    };

    const payload = {
        ...form,
        period_start: formatDateForDB(form.period_start),
        period_end: formatDateForDB(form.period_end),
    };

    router.post(route("timesheet.store"), payload);
};

// --- Actions ---
const validation = (id) => {
    const offset = new Date().getTimezoneOffset() * 60000;
const localISOTime = new Date(Date.now() - offset)
    .toISOString()
    .split("T")[0];
    router.patch(
        route("timesheet.update", id),
        {
            status: "validated",
            validated_by: props.auth.user.id,
            validated_at: localISOTime,
        },
        { 
            preserveScroll: true,
            onSuccess: () => {
                // Optionnel : refresh ou notification
            }
        }
    );
};

// --- Formatage & Style ---
const formatDate = (date) => {
    if (!date) return "-";
    return new Date(date).toLocaleDateString("fr-FR", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};

const progressBar = (joursSaisis, totalJours) => {
    if (!totalJours || totalJours <= 0) return 0;
    return Math.min(Math.round((joursSaisis / totalJours) * 100), 100);
};

// --- Filtres ---
const initFilters = () => {
    filters.value = {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        status: {
            operator: FilterOperator.OR,
            constraints: [{ value: null, matchMode: FilterMatchMode.EQUALS }],
        },
    };
};

onMounted(() => {
    initFilters();
});

const clearFilter = () => {
    initFilters();
};

const getStatusSeverity = (status) => {
    switch (status) {
        case 'draft':
            return 'secondary';
        case 'submitted':
            return 'warn';
        case 'validated':
            return 'success';
        default:
            return 'info';
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <div class="py-6 space-y-8">
            <!-- Header Section -->
            <div class="flex items-center gap-2 mb-2">
                    <button @click="router.get(route('index.telecon'))" class="h-8 w-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-teal-50 hover:text-teal-600 transition-all">
                        <i class="pi pi-arrow-left text-xs"></i>
                    </button>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Retour à la saisie des heures</span>
                </div>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">Gestion des Temps (TC)</h1>
                    <p class="mt-1 text-sm text-slate-500 font-medium">Validation des feuilles d'heures téléconseillers basées sur le superviseur.</p>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white p-6 rounded-[32px] border border-slate-100 shadow-sm flex items-center gap-4">
                    <div class="h-12 w-12 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center text-xl shadow-sm">
                        <i class="pi pi-users"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Téléconseillers</p>
                        <p class="text-2xl font-black text-slate-900">{{ props.telecon?.length || 0 }}</p>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-[32px] border border-slate-100 shadow-sm flex items-center gap-4">
                    <div class="h-12 w-12 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center text-xl shadow-sm">
                        <i class="pi pi-clock"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">En attente (Submitted)</p>
                        <p class="text-2xl font-black text-slate-900">
                            {{ props.timesheets?.filter(t => t.status === 'submitted').length || 0 }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Main Table Card -->
            <div class="bg-white rounded-[40px] border border-slate-100 p-8 shadow-sm space-y-8 overflow-hidden">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <IconField iconPosition="left" class="flex-1 max-w-md relative group">
                        <InputIcon class="pi pi-search text-slate-400 group-focus-within:text-teal-500 transition-colors" />
                        <InputText v-if="filters" v-model="filters['global'].value" placeholder="Rechercher un collaborateur..." 
                            class="w-full pl-12 pr-4 py-3 bg-slate-50 border-slate-100 rounded-xl focus:border-teal-500 focus:ring-teal-500 transition-all placeholder:text-slate-400 text-sm font-medium" />
                    </IconField>
                    <Button v-if="filters" icon="pi pi-filter-slash" label="Réinitialiser" outlined severity="secondary" @click="clearFilter"
                        class="rounded-xl font-bold text-xs px-4 py-2 border-slate-200 text-slate-600 hover:border-teal-500 hover:text-teal-600 transition-all" />
                </div>

                <div class="overflow-x-auto rounded-3xl border border-slate-50">

                    <DataTable v-if="filters" v-model:filters="filters" :value="props.timesheets" paginator :rows="10" 
                        dataKey="id" :globalFilterFields="['employee.first_name', 'employee.last_name', 'status']"
                        class="p-datatable-modern border-none" :pt="{ header: { class: 'hidden' } }">
                        
                        <Column header="Téléconseiller" sortable field="employee.last_name" class="px-6 py-4">
                            <template #body="{ data }">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-slate-900 flex items-center justify-center text-white font-black text-xs shadow-lg shadow-slate-900/10">
                                        {{ data.employee.first_name[0] }}{{ data.employee.last_name[0] }}
                                    </div>
                                    <div>
                                        <p class="font-black text-slate-800 text-sm leading-tight">{{ data.employee.first_name }} {{ data.employee.last_name }}</p>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Téléconseiller</p>
                                    </div>
                                </div>
                            </template>
                        </Column>

                        <Column header="Période" class="px-6 py-4">
                            <template #body="{ data }">
                                <div class="flex items-center gap-3">
                                    <div class="px-3 py-1.5 bg-slate-50 border border-slate-100 rounded-lg text-xs font-bold text-slate-600">
                                        {{ formatDate(data.period_start) }}
                                    </div>
                                    <i class="pi pi-arrow-right text-[10px] text-slate-300"></i>
                                    <div class="px-3 py-1.5 bg-slate-50 border border-slate-100 rounded-lg text-xs font-bold text-slate-600">
                                        {{ formatDate(data.period_end) }}
                                    </div>
                                </div>
                            </template>
                        </Column>

                        <Column header="Saisie" class="px-6 py-4">
                            <template #body="{ data }">
                                <div class="space-y-2 max-w-[120px]">
                                    <div class="flex justify-between items-center">
                                        <span :class="['text-[10px] font-black uppercase tracking-widest', data.stats?.is_complete ? 'text-emerald-600' : 'text-slate-400']">
                                            {{ data.stats?.jours_saisis }} / {{ data.stats?.total_jours }}j
                                        </span>
                                        <i v-if="data.stats?.is_complete" class="pi pi-check-circle text-emerald-500 text-[10px]"></i>
                                    </div>
                                    <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                                        <div :class="['h-full rounded-full transition-all duration-500', data.stats?.is_complete ? 'bg-emerald-500' : 'bg-amber-400']"
                                            :style="{ width: `${progressBar(data.stats?.jours_saisis, data.stats?.total_jours)}%` }"></div>
                                    </div>
                                </div>
                            </template>
                        </Column>

                        <Column field="status" header="Statut" sortable class="px-6 py-4 text-center">
                            <template #body="{ data }">
                                <span :class="['rounded-full px-3 py-1 text-[9px] font-black uppercase tracking-widest', 
                                    data.status === 'validated' || data.status === 'validé' ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 
                                    data.status === 'submitted' || data.status === 'en attente' ? 'bg-amber-50 text-amber-600 border border-amber-100' : 
                                    'bg-slate-50 text-slate-400 border border-slate-200']">
                                    {{ data.status }}
                                </span>
                            </template>
                        </Column>

                        <Column header="Action" alignFrozen="right" frozen class="px-6 py-4">
                            <template #body="{ data }">
                                <Button v-if="data.status === 'submitted' || data.status === 'pending'"
                                    label="Valider" icon="pi pi-check" size="small"
                                    class="bg-emerald-600 border-none text-white px-4 py-2 rounded-xl text-[9px] font-black uppercase shadow-md shadow-emerald-600/10"
                                    @click="validation(data.id)" />
                                <div v-else-if="data.status === 'validated'" class="text-emerald-500 flex items-center gap-1 font-bold text-[10px]">
                                    <i class="pi pi-lock"></i> Verrouillé
                                </div>
                                <div v-else class="text-slate-300 text-[10px] uppercase font-bold italic">En cours...</div>
                            </template>
                        </Column>
                    </DataTable>
                </div>
            </div>
        </div>

        <!-- Dialog : Création Multiple -->
        <Dialog v-model:visible="visible" modal header="Générer des feuilles de temps" :style="{ width: '450px' }" class="rounded-3xl">
            <div class="space-y-6 pt-4">
                <div class="flex flex-col gap-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Sélectionner les TCs</label>
                    <MultiSelect v-model="form.employee_id" :options="formattedTelecon" optionLabel="fullName" optionValue="id"
                        placeholder="Choisir les téléconseillers..." display="chip" filter class="w-full rounded-xl" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Début</label>
                        <DatePicker v-model="form.period_start" dateFormat="dd/mm/yy" class="w-full" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Fin</label>
                        <DatePicker v-model="form.period_end" dateFormat="dd/mm/yy" class="w-full" />
                    </div>
                </div>
            </div>
            <template #footer>
                <div class="flex gap-3 w-full mt-4">
                    <Button label="Annuler" text severity="secondary" @click="visible = false" class="flex-1 font-black text-xs" />
                    <Button label="Confirmer" @click="submit" :loading="form.processing"
                        class="flex-1 bg-teal-600 border-none font-black text-xs text-white p-3 rounded-xl shadow-lg shadow-teal-600/20" />
                </div>
            </template>
        </Dialog>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Ta personnalisation PrimeVue conservée */
:deep(.p-datatable-modern .p-datatable-thead > tr > th) {
    background-color: #f8fafc;
    color: #94a3b8;
    font-size: 10px;
    font-weight: 900;
    text-transform: uppercase;
    padding: 1.25rem 1.5rem;
    border: none;
}
:deep(.p-datatable-modern .p-datatable-tbody > tr > td) {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #f1f5f9;
}
</style>