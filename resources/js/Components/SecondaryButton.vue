<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button
        :type="type"
        class="inline-flex items-center rounded-xl border border-border bg-surface px-4 py-2 text-xs font-semibold uppercase tracking-widest text-text-main shadow-xs hover:bg-surface-muted focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 disabled:opacity-25 tactile-press cursor-pointer"
    >
        <slot />
    </button>
</template>
