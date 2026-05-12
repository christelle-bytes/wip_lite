<script setup>
import { computed, ref } from "vue";
import { router } from "@inertiajs/vue3";
import { useToast } from "primevue/usetoast";
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Button from "primevue/button";
import Paginator from "primevue/paginator";
import InputText from "primevue/inputtext";

const props = defineProps({
    assignments: Object, // Maintenant un objet avec pagination
});

const toast = useToast();
const activeFilter = ref("en attente");
const search = ref("");

const filters = [
    { label: "En attente", value: "en attente" },
    { label: "Validés",    value: "validé" },
    { label: "Suspendus",  value: "suspendu" },
    { label: "Terminés",   value: "terminé" },
];

const filteredAssignments = computed(() =>
    (props.assignments?.data ?? []).filter((a) => a.status === activeFilter.value)
);

function countByStatus(status) {
    return (props.assignments?.data ?? []).filter((a) => a.status === status).length;
}

function employeeFullName(employee) {
    if (!employee) return "—";
    return `${employee.first_name} ${employee.last_name}`;
}

function employeeInitials(employee) {
    if (!employee) return "U";
    return (employee.first_name[0] + employee.last_name[0]).toUpperCase();
}

function avatarColor(employee) {
    const name = employee ? `${employee.first_name} ${employee.last_name}` : 'U';
    const colors = ["#2563eb", "#7c3aed", "#0891b2", "#059669", "#d97706", "#dc2626"];
    let hash = 0;
    for (let i = 0; i < name.length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash);
    return colors[Math.abs(hash) % colors.length];
}

function formatDate(date) {
    if (!date) return "—";
    return new Date(date).toLocaleDateString("fr-FR");
}

function employeeName(employee) {
    if (!employee) return "—";
    return `${employee.first_name} ${employee.last_name}`;
}

function changeStatus(assignment, status) {
    router.patch(
        route('planning-assignments.changeStatus', assignment.id),
        { status },
        {
            onError: () => toast.add({ severity: 'error', summary: 'Alerte', detail: 'Erreur lors du changement de statut.', life: 4000 }),
        }
    );
}

// Gestion de la recherche
function performSearch() {
    const params = new URLSearchParams(window.location.search);
    if (search.value.trim()) {
        params.set('search', search.value.trim());
    } else {
        params.delete('search');
    }
    params.delete('page'); // Reset to first page on new search
    
    router.get(window.location.pathname, Object.fromEntries(params), {
        preserveState: true,
        preserveScroll: true,
    });
}

// Gestion de la pagination
function onPageChange(event) {
    const page = event.page + 1; // PrimeVue utilise 0-based index
    const rows = event.rows;
    
    // Conserver les filtres et recherche existants
    const params = new URLSearchParams(window.location.search);
    params.set('page', page);
    params.set('per_page', rows);
    
    router.get(window.location.pathname, Object.fromEntries(params), {
        preserveState: true,
        preserveScroll: true,
    });
}

// Gestion du filtre de statut
function filterByStatus(status) {
    const params = new URLSearchParams(window.location.search);
    if (status === 'all') {
        params.delete('status');
    } else {
        params.set('status', status);
    }
    params.delete('page'); // Reset to first page on new filter
    
    router.get(window.location.pathname, Object.fromEntries(params), {
        preserveState: true,
        preserveScroll: true,
    });
}

function deleteAssignment(assignment) {
    router.delete(route('planning-assignments.destroy', assignment.id), {
        onError: () => toast.add({ severity: 'error', summary: 'Alerte', detail: 'Impossible de supprimer cette assignation.', life: 4000 }),
    });
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="validation-page">

            <!-- Header -->
            <div class="header">
                <div class="header-content">
                    <div class="back-link" @click="router.get('/planning')">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
                        Retour aux plannings
                    </div>
                    <h1 class="title">Validation des assignations</h1>
                    <p class="subtitle">Gérez et validez les assignations de planning en attente.</p>
                </div>
                <!-- Recherche -->
                <div class="search-container">
                    <div class="search-box">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                        <InputText 
                            v-model="search" 
                            placeholder="Rechercher un employé ou un planning..." 
                            @keyup.enter="performSearch"
                            class="search-input"
                        />
                        <Button @click="performSearch" class="search-btn">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Filtres onglets -->
            <div class="filters">
                <button
                    v-for="f in filters"
                    :key="f.value"
                    :class="['filter-tab', activeFilter === f.value && 'active']"
                    @click="activeFilter = f.value"
                >
                    {{ f.label }}
                    <span class="badge">{{ countByStatus(f.value) }}</span>
                </button>
            </div>

            <!-- Tableau -->
            <div class="table-card">
                <table v-if="filteredAssignments.length > 0">
                    <thead>
                        <tr>
                            <th style="min-width: 180px">Employé</th>
                            <th style="min-width: 120px">Rôle</th>
                            <th style="min-width: 180px">Modèle</th>
                            <th style="min-width: 200px">Période</th>
                            <th style="min-width: 120px">Statut</th>
                            <th style="min-width: 120px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="assignment in filteredAssignments" :key="assignment.id">
                            <!-- Employé -->
                            <td class="col-employee">
                                <div class="employee-cell">
                                    <div
                                        class="avatar"
                                        :style="{ background: avatarColor(assignment.employee) }"
                                    >
                                        {{ employeeInitials(assignment.employee) }}
                                    </div>
                                    <span class="employee-name">{{ employeeFullName(assignment.employee) }}</span>
                                </div>
                            </td>
                            <!-- Rôle -->
                            <td class="col-role">
                                <span class="role-badge">{{ assignment.employee?.user?.role?.name ?? '—' }}</span>
                            </td>
                            <!-- Modèle -->
                            <td class="col-model">
                                {{ assignment.planning_model?.name ?? '—' }}
                            </td>
                            <!-- Période -->
                            <td class="col-period">
                                <div class="period-info">
                                    <span class="period-date">{{ formatDate(assignment.start_date) }}</span>
                                    <span v-if="assignment.end_date" class="period-separator">–</span>
                                    <span v-if="assignment.end_date" class="period-date">{{ formatDate(assignment.end_date) }}</span>
                                    <span v-else class="period-indefinite">Indéterminé</span>
                                </div>
                            </td>
                            <!-- Statut -->
                            <td>
                                <span :class="['status-badge', assignment.status.replace(' ', '-')]">
                                    {{ assignment.status }}
                                </span>
                            </td>
                            <!-- Actions -->
                            <td class="col-actions">
                                <div class="actions">
                                    <!-- Valider (si en attente) -->
                                    <Button
                                        v-if="assignment.status === 'en attente'"
                                        icon="pi pi-check"
                                        severity="success"
                                        variant="text"
                                        rounded
                                        class="action-btn"
                                        title="Valider"
                                        @click="changeStatus(assignment, 'validé')"
                                    />
                                    <!-- Suspendre (si validé) -->
                                    <Button
                                        v-if="assignment.status === 'validé'"
                                        icon="pi pi-pause"
                                        severity="warn"
                                        variant="text"
                                        rounded
                                        class="action-btn"
                                        title="Suspendre"
                                        @click="changeStatus(assignment, 'suspendu')"
                                    />
                                    <!-- Remettre en attente (si suspendu) -->
                                    <Button
                                        v-if="assignment.status === 'suspendu'"
                                        icon="pi pi-undo"
                                        severity="secondary"
                                        variant="text"
                                        rounded
                                        class="action-btn"
                                        title="Remettre en attente"
                                        @click="changeStatus(assignment, 'en attente')"
                                    />
                                    <!-- Terminer (si validé) -->
                                    <Button
                                        v-if="assignment.status === 'validé'"
                                        icon="pi pi-stop-circle"
                                        severity="secondary"
                                        variant="text"
                                        rounded
                                        class="action-btn"
                                        title="Terminer"
                                        @click="changeStatus(assignment, 'terminé')"
                                    />
                                    <!-- Supprimer (si en attente) -->
                                    <Button
                                        v-if="assignment.status === 'en attente'"
                                        icon="pi pi-trash"
                                        severity="danger"
                                        variant="text"
                                        rounded
                                        class="action-btn"
                                        title="Supprimer"
                                        @click="deleteAssignment(assignment)"
                                    />
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div v-else class="empty">Aucune assignation trouvée.</div>
                
                <!-- Pagination -->
                <div v-if="props.assignments?.data?.length > 0" class="mt-6">
                    <Paginator 
                        :rows="10"
                        :totalRecords="props.assignments.total"
                        :first="props.assignments.from - 1"
                        @page="onPageChange"
                        template="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                        :rowsPerPageOptions="[5, 10, 20, 50]"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.validation-page {
    font-family: 'DM Sans', 'Segoe UI', sans-serif;
    padding: 2rem 2.5rem;
    max-width: 1200px;
    margin: 0 auto;
    color: #111;
}

.header {
    margin-bottom: 1.5rem;
}

.header-content {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.875rem;
    color: #6b7280;
    cursor: pointer;
    transition: color 0.15s;
    width: fit-content;
}

.back-link:hover {
    color: #111;
}

.title {
    font-size: 1.5rem;
    font-weight: 600;
    margin: 0;
    color: #111;
}

.subtitle {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0;
}

/* Filtres */
.filters {
    display: flex;
    gap: 0.25rem;
    margin-bottom: 1.25rem;
    flex-wrap: wrap;
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

/* Table Card */
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

/* Columns */
.col-employee {
    min-width: 180px;
}

.col-role {
    min-width: 120px;
}

.col-model {
    min-width: 180px;
}

.col-period {
    min-width: 200px;
}

.col-actions {
    min-width: 120px;
}

/* Employee Cell */
.employee-cell {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    color: #fff;
    font-size: 0.8rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.employee-name {
    font-weight: 500;
    color: #111;
}

/* Role Badge */
.role-badge {
    display: inline-block;
    padding: 0.2rem 0.65rem;
    border-radius: 20px;
    background: #d1fae5;
    color: #065f46;
    font-size: 0.78rem;
    font-weight: 500;
}

/* Period Info */
.period-info {
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.period-date {
    color: #111;
    font-weight: 500;
}

.period-separator {
    color: #d1d5db;
}

.period-indefinite {
    color: #059669;
    font-size: 0.8rem;
}

/* Status Badge */
.status-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
}

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

/* Actions */
.actions {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.action-btn {
    width: 32px;
    height: 32px;
    padding: 0 !important;
}

/* Empty State */
.empty {
    text-align: center;
    color: #9ca3af;
    padding: 3rem;
}

@media (max-width: 1024px) {
    .validation-page {
        padding: 1.5rem 1rem;
    }
    .header-content {
        gap: 0.75rem;
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
        min-width: 700px;
    }
}

@media (max-width: 640px) {
    .validation-page {
        padding: 1rem 0.75rem;
    }
    .header-content {
        gap: 0.5rem;
    }
    th, td {
        padding: 0.75rem 0.85rem;
    }
    .avatar {
        width: 32px;
        height: 32px;
        font-size: 0.75rem;
    }
    .period-info {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
    }
}
</style>
