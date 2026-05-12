<template>
  <Head title="Créer utilisateur" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Créer utilisateur</h2>
        <Link :href="route('users.index')" class="text-sm text-gray-400 hover:text-gray-900">
          ← Retour
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <form @submit.prevent="submit" class="space-y-6">
              <div>
                <InputLabel for="employee_ids" value="Sélectionner des employés" />
                <MultiSelect 
                  id="employee_ids"
                  v-model="form.employee_ids" 
                  :options="employees" 
                  optionLabel="full_name"
                  optionValue="id"
                  filter 
                  placeholder="Sélectionner un ou plusieurs employés..." 
                  :maxSelectedLabels="3" 
                  class="mt-1 w-full border-gray-300 rounded-md shadow-sm" 
                  required
                >
                  <template #option="slotProps">
                    <div class="flex flex-col">
                      <span class="font-bold">{{ slotProps.option.first_name }} {{ slotProps.option.last_name }}</span>
                      <span class="text-[10px] text-slate-500 uppercase tracking-tighter">#{{ slotProps.option.matricule }} - {{ slotProps.option.email }}</span>
                    </div>
                  </template>
                </MultiSelect>
                <InputError :message="form.errors.employee_ids" class="mt-2" />
                <p v-if="form.employee_ids.length > 0" class="mt-2 text-[11px] text-teal-600 font-bold">
                  <i class="pi pi-users mr-1"></i>
                  {{ form.employee_ids.length }} employé(s) sélectionné(s)
                </p>
              </div>

              <div>
                <InputLabel for="role_id" value="Rôle d'accès" />
                <select id="role_id" v-model="form.role_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                  <option value="">Sélectionner un rôle...</option>
                  <option v-for="(name, id) in props.roles" :key="id" :value="id">
                    {{ name.toUpperCase() }}
                  </option>
                </select>
                <InputError :message="form.errors.role_id" class="mt-2" />
              </div>

              <div class="flex items-center gap-4">
                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                  Créer les comptes
                </PrimaryButton>

                <Transition 
                  enter-from-class="opacity-0"
                  leave-to-class="opacity-0"
                  class="transition ease-in-out"
                >
                  <p v-if="form.recentlySuccessful" class="text-sm text-green-600">
                    Comptes créés avec succès !
                  </p>
                </Transition>
              </div>
              
              <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                <p class="text-xs text-slate-500 font-medium">
                  <i class="pi pi-info-circle mr-1"></i>
                  Le mot de passe par défaut est <span class="font-bold text-slate-900">Welcome123!</span>. 
                  L'utilisateur devra le modifier lors de sa première connexion.
                </p>
              </div>
            </form>

            <div v-if="$page.props.flash.success" class="mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
              {{ $page.props.flash.success }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import MultiSelect from 'primevue/multiselect';

const props = defineProps({
  roles: Object,
  employees: Array,
});

// Préparer les données des employés pour le MultiSelect
const employees = computed(() => {
  return props.employees.map(emp => ({
    ...emp,
    full_name: `${emp.first_name} ${emp.last_name} (#${emp.matricule})`
  }));
});

const form = useForm({
  employee_ids: [],
  role_id: '',
});

const submit = () => {
  form.post(route('users.store'));
};
</script>

