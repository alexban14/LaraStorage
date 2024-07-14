<template>
    <div class="h-screen bg-grey-50 flex w-full gap-4">
        <NavigationMenu />

        <main class="flex flex-col flex-1 px-4 overflow-hidden">
            <div class="flex items-center justify-between w-full">
                <SearchForm />
                <UserSettingsDropdown />
            </div>
            <slot />
        </main>
    </div>
</template>

<script setup>
import NavigationMenu from '@/Components/app/NavigationMenu.vue';
import SearchForm from '@/Components/app/SearchForm.vue';
import UserSettingsDropdown from '@/Components/app/UserSettingsDropdown.vue';

</script>

