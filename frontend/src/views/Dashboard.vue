<template>
  <div class="dashboard">
    <header class="header">
      <nav class="navbar">
        <div class="container">
          <div class="nav-brand">
            <router-link to="/">A22.com</router-link>
          </div>
          <div class="nav-menu">
            <router-link to="/campaigns">Campaigns</router-link>
            <button @click="handleLogout" class="btn-logout">Logout</button>
          </div>
        </div>
      </nav>
    </header>

    <main class="main-content">
      <div class="container">
        <h1>Dashboard</h1>
        <div class="dashboard-stats">
          <div class="stat-card">
            <h3>Total Campaigns</h3>
            <p>{{ stats.totalCampaigns || 0 }}</p>
          </div>
          <div class="stat-card">
            <h3>Total Conversions</h3>
            <p>{{ stats.totalConversions || 0 }}</p>
          </div>
          <div class="stat-card">
            <h3>Total Earnings</h3>
            <p>${{ stats.totalEarnings || 0 }}</p>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { authService } from '../services/authService'
import api from '../services/api'

export default {
  name: 'Dashboard',
  setup() {
    const router = useRouter()
    const stats = ref({})

    const handleLogout = async () => {
      try {
        await authService.logout()
        router.push('/')
      } catch (error) {
        console.error('Logout error:', error)
      }
    }

    const fetchDashboardData = async () => {
      try {
        const response = await api.get('/dashboard')
        // Handle dashboard data
      } catch (error) {
        console.error('Dashboard error:', error)
      }
    }

    onMounted(() => {
      fetchDashboardData()
    })

    return {
      stats,
      handleLogout
    }
  }
}
</script>

<style scoped>
.dashboard {
  min-height: 100vh;
  background: #f5f5f5;
}

.header {
  background: #fff;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.navbar {
  padding: 1rem 0;
}

.navbar .container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.nav-brand a {
  font-size: 1.5rem;
  font-weight: bold;
  text-decoration: none;
  color: #333;
}

.nav-menu {
  display: flex;
  gap: 1.5rem;
  align-items: center;
}

.nav-menu a {
  text-decoration: none;
  color: #333;
}

.btn-logout {
  background: #dc3545;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
}

.main-content h1 {
  margin-bottom: 2rem;
}

.dashboard-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 2rem;
}

.stat-card {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.stat-card h3 {
  color: #666;
  font-size: 0.9rem;
  margin-bottom: 0.5rem;
}

.stat-card p {
  font-size: 2rem;
  font-weight: bold;
  color: #333;
}
</style>
