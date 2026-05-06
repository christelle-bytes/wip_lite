<script setup>
import { ref, onMounted } from "vue";
import { FilterMatchMode, FilterOperator } from "@primevue/core/api";
import Button from "primevue/button";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import InputText from "primevue/inputtext";
import IconField from "primevue/iconfield";
import InputIcon from "primevue/inputicon"; // Le composant correct
import Dialog from "primevue/dialog";
import Tag from "primevue/tag"; // Pour un rendu pro du status

import MultiSelect from "primevue/multiselect";

import DatePicker from "primevue/datepicker";
import { useForm } from "@inertiajs/vue3";
import { computed } from "vue";

const formattedCP = computed(() => {
    return props.cp.map((item) => ({
        ...item,
        fullName: `${item.first_name} ${item.last_name}`,
    }));
});

const props = defineProps({
    timesheets: Array,
    cp: Array,
    planning: Array,
    auth: Object,
});

const visible = ref(false);
const filters = ref();

const selectedPlanning = ref(null);
const form = useForm({
    employee_id: "",
    period_start: "",
    period_end: "",
});

const submit = () => {
    form.post(route("timesheet.store"));
    visible.value = false;
};

// Fonction de formatage des dates
const formatDate = (date) => {
    if (!date) return "-";
    return new Date(date).toLocaleDateString("fr-FR", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
};

// Initialisation des filtres adaptés aux Timesheets
const initFilters = () => {
    filters.value = {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        status: {
            operator: FilterOperator.OR,
            constraints: [{ value: null, matchMode: FilterMatchMode.EQUALS }],
        },
        period_start: {
            operator: FilterOperator.AND,
            constraints: [{ value: null, matchMode: FilterMatchMode.DATE_IS }],
        },
    };
};
initFilters();
onMounted(() => {
    console.log(props.cp);
    console.log(props.planning);
    initFilters();
});

const clearFilter = () => {
    initFilters();
};

// Logique pour les couleurs de status
const getStatusSeverity = (status) => {
    switch (status) {
        case "validé":
            return "success";
        case "en attente":
            return "info";
        case "suspendu":
            return "danger";
        default:
            return null;
    }
};

const validation = (id) => {
    form.status = 'validated';
    form.patch(route('timesheet.update', id))
}


</script>

<template>
    <h2>Registre des feuilles d'heures (CP)</h2>
    <div class="card p-4">
        <DataTable
            v-model:filters="filters"
            :value="props.timesheets"
            paginator
            :rows="10"
            dataKey="id"
            filterDisplay="menu"
            :globalFilterFields="[
                'status',
                'employee.firstName',
                'employee.lastName',
            ]"
            class="p-datatable-sm"
        >
            <template #header>
                <div class="flex justify-between items-center gap-2">
                    <Button
                        type="button"
                        icon="pi pi-add-slash"
                        label="Ajouter"
                        outlined
                        @click="visible = true"
                    />
                    <IconField>
                        <InputIcon>
                            <i class="pi pi-search" />
                        </InputIcon>
                        <InputText
                            v-model="filters['global'].value"
                            placeholder="Recherche globale..."
                        />
                    </IconField>
                </div>
            </template>

            <template #empty> Aucune feuille de temps trouvée. </template>

            <!-- Colonne Employe auquel la feuille est assigné -->
            <Column header="Assigné à" sortable style="min-width: 12rem">
                <template #body="{ data }">
                    <span
                        >{{ data.employee.first_name }}
                        {{ data.employee.last_name }}</span
                    >
                </template>
            </Column>
            <!-- Colonne Début -->
            <Column
                field="period_start"
                header="Début"
                sortable
                style="min-width: 12rem"
            >
                <template #body="{ data }">
                    <span class="font-medium">{{
                        formatDate(data.period_start)
                    }}</span>
                </template>
            </Column>

            <!-- Colonne Fin -->
            <Column
                field="period_end"
                header="Fin"
                sortable
                style="min-width: 12rem"
            >
                <template #body="{ data }">
                    {{ formatDate(data.period_end) }}
                </template>
            </Column>

            <!-- Colonne Status avec Tag -->
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
                    />
                </template>
            </Column>

            <!-- Colonne Validé par (Relation) -->
            <Column header="Validé par" style="min-width: 12rem">
                <template #body="{ data }">
                    <div
                        v-if="data.validated_by"
                        class="flex items-center gap-2"
                    >
                        <i class="pi pi-check-circle text-green-500"></i>
                        <span
                            >{{ data.validator.first_name }}
                            {{ data.validator.last_name }}</span
                        >
                        <!-- <span>ID: {{ data.validated_by }}</span>  -->
                        <!-- Si tu as chargé la relation en PHP, utilise data.validator.lastName -->
                    </div>
                    <span v-else class="text-gray-400 italic">Non validé</span>
                </template>
            </Column>

            <!-- Actions -->
            <Column
                header="Validations"
                :exportable="false"
                style="min-width: 8rem"
            >
                <template #body="{ data }">
                    <Button
                        v-if="data.validated_by"
                        icon="pi pi-lock"
                        outlined
                        rounded
                        class="mr-2"
                    />
                    <Button
                        v-else
                        icon="pi pi-unlock"
                        outlined
                        rounded
                        class="mr-2"
                        @click="validation(data.id)"
                    />
                    <span
                        v-if="!data.validated_by && data.status != 'validated'"
                        class="text-gray-400 italic animate-pulse"
                        >Pending</span
                    >
                </template>
            </Column>
        </DataTable>
    </div>

    <!-- Le Dialog pour l'ajout -->
    <Dialog
        v-model:visible="visible"
        modal
        header="New Timesheet"
        :style="{ width: '30rem' }"
    >
        <div class="flex flex-col gap-4">
            <span class="text-surface-500 dark:text-surface-400 block mb-8"
                >Enregistrez une nouvelle feuille d'heure</span
            >

            <div class="flex items-center gap-4 mb-8">
                <label for="email" class="font-semibold w-24"
                    >Selectionnez un CP</label
                >
                <MultiSelect
                    v-model="selectedCities"
                    display="chip"
                    :options="formattedCP"
                    optionLabel="fullName"
                    optionValue="id"
                    filter
                    placeholder="Select a CP"
                    class="w-full md:w-80"
                >
                    <!-- Slot pour personnaliser l'affichage dans la liste déroulante -->
                    <template #option="slotProps">
                        <div class="flex items-center">
                            {{ slotProps.option.fullName }}
                        </div>
                    </template>
                </MultiSelect>
            </div>
            <div class="flex-auto">
                <label for="period_start" class="font-bold block mb-2">
                    Début
                </label>
                <DatePicker
                    v-model="form.period_start"
                    showIcon
                    fluid
                    period_start="input"
                    inputId="period_start"
                />
            </div>
            <div class="flex-auto">
                <label for="period_end" class="font-bold block mb-2">
                    Fin</label
                >
                <DatePicker
                    v-model="form.period_end"
                    showIcon
                    fluid
                    period_end="input"
                    inputId="period_end"
                />
            </div>
        </div>
        <template #footer>
            <Button
                label="Fermer"
                icon="pi pi-times"
                text
                @click="visible = false"
            />
            <Button
                label="Enregistrer"
                icon="pi pi-check"
                @click="submit"
            />
        </template>
    </Dialog>
</template>
