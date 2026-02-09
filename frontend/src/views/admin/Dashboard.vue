<template>
  <AdminLayout page-title="Dashboard">
    <div class="admin-dashboard">
      <div class="row">
        <div class="col-md-3 mb-4">
          <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
              <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
              <h3>{{ stats.totalUsers || 0 }}</h3>
              <p>Total Users</p>
            </div>
          </div>
        </div>
        <div class="col-md-3 mb-4">
          <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
              <i class="fas fa-graduation-cap"></i>
            </div>
            <div class="stat-content">
              <h3>{{ stats.totalCourses || 0 }}</h3>
              <p>Total Courses</p>
            </div>
          </div>
        </div>
        <div class="col-md-3 mb-4">
          <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
              <i class="fas fa-briefcase"></i>
            </div>
            <div class="stat-content">
              <h3>{{ stats.totalCampaigns || 0 }}</h3>
              <p>Total Campaigns</p>
            </div>
          </div>
        </div>
        <div class="col-md-3 mb-4">
          <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
              <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="stat-content">
              <h3>₹{{ formatAmount(stats.totalRevenue || 0) }}</h3>
              <p>Total Revenue</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script>
import { ref, onMounted } from 'vue'
import AdminLayout from '../../components/admin/AdminLayout.vue'
import api from '../../services/api'

export default {
  name: 'AdminDashboard',
  components: {
    AdminLayout
  },
  setup() {
    const stats = ref({
      totalUsers: 0,
      totalCourses: 0,
      totalCampaigns: 0,
      totalRevenue: 0
    })

    const formatAmount = (amount) => {
      return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    }

    onMounted(async () => {
      try {
        const response = await api.get('/admin/dashboard')
        if (response.data.status === 'success') {
          stats.value = response.data.data || stats.value
        }
      } catch (error) {
        console.error('Error fetching dashboard stats:', error)
      }
    })

    return {
      stats,
      formatAmount
    }
  }
}
</script>

<style scoped>
.admin-dashboard {
  padding: 20px 0;
}

.stat-card {
  background: white;
  border-radius: 15px;
  padding: 25px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  display: flex;
  align-items: center;
  gap: 20px;
  transition: transform 0.3s;
}

.stat-card:hover {
  transform: translateY(-5px);
}

.stat-icon {
  width: 70px;
  height: 70px;
  border-radius: 15px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 30px;
}

.stat-content h3 {
  margin: 0;
  font-size: 32px;
  font-weight: 700;
  color: #2d3748;
}

.stat-content p {
  margin: 5px 0 0 0;
  color: #718096;
  font-size: 14px;
}
</style>
