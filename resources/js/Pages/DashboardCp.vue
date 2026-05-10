<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';
import { Bar } from 'vue-chartjs';
import {
    Chart as ChartJS, Title, Tooltip, Legend,
    BarElement, CategoryScale, LinearScale
} from 'chart.js';
import { useToast } from 'primevue/usetoast';
import Button from 'primevue/button';
import Toast from 'primevue/toast';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            activeCampaigns: 0,
            totalAssignments: 0,
            totalEmployees: 0,
            totalHours: 0,
            gap: 0
        })
    },
    charts: {
        type: Object,
        default: () => ({
            presenceByEmployee: [],
            performanceByEmployee: []
        })
    }
});

const page = usePage();
const toast = useToast();

const userRole = computed(() => page.props?.auth?.user?.role);
const isAuthorized = computed(() => userRole.value?.name?.toUpperCase() === 'CP');

// ── Graphe 1 : Taux de présence par employé (Bar horizontal) ─────────────────
const presenceData = computed(() => {
    const items = props.charts?.presenceByEmployee ?? [];
    return {
        labels: items.map(i => i.name),
        datasets: [{
            label: 'Taux de présence (%)',
            data: items.map(i => i.presence_rate),
            backgroundColor: items.map(i =>
                i.presence_rate >= 80 ? 'rgba(20,184,166,0.7)' : // teal-500
                i.presence_rate >= 50 ? 'rgba(245,158,11,0.7)' : 'rgba(244,63,94,0.7)'
            ),
            borderRadius: 6,
        }],
    };
});

// ── Graphe 2 : Performance — heures réelles vs planifiées par employé ─────────
const performanceData = computed(() => {
    const items = props.charts?.performanceByEmployee ?? [];
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

onMounted(() => {
    if (isAuthorized.value) {
        toast.add({
            severity: 'success',
            summary: 'Bienvenue',
            detail: 'Connecté en tant que Chef Plateau',
            life: 3000,
        });
    }
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

// Options groupées pour la performance
const performanceOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 8 } },
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
</script>

<template>
    <Toast />
    <Head title="Dashboard CP" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight">Espace Chef de Plateau</h2>
                    <p class="text-sm text-slate-400 font-medium">Suivi opérationnel des campagnes</p>
                </div>
            </div>
        </template>

        <div class="py-6 space-y-10">
            <!-- KPI Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex flex-col gap-1 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-3 opacity-5 group-hover:scale-110 transition-transform">
                        <i class="pi pi-flag text-4xl text-teal-600"></i>
                    </div>
                    <p class="text-[10px] uppercase font-bold text-slate-400 tracking-widest">Campagnes</p>
                    <p class="text-3xl font-black text-teal-600">{{ stats.activeCampaigns }}</p>
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
                        <i class="pi pi-users text-4xl text-slate-900"></i>
                    </div>
                    <p class="text-[10px] uppercase font-bold text-slate-400 tracking-widest">Effectif</p>
                    <p class="text-3xl font-black text-slate-800">{{ stats.totalEmployees }}</p>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex flex-col gap-1 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-3 opacity-5 group-hover:scale-110 transition-transform">
                        <i class="pi pi-clock text-4xl text-teal-600"></i>
                    </div>
                    <p class="text-[10px] uppercase font-bold text-slate-400 tracking-widest">Volume heures</p>
                    <p class="text-3xl font-black text-teal-600">{{ stats.totalHours }}h</p>
                </div>
                <div :class="[stats.gap < 0 ? 'bg-rose-50 border-rose-100' : 'bg-teal-50 border-teal-100']" 
                    class="rounded-2xl p-6 shadow-sm border flex flex-col gap-1 relative overflow-hidden group">
                    <p class="text-[10px] uppercase font-bold tracking-widest" :class="[stats.gap < 0 ? 'text-rose-400' : 'text-teal-500']">Écart Prod.</p>
                    <p class="text-3xl font-black" :class="[stats.gap < 0 ? 'text-rose-600' : 'text-teal-700']">{{ stats.gap > 0 ? '+' : '' }}{{ stats.gap }}%</p>
                </div>
            </div>

            <!-- Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h3 class="text-lg font-black text-slate-800">Taux de Présence</h3>
                            <p class="text-[10px] text-slate-400 mt-1 uppercase tracking-widest font-bold font-medium">Top performance individuelle</p>
                        </div>
                        <i class="pi pi-users text-slate-200 text-2xl"></i>
                    </div>
                    <div class="h-96">
                        <Bar :data="presenceData" :options="presenceOptions" />
                    </div>
                </div>

                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h3 class="text-lg font-black text-slate-800">Réel vs Planifié</h3>
                            <p class="text-[10px] text-slate-400 mt-1 uppercase tracking-widest font-bold font-medium">Comparatif de production par agent</p>
                        </div>
                        <i class="pi pi-chart-bar text-slate-200 text-2xl"></i>
                    </div>
                    <div class="h-96">
                        <Bar :data="performanceData" :options="performanceOptions" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
