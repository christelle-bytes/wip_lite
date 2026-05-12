<script setup>
import { ref, computed, watch } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import { useToast } from "primevue/usetoast";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Dialog from "primevue/dialog";
import InputText from "primevue/inputtext";
import Textarea from "primevue/textarea";
import InputNumber from "primevue/inputnumber";
import Button from "primevue/button";
import Paginator from "primevue/paginator";

const props = defineProps({
    planningModels: Object, // Maintenant un objet avec pagination
    assignments: Object, // Groupé par statut
    myAssignments: Array, // Ajouté
    auth: Object,
});

const toast = useToast();

const userRole = computed(() => props.auth?.user?.role?.name?.toUpperCase());
const isAdmin = computed(() => userRole.value === "ADMIN");
const isCP = computed(() => userRole.value === "CP");
const isSUP = computed(() => userRole.value === "SUP");
const isTC = computed(() => userRole.value === "TC");
const canManage = computed(() => isAdmin.value || isCP.value);

// ─── Liste & filtres ───────────────────────────────────────────────────────────

const search = ref("");
const activeFilter = ref("tous");

const filteredModels = computed(() => {
    let models = props.planningModels?.data ?? [];
    if (activeFilter.value === "actifs") {
        models = models.filter((m) => m.status === "actif");
    } else if (activeFilter.value === "inactifs") {
        models = models.filter((m) => m.status !== "actif");
    }
    if (search.value.trim()) {
        models = models.filter(
            (m) =>
                m.name.toLowerCase().includes(search.value.toLowerCase()) ||
                (m.description ?? "")
                    .toLowerCase()
                    .includes(search.value.toLowerCase()),
        );
    }
    return models;
});
console.log(props.planningModels);
const countAll = computed(() => props.planningModels?.total ?? 0);
const countActifs = computed(
    () =>
        props.planningModels?.data.filter((m) => m.status === "actif").length ??
        0,
);
const countInactifs = computed(
    () =>
        props.planningModels?.data.filter((m) => m.status !== "actif").length ??
        0,
);

const days = [
    "monday",
    "tuesday",
    "wednesday",
    "thursday",
    "friday",
    "saturday",
    "sunday",
];
const dayLabels = ["L", "M", "M", "J", "V", "S", "D"];

// ─── Suppression ──────────────────────────────────────────────────────────────

const confirmDeleteVisible = ref(false);
const modelToDelete = ref(null);

function deletePlanning(model) {
    router.delete(`/planning/${model.id}`, {
        onError: () =>
            toast.add({
                severity: "error",
                summary: "Alerte",
                detail: "Suppression impossible.",
                life: 4000,
            }),
    });
}

// ─── Dialog création / édition ────────────────────────────────────────────────

const showDialog = ref(false);
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
    days.reduce((sum, day) => sum + (Number(form[day + "_hours"]) || 0), 0),
);

watch(selectedPlanning, (model) => {
    if (model) {
        form.name = model.name ?? "";
        form.description = model.description ?? "";
        form.monday_hours = model.monday_hours ?? 0;
        form.tuesday_hours = model.tuesday_hours ?? 0;
        form.wednesday_hours = model.wednesday_hours ?? 0;
        form.thursday_hours = model.thursday_hours ?? 0;
        form.friday_hours = model.friday_hours ?? 0;
        form.saturday_hours = model.saturday_hours ?? 0;
        form.sunday_hours = model.sunday_hours ?? 0;
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
    if (!date) return "—";
    return new Date(date).toLocaleDateString("fr-FR");
}

// Gestion de la pagination
function onPageChange(event) {
    const page = event.page + 1; // PrimeVue utilise 0-based index
    const rows = event.rows;

    // Conserver les filtres et recherche existants
    const params = new URLSearchParams(window.location.search);
    params.set("page", page);
    params.set("per_page", rows);

    router.get(window.location.pathname, Object.fromEntries(params), {
        preserveState: true,
        preserveScroll: true,
    });
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
                        <svg
                            width="16"
                            height="16"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <circle cx="11" cy="11" r="8" />
                            <path d="m21 21-4.35-4.35" />
                        </svg>
                        <input v-model="search" placeholder="Rechercher..." />
                    </div>
                    <button
                        class="btn-ghost"
                        @click="router.get(route('planning.validation'))"
                    >
                        <svg
                            width="15"
                            height="15"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <rect x="3" y="4" width="18" height="18" rx="2" />
                            <path d="M16 2v4M8 2v4M3 10h18" />
                        </svg>
                        Validation
                    </button>
                    <button
                        class="btn-ghost"
                        @click="router.get(route('planning.affectation'))"
                    >
                        <svg
                            width="15"
                            height="15"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <circle cx="12" cy="8" r="4" />
                            <path d="M20 21a8 8 0 1 0-16 0" />
                        </svg>
                        Affecter un planning
                    </button>
                    <button
                        v-if="isAdmin || isCP"
                        class="btn-primary"
                        @click="openCreate"
                    >
                        <svg
                            width="15"
                            height="15"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path d="M12 5v14M5 12h14" />
                        </svg>
                        Créer un modèle
                    </button>
                </div>
            </div>

            <!-- Vue ADMIN / CP / SUP -->
            <template v-if="canManage">
                <!-- Filtres -->
                <div class="filters">
                    <button
                        :class="[
                            'filter-tab',
                            activeFilter === 'tous' && 'active',
                        ]"
                        @click="activeFilter = 'tous'"
                    >
                        Modèles <span class="badge">{{ countAll }}</span>
                    </button>
                    <button
                        :class="[
                            'filter-tab',
                            activeFilter === 'actifs' && 'active',
                        ]"
                        @click="activeFilter = 'actifs'"
                    >
                        Actifs <span class="badge">{{ countActifs }}</span>
                    </button>
                    <button
                        :class="[
                            'filter-tab',
                            activeFilter === 'inactifs' && 'active',
                        ]"
                        @click="activeFilter = 'inactifs'"
                    >
                        Inactifs <span class="badge">{{ countInactifs }}</span>
                    </button>
                </div>

                <!-- Résumé des assignations par statut -->
                <div class="assignment-summary mb-6">
                    <div class="summary-grid">
                        <div
                            v-for="(list, status) in props.assignments"
                            :key="status"
                            class="summary-card"
                        >
                            <span
                                class="status-dot"
                                :class="status.replace(' ', '-')"
                            ></span>
                            <div class="summary-info">
                                <span class="summary-count">{{
                                    list.length
                                }}</span>
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
                                <td colspan="5" class="empty">
                                    Aucun planning trouvé.
                                </td>
                            </tr>
                            <tr v-for="model in filteredModels" :key="model.id">
                                <td class="col-name">
                                    <span class="model-name">{{
                                        model.name
                                    }}</span>
                                    <span class="model-desc">{{
                                        model.description
                                    }}</span>
                                </td>
                                <td class="col-hours">
                                    <div class="day-pills">
                                        <span
                                            v-for="(day, i) in days"
                                            :key="day"
                                            :class="[
                                                'day-pill',
                                                model[day + '_hours'] > 0
                                                    ? 'active'
                                                    : 'zero',
                                            ]"
                                            :title="dayLabels[i]"
                                        >
                                            {{ model[day + "_hours"] }}
                                        </span>
                                    </div>
                                    <span class="day-total"
                                        >{{ model.total_hours }}h</span
                                    >
                                </td>
                                <td class="col-total">
                                    {{ model.total_hours }}h
                                </td>
                                <td>
                                    <span
                                        :class="[
                                            'status-badge',
                                            model.status === 'actif'
                                                ? 'actif'
                                                : 'inactif',
                                        ]"
                                    >
                                        {{
                                            model.status === "actif"
                                                ? "Actif"
                                                : "Inactif"
                                        }}
                                    </span>
                                </td>
                                <td class="col-actions">
                                    <button
                                        class="icon-btn"
                                        @click="openEdit(model)"
                                        title="Modifier"
                                    >
                                        <svg
                                            width="16"
                                            height="16"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <path
                                                d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"
                                            />
                                            <path
                                                d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"
                                            />
                                        </svg>
                                    </button>
                                    <button
                                        class="icon-btn danger"
                                        @click="deletePlanning(model)"
                                        title="Supprimer"
                                    >
                                        <svg
                                            width="16"
                                            height="16"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <polyline points="3 6 5 6 21 6" />
                                            <path d="M19 6l-1 14H6L5 6" />
                                            <path d="M10 11v6M14 11v6" />
                                            <path d="M9 6V4h6v2" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </template>

            <!-- Section "Mon Planning" -->
            <template v-if="!canManage && !isTC">
                <div
                    v-if="props.myAssignments && props.myAssignments.length > 0"
                    class="my-planning-section mt-12"
                >
                    <div class="section-header mb-4">
                        <h2
                            class="text-xl font-semibold flex items-center gap-2"
                        >
                            <svg
                                width="20"
                                height="20"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"
                                />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                            Mon Planning
                        </h2>
                        <p class="text-sm text-gray-500">
                            Assignations qui me sont personnellement attribuées.
                        </p>
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
                                <tr
                                    v-for="assignment in props.myAssignments"
                                    :key="assignment.id"
                                >
                                    <td class="col-name">
                                        <span class="model-name">{{
                                            assignment.planning_model?.name
                                        }}</span>
                                        <span class="model-desc"
                                            >{{
                                                assignment.planning_model
                                                    ?.total_hours
                                            }}h par semaine</span
                                        >
                                    </td>
                                    <td>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-medium"
                                                >Du
                                                {{
                                                    new Date(
                                                        assignment.start_date,
                                                    ).toLocaleDateString(
                                                        "fr-FR",
                                                    )
                                                }}</span
                                            >
                                            <span
                                                v-if="assignment.end_date"
                                                class="text-xs text-gray-500"
                                                >Au
                                                {{
                                                    new Date(
                                                        assignment.end_date,
                                                    ).toLocaleDateString(
                                                        "fr-FR",
                                                    )
                                                }}</span
                                            >
                                            <span
                                                v-else
                                                class="text-xs text-blue-500"
                                                >Indéterminé</span
                                            >
                                        </div>
                                    </td>
                                    <td>
                                        <span
                                            :class="[
                                                'status-badge',
                                                assignment.status.replace(
                                                    ' ',
                                                    '-',
                                                ),
                                            ]"
                                        >
                                            {{ assignment.status }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </template>
            <!-- <template> -->
            <!-- Filter Tabs -->
            <!-- <div
                class="flex items-center gap-2 p-1.5 bg-slate-50 rounded-2xl border border-slate-100"
            >
                <button
                    v-for="tab in [
                        { l: 'Tous', v: 'tous', c: countAll },
                        { l: 'Actifs', v: 'actifs', c: countActifs },
                        { l: 'Inactifs', v: 'inactifs', c: countInactifs },
                    ]"
                    :key="tab.v"
                    @click="activeFilter = tab.v"
                    :class="[
                        activeFilter === tab.v
                            ? 'bg-white text-teal-600 shadow-sm border-teal-100'
                            : 'text-slate-400 hover:text-slate-600 border-transparent',
                    ]"
                    class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest border transition-all flex items-center gap-2"
                >
                    {{ tab.l }}
                    <span
                        :class="[
                            activeFilter === tab.v
                                ? 'bg-teal-600 text-white'
                                : 'bg-slate-200 text-slate-500',
                            'px-1.5 py-0.5 rounded text-[8px] font-black',
                        ]"
                        >{{ tab.c }}</span
                    >
                </button>
            </div>

            <div class="overflow-x-auto rounded-2xl border border-slate-50">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50">
                            <th
                                class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400"
                            >
                                Modèle
                            </th>
                            <th
                                class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400"
                            >
                                Répartition Hebdomadaire
                            </th>
                            <th
                                class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center"
                            >
                                Total
                            </th>
                            <th
                                class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center"
                            >
                                Statut
                            </th>
                            <th
                                class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right"
                            >
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr
                            v-for="model in filteredModels"
                            :key="model.id"
                            class="hover:bg-slate-50/30 transition-colors group"
                        >
                            <td class="px-6 py-4">
                                <p class="text-sm font-black text-slate-800">
                                    {{ model.name }}
                                </p>
                                <p
                                    class="text-[11px] text-slate-400 font-medium truncate max-w-[200px]"
                                >
                                    {{
                                        model.description || "Sans description"
                                    }}
                                </p>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex gap-1">
                                    <div
                                        v-for="(day, idx) in days"
                                        :key="day"
                                        class="flex flex-col items-center gap-1 group/day"
                                    >
                                        <span
                                            class="text-[8px] font-black text-slate-300 uppercase group-hover/day:text-teal-500 transition-colors"
                                            >{{ dayLabels[idx] }}</span
                                        >
                                        <div
                                            :class="[
                                                'h-6 w-6 rounded-md flex items-center justify-center text-[9px] font-black',
                                                model[day + '_hours'] > 0
                                                    ? 'bg-teal-50 text-teal-600 border border-teal-100'
                                                    : 'bg-slate-50 text-slate-300 border border-slate-100 opacity-40',
                                            ]"
                                        >
                                            {{ model[day + "_hours"] }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span
                                    class="text-sm font-black text-slate-900 bg-slate-100 px-3 py-1 rounded-lg"
                                >
                                    {{
                                        days.reduce(
                                            (sum, d) =>
                                                sum +
                                                (Number(model[d + "_hours"]) ||
                                                    0),
                                            0,
                                        )
                                    }}h
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span
                                    :class="[
                                        'rounded-full px-3 py-1 text-[9px] font-black uppercase tracking-widest',
                                        model.status === 'actif'
                                            ? 'bg-emerald-50 text-emerald-600 border border-emerald-100'
                                            : 'bg-slate-100 text-slate-500 border border-slate-200',
                                    ]"
                                >
                                    {{
                                        model.status === "actif"
                                            ? "Actif"
                                            : "Inactif"
                                    }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div
                                    class="flex items-center justify-end gap-1"
                                >
                                    <Button
                                        icon="pi pi-pencil"
                                        @click="openEdit(model)"
                                        class="p-button-text p-button-secondary p-button-sm rounded-lg hover:text-teal-600"
                                    />
                                    <Button
                                        icon="pi pi-trash"
                                        @click="deletePlanning(model)"
                                        class="p-button-text p-button-danger p-button-sm rounded-lg"
                                    />
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredModels.length === 0">
                            <td
                                colspan="5"
                                class="px-6 py-12 text-center text-slate-400 italic"
                            >
                                Aucun modèle trouvé.
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <!-- Pagination -->
            <div
                v-if="props.planningModels && props.planningModels.total > 10"
                class="mt-6"
            >
                <Paginator
                    :rows="10"
                    :totalRecords="props.planningModels.total"
                    :first="(props.planningModels.current_page - 1) * 10"
                    @page="onPageChange"
                    template="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                    :rowsPerPageOptions="[5, 10, 20, 50]"
                />
            </div>

            
        </div>
        <!-- </template> -->

        <template v-if="isTC">
            <!-- Vue TC : affiche ses propres affectations -->
            <div class="header">
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">
                    Mes Plannings
                </h1>
            </div>

            <div
                v-if="props.myAssignments?.length"
                class="grid gap-6 md:grid-cols-2"
            >
                <div
                    v-for="assign in props.myAssignments"
                    :key="assign.id"
                    class="bg-white p-8 rounded-[32px] border border-slate-100 shadow-sm group hover:border-teal-100 transition-all"
                >
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h3 class="font-black text-slate-900 text-xl">
                                {{ assign.planning_model?.name }}
                            </h3>
                            <p
                                class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1"
                            >
                                Du {{ formatDate(assign.start_date) }} au
                                {{ formatDate(assign.end_date) }}
                            </p>
                        </div>
                        <span
                            :class="[
                                'rounded-full px-3 py-1 text-[9px] font-black uppercase tracking-widest',
                                assign.status === 'validé'
                                    ? 'bg-emerald-50 text-emerald-600 border border-emerald-100'
                                    : 'bg-amber-50 text-amber-600 border border-amber-100',
                            ]"
                        >
                            {{ assign.status }}
                        </span>
                    </div>
                    <div class="flex gap-1.5">
                        <div
                            v-for="(day, idx) in days"
                            :key="day"
                            class="flex flex-col items-center gap-1 group/day"
                        >
                            <span
                                class="text-[8px] font-black text-slate-300 uppercase"
                                >{{ dayLabels[idx] }}</span
                            >
                            <div
                                :class="[
                                    'h-7 w-7 rounded-lg flex items-center justify-center text-[10px] font-black',
                                    assign.planning_model[day + '_hours'] > 0
                                        ? 'bg-teal-50 text-teal-600 border border-teal-100'
                                        : 'bg-slate-50 text-slate-300 border border-slate-100 opacity-40',
                                ]"
                            >
                                {{ assign.planning_model[day + "_hours"] }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div
                v-else
                class="text-center py-24 bg-white rounded-[40px] border border-dashed border-slate-200"
            >
                <i
                    class="pi pi-calendar-times text-5xl text-slate-200 mb-4"
                ></i>
                <p
                    class="text-slate-400 font-bold uppercase tracking-widest text-sm"
                >
                    Aucun planning ne vous est assigné
                </p>
            </div>
        </template>

        <!-- Dialog PrimeVue création / édition -->
        <Dialog
            v-model:visible="showDialog"
            :header="isEdit ? 'Modifier le modèle' : 'Nouveau modèle'"
            modal
            class="rounded-3xl shadow-2xl border-none"
            :style="{ width: '500px' }"
            :pt="{
                header: {
                    class: 'bg-slate-50 p-6 rounded-t-3xl border-b border-slate-100',
                },
                content: { class: 'p-8 bg-white' },
                footer: {
                    class: 'p-6 bg-slate-50 rounded-b-3xl border-t border-slate-100',
                },
            }"
        >
            <div class="space-y-6">
                <div class="flex flex-col gap-2">
                    <label
                        class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1"
                        >Nom du modèle</label
                    >
                    <InputText
                        v-model="form.name"
                        placeholder="ex: 40h Standard"
                        class="w-full p-3 rounded-xl border-slate-200 focus:border-teal-500"
                    />
                </div>
                <div class="flex flex-col gap-2">
                    <label
                        class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1"
                        >Description (optionnel)</label
                    >
                    <Textarea
                        v-model="form.description"
                        rows="2"
                        class="w-full p-3 rounded-xl border-slate-200 focus:border-teal-500"
                    />
                </div>

                <div class="space-y-4">
                    <label
                        class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1"
                        >Heures par jour</label
                    >
                    <div class="grid grid-cols-4 gap-3">
                        <div
                            v-for="(day, idx) in days"
                            :key="day"
                            class="flex flex-col gap-1.5"
                        >
                            <label
                                class="text-[9px] font-bold text-slate-500 text-center"
                                >{{ dayLabels[idx] }}</label
                            >
                            <InputNumber
                                v-model="form[day + '_hours']"
                                :min="0"
                                :max="24"
                                inputClass="w-full p-2 text-center rounded-lg border-slate-200 text-sm font-black"
                            />
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label
                                class="text-[9px] font-bold text-teal-600 text-center"
                                >TOTAL</label
                            >
                            <div
                                class="w-full p-2 text-center rounded-lg bg-teal-50 border border-teal-100 text-teal-700 text-sm font-black"
                            >
                                {{ totalHours }}h
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <template #footer>
                <div class="flex gap-3 w-full">
                    <Button
                        label="Annuler"
                        class="flex-1 p-button-text p-button-secondary font-black text-xs uppercase"
                        @click="closeDialog"
                    />
                    <Button
                        :label="isEdit ? 'Enregistrer' : 'Créer'"
                        @click="submit"
                        :loading="form.processing"
                        class="flex-1 bg-teal-600 border-none font-black text-xs uppercase p-3 rounded-xl shadow-lg shadow-teal-600/20"
                    />
                </div>
            </template>
        </Dialog>

        <!-- Delete Confirmation Dialog -->
        <Dialog
            v-model:visible="confirmDeleteVisible"
            modal
            header="Supprimer le modèle"
            class="rounded-3xl shadow-2xl border-none"
            :style="{ width: '400px' }"
            :pt="{
                header: {
                    class: 'bg-slate-50 p-6 rounded-t-3xl border-b border-slate-100',
                },
                content: { class: 'p-8 bg-white' },
                footer: {
                    class: 'p-6 bg-slate-50 rounded-b-3xl border-t border-slate-100',
                },
            }"
        >
            <div v-if="modelToDelete" class="space-y-4">
                <div
                    class="h-16 w-16 rounded-2xl bg-rose-50 text-rose-500 mx-auto flex items-center justify-center mb-4"
                >
                    <i class="pi pi-trash text-2xl"></i>
                </div>
                <p class="text-sm text-slate-600 text-center leading-relaxed">
                    Êtes-vous sûr de vouloir supprimer le modèle
                    <span class="font-black text-slate-900">{{
                        modelToDelete.name
                    }}</span>
                    ?
                </p>
                <p class="text-[10px] text-slate-400 text-center font-medium">
                    Cette action est irréversible et supprimera définitivement
                    le modèle.
                </p>
            </div>
            <template #footer>
                <div class="flex gap-3 w-full">
                    <Button
                        label="Annuler"
                        class="flex-1 p-button-text p-button-secondary font-black text-xs uppercase"
                        @click="confirmDeleteVisible = false"
                    />
                    <Button
                        label="Supprimer"
                        @click="confirmDelete"
                        class="flex-1 bg-rose-600 border-none font-black text-xs uppercase p-3 rounded-xl shadow-lg shadow-rose-600/20"
                    />
                </div>
            </template>
        </Dialog>
    </AuthenticatedLayout>
</template>

<style scoped>
.plannings-page {
    font-family: "DM Sans", "Segoe UI", sans-serif;
    padding: 2rem 2.5rem;
    max-width: 1200px;
    margin: 0 auto;
    color: #111;
}
.header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}
.title {
    font-size: 1.5rem;
    font-weight: 600;
    margin: 0;
}
.header-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}
.search-box {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 0.4rem 0.75rem;
    background: #fff;
    color: #6b7280;
}
.search-box input {
    border: none;
    outline: none;
    font-size: 0.875rem;
    color: #111;
    width: 180px;
    background: transparent;
}
.btn-ghost {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    border: 1px solid #e5e7eb;
    background: #fff;
    border-radius: 8px;
    padding: 0.45rem 0.9rem;
    font-size: 0.875rem;
    cursor: pointer;
    color: #374151;
    transition: background 0.15s;
}
.btn-ghost:hover {
    background: #f9fafb;
}
.btn-primary {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    background: #059669;
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 0.45rem 1rem;
    font-size: 0.875rem;
    cursor: pointer;
    font-weight: 500;
    transition: background 0.15s;
}
.btn-primary:hover {
    background: #047857;
}
.filters {
    display: flex;
    gap: 0.25rem;
    margin-bottom: 1.25rem;
}
.filter-tab {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.4rem 1rem;
    border-radius: 20px;
    border: none;
    background: transparent;
    font-size: 0.875rem;
    cursor: pointer;
    color: #6b7280;
    transition: all 0.15s;
}
.filter-tab:hover {
    background: #f3f4f6;
}
.filter-tab.active {
    background: #059669;
    color: #fff;
}
.filter-tab.active .badge {
    background: rgba(255, 255, 255, 0.25);
    color: #fff;
}
.badge {
    background: #f3f4f6;
    color: #374151;
    border-radius: 20px;
    padding: 0.1rem 0.5rem;
    font-size: 0.75rem;
    font-weight: 500;
}
.table-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    overflow: hidden;
}
table {
    width: 100%;
    border-collapse: collapse;
}
thead tr {
    border-bottom: 1px solid #e5e7eb;
}
th {
    padding: 0.85rem 1.25rem;
    text-align: left;
    font-size: 0.8rem;
    font-weight: 500;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}
td {
    padding: 1rem 1.25rem;
    font-size: 0.875rem;
    vertical-align: middle;
}
tr:not(:last-child) td {
    border-bottom: 1px solid #f3f4f6;
}
tr:hover td {
    background: #fafafa;
}
.col-name {
    min-width: 200px;
}
.model-name {
    display: block;
    font-weight: 500;
    color: #111;
}
.model-desc {
    display: block;
    font-size: 0.8rem;
    color: #9ca3af;
    margin-top: 2px;
}
.col-hours {
    min-width: 280px;
}
.day-pills {
    display: inline-flex;
    gap: 4px;
    align-items: center;
}
.day-pill {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    font-weight: 500;
}
.day-pill.active {
    background: #d1fae5;
    color: #047857;
}
.day-pill.zero {
    background: #f3f4f6;
    color: #9ca3af;
}
.day-total {
    margin-left: 8px;
    font-size: 0.85rem;
    color: #6b7280;
}
.col-total {
    font-weight: 600;
    color: #111;
}
.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
}
.status-badge.actif {
    background: #d1fae5;
    color: #065f46;
}
.status-badge.inactif {
    background: #f3f4f6;
    color: #6b7280;
}
.col-actions {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}
.icon-btn {
    border: 1px solid #e5e7eb;
    background: #fff;
    border-radius: 7px;
    padding: 0.35rem;
    cursor: pointer;
    color: #6b7280;
    display: flex;
    align-items: center;
    transition: all 0.15s;
}
.icon-btn:hover {
    background: #f3f4f6;
    color: #111;
}
.icon-btn.danger:hover {
    background: #fee2e2;
    color: #dc2626;
    border-color: #fca5a5;
}
.empty {
    text-align: center;
    color: #9ca3af;
    padding: 3rem;
}

/* Assignment Summary */
.assignment-summary {
    margin-bottom: 1.5rem;
}
.summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 1rem;
}
.summary-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 0.75rem 1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.status-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #9ca3af;
}
.status-dot.en-attente {
    background: #f59e0b;
}
.status-dot.validé {
    background: #10b981;
}
.status-dot.suspendu {
    background: #ef4444;
}
.status-dot.terminé {
    background: #6b7280;
}
.summary-info {
    display: flex;
    flex-direction: column;
}
.summary-count {
    font-size: 1.125rem;
    font-weight: 700;
    color: #111;
    line-height: 1;
}
.summary-label {
    font-size: 0.75rem;
    color: #6b7280;
    text-transform: capitalize;
    margin-top: 2px;
}

/* Status Badges for My Planning */
.status-badge.en-attente {
    background: #fef3c7;
    color: #92400e;
}
.status-badge.validé {
    background: #d1fae5;
    color: #065f46;
}
.status-badge.suspendu {
    background: #fee2e2;
    color: #991b1b;
}
.status-badge.terminé {
    background: #f3f4f6;
    color: #374151;
}

/* Dialog form */
.dialog-form {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
    padding: 0.25rem 0;
}

@media (max-width: 1024px) {
    .plannings-page {
        padding: 1.5rem 1rem;
    }
    .header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    .header-actions {
        width: 100%;
        justify-content: flex-start;
        gap: 0.5rem;
    }
    .search-box {
        width: 100%;
    }
    .btn-ghost {
        min-width: 160px;
    }
    .filters {
        flex-direction: column;
        align-items: stretch;
    }
    .filter-tab {
        width: 100%;
        justify-content: space-between;
    }
    .table-card {
        overflow-x: auto;
    }
    table {
        min-width: 720px;
    }
}

@media (max-width: 640px) {
    .search-box {
        width: 100%;
    }
    .header-actions {
        flex-direction: column;
        align-items: stretch;
    }
    .day-pills {
        flex-wrap: wrap;
        gap: 0.35rem;
    }
    .col-hours .day-pill {
        width: 100%;
        max-width: 48px;
    }
    th,
    td {
        padding: 0.75rem 0.75rem;
    }
}
.field {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}
.field label {
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
}
.required {
    color: #ef4444;
    margin-left: 2px;
}
.w-full {
    width: 100%;
}
.days-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 0.5rem;
}
.day-field {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.35rem;
    padding: 0.5rem 0.25rem;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    background: #f9fafb;
    transition: all 0.15s;
}
.day-field.has-hours {
    border-color: #a7f3d0;
    background: #ecfdf5;
}
.day-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
}
.day-field.has-hours .day-label {
    color: #059669;
}
:deep(.day-input) {
    width: 100% !important;
    text-align: center !important;
    padding: 0.3rem 0.2rem !important;
    font-size: 0.9rem !important;
    font-weight: 500 !important;
    border-radius: 6px !important;
}
.total-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #ecfdf5;
    border: 1px solid #a7f3d0;
    border-radius: 8px;
    padding: 0.75rem 1rem;
}
.total-label {
    font-size: 0.875rem;
    font-weight: 500;
    color: #059669;
}
.total-value {
    font-size: 1.25rem;
    font-weight: 700;
    color: #059669;
}
.error {
    font-size: 0.8rem;
    color: #dc2626;
}
</style>
