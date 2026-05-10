<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <!-- Conteneur avec arrière-plan stylisé -->
    <div class="min-h-screen w-full flex items-center justify-center bg-gradient-to-br from-indigo-600 via-purple-700 to-pink-500 py-12 px-4 sm:px-6 lg:px-8">
        
        <Head title="Log in" />

        <div class="max-w-md w-full space-y-8 bg-white/95 backdrop-blur-sm p-8 rounded-2xl shadow-2xl">
            
            <!-- Header du Formulaire -->
            <div class="text-center">
                <h2 class="mt-2 text-3xl font-extrabold text-gray-900">
                    Bon retour !
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Veuillez vous connecter à votre compte
                </p>
            </div>

            <!-- Statut & Erreurs -->
            <div v-if="status" class="rounded-md bg-green-50 p-4 text-sm font-medium text-green-800 border border-green-200 text-center">
                {{ status }}
            </div>

            <div v-if="Object.keys(form.errors).length" class="rounded-md bg-red-50 p-4 border border-red-100">
                <div class="flex">
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Identifiants incorrects</h3>
                        <div class="mt-1 text-xs text-red-700 space-y-1">
                            <p v-for="(message, field) in form.errors" :key="field">{{ message }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulaire -->
            <form @submit.prevent="submit" class="mt-8 space-y-6">
                <div class="rounded-md shadow-sm space-y-4">
                    <div>
                        <InputLabel for="email" value="Adresse Email" class="sr-only" />
                        <TextInput
                            id="email"
                            type="email"
                            class="appearance-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm transition-all"
                            v-model="form.email"
                            required
                            autofocus
                            placeholder="Email"
                        />
                        <InputError class="mt-1" :message="form.errors.email" />
                    </div>

                    <div>
                        <InputLabel for="password" value="Mot de passe" class="sr-only" />
                        <TextInput
                            id="password"
                            type="password"
                            class="appearance-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm transition-all"
                            v-model="form.password"
                            required
                            placeholder="Mot de passe"
                        />
                        <InputError class="mt-1" :message="form.errors.password" />
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <Checkbox name="remember" v-model:checked="form.remember" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" />
                        <label for="remember" class="ml-2 block text-sm text-gray-900">
                            Rester connecté
                        </label>
                    </div>

                    <div class="text-sm">
                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="font-medium text-indigo-600 hover:text-indigo-500"
                        >
                            Oublié ?
                        </Link>
                    </div>
                </div>

                <div>
                    <PrimaryButton
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-bold rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all active:scale-95 shadow-lg"
                        :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                        :disabled="form.processing"
                    >
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-indigo-500 group-hover:text-indigo-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                            </svg>
                        </span>
                        {{ form.processing ? 'Chargement...' : 'Se connecter' }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </div>
</template>
