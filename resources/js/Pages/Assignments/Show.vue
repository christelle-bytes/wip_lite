<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Button from 'primevue/button'
import Card from 'primevue/card'
import Tag from 'primevue/tag'

const props = defineProps({
    assignment: Object
})

const getStatusSeverity = (status) => {
    switch (status) {
        case 'actif':
            return 'success'
        case 'terminé':
            return 'info'
        case 'suspendu':
            return 'warning'
        default:
            return 'info'
    }
}
</script>

<template>
    <Head title="Détails de l'assignation" />
    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto">
            <Card>
                <template #title>
                    <div class="flex justify-between items-center">
                        <h1 class="text-2xl font-bold">Assignation</h1>
                        <Tag :value="assignment.status" :severity="getStatusSeverity(assignment.status)" />
                    </div>
                </template>
                <template #content>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <strong>Employé:</strong>
                            <p class="mt-1">{{ assignment.employee.user.name }}</p>
                        </div>
                        <div>
                            <strong>Campagne:</strong>
                            <p class="mt-1">{{ assignment.campaign.name }}</p>
                        </div>
                        <div>
                            <strong>Position:</strong>
                            <p class="mt-1">{{ assignment.position.name }}</p>
                        </div>
                        <div>
                            <strong>Manager:</strong>
                            <p class="mt-1">{{ assignment.manager ? assignment.manager.user.name : 'N/A' }}</p>
                        </div>
                        <div>
                            <strong>Date de début:</strong>
                            <p class="mt-1">{{ new Date(assignment.start_date).toLocaleDateString('fr-FR') }}</p>
                        </div>
                        <div>
                            <strong>Date de fin:</strong>
                            <p class="mt-1">{{ assignment.end_date ? new Date(assignment.end_date).toLocaleDateString('fr-FR') : 'N/A' }}</p>
                        </div>
                    </div>
                </template>
                <template #footer>
                    <div class="flex gap-2">
                        <Link :href="route('assignments.edit', assignment.id)">
                            <Button label="Modifier" severity="warning" />
                        </Link>
                        <Link :href="route('assignments.index')">
                            <Button label="Retour" severity="secondary" />
                        </Link>
                    </div>
                </template>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>