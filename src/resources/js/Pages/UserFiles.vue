<template>
    <div>
        <AuthenticatedLayout>
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
                    <tr
                        v-for="file in files.data"
                        :key="file.id"
                        class="bg-white border-b transition duration-300 ease-in-out hover:bg-gray-100"
                    >
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

const { files } = defineProps({
    files: Object
})

for (let file in files) {
    console.log(file);
}
</script>
