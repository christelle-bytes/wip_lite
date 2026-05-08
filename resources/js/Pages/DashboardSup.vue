<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Bar, Line } from 'vue-chartjs';
import {
    Chart as ChartJS, Title, Tooltip, Legend,
    BarElement, LineElement, PointElement,
    CategoryScale, LinearScale, Filler
} from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, BarElement, LineElement, PointElement, CategoryScale, LinearScale, Filler);

const props = defineProps({ stats: Object, charts: Object });

// ── Graphe 1 : Écart heures réelles vs planifiées par employé ─────────────────
const gapData = computed(() => {
    const items = props.charts?.gapByEmployee ?? [];
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
                borderColor: '#6366F1',
                backgroundColor: 'rgba(99,102,241,0.12)',
                pointBackgroundColor: '#6366F1',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 6,
                pointHoverRadius: 9,
                fill: true,
                tension: 0.4,
            },
            {
                label: 'Heures planifiées',
                data: items.map(i => parseFloat(i.planned_hours) || 0),
                borderColor: '#F59E0B',
                backgroundColor: 'rgba(245,158,11,0.08)',
                pointBackgroundColor: '#F59E0B',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 6,
                pointHoverRadius: 9,
                fill: true,
                tension: 0.4,
                borderDash: [6, 3],
            },
        ],
    };
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
                afterBody: (items) => {
                    const real    = items.find(i => i.dataset.label === 'Heures réelles')?.parsed.y ?? 0;
                    const planned = items.find(i => i.dataset.label === 'Heures planifiées')?.parsed.y ?? 0;
                    const diff    = (real - planned).toFixed(1);
                    return [`Écart : ${diff > 0 ? '+' : ''}${diff}h`];
                },
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

const lineOptions = {
    responsive: true,
    maintainAspectRatio: false,
    interaction: { mode: 'index', intersect: false },
    plugins: {
        legend: { position: 'top', labels: { usePointStyle: true, pointStyle: 'circle', font: { size: 12 } } },
        tooltip: {
            callbacks: {
                label: (ctx) => ` ${ctx.dataset.label} : ${ctx.parsed.y}h`,
                afterBody: (items) => {
                    const real    = items.find(i => i.dataset.label === 'Heures réelles')?.parsed.y ?? 0;
                    const planned = items.find(i => i.dataset.label === 'Heures planifiées')?.parsed.y ?? 0;
                    if (planned === 0) return [];
                    const pct = (((real - planned) / planned) * 100).toFixed(1);
                    return [`Écart : ${pct > 0 ? '+' : ''}${pct}%`];
                },
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
    <Head title="Dashboard Superviseur" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tableau de bord — Superviseur</h2>
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
                <div class="bg-gradient-to-br from-violet-500 to-violet-600 rounded-xl p-5 text-white shadow-lg">
                    <p class="text-xs uppercase font-semibold opacity-80">Affectations</p>
                    <p class="text-3xl font-black mt-1">{{ stats.totalAssignments }}</p>
                </div>
                <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl p-5 text-white shadow-lg">
                    <p class="text-xs uppercase font-semibold opacity-80">Heures réelles</p>
                    <p class="text-3xl font-black mt-1">{{ stats.totalHours }}h</p>
                </div>
                <!-- Timesheets en attente — badge d'alerte -->
                <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl p-5 text-white shadow-lg">
                    <p class="text-xs uppercase font-semibold opacity-80">En attente validation</p>
                    <p class="text-3xl font-black mt-1">{{ stats.pendingTimesheets }}</p>
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
                        <h3 class="text-base font-bold text-gray-800">Écart par employé</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Heures réelles vs planifiées — le tooltip affiche l'écart exact</p>
                    </div>
                    <div class="h-72">
                        <Bar :data="gapData" :options="gapOptions" />
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="mb-5">
                        <h3 class="text-base font-bold text-gray-800">Évolution des heures (6 mois)</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Courbe réelle (pleine) vs planifiée (pointillée) — écart en %</p>
                    </div>
                    <div class="h-72">
                        <Line :data="hoursEvolutionData" :options="lineOptions" />
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
