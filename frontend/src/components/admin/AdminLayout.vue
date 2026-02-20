<template>
  <div class="admin-layout">
    <AdminSidebar
      :collapsed="sidebarCollapsed"
      :mobile-open="sidebarMobileOpen"
      @toggle="toggleSidebar"
      @close-mobile="sidebarMobileOpen = false"
    />
    <div class="admin-content" :class="{ collapsed: sidebarCollapsed }">
      <AdminHeader
        :page-title="pageTitle"
        :sidebar-collapsed="sidebarCollapsed"
        @toggle-sidebar="toggleSidebar"
      />
      <div class="admin-main-content">
        <slot />
      </div>
    </div>

    <!-- Mobile overlay -->
    <div
      v-if="sidebarMobileOpen"
      class="tw-fixed tw-inset-0 tw-bg-black/50 tw-backdrop-blur-sm tw-z-[900] md:tw-hidden"
      @click="sidebarMobileOpen = false"
    ></div>
  </div>
</template>

<script>
import { defineComponent, ref, watch } from 'vue'
import AdminSidebar from './AdminSidebar.vue'
import AdminHeader from './AdminHeader.vue'
import { useRoute } from 'vue-router'

export default defineComponent({
  name: 'AdminLayout',
  components: {
    AdminSidebar,
    AdminHeader
  },
  props: {
    pageTitle: {
      type: String,
      default: 'Dashboard'
    }
  },
  setup() {
    const sidebarCollapsed = ref(false)
    const sidebarMobileOpen = ref(false)
    const route = useRoute()

    const isMobile = () => {
      if (typeof window === 'undefined') return false
      return window.matchMedia && window.matchMedia('(max-width: 768px)').matches
    }

    const toggleSidebar = () => {
      if (isMobile()) {
        sidebarMobileOpen.value = !sidebarMobileOpen.value
      } else {
        sidebarCollapsed.value = !sidebarCollapsed.value
      }
    }

    watch(() => route.path, () => {
      sidebarMobileOpen.value = false
    })

    return { sidebarCollapsed, sidebarMobileOpen, toggleSidebar }
  }
})
</script>

<style scoped>
.admin-layout {
  display: flex;
  min-height: 100vh;
  background: #f5f7fa;
}

.admin-content {
  flex: 1;
  margin-left: 260px;
  transition: margin-left 0.3s;
}

.admin-content.collapsed {
  margin-left: 70px;
}

.admin-main-content {
  padding: 20px;
  margin-top: 70px;
}

@media (max-width: 768px) {
  .admin-content {
    margin-left: 0;
  }
  .admin-content.collapsed {
    margin-left: 0;
  }
}
</style>
