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
        class="tw-absolute tw-right-3 tw-top-3 tw-w-10 tw-h-10 tw-rounded-xl tw-border tw-border-white/10 tw-bg-white/5 tw-text-white md:tw-hidden"
        @click="$emit('close')"
        aria-label="Close sidebar"
      >
        <i class="fas fa-times"></i>
      </button>
    </div>
    <ul class="sidebar-menu">
      <li :class="{ active: isActive('/master_admin/dashboard') }"><router-link to="/master_admin/dashboard"><i class="fas fa-home"></i><span>Dashboard</span></router-link></li>
      <li :class="{ active: isActive('/master_admin/users') }"><router-link to="/master_admin/users"><i class="fas fa-users"></i><span>All Users</span></router-link></li>
      <li :class="{ active: isActive('/master_admin/orders') }"><router-link to="/master_admin/orders"><i class="fas fa-receipt"></i><span>All Orders</span></router-link></li>
      <li :class="{ active: isActive('/master_admin/deposits') }"><router-link to="/master_admin/deposits"><i class="fas fa-arrow-down"></i><span>Deposits</span></router-link></li>
      <li :class="{ active: isActive('/master_admin/withdrawals') }"><router-link to="/master_admin/withdrawals"><i class="fas fa-arrow-up"></i><span>Withdrawals</span></router-link></li>
      <li :class="{ active: isActive('/master_admin/transactions') }"><router-link to="/master_admin/transactions"><i class="fas fa-exchange-alt"></i><span>Transactions</span></router-link></li>
      <li :class="{ active: isActive('/master_admin/commissions') }"><router-link to="/master_admin/commissions"><i class="fas fa-coins"></i><span>Commission Management</span></router-link></li>
      <li :class="{ active: isActive('/master_admin/special-links') }"><router-link to="/master_admin/special-links"><i class="fas fa-tag"></i><span>Special Discount Links</span></router-link></li>
      <li :class="{ active: isActive('/master_admin/kyc') }"><router-link to="/master_admin/kyc"><i class="fas fa-id-card"></i><span>KYC Management</span></router-link></li>
      <li :class="{ active: isActive('/master_admin/reports') }"><router-link to="/master_admin/reports"><i class="fas fa-chart-bar"></i><span>Reports</span></router-link></li>
      <li :class="{ active: isActive('/master_admin/courses') }"><router-link to="/master_admin/courses"><i class="fas fa-play-circle"></i><span>Courses & Videos</span></router-link></li>
      <li :class="{ active: isActive('/master_admin/admins') }"><router-link to="/master_admin/admins"><i class="fas fa-user-shield"></i><span>Admins</span></router-link></li>
      <li :class="{ active: isActive('/master_admin/settings') }"><router-link to="/master_admin/settings"><i class="fas fa-cog"></i><span>Settings</span></router-link></li>
      <li :class="{ active: isActive('/master_admin/customer-support') }"><router-link to="/master_admin/customer-support"><i class="fas fa-headset"></i><span>Customer Support</span></router-link></li>
      <li><a href="#" @click.prevent="handleLogout"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></li>
    </ul>
  </div>
</template>

<script>
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../services/api'

export default {
  name: 'MasterAdminSidebar',
  props: {
    isOpen: { type: Boolean, default: false }
  },
  emits: ['close'],
  setup() {
    const route = useRoute()
    const router = useRouter()
    
    const isActive = (path) => route.path === path || route.path.startsWith(path + '/')
    const logoUrl = ref('/assets/images/logo_icon/logo.png?v=' + new Date().getTime())
    
    const onLogoError = () => {
      // detailed error handling or fallback if needed
      // for now, we keep it simple or maybe verify the path
    }

    const handleLogout = async () => {
      try { await api.post('/admin/logout') } catch (e) {}
      localStorage.removeItem('admin_token')
      router.push('/master_admin/login')
    }
    
    return { isActive, handleLogout, logoUrl, onLogoError }
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
}

@media (max-width: 768px) {
  .master-sidebar { transform: translateX(-100%); }
  .master-sidebar.master-sidebar--open { transform: translateX(0); }
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
  padding: 12px 0; 
  margin: 0; 
}

.sidebar-menu li {
  margin: 4px 12px;
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
