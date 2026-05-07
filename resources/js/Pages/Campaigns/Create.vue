<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import Calendar from 'primevue/calendar'
import Dropdown from 'primevue/dropdown'

const form = useForm({
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

const submit = () => {
    form.post(route('campaigns.store'))
}
</script>

<template>
    <Head title="Créer une campagne" />
    <AuthenticatedLayout>
        <div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow">
            <h1 class="text-2xl font-bold mb-6">Créer une campagne</h1>

            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nom</label>
                    <InputText id="name" v-model="form.name" class="w-full" required />
                    <small v-if="form.errors.name" class="text-red-500">{{ form.errors.name }}</small>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <Textarea id="description" v-model="form.description" class="w-full" rows="4" required />
                    <small v-if="form.errors.description" class="text-red-500">{{ form.errors.description }}</small>
                </div>

                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Date de début</label>
                    <Calendar id="start_date" v-model="form.start_date" class="w-full" dateFormat="dd/mm/yy" required />
                    <small v-if="form.errors.start_date" class="text-red-500">{{ form.errors.start_date }}</small>
                </div>

                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">Date de fin</label>
                    <Calendar id="end_date" v-model="form.end_date" class="w-full" dateFormat="dd/mm/yy" required />
                    <small v-if="form.errors.end_date" class="text-red-500">{{ form.errors.end_date }}</small>
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                    <Dropdown id="status" v-model="form.status" :options="statusOptions" optionLabel="label" optionValue="value" class="w-full" />
                    <small v-if="form.errors.status" class="text-red-500">{{ form.errors.status }}</small>
                </div>

                <div class="flex gap-4">
                    <Button type="submit" label="Créer" :loading="form.processing" />
                    <Button type="button" label="Annuler" severity="secondary" @click="$inertia.visit(route('campaigns.index'))" />
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>