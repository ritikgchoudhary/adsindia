<template>
  <div class="admin-header">
    <div class="header-left">
      <button @click="$emit('toggle-sidebar')" class="menu-toggle">
        <i class="fas fa-bars"></i>
      </button>
      <h4 class="page-title">{{ pageTitle }}</h4>
    </div>
    <div class="header-right">
      <div class="admin-info">
        <span class="admin-name">{{ adminName }}</span>
        <div class="admin-avatar">
          <i class="fas fa-user-circle"></i>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import api from '../../services/api'

export default {
  name: 'AdminHeader',
  props: {
    pageTitle: {
      type: String,
      default: 'Dashboard'
    }
  },
  setup() {
    const adminName = ref('Admin')

    onMounted(async () => {
      try {
        const response = await api.get('/admin/user')
        if (response.data.status === 'success') {
          adminName.value = response.data.data.name || 'Admin'
        }
      } catch (error) {
        console.error('Error fetching admin info:', error)
      }
    })

    return {
      adminName
    }
  }
}
</script>

<style scoped>
.admin-header {
  position: fixed;
  top: 0;
  left: 260px;
  right: 0;
  height: 70px;
  background: white;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 30px;
  z-index: 999;
  transition: left 0.3s;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 20px;
}

.menu-toggle {
  background: none;
  border: none;
  font-size: 20px;
  cursor: pointer;
  color: #667eea;
}

.page-title {
  margin: 0;
  font-size: 24px;
  font-weight: 700;
  color: #2d3748;
}

.header-right {
  display: flex;
  align-items: center;
}

.admin-info {
  display: flex;
  align-items: center;
  gap: 15px;
}

.admin-name {
  font-weight: 600;
  color: #4a5568;
}

.admin-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 24px;
}

@media (max-width: 768px) {
  .admin-header {
    left: 0;
  }
  
  .admin-name {
    display: none;
  }
}
</style>
