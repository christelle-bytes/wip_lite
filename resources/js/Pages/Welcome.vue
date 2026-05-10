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
    <Head title="Bienvenue — GRH System" />

    <div class="relative min-h-screen flex items-center justify-center bg-slate-950 selection:bg-teal-500 selection:text-white">

        <!-- Arrière-plan stylisé -->
        <div class="absolute inset-0 z-0 overflow-hidden">
            <div class="absolute -top-[20%] -left-[10%] w-[60%] h-[60%] rounded-full bg-teal-600/20 blur-[120px]"></div>
            <div class="absolute -bottom-[20%] -right-[10%] w-[50%] h-[50%] rounded-full bg-slate-800/30 blur-[100px]"></div>
            <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1557426272-fc759fbb7a8d?auto=format&fit=crop&q=80&w=1920')] bg-cover bg-center opacity-10 grayscale"></div>
        </div>

        <!-- Contenu Principal -->
        <div class="relative z-10 w-full max-w-4xl px-6">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                
                <div class="text-left">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-500/10 border border-teal-500/20 mb-8">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-teal-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-teal-500"></span>
                        </span>
                        <span class="text-[10px] font-black uppercase tracking-[0.2em] text-teal-400">Solution RH Intelligente</span>
                    </div>
                    
                    <h1 class="text-6xl font-black text-white tracking-tight leading-[1.1] mb-6">
                        Gérez vos équipes avec <span class="text-teal-500 italic">précision.</span>
                    </h1>
                    
                    <p class="text-slate-400 text-lg mb-10 leading-relaxed font-medium">
                        Une plateforme moderne conçue pour simplifier la gestion RH, le suivi des performances et la planification des campagnes.
                    </p>

                    <div v-if="canLogin" class="flex flex-wrap gap-5">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="px-8 py-4 bg-teal-600 text-white font-black rounded-2xl hover:bg-teal-700 transition-all shadow-xl shadow-teal-600/20 uppercase tracking-widest text-xs"
                        >
                            Accéder au Dashboard
                        </Link>

                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="px-8 py-4 bg-teal-600 text-white font-black rounded-2xl hover:bg-teal-700 transition-all shadow-xl shadow-teal-600/20 uppercase tracking-widest text-xs"
                            >
                                Connexion sécurisée
                            </Link>
                            <a href="#features" class="px-8 py-4 bg-white/5 text-slate-300 font-black rounded-2xl border border-white/10 hover:bg-white/10 transition-all uppercase tracking-widest text-xs">
                                Découvrir
                            </a>
                        </template>
                    </div>
                </div>

                <div class="hidden lg:block">
                    <div class="relative group">
                        <div class="absolute inset-0 bg-teal-500/20 rounded-[40px] blur-2xl group-hover:bg-teal-500/30 transition-all duration-500"></div>
                        <div class="relative bg-slate-900/50 backdrop-blur-xl border border-white/10 p-8 rounded-[40px] shadow-2xl">
                            <!-- Mini Dashboard Mockup -->
                            <div class="space-y-6">
                                <div class="flex items-center justify-between">
                                    <div class="h-4 w-24 bg-slate-700 rounded-full"></div>
                                    <div class="h-8 w-8 bg-teal-500 rounded-lg"></div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="h-20 bg-slate-800 rounded-2xl border border-white/5 p-4">
                                        <div class="h-2 w-12 bg-slate-600 rounded-full mb-3"></div>
                                        <div class="h-4 w-16 bg-teal-400 rounded-full"></div>
                                    </div>
                                    <div class="h-20 bg-slate-800 rounded-2xl border border-white/5 p-4">
                                        <div class="h-2 w-12 bg-slate-600 rounded-full mb-3"></div>
                                        <div class="h-4 w-16 bg-slate-400 rounded-full"></div>
                                    </div>
                                </div>
                                <div class="h-40 bg-slate-800/50 rounded-2xl border border-white/5"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer discret -->
            <footer class="mt-20 text-center lg:text-left text-slate-600 text-[10px] font-black uppercase tracking-[0.3em]">
                &copy; 2024 GRH System &bull; Powered by Modern HR Tech
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
