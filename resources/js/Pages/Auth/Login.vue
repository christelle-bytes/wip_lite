<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: { type: Boolean },
    status: { type: String },
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
    <Head title="Connexion — GRH System" />

    <div class="relative min-h-screen w-full flex items-center justify-center p-4 overflow-hidden bg-slate-950 font-sans">
        
        <!-- Background : Image thématique RH ultra-visible -->
        <div class="absolute inset-0 z-0">
            <img 
                src="https://images.unsplash.com/photo-1497215728101-856f4ea42174?auto=format&fit=crop&q=80&w=1920" 
                alt="Modern Workplace" 
                class="w-full h-full object-cover opacity-50"
            />
            <!-- Overlay progressif pour garder le focus sur le formulaire -->
            <div class="absolute inset-0 bg-gradient-to-br from-slate-950/90 via-slate-950/40 to-teal-900/20"></div>
            
            <!-- Effet de lumière diffuse -->
            <div class="absolute top-1/4 left-1/4 w-[500px] h-[500px] bg-teal-500/10 blur-[120px] rounded-full"></div>
        </div>

        <div class="relative z-10 w-full max-w-[480px]">
            <!-- Carte en Verre (Glassmorphism) -->
            <div class="bg-white/[0.03] backdrop-blur-2xl border border-white/10 p-8 md:p-12 rounded-[3rem] shadow-2xl space-y-10">
                
                <!-- Header du Formulaire -->
                <div class="text-center space-y-3">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-500/10 border border-teal-500/20 mb-2">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-teal-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-teal-500"></span>
                        </span>
                        <span class="text-[9px] font-black uppercase tracking-[0.2em] text-teal-400">Accès Sécurisé</span>
                    </div>
                    <h2 class="text-4xl font-black text-white tracking-tight">Bon retour !</h2>
                    <p class="text-slate-400 text-sm font-medium">Entrez vos accès pour gérer vos équipes.</p>
                </div>

                <!-- Messages d'État / Erreurs -->
                <div v-if="status" class="rounded-2xl bg-teal-500/10 p-4 text-[10px] font-black text-teal-400 border border-teal-500/20 text-center uppercase tracking-widest">
                    {{ status }}
                </div>

                <div v-if="Object.keys(form.errors).length" class="rounded-2xl bg-rose-500/10 p-4 border border-rose-500/20 animate-shake">
                    <div class="text-[10px] font-black text-rose-400 uppercase tracking-widest text-center">
                        Identifiants incorrects ou manquants
                    </div>
                </div>

                <!-- Formulaire -->
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="space-y-5">
                        <!-- Champ Email -->
                        <div class="group">
                            <label for="email" class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-4 mb-2 block transition-colors group-focus-within:text-teal-500">
                                Adresse Professionnelle
                            </label>
                            <input
                                id="email"
                                type="email"
                                v-model="form.email"
                                required
                                autofocus
                                placeholder="nom@entreprise.com"
                                class="w-full px-6 py-4 bg-white/5 border border-white/10 rounded-2xl focus:border-teal-500/50 focus:ring-4 focus:ring-teal-500/10 transition-all text-white text-sm font-medium placeholder:text-slate-600"
                            />
                        </div>

                        <!-- Champ Password -->
                        <div class="group">
                            <div class="flex items-center justify-between px-4 mb-2">
                                <label for="password" class="text-[10px] font-black text-slate-500 uppercase tracking-widest transition-colors group-focus-within:text-teal-500">Mot de passe</label>
                                <Link
                                    v-if="canResetPassword"
                                    :href="route('password.request')"
                                    class="text-[9px] font-bold text-teal-500/60 uppercase tracking-widest hover:text-teal-400 transition-colors"
                                >
                                    Oublié ?
                                </Link>
                            </div>
                            <input
                                id="password"
                                type="password"
                                v-model="form.password"
                                required
                                placeholder="••••••••"
                                class="w-full px-6 py-4 bg-white/5 border border-white/10 rounded-2xl focus:border-teal-500/50 focus:ring-4 focus:ring-teal-500/10 transition-all text-white text-sm font-medium placeholder:text-slate-600"
                            />
                        </div>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center px-4">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <Checkbox name="remember" v-model:checked="form.remember" class="border-white/10 bg-white/5 text-teal-600 rounded-lg focus:ring-teal-500/20" />
                            <span class="text-xs font-bold text-slate-400 group-hover:text-slate-200 transition-colors">Session persistante</span>
                        </label>
                    </div>

                    <!-- Bouton Submit -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full py-5 bg-teal-600 text-white font-black rounded-2xl hover:bg-teal-500 hover:scale-[1.02] active:scale-[0.98] transition-all shadow-xl shadow-teal-900/40 uppercase tracking-[0.2em] text-xs flex items-center justify-center gap-3 group"
                    >
                        <span v-if="form.processing" class="animate-spin h-4 w-4 border-2 border-white/30 border-t-white rounded-full"></span>
                        <span v-else>S'authentifier</span>
                        <svg v-if="!form.processing" class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                </form>

                <!-- Lien Retour -->
                <div class="text-center">
                    <Link href="/" class="text-[9px] font-black text-slate-500 uppercase tracking-[0.3em] hover:text-white transition-colors">
                        &larr; Retour à l'accueil
                    </Link>
                </div>
            </div>

            <!-- Footer discret -->
            <p class="mt-8 text-center text-white/20 text-[9px] font-black uppercase tracking-[0.4em]">
                GRH System &bull; Secure Environment
            </p>
        </div>
    </div>
</template>

<style scoped>
/* Animation d'entrée pour la carte */
.bg-white\/\[0\.03\] {
    animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

.animate-shake {
    animation: shake 0.4s ease-in-out;
}
</style>