<template>
  <div class="master-header">
    <div class="header-left">
      <button
        type="button"
        class="ma-mobile-toggle tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-rounded-xl tw-border tw-border-white/10 tw-bg-white/5 tw-text-white tw-mr-3"
        @click="$emit('toggle-sidebar')"
        aria-label="Toggle sidebar"
      >
        <i class="fas fa-bars"></i>
      </button>
      <h4 class="page-title">{{ pageTitle }}</h4>
    </div>
    <div class="header-right">
      <div class="admin-info">
        <span class="admin-name">{{ adminName }}</span>
        <div class="admin-avatar">
          <i class="fas fa-user-shield"></i>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import api from '../../services/api'

export default {
  name: 'MasterAdminHeader',
  props: { pageTitle: { type: String, default: 'Dashboard' } },
  emits: ['toggle-sidebar'],
  setup() {
    const adminName = ref('Master Admin')
    onMounted(async () => {
      try {
        const res = await api.get('/admin/user')
        if (res.data?.status === 'success')
          adminName.value = res.data.data?.name || res.data.data?.username || 'Master Admin'
      } catch (e) {}
    })
    return { adminName }
  }
}
</script>

<style scoped>
.master-header {
  position: sticky;
  top: 0;
  left: 0;
  width: 100%;
  height: 72px;
  background: rgba(0, 0, 0, 0.5); /* Black glass header */
  backdrop-filter: blur(20px) saturate(180%);
  -webkit-backdrop-filter: blur(20px) saturate(180%);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 32px;
  z-index: 999;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
}

.page-title { 
  margin: 0; 
  font-size: 1.5rem; 
  font-weight: 700; 
  color: #f1f5f9;
  letter-spacing: -0.02em;
  background: linear-gradient(135deg, #f1f5f9 0%, #cbd5e1 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.admin-info { 
  display: flex; 
  align-items: center; 
  gap: 14px; 
}

.admin-name { 
  font-weight: 600; 
  color: rgba(255, 255, 255, 0.8); 
  font-size: 0.9rem;
  padding: 8px 16px;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 10px;
  border: 1px solid rgba(255, 255, 255, 0.08);
  transition: all 0.2s;
}

.admin-name:hover {
  background: rgba(255, 255, 255, 0.08);
  color: rgba(255, 255, 255, 0.95);
}

.admin-avatar {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.1);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.1rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  border: 2px solid rgba(255, 255, 255, 0.1);
}

.admin-avatar:hover {
  transform: translateY(-2px) scale(1.05);
  box-shadow: 0 6px 16px rgba(255, 255, 255, 0.1);
}

@media (min-width: 1200px) {
  .ma-mobile-toggle {
    display: none !important;
  }
}

@media (max-width: 1199px) {
  .master-header { padding: 0 20px !important; }
  .admin-name { display: none !important; }
}
</style>

<style>
/* JS Fallback for Desktop-Mode on Mobile */
body.master-is-mobile .ma-mobile-toggle {
  display: inline-flex !important;
}

body.master-is-mobile .master-header { 
  padding: 0 20px !important;
}

body.master-is-mobile .admin-name { 
  display: none !important; 
}
</style>
