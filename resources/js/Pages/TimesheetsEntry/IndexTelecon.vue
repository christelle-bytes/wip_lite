<script setup>
import { ref, watch, computed, onMounted } from "vue";
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
import { Link, router, useForm, usePage } from "@inertiajs/vue3";
import { FilterMatchMode } from "@primevue/core/api";

const props = defineProps({
    telecon: Array,
    allPeriods: Array,
    selectedPeriod: Object,
    auth: Object,
    currentMonth: String,
});

const page = usePage();
const expandedRows = ref([]);
const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

// Sélection de la période
const selectedPeriod = ref(props.selectedPeriod);

// Options sécurisées pour le sélecteur
const periodOptions = computed(() => {
    return Array.isArray(props.allPeriods) ? props.allPeriods : [];
});

// Watcher pour déclencher la navigation lors du changement de période
watch(selectedPeriod, (newPeriod) => {
    if (newPeriod) {
        router.get(route('index.telecon'), {
            start_date: newPeriod.period_start,
            end_date: newPeriod.period_end
        }, { preserveState: true, replace: true });
    }
});

// Formattage simple
const formatH = (v) => {
    const num = parseFloat(v);
    if (isNaN(num)) return "0h00";
    const hours = Math.floor(num);
    const minutes = Math.round((num % 1) * 60);
    return `${hours}h${minutes.toString().padStart(2, "0")}`;
};

const formatD = (d) =>
    new Date(d).toLocaleDateString("fr-FR", {
        weekday: 'long',
        day: "2-digit",
        month: "short",
        year: 'numeric'
    });

// Récupère toutes les entrées de l'employé
const getEntries = (employee) => {
    if (!employee.timesheet || employee.timesheet.length === 0) return [];
    
    return employee.timesheet.flatMap((ts) => (ts.entries || []).map(entry => ({
        ...entry,
        timesheet_status: ts.status,
        period_label: `${new Date(ts.period_start).toLocaleDateString('fr-FR')} au ${new Date(ts.period_end).toLocaleDateString('fr-FR')}`
    })));
};

// Calcul du total pour l'employé
const getTotal = (employee) => {
    const entries = getEntries(employee);
    return entries.reduce((acc, curr) => {
        const val = parseFloat(curr.total_hours);
        return acc + (isNaN(val) ? 0 : val);
    }, 0);
};

// --- Modification des entrées ---
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

const openEditDialog = (entry) => {
    editingEntry.value = entry;
    
    const entryDate = new Date(entry.date);
    
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

    // On utilise la route 'store.telecon' qui gère l'upsert pour les TCs
    router.post(route("store.telecon"), {
        ...editForm.data(),
        date: formattedDate,
        check_in: formatLocalTime(editForm.check_in),
        check_out: formatLocalTime(editForm.check_out),
        sup_id: props.auth.user.employee.id, // On passe l'ID du sup connecté
        tc_ids: editForm.employee_ids // Le controller attend tc_ids pour storeTelecon
    }, {
        onSuccess: () => {
            editDialogVisible.value = false;
            editingEntry.value = null;
        }
    });
};

// --- Dialogue Nouvelle Saisie (Groupée) ---
const visible = ref(false);
const selectedPeriodForCreation = ref(null);
const selectedTCs = ref([]);

function correctDate(date) {
    if (!date) return null;
    const d = new Date(date);
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

const minDate = computed(() => selectedPeriodForCreation.value ? new Date(selectedPeriodForCreation.value.period_start) : null);
const maxDate = computed(() => selectedPeriodForCreation.value ? new Date(selectedPeriodForCreation.value.period_end) : null);

// Téléconseillers filtrés : même période + statut 'draft'
const filteredTelecon = computed(() => {
    if (!selectedPeriodForCreation.value) return [];
    
    const targetStart = correctDate(selectedPeriodForCreation.value.period_start);
    const targetEnd = correctDate(selectedPeriodForCreation.value.period_end);

    return props.telecon.filter(tc => {
        return tc.timesheet?.some(ts => 
            correctDate(ts.period_start) === targetStart && 
            correctDate(ts.period_end) === targetEnd
        );
    });
});

const createForm = useForm({
    tc_ids: [],
    date: null,
    check_in: null,
    check_out: null,
    break_duration: 0,
    comment: "",
});

const openCreateDialog = () => {
    selectedPeriodForCreation.value = null;
    selectedTCs.value = [];
    createForm.reset();
    visible.value = true;
};

const submitCreate = () => {
    const formattedDate = createForm.date 
        ? `${createForm.date.getFullYear()}-${String(createForm.date.getMonth()+1).padStart(2,'0')}-${String(createForm.date.getDate()).padStart(2,'0')}`
        : null;

    const formatLocalTime = (date) => {
        if (!date) return null;
        const h = String(date.getHours()).padStart(2, '0');
        const m = String(date.getMinutes()).padStart(2, '0');
        return `${h}:${m}`;
    };

    router.post(route("store.telecon"), {
        ...createForm.data(),
        date: formattedDate,
        check_in: formatLocalTime(createForm.check_in),
        check_out: formatLocalTime(createForm.check_out),
        sup_id: props.auth.user.employee?.id,
        tc_ids: selectedTCs.value.map(tc => tc.id)
    }, {
        onSuccess: () => {
            visible.value = false;
            createForm.reset();
            selectedTCs.value = [];
        }
    });
};

const userRole = computed(() => page.props.auth?.user?.role?.name || '');
</script>

<template>
    <AuthenticatedLayout>
        <div class="py-6 space-y-8">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 px-6">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">Reporting Téléconseillers</h1>
                    <p class="mt-1 text-sm text-slate-500 font-medium">{{ props.currentMonth }} • Synthèse détaillée des heures.</p>
                </div>
                
                <div class="flex items-center gap-4">
                    <!-- <Button @click="$inertia.history.back()"
                        class="bg-slate-100 border-none text-slate-700 px-4 py-2.5 rounded-xl font-black text-[10px] uppercase tracking-widest shadow-sm flex items-center gap-2">
                        <i class="pi pi-arrow-left"></i> Retour
                    </Button> -->
                    <Link :href="route('timesheet.telecon')"
                    icon="pi pi-file-edit"
                        class="bg-green-600 border-none text-white px-6 py-3 rounded-xl font-black text-xs uppercase tracking-widest shadow-lg shadow-blue-600/20">
                        
                        Feuille d'heures
                    </Link>
                    <!-- <Button
                        label="Feuille d'heures"
                        icon="pi pi-file-edit"
                        @click="router.visit(route('timesheet.telecon'))"
                        class="bg-slate-100 border-none text-slate-700 px-6 py-3 rounded-xl font-black text-xs uppercase tracking-widest shadow-sm hover:bg-slate-200 transition-all"
                    /> -->
                    <Button
                        label="Saisir des heures"
                        icon="pi pi-plus"
                        @click="openCreateDialog"
                        class="bg-blue-600 border-none text-white px-6 py-3 rounded-xl font-black text-xs uppercase tracking-widest shadow-lg shadow-blue-600/20"
                    />
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 px-6">
                <div class="bg-white p-6 rounded-[32px] border border-slate-100 shadow-sm flex items-center gap-4">
                    <div class="h-12 w-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shadow-sm">
                        <i class="pi pi-users"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Téléconseillers</p>
                        <p class="text-2xl font-black text-slate-900">{{ props.telecon?.length || 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- Tableau Principal -->
            <div class="bg-white rounded-[40px] border border-slate-100 p-8 shadow-sm mx-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-6">
                    <div class="flex items-center gap-3">
                        <div class="h-8 w-8 rounded-xl bg-slate-900 text-white flex items-center justify-center text-sm shadow-sm">
                            <i class="pi pi-table"></i>
                        </div>
                        <h2 class="text-xl font-black text-slate-900">Détails des collaborateurs</h2>
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
                                v-model="selectedPeriod"
                                :options="periodOptions"
                                optionLabel="label"
                                placeholder="Filtrer par période"
                                class="w-full"
                            />
                        </FloatLabel>
                    </div>
                </div>

                <DataTable
                    :value="props.telecon"
                    v-model:expandedRows="expandedRows"
                    v-model:filters="filters"
                    dataKey="id"
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
                                <div class="w-11 h-11 rounded-2xl bg-slate-900 flex items-center justify-center font-black text-white text-xs">
                                    {{ data.first_name?.[0] }}{{ data.last_name?.[0] }}
                                </div>
                                <div>
                                    <p class="font-black text-slate-900">{{ data.first_name }} {{ data.last_name }}</p>
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
                                <span class="text-[10px] text-slate-400 font-medium italic" v-if="selectedPeriod">
                                    Période: {{ selectedPeriod.label }}
                                </span>
                            </div>
                        </template>
                    </Column>

                    <Column header="Total Période" style="min-width: 12rem">
                        <template #body="{ data }">
                            <span class="font-black text-blue-700">
                                {{ formatH(getTotal(data)) }}
                            </span>
                        </template>
                    </Column>

                    <!-- Expansion -->
                    <template #expansion="{ data }">
                        <div class="p-8 bg-slate-50">
                            <DataTable :value="getEntries(data)" class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
                                <Column field="date" header="Date" style="min-width: 12rem">
                                    <template #body="sp">
                                        <div class="flex flex-col">
                                            <span class="capitalize">{{ formatD(sp.data.date) }}</span>
                                            <span class="text-[10px] text-slate-400 font-medium italic">Période: {{ sp.data.period_label }}</span>
                                        </div>
                                    </template>
                                </Column>
                                <Column header="Entrée / Sortie">
                                    <template #body="sp">{{ sp.data.check_in }} - {{ sp.data.check_out }}</template>
                                </Column>
                                <Column header="Durée">
                                    <template #body="sp">{{ formatH(sp.data.total_hours) }}</template>
                                </Column>
                                <Column header="Heures Sup.">
                                    <template #body="sp">
                                        <Tag v-if="sp.data.overtime_hours > 0" 
                                             :value="`+${formatH(sp.data.overtime_hours)}`" 
                                             severity="warn" 
                                             class="text-[10px]" />
                                        <span v-else class="text-slate-300">-</span>
                                    </template>
                                </Column>
                                <Column header="Action" style="width: 80px">
                                    <template #body="sp">
                                        <Button
                                            v-if="sp.data.timesheet_status !== 'validated'"
                                            icon="pi pi-pencil"
                                            text
                                            severity="secondary"
                                            @click="openEditDialog(sp.data)"
                                        />
                                        <Tag v-else value="Validé" severity="success" class="text-[8px] font-black uppercase" />
                                    </template>
                                </Column>
                            </DataTable>
                        </div>
                    </template>
                </DataTable>
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
                        <label class="text-xs font-bold uppercase text-slate-500">Date</label>
                        <DatePicker v-model="editForm.date" dateFormat="dd/mm/yy" disabled class="bg-slate-50" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="text-xs font-bold uppercase text-slate-500">Pause (min)</label>
                        <InputNumber v-model="editForm.break_duration" :min="0" :max="120" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="text-xs font-bold uppercase text-slate-500">Arrivée</label>
                        <DatePicker v-model="editForm.check_in" timeOnly showIcon />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="text-xs font-bold uppercase text-slate-500">Départ</label>
                        <DatePicker v-model="editForm.check_out" timeOnly showIcon />
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-xs font-bold uppercase text-slate-500">Commentaire</label>
                    <InputText v-model="editForm.comment" placeholder="Optionnel..." />
                </div>
            </div>

            <template #footer>
                <div class="flex gap-3 justify-end mt-4">
                    <Button label="Annuler" text severity="secondary" @click="editDialogVisible = false" />
                    <Button label="Enregistrer" severity="primary" @click="submitEdit" :loading="editForm.processing" />
                </div>
            </template>
        </Dialog>

        <!-- Dialog Nouvelle Saisie Groupée -->
        <Dialog
            v-model:visible="visible"
            modal
            header="Nouvelle saisie groupée"
            :style="{ width: '32rem' }"
            class="p-fluid"
        >
            <div class="space-y-6 py-4">
                <!-- Période -->
                <div class="flex flex-col gap-2">
                    <label class="text-xs font-bold uppercase text-slate-500">Période</label>
                    <Select
                        v-model="selectedPeriodForCreation"
                        :options="props.allPeriods"
                        optionLabel="label"
                        placeholder="Sélectionner une période"
                        class="w-full"
                    />
                </div>

                <!-- Téléconseillers -->
                <div class="flex flex-col gap-2">
                    <label class="text-xs font-bold uppercase text-slate-500">
                        Téléconseillers ({{ filteredTelecon.length }} disponibles)
                    </label>
                    <MultiSelect
                        v-model="selectedTCs"
                        :options="filteredTelecon"
                        optionLabel="first_name"
                        placeholder="Choisir les téléconseillers"
                        :disabled="!selectedPeriodForCreation"
                        display="chip"
                        :filter="true"
                    >
                        <template #option="slotProps">
                            <div class="flex items-center gap-2">
                                <span>{{ slotProps.option.first_name }} {{ slotProps.option.last_name }}</span>
                            </div>
                        </template>
                    </MultiSelect>
                </div>

                <!-- Date -->
                <div class="flex flex-col gap-2">
                    <label class="text-xs font-bold uppercase text-slate-500">Date</label>
                    <DatePicker 
                        v-model="createForm.date" 
                        showIcon 
                        dateFormat="dd/mm/yy" 
                        :minDate="minDate"
                        :maxDate="maxDate"
                        :disabled="!selectedPeriodForCreation"
                    />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="text-xs font-bold uppercase text-slate-500">Arrivée</label>
                        <DatePicker v-model="createForm.check_in" timeOnly showIcon />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="text-xs font-bold uppercase text-slate-500">Départ</label>
                        <DatePicker v-model="createForm.check_out" timeOnly showIcon />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="text-xs font-bold uppercase text-slate-500">Pause (min)</label>
                        <InputNumber v-model="createForm.break_duration" :min="0" :max="120" />
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-xs font-bold uppercase text-slate-500">Commentaire</label>
                    <InputText v-model="createForm.comment" placeholder="Optionnel..." />
                </div>
            </div>

            <template #footer>
                <div class="flex gap-3 justify-end mt-4">
                    <Button label="Annuler" text severity="secondary" @click="visible = false" />
                    <Button 
                        label="Enregistrer les entrées" 
                        severity="primary" 
                        @click="submitCreate" 
                        :loading="createForm.processing"
                        :disabled="!selectedPeriodForCreation || selectedTCs.length === 0"
                    />
                </div>
            </template>
        </Dialog>
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
