<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Line, Bar } from 'vue-chartjs';
import {
    Chart as ChartJS, Title, Tooltip, Legend,
    LineElement, PointElement, BarElement,
    CategoryScale, LinearScale, Filler
} from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, LineElement, PointElement, BarElement, CategoryScale, LinearScale, Filler);

const props = defineProps({ stats: Object, charts: Object });

// ── Graphe 1 : Campagnes créées par mois (Line) ──────────────────────────────
const campaignsLineData = computed(() => {
    const items = props.charts?.campaignsByMonth ?? [];
    return {
        labels: items.map(i => i.month),
        datasets: [{
            label: 'Campagnes créées',
            data: items.map(i => i.total),
            borderColor: '#6366F1',
            backgroundColor: 'rgba(99,102,241,0.15)',
            pointBackgroundColor: '#6366F1',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 6,
            pointHoverRadius: 9,
            fill: true,
            tension: 0.4,
        }],
    };
});

// ── Graphe 2 : Évolution des employés par mois (Bar + Line superposés) ───────
const employeesChartData = computed(() => {
    const items = props.charts?.employeesByMonth ?? [];
    // Calcul du cumulatif pour montrer l'évolution totale
    let cumul = 0;
    const cumulData = items.map(i => { cumul += i.total; return cumul; });
    return {
        labels: items.map(i => i.month),
        datasets: [
            {
                type: 'bar',
                label: 'Nouveaux employés',
                data: items.map(i => i.total),
                backgroundColor: 'rgba(34,211,238,0.7)',
                borderRadius: 6,
                borderSkipped: false,
                yAxisID: 'y',
            },
            {
                type: 'line',
                label: 'Total cumulé',
                data: cumulData,
                borderColor: '#F59E0B',
                backgroundColor: 'transparent',
                pointBackgroundColor: '#F59E0B',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                tension: 0.4,
                yAxisID: 'y2',
            },
        ],
    };
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
    <Head title="Dashboard Admin" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tableau de bord — Admin</h2>
        </template>

        <div class="py-6 space-y-6 px-4 sm:px-6 lg:px-8">

            <!-- KPI Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl p-5 text-white shadow-lg">
                    <p class="text-xs uppercase font-semibold opacity-80">Employés</p>
                    <p class="text-3xl font-black mt-1">{{ stats.totalEmployees }}</p>
                </div>
                <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl p-5 text-white shadow-lg">
                    <p class="text-xs uppercase font-semibold opacity-80">Campagnes actives</p>
                    <p class="text-3xl font-black mt-1">{{ stats.activeCampaigns }}</p>
                </div>
                <div class="bg-gradient-to-br from-cyan-500 to-cyan-600 rounded-xl p-5 text-white shadow-lg">
                    <p class="text-xs uppercase font-semibold opacity-80">Utilisateurs</p>
                    <p class="text-3xl font-black mt-1">{{ stats.totalUsers }}</p>
                </div>
                <div class="bg-gradient-to-br from-violet-500 to-violet-600 rounded-xl p-5 text-white shadow-lg">
                    <p class="text-xs uppercase font-semibold opacity-80">Affectations</p>
                    <p class="text-3xl font-black mt-1">{{ stats.totalAssignments }}</p>
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
                        <h3 class="text-base font-bold text-gray-800">Campagnes par mois</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Nombre de campagnes créées sur les 12 derniers mois</p>
                    </div>
                    <div class="h-72">
                        <Line :data="campaignsLineData" :options="lineOptions" />
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="mb-5">
                        <h3 class="text-base font-bold text-gray-800">Évolution des employés</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Nouveaux employés par mois + total cumulé</p>
                    </div>
                    <div class="h-72">
                        <Bar :data="employeesChartData" :options="mixedOptions" />
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
