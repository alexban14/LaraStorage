<template>
    <MenuItem v-slot="{ active }">
        <a href="#" class="text-grey-700 block px-4 py-2 text-sm relative">
            Upload Files

            <input @change="onUpload" type="file" multiple class="absolute left-0 top-0 bottom-0 right-0 cursor-pointer opacity-0">
        </a>
    </MenuItem>
</template>

<script setup>
import { MenuItem } from '@headlessui/vue'
import { emitter, FILE_UPLOAD_STARTED } from '@/event-bus';

function onUpload(e) {
    emitter.emit(FILE_UPLOAD_STARTED, e.target.files);
}

</script>
