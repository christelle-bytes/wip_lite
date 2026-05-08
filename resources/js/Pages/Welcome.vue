<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

function handleImageError() {
    document.getElementById('screenshot-container')?.classList.add('!hidden');
    document.getElementById('docs-card')?.classList.add('!row-span-1');
    document.getElementById('docs-card-content')?.classList.add('!flex-row');
    document.getElementById('background')?.classList.add('!hidden');
}
</script>


<template>
    <Head title="Bienvenue - Gestion des Employés" />

    <div class="relative min-h-screen flex items-center justify-center bg-gray-900 selection:bg-blue-500 selection:text-white">

        <!-- Image d'arrière-plan professionnelle -->
        <div class="absolute inset-0 z-0">
            <img
                src="https://images.unsplash.com/photo-1497215728101-856f4ea42174?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80"
                class="w-full h-full object-cover opacity-40"
                alt="Background"
            />
            <div class="absolute inset-0 bg-gradient-to-br from-blue-900/40 via-black/60 to-gray-900/80"></div>
        </div>

        <!-- Contenu Principal -->
        <div class="relative z-10 w-full max-w-2xl px-6">

            <!-- Carte avec effet de transparence (Glassmorphism) -->
            <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-3xl p-10 shadow-2xl text-center">

                <!-- Logo ou Icône de l'application -->
                <div class="mb-6 flex justify-center">
                    <div class="p-4 bg-blue-600 rounded-2xl shadow-lg shadow-blue-500/50">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>

                <h1 class="text-4xl font-extrabold text-white tracking-tight mb-4">
                    Système de Gestion RH
                </h1>

                <p class="text-gray-200 text-lg mb-10 leading-relaxed">
                    Plateforme centralisée pour la gestion de vos collaborateurs,
                    suivi des performances et administration simplifiée.
                </p>

                <!-- Navigation Logique -->
                <div v-if="canLogin" class="flex flex-col sm:flex-row gap-4 justify-center">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('reporting.index')"
                        class="px-8 py-4 bg-white text-blue-900 font-bold rounded-xl hover:bg-gray-100 transition duration-300 shadow-xl"
                    >
                        Aller au Tableau de Bord
                    </Link>

                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="px-8 py-4 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition duration-300 shadow-lg shadow-blue-600/30"
                        >
                            Connexion
                        </Link>

                        <!-- <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="px-8 py-4 bg-white/10 text-white font-bold border border-white/30 rounded-xl hover:bg-white/20 transition duration-300"
                        >
                            Créer un compte
                        </Link> -->
                    </template>
                </div>
            </div>

            <!-- Footer discret -->
            <footer class="mt-8 text-center text-gray-400 text-sm">
                Laravel v{{ laravelVersion }} (PHP v{{ phpVersion }}) &bull; &copy; 2024 HR Management System
            </footer>
        </div>
    </div>
</template>

<style scoped>
/* Petit effet de flottement pour rendre le tout plus dynamique */
@keyframes float {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
    100% { transform: translateY(0px); }
}

.backdrop-blur-md {
    animation: float 6s ease-in-out infinite;
}
</style>
