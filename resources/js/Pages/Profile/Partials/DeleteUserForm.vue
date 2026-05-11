<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section class="space-y-6">
        <header>
            <h2 class="text-sm font-black text-rose-900 uppercase tracking-widest">
                Supprimer le compte
            </h2>

            <p class="mt-1 text-sm text-rose-700/70 font-medium">
                Une fois votre compte supprimé, toutes ses ressources et données seront définitivement effacées.
            </p>
        </header>

        <button
            @click="confirmUserDeletion"
            class="bg-rose-600 hover:bg-rose-700 text-white px-8 py-3 rounded-2xl font-black uppercase tracking-widest text-[10px] shadow-lg shadow-rose-600/20 transition-all"
        >
            Supprimer mon compte
        </button>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-10 bg-white rounded-[40px]">
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">
                    Êtes-vous sûr de vouloir supprimer votre compte ?
                </h2>

                <p class="mt-4 text-sm text-slate-500 font-medium leading-relaxed">
                    Une fois votre compte supprimé, toutes ses ressources et données seront définitivement effacées. Veuillez saisir votre mot de passe pour confirmer que vous souhaitez supprimer définitivement votre compte.
                </p>

                <div class="mt-8 flex flex-col gap-2">
                    <InputLabel for="password" value="Mot de passe" class="sr-only" />
                    <div class="relative group">
                        <i class="pi pi-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-teal-500 transition-colors"></i>
                        <input
                            id="password"
                            ref="passwordInput"
                            v-model="form.password"
                            type="password"
                            class="w-full pl-12 pr-4 py-3 bg-slate-50 border-slate-100 rounded-2xl focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 transition-all text-sm font-medium"
                            placeholder="Saisissez votre mot de passe pour confirmer"
                            @keyup.enter="deleteUser"
                        />
                    </div>
                    <InputError :message="form.errors.password" class="mt-1" />
                </div>

                <div class="mt-10 flex justify-end gap-4">
                    <button 
                        @click="closeModal"
                        class="px-6 py-3 text-[10px] font-black uppercase tracking-widest text-slate-500 hover:text-slate-700 transition-colors"
                    >
                        Annuler
                    </button>

                    <button
                        class="bg-rose-600 hover:bg-rose-700 text-white px-8 py-3 rounded-2xl font-black uppercase tracking-widest text-[10px] shadow-lg shadow-rose-600/20 transition-all disabled:opacity-50"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        Confirmer la suppression
                    </button>
                </div>
            </div>
        </Modal>
    </section>
</template>
