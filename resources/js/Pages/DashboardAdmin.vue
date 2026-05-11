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
        default: () => ({ totalEmployees: 0, activeCampaigns: 0, totalUsers: 0, totalAssignments: 0, totalHours: 0, gap: 0 })
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

// Mixed 1 : campagnes par mois — barres + courbe tendance
const campaignsMixedData = computed(() => {
    const items = props.charts?.campaignsByMonth ?? [];
    const values = items.map(i => i.total);
    return {
        labels: items.map(i => i.month),
        datasets: [
            {
                type: 'bar',
                label: 'Campagnes créées',
                data: values,
                backgroundColor: 'rgba(13,148,136,0.6)',
                borderRadius: 6,
                borderSkipped: false,
                order: 2,
            },
            {
                type: 'line',
                label: 'Tendance',
                data: values,
                borderColor: '#0f172a',
                backgroundColor: 'transparent',
                pointBackgroundColor: '#0f172a',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                tension: 0.4,
                order: 1,
            },
        ],
    };
});

// Mixed 2 : employés par mois — barres + courbe cumulée
const employeesMixedData = computed(() => {
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
                backgroundColor: 'rgba(20,184,166,0.6)',
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
                backgroundColor: 'transparent',
                pointBackgroundColor: '#0f172a',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                tension: 0.4,
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
        legend: { position: 'top', labels: { usePointStyle: true, pointStyle: 'circle', font: { size: 12 } } },
        tooltip: { callbacks: { label: (ctx) => ` ${ctx.dataset.label} : ${ctx.parsed.y}` } },
    },
    scales: {
        x: { ticks: { font: { size: 11 } }, grid: { display: false } },
        y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#F3F4F6' } },
    },
};

const mixedOptionsDouble = {
    responsive: true,
    maintainAspectRatio: false,
    interaction: { mode: 'index', intersect: false },
    plugins: {
        legend: { position: 'top', labels: { usePointStyle: true, pointStyle: 'circle', font: { size: 12 } } },
        tooltip: { callbacks: { label: (ctx) => ` ${ctx.dataset.label} : ${ctx.parsed.y} employé(s)` } },
    },
    scales: {
        x: { ticks: { font: { size: 11 } }, grid: { display: false } },
        y:  { beginAtZero: true, position: 'left',  ticks: { precision: 0 }, grid: { color: '#F3F4F6' }, title: { display: true, text: 'Nouveaux' } },
        y2: { beginAtZero: true, position: 'right', ticks: { precision: 0 }, grid: { display: false }, title: { display: true, text: 'Cumulé' } },
    },
};

onMounted(() => {
    if (isAuthorized.value) {
        toast.add({ severity: 'success', summary: 'Bienvenue', detail: 'Connecté en tant qu\'Administrateur', life: 3000 });
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
                    <a :href="route('campaigns.stats.export.pdf')"
                       class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-sm font-semibold text-gray-700 rounded-lg hover:bg-gray-50 transition">
                        <i class="pi pi-file-pdf"></i> Exporter PDF
                    </a>
                    <a :href="route('campaigns.stats.export')"
                       class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-sm font-semibold text-gray-700 rounded-lg hover:bg-gray-50 transition">
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
                <div :class="[stats.gap < 0 ? 'bg-rose-50 border-rose-100' : 'bg-teal-50 border-teal-100']"
                    class="rounded-2xl p-6 shadow-sm border flex flex-col gap-1">
                    <p class="text-[10px] uppercase font-bold tracking-widest" :class="[stats.gap < 0 ? 'text-rose-400' : 'text-teal-500']">Écart planning</p>
                    <p class="text-3xl font-black" :class="[stats.gap < 0 ? 'text-rose-600' : 'text-teal-700']">{{ stats.gap > 0 ? '+' : '' }}{{ stats.gap }}%</p>
                </div>
            </div>

            <!-- Mixed Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
                    <div class="mb-6">
                        <h3 class="text-lg font-black text-slate-800">Campagnes par mois</h3>
                        <p class="text-xs text-slate-400 mt-1 uppercase tracking-wider font-bold">Barres = volume · Courbe = tendance</p>
                    </div>
                    <div class="h-80 w-full">
                        <Bar :data="campaignsMixedData" :options="mixedOptions" />
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
                    <div class="mb-6">
                        <h3 class="text-lg font-black text-slate-800">Évolution des effectifs</h3>
                        <p class="text-xs text-slate-400 mt-1 uppercase tracking-wider font-bold">Barres = nouveaux · Courbe = total cumulé</p>
                    </div>
                    <div class="h-80 w-full">
                        <Bar :data="employeesMixedData" :options="mixedOptionsDouble" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
