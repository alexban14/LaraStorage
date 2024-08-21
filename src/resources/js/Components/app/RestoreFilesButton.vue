<template>
    <button @click="onRestoreClick"
            class="inline-flex items-center px-4 py-2 mr-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 mr-2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15m-3 0-3-3m0 0 3-3m-3 3H15" />
        </svg>
        Restore
    </button>

    <ConfirmationDialog :show="showDeleteDialog"
                        message="Are you sure you want to restore the selected files?"
                        @cancel="onRestoreCancel"
                        @confirm="onRestoreConfirm"
    />
</template>

<script setup>


import ConfirmationDialog from "@/Components/app/ConfirmationDialog.vue";
import {ref} from "vue";
import {useForm, usePage} from "@inertiajs/vue3";
import {showErrorDialog, showSuccessNotification} from "@/event-bus.js";

const page = usePage();
const restoreFilesForm = useForm({
    all: false,
    ids: [],
    parent_id: null,
})

const showDeleteDialog = ref(false);

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

const emit = defineEmits(['restore']);
function onRestoreClick() {
    if (!props.allSelected && !props.selectedIds.length) {
        showErrorDialog("Please select files to delete");
        return;
    }
    showDeleteDialog.value = true;
}

function onRestoreCancel() {
    showDeleteDialog.value = false;
}

function onRestoreConfirm() {
    // restoreFilesForm.parent_id = page.props.folder.id;
    if (props.allSelected) {
        restoreFilesForm.all = true;
    } else {
        restoreFilesForm.ids = props.selectedIds;
    }

    restoreFilesForm.post(route('file.restore'), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteDialog.value = false;
            emit('restore');
            showSuccessNotification('Files restored successfully');
        }
    });

    console.log("Restore ", restoreFilesForm.data());
}

</script>

<style scoped>

</style>
