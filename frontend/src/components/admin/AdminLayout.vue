<template>
  <div class="admin-layout">
    <AdminSidebar />
    <div class="admin-content">
      <AdminHeader :page-title="pageTitle" />
      <div class="admin-main-content">
        <slot />
      </div>
    </div>
  </div>
</template>

<script>
import { defineComponent } from 'vue'
import AdminSidebar from './AdminSidebar.vue'
import AdminHeader from './AdminHeader.vue'

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

.admin-main-content {
  padding: 20px;
  margin-top: 70px;
}

@media (max-width: 768px) {
  .admin-content {
    margin-left: 0;
  }
}
</style>
