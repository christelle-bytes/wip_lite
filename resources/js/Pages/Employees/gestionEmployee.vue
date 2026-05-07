<script setup>
import { ref, computed, onMounted } from 'vue';
import { useToast } from 'primevue/usetoast';
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
      return 'success';
    case 'suspendu':
      return 'danger';
    case 'inactif':
      return 'warning';
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
  <div class="card p-4">
    <Toast />

    <Toolbar class="mb-4">
      <template #start>
        <div class="flex flex-wrap gap-3 items-center w-full">
          <div class="flex items-center gap-2 bg-white border border-gray-200 rounded-lg px-3 py-2 shadow-sm">
            <i class="pi pi-search text-gray-500"></i>
            <InputText v-model="search" placeholder="Rechercher par nom, matricule ou email..." class="min-w-[280px]" />
          </div>
        </div>
      </template>
      <template #end>
        <Button label="Créer un employé" icon="pi pi-plus" severity="primary" class="!rounded-lg !px-4 !font-medium" @click="openNew" />
      </template>
    </Toolbar>

    <div class="grid gap-4 mb-4">
      <div class="flex flex-wrap items-center gap-2">
        <span class="font-bold  uppercase tracking-wider px-2 py-1 rounded bg-sky-50 text-sky-600 border border-sky-100">
    Par rôle
</span>
<div class="flex flex-wrap items-center gap-2 p-2 rounded-xl border border-surface-200 dark:border-surface-700 bg-surface-50/50">
        <Button 
          label="Tous"
          :severity="selectedRole === 'all' ? 'primary' : 'secondary'"
          text
          class="uppercase"
          @click="setRole('all')"
        />
    <Button
  v-for="position in positions"
  :key="position.id"
  :label="position.name"
  @click="setRole(position.id)"
  :class="[
    'uppercase !rounded-lg !border-none !px-4 !py-2 transition-colors duration-200',
    selectedRole === position.id 
      ? '!bg-sky-500 !text-white hover:!bg-sky-600' 
      : '!bg-gray-100 !text-gray-600 hover:!bg-sky-100 hover:!text-sky-600'
  ]"
/>
</div>
      </div>

      <div class="flex flex-wrap items-center gap-2">
        <span class="font-semibold">Par statut</span>
<div class="flex flex-wrap items-center gap-2 p-2 rounded-xl border border-surface-200 dark:border-surface-700 bg-surface-50/50">

        <Button
          label="Tous"
          :severity="selectedStatus === 'all' ? 'primary' : 'secondary'"
          text
          class="uppercase"
          @click="setStatus('all')"
        />
        <Button
          label="Actifs"
          :severity="selectedStatus === 'actif' ? 'primary' : 'secondary'"
          text
          class="uppercase"
          @click="setStatus('actif')"
        />
        <Button
          label="Inactifs"
          :severity="selectedStatus === 'inactif' ? 'primary' : 'secondary'"
          text
          class="uppercase"
          @click="setStatus('inactif')"
        />
        <Button
          label="Suspendus"
          :severity="selectedStatus === 'suspendu' ? 'primary' : 'secondary'"
          text
          class="uppercase"
          @click="setStatus('suspendu')"
        />
      </div>
      </div>
    </div>

    <DataTable
      ref="dt"
      :value="filteredEmployees"
      dataKey="id"
      :paginator="true"
      :rows="10"
      :rowsPerPageOptions="[5, 10, 25]"
      :loading="loading"
      paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
      currentPageReportTemplate="Affichage de {first} à {last} sur {totalRecords} employés"
      :rowClassName="rowClassName"
    >
      <Column field="matricule" header="Matricule" sortable></Column>
      <Column header="Nom" sortable sortField="last_name">
        <template #body="slotProps">
          <span class="font-medium">
            {{ slotProps.data.first_name }} {{ slotProps.data.last_name }}
          </span>
        </template>
      </Column>
      <Column field="email" header="Email"></Column>
      <Column field="position.name" header="Rôle" sortable></Column>
      <Column field="salary_base" header="Salaire" sortable>
        <template #body="slotProps">
          {{ formatCurrency(slotProps.data.salary_base) }}
        </template>
      </Column>
      <Column field="status" header="Statut" sortable>
        <template #body="slotProps">
          <Tag :value="getStatusText(slotProps.data.status)" :severity="getSeverity(slotProps.data.status)" />
        </template>
      </Column>
      <Column header="Actions" :exportable="false" style="min-width: 12rem">
        <template #body="slotProps">
          <Button icon="pi pi-pencil" rounded text class="mr-2" @click="editEmployee(slotProps.data)" />
          <Button
            v-if="slotProps.data.status === 'actif'"
            icon="pi pi-user-minus"
            rounded
            severity="danger"
            text
            @click="confirmDeactivate(slotProps.data)"
          />
          <span v-else class="text-sm text-gray-500">Désactivé</span>
        </template>
      </Column>
    </DataTable>

    <Dialog v-model:visible="employeeDialog" :header="dialogHeader" :modal="true" class="w-11/12 md:w-1/2">
      <div class="grid grid-cols-1 gap-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label for="first_name" class="block font-semibold mb-2">Prénom</label>
            <InputText id="first_name" v-model.trim="employee.first_name" class="w-full" />
          </div>
          <div>
            <label for="last_name" class="block font-semibold mb-2">Nom</label>
            <InputText id="last_name" v-model.trim="employee.last_name" class="w-full" />
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label for="email" class="block font-semibold mb-2">Email</label>
            <InputText id="email" v-model.trim="employee.email" class="w-full" />
          </div>
          <div>
            <label for="phone" class="block font-semibold mb-2">Téléphone</label>
            <InputText id="phone" v-model.trim="employee.phone" class="w-full" />
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label for="position" class="block font-semibold mb-2">Rôle</label>
            <Dropdown id="position" v-model="employee.position_id" :options="positions" optionLabel="name" optionValue="id" placeholder="Sélectionner un rôle" class="w-full" />
          </div>
          <div>
            <label for="salary" class="block font-semibold mb-2">Salaire de base</label>
            <InputNumber id="salary" v-model="employee.salary_base" mode="currency" currency="XOF" locale="fr-BJ" class="w-full" />
          </div>
        </div>

        <div>
          <label for="status" class="block font-semibold mb-2">Statut</label>
          <Dropdown id="status" v-model="employee.status" :options="statusOptions" optionLabel="label" optionValue="value" class="w-full" />
        </div>
      </div>

      <template #footer>
        <Button label="Annuler" icon="pi pi-times" text @click="hideDialog" />
        <Button label="Enregistrer" icon="pi pi-check" @click="saveEmployee" />
      </template>
    </Dialog>

    <Dialog v-model:visible="deactivateDialog" header="Confirmer la désactivation" :modal="true" class="w-11/12 md:w-1/3">
      <p>Voulez-vous vraiment désactiver <strong>{{ displayEmployeeName }}</strong> ?</p>
      <template #footer>
        <Button label="Annuler" icon="pi pi-times" text @click="cancelDeactivate" />
        <Button label="Désactiver" icon="pi pi-user-minus" severity="danger" @click="deactivateEmployee" />
      </template>
    </Dialog>
  </div>
</template>

