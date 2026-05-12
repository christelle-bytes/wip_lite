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

// Données de démonstration
const DEMO_GAP_EMPLOYEES = [
    { name: 'Alice M.', real_hours: 38, planned_hours: 40 },
    { name: 'Bruno K.', real_hours: 29, planned_hours: 40 },
    { name: 'Clara D.', real_hours: 43, planned_hours: 40 },
    { name: 'David L.', real_hours: 18, planned_hours: 40 },
    { name: 'Eva R.', real_hours: 40, planned_hours: 40 },
    { name: 'Franck B.', real_hours: 25, planned_hours: 40 },
];

const DEMO_HOURS_MONTH = [
    { month: 'Déc', real_hours: 820, planned_hours: 880 },
    { month: 'Jan', real_hours: 750, planned_hours: 880 },
    { month: 'Fév', real_hours: 910, planned_hours: 880 },
    { month: 'Mar', real_hours: 640, planned_hours: 880 },
    { month: 'Avr', real_hours: 870, planned_hours: 880 },
    { month: 'Mai', real_hours: 900, planned_hours: 880 },
];

// Graphique 1 : Performance par agent — barres réelles + courbe planifiée
const gapMixedData = computed(() => {
    const raw = props.charts?.gapByEmployee ?? [];
    const items = raw.length >= 3 ? raw : DEMO_GAP_EMPLOYEES;
    return {
        labels: items.map(i => i.name),
        datasets: [
            {
                type: 'bar',
                label: 'Heures réelles',
                data: items.map(i => parseFloat(i.real_hours) || 0),
                backgroundColor: 'rgba(29,158,117,0.75)',
                borderRadius: 6,
                borderSkipped: false,
                order: 2,
            },
            {
                type: 'line',
                label: 'Heures planifiées',
                data: items.map(i => parseFloat(i.planned_hours) || 0),
                borderColor: '#0f172a',
                borderDash: [5, 3],
                backgroundColor: 'transparent',
                pointBackgroundColor: '#0f172a',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 5,
                tension: 0.35,
                order: 1,
            },
        ],
    };
});

// Graphique 2 : Évolution mensuelle — barres réelles + courbe planifiée
const hoursMixedData = computed(() => {
    const raw = props.charts?.hoursByMonth ?? [];
    const items = raw.length >= 3 ? raw : DEMO_HOURS_MONTH;
    return {
        labels: items.map(i => i.month),
        datasets: [
            {
                type: 'bar',
                label: 'Heures réelles',
                data: items.map(i => parseFloat(i.real_hours) || 0),
                backgroundColor: 'rgba(29,158,117,0.75)',
                borderRadius: 6,
                borderSkipped: false,
                order: 2,
            },
            {
                type: 'line',
                label: 'Heures planifiées',
                data: items.map(i => parseFloat(i.planned_hours) || 0),
                borderColor: '#0f172a',
                borderDash: [5, 3],
                backgroundColor: 'transparent',
                pointBackgroundColor: '#0f172a',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 5,
                tension: 0.35,
                order: 1,
            },
        ],
    };
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    interaction: { mode: 'index', intersect: false },
    plugins: {
        legend: {
            position: 'top',
            align: 'start',
            labels: {
                usePointStyle: true,
                pointStyle: 'rectRounded',
                boxWidth: 10,
                boxHeight: 10,
                font: { size: 12 },
                padding: 20,
            }
        },
        tooltip: {
            backgroundColor: '#0f172a',
            titleColor: '#94a3b8',
            bodyColor: '#f1f5f9',
            padding: 12,
            cornerRadius: 8,
            callbacks: { label: (ctx) => ` ${ctx.dataset.label} : ${ctx.parsed.y}h` }
        },
    },
    scales: {
        x: {
            ticks: { font: { size: 11 }, color: '#6b7280' },
            grid: { display: false },
        },
        y: {
            beginAtZero: true,
            ticks: { font: { size: 11 }, color: '#6b7280', callback: (v) => v + 'h' },
            grid: { color: '#F3F4F6' },
        },
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

        <div class="py-6 space-y-8">

            <!-- KPI Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex flex-col gap-1">
                    <p class="text-[10px] uppercase font-bold text-slate-400 tracking-widest">Équipes</p>
                    <p class="text-3xl font-black text-slate-800">{{ stats.totalEmployees }}</p>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex flex-col gap-1">
                    <p class="text-[10px] uppercase font-bold text-slate-400 tracking-widest">Heures réelles</p>
                    <p class="text-3xl font-black text-teal-600">{{ stats.totalHours }}h</p>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex flex-col gap-1">
                    <p class="text-[10px] uppercase font-bold text-slate-400 tracking-widest">Validation en attente</p>
                    <p class="text-3xl font-black text-amber-600">{{ stats.pendingTimesheets }}</p>
                </div>

                <div
                    class="rounded-2xl p-6 shadow-sm border flex flex-col gap-1"
                    :class="stats.gap < 0 ? 'bg-rose-50 border-rose-100' : 'bg-teal-50 border-teal-100'"
                >
                    <p
                        class="text-[10px] uppercase font-bold tracking-widest"
                        :class="stats.gap < 0 ? 'text-rose-400' : 'text-teal-500'"
                    >
                        Productivité
                    </p>
                    <p
                        class="text-3xl font-black"
                        :class="stats.gap < 0 ? 'text-rose-600' : 'text-teal-700'"
                    >
                        {{ stats.gap > 0 ? '+' : '' }}{{ stats.gap }}%
                    </p>
                </div>

            </div>

            <!-- Graphiques -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                <!-- Graphique 1 : Performance par agent -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
                    <div class="mb-4">
                        <h3 class="text-lg font-black text-slate-800">Analyse de performance</h3>
                        <p class="text-xs text-slate-400 mt-1 uppercase tracking-wider font-bold">
                            Barres = réel · Courbe = planifié par agent
                        </p>
                    </div>
                    <div class="h-80 w-full">
                        <Bar :data="gapMixedData" :options="chartOptions" />
                    </div>
                </div>

                <!-- Graphique 2 : Évolution mensuelle -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
                    <div class="mb-4">
                        <h3 class="text-lg font-black text-slate-800">Évolution de la production</h3>
                        <p class="text-xs text-slate-400 mt-1 uppercase tracking-wider font-bold">
                            Barres = réel · Courbe = planifié par mois
                        </p>
                    </div>
                    <div class="h-80 w-full">
                        <Bar :data="hoursMixedData" :options="chartOptions" />
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
