<template>
  <div class="task-card" :class="[`priority--${task.priority}`, `status--${task.status}`]">
    <div class="card-priority-bar"></div>

    <div class="card-body">
      <div class="card-top">
        <span class="priority-badge" :class="`badge--${task.priority}`">
          {{ priorityIcon }} {{ task.priority }}
        </span>
        <span class="status-badge" :class="`status-badge--${task.status}`">
          {{ statusLabel }}
        </span>
      </div>

      <h3 class="task-title">{{ task.title }}</h3>

      <div class="card-meta">
        <span class="due-date" :class="{ overdue: isOverdue }">
          <span class="meta-icon">◷</span>
          {{ formattedDate }}
          <span v-if="isOverdue" class="overdue-tag">Overdue</span>
        </span>
        <span class="task-id">#{{ task.id }}</span>
      </div>

      <div class="card-actions">
        <!-- Advance Status -->
        <button
          v-if="task.status !== 'done'"
          class="btn btn--advance"
          @click="$emit('advance', task)"
          :title="`Move to ${nextStatus}`"
        >
          <span>{{ nextStatusLabel }}</span>
          <span class="btn-arrow">→</span>
        </button>
        <span v-else class="done-label">✓ Completed</span>

        <!-- Delete (only done tasks) -->
        <button
          v-if="task.status === 'done'"
          class="btn btn--delete"
          @click="$emit('delete', task)"
          title="Delete task"
        >
          ✕
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({ task: { type: Object, required: true } })
defineEmits(['advance', 'delete'])

const priorityIcon = computed(() => ({ high: '▲', medium: '◆', low: '▽' }[props.task.priority]))
const statusLabel = computed(() => ({
  pending: 'Pending', in_progress: 'In Progress', done: 'Done'
}[props.task.status]))

const nextStatus = computed(() => ({
  pending: 'in_progress', in_progress: 'done'
}[props.task.status]))

const nextStatusLabel = computed(() => ({
  pending: 'Start Task', in_progress: 'Mark Done'
}[props.task.status]))

const formattedDate = computed(() => {
  const d = new Date(props.task.due_date + 'T00:00:00')
  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
})

const isOverdue = computed(() => {
  if (props.task.status === 'done') return false
  const due = new Date(props.task.due_date + 'T00:00:00')
  const today = new Date(); today.setHours(0,0,0,0)
  return due < today
})
</script>

<style scoped>
.task-card {
  background: white;
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-card);
  overflow: hidden;
  display: flex;
  transition: transform 0.22s ease, box-shadow 0.22s ease;
  position: relative;
  border: 1px solid rgba(122,158,135,0.08);
}
.task-card:hover {
  transform: translateY(-3px);
  box-shadow: var(--shadow-md);
}

/* Priority sidebar bar */
.card-priority-bar {
  width: 5px;
  flex-shrink: 0;
  border-radius: 0;
}
.priority--high .card-priority-bar   { background: var(--priority-high); }
.priority--medium .card-priority-bar { background: var(--priority-medium); }
.priority--low .card-priority-bar    { background: var(--priority-low); }

/* Done cards are slightly muted */
.status--done { opacity: 0.82; }

.card-body {
  padding: 1.25rem 1.4rem 1.25rem 1.2rem;
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 0.7rem;
}

.card-top {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex-wrap: wrap;
}

/* Priority badge */
.priority-badge {
  font-size: 0.7rem;
  font-weight: 600;
  padding: 0.2rem 0.6rem;
  border-radius: 100px;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}
.badge--high   { background: rgba(201,123,92,0.12); color: var(--priority-high); }
.badge--medium { background: rgba(201,169,110,0.15); color: #A07840; }
.badge--low    { background: rgba(122,158,135,0.12); color: var(--sage-dark); }

/* Status badge */
.status-badge {
  font-size: 0.7rem;
  font-weight: 500;
  padding: 0.2rem 0.6rem;
  border-radius: 100px;
  letter-spacing: 0.04em;
}
.status-badge--pending     { background: rgba(160,174,192,0.15); color: #6B7A90; }
.status-badge--in_progress { background: rgba(201,169,110,0.15); color: #8A6A2A; }
.status-badge--done        { background: rgba(122,158,135,0.15); color: var(--sage-dark); }

.task-title {
  font-family: var(--font-display);
  font-size: 1.05rem;
  color: var(--charcoal);
  line-height: 1.35;
  word-break: break-word;
}

.card-meta {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
}
.due-date {
  font-size: 0.8rem;
  color: var(--slate-light);
  display: flex;
  align-items: center;
  gap: 0.3rem;
}
.due-date.overdue { color: var(--terracotta); font-weight: 500; }
.overdue-tag {
  background: rgba(201,123,92,0.12);
  color: var(--terracotta);
  font-size: 0.65rem;
  padding: 0.1rem 0.4rem;
  border-radius: 100px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
.meta-icon { opacity: 0.6; }
.task-id { font-size: 0.72rem; color: var(--parchment); font-variant-numeric: tabular-nums; }

.card-actions {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-top: 0.2rem;
}
.btn {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.4rem 0.9rem;
  border-radius: 100px;
  border: none;
  font-family: var(--font-body);
  font-size: 0.8rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.18s ease;
}
.btn--advance {
  background: var(--sage);
  color: white;
}
.btn--advance:hover { background: var(--sage-dark); transform: scale(1.03); }
.btn-arrow { font-size: 0.85em; transition: transform 0.18s ease; }
.btn--advance:hover .btn-arrow { transform: translateX(3px); }

.btn--delete {
  background: rgba(201,123,92,0.1);
  color: var(--terracotta);
  padding: 0.4rem 0.65rem;
  margin-left: auto;
}
.btn--delete:hover { background: var(--terracotta); color: white; }

.done-label {
  font-size: 0.8rem;
  color: var(--sage);
  font-weight: 500;
}
</style>
