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

// ── Graphe 1 : Planning de la semaine (Bar groupé jours) ─────────────────────
const weekData = computed(() => {
    const items = props.charts?.weekPlanning ?? [];
    return {
        labels: items.map(i => i.day),
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


// ── Graphe 2 : Évolution de mes heures sur 6 mois (Line) ─────────────────────
const evolutionData = computed(() => {
    const items = props.charts?.hoursEvolution ?? [];
    return {
        labels: items.map(i => i.month),
        datasets: [
            {
                label: 'Mes heures réelles',
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
                label: 'Mes heures planifiées',
                data: items.map(i => parseFloat(i.planned_hours) || 0),
                borderColor: '#F59E0B',
                backgroundColor: 'transparent',
                pointBackgroundColor: '#F59E0B',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                tension: 0.4,
                borderDash: [6, 3],
            },
        ],
    };
});
const isAuthorized = computed(() => userRole.value?.name?.toUpperCase() === 'TC');

onMounted(() => {
    toast.add({
        severity: 'success',
        summary: 'Bienvenue',
        detail: 'Connecté en tant que Teleconseiller',
        life: 3000,
    });

});

const weekOptions = {
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
        x: { ticks: { font: { size: 12 } }, grid: { display: false } },
        y: {
            beginAtZero: true,
            ticks: { callback: (v) => v + 'h', font: { size: 11 } },
            grid: { color: '#F3F4F6' },
        },
    },
};

const evolutionOptions = {
    responsive: true,
    maintainAspectRatio: false,
    interaction: { mode: 'index', intersect: false },
    plugins: {
        legend: { position: 'top', labels: { usePointStyle: true, pointStyle: 'circle', font: { size: 12 } } },
        tooltip: {
            callbacks: {
                label: (ctx) => ` ${ctx.dataset.label} : ${ctx.parsed.y}h`,
                afterBody: (items) => {
                    const real    = items.find(i => i.dataset.label === 'Mes heures réelles')?.parsed.y ?? 0;
                    const planned = items.find(i => i.dataset.label === 'Mes heures planifiées')?.parsed.y ?? 0;
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

const hasWeekData     = computed(() => (props.charts?.weekPlanning ?? []).length > 0);
const hasEvolution    = computed(() => (props.charts?.hoursEvolution ?? []).length > 0);
const activeCampaigns = computed(() => props.charts?.activeCampaigns ?? []);
</script>

<template>
    <Head title="Dashboard Technicien" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tableau de bord — Technicien</h2>
        </template>

        <div class="py-6 space-y-6 px-4 sm:px-6 lg:px-8">

            <!-- KPI Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl p-5 text-white shadow-lg">
                    <p class="text-xs uppercase font-semibold opacity-80">Mes affectations actives</p>
                    <p class="text-3xl font-black mt-1">{{ stats.myAssignments }}</p>
                </div>
                <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl p-5 text-white shadow-lg">
                    <p class="text-xs uppercase font-semibold opacity-80">Heures réelles</p>
                    <p class="text-3xl font-black mt-1">{{ stats.myHours }}h</p>
                </div>
                <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl p-5 text-white shadow-lg">
                    <p class="text-xs uppercase font-semibold opacity-80">Heures planifiées</p>
                    <p class="text-3xl font-black mt-1">{{ stats.myPlanned }}h</p>
                </div>
                <div :class="`rounded-xl p-5 text-white shadow-lg bg-gradient-to-br ${stats.gap < 0 ? 'from-red-500 to-red-600' : 'from-green-500 to-green-600'}`">
                    <p class="text-xs uppercase font-semibold opacity-80">Écart</p>
                    <p class="text-3xl font-black mt-1">{{ stats.gap > 0 ? '+' : '' }}{{ stats.gap }}%</p>
                </div>
            </div>

            <!-- Campagnes en cours -->
            <div v-if="activeCampaigns.length > 0" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="mb-4">
                    <h3 class="text-base font-bold text-gray-800">Mes campagnes en cours</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Affectations actives</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    <div
                        v-for="c in activeCampaigns"
                        :key="c.name"
                        class="flex items-start gap-3 p-4 rounded-xl border border-gray-100 bg-gray-50"
                    >
                        <div class="w-2.5 h-2.5 rounded-full mt-1.5 flex-shrink-0"
                            :class="c.status === 'active' ? 'bg-emerald-500' : 'bg-gray-400'">
                        </div>
                        <div>
                            <p class="font-semibold text-sm text-gray-800">{{ c.name }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">
                                Du {{ c.start_date }} {{ c.end_date ? '→ ' + c.end_date : '(en cours)' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="mb-5">
                        <h3 class="text-base font-bold text-gray-800">Mon planning cette semaine</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Heures réelles vs planifiées par jour</p>
                    </div>
                    <div class="h-64 flex items-center justify-center">
                        <Bar v-if="hasWeekData" :data="weekData" :options="weekOptions" />
                        <div v-else class="text-center text-gray-400">
                            <p class="text-4xl mb-2">📅</p>
                            <p class="text-sm">Aucune entrée cette semaine</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="mb-5">
                        <h3 class="text-base font-bold text-gray-800">Évolution de mes heures (6 mois)</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Courbe réelle (pleine) vs planifiée (pointillée)</p>
                    </div>
                    <div class="h-64 flex items-center justify-center">
                        <Line v-if="hasEvolution" :data="evolutionData" :options="evolutionOptions" />
                        <div v-else class="text-center text-gray-400">
                            <p class="text-4xl mb-2">📊</p>
                            <p class="text-sm">Aucune donnée sur 6 mois</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
