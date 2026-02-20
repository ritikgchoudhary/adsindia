import { ref, computed } from 'vue'

// Simple global loader (no Pinia needed)
const apiPendingCount = ref(0)
const routePendingCount = ref(0)
const pendingCount = computed(() => (apiPendingCount.value || 0) + (routePendingCount.value || 0))
const visible = computed(() => false)

let showTimer = null
const SHOW_DELAY_MS = 200

function syncStart() {
  // Global loader disabled
}

function syncStop() {
  if (pendingCount.value === 0) {
    if (showTimer) {
      clearTimeout(showTimer)
      showTimer = null
    }
  }
}

export function apiLoaderStart() {
  apiPendingCount.value = (apiPendingCount.value || 0) + 1
  syncStart()
}

export function apiLoaderStop() {
  apiPendingCount.value = Math.max(0, (apiPendingCount.value || 0) - 1)
  syncStop()
}

export function routeLoaderStart() {
  routePendingCount.value = (routePendingCount.value || 0) + 1
  syncStart()
}

export function routeLoaderStop() {
  routePendingCount.value = Math.max(0, (routePendingCount.value || 0) - 1)
  syncStop()
}

export function routeLoaderReset() {
  routePendingCount.value = 0
  syncStop()
}

export function loaderReset() {
  apiPendingCount.value = 0
  routePendingCount.value = 0
  if (showTimer) {
    clearTimeout(showTimer)
    showTimer = null
  }
}

export function useLoader() {
  return { pendingCount, visible, apiPendingCount, routePendingCount }
}

