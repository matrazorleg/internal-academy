<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();

const user = computed(() => page.props.auth?.user ?? null);
const urls = computed(() => page.props.urls ?? {});
const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);
</script>

<template>
    <div class="min-h-screen bg-slate-50 text-slate-900">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">
                <div class="flex items-center gap-6">
                    <span class="text-lg font-semibold">Internal Academy</span>

                    <nav v-if="user" class="flex items-center gap-3 text-sm">
                        <Link
                            v-if="user.role === 'employee'"
                            :href="urls.dashboard"
                            class="rounded px-3 py-1 hover:bg-slate-100"
                        >
                            Workshops
                        </Link>
                        <Link
                            v-if="user.role === 'admin'"
                            :href="urls.adminWorkshopsIndex"
                            class="rounded px-3 py-1 hover:bg-slate-100"
                        >
                            Admin
                        </Link>
                    </nav>
                </div>

                <div class="flex items-center gap-4 text-sm">
                    <span v-if="user" class="text-slate-600">{{ user.name }} ({{ user.role }})</span>
                    <Link
                        v-if="user"
                        :href="urls.logout"
                        method="post"
                        as="button"
                        class="rounded bg-slate-900 px-3 py-1.5 font-medium text-white"
                    >
                        Logout
                    </Link>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-6xl p-4">
            <div v-if="flashSuccess" class="mb-4 rounded border border-emerald-300 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">
                {{ flashSuccess }}
            </div>
            <div v-if="flashError" class="mb-4 rounded border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-800">
                {{ flashError }}
            </div>
            <slot />
        </main>
    </div>
</template>
