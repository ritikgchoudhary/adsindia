<template>
  <div class="master-admin-layout">
    <MasterAdminSidebar :is-open="sidebarOpen" @close="sidebarOpen = false" />
    <div class="master-content">
      <MasterAdminHeader :page-title="pageTitle" @toggle-sidebar="toggleSidebar" />
      <div class="master-main-content">
        <slot />
      </div>
    </div>

    <!-- Mobile overlay -->
    <div
      v-if="sidebarOpen"
      class="tw-fixed tw-inset-0 tw-bg-black/60 tw-backdrop-blur-sm tw-z-[900]"
      @click="sidebarOpen = false"
    ></div>
  </div>
</template>

<script>
import MasterAdminSidebar from './MasterAdminSidebar.vue'
import MasterAdminHeader from './MasterAdminHeader.vue'
import { ref, watch, onMounted, onBeforeUnmount } from 'vue'
import { useRoute } from 'vue-router'

export default {
  name: 'MasterAdminLayout',
  components: {
    MasterAdminSidebar,
    MasterAdminHeader
  },
  props: {
    pageTitle: {
      type: String,
      default: 'Dashboard'
    }
  },
  setup() {
    const route = useRoute()
    const sidebarOpen = ref(false)

    const applyBodyClass = (open) => {
      if (typeof document === 'undefined') return
      document.body.classList.toggle('master-admin-sidebar-open', !!open)
    }

    const checkMobile = () => {
      if (typeof window === 'undefined') return
      const ua = navigator.userAgent.toLowerCase()
      const isMobileAgent = /android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini/i.test(ua)
      if (isMobileAgent || window.innerWidth <= 1199) {
        document.body.classList.add('master-is-mobile')
      } else {
        document.body.classList.remove('master-is-mobile')
      }
    }

    const toggleSidebar = () => {
      sidebarOpen.value = !sidebarOpen.value
    }

    onMounted(() => {
      applyBodyClass(sidebarOpen.value)
      checkMobile()
      window.addEventListener('resize', checkMobile)
    })
    
    watch(sidebarOpen, (v) => applyBodyClass(v))
    
    onBeforeUnmount(() => {
      applyBodyClass(false)
      if (typeof document !== 'undefined') document.body.classList.remove('master-is-mobile')
      if (typeof window !== 'undefined') window.removeEventListener('resize', checkMobile)
    })

    // Close sidebar on navigation (mobile UX)
    watch(() => route.path, () => {
      if (typeof document !== 'undefined' && document.body.classList.contains('master-is-mobile')) {
        sidebarOpen.value = false
      }
    })

    return { sidebarOpen, toggleSidebar }
  }
}
</script>

<style scoped>
.master-admin-layout {
  display: flex;
  min-height: 100vh;
  background: transparent;
  position: relative;
  min-width: max-content;
}

.master-content {
  flex: 1;
  margin-left: 240px; 
  transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  min-height: 100vh;
  position: relative;
  z-index: 1;
  min-width: max-content;
}

.master-main-content {
  padding: 32px;
  margin-top: 72px;
}

@media (max-width: 1199px) {
  .master-content { margin-left: 0; }
}
</style>

<style>
/* Global fix for Master Admin tables to prevent vertical text stacking */
.master-admin-layout table th,
.master-admin-layout table td {
  white-space: nowrap !important;
}

/* Ensure browser allows horizontal scroll */
html, body {
  overflow-x: auto !important;
}

/* JS Fallback for Desktop-Mode on Mobile */
body.master-is-mobile .master-content {
  margin-left: 0 !important;
}
body.master-is-mobile .master-main-content {
  padding: 16px !important;
}
</style>
