<script setup>
import { ref } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import InputText from "primevue/inputtext";
import Textarea from "primevue/textarea";
import Calendar from "primevue/calendar";
import Dropdown from "primevue/dropdown";

const props = defineProps({
    campaigns: Array,
    isAdmin: Boolean,
});

const filters = ["Toutes", "Actives", "Inactives", "Terminées"];
const activeFilter = ref("Toutes");

const createDialogVisible = ref(false);
const editDialogVisible = ref(false);
const editingCampaign = ref(null);
const deactivateDialogVisible = ref(false);
const campaignToDeactivate = ref(null);
const deactivateForm = useForm({});
const createForm = useForm({
    name: "",
    description: "",
    start_date: null,
    end_date: null,
    status: "active",
});

const editForm = useForm({
    name: "",
    description: "",
    start_date: null,
    end_date: null,
    status: "active",
});

const statusOptions = [
    { label: "Active", value: "active" },
    { label: "Inactive", value: "inactive" },
    { label: "Terminée", value: "terminée" },
];

const getStatusClass = (status) => {
    switch (status) {
        case "active":
            return "bg-teal-100 text-teal-700";
        case "inactive":
            return "bg-slate-100 text-slate-700";
        case "terminée":
            return "bg-slate-200 text-slate-800";
        default:
            return "bg-slate-100 text-slate-700";
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("fr-FR", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
    });
};

const openCreateDialog = () => {
    createForm.reset();
    createDialogVisible.value = true;
};

const openEditDialog = (campaign) => {
    editingCampaign.value = campaign;
    editForm.name = campaign.name;
    editForm.description = campaign.description;
    editForm.start_date = new Date(campaign.start_date);
    editForm.end_date = new Date(campaign.end_date);
    editForm.status = campaign.status;
    editDialogVisible.value = true;
};

const submitCreate = () => {
    createForm.post(route("campaigns.store"), {
        onSuccess: () => {
            createDialogVisible.value = false;
        },
    });
};

const submitEdit = () => {
    editForm.put(route("campaigns.update", editingCampaign.value.id), {
        onSuccess: () => {
            editDialogVisible.value = false;
        },
    });
};
const openDeactivateDialog = (campaign) => {
    campaignToDeactivate.value = campaign;
    deactivateDialogVisible.value = true;
};

const confirmDeactivate = () => {
    deactivateForm.delete(
        route("campaigns.destroy", campaignToDeactivate.value.id),
        {
            onSuccess: () => {
                deactivateDialogVisible.value = false;
            },
        },
    );
};
</script>

<template>
    <Head title="Campagnes" />
    <AuthenticatedLayout>
        <div class="py-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">Campagnes</h1>
                    <p class="mt-1 text-sm text-slate-500 font-medium">
                        Pilotez vos opérations et suivez l'affectation de vos ressources.
                    </p>
                </div>
                <Button
                    v-if="isAdmin"
                    @click="openCreateDialog"
                    class="bg-teal-600 hover:bg-teal-700 text-white px-6 py-3 rounded-xl shadow-lg shadow-teal-600/20 border-none font-bold transition-all"
                >
                    <i class="pi pi-plus mr-2"></i> Nouvelle campagne
                </Button>
            </div>

            <div class="grid gap-8 xl:grid-cols-3 lg:grid-cols-2">
                <div
                    v-for="campaign in campaigns"
                    :key="campaign.id"
                    class="rounded-3xl border border-slate-100 bg-white p-8 shadow-sm hover:shadow-xl hover:shadow-slate-200/50 transition-all duration-300 group"
                >
                    <div class="flex items-start justify-between gap-4 mb-6">
                        <div class="flex-1">
                            <h2 class="text-xl font-black text-slate-800 group-hover:text-teal-600 transition-colors">
                                {{ campaign.name }}
                            </h2>
                            <p class="mt-2 text-xs leading-relaxed text-slate-400 font-medium line-clamp-2">
                                {{ campaign.description }}
                            </p>
                        </div>
                        <span
                            :class="[
                                'rounded-lg px-3 py-1 text-[10px] font-black uppercase tracking-widest',
                                getStatusClass(campaign.status),
                            ]"
                        >
                            {{ campaign.status }}
                        </span>
                    </div>

                    <div class="flex items-center gap-4 py-4 border-y border-slate-50 mb-6">
                        <div class="flex items-center gap-2 text-xs font-bold text-slate-500">
                            <i class="pi pi-calendar text-teal-500"></i>
                            {{ formatDate(campaign.start_date) }}
                        </div>
                        <i class="pi pi-arrow-right text-[10px] text-slate-300"></i>
                        <div class="flex items-center gap-2 text-xs font-bold text-slate-500">
                            {{ formatDate(campaign.end_date) }}
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3 mb-8">
                        <div class="flex flex-col items-center justify-center rounded-2xl bg-slate-50 py-3 px-2 border border-slate-100">
                            <span class="text-lg font-black text-slate-800">{{ campaign.cp_count }}</span>
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">CP</span>
                        </div>
                        <div class="flex flex-col items-center justify-center rounded-2xl bg-slate-50 py-3 px-2 border border-slate-100">
                            <span class="text-lg font-black text-slate-800">{{ campaign.sup_count }}</span>
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">SUP</span>
                        </div>
                        <div class="flex flex-col items-center justify-center rounded-2xl bg-slate-50 py-3 px-2 border border-slate-100">
                            <span class="text-lg font-black text-slate-800">{{ campaign.tc_count }}</span>
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">TC</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <Link
                            :href="route('campaigns.show', campaign.id)"
                            class="flex-1 inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-3 text-xs font-bold text-white hover:bg-slate-800 transition-all shadow-lg shadow-slate-900/10"
                        >
                            Détails
                        </Link>
                        <template v-if="isAdmin">
                            <Button
                                @click="openEditDialog(campaign)"
                                icon="pi pi-pencil"
                                class="h-10 w-10 p-button-rounded p-button-secondary p-button-outlined border-slate-200 text-slate-600 hover:text-teal-600 hover:border-teal-200 transition-all"
                            />
                            <Button
                                @click="openDeactivateDialog(campaign)"
                                icon="pi pi-ban"
                                class="h-10 w-10 p-button-rounded p-button-danger p-button-outlined border-slate-200 text-slate-400 hover:text-rose-600 hover:border-rose-200 transition-all"
                                :disabled="campaign.status === 'inactive'"
                            />
                        </template>
                    </div>
                </div>
            </div>

            <!-- Create Dialog -->
            <Dialog
                v-model:visible="createDialogVisible"
                modal
                header="Nouvelle Campagne"
                class="rounded-3xl shadow-2xl border-none"
                :style="{ width: '450px' }"
                :pt="{
                    header: { class: 'bg-slate-50 p-6 rounded-t-3xl border-b border-slate-100' },
                    content: { class: 'p-8 bg-white' },
                    footer: { class: 'p-6 bg-slate-50 rounded-b-3xl border-t border-slate-100' }
                }"
            >
                <form @submit.prevent="submitCreate" class="space-y-5">
                    <div class="flex flex-col gap-2">
                        <label for="create-name" class="text-xs font-bold text-slate-500 uppercase tracking-widest">Nom de la campagne</label>
                        <InputText id="create-name" v-model="createForm.name" class="w-full p-3 rounded-xl border-slate-200 focus:border-teal-500 focus:ring-teal-500" required placeholder="Ex: Campagne Hiver 2024" />
                        <small v-if="createForm.errors.name" class="text-rose-500 text-[10px] font-bold">{{ createForm.errors.name }}</small>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label for="create-description" class="text-xs font-bold text-slate-500 uppercase tracking-widest">Description</label>
                        <Textarea id="create-description" v-model="createForm.description" class="w-full p-3 rounded-xl border-slate-200 focus:border-teal-500 focus:ring-teal-500" rows="3" required placeholder="Objectifs et contexte..." />
                        <small v-if="createForm.errors.description" class="text-rose-500 text-[10px] font-bold">{{ createForm.errors.description }}</small>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-2">
                            <label for="create-start_date" class="text-xs font-bold text-slate-500 uppercase tracking-widest">Début</label>
                            <Calendar id="create-start_date" v-model="createForm.start_date" class="w-full" inputClass="p-3 rounded-xl border-slate-200" dateFormat="dd/mm/yy" required />
                            <small v-if="createForm.errors.start_date" class="text-rose-500 text-[10px] font-bold">{{ createForm.errors.start_date }}</small>
                        </div>
                        <div class="flex flex-col gap-2">
                            <label for="create-end_date" class="text-xs font-bold text-slate-500 uppercase tracking-widest">Fin</label>
                            <Calendar id="create-end_date" v-model="createForm.end_date" class="w-full" inputClass="p-3 rounded-xl border-slate-200" dateFormat="dd/mm/yy" required />
                            <small v-if="createForm.errors.end_date" class="text-rose-500 text-[10px] font-bold">{{ createForm.errors.end_date }}</small>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label for="create-status" class="text-xs font-bold text-slate-500 uppercase tracking-widest">Statut initial</label>
                        <Dropdown id="create-status" v-model="createForm.status" :options="statusOptions" optionLabel="label" optionValue="value" class="w-full rounded-xl border-slate-200" />
                        <small v-if="createForm.errors.status" class="text-rose-500 text-[10px] font-bold">{{ createForm.errors.status }}</small>
                    </div>
                </form>

                <template #footer>
                    <div class="flex gap-3 w-full">
                        <Button label="Annuler" class="flex-1 p-button-text p-button-secondary font-bold text-xs" @click="createDialogVisible = false" />
                        <Button label="Confirmer" class="flex-1 bg-teal-600 border-none font-bold text-xs p-3 rounded-xl shadow-lg shadow-teal-600/20" @click="submitCreate" :loading="createForm.processing" />
                    </div>
                </template>
            </Dialog>

            <!-- Edit Dialog -->
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
                        <small v-if="editForm.errors.name" class="text-rose-500 text-[10px] font-bold">{{ editForm.errors.name }}</small>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label for="edit-description" class="text-xs font-bold text-slate-500 uppercase tracking-widest">Description</label>
                        <Textarea id="edit-description" v-model="editForm.description" class="w-full p-3 rounded-xl border-slate-200 focus:border-teal-500 focus:ring-teal-500" rows="3" required />
                        <small v-if="editForm.errors.description" class="text-rose-500 text-[10px] font-bold">{{ editForm.errors.description }}</small>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-2">
                            <label for="edit-start_date" class="text-xs font-bold text-slate-500 uppercase tracking-widest">Début</label>
                            <Calendar id="edit-start_date" v-model="editForm.start_date" class="w-full" inputClass="p-3 rounded-xl border-slate-200" dateFormat="dd/mm/yy" required />
                            <small v-if="editForm.errors.start_date" class="text-rose-500 text-[10px] font-bold">{{ editForm.errors.start_date }}</small>
                        </div>
                        <div class="flex flex-col gap-2">
                            <label for="edit-end_date" class="text-xs font-bold text-slate-500 uppercase tracking-widest">Fin</label>
                            <Calendar id="edit-end_date" v-model="editForm.end_date" class="w-full" inputClass="p-3 rounded-xl border-slate-200" dateFormat="dd/mm/yy" required />
                            <small v-if="editForm.errors.end_date" class="text-rose-500 text-[10px] font-bold">{{ editForm.errors.end_date }}</small>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label for="edit-status" class="text-xs font-bold text-slate-500 uppercase tracking-widest">Statut</label>
                        <Dropdown id="edit-status" v-model="editForm.status" :options="statusOptions" optionLabel="label" optionValue="value" class="w-full rounded-xl border-slate-200" />
                        <small v-if="editForm.errors.status" class="text-rose-500 text-[10px] font-bold">{{ editForm.errors.status }}</small>
                    </div>
                </form>

                <template #footer>
                    <div class="flex gap-3 w-full">
                        <Button label="Annuler" class="flex-1 p-button-text p-button-secondary font-bold text-xs" @click="editDialogVisible = false" />
                        <Button label="Mettre à jour" class="flex-1 bg-teal-600 border-none font-bold text-xs p-3 rounded-xl shadow-lg shadow-teal-600/20" @click="submitEdit" :loading="editForm.processing" />
                    </div>
                </template>
            </Dialog>
            <Dialog
                v-model:visible="deactivateDialogVisible"
                modal
                header="Confirmer la Désactivation"
                class="rounded-3xl shadow-2xl border-none"
                :style="{ width: '400px' }"
                :pt="{
                    header: { class: 'bg-slate-50 p-6 rounded-t-3xl border-b border-slate-100' },
                    content: { class: 'p-8 bg-white' },
                    footer: { class: 'p-6 bg-slate-50 rounded-b-3xl border-t border-slate-100' }
                }"
            >
                <div v-if="campaignToDeactivate">
                    <div
                        v-if="
                            campaignToDeactivate.cp_count +
                                campaignToDeactivate.sup_count +
                                campaignToDeactivate.tc_count >
                            0
                        "
                        class="rounded-2xl bg-rose-50 border border-rose-100 p-4 mb-6"
                    >
                        <div class="flex items-start gap-3">
                            <i class="pi pi-exclamation-circle text-rose-500 text-lg"></i>
                            <div>
                                <p class="text-xs font-black text-rose-800 uppercase tracking-wider">Attention</p>
                                <p class="text-xs text-rose-700 mt-1 font-medium">
                                    {{ campaignToDeactivate.cp_count + campaignToDeactivate.sup_count + campaignToDeactivate.tc_count }} ressources seront désassignées.
                                </p>
                            </div>
                        </div>
                    </div>

                    <p class="text-sm text-slate-600 leading-relaxed text-center">
                        Voulez-vous vraiment désactiver la campagne <br>
                        <span class="font-black text-slate-900">"{{ campaignToDeactivate.name }}"</span> ?
                    </p>
                </div>

                <template #footer>
                    <div class="flex gap-3 w-full">
                        <Button label="Annuler" class="flex-1 p-button-text p-button-secondary font-bold text-xs" @click="deactivateDialogVisible = false" />
                        <Button label="Désactiver" class="flex-1 bg-rose-600 border-none font-bold text-xs p-3 rounded-xl shadow-lg shadow-rose-600/20" @click="confirmDeactivate" :loading="deactivateForm.processing" />
                    </div>
                </template>
            </Dialog>
        </div>
    </AuthenticatedLayout>
</template>
