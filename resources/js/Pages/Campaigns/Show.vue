<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Button from 'primevue/button'
import Card from 'primevue/card'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Tag from 'primevue/tag'

const props = defineProps({
    campaign: Object
})

const getStatusSeverity = (status) => {
    switch (status) {
        case 'active':
            return 'success'
        case 'inactive':
            return 'warning'
        case 'terminée':
            return 'info'
        default:
            return 'info'
    }
}
</script>

<template>
    <Head title="Détails de la campagne" />
    <AuthenticatedLayout>
        <div class="max-w-6xl mx-auto space-y-6">
            <!-- Campaign Details -->
            <Card>
                <template #title>
                    <div class="flex justify-between items-center">
                        <h1 class="text-2xl font-bold">{{ campaign.name }}</h1>
                        <Tag :value="campaign.status" :severity="getStatusSeverity(campaign.status)" />
                    </div>
                </template>
                <template #content>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <strong>Description:</strong>
                            <p class="mt-1">{{ campaign.description }}</p>
                        </div>
                        <div>
                            <strong>Date de début:</strong>
                            <p class="mt-1">{{ new Date(campaign.start_date).toLocaleDateString('fr-FR') }}</p>
                        </div>
                        <div>
                            <strong>Date de fin:</strong>
                            <p class="mt-1">{{ new Date(campaign.end_date).toLocaleDateString('fr-FR') }}</p>
                        </div>
                    </div>
                </template>
                <template #footer>
                    <div class="flex gap-2">
                        <Link :href="route('campaigns.edit', campaign.id)">
                            <Button label="Modifier" severity="warning" />
                        </Link>
                        <Link :href="route('campaigns.index')">
                            <Button label="Retour" severity="secondary" />
                        </Link>
                    </div>
                </template>
            </Card>

            <!-- Assignments -->
            <Card>
                <template #title>
                    <h2 class="text-xl font-semibold">Assignations</h2>
                </template>
                <template #content>
                    <DataTable :value="campaign.assignments" paginator :rows="10">
                        <Column field="employee.user.name" header="Employé" sortable></Column>
                        <Column field="position.name" header="Position" sortable></Column>
                        <Column field="status" header="Statut">
                            <template #body="slotProps">
                                <Tag :value="slotProps.data.status" :severity="getStatusSeverity(slotProps.data.status)" />
                            </template>
                        </Column>
                        <Column field="start_date" header="Date début" sortable>
                            <template #body="slotProps">
                                {{ new Date(slotProps.data.start_date).toLocaleDateString('fr-FR') }}
                            </template>
                        </Column>
                        <Column field="end_date" header="Date fin" sortable>
                            <template #body="slotProps">
                                {{ slotProps.data.end_date ? new Date(slotProps.data.end_date).toLocaleDateString('fr-FR') : 'N/A' }}
                            </template>
                        </Column>
                    </DataTable>
                </template>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>