<template>
  <div class="tasks-view">
    <!-- Page Header -->
    <div class="page-header">
      <div class="page-header__content">
        <h1 class="page-title">My Tasks</h1>
        <p class="page-subtitle">Organised by priority &amp; deadline</p>
      </div>
      <button class="btn-new-task" @click="showModal = true">
        <span class="btn-plus">+</span> New Task
      </button>
    </div>

    <!-- Stats Bar -->
    <div class="section-container">
      <StatsBar />
    </div>

    <!-- Filters -->
    <div class="section-container filter-row">
      <div class="filter-group">
        <span class="filter-label">Filter by status:</span>
        <div class="filter-pills">
          <button
            v-for="f in filters"
            :key="f.value"
            class="filter-pill"
            :class="{ active: store.statusFilter === f.value }"
            @click="store.setFilter(f.value)"
          >
            <span class="pill-dot" :class="`dot--${f.value || 'all'}`"></span>
            {{ f.label }}
          </button>
        </div>
      </div>

      <div class="filter-actions">
        <div class="view-toggle" role="group" aria-label="Task view mode">
          <button
            class="view-pill"
            :class="{ active: viewMode === 'grid' }"
            @click="viewMode = 'grid'"
          >
            Cards
          </button>
          <button
            class="view-pill"
            :class="{ active: viewMode === 'table' }"
            @click="viewMode = 'table'"
          >
            Table
          </button>
        </div>

        <button class="btn-refresh" @click="store.fetchTasks(store.statusFilter)" :disabled="store.loading">
          <span :class="{ spinning: store.loading }">↻</span>
        </button>
      </div>
    </div>

    <!-- Error -->
    <Transition name="fade">
      <div v-if="store.error" class="error-banner">
        <span>⚠</span> {{ store.error }}
        <button @click="store.clearError()" class="error-dismiss">✕</button>
      </div>
    </Transition>

    <!-- Loading -->
    <div v-if="store.loading && !store.tasks.length" class="loading-state">
      <div class="loading-spinner"></div>
      <p>Loading tasks…</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="!store.loading && !store.tasks.length" class="empty-state">
      <div class="empty-illustration">⟡</div>
      <h3>No tasks found</h3>
      <p v-if="store.statusFilter">
        No tasks with status <strong>{{ store.statusFilter }}</strong> found.
      </p>
      <p v-else>You're all clear! Create a task to get started.</p>
      <button class="btn-new-task btn-new-task--sm" @click="showModal = true">
        + Create your first task
      </button>
    </div>

    <!-- Task Grid / Table -->
    <div v-else class="section-container">
      <TransitionGroup name="list" tag="div" class="task-grid">
        <TaskCard
          v-if="viewMode === 'grid'"
          v-for="task in store.tasks"
          :key="task.id"
          :task="task"
          @advance="handleAdvance"
          @delete="handleDelete"
        />
      </TransitionGroup>

      <div v-if="viewMode === 'table'" class="task-table-wrap">
        <table class="task-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Title</th>
              <th>Priority</th>
              <th>Status</th>
              <th>Due Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="task in store.tasks" :key="task.id">
              <td class="mono">#{{ task.id }}</td>
              <td class="task-title-cell">{{ task.title }}</td>
              <td>
                <span class="table-badge" :class="`badge--${task.priority}`">
                  {{ priorityLabel(task.priority) }}
                </span>
              </td>
              <td>
                <span class="table-badge" :class="`status-badge--${task.status}`">
                  {{ statusLabel(task.status) }}
                </span>
              </td>
              <td :class="{ overdue: isOverdue(task) }">
                {{ formatDate(task.due_date) }}
              </td>
              <td>
                <div class="table-actions">
                  <button
                    v-if="task.status !== 'done'"
                    class="btn-table btn-table--advance"
                    @click="handleAdvance(task)"
                  >
                    {{ nextStatusLabel(task.status) }}
                  </button>
                  <button
                    v-if="task.status === 'done'"
                    class="btn-table btn-table--delete"
                    @click="handleDelete(task)"
                  >
                    Delete
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Toast Notification -->
    <Transition name="slide-up">
      <div v-if="toast.show" class="toast" :class="`toast--${toast.type}`">
        <span>{{ toast.icon }}</span>
        {{ toast.message }}
      </div>
    </Transition>

    <!-- Create Modal -->
    <CreateTaskModal
      :show="showModal"
      @close="showModal = false"
      @created="onTaskCreated"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useTaskStore } from '@/stores/taskStore'
import TaskCard from '@/components/TaskCard.vue'
import StatsBar from '@/components/StatsBar.vue'
import CreateTaskModal from '@/components/CreateTaskModal.vue'

const store = useTaskStore()
const showModal = ref(false)
const viewMode = ref('grid')

const filters = [
  { value: '',           label: 'All' },
  { value: 'pending',    label: 'Pending' },
  { value: 'in_progress', label: 'In Progress' },
  { value: 'done',       label: 'Done' },
]

const toast = ref({ show: false, message: '', type: 'success', icon: '✓' })
let toastTimer = null

function showToast(message, type = 'success') {
  if (toastTimer) clearTimeout(toastTimer)
  toast.value = {
    show: true,
    message,
    type,
    icon: type === 'success' ? '✓' : '⚠',
  }
  toastTimer = setTimeout(() => { toast.value.show = false }, 3200)
}

async function handleAdvance(task) {
  const nextStatus = { pending: 'in_progress', in_progress: 'done' }[task.status]
  if (!nextStatus) return
  const result = await store.updateTaskStatus(task.id, nextStatus)
  if (result.success) {
    showToast(`Task moved to "${nextStatus.replace('_', ' ')}"`)
  } else {
    showToast(result.error, 'error')
  }
}

async function handleDelete(task) {
  const result = await store.deleteTask(task.id)
  if (result.success) {
    showToast('Task deleted successfully.')
  } else {
    showToast(result.error, 'error')
  }
}

function onTaskCreated() {
  showToast('Task created successfully! ✦')
}

function formatDate(dateString) {
  const d = new Date(dateString + 'T00:00:00')
  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

function statusLabel(status) {
  return { pending: 'Pending', in_progress: 'In Progress', done: 'Done' }[status] || status
}

function priorityLabel(priority) {
  return { high: 'High', medium: 'Medium', low: 'Low' }[priority] || priority
}

function nextStatusLabel(status) {
  return { pending: 'Start Task', in_progress: 'Mark Done' }[status] || 'Update'
}

function isOverdue(task) {
  if (task.status === 'done') return false
  const due = new Date(task.due_date + 'T00:00:00')
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  return due < today
}

onMounted(() => store.fetchTasks())
</script>

<style scoped>
.tasks-view {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2.5rem 2rem 4rem;
  display: flex;
  flex-direction: column;
  gap: 1.75rem;
}

/* Page header */
.page-header {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 1rem;
  flex-wrap: wrap;
}
.page-title {
  font-family: var(--font-display);
  font-size: clamp(2rem, 5vw, 3rem);
  color: var(--charcoal);
  line-height: 1.1;
  letter-spacing: -0.02em;
}
.page-subtitle {
  font-size: 0.9rem;
  color: var(--slate-light);
  margin-top: 0.25rem;
  font-style: italic;
}

.btn-new-task {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.75rem 1.5rem;
  background: var(--charcoal);
  color: white;
  border: none;
  border-radius: 100px;
  font-family: var(--font-body);
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  letter-spacing: 0.01em;
  white-space: nowrap;
}
.btn-new-task:hover {
  background: var(--sage-dark);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(78,122,96,0.3);
}
.btn-plus { font-size: 1.1em; line-height: 1; }
.btn-new-task--sm {
  padding: 0.6rem 1.2rem;
  font-size: 0.85rem;
  margin-top: 0.5rem;
}

.section-container { width: 100%; }

/* Filter row */
.filter-row {
  display: flex;
  align-items: center;
  gap: 1rem;
  justify-content: space-between;
  flex-wrap: wrap;
}
.filter-group { display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap; }
.filter-label {
  font-size: 0.78rem;
  text-transform: uppercase;
  letter-spacing: 0.07em;
  font-weight: 600;
  color: var(--slate-light);
}
.filter-pills { display: flex; gap: 0.4rem; flex-wrap: wrap; }
.filter-actions { display: flex; align-items: center; gap: 0.5rem; }
.view-toggle {
  display: inline-flex;
  border: 1.5px solid var(--parchment);
  border-radius: 100px;
  background: white;
  overflow: hidden;
}
.view-pill {
  border: none;
  background: transparent;
  color: var(--slate);
  font-family: var(--font-body);
  font-size: 0.82rem;
  padding: 0.35rem 0.75rem;
  cursor: pointer;
}
.view-pill.active {
  background: var(--sage);
  color: white;
}
.filter-pill {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.35rem 0.85rem;
  border-radius: 100px;
  border: 1.5px solid var(--parchment);
  background: white;
  font-size: 0.82rem;
  font-family: var(--font-body);
  font-weight: 500;
  color: var(--slate);
  cursor: pointer;
  transition: all 0.18s ease;
}
.filter-pill:hover { border-color: var(--sage-light); }
.filter-pill.active {
  background: var(--sage);
  border-color: var(--sage);
  color: white;
}
.pill-dot {
  width: 6px; height: 6px;
  border-radius: 50%;
  background: currentColor;
  opacity: 0.6;
}
.dot--all      { background: var(--slate-light); }
.dot--pending  { background: var(--status-pending); }
.dot--in_progress { background: var(--status-in-progress); }
.dot--done     { background: var(--status-done); }

.btn-refresh {
  background: white;
  border: 1.5px solid var(--parchment);
  padding: 0.35rem 0.75rem;
  border-radius: var(--radius-md);
  font-size: 1.1rem;
  color: var(--sage);
  cursor: pointer;
  transition: all 0.2s ease;
  line-height: 1;
}
.btn-refresh:hover:not(:disabled) { border-color: var(--sage); background: var(--cream); }
.spinning { display: inline-block; animation: spin 0.7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* Error banner */
.error-banner {
  background: rgba(201,123,92,0.1);
  border: 1px solid rgba(201,123,92,0.25);
  border-radius: var(--radius-md);
  padding: 0.85rem 1.2rem;
  color: var(--terracotta);
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.9rem;
}
.error-dismiss {
  margin-left: auto;
  background: none;
  border: none;
  color: var(--terracotta);
  cursor: pointer;
  font-size: 0.85rem;
  opacity: 0.7;
}
.error-dismiss:hover { opacity: 1; }

/* Loading */
.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  padding: 4rem;
  color: var(--slate-light);
  font-size: 0.9rem;
  font-style: italic;
}
.loading-spinner {
  width: 36px; height: 36px;
  border: 3px solid var(--cream-dark);
  border-top-color: var(--sage);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

/* Empty state */
.empty-state {
  text-align: center;
  padding: 5rem 2rem;
  color: var(--slate);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
}
.empty-illustration {
  font-size: 4rem;
  color: var(--sage-light);
  line-height: 1;
  margin-bottom: 0.5rem;
  animation: pulse 3s ease-in-out infinite;
}
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:0.5} }
.empty-state h3 { font-family: var(--font-display); font-size: 1.5rem; color: var(--charcoal); }
.empty-state p { color: var(--slate-light); max-width: 280px; }

/* Task Grid */
.task-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.1rem;
  position: relative;
}

.task-table-wrap {
  border: 1px solid var(--parchment);
  border-radius: var(--radius-lg);
  background: white;
  overflow-x: auto;
}
.task-table {
  width: 100%;
  border-collapse: collapse;
  min-width: 760px;
}
.task-table th,
.task-table td {
  padding: 0.8rem 0.9rem;
  border-bottom: 1px solid rgba(122,158,135,0.14);
  text-align: left;
  font-size: 0.88rem;
  color: var(--slate);
}
.task-table th {
  font-size: 0.72rem;
  text-transform: uppercase;
  letter-spacing: 0.07em;
  color: var(--slate-light);
  background: var(--cream);
}
.task-title-cell {
  min-width: 220px;
  color: var(--charcoal);
  font-weight: 600;
}
.mono {
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
  color: var(--slate-light);
}
.table-badge {
  display: inline-flex;
  align-items: center;
  border-radius: 100px;
  font-size: 0.72rem;
  font-weight: 600;
  padding: 0.2rem 0.55rem;
}
.table-actions { display: flex; gap: 0.4rem; }
.btn-table {
  border: none;
  border-radius: 100px;
  font-size: 0.75rem;
  padding: 0.3rem 0.7rem;
  cursor: pointer;
}
.btn-table--advance {
  background: var(--sage);
  color: white;
}
.btn-table--delete {
  background: rgba(201,123,92,0.12);
  color: var(--terracotta);
}
.overdue {
  color: var(--terracotta) !important;
  font-weight: 600;
}

/* Toast */
.toast {
  position: fixed;
  bottom: 2rem;
  left: 50%;
  transform: translateX(-50%);
  padding: 0.85rem 1.5rem;
  border-radius: 100px;
  font-size: 0.9rem;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  box-shadow: var(--shadow-lg);
  z-index: 300;
  white-space: nowrap;
}
.toast--success { background: var(--charcoal); color: white; }
.toast--error   { background: var(--terracotta); color: white; }

@media (max-width: 640px) {
  .tasks-view { padding: 1.5rem 1rem 3rem; }
  .task-grid { grid-template-columns: 1fr; }
  .page-title { font-size: 2rem; }
  .header-date { display: none; }
}
</style>
