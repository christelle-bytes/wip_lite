<template>
    <div :class="['space-y-4', level > 0 ? 'ml-8 border-l-2 border-slate-100 pl-8 relative' : '']">
        <!-- Connector for children -->
        <div v-if="level > 0" class="absolute left-0 top-10 w-8 h-0.5 bg-slate-100"></div>

        <div class="flex items-center gap-4 rounded-2xl border border-slate-100 bg-white p-5 shadow-sm hover:shadow-md transition-shadow group">
            <div :class="['flex items-center justify-center rounded-xl text-xs font-black text-white shadow-lg', level === 0 ? 'h-12 w-12 text-sm' : 'h-10 w-10', getPositionColor(node.position?.name)]">
                {{ getInitials(node.employee?.last_name) }}{{ getInitials(node.employee?.first_name) }}
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                    <p class="font-bold text-slate-800">{{ node.employee?.first_name }} {{ node.employee?.last_name }}</p>
                    <span v-if="level === 0" class="px-2 py-0.5 rounded bg-teal-50 text-[8px] font-black text-teal-600 uppercase tracking-widest border border-teal-100">Responsable</span>
                </div>
                <div class="flex items-center gap-2 mt-1">
                    <span :class="['rounded px-2 py-0.5 text-[9px] font-black uppercase tracking-widest', getPositionClass(node.position?.name)]">
                        {{ node.position?.name ?? '–' }}
                    </span>
                    <span class="text-[10px] text-slate-400 font-medium">ID: #{{ node.employee_id }}</span>
                </div>
            </div>
            
            <div v-if="$page.props.auth.user.role.name.toUpperCase() === 'ADMIN'" class="opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1">
                 <Link :href="route('assignments.release', node.id)" method="patch" as="button" class="h-8 w-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition-all" title="Libérer l'affectation">
                    <i class="pi pi-trash text-xs"></i>
                </Link>
            </div>
        </div>

        <div v-if="node.tree_children && node.tree_children.length" class="space-y-4 mt-4">
            <HierarchyNode v-for="child in node.tree_children" :key="child.id" :node="child" :level="level + 1" />
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
defineOptions({ name: 'HierarchyNode' })

const props = defineProps({
    node: Object,
    level: {
        type: Number,
        default: 0
    }
})

const getPositionClass = (position) => {
    switch (position) {
        case 'Chef Plateau':
            return 'bg-slate-900 text-white shadow-lg shadow-slate-900/10'
        case 'Superviseur':
            return 'bg-teal-100 text-teal-700'
        case 'Teleconseiller':
            return 'bg-slate-100 text-slate-600'
        default:
            return 'bg-slate-50 text-slate-500'
    }
}

const getPositionColor = (position) => {
    switch (position) {
        case 'Chef Plateau':
            return 'bg-slate-900'
        case 'Superviseur':
            return 'bg-teal-600 shadow-teal-600/20'
        case 'Teleconseiller':
            return 'bg-slate-400'
        default:
            return 'bg-slate-300'
    }
}

const getInitials = (name) => {
    if (!name) return '?'
    return name.charAt(0).toUpperCase()
}
</script>
