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
              <i v-else-if="flowType === 'withdraw_instant_upgrade'" class="fas fa-bolt tw-text-white tw-text-3xl tw--rotate-3"></i>
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

            <div class="tw-flex tw-flex-col tw-gap-4 tw-mb-10">
              <!-- SimplyPay Option (Recommended) -->
              <button 
                v-if="gatewayStatuses.simplypay"
                @click="selectedGateway = 'simplypay'"
                class="tw-group tw-w-full tw-text-left tw-bg-slate-900/40 tw-border tw-border-slate-800 tw-rounded-[28px] tw-p-5 tw-transition-all tw-duration-300 hover:tw-bg-emerald-500/5 hover:tw-border-emerald-500/50 hover:tw-shadow-[0_0_30px_-10px_rgba(16,185,129,0.2)] tw-flex tw-items-center tw-justify-between tw-relative tw-overflow-hidden"
                :class="selectedGateway === 'simplypay' ? 'tw-border-emerald-500 tw-bg-emerald-500/10 tw-ring-1 tw-ring-emerald-500/50' : ''"
              >
                <!-- Badge for Recommended -->
                <div class="tw-absolute tw-top-0 tw-right-0 tw-bg-emerald-500 tw-text-[9px] tw-text-white tw-font-black tw-px-3 tw-py-1 tw-rounded-bl-xl tw-uppercase tw-tracking-widest">Recommended</div>
                
                <div class="tw-flex tw-items-center">
                  <div class="tw-w-14 tw-h-14 tw-bg-gradient-to-br tw-from-emerald-400 tw-to-emerald-600 tw-rounded-2xl tw-flex tw-items-center tw-justify-center tw-mr-5 tw-shadow-lg tw-shadow-emerald-600/20 tw-transition-transform group-hover:tw-scale-110">
                    <i class="fas fa-bolt tw-text-white tw-text-xl"></i>
                  </div>
                  <div>
                    <div class="tw-font-bold tw-text-white tw-text-lg tw-flex tw-items-center tw-gap-2">
                      SimplyPay
                    </div>
                    <div class="tw-text-xs tw-text-emerald-400/80 tw-font-medium">Instant & Most Reliable</div>
                  </div>
                </div>
                <div class="tw-w-10 tw-h-10 tw-bg-slate-800/50 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-transition-colors group-hover:tw-bg-emerald-500/20">
                    <i class="fas fa-chevron-right tw-text-slate-500 tw-text-sm tw-transition-transform group-hover:tw-translate-x-0.5 group-hover:tw-text-emerald-400"></i>
                </div>
              </button>

              <!-- WatchPay Option -->
              <button 
                v-if="gatewayStatuses.watchpay"
                @click="selectedGateway = 'watchpay'"
                class="tw-group tw-w-full tw-text-left tw-bg-slate-900/40 tw-border tw-border-slate-800 tw-rounded-[28px] tw-p-5 tw-transition-all tw-duration-300 hover:tw-bg-indigo-500/5 hover:tw-border-indigo-500/50 hover:tw-shadow-[0_0_30px_-10px_rgba(99,102,241,0.2)] tw-flex tw-items-center tw-justify-between"
                :class="selectedGateway === 'watchpay' ? 'tw-border-indigo-500 tw-bg-indigo-500/10 tw-ring-1 tw-ring-indigo-500/50' : ''"
              >
                <div class="tw-flex tw-items-center">
                  <div class="tw-w-14 tw-h-14 tw-bg-gradient-to-br tw-from-indigo-400 tw-to-indigo-600 tw-rounded-2xl tw-flex tw-items-center tw-justify-center tw-mr-5 tw-shadow-lg tw-shadow-indigo-600/20 tw-transition-transform group-hover:tw-scale-110">
                    <i class="fas fa-eye tw-text-white tw-text-xl"></i>
                  </div>
                  <div>
                    <div class="tw-font-bold tw-text-white tw-text-lg">WatchPay</div>
                    <div class="tw-text-xs tw-text-slate-400/80 tw-font-medium">Secure UPI Payments</div>
                  </div>
                </div>
                <div class="tw-w-10 tw-h-10 tw-bg-slate-800/50 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-transition-colors group-hover:tw-bg-indigo-500/20">
                    <i class="fas fa-chevron-right tw-text-slate-500 tw-text-sm tw-transition-transform group-hover:tw-translate-x-0.5 group-hover:tw-text-indigo-400"></i>
                </div>
              </button>

              <!-- RupeeRush Option -->
              <button 
                v-if="gatewayStatuses.rupeerush"
                @click="selectedGateway = 'rupeerush'"
                class="tw-group tw-w-full tw-text-left tw-bg-slate-900/40 tw-border tw-border-slate-800 tw-rounded-[28px] tw-p-5 tw-transition-all tw-duration-300 hover:tw-bg-orange-500/5 hover:tw-border-orange-500/50 hover:tw-shadow-[0_0_30px_-10px_rgba(249,115,22,0.2)] tw-flex tw-items-center tw-justify-between"
                :class="selectedGateway === 'rupeerush' ? 'tw-border-orange-500 tw-bg-orange-500/10 tw-ring-1 tw-ring-orange-500/50' : ''"
              >
                <div class="tw-flex tw-items-center">
                  <div class="tw-w-14 tw-h-14 tw-bg-gradient-to-br tw-from-orange-400 tw-to-orange-600 tw-rounded-2xl tw-flex tw-items-center tw-justify-center tw-mr-5 tw-shadow-lg tw-shadow-orange-600/20 tw-transition-transform group-hover:tw-scale-110">
                    <i class="fas fa-indian-rupee-sign tw-text-white tw-text-xl"></i>
                  </div>
                  <div>
                    <div class="tw-font-bold tw-text-white tw-text-lg">RupeeRush</div>
                    <div class="tw-text-xs tw-text-slate-400/80 tw-font-medium">UPI Direct Gateway</div>
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
                class="tw-group tw-w-full tw-text-left tw-bg-slate-900/40 tw-border tw-border-slate-800 tw-rounded-[28px] tw-p-5 tw-transition-all tw-duration-300 hover:tw-bg-slate-500/5 hover:tw-border-slate-500/50 hover:tw-shadow-[0_0_30px_-10px_rgba(148,163,184,0.2)] tw-flex tw-items-center tw-justify-between"
                :class="selectedGateway === 'custom_qr' ? 'tw-border-slate-400 tw-bg-slate-500/10 tw-ring-1 tw-ring-slate-400/50' : ''"
              >
                <div class="tw-flex tw-items-center">
                  <div class="tw-w-14 tw-h-14 tw-bg-gradient-to-br tw-from-slate-500 tw-to-slate-700 tw-rounded-2xl tw-flex tw-items-center tw-justify-center tw-mr-5 tw-shadow-lg tw-shadow-slate-600/20 tw-transition-transform group-hover:tw-scale-110">
                    <i class="fas fa-qrcode tw-text-white tw-text-xl"></i>
                  </div>
                  <div>
                    <div class="tw-font-bold tw-text-white tw-text-lg">Manual QR</div>
                    <div class="tw-text-xs tw-text-slate-400/80 tw-font-medium">Scan & Upload Screenshot</div>
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

          <!-- State: Manual QR -->
          <div v-else-if="status === 'manual_qr'" class="tw-py-4 tw-text-center">
             <h4 class="tw-text-white tw-font-black tw-text-lg tw-mb-6">Scan QR & Submit Proof</h4>
             
             <div class="tw-space-y-6">
                <div v-for="(img, idx) in manualQrImages" :key="idx" class="tw-bg-white tw-p-4 tw-rounded-3xl tw-inline-block">
                  <img :src="img" class="tw-w-64 tw-h-auto tw-rounded-xl" />
                </div>
             </div>

             <div class="tw-mt-8 tw-text-left tw-space-y-4">
                <div class="tw-bg-slate-900/50 tw-p-5 tw-rounded-2xl tw-border tw-border-slate-800">
                   <label class="tw-block tw-text-slate-400 tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-mb-2">Enter Amount Paid (₹)</label>
                   <input type="number" step="0.01" v-model="manualData.amount" class="tw-w-full tw-bg-slate-800 tw-text-white tw-border-0 tw-rounded-xl tw-p-4 tw-font-bold focus:tw-ring-2 focus:tw-ring-indigo-500 tw-transition-all" placeholder="e.g. 1499" />
                </div>
                
                <div class="tw-bg-slate-900/50 tw-p-5 tw-rounded-2xl tw-border tw-border-slate-800">
                   <label class="tw-block tw-text-slate-400 tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-mb-2">UTR / Reference No.</label>
                   <input type="text" v-model="manualData.utr" class="tw-w-full tw-bg-slate-800 tw-text-white tw-border-0 tw-rounded-xl tw-p-4 tw-font-bold focus:tw-ring-2 focus:tw-ring-indigo-500 tw-transition-all" placeholder="Enter 12-digit UTR" />
                </div>

                <div class="tw-bg-slate-900/50 tw-p-5 tw-rounded-2xl tw-border tw-border-slate-800">
                   <label class="tw-block tw-text-slate-400 tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-mb-2">Payment Screenshot</label>
                   <label class="tw-flex tw-items-center tw-justify-center tw-w-full tw-py-4 tw-px-4 tw-border-2 tw-border-dashed tw-border-slate-700 hover:tw-border-indigo-500 tw-rounded-xl tw-cursor-pointer tw-transition-all tw-bg-slate-800" :class="{'tw-border-indigo-500 tw-bg-indigo-500/10': manualData.file}">
                     <span class="tw-text-slate-300 tw-text-sm tw-font-medium">{{ manualFileName || 'Upload Screenshot (JPG/PNG)' }}</span>
                     <input type="file" class="tw-hidden" accept="image/*" @change="handleManualFile" />
                   </label>
                </div>
             </div>

             <div class="tw-mt-8 tw-bg-slate-900/50 tw-border tw-border-slate-800 tw-rounded-3xl tw-p-6 tw-text-left">
                <div class="tw-flex tw-items-center tw-gap-4 tw-mb-4 tw-text-amber-400">
                  <i class="fas fa-info-circle"></i>
                  <span class="tw-text-xs tw-font-black tw-uppercase">Verification Required</span>
                </div>
                <p class="tw-text-slate-400 tw-text-[11px] tw-leading-relaxed tw-mb-0">
                  This is a manual QR system. Once you complete the payment and submit the proof above, our team will manually verify it.
                </p>
             </div>

             <div class="tw-flex tw-gap-4 tw-mt-8">
                <button @click="submitManualPaymentForm" :disabled="isSubmittingManual" class="tw-flex-1 tw-py-4 tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-rounded-2xl tw-font-black tw-transition-all tw-border-0 disabled:tw-opacity-50">
                  {{ isSubmittingManual ? 'Submitting...' : 'Submit Proof' }}
                </button>
                <button @click="status = 'selecting'" class="tw-flex-1 tw-py-4 tw-bg-red-500/10 tw-text-red-500 hover:tw-bg-red-500/20 tw-rounded-2xl tw-font-black tw-transition-all tw-border-0">Cancel</button>
             </div>
          </div>
          
          <!-- State: Manual Success -->
          <div v-else-if="status === 'manual_success'" class="tw-py-8">
             <div class="tw-w-24 tw-h-24 tw-bg-emerald-500/10 tw-rounded-[40px] tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-8 tw-border-4 tw-border-emerald-500/20">
                <i class="fas fa-check-circle tw-text-4xl tw-text-emerald-500"></i>
             </div>
             <h4 class="tw-text-white tw-font-black tw-text-2xl tw-mb-3">Proof Submitted!</h4>
             <p class="tw-text-slate-400 tw-font-medium tw-leading-relaxed tw-mb-8 tw-px-6">We have received your payment details. It will be verified by our team shortly. Thank you!</p>
             <button @click="goBack" class="tw-w-full tw-py-5 tw-bg-slate-800 hover:tw-bg-indigo-600 tw-text-white tw-font-bold tw-rounded-2xl tw-transition-all tw-border-0 tw-shadow-xl tw-shadow-transparent hover:tw-shadow-indigo-500/20">
                Go to Dashboard
             </button>
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
    const status = ref('selecting') // selecting | processing | failed | manual_qr | manual_success
    const errorMessage = ref('Unknown error')
    const selectedGateway = ref('')
    const manualQrImages = ref([])
    const currentTrx = ref('')
    const manualData = ref({
       amount: '',
       utr: '',
       file: null
    })
    const manualFileName = ref('')
    const isSubmittingManual = ref(false)
    
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
       if (flowType.value === 'withdraw_instant_upgrade') return 'Instant Payout Upgrade'
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
          } else if (flow === 'kyc_fast_track_fee') {
             itemName.value = 'KYC Fast Track Fee'
             if (!itemAmount.value || itemAmount.value == 0) itemAmount.value = 299
          } else if (flow === 'withdraw_gst') {
             itemName.value = '18% GST Fee'
          } else if (flow === 'ad_certificate') {
             itemName.value = 'Ad Certificate'
             if (!itemAmount.value || itemAmount.value == 0) itemAmount.value = 1250
          } else if (flow === 'withdraw_instant_upgrade') {
             itemName.value = 'Instant Payout Upgrade Fee'
             // Amount should be passed in query, if not we can't easily fetch it here without withdraw_id
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
          res = await api.post('/partner-program/join', { planId, gateway })
        } else if (flow === 'kyc_fee') {
          res = await api.post('/kyc-payment', { gateway })
        } else if (flow === 'kyc_fast_track_fee') {
          res = await api.post('/kyc-fast-track-payment', { gateway })
        } else if (flow === 'ad_certificate' || flow === 'ad_certificate_course') {
          res = await api.post('/ad-certificate/purchase', { gateway, type: 'course' })
        } else if (flow === 'ad_certificate_view') {
          res = await api.post('/ad-certificate/purchase', { gateway, type: 'view' })
        } else if (flow === 'withdraw_gst') {
          const methodCode = parseInt(route.query.method_code)
          const payoutType = String(route.query.payout_type || 'bank')
          const isPriority = parseInt(route.query.is_priority || 0)
          res = await api.post('/withdraw-request/gst/initiate', { method_code: methodCode, payout_type: payoutType, gateway, is_priority: isPriority })
         } else if (flow === 'registration') {
            const token = route.query.registration_token
            res = await api.post('/register/payment/initiate', { registration_token: token, gateway })
         } else if (flow === 'withdraw_instant_upgrade') {
            const withdrawId = route.query.withdraw_id
            res = await api.post('/withdraw-instant/payment/initiate', { withdraw_id: withdrawId, gateway })
         } else {
          throw new Error('Invalid flow type')
        }

        const data = res?.data
        const paymentUrl = data?.data?.payment_url
        if (data?.status === 'success') {
          if (data.data?.is_manual) {
            manualQrImages.value = data.data.qr_images || []
            currentTrx.value = data.data.trx || ''
            manualData.value.amount = itemAmount.value || data.data.amount || ''
            status.value = 'manual_qr'
            return
          }
          if (paymentUrl) {
            window.location.replace(paymentUrl)
            return
          }
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
       manualData.value = { amount: '', utr: '', file: null }
       manualFileName.value = ''
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

    const handleManualFile = (e) => {
      const file = e.target.files[0]
      if (!file) return
      manualData.value.file = file
      manualFileName.value = file.name
    }

    const submitManualPaymentForm = async () => {
       if (!manualData.value.amount || !manualData.value.utr || !manualData.value.file) {
          if (window.notify) window.notify('error', 'Please fill all fields and upload the screenshot.')
          return
       }
       if (!currentTrx.value) {
          if (window.notify) window.notify('error', 'Invalid transaction. Please try again.')
          return
       }
       isSubmittingManual.value = true
       try {
          const formData = new FormData()
          formData.append('trx', currentTrx.value)
          formData.append('amount', manualData.value.amount)
          formData.append('utr', manualData.value.utr)
          formData.append('screenshot', manualData.value.file)
          
          const res = await api.post('/manual-payment/submit', formData, {
             headers: { 'Content-Type': 'multipart/form-data' }
          })
          if (res.data?.status === 'success') {
             status.value = 'manual_success'
          } else {
             const m = res.data?.message;
             const msg = (m?.error?.[0] || m?.success?.[0] || (Array.isArray(m) ? m[0] : m) || 'Failed to submit proof');
             if (window.notify) window.notify('error', msg)
          }
       } catch (err) {
          console.error(err)
          const m = err.response?.data?.message;
          const msg = (m?.error?.[0] || m?.success?.[0] || (Array.isArray(m) ? m[0] : m) || 'Submission failed');
          if (window.notify) window.notify('error', msg)
       } finally {
          isSubmittingManual.value = false
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
      gatewayStatuses,
      manualQrImages,
      manualData,
      manualFileName,
      isSubmittingManual,
      handleManualFile,
      submitManualPaymentForm
    }
  }
}
</script>

<style scoped>
.tw-animation-slow {
  animation-duration: 3s;
}

@media (max-width: 768px) {
  .tw-p-8.sm\:tw-p-12 { padding: 1.25rem 1rem !important; }
  .tw-rounded-\[40px\] { border-radius: 2rem !important; }
  .tw-w-20.tw-h-20 { width: 3.5rem !important; height: 3.5rem !important; margin-bottom: 1.25rem !important; }
  .tw-text-3xl { font-size: 1.25rem !important; }
  .tw-text-6xl { font-size: 2.5rem !important; }
  .tw-text-3xl.tw-font-bold { font-size: 1.5rem !important; }
  h5.tw-text-slate-400 { font-size: 10px !important; margin-bottom: 0.25rem !important; letter-spacing: 0.15em !important; }
  
  .tw-mb-10 { margin-bottom: 1.25rem !important; }
  .tw-mb-8 { margin-bottom: 0.75rem !important; }
  
  /* Gateway Buttons */
  .tw-p-5 { padding: 0.85rem !important; border-radius: 1.25rem !important; }
  .tw-w-14.tw-h-14 { width: 2.75rem !important; height: 2.75rem !important; border-radius: 0.85rem !important; margin-right: 0.75rem !important; }
  .tw-w-14.tw-h-14 i { font-size: 1rem !important; }
  .tw-text-lg { font-size: 0.95rem !important; }
  .tw-text-xs { font-size: 10px !important; }
  .tw-w-10.tw-h-10 { width: 2rem !important; height: 2rem !important; }
  .tw-flex-col.tw-gap-5 { gap: 0.75rem !important; }
  
  /* Manual QR Section */
  .tw-bg-white.tw-p-4 { padding: 0.65rem !important; border-radius: 1rem !important; }
  .tw-w-64 { width: 10rem !important; }
  .tw-bg-slate-900\/50.tw-p-5 { padding: 0.85rem !important; border-radius: 1.25rem !important; }
  input.tw-p-4 { padding: 0.65rem !important; font-size: 0.85rem !important; }
  .tw-py-4.tw-px-4 { padding: 0.65rem !important; }
  
  .tw-py-5 { padding: 0.85rem !important; font-size: 0.9rem !important; border-radius: 1.25rem !important; }
  .tw-text-2xl { font-size: 1.15rem !important; }
  
  /* Modal-like card padding on very small devices */
  .tw-min-h-\[85vh\] { min-height: auto !important; padding: 1rem 0.75rem !important; }
}

@media (max-width: 480px) {
  .tw-text-6xl { font-size: 2.2rem !important; }
  .tw-text-3xl.tw-font-bold { font-size: 1.25rem !important; }
  .tw-flex-col.tw-gap-5 { gap: 0.6rem !important; }
  .tw-w-14.tw-h-14 { width: 2.5rem !important; height: 2.5rem !important; }
  .tw-rounded-\[40px\] { border-radius: 1.5rem !important; }
  .tw-p-8.sm\:tw-p-12 { padding: 1.5rem 1rem !important; }
  .tw-bg-[#0f172a] { max-width: 100% !important; margin: 0 !important; }
}
</style>
