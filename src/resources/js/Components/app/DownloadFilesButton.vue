<template>
    <PrimaryButton @click="download">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 8.25H7.5a2.25 2.25 0 0 0-2.25 2.25v9a2.25 2.25 0 0 0 2.25 2.25h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25H15M9 12l3 3m0 0 3-3m-3 3V2.25" />
        </svg>
        Download
    </PrimaryButton>
</template>

<script setup>
import ConfirmationDialog from "@/Components/app/ConfirmationDialog.vue";
import {ref} from "vue";
import {useForm, usePage} from "@inertiajs/vue3";
import {showErrorDialog} from "@/event-bus.js";
import PrimaryButton from "@/Components/PrimaryButton.vue";

const page = usePage();
const downloadFileForm = useForm({
    all: false,
    ids: [],
    parent_id: null,
})

const props = defineProps({
    all: {
        type: Boolean,
        required: false,
        default: false
    },
    ids: {
        type: Array,
        default: []
    }
})

function download() {
    console.log("donwloading...");
}
</script>

<style scoped>

</style>
