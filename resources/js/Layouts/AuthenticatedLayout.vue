<script setup>
import { ref, watch, computed } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import { useToast } from "primevue/usetoast";
import Toast from "primevue/toast";

const page = usePage();
const showingNavigationDropdown = ref(false);
const toast = useToast();

// Calcul du rôle pour afficher les liens conditionnels
const user = computed(() => page.props.auth.user);
const roleName = computed(() => user.value?.role?.name?.toUpperCase() || '');

// On crée une référence réactive sur les messages flash partagés par Laravel
const flash = computed(() => page.props.flash);

// Fonction pour déterminer si on doit afficher le menu de navigation complet
const showFullNavigation = computed(() => {
    return !!roleName.value;
});

// Fonction pour déterminer si on doit afficher le bouton de déconnexion
const showLogoutButton = computed(() => {
    return user.value && page.props.auth; // Afficher si l'utilisateur est authentifié
});

// On "observe" les changements sur flash
watch(
    flash,
    (newFlash) => {
        if (newFlash && newFlash.success) {
            console.log(newFlash)
            toast.add({
                severity: "success",
                summary: "Succès",
                detail: newFlash.success,
                life: 3000, // Disparaît après 3 secondes
            });
        }

        if (newFlash && newFlash.error) {
            toast.add({
                severity: "error",
                summary: "Erreur",
                detail: newFlash.error,
                life: 5000,
            });
        }
    },
    { deep: true },
);
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex">
        <aside class="w-64 bg-slate-900 text-white hidden sm:flex flex-col">
            <div class="p-6">
                <h1 class="text-xl font-bold text-blue-400">GRH Gestion RH</h1>
            </div>

            <nav class="flex-1 px-4 space-y-2">
                <Link v-if="user" :href="route('reporting.index')" class="block p-2 hover:bg-slate-800 rounded">Tableau de bord</Link>

                <template v-if="user?.role?.name === 'Admin'">
                    <Link href="#" class="block p-2 hover:bg-slate-800 rounded">Employés</Link>
                    <Link href="#" class="block p-2 hover:bg-slate-800 rounded">Campagnes</Link>
                    <Link href="/planning" class="block p-2 hover:bg-slate-800 rounded">Plannings</Link>
                    <Link href="#" class="block p-2 hover:bg-slate-800 rounded">Heures</Link>
                </template>
                <template v-else-if="user?.role?.name === 'CP'">
                    <Link href="#" class="block p-2 hover:bg-slate-800 rounded">Employés</Link>
                    <Link href="#" class="block p-2 hover:bg-slate-800 rounded">Campagnes</Link>
                     <Link href="/planning" class="block p-2 hover:bg-slate-800 rounded">Plannings</Link>
                </template>
                <template v-else-if="user?.role?.name === 'SUP'">
                    <Link href="#" class="block p-2 hover:bg-slate-800 rounded">Employés</Link>
                    <Link href="#" class="block p-2 hover:bg-slate-800 rounded">Campagnes</Link>
                    <Link href="#" class="block p-2 hover:bg-slate-800 rounded">Affectations</Link>
                    <Link href="/planning" class="block p-2 hover:bg-slate-800 rounded">Plannings</Link>
                </template>
                <template v-else-if="user?.role?.name === 'TC'">
                    <Link href="#" class="block p-2 hover:bg-slate-800 rounded">Campagnes</Link>
                    <Link href="#" class="block p-2 hover:bg-slate-800 rounded">Affectations</Link>
                    <Link href="/planning" class="block p-2 hover:bg-slate-800 rounded">Plannings</Link>
                    <Link href="/gestion-employees" class="block p-2 hover:bg-slate-800 rounded">Employés</Link>
                    <Link :href="route('campaigns.index')" class="block p-2 hover:bg-slate-800 rounded">Campagnes</Link>
                    <Link :href="route('assignments.index')" class="block p-2 hover:bg-slate-800 rounded">Affectations</Link>
                    <Link href="#" class="block p-2 hover:bg-slate-800 rounded">Plannings</Link>
                    <Link
                        :href="route('index.sup')"
                        class="block p-2 hover:bg-slate-800 rounded"
                        >Heures</Link
                    >
                    <Link :href="route('timesheet.index')" class="block p-2 hover:bg-slate-800 rounded"
                        >Feuille d'heures</Link
                    >

                </template>
                <template v-else-if="user?.role?.name === 'CP'">
                    <Link href="/gestion-employees" class="block p-2 hover:bg-slate-800 rounded"
                        >Employés</Link
                    >
                    <Link :href="route('campaigns.index')" class="block p-2 hover:bg-slate-800 rounded"
                        >Campagnes</Link
                    >
                    <Link href="#" class="block p-2 hover:bg-slate-800 rounded"
                        >Plannings</Link
                    >
                    <Link :href="route('index.sup')" class="block p-2 hover:bg-slate-800 rounded"
                        >Heures</Link
                    >
                    <Link :href="route('timesheet.index')" class="block p-2 hover:bg-slate-800 rounded"
                        >Feuille d'heures</Link
                    >
                </template>
                <template v-else-if="user?.role?.name === 'SUP'">
                    <Link href="#" class="block p-2 hover:bg-slate-800 rounded"
                        >Employés</Link
                    >
                    <Link :href="route('campaigns.index')" class="block p-2 hover:bg-slate-800 rounded"
                        >Campagnes</Link
                    >
                    <Link :href="route('assignments.index')" class="block p-2 hover:bg-slate-800 rounded"
                        >Affectations</Link
                    >
                    <Link :href="route('index.telecon')" class="block p-2 hover:bg-slate-800 rounded"
                        >Heures</Link
                    >
                </template>
                <template v-else-if="user?.role?.name === 'TC'">
                    <Link :href="route('campaigns.index')" class="block p-2 hover:bg-slate-800 rounded"
                        >Campagnes</Link
                    >
                    <Link :href="route('assignments.index')" class="block p-2 hover:bg-slate-800 rounded"
                        >Affectations</Link
                    >
                </template>
            </nav>
        </aside>

        <div class="flex-1 flex flex-col">
            <nav
                class="bg-white border-b border-gray-200 h-16 flex items-center justify-between px-8"
            >
                <!-- Espace vide à gauche pour équilibre -->
                <div class="flex items-center space-x-4">
                    <!-- Logo ou titre pourrait aller ici -->
                </div>

                <!-- Menu dropdown à droite avec bouton de déconnexion conditionnel -->
                <div
                    v-if="showLogoutButton"
                    class="flex items-center space-x-3"
                >
                    <!-- Avatar et nom de l'utilisateur toujours visibles -->
                    <div class="flex items-center space-x-2">
                        <svg
                            class="h-5 w-5 text-gray-500"
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        <span class="text-sm font-medium text-gray-700">{{
                            user?.name
                        }}</span>
                        <span class="text-xs text-gray-500"
                            >({{ user?.role?.name }})</span
                        >
                    </div>

                    <!-- Dropdown pour les options -->
                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button
                                class="inline-flex items-center px-2 py-1 border border-transparent text-xs leading-4 font-medium rounded-md text-gray-400 hover:text-gray-600 focus:outline-none transition ease-in-out duration-150"
                            >
                                <svg
                                    class="h-4 w-4"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </button>
                        </template>

                        <template #content>
                            <div
                                class="block px-4 py-2 text-xs text-gray-700 border-b border-gray-200"
                            >
                                Connecté en tant que
                                <strong>{{
                                    user?.role?.name || "Invité"
                                }}</strong>
                            </div>
                            <DropdownLink
                                :href="route('profile.edit')"
                                class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100"
                            >
                                Profil
                            </DropdownLink>

                            <!-- Bouton de déconnexion - visible selon le niveau -->
                            <DropdownLink
                                v-if="showLogoutButton"
                                :href="route('logout')"
                                method="post"
                                as="button"
                                class="block w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50"
                            >
                                Déconnexion
                            </DropdownLink>
                        </template>
                    </Dropdown>
                </div>

                <!-- Bouton de connexion simple si non authentifié -->
                <Link
                    v-else
                    :href="route('login')"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring focus:ring-blue-300 disabled:opacity-25 transition"
                >
                    Se connecter
                </Link>
            </nav>

            <header v-if="$slots.header" class="bg-white shadow">
                <div class="px-8 py-6">
                    <slot name="header" />
                </div>
            </header>

            <main class="p-8">
                <Toast />

                <slot />
            </main>
        </div>
    </div>
</template>
