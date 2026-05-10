<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';
import { Bar, Line } from 'vue-chartjs';
import {
    Chart as ChartJS, Title, Tooltip, Legend,
    BarElement, LineElement, PointElement,
    CategoryScale, LinearScale, Filler
} from 'chart.js';
import { useToast } from 'primevue/usetoast';
import Button from 'primevue/button';
import Toast from 'primevue/toast';

ChartJS.register(Title, Tooltip, Legend, BarElement, LineElement, PointElement, CategoryScale, LinearScale, Filler);

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            totalEmployees: 0,
            activeCampaigns: 0,
            totalAssignments: 0,
            totalHours: 0,
            gap: 0,
            pendingTimesheets: 0
        })
    },
    charts: {
        type: Object,
        default: () => ({
            gapByEmployee: [],
            hoursByMonth: []
        })
    }
});

const page = usePage();
const toast = useToast();

const userRole = computed(() => page.props?.auth?.user?.role);
const isAuthorized = computed(() => userRole.value?.name?.toUpperCase() === 'SUP');

// ── Graphe 1 : Écart heures réelles vs planifiées par employé ─────────────────
const gapData = computed(() => {
    const items = props.charts?.gapByEmployee ?? [];
    return {
        labels: items.map(i => i.name),
        datasets: [
            {
                label: 'Heures réelles',
                data: items.map(i => parseFloat(i.real_hours) || 0),
                backgroundColor: '#0d9488', // teal-600
                borderRadius: 4,
            },
            {
                label: 'Heures planifiées',
                data: items.map(i => parseFloat(i.planned_hours) || 0),
                backgroundColor: '#f1f5f9', // slate-100
                borderRadius: 4,
            },
        ],
    };
});

// ── Graphe 2 : Évolution heures réelles vs planifiées par mois (Line) ─────────
const hoursEvolutionData = computed(() => {
    const items = props.charts?.hoursByMonth ?? [];
    return {
        labels: items.map(i => i.month),
        datasets: [
            {
                label: 'Heures réelles',
                data: items.map(i => parseFloat(i.real_hours) || 0),
                borderColor: '#0d9488', // teal-600
                backgroundColor: 'rgba(13,148,136,0.1)',
                pointBackgroundColor: '#0d9488',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                fill: true,
                tension: 0.4,
            },
            {
                label: 'Heures planifiées',
                data: items.map(i => parseFloat(i.planned_hours) || 0),
                borderColor: '#64748b', // slate-500
                backgroundColor: 'transparent',
                pointBackgroundColor: '#64748b',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                tension: 0.4,
                borderDash: [6, 3],
            },
        ],
    };
});

onMounted(() => {
    if (isAuthorized.value) {
        toast.add({
            severity: 'success',
            summary: 'Bienvenue',
            detail: 'Connecté en tant que Superviseur',
            life: 3000,
        });
    }
});

const gapOptions = {
    responsive: true,
    maintainAspectRatio: false,
    interaction: { mode: 'index', intersect: false },
    plugins: {
        legend: { position: 'top', labels: { usePointStyle: true, pointStyle: 'circle', font: { size: 12 } } },
        tooltip: {
            callbacks: {
                label: (ctx) => ` ${ctx.dataset.label} : ${ctx.parsed.y}h`,
            },
        },
    },
    scales: {
        x: { grid: { display: false }, ticks: { font: { size: 11 } } },
        y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#F3F4F6' } },
    },
};

const lineOptions = {
    responsive: true,
    maintainAspectRatio: false,
    interaction: { mode: 'index', intersect: false },
    plugins: {
        legend: { position: 'top', labels: { usePointStyle: true, pointStyle: 'circle' } },
    },
    scales: {
        x: { grid: { display: false } },
        y: { beginAtZero: true, grid: { color: '#F3F4F6' } },
    },
};
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
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex flex-col gap-1 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-3 opacity-5 group-hover:scale-110 transition-transform">
                        <i class="pi pi-users text-4xl text-slate-900"></i>
                    </div>
                    <p class="text-[10px] uppercase font-bold text-slate-400 tracking-widest">Équipes</p>
                    <p class="text-3xl font-black text-slate-800">{{ stats.totalEmployees }}</p>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex flex-col gap-1 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-3 opacity-5 group-hover:scale-110 transition-transform">
                        <i class="pi pi-clock text-4xl text-teal-600"></i>
                    </div>
                    <p class="text-[10px] uppercase font-bold text-slate-400 tracking-widest">Heures Réelles</p>
                    <p class="text-3xl font-black text-teal-600">{{ stats.totalHours }}h</p>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex flex-col gap-1 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-3 opacity-5 group-hover:scale-110 transition-transform">
                        <i class="pi pi-file-edit text-4xl text-amber-500"></i>
                    </div>
                    <p class="text-[10px] uppercase font-bold text-slate-400 tracking-widest">Validation en attente</p>
                    <p class="text-3xl font-black text-amber-600">{{ stats.pendingTimesheets }}</p>
                </div>
                <div :class="[stats.gap < 0 ? 'bg-rose-50 border-rose-100' : 'bg-teal-50 border-teal-100']" 
                    class="rounded-2xl p-6 shadow-sm border flex flex-col gap-1 relative overflow-hidden group">
                    <p class="text-[10px] uppercase font-bold tracking-widest" :class="[stats.gap < 0 ? 'text-rose-400' : 'text-teal-500']">Productivité</p>
                    <p class="text-3xl font-black" :class="[stats.gap < 0 ? 'text-rose-600' : 'text-teal-700']">{{ stats.gap > 0 ? '+' : '' }}{{ stats.gap }}%</p>
                </div>
            </div>

            <!-- Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h3 class="text-lg font-black text-slate-800">Analyse de Performance</h3>
                            <p class="text-[10px] text-slate-400 mt-1 uppercase tracking-widest font-bold">Écart réel vs prévisionnel par agent</p>
                        </div>
                        <i class="pi pi-chart-bar text-slate-200 text-2xl"></i>
                    </div>
                    <div class="h-96">
                        <Bar :data="gapData" :options="gapOptions" />
                    </div>
                </div>

                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h3 class="text-lg font-black text-slate-800">Évolution de la Production</h3>
                            <p class="text-[10px] text-slate-400 mt-1 uppercase tracking-widest font-bold">Suivi temporel du volume horaire</p>
                        </div>
                        <i class="pi pi-chart-line text-slate-200 text-2xl"></i>
                    </div>
                    <div class="h-96">
                        <Line :data="hoursEvolutionData" :options="lineOptions" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
