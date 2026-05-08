<script setup>
import { ref } from 'vue';

const props = defineProps({
  positions: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['close', 'created']);

function getCsrfToken() {
  const token = document.head.querySelector('meta[name="csrf-token"]');
  return token ? token.content : null;
}

const loading = ref(false);
const errors = ref({});
const form = ref({
  first_name: '',
  last_name: '',
  birth_date: '',
  phone: '',
  email: '',
  address: '',
  position_id: '',
  salary_base: '',
  status: 'actif'
});

const saveEmployee = async () => {
  loading.value = true;
  errors.value = {};

  try {
    const response = await fetch('/employees', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': getCsrfToken(),
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify(form.value)
    });

    const data = await response.json();
    if (response.status === 422) {
      errors.value = data.errors || {};
      return;
    }
    if (!response.ok) {
      console.error('Erreur lors de la création:', data);
      return;
    }

    emit('created', data);
    emit('close');
  } catch (error) {
    console.error('Erreur lors de la création:', error);
  } finally {
    loading.value = false;
  }
};
</script>


<template>
  <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click="$emit('close')">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white" @click.stop>
      <div class="mt-3">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Créer un employé</h3>

        <form @submit.prevent="saveEmployee" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Prénom *</label>
              <input
                v-model="form.first_name"
                type="text"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-500': errors.first_name }"
              >
              <span v-if="errors.first_name" class="text-red-500 text-sm">{{ errors.first_name[0] }}</span>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Nom *</label>
              <input
                v-model="form.last_name"
                type="text"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-500': errors.last_name }"
              >
              <span v-if="errors.last_name" class="text-red-500 text-sm">{{ errors.last_name[0] }}</span>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Date de naissance *</label>
              <input
                v-model="form.birth_date"
                type="date"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-500': errors.birth_date }"
              >
              <span v-if="errors.birth_date" class="text-red-500 text-sm">{{ errors.birth_date[0] }}</span>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Téléphone *</label>
              <input
                v-model="form.phone"
                type="tel"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-500': errors.phone }"
              >
              <span v-if="errors.phone" class="text-red-500 text-sm">{{ errors.phone[0] }}</span>
            </div>

            <div class="col-span-2">
              <label class="block text-sm font-medium text-gray-700">Email *</label>
              <input
                v-model="form.email"
                type="email"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-500': errors.email }"
              >
              <span v-if="errors.email" class="text-red-500 text-sm">{{ errors.email[0] }}</span>
            </div>

            <div class="col-span-2">
              <label class="block text-sm font-medium text-gray-700">Adresse</label>
              <input
                v-model="form.address"
                type="text"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
              >
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Position *</label>
              <select
                v-model="form.position_id"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-500': errors.position_id }"
              >
                <option value="">Sélectionner une position</option>
                <option v-for="position in positions" :key="position.id" :value="position.id">
                  {{ position.name }}
                </option>
              </select>
              <span v-if="errors.position_id" class="text-red-500 text-sm">{{ errors.position_id[0] }}</span>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Salaire de base *</label>
              <input
                v-model="form.salary_base"
                type="number"
                step="0.01"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-500': errors.salary_base }"
              >
              <span v-if="errors.salary_base" class="text-red-500 text-sm">{{ errors.salary_base[0] }}</span>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Statut *</label>
              <select
                v-model="form.status"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-500': errors.status }"
              >
                <option value="actif">Actif</option>
                <option value="suspendu">Suspendu</option>
                <option value="inactif">Inactif</option>
              </select>
              <span v-if="errors.status" class="text-red-500 text-sm">{{ errors.status[0] }}</span>
            </div>
          </div>

          <div class="flex justify-end space-x-3 pt-4">
            <button
              type="button"
              @click="$emit('close')"
              class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400"
            >
              Annuler
            </button>
            <button
              type="submit"
              :disabled="loading"
              class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 disabled:opacity-50"
            >
              <span v-if="loading">Création...</span>
              <span v-else>Créer</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>