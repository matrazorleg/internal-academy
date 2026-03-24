<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    initialValues: {
        type: Object,
        default: () => ({
            title: '',
            description: '',
            starts_at: '',
            ends_at: '',
            capacity: 10,
        }),
    },
    action: {
        type: String,
        required: true,
    },
    method: {
        type: String,
        default: 'post',
    },
    submitLabel: {
        type: String,
        default: 'Save Workshop',
    },
});

const form = useForm({
    title: props.initialValues.title,
    description: props.initialValues.description,
    starts_at: props.initialValues.starts_at,
    ends_at: props.initialValues.ends_at,
    capacity: props.initialValues.capacity,
});

const submit = () => {
    form.submit(props.method, props.action);
};
</script>

<template>
    <form class="space-y-4" @submit.prevent="submit">
        <div>
            <label class="mb-1 block text-sm font-medium">Title</label>
            <input v-model="form.title" type="text" required class="w-full rounded border border-slate-300 px-3 py-2" />
            <p v-if="form.errors.title" class="mt-1 text-xs text-red-600">{{ form.errors.title }}</p>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium">Description</label>
            <textarea
                v-model="form.description"
                rows="4"
                required
                class="w-full rounded border border-slate-300 px-3 py-2"
            />
            <p v-if="form.errors.description" class="mt-1 text-xs text-red-600">{{ form.errors.description }}</p>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-medium">Start</label>
                <input
                    v-model="form.starts_at"
                    type="datetime-local"
                    required
                    class="w-full rounded border border-slate-300 px-3 py-2"
                />
                <p v-if="form.errors.starts_at" class="mt-1 text-xs text-red-600">{{ form.errors.starts_at }}</p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium">End</label>
                <input
                    v-model="form.ends_at"
                    type="datetime-local"
                    required
                    class="w-full rounded border border-slate-300 px-3 py-2"
                />
                <p v-if="form.errors.ends_at" class="mt-1 text-xs text-red-600">{{ form.errors.ends_at }}</p>
            </div>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium">Capacity</label>
            <input
                v-model="form.capacity"
                type="number"
                min="1"
                required
                class="w-full rounded border border-slate-300 px-3 py-2"
            />
            <p v-if="form.errors.capacity" class="mt-1 text-xs text-red-600">{{ form.errors.capacity }}</p>
        </div>

        <button
            type="submit"
            :disabled="form.processing"
            class="rounded bg-blue-700 px-4 py-2 text-sm font-medium text-white hover:bg-blue-800 disabled:opacity-60"
        >
            {{ submitLabel }}
        </button>
    </form>
</template>
