<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';
import AppLayout from '../../../Layouts/AppLayout.vue';

const props = defineProps({
    workshops: {
        type: Array,
        required: true,
    },
    mostPopularWorkshop: {
        type: Object,
        default: null,
    },
    createUrl: {
        type: String,
        required: true,
    },
    statsUrl: {
        type: String,
        required: true,
    },
});

const localWorkshops = ref([...props.workshops]);
const localMostPopular = ref(props.mostPopularWorkshop);

const formatDate = (date) =>
    new Date(date).toLocaleString([], {
        dateStyle: 'medium',
        timeStyle: 'short',
    });

const removeWorkshop = (url) => {
    if (!window.confirm('Delete this workshop?')) {
        return;
    }

    router.delete(url);
};

let timer = null;

const pollStats = async () => {
    const { data } = await window.axios.get(props.statsUrl);
    const counters = new Map(data.workshops.map((workshop) => [workshop.id, workshop]));

    localWorkshops.value = localWorkshops.value.map((workshop) => {
        const updated = counters.get(workshop.id);

        if (!updated) {
            return workshop;
        }

        return {
            ...workshop,
            confirmed_count: updated.confirmed_count,
            waiting_count: updated.waiting_count,
        };
    });

    localMostPopular.value = data.mostPopularWorkshop;
};

onMounted(() => {
    timer = window.setInterval(pollStats, 5000);
});

onUnmounted(() => {
    if (timer) {
        window.clearInterval(timer);
    }
});
</script>

<template>
    <Head title="Admin Workshops" />

    <AppLayout>
        <div class="mb-6 flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold">Workshop Management</h1>
                <p class="text-sm text-slate-600">Create, update, and monitor workshop participation in real time.</p>
            </div>
            <Link :href="createUrl" class="rounded bg-blue-700 px-3 py-2 text-sm font-medium text-white hover:bg-blue-800">
                New Workshop
            </Link>
        </div>

        <section class="mb-6 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <h2 class="mb-2 text-sm font-semibold uppercase tracking-wide text-slate-500">Most Popular Workshop</h2>
            <p v-if="localMostPopular" class="text-base">
                <strong>{{ localMostPopular.title }}</strong>
                <span class="text-slate-600">({{ localMostPopular.confirmed_count }} confirmed)</span>
            </p>
            <p v-else class="text-sm text-slate-600">No registrations yet.</p>
        </section>

        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Title</th>
                        <th class="px-4 py-3 font-semibold">Schedule</th>
                        <th class="px-4 py-3 font-semibold">Seats</th>
                        <th class="px-4 py-3 font-semibold">Waiting</th>
                        <th class="px-4 py-3 font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="workshop in localWorkshops" :key="workshop.id">
                        <td class="px-4 py-3">
                            <p class="font-medium">{{ workshop.title }}</p>
                            <p class="text-xs text-slate-600">{{ workshop.description }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <p>{{ formatDate(workshop.starts_at) }}</p>
                            <p class="text-xs text-slate-600">{{ formatDate(workshop.ends_at) }}</p>
                        </td>
                        <td class="px-4 py-3">{{ workshop.confirmed_count }} / {{ workshop.capacity }}</td>
                        <td class="px-4 py-3">{{ workshop.waiting_count }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <Link :href="workshop.edit_url" class="text-blue-700 hover:underline">
                                    Edit
                                </Link>
                                <button class="text-red-700 hover:underline" @click="removeWorkshop(workshop.destroy_url)">Delete</button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="localWorkshops.length === 0">
                        <td colspan="5" class="px-4 py-6 text-center text-slate-600">No workshops found.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>
