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

function deletePlanning(model) {
    if (confirm("Supprimer ce planning ?")) {
        router.delete(`/planning/${model.id}`, {
            onError: () => alert("Suppression impossible."),
        });
    }
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
</script>

<template>
    <AuthenticatedLayout>
        <div class="plannings-page">

            <!-- Header -->
            <div class="header">
                <h1 class="title">Plannings</h1>
                <div v-if="canManage" class="header-actions">
                    <div class="search-box">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                        <input v-model="search" placeholder="Rechercher..." />
                    </div>
                    <button class="btn-ghost" @click="router.get(route('planning.validation'))">
                         <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                          Validation
                    </button>
                    <button class="btn-ghost" @click="router.get(route('planning.affectation'))">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M20 21a8 8 0 1 0-16 0"/></svg>
                        Affecter un planning
                    </button>
                    <button v-if="isAdmin || isCP" class="btn-primary" @click="openCreate">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                        Créer un modèle
                    </button>
                </div>
            </div>

            <template v-if="canManage">
                <!-- Filtres -->
                <div class="filters">
                    <button :class="['filter-tab', activeFilter === 'tous' && 'active']" @click="activeFilter = 'tous'">
                        Modèles <span class="badge">{{ countAll }}</span>
                    </button>
                    <button :class="['filter-tab', activeFilter === 'actifs' && 'active']" @click="activeFilter = 'actifs'">
                        Actifs <span class="badge">{{ countActifs }}</span>
                    </button>
                    <button :class="['filter-tab', activeFilter === 'inactifs' && 'active']" @click="activeFilter = 'inactifs'">
                        Inactifs <span class="badge">{{ countInactifs }}</span>
                    </button>
                </div>

                <!-- Résumé des assignations par statut -->
                <div class="assignment-summary mb-6">
                    <div class="summary-grid">
                        <div v-for="(list, status) in props.assignments" :key="status" class="summary-card">
                            <span class="status-dot" :class="status.replace(' ', '-')"></span>
                            <div class="summary-info">
                                <span class="summary-count">{{ list.length }}</span>
                                <span class="summary-label">{{ status }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tableau -->
                <div class="table-card">
                    <table>
                        <thead>
                            <tr>
                                <th>Nom du modèle</th>
                                <th>Heures par jour</th>
                                <th>Total/semaine</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="filteredModels.length === 0">
                                <td colspan="5" class="empty">Aucun planning trouvé.</td>
                            </tr>
                            <tr v-for="model in filteredModels" :key="model.id">
                                <td class="col-name">
                                    <span class="model-name">{{ model.name }}</span>
                                    <span class="model-desc">{{ model.description }}</span>
                                </td>
                                <td class="col-hours">
                                    <div class="day-pills">
                                        <span
                                            v-for="(day, i) in days"
                                            :key="day"
                                            :class="['day-pill', model[day + '_hours'] > 0 ? 'active' : 'zero']"
                                            :title="dayLabels[i]"
                                        >
                                            {{ model[day + '_hours'] }}
                                        </span>
                                    </div>
                                    <span class="day-total">{{ model.total_hours }}h</span>
                                </td>
                                <td class="col-total">{{ model.total_hours }}h</td>
                                <td>
                                    <span :class="['status-badge', model.status === 'actif' ? 'actif' : 'inactif']">
                                        {{ model.status === 'actif' ? 'Actif' : 'Inactif' }}
                                    </span>
                                </td>
                                <td class="col-actions">
                                    <button class="icon-btn" @click="openEdit(model)" title="Modifier">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </button>
                                    <button class="icon-btn danger" @click="deletePlanning(model)" title="Supprimer">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </template>

            <!-- Section "Mon Planning" -->
            <div v-if="props.myAssignments && props.myAssignments.length > 0" class="my-planning-section mt-12">
                <div class="section-header mb-4">
                    <h2 class="text-xl font-semibold flex items-center gap-2">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        Mon Planning
                    </h2>
                    <p class="text-sm text-gray-500">Assignations qui me sont personnellement attribuées.</p>
                </div>

                <div class="table-card">
                    <table>
                        <thead>
                            <tr>
                                <th>Modèle</th>
                                <th>Période</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="assignment in props.myAssignments" :key="assignment.id">
                                <td class="col-name">
                                    <span class="model-name">{{ assignment.planning_model?.name }}</span>
                                    <span class="model-desc">{{ assignment.planning_model?.total_hours }}h par semaine</span>
                                </td>
                                <td>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-medium">Du {{ new Date(assignment.start_date).toLocaleDateString('fr-FR') }}</span>
                                        <span v-if="assignment.end_date" class="text-xs text-gray-500">Au {{ new Date(assignment.end_date).toLocaleDateString('fr-FR') }}</span>
                                        <span v-else class="text-xs text-blue-500">Indéterminé</span>
                                    </div>
                                </td>
                                <td>
                                    <span :class="['status-badge', assignment.status.replace(' ', '-')]">
                                        {{ assignment.status }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Dialog PrimeVue création / édition -->
            <Dialog
                :visible="showDialog"
                @update:visible="closeDialog"
                :header="isEdit ? 'Modifier le planning' : 'Créer un modèle de planning'"
                :modal="true"
                :closable="true"
                :draggable="false"
                :style="{ width: '560px' }"
            >
                <form @submit.prevent="submit" class="dialog-form">

                    <div class="field">
                        <label>Nom <span class="required">*</span></label>
                        <InputText
                            v-model="form.name"
                            placeholder="ex: Planning 35h standard"
                            :invalid="!!form.errors.name"
                            class="w-full"
                        />
                        <span v-if="form.errors.name" class="error">{{ form.errors.name }}</span>
                    </div>

                    <div class="field">
                        <label>Description</label>
                        <Textarea
                            v-model="form.description"
                            placeholder="Description optionnelle..."
                            :autoResize="true"
                            rows="2"
                            class="w-full"
                        />
                    </div>

                    <div class="field">
                        <label>Heures par jour <span class="required">*</span></label>
                        <div class="days-grid">
                            <div
                                v-for="(day, i) in days"
                                :key="day"
                                :class="['day-field', form[day + '_hours'] > 0 && 'has-hours']"
                            >
                                <span class="day-label">{{ dayLabels[i] }}</span>
                                <InputNumber
                                    v-model="form[day + '_hours']"
                                    :min="0"
                                    :max="24"
                                    :step="0.5"
                                    :minFractionDigits="0"
                                    :maxFractionDigits="1"
                                    inputClass="day-input"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="total-bar">
                        <span class="total-label">Total semaine</span>
                        <span class="total-value">{{ totalHours }}h</span>
                    </div>

                </form>

                <template #footer>
                    <Button
                        label="Annuler"
                        severity="secondary"
                        variant="outlined"
                        @click="closeDialog"
                        :disabled="form.processing"
                    />
                    <Button
                        :label="isEdit ? 'Mettre à jour' : 'Créer'"
                        :icon="isEdit ? 'pi pi-check' : 'pi pi-plus'"
                        :loading="form.processing"
                        @click="submit"
                    />
                </template>
            </Dialog>

        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.plannings-page {
    font-family: 'DM Sans', 'Segoe UI', sans-serif;
    padding: 2rem 2.5rem;
    max-width: 1200px;
    margin: 0 auto;
    color: #111;
}
.header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;
}
.title { font-size: 1.5rem; font-weight: 600; margin: 0; }
.header-actions { display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; }
.search-box {
    display: flex; align-items: center; gap: 0.5rem;
    border: 1px solid #e5e7eb; border-radius: 8px;
    padding: 0.4rem 0.75rem; background: #fff; color: #6b7280;
}
.search-box input {
    border: none; outline: none; font-size: 0.875rem;
    color: #111; width: 180px; background: transparent;
}
.btn-ghost {
    display: flex; align-items: center; gap: 0.4rem;
    border: 1px solid #e5e7eb; background: #fff; border-radius: 8px;
    padding: 0.45rem 0.9rem; font-size: 0.875rem; cursor: pointer;
    color: #374151; transition: background 0.15s;
}
.btn-ghost:hover { background: #f9fafb; }
.btn-primary {
    display: flex; align-items: center; gap: 0.4rem;
    background: #059669; color: #fff; border: none; border-radius: 8px;
    padding: 0.45rem 1rem; font-size: 0.875rem; cursor: pointer;
    font-weight: 500; transition: background 0.15s;
}
.btn-primary:hover { background: #047857; }
.filters { display: flex; gap: 0.25rem; margin-bottom: 1.25rem; }
.filter-tab {
    display: flex; align-items: center; gap: 0.4rem;
    padding: 0.4rem 1rem; border-radius: 20px; border: none;
    background: transparent; font-size: 0.875rem; cursor: pointer;
    color: #6b7280; transition: all 0.15s;
}
.filter-tab:hover { background: #f3f4f6; }
.filter-tab.active { background: #059669; color: #fff; }
.filter-tab.active .badge { background: rgba(255,255,255,0.25); color: #fff; }
.badge {
    background: #f3f4f6; color: #374151; border-radius: 20px;
    padding: 0.1rem 0.5rem; font-size: 0.75rem; font-weight: 500;
}
.table-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; }
table { width: 100%; border-collapse: collapse; }
thead tr { border-bottom: 1px solid #e5e7eb; }
th {
    padding: 0.85rem 1.25rem; text-align: left;
    font-size: 0.8rem; font-weight: 500; color: #6b7280;
    text-transform: uppercase; letter-spacing: 0.04em;
}
td { padding: 1rem 1.25rem; font-size: 0.875rem; vertical-align: middle; }
tr:not(:last-child) td { border-bottom: 1px solid #f3f4f6; }
tr:hover td { background: #fafafa; }
.col-name { min-width: 200px; }
.model-name { display: block; font-weight: 500; color: #111; }
.model-desc { display: block; font-size: 0.8rem; color: #9ca3af; margin-top: 2px; }
.col-hours { min-width: 280px; }
.day-pills { display: inline-flex; gap: 4px; align-items: center; }
.day-pill {
    width: 28px; height: 28px; border-radius: 6px;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.8rem; font-weight: 500;
}
.day-pill.active { background: #d1fae5; color: #047857; }
.day-pill.zero   { background: #f3f4f6; color: #9ca3af; }
.day-total { margin-left: 8px; font-size: 0.85rem; color: #6b7280; }
.col-total { font-weight: 600; color: #111; }
.status-badge { padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 500; }
.status-badge.actif   { background: #d1fae5; color: #065f46; }
.status-badge.inactif { background: #f3f4f6; color: #6b7280; }
.col-actions { display: flex; gap: 0.5rem; align-items: center; }
.icon-btn {
    border: 1px solid #e5e7eb; background: #fff; border-radius: 7px;
    padding: 0.35rem; cursor: pointer; color: #6b7280;
    display: flex; align-items: center; transition: all 0.15s;
}
.icon-btn:hover { background: #f3f4f6; color: #111; }
.icon-btn.danger:hover { background: #fee2e2; color: #dc2626; border-color: #fca5a5; }
.empty { text-align: center; color: #9ca3af; padding: 3rem; }

/* Assignment Summary */
.assignment-summary { margin-bottom: 1.5rem; }
.summary-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 1rem; }
.summary-card {
    background: #fff; border: 1px solid #e5e7eb; border-radius: 10px;
    padding: 0.75rem 1rem; display: flex; align-items: center; gap: 0.75rem;
}
.status-dot { width: 10px; height: 10px; border-radius: 50%; background: #9ca3af; }
.status-dot.en-attente { background: #f59e0b; }
.status-dot.validé { background: #10b981; }
.status-dot.suspendu { background: #ef4444; }
.status-dot.terminé { background: #6b7280; }
.summary-info { display: flex; flex-direction: column; }
.summary-count { font-size: 1.125rem; font-weight: 700; color: #111; line-height: 1; }
.summary-label { font-size: 0.75rem; color: #6b7280; text-transform: capitalize; margin-top: 2px; }

/* Status Badges for My Planning */
.status-badge.en-attente { background: #fef3c7; color: #92400e; }
.status-badge.validé { background: #d1fae5; color: #065f46; }
.status-badge.suspendu { background: #fee2e2; color: #991b1b; }
.status-badge.terminé { background: #f3f4f6; color: #374151; }

/* Dialog form */
.dialog-form { display: flex; flex-direction: column; gap: 1.25rem; padding: 0.25rem 0; }
.field { display: flex; flex-direction: column; gap: 0.4rem; }
.field label { font-size: 0.875rem; font-weight: 500; color: #374151; }
.required { color: #ef4444; margin-left: 2px; }
.w-full { width: 100%; }
.days-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 0.5rem; }
.day-field {
    display: flex; flex-direction: column; align-items: center; gap: 0.35rem;
    padding: 0.5rem 0.25rem; border-radius: 8px;
    border: 1px solid #e5e7eb; background: #f9fafb; transition: all 0.15s;
}
.day-field.has-hours { border-color: #a7f3d0; background: #ecfdf5; }
.day-label { font-size: 0.75rem; font-weight: 600; color: #6b7280; text-transform: uppercase; }
.day-field.has-hours .day-label { color: #059669; }
:deep(.day-input) {
    width: 100% !important; text-align: center !important;
    padding: 0.3rem 0.2rem !important; font-size: 0.9rem !important;
    font-weight: 500 !important; border-radius: 6px !important;
}
.total-bar {
    display: flex; align-items: center; justify-content: space-between;
    background: #ecfdf5; border: 1px solid #a7f3d0;
    border-radius: 8px; padding: 0.75rem 1rem;
}
.total-label { font-size: 0.875rem; font-weight: 500; color: #059669; }
.total-value { font-size: 1.25rem; font-weight: 700; color: #059669; }
.error { font-size: 0.8rem; color: #dc2626; }
</style>
