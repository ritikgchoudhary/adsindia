<template>
  <DashboardLayout page-title="My Package">
    <div class="row">
      <div class="col-lg-8">
        <div class="card custom--card">
          <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-box me-2"></i>Current Package</h5>
          </div>
          <div class="card-body">
            <div v-if="currentPackage" class="package-info">
              <div class="d-flex align-items-center mb-3">
                <div class="package-icon me-3">
                  <i class="fas fa-crown fa-3x text-warning"></i>
                </div>
                <div>
                  <h4>{{ currentPackage.name }}</h4>
                  <p class="text-muted mb-0">Valid until: {{ formatDate(currentPackage.valid_until) }}</p>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <p><strong>Price:</strong> {{ currencySymbol }}{{ formatAmount(currentPackage.price) }}</p>
                  <p><strong>Daily Ads Limit:</strong> {{ currentPackage.daily_ads_limit }}</p>
                  <p><strong>Earning Per Ad:</strong> {{ currencySymbol }}{{ formatAmount(currentPackage.earning_per_ad) }}</p>
                </div>
                <div class="col-md-6">
                  <p><strong>Max Daily Earning:</strong> {{ currencySymbol }}{{ formatAmount(currentPackage.max_daily_earning) }}</p>
                  <p><strong>Features:</strong></p>
                  <ul>
                    <li v-for="feature in currentPackage.features" :key="feature">{{ feature }}</li>
                  </ul>
                </div>
              </div>
            </div>
            <div v-else class="text-center">
              <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
              <h5>No Package Active</h5>
              <p class="text-muted">Please purchase a package to start earning.</p>
              <router-link to="/user/upgrade-package" class="btn btn--base mt-3">
                <i class="fas fa-shopping-cart me-2"></i>Buy Package
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script>
import { ref, onMounted } from 'vue'
import DashboardLayout from '../../components/DashboardLayout.vue'
import api from '../../services/api'

export default {
  name: 'Packages',
  components: {
    DashboardLayout
  },
  setup() {
    const currentPackage = ref(null)
    const currencySymbol = ref('₹')

    const formatAmount = (amount) => {
      if (!amount && amount !== 0) return '0.00'
      return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    }

    const formatDate = (dateString) => {
      if (!dateString) return '-'
      return new Date(dateString).toLocaleDateString('en-IN', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
      })
    }

    const fetchPackage = async () => {
      try {
        const response = await api.get('/packages/current')
        if (response.data.status === 'success') {
          currentPackage.value = response.data.data
          currencySymbol.value = response.data.currency_symbol || '₹'
        }
      } catch (error) {
        console.error('Error loading package:', error)
      }
    }

    onMounted(() => {
      fetchPackage()
    })

    return {
      currentPackage,
      currencySymbol,
      formatAmount,
      formatDate
    }
  }
}
</script>
