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
            myAssignments: 0,
            myHours: 0,
            myPlanned: 0,
            gap: 0
        })
    },
    charts: {
        type: Object,
        default: () => ({
            activeCampaigns: [],
            weekPlanning: [],
            hoursEvolution: []
        })
    }
});

const page = usePage();
const toast = useToast();

const userRole = computed(() => page.props?.auth?.user?.role);
const isAuthorized = computed(() => userRole.value?.name?.toUpperCase() === 'TC');

// ── Graphe 1 : Planning de la semaine (Bar groupé jours) ─────────────────────
const weekData = computed(() => {
    const items = props.charts?.weekPlanning ?? [];
    return {
        labels: items.map(i => i.day),
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

// ── Graphe 2 : Évolution de mes heures sur 6 mois (Line) ─────────────────────
const evolutionData = computed(() => {
    const items = props.charts?.hoursEvolution ?? [];
    return {
        labels: items.map(i => i.month),
        datasets: [
            {
                label: 'Mes heures réelles',
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
                label: 'Mes heures planifiées',
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
            detail: 'Connecté en tant que Téléconseiller',
            life: 3000,
        });
    }
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
        y: { beginAtZero: true, grid: { color: '#F3F4F6' } },
    },
};

const evolutionOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'top', labels: { usePointStyle: true } },
    },
    scales: {
        x: { grid: { display: false } },
        y: { beginAtZero: true, grid: { color: '#F3F4F6' } },
    },
};
</script>

<template>
    <Toast />
    <Head title="Dashboard TC" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight">Mon Espace Personnel</h2>
                    <p class="text-sm text-slate-400 font-medium">Suivi de votre activité et de vos plannings</p>
                </div>
            </div>
        </template>

        <div class="py-6 space-y-10">
            <!-- KPI Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-center gap-5 group hover:border-teal-100 transition-all">
                    <div class="h-14 w-14 rounded-2xl bg-teal-50 flex items-center justify-center text-teal-600 group-hover:bg-teal-600 group-hover:text-white transition-all shadow-sm">
                        <i class="pi pi-briefcase text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Campagnes</p>
                        <p class="text-3xl font-black text-slate-800">{{ stats.myAssignments }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-center gap-5 group hover:border-teal-100 transition-all">
                    <div class="h-14 w-14 rounded-2xl bg-teal-50 flex items-center justify-center text-teal-600 group-hover:bg-teal-600 group-hover:text-white transition-all shadow-sm">
                        <i class="pi pi-clock text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Heures Réelles</p>
                        <p class="text-3xl font-black text-slate-800">{{ stats.myHours }}h</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-center gap-5 group hover:border-teal-100 transition-all">
                    <div class="h-14 w-14 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-slate-900 group-hover:text-white transition-all shadow-sm">
                        <i class="pi pi-calendar text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Prévues</p>
                        <p class="text-3xl font-black text-slate-800">{{ stats.myPlanned }}h</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-center gap-5 group hover:border-teal-100 transition-all">
                    <div :class="[stats.gap < 0 ? 'bg-rose-50 text-rose-600 group-hover:bg-rose-600' : 'bg-teal-50 text-teal-600 group-hover:bg-teal-600']" 
                        class="h-14 w-14 rounded-2xl flex items-center justify-center group-hover:text-white transition-all shadow-sm">
                        <i :class="[stats.gap < 0 ? 'pi-arrow-down' : 'pi-arrow-up']" class="pi text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Performance</p>
                        <p :class="[stats.gap < 0 ? 'text-rose-600' : 'text-teal-700']" class="text-3xl font-black">{{ stats.gap }}%</p>
                    </div>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Charts Column -->
                <div class="lg:col-span-2 space-y-8">
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8">
                        <div class="flex items-center justify-between mb-8">
                            <div>
                                <h3 class="text-lg font-black text-slate-800">Activité Hebdomadaire</h3>
                                <p class="text-[10px] text-slate-400 mt-1 uppercase tracking-widest font-bold">Heures produites vs planifiées</p>
                            </div>
                            <i class="pi pi-chart-bar text-slate-200 text-2xl"></i>
                        </div>
                        <div class="h-96">
                            <Bar :data="weekData" :options="weekOptions" />
                        </div>
                    </div>
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8">
                        <div class="flex items-center justify-between mb-8">
                            <div>
                                <h3 class="text-lg font-black text-slate-800">Évolution Mensuelle</h3>
                                <p class="text-[10px] text-slate-400 mt-1 uppercase tracking-widest font-bold">Suivi de production long terme</p>
                            </div>
                            <i class="pi pi-chart-line text-slate-200 text-2xl"></i>
                        </div>
                        <div class="h-96">
                            <Line :data="evolutionData" :options="evolutionOptions" />
                        </div>
                    </div>
                </div>

                <!-- Side Info Column -->
                <div class="space-y-8">
                    <div class="bg-slate-900 rounded-3xl shadow-xl p-8 text-white relative overflow-hidden">
                        <div class="absolute -right-10 -bottom-10 opacity-10 rotate-12">
                            <i class="pi pi-flag text-[120px]"></i>
                        </div>
                        <h3 class="text-lg font-black mb-8 flex items-center gap-3">
                            <span class="h-2 w-2 rounded-full bg-teal-500"></span>
                            Mes Campagnes Actives
                        </h3>
                        <div class="space-y-4 relative z-10">
                            <div v-for="camp in charts.activeCampaigns" :key="camp.name" 
                                class="p-5 rounded-2xl bg-white/5 border border-white/10 hover:bg-white/10 transition-colors group">
                                <div class="flex justify-between items-start mb-3">
                                    <p class="font-black text-teal-400 group-hover:text-teal-300 transition-colors">{{ camp.name }}</p>
                                    <span class="text-[8px] px-2 py-0.5 rounded bg-teal-500/20 text-teal-400 font-black uppercase tracking-widest">En cours</span>
                                </div>
                                <p class="text-[10px] text-slate-400 flex items-center gap-2 font-bold uppercase tracking-widest">
                                    <i class="pi pi-calendar text-teal-500"></i>
                                    {{ new Date(camp.start_date).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short' }) }} - {{ camp.end_date ? new Date(camp.end_date).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short' }) : 'Indéfini' }}
                                </p>
                            </div>
                            <div v-if="!charts.activeCampaigns?.length" class="text-center py-12 bg-white/5 rounded-2xl border border-dashed border-white/10">
                                <i class="pi pi-inbox text-3xl mb-3 text-white/20"></i>
                                <p class="text-xs font-bold text-slate-500 uppercase tracking-widest">Aucune affectation</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
