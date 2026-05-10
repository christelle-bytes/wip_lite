<script setup>
import { ref, computed } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import Calendar from 'primevue/calendar'
import Dropdown from 'primevue/dropdown'
import MultiSelect from 'primevue/multiselect'
import TabView from 'primevue/tabview'
import TabPanel from 'primevue/tabpanel'
import InputText from 'primevue/inputtext'

const props = defineProps({
    activeCampaigns:     Array,
    unassignedCPs:       Array,
    unassignedSUPs:      Array,
    unassignedTCs:       Array,
    assignedCPs:         Array,
    assignedSUPs:        Array,
    campaigns:           Array,
    unassignedEmployees: Array,
    myAssignment:        Object,
    role:                String,
})

// ── Filtres ───────────────────────────────────────────────────────────────────
const searchActive = ref('')
const searchAvailable = ref('')

const filteredCampaigns = computed(() => {
    if (!searchActive.value) return props.campaigns
    const query = searchActive.value.toLowerCase()
    return props.campaigns.filter(c => 
        c.name.toLowerCase().includes(query) || 
        c.tree.some(cp => 
            `${cp.employee.first_name} ${cp.employee.last_name}`.toLowerCase().includes(query) ||
            cp.children.some(sup => 
                `${sup.employee.first_name} ${sup.employee.last_name}`.toLowerCase().includes(query)
            )
        )
    )
})

const filteredUnassignedEmployees = computed(() => {
    if (!searchAvailable.value) return props.unassignedEmployees
    const query = searchAvailable.value.toLowerCase()
    return props.unassignedEmployees.filter(e => 
        `${e.first_name} ${e.last_name}`.toLowerCase().includes(query) ||
        e.matricule.toLowerCase().includes(query) ||
        e.email.toLowerCase().includes(query)
    )
})

// ── Rôles ─────────────────────────────────────────────────────────────────────
const isAdmin = computed(() => props.role === 'admin')
const isCP    = computed(() => props.role === 'cp')
const isSUP   = computed(() => props.role === 'sup')
const isTC    = computed(() => props.role === 'tc')

// ── Dialogs ───────────────────────────────────────────────────────────────────
const cpDialogVisible      = ref(false)
const supDialogVisible     = ref(false)
const tcDialogVisible      = ref(false)
const releaseDialogVisible = ref(false)
const assignmentToRelease  = ref(null)

// ── Forms ─────────────────────────────────────────────────────────────────────
const cpForm  = useForm({ employee_id: null, campaign_ids: [], start_date: null })
const supForm = useForm({ employee_id: null, cp_assignment_id: null, start_date: null })
const tcForm  = useForm({ employee_ids: [], sup_assignment_id: null, start_date: null })

// ── Options dropdowns ─────────────────────────────────────────────────────────
const cpOptions = computed(() =>
    (props.unassignedCPs ?? []).map(e => ({
        label: `${e.first_name} ${e.last_name} (${e.matricule})`,
        value: e.id
    }))
)
const supOptions = computed(() =>
    (props.unassignedSUPs ?? []).map(e => ({
        label: `${e.first_name} ${e.last_name} (${e.matricule})`,
        value: e.id
    }))
)
const tcOptions = computed(() =>
    (props.unassignedTCs ?? []).map(e => ({
        label: `${e.first_name} ${e.last_name} (${e.matricule})`,
        value: e.id
    }))
)
const campaignOptions = computed(() =>
    (props.activeCampaigns ?? []).map(c => ({ label: c.name, value: c.id }))
)
const cpAssignmentOptions = computed(() =>
    (props.assignedCPs ?? []).map(a => ({
        label: `${a.employee.first_name} ${a.employee.last_name} → ${a.campaign.name}`,
        value: a.id
    }))
)
const supAssignmentOptions = computed(() =>
    (props.assignedSUPs ?? []).map(a => ({
        label: `${a.employee.first_name} ${a.employee.last_name} → ${a.campaign.name}`,
        value: a.id
    }))
)

// ── Ouvrir dialog depuis carte "disponibles" ──────────────────────────────────
const openDialogForEmployee = (employee) => {
    const code = employee.position?.code
    if (code === 'CP') {
        cpForm.reset(); cpForm.employee_id = employee.id
        cpDialogVisible.value = true
    } else if (code === 'SUP') {
        supForm.reset(); supForm.employee_id = employee.id
        supDialogVisible.value = true
    } else if (code === 'TC') {
        tcForm.reset(); tcForm.employee_ids = [employee.id]
        tcDialogVisible.value = true
    }
}

// ── Soumissions ───────────────────────────────────────────────────────────────
const submitCP = () => {
    cpForm.post(route('assignments.assignCP'), {
        onSuccess: () => { cpDialogVisible.value = false; cpForm.reset() }
    })
}
const submitSUP = () => {
    supForm.post(route('assignments.assignSUP'), {
        onSuccess: () => { supDialogVisible.value = false; supForm.reset() }
    })
}
const submitTC = () => {
    tcForm.post(route('assignments.assignTC'), {
        onSuccess: () => { tcDialogVisible.value = false; tcForm.reset() }
    })
}

// ── Libération ────────────────────────────────────────────────────────────────
const openRelease = (assignment, name) => {
    assignmentToRelease.value = { ...assignment, displayName: name }
    releaseDialogVisible.value = true
}
const confirmRelease = () => {
    router.patch(route('assignments.release', assignmentToRelease.value.id), {}, {
        onSuccess: () => { releaseDialogVisible.value = false }
    })
}

// ── Helpers ───────────────────────────────────────────────────────────────────
const getStatusBadge = (status) => {
    const map = {
        active:   'bg-teal-100 text-teal-700',
        inactive: 'bg-slate-100 text-slate-600',
        terminée: 'bg-slate-200 text-slate-800',
    }
    return map[status] ?? 'bg-slate-100 text-slate-600'
}

const getPositionStyle = (code) => {
    const map = {
        CP:  { badge: 'bg-slate-900 text-white shadow-lg shadow-slate-900/10',  card: 'border-slate-100 bg-white' },
        SUP: { badge: 'bg-teal-600 text-white shadow-lg shadow-teal-600/20',    card: 'border-teal-100 bg-teal-50/30' },
        TC:  { badge: 'bg-slate-500 text-white shadow-lg shadow-slate-500/10', card: 'border-slate-100 bg-white' },
        RH:  { badge: 'bg-slate-900 text-white shadow-lg shadow-slate-900/10',  card: 'border-slate-100 bg-white' },
    }
    return map[code] ?? { badge: 'bg-slate-400 text-white', card: 'border-slate-100 bg-white' }
}

const getEmployeeStatusStyle = (status) => {
    const map = {
        actif:    'bg-emerald-100 text-emerald-700',
        suspendu: 'bg-amber-100 text-amber-700',
        inactif:  'bg-red-100 text-red-700',
    }
    return map[status] ?? 'bg-slate-100 text-slate-600'
}

const formatDate = (date) => {
    if (!date) return '—'
    return new Date(date).toLocaleDateString('fr-FR', {
        day: '2-digit', month: '2-digit', year: 'numeric'
    })
}

// Grouper les non affectés par poste
const unassignedByPosition = computed(() => {
    const groups = {}
    filteredUnassignedEmployees.value?.forEach(emp => {
        const code = emp.position?.code ?? 'Autre'
        if (!groups[code]) groups[code] = []
        groups[code].push(emp)
    })
    return groups
})

const positionOrder = ['CP', 'SUP', 'TC', 'RH']
const sortedPositionKeys = computed(() =>
    positionOrder.filter(k => unassignedByPosition.value[k])
)

const totalUnassigned = computed(() =>
    (filteredUnassignedEmployees.value ?? []).length
)
</script>

<template>
    <Head title="Affectations" />
    <AuthenticatedLayout>
        <div class="py-6 space-y-10">
            <!-- ── Header ──────────────────────────────────────────────────── -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">Affectations</h1>
                    <p class="mt-1 text-sm text-slate-500 font-medium">
                        <template v-if="isAdmin">Pilotez la structure hiérarchique de vos campagnes.</template>
                        <template v-else-if="isCP">Gérez vos équipes et vos campagnes.</template>
                        <template v-else-if="isSUP">Supervisez vos téléconseillers.</template>
                        <template v-else>Consultez votre affectation actuelle.</template>
                    </p>
                </div>
                <div class="flex gap-3 flex-wrap">
                    <template v-if="isAdmin">
                        <Button @click="cpDialogVisible = true" label="Assigner CP" icon="pi pi-plus" class="bg-slate-900 border-none px-5 py-2.5 rounded-xl font-bold text-xs shadow-lg shadow-slate-900/10 transition-all" />
                        <Button @click="supDialogVisible = true" label="Assigner SUP" icon="pi pi-plus" class="bg-teal-600 border-none px-5 py-2.5 rounded-xl font-bold text-xs shadow-lg shadow-teal-600/20 transition-all" />
                        <Button @click="tcDialogVisible = true" label="Assigner TC" icon="pi pi-plus" class="bg-white border border-slate-200 text-slate-700 px-5 py-2.5 rounded-xl font-bold text-xs hover:border-teal-500 hover:text-teal-600 transition-all" />
                    </template>
                    <template v-else-if="isCP">
                        <Button @click="supDialogVisible = true" label="Assigner SUP" icon="pi pi-plus" class="bg-teal-600 border-none px-5 py-2.5 rounded-xl font-bold text-xs shadow-lg shadow-teal-600/20 transition-all" />
                        <Button @click="tcDialogVisible = true" label="Assigner TC" icon="pi pi-plus" class="bg-white border border-slate-200 text-slate-700 px-5 py-2.5 rounded-xl font-bold text-xs hover:border-teal-500 hover:text-teal-600 transition-all" />
                    </template>
                </div>
            </div>

            <!-- ── VUE TC ─────────────────────── -->
            <div v-if="isTC" class="bg-white rounded-3xl border border-slate-100 p-8 shadow-sm">
                <h2 class="text-xl font-black text-slate-800 mb-8 flex items-center gap-2">
                    <i class="pi pi-id-card text-teal-500"></i>
                    Mon affectation
                </h2>
                <div v-if="myAssignment" class="relative">
                    <div class="flex flex-col md:flex-row md:items-center gap-8 p-8 rounded-3xl bg-teal-50 border border-teal-100">
                        <div class="h-20 w-20 rounded-2xl bg-teal-600 flex items-center justify-center text-white shadow-xl shadow-teal-600/20">
                            <i class="pi pi-briefcase text-3xl"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-[10px] font-black uppercase tracking-widest text-teal-600 mb-1">Campagne actuelle</p>
                            <h3 class="text-3xl font-black text-slate-900 mb-4">{{ myAssignment.campaign?.name }}</h3>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-xl bg-white flex items-center justify-center text-slate-400 border border-teal-100">
                                        <i class="pi pi-user text-sm"></i>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Superviseur</p>
                                        <p class="text-sm font-black text-slate-700">{{ myAssignment.manager?.first_name }} {{ myAssignment.manager?.last_name }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-xl bg-white flex items-center justify-center text-slate-400 border border-teal-100">
                                        <i class="pi pi-calendar text-sm"></i>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Depuis le</p>
                                        <p class="text-sm font-black text-slate-700">{{ formatDate(myAssignment.start_date) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="py-20 text-center bg-slate-50 rounded-3xl border border-dashed border-slate-200">
                    <i class="pi pi-info-circle text-4xl mb-4 text-slate-300"></i>
                    <p class="font-bold text-slate-400">Aucune affectation active pour le moment.</p>
                </div>
            </div>

            <!-- ── VUE SUP/CP/ADMIN ──────────────────────────────── -->
            <div v-else class="space-y-8">
                <TabView class="custom-tabview">
                    <TabPanel>
                        <template #header>
                            <div class="flex items-center gap-2 px-2">
                                <i class="pi pi-check-circle"></i>
                                <span class="font-black text-xs uppercase tracking-widest">Actives</span>
                            </div>
                        </template>
                        
                        <div class="flex items-center justify-between mt-8 mb-6">
                            <div class="relative group max-w-md w-full">
                                <i class="pi pi-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-teal-500 transition-colors"></i>
                                <InputText v-model="searchActive" placeholder="Rechercher une campagne, un CP ou un SUP..." 
                                    class="w-full pl-12 pr-4 py-3 bg-white border-slate-100 rounded-xl focus:border-teal-500 focus:ring-teal-500 transition-all placeholder:text-slate-400 text-sm font-medium shadow-sm" />
                            </div>
                        </div>
                        
                        <div class="grid gap-8">
                            <div
                                v-for="campaign in filteredCampaigns"
                                :key="campaign.id"
                                class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden"
                            >
                                <div class="px-8 py-6 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                                    <div class="flex items-center gap-4">
                                        <h3 class="text-xl font-black text-slate-900">{{ campaign.name }}</h3>
                                        <span :class="['rounded-lg px-2.5 py-1 text-[9px] font-black uppercase tracking-widest', getStatusBadge(campaign.status)]">
                                            {{ campaign.status }}
                                        </span>
                                    </div>
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest bg-white px-3 py-1.5 rounded-full border border-slate-100 shadow-sm">
                                        {{ campaign.tree.length ? 'Chef de Plateau assigné' : 'Aucun responsable' }}
                                    </span>
                                </div>

                                <div class="p-8">
                                    <div v-if="campaign.tree.length === 0" class="py-12 text-center text-slate-400">
                                        <i class="pi pi-users text-4xl mb-4 opacity-20"></i>
                                        <p class="font-bold">Aucune ressource affectée.</p>
                                    </div>
                                    <div v-else class="space-y-6">
                                        <!-- CP Tree -->
                                        <div v-for="cp in campaign.tree" :key="cp.id" class="rounded-2xl border border-slate-100 p-6 bg-slate-50/30">
                                            <div class="flex items-center justify-between gap-4 mb-6">
                                                <div class="flex items-center gap-4">
                                                    <div class="h-12 w-12 rounded-xl bg-slate-900 flex items-center justify-center text-white font-black text-xs shadow-lg shadow-slate-900/10">CP</div>
                                                    <div>
                                                        <p class="font-black text-slate-800 text-lg">{{ cp.employee.first_name }} {{ cp.employee.last_name }}</p>
                                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Assigné le {{ formatDate(cp.start_date) }}</p>
                                                    </div>
                                                </div>
                                                <Button v-if="isAdmin" @click="openRelease(cp, `${cp.employee.first_name} ${cp.employee.last_name}`)" 
                                                    icon="pi pi-sign-out" label="Libérer" class="p-button-text p-button-danger font-black text-[10px] uppercase tracking-widest hover:bg-rose-50 rounded-xl" />
                                            </div>

                                            <!-- SUPs -->
                                            <div class="ml-12 space-y-4">
                                                <div v-for="sup in cp.children" :key="sup.id" class="bg-white rounded-2xl border border-teal-100 p-5 shadow-sm">
                                                    <div class="flex items-center justify-between gap-4 mb-4">
                                                        <div class="flex items-center gap-4">
                                                            <div class="h-10 w-10 rounded-xl bg-teal-600 flex items-center justify-center text-white font-black text-[10px] shadow-lg shadow-teal-600/10">SUP</div>
                                                            <div>
                                                                <p class="font-black text-slate-800">{{ sup.employee.first_name }} {{ sup.employee.last_name }}</p>
                                                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Depuis {{ formatDate(sup.start_date) }}</p>
                                                            </div>
                                                        </div>
                                                        <Button v-if="isAdmin || isCP" @click="openRelease(sup, `${sup.employee.first_name} ${sup.employee.last_name}`)" 
                                                            icon="pi pi-sign-out" class="p-button-text p-button-danger p-button-sm rounded-lg" />
                                                    </div>

                                                    <!-- TCs -->
                                                    <div class="ml-14 flex flex-wrap gap-2">
                                                        <div v-for="tc in sup.children" :key="tc.id" class="flex items-center gap-2.5 px-3 py-2 bg-slate-50 border border-slate-100 rounded-xl group transition-all hover:border-teal-200 hover:bg-white">
                                                            <div class="h-6 w-6 rounded-lg bg-slate-200 flex items-center justify-center text-slate-600 font-black text-[8px] group-hover:bg-teal-100 group-hover:text-teal-600 transition-colors">TC</div>
                                                            <span class="text-xs font-bold text-slate-700">{{ tc.employee.first_name }} {{ tc.employee.last_name }}</span>
                                                            <button v-if="isAdmin || isCP" @click="openRelease(tc, `${tc.employee.first_name} ${tc.employee.last_name}`)" 
                                                                class="ml-1 text-slate-300 hover:text-rose-500 transition-colors">
                                                                <i class="pi pi-times-circle text-xs"></i>
                                                            </button>
                                                        </div>
                                                        <p v-if="!sup.children?.length" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest italic">Aucun TC affecté</p>
                                                    </div>
                                                </div>
                                                <p v-if="!cp.children?.length" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest italic ml-4">Aucun superviseur affecté</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </TabPanel>

                    <TabPanel v-if="isAdmin || isCP">
                        <template #header>
                            <div class="flex items-center gap-2 px-2">
                                <i class="pi pi-user-plus"></i>
                                <span class="font-black text-xs uppercase tracking-widest">Disponibles</span>
                                <span class="bg-teal-600 text-white text-[9px] font-black px-1.5 py-0.5 rounded-md min-w-[20px] text-center ml-1">
                                    {{ totalUnassigned }}
                                </span>
                            </div>
                        </template>

                        <div class="mt-8 space-y-12">
                            <div class="flex items-center justify-between mb-8">
                                <div class="relative group max-w-md w-full">
                                    <i class="pi pi-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-teal-500 transition-colors"></i>
                                    <InputText v-model="searchAvailable" placeholder="Rechercher par nom, matricule ou email..." 
                                        class="w-full pl-12 pr-4 py-3 bg-white border-slate-100 rounded-xl focus:border-teal-500 focus:ring-teal-500 transition-all placeholder:text-slate-400 text-sm font-medium shadow-sm" />
                                </div>
                            </div>

                            <div v-if="!totalUnassigned" class="py-24 text-center bg-white rounded-3xl border border-dashed border-slate-200">
                                <i class="pi pi-check-circle text-5xl text-teal-500 mb-4 opacity-20"></i>
                                <p class="text-slate-400 font-black uppercase tracking-widest text-sm">Aucune ressource ne correspond à votre recherche</p>
                            </div>

                            <div v-for="code in sortedPositionKeys" :key="code" class="space-y-6">
                                <div class="flex items-center gap-4">
                                    <span :class="['px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest', getPositionStyle(code).badge]">
                                        {{ code }}
                                    </span>
                                    <div class="h-px flex-1 bg-slate-100"></div>
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ unassignedByPosition[code].length }} libre(s)</span>
                                </div>

                                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                                    <div v-for="employee in unassignedByPosition[code]" :key="employee.id" 
                                        class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm hover:shadow-xl hover:shadow-slate-200/50 transition-all group relative overflow-hidden">
                                        <div class="absolute top-0 right-0 p-3 opacity-0 group-hover:opacity-10 transition-opacity">
                                            <i class="pi pi-user text-4xl text-slate-900"></i>
                                        </div>
                                        
                                        <div class="flex items-center gap-4 mb-6">
                                            <div class="h-12 w-12 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400 font-black group-hover:bg-teal-50 group-hover:text-teal-600 group-hover:border-teal-100 transition-all">
                                                {{ employee.first_name[0] }}{{ employee.last_name[0] }}
                                            </div>
                                            <div class="min-w-0">
                                                <p class="font-black text-slate-800 truncate">{{ employee.first_name }} {{ employee.last_name }}</p>
                                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Mat: {{ employee.matricule }}</p>
                                            </div>
                                        </div>

                                        <div class="space-y-3 mb-6">
                                            <div class="flex items-center gap-2 text-[11px] text-slate-500 font-medium">
                                                <i class="pi pi-envelope text-teal-500 text-[10px]"></i>
                                                <span class="truncate">{{ employee.email }}</span>
                                            </div>
                                            <div class="flex items-center gap-2 text-[11px] text-slate-500 font-medium">
                                                <i class="pi pi-calendar text-teal-500 text-[10px]"></i>
                                                <span>Depuis le {{ formatDate(employee.created_at) }}</span>
                                            </div>
                                        </div>

                                        <div class="flex gap-2">
                                            <Button @click="openDialogForEmployee(employee)" label="Affecter" icon="pi pi-plus" 
                                                class="flex-1 bg-teal-600 border-none font-black text-[10px] uppercase tracking-widest p-2.5 rounded-xl shadow-lg shadow-teal-600/10"
                                                :disabled="employee.status !== 'actif' || code === 'RH' || (isCP && code === 'CP')" />
                                            <Button icon="pi pi-eye" class="p-button-secondary p-button-text p-button-sm rounded-xl" 
                                                @click="router.visit(route('employees.show', employee.id))" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </TabPanel>
                </TabView>
            </div>
        </div>

        <!-- Styled Dialogs -->
        <Dialog v-model:visible="cpDialogVisible" modal header="Assigner Chef de Plateau" class="rounded-3xl shadow-2xl border-none" :style="{ width: '450px' }"
            :pt="{ header: { class: 'bg-slate-50 p-6 rounded-t-3xl border-b border-slate-100' }, content: { class: 'p-8 bg-white' }, footer: { class: 'p-6 bg-slate-50 rounded-b-3xl border-t border-slate-100' } }">
            <div class="space-y-6">
                <div class="flex flex-col gap-2">
                    <label class="text-xs font-black text-slate-500 uppercase tracking-widest">Chef de Plateau</label>
                    <Dropdown v-model="cpForm.employee_id" :options="cpOptions" optionLabel="label" optionValue="value" placeholder="Sélectionner un CP" class="w-full rounded-xl border-slate-200" filter />
                </div>
                <div class="flex flex-col gap-2">
                    <label class="text-xs font-black text-slate-500 uppercase tracking-widest">Campagnes</label>
                    <MultiSelect v-model="cpForm.campaign_ids" :options="campaignOptions" optionLabel="label" optionValue="value" placeholder="Sélectionner les campagnes" class="w-full rounded-xl border-slate-200" />
                </div>
                <div class="flex flex-col gap-2">
                    <label class="text-xs font-black text-slate-500 uppercase tracking-widest">Date de début</label>
                    <Calendar v-model="cpForm.start_date" class="w-full" inputClass="p-3 rounded-xl border-slate-200" dateFormat="dd/mm/yy" />
                </div>
            </div>
            <template #footer>
                <div class="flex gap-3 w-full">
                    <Button label="Annuler" class="flex-1 p-button-text p-button-secondary font-black text-xs uppercase" @click="cpDialogVisible = false" />
                    <Button label="Confirmer" class="flex-1 bg-teal-600 border-none font-black text-xs uppercase p-3 rounded-xl shadow-lg shadow-teal-600/20" @click="submitCP" :loading="cpForm.processing" />
                </div>
            </template>
        </Dialog>

        <!-- Similar styling for SUP and TC Dialogs... -->
        <Dialog v-model:visible="supDialogVisible" modal header="Assigner Superviseur" class="rounded-3xl shadow-2xl border-none" :style="{ width: '450px' }"
            :pt="{ header: { class: 'bg-slate-50 p-6 rounded-t-3xl border-b border-slate-100' }, content: { class: 'p-8 bg-white' }, footer: { class: 'p-6 bg-slate-50 rounded-b-3xl border-t border-slate-100' } }">
            <div class="space-y-6">
                <div class="flex flex-col gap-2">
                    <label class="text-xs font-black text-slate-500 uppercase tracking-widest">Superviseur</label>
                    <Dropdown v-model="supForm.employee_id" :options="supOptions" optionLabel="label" optionValue="value" placeholder="Sélectionner un SUP" class="w-full rounded-xl border-slate-200" filter />
                </div>
                <div class="flex flex-col gap-2">
                    <label class="text-xs font-black text-slate-500 uppercase tracking-widest">Manager (CP)</label>
                    <Dropdown v-model="supForm.cp_assignment_id" :options="cpAssignmentOptions" optionLabel="label" optionValue="value" placeholder="Assigner à un CP" class="w-full rounded-xl border-slate-200" filter />
                </div>
                <div class="flex flex-col gap-2">
                    <label class="text-xs font-black text-slate-500 uppercase tracking-widest">Date de début</label>
                    <Calendar v-model="supForm.start_date" class="w-full" inputClass="p-3 rounded-xl border-slate-200" dateFormat="dd/mm/yy" />
                </div>
            </div>
            <template #footer>
                <div class="flex gap-3 w-full">
                    <Button label="Annuler" class="flex-1 p-button-text p-button-secondary font-black text-xs uppercase" @click="supDialogVisible = false" />
                    <Button label="Confirmer" class="flex-1 bg-teal-600 border-none font-black text-xs uppercase p-3 rounded-xl shadow-lg shadow-teal-600/20" @click="submitSUP" :loading="supForm.processing" />
                </div>
            </template>
        </Dialog>

        <Dialog v-model:visible="tcDialogVisible" modal header="Assigner Téléconseillers" class="rounded-3xl shadow-2xl border-none" :style="{ width: '450px' }"
            :pt="{ header: { class: 'bg-slate-50 p-6 rounded-t-3xl border-b border-slate-100' }, content: { class: 'p-8 bg-white' }, footer: { class: 'p-6 bg-slate-50 rounded-b-3xl border-t border-slate-100' } }">
            <div class="space-y-6">
                <div class="flex flex-col gap-2">
                    <label class="text-xs font-black text-slate-500 uppercase tracking-widest">Téléconseillers</label>
                    <MultiSelect v-model="tcForm.employee_ids" :options="tcOptions" optionLabel="label" optionValue="value" placeholder="Sélectionner les TC" class="w-full rounded-xl border-slate-200" filter />
                </div>
                <div class="flex flex-col gap-2">
                    <label class="text-xs font-black text-slate-500 uppercase tracking-widest">Superviseur</label>
                    <Dropdown v-model="tcForm.sup_assignment_id" :options="supAssignmentOptions" optionLabel="label" optionValue="value" placeholder="Assigner à un SUP" class="w-full rounded-xl border-slate-200" filter />
                </div>
                <div class="flex flex-col gap-2">
                    <label class="text-xs font-black text-slate-500 uppercase tracking-widest">Date de début</label>
                    <Calendar v-model="tcForm.start_date" class="w-full" inputClass="p-3 rounded-xl border-slate-200" dateFormat="dd/mm/yy" />
                </div>
            </div>
            <template #footer>
                <div class="flex gap-3 w-full">
                    <Button label="Annuler" class="flex-1 p-button-text p-button-secondary font-black text-xs uppercase" @click="tcDialogVisible = false" />
                    <Button label="Confirmer" class="flex-1 bg-teal-600 border-none font-black text-xs uppercase p-3 rounded-xl shadow-lg shadow-teal-600/20" @click="submitTC" :loading="tcForm.processing" />
                </div>
            </template>
        </Dialog>

        <!-- Release Dialog -->
        <Dialog v-model:visible="releaseDialogVisible" modal header="Confirmer la Libération" class="rounded-3xl shadow-2xl border-none" :style="{ width: '400px' }"
            :pt="{ header: { class: 'bg-slate-50 p-6 rounded-t-3xl border-b border-slate-100' }, content: { class: 'p-8 bg-white' }, footer: { class: 'p-6 bg-slate-50 rounded-b-3xl border-t border-slate-100' } }">
            <div v-if="assignmentToRelease" class="space-y-6">
                <div class="rounded-2xl bg-rose-50 border border-rose-100 p-4">
                    <div class="flex items-start gap-3 text-rose-600">
                        <i class="pi pi-exclamation-triangle mt-0.5"></i>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest">Impact en cascade</p>
                            <p class="text-xs font-medium mt-1">La libération d'un responsable désassigne également toute sa hiérarchie inférieure.</p>
                        </div>
                    </div>
                </div>
                <p class="text-sm text-slate-600 leading-relaxed text-center">
                    Libérer <span class="font-black text-slate-900">{{ assignmentToRelease.displayName }}</span> de ses fonctions actuelles ?
                </p>
            </div>
            <template #footer>
                <div class="flex gap-3 w-full">
                    <Button label="Annuler" class="flex-1 p-button-text p-button-secondary font-black text-xs uppercase" @click="releaseDialogVisible = false" />
                    <Button label="Libérer" class="flex-1 bg-rose-600 border-none font-black text-xs uppercase p-3 rounded-xl shadow-lg shadow-rose-600/20" @click="confirmRelease" />
                </div>
            </template>
        </Dialog>
    </AuthenticatedLayout>
</template>