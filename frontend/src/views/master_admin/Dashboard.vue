<template>
  <MasterAdminLayout page-title="Dashboard">
    <div class="master-dashboard">
      <div class="row g-4">
        <div class="col-md-3">
          <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-content">
              <h3>{{ stats.totalUsers || 0 }}</h3>
              <p>Total Users</p>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-graduation-cap"></i></div>
            <div class="stat-content">
              <h3>{{ stats.totalCourses || 0 }}</h3>
              <p>Total Courses</p>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-briefcase"></i></div>
            <div class="stat-content">
              <h3>{{ stats.totalCampaigns || 0 }}</h3>
              <p>Total Campaigns</p>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <router-link to="/master_admin/kyc" class="stat-card" style="text-decoration: none;">
            <div class="stat-icon" style="border-color: #f59e0b; background: rgba(245, 158, 11, 0.2); box-shadow: 0 4px 16px rgba(245, 158, 11, 0.15);">
               <i class="fas fa-id-card" style="color: #fbbf24;"></i>
            </div>
            <div class="stat-content">
              <h3>{{ stats.kyc_pending_users || 0 }}</h3>
              <p>Pending KYC</p>
            </div>
          </router-link>
        </div>
        <div class="col-md-3">
          <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div>
            <div class="stat-content">
              <h3>₹{{ formatAmount(stats.totalRevenue || 0) }}</h3>
              <p>Total Revenue</p>
            </div>
          </div>
        </div>
      </div>
      <div class="welcome-card mt-4">
        <h5 class="mb-2">Welcome to Master Admin Panel</h5>
        <p class="text-muted mb-0">Manage admins, system settings and overview from here.</p>
      </div>
    </div>
  </MasterAdminLayout>
</template>

<script>
import { ref, onMounted } from 'vue'
import MasterAdminLayout from '../../components/master_admin/MasterAdminLayout.vue'
import api from '../../services/api'

export default {
  name: 'MasterAdminDashboard',
  components: { MasterAdminLayout },
  setup() {
    const stats = ref({ totalUsers: 0, totalCourses: 0, totalCampaigns: 0, totalRevenue: 0 })
    const formatAmount = (n) => parseFloat(n).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    onMounted(async () => {
      try {
        const res = await api.get('/admin/dashboard')
        if (res.data?.status === 'success') stats.value = res.data.data || stats.value
      } catch (e) {}
    })
    return { stats, formatAmount }
  }
}
</script>

<style scoped>
.master-dashboard { 
  padding: 0; 
  animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.stat-card {
  background: rgba(0, 0, 0, 0.4); /* Black glass background */
  backdrop-filter: blur(20px) saturate(180%);
  -webkit-backdrop-filter: blur(20px) saturate(180%);
  border: 1px solid rgba(255, 255, 255, 0.08); /* Subtle border */
  border-radius: 20px;
  padding: 28px;
  display: flex;
  align-items: center;
  gap: 20px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2); /* Deeper shadow */
}

.stat-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: linear-gradient(90deg, #6366f1 0%, #8b5cf6 50%, #a78bfa 100%);
  opacity: 0;
  transition: opacity 0.3s;
}

.stat-card:hover {
  transform: translateY(-4px);
  border-color: rgba(255, 255, 255, 0.15);
  box-shadow: 0 12px 40px rgba(99, 102, 241, 0.15);
  background: rgba(255, 255, 255, 0.03); /* Lighter on hover */
}

.stat-card:hover::before {
  opacity: 1;
}

.stat-icon {
  width: 60px;
  height: 60px;
  border-radius: 16px;
  background: rgba(255, 255, 255, 0.05); /* Subtle icon background */
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #f1f5f9; /* White icon */
  font-size: 1.5rem;
  flex-shrink: 0;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
}

.stat-card:hover .stat-icon {
  transform: scale(1.1) rotate(5deg);
  background: rgba(255, 255, 255, 0.1);
  box-shadow: 0 6px 20px rgba(255, 255, 255, 0.05);
}

.stat-content {
  flex: 1;
  min-width: 0;
}

.stat-content h3 { 
  margin: 0 0 6px 0; 
  font-size: 2rem; 
  font-weight: 800; 
  color: #f1f5f9;
  letter-spacing: -0.02em;
  line-height: 1.2;
}

.stat-content p { 
  margin: 0; 
  color: rgba(255, 255, 255, 0.5); 
  font-size: 0.875rem;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.welcome-card {
  background: rgba(0, 0, 0, 0.3); /* Black glass */
  backdrop-filter: blur(20px) saturate(180%);
  -webkit-backdrop-filter: blur(20px) saturate(180%);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 20px;
  padding: 32px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
  transition: all 0.3s;
}

.welcome-card:hover {
  border-color: rgba(255, 255, 255, 0.15);
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.25);
}

.welcome-card h5 { 
  color: #f1f5f9; 
  font-weight: 700;
  font-size: 1.25rem;
  margin-bottom: 8px;
  letter-spacing: -0.01em;
}

.welcome-card .text-muted { 
  color: rgba(255, 255, 255, 0.5) !important;
  font-size: 0.95rem;
  line-height: 1.6;
}

@media (max-width: 768px) {
  .stat-card {
    padding: 20px;
    gap: 16px;
  }
  
  .stat-icon {
    width: 50px;
    height: 50px;
    font-size: 1.25rem;
  }
  
  .stat-content h3 {
    font-size: 1.5rem;
  }
  
  .welcome-card {
    padding: 24px;
  }
}
</style>
