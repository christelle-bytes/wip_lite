<script setup>
import { ref, computed, watch } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Dialog from "primevue/dialog";
import InputText from "primevue/inputtext";
import Textarea from "primevue/textarea";
import InputNumber from "primevue/inputnumber";
import Button from "primevue/button";

const props = defineProps({
    planningModels: Array,
    assignments: Object, // Groupé par statut
    myAssignments: Array, // Ajouté
    auth: Object,
});

const userRole = computed(() => props.auth?.user?.role?.name?.toUpperCase());
const isAdmin = computed(() => userRole.value === 'ADMIN');
const isCP    = computed(() => userRole.value === 'CP');
const isSUP   = computed(() => userRole.value === 'SUP');
const isTC    = computed(() => userRole.value === 'TC');
const canManage = computed(() => isAdmin.value || isCP.value);

// ─── Liste & filtres ───────────────────────────────────────────────────────────

const search = ref("");
const activeFilter = ref("tous");

const filteredModels = computed(() => {
    let models = props.planningModels ?? [];
    if (activeFilter.value === "actifs") {
        models = models.filter((m) => m.status === "actif");
    } else if (activeFilter.value === "inactifs") {
        models = models.filter((m) => m.status !== "actif");
    }
    if (search.value.trim()) {
        models = models.filter(
            (m) =>
                m.name.toLowerCase().includes(search.value.toLowerCase()) ||
                (m.description ?? "").toLowerCase().includes(search.value.toLowerCase())
        );
    }
    return models;
});

const countAll      = computed(() => props.planningModels?.length ?? 0);
const countActifs   = computed(() => props.planningModels?.filter((m) => m.status === "actif").length ?? 0);
const countInactifs = computed(() => props.planningModels?.filter((m) => m.status !== "actif").length ?? 0);

const days      = ["monday", "tuesday", "wednesday", "thursday", "friday", "saturday", "sunday"];
const dayLabels = ["L", "M", "M", "J", "V", "S", "D"];

// ─── Suppression ──────────────────────────────────────────────────────────────

const confirmDeleteVisible = ref(false);
const modelToDelete = ref(null);

function deletePlanning(model) {
    modelToDelete.value = model;
    confirmDeleteVisible.value = true;
}

function confirmDelete() {
    router.delete(`/planning/${modelToDelete.value.id}`, {
        onSuccess: () => {
            confirmDeleteVisible.value = false;
            modelToDelete.value = null;
        },
        onError: () => alert("Suppression impossible."),
    });
}

// ─── Dialog création / édition ────────────────────────────────────────────────

const showDialog       = ref(false);
const selectedPlanning = ref(null);

const isEdit = computed(() => !!selectedPlanning.value);

const form = useForm({
    name: "",
    description: "",
    monday_hours: 0,
    tuesday_hours: 0,
    wednesday_hours: 0,
    thursday_hours: 0,
    friday_hours: 0,
    saturday_hours: 0,
    sunday_hours: 0,
});

const totalHours = computed(() =>
    days.reduce((sum, day) => sum + (Number(form[day + "_hours"]) || 0), 0)
);

watch(selectedPlanning, (model) => {
    if (model) {
        form.name            = model.name            ?? "";
        form.description     = model.description     ?? "";
        form.monday_hours    = model.monday_hours    ?? 0;
        form.tuesday_hours   = model.tuesday_hours   ?? 0;
        form.wednesday_hours = model.wednesday_hours ?? 0;
        form.thursday_hours  = model.thursday_hours  ?? 0;
        form.friday_hours    = model.friday_hours    ?? 0;
        form.saturday_hours  = model.saturday_hours  ?? 0;
        form.sunday_hours    = model.sunday_hours    ?? 0;
    } else {
        form.reset();
    }
});

function openCreate() {
    selectedPlanning.value = null;
    showDialog.value = true;
}

function openEdit(model) {
    selectedPlanning.value = model;
    showDialog.value = true;
}

function closeDialog() {
    showDialog.value = false;
    selectedPlanning.value = null;
    form.reset();
    form.clearErrors();
}

function submit() {
    if (isEdit.value) {
        form.put(`/planning/${selectedPlanning.value.id}`, {
            onSuccess: () => closeDialog(),
        });
    } else {
        form.post("/planning", {
            onSuccess: () => closeDialog(),
        });
    }
}

function formatDate(date) {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('fr-FR');
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="py-6 space-y-8">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">Gestion des Plannings</h1>
                    <p class="mt-1 text-sm text-slate-500 font-medium">Configurez vos modèles de temps de travail et suivez les affectations.</p>
                </div>
                <div v-if="canManage" class="flex flex-wrap gap-3">
                    <Button @click="router.get(route('planning.validation'))" 
                        class="bg-white border border-slate-200 text-slate-700 px-5 py-2.5 rounded-xl font-bold text-xs hover:border-teal-500 hover:text-teal-600 transition-all shadow-sm">
                        <i class="pi pi-check-square mr-2"></i> Validation
                    </Button>
                    <Button @click="router.get(route('planning.affectation'))"
                        class="bg-slate-900 border-none text-white px-5 py-2.5 rounded-xl font-bold text-xs shadow-lg shadow-slate-900/10 transition-all">
                        <i class="pi pi-user-plus mr-2"></i> Affecter un planning
                    </Button>
                    <Button v-if="isAdmin || isCP" @click="openCreate"
                        class="bg-teal-600 border-none text-white px-5 py-2.5 rounded-xl font-bold text-xs shadow-lg shadow-teal-600/20 transition-all">
                        <i class="pi pi-plus mr-2"></i> Créer un modèle
                    </Button>
                </div>
            </div>

            <template v-if="canManage">
                <!-- Stats/Summary Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div v-for="(list, status) in props.assignments" :key="status" 
                        class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm flex items-center gap-4 group hover:border-teal-100 transition-all">
                        <div :class="['h-12 w-12 rounded-2xl flex items-center justify-center text-xl shadow-sm transition-transform group-hover:scale-110', 
                            status === 'en attente' ? 'bg-amber-50 text-amber-500' : 
                            status === 'validé' ? 'bg-emerald-50 text-emerald-500' : 
                            status === 'suspendu' ? 'bg-rose-50 text-rose-500' : 'bg-slate-50 text-slate-500']">
                            <i :class="['pi', 
                                status === 'en attente' ? 'pi-clock' : 
                                status === 'validé' ? 'pi-check-circle' : 
                                status === 'suspendu' ? 'pi-pause-circle' : 'pi-history']"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ status }}</p>
                            <p class="text-2xl font-black text-slate-900">{{ list.length }}</p>
                        </div>
                    </div>
                </div>

                <!-- Content Card -->
                <div class="bg-white rounded-3xl border border-slate-100 p-8 shadow-sm space-y-8">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                        <!-- Search -->
                        <div class="flex-1 max-w-md relative group">
                            <i class="pi pi-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-teal-500 transition-colors"></i>
                            <InputText v-model="search" placeholder="Rechercher un modèle..." 
                                class="w-full pl-12 pr-4 py-3 bg-slate-50 border-slate-100 rounded-xl focus:border-teal-500 focus:ring-teal-500 transition-all placeholder:text-slate-400 text-sm font-medium" />
                        </div>

                        <!-- Filter Tabs -->
                        <div class="flex items-center gap-2 p-1.5 bg-slate-50 rounded-2xl border border-slate-100">
                            <button v-for="tab in [{l: 'Tous', v: 'tous', c: countAll}, {l: 'Actifs', v: 'actifs', c: countActifs}, {l: 'Inactifs', v: 'inactifs', c: countInactifs}]"
                                :key="tab.v" @click="activeFilter = tab.v"
                                :class="[activeFilter === tab.v ? 'bg-white text-teal-600 shadow-sm border-teal-100' : 'text-slate-400 hover:text-slate-600 border-transparent']"
                                class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest border transition-all flex items-center gap-2">
                                {{ tab.l }}
                                <span :class="[activeFilter === tab.v ? 'bg-teal-600 text-white' : 'bg-slate-200 text-slate-500', 'px-1.5 py-0.5 rounded text-[8px] font-black']">{{ tab.c }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- DataTable -->
                    <div class="overflow-x-auto rounded-2xl border border-slate-50">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/50">
                                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Modèle</th>
                                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Répartition Hebdomadaire</th>
                                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">Total</th>
                                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">Statut</th>
                                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <tr v-for="model in filteredModels" :key="model.id" class="hover:bg-slate-50/30 transition-colors group">
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-black text-slate-800">{{ model.name }}</p>
                                        <p class="text-[11px] text-slate-400 font-medium truncate max-w-[200px]">{{ model.description || 'Sans description' }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex gap-1">
                                            <div v-for="(day, idx) in days" :key="day" 
                                                class="flex flex-col items-center gap-1 group/day">
                                                <span class="text-[8px] font-black text-slate-300 uppercase group-hover/day:text-teal-500 transition-colors">{{ dayLabels[idx] }}</span>
                                                <div :class="['h-6 w-6 rounded-md flex items-center justify-center text-[9px] font-black', model[day + '_hours'] > 0 ? 'bg-teal-50 text-teal-600 border border-teal-100' : 'bg-slate-50 text-slate-300 border border-slate-100 opacity-40']">
                                                    {{ model[day + '_hours'] }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-sm font-black text-slate-900 bg-slate-100 px-3 py-1 rounded-lg">
                                            {{ days.reduce((sum, d) => sum + (Number(model[d + '_hours']) || 0), 0) }}h
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span :class="['rounded-full px-3 py-1 text-[9px] font-black uppercase tracking-widest', model.status === 'actif' ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-slate-100 text-slate-500 border border-slate-200']">
                                            {{ model.status === 'actif' ? 'Actif' : 'Inactif' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <Button icon="pi pi-pencil" @click="openEdit(model)" class="p-button-text p-button-secondary p-button-sm rounded-lg hover:text-teal-600" />
                                            <Button icon="pi pi-trash" @click="deletePlanning(model)" class="p-button-text p-button-danger p-button-sm rounded-lg" />
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="filteredModels.length === 0">
                                    <td colspan="5" class="px-6 py-12 text-center text-slate-400 italic">Aucun modèle trouvé.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </template>
            <template v-else-if="isTC">
                 <!-- Vue TC : affiche ses propres affectations -->
                 <div class="header">
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">Mes Plannings</h1>
                </div>

                <div v-if="props.myAssignments?.length" class="grid gap-6 md:grid-cols-2">
                    <div v-for="assign in props.myAssignments" :key="assign.id" class="bg-white p-8 rounded-[32px] border border-slate-100 shadow-sm group hover:border-teal-100 transition-all">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <h3 class="font-black text-slate-900 text-xl">{{ assign.planning_model?.name }}</h3>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">
                                    Du {{ formatDate(assign.start_date) }} au {{ formatDate(assign.end_date) }}
                                </p>
                            </div>
                            <span :class="['rounded-full px-3 py-1 text-[9px] font-black uppercase tracking-widest', assign.status === 'validé' ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-amber-50 text-amber-600 border border-amber-100']">
                                {{ assign.status }}
                            </span>
                        </div>
                        <div class="flex gap-1.5">
                             <div v-for="(day, idx) in days" :key="day" 
                                class="flex flex-col items-center gap-1 group/day">
                                <span class="text-[8px] font-black text-slate-300 uppercase">{{ dayLabels[idx] }}</span>
                                <div :class="['h-7 w-7 rounded-lg flex items-center justify-center text-[10px] font-black', assign.planning_model[day + '_hours'] > 0 ? 'bg-teal-50 text-teal-600 border border-teal-100' : 'bg-slate-50 text-slate-300 border border-slate-100 opacity-40']">
                                    {{ assign.planning_model[day + '_hours'] }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-24 bg-white rounded-[40px] border border-dashed border-slate-200">
                    <i class="pi pi-calendar-times text-5xl text-slate-200 mb-4"></i>
                    <p class="text-slate-400 font-bold uppercase tracking-widest text-sm">Aucun planning ne vous est assigné</p>
                </div>
            </template>
        </div>

        <!-- Dialog PrimeVue création / édition -->
        <Dialog v-model:visible="showDialog" :header="isEdit ? 'Modifier le modèle' : 'Nouveau modèle'" modal 
            class="rounded-3xl shadow-2xl border-none" :style="{ width: '500px' }"
            :pt="{ header: { class: 'bg-slate-50 p-6 rounded-t-3xl border-b border-slate-100' }, content: { class: 'p-8 bg-white' }, footer: { class: 'p-6 bg-slate-50 rounded-b-3xl border-t border-slate-100' } }">
            <div class="space-y-6">
                <div class="flex flex-col gap-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Nom du modèle</label>
                    <InputText v-model="form.name" placeholder="ex: 40h Standard" class="w-full p-3 rounded-xl border-slate-200 focus:border-teal-500" />
                </div>
                <div class="flex flex-col gap-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Description (optionnel)</label>
                    <Textarea v-model="form.description" rows="2" class="w-full p-3 rounded-xl border-slate-200 focus:border-teal-500" />
                </div>

                <div class="space-y-4">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Heures par jour</label>
                    <div class="grid grid-cols-4 gap-3">
                        <div v-for="(day, idx) in days" :key="day" class="flex flex-col gap-1.5">
                            <label class="text-[9px] font-bold text-slate-500 text-center">{{ dayLabels[idx] }}</label>
                            <InputNumber v-model="form[day + '_hours']" :min="0" :max="24" 
                                inputClass="w-full p-2 text-center rounded-lg border-slate-200 text-sm font-black" />
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[9px] font-bold text-teal-600 text-center">TOTAL</label>
                            <div class="w-full p-2 text-center rounded-lg bg-teal-50 border border-teal-100 text-teal-700 text-sm font-black">
                                {{ totalHours }}h
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <template #footer>
                <div class="flex gap-3 w-full">
                    <Button label="Annuler" class="flex-1 p-button-text p-button-secondary font-black text-xs uppercase" @click="closeDialog" />
                    <Button :label="isEdit ? 'Enregistrer' : 'Créer'" @click="submit" :loading="form.processing"
                        class="flex-1 bg-teal-600 border-none font-black text-xs uppercase p-3 rounded-xl shadow-lg shadow-teal-600/20" />
                </div>
            </template>
        </Dialog>

        <!-- Delete Confirmation Dialog -->
        <Dialog v-model:visible="confirmDeleteVisible" modal header="Supprimer le modèle" 
            class="rounded-3xl shadow-2xl border-none" :style="{ width: '400px' }"
            :pt="{ header: { class: 'bg-slate-50 p-6 rounded-t-3xl border-b border-slate-100' }, content: { class: 'p-8 bg-white' }, footer: { class: 'p-6 bg-slate-50 rounded-b-3xl border-t border-slate-100' } }">
            <div v-if="modelToDelete" class="space-y-4">
                <div class="h-16 w-16 rounded-2xl bg-rose-50 text-rose-500 mx-auto flex items-center justify-center mb-4">
                    <i class="pi pi-trash text-2xl"></i>
                </div>
                <p class="text-sm text-slate-600 text-center leading-relaxed">
                    Êtes-vous sûr de vouloir supprimer le modèle 
                    <span class="font-black text-slate-900">{{ modelToDelete.name }}</span> ?
                </p>
                <p class="text-[10px] text-slate-400 text-center font-medium">
                    Cette action est irréversible et supprimera définitivement le modèle.
                </p>
            </div>
            <template #footer>
                <div class="flex gap-3 w-full">
                    <Button label="Annuler" class="flex-1 p-button-text p-button-secondary font-black text-xs uppercase" @click="confirmDeleteVisible = false" />
                    <Button label="Supprimer" @click="confirmDelete"
                        class="flex-1 bg-rose-600 border-none font-black text-xs uppercase p-3 rounded-xl shadow-lg shadow-rose-600/20" />
                </div>
            </template>
        </Dialog>
    </AuthenticatedLayout>
</template>

<style scoped>
:deep(.p-inputnumber-input) {
    width: 100% !important;
}
</style>
