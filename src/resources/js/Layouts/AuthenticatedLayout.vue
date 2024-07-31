<template>
    <div class="h-screen bg-grey-50 flex w-full gap-4">
        <NavigationMenu />

        <main
            @drop.prevent="handleDrop"
            @dragover.prevent="onDragOver"
            @dragleave.prevent="onDragLeave"
            class="flex flex-col flex-1 px-4 overflow-hidden"
            :class="dragOver ? 'dropzone' :  '' ">

            <template v-if="dragOver" class="text-grey-500 text-center py-8 text-sm">
                Drop files here to upload
            </template>
            <template v-else>
                <div class="flex items-center justify-between w-full">
                    <SearchForm />
                    <UserSettingsDropdown />
                </div>
                <div class="flex-1 flex-col overflow-hidden">
                    <slot />
                </div>
            </template>
        </main>
    </div>
</template>

<script setup>
import NavigationMenu from '@/Components/app/NavigationMenu.vue';
import SearchForm from '@/Components/app/SearchForm.vue';
import UserSettingsDropdown from '@/Components/app/UserSettingsDropdown.vue';
import { emitter, FILE_UPLOAD_STARTED } from '@/event-bus';
import { useForm, usePage } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

const page = usePage();

const fileUploadForm = useForm({
    files: [],
    relative_paths: [],
    parent_id: null
});

const dragOver = ref(false);

function onDragOver(e) {
    dragOver.value = true;
}

function onDragLeave(e) {
    dragOver.value = false;
}


function handleDrop(e) {
    dragOver.value = false;
    const files = e.dataTransfer.files;

    uploadFiles(files);

    if (!files.length) {
        return;
    }


}

function uploadFiles(files) {
    fileUploadForm.parent_id = page.props.folder.data.id;
    fileUploadForm.files = files;
    fileUploadForm.relative_paths = [...files].map(file => file.webkitRelativePath);

    fileUploadForm.post(route('file.store'));
}

onMounted(() => {
    emitter.on(FILE_UPLOAD_STARTED, uploadFiles);
})

</script>

<style scoped>
.dropzone {
    width: 100%;
    height: 100%;
    color: #8d8d8d;
    border: 2px dashed gray;
    display: flex;
    justify-content: center;
    align-items: center;
}
</style>
