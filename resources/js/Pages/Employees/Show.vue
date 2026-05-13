<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Dropdown from 'primevue/dropdown';
import { useToast } from 'primevue/usetoast';
import Toast from 'primevue/toast';

const props = defineProps({
    employee: Object,
    from: String
});

const toast = useToast();
const loading = ref(false);
const deactivateDialog = ref(false);
const replacementId = ref(null);
const availableReplacements = ref([]);

const confirmDeactivate = async () => {
  replacementId.value = null;
  availableReplacements.value = [];
  
  try {
    const response = await fetch(`/employees/${props.employee.id}/replacements`, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
    if (response.ok) {
      availableReplacements.value = await response.json();
    }
  } catch (err) {
    console.error('Erreur chargement remplaçants:', err);
  }
  
  deactivateDialog.value = true;
};

const deactivateEmployee = async () => {
  loading.value = true;
  try {
    const response = await fetch(`/employees/${props.employee.id}`, {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify({
        replacement_id: replacementId.value
      })
    });
    const data = await response.json();
    if (!response.ok) {
      throw new Error(data.error || 'Erreur de désactivation');
    }
    
    toast.add({ severity: 'success', summary: 'Désactivé', detail: 'Employé désactivé', life: 3000 });
    deactivateDialog.value = false;
    
    // Rediriger ou rafraîchir
    router.reload();
  } catch (err) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: err.message || 'Impossible de désactiver', life: 3000 });
  } finally {
    loading.value = false;
  }
};

const backRoute = computed(() => {
    return props.from === 'assignments' ? route('assignments.index') : route('employees.gestion');
});

const backLabel = computed(() => {
    return props.from === 'assignments' ? 'Retour aux affectations' : 'Retour aux employés';
});

const formatDate = (dateString) => {
    if (!dateString) return '—';
    return new Date(dateString).toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('fr-BJ', {
        style: 'currency',
        currency: 'XOF'
    }).format(value || 0);
};

const activeAssignment = computed(() => {
    return props.employee.assignments?.find(a => a.status === 'actif');
});

const historyAssignments = computed(() => {
    return props.employee.assignments?.filter(a => a.status !== 'actif') || [];
});

const getStatusClass = (status) => {
    switch (status) {
        case 'actif': return 'bg-teal-100 text-teal-700';
        case 'inactif': return 'bg-slate-100 text-slate-600';
        case 'suspendu': return 'bg-rose-100 text-rose-700';
        default: return 'bg-slate-50 text-slate-500';
    }
};
</script>

<template>
    <Head :title="`Profil - ${employee.first_name} ${employee.last_name}`" />

    <AuthenticatedLayout>
        <div class="py-6 max-w-5xl mx-auto space-y-8">
            
            <!-- Breadcrumb & Actions -->
            <div class="flex items-center justify-between">
                <Link :href="backRoute" class="inline-flex items-center gap-2 text-xs font-bold text-slate-400 hover:text-teal-600 uppercase tracking-widest transition-colors">
                    <i class="pi pi-arrow-left text-[10px]"></i>
                    {{ backLabel }}
                </Link>
                <div class="flex gap-3">
                    <button v-if="employee.status === 'actif'" @click="confirmDeactivate" class="px-5 py-2.5 bg-rose-50 border border-rose-100 text-rose-600 rounded-xl font-bold text-xs hover:bg-rose-100 transition-all shadow-sm">
                        <i class="pi pi-user-minus mr-2"></i> Désactiver l'employé
                    </button>
                    <button @click="$inertia.visit(route('employees.gestion'))" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl font-bold text-xs hover:border-teal-500 hover:text-teal-600 transition-all shadow-sm">
                        <i class="pi pi-pencil mr-2"></i> Modifier le profil
                    </button>
                </div>
            </div>

            <Toast />

            <!-- Deactivate Dialog -->
            <Dialog v-model:visible="deactivateDialog" header="Confirmer la Désactivation" modal class="rounded-3xl shadow-2xl border-none" :style="{ width: '400px' }"
                :pt="{ header: { class: 'bg-slate-50 p-6 rounded-t-3xl border-b border-slate-100' }, content: { class: 'p-8 bg-white' }, footer: { class: 'p-6 bg-slate-50 rounded-b-3xl border-t border-slate-100' } }">
                <div class="space-y-6 text-center">
                    <div class="h-16 w-16 bg-rose-50 rounded-full flex items-center justify-center text-rose-500 mx-auto">
                        <i class="pi pi-user-minus text-2xl"></i>
                    </div>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Libérer <span class="font-black text-slate-900">"{{ employee.first_name }} {{ employee.last_name }}"</span> ?
                    </p>

                    <div v-if="availableReplacements.length > 0" class="space-y-4 pt-4 border-t border-slate-100 text-left">
                        <div class="flex flex-col gap-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Remplacer par (Optionnel)</label>
                            <Dropdown v-model="replacementId" :options="availableReplacements" 
                                :optionLabel="(e) => `${e.first_name} ${e.last_name} (${e.matricule})`" 
                                optionValue="id" 
                                placeholder="Choisir un remplaçant..." 
                                class="w-full rounded-xl border-slate-200" filter showClear />
                            <p class="text-[10px] text-slate-400 italic leading-tight">
                                Si sélectionné, tous les subordonnés seront automatiquement transférés au nouveau responsable.
                            </p>
                        </div>
                    </div>
                </div>
                <template #footer>
                    <div class="flex gap-3 w-full">
                        <Button label="Annuler" class="flex-1 p-button-text p-button-secondary font-black text-xs uppercase" @click="deactivateDialog = false" />
                        <Button :label="replacementId ? 'Remplacer' : 'Désactiver'" class="flex-1 bg-rose-600 border-none font-black text-xs uppercase p-3 rounded-xl shadow-lg shadow-rose-600/20" @click="deactivateEmployee" :loading="loading" />
                    </div>
                </template>
            </Dialog>

            <!-- Header Profile Card -->
            <div class="bg-white rounded-3xl border border-slate-100 p-8 shadow-sm relative overflow-hidden">
                <div class="absolute top-0 right-0 p-12 opacity-5 pointer-events-none">
                    <i class="pi pi-user text-[180px] text-slate-900 rotate-12"></i>
                </div>
                
                <div class="relative z-10 flex flex-col md:flex-row items-center gap-8">
                    <div class="h-32 w-32 rounded-[32px] bg-teal-600 flex items-center justify-center text-white text-4xl font-black shadow-2xl shadow-teal-600/30 ring-4 ring-teal-50">
                        {{ employee.first_name[0] }}{{ employee.last_name[0] }}
                    </div>
                    
                    <div class="flex-1 text-center md:text-left">
                        <div class="flex flex-wrap items-center justify-center md:justify-start gap-4 mb-2">
                            <h1 class="text-4xl font-black text-slate-900 tracking-tight">
                                {{ employee.first_name }} {{ employee.last_name }}
                            </h1>
                            <span :class="['rounded-lg px-3 py-1 text-[10px] font-black uppercase tracking-widest', getStatusClass(employee.status)]">
                                {{ employee.status }}
                            </span>
                        </div>
                        <p class="text-slate-400 font-bold uppercase tracking-[0.2em] text-xs flex items-center justify-center md:justify-start gap-2">
                            <i class="pi pi-id-card text-teal-500"></i>
                            Matricule #{{ employee.matricule }}
                        </p>
                        
                        <div class="mt-6 flex flex-wrap justify-center md:justify-start gap-3">
                            <div class="px-4 py-2 bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-slate-900/10">
                                {{ employee.position?.name || 'Poste non défini' }}
                            </div>
                            <div v-if="activeAssignment" class="px-4 py-2 bg-teal-50 text-teal-700 border border-teal-100 rounded-xl text-[10px] font-black uppercase tracking-widest">
                                <i class="pi pi-briefcase mr-1.5"></i> {{ activeAssignment.campaign?.name }}
                            </div>
                            <div v-else class="px-4 py-2 bg-rose-50 text-rose-600 border border-rose-100 rounded-xl text-[10px] font-black uppercase tracking-widest">
                                <i class="pi pi-exclamation-circle mr-1.5"></i> Non assigné
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Left Column: Personal & Pro Info -->
                <div class="lg:col-span-2 space-y-8">
                    
                    <!-- Details Sections -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Personnel -->
                        <div class="bg-white rounded-3xl border border-slate-100 p-8 shadow-sm">
                            <h3 class="text-xs font-black text-slate-900 uppercase tracking-widest mb-8 flex items-center gap-3">
                                <span class="h-6 w-1 bg-teal-500 rounded-full"></span>
                                Informations Personnelles
                            </h3>
                            <div class="space-y-6">
                                <div>
                                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] block mb-1.5">Email professionnel</label>
                                    <p class="text-sm font-bold text-slate-700">{{ employee.email || '—' }}</p>
                                </div>
                                <div>
                                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] block mb-1.5">Téléphone</label>
                                    <p class="text-sm font-bold text-slate-700">{{ employee.phone || '—' }}</p>
                                </div>
                                <div>
                                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] block mb-1.5">Adresse de résidence</label>
                                    <p class="text-sm font-bold text-slate-700 leading-relaxed">{{ employee.address || '—' }}</p>
                                </div>
                                <div>
                                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] block mb-1.5">Date de naissance</label>
                                    <p class="text-sm font-bold text-slate-700">{{ formatDate(employee.birth_date) }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Professionnel -->
                        <div class="bg-white rounded-3xl border border-slate-100 p-8 shadow-sm">
                            <h3 class="text-xs font-black text-slate-900 uppercase tracking-widest mb-8 flex items-center gap-3">
                                <span class="h-6 w-1 bg-slate-900 rounded-full"></span>
                                Contrat & Carrière
                            </h3>
                            <div class="space-y-6">
                                <div>
                                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] block mb-1.5">Salaire de base</label>
                                    <p class="text-xl font-black text-teal-600">{{ formatCurrency(employee.salary_base) }}</p>
                                </div>
                                <div>
                                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] block mb-1.5">Poste occupé</label>
                                    <p class="text-sm font-bold text-slate-700">{{ employee.position?.name || '—' }}</p>
                                </div>
                                <div>
                                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] block mb-1.5">Date d'embauche</label>
                                    <p class="text-sm font-bold text-slate-700">{{ formatDate(employee.created_at) }}</p>
                                </div>
                                <div>
                                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] block mb-1.5">Ancienneté</label>
                                    <p class="text-sm font-bold text-slate-700 italic">Membre depuis {{ new Date().getFullYear() - new Date(employee.created_at).getFullYear() }} an(s)</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Assignments History -->
                    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="px-8 py-6 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                            <h3 class="text-xs font-black text-slate-900 uppercase tracking-widest flex items-center gap-3">
                                <i class="pi pi-history text-teal-500"></i>
                                Historique des affectations
                            </h3>
                            <span class="text-[10px] font-black text-slate-400 bg-white px-2.5 py-1 rounded-lg border border-slate-100 shadow-sm">
                                {{ employee.assignments?.length || 0 }} AU TOTAL
                            </span>
                        </div>
                        <div class="p-8">
                            <div v-if="!employee.assignments?.length" class="py-12 text-center text-slate-300">
                                <i class="pi pi-inbox text-4xl mb-4 opacity-20"></i>
                                <p class="font-bold text-xs uppercase tracking-widest">Aucun historique disponible</p>
                            </div>
                            <div v-else class="space-y-4">
                                <div v-for="assign in employee.assignments" :key="assign.id" 
                                    class="flex items-center gap-4 p-4 rounded-2xl border border-slate-50 hover:border-teal-100 hover:bg-teal-50/30 transition-all group">
                                    <div :class="['h-10 w-10 rounded-xl flex items-center justify-center text-white font-black text-[10px]', assign.status === 'actif' ? 'bg-teal-600' : 'bg-slate-200']">
                                        {{ assign.position?.name?.charAt(0) || '?' }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-black text-slate-800">{{ assign.campaign?.name }}</p>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                            {{ formatDate(assign.start_date) }} — {{ formatDate(assign.end_date) }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <span :class="['rounded-lg px-2.5 py-1 text-[8px] font-black uppercase tracking-widest', getStatusClass(assign.status)]">
                                            {{ assign.status }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Manager & Statistics -->
                <div class="space-y-8">
                    
                    <!-- Current Manager -->
                    <div v-if="activeAssignment && activeAssignment.manager" class="bg-slate-900 rounded-3xl p-8 text-white shadow-xl relative overflow-hidden group">
                        <div class="absolute -right-6 -bottom-6 opacity-10 group-hover:scale-110 transition-transform duration-500">
                            <i class="pi pi-user text-[120px]"></i>
                        </div>
                        <h3 class="text-[10px] font-black uppercase tracking-[0.3em] text-teal-400 mb-8 flex items-center gap-3">
                            <span class="h-1.5 w-1.5 rounded-full bg-teal-400"></span>
                            Manager Direct
                        </h3>
                        <div class="flex items-center gap-4 mb-6">
                            <div class="h-14 w-14 rounded-2xl bg-white/10 border border-white/10 flex items-center justify-center text-xl font-black">
                                {{ activeAssignment.manager.first_name[0] }}{{ activeAssignment.manager.last_name[0] }}
                            </div>
                            <div>
                                <p class="font-black text-lg">{{ activeAssignment.manager.first_name }} {{ activeAssignment.manager.last_name }}</p>
                                <p class="text-[10px] font-bold text-teal-400 uppercase tracking-widest">
                                    {{ activeAssignment.manager.position?.name || 'Responsable' }}
                                </p>
                            </div>
                        </div>
                        <Link :href="route('employees.show', activeAssignment.manager_id)" class="block w-full py-3 bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl text-center text-[10px] font-black uppercase tracking-widest transition-all">
                            Voir le profil
                        </Link>
                    </div>

                    <!-- Quick Stats -->
                    <div class="bg-white rounded-3xl border border-slate-100 p-8 shadow-sm">
                        <h3 class="text-xs font-black text-slate-900 uppercase tracking-widest mb-8 flex items-center gap-3">
                            <i class="pi pi-chart-bar text-teal-500"></i>
                            Performance
                        </h3>
                        <div class="space-y-6">
                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Heures</p>
                                <p class="text-2xl font-black text-slate-900">0h <span class="text-xs font-bold text-slate-300 ml-1">réelles</span></p>
                            </div>
                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Taux de présence</p>
                                <p class="text-2xl font-black text-slate-900">0% <span class="text-xs font-bold text-slate-300 ml-1">mensuel</span></p>
                            </div>
                        </div>
                        <div class="mt-8 pt-8 border-t border-slate-50">
                            <button class="w-full py-3 bg-teal-50 text-teal-600 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-teal-100 transition-all">
                                Consulter le planning
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
