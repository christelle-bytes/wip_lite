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
    sup: Array,
    planning: Array,
    auth: Object,
});

// --- Logique de Données ---
const formattedSup = computed(() => {
    return (props.sup || []).map((item) => ({
        ...item,
        fullName: `${item.first_name} ${item.last_name}`,
    }));
});

const visible = ref(false);
const filters = ref();

const form = useForm({
    employee_id: "",
    period_start: "",
    period_end: "",
});

const submit = () => {
    if (form.period_start) {
        // Force une string sans fuseau
        const d = new Date(form.period_start);
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        form.period_start = `${year}-${month}-${day}`; 
    }
    if (form.period_end) {
        // Force une string sans fuseau
        const d = new Date(form.period_end);
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        form.period_end = `${year}-${month}-${day}`; 
    }
    form.post(route("timesheet.store"), {
        onSuccess: () => {
            visible.value = false;
            form.reset();
        },
    });
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

const getStatusSeverity = (status) => {
    const s = status?.toLowerCase();
    if (s === "validé" || s === "validated") return "success";
    if (s === "en attente" || s === "pending") return "warn";
    if (s === "suspendu" || s === "rejected") return "danger";
    return "secondary";
};

const progressBar = (joursSaisis, totalJours) => {
    if (!totalJours || totalJours <= 0) return 0;
    if (!joursSaisis || joursSaisis <= 0) return 0;

    const percentage = Math.min(Math.round((joursSaisis / totalJours) * 100), 100);
    return percentage;
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
        },
    );
};
</script>

<template>
    <AuthenticatedLayout>
        <div class="py-6 space-y-8">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">Feuilles d'Heures</h1>
                    <p class="mt-1 text-sm text-slate-500 font-medium">Suivez la saisie et validez les temps de travail de vos équipes.</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <Button @click="visible = true"
                        class="bg-teal-600 border-none text-white px-6 py-3 rounded-xl font-bold text-xs shadow-lg shadow-teal-600/20 transition-all flex items-center gap-2">
                        <i class="pi pi-plus"></i> Nouvelle feuille
                    </Button>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white p-6 rounded-[32px] border border-slate-100 shadow-sm flex items-center gap-4">
                    <div class="h-12 w-12 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center text-xl shadow-sm">
                        <i class="pi pi-file-edit"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total feuilles</p>
                        <p class="text-2xl font-black text-slate-900">{{ props.timesheets?.length || 0 }}</p>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-[32px] border border-slate-100 shadow-sm flex items-center gap-4">
                    <div class="h-12 w-12 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center text-xl shadow-sm">
                        <i class="pi pi-clock"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">En attente</p>
                        <p class="text-2xl font-black text-slate-900">{{ props.timesheets?.filter(t => t.status === 'submitted' || t.status === 'pending').length || 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- Content Card -->
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
                        dataKey="id" filterDisplay="menu" :globalFilterFields="['status', 'employee.first_name', 'employee.last_name']"
                        class="p-datatable-modern border-none" :pt="{ header: { class: 'hidden' } }">
                        
                        <Column header="Collaborateur" sortable field="employee.last_name" class="px-6 py-4">
                            <template #body="{ data }">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-slate-900 flex items-center justify-center text-white font-black text-xs shadow-lg shadow-slate-900/10">
                                        {{ data.employee.first_name[0] }}{{ data.employee.last_name[0] }}
                                    </div>
                                    <div>
                                        <p class="font-black text-slate-800 text-sm leading-tight">{{ data.employee.first_name }} {{ data.employee.last_name }}</p>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Superviseur</p>
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

                        <Column header="Validation" class="px-6 py-4">
                            <template #body="{ data }">
                                <div v-if="data.validated_by" class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-[10px] font-black">
                                        {{ data.validator?.first_name[0] }}{{ data.validator?.last_name[0] }}
                                    </div>
                                    <div>
                                        <p class="text-[11px] font-black text-slate-700 leading-tight">{{ data.validator?.first_name }} {{ data.validator?.last_name }}</p>
                                        <p class="text-[9px] font-bold text-emerald-500 mt-0.5">{{ formatDate(data.validated_at) }}</p>
                                    </div>
                                </div>
                                <span v-else class="text-[10px] font-bold text-slate-300 uppercase tracking-widest">En attente</span>
                            </template>
                        </Column>

                        <Column class="px-6 py-4 text-right">
                            <template #body="{ data }">
                                <Button v-if="!data.validated_by && (data.status === 'submitted' || data.status === 'en attente')"
                                    label="Approuver" icon="pi pi-check-circle" size="small"
                                    class="bg-emerald-600 border-none text-white px-4 py-2 rounded-xl font-black text-[9px] uppercase tracking-widest shadow-lg shadow-emerald-600/20 transition-all"
                                    @click="validation(data.id)" />
                                <div v-else class="h-8 w-8 inline-flex items-center justify-center rounded-xl bg-slate-50 text-slate-300">
                                    <i class="pi pi-lock text-xs"></i>
                                </div>
                            </template>
                        </Column>
                    </DataTable>
                </div>
            </div>
        </div>

        <!-- Dialog Nouvelle Feuille -->
        <Dialog v-model:visible="visible" modal header="Nouvelle feuille d'heures" 
            class="rounded-3xl shadow-2xl border-none" :style="{ width: '450px' }"
            :pt="{ header: { class: 'bg-slate-50 p-6 rounded-t-3xl border-b border-slate-100' }, content: { class: 'p-8 bg-white' }, footer: { class: 'p-6 bg-slate-50 rounded-b-3xl border-t border-slate-100' } }">
            <div class="space-y-6">
                <div class="flex flex-col gap-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Collaborateurs concernés</label>
                    <MultiSelect v-model="form.employee_id" :options="formattedSup" optionLabel="fullName" optionValue="id"
                        placeholder="Choisir les superviseurs..." display="chip" filter
                        class="w-full rounded-xl border-slate-200 focus:border-teal-500 shadow-sm" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Début</label>
                        <DatePicker v-model="form.period_start" showIcon dateFormat="dd/mm/yy" placeholder="JJ/MM/AA"
                            class="w-full rounded-xl border-slate-200 focus:border-teal-500" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Fin</label>
                        <DatePicker v-model="form.period_end" showIcon dateFormat="dd/mm/yy" placeholder="JJ/MM/AA"
                            class="w-full rounded-xl border-slate-200 focus:border-teal-500" />
                    </div>
                </div>
            </div>

            <template #footer>
                <div class="flex gap-3 w-full">
                    <Button label="Annuler" class="flex-1 p-button-text p-button-secondary font-black text-xs uppercase" @click="visible = false" />
                    <Button label="Créer la feuille" @click="submit" :loading="form.processing"
                        class="flex-1 bg-teal-600 border-none font-black text-xs uppercase p-3 rounded-xl shadow-lg shadow-teal-600/20" />
                </div>
            </template>
        </Dialog>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Suppression des styles DataTable par défaut pour notre look moderne */
:deep(.p-datatable-modern .p-datatable-thead > tr > th) {
    background-color: #f8fafc;
    color: #94a3b8;
    font-size: 10px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    padding: 1.25rem 1.5rem;
    border: none;
}

:deep(.p-datatable-modern .p-datatable-tbody > tr) {
    background-color: transparent;
    transition: all 0.2s;
}

:deep(.p-datatable-modern .p-datatable-tbody > tr:hover) {
    background-color: #f8fafc;
}

:deep(.p-datatable-modern .p-datatable-tbody > tr > td) {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #f1f5f9;
}

:deep(.p-paginator) {
    background-color: #f8fafc;
    border: none;
    padding: 1rem;
    border-radius: 0 0 24px 24px;
}
</style>
