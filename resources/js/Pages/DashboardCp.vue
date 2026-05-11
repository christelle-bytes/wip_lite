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
        default: () => ({ activeCampaigns: 0, totalAssignments: 0, totalEmployees: 0, totalHours: 0, gap: 0 })
    },
    charts: {
        type: Object,
        default: () => ({ presenceByEmployee: [], performanceByEmployee: [] })
    }
});

const page = usePage();
const toast = useToast();
const userRole = computed(() => page.props?.auth?.user?.role);
const isAuthorized = computed(() => userRole.value?.name?.toUpperCase() === 'CP');

// Données de démonstration
const DEMO_PRESENCE = [
    { name: 'Alice M.', presence_rate: 92 },
    { name: 'Bruno K.', presence_rate: 74 },
    { name: 'Clara D.', presence_rate: 88 },
    { name: 'David L.', presence_rate: 45 },
    { name: 'Eva R.', presence_rate: 95 },
    { name: 'Franck B.', presence_rate: 61 },
];

const DEMO_PERFORMANCE = [
    { name: 'Alice M.', real_hours: 38, planned_hours: 40 },
    { name: 'Bruno K.', real_hours: 29, planned_hours: 40 },
    { name: 'Clara D.', real_hours: 42, planned_hours: 40 },
    { name: 'David L.', real_hours: 18, planned_hours: 40 },
    { name: 'Eva R.', real_hours: 40, planned_hours: 40 },
    { name: 'Franck B.', real_hours: 25, planned_hours: 40 },
];

// Graphique 1 : Taux de présence — barres colorées + ligne seuil 80%
const presenceMixedData = computed(() => {
    const raw = props.charts?.presenceByEmployee ?? [];
    const items = raw.length >= 3 ? raw : DEMO_PRESENCE;
    return {
        labels: items.map(i => i.name),
        datasets: [
            {
                type: 'bar',
                label: 'Taux de présence (%)',
                data: items.map(i => i.presence_rate),
                backgroundColor: items.map(i =>
                    i.presence_rate >= 80 ? 'rgba(29,158,117,0.75)' :
                    i.presence_rate >= 50 ? 'rgba(245,158,11,0.75)' : 'rgba(244,63,94,0.75)'
                ),
                borderRadius: 6,
                borderSkipped: false,
                order: 2,
            },
            {
                type: 'line',
                label: 'Seuil 80%',
                data: items.map(() => 80),
                borderColor: '#ef4444',
                borderDash: [6, 3],
                backgroundColor: 'transparent',
                pointRadius: 0,
                tension: 0,
                order: 1,
            },
        ],
    };
});

// Graphique 2 : Réel vs Planifié — barres + courbe pointillée
const performanceMixedData = computed(() => {
    const raw = props.charts?.performanceByEmployee ?? [];
    const items = raw.length >= 3 ? raw : DEMO_PERFORMANCE;
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
            callbacks: { label: (ctx) => ` ${ctx.dataset.label} : ${ctx.parsed.y}` }
        },
    },
    scales: {
        x: {
            ticks: { font: { size: 11 }, color: '#6b7280' },
            grid: { display: false },
        },
        y: {
            beginAtZero: true,
            ticks: { precision: 0, font: { size: 11 }, color: '#6b7280' },
            grid: { color: '#F3F4F6' },
        },
    },
};

onMounted(() => {
    if (isAuthorized.value) {
        toast.add({ severity: 'success', summary: 'Bienvenue', detail: 'Connecté en tant que Chef Plateau', life: 3000 });
    }
});
</script>

<template>
    <Toast />
    <Head title="Dashboard CP" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight">Espace Chef de Plateau</h2>
                    <p class="text-sm text-slate-400 font-medium">Suivi opérationnel des campagnes</p>
                </div>
            </div>
        </template>

        <div class="py-6 space-y-8">

            <!-- KPI Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">

                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex flex-col gap-1">
                    <p class="text-[10px] uppercase font-bold text-slate-400 tracking-widest">Campagnes</p>
                    <p class="text-3xl font-black text-teal-600">{{ stats.activeCampaigns }}</p>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex flex-col gap-1">
                    <p class="text-[10px] uppercase font-bold text-slate-400 tracking-widest">Affectations</p>
                    <p class="text-3xl font-black text-slate-800">{{ stats.totalAssignments }}</p>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex flex-col gap-1">
                    <p class="text-[10px] uppercase font-bold text-slate-400 tracking-widest">Effectif</p>
                    <p class="text-3xl font-black text-slate-800">{{ stats.totalEmployees }}</p>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex flex-col gap-1">
                    <p class="text-[10px] uppercase font-bold text-slate-400 tracking-widest">Volume heures</p>
                    <p class="text-3xl font-black text-teal-600">{{ stats.totalHours }}h</p>
                </div>

                <div
                    class="rounded-2xl p-6 shadow-sm border flex flex-col gap-1"
                    :class="stats.gap < 0 ? 'bg-rose-50 border-rose-100' : 'bg-teal-50 border-teal-100'"
                >
                    <p
                        class="text-[10px] uppercase font-bold tracking-widest"
                        :class="stats.gap < 0 ? 'text-rose-400' : 'text-teal-500'"
                    >
                        Écart Prod.
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

                <!-- Graphique 1 : Taux de présence -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
                    <div class="mb-4">
                        <h3 class="text-lg font-black text-slate-800">Taux de présence</h3>
                        <p class="text-xs text-slate-400 mt-1 uppercase tracking-wider font-bold">
                            Barres = taux · Ligne rouge = seuil 80%
                        </p>
                        <div class="flex items-center gap-4 mt-3 text-xs text-slate-500 font-medium">
                            <span class="flex items-center gap-1.5">
                                <span class="inline-block w-3 h-3 rounded-sm bg-teal-500/75"></span> ≥ 80%
                            </span>
                            <span class="flex items-center gap-1.5">
                                <span class="inline-block w-3 h-3 rounded-sm bg-amber-400/75"></span> 50–79%
                            </span>
                            <span class="flex items-center gap-1.5">
                                <span class="inline-block w-3 h-3 rounded-sm bg-rose-400/75"></span> &lt; 50%
                            </span>
                        </div>
                    </div>
                    <div class="h-80 w-full">
                        <Bar :data="presenceMixedData" :options="chartOptions" />
                    </div>
                </div>

                <!-- Graphique 2 : Réel vs Planifié -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
                    <div class="mb-4">
                        <h3 class="text-lg font-black text-slate-800">Réel vs planifié</h3>
                        <p class="text-xs text-slate-400 mt-1 uppercase tracking-wider font-bold">
                            Barres = heures réelles · Courbe = heures planifiées
                        </p>
                    </div>
                    <div class="h-80 w-full">
                        <Bar :data="performanceMixedData" :options="chartOptions" />
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
