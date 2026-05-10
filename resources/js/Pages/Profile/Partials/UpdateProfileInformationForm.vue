<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});
</script>

<template>
    <section>
        <header>
            <h2 class="text-sm font-black text-slate-900 uppercase tracking-widest">
                Informations du compte
            </h2>

            <p class="mt-1 text-sm text-slate-500 font-medium">
                Mettez à jour les informations de profil et l'adresse e-mail de votre compte.
            </p>
        </header>

        <form
            @submit.prevent="form.patch(route('profile.update'))"
            class="mt-8 space-y-6"
        >
            <div class="grid gap-6">
                <div class="flex flex-col gap-2">
                    <InputLabel for="name" value="Nom complet" class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1" />
                    <div class="relative group">
                        <i class="pi pi-user absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-teal-500 transition-colors"></i>
                        <input
                            id="name"
                            type="text"
                            class="w-full pl-12 pr-4 py-3 bg-slate-50 border-slate-100 rounded-2xl focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 transition-all text-sm font-medium"
                            v-model="form.name"
                            required
                            autofocus
                            autocomplete="name"
                            placeholder="Votre nom"
                        />
                    </div>
                    <InputError class="mt-1" :message="form.errors.name" />
                </div>

                <div class="flex flex-col gap-2">
                    <InputLabel for="email" value="Adresse Email" class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1" />
                    <div class="relative group">
                        <i class="pi pi-envelope absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-teal-500 transition-colors"></i>
                        <input
                            id="email"
                            type="email"
                            class="w-full pl-12 pr-4 py-3 bg-slate-50 border-slate-100 rounded-2xl focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 transition-all text-sm font-medium"
                            v-model="form.email"
                            required
                            autocomplete="username"
                            placeholder="nom@exemple.com"
                        />
                    </div>
                    <InputError class="mt-1" :message="form.errors.email" />
                </div>
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="mt-4 text-sm text-slate-800 bg-amber-50 p-4 rounded-2xl border border-amber-100">
                    Votre adresse e-mail n'est pas vérifiée.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="ml-2 font-black text-amber-600 uppercase tracking-widest text-[10px] hover:text-amber-700 underline"
                    >
                        Renvoyer l'e-mail de vérification
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-sm font-bold text-teal-600"
                >
                    Un nouveau lien de vérification a été envoyé à votre adresse e-mail.
                </div>
            </div>

            <div class="flex items-center gap-4 pt-4">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="bg-teal-600 hover:bg-teal-700 text-white px-8 py-3 rounded-2xl font-black uppercase tracking-widest text-[10px] shadow-lg shadow-teal-600/20 transition-all disabled:opacity-50"
                >
                    Enregistrer les modifications
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
