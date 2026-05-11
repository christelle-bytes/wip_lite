<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';
import { Bar } from 'vue-chartjs';
import {
    Chart as ChartJS, Title, Tooltip, Legend,
    BarElement, LineElement, PointElement,
    CategoryScale, LinearScale, Filler
} from 'chart.js';
import { useToast } from 'primevue/usetoast';
import Toast from 'primevue/toast';

ChartJS.register(Title, Tooltip, Legend, BarElement, LineElement, PointElement, CategoryScale, LinearScale, Filler);

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({ totalEmployees: 0, activeCampaigns: 0, totalAssignments: 0, totalHours: 0, gap: 0, pendingTimesheets: 0 })
    },
    charts: {
        type: Object,
        default: () => ({ gapByEmployee: [], hoursByMonth: [] })
    }
});

const page = usePage();
const toast = useToast();
const userRole = computed(() => page.props?.auth?.user?.role);
const isAuthorized = computed(() => userRole.value?.name?.toUpperCase() === 'SUP');

// Mixed 1 : écart par employé — barres réelles + courbe planifiées
const gapMixedData = computed(() => {
    const items = props.charts?.gapByEmployee ?? [];
    return {
        labels: items.map(i => i.name),
        datasets: [
            {
                type: 'bar',
                label: 'Heures réelles',
                data: items.map(i => parseFloat(i.real_hours) || 0),
                backgroundColor: 'rgba(13,148,136,0.6)',
                borderRadius: 6,
                borderSkipped: false,
                order: 2,
            },
            {
                type: 'line',
                label: 'Heures planifiées',
                data: items.map(i => parseFloat(i.planned_hours) || 0),
                borderColor: '#0f172a',
                backgroundColor: 'transparent',
                pointBackgroundColor: '#0f172a',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                tension: 0.4,
                order: 1,
            },
        ],
    };
});

// Mixed 2 : évolution heures par mois — barres réelles + courbe planifiées
const hoursMixedData = computed(() => {
    const items = props.charts?.hoursByMonth ?? [];
    return {
        labels: items.map(i => i.month),
        datasets: [
            {
                type: 'bar',
                label: 'Heures réelles',
                data: items.map(i => parseFloat(i.real_hours) || 0),
                backgroundColor: 'rgba(13,148,136,0.6)',
                borderRadius: 6,
                borderSkipped: false,
                order: 2,
            },
            {
                type: 'line',
                label: 'Heures planifiées',
                data: items.map(i => parseFloat(i.planned_hours) || 0),
                borderColor: '#0f172a',
                backgroundColor: 'transparent',
                pointBackgroundColor: '#0f172a',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                tension: 0.4,
                order: 1,
            },
        ],
    };
});

const mixedOptions = {
    responsive: true,
    maintainAspectRatio: false,
    interaction: { mode: 'index', intersect: false },
    plugins: {
        legend: { position: 'top', labels: { usePointStyle: true, pointStyle: 'circle', font: { size: 12 } } },
        tooltip: { callbacks: { label: (ctx) => ` ${ctx.dataset.label} : ${ctx.parsed.y}h` } },
    },
    scales: {
        x: { ticks: { font: { size: 11 } }, grid: { display: false } },
        y: { beginAtZero: true, ticks: { callback: (v) => v + 'h' }, grid: { color: '#F3F4F6' } },
    },
};

onMounted(() => {
    if (isAuthorized.value) {
        toast.add({ severity: 'success', summary: 'Bienvenue', detail: 'Connecté en tant que Superviseur', life: 3000 });
    }
});
</script>

<template>
    <Toast />
    <Head title="Dashboard SUP" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight">Espace Superviseur</h2>
                    <p class="text-sm text-slate-400 font-medium">Gestion de la production et validation des heures</p>
                </div>
            </div>
        </template>

        <div class="py-6 space-y-10">
            <!-- KPI Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex flex-col gap-1">
                    <p class="text-[10px] uppercase font-bold text-slate-400 tracking-widest">Équipes</p>
                    <p class="text-3xl font-black text-slate-800">{{ stats.totalEmployees }}</p>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex flex-col gap-1">
                    <p class="text-[10px] uppercase font-bold text-slate-400 tracking-widest">Heures Réelles</p>
                    <p class="text-3xl font-black text-teal-600">{{ stats.totalHours }}h</p>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex flex-col gap-1">
                    <p class="text-[10px] uppercase font-bold text-slate-400 tracking-widest">Validation en attente</p>
                    <p class="text-3xl font-black text-amber-600">{{ stats.pendingTimesheets }}</p>
                </div>
                <div :class="[stats.gap < 0 ? 'bg-rose-50 border-rose-100' : 'bg-teal-50 border-teal-100']"
                    class="rounded-2xl p-6 shadow-sm border flex flex-col gap-1">
                    <p class="text-[10px] uppercase font-bold tracking-widest" :class="[stats.gap < 0 ? 'text-rose-400' : 'text-teal-500']">Productivité</p>
                    <p class="text-3xl font-black" :class="[stats.gap < 0 ? 'text-rose-600' : 'text-teal-700']">{{ stats.gap > 0 ? '+' : '' }}{{ stats.gap }}%</p>
                </div>
            </div>

            <!-- Mixed Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8">
                    <div class="mb-6">
                        <h3 class="text-lg font-black text-slate-800">Analyse de Performance</h3>
                        <p class="text-xs text-slate-400 mt-1 uppercase tracking-wider font-bold">Barres = réel · Courbe = planifié par agent</p>
                    </div>
                    <div class="h-96">
                        <Bar :data="gapMixedData" :options="mixedOptions" />
                    </div>
                </div>
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8">
                    <div class="mb-6">
                        <h3 class="text-lg font-black text-slate-800">Évolution de la Production</h3>
                        <p class="text-xs text-slate-400 mt-1 uppercase tracking-wider font-bold">Barres = réel · Courbe = planifié par mois</p>
                    </div>
                    <div class="h-96">
                        <Bar :data="hoursMixedData" :options="mixedOptions" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
