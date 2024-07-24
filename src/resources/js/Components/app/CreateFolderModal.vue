<template>
    <modal :show="modelValue" @show="onShow" >
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                Create New Folder
            </h2>
            <div class="mt-6">
                <InputLabel for="folderName" value="Folder Name" class="sr-only" />
                <TextInput
                    id="folderName" v-model="form.name"
                    type="text"
                    ref="folderNameInput"
                    class="mt-1 block w-full"
                    :class="form.errors.name ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : '' "
                    placeholder="Folder Name"
                    @keyup.enter="createFolder"
                />
                <InputError :message="form.errors.name" class="mt-2" />
            </div>
            <div class="mt-6 flex justify-end">
                <SecondaryButton @click="closeModal"> Cancel </SecondaryButton>
                <PrimaryButton @click="createFolder" class="ml-3" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
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

const form = useForm({
    name: '',
    parent_id: null
});

const page = usePage();

const folderNameInput = ref(null);

const {modelValue} = defineProps({
    modelValue: {
        type: Boolean
    }
})

const emit = defineEmits(['update:modelValue']);

function onShow() {
    nextTick(() => folderNameInput.value.focus());
}

function createFolder() {
    form.parent_id = page.props.folder.id;
    form.post(route('folder.store'), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            form.reset();
        },
        onError: () => {
            if (form.errors.name) {
                form.reset('name');
                nextTick(() => folderNameInput.value.focus());
            }
        }
    })
}

function closeModal() {
    emit('update:modelValue');
    form.clearErrors();
    form.reset();
}

</script>
