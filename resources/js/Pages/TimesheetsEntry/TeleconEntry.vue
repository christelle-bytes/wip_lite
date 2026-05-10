<script setup>
import { ref, onMounted } from "vue";
import { FilterMatchMode, FilterOperator } from "@primevue/core/api";
import Button from "primevue/button";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import InputText from "primevue/inputtext";
import IconField from "primevue/iconfield";
import InputIcon from "primevue/inputicon";
import Dialog from "primevue/dialog";
import Select from "primevue/select";
import DatePicker from "primevue/datepicker";
import InputNumber from "primevue/inputnumber";
import { Link, router, useForm } from "@inertiajs/vue3";
import { computed } from "vue";
import AuthenticatedLayout from "../../Layouts/AuthenticatedLayout.vue";
import { useToast } from 'primevue/usetoast';

const toast = useToast();

// les employées sélectionné
const selectedTelecon = ref([]);

const props = defineProps({
    telecon: Array,
    auth: Object,
});

const formattedSup = computed(() => {
    return props.telecon.map((item) => ({
        ...item,
        fullName: `${item.first_name} ${item.last_name}`,
    }));
});

const visible = ref(false);
const filters = ref();

const form = useForm({
    employee_ids: [],
    date: "",
    check_in: "",
    break_duration: "",
    check_out: "",
    absence_type: "",
    comment: "",
});

const submit = () => {
    form.employee_ids = selectedTelecon.value.map((sup) => sup.id);
    console.log(selectedTelecon.value);
    form.post(route("store.telecon"));
    visible.value = false;
};

const typeAbs = ref([
    { name: "Formation" },
    { name: "Congé" },
    { name: "Inccident de travail" },
    { name: "Autre" },
]);

// Fonction de formatage des dates
const formatDate = (date) => {
    if (!date) return "-";
    return new Date(date).toLocaleDateString("fr-FR", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
};

// Initialisation des filtres
const initFilters = () => {
    filters.value = {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        first_name: {
            operator: FilterOperator.OR,
            constraints: [{ value: null, matchMode: FilterMatchMode.EQUALS }],
        },
        last_name: {
            operator: FilterOperator.AND,
            constraints: [{ value: null, matchMode: FilterMatchMode.DATE_IS }],
        },
    };
};
initFilters();

onMounted(() => {
    console.log(props.telecon);
    initFilters();
});

const clearFilter = () => {
    initFilters();
};

const createEntry = () => {
    const supervisorIds = selectedTelecon.value
        .map((tc) => {
            return tc.assignments && tc.assignments.length > 0
                ? tc.assignments[0].manager_id
                : null;
        })
        .filter((id, index, self) => id !== null && self.indexOf(id) === index);

    if (supervisorIds.length === 0) {
       return toast.add({
        severity: 'error',
        summary: 'Alerte',
        detail: 'Certains teleconseillers ne sont pas assigné à de superviseur',
        life: 5000,
    });
    }

    form.employee_ids = supervisorIds;
    visible.value = true;
};
</script>

<template>
    <AuthenticatedLayout>
        <div class="p-6 space-y-6">
            <!-- En-tête -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="text-3xl font-semibold text-slate-800">
                            Saisie d'heures - Téléconseillers
                        </h1>
                        <p class="text-slate-500 mt-1">
                            Sélectionnez un ou plusieurs téléconseillers pour créer une nouvelle saisie
                        </p>
                    </div>

                    <Link
                        :href="route('index.telecon')"
                        class="inline-flex items-center gap-2 px-5 py-2.5 text-slate-700 hover:bg-slate-100 border border-slate-200 rounded-xl font-medium transition-all"
                    >
                        <i class="pi pi-chart-bar"></i>
                        Récap des heures
                    </Link>
                </div>
            </div>

            <!-- Tableau Principal -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <DataTable
                    v-model:filters="filters"
                    :value="props.telecon"
                    paginator
                    :rows="10"
                    dataKey="id"
                    v-model:selection="selectedTelecon"
                    filterDisplay="menu"
                    :globalFilterFields="['first_name', 'last_name', 'matricule']"
                    class="p-datatable-sm"
                    stripedRows
                    rowHover
                >
                    <template #header>
                        <div class="p-5 border-b border-slate-100 bg-slate-50 flex flex-wrap justify-between items-center gap-4">
                            <div class="flex items-center gap-3">
                                <Button
                                    label="Nouvelle saisie"
                                    icon="pi pi-plus"
                                    severity="primary"
                                    :disabled="!selectedTelecon || !selectedTelecon.length"
                                    @click="createEntry"
                                    class="shadow-sm"
                                />
                                <Button
                                    v-if="selectedTelecon.length"
                                    label="Effacer sélection"
                                    icon="pi pi-trash"
                                    severity="secondary"
                                    outlined
                                    size="small"
                                    @click="selectedTelecon = []"
                                />
                            </div>

                            <IconField iconPosition="left" class="w-full max-w-md">
                                <InputIcon class="pi pi-search text-slate-400" />
                                <InputText
                                    v-model="filters['global'].value"
                                    placeholder="Rechercher par nom ou matricule..."
                                />
                            </IconField>
                        </div>
                    </template>

                    <template #empty>
                        <div class="py-12 text-center text-slate-500">
                            <i class="pi pi-users text-4xl mb-3 block"></i>
                            Aucun téléconseiller trouvé
                        </div>
                    </template>

                    <Column selectionMode="multiple" headerStyle="width: 3.5rem" />

                    <!-- Collaborateur -->
                    <Column header="Collaborateur" sortable style="min-width: 18rem">
                        <template #body="{ data }">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-100 to-indigo-100 flex items-center justify-center font-semibold text-slate-700 border border-slate-200">
                                    {{ data.first_name?.[0] }}{{ data.last_name?.[0] }}
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-800">
                                        {{ data.first_name }} {{ data.last_name }}
                                    </p>
                                    <p class="text-xs text-slate-500">{{ data.matricule }}</p>
                                </div>
                            </div>
                        </template>
                    </Column>

                    <Column field="matricule" header="Matricule" sortable style="min-width: 10rem" />
                </DataTable>
            </div>
        </div>

        <!-- Dialog Nouvelle Saisie -->
        <Dialog
            v-model:visible="visible"
            modal
            header="Nouvelle saisie d'heures"
            :style="{ width: '32rem' }"
            class="p-fluid"
        >
            <div class="space-y-6 py-2">
                <div class="text-slate-500 text-sm">
                    Création d'une entrée pour {{ selectedTelecon.length }} téléconseiller(s) sélectionné(s)
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="font-semibold text-slate-700">Date</label>
                        <DatePicker
                            v-model="form.date"
                            showIcon
                            dateFormat="dd/mm/yy"
                            placeholder="Sélectionner une date"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="font-semibold text-slate-700">Heure d'arrivée</label>
                        <DatePicker
                            v-model="form.check_in"
                            timeOnly
                            showIcon
                            placeholder="HH:mm"
                        />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-semibold text-slate-700">Heure de départ</label>
                        <DatePicker
                            v-model="form.check_out"
                            timeOnly
                            showIcon
                            placeholder="HH:mm"
                        />
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="font-semibold text-slate-700">Durée de pause (minutes)</label>
                    <InputNumber
                        v-model="form.break_duration"
                        mode="decimal"
                        showButtons
                        :min="0"
                        :max="180"
                        placeholder="0"
                    />
                </div>

                <!-- Section Absence -->
                <div v-if="!form.check_in || !form.check_out" class="border border-dashed border-slate-300 rounded-xl p-5 bg-slate-50">
                    <h4 class="font-medium text-slate-700 mb-4">Absence</h4>
                    <div class="space-y-4">
                        <div>
                            <label class="font-semibold text-slate-700 block mb-2">Type d'absence</label>
                            <Select
                                v-model="form.absence_type"
                                :options="typeAbs"
                                optionLabel="name"
                                placeholder="Choisir le motif"
                                class="w-full"
                            />
                        </div>
                        <div>
                            <label class="font-semibold text-slate-700 block mb-2">Commentaire</label>
                            <InputText
                                v-model="form.comment"
                                placeholder="Détails supplémentaires..."
                                class="w-full"
                            />
                        </div>
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
                    label="Enregistrer la saisie"
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
:deep(.p-datatable-thead > tr > th) {
    background-color: #f8fafc;
    color: #475569;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    padding: 1.1rem 1rem;
}

:deep(.p-dialog-header) {
    border-bottom: 1px solid #e2e8f0;
}
</style>