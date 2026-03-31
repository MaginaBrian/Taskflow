<template>
  <div class="stats-bar">
    <div class="stat-chip" v-for="stat in displayStats" :key="stat.label" :class="stat.class">
      <span class="stat-value">{{ stat.value }}</span>
      <span class="stat-label">{{ stat.label }}</span>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useTaskStore } from '@/stores/taskStore'

const store = useTaskStore()
const displayStats = computed(() => [
  { label: 'Total',       value: store.stats.total,      class: 'chip--total' },
  { label: 'Pending',     value: store.stats.pending,    class: 'chip--pending' },
  { label: 'In Progress', value: store.stats.inProgress, class: 'chip--progress' },
  { label: 'Done',        value: store.stats.done,       class: 'chip--done' },
  { label: 'High Priority', value: store.stats.high,    class: 'chip--high' },
])
</script>

<style scoped>
.stats-bar {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
}
.stat-chip {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 0.8rem 1.2rem;
  border-radius: var(--radius-lg);
  background: white;
  box-shadow: var(--shadow-sm);
  border: 1px solid rgba(122,158,135,0.08);
  min-width: 80px;
  gap: 0.15rem;
  transition: transform 0.18s ease;
}
.stat-chip:hover { transform: translateY(-2px); }
.stat-value {
  font-family: var(--font-display);
  font-size: 1.8rem;
  line-height: 1;
  color: var(--charcoal);
}
.stat-label {
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  font-weight: 600;
  color: var(--slate-light);
}
.chip--pending  .stat-value { color: #6B7A90; }
.chip--progress .stat-value { color: #8A6A2A; }
.chip--done     .stat-value { color: var(--sage-dark); }
.chip--high     .stat-value { color: var(--terracotta); }
</style>
