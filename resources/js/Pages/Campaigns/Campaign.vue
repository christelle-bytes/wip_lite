<script setup>
import { ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Button from 'primevue/button'
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

const getStatusClass = (status) => {
    switch (status) {
        case 'active':
            return 'bg-emerald-100 text-emerald-700'
        case 'inactive':
            return 'bg-slate-100 text-slate-700'
        case 'terminée':
            return 'bg-sky-100 text-sky-700'
        default:
            return 'bg-slate-100 text-slate-700'
    }
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    })
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
        <div class="min-h-screen bg-slate-50 p-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-slate-900">Campagnes</h1>
                    <p class="mt-2 text-slate-500">Gestion des campagnes et suivi des ressources affectées.</p>
                </div>
                <Button @click="openCreateDialog" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl shadow-sm">
                    <span class="mr-2 text-xl">+</span> Créer une campagne
                </Button>
            </div>

            <div class="grid gap-6 xl:grid-cols-3 lg:grid-cols-2">
                <div v-for="campaign in campaigns" :key="campaign.id" class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-semibold text-slate-900">{{ campaign.name }}</h2>
                            <p class="mt-2 text-sm leading-6 text-slate-500">{{ campaign.description }}</p>
                        </div>
                        <span :class="['rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wide', getStatusClass(campaign.status)]">
                            {{ campaign.status }}
                        </span>
                    </div>

                    <div class="mt-6 text-sm text-slate-500 space-y-2">
                        <div class="flex items-center gap-2">
                            <i class="pi pi-calendar text-slate-400"></i>
                            {{ formatDate(campaign.start_date) }} - {{ formatDate(campaign.end_date) }}
                        </div>
                    </div>

                    <div class="mt-6 grid grid-cols-3 gap-3 text-xs font-semibold uppercase tracking-wide text-slate-600">
                        <div class="flex items-center gap-2 rounded-2xl bg-slate-50 px-3 py-2">
                            <span class="h-2 w-2 rounded-full bg-slate-900"></span>
                            {{ campaign.cp_count }} CP
                        </div>
                        <div class="flex items-center gap-2 rounded-2xl bg-slate-50 px-3 py-2">
                            <span class="h-2 w-2 rounded-full bg-blue-600"></span>
                            {{ campaign.sup_count }} SUP
                        </div>
                        <div class="flex items-center gap-2 rounded-2xl bg-slate-50 px-3 py-2">
                            <span class="h-2 w-2 rounded-full bg-emerald-600"></span>
                            {{ campaign.tc_count }} TC
                        </div>
                    </div>

                    <div class="mt-6 flex flex-wrap gap-3">
                        <Link :href="route('campaigns.show', campaign.id)" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">
                            Voir
                        </Link>
                        <Button @click="openEditDialog(campaign)" label="Modifier" severity="warning" class="rounded-xl px-4 py-2 text-sm" />
                        <Link :href="route('campaigns.destroy', campaign.id)" method="delete" as="button" class="inline-flex items-center justify-center rounded-xl border border-red-100 bg-red-50 px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-100">
                            Désactiver
                        </Link>
                    </div>
                </div>
            </div>

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
