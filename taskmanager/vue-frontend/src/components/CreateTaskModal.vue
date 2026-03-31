<template>
  <Teleport to="body">
    <Transition name="modal-bg">
      <div v-if="show" class="modal-overlay" @click.self="$emit('close')">
        <Transition name="modal-panel">
          <div v-if="show" class="modal-panel" role="dialog" aria-modal="true" aria-labelledby="modal-title">
            <div class="modal-header">
              <h2 class="modal-title" id="modal-title">
                <span class="title-accent">✦</span> New Task
              </h2>
              <button class="close-btn" @click="$emit('close')" aria-label="Close">✕</button>
            </div>

            <form class="modal-form" @submit.prevent="handleSubmit">
              <!-- Title -->
              <div class="form-group" :class="{ 'has-error': errors.title }">
                <label class="form-label" for="task-title">Task Title</label>
                <input
                  id="task-title"
                  v-model="form.title"
                  class="form-input"
                  type="text"
                  placeholder="What needs to be done?"
                  maxlength="255"
                  autocomplete="off"
                />
                <p v-if="errors.title" class="form-error">{{ errors.title }}</p>
              </div>

              <!-- Due Date -->
              <div class="form-group" :class="{ 'has-error': errors.due_date }">
                <label class="form-label" for="task-due-date">Due Date</label>
                <input
                  id="task-due-date"
                  v-model="form.due_date"
                  class="form-input"
                  type="date"
                  :min="today"
                />
                <p v-if="errors.due_date" class="form-error">{{ errors.due_date }}</p>
              </div>

              <!-- Priority -->
              <div class="form-group" :class="{ 'has-error': errors.priority }">
                <label class="form-label">Priority</label>
                <div class="priority-picker">
                  <label
                    v-for="p in priorities"
                    :key="p.value"
                    class="priority-option"
                    :class="[`option--${p.value}`, { selected: form.priority === p.value }]"
                  >
                    <input type="radio" v-model="form.priority" :value="p.value" hidden />
                    <span class="option-icon">{{ p.icon }}</span>
                    <span class="option-label">{{ p.label }}</span>
                  </label>
                </div>
                <p v-if="errors.priority" class="form-error">{{ errors.priority }}</p>
              </div>

              <!-- API Error -->
              <Transition name="fade">
                <div v-if="apiError" class="api-error">
                  <span>⚠</span> {{ apiError }}
                </div>
              </Transition>

              <div class="form-actions">
                <button type="button" class="btn-cancel" @click="$emit('close')">Cancel</button>
                <button type="submit" class="btn-submit" :disabled="submitting">
                  <span v-if="submitting" class="spinner"></span>
                  <span v-else>Create Task</span>
                </button>
              </div>
            </form>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useTaskStore } from '@/stores/taskStore'

const props = defineProps({ show: Boolean })
const emit = defineEmits(['close', 'created'])

const store = useTaskStore()

const today = computed(() => new Date().toISOString().split('T')[0])

const priorities = [
  { value: 'high',   label: 'High',   icon: '▲' },
  { value: 'medium', label: 'Medium', icon: '◆' },
  { value: 'low',    label: 'Low',    icon: '▽' },
]

const form = ref({ title: '', due_date: today.value, priority: 'medium' })
const errors = ref({})
const apiError = ref('')
const submitting = ref(false)

// Reset form when modal opens
watch(() => props.show, (val) => {
  if (val) {
    form.value = { title: '', due_date: today.value, priority: 'medium' }
    errors.value = {}
    apiError.value = ''
    submitting.value = false
  }
})

function validate() {
  errors.value = {}
  if (!form.value.title.trim()) errors.value.title = 'Title is required.'
  if (!form.value.due_date) errors.value.due_date = 'Due date is required.'
  if (!form.value.priority) errors.value.priority = 'Priority is required.'
  return Object.keys(errors.value).length === 0
}

async function handleSubmit() {
  if (!validate()) return
  submitting.value = true
  apiError.value = ''
  const result = await store.createTask({
    title:    form.value.title.trim(),
    due_date: form.value.due_date,
    priority: form.value.priority,
  })
  submitting.value = false
  if (result.success) {
    emit('created', result.data)
    emit('close')
  } else {
    apiError.value = result.error
  }
}
</script>

<style scoped>
.modal-overlay {
  position: fixed; inset: 0;
  background: rgba(45,55,72,0.35);
  backdrop-filter: blur(6px);
  z-index: 200;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.5rem;
}
.modal-bg-enter-active, .modal-bg-leave-active { transition: opacity 0.25s ease; }
.modal-bg-enter-from, .modal-bg-leave-to { opacity: 0; }

.modal-panel {
  background: white;
  border-radius: var(--radius-xl);
  box-shadow: var(--shadow-lg), 0 0 0 1px rgba(122,158,135,0.08);
  width: 100%;
  max-width: 480px;
  overflow: hidden;
}
.modal-panel-enter-active {
  transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.modal-panel-leave-active { transition: all 0.2s ease; }
.modal-panel-enter-from, .modal-panel-leave-to {
  opacity: 0; transform: scale(0.92) translateY(10px);
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.75rem 1.75rem 0;
}
.modal-title {
  font-family: var(--font-display);
  font-size: 1.5rem;
  color: var(--charcoal);
  display: flex;
  align-items: center;
  gap: 0.5rem;
}
.title-accent { color: var(--sage); font-size: 1rem; }
.close-btn {
  background: var(--cream-dark);
  border: none;
  width: 32px; height: 32px;
  border-radius: 50%;
  cursor: pointer;
  font-size: 0.8rem;
  color: var(--slate);
  transition: all 0.18s ease;
  display: flex; align-items: center; justify-content: center;
}
.close-btn:hover { background: var(--parchment); }

.modal-form {
  padding: 1.5rem 1.75rem 1.75rem;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.form-group { display: flex; flex-direction: column; gap: 0.45rem; }
.form-label {
  font-size: 0.8rem;
  font-weight: 600;
  color: var(--slate);
  text-transform: uppercase;
  letter-spacing: 0.06em;
}
.form-input {
  padding: 0.7rem 1rem;
  border: 1.5px solid var(--parchment);
  border-radius: var(--radius-md);
  font-family: var(--font-body);
  font-size: 0.95rem;
  color: var(--charcoal);
  background: var(--cream);
  transition: border-color 0.18s ease, box-shadow 0.18s ease;
  width: 100%;
}
.form-input:focus {
  outline: none;
  border-color: var(--sage);
  box-shadow: 0 0 0 3px rgba(122,158,135,0.15);
  background: white;
}
.has-error .form-input {
  border-color: var(--terracotta);
  box-shadow: 0 0 0 3px rgba(201,123,92,0.1);
}
.form-error { font-size: 0.78rem; color: var(--terracotta); }

/* Priority picker */
.priority-picker {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.6rem;
}
.priority-option {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.4rem;
  padding: 0.6rem;
  border-radius: var(--radius-md);
  border: 1.5px solid var(--parchment);
  cursor: pointer;
  transition: all 0.18s ease;
  font-size: 0.875rem;
  font-weight: 500;
  background: var(--cream);
  color: var(--slate);
  user-select: none;
}
.priority-option:hover { border-color: var(--sage-light); background: white; }
.option--high.selected   { border-color: var(--priority-high); background: rgba(201,123,92,0.08); color: var(--priority-high); }
.option--medium.selected { border-color: var(--priority-medium); background: rgba(201,169,110,0.12); color: #8A6A2A; }
.option--low.selected    { border-color: var(--priority-low); background: rgba(122,158,135,0.1); color: var(--sage-dark); }
.option-icon { font-size: 0.75em; }

.api-error {
  background: rgba(201,123,92,0.1);
  border: 1px solid rgba(201,123,92,0.2);
  border-radius: var(--radius-md);
  padding: 0.75rem 1rem;
  font-size: 0.85rem;
  color: var(--terracotta);
  display: flex;
  gap: 0.5rem;
  align-items: flex-start;
}

.form-actions { display: flex; gap: 0.75rem; margin-top: 0.25rem; }
.btn-cancel {
  flex: 1;
  padding: 0.7rem;
  border: 1.5px solid var(--parchment);
  border-radius: var(--radius-md);
  background: transparent;
  font-family: var(--font-body);
  font-size: 0.9rem;
  color: var(--slate);
  cursor: pointer;
  transition: all 0.18s ease;
}
.btn-cancel:hover { background: var(--cream-dark); border-color: var(--sage-light); }
.btn-submit {
  flex: 2;
  padding: 0.7rem;
  border: none;
  border-radius: var(--radius-md);
  background: var(--sage);
  font-family: var(--font-body);
  font-size: 0.9rem;
  font-weight: 600;
  color: white;
  cursor: pointer;
  transition: all 0.18s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}
.btn-submit:hover:not(:disabled) { background: var(--sage-dark); transform: translateY(-1px); }
.btn-submit:disabled { opacity: 0.65; cursor: not-allowed; }

.spinner {
  width: 16px; height: 16px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
  display: inline-block;
}
@keyframes spin { to { transform: rotate(360deg); } }
</style>
