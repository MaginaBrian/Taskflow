<template>
  <div class="report-view">
    <div class="page-header">
      <div>
        <h1 class="page-title">Daily Report</h1>
        <p class="page-subtitle">Task summary by priority &amp; status</p>
      </div>
    </div>

    <!-- Date Picker -->
    <div class="date-section">
      <label class="date-label" for="report-date">Select Date</label>
      <div class="date-input-row">
        <input
          id="report-date"
          v-model="selectedDate"
          type="date"
          class="date-input"
          :max="today"
        />
        <button class="btn-fetch" @click="fetchReport" :disabled="store.reportLoading">
          <span v-if="store.reportLoading" class="spinner"></span>
          <span v-else>Generate Report ◇</span>
        </button>
      </div>
    </div>

    <!-- Error -->
    <Transition name="fade">
      <div v-if="store.error" class="error-banner">⚠ {{ store.error }}</div>
    </Transition>

    <!-- Report Result -->
    <Transition name="fade">
      <div v-if="store.report" class="report-content">
        <div class="report-date-heading">
          <span class="report-date-accent">◆</span>
          Report for
          <strong>{{ formattedReportDate }}</strong>
        </div>

        <!-- Summary grid -->
        <div class="summary-grid">
          <div
            v-for="(statusData, priority) in store.report.summary"
            :key="priority"
            class="priority-block"
            :class="`block--${priority}`"
          >
            <div class="block-header">
              <span class="block-icon">{{ priorityIcons[priority] }}</span>
              <h3 class="block-title">{{ priority }} Priority</h3>
              <span class="block-total">{{ blockTotal(statusData) }} tasks</span>
            </div>

            <div class="status-breakdown">
              <div
                v-for="(count, status) in statusData"
                :key="status"
                class="status-row"
                :class="`row--${status}`"
              >
                <span class="status-name">{{ statusLabels[status] }}</span>
                <div class="status-bar-wrap">
                  <div
                    class="status-bar"
                    :class="`bar--${status}`"
                    :style="{ width: barWidth(count, blockTotal(statusData)) }"
                  ></div>
                </div>
                <span class="status-count">{{ count }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Raw JSON View -->
        <details class="json-block">
          <summary class="json-summary">View raw JSON response ↓</summary>
          <pre class="json-content">{{ JSON.stringify(store.report, null, 2) }}</pre>
        </details>
      </div>
    </Transition>

    <!-- Placeholder -->
    <div v-if="!store.report && !store.reportLoading" class="placeholder-state">
      <div class="placeholder-icon">◇</div>
      <p>Select a date and generate a report to see task statistics.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useTaskStore } from '@/stores/taskStore'

const store = useTaskStore()
const today = new Date().toISOString().split('T')[0]
const selectedDate = ref(today)

const priorityIcons = { high: '▲', medium: '◆', low: '▽' }
const statusLabels = { pending: 'Pending', in_progress: 'In Progress', done: 'Done' }

async function fetchReport() {
  if (!selectedDate.value) return
  await store.fetchReport(selectedDate.value)
}

function blockTotal(statusData) {
  return Object.values(statusData).reduce((a, b) => a + b, 0)
}
function barWidth(count, total) {
  if (!total) return '0%'
  return Math.round((count / total) * 100) + '%'
}

const formattedReportDate = computed(() => {
  if (!store.report?.date) return ''
  const d = new Date(store.report.date + 'T00:00:00')
  return d.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })
})
</script>

<style scoped>
.report-view {
  max-width: 900px;
  margin: 0 auto;
  padding: 2.5rem 2rem 5rem;
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.page-header { display: flex; align-items: flex-end; gap: 1rem; }
.page-title {
  font-family: var(--font-display);
  font-size: clamp(2rem, 5vw, 3rem);
  color: var(--charcoal);
  line-height: 1.1;
  letter-spacing: -0.02em;
}
.page-subtitle { font-size: 0.9rem; color: var(--slate-light); margin-top: 0.25rem; font-style: italic; }

/* Date section */
.date-section { display: flex; flex-direction: column; gap: 0.6rem; }
.date-label {
  font-size: 0.78rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.07em;
  color: var(--slate);
}
.date-input-row { display: flex; gap: 0.75rem; flex-wrap: wrap; }
.date-input {
  padding: 0.7rem 1rem;
  border: 1.5px solid var(--parchment);
  border-radius: var(--radius-md);
  font-family: var(--font-body);
  font-size: 0.95rem;
  color: var(--charcoal);
  background: white;
  transition: border-color 0.18s ease;
}
.date-input:focus {
  outline: none;
  border-color: var(--sage);
  box-shadow: 0 0 0 3px rgba(122,158,135,0.15);
}
.btn-fetch {
  padding: 0.7rem 1.5rem;
  background: var(--sage);
  color: white;
  border: none;
  border-radius: var(--radius-md);
  font-family: var(--font-body);
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.18s ease;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  letter-spacing: 0.01em;
}
.btn-fetch:hover:not(:disabled) { background: var(--sage-dark); transform: translateY(-1px); }
.btn-fetch:disabled { opacity: 0.6; cursor: not-allowed; }

.spinner {
  width: 16px; height: 16px;
  border: 2px solid rgba(255,255,255,0.35);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
  display: inline-block;
}
@keyframes spin { to { transform: rotate(360deg); } }

.error-banner {
  background: rgba(201,123,92,0.1);
  border: 1px solid rgba(201,123,92,0.2);
  border-radius: var(--radius-md);
  padding: 0.85rem 1.2rem;
  color: var(--terracotta);
  font-size: 0.9rem;
}

/* Report content */
.report-date-heading {
  font-family: var(--font-display);
  font-size: 1.15rem;
  color: var(--charcoal);
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 1.5rem;
}
.report-date-accent { color: var(--sage); font-size: 0.9em; }

.summary-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 1.1rem;
}

.priority-block {
  background: white;
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-card);
  overflow: hidden;
  border: 1px solid rgba(122,158,135,0.08);
}
.block-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 1.1rem 1.3rem;
  border-bottom: 1px solid rgba(122,158,135,0.08);
}
.block--high   .block-header { background: rgba(201,123,92,0.06); }
.block--medium .block-header { background: rgba(201,169,110,0.08); }
.block--low    .block-header { background: rgba(122,158,135,0.06); }

.block-icon { font-size: 0.9em; }
.block--high   .block-icon { color: var(--priority-high); }
.block--medium .block-icon { color: var(--priority-medium); }
.block--low    .block-icon { color: var(--priority-low); }

.block-title {
  font-size: 0.85rem;
  font-weight: 600;
  text-transform: capitalize;
  color: var(--charcoal);
  flex: 1;
}
.block-total {
  font-size: 0.75rem;
  color: var(--slate-light);
  font-variant-numeric: tabular-nums;
}

.status-breakdown {
  padding: 1rem 1.3rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}
.status-row { display: flex; align-items: center; gap: 0.65rem; }
.status-name {
  font-size: 0.78rem;
  color: var(--slate);
  min-width: 72px;
  font-weight: 500;
}
.status-bar-wrap {
  flex: 1;
  height: 6px;
  background: var(--cream-dark);
  border-radius: 3px;
  overflow: hidden;
}
.status-bar {
  height: 100%;
  border-radius: 3px;
  min-width: 2px;
  transition: width 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.bar--pending     { background: var(--status-pending); }
.bar--in_progress { background: var(--status-in-progress); }
.bar--done        { background: var(--status-done); }
.status-count {
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--charcoal);
  min-width: 20px;
  text-align: right;
  font-variant-numeric: tabular-nums;
}

/* JSON block */
.json-block {
  margin-top: 0.5rem;
  background: var(--cream);
  border-radius: var(--radius-lg);
  overflow: hidden;
  border: 1px solid var(--parchment);
}
.json-summary {
  padding: 0.85rem 1.2rem;
  font-size: 0.82rem;
  color: var(--slate);
  cursor: pointer;
  font-weight: 500;
  letter-spacing: 0.02em;
  user-select: none;
}
.json-summary:hover { color: var(--charcoal); }
.json-content {
  padding: 1rem 1.2rem 1.2rem;
  font-size: 0.8rem;
  line-height: 1.7;
  color: var(--slate);
  overflow-x: auto;
  background: var(--cream-dark);
  border-top: 1px solid var(--parchment);
  font-family: 'Courier New', monospace;
}

/* Placeholder */
.placeholder-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  padding: 4rem 2rem;
  text-align: center;
  color: var(--slate-light);
}
.placeholder-icon {
  font-size: 3rem;
  color: var(--sage-light);
  animation: pulse 3s ease-in-out infinite;
}
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:0.4} }
.placeholder-state p { max-width: 280px; font-size: 0.9rem; font-style: italic; }

@media (max-width: 640px) {
  .report-view { padding: 1.5rem 1rem 3rem; }
  .summary-grid { grid-template-columns: 1fr; }
}
</style>
