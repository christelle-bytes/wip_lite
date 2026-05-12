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
        default: () => ({ campaignsByMonth: [], employeesByMonth: [] })
    }
});

const page = usePage();
const toast = useToast();
const userRole = computed(() => page.props?.auth?.user?.role);
const isAuthorized = computed(() => userRole.value?.name?.toUpperCase() === 'ADMIN');

// Données de démonstration utilisées si le backend renvoie moins de 3 mois
const DEMO_CAMPAIGNS = [
    { month: 'Déc', total: 2 },
    { month: 'Jan', total: 3 },
    { month: 'Fév', total: 1 },
    { month: 'Mar', total: 4 },
    { month: 'Avr', total: 3 },
    { month: 'Mai', total: 5 },
];

const DEMO_EMPLOYEES = [
    { month: 'Déc', total: 10 },
    { month: 'Jan', total: 8 },
    { month: 'Fév', total: 14 },
    { month: 'Mar', total: 6 },
    { month: 'Avr', total: 12 },
    { month: 'Mai', total: 9 },
];

// Graphique 1 : Campagnes par mois — barres + courbe tendance
const campaignsMixedData = computed(() => {
    const raw = props.charts?.campaignsByMonth ?? [];
    const items = raw.length >= 3 ? raw : DEMO_CAMPAIGNS;
    const values = items.map(i => i.total);

    return {
        labels: items.map(i => i.month),
        datasets: [
            {
                type: 'bar',
                label: 'Campagnes créées',
                data: values,
                backgroundColor: 'rgba(29,158,117,0.75)',
                borderRadius: 6,
                borderSkipped: false,
                order: 2,
            },
            {
                type: 'line',
                label: 'Tendance',
                data: values,
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

// Graphique 2 : Employés par mois — barres + courbe cumulée
const employeesMixedData = computed(() => {
    const raw = props.charts?.employeesByMonth ?? [];
    const items = raw.length >= 3 ? raw : DEMO_EMPLOYEES;

    let cumul = 0;
    const cumulData = items.map(i => { cumul += i.total; return cumul; });

    return {
        labels: items.map(i => i.month),
        datasets: [
            {
                type: 'bar',
                label: 'Nouveaux employés',
                data: items.map(i => i.total),
                backgroundColor: 'rgba(93,202,165,0.75)',
                borderRadius: 6,
                borderSkipped: false,
                yAxisID: 'y',
                order: 2,
            },
            {
                type: 'line',
                label: 'Total cumulé',
                data: cumulData,
                borderColor: '#0f172a',
                borderDash: [5, 3],
                backgroundColor: 'transparent',
                pointBackgroundColor: '#0f172a',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 5,
                tension: 0.35,
                yAxisID: 'y2',
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
            callbacks: {
                label: (ctx) => ` ${ctx.dataset.label} : ${ctx.parsed.y}`
            }
        },
    },
    scales: {
        x: {
            ticks: { font: { size: 12 }, color: '#6b7280' },
            grid: { display: false },
        },
        y: {
            beginAtZero: true,
            ticks: { precision: 0, font: { size: 11 }, color: '#6b7280' },
            grid: { color: '#F3F4F6' },
        },
    },
};

const mixedOptionsDouble = {
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
            callbacks: {
                label: (ctx) => ` ${ctx.dataset.label} : ${ctx.parsed.y} employé(s)`
            }
        },
    },
    scales: {
        x: {
            ticks: { font: { size: 12 }, color: '#6b7280' },
            grid: { display: false },
        },
        y: {
            beginAtZero: true,
            position: 'left',
            ticks: { precision: 0, font: { size: 11 }, color: '#6b7280' },
            grid: { color: '#F3F4F6' },
            title: { display: true, text: 'Nouveaux', font: { size: 11 }, color: '#6b7280' },
        },
        y2: {
            beginAtZero: true,
            position: 'right',
            ticks: { precision: 0, font: { size: 11 }, color: '#6b7280' },
            grid: { display: false },
            title: { display: true, text: 'Cumulé', font: { size: 11 }, color: '#6b7280' },
        },
    },
};

onMounted(() => {
    if (isAuthorized.value) {
        toast.add({
            severity: 'success',
            summary: 'Bienvenue',
            detail: "Connecté en tant qu'Administrateur",
            life: 3000
        });
    }
});
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
                    <a
                        :href="route('campaigns.stats.export.pdf')"
                        class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-sm font-semibold text-gray-700 rounded-lg hover:bg-gray-50 transition"
                    >
                        <i class="pi pi-file-pdf"></i> Exporter PDF
                    </a>
                    <a
                        :href="route('campaigns.stats.export')"
                        class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-sm font-semibold text-gray-700 rounded-lg hover:bg-gray-50 transition"
                    >
                        <i class="pi pi-download"></i> Exporter Excel
                    </a>
                </div>
            </div>
        </template>

        <div class="py-6 space-y-8">

            <!-- KPI Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6">

                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex flex-col gap-1">
                    <p class="text-[10px] uppercase font-bold text-slate-400 tracking-widest">Employés</p>
                    <p class="text-3xl font-black text-slate-800">{{ stats.totalEmployees }}</p>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex flex-col gap-1">
                    <p class="text-[10px] uppercase font-bold text-slate-400 tracking-widest">Campagnes actives</p>
                    <p class="text-3xl font-black text-teal-600">{{ stats.activeCampaigns }}</p>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex flex-col gap-1">
                    <p class="text-[10px] uppercase font-bold text-slate-400 tracking-widest">Utilisateurs</p>
                    <p class="text-3xl font-black text-slate-800">{{ stats.totalUsers }}</p>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex flex-col gap-1">
                    <p class="text-[10px] uppercase font-bold text-slate-400 tracking-widest">Affectations</p>
                    <p class="text-3xl font-black text-slate-800">{{ stats.totalAssignments }}</p>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex flex-col gap-1">
                    <p class="text-[10px] uppercase font-bold text-slate-400 tracking-widest">Heures réelles</p>
                    <p class="text-3xl font-black text-teal-600">{{ stats.totalHours }}h</p>
                </div>

                <div
                    class="rounded-2xl p-6 shadow-sm border flex flex-col gap-1"
                    :class="stats.gap < 0
                        ? 'bg-rose-50 border-rose-100'
                        : 'bg-teal-50 border-teal-100'"
                >
                    <p
                        class="text-[10px] uppercase font-bold tracking-widest"
                        :class="stats.gap < 0 ? 'text-rose-400' : 'text-teal-500'"
                    >
                        Écart planning
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

                <!-- Graphique 1 : Campagnes par mois -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
                    <div class="mb-4">
                        <h3 class="text-lg font-black text-slate-800">Campagnes par mois</h3>
                        <p class="text-xs text-slate-400 mt-1 uppercase tracking-wider font-bold">
                            Barres = volume · Courbe = tendance
                        </p>
                    </div>
                    <div class="h-72 w-full">
                        <Bar :data="campaignsMixedData" :options="mixedOptions" />
                    </div>
                </div>

                <!-- Graphique 2 : Évolution des effectifs -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
                    <div class="mb-4">
                        <h3 class="text-lg font-black text-slate-800">Évolution des effectifs</h3>
                        <p class="text-xs text-slate-400 mt-1 uppercase tracking-wider font-bold">
                            Barres = nouveaux · Courbe = total cumulé
                        </p>
                    </div>
                    <div class="h-72 w-full">
                        <Bar :data="employeesMixedData" :options="mixedOptionsDouble" />
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>