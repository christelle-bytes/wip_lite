<script setup>
import { ref, computed, onMounted } from 'vue';
import { useToast } from 'primevue/usetoast';
import { router } from '@inertiajs/vue3';
import Toast from 'primevue/toast';
import Toolbar from 'primevue/toolbar';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Dropdown from 'primevue/dropdown';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';



const detailsDialog = ref(false);
const selectedEmployee = ref(null);


const toast = useToast();
const dt = ref(null);
const employees = ref([]);
const positions = ref([]);
const loading = ref(false);
const search = ref('');
const selectedRole = ref('all');
const selectedStatus = ref('all');
const employeeDialog = ref(false);
const deactivateDialog = ref(false);
const employee = ref({ status: 'actif', position_id: null, salary_base: null, first_name: '', last_name: '', email: '', phone: '', address: '' });
const employeeToDeactivate = ref(null);
const errors = ref({});

const statusOptions = [
  { value: 'actif', label: 'Actif' },
  { value: 'suspendu', label: 'Suspendu' },
  { value: 'inactif', label: 'Inactif' },
];

const viewEmployeeDetails = (employee) => {
  selectedEmployee.value = employee;
  detailsDialog.value = true;
};

const loadData = async () => {
  loading.value = true;
  try {
    const [empRes, posRes] = await Promise.all([
      fetch('/employees', { headers: { 'X-Requested-With': 'XMLHttpRequest' } }),
      fetch('/positions', { headers: { 'X-Requested-With': 'XMLHttpRequest' } }),
    ]);

    if (!empRes.ok || !posRes.ok) {
      throw new Error('Erreur de chargement');
    }

    const empData = await empRes.json();
    employees.value = empData.data || empData;
    positions.value = await posRes.json();
  } catch (err) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible de charger les employés', life: 3000 });
  } finally {
    loading.value = false;
  }
};

onMounted(loadData);

const uniquePositions = computed(() => {
  const seen = new Set();
  return positions.value.filter(pos => {
    const duplicate = seen.has(pos.name);
    seen.add(pos.name);
    return !duplicate;
  });
});

const filteredEmployees = computed(() => {
  return employees.value.filter((emp) => {
    const query = search.value.trim().toLowerCase();
    const matchesSearch =
      !query ||
      [emp.first_name, emp.last_name, emp.matricule, emp.email]
        .filter(Boolean)
        .some((value) => value.toString().toLowerCase().includes(query));

    const matchesRole = selectedRole.value === 'all' || emp.position_id === selectedRole.value;
    const matchesStatus = selectedStatus.value === 'all' || emp.status === selectedStatus.value;

    return matchesSearch && matchesRole && matchesStatus;
  });
});

const rowClassName = (data) => {
  return data.status !== 'actif' ? 'row-inactive' : '';
};

const dialogHeader = computed(() => (employee.value?.id ? 'Modifier un employé' : 'Créer un employé'));

const displayEmployeeName = computed(() => {
  if (!employeeToDeactivate.value) return '';
  return `${employeeToDeactivate.value.first_name} ${employeeToDeactivate.value.last_name}`;
});

const setRole = (roleId) => {
  selectedRole.value = roleId;
};

const setStatus = (statusValue) => {
  selectedStatus.value = statusValue;
};

const openNew = () => {
  employee.value = {
    status: 'actif',
    position_id: null,
    salary_base: null,
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    address: '',
    birth_date: new Date().toISOString().slice(0, 10),
  };
  errors.value = {};
  employeeDialog.value = true;
};

const editEmployee = (data) => {
  employee.value = {
    ...data,
    position_id: data.position?.id ?? data.position_id,
    birth_date: data.birth_date ?? new Date().toISOString().slice(0, 10),
  };
  errors.value = {};
  employeeDialog.value = true;
};

const hideDialog = () => {
  employeeDialog.value = false;
  errors.value = {};
};

const saveEmployee = async () => {
  loading.value = true;
  errors.value = {};
  try {
    const method = employee.value.id ? 'PUT' : 'POST';
    const url = employee.value.id ? `/employees/${employee.value.id}` : '/employees';
    const response = await fetch(url, {
      method,
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify({
        first_name: employee.value.first_name,
        last_name: employee.value.last_name,
        email: employee.value.email,
        phone: employee.value.phone,
        address: employee.value.address,
        position_id: employee.value.position_id,
        salary_base: employee.value.salary_base,
        status: employee.value.status,
        birth_date: employee.value.birth_date,
      }),
    });

    const data = await response.json();
    if (response.status === 422) {
      errors.value = data.errors || {};
      toast.add({ severity: 'warn', summary: 'Validation', detail: 'Corrigez les champs', life: 3000 });
      return;
    }

    if (!response.ok) {
      throw new Error(data.error || 'Erreur serveur');
    }

    const savedEmployee = response.status === 200 ? data.employee || data : data;
    if (employee.value.id) {
      const index = employees.value.findIndex((e) => e.id === savedEmployee.id);
      if (index !== -1) {
        employees.value.splice(index, 1, savedEmployee);
      }
      toast.add({ severity: 'success', summary: 'Modifié', detail: 'Employé mis à jour', life: 3000 });
    } else {
      employees.value.unshift(savedEmployee);
      toast.add({ severity: 'success', summary: 'Ajouté', detail: 'Employé créé', life: 3000 });
    }

    employeeDialog.value = false;
  } catch (err) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: err.message || 'Impossible de sauvegarder', life: 3000 });
  } finally {
    loading.value = false;
  }
};

const confirmDeactivate = (data) => {
  employeeToDeactivate.value = data;
  deactivateDialog.value = true;
};

const cancelDeactivate = () => {
  deactivateDialog.value = false;
  employeeToDeactivate.value = null;
};

const deactivateEmployee = async () => {
  if (!employeeToDeactivate.value) return;
  loading.value = true;
  try {
    const response = await fetch(`/employees/${employeeToDeactivate.value.id}`, {
      method: 'DELETE',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
      },
    });
    const data = await response.json();
    if (!response.ok) {
      throw new Error(data.error || 'Erreur de désactivation');
    }
    const index = employees.value.findIndex((e) => e.id === employeeToDeactivate.value.id);
    if (index !== -1) {
      employees.value[index] = { ...employees.value[index], status: 'suspendu' };
    }
    toast.add({ severity: 'success', summary: 'Désactivé', detail: 'Employé désactivé', life: 3000 });
    deactivateDialog.value = false;
  } catch (err) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: err.message || 'Impossible de désactiver', life: 3000 });
  } finally {
    loading.value = false;
  }
};

const formatCurrency = (value) => {
  return value !== null && value !== undefined
    ? Number(value).toLocaleString('fr-BJ', { style: 'currency', currency: 'XOF' })
    : '0 FCFA';
};

const getSeverity = (status) => {
  switch (status) {
    case 'actif':
      return 'success'; // Tends toward Teal in our theme
    case 'suspendu':
      return 'danger';
    case 'inactif':
      return 'secondary'; // Tends toward Slate
    default:
      return 'info';
  }
};

const getStatusText = (status) => {
  return {
    actif: 'Actif',
    suspendu: 'Suspendu',
    inactif: 'Inactif',
  }[status] || status;
};
</script>

<style scoped>
.row-inactive td {
  color: #6b7280 !important;
  opacity: 0.75;
}
</style>
<template>
    <AuthenticatedLayout>
        <div class="py-6 space-y-8">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">Gestion des Employés</h1>
                    <p class="mt-1 text-sm text-slate-500 font-medium">Administrez votre capital humain et gérez les fiches collaborateurs.</p>
                </div>
                <Button label="Nouvel Employé" icon="pi pi-plus" 
                    class="bg-teal-600 hover:bg-teal-700 text-white border-none px-6 py-3 rounded-xl font-bold shadow-lg shadow-teal-600/20 transition-all" 
                    @click="openNew" />
            </div>

            <!-- Filters Card -->
            <div class="bg-white rounded-3xl border border-slate-100 p-8 shadow-sm space-y-8">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <!-- Search -->
                    <div class="flex-1 max-w-md relative group">
                        <i class="pi pi-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-teal-500 transition-colors"></i>
                        <InputText v-model="search" placeholder="Rechercher un matricule, nom, email..." 
                            class="w-full pl-12 pr-4 py-3 bg-slate-50 border-slate-100 rounded-xl focus:border-teal-500 focus:ring-teal-500 transition-all placeholder:text-slate-400 text-sm font-medium" />
                    </div>

                    <!-- Filter Buttons -->
                    <div class="flex flex-wrap items-center gap-4">
                        <div class="flex items-center gap-2 p-1.5 bg-slate-50 rounded-2xl border border-slate-100">
                            <button @click="setRole('all')" 
                                :class="[selectedRole === 'all' ? 'bg-white text-slate-900 shadow-sm border-slate-200' : 'text-slate-400 hover:text-slate-600 border-transparent']"
                                class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest border transition-all">Tous les rôles</button>
                            <button v-for="pos in uniquePositions" :key="pos.id" @click="setRole(pos.id)"
                                :class="[selectedRole === pos.id ? 'bg-white text-teal-600 shadow-sm border-teal-100' : 'text-slate-400 hover:text-slate-600 border-transparent']"
                                class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest border transition-all">{{ pos.name }}</button>
                        </div>

                        <div class="flex items-center gap-2 p-1.5 bg-slate-50 rounded-2xl border border-slate-100">
                            <button @click="setStatus('all')"
                                :class="[selectedStatus === 'all' ? 'bg-white text-slate-900 shadow-sm border-slate-200' : 'text-slate-400 hover:text-slate-600 border-transparent']"
                                class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest border transition-all">Tous les statuts</button>
                            <button @click="setStatus('actif')"
                                :class="[selectedStatus === 'actif' ? 'bg-white text-teal-600 shadow-sm border-teal-100' : 'text-slate-400 hover:text-slate-600 border-transparent']"
                                class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest border transition-all">Actifs</button>
                        </div>
                    </div>
                </div>

                <!-- DataTable -->
                <DataTable
                    :value="filteredEmployees"
                    dataKey="id"
                    :paginator="true"
                    :rows="10"
                    :loading="loading"
                    class="p-datatable-custom"
                    paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
                    currentPageReportTemplate="Affichage de {first} à {last} sur {totalRecords} employés"
                    responsiveLayout="stack"
                    breakpoint="960px"
                    :pt="{
                        header: { class: 'bg-transparent border-none p-0' },
                        thead: { class: 'bg-slate-50 border-y border-slate-100' },
                        th: { class: 'px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 border-none bg-transparent' },
                        tbody: { class: 'divide-y divide-slate-50' },
                        tr: { class: 'hover:bg-slate-50/50 transition-colors group' },
                        td: { class: 'px-6 py-4 border-none align-middle' },
                        paginator: { class: 'bg-transparent border-t border-slate-100 p-6' }
                    }"
                >
                    <Column field="matricule" header="Matricule">
                        <template #body="{ data }">
                            <span class="text-xs font-black text-slate-900 bg-slate-100 px-2.5 py-1 rounded-lg">#{{ data.matricule }}</span>
                        </template>
                    </Column>
                    <Column header="Collaborateur">
                        <template #body="{ data }">
                            <div class="flex items-center gap-4">
                                <div class="h-10 w-10 rounded-xl bg-teal-50 border border-teal-100 flex items-center justify-center text-teal-600 font-black text-xs">
                                    {{ data.first_name[0] }}{{ data.last_name[0] }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-black text-slate-800 truncate">{{ data.first_name }} {{ data.last_name }}</p>
                                    <p class="text-[11px] font-medium text-slate-400 truncate">{{ data.email }}</p>
                                </div>
                            </div>
                        </template>
                    </Column>
                    <Column field="position.name" header="Rôle">
                        <template #body="{ data }">
                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-600 bg-slate-100 px-2 py-0.5 rounded">{{ data.position?.name }}</span>
                        </template>
                    </Column>
                    <Column field="salary_base" header="Salaire">
                        <template #body="{ data }">
                            <span class="text-sm font-black text-slate-700">{{ formatCurrency(data.salary_base) }}</span>
                        </template>
                    </Column>
                    <Column field="status" header="Statut">
                        <template #body="{ data }">
                            <span :class="[
                                'rounded-lg px-2.5 py-1 text-[9px] font-black uppercase tracking-widest',
                                data.status === 'actif' ? 'bg-teal-100 text-teal-700' : 'bg-slate-100 text-slate-600'
                            ]">{{ data.status }}</span>
                        </template>
                    </Column>
                    <Column header="Actions" class="text-right">
                        <template #body="{ data }">
                            <div class="flex items-center justify-end gap-1 transition-opacity">
                                <Button icon="pi pi-eye" class="p-button-text p-button-secondary p-button-sm rounded-lg" @click="router.visit(route('employees.show', data.id), { data: { from: 'employees' } })" />
                                <Button icon="pi pi-pencil" class="p-button-text p-button-secondary p-button-sm rounded-lg hover:text-teal-600" @click="editEmployee(data)" />
                                <Button v-if="data.status === 'actif'" icon="pi pi-user-minus" class="p-button-text p-button-danger p-button-sm rounded-lg" @click="confirmDeactivate(data)" />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>

        <!-- Create/Edit Dialog -->
        <Dialog v-model:visible="employeeDialog" :header="dialogHeader" modal class="rounded-3xl shadow-2xl border-none" :style="{ width: '550px' }"
            :pt="{ header: { class: 'bg-slate-50 p-6 rounded-t-3xl border-b border-slate-100' }, content: { class: 'p-8 bg-white' }, footer: { class: 'p-6 bg-slate-50 rounded-b-3xl border-t border-slate-100' } }">
            <div class="grid gap-6">
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Prénom</label>
                        <InputText v-model.trim="employee.first_name" class="w-full p-3 rounded-xl border-slate-200 focus:border-teal-500 focus:ring-teal-500 transition-all" />
                        <small v-if="errors.first_name" class="text-rose-500 text-[10px] font-bold">{{ errors.first_name[0] }}</small>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Nom</label>
                        <InputText v-model.trim="employee.last_name" class="w-full p-3 rounded-xl border-slate-200 focus:border-teal-500 focus:ring-teal-500 transition-all" />
                        <small v-if="errors.last_name" class="text-rose-500 text-[10px] font-bold">{{ errors.last_name[0] }}</small>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Email professionnel</label>
                        <InputText v-model.trim="employee.email" class="w-full p-3 rounded-xl border-slate-200 focus:border-teal-500 focus:ring-teal-500 transition-all" />
                        <small v-if="errors.email" class="text-rose-500 text-[10px] font-bold">{{ errors.email[0] }}</small>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Téléphone</label>
                        <InputText v-model.trim="employee.phone" class="w-full p-3 rounded-xl border-slate-200 focus:border-teal-500 focus:ring-teal-500 transition-all" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Rôle / Poste</label>
                        <Dropdown v-model="employee.position_id" :options="positions" optionLabel="name" optionValue="id" placeholder="Sélectionner..." class="w-full rounded-xl border-slate-200" />
                        <small v-if="errors.position_id" class="text-rose-500 text-[10px] font-bold">{{ errors.position_id[0] }}</small>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Salaire de base</label>
                        <InputNumber v-model="employee.salary_base" mode="currency" currency="XOF" locale="fr-BJ" class="w-full" inputClass="p-3 rounded-xl border-slate-200" />
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Statut du contrat</label>
                    <Dropdown v-model="employee.status" :options="statusOptions" optionLabel="label" optionValue="value" class="w-full rounded-xl border-slate-200" />
                </div>
            </div>

            <template #footer>
                <div class="flex gap-3 w-full">
                    <Button label="Annuler" class="flex-1 p-button-text p-button-secondary font-black text-xs uppercase" @click="hideDialog" />
                    <Button label="Enregistrer" class="flex-1 bg-teal-600 border-none font-black text-xs uppercase p-3 rounded-xl shadow-lg shadow-teal-600/20" @click="saveEmployee" :loading="loading" />
                </div>
            </template>
        </Dialog>

        <!-- Deactivate Dialog -->
        <Dialog v-model:visible="deactivateDialog" header="Confirmer la Désactivation" modal class="rounded-3xl shadow-2xl border-none" :style="{ width: '400px' }"
            :pt="{ header: { class: 'bg-slate-50 p-6 rounded-t-3xl border-b border-slate-100' }, content: { class: 'p-8 bg-white' }, footer: { class: 'p-6 bg-slate-50 rounded-b-3xl border-t border-slate-100' } }">
            <div class="space-y-6 text-center">
                <div class="h-16 w-16 bg-rose-50 rounded-full flex items-center justify-center text-rose-500 mx-auto">
                    <i class="pi pi-user-minus text-2xl"></i>
                </div>
                <p class="text-sm text-slate-600 leading-relaxed">
                    Voulez-vous vraiment désactiver <br>
                    <span class="font-black text-slate-900">"{{ displayEmployeeName }}"</span> ?
                </p>
            </div>
            <template #footer>
                <div class="flex gap-3 w-full">
                    <Button label="Annuler" class="flex-1 p-button-text p-button-secondary font-black text-xs uppercase" @click="cancelDeactivate" />
                    <Button label="Désactiver" class="flex-1 bg-rose-600 border-none font-black text-xs uppercase p-3 rounded-xl shadow-lg shadow-rose-600/20" @click="deactivateEmployee" :loading="loading" />
                </div>
            </template>
        </Dialog>

        <!-- Details Dialog -->
        <Dialog v-model:visible="detailsDialog" :header="`Profil : ${selectedEmployee?.first_name} ${selectedEmployee?.last_name}`" modal class="rounded-3xl shadow-2xl border-none" :style="{ width: '800px' }"
            :pt="{ header: { class: 'bg-slate-50 p-6 rounded-t-3xl border-b border-slate-100' }, content: { class: 'p-8 bg-white' }, footer: { class: 'p-6 bg-slate-50 rounded-b-3xl border-t border-slate-100' } }">
            <div v-if="selectedEmployee" class="space-y-8">
                <div class="flex items-center gap-6">
                    <div class="h-20 w-20 rounded-2xl bg-teal-600 flex items-center justify-center text-white text-2xl font-black shadow-xl shadow-teal-600/20">
                        {{ selectedEmployee.first_name[0] }}{{ selectedEmployee.last_name[0] }}
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-1">
                            <h2 class="text-3xl font-black text-slate-900 tracking-tight">{{ selectedEmployee.first_name }} {{ selectedEmployee.last_name }}</h2>
                            <span :class="[
                                'rounded-lg px-2.5 py-1 text-[9px] font-black uppercase tracking-widest',
                                selectedEmployee.status === 'actif' ? 'bg-teal-100 text-teal-700' : 'bg-slate-100 text-slate-600'
                            ]">{{ selectedEmployee.status }}</span>
                        </div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest flex items-center gap-2">
                            <i class="pi pi-id-card text-teal-500"></i>
                            Matricule: #{{ selectedEmployee.matricule }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-8">
                    <div class="space-y-6">
                        <h3 class="text-xs font-black text-slate-900 uppercase tracking-widest flex items-center gap-2">
                            <i class="pi pi-user text-teal-500"></i> Personnel
                        </h3>
                        <div class="bg-slate-50 rounded-2xl p-6 border border-slate-100 space-y-4">
                            <div>
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Email</label>
                                <p class="text-sm font-bold text-slate-700">{{ selectedEmployee.email || '—' }}</p>
                            </div>
                            <div>
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Téléphone</label>
                                <p class="text-sm font-bold text-slate-700">{{ selectedEmployee.phone || '—' }}</p>
                            </div>
                            <div>
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Adresse</label>
                                <p class="text-sm font-bold text-slate-700">{{ selectedEmployee.address || '—' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <h3 class="text-xs font-black text-slate-900 uppercase tracking-widest flex items-center gap-2">
                            <i class="pi pi-briefcase text-teal-500"></i> Professionnel
                        </h3>
                        <div class="bg-slate-50 rounded-2xl p-6 border border-slate-100 space-y-4">
                            <div>
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Poste actuel</label>
                                <p class="text-sm font-black text-teal-600">{{ selectedEmployee.position?.name || 'Non assigné' }}</p>
                            </div>
                            <div>
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Salaire contractuel</label>
                                <p class="text-lg font-black text-slate-900">{{ formatCurrency(selectedEmployee.salary_base) }}</p>
                            </div>
                            <div>
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Date d'embauche</label>
                                <p class="text-sm font-bold text-slate-700">{{ selectedEmployee.hire_date || '—' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <template #footer>
                <div class="flex gap-3 justify-end">
                    <Button label="Fermer" class="p-button-text p-button-secondary font-black text-xs uppercase" @click="detailsDialog = false" />
                    <Button label="Modifier le profil" icon="pi pi-pencil" class="bg-teal-600 border-none px-5 py-2.5 rounded-xl font-bold text-xs shadow-lg shadow-teal-600/20 transition-all" @click="() => { detailsDialog = false; editEmployee(selectedEmployee); }" />
                </div>
            </template>
        </Dialog>
    </AuthenticatedLayout>

</template>

