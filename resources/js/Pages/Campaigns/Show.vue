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
        <div class="max-w-6xl mx-auto py-6 space-y-8">
            <!-- Header Section -->
            <div class="bg-white rounded-3xl border border-slate-100 p-8 shadow-sm">
                <div class="flex flex-col gap-8 lg:flex-row lg:items-start lg:justify-between">
                    <div class="flex-1">
                        <Link
                            :href="route('campaigns.index')"
                            class="inline-flex items-center gap-2 text-xs font-bold text-slate-400 hover:text-teal-600 uppercase tracking-widest transition-colors mb-6"
                        >
                            <i class="pi pi-arrow-left text-[10px]"></i>
                            Retour aux campagnes
                        </Link>
                        
                        <div class="flex flex-wrap items-center gap-4 mb-4">
                            <h1 class="text-4xl font-black text-slate-900 tracking-tight">
                                {{ campaign.name }}
                            </h1>
                            <span
                                :class="[
                                    'rounded-lg px-3 py-1 text-[10px] font-black uppercase tracking-widest',
                                    campaign.status === 'active' ? 'bg-teal-100 text-teal-700' : 'bg-slate-100 text-slate-700'
                                ]"
                            >
                                {{ campaign.status }}
                            </span>
                        </div>
                        
                        <p class="text-slate-500 leading-relaxed max-w-2xl font-medium">
                            {{ campaign.description }}
                        </p>
                        
                        <div class="mt-8 flex flex-wrap items-center gap-6">
                            <div class="flex items-center gap-2 px-4 py-2 bg-slate-50 rounded-xl border border-slate-100 text-xs font-bold text-slate-600">
                                <i class="pi pi-calendar text-teal-500"></i>
                                {{ formatDate(campaign.start_date) }} — {{ formatDate(campaign.end_date) }}
                            </div>
                        </div>
                    </div>

                    <div v-if="isAdmin" class="flex flex-wrap gap-3 shrink-0">
                        <Button 
                            @click="openEditDialog(campaign)" 
                            label="Modifier" 
                            icon="pi pi-pencil"
                            class="bg-white border border-slate-200 text-slate-700 hover:border-teal-500 hover:text-teal-600 px-6 py-3 rounded-xl font-bold transition-all" 
                        />
                        <Button 
                            label="Affecter une ressource" 
                            icon="pi pi-user-plus"
                            class="bg-teal-600 hover:bg-teal-700 text-white border-none px-6 py-3 rounded-xl font-bold shadow-lg shadow-teal-600/20 transition-all"
                            @click="$inertia.visit(route('assignments.index'))"
                        />
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white rounded-3xl border border-slate-100 p-6 shadow-sm flex flex-col gap-1 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-3 opacity-5 group-hover:scale-110 transition-transform">
                        <i class="pi pi-users text-4xl text-slate-900"></i>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Total Ressources</span>
                    <p class="text-3xl font-black text-slate-800">{{ summary.total_resources }}</p>
                </div>
                <div class="bg-white rounded-3xl border border-slate-100 p-6 shadow-sm flex flex-col gap-1 relative overflow-hidden group border-l-4 border-l-slate-900">
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Chef de Plateau</span>
                    <p class="text-3xl font-black text-slate-800">{{ summary.cp_count }}</p>
                </div>
                <div class="bg-white rounded-3xl border border-slate-100 p-6 shadow-sm flex flex-col gap-1 relative overflow-hidden group border-l-4 border-l-teal-600">
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Superviseurs</span>
                    <p class="text-3xl font-black text-teal-600">{{ summary.sup_count }}</p>
                </div>
                <div class="bg-white rounded-3xl border border-slate-100 p-6 shadow-sm flex flex-col gap-1 relative overflow-hidden group border-l-4 border-l-teal-500">
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Téléconseillers</span>
                    <p class="text-3xl font-black text-teal-500">{{ summary.tc_count }}</p>
                </div>
            </div>

            <!-- Hierarchy Section -->
            <div class="bg-white rounded-3xl border border-slate-100 p-8 shadow-sm">
                <div class="mb-8 flex items-center justify-between">
                    <h2 class="text-xl font-black text-slate-800 flex items-center gap-3">
                        <i class="pi pi-sitemap text-teal-500"></i>
                        Organisation hiérarchique
                    </h2>
                </div>
                <div class="space-y-6">
                    <template v-for="node in hierarchy" :key="node.id">
                        <HierarchyNode :node="node" :level="0" />
                    </template>
                    <div v-if="!hierarchy.length" class="text-center py-12 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                        <i class="pi pi-inbox text-3xl text-slate-300 mb-2"></i>
                        <p class="text-slate-400 font-medium">Aucune ressource affectée pour le moment.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Dialog (Styled like Create) -->
        <Dialog
            v-model:visible="editDialogVisible"
            modal
            header="Modifier la Campagne"
            class="rounded-3xl shadow-2xl border-none"
            :style="{ width: '450px' }"
            :pt="{
                header: { class: 'bg-slate-50 p-6 rounded-t-3xl border-b border-slate-100' },
                content: { class: 'p-8 bg-white' },
                footer: { class: 'p-6 bg-slate-50 rounded-b-3xl border-t border-slate-100' }
            }"
        >
            <form @submit.prevent="submitEdit" class="space-y-5">
                <div class="flex flex-col gap-2">
                    <label for="edit-name" class="text-xs font-bold text-slate-500 uppercase tracking-widest">Nom de la campagne</label>
                    <InputText id="edit-name" v-model="editForm.name" class="w-full p-3 rounded-xl border-slate-200 focus:border-teal-500 focus:ring-teal-500" required />
                </div>

                <div class="flex flex-col gap-2">
                    <label for="edit-description" class="text-xs font-bold text-slate-500 uppercase tracking-widest">Description</label>
                    <Textarea id="edit-description" v-model="editForm.description" class="w-full p-3 rounded-xl border-slate-200 focus:border-teal-500 focus:ring-teal-500" rows="3" required />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label for="edit-start_date" class="text-xs font-bold text-slate-500 uppercase tracking-widest">Début</label>
                        <Calendar id="edit-start_date" v-model="editForm.start_date" class="w-full" inputClass="p-3 rounded-xl border-slate-200" dateFormat="dd/mm/yy" required />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="edit-end_date" class="text-xs font-bold text-slate-500 uppercase tracking-widest">Fin</label>
                        <Calendar id="edit-end_date" v-model="editForm.end_date" class="w-full" inputClass="p-3 rounded-xl border-slate-200" dateFormat="dd/mm/yy" required />
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="edit-status" class="text-xs font-bold text-slate-500 uppercase tracking-widest">Statut</label>
                    <Dropdown id="edit-status" v-model="editForm.status" :options="statusOptions" optionLabel="label" optionValue="value" class="w-full rounded-xl border-slate-200" />
                </div>
            </form>

            <template #footer>
                <div class="flex gap-3 w-full">
                    <Button label="Annuler" class="flex-1 p-button-text p-button-secondary font-bold text-xs" @click="editDialogVisible = false" />
                    <Button label="Mettre à jour" class="flex-1 bg-teal-600 border-none font-bold text-xs p-3 rounded-xl shadow-lg shadow-teal-600/20" @click="submitEdit" :loading="editForm.processing" />
                </div>
            </template>
        </Dialog>
    </AuthenticatedLayout>
</template>
