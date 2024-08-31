<template>
    <div class="flex flex-col h-full">
        <AuthenticatedLayout>
            <nav class="flex items-center justify-end p-1 mb-3">
                <div>
                    <DownloadFilesButton
                        :all="allSelected"
                        :ids="selectedIds"
                        :shared-type="sharedType"
                        download-route='/file/download/shared'
                    />
                </div>
            </nav>
            <div class="flex-1 overflow-auto">
                <table class="min-w-full">
                    <thead class="bg-grey-100 border-b">
                        <tr>
                            <th class="text-sm font-medium text-gray-600 px-6 py-4 text-left" scope="col">
                                <Checkbox @change="onSelectAllChange" v-model:checked="allSelected" />
                            </th>
                            <th class="text-sm font-medium text-gray-600 px-6 py-4 text-left" scope="col">
                                Name
                            </th>
                            <th class="text-sm font-medium text-gray-600 px-6 py-4 text-left" scope="col">
                                Path
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="file in allFiles.data" :key="file.id"
                            @click="toggleFileSelected(file)"
                            class="border-b transition duration-300 ease-in-out hover:bg-blue-100"
                            :class="(selected[file.id] || allSelected) ? 'bg-blue-50' : 'bg-white'">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <Checkbox @change="$event => onSelectCheckboxChange(file)" v-model="selected[file.id]" :checked="selected[file.id] || allSelected" />
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 flex items-center">
                                <FileIcon :file="file" />
                                {{ file.name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ file.path }}
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div v-if="!allFiles.data.length" class="py-8 text-center text-lg text-grey-400">
                    There is no data in the folder
                </div>
                <div ref="loadMoreIntersect" >

                </div>
            </div>
        </AuthenticatedLayout>
    </div>
</template>

<script setup>
import FileIcon from '@/Components/app/FileIcon.vue';
import { httpGet } from '@/Helpers/http-helper';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { HomeIcon } from '@heroicons/vue/20/solid';
import { Link, router } from '@inertiajs/vue3';
import {computed, onMounted, onUpdated, registerRuntimeCompiler} from 'vue';
import { ref } from 'vue';
import Checkbox from "@/Components/Checkbox.vue";
import DeleteFilesButton from "@/Components/app/DeleteFilesButton.vue";
import DownloadFilesButton from "@/Components/app/DownloadFilesButton.vue";
import RestoreFilesButton from "@/Components/app/RestoreFilesButton.vue";
import DeleteForeverButton from "@/Components/app/DeleteForeverButton.vue";

const allSelected = ref(false);
const selected = ref({});
const loadMoreIntersect = ref(null);

const props = defineProps({
    files: Object,
    folder: Object,
    ancestors: Object,
    sharedType: String,
})

const allFiles = ref({
    data: props.files.data,
    next: props.files.links.next
})

const selectedIds = computed(() => Object.entries(selected.value).filter(a => a[1]).map(a => a[0]));

function loadMore() {
    console.log('load more');
    console.log(allFiles.value.next);

    if (allFiles.value.next === null) {
        return;
    }

    httpGet(allFiles.value.next)
        .then(response => {
            allFiles.value.data = [...allFiles.value.data, ...response.data];
            allFiles.value.next = response.links.next;
        });
}

function onSelectAllChange() {
    allFiles.value.data.forEach((file) => {
        selected.value[file.id] = allSelected.value;
    })
}

function toggleFileSelected(file) {
    selected.value[file.id] = !selected.value[file.id];
    onSelectCheckboxChange(file);
}

function onSelectCheckboxChange(file) {
    if (!selected.value[file.id]) {
        allSelected.value = false;
    } else {
        let checked = true;

        for (let file of allFiles.value.data) {
            if (!selected.value[file.id]) {
                checked = false;
                break;
            }
        }

        allSelected.value = checked;
    }
}

function onDelete() {
    allSelected.value = false;
    selected.value = {};
}

onMounted(() => {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => entry.isIntersecting && loadMore());
    }, {
        rootMargin: '250px 0px 0px 0px'
    });

    observer.observe(loadMoreIntersect.value);
});
</script>
