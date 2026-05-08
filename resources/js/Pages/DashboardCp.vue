<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Bar } from 'vue-chartjs';
import {
    Chart as ChartJS, Title, Tooltip, Legend,
    BarElement, CategoryScale, LinearScale
} from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

const props = defineProps({ stats: Object, charts: Object });

// ── Graphe 1 : Taux de présence par employé (Bar horizontal) ─────────────────
const presenceData = computed(() => {
    const items = props.charts?.presenceByEmployee ?? [];
    return {
        labels: items.map(i => i.name),
        datasets: [{
            label: 'Taux de présence (%)',
            data: items.map(i => i.presence_rate),
            backgroundColor: items.map(i =>
                i.presence_rate >= 80 ? '#10B981' :
                i.presence_rate >= 50 ? '#F59E0B' : '#EF4444'
            ),
            borderRadius: 6,
            borderSkipped: false,
        }],
    };
});

<<<<<<< HEAD
// ── Graphe 2 : Performance — heures réelles vs planifiées par employé ─────────
const performanceData = computed(() => {
    const items = props.charts?.performanceByEmployee ?? [];
    return {
        labels: items.map(i => i.name),
        datasets: [
            {
                label: 'Heures réelles',
                data: items.map(i => parseFloat(i.real_hours) || 0),
                backgroundColor: '#6366F1',
                borderRadius: 6,
                borderSkipped: false,
            },
            {
                label: 'Heures planifiées',
                data: items.map(i => parseFloat(i.planned_hours) || 0),
                backgroundColor: '#E0E7FF',
                borderRadius: 6,
                borderSkipped: false,
            },
        ],
    };
=======
const isAuthorized = computed(() => userRole.value?.name?.toUpperCase() === 'CP');

onMounted(() => {
    toast.add({
        severity: 'success',
        summary: 'Bienvenue',
        detail: 'Connecté en tant que Chef Plateau',
        life: 3000,
    });
>>>>>>> 9781550fa90c83bb4fd7e25de8355e3cb638af32
});

// Options bar horizontal pour la présence
const presenceOptions = {
    indexAxis: 'y',
    responsive: true,
    maintainAspectRatio: false,
    interaction: { mode: 'index', intersect: false },
    plugins: {
        legend: { display: false },
        tooltip: {
            callbacks: {
                label: (ctx) => ` Présence : ${ctx.parsed.x}%`,
                afterLabel: (ctx) => {
                    const item = props.charts?.presenceByEmployee?.[ctx.dataIndex];
                    return item ? ` ${item.present_days} jours présents / ${item.total_days} total` : '';
                },
            },
        },
    },
    scales: {
        x: {
            beginAtZero: true,
            max: 100,
            ticks: { callback: (v) => v + '%', font: { size: 11 } },
            grid: { color: '#F3F4F6' },
        },
        y: {
            ticks: { font: { size: 12 } },
            grid: { display: false },
        },
    },
};

// Options bar groupé pour la performance
const performanceOptions = {
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
        x: { ticks: { font: { size: 11 } }, grid: { display: false } },
        y: {
            beginAtZero: true,
            ticks: { callback: (v) => v + 'h', font: { size: 11 } },
            grid: { color: '#F3F4F6' },
        },
    },
};
</script>

<template>
    <Head title="Dashboard Chef Plateau" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tableau de bord — Chef Plateau</h2>
        </template>

        <div class="py-6 space-y-6 px-4 sm:px-6 lg:px-8">

            <!-- KPI Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
                <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl p-5 text-white shadow-lg">
                    <p class="text-xs uppercase font-semibold opacity-80">Campagnes actives</p>
                    <p class="text-3xl font-black mt-1">{{ stats.activeCampaigns }}</p>
                </div>
                <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl p-5 text-white shadow-lg">
                    <p class="text-xs uppercase font-semibold opacity-80">Affectations</p>
                    <p class="text-3xl font-black mt-1">{{ stats.totalAssignments }}</p>
                </div>
                <div class="bg-gradient-to-br from-violet-500 to-violet-600 rounded-xl p-5 text-white shadow-lg">
                    <p class="text-xs uppercase font-semibold opacity-80">Employés</p>
                    <p class="text-3xl font-black mt-1">{{ stats.totalEmployees }}</p>
                </div>
                <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl p-5 text-white shadow-lg">
                    <p class="text-xs uppercase font-semibold opacity-80">Heures réelles</p>
                    <p class="text-3xl font-black mt-1">{{ stats.totalHours }}h</p>
                </div>
                <div :class="`rounded-xl p-5 text-white shadow-lg bg-gradient-to-br ${stats.gap < 0 ? 'from-red-500 to-red-600' : 'from-green-500 to-green-600'}`">
                    <p class="text-xs uppercase font-semibold opacity-80">Écart planning</p>
                    <p class="text-3xl font-black mt-1">{{ stats.gap > 0 ? '+' : '' }}{{ stats.gap }}%</p>
                </div>
            </div>

            <!-- Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="mb-5">
                        <h3 class="text-base font-bold text-gray-800">Taux de présence par employé</h3>
                        <p class="text-xs text-gray-400 mt-0.5">
                            <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 mr-1"></span>≥80% bon
                            <span class="inline-block w-2 h-2 rounded-full bg-amber-500 mx-1 ml-3"></span>50–80% moyen
                            <span class="inline-block w-2 h-2 rounded-full bg-red-500 mx-1 ml-3"></span>&lt;50% faible
                        </p>
                    </div>
                    <div class="h-72">
                        <Bar :data="presenceData" :options="presenceOptions" />
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="mb-5">
                        <h3 class="text-base font-bold text-gray-800">Performance de l'équipe</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Heures réelles vs planifiées par employé</p>
                    </div>
                    <div class="h-72">
                        <Bar :data="performanceData" :options="performanceOptions" />
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
