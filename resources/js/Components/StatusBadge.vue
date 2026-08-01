<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: { type: String, required: true },
});

const STATUS_META = {
    online: { label: 'Online', color: 'var(--status-good)' },
    offline: { label: 'Offline', color: 'var(--text-muted)' },
    good: { label: 'Good', color: 'var(--status-good)' },
    warning: { label: 'Warning', color: 'var(--status-warning)' },
    serious: { label: 'Serious', color: 'var(--status-serious)' },
    critical: { label: 'Critical', color: 'var(--status-critical)' },
};

const meta = computed(() => STATUS_META[props.status] ?? STATUS_META.offline);
</script>

<template>
    <span class="status-badge">
        <span class="status-badge__dot" :style="{ backgroundColor: meta.color }" />
        {{ meta.label }}
    </span>
</template>

<style scoped>
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.8125rem;
    color: var(--text-secondary);
    white-space: nowrap;
}
.status-badge__dot {
    width: 8px;
    height: 8px;
    border-radius: 9999px;
    flex-shrink: 0;
}
</style>
