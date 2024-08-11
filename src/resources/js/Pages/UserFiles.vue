<template>
    <div class="flex flex-col h-full">
        <AuthenticatedLayout>
            <nav class="flex items-center justify-between p-1 mb-3">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li v-for="ancestor of ancestors.data" :key="ancestor.id" class="inline-flex items-center">
                        <Link v-if="!ancestor.parent_id" :href="route('user-files')"
                            class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                        <HomeIcon class="w-4 h-4 mr-2" />

                        My Files
                        </Link>
                        <Link v-else :href="route('user-files', { folderPath: ancestor.path })"
                            class="inline-flex items-center ml-1 text-sm font-medium text-grey-700 hover:text-blue-600 md:ml-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>

                        {{ ancestor.name }}
                        </Link>
                    </li>
                </ol>
                <div>
                    <DeleteFilesButton :delete-ids="selectedIds" :delete-all="allSelected" @delete="onDelete"/>
                </div>
            </nav>
            <pre>{{ selectedIds }}</pre>
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
                                Owner
                            </th>
                            <th class="text-sm font-medium text-gray-600 px-6 py-4 text-left" scope="col">
                                Last Modified
                            </th>
                            <th class="text-sm font-medium text-gray-600 px-6 py-4 text-left" scope="col">
                                Size
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="file in allFiles.data" :key="file.id"
                            @click="toggleFileSelected(file)"
                            @dblclick="openFolder(file)"
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
                                {{ file.owner }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ file.updated_at }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ file.size }}
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

const allSelected = ref(false);
const selected = ref({});
const loadMoreIntersect = ref(null);

const { files } = defineProps({
    files: Object,
    folder: Object,
    ancestors: Object,
})

const allFiles = ref({
    data: files.data,
    next: files.links.next
})

const selectedIds = computed(() => Object.entries(selected.value).filter(a => a[1]).map(a => a[0]));

function openFolder(file) {
    if (!file.is_folder) {
        return;
    }

    router.visit(route('user-files', {folderPath: file.path}));
}

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
