<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
    points: { type: Array, default: () => [] },
});

const width = 640;
const height = 220;
const padding = { top: 16, right: 16, bottom: 28, left: 40 };
const plotWidth = width - padding.left - padding.right;
const plotHeight = height - padding.top - padding.bottom;

const values = computed(() => props.points.map((p) => p.avg_temperature));
const minValue = computed(() => (values.value.length ? Math.min(...values.value) : 0));
const maxValue = computed(() => (values.value.length ? Math.max(...values.value) : 1));
const valueSpan = computed(() => Math.max(maxValue.value - minValue.value, 1));
const paddedMin = computed(() => minValue.value - valueSpan.value * 0.15);
const paddedSpan = computed(() => Math.max(valueSpan.value * 1.3, 1));

function xFor(index) {
    if (props.points.length <= 1) return padding.left + plotWidth / 2;
    return padding.left + (index / (props.points.length - 1)) * plotWidth;
}

function yFor(value) {
    return padding.top + plotHeight - ((value - paddedMin.value) / paddedSpan.value) * plotHeight;
}

const linePath = computed(() =>
    props.points
        .map((p, i) => `${i === 0 ? 'M' : 'L'} ${xFor(i).toFixed(1)} ${yFor(p.avg_temperature).toFixed(1)}`)
        .join(' ')
);

const areaPath = computed(() => {
    if (!props.points.length) return '';
    const baseline = padding.top + plotHeight;
    const first = `M ${xFor(0).toFixed(1)} ${baseline}`;
    const line = props.points
        .map((p, i) => `L ${xFor(i).toFixed(1)} ${yFor(p.avg_temperature).toFixed(1)}`)
        .join(' ');
    const last = `L ${xFor(props.points.length - 1).toFixed(1)} ${baseline} Z`;
    return `${first} ${line} ${last}`;
});

const gridLines = computed(() => {
    const steps = 4;
    return Array.from({ length: steps + 1 }, (_, i) => {
        const value = paddedMin.value + (paddedSpan.value * i) / steps;
        return { y: yFor(value), value: Math.round(value * 10) / 10 };
    });
});

function formatBucket(bucket) {
    return new Date(bucket.replace(' ', 'T')).toLocaleString(undefined, {
        hour: 'numeric',
        minute: '2-digit',
    });
}

const hoveredIndex = ref(null);
const svgEl = ref(null);

function handlePointerMove(event) {
    if (!props.points.length || !svgEl.value) return;
    const rect = svgEl.value.getBoundingClientRect();
    const relativeX = ((event.clientX - rect.left) / rect.width) * width;
    let nearest = 0;
    let nearestDistance = Infinity;
    props.points.forEach((_, i) => {
        const distance = Math.abs(xFor(i) - relativeX);
        if (distance < nearestDistance) {
            nearestDistance = distance;
            nearest = i;
        }
    });
    hoveredIndex.value = nearest;
}

function handlePointerLeave() {
    hoveredIndex.value = null;
}

const hoveredPoint = computed(() => (hoveredIndex.value !== null ? props.points[hoveredIndex.value] : null));
const lastPoint = computed(() => props.points[props.points.length - 1] ?? null);
</script>

<template>
    <div class="telemetry-chart">
        <div v-if="!points.length" class="telemetry-chart__empty">
            No telemetry recorded in the last 24 hours.
        </div>
        <template v-else>
            <svg
                ref="svgEl"
                :viewBox="`0 0 ${width} ${height}`"
                class="telemetry-chart__svg"
                @pointermove="handlePointerMove"
                @pointerleave="handlePointerLeave"
            >
                <line
                    v-for="line in gridLines"
                    :key="line.y"
                    :x1="padding.left"
                    :x2="width - padding.right"
                    :y1="line.y"
                    :y2="line.y"
                    class="gridline"
                />
                <text
                    v-for="line in gridLines"
                    :key="`label-${line.y}`"
                    :x="padding.left - 8"
                    :y="line.y + 3"
                    class="axis-label"
                    text-anchor="end"
                >{{ line.value }}&#176;</text>

                <path :d="areaPath" class="area" />
                <path :d="linePath" class="line" />

                <g v-if="lastPoint">
                    <circle
                        :cx="xFor(points.length - 1)"
                        :cy="yFor(lastPoint.avg_temperature)"
                        r="4"
                        class="end-marker"
                    />
                    <text
                        :x="xFor(points.length - 1) - 8"
                        :y="yFor(lastPoint.avg_temperature) - 10"
                        class="end-label"
                        text-anchor="end"
                    >{{ lastPoint.avg_temperature }}&#176;</text>
                </g>

                <g v-if="hoveredPoint">
                    <line
                        :x1="xFor(hoveredIndex)"
                        :x2="xFor(hoveredIndex)"
                        :y1="padding.top"
                        :y2="padding.top + plotHeight"
                        class="crosshair"
                    />
                    <circle
                        :cx="xFor(hoveredIndex)"
                        :cy="yFor(hoveredPoint.avg_temperature)"
                        r="4"
                        class="hover-marker"
                    />
                </g>
            </svg>

            <div
                v-if="hoveredPoint"
                class="telemetry-chart__tooltip"
                :style="{ left: `${(xFor(hoveredIndex) / width) * 100}%` }"
            >
                <div class="telemetry-chart__tooltip-value">{{ hoveredPoint.avg_temperature }}&#176;C</div>
                <div class="telemetry-chart__tooltip-label">{{ formatBucket(hoveredPoint.bucket) }}</div>
            </div>
        </template>
    </div>
</template>

<style scoped>
.telemetry-chart {
    position: relative;
}
.telemetry-chart__svg {
    width: 100%;
    height: auto;
    display: block;
}
.telemetry-chart__empty {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 220px;
    color: var(--text-muted);
    font-size: 0.875rem;
}
.gridline {
    stroke: var(--gridline);
    stroke-width: 1;
}
.axis-label {
    fill: var(--text-muted);
    font-size: 10px;
}
.area {
    fill: var(--series-1);
    opacity: 0.1;
    stroke: none;
}
.line {
    fill: none;
    stroke: var(--series-1);
    stroke-width: 2;
    stroke-linejoin: round;
    stroke-linecap: round;
}
.end-marker {
    fill: var(--series-1);
    stroke: var(--surface-1);
    stroke-width: 2;
}
.end-label {
    fill: var(--text-primary);
    font-size: 12px;
    font-weight: 600;
}
.crosshair {
    stroke: var(--baseline);
    stroke-width: 1;
}
.hover-marker {
    fill: var(--series-1);
    stroke: var(--surface-1);
    stroke-width: 2;
}
.telemetry-chart__tooltip {
    position: absolute;
    top: 8px;
    transform: translateX(-50%);
    background: var(--surface-1);
    border: 1px solid var(--gridline);
    border-radius: 8px;
    padding: 0.375rem 0.625rem;
    box-shadow: 0 2px 8px rgba(11, 11, 11, 0.12);
    pointer-events: none;
    white-space: nowrap;
}
.telemetry-chart__tooltip-value {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--text-primary);
}
.telemetry-chart__tooltip-label {
    font-size: 0.75rem;
    color: var(--text-secondary);
}
</style>
