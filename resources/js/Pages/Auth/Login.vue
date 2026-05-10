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
    <Head title="Connexion" />

    <div class="min-h-screen w-full flex items-center justify-center bg-slate-950 selection:bg-teal-500 selection:text-white relative overflow-hidden">
        
        <!-- Background elements matching Welcome page -->
        <div class="absolute inset-0 z-0 overflow-hidden">
            <div class="absolute -top-[20%] -left-[10%] w-[60%] h-[60%] rounded-full bg-teal-600/10 blur-[120px]"></div>
            <div class="absolute -bottom-[20%] -right-[10%] w-[50%] h-[50%] rounded-full bg-slate-800/20 blur-[100px]"></div>
        </div>

        <div class="relative z-10 w-full max-w-md px-6">
            <div class="bg-slate-900/50 backdrop-blur-xl border border-white/10 p-10 rounded-[40px] shadow-2xl space-y-8">
                
                <!-- Logo/Header -->
                <div class="text-center space-y-4">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-500/10 border border-teal-500/20 mb-2">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-teal-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-teal-500"></span>
                        </span>
                        <span class="text-[9px] font-black uppercase tracking-[0.2em] text-teal-400">Accès Sécurisé</span>
                    </div>
                    <h2 class="text-3xl font-black text-white tracking-tight">
                        Bon retour !
                    </h2>
                    <p class="text-slate-400 text-sm font-medium">
                        Identifiez-vous pour accéder à votre espace.
                    </p>
                </div>

                <!-- Status & Errors -->
                <div v-if="status" class="rounded-2xl bg-teal-500/10 p-4 text-xs font-bold text-teal-400 border border-teal-500/20 text-center uppercase tracking-widest">
                    {{ status }}
                </div>

                <div v-if="Object.keys(form.errors).length" class="rounded-2xl bg-rose-500/10 p-4 border border-rose-500/20">
                    <div class="flex items-center gap-3">
                        <i class="pi pi-exclamation-circle text-rose-500"></i>
                        <div class="text-xs font-bold text-rose-400 uppercase tracking-widest">
                            Identifiants incorrects
                        </div>
                    </div>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="space-y-5">
                        <div class="flex flex-col gap-2">
                            <label for="email" class="text-[10px] font-black text-slate-500 uppercase tracking-[0.1em] ml-1">Adresse Email</label>
                            <div class="relative group">
                                <i class="pi pi-envelope absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 group-focus-within:text-teal-500 transition-colors"></i>
                                <input
                                    id="email"
                                    type="email"
                                    class="w-full pl-12 pr-4 py-4 bg-slate-800/50 border border-white/5 rounded-2xl focus:border-teal-500/50 focus:ring-4 focus:ring-teal-500/10 transition-all text-white text-sm font-medium placeholder:text-slate-600"
                                    v-model="form.email"
                                    required
                                    autofocus
                                    placeholder="nom@entreprise.com"
                                />
                            </div>
                        </div>

                        <div class="flex flex-col gap-2">
                            <div class="flex items-center justify-between ml-1">
                                <label for="password" class="text-[10px] font-black text-slate-500 uppercase tracking-[0.1em]">Mot de passe</label>
                                <Link
                                    v-if="canResetPassword"
                                    :href="route('password.request')"
                                    class="text-[9px] font-black text-teal-500 uppercase tracking-widest hover:text-teal-400 transition-colors"
                                >
                                    Oublié ?
                                </Link>
                            </div>
                            <div class="relative group">
                                <i class="pi pi-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 group-focus-within:text-teal-500 transition-colors"></i>
                                <input
                                    id="password"
                                    type="password"
                                    class="w-full pl-12 pr-4 py-4 bg-slate-800/50 border border-white/5 rounded-2xl focus:border-teal-500/50 focus:ring-4 focus:ring-teal-500/10 transition-all text-white text-sm font-medium placeholder:text-slate-600"
                                    v-model="form.password"
                                    required
                                    placeholder="••••••••"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between px-1">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <Checkbox name="remember" v-model:checked="form.remember" class="border-white/10 bg-slate-800 text-teal-500 rounded-md focus:ring-teal-500/20" />
                            <span class="text-xs font-bold text-slate-400 group-hover:text-slate-300 transition-colors">Se souvenir de moi</span>
                        </label>
                    </div>

                    <button
                        type="submit"
                        :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                        :disabled="form.processing"
                        class="w-full py-4 bg-teal-600 text-white font-black rounded-2xl hover:bg-teal-700 transition-all shadow-xl shadow-teal-600/20 uppercase tracking-widest text-xs flex items-center justify-center gap-2 group"
                    >
                        <span>Se connecter</span>
                        <i class="pi pi-arrow-right text-[10px] group-hover:translate-x-1 transition-transform"></i>
                    </button>
                </form>

                <div class="text-center pt-4">
                    <Link href="/" class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] hover:text-teal-500 transition-colors flex items-center justify-center gap-2">
                        <i class="pi pi-arrow-left text-[8px]"></i>
                        Retour à l'accueil
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
