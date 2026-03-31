import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

// Configure axios base URL
const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || (import.meta.env.DEV ? '/api' : 'http://localhost:8000/api'),
  headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
})

export const useTaskStore = defineStore('tasks', () => {
  // State
  const tasks = ref([])
  const loading = ref(false)
  const error = ref(null)
  const statusFilter = ref('')
  const report = ref(null)
  const reportLoading = ref(false)

  // Computed
  const filteredTasks = computed(() => tasks.value)

  const stats = computed(() => ({
    total:      tasks.value.length,
    pending:    tasks.value.filter(t => t.status === 'pending').length,
    inProgress: tasks.value.filter(t => t.status === 'in_progress').length,
    done:       tasks.value.filter(t => t.status === 'done').length,
    high:       tasks.value.filter(t => t.priority === 'high').length,
  }))

  // Actions
  async function fetchTasks(status = '') {
    loading.value = true
    error.value = null
    try {
      const params = status ? { status } : {}
      const res = await api.get('/tasks', { params })
      tasks.value = res.data.data || []
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch tasks.'
      tasks.value = []
    } finally {
      loading.value = false
    }
  }

  async function createTask(payload) {
    loading.value = true
    error.value = null
    try {
      const res = await api.post('/tasks', payload)
      await fetchTasks(statusFilter.value)
      return { success: true, data: res.data.data }
    } catch (err) {
      const errors = err.response?.data?.errors
      const msg = errors
        ? Object.values(errors).flat().join(' ')
        : err.response?.data?.message || 'Failed to create task.'
      error.value = msg
      return { success: false, error: msg }
    } finally {
      loading.value = false
    }
  }

  async function updateTaskStatus(id, newStatus) {
    error.value = null
    try {
      const res = await api.patch(`/tasks/${id}/status`, { status: newStatus })
      // Update in place
      const idx = tasks.value.findIndex(t => t.id === id)
      if (idx !== -1) tasks.value[idx] = res.data.data
      return { success: true }
    } catch (err) {
      const msg = err.response?.data?.message || 'Failed to update status.'
      error.value = msg
      return { success: false, error: msg }
    }
  }

  async function deleteTask(id) {
    error.value = null
    try {
      await api.delete(`/tasks/${id}`)
      tasks.value = tasks.value.filter(t => t.id !== id)
      return { success: true }
    } catch (err) {
      const msg = err.response?.data?.message || 'Failed to delete task.'
      error.value = msg
      return { success: false, error: msg }
    }
  }

  async function fetchReport(date) {
    reportLoading.value = true
    error.value = null
    try {
      const res = await api.get('/tasks/report', { params: { date } })
      report.value = res.data
      return { success: true }
    } catch (err) {
      const msg = err.response?.data?.message || 'Failed to fetch report.'
      error.value = msg
      return { success: false, error: msg }
    } finally {
      reportLoading.value = false
    }
  }

  function setFilter(status) {
    statusFilter.value = status
    fetchTasks(status)
  }

  function clearError() {
    error.value = null
  }

  return {
    tasks, loading, error, statusFilter, report, reportLoading,
    filteredTasks, stats,
    fetchTasks, createTask, updateTaskStatus, deleteTask, fetchReport,
    setFilter, clearError,
  }
})
