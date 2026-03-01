<template>
  <DashboardLayout page-title="Ad Plans" :dark-theme="true">
    


    <!-- Terms gate -->
    <div class="tw-mb-4 sm:tw-mb-6">
      <div class="tw-bg-slate-800/80 tw-backdrop-blur-sm tw-border tw-border-slate-700 tw-rounded-xl sm:tw-rounded-2xl tw-p-3 sm:tw-p-5">
        <div class="tw-text-white tw-font-bold tw-mb-1 sm:tw-mb-2">
          <i class="fas fa-file-contract tw-mr-2 tw-text-indigo-400"></i>Terms Required
        </div>
        <label class="tw-flex tw-items-start tw-gap-3 tw-cursor-pointer tw-select-none">
          <input type="checkbox" v-model="termsAccepted" class="tw-mt-1">
          <span class="tw-text-white/90 tw-text-xs sm:tw-text-sm tw-leading-relaxed">
            I agree with
            <router-link to="/policy/terms-of-service" class="tw-font-bold tw-text-indigo-400 tw-no-underline hover:tw-underline">Terms</router-link>
            and
            <router-link to="/policy/privacy-policy" class="tw-font-bold tw-text-indigo-400 tw-no-underline hover:tw-underline">Privacy Policy</router-link>
            before payment.
          </span>
        </label>
      </div>
    </div>

    <!-- Info Alert (Master Admin editable) -->
    <div class="tw-mb-6 sm:tw-mb-8">
      <div class="tw-bg-slate-800/80 tw-backdrop-blur-sm tw-border tw-border-slate-700 tw-rounded-xl sm:tw-rounded-2xl tw-p-4 sm:tw-p-6 tw-flex tw-items-start tw-gap-3 sm:tw-gap-4 tw-shadow-xl">
        <div class="tw-bg-indigo-500/20 tw-rounded-full tw-p-2 sm:tw-p-3 tw-text-indigo-400 tw-shrink-0">
          <i class="fas fa-info-circle tw-text-lg sm:tw-text-2xl"></i>
        </div>
        <div>
          <h5 class="tw-text-white tw-font-bold tw-text-base sm:tw-text-xl tw-mb-1.5 sm:tw-mb-3">{{ infoTitle }}</h5>
          <ul class="tw-text-slate-300 tw-text-xs sm:tw-text-sm tw-m-0 tw-pl-4 sm:tw-pl-5 tw-space-y-1 sm:tw-space-y-2">
            <li v-for="(b, idx) in infoBullets" :key="idx" v-html="renderBullet(b)"></li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Plans Grid -->
    <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-3 xl:tw-grid-cols-4 tw-gap-6">
      <template v-for="plan in adPlans" :key="plan?.id || Math.random()">
        <div v-if="plan && plan.id" 
          class="tw-bg-white tw-rounded-2xl tw-border tw-transition-all tw-duration-300 tw-flex tw-flex-col tw-h-full tw-overflow-hidden tw-relative"
          :class="[
            activePlanId === plan.id ? 'tw-border-emerald-500 tw-shadow-lg tw-shadow-emerald-500/10' : (plan.is_recommended ? 'tw-border-amber-400 tw-shadow-lg tw-shadow-amber-500/10' : 'tw-border-slate-200 tw-shadow-sm hover:tw-shadow-xl hover:-tw-translate-y-1')
          ]"
        >
          <!-- Badge -->
          <div v-if="activePlanId === plan.id" class="tw-bg-emerald-500 tw-text-white tw-text-center tw-py-2 tw-font-bold tw-text-sm tw-uppercase tw-tracking-wide">
            <i class="fas fa-check-circle tw-mr-1"></i> Activated
          </div>
          <div v-else-if="plan.is_recommended" class="tw-bg-amber-400 tw-text-white tw-text-center tw-py-2 tw-font-bold tw-text-sm tw-uppercase tw-tracking-wide">
            <i class="fas fa-star tw-mr-1"></i> Recommended
          </div>

          <div class="tw-p-4 sm:tw-p-6 tw-flex-1 tw-flex tw-flex-col">
            <div class="tw-text-center tw-mb-4 sm:tw-mb-6">
              <h4 class="tw-text-lg sm:tw-text-xl tw-font-bold tw-text-slate-800 tw-mb-1 sm:tw-mb-2">{{ plan.name }}</h4>
              <h2 class="tw-text-2xl sm:tw-text-3xl tw-font-extrabold tw-text-indigo-600">{{ currencySymbol }}{{ formatAmount(plan.price) }}</h2>
            </div>
            
            <ul class="tw-space-y-2 sm:tw-space-y-3 tw-mb-6 sm:tw-mb-8 tw-flex-1">
              <li class="tw-flex tw-items-start tw-gap-2 sm:tw-gap-3">
                <div class="tw-bg-emerald-100 tw-text-emerald-600 tw-rounded-full tw-p-1 tw-w-5 tw-h-5 sm:tw-w-6 sm:tw-h-6 tw-flex tw-items-center tw-justify-center tw-shrink-0 tw-mt-0.5">
                  <i class="fas fa-check tw-text-[10px] sm:tw-text-xs"></i>
                </div>
                <span class="tw-text-slate-600 tw-text-xs sm:tw-text-sm">
                  Valid: <strong class="tw-text-slate-800">{{ plan.validity_days }} days ({{ formatValidity(plan.validity_days) }})</strong>
                </span>
              </li>
              <li class="tw-flex tw-items-start tw-gap-2 sm:tw-gap-3">
                <div class="tw-bg-emerald-100 tw-text-emerald-600 tw-rounded-full tw-p-1 tw-w-5 tw-h-5 sm:tw-w-6 sm:tw-h-6 tw-flex tw-items-center tw-justify-center tw-shrink-0 tw-mt-0.5">
                  <i class="fas fa-check tw-text-[10px] sm:tw-text-xs"></i>
                </div>
                <span class="tw-text-slate-600 tw-text-xs sm:tw-text-sm">
                  <strong class="tw-text-slate-800">{{ plan.daily_ad_limit }}</strong> ads per day
                </span>
              </li>
              <li class="tw-flex tw-items-start tw-gap-2 sm:tw-gap-3">
                <div class="tw-bg-emerald-100 tw-text-emerald-600 tw-rounded-full tw-p-1 tw-w-5 tw-h-5 sm:tw-w-6 sm:tw-h-6 tw-flex tw-items-center tw-justify-center tw-shrink-0 tw-mt-0.5">
                  <i class="fas fa-check tw-text-[10px] sm:tw-text-xs"></i>
                </div>
                <span class="tw-text-slate-600 tw-text-xs sm:tw-text-sm">
                  Instant reward after each ad
                </span>
              </li>
              <li class="tw-flex tw-items-start tw-gap-2 sm:tw-gap-3">
                <div class="tw-bg-amber-100 tw-text-amber-600 tw-rounded-full tw-p-1 tw-w-5 tw-h-5 sm:tw-w-6 sm:tw-h-6 tw-flex tw-items-center tw-justify-center tw-shrink-0 tw-mt-0.5">
                  <i class="fas fa-star tw-text-[10px] sm:tw-text-xs"></i>
                </div>
                <span class="tw-text-slate-600 tw-text-[10px] sm:tw-text-xs tw-italic">
                  {{ getPlanBenefit(plan) }}
                </span>
              </li>
            </ul>

            <div v-if="activePlanId === plan.id" class="tw-grid tw-grid-cols-1 tw-gap-2 sm:tw-gap-3">
              <button
                class="tw-w-full tw-py-2.5 sm:tw-py-3 tw-bg-emerald-500 tw-text-white tw-font-bold tw-rounded-xl tw-cursor-not-allowed tw-opacity-90 tw-flex tw-items-center tw-justify-center tw-gap-2 tw-border-0 tw-text-sm"
                disabled
              >
                <i class="fas fa-check-circle"></i> Activated
              </button>
              <button
                type="button"
                @click="initiatePayment(plan)"
                :disabled="!termsAccepted"
                class="tw-w-full tw-py-2.5 sm:tw-py-3 tw-bg-white tw-text-emerald-700 tw-font-bold tw-rounded-xl tw-border tw-border-emerald-200 hover:tw-border-emerald-300 hover:tw-bg-emerald-50 tw-transition-all tw-flex tw-items-center tw-justify-center tw-gap-2 tw-text-sm"
                :class="!termsAccepted ? 'tw-opacity-60 tw-cursor-not-allowed' : ''"
              >
                <i class="fas fa-undo"></i> Renew Plan
              </button>
            </div>
            <button
              v-else
              @click="initiatePayment(plan)"
              :disabled="!termsAccepted"
              class="tw-w-full tw-py-2.5 sm:tw-py-3 tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-font-bold tw-rounded-xl tw-shadow-lg tw-shadow-indigo-500/20 tw-transition-all tw-flex tw-items-center tw-justify-center tw-gap-2 tw-border-0 tw-cursor-pointer tw-text-sm"
              :class="!termsAccepted ? 'tw-bg-slate-500 hover:tw-bg-slate-500 tw-opacity-70 tw-cursor-not-allowed tw-shadow-none' : ''"
            >
              <i class="fas fa-shopping-cart"></i> Buy Now
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
  name: 'AdPlans',
  components: {
    DashboardLayout
  },
  setup() {
    const adPlans = ref([])
    const currencySymbol = ref('₹')
    const activePlanId = ref(null)
    const termsAccepted = ref(false)
    const infoTitle = ref('How Ad Plans Work')
    const infoDescription = ref('')
    const infoBullets = ref([
      'Each ad takes <strong class="tw-text-white">1 minute</strong> to watch completely',
      'Higher plans = More ads per day = More earning potential',
      'Plans are valid for specific number of days (7-60 days)'
    ])
    const rewardMin = ref(1000)
    const rewardMax = ref(5000)

    const formatAmount = (amount) => {
      if (!amount && amount !== 0) return '0.00'
      return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    }

    const initiatePayment = async (plan) => {
      if (!plan || !plan.id) {
        console.error('Invalid plan:', plan)
        return
      }

      if (!termsAccepted.value) {
        if (window.notify) window.notify('error', 'Please accept Terms & Privacy Policy before payment.')
        return
      }

      // Open same-site redirect page in new tab (reliable in all browsers)
      const redirectUrl = `/user/payment-redirect?flow=ad_plan&plan_id=${encodeURIComponent(plan.id)}&amount=${plan.price}&plan_name=${encodeURIComponent(plan.name)}&back=${encodeURIComponent('/user/ad-plans')}`
      const w = window.open(redirectUrl, '_blank')
      if (!w) {
        // Popup blocked: fallback to same tab
        window.location.href = redirectUrl
      } else if (window.notify) {
        window.notify('info', 'Payment tab opened. Complete payment to activate your ad plan.')
      }
    }

    const fetchAdPlans = async () => {
      try {
        const response = await api.get('/ad-plans')
        console.log('Ad Plans API Response:', response.data)
        
        if (response.data.status === 'success') {
          const responseData = response.data.data || {}
          const plans = responseData.data || []
          
          adPlans.value = Array.isArray(plans) ? plans : []
          currencySymbol.value = responseData.currency_symbol || '₹'

          const ui = responseData.ui || {}
          if (ui && typeof ui === 'object') {
            if (ui.info_title) infoTitle.value = ui.info_title
            if (ui.info_description) infoDescription.value = ui.info_description
            if (Array.isArray(ui.info_bullets) && ui.info_bullets.length) {
              // Always remove the old "Earn {reward_min}-{reward_max} per ad" line from UI
              infoBullets.value = ui.info_bullets.filter(b => {
                const s = String(b || '')
                return !(s.includes('{reward_min}') || s.includes('{reward_max}'))
              })
            }
            if (ui.reward_min != null) rewardMin.value = Number(ui.reward_min) || rewardMin.value
            if (ui.reward_max != null) rewardMax.value = Number(ui.reward_max) || rewardMax.value
          }
          
          if (adPlans.value.length === 0) {
            if (window.notify) {
              window.notify('warning', 'No ad plans available at the moment')
            }
          }
        } else {
          const errorMsg = response.data.message?.error?.[0] || response.data.message?.success?.[0] || 'Failed to load ad plans'
          if (window.notify) {
            window.notify('error', errorMsg)
          }
        }
      } catch (error) {
        console.error('Error loading ad plans:', error)
        const errorMsg = error.response?.data?.message?.error?.[0] || error.response?.data?.message || 'Failed to load ad plans. Please try again.'
        if (window.notify) {
          window.notify('error', errorMsg)
        }
      }
    }

    const renderBullet = (b) => {
      const s = String(b || '')
      return s
        .replaceAll('{currency}', currencySymbol.value || '₹')
        .replaceAll('{reward_min}', formatAmount(rewardMin.value))
        .replaceAll('{reward_max}', formatAmount(rewardMax.value))
    }

    const fetchActivePlan = async () => {
      try {
        // Fetch ads/work to get the active ad package info
        const response = await api.get('/ads/work')
        if (response.data.status === 'success' && response.data.data?.active_package) {
          const activePackage = response.data.data.active_package
          // Backend provides package_id (AdPackage ID) for active plan
          activePlanId.value = activePackage.package_id || null
        } else {
          activePlanId.value = null
        }
      } catch (error) {
        console.error('Error fetching active plan:', error)
        activePlanId.value = null
      }
    }

    onMounted(async () => {
      await fetchAdPlans()
      await fetchActivePlan()
    })

    const formatValidity = (days) => {
      const d = Number(days) || 0
      if (d <= 30) return '1 month'
      if (d <= 60) return '2 months'
      if (d <= 180) return '6 months'
      if (d <= 365) return '12 months'
      const months = Math.round(d / 30)
      return `${months} month${months > 1 ? 's' : ''}`
    }

    const getPlanBenefit = (plan) => {
      const price = Number(plan.price) || 0
      if (price <= 2999) return 'Standard Earning Plan'
      if (price <= 4999) return 'Better ROI & Longer Validity'
      if (price <= 7499) return 'High Profit & Half Yearly Access'
      return 'Maximum Returns & Full Year VIP Access'
    }

    return {
      adPlans,
      currencySymbol,
      formatAmount,
      initiatePayment,
      activePlanId,
      termsAccepted,
      infoTitle,
      infoDescription,
      infoBullets,
      renderBullet,
      formatValidity,
      getPlanBenefit,
    }
  }
}
</script>

<style scoped>
@media (max-width: 640px) {
  /* Terms Gate */
  .tw-bg-amber-500\/10 { padding: 0.75rem !important; border-radius: 0.85rem !important; }
  .tw-text-white.tw-font-bold.tw-mb-1 { font-size: 0.85rem !important; }
  .tw-text-white\/90.tw-text-\[11px\] { font-size: 10px !important; }
  
  /* Info Alert */
  .tw-mb-6.sm\:tw-mb-8 { margin-bottom: 1rem !important; }
  .tw-bg-slate-800\/80 { padding: 0.85rem !important; border-radius: 1rem !important; }
  .tw-bg-indigo-500\/20.tw-p-2 { padding: 0.5rem !important; }
  .tw-text-lg.sm\:tw-text-2xl { font-size: 1.15rem !important; }
  h5.tw-text-white.tw-font-bold { font-size: 0.95rem !important; margin-bottom: 0.5rem !important; }
  ul.tw-text-slate-300 { font-size: 10px !important; space-y: 1 !important; }
  
  /* Plans Grid */
  .tw-gap-6 { gap: 1rem !important; }
  
  /* Plan Cards */
  .tw-rounded-2xl { border-radius: 1rem !important; }
  .tw-bg-emerald-500.tw-text-white.tw-py-2 { padding: 0.35rem !important; font-size: 11px !important; }
  .tw-bg-amber-400.tw-text-white.tw-py-2 { padding: 0.35rem !important; font-size: 11px !important; }
  
  .tw-p-4.sm\:tw-p-6 { padding: 1rem !important; }
  h4.tw-text-lg { font-size: 1.1rem !important; margin-bottom: 0.25rem !important; }
  h2.tw-text-2xl { font-size: 1.75rem !important; }
  
  /* Plan Features */
  .tw-space-y-2 { space-y: 1.5 !important; margin-bottom: 1rem !important; }
  .tw-bg-emerald-100 { width: 1.25rem !important; height: 1.25rem !important; border-radius: 0.5rem !important; }
  .tw-bg-amber-100 { width: 1.25rem !important; height: 1.25rem !important; border-radius: 0.5rem !important; }
  span.tw-text-slate-600 { font-size: 11px !important; }
  span.tw-text-\[10px\] { font-size: 9px !important; }
  
  /* Buttons */
  .tw-py-2\.5 { padding: 0.65rem !important; font-size: 0.85rem !important; border-radius: 0.85rem !important; }
}
</style>
