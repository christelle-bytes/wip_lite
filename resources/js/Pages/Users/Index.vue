<template>
  <Head title="Utilisateurs" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Utilisateurs</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <div class="flex justify-between items-center mb-6">
              <h3 class="text-lg font-medium">Liste des utilisateurs</h3>
              <Link :href="route('users.create')" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Créer utilisateur
              </Link>
            </div>

            <div v-if="props.users.data.length === 0" class="text-center py-8">
              <p>Aucun utilisateur.</p>
            </div>

            <table v-else class="min-w-full divide-y divide-gray-200">
              <thead>
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rôle</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="user in props.users.data" :key="user.id">
                  <td class="px-6 py-4 whitespace-nowrap">{{ user.name }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">{{ user.email }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ getRoleColor(user.role.name) }}">
                      {{ user.role.name.toUpperCase() }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <button @click="deleteUser(user)" class="text-red-600 hover:text-red-900">Supprimer</button>
                  </td>
                </tr>
              </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="props.users.links" class="mt-6">
              <Link 
                v-for="link in props.users.links" 
                :key="link.label"
                :href="link.url" 
                class="mr-2 px-3 py-2 bg-blue-500 text-white rounded {{ link.active ? 'bg-blue-700' : '' }}"
                v-html="link.label"
              />
            </div>

            <!-- Success message -->
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
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  users: Object,
});

const { delete: destroy } = useForm();

const getRoleColor = (role) => {
  return {
    admin: 'bg-red-100 text-red-800',
    cp: 'bg-blue-100 text-blue-800',
    sup: 'bg-yellow-100 text-yellow-800',
    tc: 'bg-gray-100 text-gray-800',
  }[role] || 'bg-gray-100 text-gray-800';
};

const deleteUser = (user) => {
  if (confirm(`Supprimer ${user.name}?`)) {
    destroy(route('users.destroy', user));
  }
};
</script>

