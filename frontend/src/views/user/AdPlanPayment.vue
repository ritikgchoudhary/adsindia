<template>
  <DashboardLayout page-title="Ad Plan Payment" :dark-theme="true">
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
              <i class="fas fa-ad tw-text-white tw-text-3xl tw--rotate-3"></i>
            </div>
          </div>

          <!-- Plan Info -->
            <h5 class="tw-text-slate-400 tw-text-[13px] tw-font-bold tw-uppercase tw-tracking-[0.2em] tw-mb-4">{{ planName }}</h5>
            <div class="tw-text-6xl tw-font-black tw-text-white tw-tracking-tight tw-flex tw-items-center tw-justify-center tw-gap-1">
              <span class="tw-text-indigo-500 tw-text-3xl tw-font-bold">₹</span>
              {{ formatAmount(paymentAmount) }}
            </div>

          <!-- Divider -->
          <div class="tw-relative tw-h-px tw-w-full tw-bg-slate-800 tw-mb-10">
            <div class="tw-absolute tw-inset-0 tw-bg-gradient-to-r tw-from-transparent tw-via-indigo-500/40 tw-to-transparent"></div>
          </div>

          <!-- Gateway Selection -->
          <div v-if="paymentStatus === 'selecting_gateway'">
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
              @click="initiatePayment"
              class="tw-w-full tw-py-4 tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-rounded-2xl tw-font-bold tw-transition-all tw-border-0 tw-shadow-lg tw-shadow-indigo-600/20 tw-mb-4 disabled:tw-opacity-50"
              :disabled="!selectedGateway"
            >
              Continue Payment
            </button>
            <button 
              type="button"
              @click="goBack"
              class="tw-w-full tw-py-4 tw-bg-slate-800 hover:tw-bg-slate-700 tw-text-white tw-rounded-2xl tw-font-bold tw-transition-all tw-border-0"
            >
              Cancel
            </button>
          </div>

          <!-- Processing State -->
          <div v-else-if="paymentStatus === 'processing'" class="tw-py-12">
            <div class="tw-relative tw-w-20 tw-h-20 tw-mx-auto tw-mb-8">
              <div class="tw-absolute tw-inset-0 tw-border-4 tw-border-indigo-500/20 tw-rounded-full"></div>
              <div class="tw-absolute tw-inset-0 tw-border-4 tw-border-indigo-500 tw-border-t-transparent tw-rounded-full tw-animate-spin"></div>
            </div>
            <div class="tw-text-white tw-font-bold tw-text-xl tw-mb-2">{{ processingPayment ? 'Redirecting...' : 'Verifying Payment...' }}</div>
            <div class="tw-text-slate-400 tw-text-sm">Please do not close this window.</div>
          </div>

          <!-- State: Manual QR -->
          <div v-else-if="paymentStatus === 'manual_qr'" class="tw-py-4 tw-text-center">
             <h4 class="tw-text-white tw-font-black tw-text-lg tw-mb-6">Scan to Pay ₹{{ formatAmount(paymentAmount) }}</h4>
             
             <div class="tw-space-y-6">
                <div v-for="(img, idx) in manualQrImages" :key="idx" class="tw-bg-white tw-p-4 tw-rounded-3xl tw-inline-block">
                  <img :src="img" class="tw-w-64 tw-h-auto tw-rounded-xl" />
                </div>
             </div>

             <div class="tw-mt-10 tw-bg-slate-900/50 tw-border tw-border-slate-800 tw-rounded-3xl tw-p-6 tw-text-left">
                <div class="tw-flex tw-items-center tw-gap-4 tw-mb-4 tw-text-amber-400">
                  <i class="fas fa-info-circle"></i>
                  <span class="tw-text-xs tw-font-black tw-uppercase">Verification Required</span>
                </div>
                <p class="tw-text-slate-400 tw-text-[11px] tw-leading-relaxed tw-mb-0">
                  This is a manual QR system. Once you complete the payment, our admin team will verify it. Turnback to dashboard and check history.
                </p>
             </div>

             <div class="tw-flex tw-gap-4 tw-mt-8">
                <button @click="$router.push('/dashboard')" class="tw-flex-1 tw-py-4 tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-rounded-2xl tw-font-black tw-transition-all tw-border-0">I have paid</button>
                <button @click="paymentStatus = 'selecting_gateway'" class="tw-flex-1 tw-py-4 tw-bg-red-500/10 tw-text-red-500 hover:tw-bg-red-500/20 tw-rounded-2xl tw-font-black tw-transition-all tw-border-0">Cancel</button>
             </div>
          </div>

          <!-- Error State -->
          <div v-else-if="paymentStatus === 'failed'" class="tw-py-8">
            <div class="tw-w-20 tw-h-20 tw-bg-rose-500/10 tw-rounded-[30px] tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-8 tw-text-rose-500">
              <i class="fas fa-exclamation-triangle tw-text-3xl"></i>
            </div>
            <h4 class="tw-font-bold tw-text-white tw-text-2xl tw-mb-3">Payment Error</h4>
            <p class="tw-text-slate-400 tw-mb-10 tw-max-w-[80%] tw-mx-auto">{{ errorMessage }}</p>
            <div class="tw-flex tw-flex-col tw-gap-4">
               <button @click="retryPayment" class="tw-w-full tw-py-4 tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-font-bold tw-rounded-2xl tw-transition-all tw-border-0">Try Again</button>
               <button @click="goBack" class="tw-w-full tw-py-4 tw-bg-slate-800 hover:tw-bg-slate-700 tw-text-white tw-font-bold tw-rounded-2xl tw-transition-all tw-border-0">Go Back</button>
            </div>
          </div>

          <!-- Success State -->
          <div v-else-if="paymentStatus === 'success'" class="tw-py-8">
            <div class="tw-w-20 tw-h-20 tw-bg-emerald-500/10 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-8 tw-text-emerald-500 tw-animate-bounce">
              <i class="fas fa-check-circle tw-text-4xl"></i>
            </div>
            <h4 class="tw-font-bold tw-text-white tw-text-2xl tw-mb-3">Payment Successful</h4>
            <p class="tw-text-slate-400 tw-mb-10">Your ad plan has been activated successfully.</p>
            <button @click="goToAdsWork" class="tw-w-full tw-py-4 tw-bg-emerald-600 hover:tw-bg-emerald-700 tw-text-white tw-font-bold tw-rounded-2xl tw-transition-all tw-border-0">Start Watching Ads</button>
          </div>

        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import DashboardLayout from '../../components/DashboardLayout.vue'
import api from '../../services/api'

export default {
  name: 'AdPlanPayment',
  components: {
    DashboardLayout
  },
  setup() {
    const route = useRoute()
    const router = useRouter()
    const paymentStatus = ref('processing') // selecting_gateway, processing, success, failed, manual_qr
    const paymentAmount = ref(0)
    const planName = ref('')
    const planId = ref(null)
    const transactionId = ref('')
    const currencySymbol = ref('₹')
    const errorMessage = ref('')
    const selectedGateway = ref('')
    const processingPayment = ref(false)
    const manualQrImages = ref([])
    const gatewayStatuses = ref({
      watchpay: 1, simplypay: 1, rupeerush: 1, customqr: 1
    })

    const formatAmount = (amount) => {
      if (!amount && amount !== 0) return '0.00'
      return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
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

    const processPayment = async () => {
      try {
        planId.value = route.query.plan_id || route.params.plan_id
        paymentAmount.value = parseFloat(route.query.amount) || 0
        planName.value = route.query.plan_name || 'Ad Plan'
        transactionId.value = route.query.watchpay_trx || route.query.simplypay_trx || route.query.trx || ''

        if (!planId.value) {
          paymentStatus.value = 'failed'
          errorMessage.value = 'Invalid plan ID'
          return
        }

        if (transactionId.value) {
          paymentStatus.value = 'processing'
          const gateway = route.query.simplypay_trx ? 'simplypay' : 'watchpay'
          const maxTries = 12
          for (let i = 0; i < maxTries; i++) {
            try {
              const paymentResponse = await api.post('/ad-plans/payment/dummy', {
                trx: transactionId.value,
                plan_id: planId.value,
                gateway: gateway
              })
              if (paymentResponse.data.status === 'success') {
                paymentStatus.value = 'success'
                setTimeout(() => router.replace('/dashboard'), 1500)
                return
              }
            } catch (e) {}
            await new Promise(r => setTimeout(r, 2000))
          }
          paymentStatus.value = 'failed'
          errorMessage.value = 'Payment not verified yet.'
          return
        }

        paymentStatus.value = 'selecting_gateway'

      } catch (error) {
        paymentStatus.value = 'failed'
        errorMessage.value = error.response?.data?.message?.error?.[0] || 'Unknown error'
      }
    }

    const initiatePayment = async () => {
      if (!selectedGateway.value) return
      processingPayment.value = true
      paymentStatus.value = 'processing'
      try {
        const response = await api.post('/ad-plans/purchase', {
          plan_id: planId.value,
          payment_method: 'gateway',
          gateway: selectedGateway.value
        })
        const data = response.data
        if (data.status === 'success') {
          if (data.data?.is_manual) {
            manualQrImages.value = data.data.qr_images || []
            paymentStatus.value = 'manual_qr'
            return
          }
          if (data.data?.payment_url) {
            window.location.href = data.data.payment_url
            return
          }
        }
        throw new Error(data.message?.error?.[0] || data.message || 'Initiation failed')
      } catch (error) {
        errorMessage.value = error.response?.data?.message?.error?.[0] || error.message
        paymentStatus.value = 'failed'
      } finally {
        processingPayment.value = false
      }
    }

    const retryPayment = () => {
      paymentStatus.value = 'processing'
      processPayment()
    }

    const goBack = () => router.push('/user/ad-plans')
    const goToAdsWork = () => router.push('/user/ads-work')

    onMounted(() => {
        fetchGatewayStatus()
        processPayment()
    })

    return {
      paymentStatus,
      paymentAmount,
      planName,
      transactionId,
      currencySymbol,
      errorMessage,
      selectedGateway,
      processingPayment,
      manualQrImages,
      gatewayStatuses,
      formatAmount,
      retryPayment,
      goBack,
      goToAdsWork,
      initiatePayment
    }
  }
}
</script>

<style scoped>
.tw-animation-slow { animation-duration: 3s; }
</style>
