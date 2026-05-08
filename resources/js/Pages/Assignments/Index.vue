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
        active:   'bg-emerald-100 text-emerald-700',
        inactive: 'bg-slate-100 text-slate-600',
        terminée: 'bg-sky-100 text-sky-700',
    }
    return map[status] ?? 'bg-slate-100 text-slate-600'
}

const getPositionStyle = (code) => {
    const map = {
        CP:  { badge: 'bg-slate-900 text-white',  card: 'border-slate-200 bg-slate-50' },
        SUP: { badge: 'bg-blue-600 text-white',    card: 'border-blue-100 bg-blue-50' },
        TC:  { badge: 'bg-emerald-600 text-white', card: 'border-emerald-100 bg-emerald-50' },
        RH:  { badge: 'bg-purple-600 text-white',  card: 'border-purple-100 bg-purple-50' },
    }
    return map[code] ?? { badge: 'bg-slate-400 text-white', card: 'border-slate-100 bg-slate-50' }
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
    props.unassignedEmployees?.forEach(emp => {
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
    (props.unassignedEmployees ?? []).length
)
</script>

<template>
    <Head title="Affectations" />
    <AuthenticatedLayout>
        <div class="min-h-screen bg-slate-50 p-8">

            <!-- ── Header ──────────────────────────────────────────────────── -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-slate-900">Affectations</h1>
                    <p class="mt-2 text-slate-500">
                        <template v-if="isAdmin">Gérez les affectations hiérarchiques CP → SUP → TC.</template>
                        <template v-else-if="isCP">Gérez vos superviseurs et téléconseillers.</template>
                        <template v-else-if="isSUP">Votre équipe de téléconseillers.</template>
                        <template v-else>Votre affectation actuelle.</template>
                    </p>
                </div>
                <div class="flex gap-3 flex-wrap">
                    <!-- Admin : tout affecter -->
                    <template v-if="isAdmin">
                        <Button @click="cpDialogVisible = true"  label="Affecter un CP"  icon="pi pi-plus" class="rounded-xl" />
                        <Button @click="supDialogVisible = true" label="Affecter un SUP" icon="pi pi-plus" severity="secondary" class="rounded-xl" />
                        <Button @click="tcDialogVisible = true"  label="Affecter un TC"  icon="pi pi-plus" severity="success" class="rounded-xl" />
                    </template>
                    <!-- CP : affecter SUP et TC uniquement -->
                    <template v-else-if="isCP">
                        <Button @click="supDialogVisible = true" label="Affecter un SUP" icon="pi pi-plus" severity="secondary" class="rounded-xl" />
                        <Button @click="tcDialogVisible = true"  label="Affecter un TC"  icon="pi pi-plus" severity="success" class="rounded-xl" />
                    </template>
                </div>
            </div>

            <!-- ── VUE TC : uniquement son affectation ─────────────────────── -->
            <div v-if="isTC" class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-800 mb-4">Mon affectation actuelle</h2>
                <div v-if="myAssignment">
                    <div class="flex items-center gap-4 p-4 rounded-2xl bg-emerald-50 border border-emerald-100">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-600 text-xs font-bold text-white">TC</span>
                        <div>
                            <p class="font-semibold text-slate-800 text-lg">{{ myAssignment.campaign?.name }}</p>
                            <p class="text-sm text-slate-500 mt-1">
                                <i class="pi pi-user mr-1"></i>
                                Superviseur : {{ myAssignment.manager?.first_name }} {{ myAssignment.manager?.last_name }}
                            </p>
                            <p class="text-xs text-slate-400 mt-1">
                                <i class="pi pi-calendar mr-1"></i>
                                Depuis le {{ formatDate(myAssignment.start_date) }}
                            </p>
                        </div>
                    </div>
                </div>
                <div v-else class="py-12 text-center text-slate-400">
                    <i class="pi pi-info-circle text-4xl mb-3 block opacity-30"></i>
                    <p class="font-medium">Vous n'êtes actuellement affecté à aucune campagne.</p>
                </div>
            </div>

            <!-- ── VUE SUP/CP/ADMIN : TabView ──────────────────────────────── -->
            <TabView v-else>

                <!-- ═══ ONGLET 1 : Affectations actives ══════════════════════ -->
                <TabPanel header="Affectations actives">
                    <div class="space-y-6 mt-4">

                        <!-- Vide global -->
                        <div v-if="!campaigns?.length" class="py-16 text-center text-slate-400">
                            <i class="pi pi-users text-4xl mb-3 block opacity-30"></i>
                            <p class="font-medium">Aucune affectation active pour le moment.</p>
                        </div>

                        <div
                            v-for="campaign in campaigns"
                            :key="campaign.id"
                            class="rounded-[28px] border border-slate-200 bg-white shadow-sm overflow-hidden"
                        >
                            <!-- Header campagne -->
                            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                                <div class="flex items-center gap-3">
                                    <span class="text-lg font-bold text-slate-900">{{ campaign.name }}</span>
                                    <span :class="['rounded-full px-3 py-1 text-xs font-semibold uppercase', getStatusBadge(campaign.status)]">
                                        {{ campaign.status }}
                                    </span>
                                </div>
                                <span class="text-sm text-slate-400">{{ campaign.tree.length }} CP(s)</span>
                            </div>

                            <!-- Campagne vide -->
                            <div v-if="campaign.tree.length === 0" class="px-6 py-10 text-center text-slate-400 text-sm">
                                <i class="pi pi-users text-3xl mb-2 block opacity-30"></i>
                                Aucune ressource affectée à cette campagne.
                            </div>

                            <!-- Arbre CP > SUP > TC -->
                            <div v-else class="p-6 space-y-4">
                                <div v-for="cp in campaign.tree" :key="cp.id" class="rounded-2xl border border-slate-200 bg-slate-50 p-4">

                                    <!-- CP row -->
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-900 text-xs font-bold text-white">CP</span>
                                            <div>
                                                <p class="font-semibold text-slate-800">
                                                    {{ cp.employee.first_name }} {{ cp.employee.last_name }}
                                                </p>
                                                <p class="text-xs text-slate-400">Depuis {{ formatDate(cp.start_date) }}</p>
                                            </div>
                                        </div>
                                        <!-- Libérer CP : admin seulement -->
                                        <Button
                                            v-if="isAdmin"
                                            @click="openRelease(cp, `${cp.employee.first_name} ${cp.employee.last_name}`)"
                                            label="Libérer" size="small" severity="danger" outlined class="rounded-xl"
                                        />
                                    </div>

                                    <!-- SUPs -->
                                    <div v-if="cp.children?.length" class="mt-4 ml-8 space-y-3">
                                        <div v-for="sup in cp.children" :key="sup.id" class="rounded-2xl border border-blue-100 bg-blue-50 p-4">

                                            <!-- SUP row -->
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-3">
                                                    <span class="flex h-7 w-12 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white">SUP</span>
                                                    <div>
                                                        <p class="font-semibold text-slate-800">
                                                            {{ sup.employee.first_name }} {{ sup.employee.last_name }}
                                                        </p>
                                                        <p class="text-xs text-slate-400">Depuis {{ formatDate(sup.start_date) }}</p>
                                                    </div>
                                                </div>
                                                <!-- Libérer SUP : admin ou CP -->
                                                <Button
                                                    v-if="isAdmin || isCP"
                                                    @click="openRelease(sup, `${sup.employee.first_name} ${sup.employee.last_name}`)"
                                                    label="Libérer" size="small" severity="danger" outlined class="rounded-xl"
                                                />
                                            </div>

                                            <!-- TCs -->
                                            <div v-if="sup.children?.length" class="mt-3 ml-8 flex flex-wrap gap-2">
                                                <div
                                                    v-for="tc in sup.children"
                                                    :key="tc.id"
                                                    class="flex items-center gap-2 rounded-2xl border border-emerald-100 bg-emerald-50 px-3 py-2"
                                                >
                                                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-600 text-[10px] font-bold text-white">TC</span>
                                                    <span class="text-sm font-medium text-slate-700">
                                                        {{ tc.employee.first_name }} {{ tc.employee.last_name }}
                                                    </span>
                                                    <!-- Libérer TC : admin ou CP -->
                                                    <button
                                                        v-if="isAdmin || isCP"
                                                        @click="openRelease(tc, `${tc.employee.first_name} ${tc.employee.last_name}`)"
                                                        class="ml-1 text-red-400 hover:text-red-600 transition-colors"
                                                    >
                                                        <i class="pi pi-times text-xs"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <p v-else class="mt-2 ml-8 text-xs text-slate-400">Aucun TC affecté</p>
                                        </div>
                                    </div>
                                    <p v-else class="mt-3 ml-8 text-xs text-slate-400">Aucun superviseur affecté</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </TabPanel>

                <!-- ═══ ONGLET 2 : Ressources disponibles (Admin et CP) ═══════ -->
                <TabPanel v-if="isAdmin || isCP">
                    <template #header>
                        <div class="flex items-center gap-2">
                            <span>Ressources disponibles</span>
                            <span class="ml-1 flex h-5 w-5 items-center justify-center rounded-full bg-blue-600 text-[10px] font-bold text-white">
                                {{ totalUnassigned }}
                            </span>
                        </div>
                    </template>

                    <div class="mt-4 space-y-8">
                        <!-- Vide global -->
                        <div v-if="!totalUnassigned" class="py-16 text-center text-slate-400">
                            <i class="pi pi-check-circle text-4xl mb-3 block text-emerald-400"></i>
                            <p class="font-medium">Toutes les ressources sont affectées.</p>
                        </div>

                        <!-- Groupes par poste -->
                        <div v-for="code in sortedPositionKeys" :key="code">
                            <div class="flex items-center gap-3 mb-4">
                                <span :class="['rounded-full px-3 py-1 text-xs font-bold uppercase', getPositionStyle(code).badge]">
                                    {{ code }}
                                </span>
                                <span class="text-sm font-semibold text-slate-600">
                                    {{ unassignedByPosition[code].length }} disponible(s)
                                </span>
                                <div class="flex-1 h-px bg-slate-200"></div>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                                <div
                                    v-for="employee in unassignedByPosition[code]"
                                    :key="employee.id"
                                    :class="['rounded-2xl border p-4 bg-white shadow-sm', getPositionStyle(code).card]"
                                >
                                    <!-- Identité -->
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-700 font-bold text-sm">
                                                {{ employee.first_name[0] }}{{ employee.last_name[0] }}
                                            </div>
                                            <div>
                                                <p class="font-semibold text-slate-800">
                                                    {{ employee.first_name }} {{ employee.last_name }}
                                                </p>
                                                <p class="text-xs text-slate-400">{{ employee.matricule }}</p>
                                            </div>
                                        </div>
                                        <span :class="['rounded-full px-2 py-1 text-[10px] font-semibold uppercase', getEmployeeStatusStyle(employee.status)]">
                                            {{ employee.status }}
                                        </span>
                                    </div>

                                    <!-- Infos -->
                                    <div class="mt-4 space-y-1 text-xs text-slate-500">
                                        <div class="flex items-center gap-2">
                                            <i class="pi pi-briefcase text-slate-400 w-4"></i>
                                            {{ employee.position?.name }}
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <i class="pi pi-envelope text-slate-400 w-4"></i>
                                            {{ employee.email }}
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <i class="pi pi-calendar text-slate-400 w-4"></i>
                                            Embauché le {{ formatDate(employee.created_at) }}
                                        </div>
                                        <div v-if="employee.last_assignment" class="flex items-center gap-2">
                                            <i class="pi pi-history text-slate-400 w-4"></i>
                                            Dernière : {{ employee.last_assignment.campaign?.name }}
                                            ({{ formatDate(employee.last_assignment.end_date) }})
                                        </div>
                                        <div v-else class="flex items-center gap-2">
                                            <i class="pi pi-history text-slate-400 w-4"></i>
                                            Jamais affecté
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="mt-4 flex gap-2">
                                        <Button
                                            @click="openDialogForEmployee(employee)"
                                            label="Affecter"
                                            icon="pi pi-plus"
                                            size="small"
                                            class="rounded-xl flex-1"
                                            :disabled="employee.status !== 'actif' || code === 'RH' || (isCP && code === 'CP')"
                                        />
                                        <Button
                                            icon="pi pi-eye"
                                            size="small"
                                            severity="secondary"
                                            outlined
                                            class="rounded-xl"
                                            @click="router.visit(route('employees.show', employee.id))"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </TabPanel>

            </TabView>

            <!-- ─── Dialog Affecter CP (Admin uniquement) ──────────────────── -->
            <Dialog v-model:visible="cpDialogVisible" modal header="Affecter un Chef de Plateau" :style="{ width: '40rem' }">
                <div class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Chef de Plateau</label>
                        <Dropdown v-model="cpForm.employee_id" :options="cpOptions" optionLabel="label" optionValue="value" placeholder="Sélectionner un CP" class="w-full" filter />
                        <small v-if="cpForm.errors.employee_id" class="text-red-500">{{ cpForm.errors.employee_id }}</small>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Campagne(s) active(s)</label>
                        <MultiSelect v-model="cpForm.campaign_ids" :options="campaignOptions" optionLabel="label" optionValue="value" placeholder="Sélectionner une ou plusieurs campagnes" class="w-full" />
                        <small v-if="cpForm.errors.campaign_ids" class="text-red-500">{{ cpForm.errors.campaign_ids }}</small>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date de début</label>
                        <Calendar v-model="cpForm.start_date" class="w-full" dateFormat="dd/mm/yy" />
                        <small v-if="cpForm.errors.start_date" class="text-red-500">{{ cpForm.errors.start_date }}</small>
                    </div>
                </div>
                <template #footer>
                    <Button label="Annuler" severity="secondary" @click="cpDialogVisible = false" />
                    <Button label="Affecter" icon="pi pi-check" @click="submitCP" :loading="cpForm.processing" />
                </template>
            </Dialog>

            <!-- ─── Dialog Affecter SUP ────────────────────────────────────── -->
            <Dialog v-model:visible="supDialogVisible" modal header="Affecter un Superviseur" :style="{ width: '40rem' }">
                <div class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Superviseur</label>
                        <Dropdown v-model="supForm.employee_id" :options="supOptions" optionLabel="label" optionValue="value" placeholder="Sélectionner un SUP" class="w-full" filter />
                        <small v-if="supForm.errors.employee_id" class="text-red-500">{{ supForm.errors.employee_id }}</small>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Chef de Plateau / Campagne</label>
                        <Dropdown v-model="supForm.cp_assignment_id" :options="cpAssignmentOptions" optionLabel="label" optionValue="value" placeholder="Sélectionner un CP" class="w-full" filter />
                        <small v-if="supForm.errors.cp_assignment_id" class="text-red-500">{{ supForm.errors.cp_assignment_id }}</small>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date de début</label>
                        <Calendar v-model="supForm.start_date" class="w-full" dateFormat="dd/mm/yy" />
                        <small v-if="supForm.errors.start_date" class="text-red-500">{{ supForm.errors.start_date }}</small>
                    </div>
                </div>
                <template #footer>
                    <Button label="Annuler" severity="secondary" @click="supDialogVisible = false" />
                    <Button label="Affecter" icon="pi pi-check" @click="submitSUP" :loading="supForm.processing" />
                </template>
            </Dialog>

            <!-- ─── Dialog Affecter TC ─────────────────────────────────────── -->
            <Dialog v-model:visible="tcDialogVisible" modal header="Affecter des Téléconseillers" :style="{ width: '40rem' }">
                <div class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Téléconseiller(s)</label>
                        <MultiSelect v-model="tcForm.employee_ids" :options="tcOptions" optionLabel="label" optionValue="value" placeholder="Sélectionner un ou plusieurs TC" class="w-full" filter />
                        <small v-if="tcForm.errors.employee_ids" class="text-red-500">{{ tcForm.errors.employee_ids }}</small>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Superviseur</label>
                        <Dropdown v-model="tcForm.sup_assignment_id" :options="supAssignmentOptions" optionLabel="label" optionValue="value" placeholder="Sélectionner un SUP" class="w-full" filter />
                        <small v-if="tcForm.errors.sup_assignment_id" class="text-red-500">{{ tcForm.errors.sup_assignment_id }}</small>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date de début</label>
                        <Calendar v-model="tcForm.start_date" class="w-full" dateFormat="dd/mm/yy" />
                        <small v-if="tcForm.errors.start_date" class="text-red-500">{{ tcForm.errors.start_date }}</small>
                    </div>
                </div>
                <template #footer>
                    <Button label="Annuler" severity="secondary" @click="tcDialogVisible = false" />
                    <Button label="Affecter" icon="pi pi-check" @click="submitTC" :loading="tcForm.processing" />
                </template>
            </Dialog>

            <!-- ─── Dialog Libération ──────────────────────────────────────── -->
            <Dialog v-model:visible="releaseDialogVisible" modal header="Libérer une ressource" :style="{ width: '35rem' }">
                <div v-if="assignmentToRelease" class="space-y-4">
                    <div class="rounded-xl bg-amber-50 border border-amber-200 p-4 flex items-start gap-3">
                        <i class="pi pi-exclamation-triangle text-amber-500 text-xl mt-0.5"></i>
                        <div>
                            <p class="font-semibold text-amber-800">Attention : libération en cascade</p>
                            <p class="text-sm text-amber-700 mt-1">
                                Libérer un <strong>CP</strong> libère tous ses SUP et leurs TC.<br>
                                Libérer un <strong>SUP</strong> libère tous ses TC.
                            </p>
                        </div>
                    </div>
                    <p class="text-slate-600">
                        Voulez-vous vraiment libérer <strong>{{ assignmentToRelease.displayName }}</strong> de son affectation ?
                    </p>
                </div>
                <template #footer>
                    <Button label="Annuler" severity="secondary" @click="releaseDialogVisible = false" />
                    <Button label="Libérer" icon="pi pi-sign-out" severity="danger" @click="confirmRelease" />
                </template>
            </Dialog>

        </div>
    </AuthenticatedLayout>
</template>