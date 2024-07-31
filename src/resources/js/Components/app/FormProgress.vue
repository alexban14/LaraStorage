<template>
    <div v-if="form.progress" class="absolute p-2 border-grey-900 border right-8 bottom-4 bg-white shadow-lg rounded flex flex-col w-[240px]">
        <div class="text-sm mb-2">
            Uploading {{  form.files.length }} files
        </div>
        <div class="h-3 bg-grey-100 rounded-md">
            <div class="h-full bg-grey-900 rounded-md transition-all" :style="{width: form.progress.percentage+'%'}">

            </div>
        </div>
    </div>
</template>

<script setup>

const props = defineProps({
    form: Object
});

</script>
