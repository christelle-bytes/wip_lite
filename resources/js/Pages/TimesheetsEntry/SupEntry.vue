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
import Tag from "primevue/tag";
import Select from "primevue/select";
import DatePicker from "primevue/datepicker";
import InputNumber from "primevue/inputnumber";
import { router, useForm } from "@inertiajs/vue3";
import { computed } from "vue";
import AuthenticatedLayout from "../../Layouts/AuthenticatedLayout.vue";

// les employées sélectionné
const selectedSuperior = ref([]);

const props = defineProps({
    superior: Array,
    auth: Object,
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
    // Formattage local pour éviter les décalages UTC
    const formatLocalTime = (date) => {
        if (!(date instanceof Date)) return date;
        const h = String(date.getHours()).padStart(2, '0');
        const m = String(date.getMinutes()).padStart(2, '0');
        return `${h}:${m}`;
    };

    if (form.date) {
        const year = form.date.getFullYear();
        const month = String(form.date.getMonth() + 1).padStart(2, '0');
        const day = String(form.date.getDate()).padStart(2, '0');
        form.date = `${year}-${month}-${day}`; 
    }

    // Préparation des données pour l'envoi
    const payload = {
        ...form.data(),
        check_in: formatLocalTime(form.check_in),
        check_out: formatLocalTime(form.check_out),
        employee_ids: selectedSuperior.value.map((sup) => sup.id)
    };

    router.post(route("store.sup"), payload, {
        onSuccess: () => {
            visible.value = false;
            form.reset();
            selectedSuperior.value = [];
            // Redirection vers la page indexSup après succès
            router.visit('/timesheets/entry/sup');
        },
        onError: (errors) => {
            // Redirection rollback en cas d'erreur
            console.error('Erreurs de validation:', errors);
            setTimeout(() => {
                router.visit('/timesheets/entry/sup');
            }, 2000);
        },
    });
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
        month: "short",
        day: "numeric",
    });
};
console.log(props.superior)


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
    initFilters();
});

const clearFilter = () => {
    initFilters();
};

const createEntry = () => {
    visible.value = true;
};
</script>

<template>
    <AuthenticatedLayout>
        <div class="py-6 space-y-8">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">Saisie d'heures - Superviseurs</h1>
                    <p class="mt-1 text-sm text-slate-500 font-medium">Créez des entrées groupées pour vos collaborateurs.</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <Button @click="$inertia.history.back()"
                        class="bg-slate-100 border-slate-200 text-slate-700 px-4 py-3 rounded-xl font-bold text-xs transition-all flex items-center gap-2">
                        <i class="pi pi-arrow-left"></i> Retour
                    </Button>
                    <Button
                        label="Nouvelle saisie"
                        icon="pi pi-plus"
                        :disabled="!selectedSuperior || !selectedSuperior.length"
                        @click="createEntry"
                        class="bg-teal-600 border-none text-white px-6 py-3 rounded-xl font-black text-xs uppercase tracking-widest shadow-lg shadow-teal-600/20 transition-all flex items-center gap-2"
                    />
                    <Button
                        v-if="selectedSuperior.length"
                        label="Effacer"
                        icon="pi pi-trash"
                        severity="secondary"
                        text
                        @click="selectedSuperior = []"
                        class="px-4 py-2 rounded-xl font-bold text-xs uppercase text-slate-500 hover:bg-slate-100 transition-all"
                    />
                </div>
            </div>

            <!-- Content Card -->
            <div class="bg-white rounded-[40px] border border-slate-100 p-8 shadow-sm space-y-8 overflow-hidden">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <IconField iconPosition="left" class="flex-1 max-w-md relative group">
                        <InputIcon class="pi pi-search text-slate-400 group-focus-within:text-teal-500 transition-colors" />
                        <InputText v-if="filters" v-model="filters['global'].value" placeholder="Rechercher un superviseur..." 
                            class="w-full pl-12 pr-4 py-3 bg-slate-50 border-slate-100 rounded-xl focus:border-teal-500 focus:ring-teal-500 transition-all placeholder:text-slate-400 text-sm font-medium" />
                    </IconField>
                </div>

                <div class="overflow-x-auto rounded-3xl border border-slate-50">
                    <DataTable
                        v-model:filters="filters"
                        :value="props.superior"
                        paginator
                        :rows="10"
                        dataKey="id"
                        v-model:selection="selectedSuperior"
                        filterDisplay="menu"
                        :globalFilterFields="['first_name', 'last_name', 'matricule']"
                        class="p-datatable-modern border-none"
                        rowHover
                    >
                        <template #empty>
                            <div class="py-12 text-center text-slate-400 italic">
                                <i class="pi pi-users text-4xl mb-3 block opacity-20"></i>
                                Aucun superviseur trouvé
                            </div>
                        </template>

                        <Column selectionMode="multiple" headerStyle="width: 4rem" class="px-6 py-4" />

                        <Column header="Collaborateur" sortable class="px-6 py-4">
                            <template #body="{ data }">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-slate-900 flex items-center justify-center font-black text-white text-xs shadow-lg shadow-slate-900/10">
                                        {{ data.first_name?.[0] }}{{ data.last_name?.[0] }}
                                    </div>
                                    <div>
                                        <p class="font-black text-slate-800 text-sm leading-tight">{{ data.first_name }} {{ data.last_name }}</p>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Matricule: {{ data.matricule }}</p>
                                    </div>
                                </div>
                            </template>
                        </Column>

                        <Column header="Début de Période" sortable class="px-6 py-4 text-center">
                            <template #body="{ data }">
                                <span class="px-3 py-1.5 bg-slate-50 border border-slate-100 rounded-lg text-xs font-bold text-slate-600">
                                    {{ formatDate(data.timesheet?.[0]?.period_start) }}
                                </span>
                            </template>
                        </Column>

                        <Column header="Fin de Période" sortable class="px-6 py-4 text-center">
                            <template #body="{ data }">
                                <span class="px-3 py-1.5 bg-slate-50 border border-slate-100 rounded-lg text-xs font-bold text-slate-600">
                                    {{ formatDate(data.timesheet?.[0]?.period_end) }}
                                </span>
                            </template>
                        </Column>

                        <Column field="matricule" header="Matricule" sortable class="px-6 py-4" />
                    </DataTable>
                </div>
            </div>
        </div>

        <!-- Dialog Nouvelle Saisie -->
        <Dialog v-model:visible="visible" modal header="Saisie groupée" 
            class="rounded-3xl shadow-2xl border-none" :style="{ width: '450px' }"
            :pt="{ header: { class: 'bg-slate-50 p-6 rounded-t-3xl border-b border-slate-100' }, content: { class: 'p-8 bg-white' }, footer: { class: 'p-6 bg-slate-50 rounded-b-3xl border-t border-slate-100' } }">
            <div class="space-y-6">
                <div class="p-4 bg-teal-50 border border-teal-100 rounded-2xl flex items-center gap-3">
                    <div class="h-8 w-8 rounded-lg bg-teal-600 text-white flex items-center justify-center text-xs">
                        <i class="pi pi-users"></i>
                    </div>
                    <p class="text-xs font-black text-teal-800 uppercase tracking-widest">
                        {{ selectedSuperior.length }} collaborateur(s) sélectionné(s)
                    </p>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Date de l'entrée</label>
                    <DatePicker v-model="form.date" showIcon dateFormat="dd/mm/yy" placeholder="Sélectionner une date"
                        class="w-full rounded-xl border-slate-200 focus:border-teal-500" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Entrée</label>
                        <DatePicker v-model="form.check_in" timeOnly showIcon placeholder="HH:mm"
                            class="w-full rounded-xl border-slate-200 focus:border-teal-500" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Sortie</label>
                        <DatePicker v-model="form.check_out" timeOnly showIcon placeholder="HH:mm"
                            class="w-full rounded-xl border-slate-200 focus:border-teal-500" />
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Durée de pause (min)</label>
                    <InputNumber v-model="form.break_duration" :min="0" :max="180" placeholder="0"
                        inputClass="w-full p-3 rounded-xl border-slate-200 text-sm font-black" />
                </div>

                <!-- Section Absence -->
                <div v-if="!form.check_in || !form.check_out" class="p-6 bg-slate-50 rounded-[32px] border border-dashed border-slate-200 space-y-4">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="pi pi-info-circle text-slate-400"></i>
                        <h4 class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Motif d'absence</h4>
                    </div>
                    <div class="flex flex-col gap-2">
                        <Select v-model="form.absence_type" :options="typeAbs" optionLabel="name" placeholder="Choisir le motif"
                            class="w-full rounded-xl border-slate-200 bg-white" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <InputText v-model="form.comment" placeholder="Commentaire facultatif..."
                            class="w-full p-3 rounded-xl border-slate-200 bg-white text-sm" />
                    </div>
                </div>
            </div>

            <template #footer>
                <div class="flex gap-3 w-full">
                    <Button label="Annuler" class="flex-1 p-button-text p-button-secondary font-black text-xs uppercase" @click="visible = false" />
                    <Button label="Enregistrer" @click="submit" :loading="form.processing"
                        class="flex-1 bg-teal-600 border-none font-black text-xs uppercase p-3 rounded-xl shadow-lg shadow-teal-600/20" />
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
</style>
