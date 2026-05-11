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
        default: () => ({ myAssignments: 0, myHours: 0, myPlanned: 0, gap: 0 })
    },
    charts: {
        type: Object,
        default: () => ({ activeCampaigns: [], weekPlanning: [], hoursEvolution: [] })
    }
});

const page = usePage();
const toast = useToast();
const userRole = computed(() => page.props?.auth?.user?.role);
const isAuthorized = computed(() => userRole.value?.name?.toUpperCase() === 'TC');

// Données de démonstration
const DEMO_WEEK = [
    { day: 'Lun', real_hours: 7.5, planned_hours: 8 },
    { day: 'Mar', real_hours: 8,   planned_hours: 8 },
    { day: 'Mer', real_hours: 6,   planned_hours: 8 },
    { day: 'Jeu', real_hours: 8.5, planned_hours: 8 },
    { day: 'Ven', real_hours: 7,   planned_hours: 8 },
];

const DEMO_EVOLUTION = [
    { month: 'Déc', real_hours: 148, planned_hours: 160 },
    { month: 'Jan', real_hours: 132, planned_hours: 160 },
    { month: 'Fév', real_hours: 165, planned_hours: 160 },
    { month: 'Mar', real_hours: 120, planned_hours: 160 },
    { month: 'Avr', real_hours: 158, planned_hours: 160 },
    { month: 'Mai', real_hours: 155, planned_hours: 160 },
];

const DEMO_CAMPAIGNS = [
    { name: 'Campagne Printemps', start_date: '2026-03-01', end_date: '2026-06-30' },
    { name: 'Relance Clients', start_date: '2026-04-15', end_date: '2026-05-31' },
];

// Graphique 1 : Activité hebdomadaire — barres + courbe planifiée
const weekMixedData = computed(() => {
    const raw = props.charts?.weekPlanning ?? [];
    const items = raw.length >= 3 ? raw : DEMO_WEEK;
    return {
        labels: items.map(i => i.day),
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

// Graphique 2 : Évolution mensuelle — barres + courbe planifiée
const evolutionMixedData = computed(() => {
    const raw = props.charts?.hoursEvolution ?? [];
    const items = raw.length >= 3 ? raw : DEMO_EVOLUTION;
    return {
        labels: items.map(i => i.month),
        datasets: [
            {
                type: 'bar',
                label: 'Mes heures réelles',
                data: items.map(i => parseFloat(i.real_hours) || 0),
                backgroundColor: 'rgba(29,158,117,0.75)',
                borderRadius: 6,
                borderSkipped: false,
                order: 2,
            },
            {
                type: 'line',
                label: 'Mes heures planifiées',
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

// Campagnes à afficher (réelles ou démo)
const displayedCampaigns = computed(() => {
    const raw = props.charts?.activeCampaigns ?? [];
    return raw.length ? raw : DEMO_CAMPAIGNS;
});

onMounted(() => {
    if (isAuthorized.value) {
        toast.add({ severity: 'success', summary: 'Bienvenue', detail: 'Connecté en tant que Téléconseiller', life: 3000 });
    }
});
</script>

<template>
    <Toast />
    <Head title="Dashboard TC" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight">Mon espace personnel</h2>
                    <p class="text-sm text-slate-400 font-medium">Suivi de votre activité et de vos plannings</p>
                </div>
            </div>
        </template>

        <div class="py-6 space-y-8">

            <!-- KPI Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-center gap-5">
                    <div class="h-14 w-14 rounded-2xl bg-teal-50 flex items-center justify-center text-teal-600 shrink-0">
                        <i class="pi pi-briefcase text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Campagnes</p>
                        <p class="text-3xl font-black text-slate-800">{{ stats.myAssignments }}</p>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-center gap-5">
                    <div class="h-14 w-14 rounded-2xl bg-teal-50 flex items-center justify-center text-teal-600 shrink-0">
                        <i class="pi pi-clock text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Heures réelles</p>
                        <p class="text-3xl font-black text-slate-800">{{ stats.myHours }}h</p>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-center gap-5">
                    <div class="h-14 w-14 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400 shrink-0">
                        <i class="pi pi-calendar text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Prévues</p>
                        <p class="text-3xl font-black text-slate-800">{{ stats.myPlanned }}h</p>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-center gap-5">
                    <div
                        class="h-14 w-14 rounded-2xl flex items-center justify-center shrink-0"
                        :class="stats.gap < 0 ? 'bg-rose-50 text-rose-600' : 'bg-teal-50 text-teal-600'"
                    >
                        <i class="pi text-2xl" :class="stats.gap < 0 ? 'pi-arrow-down' : 'pi-arrow-up'"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Performance</p>
                        <p
                            class="text-3xl font-black"
                            :class="stats.gap < 0 ? 'text-rose-600' : 'text-teal-700'"
                        >
                            {{ stats.gap > 0 ? '+' : '' }}{{ stats.gap }}%
                        </p>
                    </div>
                </div>

            </div>

            <!-- Contenu principal -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <!-- Graphiques (colonne gauche 2/3) -->
                <div class="lg:col-span-2 space-y-8">

                    <!-- Graphique 1 : Activité hebdomadaire -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
                        <div class="mb-4">
                            <h3 class="text-lg font-black text-slate-800">Activité hebdomadaire</h3>
                            <p class="text-xs text-slate-400 mt-1 uppercase tracking-wider font-bold">
                                Barres = réel · Courbe = planifié
                            </p>
                        </div>
                        <div class="h-72 w-full">
                            <Bar :data="weekMixedData" :options="chartOptions" />
                        </div>
                    </div>

                    <!-- Graphique 2 : Évolution mensuelle -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
                        <div class="mb-4">
                            <h3 class="text-lg font-black text-slate-800">Évolution mensuelle</h3>
                            <p class="text-xs text-slate-400 mt-1 uppercase tracking-wider font-bold">
                                Barres = réel · Courbe = planifié sur 6 mois
                            </p>
                        </div>
                        <div class="h-72 w-full">
                            <Bar :data="evolutionMixedData" :options="chartOptions" />
                        </div>
                    </div>

                </div>

                <!-- Campagnes actives (colonne droite 1/3) -->
                <div>
                    <div class="bg-slate-900 rounded-2xl shadow-xl p-8 text-white relative overflow-hidden">
                        <!-- Icône décorative de fond -->
                        <div class="absolute -right-8 -bottom-8 opacity-10 rotate-12 pointer-events-none">
                            <i class="pi pi-flag" style="font-size: 110px;"></i>
                        </div>

                        <h3 class="text-lg font-black mb-6 flex items-center gap-3">
                            <span class="h-2 w-2 rounded-full bg-teal-500 shrink-0"></span>
                            Mes campagnes actives
                        </h3>

                        <div class="space-y-4 relative z-10">
                            <div
                                v-for="camp in displayedCampaigns"
                                :key="camp.name"
                                class="p-5 rounded-xl bg-white/5 border border-white/10 hover:bg-white/10 transition-colors"
                            >
                                <div class="flex justify-between items-start mb-3">
                                    <p class="font-black text-teal-400 leading-tight">{{ camp.name }}</p>
                                    <span class="text-[8px] px-2 py-0.5 rounded bg-teal-500/20 text-teal-400 font-black uppercase tracking-widest shrink-0 ml-2">
                                        En cours
                                    </span>
                                </div>
                                <p class="text-[10px] text-slate-400 flex items-center gap-2 font-bold uppercase tracking-widest">
                                    <i class="pi pi-calendar text-teal-500"></i>
                                    {{ new Date(camp.start_date).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short' }) }}
                                    —
                                    {{ camp.end_date
                                        ? new Date(camp.end_date).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short' })
                                        : 'Indéfini' }}
                                </p>
                            </div>

                            <div
                                v-if="!displayedCampaigns.length"
                                class="text-center py-12 bg-white/5 rounded-xl border border-dashed border-white/10"
                            >
                                <i class="pi pi-inbox text-3xl mb-3 text-white/20 block"></i>
                                <p class="text-xs font-bold text-slate-500 uppercase tracking-widest">Aucune affectation</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
