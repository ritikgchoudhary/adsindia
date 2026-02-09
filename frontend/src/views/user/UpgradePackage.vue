<template>
  <DashboardLayout page-title="Upgrade Package">
    <div class="row">
      <template v-for="pkg in packages" :key="pkg?.id || Math.random()">
        <div v-if="pkg && pkg.id" class="col-lg-4 col-md-6 mb-4">
          <div class="card custom--card" :class="{ 
            'border-warning': pkg.is_recommended,
            'border-success': pkg.id === currentPackageId,
            'opacity-75': !canUpgradeTo(pkg.id)
          }">
          <div v-if="pkg.is_recommended" class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="fas fa-star me-2"></i>Recommended</h5>
          </div>
          <div v-if="pkg.id === currentPackageId" class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>Current Package</h5>
          </div>
          <div class="card-body">
            <h4 class="text-center mb-3">{{ pkg.name }}</h4>
            <div class="text-center mb-3">
              <h2 class="text-primary">{{ currencySymbol }}{{ formatAmount(pkg.price) }}</h2>
              <p class="text-muted mb-0">{{ pkg.validity_days }} Days Validity</p>
            </div>
            
            <!-- Remaining Amount Display -->
            <div v-if="pkg.id !== currentPackageId && canUpgradeTo(pkg.id)" class="alert alert-info mb-3 text-center">
              <strong>Pay Only:</strong><br>
              <span class="h4 text-primary">{{ currencySymbol }}{{ formatAmount(getRemainingAmount(pkg.id)) }}</span>
              <small class="d-block text-muted mt-1">
                (Full Price: {{ currencySymbol }}{{ formatAmount(pkg.price) }})
              </small>
            </div>
            
            <ul class="list-unstyled mb-4">
              <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Daily Ads Limit: {{ pkg.daily_ads_limit }}</li>
              <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Earning Per Ad: {{ currencySymbol }}{{ formatAmount(pkg.earning_per_ad) }}</li>
              <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Max Daily Earning: {{ currencySymbol }}{{ formatAmount(pkg.max_daily_earning) }}</li>
              <li v-for="feature in pkg.features" :key="feature" class="mb-2">
                <i class="fas fa-check text-success me-2"></i>{{ feature }}
              </li>
            </ul>
            
            <button 
              class="btn w-100" 
              :class="{
                'btn-success': pkg.id === currentPackageId,
                'btn--base': canUpgradeTo(pkg.id) && pkg.id !== currentPackageId,
                'btn-secondary': !canUpgradeTo(pkg.id) && pkg.id !== currentPackageId
              }"
              @click="purchasePackage(pkg)" 
              :disabled="!canUpgradeTo(pkg.id) || pkg.id === currentPackageId">
              <span v-if="pkg.id === currentPackageId">
                <i class="fas fa-check-circle me-2"></i>Current Package
              </span>
              <span v-else-if="canUpgradeTo(pkg.id)">
                <i class="fas fa-arrow-up me-2"></i>Upgrade Now (Pay {{ currencySymbol }}{{ formatAmount(getRemainingAmount(pkg.id)) }})
              </span>
              <span v-else>
                <i class="fas fa-lock me-2"></i>Upgrade Previous Package First
              </span>
            </button>
          </div>
        </div>
        </div>
      </template>
    </div>
  </DashboardLayout>
</template>

<script>
import { ref, onMounted, computed } from 'vue'
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

    // Check if user can upgrade to this package (sequential upgrade only)
    const canUpgradeTo = (packageId) => {
      if (!currentPackageId.value) {
        // No current package - can only start with package 1 (AdsLite)
        return packageId === 1
      }
      
      // Can only upgrade to next package (currentPackageId + 1)
      return packageId === currentPackageId.value + 1
    }

    // Calculate remaining amount to pay for upgrade
    const getRemainingAmount = (packageId) => {
      if (!currentPackageId.value) {
        // No current package - pay full price
        const pkg = packages.value.find(p => p.id === packageId)
        return pkg ? pkg.price : 0
      }

      // Calculate difference between new package and current package
      const currentPkg = packages.value.find(p => p.id === currentPackageId.value)
      const newPkg = packages.value.find(p => p.id === packageId)
      
      if (!currentPkg || !newPkg) return 0
      
      const remaining = newPkg.price - currentPkg.price
      return remaining > 0 ? remaining : 0
    }

    const purchasePackage = async (pkg) => {
      if (!pkg || !pkg.id || pkg.id === currentPackageId.value) return
      
      if (!canUpgradeTo(pkg.id)) {
        if (window.notify) {
          window.notify('error', 'You can only upgrade to the next package sequentially. Please upgrade previous packages first.')
        }
        return
      }

      const remainingAmount = getRemainingAmount(pkg.id)
      const confirmMessage = `Are you sure you want to upgrade to ${pkg.name}?\n\nYou will pay: ${currencySymbol.value}${formatAmount(remainingAmount)}\n(Full Price: ${currencySymbol.value}${formatAmount(pkg.price)})`

      if (!confirm(confirmMessage)) {
        return
      }

      try {
        const response = await api.post('/packages/purchase', { package_id: pkg.id })
        if (response.data.status === 'success') {
          if (window.notify) {
            window.notify('success', `Package upgraded successfully! You paid ${currencySymbol.value}${formatAmount(remainingAmount)}`)
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
          // Backend returns: { data: { data: [...], currency_symbol: '₹' } }
          packages.value = packagesResponse.data.data?.data || packagesResponse.data.data || []
          currencySymbol.value = packagesResponse.data.data?.currency_symbol || packagesResponse.data.currency_symbol || '₹'
        }
        
        console.log('Packages loaded:', packages.value)
        console.log('Current package ID:', currentPackageId.value)

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
      purchasePackage,
      canUpgradeTo,
      getRemainingAmount
    }
  }
}
</script>
