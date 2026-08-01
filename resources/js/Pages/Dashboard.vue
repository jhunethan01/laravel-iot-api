<script setup>
import { Head, router } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import StatTile from '@/Components/StatTile.vue';
import StatusBadge from '@/Components/StatusBadge.vue';

const props = defineProps({
    stats: { type: Object, required: true },
    telemetry: { type: Array, default: () => [] },
    alerts: { type: Array, default: () => [] },
    devices: { type: Array, default: () => [] },
    filters: { type: Object, required: true },
});

const STATUS_OPTIONS = [
    { value: 'all', label: 'All' },
    { value: 'online', label: 'Online' },
    { value: 'offline', label: 'Offline' },
    { value: 'critical', label: 'Critical' },
];

const search = ref(props.filters.search);
const statusFilter = ref(props.filters.status);

const activeAlerts = computed(() => props.alerts.filter((alert) => !alert.resolved));

const TABS = [
    { value: 'devices', label: 'Devices' },
    { value: 'alerts', label: 'Alerts' },
    { value: 'telemetry', label: 'Recent telemetry' },
];
const activeTab = ref('devices');

function applyFilters() {
    router.get(
        '/',
        {
            search: search.value || undefined,
            status: statusFilter.value === 'all' ? undefined : statusFilter.value,
        },
        { preserveState: true, preserveScroll: true, replace: true, only: ['devices', 'filters'] },
    );
}

let debounceTimer;
watch(search, () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(applyFilters, 300);
});

function selectStatus(value) {
    statusFilter.value = value;
    applyFilters();
}

function formatLastSeen(value) {
    return value ? new Date(value).toLocaleString() : 'Never';
}

function formatAlertType(type) {
    return type
        .split('_')
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
}

function formatTriggeredAt(value) {
    return new Date(value).toLocaleString();
}

function formatOnline(value) {
    return value ? 'Online' : 'Offline';
}

function formatTemperature(value) {
    return value === null || value === undefined ? '—' : `${value}°C`;
}

function formatBattery(value) {
    return value === null || value === undefined ? '—' : `${value}%`;
}

function formatStorage(value) {
    return value === null || value === undefined ? '—' : `${value}%`;
}

function formatCoordinates(lat, lng) {
    if (lat === null || lat === undefined || lng === null || lng === undefined) return '—';
    return `${Number(lat).toFixed(4)}, ${Number(lng).toFixed(4)}`;
}

const REFRESH_INTERVAL_SECONDS = 10;
const secondsUntilRefresh = ref(REFRESH_INTERVAL_SECONDS);
const lastRefreshedAt = ref(new Date());
let isRefreshing = false;
let tickTimer;

function formatLastRefreshedAt(value) {
    return value.toLocaleTimeString();
}

function refreshDashboard() {
    isRefreshing = true;
    router.reload({
        only: ['stats', 'telemetry', 'alerts', 'devices'],
        preserveState: true,
        preserveScroll: true,
        onFinish: () => {
            isRefreshing = false;
            lastRefreshedAt.value = new Date();
            secondsUntilRefresh.value = REFRESH_INTERVAL_SECONDS;
        },
    });
}

onMounted(() => {
    tickTimer = setInterval(() => {
        if (isRefreshing) return;
        secondsUntilRefresh.value -= 1;
        if (secondsUntilRefresh.value <= 0) {
            refreshDashboard();
        }
    }, 1000);
});

onUnmounted(() => {
    clearInterval(tickTimer);
});
</script>

<template>
    <Head title="Dashboard" />

    <div class="dashboard">
        <header class="dashboard__header">
            <h1>Sentinel Dashboard</h1>
            <p class="refresh-status">
                Last updated {{ formatLastRefreshedAt(lastRefreshedAt) }} &middot; refreshing in {{ secondsUntilRefresh }}s
            </p>
        </header>

        <section class="stat-grid">
            <StatTile label="Devices online" :value="stats.devicesOnline" accent="var(--status-good)" />
            <StatTile label="Devices offline" :value="stats.devicesOffline" accent="var(--text-muted)" />
            <StatTile label="Alerts" :value="stats.activeAlerts" accent="var(--status-critical)" />
        </section>

        <section class="legend">
            <span class="legend__title">Status colours</span>
            <StatusBadge status="online" />
            <StatusBadge status="offline" />
            <StatusBadge status="critical" />
        </section>

        <section class="panel">
            <h2>Active alerts</h2>
            <p class="panel__subtitle">Unresolved alerts requiring attention</p>

            <ul class="active-alerts">
                <li v-for="alert in activeAlerts" :key="alert.id" class="active-alerts__item">
                    <StatusBadge :status="alert.severity" />
                    <div class="active-alerts__body">
                        <span class="active-alerts__message">{{ alert.message }}</span>
                        <span class="active-alerts__meta">{{ alert.device_name }} &middot; {{ formatTriggeredAt(alert.triggered_at) }}</span>
                    </div>
                </li>
                <li v-if="!activeAlerts.length" class="active-alerts__empty">No active alerts. All clear.</li>
            </ul>
        </section>

        <section class="panel">
            <div class="tab-bar">
                <button
                    v-for="tab in TABS"
                    :key="tab.value"
                    type="button"
                    class="tab-bar__option"
                    :class="{ 'tab-bar__option--active': activeTab === tab.value }"
                    @click="activeTab = tab.value"
                >
                    {{ tab.label }}
                </button>
            </div>

            <div v-if="activeTab === 'devices'">
                <div class="filters-row">
                    <input v-model="search" type="search" placeholder="Search devices" class="search-input" />
                    <div class="status-filter">
                        <button
                            v-for="option in STATUS_OPTIONS"
                            :key="option.value"
                            type="button"
                            class="status-filter__option"
                            :class="{ 'status-filter__option--active': statusFilter === option.value }"
                            @click="selectStatus(option.value)"
                        >
                            {{ option.label }}
                        </button>
                    </div>
                </div>

                <div class="table-scroll">
                    <table class="device-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Device key</th>
                                <th>Model</th>
                                <th>Temperature</th>
                                <th>Battery</th>
                                <th>Storage</th>
                                <th>Location</th>
                                <th>Last seen</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="device in devices" :key="device.id">
                                <td>{{ device.name }}</td>
                                <td>{{ device.device_key }}</td>
                                <td>{{ device.model ?? '—' }}</td>
                                <td>{{ formatTemperature(device.latest_temperature) }}</td>
                                <td>{{ formatBattery(device.latest_battery) }}</td>
                                <td>{{ formatStorage(device.latest_storage_used) }}</td>
                                <td>{{ formatCoordinates(device.latest_latitude, device.latest_longitude) }}</td>
                                <td>{{ formatLastSeen(device.last_seen_at) }}</td>
                                <td><StatusBadge :status="device.status" /></td>
                            </tr>
                            <tr v-if="!devices.length">
                                <td colspan="9" class="device-table__empty">No devices match your search.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-else-if="activeTab === 'alerts'">
                <p class="panel__subtitle">Most recent alerts across all devices</p>

                <div class="table-scroll">
                    <table class="device-table">
                        <thead>
                            <tr>
                                <th>Severity</th>
                                <th>Type</th>
                                <th>Message</th>
                                <th>Device</th>
                                <th>Triggered</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="alert in alerts" :key="alert.id">
                                <td><StatusBadge :status="alert.severity" /></td>
                                <td>{{ formatAlertType(alert.type) }}</td>
                                <td class="device-table__wrap">{{ alert.message }}</td>
                                <td>{{ alert.device_name }}</td>
                                <td>{{ formatTriggeredAt(alert.triggered_at) }}</td>
                                <td>
                                    <span :class="alert.resolved ? 'alert-status alert-status--resolved' : 'alert-status alert-status--active'">
                                        {{ alert.resolved ? 'Resolved' : 'Active' }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="!alerts.length">
                                <td colspan="6" class="device-table__empty">No alerts recorded.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-else-if="activeTab === 'telemetry'">
                <p class="panel__subtitle">Latest raw readings, last 24 hours</p>

                <div class="table-scroll">
                    <table class="device-table">
                        <thead>
                            <tr>
                                <th>Device ID</th>
                                <th>Device</th>
                                <th>Recorded at</th>
                                <th>Temperature</th>
                                <th>Battery</th>
                                <th>Storage</th>
                                <th>Location</th>
                                <th>Online</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="reading in telemetry" :key="reading.id">
                                <td>{{ reading.device_id }}</td>
                                <td>{{ reading.device_name }}</td>
                                <td>{{ formatTriggeredAt(reading.recorded_at) }}</td>
                                <td>{{ formatTemperature(reading.temperature) }}</td>
                                <td>{{ formatBattery(reading.battery) }}</td>
                                <td>{{ formatStorage(reading.storage_used) }}</td>
                                <td>{{ formatCoordinates(reading.latitude, reading.longitude) }}</td>
                                <td>{{ formatOnline(reading.online) }}</td>
                            </tr>
                            <tr v-if="!telemetry.length">
                                <td colspan="8" class="device-table__empty">No telemetry recorded in the last 24 hours.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</template>

<style scoped>
.dashboard {
    max-width: 1080px;
    margin: 0 auto;
    padding: 2rem 1.5rem 4rem;
    color: var(--text-primary);
}
.dashboard__header h1 {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 0.375rem;
}
.refresh-status {
    color: var(--text-secondary);
    font-size: 0.8125rem;
    margin-bottom: 1.5rem;
}
.stat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.legend {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 2rem;
    font-size: 0.8125rem;
}
.legend__title {
    color: var(--text-secondary);
    font-weight: 600;
}
.panel {
    background: var(--surface-1);
    border: 1px solid var(--gridline);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}
.panel h2 {
    font-size: 1.0625rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
}
.panel__subtitle {
    color: var(--text-secondary);
    font-size: 0.8125rem;
    margin-bottom: 1rem;
}
.filters-row {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.25rem;
    flex-wrap: wrap;
}
.search-input {
    flex: 1;
    min-width: 200px;
    padding: 0.5rem 0.75rem;
    border: 1px solid var(--gridline);
    border-radius: 8px;
    background: var(--surface-1);
    color: var(--text-primary);
    font-size: 0.875rem;
}
.search-input:focus {
    outline: 2px solid var(--series-1);
    outline-offset: 1px;
}
.status-filter {
    display: flex;
    gap: 0.25rem;
    background: var(--page-plane);
    border-radius: 8px;
    padding: 0.25rem;
}
.status-filter__option {
    border: none;
    background: transparent;
    padding: 0.375rem 0.75rem;
    border-radius: 6px;
    font-size: 0.8125rem;
    color: var(--text-secondary);
    cursor: pointer;
}
.status-filter__option--active {
    background: var(--surface-1);
    color: var(--text-primary);
    font-weight: 600;
    box-shadow: 0 1px 2px rgba(11, 11, 11, 0.08);
}
.tab-bar {
    display: flex;
    gap: 0.25rem;
    border-bottom: 1px solid var(--gridline);
    margin-bottom: 1.25rem;
}
.tab-bar__option {
    border: none;
    background: transparent;
    padding: 0.625rem 0.875rem;
    margin-bottom: -1px;
    border-bottom: 2px solid transparent;
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--text-secondary);
    cursor: pointer;
}
.tab-bar__option--active {
    color: var(--text-primary);
    border-bottom-color: var(--series-1);
}
.table-scroll {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    margin: 0 -0.25rem;
}
.device-table {
    width: 100%;
    min-width: 640px;
    border-collapse: collapse;
    font-size: 0.875rem;
}
.device-table th {
    text-align: left;
    color: var(--text-secondary);
    font-weight: 600;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    padding: 0.5rem 0.75rem;
    border-bottom: 1px solid var(--gridline);
    white-space: nowrap;
}
.device-table td {
    padding: 0.625rem 0.75rem;
    border-bottom: 1px solid var(--gridline);
    white-space: nowrap;
}
.device-table td.device-table__wrap {
    white-space: normal;
    min-width: 200px;
    max-width: 320px;
}
.device-table__empty {
    text-align: center;
    color: var(--text-muted);
    padding: 1.5rem;
}
.active-alerts {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}
.active-alerts__item {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 0.75rem;
    border: 1px solid var(--gridline);
    border-radius: 8px;
    background: var(--page-plane);
}
.active-alerts__body {
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
    min-width: 0;
}
.active-alerts__message {
    font-size: 0.875rem;
    color: var(--text-primary);
}
.active-alerts__meta {
    font-size: 0.75rem;
    color: var(--text-secondary);
}
.active-alerts__empty {
    text-align: center;
    color: var(--text-muted);
    padding: 1.5rem;
    font-size: 0.875rem;
}
.alert-status {
    font-size: 0.8125rem;
    font-weight: 600;
}
.alert-status--active {
    color: var(--status-critical);
}
.alert-status--resolved {
    color: var(--text-muted);
    font-weight: 400;
}
</style>
