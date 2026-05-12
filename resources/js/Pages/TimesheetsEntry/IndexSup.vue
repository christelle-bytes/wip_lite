<script setup>
import { ref, computed, onMounted, watch } from "vue";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Tag from "primevue/tag";
import Select from "primevue/select";
import FloatLabel from "primevue/floatlabel";
import InputText from "primevue/inputtext";
import IconField from "primevue/iconfield";
import InputIcon from "primevue/inputicon";
import Dialog from "primevue/dialog";
import DatePicker from "primevue/datepicker";
import InputNumber from "primevue/inputnumber";
import Button from "primevue/button";
import AuthenticatedLayout from "../../Layouts/AuthenticatedLayout.vue";
import { Link, router, useForm } from "@inertiajs/vue3";
import { FilterMatchMode } from "@primevue/core/api";


const props = defineProps({
    supervisors: { type: Array, default: () => [] },
    allPeriods: { type: Array, default: () => [] },
    selectedPeriod: { type: Object, default: null },
    auth: Object,
});

const expandedRows = ref([]);
const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

// Sélection
const selectedPeriod = ref(props.selectedPeriod);

// Watcher pour le filtrage
watch(selectedPeriod, (newPeriod) => {
    if (newPeriod) {
        router.get(route('index.sup'), {
            start_date: newPeriod.period_start,
            end_date: newPeriod.period_end
        }, { preserveState: true, replace: true });
    } else {
        router.get(route('index.sup'), {}, { preserveState: true, replace: true });
    }
});

// Options sécurisées
const periodOptions = computed(() => {
    return Array.isArray(props.allPeriods) ? props.allPeriods : [];
});

const getFilteredEntries = (supervisor) => {
    return supervisor.timesheet?.flatMap(ts => (ts.entries || []).map(entry => ({
        ...entry,
        timesheet_status: ts.status
    }))) || [];
};

const formatHours = (v) => {
    const num = parseFloat(v);
    if (isNaN(num)) return "0h00";
    const hours = Math.floor(num);
    const minutes = Math.round((num % 1) * 60);
    return `${hours}h${minutes.toString().padStart(2, "0")}`;
};


const formatDate = (d) =>
    d ? new Date(d).toLocaleDateString("fr-FR", { day: "2-digit", month: "2-digit" }) : "-";

// --- Modification des entrées ---
const editDialogVisible = ref(false);
const editingEntry = ref(null);

const editForm = useForm({
    employee_ids: [], // Le controller attend un tableau d'IDs
    date: null,
    check_in: null,
    check_out: null,
    break_duration: 0,
    comment: "",
});

const openEditDialog = (entry) => {
    editingEntry.value = entry;
    
    // On parse les dates/heures pour le DatePicker
    const entryDate = new Date(entry.date);
    
    // Pour check_in et check_out, on crée des objets Date
    const [hIn, mIn] = entry.check_in.split(':');
    const checkInDate = new Date();
    checkInDate.setHours(parseInt(hIn), parseInt(mIn), 0);

    const [hOut, mOut] = entry.check_out.split(':');
    const checkOutDate = new Date();
    checkOutDate.setHours(parseInt(hOut), parseInt(mOut), 0);

    editForm.employee_ids = [entry.employee_id];
    editForm.date = entryDate;
    editForm.check_in = checkInDate;
    editForm.check_out = checkOutDate;
    editForm.break_duration = parseInt(entry.break_duration);
    editForm.comment = entry.comment || "";
    
    editDialogVisible.value = true;
};

const submitEdit = () => {
    // Formattage de la date locale (Y-m-d)
    const year = editForm.date.getFullYear();
    const month = String(editForm.date.getMonth() + 1).padStart(2, '0');
    const day = String(editForm.date.getDate()).padStart(2, '0');
    const formattedDate = `${year}-${month}-${day}`;

    // Formattage des heures locales (HH:mm) pour éviter le décalage UTC
    const formatLocalTime = (date) => {
        if (!date) return null;
        const h = String(date.getHours()).padStart(2, '0');
        const m = String(date.getMinutes()).padStart(2, '0');
        return `${h}:${m}`;
    };

    router.post(route("store.sup"), {
        ...editForm.data(),
        date: formattedDate,
        check_in: formatLocalTime(editForm.check_in),
        check_out: formatLocalTime(editForm.check_out),
    }, {
        onSuccess: () => {
            editDialogVisible.value = false;
            editingEntry.value = null;
        }
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <div class="py-6 space-y-8">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">Reporting Superviseurs</h1>
                    <p class="mt-1 text-sm text-slate-500 font-medium">Visualisation détaillée des heures de travail par période.</p>
                </div>
                <div class="flex items-center gap-4 bg-white p-2 rounded-2xl border border-slate-100 shadow-sm">
                    <Link
                        :href="route('entry.sup')"
                        class="px-5 py-2.5 rounded-xl bg-slate-900 text-white font-black text-[10px] uppercase tracking-widest shadow-lg shadow-slate-900/10 hover:bg-slate-800 transition-all flex items-center gap-2"
                    >
                        <i class="pi pi-pencil"></i>
                        Saisie Heures
                    </Link>
                    <div class="h-8 w-[1px] bg-slate-100 mx-1"></div>
                    <FloatLabel variant="on" class="min-w-64">
                        <Select
                            v-model="selectedPeriod"
                            :options="periodOptions"
                            optionLabel="label"
                            placeholder="Sélectionner une période"
                            class="w-full border-none shadow-none focus:ring-0 text-sm font-bold text-slate-700"
                        />
                    </FloatLabel>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white p-6 rounded-[32px] border border-slate-100 shadow-sm flex items-center gap-4">
                    <div class="h-12 w-12 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center text-xl shadow-sm">
                        <i class="pi pi-users"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Superviseurs</p>
                        <p class="text-2xl font-black text-slate-900">{{ props.supervisors?.length || 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- Tableau Principal -->
            <div class="bg-white rounded-[40px] border border-slate-100 p-8 shadow-sm space-y-8 overflow-hidden">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="flex items-center gap-3">
                        <div class="h-8 w-8 rounded-xl bg-slate-900 text-white flex items-center justify-center text-sm shadow-sm">
                            <i class="pi pi-table"></i>
                        </div>
                        <h2 class="text-xl font-black text-slate-900 tracking-tight">Détails par collaborateur</h2>
                    </div>

                    <IconField iconPosition="left" class="w-full md:max-w-md">
                        <InputIcon class="pi pi-search text-slate-400" />
                        <InputText
                            v-model="filters['global'].value"
                            placeholder="Rechercher un superviseur (nom, matricule...)"
                            class="w-full border-slate-100 bg-slate-50 rounded-xl text-sm"
                        />
                    </IconField>
                </div>

                <div class="overflow-x-auto rounded-3xl border border-slate-50">
                    <DataTable
                        v-model:expandedRows="expandedRows"
                        v-model:filters="filters"
                        :value="props.supervisors"
                        dataKey="id"
                        class="p-datatable-modern border-none"
                        rowHover
                        paginator
                        :rows="10"
                        :globalFilterFields="['first_name', 'last_name', 'matricule']"
                    >
                        <Column expander style="width: 4rem" />

                        <!-- Collaborateur -->
                        <Column header="Collaborateur" style="min-width: 18rem">
                            <template #body="{ data }">
                                <div class="flex items-center gap-4">
                                    <div class="w-11 h-11 rounded-2xl bg-slate-900 flex items-center justify-center font-black text-white text-xs shadow-lg shadow-slate-900/10">
                                        {{ data.first_name?.[0] }}{{ data.last_name?.[0] }}
                                    </div>
                                    <div>
                                        <p class="font-black text-slate-800 leading-tight">
                                            {{ data.first_name }} {{ data.last_name }}
                                        </p>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">
                                            Matricule: {{ data.matricule }}
                                        </p>
                                    </div>
                                </div>
                            </template>
                        </Column>

                        <Column header="Poste" style="min-width: 12rem">
                            <template #body="{ data }">
                                <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-lg text-[10px] font-black uppercase tracking-widest border border-slate-200">
                                    {{ data.position?.name || 'Superviseur' }}
                                </span>
                            </template>
                        </Column>

                        <Column header="Total Période" style="min-width: 11rem" class="text-center">
                            <template #body="{ data }">
                                <div class="inline-flex items-center gap-2 px-4 py-2 bg-teal-50 text-teal-700 rounded-2xl border border-teal-100">
                                    <i class="pi pi-clock text-xs"></i>
                                    <span class="text-lg font-black tracking-tight">
                                        {{
                                            formatHours(
                                                getFilteredEntries(data).reduce(
                                                    (acc, curr) => acc + (curr?.total_hours || 0),
                                                    0
                                                )
                                            )
                                        }}
                                    </span>
                                </div>
                            </template>
                        </Column>

                        <!-- Expansion -->
                        <template #expansion="{ data }">
                            <div class="p-8 bg-slate-50/50 border-y border-slate-100">
                                <div class="flex items-center justify-between mb-6">
                                    <div class="flex items-center gap-3">
                                        <div class="h-8 w-8 rounded-lg bg-teal-600 text-white flex items-center justify-center text-xs">
                                            <i class="pi pi-calendar"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-black text-slate-900">Période sélectionnée</p>
                                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                                {{ selectedPeriod?.label || "Toutes les périodes" }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="px-4 py-2 bg-white rounded-xl border border-slate-200 shadow-sm text-[10px] font-black uppercase tracking-widest text-slate-500">
                                        {{ getFilteredEntries(data).length }} jour(s) saisis
                                    </div>
                                </div>

                                <DataTable
                                    :value="getFilteredEntries(data)"
                                    class="p-datatable-modern rounded-2xl overflow-hidden border border-slate-100 shadow-sm bg-white"
                                >
                                    <Column field="date" header="Date" style="width: 150px">
                                        <template #body="sp">
                                            <span class="font-bold text-slate-700">{{ formatDate(sp.data.date) }}</span>
                                        </template>
                                    </Column>
                                    <Column header="Entrée / Sortie" style="min-width: 200px">
                                        <template #body="sp">
                                            <div class="flex items-center gap-3">
                                                <span class="px-2 py-1 bg-slate-100 rounded text-[10px] font-black text-slate-600">{{ sp.data.check_in }}</span>
                                                <i class="pi pi-arrow-right text-[8px] text-slate-300"></i>
                                                <span class="px-2 py-1 bg-slate-100 rounded text-[10px] font-black text-slate-600">{{ sp.data.check_out }}</span>
                                            </div>
                                        </template>
                                    </Column>
                                    <Column header="Durée" style="width: 130px">
                                        <template #body="sp">
                                            <span class="font-black text-slate-900">
                                                {{ formatHours(sp.data.total_hours) }}
                                            </span>
                                        </template>
                                    </Column>
                                    <Column header="Heures supplémentaires">
                                        <template #body="sp">
                                            <div v-if="sp.data.overtime_hours > 0" class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-amber-50 text-amber-600 rounded-lg border border-amber-100">
                                                <i class="pi pi-bolt text-[10px]"></i>
                                                <span class="text-[10px] font-black uppercase tracking-widest">+{{ formatHours(sp.data.overtime_hours) }}</span>
                                            </div>
                                            <span v-else class="text-slate-300 text-xs font-bold">—</span>
                                        </template>
                                    </Column>
                                    <Column header="Action" style="width: 100px">
                                        <template #body="sp">
                                            <div class="flex items-center gap-2">
                                                <Button
                                                    v-if="sp.data.timesheet_status !== 'validated'"
                                                    icon="pi pi-pencil"
                                                    text
                                                    rounded
                                                    severity="secondary"
                                                    size="small"
                                                    @click="openEditDialog(sp.data)"
                                                />
                                                <Tag v-else value="Validé" severity="success" class="text-[8px]" />
                                            </div>
                                        </template>
                                    </Column>
                                </DataTable>
                            </div>
                        </template>
                    </DataTable>
                </div>
            </div>
        </div>

        <!-- Dialog de modification -->
        <Dialog
            v-model:visible="editDialogVisible"
            modal
            header="Modifier l'entrée"
            :style="{ width: '30rem' }"
            class="p-fluid"
        >
            <div class="space-y-6 py-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="text-xs font-black uppercase text-slate-400 tracking-widest">Date</label>
                        <DatePicker v-model="editForm.date" dateFormat="dd/mm/yy" disabled class="bg-slate-50" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="text-xs font-black uppercase text-slate-400 tracking-widest">Pause (min)</label>
                        <InputNumber v-model="editForm.break_duration" :min="0" :max="120" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="text-xs font-black uppercase text-slate-400 tracking-widest">Arrivée</label>
                        <DatePicker v-model="editForm.check_in" timeOnly showIcon />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="text-xs font-black uppercase text-slate-400 tracking-widest">Départ</label>
                        <DatePicker v-model="editForm.check_out" timeOnly showIcon />
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-xs font-black uppercase text-slate-400 tracking-widest">Commentaire</label>
                    <InputText v-model="editForm.comment" placeholder="Optionnel..." />
                </div>
            </div>

            <template #footer>
                <div class="flex gap-3 justify-end mt-4">
                    <Button label="Annuler" text severity="secondary" @click="editDialogVisible = false" class="font-bold" />
                    <Button label="Enregistrer" severity="primary" @click="submitEdit" :loading="editForm.processing" class="font-bold px-6" />
                </div>
            </template>
        </Dialog>
    </AuthenticatedLayout>
</template>

<style scoped>
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

:deep(.p-datatable-modern .p-datatable-tbody > tr > td) {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #f1f5f9;
}

:deep(.p-datatable-modern .p-datatable-row-expansion > td) {
    padding: 0;
}
</style>
