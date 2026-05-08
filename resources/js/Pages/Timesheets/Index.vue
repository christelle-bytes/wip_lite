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
    return props.sup.map((item) => ({
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
    console.log(props.timesheets);
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
        <template #header>
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold text-slate-800">
                    Registre des feuilles d'heures
                </h1>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Carte principale -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <DataTable
                    v-if="filters"
                    v-model:filters="filters"
                    :value="props.timesheets"
                    paginator
                    :rows="10"
                    :rowsPerPageOptions="[10, 20, 50]"
                    dataKey="id"
                    filterDisplay="menu"
                    :globalFilterFields="[
                        'status',
                        'employee.first_name',
                        'employee.last_name',
                    ]"
                    class="p-datatable-sm"
                    stripedRows
                    showGridlines
                >
                    <!-- Toolbar -->
                    <template #header>
                        <div class="flex flex-wrap justify-between items-center gap-4 p-5 border-b border-slate-100 bg-slate-50">
                            <div class="flex items-center gap-3">
                                <Button
                                    label="Nouvelle feuille"
                                    icon="pi pi-plus"
                                    severity="primary"
                                    @click="visible = true"
                                    class="shadow-sm hover:shadow-md transition-all"
                                />
                                <Button
                                    type="button"
                                    icon="pi pi-filter-slash"
                                    label="Effacer les filtres"
                                    outlined
                                    severity="secondary"
                                    @click="clearFilter()"
                                />
                            </div>

                            <IconField iconPosition="left" class="w-full max-w-md">
                                <InputIcon class="pi pi-search text-slate-400" />
                                <InputText
                                    v-model="filters['global'].value"
                                    placeholder="Rechercher un collaborateur..."
                                    class="w-full"
                                />
                            </IconField>
                        </div>
                    </template>

                    <template #empty>
                        <div class="py-16 text-center text-slate-500">
                            <i class="pi pi-folder-open text-5xl mb-4 text-slate-300 block"></i>
                            <p class="text-lg">Aucune feuille de temps trouvée</p>
                        </div>
                    </template>

                    <!-- Colonne Collaborateur -->
                    <Column
                        header="Collaborateur"
                        sortable
                        field="employee.last_name"
                        style="min-width: 18rem"
                    >
                        <template #body="{ data }">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-100 to-slate-100 text-slate-700 flex items-center justify-center font-semibold text-sm border border-slate-200 shadow-sm">
                                    {{ data.employee.first_name[0] }}{{ data.employee.last_name[0] }}
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-800">
                                        {{ data.employee.first_name }} {{ data.employee.last_name }}
                                    </p>
                                    <p class="text-xs text-slate-500">Superviseur</p>
                                </div>
                            </div>
                        </template>
                    </Column>

                    <!-- Période -->
                    <Column header="Période" style="min-width: 15rem">
                        <template #body="{ data }">
                            <div class="flex items-center text-slate-600 font-medium">
                                <span>{{ formatDate(data.period_start) }}</span>
                                <i class="pi pi-arrow-right mx-3 text-slate-300"></i>
                                <span>{{ formatDate(data.period_end) }}</span>
                            </div>
                        </template>
                    </Column>

                    <!-- Saisie -->
                    <Column header="Saisie" style="min-width: 12rem">
                        <template #body="{ data }">
                            <div class="flex flex-col gap-2">
                                <div class="flex items-center gap-2.5">
                                    <i
                                        v-if="data.stats?.is_complete"
                                        class="pi pi-check-circle text-emerald-500 text-lg"
                                    ></i>
                                    <i
                                        v-else
                                        class="pi pi-clock text-amber-500 text-lg"
                                    ></i>
                                    <span
                                        :class="data.stats?.is_complete ? 'text-emerald-700 font-semibold' : 'text-slate-700'"
                                    >
                                        {{ data.stats?.jours_saisis }} / {{ data.stats?.total_jours }} jours
                                    </span>
                                </div>

                                <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                    <div
                                        class="h-full rounded-full transition-all duration-300"
                                        :class="data.stats?.is_complete ? 'bg-emerald-500' : 'bg-amber-400'"
                                        :style="{ width: data.stats?.is_complete ? '100%' : '65%' }"
                                    ></div>
                                </div>
                            </div>
                        </template>
                    </Column>

                    <!-- Statut -->
                    <Column field="status" header="Statut" sortable style="min-width: 10rem">
                        <template #body="{ data }">
                            <Tag
                                :value="data.status"
                                :severity="getStatusSeverity(data.status)"
                                class="text-xs font-semibold px-3 py-1.5 rounded-lg"
                            />
                        </template>
                    </Column>

                    <!-- Validation -->
                    <Column header="Validation" style="min-width: 15rem">
                        <template #body="{ data }">
                            <div v-if="data.validated_by" class="flex items-center gap-3 text-emerald-700">
                                <i class="pi pi-check-circle text-xl"></i>
                                <div>
                                    <p class="font-medium text-sm">
                                        {{ data.validator?.first_name }} {{ data.validator?.last_name }}
                                    </p>
                                    <p class="text-xs text-emerald-600/75">
                                        {{ formatDate(data.validated_at) }}
                                    </p>
                                </div>
                            </div>
                            <span v-else class="text-slate-400 text-sm italic">
                                En attente de validation
                            </span>
                        </template>
                    </Column>

                    <!-- Actions -->
                    <Column :exportable="false" style="min-width: 9rem" alignFrozen="right" frozen>
                        <template #body="{ data }">
                            <div class="flex justify-end gap-2">
                                <Button
                                    v-if="!data.validated_by && data.status == 'submitted'"
                                    icon="pi pi-shield"
                                    label="Approuver"
                                    size="small"
                                    severity="success"
                                    class="shadow-sm hover:shadow"
                                    @click="validation(data.id)"
                                />
                                <Button
                                    v-else-if="!data.validated_by && data.status == 'draft'"
                                    icon="pi pi-clock"
                                    label="Brouillon"
                                    size="small"
                                    severity="secondary"
                                    text
                                    disabled
                                />
                                <Button
                                    v-else
                                    icon="pi pi-lock"
                                    severity="secondary"
                                    text
                                    disabled
                                />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>

        <!-- Dialog Nouvelle Feuille -->
        <Dialog
            v-model:visible="visible"
            modal
            header="Créer une nouvelle feuille d'heures"
            :style="{ width: '32rem' }"
            class="p-fluid rounded-2xl"
        >
            <div class="space-y-6 py-4">
                <div class="flex flex-col gap-2">
                    <label class="font-semibold text-slate-700">Collaborateurs concernés</label>
                    <MultiSelect
                        v-model="form.employee_id"
                        :options="formattedSup"
                        optionLabel="fullName"
                        optionValue="id"
                        placeholder="Sélectionner les superviseurs"
                        display="chip"
                        filter
                        class="w-full"
                    />
                </div>

                <div class="grid grid-cols-2 gap-5">
                    <div class="flex flex-col gap-2">
                        <label class="font-semibold text-slate-700">Date de début</label>
                        <DatePicker
                            v-model="form.period_start"
                            showIcon
                            dateFormat="dd/mm/yy"
                            placeholder="Choisir"
                            class="w-full"
                        />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-semibold text-slate-700">Date de fin</label>
                        <DatePicker
                            v-model="form.period_end"
                            showIcon
                            dateFormat="dd/mm/yy"
                            placeholder="Choisir"
                            class="w-full"
                        />
                    </div>
                </div>
            </div>

            <template #footer>
                <Button
                    label="Annuler"
                    icon="pi pi-times"
                    text
                    severity="secondary"
                    @click="visible = false"
                />
                <Button
                    label="Créer la feuille"
                    icon="pi pi-check"
                    severity="primary"
                    @click="submit"
                    :loading="form.processing"
                />
            </template>
        </Dialog>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Styles affinés */
.p-datatable .p-datatable-thead > tr > th {
    background-color: #f8fafc;
    color: #475569;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    padding: 1.1rem 1rem;
    border-bottom: 1px solid #e2e8f0;
}

.p-datatable .p-datatable-tbody > tr {
    transition: all 0.2s;
}

.p-datatable .p-datatable-tbody > tr:hover {
    background-color: #f8fafc;
}

.p-tag {
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
}

/* Amélioration du Dialog */
.p-dialog .p-dialog-header {
    border-bottom: 1px solid #e2e8f0;
    padding: 1.25rem 1.75rem;
}

.p-dialog .p-dialog-content {
    padding: 1.5rem 1.75rem;
}
</style>