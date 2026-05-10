<template>
  <Head title="Utilisateurs" />
  <AuthenticatedLayout>
    <div class="py-6 space-y-8">
      <!-- Header Section -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
          <div>
              <h1 class="text-3xl font-black text-slate-900 tracking-tight">Gestion des Utilisateurs</h1>
              <p class="mt-1 text-sm text-slate-500 font-medium">Gérez les accès et les rôles des membres de la plateforme.</p>
          </div>
          <Link :href="route('users.create')" 
              class="bg-teal-600 hover:bg-teal-700 text-white px-6 py-3 rounded-xl font-bold shadow-lg shadow-teal-600/20 transition-all flex items-center gap-2">
              <i class="pi pi-user-plus"></i>
              Nouvel Utilisateur
          </Link>
      </div>

      <!-- Table Card -->
      <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-slate-50 border-y border-slate-100">
                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Utilisateur (Email)</th>
                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">Rôle</th>
                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">Status PWD</th>
                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
              <tr v-for="user in props.users.data" :key="user.id" class="hover:bg-slate-50/50 transition-colors group">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div class="h-8 w-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500 font-black text-[10px]">
                      {{ user.email.charAt(0).toUpperCase() }}
                    </div>
                    <span class="text-sm font-bold text-slate-700">{{ user.email }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 text-center">
                  <span :class="['rounded-lg px-2.5 py-1 text-[9px] font-black uppercase tracking-widest', getRoleColor(user.role?.name)]">
                    {{ user.role?.name || 'Inconnu' }}
                  </span>
                </td>
                <td class="px-6 py-4 text-center">
                  <span v-if="user.must_change_password" class="text-[9px] font-bold text-amber-600 bg-amber-50 px-2 py-1 rounded-md border border-amber-100">
                    À changer
                  </span>
                  <span v-else class="text-[9px] font-bold text-teal-600 bg-teal-50 px-2 py-1 rounded-md border border-teal-100">
                    OK
                  </span>
                </td>
                <td class="px-6 py-4 text-right">
                  <button @click="deleteUser(user)" 
                    class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all"
                    title="Supprimer l'utilisateur">
                    <i class="pi pi-trash"></i>
                  </button>
                </td>
              </tr>
              <tr v-if="props.users.data.length === 0">
                <td colspan="4" class="px-6 py-12 text-center text-slate-400 italic">
                  Aucun utilisateur trouvé.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="props.users.links && props.users.links.length > 3" class="p-6 bg-slate-50 border-t border-slate-100 flex justify-center gap-2">
          <Link v-for="(link, index) in props.users.links" :key="index"
            :href="link.url || '#'"
            v-html="link.label"
            :class="[
              'px-4 py-2 rounded-xl text-xs font-bold transition-all',
              link.active ? 'bg-teal-600 text-white shadow-lg shadow-teal-600/20' : 'bg-white border border-slate-200 text-slate-500 hover:border-teal-500 hover:text-teal-600',
              !link.url ? 'opacity-50 cursor-not-allowed pointer-events-none' : ''
            ]"
          />
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
  users: Object,
});

const form = useForm({});

const getRoleColor = (role) => {
  const name = role?.toLowerCase();
  if (name === 'admin') return 'bg-rose-100 text-rose-700';
  if (name === 'cp') return 'bg-slate-900 text-white';
  if (name === 'sup') return 'bg-teal-100 text-teal-700';
  return 'bg-slate-100 text-slate-600';
};

const deleteUser = (user) => {
  if (confirm(`Voulez-vous vraiment supprimer l'utilisateur ${user.email} ?`)) {
    form.delete(route('users.destroy', user.id));
  }
};
</script>

