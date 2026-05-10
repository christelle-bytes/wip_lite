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

watch(
    flash,
    (newFlash) => {
        if (newFlash && newFlash.success) {
            toast.add({
                severity: "success",
                summary: "Succès",
                detail: newFlash.success,
                life: 3000,
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

        if (newFlash && newFlash.info) {
            toast.add({
                severity: "info",
                summary: "Information",
                detail: newFlash.info,
                life: 3000,
            });
        }

        if (newFlash && newFlash.warning) {
            toast.add({
                severity: "warn",
                summary: "Attention",
                detail: newFlash.warning,
                life: 4000,
            });
        }
    },
    { deep: true },
);
</script>

<template>
    <div class="h-screen bg-slate-50 flex overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 bg-slate-900 text-white hidden sm:flex flex-col shadow-xl h-full flex-shrink-0">
            <div class="p-8 border-b border-slate-800/50">
                <h1 class="text-2xl font-black tracking-tight flex items-center gap-2">
                    <span class="text-teal-500">GRH</span>
                    <span class="text-slate-200">System</span>
                </h1>
            </div>

            <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto">
                <!-- Dashboard -->
                <Link v-if="user" :href="route('dashboard')" 
                    :class="[route().current('dashboard') ? 'bg-teal-600/10 text-teal-400 border-l-4 border-teal-500' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-100']"
                    class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200">
                    <i class="pi pi-home mr-3 text-lg" :class="[route().current('dashboard') ? 'text-teal-400' : 'text-slate-500 group-hover:text-slate-300']"></i>
                    Tableau de bord
                </Link>

                <div class="pt-4 pb-2 px-4 text-[10px] font-bold uppercase tracking-widest text-slate-500">Gestion</div>

                <!-- Admin Specific Links -->
                <template v-if="roleName === 'ADMIN'">
                    <Link :href="route('users.index')" 
                        :class="[route().current('users.*') ? 'bg-teal-600/10 text-teal-400 border-l-4 border-teal-500' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-100']"
                        class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200">
                        <i class="pi pi-user-plus mr-3 text-lg" :class="[route().current('users.*') ? 'text-teal-400' : 'text-slate-500 group-hover:text-slate-300']"></i>
                        Utilisateurs
                    </Link>
                    <Link href="/gestion-employees" 
                        class="group flex items-center px-4 py-3 text-sm font-medium text-slate-400 hover:bg-slate-800/50 hover:text-slate-100 rounded-lg transition-all duration-200">
                        <i class="pi pi-users mr-3 text-lg text-slate-500 group-hover:text-slate-300"></i>
                        Employés
                    </Link>
                    <Link :href="route('campaigns.index')" 
                        :class="[route().current('campaigns.*') ? 'bg-teal-600/10 text-teal-400 border-l-4 border-teal-500' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-100']"
                        class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200">
                        <i class="pi pi-flag mr-3 text-lg" :class="[route().current('campaigns.*') ? 'text-teal-400' : 'text-slate-500 group-hover:text-slate-300']"></i>
                        Campagnes
                    </Link>
                    <Link :href="route('assignments.index')" 
                        :class="[route().current('assignments.*') ? 'bg-teal-600/10 text-teal-400 border-l-4 border-teal-500' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-100']"
                        class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200">
                        <i class="pi pi-link mr-3 text-lg" :class="[route().current('assignments.*') ? 'text-teal-400' : 'text-slate-500 group-hover:text-slate-300']"></i>
                        Affectations
                    </Link>
                    <Link href="/planning" 
                        class="group flex items-center px-4 py-3 text-sm font-medium text-slate-400 hover:bg-slate-800/50 hover:text-slate-100 rounded-lg transition-all duration-200">
                        <i class="pi pi-calendar mr-3 text-lg text-slate-500 group-hover:text-slate-300"></i>
                        Plannings
                    </Link>
                    <Link :href="route('index.sup')" 
                        class="group flex items-center px-4 py-3 text-sm font-medium text-slate-400 hover:bg-slate-800/50 hover:text-slate-100 rounded-lg transition-all duration-200">
                        <i class="pi pi-clock mr-3 text-lg text-slate-500 group-hover:text-slate-300"></i>
                        Heures
                    </Link>
                    <Link :href="route('timesheet.index')" 
                        class="group flex items-center px-4 py-3 text-sm font-medium text-slate-400 hover:bg-slate-800/50 hover:text-slate-100 rounded-lg transition-all duration-200">
                        <i class="pi pi-file-edit mr-3 text-lg text-slate-500 group-hover:text-slate-300"></i>
                        Feuille d'heures
                    </Link>
                    <Link :href="route('admin.logs')" 
                        :class="[route().current('admin.logs') ? 'bg-teal-600/10 text-teal-400 border-l-4 border-teal-500' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-100']"
                        class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200">
                        <i class="pi pi-history mr-3 text-lg" :class="[route().current('admin.logs') ? 'text-teal-400' : 'text-slate-500 group-hover:text-slate-300']"></i>
                        Logs d'activité
                    </Link>
                </template>

                <!-- CP Specific Links -->
                <template v-else-if="roleName === 'CP'">
                    <Link href="/gestion-employees" class="group flex items-center px-4 py-3 text-sm font-medium text-slate-400 hover:bg-slate-800/50 hover:text-slate-100 rounded-lg transition-all duration-200">
                        <i class="pi pi-users mr-3 text-lg text-slate-500 group-hover:text-slate-300"></i>
                        Employés
                    </Link>
                    <Link :href="route('campaigns.index')" class="group flex items-center px-4 py-3 text-sm font-medium text-slate-400 hover:bg-slate-800/50 hover:text-slate-100 rounded-lg transition-all duration-200">
                        <i class="pi pi-flag mr-3 text-lg text-slate-500 group-hover:text-slate-300"></i>
                        Campagnes
                    </Link>
                    <Link :href="route('assignments.index')" class="group flex items-center px-4 py-3 text-sm font-medium text-slate-400 hover:bg-slate-800/50 hover:text-slate-100 rounded-lg transition-all duration-200">
                        <i class="pi pi-link mr-3 text-lg text-slate-500 group-hover:text-slate-300"></i>
                        Affectations
                    </Link>
                    <Link href="/planning" class="group flex items-center px-4 py-3 text-sm font-medium text-slate-400 hover:bg-slate-800/50 hover:text-slate-100 rounded-lg transition-all duration-200">
                        <i class="pi pi-calendar mr-3 text-lg text-slate-500 group-hover:text-slate-300"></i>
                        Plannings
                    </Link>
                    <Link :href="route('index.sup')" class="group flex items-center px-4 py-3 text-sm font-medium text-slate-400 hover:bg-slate-800/50 hover:text-slate-100 rounded-lg transition-all duration-200">
                        <i class="pi pi-clock mr-3 text-lg text-slate-500 group-hover:text-slate-300"></i>
                        Heures
                    </Link>
                    <Link :href="route('timesheet.index')" class="group flex items-center px-4 py-3 text-sm font-medium text-slate-400 hover:bg-slate-800/50 hover:text-slate-100 rounded-lg transition-all duration-200">
                        <i class="pi pi-file-edit mr-3 text-lg text-slate-500 group-hover:text-slate-300"></i>
                        Feuille d'heures
                    </Link>
                </template>

                <!-- SUP Specific Links -->
                <template v-else-if="roleName === 'SUP'">
                    <Link :href="route('campaigns.index')" class="group flex items-center px-4 py-3 text-sm font-medium text-slate-400 hover:bg-slate-800/50 hover:text-slate-100 rounded-lg transition-all duration-200">
                        <i class="pi pi-flag mr-3 text-lg text-slate-500 group-hover:text-slate-300"></i>
                        Campagnes
                    </Link>
                    <Link :href="route('assignments.index')" class="group flex items-center px-4 py-3 text-sm font-medium text-slate-400 hover:bg-slate-800/50 hover:text-slate-100 rounded-lg transition-all duration-200">
                        <i class="pi pi-link mr-3 text-lg text-slate-500 group-hover:text-slate-300"></i>
                        Affectations
                    </Link>
                    <Link href="/planning" class="group flex items-center px-4 py-3 text-sm font-medium text-slate-400 hover:bg-slate-800/50 hover:text-slate-100 rounded-lg transition-all duration-200">
                        <i class="pi pi-calendar mr-3 text-lg text-slate-500 group-hover:text-slate-300"></i>
                        Plannings
                    </Link>
                    <Link :href="route('index.sup')" class="group flex items-center px-4 py-3 text-sm font-medium text-slate-400 hover:bg-slate-800/50 hover:text-slate-100 rounded-lg transition-all duration-200">
                        <i class="pi pi-clock mr-3 text-lg text-slate-500 group-hover:text-slate-300"></i>
                        Heures
                    </Link>
                    <Link :href="route('timesheet.index')" class="group flex items-center px-4 py-3 text-sm font-medium text-slate-400 hover:bg-slate-800/50 hover:text-slate-100 rounded-lg transition-all duration-200">
                        <i class="pi pi-file-edit mr-3 text-lg text-slate-500 group-hover:text-slate-300"></i>
                        Feuille d'heures
                    </Link>
                </template>

                <!-- TC Specific Links -->
                <template v-else-if="roleName === 'TC'">
                    <Link :href="route('campaigns.index')" class="group flex items-center px-4 py-3 text-sm font-medium text-slate-400 hover:bg-slate-800/50 hover:text-slate-100 rounded-lg transition-all duration-200">
                        <i class="pi pi-flag mr-3 text-lg text-slate-500 group-hover:text-slate-300"></i>
                        Campagnes
                    </Link>
                    <Link :href="route('timesheet.index')" class="group flex items-center px-4 py-3 text-sm font-medium text-slate-400 hover:bg-slate-800/50 hover:text-slate-100 rounded-lg transition-all duration-200">
                        <i class="pi pi-file-edit mr-3 text-lg text-slate-500 group-hover:text-slate-300"></i>
                        Feuille d'heures
                    </Link>
                </template>
            </nav>

            <div class="p-4 border-t border-slate-800/50">
                <div class="bg-slate-800/30 rounded-xl p-4">
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Profil</p>
                    <div class="flex items-center gap-3">
                        <div class="h-8 w-8 rounded-full bg-teal-500 flex items-center justify-center text-xs font-bold text-white">
                            {{ user?.employee?.first_name?.charAt(0) || 'U' }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-slate-200 truncate">{{ user?.employee ? `${user.employee.first_name} ${user.employee.last_name}` : user?.email }}</p>
                            <p class="text-[10px] text-slate-500 truncate">{{ user?.role?.name }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Navbar -->
            <nav class="bg-white border-b border-slate-200 h-16 flex items-center justify-between px-8 sticky top-0 z-10">
                <div class="flex items-center gap-4">
                    <button class="sm:hidden text-slate-500 hover:text-slate-700">
                        <i class="pi pi-bars text-xl"></i>
                    </button>
                    <div class="text-slate-400 text-sm hidden md:flex items-center gap-2">
                        <i class="pi pi-clock"></i>
                        <span>{{ new Date().toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long' }) }}</span>
                    </div>
                </div>

                <div v-if="showLogoutButton" class="flex items-center gap-6">
                    <!-- Notifications Icon (Visual Only) -->
                    <button class="text-slate-400 hover:text-teal-600 transition-colors">
                        <i class="pi pi-bell text-lg"></i>
                    </button>

                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button class="flex items-center gap-2 group">
                                <div class="h-8 w-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500 group-hover:bg-teal-50 group-hover:text-teal-600 transition-all">
                                    <i class="pi pi-user text-sm"></i>
                                </div>
                                <i class="pi pi-chevron-down text-[10px] text-slate-400 group-hover:text-slate-600"></i>
                            </button>
                        </template>

                        <template #content>
                            <div class="px-4 py-3 border-b border-slate-100">
                                <p class="text-xs font-bold text-slate-800">{{ user?.email }}</p>
                                <p class="text-[10px] text-slate-500 mt-0.5">Role: {{ user?.role?.name }}</p>
                            </div>
                            <DropdownLink :href="route('profile.edit')" class="hover:bg-slate-50 text-slate-700">
                                <i class="pi pi-cog mr-2 text-xs text-slate-400"></i> Paramètres
                            </DropdownLink>
                            <DropdownLink :href="route('logout')" method="post" as="button" class="text-rose-600 hover:bg-rose-50 border-t border-slate-50">
                                <i class="pi pi-power-off mr-2 text-xs"></i> Déconnexion
                            </DropdownLink>
                        </template>
                    </Dropdown>
                </div>

                <Link v-else :href="route('login')" 
                    class="px-5 py-2 bg-teal-600 text-white text-sm font-bold rounded-lg hover:bg-teal-700 transition-all shadow-lg shadow-teal-600/20">
                    Se connecter
                </Link>
            </nav>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto p-8">
                <div class="max-w-7xl mx-auto">
                    <header v-if="$slots.header" class="mb-8">
                        <slot name="header" />
                    </header>
                    <slot />
                </div>
            </main>
        </div>
        <Toast />
    </div>
</template>
