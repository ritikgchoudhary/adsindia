<template>
  <DashboardLayout page-title="Upgrade Package" :dark-theme="true">
    <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-3 tw-gap-6">
      <template v-for="pkg in packages" :key="pkg?.id || Math.random()">
        <div v-if="pkg && pkg.id" 
          class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-transition-all tw-duration-300 tw-flex tw-flex-col tw-h-full hover:-tw-translate-y-1 hover:tw-shadow-xl"
          :class="{ 
            'tw-border-amber-400 tw-ring-2 tw-ring-amber-400/20': pkg.is_recommended,
            'tw-border-emerald-500 tw-ring-2 tw-ring-emerald-500/20': pkg.id === currentPackageId,
            'tw-opacity-75 tw-grayscale-[0.5]': !canUpgradeTo(pkg.id) && pkg.id !== currentPackageId,
            'tw-border-slate-200': !pkg.is_recommended && pkg.id !== currentPackageId
          }"
        >
          <div v-if="pkg.is_recommended" class="tw-bg-amber-400 tw-text-indigo-900 tw-py-2 tw-px-4 tw-text-center tw-font-bold tw-text-sm tw-uppercase tw-tracking-wide">
            <i class="fas fa-star tw-mr-1"></i>Recommended
          </div>
          <div v-if="pkg.id === currentPackageId" class="tw-bg-emerald-500 tw-text-white tw-py-2 tw-px-4 tw-text-center tw-font-bold tw-text-sm tw-uppercase tw-tracking-wide">
            <i class="fas fa-check-circle tw-mr-1"></i>Current Package
          </div>

          <div class="tw-p-6 tw-flex-1 tw-flex tw-flex-col">
            <h4 class="tw-text-xl tw-font-bold tw-text-slate-900 tw-text-center tw-mb-4">{{ pkg.name }}</h4>
            
            <div class="tw-text-center tw-mb-6">
              <h2 class="tw-text-3xl tw-font-extrabold tw-text-indigo-600 tw-mb-1">{{ currencySymbol }}{{ formatAmount(pkg.price) }}</h2>
              <p class="tw-text-slate-400 tw-text-sm tw-m-0">{{ pkg.validity_days }} Days Validity</p>
            </div>
            
            <!-- Remaining Amount Display -->
            <div v-if="pkg.id !== currentPackageId && canUpgradeTo(pkg.id)" class="tw-bg-indigo-50 tw-border tw-border-indigo-100 tw-rounded-xl tw-p-4 tw-mb-6 tw-text-center">
              <strong class="tw-block tw-text-indigo-900 tw-text-sm tw-uppercase tw-tracking-wide tw-mb-1">Pay Only</strong>
              <span class="tw-block tw-text-2xl tw-font-bold tw-text-indigo-600 tw-mb-1">{{ currencySymbol }}{{ formatAmount(getRemainingAmount(pkg.id)) }}</span>
              <small class="tw-block tw-text-slate-500 tw-text-xs">
                (Full Price: {{ currencySymbol }}{{ formatAmount(pkg.price) }})
              </small>
            </div>
            
            <ul class="tw-space-y-3 tw-mb-8 tw-flex-1">
              <li class="tw-flex tw-items-start tw-gap-3">
                <div class="tw-bg-emerald-100 tw-text-emerald-600 tw-rounded-full tw-p-1 tw-w-5 tw-h-5 tw-flex tw-items-center tw-justify-center tw-shrink-0 tw-mt-0.5">
                  <i class="fas fa-check tw-text-[10px]"></i>
                </div>
                <span class="tw-text-slate-600 tw-text-sm">Daily Ads Limit: <strong>{{ pkg.daily_ads_limit }}</strong></span>
              </li>
              <li class="tw-flex tw-items-start tw-gap-3">
                <div class="tw-bg-emerald-100 tw-text-emerald-600 tw-rounded-full tw-p-1 tw-w-5 tw-h-5 tw-flex tw-items-center tw-justify-center tw-shrink-0 tw-mt-0.5">
                  <i class="fas fa-check tw-text-[10px]"></i>
                </div>
                <span class="tw-text-slate-600 tw-text-sm">Earning Per Ad: <strong>{{ currencySymbol }}{{ formatAmount(pkg.earning_per_ad) }}</strong></span>
              </li>
              <li class="tw-flex tw-items-start tw-gap-3">
                <div class="tw-bg-emerald-100 tw-text-emerald-600 tw-rounded-full tw-p-1 tw-w-5 tw-h-5 tw-flex tw-items-center tw-justify-center tw-shrink-0 tw-mt-0.5">
                  <i class="fas fa-check tw-text-[10px]"></i>
                </div>
                <span class="tw-text-slate-600 tw-text-sm">Max Daily Earning: <strong>{{ currencySymbol }}{{ formatAmount(pkg.max_daily_earning) }}</strong></span>
              </li>
              <li v-for="feature in pkg.features" :key="feature" class="tw-flex tw-items-start tw-gap-3">
                <div class="tw-bg-emerald-100 tw-text-emerald-600 tw-rounded-full tw-p-1 tw-w-5 tw-h-5 tw-flex tw-items-center tw-justify-center tw-shrink-0 tw-mt-0.5">
                  <i class="fas fa-check tw-text-[10px]"></i>
                </div>
                <span class="tw-text-slate-600 tw-text-sm">{{ feature }}</span>
              </li>
            </ul>
            
            <button 
              type="button"
              class="tw-w-full tw-py-3 tw-font-bold tw-rounded-xl tw-flex tw-items-center tw-justify-center tw-gap-2 tw-transition-all tw-border-0 tw-cursor-pointer" 
              :class="{
                'tw-bg-emerald-100 tw-text-emerald-700 tw-cursor-not-allowed': pkg.id === currentPackageId,
                'tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-shadow-lg tw-shadow-indigo-500/20': canUpgradeTo(pkg.id) && pkg.id !== currentPackageId,
                'tw-bg-slate-100 tw-text-slate-400 tw-cursor-not-allowed': !canUpgradeTo(pkg.id) && pkg.id !== currentPackageId
              }"
              @click="purchasePackage(pkg)" 
              :disabled="!canUpgradeTo(pkg.id) || pkg.id === currentPackageId">
              <span v-if="pkg.id === currentPackageId">
                <i class="fas fa-check-circle"></i> Current Package
              </span>
              <span v-else-if="canUpgradeTo(pkg.id)">
                <i class="fas fa-arrow-up"></i> Upgrade Now
              </span>
              <span v-else>
                <i class="fas fa-lock"></i> Locked
              </span>
            </button>
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
