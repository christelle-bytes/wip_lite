<script setup>
import { ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import Tag from 'primevue/tag'
import Dialog from 'primevue/dialog'
import Dropdown from 'primevue/dropdown'
import Calendar from 'primevue/calendar'

const props = defineProps({
    assignments: Array,
    employees: Array,
    campaigns: Array,
    positions: Array
});

const createDialogVisible = ref(false)
const editDialogVisible = ref(false)
const editingAssignment = ref(null)

const createForm = useForm({
    employee_id: null,
    campaign_id: null,
    position_id: null,
    manager_id: null,
    status: 'actif',
    start_date: null,
    end_date: null
})

const editForm = useForm({
    employee_id: null,
    campaign_id: null,
    position_id: null,
    manager_id: null,
    status: 'actif',
    start_date: null,
    end_date: null
})

const statusOptions = [
    { label: 'Actif', value: 'actif' },
    { label: 'Terminé', value: 'terminé' },
    { label: 'Suspendu', value: 'suspendu' }
]

const employeeOptions = props.employees.map(emp => ({
    label: emp.user.name,
    value: emp.id
}))

const campaignOptions = props.campaigns.map(camp => ({
    label: camp.name,
    value: camp.id
}))

const positionOptions = props.positions.map(pos => ({
    label: pos.name,
    value: pos.id
}))

const managerOptions = props.employees.map(emp => ({
    label: emp.user.name,
    value: emp.id
}))

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

const openCreateDialog = () => {
    createForm.reset()
    createDialogVisible.value = true
}

const openEditDialog = (assignment) => {
    editingAssignment.value = assignment
    editForm.employee_id = assignment.employee_id
    editForm.campaign_id = assignment.campaign_id
    editForm.position_id = assignment.position_id
    editForm.manager_id = assignment.manager_id
    editForm.status = assignment.status
    editForm.start_date = new Date(assignment.start_date)
    editForm.end_date = assignment.end_date ? new Date(assignment.end_date) : null
    editDialogVisible.value = true
}

const submitCreate = () => {
    createForm.post(route('assignments.store'), {
        onSuccess: () => {
            createDialogVisible.value = false
        }
    })
}

const submitEdit = () => {
    editForm.put(route('assignments.update', editingAssignment.value.id), {
        onSuccess: () => {
            editDialogVisible.value = false
        }
    })
}
</script>

<template>
    <Head title="Assignations" />
    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 p-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-2xl font-bold">Assignations</h1>
                <Button @click="openCreateDialog" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg flex items-center font-medium transition-shadow shadow-sm">
                    <span class="mr-2 text-xl">+</span> Créer une assignation
                </Button>
            </div>

            <!-- DataTable -->
            <DataTable :value="assignments" paginator :rows="10" :rowsPerPageOptions="[5, 10, 25]" tableStyle="min-width: 50rem">
                <Column field="employee.user.name" header="Employé" sortable></Column>
                <Column field="campaign.name" header="Campagne" sortable></Column>
                <Column field="position.name" header="Position" sortable></Column>
                <Column field="manager.user.name" header="Manager" sortable></Column>
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
                <Column header="Actions">
                    <template #body="slotProps">
                        <div class="flex gap-2">
                            <Link :href="route('assignments.show', slotProps.data.id)">
                                <Button icon="pi pi-eye" severity="info" size="small" />
                            </Link>
                            <Button @click="openEditDialog(slotProps.data)" icon="pi pi-pencil" severity="warning" size="small" />
                            <Link :href="route('assignments.destroy', slotProps.data.id)" method="delete" as="button">
                                <Button icon="pi pi-ban" severity="danger" size="small" />
                            </Link>
                        </div>
                    </template>
                </Column>
            </DataTable>

            <!-- Create Dialog -->
            <Dialog v-model:visible="createDialogVisible" modal header="Créer une assignation" :style="{ width: '50rem' }">
                <form @submit.prevent="submitCreate" class="space-y-6">
                    <div>
                        <label for="create-employee_id" class="block text-sm font-medium text-gray-700 mb-2">Employé</label>
                        <Dropdown id="create-employee_id" v-model="createForm.employee_id" :options="employeeOptions" optionLabel="label" optionValue="value" class="w-full" placeholder="Sélectionner un employé" required />
                        <small v-if="createForm.errors.employee_id" class="text-red-500">{{ createForm.errors.employee_id }}</small>
                    </div>

                    <div>
                        <label for="create-campaign_id" class="block text-sm font-medium text-gray-700 mb-2">Campagne</label>
                        <Dropdown id="create-campaign_id" v-model="createForm.campaign_id" :options="campaignOptions" optionLabel="label" optionValue="value" class="w-full" placeholder="Sélectionner une campagne" required />
                        <small v-if="createForm.errors.campaign_id" class="text-red-500">{{ createForm.errors.campaign_id }}</small>
                    </div>

                    <div>
                        <label for="create-position_id" class="block text-sm font-medium text-gray-700 mb-2">Position</label>
                        <Dropdown id="create-position_id" v-model="createForm.position_id" :options="positionOptions" optionLabel="label" optionValue="value" class="w-full" placeholder="Sélectionner une position" required />
                        <small v-if="createForm.errors.position_id" class="text-red-500">{{ createForm.errors.position_id }}</small>
                    </div>

                    <div>
                        <label for="create-manager_id" class="block text-sm font-medium text-gray-700 mb-2">Manager</label>
                        <Dropdown id="create-manager_id" v-model="createForm.manager_id" :options="managerOptions" optionLabel="label" optionValue="value" class="w-full" placeholder="Sélectionner un manager (optionnel)" />
                        <small v-if="createForm.errors.manager_id" class="text-red-500">{{ createForm.errors.manager_id }}</small>
                    </div>

                    <div>
                        <label for="create-status" class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                        <Dropdown id="create-status" v-model="createForm.status" :options="statusOptions" optionLabel="label" optionValue="value" class="w-full" />
                        <small v-if="createForm.errors.status" class="text-red-500">{{ createForm.errors.status }}</small>
                    </div>

                    <div>
                        <label for="create-start_date" class="block text-sm font-medium text-gray-700 mb-2">Date de début</label>
                        <Calendar id="create-start_date" v-model="createForm.start_date" class="w-full" dateFormat="dd/mm/yy" required />
                        <small v-if="createForm.errors.start_date" class="text-red-500">{{ createForm.errors.start_date }}</small>
                    </div>

                    <div>
                        <label for="create-end_date" class="block text-sm font-medium text-gray-700 mb-2">Date de fin</label>
                        <Calendar id="create-end_date" v-model="createForm.end_date" class="w-full" dateFormat="dd/mm/yy" />
                        <small v-if="createForm.errors.end_date" class="text-red-500">{{ createForm.errors.end_date }}</small>
                    </div>
                </form>

                <template #footer>
                    <Button label="Annuler" icon="pi pi-times" severity="secondary" @click="createDialogVisible = false" />
                    <Button label="Créer" icon="pi pi-check" @click="submitCreate" :loading="createForm.processing" />
                </template>
            </Dialog>

            <!-- Edit Dialog -->
            <Dialog v-model:visible="editDialogVisible" modal header="Modifier l'assignation" :style="{ width: '50rem' }">
                <form @submit.prevent="submitEdit" class="space-y-6">
                    <div>
                        <label for="edit-employee_id" class="block text-sm font-medium text-gray-700 mb-2">Employé</label>
                        <Dropdown id="edit-employee_id" v-model="editForm.employee_id" :options="employeeOptions" optionLabel="label" optionValue="value" class="w-full" placeholder="Sélectionner un employé" required />
                        <small v-if="editForm.errors.employee_id" class="text-red-500">{{ editForm.errors.employee_id }}</small>
                    </div>

                    <div>
                        <label for="edit-campaign_id" class="block text-sm font-medium text-gray-700 mb-2">Campagne</label>
                        <Dropdown id="edit-campaign_id" v-model="editForm.campaign_id" :options="campaignOptions" optionLabel="label" optionValue="value" class="w-full" placeholder="Sélectionner une campagne" required />
                        <small v-if="editForm.errors.campaign_id" class="text-red-500">{{ editForm.errors.campaign_id }}</small>
                    </div>

                    <div>
                        <label for="edit-position_id" class="block text-sm font-medium text-gray-700 mb-2">Position</label>
                        <Dropdown id="edit-position_id" v-model="editForm.position_id" :options="positionOptions" optionLabel="label" optionValue="value" class="w-full" placeholder="Sélectionner une position" required />
                        <small v-if="editForm.errors.position_id" class="text-red-500">{{ editForm.errors.position_id }}</small>
                    </div>

                    <div>
                        <label for="edit-manager_id" class="block text-sm font-medium text-gray-700 mb-2">Manager</label>
                        <Dropdown id="edit-manager_id" v-model="editForm.manager_id" :options="managerOptions" optionLabel="label" optionValue="value" class="w-full" placeholder="Sélectionner un manager (optionnel)" />
                        <small v-if="editForm.errors.manager_id" class="text-red-500">{{ editForm.errors.manager_id }}</small>
                    </div>

                    <div>
                        <label for="edit-status" class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                        <Dropdown id="edit-status" v-model="editForm.status" :options="statusOptions" optionLabel="label" optionValue="value" class="w-full" />
                        <small v-if="editForm.errors.status" class="text-red-500">{{ editForm.errors.status }}</small>
                    </div>

                    <div>
                        <label for="edit-start_date" class="block text-sm font-medium text-gray-700 mb-2">Date de début</label>
                        <Calendar id="edit-start_date" v-model="editForm.start_date" class="w-full" dateFormat="dd/mm/yy" required />
                        <small v-if="editForm.errors.start_date" class="text-red-500">{{ editForm.errors.start_date }}</small>
                    </div>

                    <div>
                        <label for="edit-end_date" class="block text-sm font-medium text-gray-700 mb-2">Date de fin</label>
                        <Calendar id="edit-end_date" v-model="editForm.end_date" class="w-full" dateFormat="dd/mm/yy" />
                        <small v-if="editForm.errors.end_date" class="text-red-500">{{ editForm.errors.end_date }}</small>
                    </div>
                </form>

                <template #footer>
                    <Button label="Annuler" icon="pi pi-times" severity="secondary" @click="editDialogVisible = false" />
                    <Button label="Mettre à jour" icon="pi pi-check" @click="submitEdit" :loading="editForm.processing" />
                </template>
            </Dialog>
        </div>
    </AuthenticatedLayout>
</template>