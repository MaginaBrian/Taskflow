import { createRouter, createWebHistory } from 'vue-router'
import TasksView from './views/TasksView.vue'
import ReportView from './views/ReportView.vue'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/',       component: TasksView, name: 'tasks'  },
    { path: '/report', component: ReportView, name: 'report' },
  ],
})

export default router
