<template>
  <DashboardLayout page-title="My Team / Referral" :dark-theme="true">
    <!-- Partner Program Restriction Modal -->
    <div v-if="showPartnerModal" class="tw-fixed tw-inset-0 tw-z-[100] tw-flex tw-items-center tw-justify-center tw-px-4">
      <div class="tw-absolute tw-inset-0 tw-bg-slate-950/80 tw-backdrop-blur-md" @click="showPartnerModal = false"></div>
      <div class="tw-bg-[#151921] tw-rounded-2xl sm:tw-rounded-[34px] tw-shadow-2xl tw-w-full tw-max-w-md tw-relative tw-z-10 tw-overflow-hidden tw-border tw-border-white/10 tw-animate-fade-in-up">
        <div class="tw-p-6 sm:tw-p-10 tw-text-center">
          <div class="tw-w-14 tw-h-14 sm:tw-w-20 sm:tw-h-20 tw-bg-indigo-500/10 tw-rounded-xl sm:tw-rounded-3xl tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-4 sm:tw-mb-8 tw-border tw-border-indigo-500/20">
            <i class="fas fa-crown tw-text-2xl sm:tw-text-4xl tw-text-indigo-400"></i>
          </div>
          <h3 class="tw-text-lg sm:tw-text-2xl tw-font-black tw-text-white tw-mb-2 sm:tw-mb-4">Join Partner Program</h3>
          <p class="tw-text-slate-400 tw-leading-relaxed tw-mb-6 sm:tw-mb-8 tw-text-xs sm:tw-text-sm">
            You need to join our <b>Partner Program</b> to access the Referral & Team Building tools. Unlock your earning potential today!
          </p>
          <div class="tw-flex tw-flex-col tw-gap-3 sm:tw-gap-4">
            <router-link
              to="/user/partner-program"
              class="tw-w-full tw-py-3 sm:tw-py-4 tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-font-black tw-rounded-xl sm:tw-rounded-2xl tw-transition-all tw-no-underline tw-flex tw-items-center tw-justify-center tw-gap-2 tw-shadow-lg tw-shadow-indigo-500/20 tw-text-sm sm:tw-text-base"
            >
              <i class="fas fa-rocket"></i> View Partner Plans
            </router-link>
            <button
              @click="showPartnerModal = false"
              class="tw-w-full tw-py-3 sm:tw-py-4 tw-bg-white/5 hover:tw-bg-white/10 tw-text-slate-400 tw-font-bold tw-rounded-xl sm:tw-rounded-2xl tw-transition-all tw-border-0 tw-cursor-pointer tw-text-xs sm:tw-text-sm"
            >
              Maybe Later
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-12 tw-gap-8">
      
      <!-- Main Content -->
      <div class="lg:tw-col-span-8 tw-flex tw-flex-col tw-gap-8">
        


        <!-- General Referral Link (KEPT ORIGINAL) -->
        <div class="tw-bg-white tw-rounded-xl sm:tw-rounded-2xl tw-shadow-sm tw-border tw-border-slate-200 tw-overflow-hidden">
          <div class="tw-bg-slate-50 tw-p-3 sm:tw-p-5 tw-border-b tw-border-slate-200">
            <h5 class="tw-text-slate-900 tw-font-bold tw-text-base sm:tw-text-lg tw-m-0 tw-flex tw-items-center">
              <i class="fas fa-link tw-mr-2 tw-text-indigo-600"></i>General Link
            </h5>
          </div>
          <div class="tw-p-4 sm:tw-p-6">
            <div class="tw-flex tw-flex-col md:tw-flex-row tw-gap-2 sm:tw-gap-3 tw-mb-3 sm:tw-mb-4">
              <input 
                type="text" 
                :value="referralLink" 
                class="tw-flex-1 tw-px-3 tw-py-2 tw-bg-slate-50 tw-border tw-border-slate-300 tw-rounded-lg tw-text-slate-600 tw-font-mono tw-text-xs sm:tw-text-sm focus:tw-outline-none"
                readonly 
                :id="'referralLink'"
              >
              <button 
                type="button" 
                class="tw-px-4 tw-py-2 tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-font-bold tw-rounded-lg tw-transition-all tw-flex tw-items-center tw-justify-center tw-gap-2 tw-border-0 tw-cursor-pointer tw-text-xs sm:tw-text-sm"
                @click="copyReferralLink('referralLink')"
              >
                <i class="fas fa-copy"></i> Copy Link
              </button>
            </div>
            <p v-if="referralCode" class="tw-text-slate-600 tw-text-[11px] sm:tw-text-sm tw-mb-2 tw-flex tw-items-center tw-gap-2">
              <span class="tw-font-medium">Code:</span>
              <code class="tw-bg-slate-100 tw-px-1.5 tw-py-0.5 tw-rounded tw-font-mono tw-font-bold tw-text-indigo-600">{{ referralCode }}</code>
            </p>
          </div>
        </div>

        <!-- Package-Specific Links (KEPT ORIGINAL) -->
        <div class="tw-bg-white tw-rounded-xl sm:tw-rounded-2xl tw-shadow-sm tw-border tw-border-slate-200 tw-overflow-hidden">
          <div class="tw-bg-slate-50 tw-p-3 sm:tw-p-5 tw-border-b tw-border-slate-200">
            <h5 class="tw-text-slate-900 tw-font-bold tw-text-base sm:tw-text-lg tw-m-0 tw-flex tw-items-center">
              <i class="fas fa-gift tw-mr-2 tw-text-indigo-600"></i>Plan-Specific Links
            </h5>
          </div>
          <div class="tw-p-4 sm:tw-p-6">
            <div class="tw-flex tw-flex-col tw-gap-3 sm:tw-gap-4">
              <template v-for="pkgLink in packageLinks" :key="pkgLink?.package_id || Math.random()">
                <div v-if="pkgLink && pkgLink.package_id" class="tw-p-3 sm:tw-p-4 tw-rounded-lg sm:tw-rounded-xl tw-bg-slate-50 tw-border tw-border-slate-200">
                  <div class="tw-flex tw-flex-col md:tw-flex-row md:tw-items-center tw-justify-between tw-gap-1 tw-mb-2">
                    <h6 class="tw-text-slate-900 tw-font-bold tw-text-sm sm:tw-text-base tw-m-0">{{ pkgLink.package_name }}</h6>
                    <div class="tw-text-[10px] sm:tw-text-sm tw-text-slate-500">
                      Price: <span class="tw-font-medium">{{ currencySymbol }}{{ formatAmount(pkgLink.original_price) }}</span>
                    </div>
                  </div>
                  <div class="tw-flex tw-gap-2">
                    <input
                      type="text"
                      :value="pkgLink.link"
                      class="tw-flex-1 tw-px-2 tw-py-1.5 tw-bg-white tw-border tw-border-slate-300 tw-rounded-lg tw-text-slate-600 tw-font-mono tw-text-[10px] focus:tw-outline-none"
                      readonly
                      :id="`pkgLink${pkgLink.package_id}`"
                    >
                    <button
                      type="button"
                      class="tw-px-3 tw-py-1.5 tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-font-bold tw-text-[10px] tw-rounded-lg tw-transition-all tw-border-0 tw-cursor-pointer"
                      @click="copyReferralLink(`pkgLink${pkgLink.package_id}`)"
                    >
                      Copy
                    </button>
                  </div>
                </div>
              </template>
            </div>
          </div>
        </div>

        <!-- Special Discount Links (KEPT ORIGINAL) -->
        <div v-if="globalSpecialLinks.length > 0" class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-slate-200 tw-overflow-hidden">
          <div class="tw-bg-slate-50 tw-p-5 tw-border-b tw-border-slate-200">
            <h5 class="tw-text-slate-900 tw-font-bold tw-text-lg tw-m-0 tw-flex tw-items-center">
              <i class="fas fa-bolt tw-mr-2 tw-text-indigo-600"></i>Special Discount Links
            </h5>
          </div>
          <div class="tw-p-6">
            <div class="tw-flex tw-flex-col tw-gap-4">
              <template v-for="sLink in globalSpecialLinks" :key="sLink?.id || Math.random()">
                <div v-if="sLink && sLink.id" class="tw-p-4 tw-rounded-xl tw-bg-slate-50 tw-border tw-border-slate-200 hover:tw-border-indigo-200 hover:tw-bg-white hover:tw-shadow-md tw-transition-all">
                  <div class="tw-flex tw-flex-col md:tw-flex-row md:tw-items-center tw-justify-between tw-gap-2 tw-mb-3">
                    <h6 class="tw-text-slate-900 tw-font-bold tw-text-base tw-m-0">{{ sLink.package_name }}</h6>
                    <div class="tw-text-sm tw-text-slate-600 tw-flex tw-flex-wrap tw-gap-x-4 tw-gap-y-1">
                      <span>Price: <span class="tw-font-medium">{{ currencySymbol }}{{ formatAmount(sLink.original_price) }}</span></span>
                      <span>Discount: <span class="tw-font-medium">{{ currencySymbol }}{{ formatAmount(sLink.discount) }}</span></span>
                      <span>Final: <span class="tw-font-bold tw-text-emerald-600">{{ currencySymbol }}{{ formatAmount(sLink.final_price) }}</span></span>
                    </div>
                  </div>
                  <div class="tw-flex tw-gap-2">
                    <input
                      type="text"
                      :value="sLink.link"
                      class="tw-flex-1 tw-px-3 tw-py-2 tw-bg-white tw-border tw-border-slate-300 tw-rounded-lg tw-text-slate-600 tw-font-mono tw-text-xs focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-indigo-500/20"
                      readonly
                      :id="`specialLink${sLink.id}`"
                    >
                    <button
                      type="button"
                      class="tw-px-4 tw-py-2 tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-font-bold tw-text-xs tw-rounded-lg tw-transition-all tw-border-0 tw-cursor-pointer"
                      @click="copyReferralLink(`specialLink${sLink.id}`)"
                    >
                      Copy
                    </button>
                  </div>
                </div>
              </template>
            </div>
          </div>
        </div>

      </div>

      <!-- Right Sidebar -->
      <div class="lg:tw-col-span-4 tw-flex tw-flex-col tw-gap-8">
        
        <!-- Affiliate Wallet -->
        <div 
          class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-slate-200 tw-overflow-hidden tw-cursor-pointer group"
          @click="handleAffiliateAction(null)"
        >
          <div class="tw-bg-slate-50 tw-p-5 tw-border-b tw-border-slate-200 group-hover:tw-bg-slate-100 tw-transition-colors">
            <h5 class="tw-text-slate-900 tw-font-bold tw-text-lg tw-m-0 tw-flex tw-items-center">
              <i class="fas fa-wallet tw-mr-2 tw-text-emerald-600"></i>Affiliate Wallet
            </h5>
          </div>
          <div class="tw-p-6 tw-flex tw-flex-col tw-gap-3">
            <button
              @click.stop="handleAffiliateAction('/user/affiliate-income')"
              class="tw-w-full tw-py-3 tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-font-bold tw-rounded-xl tw-text-center tw-no-underline tw-transition-all tw-text-sm tw-border-0 tw-cursor-pointer"
            >
              View Affiliate Dashboard
            </button>
            <button
              @click.stop="handleAffiliateAction('/user/affiliate-withdraw')"
              class="tw-w-full tw-py-3 tw-bg-emerald-600 hover:tw-bg-emerald-700 tw-text-white tw-font-bold tw-rounded-xl tw-text-center tw-no-underline tw-transition-all tw-text-sm tw-border-0 tw-cursor-pointer"
            >
              Withdraw Affiliate Income
            </button>
            <router-link
              to="/user/account-kyc"
              @click.stop
              class="tw-w-full tw-py-3 tw-bg-slate-900 hover:tw-bg-slate-800 tw-text-white tw-font-bold tw-rounded-xl tw-text-center tw-no-underline tw-transition-all tw-text-sm"
            >
              KYC (Required)
            </router-link>
          </div>
        </div>

        <!-- Referral Earning -->
        <div class="tw-bg-white tw-rounded-xl sm:tw-rounded-2xl tw-shadow-xl tw-border-2 tw-border-indigo-100 tw-overflow-hidden">
          <div class="tw-bg-indigo-600 tw-p-4 sm:tw-p-6 tw-text-white tw-flex tw-items-center tw-justify-between">
            <h5 class="tw-font-bold tw-text-base sm:tw-text-lg tw-m-0">Referral Earning</h5>
            <span class="tw-text-indigo-200 tw-text-lg sm:tw-text-xl tw-font-bold">₹</span>
          </div>
          <div class="tw-p-4 sm:tw-p-6 tw-flex tw-flex-col tw-gap-0">
            
            <div class="tw-py-3 sm:tw-py-4 tw-border-b tw-border-slate-100">
              <p class="tw-text-[9px] sm:tw-text-[10px] tw-font-bold tw-uppercase tw-text-slate-400 tw-mb-0.5 sm:tw-mb-1">Today Earning</p>
              <h4 class="tw-text-xl sm:tw-text-2xl tw-font-extrabold tw-text-emerald-500 tw-m-0">
                {{ currencySymbol }}{{ formatAmount(todayEarningAnim) }}
              </h4>
            </div>

            <div class="tw-py-3 sm:tw-py-4 tw-border-b tw-border-slate-100">
              <p class="tw-text-[9px] sm:tw-text-[10px] tw-font-bold tw-uppercase tw-text-slate-400 tw-mb-0.5 sm:tw-mb-1">This Month</p>
              <h4 class="tw-text-xl sm:tw-text-2xl tw-font-extrabold tw-text-indigo-500 tw-m-0">
                {{ currencySymbol }}{{ formatAmount(thisMonthEarningAnim) }}
              </h4>
            </div>

            <div class="tw-py-3 sm:tw-py-4">
              <p class="tw-text-[9px] sm:tw-text-[10px] tw-font-bold tw-uppercase tw-text-slate-400 tw-mb-0.5 sm:tw-mb-1">Total Earning</p>
              <h4 class="tw-text-2xl sm:tw-text-3xl tw-font-extrabold tw-text-indigo-600 tw-m-0">
                {{ currencySymbol }}{{ formatAmount(totalEarningAnim) }}
              </h4>
            </div>

          </div>
        </div>
      </div>
      
    </div>
  </DashboardLayout>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import DashboardLayout from '../../components/DashboardLayout.vue'
import api from '../../services/api'

export default {
  name: 'Referral',
  components: {
    DashboardLayout
  },
  setup() {
    const router = useRouter()
    const referralCode = ref('')
    const referralLink = ref('')
    const packageLinks = ref([])
    const globalSpecialLinks = ref([])
    const referralEarning = ref({
      today: 0,
      this_month: 0,
      total: 0
    })
    const currencySymbol = ref('₹')
    const isPartner = ref(false)
    const isAgent = ref(false)
    const showPartnerModal = ref(false)

    // Count-up animated values
    const todayEarningAnim = ref(0)
    const thisMonthEarningAnim = ref(0)
    const totalEarningAnim = ref(0)

    const animSeqMap = new WeakMap()
    const animateTo = (targetRef, to, duration = 1000) => {
      const seq = (animSeqMap.get(targetRef) || 0) + 1
      animSeqMap.set(targetRef, seq)
      const toNum = Number(to) || 0
      const start = performance.now()
      const step = (now) => {
        if (animSeqMap.get(targetRef) !== seq) return
        const p = Math.min(1, (now - start) / duration)
        const eased = 1 - Math.pow(1 - p, 3) // easeOutCubic
        targetRef.value = toNum * eased
        if (p < 1) requestAnimationFrame(step)
        else targetRef.value = toNum
      }
      requestAnimationFrame(step)
    }

    const formatAmount = (amount) => {
      if (!amount && amount !== 0) return '0.00'
      return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    }

    const formatDate = (dateString) => {
      if (!dateString) return '-'
      return new Date(dateString).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
    }

    const copyReferralLink = (inputId) => {
      const input = document.getElementById(inputId)
      if (input) {
        input.select()
        document.execCommand('copy')
        if (window.notify) {
          window.notify('success', 'Referral link copied successfully!')
        }
      }
    }

    const fetchReferralData = async () => {
      try {
        const response = await api.get('/referral')
        if (response.data.status === 'success' && response.data.data) {
          referralCode.value = response.data.data.referral_code || ''
          referralLink.value = response.data.data.referral_link || ''
          packageLinks.value = response.data.data.package_links || []
          globalSpecialLinks.value = response.data.data.global_special_links || []
          referralEarning.value = response.data.data.referral_earning || referralEarning.value
          currencySymbol.value = response.data.data?.currency_symbol || response.data.currency_symbol || '₹'
          isPartner.value = response.data.data.is_partner || false
          isAgent.value = response.data.data.is_agent || false

          // Start count-up animations
          animateTo(todayEarningAnim, referralEarning.value.today)
          animateTo(thisMonthEarningAnim, referralEarning.value.this_month)
          animateTo(totalEarningAnim, referralEarning.value.total)
        }
      } catch (error) {
        console.error('Error loading referral data:', error)
      }
    }

    const handleAffiliateAction = (route) => {
      if (isAgent.value) {
        if (route) router.push(route)
      } else if (isPartner.value) {
        if (window.notify) {
          window.notify('info', 'You have joined the partner program. Please wait for admin to enable your affiliate dashboard.')
        }
      } else {
        showPartnerModal.value = true
      }
    }

    onMounted(() => {
      fetchReferralData()
    })

    return {
      referralCode,
      referralLink,
      packageLinks,
      globalSpecialLinks,
      referralEarning,
      currencySymbol,
      formatAmount,
      formatDate,
      copyReferralLink,
      todayEarningAnim,
      thisMonthEarningAnim,
      totalEarningAnim,
      isPartner,
      isAgent,
      showPartnerModal,
      handleAffiliateAction
    }
  }
}
</script>

<style scoped>
@media (max-width: 640px) {
  /* Partner Modal */
  .tw-bg-\[\#151921\].tw-rounded-\[34px\] { border-radius: 1.5rem !important; }
  .tw-p-10 { padding: 1.5rem !important; }
  .tw-w-20.tw-h-20 { width: 3.5rem !important; height: 3.5rem !important; border-radius: 1rem !important; margin-bottom: 1.5rem !important; }
  .tw-w-20.tw-h-20 i { font-size: 1.75rem !important; }
  h3.tw-text-2xl { font-size: 1.25rem !important; }
  .tw-py-4 { padding: 0.85rem !important; border-radius: 1rem !important; font-size: 0.9rem !important; }

  /* Links & Cards */
  .tw-p-4.sm\:tw-p-6 { padding: 1rem !important; }
  .tw-p-3.sm\:tw-p-5 { padding: 0.85rem 1rem !important; }
  h5.tw-text-base { font-size: 1rem !important; }
  input.tw-py-2 { padding: 0.65rem 0.85rem !important; font-size: 0.75rem !important; border-radius: 0.75rem !important; }
  .tw-px-4.tw-py-2 { padding: 0.65rem 1rem !important; font-size: 0.85rem !important; border-radius: 0.75rem !important; }

  /* Plan Specific Box */
  .tw-p-3.sm\:tw-p-4 { padding: 0.85rem !important; border-radius: 0.85rem !important; }
  h6.tw-text-sm { font-size: 0.85rem !important; }
  .tw-text-\[10px\] { font-size: 0.65rem !important; }
  input.tw-py-1\.5 { padding: 0.5rem !important; font-size: 0.65rem !important; border-radius: 0.5rem !important; }
  .tw-px-3.tw-py-1\.5 { padding: 0.5rem 0.75rem !important; font-size: 0.65rem !important; border-radius: 0.5rem !important; }

  /* Wallet & Earning Sidebar Cards */
  .tw-p-5 { padding: 0.85rem 1rem !important; }
  .tw-p-6 { padding: 1rem !important; }
  .tw-py-3 { padding: 0.75rem !important; border-radius: 0.85rem !important; font-size: 0.85rem !important; }
  .tw-text-xl\.sm\:tw-text-2xl { font-size: 1.35rem !important; }
  .tw-py-3.sm\:tw-py-4 { padding: 0.75rem 0 !important; }
}
</style>
