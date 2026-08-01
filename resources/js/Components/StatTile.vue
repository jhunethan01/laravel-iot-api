<script setup>
defineProps({
    label: { type: String, required: true },
    value: { type: [String, Number], required: true },
    accent: { type: String, default: 'var(--text-muted)' },
});
</script>

<template>
    <div class="stat-tile">
        <div class="stat-tile__header">
            <span class="stat-tile__dot" :style="{ backgroundColor: accent }" />
            <span class="stat-tile__label">{{ label }}</span>
        </div>
        <div class="stat-tile__value">{{ value }}</div>
    </div>
</template>

<style scoped>
.stat-tile {
    background: var(--surface-1);
    border: 1px solid var(--gridline);
    border-radius: 12px;
    padding: 1.25rem 1.5rem;
}
.stat-tile__header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}
.stat-tile__label {
    font-size: 0.8125rem;
    color: var(--text-secondary);
}
.stat-tile__value {
    font-size: 2rem;
    font-weight: 600;
    color: var(--text-primary);
    line-height: 1;
}
.stat-tile__dot {
    width: 8px;
    height: 8px;
    border-radius: 9999px;
    flex-shrink: 0;
}
</style>
