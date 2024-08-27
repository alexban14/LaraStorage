<template>
    <modal :show="props.modalValue" >
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                Share Files
            </h2>
            <div class="mt-6">
                <InputLabel for="folderName" value="Folder Name" class="sr-only" />
                <TextInput
                    id="shareEmail" v-model="form.email"
                    type="text"
                    ref="emailInput"
                    class="mt-1 block w-full"
                    :class="form.errors.email ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : '' "
                    placeholder="Enter email address"
                    @keyup.enter="shareFiles"
                />
                <InputError :message="form.errors.email" class="mt-2" />
            </div>
            <div class="mt-6 flex justify-end">
                <SecondaryButton @click="closeModal"> Cancel </SecondaryButton>
                <PrimaryButton @click="shareFiles" class="ml-3" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Submit
                </PrimaryButton>
            </div>
        </div>
    </modal>
</template>

<script setup>
import Modal from '@/Components/Modal.vue';
import TextInput from '../TextInput.vue';
import InputLabel from '../InputLabel.vue';
import InputError from '../InputError.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import SecondaryButton from '../SecondaryButton.vue';
import { nextTick, ref } from 'vue';
import PrimaryButton from '../PrimaryButton.vue';
import {showSuccessNotification} from "@/event-bus.js";

const form = useForm({
    email: null,
    all: false,
    ids: [],
    parent_id: null,
});

const page = usePage();

const emailInput = ref(null);

const props = defineProps({
    modalValue: {
        type: Boolean,
        default: false
    },
    allSelected: Boolean,
    selectedIds: Array
});

const emit = defineEmits(['update:modelValue']);

function shareFiles() {
    form.parent_id = page.props.parent_id;

    if (props.allSelected) {
        form.all = true;
        form.ids = [];
    } else {
        form.ids = props.selectedIds;
    }

    form.post(route('file.share'), {
        preserveScroll: true,
        onSuccess: () => {
            showSuccessNotification('Selected files have been shared to ' + form.email);
            closeModal();
            emit('files-shared');
        }
    });
}

function closeModal() {
    props.modalValue = false;
    emit('closeModal');
    form.clearErrors();
    form.reset();
}

</script>
