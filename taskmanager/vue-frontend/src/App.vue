<template>
  <div class="app-wrapper">
    <!-- Decorative background shapes -->
    <div class="bg-decor bg-decor--1" aria-hidden="true"></div>
    <div class="bg-decor bg-decor--2" aria-hidden="true"></div>
    <div class="bg-decor bg-decor--3" aria-hidden="true"></div>

    <header class="site-header">
      <div class="header-inner">
        <div class="logo">
          <span class="logo-icon">⟡</span>
          <span class="logo-text">TaskFlow</span>
        </div>
        <nav class="site-nav">
          <RouterLink to="/" class="nav-link" :class="{ active: $route.name === 'tasks' }">
            <span class="nav-icon">◈</span> Tasks
          </RouterLink>
          <RouterLink to="/report" class="nav-link" :class="{ active: $route.name === 'report' }">
            <span class="nav-icon">◇</span> Report
          </RouterLink>
        </nav>
        <div class="header-date">{{ today }}</div>
      </div>
    </header>

    <main class="site-main">
      <RouterView v-slot="{ Component }">
        <Transition name="fade" mode="out-in">
          <component :is="Component" />
        </Transition>
      </RouterView>
    </main>

    <footer class="site-footer">
      <span>TaskFlow</span>
    </footer>
  </div>
</template>

<script setup>
import { computed } from 'vue'
const today = computed(() =>
  new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })
)
</script>

<style scoped>
.app-wrapper {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  position: relative;
  overflow-x: hidden;
}

/* Background decorative blobs */
.bg-decor {
  position: fixed;
  border-radius: 50%;
  pointer-events: none;
  z-index: 0;
}
.bg-decor--1 {
  width: 480px; height: 480px;
  background: radial-gradient(circle, rgba(168,197,180,0.22) 0%, transparent 70%);
  top: -120px; left: -100px;
  animation: float1 18s ease-in-out infinite;
}
.bg-decor--2 {
  width: 360px; height: 360px;
  background: radial-gradient(circle, rgba(201,169,110,0.15) 0%, transparent 70%);
  bottom: 80px; right: -80px;
  animation: float2 22s ease-in-out infinite;
}
.bg-decor--3 {
  width: 200px; height: 200px;
  background: radial-gradient(circle, rgba(212,146,154,0.12) 0%, transparent 70%);
  top: 50%; left: 55%;
  animation: float3 28s ease-in-out infinite;
}
@keyframes float1 { 0%,100%{transform:translate(0,0)} 50%{transform:translate(20px,30px)} }
@keyframes float2 { 0%,100%{transform:translate(0,0)} 50%{transform:translate(-25px,-20px)} }
@keyframes float3 { 0%,100%{transform:translate(0,0)} 33%{transform:translate(15px,-25px)} 66%{transform:translate(-10px,15px)} }

/* Header */
.site-header {
  position: sticky;
  top: 0;
  z-index: 100;
  background: rgba(250,247,242,0.88);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border-bottom: 1px solid rgba(122,158,135,0.15);
}
.header-inner {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
  height: 68px;
  display: flex;
  align-items: center;
  gap: 2rem;
}
.logo {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  text-decoration: none;
}
.logo-icon {
  font-size: 1.6rem;
  color: var(--sage);
  line-height: 1;
}
.logo-text {
  font-family: var(--font-display);
  font-size: 1.4rem;
  color: var(--charcoal);
  letter-spacing: -0.01em;
}
.site-nav {
  display: flex;
  gap: 0.5rem;
  margin-left: 1rem;
}
.nav-link {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.45rem 1rem;
  border-radius: 100px;
  text-decoration: none;
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--slate);
  transition: all 0.2s ease;
  letter-spacing: 0.01em;
}
.nav-link:hover {
  background: var(--cream-dark);
  color: var(--charcoal);
}
.nav-link.active {
  background: var(--sage);
  color: white;
}
.nav-icon {
  font-size: 0.8rem;
  opacity: 0.8;
}
.header-date {
  margin-left: auto;
  font-size: 0.8rem;
  color: var(--slate-light);
  font-style: italic;
}

/* Main */
.site-main {
  flex: 1;
  position: relative;
  z-index: 1;
}

/* Footer */
.site-footer {
  text-align: center;
  padding: 1.5rem;
  font-size: 0.75rem;
  color: var(--slate-light);
  border-top: 1px solid rgba(122,158,135,0.1);
  position: relative;
  z-index: 1;
  letter-spacing: 0.02em;
}
</style>
