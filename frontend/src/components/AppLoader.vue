<template>
  <transition name="app-loader-fade">
    <div v-if="visible" class="app-loader-overlay" aria-live="polite" aria-busy="true">
      <div class="app-loader-only" role="status" aria-label="Loading">
        <div class="app-loader-spinner" aria-hidden="true"></div>
      </div>
    </div>
  </transition>
</template>

<script>
import { computed } from 'vue'
import { useLoader } from '../services/loader'

export default {
  name: 'AppLoader',
  setup() {
    const { visible } = useLoader()
    // normalize ref to computed for template stability
    const isVisible = computed(() => !!visible.value)
    return { visible: isVisible }
  }
}
</script>

<style scoped>
.app-loader-overlay{
  position: fixed;
  inset: 0;
  z-index: 9999;
  background: rgba(2, 6, 23, 0.55);
  backdrop-filter: blur(6px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
}

.app-loader-only{
  display: flex;
  align-items: center;
  justify-content: center;
  user-select: none;
}

.app-loader-spinner{
  width: 44px;
  height: 44px;
  border-radius: 999px;
  border: 4px solid rgba(255,255,255,0.18);
  border-top-color: rgba(255,255,255,0.92);
  animation: spin 0.9s linear infinite;
}

@keyframes spin{
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

@media (prefers-reduced-motion: reduce){
  .app-loader-spinner{ animation: none; }
}

.app-loader-fade-enter-active,
.app-loader-fade-leave-active{
  transition: opacity 0.14s ease;
}
.app-loader-fade-enter-from,
.app-loader-fade-leave-to{
  opacity: 0;
}
</style>

