<template>
  <div class="master-sidebar" :class="{ 'master-sidebar--open': isOpen }">
    <div class="sidebar-header">
      <div class="sidebar-logo-wrapper">
        <router-link to="/master_admin/dashboard">
          <img :src="logoUrl" alt="Logo" class="sidebar-logo-img" style="max-height: 40px; width: auto;" @error="onLogoError">
        </router-link>
      </div>
      <button
        type="button"
        class="tw-absolute tw-right-3 tw-top-3 tw-w-10 tw-h-10 tw-rounded-xl tw-border tw-border-white/10 tw-bg-white/5 tw-text-white"
        @click="$emit('close')"
        aria-label="Close sidebar"
      >
        <i class="fas fa-times"></i>
      </button>
    </div>

    <div class="sidebar-content">
      <div v-for="(group, gIdx) in menuGroups" :key="gIdx" class="menu-group">
        <div class="menu-group-label">{{ group.label }}</div>
        <ul class="sidebar-menu">
          <li v-for="(item, iIdx) in group.items" :key="iIdx" :class="{ active: isActive(item.path) }">
            <router-link :to="item.path" @click="handleNavClick">
              <i :class="item.icon"></i>
              <span>{{ item.title }}</span>
            </router-link>
          </li>
        </ul>
      </div>

      <!-- Logout (Outside Groups) -->
      <ul class="sidebar-menu mt-auto">
        <li>
          <a href="#" @click.prevent="handleLogout">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
          </a>
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../services/api'

export default {
  name: 'MasterAdminSidebar',
  props: {
    isOpen: { type: Boolean, default: false }
  },
  emits: ['close'],
  setup(props, { emit }) {
    const route = useRoute()
    const router = useRouter()
    
    const isActive = (path) => route.path === path || (path !== '/master_admin/dashboard' && route.path.startsWith(path + '/'))
    const logoUrl = ref('/assets/images/logo_icon/logo.png?v=' + new Date().getTime())
    
    const onLogoError = () => {
      // detailed error handling or fallback if needed
    }

    const admin = ref(JSON.parse(localStorage.getItem('admin_user') || '{}'))
    const perms = computed(() => admin.value.permissions || {})
    const isSuper = computed(() => admin.value.is_super_admin)

    const fullMenuGroups = [
      {
        label: 'Dashboard',
        items: [
          { title: 'Home', path: '/master_admin/dashboard', icon: 'fas fa-home', perm: 'view_reports' },
          { title: 'Account Ledger', path: '/master_admin/account-ledger', icon: 'fas fa-book-open', perm: 'view_ledger' },
        ]
      },
      {
        label: 'User Management',
        items: [
          { title: 'All Users', path: '/master_admin/users', icon: 'fas fa-users', perm: 'view_users' },
          { title: 'KYC Management', path: '/master_admin/kyc', icon: 'fas fa-id-card', perm: 'view_users' },
          { title: 'Admins', path: '/master_admin/admins', icon: 'fas fa-user-shield', superOnly: true },
        ]
      },
      {
        label: 'Financials',
        items: [
          { title: 'All Orders', path: '/master_admin/orders', icon: 'fas fa-receipt', perm: 'view_orders' },
          { title: 'Deposits', path: '/master_admin/deposits', icon: 'fas fa-arrow-down', perm: 'view_deposits' },
          { title: 'Withdrawals', path: '/master_admin/withdrawals', icon: 'fas fa-arrow-up', perm: 'view_withdrawals' },
          { title: 'Transactions', path: '/master_admin/transactions', icon: 'fas fa-exchange-alt', perm: 'view_transactions' },
          { title: 'Commissions', path: '/master_admin/commissions', icon: 'fas fa-coins', superOnly: true },
          { title: 'Support Tickets', path: '/master_admin/customer-support', icon: 'fas fa-headset', superOnly: true },
        ]
      },
      {
        label: 'Platform Assets',
        items: [
          { title: 'Gateways', path: '/master_admin/gateways', icon: 'fas fa-credit-card', superOnly: true },
          { title: 'Special Links', path: '/master_admin/special-links', icon: 'fas fa-tag', superOnly: true },
          { title: 'Courses & Videos', path: '/master_admin/courses', icon: 'fas fa-play-circle', superOnly: true },
        ]
      },
      {
        label: 'System',
        items: [
          { title: 'Settings', path: '/master_admin/settings', icon: 'fas fa-cog', superOnly: true },
          { title: 'Email Settings', path: '/master_admin/email-settings', icon: 'fas fa-envelope', superOnly: true },
        ]
      }
    ]

    const menuGroups = computed(() => {
      // Safety: If no admin data, default to showing nothing (but if it's the first time, show all for Super)
      if (!admin.value || !admin.value.id) return fullMenuGroups

      return fullMenuGroups.map(group => {
        const filteredItems = group.items.filter(item => {
          if (isSuper.value) return true
          if (item.superOnly) return false
          if (item.perm) return perms.value[item.perm]
          return true
        })
        return { ...group, items: filteredItems }
      }).filter(group => group.items.length > 0)
    })

    const handleLogout = async () => {
      try { await api.post('/admin/logout') } catch (e) {}
      localStorage.removeItem('admin_token')
      router.push('/master_admin/login')
    }
    
    const handleNavClick = () => {
      if (typeof window !== 'undefined') {
        if (window.innerWidth <= 1199 || document.body.classList.contains('master-is-mobile')) {
          emit('close')
        }
      }
    }
    
    return { isActive, handleLogout, handleNavClick, logoUrl, onLogoError, route, menuGroups }
  }
}
</script>

<style scoped>
.master-sidebar {
  position: fixed;
  left: 0;
  top: 0;
  width: 240px;
  height: 100vh;
  background: rgba(0, 0, 0, 0.85); /* Darker black background */
  backdrop-filter: blur(20px) saturate(180%);
  -webkit-backdrop-filter: blur(20px) saturate(180%);
  color: white;
  z-index: 1000;
  overflow-y: auto;
  overflow-x: hidden;
  border-right: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 4px 0 24px rgba(0, 0, 0, 0.15);
  transition: transform 0.28s cubic-bezier(0.4, 0, 0.2, 1);
  /* Visible by default on desktop */
  transform: translateX(0);
}

@media (max-width: 1199px) {
  .master-sidebar { transform: translateX(-100%); }
  .master-sidebar.master-sidebar--open { transform: translateX(0) !important; }
}

.master-sidebar::-webkit-scrollbar {
  width: 6px;
}

.master-sidebar::-webkit-scrollbar-track {
  background: transparent;
}

.master-sidebar::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 3px;
}

.master-sidebar::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.2);
}

.sidebar-header { 
  padding: 24px 20px; 
  border-bottom: 1px solid rgba(255, 255, 255, 0.08); 
  background: rgba(0, 0, 0, 0.5); /* Darker header */
  backdrop-filter: blur(10px);
  position: relative;
}

.sidebar-logo-wrapper {
  background: rgba(255, 255, 255, 0.05); /* Lighter glass effect */
  backdrop-filter: blur(10px);
  border-radius: 14px;
  padding: 18px 22px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2); /* Black shadow */
  border: 1px solid rgba(255, 255, 255, 0.1); /* Subtle border */
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.sidebar-logo-wrapper:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
  background: rgba(255, 255, 255, 0.08);
}

.sidebar-logo { 
  font-size: 24px; 
  font-weight: 800; 
  margin: 0; 
  background: linear-gradient(135deg, #ffffff 0%, #cbd5e1 100%); /* Simpler white/silver gradient */
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  letter-spacing: 1px;
  line-height: 1.2;
}

.sidebar-menu { 
  list-style: none; 
  padding: 0; 
  margin: 0; 
}

.menu-group {
  margin-top: 1.5rem;
  padding: 0 12px;
}

.menu-group-label {
  padding: 0 16px;
  margin-bottom: 0.5rem;
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  color: rgba(255, 255, 255, 0.35);
  letter-spacing: 0.05em;
}

.sidebar-menu li {
  margin: 4px 0;
}

.sidebar-menu li a {
  display: flex;
  align-items: center;
  padding: 12px 16px;
  color: rgba(255, 255, 255, 0.6); /* Slightly darker inactive text */
  text-decoration: none;
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  border-radius: 12px;
  position: relative;
  font-size: 0.9rem;
  font-weight: 500;
}

.sidebar-menu li a::before {
  content: '';
  position: absolute;
  left: 0;
  top: 50%;
  transform: translateY(-50%);
  width: 3px;
  height: 0;
  background: white; /* White indicator */
  border-radius: 0 3px 3px 0;
  transition: height 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.sidebar-menu li a:hover { 
  background: rgba(255, 255, 255, 0.08);
  color: white;
  transform: translateX(4px);
  padding-left: 20px;
}

.sidebar-menu li a:hover::before {
  height: 60%;
}

.sidebar-menu li.active a { 
  background: rgba(255, 255, 255, 0.1); /* White glass active state */
  color: white; 
  font-weight: 600;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.15);
}

.sidebar-menu li.active a::before {
  height: 70%;
}

.sidebar-menu li a i { 
  width: 20px; 
  margin-right: 12px; 
  font-size: 0.9rem;
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex;
  align-items: center;
  justify-content: center;
}

.sidebar-menu li a:hover i {
  transform: scale(1.15);
  color: white;
}

.sidebar-menu li.active a i {
  color: white;
}

/* Logout button special styling */
.sidebar-menu li:last-child a {
  margin-top: 8px;
  color: rgba(239, 68, 68, 0.8);
}

.sidebar-menu li:last-child a:hover {
  background: rgba(239, 68, 68, 0.1);
  color: #f87171;
}

.sidebar-menu li:last-child.active a {
  background: rgba(239, 68, 68, 0.15);
  border-color: rgba(239, 68, 68, 0.3);
}
</style>

<style>
/* JS Fallback for Desktop-Mode on Mobile */
body.master-is-mobile .master-sidebar {
  transform: translateX(-100%) !important;
}
body.master-is-mobile .master-sidebar.master-sidebar--open {
  transform: translateX(0) !important;
}
</style>
