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
        <template #header> Registre des feuilles d'heures </template>

        <div class="space-y-6">
            <!-- Carte principale du Tableau -->
            <div
                class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden"
            >
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
                >
                    <!-- Toolbar de la table -->
                    <template #header>
                        <div
                            class="flex flex-wrap justify-between items-center gap-4 p-2"
                        >
                            <div class="flex items-center gap-3">
                                <Button
                                    label="Nouvelle feuille"
                                    icon="pi pi-plus"
                                    severity="primary"
                                    @click="visible = true"
                                    class="shadow-sm"
                                />
                                <Button
                                    type="button"
                                    icon="pi pi-filter-slash"
                                    label="Effacer"
                                    outlined
                                    severity="secondary"
                                    @click="clearFilter()"
                                />
                            </div>

                            <IconField iconPosition="left">
                                <InputIcon class="pi pi-search" />
                                <InputText
                                    v-model="filters['global'].value"
                                    placeholder="Rechercher un collaborateur..."
                                    class="w-full md:w-80"
                                />
                            </IconField>
                        </div>
                    </template>

                    <template #empty>
                        <div class="py-8 text-center text-slate-500">
                            <i
                                class="pi pi-folder-open text-4xl mb-3 block"
                            ></i>
                            Aucune feuille de temps trouvée.
                        </div>
                    </template>

                    <!-- Colonne Collaborateur -->
                    <Column
                        header="Collaborateur"
                        sortable
                        field="employee.last_name"
                        style="min-width: 16rem"
                    >
                        <template #body="{ data }">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-9 h-9 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-xs border border-slate-200"
                                >
                                    {{ data.employee.first_name[0]
                                    }}{{ data.employee.last_name[0] }}
                                </div>
                                <div class="flex flex-col">
                                    <span
                                        class="font-semibold text-slate-700 leading-tight"
                                    >
                                        {{ data.employee.first_name }}
                                        {{ data.employee.last_name }}
                                    </span>
                                    <small class="text-slate-500"
                                        >Superviseur</small
                                    >
                                </div>
                            </div>
                        </template>
                    </Column>

                    <!-- Période -->
                    <Column header="Période" style="min-width: 14rem">
                        <template #body="{ data }">
                            <div class="flex items-center text-slate-600">
                                <span class="font-medium">{{
                                    formatDate(data.period_start)
                                }}</span>
                                <i
                                    class="pi pi-arrow-right mx-2 text-[10px] text-slate-400"
                                ></i>
                                <span class="font-medium">{{
                                    formatDate(data.period_end)
                                }}</span>
                            </div>
                        </template>
                    </Column>

                    <!-- Statut -->
                    <Column
                        field="status"
                        header="Statut"
                        sortable
                        style="min-width: 10rem"
                    >
                        <template #body="{ data }">
                            <Tag
                                :value="data.status"
                                :severity="getStatusSeverity(data.status)"
                                class="uppercase text-[10px] px-3"
                            />
                        </template>
                    </Column>

                    <!-- Validé par -->
                    <Column header="Validation" style="min-width: 14rem">
                        <template #body="{ data }">
                            <div
                                v-if="data.validated_by"
                                class="flex items-center gap-2 text-green-700"
                            >
                                <i class="pi pi-check-circle"></i>
                                <div class="flex flex-col leading-tight">
                                    <span class="text-sm font-medium"
                                        >{{ data.validator?.first_name }}
                                        {{ data.validator?.last_name }}</span
                                    >
                                    <small class="text-[10px] opacity-75"
                                        >Validé le
                                        {{
                                            formatDate(data.validated_at)
                                        }}</small
                                    >
                                </div>
                            </div>
                            <span v-else class="text-slate-400 italic text-sm"
                                >En attente...</span
                            >
                        </template>
                    </Column>

                    <!-- Actions -->
                    <Column
                        :exportable="false"
                        style="min-width: 8rem"
                        alignFrozen="right"
                        frozen
                    >
                        <template #body="{ data }">
                            <div class="flex justify-end gap-2">
                                <Button
                                    v-if="!data.validated_by"
                                    icon="pi pi-shield"
                                    label="Approuver"
                                    size="small"
                                    severity="success"
                                    text
                                    @click="validation(data.id)"
                                />
                                <Button
                                    v-else
                                    icon="pi pi-lock"
                                    severity="secondary"
                                    text
                                    disabled
                                />
                                <Button
                                    icon="pi pi-ellipsis-v"
                                    severity="secondary"
                                    text
                                    rounded
                                />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>

        <!-- Dialog : Nouvelle Feuille -->
        <Dialog
            v-model:visible="visible"
            modal
            header="Créer une feuille d'heures"
            :style="{ width: '30rem' }"
            class="p-fluid"
        >
            <div class="space-y-5 pt-4">
                <div class="flex flex-col gap-2">
                    <label for="sup" class="font-semibold text-slate-700"
                        >Collaborateurs concernés</label
                    >
                    <MultiSelect
                        id="sup"
                        v-model="form.employee_id"
                        :options="formattedSup"
                        optionLabel="fullName"
                        optionValue="id"
                        placeholder="Choisir les superviseurs"
                        display="chip"
                        filter
                        class="w-full"
                    />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label for="start" class="font-semibold text-slate-700"
                            >Date de début</label
                        >
                        <DatePicker
                            v-model="form.period_start"
                            inputId="start"
                            showIcon
                            dateFormat="dd/mm/yy"
                            placeholder="Choisir"
                        />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="end" class="font-semibold text-slate-700"
                            >Date de fin</label
                        >
                        <DatePicker
                            v-model="form.period_end"
                            inputId="end"
                            showIcon
                            dateFormat="dd/mm/yy"
                            placeholder="Choisir"
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
                    label="Enregistrer"
                    icon="pi pi-check"
                    severity="primary"
                    @click="submit"
                    :loading="form.processing"
                />
            </template>
        </Dialog>
    </AuthenticatedLayout>
</template>

<style>
/* Optionnel : Ajustements pour un look plus "SaaS" */
.p-datatable .p-datatable-thead > tr > th {
    background-color: #f8fafc;
    color: #64748b;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.025em;
    padding: 1rem;
}

.p-tag {
    border-radius: 6px;
    font-weight: 600;
}
</style>
