<script setup>
import { ref, computed } from "vue";
import { router } from "@inertiajs/vue3";
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Button from "primevue/button";
import Tag from "primevue/tag";

const props = defineProps({
    assignments: Array,
    auth: Object,
});

const activeFilter = ref("en attente");

const filters = [
    { label: "En attente", value: "en attente" },
    { label: "Validés",    value: "validé" },
    { label: "Suspendus",  value: "suspendu" },
    { label: "Terminés",   value: "terminé" },
];

const filteredAssignments = computed(() =>
    (props.assignments ?? []).filter((a) => a.status === activeFilter.value)
);

function countByStatus(status) {
    return (props.assignments ?? []).filter((a) => a.status === status).length;
}

function initials(name) {
    return name.split(" ").map((n) => n[0]).join("").toUpperCase().slice(0, 2);
}

function avatarColor(name) {
    const colors = ["#2563eb", "#7c3aed", "#0891b2", "#059669", "#d97706", "#dc2626"];
    let hash = 0;
    for (let i = 0; i < name.length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash);
    return colors[Math.abs(hash) % colors.length];
}

function formatDate(date) {
    if (!date) return "—";
    return new Date(date).toLocaleDateString("fr-FR", {
        day: "2-digit", month: "2-digit", year: "numeric",
    });
}

function statusSeverity(status) {
    const map = {
        "en attente": "warn",
        "validé":     "success",
        "suspendu":   "danger",
        "terminé":    "secondary",
    };
    return map[status] ?? "secondary";
}

function statusLabel(status) {
    const map = {
        "en attente": "En attente",
        "validé":     "Validé",
        "suspendu":   "Suspendu",
        "terminé":    "Terminé",
    };
    return map[status] ?? status;
}

// Appel unique vers ta route changeStatus
function changeStatus(assignment, status) {
    router.patch(
        route('planning-assignments.changeStatus', assignment.id),
        { status },
        { onError: () => alert("Erreur lors du changement de statut.") }
    );
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="validation-page">

            <!-- Header -->
            <div class="page-header">
                <div class="back-link" @click="router.get('/planning')">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
                    Retour aux plannings
                </div>
            </div>

            <!-- Filtres onglets -->
            <div class="filter-tabs">
                <button
                    v-for="f in filters"
                    :key="f.value"
                    :class="['filter-tab', activeFilter === f.value && 'active']"
                    @click="activeFilter = f.value"
                >
                    {{ f.label }}
                    <span class="count">{{ countByStatus(f.value) }}</span>
                </button>
            </div>

            <!-- Tableau PrimeVue -->
            <div class="table-wrapper">
                <DataTable
                    :value="filteredAssignments"
                    :rows="10"
                    :paginator="filteredAssignments.length > 10"
                    stripedRows
                    emptyMessage="Aucun assignment trouvé."
                >
                    <Column selectionMode="multiple" style="width: 3rem" />

                    <!-- Employé -->
                    <Column header="Employé" style="min-width: 200px">
                        <template #body="{ data }">
                            <div class="employee-cell">
                                <div
                                    class="avatar"
                                    :style="{ background: avatarColor(data.employee?.full_name ?? 'U') }"
                                >
                                    {{ initials(data.employee?.full_name ?? 'U') }}
                                </div>
                                <span class="employee-name">{{ data.employee?.full_name ?? '—' }}</span>
                            </div>
                        </template>
                    </Column>

                    <!-- Rôle -->
                    <Column header="Rôle" style="min-width: 140px">
                        <template #body="{ data }">
                            <span class="role-badge">{{ data.employee?.role ?? '—' }}</span>
                        </template>
                    </Column>

                    <!-- Modèle -->
                    <Column header="Modèle" style="min-width: 200px">
                        <template #body="{ data }">
                            {{ data.planning_model?.name ?? '—' }}
                        </template>
                    </Column>

                    <!-- Période -->
                    <Column header="Période" style="min-width: 220px">
                        <template #body="{ data }">
                            {{ formatDate(data.start_date) }} – {{ formatDate(data.end_date) }}
                        </template>
                    </Column>

                    <!-- Statut -->
                    <Column header="Statut" style="min-width: 130px">
                        <template #body="{ data }">
                            <Tag
                                :severity="statusSeverity(data.status)"
                                :value="statusLabel(data.status)"
                            />
                        </template>
                    </Column>

                    <!-- Actions -->
                    <Column header="Actions" style="min-width: 130px">
                        <template #body="{ data }">
                            <div class="actions">
                                <!-- Valider (si en attente) -->
                                <Button
                                    v-if="data.status === 'en attente'"
                                    icon="pi pi-check"
                                    severity="success"
                                    variant="text"
                                    rounded
                                    title="Valider"
                                    @click="changeStatus(data, 'validé')"
                                />
                                <!-- Suspendre (si validé) -->
                                <Button
                                    v-if="data.status === 'validé'"
                                    icon="pi pi-pause"
                                    severity="warn"
                                    variant="text"
                                    rounded
                                    title="Suspendre"
                                    @click="changeStatus(data, 'suspendu')"
                                />
                                <!-- Remettre en attente (si suspendu) -->
                                <Button
                                    v-if="data.status === 'suspendu'"
                                    icon="pi pi-undo"
                                    severity="secondary"
                                    variant="text"
                                    rounded
                                    title="Remettre en attente"
                                    @click="changeStatus(data, 'en attente')"
                                />
                                <!-- Rejeter (si en attente ou validé) -->
                                <Button
                                    v-if="['en attente', 'validé'].includes(data.status)"
                                    icon="pi pi-times"
                                    severity="danger"
                                    variant="text"
                                    rounded
                                    title="Rejeter"
                                    @click="changeStatus(data, 'rejeté')"
                                />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </div>

        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.validation-page {
    font-family: 'DM Sans', 'Segoe UI', sans-serif;
    padding: 1.5rem 2.5rem;
    max-width: 1200px;
    margin: 0 auto;
    color: #111;
}
.page-header { margin-bottom: 1.25rem; }
.back-link {
    display: inline-flex; align-items: center; gap: 0.4rem;
    font-size: 0.875rem; color: #6b7280; cursor: pointer; transition: color 0.15s;
}
.back-link:hover { color: #111; }
.filter-tabs { display: flex; gap: 0.5rem; margin-bottom: 1.25rem; flex-wrap: wrap; }
.filter-tab {
    display: inline-flex; align-items: center; gap: 0.5rem;
    padding: 0.45rem 1rem; border-radius: 20px; border: 1px solid #e5e7eb;
    background: #fff; font-size: 0.875rem; cursor: pointer;
    color: #374151; transition: all 0.15s;
}
.filter-tab:hover { background: #f3f4f6; }
.filter-tab.active { border-color: #2563eb; background: #eff6ff; color: #2563eb; font-weight: 500; }
.count {
    background: #f3f4f6; color: #6b7280; border-radius: 20px;
    padding: 0.05rem 0.5rem; font-size: 0.75rem; font-weight: 500;
}
.filter-tab.active .count { background: #dbeafe; color: #2563eb; }
.table-wrapper { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; }
.employee-cell { display: flex; align-items: center; gap: 0.75rem; }
.avatar {
    width: 36px; height: 36px; border-radius: 50%; color: #fff;
    font-size: 0.8rem; font-weight: 600;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.employee-name { font-weight: 500; }
.role-badge {
    display: inline-block; padding: 0.2rem 0.65rem; border-radius: 20px;
    background: #d1fae5; color: #065f46; font-size: 0.78rem; font-weight: 500;
}
.actions { display: flex; align-items: center; gap: 0.25rem; }
</style>