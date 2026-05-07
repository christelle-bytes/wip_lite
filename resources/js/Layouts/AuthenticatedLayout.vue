<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

const page = usePage();
const showingNavigationDropdown = ref(false);

// Calcul du rôle pour afficher les liens conditionnels
const user = computed(() => page.props.auth.user);
const isAdmin = computed(() => user.value?.role?.name === 'admin');
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex">
        <aside class="w-64 bg-slate-900 text-white hidden sm:flex flex-col">
            <div class="p-6">
                <h1 class="text-xl font-bold text-blue-400">GRH Gestion RH</h1>
            </div>

            <nav class="flex-1 px-4 space-y-2">
                <Link :href="route('dashboard')" class="block p-2 hover:bg-slate-800 rounded">Tableau de bord</Link>

                <template v-if="user?.role?.name === 'Admin' || isAdmin">
                    <Link href="#" class="block p-2 hover:bg-slate-800 rounded">Employés</Link>
                    <Link href="#" class="block p-2 hover:bg-slate-800 rounded">Campagnes</Link>
                    <Link href="#" class="block p-2 hover:bg-slate-800 rounded">Affectations</Link>
                    <Link href="#" class="block p-2 hover:bg-slate-800 rounded">Plannings</Link>
                    <Link href="#" class="block p-2 hover:bg-slate-800 rounded">Heures</Link>
                </template>
                <template v-else-if ="user?.role?.name === 'CP' || isAdmin">
                    <Link href="#" class="block p-2 hover:bg-slate-800 rounded">Employés</Link>
                    <Link href="#" class="block p-2 hover:bg-slate-800 rounded">Campagnes</Link>
                    <Link href="#" class="block p-2 hover:bg-slate-800 rounded">Affectations</Link>
                     <Link href="#" class="block p-2 hover:bg-slate-800 rounded">Plannings</Link>
                </template>
                <template v-else-if ="user?.role?.name === 'SUP' || isAdmin">
                    <Link href="#" class="block p-2 hover:bg-slate-800 rounded">Employés</Link>
                    <Link href="#" class="block p-2 hover:bg-slate-800 rounded">Campagnes</Link>
                    <Link href="#" class="block p-2 hover:bg-slate-800 rounded">Affectations</Link>
                </template>
                <template v-else-if ="user?.role?.name === 'TC' || isAdmin">
                    <Link href="#" class="block p-2 hover:bg-slate-800 rounded">Campagnes</Link>
                    <Link href="#" class="block p-2 hover:bg-slate-800 rounded">Affectations</Link>
                </template>
            </nav>
        </aside>

        <div class="flex-1 flex flex-col">
            <nav class="bg-white border-b border-gray-100 h-16 flex items-center justify-end px-8">
                <Dropdown align="right" width="48">
                    <template #trigger>
                        <button class="text-sm font-medium text-gray-500 hover:text-gray-700">
                            {{ user.name }}
                        </button>
                    </template>
                    <template #content>
                        <DropdownLink :href="route('profile.edit')">Profile</DropdownLink>
                        <DropdownLink :href="route('logout')" method="post" as="button">Log Out</DropdownLink>
                    </template>
                </Dropdown>
            </nav>

            <header v-if="$slots.header" class="bg-white shadow">
                <div class="px-8 py-6">
                    <slot name="header" />
                </div>
            </header>

            <main class="p-8">
                <slot />
            </main>
        </div>
    </div>
</template>