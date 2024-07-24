<template>
    <div>
        <AuthenticatedLayout>
            <nav class="flex items-center justify-between p-1 mb-3">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li v-for="ancestor of ancestors.data" :key="ancestor.id" class="inline-flex items-center">
                        <Link v-if="!ancestor.parent_id" :href="route('user-files')"
                            class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                            <HomeIcon class="w-4 h-4 mr-2"/>

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
            </nav>
            <table class="min-w-full">
                <thead class="bg-grey-100 border-b">
                    <tr>
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
                    <tr v-for="file in files.data" @click="openFolder(file)" :key="file.id"
                        class="bg-white border-b transition duration-300 ease-in-out hover:bg-gray-100">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
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
            <div v-if="!files.data.length" class="py-8 text-center text-lg text-grey-400">
                There is no data in the folder
            </div>
        </AuthenticatedLayout>
    </div>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { HomeIcon } from '@heroicons/vue/20/solid';
import { Link, router } from '@inertiajs/vue3';

const { files } = defineProps({
    files: Object,
    folder: Object,
    ancestors: Array,
})

function openFolder(file) {
    if (!file.is_folder) {
        return;
    }

    router.visit(route('user-files', {folderPath: file.path}));
}

for (let file in files) {
    console.log(file);
}
</script>
