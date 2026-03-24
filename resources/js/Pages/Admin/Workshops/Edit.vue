<script setup>
import { Head, Link } from '@inertiajs/vue3';
import WorkshopForm from '../../../Components/WorkshopForm.vue';
import AppLayout from '../../../Layouts/AppLayout.vue';

defineProps({
    workshop: {
        type: Object,
        required: true,
    },
    indexUrl: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <Head title="Edit Workshop" />

    <AppLayout>
        <div class="mb-4 flex items-center justify-between">
            <h1 class="text-2xl font-semibold">Edit Workshop</h1>
            <Link :href="indexUrl" class="text-sm text-blue-700 hover:underline">Back to list</Link>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <WorkshopForm
                :initial-values="workshop"
                :action="workshop.update_url"
                method="put"
                submit-label="Update Workshop"
            />
        </div>
    </AppLayout>
</template>
