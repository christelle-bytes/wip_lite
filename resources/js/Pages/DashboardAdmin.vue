<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';
import { Line, Bar } from 'vue-chartjs';
import {
    Chart as ChartJS, Title, Tooltip, Legend,
    LineElement, PointElement, BarElement,
    CategoryScale, LinearScale, Filler
} from 'chart.js';
import { useToast } from 'primevue/usetoast';
import Button from 'primevue/button';
import Toast from 'primevue/toast';

ChartJS.register(Title, Tooltip, Legend, LineElement, PointElement, BarElement, CategoryScale, LinearScale, Filler);

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            totalEmployees: 0,
            activeCampaigns: 0,
            totalUsers: 0,
            totalAssignments: 0,
            totalHours: 0,
            gap: 0
        })
    },
    charts: {
        type: Object,
        default: () => ({
            campaignsByMonth: [],
            employeesByMonth: []
        })
    }
});

const page = usePage();
const toast = useToast();

const userRole = computed(() => page.props?.auth?.user?.role);
const isAuthorized = computed(() => userRole.value?.name?.toUpperCase() === 'ADMIN');

// ── Graphe 1 : Campagnes créées par mois (Line) ──────────────────────────────
const campaignsLineData = computed(() => {
    const items = props.charts?.campaignsByMonth ?? [];
    return {
        labels: items.map(i => i.month),
        datasets: [{
            label: 'Campagnes créées',
            data: items.map(i => i.total),
            borderColor: '#0d9488', // teal-600
            backgroundColor: 'rgba(13,148,136,0.1)',
            pointBackgroundColor: '#0d9488',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 4,
            tension: 0.4,
            fill: true
        }],
    };
});

// ── Graphe 2 : Évolution des employés par mois (Bar + Line superposés) ───────
const employeesChartData = computed(() => {
    const items = props.charts?.employeesByMonth ?? [];
    let cumul = 0;
    const cumulData = items.map(i => { cumul += i.total; return cumul; });
    return {
        labels: items.map(i => i.month),
        datasets: [
            {
                type: 'bar',
                label: 'Nouveaux employés',
                data: items.map(i => i.total),
                backgroundColor: 'rgba(20,184,166,0.7)', // teal-500
                borderRadius: 4,
                yAxisID: 'y',
            },
            {
                type: 'line',
                label: 'Total cumulé',
                data: cumulData,
                borderColor: '#0f172a', // slate-900
                backgroundColor: 'transparent',
                pointBackgroundColor: '#0f172a',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                tension: 0.4,
                yAxisID: 'y2',
            },
        ],
    };
});

onMounted(() => {
    if (isAuthorized.value) {
        toast.add({
            severity: 'success',
            summary: 'Bienvenue',
            detail: 'Connecté en tant qu\'Administrateur',
            life: 3000,
        });
    }
});

const lineOptions = {
    responsive: true,
    maintainAspectRatio: false,
    interaction: { mode: 'index', intersect: false },
    plugins: {
        legend: { position: 'top', labels: { usePointStyle: true, pointStyle: 'circle', font: { size: 12 } } },
        tooltip: {
            callbacks: {
                label: (ctx) => ` ${ctx.dataset.label} : ${ctx.parsed.y}`,
            },
        },
    },
    scales: {
        x: { ticks: { font: { size: 11 } }, grid: { display: false } },
        y: {
            beginAtZero: true,
            ticks: { precision: 0, font: { size: 11 } },
            grid: { color: '#F3F4F6' },
        },
    },
};

const mixedOptions = {
    responsive: true,
    maintainAspectRatio: false,
    interaction: { mode: 'index', intersect: false },
    plugins: {
        legend: { position: 'top', labels: { usePointStyle: true, pointStyle: 'circle', font: { size: 12 } } },
        tooltip: {
            callbacks: {
                label: (ctx) => ` ${ctx.dataset.label} : ${ctx.parsed.y} employé(s)`,
            },
        },
    },
    scales: {
        x: { ticks: { font: { size: 11 } }, grid: { display: false } },
        y:  { beginAtZero: true, position: 'left',  ticks: { precision: 0 }, grid: { color: '#F3F4F6' }, title: { display: true, text: 'Nouveaux' } },
        y2: { beginAtZero: true, position: 'right', ticks: { precision: 0 }, grid: { display: false },   title: { display: true, text: 'Cumulé' } },
    },
};
</script>

<template>
    <Toast />
    <Head title="Dashboard Admin" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-black text-slate-800">Tableau de bord</h2>
                    <p class="text-sm text-slate-400 font-medium">Bienvenue, Administrateur</p>
                </div>
                <div class="flex items-center gap-3">
                    <Button icon="pi pi-download" label="Exporter" class="p-button-outlined p-button-secondary p-button-sm rounded-lg" />
                    <Button icon="pi pi-plus" label="Nouvel Employé" class="p-button-teal p-button-sm rounded-lg shadow-lg shadow-teal-600/20" />
                </div>
            </div>
        </template>

        <div class="py-6 space-y-8">
            <!-- KPI Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6">
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex flex-col gap-1 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-3 opacity-5 group-hover:scale-110 transition-transform">
                        <i class="pi pi-users text-4xl text-slate-900"></i>
                    </div>
                    <p class="text-[10px] uppercase font-bold text-slate-400 tracking-widest">Employés</p>
                    <p class="text-3xl font-black text-slate-800">{{ stats.totalEmployees }}</p>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex flex-col gap-1 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-3 opacity-5 group-hover:scale-110 transition-transform">
                        <i class="pi pi-flag text-4xl text-teal-600"></i>
                    </div>
                    <p class="text-[10px] uppercase font-bold text-slate-400 tracking-widest">Campagnes actives</p>
                    <p class="text-3xl font-black text-teal-600">{{ stats.activeCampaigns }}</p>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex flex-col gap-1 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-3 opacity-5 group-hover:scale-110 transition-transform">
                        <i class="pi pi-user text-4xl text-slate-900"></i>
                    </div>
                    <p class="text-[10px] uppercase font-bold text-slate-400 tracking-widest">Utilisateurs</p>
                    <p class="text-3xl font-black text-slate-800">{{ stats.totalUsers }}</p>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex flex-col gap-1 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-3 opacity-5 group-hover:scale-110 transition-transform">
                        <i class="pi pi-link text-4xl text-slate-900"></i>
                    </div>
                    <p class="text-[10px] uppercase font-bold text-slate-400 tracking-widest">Affectations</p>
                    <p class="text-3xl font-black text-slate-800">{{ stats.totalAssignments }}</p>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex flex-col gap-1 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-3 opacity-5 group-hover:scale-110 transition-transform">
                        <i class="pi pi-clock text-4xl text-teal-600"></i>
                    </div>
                    <p class="text-[10px] uppercase font-bold text-slate-400 tracking-widest">Heures réelles</p>
                    <p class="text-3xl font-black text-teal-600">{{ stats.totalHours }}h</p>
                </div>
                <div :class="[stats.gap < 0 ? 'bg-rose-50 border-rose-100' : 'bg-teal-50 border-teal-100']" 
                    class="rounded-2xl p-6 shadow-sm border flex flex-col gap-1 relative overflow-hidden group">
                    <p class="text-[10px] uppercase font-bold tracking-widest" :class="[stats.gap < 0 ? 'text-rose-400' : 'text-teal-500']">Écart planning</p>
                    <p class="text-3xl font-black" :class="[stats.gap < 0 ? 'text-rose-600' : 'text-teal-700']">{{ stats.gap > 0 ? '+' : '' }}{{ stats.gap }}%</p>
                </div>
            </div>

            <!-- Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8 flex flex-col">
                    <div class="mb-8">
                        <h3 class="text-lg font-black text-slate-800">Campagnes par mois</h3>
                        <p class="text-xs text-slate-400 mt-1 uppercase tracking-wider font-bold">Volume de création sur 12 mois</p>
                    </div>
                    <div class="h-80 w-full">
                        <Line :data="campaignsLineData" :options="lineOptions" />
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8 flex flex-col">
                    <div class="mb-8">
                        <h3 class="text-lg font-black text-slate-800">Évolution des effectifs</h3>
                        <p class="text-xs text-slate-400 mt-1 uppercase tracking-wider font-bold">Nouveaux arrivants vs Total cumulé</p>
                    </div>
                    <div class="h-80 w-full">
                        <Bar :data="employeesChartData" :options="mixedOptions" />
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
