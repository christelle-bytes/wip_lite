<script setup>
import { ref } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Button from 'primevue/button';

const props = defineProps({
    supervisor: Array,
    auth: Object,
    currentMonth: Array,
});

const expandedRows = ref([]);

// Formater les heures décimales (ex: 8.5 -> 08h30)
const formatHours = (value) => {
    if (!value) return '0h00';
    const hours = Math.floor(value);
    const minutes = Math.round((value - hours) * 60);
    return `${hours}h${minutes.toString().padStart(2, '0')}`;
};

// Formater la date en français
const formatDate = (date) => {
    return new Date(date).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
};
console.log(props.currentMonth)
</script>

<template>
    <div class="card p-4">
        <DataTable 
            v-model:expandedRows="expandedRows" 
            :value="props.supervisor" 
            dataKey="id" 
            responsiveLayout="scroll"
            class="p-datatable-sm"
        >
            <template #header>
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="text-xl font-bold">Suivi des Temps - Superviseurs</span>
                </div>
            </template>

            <!-- Colonne d'expansion -->
            <Column expander style="width: 3rem" />

            <!-- Informations de base -->
            <Column field="matricule" header="Matricule" sortable />
            <Column header="Nom Complet">
                <template #body="{ data }">
                    {{ data.first_name }} {{ data.last_name }}
                </template>
            </Column>
            <Column field="position.name" header="Poste" />
            
            <Column header="Statut">
                <template #body="{ data }">
                    <Tag :value="data.status" :severity="data.status === 'actif' ? 'success' : 'danger'" />
                </template>
            </Column>

            <!-- Contenu détaillé (Expansion) -->
            <template #expansion="{ data }">
                <div class="p-4 bg-gray-50 rounded-lg">
                    <h5 class="mb-3 font-semibold text-blue-600">Entrées de temps pour {{ data.first_name }}</h5>
                    
                    <DataTable :value="data.timesheet.flatMap(ts => ts.entries)" class="p-datatable-sm shadow-sm">
                        <Column field="date" header="Date">
                            <template #body="slotProps">
                                {{ formatDate(slotProps.data.date) }}
                            </template>
                        </Column>
                        <Column field="check_in" header="Arrivée" />
                        <Column field="check_out" header="Départ" />
                        <Column header="Heures Travail.">
                            <template #body="slotProps">
                                <span class="font-medium text-green-600">
                                    {{ formatHours(slotProps.data.total_hours) }}
                                </span>
                            </template>
                        </Column>
                        <Column header="Heures Supp.">
                            <template #body="slotProps">
                                <Tag 
                                    v-if="slotProps.data.overtime_hours > 0" 
                                    :value="formatHours(slotProps.data.overtime_hours)" 
                                    severity="warning" 
                                />
                                <span v-else>-</span>
                            </template>
                        </Column>
                    </DataTable>
                </div>
            </template>
        </DataTable>
    </div>
</template>

<style scoped>
/* Ajoute un léger effet de profondeur au tableau */
.card {
    background: #ffffff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

:deep(.p-datatable-header) {
    background: transparent;
    border: none;
    padding-left: 0;
}
</style>