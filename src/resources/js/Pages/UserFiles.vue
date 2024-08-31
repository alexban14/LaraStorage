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
                <div class="flex">
                    <label class="flex items-center mr-2">
                        Only Favorites
                        <Checkbox @change="showOnlyFavorites" v-model:checked="onlyFavorites" class="ml-2"/>
                    </label>
                    <ShareFilesButton
                        :all-selected="allSelected"
                        :selected-ids="selectedIds"
                        class="mr-2" />
                    <DownloadFilesButton
                        :all="allSelected"
                        :ids="selectedIds"
                        class="mr-2"
                        download-route="/file/download" />
                    <DeleteFilesButton
                        :delete-ids="selectedIds"
                        :delete-all="allSelected"
                        @delete="onDelete"/>
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
                            <td class="px-3 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <Checkbox @change="$event => onSelectCheckboxChange(file)" v-model="selected[file.id]" :checked="selected[file.id] || allSelected" />
                            </td>
                            <td class="px-3 py-4 max-w-[30px] text-sm font-medium text-yellow-500">
                                <button @click="toggleFavorite(file.id)">
                                    <svg v-if="!file.is_favorite" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                                    </svg>
                                    <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                                        <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z" clip-rule="evenodd" />
                                    </svg>
                                </button>
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
import DownloadFilesButton from "@/Components/app/DownloadFilesButton.vue";
import { showSuccessNotification, showErrorNotification } from "@/event-bus.js";
import ShareFilesButton from "@/Components/app/ShareFilesButton.vue";

const allSelected = ref(false);
const selected = ref({});
const loadMoreIntersect = ref(null);
const onlyFavorites = ref(false);

const props = defineProps({
    files: Object,
    folder: Object,
    ancestors: Object,
})

let params = null;

const allFiles = ref({
    data: props.files.data,
    next: props.files.links.next
})

const emit = defineEmits(['marked-favorite']);

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

function toggleFavorite(fileId) {
    axios.get(`/file/${fileId}/mark-favorite`)
        .then(() => {
            emit('marked-favorite');
            showSuccessNotification('Selected file have been marked / unmarked as favorites');
        })
        .catch(() => {
            showErrorNotification('Could not mark / unmark file as favorite');
        })
}

function showOnlyFavorites() {
    if (onlyFavorites.value) {
        params.set('favorites', 1);
    } else {
        params.delete('favorites');
    }

    router.get(window.location.pathname + '?' + params.toString());
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
    params = new URLSearchParams(window.location.search);
    onlyFavorites.value = params.get('favorites') === '1';

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => entry.isIntersecting && loadMore());
    }, {
        rootMargin: '250px 0px 0px 0px'
    });

    observer.observe(loadMoreIntersect.value);
});
</script>
