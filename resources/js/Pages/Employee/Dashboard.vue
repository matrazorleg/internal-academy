<script setup>
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

defineProps({
    workshops: {
        type: Array,
        required: true,
    },
});

const formatDate = (date) =>
    new Date(date).toLocaleString([], {
        dateStyle: 'medium',
        timeStyle: 'short',
    });

const register = (url) => {
    router.post(url);
};

const cancel = (url) => {
    router.delete(url);
};
</script>

<template>
    <Head title="Employee Dashboard" />

    <AppLayout>
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold">Upcoming Workshops</h1>
                <p class="text-sm text-slate-600">Enroll with one click. If full, join the waiting list.</p>
            </div>
        </div>

        <div v-if="workshops.length === 0" class="rounded border border-dashed border-slate-300 bg-white p-6 text-sm text-slate-600">
            No upcoming workshops available.
        </div>

        <div v-else class="space-y-4">
            <article
                v-for="workshop in workshops"
                :key="workshop.id"
                class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm"
            >
                <div class="mb-2 flex items-start justify-between gap-6">
                    <div>
                        <h2 class="text-lg font-semibold">{{ workshop.title }}</h2>
                        <p class="text-sm text-slate-600">{{ workshop.description }}</p>
                    </div>
                    <span
                        v-if="workshop.user_registration_status === 'confirmed'"
                        class="rounded-full bg-emerald-100 px-2 py-1 text-xs font-medium text-emerald-800"
                    >
                        Confirmed
                    </span>
                    <span
                        v-else-if="workshop.user_registration_status === 'waiting'"
                        class="rounded-full bg-amber-100 px-2 py-1 text-xs font-medium text-amber-800"
                    >
                        Waiting List
                    </span>
                </div>

                <div class="mb-3 grid gap-2 text-sm text-slate-700 md:grid-cols-2">
                    <p><strong>Start:</strong> {{ formatDate(workshop.starts_at) }}</p>
                    <p><strong>End:</strong> {{ formatDate(workshop.ends_at) }}</p>
                    <p><strong>Seats:</strong> {{ workshop.confirmed_count }} / {{ workshop.capacity }}</p>
                    <p><strong>Waiting:</strong> {{ workshop.waiting_count }}</p>
                </div>

                <div class="flex items-center justify-between">
                    <p class="text-sm text-slate-600">
                        {{ workshop.available_seats }} seats left
                    </p>

                    <div>
                        <button
                            v-if="!workshop.user_registration_status"
                            class="rounded bg-blue-700 px-3 py-1.5 text-sm font-medium text-white hover:bg-blue-800"
                            @click="register(workshop.register_url)"
                        >
                            Register
                        </button>
                        <button
                            v-else
                            class="rounded border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium hover:bg-slate-100"
                            @click="cancel(workshop.cancel_url)"
                        >
                            Cancel Registration
                        </button>
                    </div>
                </div>
            </article>
        </div>
    </AppLayout>
</template>
