<script setup>
import { ref, watch, computed, onMounted } from "vue";
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
    telecon: Array,
    allPeriods: Array,
    selectedPeriod: Object,
    auth: Object,
    currentMonth: String,
});

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

// Récupère toutes les entrées de l'employé
const getEntries = (employee) => {
    if (!employee.timesheet || employee.timesheet.length === 0) return [];
    
    return employee.timesheet.flatMap((ts) => (ts.entries || []).map(entry => ({
        ...entry,
        timesheet_status: ts.status
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
                        <FloatLabel variant="on" class="min-w-64">
                            <Select
                                v-model="selectedPeriod"
                                :options="periodOptions"
                                optionLabel="label"
                                placeholder="Sélectionner une période"
                                class="w-full border-slate-200 shadow-none focus:ring-0 text-sm font-semibold text-slate-700 rounded-xl"
                            />
                        </FloatLabel>

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
                class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden p-6 space-y-6"
            >
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="flex items-center gap-3">
                        <i class="pi pi-table text-blue-600"></i>
                        <h2 class="text-xl font-semibold text-slate-800">Détails des Téléconseillers</h2>
                    </div>

                    <IconField iconPosition="left" class="w-full md:max-w-md">
                        <InputIcon class="pi pi-search text-slate-400" />
                        <InputText
                            v-model="filters['global'].value"
                            placeholder="Rechercher un téléconseiller..."
                            class="w-full border-slate-200 bg-slate-50 rounded-xl text-sm"
                        />
                    </IconField>
                </div>

                <DataTable
                    :value="props.telecon"
                    v-model:expandedRows="expandedRows"
                    v-model:filters="filters"
                    dataKey="id"
                    class="p-datatable-sm"
                    stripedRows
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
                                <Column header="Action" style="width: 80px">
                                    <template #body="sp">
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
