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

import Select from "primevue/select";

import DatePicker from "primevue/datepicker";
import { router, useForm } from "@inertiajs/vue3";
import { computed } from "vue";
import  DataView from "primevue/dataview";
import InputNumber from "primevue/inputnumber";

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
    employee_ids : [],
    date: "",
    check_in: "",
    break_duration: "",
    check_out: "",
    absence_type: "",
    comment: "",
});

const submit = () => {
    form.employee_ids = selectedTelecon.value.map(sup => sup.id);
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

// Initialisation des filtres adaptés aux Timesheets
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
    // 1. On récupère les IDs uniques des managers des TC sélectionnés
    // On utilise Map et filter pour éviter les doublons et les valeurs nulles
    const supervisorIds = selectedTelecon.value
        .map(tc => {
            // On cherche le manager_id dans le premier assignment actif
            return tc.assignments && tc.assignments.length > 0 
                ? tc.assignments[0].manager_id 
                : null;
        })
        .filter((id, index, self) => id !== null && self.indexOf(id) === index);

    if (supervisorIds.length === 0) {
        alert("Certains téléconseillers n'ont pas de superviseur assigné.");
        return;
    }

    // 2. On remplit le formulaire avec ces IDs de superviseurs
    form.employee_ids = supervisorIds; 
    
    // 3. On ouvre le modal ou on envoie
    visible.value = true; 
};
</script>

<template>
    <h2>Saisie d'heures (TeleConseiller)</h2>
    <div class="card p-4">
        <DataTable
            v-model:filters="filters"
            :value="props.telecon"
            paginator
            :rows="10"
            dataKey="id"
            v-model:selection="selectedTelecon"
            filterDisplay="menu"
            :globalFilterFields="[
                'status',
                'employee.firstName',
                'employee.lastName',
            ]"
            class="p-datatable-sm"
        >
            <template #header>
                <div>Selectionnez un collaborateur</div>
                <div class="flex justify-between items-center gap-2">
                    <Button
                        label="New entry"
                        icon="pi pi-plus"
                        variant="outlined"
                        @click="createEntry()"
                        :disabled="
                            !selectedTelecon || !selectedTelecon.length
                        "
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

            <Column selectionMode="multiple" headerStyle="width: 3rem"></Column>

            <!-- Colonne Employe auquel la feuille est assigné -->
            <Column header="FirstName" sortable style="min-width: 12rem">
                <template #body="{ data }">
                    <span>{{ data.first_name }} </span>
                </template>
            </Column>
            <Column header="LastName" sortable style="min-width: 12rem">
                <template #body="{ data }">
                    <span> {{ data.last_name }}</span>
                </template>
            </Column>
            <Column header="Matricule" sortable style="min-width: 12rem">
                <template #body="{ data }">
                    <span> {{ data.matricule }}</span>
                </template>
            </Column>
        </DataTable>
    </div>

    <!-- Le Dialog pour l'ajout -->
    <Dialog
        v-model:visible="visible"
        modal
        header="New Entry"
        :style="{ width: '30rem' }"
    >
        <div class="flex flex-col gap-4">
            <span class="text-surface-500 dark:text-surface-400 block mb-8"
                >Nouvelle saisie d'heure</span
            >
            <div class="flex-auto">
                <label for="date" class="font-bold block mb-2"> Date </label>
                <DatePicker
                    v-model="form.date"
                    showIcon
                    fluid
                    date="input"
                    inputId="date"
                    dateFormat="dd/mm/yy"
                />
            </div>
            <div class="flex-auto">
                <label for="check_in" class="font-bold block mb-2">
                    Arrivée
                </label>
                <DatePicker
                    timeOnly
                    v-model="form.check_in"
                    showIcon
                    fluid
                    check_in="input"
                    inputId="check_in"
                    dateFormat="dd/mm/yy"
                >
                    <template #inputicon="slotProps">
                        <i
                            class="pi pi-clock"
                            @click="slotProps.clickCallback"
                        />
                    </template>
                </DatePicker>
            </div>
            <div class="flex-auto">
                <label for="check_out" class="font-bold block mb-2">
                    Départ</label
                >
                <DatePicker
                    timeOnly
                    v-model="form.check_out"
                    showIcon
                    fluid
                    check_out="input"
                    inputId="check_out"
                    dateFormat="dd/mm/yy"
                >
                    <template #inputicon="slotProps">
                        <i
                            class="pi pi-clock"
                            @click="slotProps.clickCallback"
                        />
                    </template>
                </DatePicker>
            </div>
            <div class="flex-auto">
                <label for="email" class="font-semibold w-24"
                    >Durée de pause (en minute)</label
                >
                <InputNumber v-model="form.break_duration" inputId="minmax-buttons" mode="decimal" showButtons :min="0" :max="24" fluid />
            </div>
            <div
                v-if="!form.check_in"
                class="flex flex-col items-center gap-4 mb-8"
            >
                <h3>En cas d'absence</h3>
                <div class="flex items-center gap-4 mb-4">
                    <label for="username" class="font-semibold w-24"
                        >Raison de l'absence</label
                    >
                    <Select
                        v-model="form.absence_type"
                        :options="typeAbs"
                        optionLabel="name"
                        placeholder="Type d'absence"
                        class="w-full md:w-56"
                    />
                </div>
                <div class="flex items-center gap-4 mb-2">
                    <label for="email" class="font-semibold w-24"
                        >Plus de détails</label
                    >
                    <InputText
                        id="type"
                        v-model="form.comment"
                        class="flex-auto"
                        autocomplete="off"
                    />
                </div>
            </div>
        </div>
        <template #footer>
            <Button
                label="Fermer"
                icon="pi pi-times"
                text
                @click="visible = false"
            />
            <Button label="Enregistrer" icon="pi pi-check" @click="submit" />
        </template>
    </Dialog>
</template>
