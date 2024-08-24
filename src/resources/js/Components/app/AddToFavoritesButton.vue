<template>
    <button @click="onStarClick"
            class="inline-flex items-center px-4 py-2 mr-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 mr-2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
        </svg>
        Add to favorites
    </button>
</template>

<script setup>
import {ref} from "vue";
import {useForm, usePage} from "@inertiajs/vue3";
import {showErrorDialog, showSuccessNotification} from "@/event-bus.js";

const page = usePage();
const favoritesFilesForm = useForm({
    all: false,
    ids: [],
    parent_id: null,
})

const props = defineProps({
    allSelected: {
        type: Boolean,
        required: false,
        default: false
    },
    selectedIds: {
        type: Array,
        default: []
    }
})

const emit = defineEmits(['marked-favorite']);

function onStarClick() {
    if (!props.allSelected && !props.selectedIds.length) {
        showErrorDialog("Please select files to be marked as favorites");
        return;
    }

    favoritesFilesForm.parent_id = page.props.folder.id;
    if (props.allSelected) {
        favoritesFilesForm.all = true;
    } else {
        favoritesFilesForm.ids = props.selectedIds;
    }

    const favoritesFilesForm = useForm({
        all: false,
        ids: [],
        parent_id: null,
    })
    favoritesFilesForm.post(route('file.add-to-favorites'), {
        preserveScroll: true,
        onSuccess: () => {
            emit('marked-favorite');
            showSuccessNotification('Selected files have been added to favorites');
        }
    });
}

</script>

<style scoped>

</style>
