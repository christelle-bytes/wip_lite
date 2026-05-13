<script setup>
import { ref, computed, watch } from "vue";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Tag from "primevue/tag";
import Select from "primevue/select";
import MultiSelect from "primevue/multiselect";
import FloatLabel from "primevue/floatlabel";
import InputText from "primevue/inputtext";
import IconField from "primevue/iconfield";
import InputIcon from "primevue/inputicon";
import Dialog from "primevue/dialog";
import DatePicker from "primevue/datepicker";
import InputNumber from "primevue/inputnumber";
import Button from "primevue/button";
import AuthenticatedLayout from "../../Layouts/AuthenticatedLayout.vue";
import { router, useForm, usePage } from "@inertiajs/vue3";
import { FilterMatchMode } from "@primevue/core/api";

// ==================== PROPS ====================
const props = defineProps({
    supervisors: { type: Array, default: () => [] },
    allPeriods: { type: Array, default: () => [] },
    selectedPeriod: { type: Object, default: null },
    auth: Object,
});

const page = usePage();
const currentPeriod = ref(props.selectedPeriod);

// Watcher pour déclencher la navigation lors du changement de période
watch(currentPeriod, (newPeriod) => {
    if (newPeriod) {
        router.get(route('index.sup'), {
            start_date: newPeriod.period_start,
            end_date: newPeriod.period_end
        }, { preserveState: true, replace: true });
    }
});

// ==================== TABLEAU PRINCIPAL ====================
const expandedRows = ref([]);
const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

// ==================== DIALOG MODIFICATION ====================
const editDialogVisible = ref(false);
const editingEntry = ref(null);

const editForm = useForm({
    employee_ids: [],
    date: null,
    check_in: null,
    check_out: null,
    break_duration: 0,
    comment: "",
});

// ==================== DIALOG NOUVELLE SAISIE ====================
const visible = ref(false);
const selectedPeriodForCreation = ref(null);
const selectedSupervisors = ref([]);

// Limites de date pour le DatePicker de création
const minDate = computed(() => selectedPeriodForCreation.value ? new Date(selectedPeriodForCreation.value.period_start) : null);
const maxDate = computed(() => selectedPeriodForCreation.value ? new Date(selectedPeriodForCreation.value.period_end) : null);

// Fonction utilitaire pour comparer les dates proprement
function correctDate(date) {
    if (!date) return null;
    const d = new Date(date);
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

// Superviseurs filtrés localement selon la période sélectionnée
const filteredSupervisors = computed(() => {
    if (!selectedPeriodForCreation.value) return [];

    const targetStart = correctDate(selectedPeriodForCreation.value.period_start);
    const targetEnd = correctDate(selectedPeriodForCreation.value.period_end);

    return props.supervisors.filter(sup => {
        return sup.timesheet?.some(ts =>
            correctDate(ts.period_start) === targetStart &&
            correctDate(ts.period_end) === targetEnd &&
            ts.status === 'draft'
        );
    });
});

const form = useForm({
    employee_ids: [],
    date: null,
    check_in: null,
    check_out: null,
    break_duration: 0,
    absence_type: "",
    comment: "",
});

const typeAbs = ref([
    { name: "Formation" },
    { name: "Congé" },
    { name: "Incident de travail" },
    { name: "Autre" },
]);

// ==================== FONCTIONS ====================
const getFilteredEntries = (supervisor) => {
    if (!supervisor.timesheet) return [];
    
    // On ne veut sommer que les entrées de la période actuellement sélectionnée dans le filtre principal
    const targetStart = currentPeriod.value ? correctDate(currentPeriod.value.period_start) : null;
    const targetEnd = currentPeriod.value ? correctDate(currentPeriod.value.period_end) : null;

    const filtered = supervisor.timesheet
        .filter(ts => {
            if (!targetStart || !targetEnd) return true;
            return correctDate(ts.period_start) === targetStart && correctDate(ts.period_end) === targetEnd;
        })
        .flatMap(ts => (ts.entries || []).map(entry => ({
            ...entry,
            timesheet_status: ts.status,
            period_label: `${new Date(ts.period_start).toLocaleDateString('fr-FR')} au ${new Date(ts.period_end).toLocaleDateString('fr-FR')}`
        })));

    return filtered;
};

const getTotalHours = (supervisor) => {
    const entries = getFilteredEntries(supervisor);
    const total = entries.reduce((acc, e) => {
        const hours = parseFloat(e.total_hours);
        return acc + (isNaN(hours) ? 0 : hours);
    }, 0);
    return total;
};

const formatHours = (v) => {
    const num = parseFloat(v);
    if (isNaN(num)) return "0h00";
    const hours = Math.floor(num);
    const minutes = Math.round((num % 1) * 60);
    return `${hours}h${minutes.toString().padStart(2, "0")}`;
};

const formatDate = (d) =>
    d ? new Date(d).toLocaleDateString("fr-FR", { weekday: 'long', day: "2-digit", month: "short", year: 'numeric' }) : "-";

// Ouvrir dialog nouvelle saisie
const createEntry = () => {
    selectedPeriodForCreation.value = null;
    selectedSupervisors.value = [];
    form.reset();
    visible.value = true;
};

// Soumission nouvelle saisie
const submit = () => {
    if (!selectedPeriodForCreation.value) {
              return  toast.add({
        severity: 'error',
        summary: 'Alerte',
        detail: 'Veuillez sélectionner une période',
        life: 5000,
    });
    }
    if (selectedSupervisors.value.length === 0) {
         return  toast.add({
        severity: 'error',
        summary: 'Alerte',
        detail: 'Veuillez sélectionner au moins un superviseur',
        life: 5000,
    });
    }

    const formatLocalTime = (date) => {
        if (!(date instanceof Date)) return date;
        const h = String(date.getHours()).padStart(2, '0');
        const m = String(date.getMinutes()).padStart(2, '0');
        return `${h}:${m}`;
    };

    const formattedDate = form.date 
        ? `${form.date.getFullYear()}-${String(form.date.getMonth()+1).padStart(2,'0')}-${String(form.date.getDate()).padStart(2,'0')}`
        : null;

    const payload = {
        ...form.data(),
        date: formattedDate,
        check_in: formatLocalTime(form.check_in),
        check_out: formatLocalTime(form.check_out),
        employee_ids: selectedSupervisors.value.map(s => s.id),
    };

    router.post(route("store.sup"), payload, {
        onSuccess: () => {
            selectedPeriodForCreation.value = null;
    selectedSupervisors.value = [];
            form.reset();
    visible.value = false;
        }
});
};

// Modification d'une entrée
const openEditDialog = (entry) => {
    editingEntry.value = entry;
    
    const entryDate = new Date(entry.date);
    const [hIn, mIn] = entry.check_in.split(':');
    const [hOut, mOut] = entry.check_out.split(':');

    const checkInDate = new Date();
    checkInDate.setHours(parseInt(hIn), parseInt(mIn), 0);

    const checkOutDate = new Date();
    checkOutDate.setHours(parseInt(hOut), parseInt(mOut), 0);

    editForm.employee_ids = [entry.employee_id || entry.timesheet_id];
    editForm.date = entryDate;
    editForm.check_in = checkInDate;
    editForm.check_out = checkOutDate;
    editForm.break_duration = parseInt(entry.break_duration || 0);
    editForm.comment = entry.comment || "";

    editDialogVisible.value = true;
};

const submitEdit = () => {
    const formattedDate = editForm.date 
        ? `${editForm.date.getFullYear()}-${String(editForm.date.getMonth()+1).padStart(2,'0')}-${String(editForm.date.getDate()).padStart(2,'0')}`
        : null;

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

// watch(selectedPeriodForCreation, (newPeriod) => {
//     if (newPeriod) {
//         router.get(route('index.sup'), {
//             start_date: newPeriod.period_start,
//             end_date: newPeriod.period_end
//         }, { preserveState: true, replace: true });
//     }
// });

const userRole = computed(() => page.props.auth?.user?.role?.name || '');
</script>

<template>
    <AuthenticatedLayout>
        <div class="py-6 space-y-8">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">Reporting Superviseurs</h1>
                    <p class="mt-1 text-sm text-slate-500 font-medium">Visualisation détaillée des heures de travail par période.</p>
                </div>
                <div class="flex items-center gap-4">
                    <Button
                    label="Feuille d'heures"
                    icon="pi pi-file-edit"
                    v-if="userRole === 'CP' || userRole === 'Admin'"
                        @click="router.visit(route('timesheet.index'))"
                        class="bg-green-600 border-none text-white px-6 py-3 rounded-xl font-black text-xs uppercase tracking-widest shadow-lg shadow-blue-600/20"
                    />

                    <Link :href="route('timesheet.index')"
                    v-if="userRole === 'CP' || userRole === 'Admin'"
                    icon="pi pi-file-edit"
                        class="bg-green-600 border-none text-white px-6 py-3 rounded-xl font-black text-xs uppercase tracking-widest shadow-lg shadow-blue-600/20">
                        
                        Feuille d'heures
                    </Link>
                    <Button
                        label="Nouvelle saisie"
                        icon="pi pi-plus"
                        @click="createEntry"
                        class="bg-teal-600 border-none text-white px-6 py-3 rounded-xl font-black text-xs uppercase tracking-widest shadow-lg shadow-teal-600/20"
                    />
                </div>
            </div>

            <!-- Stats -->
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

            <!-- Tableau -->
            <div class="bg-white rounded-[40px] border border-slate-100 p-8 shadow-sm">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-6">
                    <div class="flex items-center gap-3">
                        <div class="h-8 w-8 rounded-xl bg-slate-900 text-white flex items-center justify-center text-sm shadow-sm">
                            <i class="pi pi-table"></i>
                        </div>
                        <h2 class="text-xl font-black text-slate-900">Détails par collaborateur</h2>
                    </div>

                    <div class="flex items-center gap-4">
                        <IconField iconPosition="left" class="w-80">
                            <InputIcon class="pi pi-search text-slate-400" />
                            <InputText
                                v-model="filters['global'].value"
                                placeholder="Rechercher..."
                                class="w-full border-slate-100 bg-slate-50 rounded-xl"
                            />
                        </IconField>

                        <FloatLabel variant="on" class="min-w-64">
                            <Select
                                v-model="currentPeriod"
                                :options="props.allPeriods"
                                optionLabel="label"
                                placeholder="Filtrer par période"
                                class="w-full"
                            />
                        </FloatLabel>
                    </div>
                </div>

                <DataTable
                    v-model:expandedRows="expandedRows"
                    v-model:filters="filters"
                    :value="props.supervisors"
                    dataKey="id"
                    :globalFilterFields="['first_name', 'last_name', 'matricule']"
                    paginator :rows="10"
                    rowHover
                >
                    <Column expander style="width: 4rem" />

                    <Column header="Collaborateur" style="min-width: 18rem">
                        <template #body="{ data }">
                            <div class="flex items-center gap-4">
                                <div class="w-11 h-11 rounded-2xl bg-slate-900 flex items-center justify-center font-black text-white text-xs">
                                    {{ data.first_name?.[0] }}{{ data.last_name?.[0] }}
                                </div>
                                <div>
                                    <p class="font-black">{{ data.first_name }} {{ data.last_name }}</p>
                                    <p class="text-xs text-slate-500">Matricule: {{ data.matricule }}</p>
                                </div>
                            </div>
                        </template>
                    </Column>

                    <Column header="Poste" style="min-width: 12rem">
                        <template #body="{ data }">
                            <div class="flex flex-col gap-1">
                                <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-lg text-xs font-bold w-fit">
                                    {{ data.position?.name }}
                                </span>
                                <span class="text-[10px] text-slate-400 font-medium italic" v-if="currentPeriod">
                                    Période: {{ currentPeriod.label }}
                                </span>
                            </div>
                        </template>
                    </Column>

                    <Column header="Total Période" style="min-width: 11rem">
                        <template #body="{ data }">
                            <span class="font-black text-teal-700">
                                {{ formatHours(getTotalHours(data)) }}
                            </span>
                        </template>
                    </Column>

                    <template #expansion="{ data }">
                        <div class="p-8 bg-slate-50">
                            <DataTable :value="getFilteredEntries(data)" class="bg-white rounded-2xl">
                                <Column field="date" header="Date">
                                <template #body="sp">
                                    <div class="flex flex-col">
                                        <span class="capitalize">{{ formatDate(sp.data.date) }}</span>
                                        <span class="text-[10px] text-slate-400 font-medium">Période: {{ sp.data.period_label }}</span>
                                    </div>
                                </template>
                                </Column>
                                <Column header="Entrée / Sortie">
                                    <template #body="sp">{{ sp.data.check_in }} - {{ sp.data.check_out }}</template>
                                </Column>
                                <Column header="Durée">
                                    <template #body="sp">{{ formatHours(sp.data.total_hours) }}</template>
                                </Column>
                                <Column header="Heures Sup.">
                                    <template #body="sp">
                                        <Tag v-if="sp.data.overtime_hours > 0" 
                                             :value="`+${formatHours(sp.data.overtime_hours)}`" 
                                             severity="warn" 
                                             class="text-[10px]" />
                                        <span v-else class="text-slate-300">-</span>
                                    </template>
                                </Column>
                                <Column header="Action">
                                    <template #body="sp">
                                        <Button v-if="sp.data.timesheet_status !== 'validated'" 
                                                icon="pi pi-pencil" text severity="secondary" 
                                                @click="openEditDialog(sp.data)" />
                                    </template>
                                </Column>
                            </DataTable>
                        </div>
                    </template>
                </DataTable>
            </div>
        </div>

        <!-- Dialog Modification -->
        <Dialog v-model:visible="editDialogVisible" modal header="Modifier l'entrée" :style="{ width: '420px' }">
            <!-- ... (ton contenu existant du dialog modification) ... -->
            <template #footer>
                <Button label="Annuler" text @click="editDialogVisible = false" />
                <Button label="Enregistrer" severity="primary" @click="submitEdit" :loading="editForm.processing" />
            </template>
        </Dialog>

        <!-- Dialog Nouvelle Saisie -->
        <Dialog v-model:visible="visible" modal header="Nouvelle saisie groupée" :style="{ width: '480px' }">
            <div class="space-y-6">
                <!-- Période -->
                <div>
                    <label class="block text-xs font-black uppercase text-slate-500 mb-2">Période</label>
                    <Select
                        v-model="selectedPeriodForCreation"
                        :options="props.allPeriods"
                        optionLabel="label"
                        placeholder="Sélectionner une période"
                        class="w-full"
                    />
                </div>

                <!-- Superviseurs filtrés -->
                <div>
                    <label class="block text-xs font-black uppercase text-slate-500 mb-2">
                        Superviseurs ({{ filteredSupervisors.length }} disponibles)
                    </label>
                    <MultiSelect
                        v-model="selectedSupervisors"
                        :options="filteredSupervisors"
                        optionLabel="first_name"
                        placeholder="Choisir les superviseurs"
                        :disabled="!selectedPeriodForCreation"
                        display="chip"
                        class="w-full"
                        :filter="true"
                    >
                        <template #option="slotProps">
                            <div class="flex items-center gap-2">
                                <span>{{ slotProps.option.first_name }} {{ slotProps.option.last_name }}</span>
                            </div>
                        </template>
                    </MultiSelect>
                </div>

                <!-- Champs existants -->
                <div>
                    <label class="block text-xs font-black uppercase text-slate-500 mb-2">Date</label>
                    <DatePicker 
                        v-model="form.date" 
                        showIcon 
                        dateFormat="dd/mm/yy" 
                        class="w-full" 
                        :minDate="minDate"
                        :maxDate="maxDate"
                        :disabled="!selectedPeriodForCreation"
                    />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-black uppercase text-slate-500 mb-2">Entrée</label>
                        <DatePicker v-model="form.check_in" timeOnly showIcon class="w-full" />
                    </div>
                    <div>
                        <label class="block text-xs font-black uppercase text-slate-500 mb-2">Sortie</label>
                        <DatePicker v-model="form.check_out" timeOnly showIcon class="w-full" />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-black uppercase text-slate-500 mb-2">Pause (minutes)</label>
                    <InputNumber v-model="form.break_duration" :min="0" :max="180" class="w-full" />
                </div>
            </div>

            <template #footer>
                <Button label="Annuler" severity="secondary" text @click="visible = false" class="flex-1" />
                <Button 
                    label="Enregistrer les entrées" 
                    severity="primary"
                    @click="submit"
                    :loading="form.processing"
                    :disabled="!selectedPeriodForCreation || selectedSupervisors.length === 0"
                    class="flex-1"
                />
            </template>
        </Dialog>
    </AuthenticatedLayout>
</template>