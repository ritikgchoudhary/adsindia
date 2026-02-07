<template>
  <DashboardLayout page-title="Upgrade Package">
    <div class="row">
      <template v-for="pkg in packages" :key="pkg?.id || Math.random()">
        <div v-if="pkg && pkg.id" class="col-lg-4 col-md-6 mb-4">
          <div class="card custom--card" :class="{ 'border-warning': pkg.is_recommended }">
          <div v-if="pkg.is_recommended" class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="fas fa-star me-2"></i>Recommended</h5>
          </div>
          <div class="card-body">
            <h4 class="text-center mb-3">{{ pkg.name }}</h4>
            <div class="text-center mb-4">
              <h2 class="text-primary">{{ currencySymbol }}{{ formatAmount(pkg.price) }}</h2>
              <p class="text-muted mb-0">{{ pkg.validity_days }} Days Validity</p>
            </div>
            <ul class="list-unstyled mb-4">
              <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Daily Ads Limit: {{ pkg.daily_ads_limit }}</li>
              <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Earning Per Ad: {{ currencySymbol }}{{ formatAmount(pkg.earning_per_ad) }}</li>
              <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Max Daily Earning: {{ currencySymbol }}{{ formatAmount(pkg.max_daily_earning) }}</li>
              <li v-for="feature in pkg.features" :key="feature" class="mb-2">
                <i class="fas fa-check text-success me-2"></i>{{ feature }}
              </li>
            </ul>
            <button class="btn btn--base w-100" @click="purchasePackage(pkg)" :disabled="pkg.id === currentPackageId">
              <span v-if="pkg.id === currentPackageId">Current Package</span>
              <span v-else>Upgrade Now</span>
            </button>
          </div>
        </div>
        </div>
      </template>
    </div>
  </DashboardLayout>
</template>

<script>
import { ref, onMounted } from 'vue'
import DashboardLayout from '../../components/DashboardLayout.vue'
import api from '../../services/api'

export default {
  name: 'UpgradePackage',
  components: {
    DashboardLayout
  },
  setup() {
    const packages = ref([])
    const currentPackageId = ref(null)
    const currencySymbol = ref('₹')

    const formatAmount = (amount) => {
      if (!amount && amount !== 0) return '0.00'
      return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    }

    const purchasePackage = async (pkg) => {
      if (!pkg || !pkg.id || pkg.id === currentPackageId.value) return

      if (!confirm(`Are you sure you want to purchase ${pkg.name} for ${currencySymbol.value}${formatAmount(pkg.price)}?`)) {
        return
      }

      try {
        const response = await api.post('/packages/purchase', { package_id: pkg.id })
        if (response.data.status === 'success') {
          if (window.notify) {
            window.notify('success', 'Package purchased successfully!')
          }
          fetchPackages()
        }
      } catch (error) {
        console.error('Error purchasing package:', error)
        if (window.notify) {
          window.notify('error', error.response?.data?.message || 'Failed to purchase package')
        }
      }
    }

    const fetchPackages = async () => {
      try {
        const [packagesResponse, currentResponse] = await Promise.all([
          api.get('/packages'),
          api.get('/packages/current')
        ])
        
        if (packagesResponse.data.status === 'success') {
          packages.value = packagesResponse.data.data || []
          currencySymbol.value = packagesResponse.data.currency_symbol || '₹'
        }

        if (currentResponse.data.status === 'success' && currentResponse.data.data) {
          if (currentResponse.data.data.package && currentResponse.data.data.package.id) {
            currentPackageId.value = currentResponse.data.data.package.id
          } else if (currentResponse.data.data.id) {
            currentPackageId.value = currentResponse.data.data.id
          }
        }
      } catch (error) {
        console.error('Error loading packages:', error)
      }
    }

    onMounted(() => {
      fetchPackages()
    })

    return {
      packages,
      currentPackageId,
      currencySymbol,
      formatAmount,
      purchasePackage
    }
  }
}
</script>
