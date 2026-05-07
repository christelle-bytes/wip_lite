<template>
    <div :class="['space-y-4', level > 0 ? 'ml-10 border-l-2 border-slate-200 pl-6' : '']">
        <div class="flex items-start gap-4 rounded-3xl border border-slate-200 bg-slate-50 p-4">
            <div :class="['flex items-center justify-center rounded-full text-sm font-semibold text-white', level === 0 ? 'h-12 w-12' : 'h-10 w-10', getPositionColor(node.position?.name)]">
                {{ getInitials(node.employee?.last_name) }}{{ getInitials(node.employee?.first_name) }}
            </div>
            <div class="min-w-0 flex-1">
                <p class="font-semibold text-slate-900">{{ node.employee?.last_name?? 'Utilisateur' }} {{ node.employee?.first_name?? 'Utilisateur' }} </p>
                <span :class="['rounded-full px-3 py-1 text-xs font-semibold', getPositionClass(node.position?.name)]">{{ node.position?.name ?? '–' }}</span>
            </div>
        </div>

        <div v-if="node.tree_children && node.tree_children.length" class="space-y-4">
            <HierarchyNode v-for="child in node.tree_children" :key="child.id" :node="child" :level="level + 1" />
        </div>
    </div>
  
</template>

<script setup>
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
        case 'Chef de Plateau':
            return 'bg-slate-900 text-white'
        case 'Superviseur':
            return 'bg-blue-100 text-blue-800'
        case 'Teleconseiller':
            return 'bg-emerald-100 text-emerald-800'
        default:
            return 'bg-slate-100 text-slate-700'
    }
}

const getPositionColor = (position) => {
    switch (position) {
        case 'Chef de Plateau':
            return 'bg-slate-900'
        case 'Superviseur':
            return 'bg-blue-600'
        case 'Teleconseiller':
            return 'bg-emerald-600'
        default:
            return 'bg-slate-600'
    }
}

const getInitials = (name) => {
    if (!name) return '–'
    return name.split(' ').map((part) => part[0] ?? '').join('').slice(0, 2).toUpperCase()
}
</script>
