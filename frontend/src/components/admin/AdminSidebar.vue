<template>
  <div class="admin-sidebar" :class="{ collapsed: isCollapsed }">
    <div class="sidebar-header">
      <h3 class="sidebar-logo">Admin Panel</h3>
      <button @click="toggleSidebar" class="toggle-btn">
        <i class="fas fa-bars"></i>
      </button>
    </div>
    <ul class="sidebar-menu">
      <li :class="{ active: isActive('/admin/dashboard') }">
        <router-link to="/admin/dashboard">
          <i class="fas fa-home"></i>
          <span>Dashboard</span>
        </router-link>
      </li>
      <li :class="{ active: isActive('/admin/courses') }">
        <router-link to="/admin/courses">
          <i class="fas fa-graduation-cap"></i>
          <span>Courses</span>
        </router-link>
      </li>
      <li :class="{ active: isActive('/admin/users') }">
        <router-link to="/admin/users">
          <i class="fas fa-users"></i>
          <span>Users</span>
        </router-link>
      </li>
      <li :class="{ active: isActive('/admin/campaigns') }">
        <router-link to="/admin/campaigns">
          <i class="fas fa-briefcase"></i>
          <span>Campaigns</span>
        </router-link>
      </li>
      <li :class="{ active: isActive('/admin/categories') }">
        <router-link to="/admin/categories">
          <i class="fas fa-list"></i>
          <span>Categories</span>
        </router-link>
      </li>
      <li :class="{ active: isActive('/admin/withdrawals') }">
        <router-link to="/admin/withdrawals">
          <i class="fas fa-money-bill-wave"></i>
          <span>Withdrawals</span>
        </router-link>
      </li>
      <li :class="{ active: isActive('/admin/settings') }">
        <router-link to="/admin/settings">
          <i class="fas fa-cog"></i>
          <span>Settings</span>
        </router-link>
      </li>
      <li>
        <a href="#" @click.prevent="handleLogout">
          <i class="fas fa-sign-out-alt"></i>
          <span>Logout</span>
        </a>
      </li>
    </ul>
  </div>
</template>

<script>
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../services/api'

export default {
  name: 'AdminSidebar',
  setup() {
    const route = useRoute()
    const router = useRouter()
    const isCollapsed = ref(false)

    const isActive = (path) => {
      return route.path === path || route.path.startsWith(path + '/')
    }

    const toggleSidebar = () => {
      isCollapsed.value = !isCollapsed.value
    }

    const handleLogout = async () => {
      try {
        await api.post('/admin/logout')
      } catch (error) {
        console.error('Logout error:', error)
      } finally {
        // Always remove token and redirect, even if API call fails
        localStorage.removeItem('admin_token')
        router.push('/admin/login')
      }
    }

    return {
      isCollapsed,
      isActive,
      toggleSidebar,
      handleLogout
    }
  }
}
</script>

<style scoped>
.admin-sidebar {
  position: fixed;
  left: 0;
  top: 0;
  width: 260px;
  height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  transition: width 0.3s;
  z-index: 1000;
  overflow-y: auto;
}

.admin-sidebar.collapsed {
  width: 70px;
}

.sidebar-header {
  padding: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.sidebar-logo {
  font-size: 20px;
  font-weight: 700;
  margin: 0;
  color: white;
}

.toggle-btn {
  background: rgba(255, 255, 255, 0.1);
  border: none;
  color: white;
  padding: 8px 12px;
  border-radius: 5px;
  cursor: pointer;
}

.sidebar-menu {
  list-style: none;
  padding: 0;
  margin: 0;
}

.sidebar-menu li {
  margin: 0;
}

.sidebar-menu li a {
  display: flex;
  align-items: center;
  padding: 15px 20px;
  color: rgba(255, 255, 255, 0.9);
  text-decoration: none;
  transition: all 0.3s;
  border-left: 3px solid transparent;
}

.sidebar-menu li a:hover {
  background: rgba(255, 255, 255, 0.1);
  border-left-color: white;
}

.sidebar-menu li.active a {
  background: rgba(255, 255, 255, 0.2);
  border-left-color: white;
  font-weight: 600;
}

.sidebar-menu li a i {
  width: 25px;
  margin-right: 15px;
  font-size: 18px;
}

.admin-sidebar.collapsed .sidebar-menu li a span {
  display: none;
}

.admin-sidebar.collapsed .sidebar-logo {
  display: none;
}

@media (max-width: 768px) {
  .admin-sidebar {
    transform: translateX(-100%);
  }
  
  .admin-sidebar.collapsed {
    transform: translateX(0);
  }
}
</style>
