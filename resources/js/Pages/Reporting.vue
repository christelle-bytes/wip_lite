<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Doughnut, Bar } from 'vue-chartjs';
import {
    Chart as ChartJS, Title, Tooltip, Legend,
    ArcElement, BarElement, CategoryScale, LinearScale
} from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, ArcElement, BarElement, CategoryScale, LinearScale);

const props = defineProps({
    stats: Object,
    rolesChart: Array,
});

const PALETTE = ['#6366F1', '#22D3EE', '#F59E0B', '#EF4444', '#10B981', '#EC4899'];

// Doughnut : répartition par rôle
const rolesChartData = computed(() => {
    const items = props.rolesChart ?? [];
    return {
        labels: items.map(i => i.label),
        datasets: [{
            backgroundColor: PALETTE,
            borderColor: '#fff',
            borderWidth: 3,
            hoverOffset: 12,
            data: items.map(i => i.value),
        }],
    };
});

// Bar horizontal : même données mais en bar pour comparer visuellement
const rolesBarData = computed(() => {
    const items = props.rolesChart ?? [];
    return {
        labels: items.map(i => i.label),
        datasets: [{
            label: 'Utilisateurs',
            backgroundColor: items.map((_, idx) => PALETTE[idx % PALETTE.length]),
            borderRadius: 8,
            borderSkipped: false,
            data: items.map(i => i.value),
        }],
    };
});

const doughnutOptions = {
    responsive: true,
    maintainAspectRatio: false,
    cutout: '65%',
    plugins: {
        legend: {
            position: 'right',
            labels: {
                padding: 16,
                usePointStyle: true,
                pointStyle: 'circle',
                font: { size: 13 },
                generateLabels: (chart) => {
                    const data = chart.data;
                    return data.labels.map((label, i) => ({
                        text: `${label}  (${data.datasets[0].data[i]})`,
                        fillStyle: data.datasets[0].backgroundColor[i],
                        strokeStyle: '#fff',
                        lineWidth: 2,
                        hidden: false,
                        index: i,
                    }));
                },
            },
        },
        tooltip: {
            callbacks: {
                label: (ctx) => ` ${ctx.label} : ${ctx.parsed} utilisateur(s)`,
            },
        },
    },
};

const barOptions = {
    indexAxis: 'y',
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            callbacks: {
                label: (ctx) => ` ${ctx.parsed.x} utilisateur(s)`,
            },
        },
    },
    scales: {
        x: {
            beginAtZero: true,
            ticks: { precision: 0, font: { size: 12 } },
            grid: { color: '#F3F4F6' },
        },
        y: {
            ticks: { font: { size: 13, weight: 'bold' } },
            grid: { display: false },
        },
    },
};
</script>

<template>
    <Head title="Reporting Global" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Reporting Global & Statistiques</h2>
        </template>

        <div class="py-6 space-y-6 px-4 sm:px-6 lg:px-8">

            <!-- KPI Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl p-5 text-white shadow-lg">
                    <p class="text-xs uppercase font-semibold opacity-80">Employés</p>
                    <p class="text-3xl font-black mt-1">{{ stats.totalEmployees }}</p>
                    <p class="text-xs opacity-70 mt-1">Total enregistrés</p>
                </div>
                <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl p-5 text-white shadow-lg">
                    <p class="text-xs uppercase font-semibold opacity-80">Campagnes actives</p>
                    <p class="text-3xl font-black mt-1">{{ stats.activeCampaigns }}</p>
                    <p class="text-xs opacity-70 mt-1">En cours</p>
                </div>
                <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl p-5 text-white shadow-lg">
                    <p class="text-xs uppercase font-semibold opacity-80">Heures réelles</p>
                    <p class="text-3xl font-black mt-1">{{ stats.totalHours }}h</p>
                    <p class="text-xs opacity-70 mt-1">Total cumulé</p>
                </div>
                <div :class="`rounded-xl p-5 text-white shadow-lg bg-gradient-to-br ${stats.gap < 0 ? 'from-red-500 to-red-600' : 'from-green-500 to-green-600'}`">
                    <p class="text-xs uppercase font-semibold opacity-80">Écart / Planning</p>
                    <p class="text-3xl font-black mt-1">{{ stats.gap > 0 ? '+' : '' }}{{ stats.gap }}%</p>
                    <p class="text-xs opacity-70 mt-1">{{ stats.gap < 0 ? 'Sous le planning' : 'Au-dessus' }}</p>
                </div>
            </div>

            <!-- Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- Doughnut avec valeurs dans la légende -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="mb-4">
                        <h3 class="text-base font-bold text-gray-800">Répartition par rôle</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Distribution des utilisateurs selon leur rôle</p>
                    </div>
                    <div class="h-72">
                        <Doughnut :data="rolesChartData" :options="doughnutOptions" />
                    </div>
                </div>

                <!-- Bar horizontal avec les mêmes données -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="mb-4">
                        <h3 class="text-base font-bold text-gray-800">Utilisateurs par rôle</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Vue comparative en barres</p>
                    </div>
                    <div class="h-72">
                        <Bar :data="rolesBarData" :options="barOptions" />
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
