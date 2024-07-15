<template>
    <div>
        <AuthenticatedLayout>
            {{ $page.url }}
        </AuthenticatedLayout>
    </div>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
</script>
