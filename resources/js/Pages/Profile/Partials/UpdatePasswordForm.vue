<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value.focus();
            }
        },
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-sm font-black text-slate-900 uppercase tracking-widest">
                Changer le mot de passe
            </h2>

            <p class="mt-1 text-sm text-slate-500 font-medium">
                Assurez-vous que votre compte utilise un mot de passe long et aléatoire pour rester en sécurité.
            </p>
        </header>

        <form @submit.prevent="updatePassword" class="mt-8 space-y-6">
            <div class="grid gap-6">
                <div class="flex flex-col gap-2">
                    <InputLabel for="current_password" value="Mot de passe actuel" class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1" />
                    <div class="relative group">
                        <i class="pi pi-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-teal-500 transition-colors"></i>
                        <input
                            id="current_password"
                            ref="currentPasswordInput"
                            v-model="form.current_password"
                            type="password"
                            class="w-full pl-12 pr-4 py-3 bg-slate-50 border-slate-100 rounded-2xl focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 transition-all text-sm font-medium"
                            autocomplete="current-password"
                            placeholder="••••••••"
                        />
                    </div>
                    <InputError :message="form.errors.current_password" class="mt-1" />
                </div>

                <div class="flex flex-col gap-2">
                    <InputLabel for="password" value="Nouveau mot de passe" class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1" />
                    <div class="relative group">
                        <i class="pi pi-key absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-teal-500 transition-colors"></i>
                        <input
                            id="password"
                            ref="passwordInput"
                            v-model="form.password"
                            type="password"
                            class="w-full pl-12 pr-4 py-3 bg-slate-50 border-slate-100 rounded-2xl focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 transition-all text-sm font-medium"
                            autocomplete="new-password"
                            placeholder="••••••••"
                        />
                    </div>
                    <InputError :message="form.errors.password" class="mt-1" />
                </div>

                <div class="flex flex-col gap-2">
                    <InputLabel for="password_confirmation" value="Confirmer le nouveau mot de passe" class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1" />
                    <div class="relative group">
                        <i class="pi pi-shield absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-teal-500 transition-colors"></i>
                        <input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            class="w-full pl-12 pr-4 py-3 bg-slate-50 border-slate-100 rounded-2xl focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 transition-all text-sm font-medium"
                            autocomplete="new-password"
                            placeholder="••••••••"
                        />
                    </div>
                    <InputError :message="form.errors.password_confirmation" class="mt-1" />
                </div>
            </div>

            <div class="flex items-center gap-4 pt-4">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="bg-teal-600 hover:bg-teal-700 text-white px-8 py-3 rounded-2xl font-black uppercase tracking-widest text-[10px] shadow-lg shadow-teal-600/20 transition-all disabled:opacity-50"
                >
                    Mettre à jour le mot de passe
                </button>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm font-bold text-teal-600"
                    >
                        Enregistré.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
