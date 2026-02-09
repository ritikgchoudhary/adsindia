<template>
  <DashboardLayout page-title="Packages">
    <!-- Current Package Section -->
    <div v-if="currentPackage" class="row mb-4">
      <div class="col-12">
        <div class="card custom--card current-package-card" style="border: 3px solid #28a745; border-radius: 12px; box-shadow: 0 4px 12px rgba(40, 167, 69, 0.2);">
          <div class="card-header current-package-header" style="background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); color: #ffffff; padding: 16px 24px; border-bottom: 3px solid #1e7e34;">
            <h5 class="mb-0 fw-bold" style="font-size: 1.2rem;"><i class="fas fa-check-circle me-2"></i>Your Current Package</h5>
          </div>
          <div class="card-body" style="background: linear-gradient(to bottom, #f0fff4 0%, #ffffff 100%); padding: 24px;">
            <div class="d-flex align-items-center mb-4">
              <div class="package-icon me-4">
                <i class="fas fa-crown fa-3x" style="color: #ffc107; text-shadow: 2px 2px 4px rgba(0,0,0,0.1);"></i>
              </div>
              <div>
                <h4 class="mb-2 fw-bold" style="color: #28a745; font-size: 1.75rem;">{{ currentPackage.name }}</h4>
                <p class="mb-0 fw-semibold" style="color: #495057; font-size: 1rem;">Valid until: <span style="color: #212529;">{{ formatDate(currentPackage.valid_until) }}</span></p>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <p class="mb-3" style="color: #212529; font-size: 1rem;"><strong style="color: #28a745;">Price:</strong> <span class="fw-bold" style="color: #0066cc; font-size: 1.1rem;">{{ currencySymbol }}{{ formatAmount(currentPackage.price) }}</span></p>
                <p class="mb-3" style="color: #212529; font-size: 1rem;"><strong style="color: #28a745;">Daily Ads Limit:</strong> <span class="fw-semibold" style="color: #212529;">{{ currentPackage.daily_ads_limit }}</span></p>
                <p class="mb-3" style="color: #212529; font-size: 1rem;"><strong style="color: #28a745;">Earning Per Ad:</strong> <span class="fw-semibold" style="color: #212529;">{{ currencySymbol }}{{ formatAmount(currentPackage.earning_per_ad) }}</span></p>
              </div>
              <div class="col-md-6">
                <p class="mb-3" style="color: #212529; font-size: 1rem;"><strong style="color: #28a745;">Max Daily Earning:</strong> <span class="fw-semibold" style="color: #212529;">{{ currencySymbol }}{{ formatAmount(currentPackage.max_daily_earning) }}</span></p>
                <p class="mb-2" style="color: #212529; font-size: 1rem;"><strong style="color: #28a745;">Features:</strong></p>
                <ul style="list-style: none; padding-left: 0;">
                  <li v-for="feature in currentPackage.features" :key="feature" class="mb-2" style="color: #212529;">
                    <i class="fas fa-check-circle me-2" style="color: #28a745;"></i><span class="fw-medium">{{ feature }}</span>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- All Packages for Upgrade -->
    <div class="row">
      <div class="col-12 mb-3">
        <h4><i class="fas fa-box me-2"></i>Available Packages</h4>
        <p class="text-muted">Upgrade sequentially to unlock more features and earnings</p>
      </div>
      <div v-if="isLoading" class="col-12">
        <div class="alert alert-info text-center">
          <i class="fas fa-spinner fa-spin me-2"></i>Loading packages...
        </div>
      </div>
      <div v-else-if="packages.length === 0" class="col-12">
        <div class="alert alert-warning text-center">
          <i class="fas fa-exclamation-triangle me-2"></i>No packages available. Please refresh the page or contact support.
          <br><small class="mt-2 d-block">If the problem persists, check browser console for errors.</small>
        </div>
      </div>
      <template v-for="pkg in packages" :key="pkg?.id || Math.random()">
        <div v-if="pkg && pkg.id" class="col-lg-4 col-md-6 mb-4">
          <div class="card custom--card package-card" :class="{ 
            'package-recommended': pkg.is_recommended,
            'package-current': pkg.id === currentPackageId,
            'package-locked': !canUpgradeTo(pkg.id) && pkg.id !== currentPackageId
          }" :style="{
            borderWidth: pkg.is_recommended ? '3px' : pkg.id === currentPackageId ? '3px' : '2px',
            borderColor: pkg.is_recommended ? '#ffc107' : pkg.id === currentPackageId ? '#28a745' : '#dee2e6'
          }">
          <div v-if="pkg.is_recommended" class="card-header package-header-recommended">
            <h5 class="mb-0 fw-bold"><i class="fas fa-star me-2"></i>Recommended</h5>
          </div>
          <div v-if="pkg.id === currentPackageId" class="card-header package-header-current">
            <h5 class="mb-0 fw-bold"><i class="fas fa-check-circle me-2"></i>Current Package</h5>
          </div>
          <div class="card-body" :style="{ backgroundColor: pkg.id === currentPackageId ? '#f8fff9' : 'transparent' }">
            <h4 class="text-center mb-3 fw-bold" :style="{ color: pkg.id === currentPackageId ? '#28a745' : '#212529' }">{{ pkg.name }}</h4>
            <div class="text-center mb-3">
              <h2 class="mb-2 fw-bold" :style="{ color: '#0066cc', fontSize: '2rem' }">{{ currencySymbol }}{{ formatAmount(pkg.price) }}</h2>
              <p class="mb-0 fw-semibold" style="color: #6c757d;">{{ pkg.validity_days }} Days Validity</p>
            </div>
            
            <!-- Remaining Amount Display -->
            <div v-if="pkg.id !== currentPackageId && canUpgradeTo(pkg.id)" class="alert mb-3 text-center" style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); border: 2px solid #2196f3; border-radius: 8px;">
              <strong style="color: #1565c0; font-size: 0.9rem;">Pay Only:</strong><br>
              <span class="h4 fw-bold" style="color: #0066cc; display: block; margin: 8px 0;">{{ currencySymbol }}{{ formatAmount(getRemainingAmount(pkg.id)) }}</span>
              <small class="d-block mt-1" style="color: #424242;">
                (Full Price: {{ currencySymbol }}{{ formatAmount(pkg.price) }})
              </small>
            </div>
            
            <ul class="list-unstyled mb-4">
              <li class="mb-2" style="color: #212529;"><i class="fas fa-check me-2" style="color: #28a745; font-weight: bold;"></i><strong>Daily Ads Limit:</strong> <span class="fw-semibold">{{ pkg.daily_ads_limit }}</span></li>
              <li class="mb-2" style="color: #212529;"><i class="fas fa-check me-2" style="color: #28a745; font-weight: bold;"></i><strong>Earning Per Ad:</strong> <span class="fw-semibold">{{ currencySymbol }}{{ formatAmount(pkg.earning_per_ad) }}</span></li>
              <li class="mb-2" style="color: #212529;"><i class="fas fa-check me-2" style="color: #28a745; font-weight: bold;"></i><strong>Max Daily Earning:</strong> <span class="fw-semibold">{{ currencySymbol }}{{ formatAmount(pkg.max_daily_earning) }}</span></li>
              <li v-for="feature in pkg.features" :key="feature" class="mb-2" style="color: #212529;">
                <i class="fas fa-check me-2" style="color: #28a745; font-weight: bold;"></i><span class="fw-medium">{{ feature }}</span>
              </li>
            </ul>
            
            <button 
              class="btn w-100 fw-bold package-btn" 
              :class="{
                'package-btn-current': pkg.id === currentPackageId,
                'package-btn-upgrade': canUpgradeTo(pkg.id) && pkg.id !== currentPackageId,
                'package-btn-locked': !canUpgradeTo(pkg.id) && pkg.id !== currentPackageId
              }"
              @click="purchasePackage(pkg)" 
              :disabled="!canUpgradeTo(pkg.id) || pkg.id === currentPackageId"
              :style="{
                fontSize: '1rem',
                padding: '12px',
                borderRadius: '8px',
                transition: 'all 0.3s ease'
              }">
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

    <!-- No Package Message -->
    <div v-if="!currentPackage && packages.length === 0" class="row">
      <div class="col-12">
        <div class="card custom--card">
          <div class="card-body text-center">
            <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
            <h5>No Package Active</h5>
            <p class="text-muted">Please purchase a package to start earning.</p>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import DashboardLayout from '../../components/DashboardLayout.vue'
import api from '../../services/api'

export default {
  name: 'Packages',
  components: {
    DashboardLayout
  },
  setup() {
    const router = useRouter()
    
    // Default packages as fallback (will be replaced by API data)
    const defaultPackages = [
      { id: 1, name: 'AdsLite', price: 1499, validity_days: 30, daily_ads_limit: 2, earning_per_ad: 5000, max_daily_earning: 10000, features: ['2 Ads per day', 'Earn ₹5,000 per ad', 'Basic Support'], is_recommended: false },
      { id: 2, name: 'AdsPro', price: 2999, validity_days: 30, daily_ads_limit: 5, earning_per_ad: 5500, max_daily_earning: 27500, features: ['5 Ads per day', 'Earn ₹5,500 per ad', 'Priority Support'], is_recommended: true },
      { id: 3, name: 'AdsSupreme', price: 5999, validity_days: 30, daily_ads_limit: 10, earning_per_ad: 6000, max_daily_earning: 60000, features: ['10 Ads per day', 'Earn ₹6,000 per ad', 'VIP Support'], is_recommended: false },
      { id: 4, name: 'AdsPremium', price: 9999, validity_days: 30, daily_ads_limit: 20, earning_per_ad: 6000, max_daily_earning: 120000, features: ['20 Ads per day', 'Earn ₹6,000 per ad', 'Premium Support'], is_recommended: false },
      { id: 5, name: 'AdsPremium+', price: 15999, validity_days: 30, daily_ads_limit: 35, earning_per_ad: 6000, max_daily_earning: 210000, features: ['35 Ads per day', 'Earn ₹6,000 per ad', 'Premium+ Support', 'All Benefits'], is_recommended: false }
    ]
    
    const packages = ref([])
    const currentPackage = ref(null)
    const currentPackageId = ref(null)
    const currencySymbol = ref('₹')
    const isLoading = ref(true)

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
        // Initiate gateway payment
        const response = await api.post('/packages/purchase', { 
          package_id: pkg.id,
          payment_method: 'gateway' // Use gateway payment (dummy gateway)
        })
        
        console.log('Purchase response:', response.data)
        
        if (response.data.status === 'success') {
          // Check if payment_url exists (gateway payment)
          if (response.data.data?.payment_url) {
            // Redirect to payment gateway using router
            console.log('Redirecting to payment gateway:', response.data.data)
            router.push({
              name: 'PackagePayment',
              query: {
                package_id: response.data.data.package_id || pkg.id,
                amount: response.data.data.amount || remainingAmount,
                package_name: response.data.data.package_name || pkg.name,
                trx: response.data.data.trx || ''
              }
            })
          } else if (response.data.data?.order_id) {
            // Direct success (balance payment fallback - should not happen with gateway)
            if (window.notify) {
              window.notify('success', `Package upgraded successfully! You paid ${currencySymbol.value}${formatAmount(remainingAmount)}`)
            }
            fetchPackages()
          } else {
            // Fallback: redirect with available data
            console.log('Fallback redirect to payment gateway')
            router.push({
              name: 'PackagePayment',
              query: {
                package_id: pkg.id,
                amount: remainingAmount,
                package_name: pkg.name,
                trx: response.data.data?.trx || ''
              }
            })
          }
        }
      } catch (error) {
        console.error('Error purchasing package:', error)
        console.error('Error response:', error.response?.data)
        if (window.notify) {
          window.notify('error', error.response?.data?.message || 'Failed to purchase package')
        }
      }
    }

    const fetchPackages = async () => {
      isLoading.value = true
      try {
        const [packagesResponse, currentResponse] = await Promise.all([
          api.get('/packages'),
          api.get('/packages/current')
        ])
        
        console.log('Packages API Response:', packagesResponse.data)
        console.log('Current Package API Response:', currentResponse.data)
        
        if (packagesResponse.data.status === 'success') {
          // Backend returns: { status: 'success', data: { data: [...packages...], currency_symbol: '₹' } }
          const responseData = packagesResponse.data.data
          
          // Extract packages array
          if (responseData && responseData.data && Array.isArray(responseData.data)) {
            packages.value = responseData.data
          } else if (Array.isArray(responseData)) {
            packages.value = responseData
          } else {
            packages.value = []
          }
          
          // Extract currency symbol
          if (responseData && responseData.currency_symbol) {
            currencySymbol.value = responseData.currency_symbol
          }
          
          console.log('Extracted packages:', packages.value)
          console.log('Packages count:', packages.value.length)
          
          // If no packages loaded, use default fallback
          if (packages.value.length === 0) {
            console.warn('No packages from API, using default packages')
            packages.value = defaultPackages
          }
        } else {
          console.error('API returned non-success status:', packagesResponse.data)
          // Use default packages as fallback
          packages.value = defaultPackages
        }

        if (currentResponse.data.status === 'success' && currentResponse.data.data) {
          currentPackage.value = currentResponse.data.data
          if (currentResponse.data.data.package && currentResponse.data.data.package.id) {
            currentPackageId.value = currentResponse.data.data.package.id
          } else if (currentResponse.data.data.id) {
            currentPackageId.value = currentResponse.data.data.id
          }
        }
        
        console.log('Final packages array:', packages.value)
        console.log('Final current package ID:', currentPackageId.value)
      } catch (error) {
        console.error('Error loading packages:', error)
        console.error('Error response:', error.response)
        console.error('Error details:', {
          message: error.message,
          status: error.response?.status,
          data: error.response?.data
        })
        
        // Use default packages as fallback on error
        packages.value = defaultPackages
        console.warn('API error, using default packages:', defaultPackages)
        
        if (window.notify) {
          const errorMsg = error.response?.data?.message || error.message || 'Failed to load packages. Please refresh the page.'
          window.notify('error', errorMsg)
        }
      } finally {
        isLoading.value = false
      }
    }

    onMounted(() => {
      fetchPackages()
    })

    return {
      packages,
      currentPackage,
      currentPackageId,
      currencySymbol,
      isLoading,
      formatAmount,
      formatDate,
      purchasePackage,
      canUpgradeTo,
      getRemainingAmount
    }
  }
}
</script>

<style scoped>
.package-card {
  border-radius: 12px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  background: #ffffff;
  min-height: 100%;
}

.package-card:hover {
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
  transform: translateY(-4px);
}

.package-recommended {
  border: 3px solid #ffc107 !important;
  background: linear-gradient(to bottom, #fff9e6 0%, #ffffff 100%);
}

.package-current {
  border: 3px solid #28a745 !important;
  background: linear-gradient(to bottom, #f0fff4 0%, #ffffff 100%);
}

.package-locked {
  opacity: 0.85;
  background: #f8f9fa;
}

.package-locked .card-body {
  background: #f8f9fa !important;
}

.package-header-recommended {
  background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%) !important;
  color: #000000 !important;
  font-weight: bold;
  border-bottom: 2px solid #ffb300;
  padding: 12px 20px;
}

.package-header-current {
  background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%) !important;
  color: #ffffff !important;
  font-weight: bold;
  border-bottom: 2px solid #1e7e34;
  padding: 12px 20px;
}

.package-btn {
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border: none;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.package-btn-current {
  background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%) !important;
  color: #ffffff !important;
  cursor: not-allowed;
}

.package-btn-current:hover {
  background: linear-gradient(135deg, #218838 0%, #1c7430 100%) !important;
  box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
}

.package-btn-upgrade {
  background: linear-gradient(135deg, #0066cc 0%, #0052a3 100%) !important;
  color: #ffffff !important;
}

.package-btn-upgrade:hover {
  background: linear-gradient(135deg, #0052a3 0%, #003d7a 100%) !important;
  box-shadow: 0 4px 12px rgba(0, 102, 204, 0.4);
  transform: translateY(-2px);
}

.package-btn-upgrade:active {
  transform: translateY(0);
}

.package-btn-locked {
  background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%) !important;
  color: #ffffff !important;
  cursor: not-allowed;
  opacity: 0.8;
}

.package-btn-locked:hover {
  background: linear-gradient(135deg, #5a6268 0%, #495057 100%) !important;
}

.card-body {
  padding: 24px;
}

.card-body h4 {
  font-size: 1.5rem;
  margin-bottom: 16px;
}

.card-body h2 {
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 8px;
}

.card-body ul li {
  font-size: 0.95rem;
  line-height: 1.6;
}

.card-body ul li i {
  font-size: 1rem;
  margin-right: 8px;
}

.alert {
  padding: 16px;
  border-radius: 8px;
  font-size: 0.95rem;
}

/* Ensure text is always visible */
.text-muted {
  color: #6c757d !important;
  font-weight: 500;
}

.text-primary {
  color: #0066cc !important;
  font-weight: 700;
}

.text-success {
  color: #28a745 !important;
}

/* Current Package Section */
.border-success {
  border-color: #28a745 !important;
  border-width: 3px !important;
}

.bg-success {
  background-color: #28a745 !important;
}

/* Make sure all text is readable */
.card-body * {
  color: inherit;
}

.package-locked .card-body h4,
.package-locked .card-body h2,
.package-locked .card-body p,
.package-locked .card-body li {
  color: #495057 !important;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .package-card {
    margin-bottom: 20px;
  }
  
  .card-body h2 {
    font-size: 1.75rem;
  }
}
</style>
