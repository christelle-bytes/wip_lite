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
                <InputLabel for="name" value="Nom" />
                <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" required autofocus />
                <InputError :message="form.errors.name" class="mt-2" />
              </div>

              <div>
                <InputLabel for="email" value="Email" />
                <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full" required />
                <InputError :message="form.errors.email" class="mt-2" />
              </div>

              <div>
                <InputLabel for="password" value="Mot de passe" />
                <TextInput id="password" v-model="form.password" type="password" class="mt-1 block w-full" required />
                <InputError :message="form.errors.password" class="mt-2" />
              </div>

              <div>
                <InputLabel for="password_confirmation" value="Confirmer mot de passe" />
                <TextInput id="password_confirmation" v-model="form.password_confirmation" type="password" class="mt-1 block w-full" required />
              </div>

              <div>
                <InputLabel for="role_id" value="Rôle" />
                <select id="role_id" v-model="form.role_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                  <option value="">Sélectionner un rôle</option>
                  <option v-for="role in props.roles" :key="role.id" :value="role.id">
                    {{ role.name.toUpperCase() }}
                  </option>
                </select>
                <InputError :message="form.errors.role_id" class="mt-2" />
              </div>

              <div class="flex items-center gap-4">
                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                  Créer
                </PrimaryButton>

                <Transition 
                  enter-from-class="opacity-0"
                  leave-to-class="opacity-0"
                  class="transition ease-in-out"
                >
                  <p v-if="form.recentlySuccessful" class="text-sm text-green-600">
                    Créé avec succès !
                  </p>
                </Transition>
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
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
  roles: Array,
});

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role_id: '',
});

const submit = () => {
  form.post(route('users.store'));
};
</script>

