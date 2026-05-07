<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Button from 'primevue/button'
import Dropdown from 'primevue/dropdown'
import Calendar from 'primevue/calendar'

const props = defineProps({
    assignment: Object,
    employees: Array,
    campaigns: Array,
    positions: Array
})

const form = useForm({
    employee_id: props.assignment.employee_id,
    campaign_id: props.assignment.campaign_id,
    position_id: props.assignment.position_id,
    manager_id: props.assignment.manager_id,
    status: props.assignment.status,
    start_date: new Date(props.assignment.start_date),
    end_date: props.assignment.end_date ? new Date(props.assignment.end_date) : null
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

const submit = () => {
    form.put(route('assignments.update', props.assignment.id))
}
</script>

<template>
    <Head title="Modifier l'assignation" />
    <AuthenticatedLayout>
        <div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow">
            <h1 class="text-2xl font-bold mb-6">Modifier l'assignation</h1>

            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-2">Employé</label>
                    <Dropdown id="employee_id" v-model="form.employee_id" :options="employeeOptions" optionLabel="label" optionValue="value" class="w-full" placeholder="Sélectionner un employé" required />
                    <small v-if="form.errors.employee_id" class="text-red-500">{{ form.errors.employee_id }}</small>
                </div>

                <div>
                    <label for="campaign_id" class="block text-sm font-medium text-gray-700 mb-2">Campagne</label>
                    <Dropdown id="campaign_id" v-model="form.campaign_id" :options="campaignOptions" optionLabel="label" optionValue="value" class="w-full" placeholder="Sélectionner une campagne" required />
                    <small v-if="form.errors.campaign_id" class="text-red-500">{{ form.errors.campaign_id }}</small>
                </div>

                <div>
                    <label for="position_id" class="block text-sm font-medium text-gray-700 mb-2">Position</label>
                    <Dropdown id="position_id" v-model="form.position_id" :options="positionOptions" optionLabel="label" optionValue="value" class="w-full" placeholder="Sélectionner une position" required />
                    <small v-if="form.errors.position_id" class="text-red-500">{{ form.errors.position_id }}</small>
                </div>

                <div>
                    <label for="manager_id" class="block text-sm font-medium text-gray-700 mb-2">Manager</label>
                    <Dropdown id="manager_id" v-model="form.manager_id" :options="managerOptions" optionLabel="label" optionValue="value" class="w-full" placeholder="Sélectionner un manager (optionnel)" />
                    <small v-if="form.errors.manager_id" class="text-red-500">{{ form.errors.manager_id }}</small>
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                    <Dropdown id="status" v-model="form.status" :options="statusOptions" optionLabel="label" optionValue="value" class="w-full" />
                    <small v-if="form.errors.status" class="text-red-500">{{ form.errors.status }}</small>
                </div>

                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Date de début</label>
                    <Calendar id="start_date" v-model="form.start_date" class="w-full" dateFormat="dd/mm/yy" required />
                    <small v-if="form.errors.start_date" class="text-red-500">{{ form.errors.start_date }}</small>
                </div>

                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">Date de fin</label>
                    <Calendar id="end_date" v-model="form.end_date" class="w-full" dateFormat="dd/mm/yy" />
                    <small v-if="form.errors.end_date" class="text-red-500">{{ form.errors.end_date }}</small>
                </div>

                <div class="flex gap-4">
                    <Button type="submit" label="Mettre à jour" :loading="form.processing" />
                    <Button type="button" label="Annuler" severity="secondary" @click="$inertia.visit(route('assignments.index'))" />
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>