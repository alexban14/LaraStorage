<template>
    <button @click="onDeleteClick"
            class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
             class="w-4 h-4 mr-2">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
        </svg>
        Delete Forever
    </button>

    <ConfirmationDialog :show="showDeleteDialog"
                        message="Are you sure you want to delete the selected files?"
                        @cancel="onDeleteCancel"
                        @confirm="onDeleteConfirm"
    />
</template>

<script setup>


import ConfirmationDialog from "@/Components/app/ConfirmationDialog.vue";
import {ref} from "vue";
import {useForm, usePage} from "@inertiajs/vue3";
import {showErrorDialog, showSuccessNotification} from "@/event-bus.js";

const page = usePage();
const deleteFilesForm = useForm({
    all: false,
    ids: [],
    parent_id: null,
})

const showDeleteDialog = ref(false);

const props = defineProps({
    deleteAll: {
        type: Boolean,
        required: false,
        default: false
    },
    deleteIds: {
        type: Array,
        default: []
    }
})

const emit = defineEmits(['delete']);
function onDeleteClick() {
    if (!props.deleteAll && !props.deleteIds.length) {
        showErrorDialog("Please select files to delete");
        return;
    }
    showDeleteDialog.value = true;
}

function onDeleteCancel() {
    showDeleteDialog.value = false;
}

function onDeleteConfirm() {
    if (props.deleteAll) {
        deleteFilesForm.all = true;
    } else {
        deleteFilesForm.ids = props.deleteIds;
    }

    deleteFilesForm.delete(route('file.delete-forever'), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteDialog.value = false;
            emit('delete');
            showSuccessNotification('Files deleted forever!');
        }
    });
}

</script>

<style scoped>

</style>
