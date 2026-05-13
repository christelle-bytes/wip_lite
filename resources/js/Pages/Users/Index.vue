<template>
  <Head title="Utilisateurs" />
  <AuthenticatedLayout>
    <div class="py-6 space-y-8">
      <!-- Header Section -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
          <div>
              <h1 class="text-3xl font-black text-slate-900 tracking-tight">Gestion des Utilisateurs</h1>
              <p class="mt-1 text-sm text-slate-500 font-medium">Gérez les accès et les statuts des membres de la plateforme.</p>
          </div>
          <Link :href="route('users.create')" 
              class="bg-teal-600 hover:bg-teal-700 text-white px-6 py-3 rounded-xl font-bold shadow-lg shadow-teal-600/20 transition-all flex items-center gap-2">
              <i class="pi pi-user-plus"></i>
              Nouvel Utilisateur
          </Link>
      </div>

      <!-- Filters Section -->
      <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm space-y-4">
        <div class="flex flex-wrap items-end gap-4">
          <!-- Search -->
          <div class="flex-1 min-w-[200px]">
            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Rechercher</label>
            <div class="relative">
              <i class="pi pi-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
              <input v-model="filters['global'].value" type="text" placeholder="Rechercher par email, rôle..." 
                class="w-full pl-10 pr-4 py-3 bg-slate-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-teal-500/20 transition-all">
            </div>
          </div>

          <!-- Role Filter -->
          <div class="w-48">
            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Rôle</label>
            <select v-model="filters['role.id'].value" 
              class="w-full px-4 py-3 bg-slate-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-teal-500/20 transition-all">
              <option :value="null">Tous les rôles</option>
              <option v-for="role in props.roles" :key="role.id" :value="role.id">{{ role.name }}</option>
            </select>
          </div>

          <!-- Status Filter -->
          <div class="w-48">
            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">État du compte</label>
            <select v-model="filters['is_active'].value" 
              class="w-full px-4 py-3 bg-slate-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-teal-500/20 transition-all">
              <option :value="null">Tous les états</option>
              <option :value="true">Comptes Actifs</option>
              <option :value="false">Comptes Désactivés</option>
            </select>
          </div>

          <!-- Reset -->
          <button @click="resetFilters" 
            class="p-3 bg-slate-50 text-slate-400 hover:text-slate-600 rounded-xl transition-all" title="Réinitialiser les filtres">
            <i class="pi pi-filter-slash"></i>
          </button>
        </div>
      </div>

      <!-- Table Card -->
      <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden p-4">
        <DataTable :value="props.users" dataKey="id" 
          v-model:filters="filters"
          :globalFilterFields="['email', 'role.name']"
          paginator :rows="10"
          paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
          currentPageReportTemplate="Affichage de {first} à {last} sur {totalRecords} utilisateurs"
          :class="'p-datatable-sm'"
          :rowClass="(data) => !data.is_active ? 'opacity-60 grayscale-[0.5]' : ''"
          responsiveLayout="scroll">
          
          <Column field="email" header="Utilisateur (Email)" headerClass="bg-slate-50 text-[10px] font-black uppercase tracking-widest text-slate-400 px-6 py-4">
            <template #body="slotProps">
              <div class="flex items-center gap-3">
                <div :class="['h-8 w-8 rounded-lg flex items-center justify-center font-black text-[10px]', slotProps.data.is_active ? 'bg-slate-100 text-slate-500' : 'bg-slate-200 text-slate-400']">
                  {{ slotProps.data.email.charAt(0).toUpperCase() }}
                </div>
                <span :class="['text-sm font-bold', slotProps.data.is_active ? 'text-slate-700' : 'text-slate-500 line-through decoration-slate-300']">
                  {{ slotProps.data.email }}
                </span>
              </div>
            </template>
          </Column>

          <Column field="role.name" header="Rôle" headerClass="bg-slate-50 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center px-6 py-4" class="text-center">
            <template #body="slotProps">
              <span :class="['rounded-lg px-2.5 py-1 text-[9px] font-black uppercase tracking-widest', getRoleColor(slotProps.data.role?.name)]">
                {{ slotProps.data.role?.name || 'Inconnu' }}
              </span>
            </template>
          </Column>

          <Column field="must_change_password" header="Status PWD" headerClass="bg-slate-50 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center px-6 py-4" class="text-center">
            <template #body="slotProps">
              <span v-if="slotProps.data.must_change_password" class="text-[9px] font-bold text-amber-600 bg-amber-50 px-2 py-1 rounded-md border border-amber-100">
                À changer
              </span>
              <span v-else class="text-[9px] font-bold text-teal-600 bg-teal-50 px-2 py-1 rounded-md border border-teal-100">
                OK
              </span>
            </template>
          </Column>

          <Column field="is_active" header="Compte" headerClass="bg-slate-50 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center px-6 py-4" class="text-center">
            <template #body="slotProps">
              <span :class="['rounded-full px-3 py-1 text-[9px] font-black uppercase tracking-widest', slotProps.data.is_active ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-rose-50 text-rose-600 border border-rose-100']">
                {{ slotProps.data.is_active ? 'Actif' : 'Désactivé' }}
              </span>
            </template>
          </Column>

          <Column header="Actions" headerClass="bg-slate-50 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right px-6 py-4" class="text-right">
            <template #body="slotProps">
              <button v-if="slotProps.data.id !== $page.props.auth.user.id" @click="confirmToggle(slotProps.data)" 
                :class="['p-2 rounded-lg transition-all', slotProps.data.is_active ? 'text-slate-400 hover:text-rose-600 hover:bg-rose-50' : 'text-teal-500 hover:bg-teal-50']"
                :title="slotProps.data.is_active ? 'Désactiver le compte' : 'Activer le compte'">
                <i :class="['pi', slotProps.data.is_active ? 'pi-user-minus' : 'pi-user-plus']"></i>
              </button>
              <span v-else class="text-[9px] font-black text-slate-400 uppercase tracking-widest px-2 py-1 bg-slate-100 rounded-md">
                <i class="pi pi-user mr-1"></i> Vous
              </span>
            </template>
          </Column>

          <template #empty>
            <div class="px-6 py-12 text-center text-slate-400 italic">
              Aucun utilisateur trouvé.
            </div>
          </template>
        </DataTable>
      </div>
    </div>

    <!-- Confirmation Modal -->
    <Dialog v-model:visible="confirmVisible" modal :header="userToToggle?.is_active ? 'Désactiver le compte' : 'Activer le compte'" 
      class="rounded-3xl shadow-2xl border-none" :style="{ width: '400px' }"
      :pt="{ header: { class: 'bg-slate-50 p-6 rounded-t-3xl border-b border-slate-100' }, content: { class: 'p-8 bg-white' }, footer: { class: 'p-6 bg-slate-50 rounded-b-3xl border-t border-slate-100' } }">
      <div v-if="userToToggle" class="space-y-4">
        <div :class="['h-16 w-16 rounded-2xl mx-auto flex items-center justify-center mb-4', userToToggle.is_active ? 'bg-rose-50 text-rose-500' : 'bg-teal-50 text-teal-500']">
          <i :class="['pi text-2xl', userToToggle.is_active ? 'pi-user-minus' : 'pi-user-plus']"></i>
        </div>
        <p class="text-sm text-slate-600 text-center leading-relaxed">
          Êtes-vous sûr de vouloir {{ userToToggle.is_active ? 'désactiver' : 'activer' }} le compte de 
          <span class="font-black text-slate-900">{{ userToToggle.email }}</span> ?
        </p>
        <p v-if="userToToggle.is_active" class="text-[10px] text-slate-400 text-center font-medium">
          L'utilisateur ne pourra plus se connecter à la plateforme.
        </p>
      </div>
      <template #footer>
        <div class="flex gap-3 w-full">
          <Button label="Annuler" class="flex-1 p-button-text p-button-secondary font-black text-xs uppercase" @click="confirmVisible = false" />
          <Button :label="userToToggle?.is_active ? 'Désactiver' : 'Activer'" 
            :class="['flex-1 border-none font-black text-xs uppercase p-3 rounded-xl shadow-lg transition-all', userToToggle?.is_active ? 'bg-rose-600 shadow-rose-600/20' : 'bg-teal-600 shadow-teal-600/20']" 
            @click="toggleStatus" />
        </div>
      </template>
    </Dialog>

  </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import { ref, reactive } from 'vue';
import { FilterMatchMode } from '@primevue/core/api';

const props = defineProps({
  users: Array,
  roles: Array,
});

// Configuration des filtres réactifs de PrimeVue
const filters = reactive({
  global: { value: null, matchMode: FilterMatchMode.CONTAINS },
  'role.id': { value: null, matchMode: FilterMatchMode.EQUALS },
  is_active: { value: null, matchMode: FilterMatchMode.EQUALS },
});

const confirmVisible = ref(false);
const userToToggle = ref(null);

const getRoleColor = (role) => {
  const name = role?.toLowerCase();
  if (name === 'admin') return 'bg-rose-100 text-rose-700';
  if (name === 'cp') return 'bg-slate-900 text-white';
  if (name === 'sup') return 'bg-teal-100 text-teal-700';
  return 'bg-slate-100 text-slate-600';
};

const resetFilters = () => {
  filters.global.value = null;
  filters['role.id'].value = null;
  filters.is_active.value = null;
};

const confirmToggle = (user) => {
  userToToggle.value = user;
  confirmVisible.value = true;
};

const toggleStatus = () => {
  router.patch(route('users.toggle-status', userToToggle.value.id), {}, {
    onSuccess: () => {
      confirmVisible.value = false;
    }
  });
};
</script>

