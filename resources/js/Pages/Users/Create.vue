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
                  :options="groupedEmployees" 
                  optionLabel="full_name"
                  optionValue="id"
                  optionGroupLabel="label"
                  optionGroupChildren="items"
                  filter 
                  placeholder="Sélectionner un ou plusieurs employés..." 
                  :maxSelectedLabels="3" 
                  class="mt-1 w-full border-gray-300 rounded-md shadow-sm" 
                  required
                >
                  <template #option="slotProps">
                    <div class="flex items-center justify-between w-full">
                      <div class="flex flex-col">
                        <span class="font-bold text-slate-900">{{ slotProps.option.first_name }} {{ slotProps.option.last_name }}</span>
                        <span class="text-[10px] text-slate-500 uppercase tracking-tighter">#{{ slotProps.option.matricule }} - {{ slotProps.option.email }}</span>
                      </div>
                      <span :class="getPositionClass(slotProps.option.position?.code)" 
                        class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-widest border">
                        {{ slotProps.option.position?.code || 'N/A' }}
                      </span>
                    </div>
                  </template>
                </MultiSelect>
                <InputError :message="form.errors.employee_ids" class="mt-2" />
                
                <div v-if="form.employee_ids.length > 0" class="mt-3 space-y-2">
                  <p class="text-[11px] text-teal-600 font-bold flex items-center gap-1">
                    <i class="pi pi-users"></i>
                    {{ form.employee_ids.length }} employé(s) sélectionné(s)
                  </p>
                  
                  <!-- Warning if positions are mixed or don't match role -->
                  <div v-if="roleMismatchWarning" class="p-3 bg-amber-50 border border-amber-200 rounded-xl flex items-start gap-3">
                    <i class="pi pi-exclamation-triangle text-amber-500 mt-0.5"></i>
                    <div>
                      <p class="text-[10px] font-black text-amber-800 uppercase tracking-widest">Attention : Incohérence détectée</p>
                      <p class="text-[11px] text-amber-700 mt-1">{{ roleMismatchWarning }}</p>
                    </div>
                  </div>
                </div>
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

// Grouper les employés par position pour le MultiSelect
const groupedEmployees = computed(() => {
  const groups = {};
  
  props.employees.forEach(emp => {
    const posName = emp.position?.name || 'Sans position';
    if (!groups[posName]) {
      groups[posName] = {
        label: posName,
        items: []
      };
    }
    groups[posName].items.push({
      ...emp,
      full_name: `${emp.first_name} ${emp.last_name} (#${emp.matricule})`
    });
  });

  return Object.values(groups);
});

const form = useForm({
  employee_ids: [],
  role_id: '',
});

// Styles pour les badges de position
const getPositionClass = (code) => {
  switch (code) {
    case 'RH': return 'bg-indigo-50 text-indigo-600 border-indigo-100';
    case 'CP': return 'bg-amber-50 text-amber-600 border-amber-100';
    case 'SUP': return 'bg-teal-50 text-teal-600 border-teal-100';
    case 'TC': return 'bg-slate-50 text-slate-600 border-slate-100';
    default: return 'bg-gray-50 text-gray-600 border-gray-100';
  }
};

// Alerte si le rôle sélectionné ne correspond pas aux positions des employés
const roleMismatchWarning = computed(() => {
  if (!form.role_id || form.employee_ids.length === 0) return null;
  
  const selectedRoleName = props.roles[form.role_id]?.toUpperCase();
  const selectedEmployees = props.employees.filter(emp => form.employee_ids.includes(emp.id));
  
  const mismatched = selectedEmployees.filter(emp => {
    const posCode = emp.position?.code?.toUpperCase();
    // Cas spécial pour Admin qui correspond souvent à RH
    if (selectedRoleName === 'ADMIN' && posCode === 'RH') return false;
    return posCode !== selectedRoleName;
  });

  if (mismatched.length > 0) {
    return `Vous allez attribuer le rôle "${selectedRoleName}" à des employés dont la position est différente (${mismatched.map(m => m.position?.code).join(', ')}).`;
  }

  return null;
});

const submit = () => {
  form.post(route('users.store'));
};
</script>

