<template>
    <nav class="min-w-[200px]">
        <div class="h-[80px] px-3 flex items-center gap-3">
            <Link href="/">
            <ApplicationLogo class="block h-9 w-auto fill-current text-gray-800" />
            </Link>
            Lara Storage
        </div>
        <div class="px-3">
            <CreateNewDropdown />

            <div class="py-3">
                <NavLink
                    :href="route('user-files')"
                    :active="$page.url == '/user-files'">
                    My Files
                </NavLink>
                <NavLink
                    :href="route('file.shared-with-me')"
                    :active="$page.url == '/file/shared-with-me'">
                    Shared with me
                </NavLink>
                <NavLink
                    :href="route('file.shared-by-me')"
                    :active="$page.url == '/file/shared-by-me'">
                    Shared by me
                </NavLink>
                <NavLink
                    href="/trash"
                    :active="$page.url == '/trash'">
                    Trash
                </NavLink>
            </div>
        </div>
    </nav>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import CreateNewDropdown from '@/Components/app/CreateNewDropdown.vue';
import NavLink from '@/Components/NavLink.vue';


</script>

