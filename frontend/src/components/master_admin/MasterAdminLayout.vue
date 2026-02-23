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
      class="tw-fixed tw-inset-0 tw-bg-black/60 tw-backdrop-blur-sm tw-z-[900] md:tw-hidden"
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

    const toggleSidebar = () => {
      sidebarOpen.value = !sidebarOpen.value
    }

    onMounted(() => applyBodyClass(sidebarOpen.value))
    watch(sidebarOpen, (v) => applyBodyClass(v))
    onBeforeUnmount(() => applyBodyClass(false))

    // Close sidebar on navigation (mobile UX)
    watch(() => route.path, () => {
      sidebarOpen.value = false
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
  width: 100%;
}

.master-content {
  flex: 1;
  margin-left: 240px;
  transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  min-height: 100vh;
  position: relative;
  z-index: 1;
}

.master-main-content {
  padding: 32px;
  margin-top: 72px;
}

@media (max-width: 768px) {
  .master-content {
    margin-left: 0;
  }
  
  .master-main-content {
    padding: 20px;
    margin-top: 72px;
  }
}
</style>
