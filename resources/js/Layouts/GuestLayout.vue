<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useToast } from "primevue/usetoast";
import Toast from "primevue/toast";
import { computed, watch } from 'vue';

const page = usePage();
const toast = useToast();
const flash = computed(() => page.props.flash);

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
    <div
        class="flex min-h-screen flex-col items-center bg-gray-100 pt-6 sm:justify-center sm:pt-0"
    >
        <div>
            <Link href="/">
                <ApplicationLogo class="h-20 w-20 fill-current text-gray-500" />
            </Link>
        </div>

        <div
            class="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg"
        >
            <slot />
        </div>
        <Toast />
    </div>
</template>
