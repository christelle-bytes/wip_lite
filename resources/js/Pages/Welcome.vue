<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: { type: Boolean },
    canRegister: { type: Boolean },
    laravelVersion: { type: String, required: true },
    phpVersion: { type: String, required: true },
});
</script>

<template>
    <Head title="Bienvenue — GRH System" />

    <!-- Container avec l'image de fond principale -->
    <div class="relative min-h-screen w-full flex items-center justify-center bg-slate-900 p-4 md:p-8">
        
        <!-- Image de fond : Très visible, haute qualité -->
        <div class="absolute inset-0 z-0">
            <img 
                src="https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&q=80&w=1920" 
                alt="Modern Office" 
                class="w-full h-full object-cover"
            />
            <!-- Filtre léger pour l'élégance -->
            <div class="absolute inset-0 bg-slate-950/40 backdrop-blur-[2px]"></div>
        </div>

        <!-- Carte Centrale (Glassmorphism) -->
        <main class="relative z-10 w-full max-w-5xl">
            <div class="bg-white/[0.08] backdrop-blur-xl border border-white/20 rounded-[3rem] overflow-hidden shadow-2xl flex flex-col lg:flex-row">
                
                <!-- Côté Gauche : Branding & Image -->
                <div class="lg:w-5/12 relative hidden lg:block border-r border-white/10">
                    <img 
                        src="https://images.unsplash.com/photo-1556761175-b413da4baf72?auto=format&fit=crop&q=80&w=800" 
                        class="h-full w-full object-cover opacity-80" 
                        alt="RH Team"
                    />
                    <div class="absolute inset-0 bg-gradient-to-t from-teal-900/90 to-transparent flex flex-col justify-end p-10">
                        <div class="h-1 w-12 bg-teal-400 mb-4"></div>
                        <h2 class="text-2xl font-bold text-white mb-2">Performance & Équité</h2>
                        <p class="text-teal-100/70 text-sm leading-relaxed">
                            Optimisez la gestion de vos collaborateurs avec des outils de nouvelle génération.
                        </p>
                    </div>
                </div>

                <!-- Côté Droit : Contenu & Actions -->
                <div class="flex-1 p-8 md:p-14 lg:p-16 flex flex-col justify-center">
                    
                    <!-- Logo / Badge -->
                    <div class="mb-10">
                        <div class="inline-flex items-center gap-2 bg-teal-500/20 px-4 py-2 rounded-full border border-teal-500/30">
                            <div class="w-2 h-2 rounded-full bg-teal-400 animate-pulse"></div>
                            <span class="text-teal-300 text-[10px] font-black uppercase tracking-[0.2em]">Plateforme GRH</span>
                        </div>
                    </div>

                    <!-- Titre Principal -->
                    <h1 class="text-4xl md:text-6xl font-black text-white leading-tight mb-6">
                        L'avenir de votre <br>
                        <span class="text-teal-400 italic">Capital Humain.</span>
                    </h1>

                    <!-- Description -->
                    <p class="text-slate-300 text-lg mb-10 max-w-md font-medium">
                        Une solution complète pour le suivi des employés, la gestion des carrières et la productivité opérationnelle.
                    </p>

                    <!-- Actions -->
                    <div v-if="canLogin" class="flex flex-col sm:flex-row gap-4">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="px-10 py-5 bg-teal-500 hover:bg-teal-400 text-slate-950 font-black rounded-2xl transition-all shadow-lg shadow-teal-500/20 text-center uppercase tracking-widest text-xs"
                        >
                            Accéder au Dashboard
                        </Link>

                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="px-10 py-5 bg-teal-500 hover:bg-teal-400 text-slate-950 font-black rounded-2xl transition-all shadow-lg shadow-teal-500/20 text-center uppercase tracking-widest text-xs"
                            >
                                Connexion
                            </Link>
                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="px-10 py-5 bg-white/5 hover:bg-white/10 text-white border border-white/10 font-black rounded-2xl transition-all text-center uppercase tracking-widest text-xs"
                            >
                                S'inscrire
                            </Link>
                        </template>
                    </div>

                    <!-- Footer Interne -->
                    <div class="mt-16 flex items-center gap-6 pt-8 border-t border-white/10">
                        <div class="flex flex-col">
                            <span class="text-[9px] text-slate-500 uppercase font-black tracking-tighter">Stack</span>
                            <span class="text-xs text-slate-300 font-bold">Laravel & Vue 3</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-[9px] text-slate-500 uppercase font-black tracking-tighter">Sécurité</span>
                            <span class="text-xs text-slate-300 font-bold">SSL</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Copyright externe -->
            <div class="mt-8 text-center text-white/40 text-[10px] font-black uppercase tracking-[0.4em]">
                &copy; 2024 GRH System &bull; Designed for excellence
            </div>
        </main>
    </div>
</template>

<style scoped>
/* Animation légère d'entrée */
main {
    animation: fadeIn 0.8s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* On s'assure que le fond ne bouge pas */
.bg-slate-900 {
    background-attachment: fixed;
}
</style>