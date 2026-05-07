<script setup>
import { ref, computed } from "vue";
import { Link, router, useForm } from "@inertiajs/vue3";

defineProps({
    planningModels: Array,
    auth: Object,
});

const isEditing = ref(false);
const editingId = ref(null);

const form = useForm({
    name: "",
    description: "",
    monday_hours: 0,
    tuesday_hours: 0,
    wednesday_hours: 0,
    thursday_hours: 0,
    friday_hours: 0,
    saturday_hours: 0,
    sunday_hours: 0,
});

function submit() {
    if (isEditing.value) {
        form.put(`/planning/${editingId.value}`, {
            onSuccess: () => {
                form.reset();
                isEditing.value = false;
                editingId.value = null;
            },
            onError: (errors) => {
                console.log('Erreur validation:', errors);
            }
        });
    } else {
        form.post("/planning", {
            onSuccess: () => {
                form.reset();
            },
            onError: (errors) => {
                console.log('Erreur validation:', errors);
            }
        });
    }
}

function startEdit(planningModel) {
    isEditing.value = true;
    editingId.value = planningModel.id;
    form.name = planningModel.name;
    form.description = planningModel.description;
    form.monday_hours = planningModel.monday_hours;
    form.tuesday_hours = planningModel.tuesday_hours;
    form.wednesday_hours = planningModel.wednesday_hours;
    form.thursday_hours = planningModel.thursday_hours;
    form.friday_hours = planningModel.friday_hours;
    form.saturday_hours = planningModel.saturday_hours;
    form.sunday_hours = planningModel.sunday_hours;
}

function cancelEdit() {
    form.reset();
    isEditing.value = false;
    editingId.value = null;
}

const deletePlanning = (planningModel) => {
    if (confirm("Supprimer ce planning ?")) {
        router.delete(`/planning/${planningModel.id}`, {
            onSuccess: () => {},
            onError: (error) => {
                alert("Erreur lors de la suppression: " + error.message);
            },
        });
    }
};
</script>
<template>
    <h1>création de planning</h1>
    <form @submit.prevent="submit">
        <label for="name">name</label
        ><input v-model="form.name" type="text" name="name" /> <br />

        <label for="description">description</label
        ><input v-model="form.description" type="text" name="description" />
        <br />

        <label for="monday_hours">monday_hours</label
        ><input
            v-model="form.monday_hours"
            type="text"
            name="monday_hours"
        /><br />

        <label for="tuesday_hours">tuesday_hours</label
        ><input
            v-model="form.tuesday_hours"
            type="text"
            name="tuesday_hours"
        /><br />

        <label for="wednesday_hours">wednesday_hours</label
        ><input
            v-model="form.wednesday_hours"
            type="text"
            name="wednesday_hours"
        /><br />

        <label for="thursday_hours">thursday_hours</label
        ><input
            v-model="form.thursday_hours"
            type="text"
            name="thursday_hours"
        /><br />

        <label for="friday_hours">friday_hours</label
        ><input
            v-model="form.friday_hours"
            type="text"
            name="friday_hours"
        /><br />

        <label for="saturday_hours">saturday_hours</label
        ><input
            v-model="form.saturday_hours"
            type="text"
            name="saturday_hours"
        /><br />

        <label for="sunday_hours">sunday_hours</label
        ><input
            v-model="form.sunday_hours"
            type="text"
            name="sunday_hours"
        /><br />
        <button style="color: green" type="submit">{{ isEditing ? 'Confirmer' : 'Créer' }}</button>
        <button v-if="isEditing" style="color: orange; margin-left: 10px" type="button" @click="cancelEdit">Annuler</button>
    </form>

    <div v-if="planningModels.length === 0">
        <p class="text-gray-500">Tu n'as pas encore de projets.</p>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div
            v-for="planningModel in planningModels"
            :key="planningModel.id"
            class="bg-white rounded-lg shadow p-4 border border-gray-200"
        >
            <h2 class="text-lg font-semibold">{{ planningModel.name }}</h2>
            <p class="text-sm text-gray-400 mt-1">
                Créé le {{ planningModel.description }}
            </p>
            <br />
            <button style="color: blue; margin-right: 10px" @click="startEdit(planningModel)">Édit</button>
            <button style="color: red" @click="deletePlanning(planningModel)">
                Suppression
            </button>
        </div>
    </div>
</template>
