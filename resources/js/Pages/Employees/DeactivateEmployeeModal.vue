<script setup>
import { ref } from 'vue';

const props = defineProps({
  employee: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['close', 'deactivated']);

function getCsrfToken() {
  const token = document.head.querySelector('meta[name="csrf-token"]');
  return token ? token.content : null;
}

const loading = ref(false);

const confirmDeactivate = async () => {
  loading.value = true;

  try {
    const response = await fetch(`/employees/${props.employee.id}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': getCsrfToken(),
        'X-Requested-With': 'XMLHttpRequest'
      }
    });

    if (!response.ok) {
      const data = await response.json().catch(() => null);
      console.error('Erreur lors de la désactivation:', response.status, data);
      return;
    }

    emit('deactivated', props.employee.id);
    emit('close');
  } catch (error) {
    console.error('Erreur lors de la désactivation:', error);
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click="$emit('close')">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" @click.stop>
      <div class="mt-3 text-center">
        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
          <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
          </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Confirmer la désactivation</h3>
        <div class="mt-2 px-7 py-3">
          <p class="text-sm text-gray-500">
            Êtes-vous sûr de vouloir désactiver l'employé <strong>{{ employee.first_name }} {{ employee.last_name }}</strong> ?
          </p>
        </div>
        <div class="flex justify-center space-x-3 pt-4">
          <button
            @click="$emit('close')"
            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400"
          >
            Annuler
          </button>
          <button
            @click="confirmDeactivate"
            :disabled="loading"
            class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 disabled:opacity-50"
          >
            <span v-if="loading">Désactivation...</span>
            <span v-else>Désactiver</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>