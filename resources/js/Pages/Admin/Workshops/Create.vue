<script setup>
import { Head, Link } from '@inertiajs/vue3';
import WorkshopForm from '../../../Components/WorkshopForm.vue';
import AppLayout from '../../../Layouts/AppLayout.vue';

defineProps({
    indexUrl: {
        type: String,
        required: true,
    },
    storeUrl: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <Head title="Create Workshop" />

    <AppLayout>
        <div class="mb-4 flex items-center justify-between">
            <h1 class="text-2xl font-semibold">Create Workshop</h1>
            <Link :href="indexUrl" class="text-sm text-blue-700 hover:underline">Back to list</Link>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <WorkshopForm :action="storeUrl" method="post" submit-label="Create Workshop" />
        </div>
    </AppLayout>
</template>
