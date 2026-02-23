<template>
  <DashboardLayout 
    :page-title="pageTitleDisplay" 
    :dark-theme="true"
    :no-sidebar="flowType === 'registration'"
  >
    <div class="tw-min-h-[85vh] tw-flex tw-items-center tw-justify-center tw-p-4 tw-font-sans">
      
      <!-- Premium Dark Card -->
      <div 
        class="tw-w-full tw-max-w-[480px] tw-bg-[#0f172a] tw-rounded-[40px] tw-border tw-border-slate-800/60 tw-shadow-[0_32px_64px_-16px_rgba(0,0,0,0.6)] tw-overflow-hidden tw-relative"
      >
        <!-- Top Glow Effect -->
        <div class="tw-absolute tw-top-0 tw-left-1/2 tw--translate-x-1/2 tw-w-full tw-h-32 tw-bg-indigo-600/10 tw-blur-[80px] tw-pointer-events-none"></div>

        <div class="tw-relative tw-p-8 sm:tw-p-12 tw-text-center">
          
          <!-- Animated Plan Icon Circle -->
          <div class="tw-relative tw-mx-auto tw-mb-8 tw-w-20 tw-h-20">
            <div class="tw-absolute tw-inset-0 tw-bg-indigo-500/20 tw-rounded-full tw-animate-ping tw-animation-slow"></div>
            <div class="tw-relative tw-w-full tw-h-full tw-bg-gradient-to-br tw-from-indigo-600 tw-to-violet-700 tw-rounded-3xl tw-flex tw-items-center tw-justify-center tw-shadow-2xl tw-shadow-indigo-500/30 tw-rotate-3">
              <i v-if="flowType === 'ad_plan'" class="fas fa-ad tw-text-white tw-text-3xl tw--rotate-3"></i>
              <i v-else-if="flowType === 'course_plan'" class="fas fa-graduation-cap tw-text-white tw-text-3xl tw--rotate-3"></i>
              <i v-else-if="flowType === 'kyc_fee'" class="fas fa-user-shield tw-text-white tw-text-3xl tw--rotate-3"></i>
              <i v-else-if="flowType === 'withdraw_gst'" class="fas fa-receipt tw-text-white tw-text-3xl tw--rotate-3"></i>
              <i v-else class="fas fa-credit-card tw-text-white tw-text-3xl tw--rotate-3"></i>
            </div>
          </div>

          <!-- Plan Info -->
            <h5 class="tw-text-slate-400 tw-text-[13px] tw-font-bold tw-uppercase tw-tracking-[0.2em] tw-mb-4">{{ itemName }}</h5>
            <div class="tw-text-6xl tw-font-black tw-text-white tw-tracking-tight tw-flex tw-items-center tw-justify-center tw-gap-1">
              <span class="tw-text-indigo-500 tw-text-3xl tw-font-bold">₹</span>
              {{ formatAmount(itemAmount) }}
            </div>

          <!-- Divider -->
          <div class="tw-relative tw-h-px tw-w-full tw-bg-slate-800 tw-mb-10">
            <div class="tw-absolute tw-inset-0 tw-bg-gradient-to-r tw-from-transparent tw-via-indigo-500/40 tw-to-transparent"></div>
          </div>

          <!-- Gateway Selection -->
          <div v-if="status === 'selecting'">
            <div class="tw-text-[11px] tw-font-bold tw-text-slate-500 tw-uppercase tw-tracking-[0.25em] tw-mb-8">
              Select Payment Gateway
            </div>

            <div class="tw-flex tw-flex-col tw-gap-5 tw-mb-10">
              <!-- WatchPay Option -->
              <button 
                v-if="gatewayStatuses.watchpay"
                @click="selectedGateway = 'watchpay'"
                class="tw-group tw-w-full tw-text-left tw-bg-slate-900/50 tw-border tw-border-slate-800 tw-rounded-[24px] tw-p-5 tw-transition-all tw-duration-300 hover:tw-bg-slate-800/50 hover:tw-border-indigo-500/50 hover:tw-shadow-lg hover:tw-shadow-indigo-500/5 tw-flex tw-items-center tw-justify-between"
                :class="selectedGateway === 'watchpay' ? 'tw-border-indigo-500 tw-bg-indigo-500/5 tw-ring-1 tw-ring-indigo-500/50' : ''"
              >
                <div class="tw-flex tw-items-center">
                  <div class="tw-w-14 tw-h-14 tw-bg-indigo-600 tw-rounded-2xl tw-flex tw-items-center tw-justify-center tw-mr-5 tw-shadow-lg tw-shadow-indigo-600/20 tw-transition-transform group-hover:tw-scale-110">
                    <i class="fas fa-eye tw-text-white tw-text-xl"></i>
                  </div>
                  <div>
                    <div class="tw-font-bold tw-text-white tw-text-lg">WatchPay</div>
                    <div class="tw-text-xs tw-text-slate-500">Fast & Secure UPI Payment</div>
                  </div>
                </div>
                <div class="tw-w-10 tw-h-10 tw-bg-slate-800/50 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-transition-colors group-hover:tw-bg-indigo-500/20">
                    <i class="fas fa-chevron-right tw-text-slate-500 tw-text-sm tw-transition-transform group-hover:tw-translate-x-0.5 group-hover:tw-text-indigo-400"></i>
                </div>
              </button>

              <!-- SimplyPay Option -->
              <button 
                v-if="gatewayStatuses.simplypay"
                @click="selectedGateway = 'simplypay'"
                class="tw-group tw-w-full tw-text-left tw-bg-slate-900/50 tw-border tw-border-slate-800 tw-rounded-[24px] tw-p-5 tw-transition-all tw-duration-300 hover:tw-bg-slate-800/50 hover:tw-border-emerald-500/50 hover:tw-shadow-lg hover:tw-shadow-emerald-500/5 tw-flex tw-items-center tw-justify-between"
                :class="selectedGateway === 'simplypay' ? 'tw-border-emerald-500 tw-bg-emerald-500/5 tw-ring-1 tw-ring-emerald-500/50' : ''"
              >
                <div class="tw-flex tw-items-center">
                  <div class="tw-w-14 tw-h-14 tw-bg-emerald-600 tw-rounded-2xl tw-flex tw-items-center tw-justify-center tw-mr-5 tw-shadow-lg tw-shadow-emerald-600/20 tw-transition-transform group-hover:tw-scale-110">
                    <i class="fas fa-credit-card tw-text-white tw-text-xl"></i>
                  </div>
                  <div>
                    <div class="tw-font-bold tw-text-white tw-text-lg">SimplyPay</div>
                    <div class="tw-text-xs tw-text-slate-500">Cards, Netbanking & Wallets</div>
                  </div>
                </div>
                <div class="tw-w-10 tw-h-10 tw-bg-slate-800/50 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-transition-colors group-hover:tw-bg-emerald-500/20">
                    <i class="fas fa-chevron-right tw-text-slate-500 tw-text-sm tw-transition-transform group-hover:tw-translate-x-0.5 group-hover:tw-text-emerald-400"></i>
                </div>
              </button>

              <!-- RupeeRush Option -->
              <button 
                v-if="gatewayStatuses.rupeerush"
                @click="selectedGateway = 'rupeerush'"
                class="tw-group tw-w-full tw-text-left tw-bg-slate-900/50 tw-border tw-border-slate-800 tw-rounded-[24px] tw-p-5 tw-transition-all tw-duration-300 hover:tw-bg-slate-800/50 hover:tw-border-orange-500/50 hover:tw-shadow-lg hover:tw-shadow-orange-500/5 tw-flex tw-items-center tw-justify-between"
                :class="selectedGateway === 'rupeerush' ? 'tw-border-orange-500 tw-bg-orange-500/5 tw-ring-1 tw-ring-orange-500/50' : ''"
              >
                <div class="tw-flex tw-items-center">
                  <div class="tw-w-14 tw-h-14 tw-bg-orange-600 tw-rounded-2xl tw-flex tw-items-center tw-justify-center tw-mr-5 tw-shadow-lg tw-shadow-orange-600/20 tw-transition-transform group-hover:tw-scale-110">
                    <i class="fas fa-indian-rupee-sign tw-text-white tw-text-xl"></i>
                  </div>
                  <div>
                    <div class="tw-font-bold tw-text-white tw-text-lg">RupeeRush</div>
                    <div class="tw-text-xs tw-text-slate-500">UPI Gateway - Rupee Rush</div>
                  </div>
                </div>
                <div class="tw-w-10 tw-h-10 tw-bg-slate-800/50 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-transition-colors group-hover:tw-bg-orange-500/20">
                    <i class="fas fa-chevron-right tw-text-slate-500 tw-text-sm tw-transition-transform group-hover:tw-translate-x-0.5 group-hover:tw-text-orange-400"></i>
                </div>
              </button>

              <!-- Custom QR Option -->
              <button 
                v-if="gatewayStatuses.customqr"
                @click="selectedGateway = 'custom_qr'"
                class="tw-group tw-w-full tw-text-left tw-bg-slate-900/50 tw-border tw-border-slate-800 tw-rounded-[24px] tw-p-5 tw-transition-all tw-duration-300 hover:tw-bg-slate-800/50 hover:tw-border-slate-500/50 hover:tw-shadow-lg hover:tw-shadow-slate-500/5 tw-flex tw-items-center tw-justify-between"
                :class="selectedGateway === 'custom_qr' ? 'tw-border-slate-400 tw-bg-slate-500/5 tw-ring-1 tw-ring-slate-400/50' : ''"
              >
                <div class="tw-flex tw-items-center">
                  <div class="tw-w-14 tw-h-14 tw-bg-slate-700 tw-rounded-2xl tw-flex tw-items-center tw-justify-center tw-mr-5 tw-shadow-lg tw-shadow-slate-600/20 tw-transition-transform group-hover:tw-scale-110">
                    <i class="fas fa-qrcode tw-text-white tw-text-xl"></i>
                  </div>
                  <div>
                    <div class="tw-font-bold tw-text-white tw-text-lg">Manual QR</div>
                    <div class="tw-text-xs tw-text-slate-500">Scan & Upload Screenshot</div>
                  </div>
                </div>
                <div class="tw-w-10 tw-h-10 tw-bg-slate-800/50 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-transition-colors group-hover:tw-bg-slate-500/20">
                    <i class="fas fa-chevron-right tw-text-slate-500 tw-text-sm tw-transition-transform group-hover:tw-translate-x-0.5 group-hover:tw-text-slate-400"></i>
                </div>
              </button>
            </div>

            <button 
              type="button"
              @click="goBack"
              class="tw-inline-flex tw-items-center tw-gap-2 tw-text-[13px] tw-font-bold tw-text-slate-500 hover:tw-text-white/80 tw-transition-all tw-duration-300 tw-bg-transparent tw-border-0 tw-cursor-pointer"
            >
              <i class="fas fa-arrow-left"></i>
              Cancel
            </button>
          </div>

          <!-- State: Processing -->
          <div v-else-if="status === 'processing'" class="tw-py-12">
            <div class="tw-relative tw-w-20 tw-h-20 tw-mx-auto tw-mb-8">
              <div class="tw-absolute tw-inset-0 tw-border-4 tw-border-indigo-500/20 tw-rounded-full"></div>
              <div class="tw-absolute tw-inset-0 tw-border-4 tw-border-indigo-500 tw-border-t-transparent tw-rounded-full tw-animate-spin"></div>
            </div>
            <div class="tw-text-white tw-font-bold tw-text-xl tw-mb-2">Securing Transaction...</div>
            <div class="tw-text-slate-400 tw-text-sm">Redirecting to {{ selectedGateway === 'simplypay' ? 'SimplyPay' : (selectedGateway === 'rupeerush' ? 'RupeeRush' : (selectedGateway === 'custom_qr' ? 'Manual QR' : 'WatchPay')) }} gateway</div>
          </div>

          <!-- State: Error -->
          <div v-else class="tw-py-8">
            <div class="tw-w-20 tw-h-20 tw-bg-rose-500/10 tw-rounded-[30px] tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-8 tw-text-rose-500">
              <i class="fas fa-exclamation-triangle tw-text-3xl"></i>
            </div>
            <h4 class="tw-font-bold tw-text-white tw-text-2xl tw-mb-3">Initiation Failed</h4>
            <p class="tw-text-slate-400 tw-mb-10 tw-max-w-[80%] tw-mx-auto">{{ errorMessage }}</p>
            <div class="tw-flex tw-flex-col tw-gap-4">
               <button 
                @click="retry" 
                class="tw-w-full tw-py-4 tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-font-bold tw-rounded-2xl tw-transition-all tw-border-0 tw-shadow-lg tw-shadow-indigo-600/20"
               >
                Try Again
               </button>
               <button 
                @click="goBack" 
                class="tw-w-full tw-py-4 tw-bg-slate-800 hover:tw-bg-slate-700 tw-text-white tw-font-bold tw-rounded-2xl tw-transition-all tw-border-0"
               >
                Back to Plans
               </button>
            </div>
          </div>

        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script>
import { ref, onMounted, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import DashboardLayout from '../../components/DashboardLayout.vue'
import api from '../../services/api'

export default {
  name: 'PaymentRedirect',
  components: { DashboardLayout },
  setup() {
    const route = useRoute()
    const router = useRouter()
    const status = ref('selecting') // selecting | processing | failed
    const errorMessage = ref('Unknown error')
    const selectedGateway = ref('')
    const gatewayStatuses = ref({
      watchpay: 1,
      simplypay: 1,
      rupeerush: 1,
      customqr: 1
    })
    
    // Dynamic content from query params
    const flowType = computed(() => route.query.flow || 'other')
    const itemName = ref(route.query.plan_name || 'Payment Request')
    const itemAmount = ref(route.query.amount || 0)
    const currencySymbol = ref('₹')

    const pageTitleDisplay = computed(() => {
       if (flowType.value === 'ad_plan') return 'Ad Plan Payment'
       if (flowType.value === 'course_plan') return 'Course Package Payment'
       if (flowType.value === 'kyc_fee') return 'KYC Fee Payment'
       if (flowType.value === 'withdraw_gst') return 'GST Payment'
       return 'Payment Gateway'
    })

    const formatAmount = (amount) => {
      if (!amount && amount !== 0) return '0.00'
      const val = typeof amount === 'string' ? parseFloat(amount) : amount
      return val.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    }

    watch(selectedGateway, (val) => {
      if (val && status.value === 'selecting') {
        startPayment()
      }
    })

    const fetchData = async () => {
       fetchGatewayStatus()
       const flow = flowType.value
       try {
          if (flow === 'ad_plan' && !route.query.plan_name) {
             const planId = parseInt(route.query.plan_id)
             const res = await api.get('/ad-plans')
             if (res.data?.status === 'success') {
                const plans = res.data.data?.data || []
                const plan = plans.find(p => p.id === planId)
                if (plan) {
                   itemName.value = plan.name
                   itemAmount.value = plan.price
                }
             }
          } else if (flow === 'course_plan' && !route.query.plan_name) {
             const planId = parseInt(route.query.plan_id)
             const res = await api.get('/course-plans')
             if (res.data?.status === 'success') {
                const plans = res.data.data?.data || res.data.data || []
                const plan = plans.find(p => p.id === planId)
                if (plan) {
                   itemName.value = plan.name
                   itemAmount.value = plan.price
                }
             }
          } else if (flow === 'kyc_fee') {
             itemName.value = 'KYC Verification Fee'
             if (!itemAmount.value || itemAmount.value == 0) itemAmount.value = 990
          } else if (flow === 'withdraw_gst') {
             itemName.value = '18% GST Fee'
          } else if (flow === 'ad_certificate') {
             itemName.value = 'Ad Certificate'
             if (!itemAmount.value || itemAmount.value == 0) itemAmount.value = 1250
          } else if (flow === 'partner_plan' && !route.query.plan_name) {
             const planId = parseInt(route.query.plan_id)
             const res = await api.get('/partner-program/plans')
             if (res.data?.status === 'success') {
                const plans = res.data.data || []
                const plan = plans.find(p => p.id === planId)
                if (plan) {
                   itemName.value = plan.name
                   itemAmount.value = plan.price
                }
             }
          }
       } catch (e) { 
         console.error('Fetch error:', e)
       }
    }

    const startPayment = async () => {
      if (!selectedGateway.value) return
      status.value = 'processing'
      errorMessage.value = 'Unknown error'

      const flow = flowType.value
      try {
        let res
        const gateway = selectedGateway.value
        
        if (flow === 'course_plan') {
          const planId = parseInt(route.query.plan_id)
          res = await api.post('/course-plans/purchase', { plan_id: planId, gateway })
        } else if (flow === 'ad_plan') {
          const planId = parseInt(route.query.plan_id)
          res = await api.post('/ad-plans/purchase', { plan_id: planId, payment_method: 'gateway', gateway })
        } else if (flow === 'partner_plan') {
          const planId = parseInt(route.query.plan_id)
          res = await api.post('/partner-program/join', { plan_id: planId, gateway })
        } else if (flow === 'kyc_fee') {
          res = await api.post('/kyc-payment', { gateway })
        } else if (flow === 'ad_certificate') {
          res = await api.post('/ad-certificate/purchase', { gateway })
        } else if (flow === 'withdraw_gst') {
          const methodCode = parseInt(route.query.method_code)
          const payoutType = String(route.query.payout_type || 'bank')
          res = await api.post('/withdraw-request/gst/initiate', { method_code: methodCode, payout_type: payoutType, gateway })
        } else if (flow === 'registration') {
           const token = route.query.registration_token
           res = await api.post('/register/payment/initiate', { registration_token: token, gateway })
        } else {
          throw new Error('Invalid flow type')
        }

        const data = res?.data
        const paymentUrl = data?.data?.payment_url
        if (data?.status === 'success' && paymentUrl) {
          window.location.replace(paymentUrl)
          return
        }

        const msg = data?.message?.error?.[0] || data?.message?.[0] || data?.message || 'Payment initiation failed'
        throw new Error(msg)
      } catch (e) {
        status.value = 'failed'
        errorMessage.value = e?.response?.data?.message?.error?.[0] || e?.response?.data?.message || e?.message || 'Payment initiation failed'
      }
    }

    const retry = () => {
       status.value = 'selecting'
       selectedGateway.value = ''
    }

    const goBack = () => {
      const from = String(route.query.back || '/dashboard')
      router.replace(from)
    }

    const fetchGatewayStatus = async () => {
      try {
        const res = await api.get('/gateway-status')
        if (res.data?.status === 'success') {
          gatewayStatuses.value = res.data.data
        }
      } catch (e) {
        console.error('Failed to fetch gateway status:', e)
      }
    }

    onMounted(() => {
      fetchData()
    })

    return {
      status,
      errorMessage,
      selectedGateway,
      retry,
      goBack,
      startPayment,
      flowType,
      itemName,
      itemAmount,
      currencySymbol,
      formatAmount,
      pageTitleDisplay,
      gatewayStatuses
    }
  }
}
</script>

<style scoped>
.tw-animation-slow {
  animation-duration: 3s;
}
</style>
