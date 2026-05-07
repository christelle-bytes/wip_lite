<script setup>
import { ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import Tag from 'primevue/tag'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import Calendar from 'primevue/calendar'
import Dropdown from 'primevue/dropdown'

const props = defineProps({
    campaigns: Array,
});

const filters = ["Toutes", "Actives", "Inactives", "Terminées"]
const activeFilter = ref("Toutes")

const createDialogVisible = ref(false)
const editDialogVisible = ref(false)
const editingCampaign = ref(null)

const createForm = useForm({
    name: '',
    description: '',
    start_date: null,
    end_date: null,
    status: 'active'
})

const editForm = useForm({
    name: '',
    description: '',
    start_date: null,
    end_date: null,
    status: 'active'
})

const statusOptions = [
    { label: 'Active', value: 'active' },
    { label: 'Inactive', value: 'inactive' },
    { label: 'Terminée', value: 'terminée' }
]

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

const openCreateDialog = () => {
    createForm.reset()
    createDialogVisible.value = true
}

const openEditDialog = (campaign) => {
    editingCampaign.value = campaign
    editForm.name = campaign.name
    editForm.description = campaign.description
    editForm.start_date = new Date(campaign.start_date)
    editForm.end_date = new Date(campaign.end_date)
    editForm.status = campaign.status
    editDialogVisible.value = true
}

const submitCreate = () => {
    createForm.post(route('campaigns.store'), {
        onSuccess: () => {
            createDialogVisible.value = false
        }
    })
}

const submitEdit = () => {
    editForm.put(route('campaigns.update', editingCampaign.value.id), {
        onSuccess: () => {
            editDialogVisible.value = false
        }
    })
}
</script>

<template>
    <Head title="Campagnes" />
    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 p-8">
            <!-- Header & Filtres -->
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
                <div class="flex flex-wrap gap-2">
                    <button v-for="filter in filters" :key="filter" @click="activeFilter = filter" :class="[
                        'px-4 py-2 rounded-full text-sm font-medium transition-colors',
                        activeFilter === filter ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-600 hover:bg-gray-300'
                    ]">
                        {{ filter }} <span class="ml-1 opacity-70">({{ campaigns.length }})</span>
                    </button>
                </div>

                <Button @click="openCreateDialog" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg flex items-center font-medium transition-shadow shadow-sm">
                    <span class="mr-2 text-xl">+</span> Créer une campagne
                </Button>
            </div>

            <!-- DataTable -->
            <DataTable :value="campaigns" paginator :rows="10" :rowsPerPageOptions="[5, 10, 25]" tableStyle="min-width: 50rem">
                <Column field="name" header="Nom" sortable style="width: 25%"></Column>
                <Column field="description" header="Description" style="width: 30%"></Column>
                <Column field="start_date" header="Date début" sortable style="width: 15%">
                    <template #body="slotProps">
                        {{ new Date(slotProps.data.start_date).toLocaleDateString('fr-FR') }}
                    </template>
                </Column>
                <Column field="end_date" header="Date fin" sortable style="width: 15%">
                    <template #body="slotProps">
                        {{ new Date(slotProps.data.end_date).toLocaleDateString('fr-FR') }}
                    </template>
                </Column>
                <Column field="status" header="Statut" style="width: 10%">
                    <template #body="slotProps">
                        <Tag :value="slotProps.data.status" :severity="getStatusSeverity(slotProps.data.status)" />
                    </template>
                </Column>
                <Column header="Actions" style="width: 15%">
                    <template #body="slotProps">
                        <div class="flex gap-2">
                            <Link :href="route('campaigns.show', slotProps.data.id)">
                                <Button icon="pi pi-eye" severity="info" size="small" />
                            </Link>
                            <Button @click="openEditDialog(slotProps.data)" icon="pi pi-pencil" severity="warning" size="small" />
                            <Link :href="route('campaigns.destroy', slotProps.data.id)" method="delete" as="button">
                                <Button icon="pi pi-ban" severity="danger" size="small" />
                            </Link>
                        </div>
                    </template>
                </Column>
            </DataTable>

            <!-- Create Dialog -->
            <Dialog v-model:visible="createDialogVisible" modal header="Créer une campagne" :style="{ width: '50rem' }">
                <form @submit.prevent="submitCreate" class="space-y-6">
                    <div>
                        <label for="create-name" class="block text-sm font-medium text-gray-700 mb-2">Nom</label>
                        <InputText id="create-name" v-model="createForm.name" class="w-full" required />
                        <small v-if="createForm.errors.name" class="text-red-500">{{ createForm.errors.name }}</small>
                    </div>

                    <div>
                        <label for="create-description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <Textarea id="create-description" v-model="createForm.description" class="w-full" rows="4" required />
                        <small v-if="createForm.errors.description" class="text-red-500">{{ createForm.errors.description }}</small>
                    </div>

                    <div>
                        <label for="create-start_date" class="block text-sm font-medium text-gray-700 mb-2">Date de début</label>
                        <Calendar id="create-start_date" v-model="createForm.start_date" class="w-full" dateFormat="dd/mm/yy" required />
                        <small v-if="createForm.errors.start_date" class="text-red-500">{{ createForm.errors.start_date }}</small>
                    </div>

                    <div>
                        <label for="create-end_date" class="block text-sm font-medium text-gray-700 mb-2">Date de fin</label>
                        <Calendar id="create-end_date" v-model="createForm.end_date" class="w-full" dateFormat="dd/mm/yy" required />
                        <small v-if="createForm.errors.end_date" class="text-red-500">{{ createForm.errors.end_date }}</small>
                    </div>

                    <div>
                        <label for="create-status" class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                        <Dropdown id="create-status" v-model="createForm.status" :options="statusOptions" optionLabel="label" optionValue="value" class="w-full" />
                        <small v-if="createForm.errors.status" class="text-red-500">{{ createForm.errors.status }}</small>
                    </div>
                </form>

                <template #footer>
                    <Button label="Annuler" icon="pi pi-times" severity="secondary" @click="createDialogVisible = false" />
                    <Button label="Créer" icon="pi pi-check" @click="submitCreate" :loading="createForm.processing" />
                </template>
            </Dialog>

            <!-- Edit Dialog -->
            <Dialog v-model:visible="editDialogVisible" modal header="Modifier la campagne" :style="{ width: '50rem' }">
                <form @submit.prevent="submitEdit" class="space-y-6">
                    <div>
                        <label for="edit-name" class="block text-sm font-medium text-gray-700 mb-2">Nom</label>
                        <InputText id="edit-name" v-model="editForm.name" class="w-full" required />
                        <small v-if="editForm.errors.name" class="text-red-500">{{ editForm.errors.name }}</small>
                    </div>

                    <div>
                        <label for="edit-description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <Textarea id="edit-description" v-model="editForm.description" class="w-full" rows="4" required />
                        <small v-if="editForm.errors.description" class="text-red-500">{{ editForm.errors.description }}</small>
                    </div>

                    <div>
                        <label for="edit-start_date" class="block text-sm font-medium text-gray-700 mb-2">Date de début</label>
                        <Calendar id="edit-start_date" v-model="editForm.start_date" class="w-full" dateFormat="dd/mm/yy" required />
                        <small v-if="editForm.errors.start_date" class="text-red-500">{{ editForm.errors.start_date }}</small>
                    </div>

                    <div>
                        <label for="edit-end_date" class="block text-sm font-medium text-gray-700 mb-2">Date de fin</label>
                        <Calendar id="edit-end_date" v-model="editForm.end_date" class="w-full" dateFormat="dd/mm/yy" required />
                        <small v-if="editForm.errors.end_date" class="text-red-500">{{ editForm.errors.end_date }}</small>
                    </div>

                    <div>
                        <label for="edit-status" class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                        <Dropdown id="edit-status" v-model="editForm.status" :options="statusOptions" optionLabel="label" optionValue="value" class="w-full" />
                        <small v-if="editForm.errors.status" class="text-red-500">{{ editForm.errors.status }}</small>
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
