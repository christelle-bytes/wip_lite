<!-- <script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
  employees: {
    type: Array,
    default: () => []
  },
  positions: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['filter']);

const searchQuery = ref('');
const selectedRoles = ref([]);
const selectedStatuses = ref([]);
const availableStatuses = ref([
  { value: 'all', label: 'Tous' },
  { value: 'actif', label: 'Actifs' },
  { value: 'inactif', label: 'Inactifs' },
  { value: 'suspendu', label: 'Suspendus' }
]);

const availableRoles = computed(() => {
  return [
    { id: 'all', name: 'Tous (...)' },
    ...props.positions
  ];
});

const filteredEmployees = computed(() => {
  let filtered = [...props.employees];

  if (searchQuery.value.trim()) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(emp =>
      (emp.first_name && emp.first_name.toLowerCase().includes(query)) ||
      (emp.last_name && emp.last_name.toLowerCase().includes(query)) ||
      (emp.matricule && emp.matricule.toLowerCase().includes(query)) ||
      (emp.email && emp.email.toLowerCase().includes(query))
    );
  }

  if (selectedRoles.value.length > 0 && !selectedRoles.value.includes('all')) {
    filtered = filtered.filter(emp => {
      return emp.position_id && selectedRoles.value.includes(emp.position_id);
    });
  }

  // Filtre par statut
  if (selectedStatuses.value.length > 0 && !selectedStatuses.value.includes('all')) {
    filtered = filtered.filter(emp => {
      return emp.status && selectedStatuses.value.includes(emp.status);
    });
  }

  return filtered;
});

const filteredCount = computed(() => {
  return filteredEmployees.value.length;
});

const hasFilters = computed(() => {
  return searchQuery.value.trim() ||
    (selectedRoles.value.length > 0 && !selectedRoles.value.includes('all')) ||
    (selectedStatuses.value.length > 0 && !selectedStatuses.value.includes('all'));
});


const toggleRoleFilter = (roleId) => {
  if (roleId === 'all') {
    selectedRoles.value = selectedRoles.value.length === 1 ? [] : ['all'];
  } else {
    selectedRoles.value = selectedRoles.value.filter(r => r !== 'all');

    const index = selectedRoles.value.indexOf(roleId);
    if (index > -1) {
      selectedRoles.value.splice(index, 1);
    } else {
      selectedRoles.value.push(roleId);
    }
  }
  applyFilters();
};

const toggleStatusFilter = (statusValue) => {
  if (statusValue === 'all') {
    selectedStatuses.value = selectedStatuses.value.length === 1 ? [] : ['all'];
  } else {
    selectedStatuses.value = selectedStatuses.value.filter(s => s !== 'all');

    const index = selectedStatuses.value.indexOf(statusValue);
    if (index > -1) {
      selectedStatuses.value.splice(index, 1);
    } else {
      selectedStatuses.value.push(statusValue);
    }
  }
  applyFilters();
};

const applyFilters = () => {
  emit('filter', filteredEmployees.value);
};

const clearFilters = () => {
  searchQuery.value = '';
  selectedRoles.value = [];
  selectedStatuses.value = [];
  applyFilters();
};

watch(() => props.employees, () => {
  applyFilters();
}, { immediate: true });
</script>


<template>
  <div class="bg-white p-6 rounded-lg shadow mb-6">
    <div class="mb-6">
      <input
        v-model="searchQuery"
        type="text"
        placeholder="Rechercher par nom, matricule ou email..."
        class="w-full px-4 py-3 border-2 border-blue-300 rounded-lg focus:outline-none focus:border-blue-500"
        @input="applyFilters"
      >
    </div>

    <div class="mb-6">
      <h3 class="text-sm font-semibold text-gray-700 mb-3">Par rôle</h3>
      <div class="flex flex-wrap gap-2">
        <button
          v-for="role in availableRoles"
          :key="role.id"
          @click="toggleRoleFilter(role.id)"
          :class="[
            'px-4 py-2 rounded-full text-sm font-medium transition-colors',
            selectedRoles.includes(role.id)
              ? 'bg-blue-500 text-white'
              : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
          ]"
        >
          {{ role.name }}
          <span v-if="selectedRoles.includes(role.id)" class="ml-1">✓</span>
        </button>
      </div>
    </div>

    <div class="mb-6">
      <h3 class="text-sm font-semibold text-gray-700 mb-3">Par statut</h3>
      <div class="flex flex-wrap gap-2">
        <button
          v-for="status in availableStatuses"
          :key="status.value"
          @click="toggleStatusFilter(status.value)"
          :class="[
            'px-4 py-2 rounded-full text-sm font-medium transition-colors',
            selectedStatuses.includes(status.value)
              ? 'bg-blue-500 text-white'
              : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
          ]"
        >
          {{ status.label }}
          <span v-if="selectedStatuses.includes(status.value)" class="ml-1">✓</span>
        </button>
      </div>
    </div>

    <div v-if="hasFilters" class="text-sm text-gray-600">
      <strong>{{ filteredCount }}</strong> employé(s) trouvé(s)
      <button
        @click="clearFilters"
        class="ml-4 text-blue-500 hover:text-blue-600 underline"
      >
        Réinitialiser les filtres
      </button>
    </div>
  </div>
</template>
 -->
