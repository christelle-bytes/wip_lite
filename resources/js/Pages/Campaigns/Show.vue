<script setup>
import { ref } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Button from "primevue/button";
import HierarchyNode from "./HierarchyNode.vue";
import Dialog from "primevue/dialog";
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import Calendar from 'primevue/calendar'
import Dropdown from 'primevue/dropdown'
import { useForm } from '@inertiajs/vue3'

// Et ajouter statusOptions dans le script
const statusOptions = [
    { label: 'Active', value: 'active' },
    { label: 'Inactive', value: 'inactive' },
    { label: 'Terminée', value: 'terminée' },
]

// Et la fonction submitEdit manquante
const submitEdit = () => {
    editForm.put(route('campaigns.update', editingCampaign.value.id), {
        onSuccess: () => { editDialogVisible.value = false }
    })
}
const props = defineProps({
    campaign: Object,
    summary: Object,
    hierarchy: Array,
    isAdmin: Boolean,
});

const getStatusSeverity = (status) => {
    switch (status) {
        case "active":
            return "success";
        case "inactive":
            return "warning";
        case "terminée":
            return "info";
        default:
            return "info";
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("fr-FR", {
        day: "2-digit",
        month: "long",
        year: "numeric",
    });
};

const getPositionClass = (position) => {
    switch (position) {
        case "Chef de Plateau":
            return "bg-slate-900 text-white";
        case "Superviseur":
            return "bg-blue-100 text-blue-800";
        case "Teleconseiller":
            return "bg-emerald-100 text-emerald-800";
        default:
            return "bg-slate-100 text-slate-700";
    }
};
const editForm = useForm({
    name: "",
    description: "",
    start_date: null,
    end_date: null,
    status: "active",
});

const openEditDialog = (campaign) => {
    editingCampaign.value = campaign;
    editForm.name = campaign.name;
    editForm.description = campaign.description;
    editForm.start_date = new Date(campaign.start_date);
    editForm.end_date = new Date(campaign.end_date);
    editForm.status = campaign.status;
    editDialogVisible.value = true;
};
const getPositionColor = (position) => {
    switch (position) {
        case "Chef de Plateau":
            return "bg-slate-900";
        case "Superviseur":
            return "bg-blue-600";
        case "Teleconseiller":
            return "bg-emerald-600";
        default:
            return "bg-slate-600";
    }
};
const editDialogVisible = ref(false);
const editingCampaign = ref(null);
</script>

<template>
    <Head title="Détails de la campagne" />
    <AuthenticatedLayout>
        <div class="max-w-6xl mx-auto space-y-6">
            <div
                class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm"
            >
                <div
                    class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between"
                >
                    <div>
                        <Link
                            :href="route('campaigns.index')"
                            class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-slate-700 mb-4 lg:mb-0"
                        >
                            <i class="pi pi-arrow-left"></i>
                            Retour aux campagnes
                        </Link>
                        <div class="flex flex-wrap items-center gap-3">
                            <h1 class="text-3xl font-bold text-slate-900">
                                {{ campaign.name }}
                            </h1>
                            <span
                                :class="[
                                    'rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wide',
                                    getStatusSeverity(campaign.status) ===
                                    'success'
                                        ? 'bg-emerald-100 text-emerald-700'
                                        : getStatusSeverity(campaign.status) ===
                                            'warning'
                                          ? 'bg-slate-100 text-slate-700'
                                          : 'bg-sky-100 text-sky-700',
                                ]"
                                >{{ campaign.status }}</span
                            >
                        </div>
                        <p class="mt-3 text-slate-500">
                            {{ campaign.description }}
                        </p>
                        <div
                            class="mt-4 flex flex-wrap items-center gap-4 text-sm text-slate-500"
                        >
                            <div class="inline-flex items-center gap-2">
                                <i class="pi pi-calendar"></i>
                                {{ formatDate(campaign.start_date) }} -
                                {{ formatDate(campaign.end_date) }}
                            </div>
                        </div>
                    </div>
                 <!-- Remplacer ce bloc dans Show.vue -->
<template v-if="isAdmin">
    <Button 
        @click="openEditDialog(campaign)" 
        label="Modifier" 
        severity="warning" 
        class="rounded-xl px-4 py-2 text-sm" 
    />
    <Button 
        label="Affecter une ressource" 
        severity="help" 
        class="rounded-xl px-5 py-3"
        @click="$inertia.visit(route('assignments.index'))"
    />
</template>

                    <div class="flex flex-wrap gap-3">
                        <Dialog
                            v-model:visible="editDialogVisible"
                            modal
                            header="Modifier la campagne"
                            :style="{ width: '50rem' }"
                        >
                            <form
                                @submit.prevent="submitEdit"
                                class="space-y-6"
                            >
                                <div>
                                    <label
                                        for="edit-name"
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                        >Nom</label
                                    >
                                    <InputText
                                        id="edit-name"
                                        v-model="editForm.name"
                                        class="w-full"
                                        required
                                    />
                                    <small
                                        v-if="editForm.errors.name"
                                        class="text-red-500"
                                        >{{ editForm.errors.name }}</small
                                    >
                                </div>

                                <div>
                                    <label
                                        for="edit-description"
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                        >Description</label
                                    >
                                    <Textarea
                                        id="edit-description"
                                        v-model="editForm.description"
                                        class="w-full"
                                        rows="4"
                                        required
                                    />
                                    <small
                                        v-if="editForm.errors.description"
                                        class="text-red-500"
                                        >{{
                                            editForm.errors.description
                                        }}</small
                                    >
                                </div>

                                <div>
                                    <label
                                        for="edit-start_date"
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                        >Date de début</label
                                    >
                                    <Calendar
                                        id="edit-start_date"
                                        v-model="editForm.start_date"
                                        class="w-full"
                                        dateFormat="dd/mm/yy"
                                        required
                                    />
                                    <small
                                        v-if="editForm.errors.start_date"
                                        class="text-red-500"
                                        >{{ editForm.errors.start_date }}</small
                                    >
                                </div>

                                <div>
                                    <label
                                        for="edit-end_date"
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                        >Date de fin</label
                                    >
                                    <Calendar
                                        id="edit-end_date"
                                        v-model="editForm.end_date"
                                        class="w-full"
                                        dateFormat="dd/mm/yy"
                                        required
                                    />
                                    <small
                                        v-if="editForm.errors.end_date"
                                        class="text-red-500"
                                        >{{ editForm.errors.end_date }}</small
                                    >
                                </div>

                                <div>
                                    <label
                                        for="edit-status"
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                        >Statut</label
                                    >
                                    <Dropdown
                                        id="edit-status"
                                        v-model="editForm.status"
                                        :options="statusOptions"
                                        optionLabel="label"
                                        optionValue="value"
                                        class="w-full"
                                    />
                                    <small
                                        v-if="editForm.errors.status"
                                        class="text-red-500"
                                        >{{ editForm.errors.status }}</small
                                    >
                                </div>
                            </form>

                            <template #footer>
                                <Button
                                    label="Annuler"
                                    icon="pi pi-times"
                                    severity="secondary"
                                    @click="editDialogVisible = false"
                                />
                                <Button
                                    label="Mettre à jour"
                                    icon="pi pi-check"
                                    @click="submitEdit"
                                    :loading="editForm.processing"
                                />
                            </template>
                        </Dialog>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-4">
                <div
                    class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <span
                        class="text-sm font-semibold uppercase tracking-wide text-slate-400"
                        >Total Ressources</span
                    >
                    <p class="mt-4 text-3xl font-bold text-slate-900">
                        {{ summary.total_resources }}
                    </p>
                </div>
                <div
                    class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <span
                        class="text-sm font-semibold uppercase tracking-wide text-slate-400"
                        >Chefs de Plateau</span
                    >
                    <p class="mt-4 text-3xl font-bold text-slate-900">
                        {{ summary.cp_count }}
                    </p>
                </div>
                <div
                    class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <span
                        class="text-sm font-semibold uppercase tracking-wide text-slate-400"
                        >Superviseurs</span
                    >
                    <p class="mt-4 text-3xl font-bold text-slate-900">
                        {{ summary.sup_count }}
                    </p>
                </div>
                <div
                    class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <span
                        class="text-sm font-semibold uppercase tracking-wide text-slate-400"
                        >Téléconseillers</span
                    >
                    <p class="mt-4 text-3xl font-bold text-emerald-700">
                        {{ summary.tc_count }}
                    </p>
                </div>
            </div>

            <div
                class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm"
            >
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h2 class="text-xl font-semibold text-slate-900">
                        Organisation hiérarchique
                    </h2>
                </div>
                <div class="space-y-4">
                    <template v-for="node in hierarchy" :key="node.id">
                        <HierarchyNode :node="node" :level="0" />
                    </template>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
