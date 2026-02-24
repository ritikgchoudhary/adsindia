<template>
  <DashboardLayout page-title="Quick Payment" :dark-theme="true">
    <div class="tw-min-h-[85vh] tw-p-4 sm:tw-p-6 tw-font-sans tw-max-w-[1200px] tw-mx-auto">
      
      <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-12 tw-gap-8">
        
        <!-- Left Column: Generator -->
        <div class="lg:tw-col-span-5">
           <!-- Balance Card -->
           <div class="tw-bg-gradient-to-br tw-from-indigo-600 tw-to-violet-700 tw-rounded-[32px] tw-p-8 tw-mb-6 tw-shadow-xl tw-shadow-indigo-500/20 tw-relative tw-overflow-hidden">
              <div class="tw-absolute tw-top-0 tw-right-0 tw-p-8 tw-opacity-10">
                <i class="fas fa-wallet tw-text-7xl"></i>
              </div>
              <div class="tw-relative">
                <div class="tw-text-indigo-100 tw-text-xs tw-font-black tw-uppercase tw-tracking-widest tw-mb-2">Quick Payment Balance</div>
                <div class="tw-text-white tw-text-4xl tw-font-black tw-flex tw-items-center tw-gap-2">
                  <span class="tw-text-indigo-200 tw-text-2xl">₹</span>
                  {{ formatAmount(userBalance) }}
                </div>
              </div>
           </div>

           <!-- Premium Quick Payment Card -->
           <div class="tw-bg-[#0f172a] tw-rounded-[40px] tw-border tw-border-slate-800/60 tw-shadow-2xl tw-relative tw-overflow-hidden">
              <div class="tw-absolute tw-top-0 tw-left-1/2 tw--translate-x-1/2 tw-w-full tw-h-32 tw-bg-indigo-600/10 tw-blur-[80px] tw-pointer-events-none"></div>
              
              <div class="tw-relative tw-p-8 sm:tw-p-10">
                <div class="tw-flex tw-items-center tw-gap-4 tw-mb-8">
                  <div class="tw-w-14 tw-h-14 tw-bg-indigo-500/10 tw-rounded-2xl tw-flex tw-items-center tw-justify-center tw-text-indigo-500">
                    <i class="fas fa-qrcode tw-text-2xl"></i>
                  </div>
                  <div>
                    <h2 class="tw-text-white tw-text-xl tw-font-black tw-m-0">Generate QR</h2>
                    <p class="tw-text-slate-500 tw-text-xs tw-m-0">Custom payment link generator</p>
                  </div>
                </div>

                <!-- Input State -->
                <div v-if="paymentStatus === 'input'">
                  <div class="tw-mb-8">
                    <label class="tw-block tw-text-[11px] tw-font-bold tw-text-slate-500 tw-uppercase tw-tracking-[0.25em] tw-mb-3 tw-ml-1">Deposit Amount (₹)</label>
                    <div class="tw-relative">
                      <span class="tw-absolute tw-left-6 tw-top-1/2 tw--translate-y-1/2 tw-text-indigo-500 tw-font-bold tw-text-xl">₹</span>
                      <input 
                        v-model="paymentAmount" 
                        type="number" 
                        placeholder="0.00"
                        class="tw-w-full tw-bg-slate-900/50 tw-border-2 tw-border-slate-800 tw-rounded-2xl tw-py-5 tw-pl-12 tw-pr-6 tw-text-white tw-text-2xl tw-font-bold focus:tw-border-indigo-500 focus:tw-outline-none tw-transition-all"
                      />
                    </div>
                  </div>

                  <div class="tw-text-[11px] tw-font-bold tw-text-slate-500 tw-uppercase tw-tracking-[0.25em] tw-mb-4 tw-ml-1">Select Gateway</div>
                  
                  <div class="tw-grid tw-grid-cols-2 tw-gap-4 tw-mb-10">
                    <button 
                      v-for="gw in availableGateways" :key="gw.alias"
                      @click="selectedGateway = gw.alias"
                      class="tw-group tw-relative tw-p-5 tw-rounded-2xl tw-border-2 tw-transition-all tw-duration-300 tw-text-center"
                      :class="selectedGateway === gw.alias ? 'tw-bg-indigo-500/10 tw-border-indigo-500 tw-shadow-lg tw-shadow-indigo-500/20' : 'tw-bg-slate-900/50 tw-border-slate-800 hover:tw-border-slate-700'"
                    >
                      <i :class="[getGatewayIcon(gw.alias), selectedGateway === gw.alias ? 'tw-text-indigo-400' : 'tw-text-slate-600']" class="tw-text-xl tw-mb-2"></i>
                      <div class="tw-text-xs tw-font-black" :class="selectedGateway === gw.alias ? 'tw-text-white' : 'tw-text-slate-500'">{{ gw.name }}</div>
                      <div v-if="selectedGateway === gw.alias" class="tw-absolute tw-top-2 tw-right-2">
                        <i class="fas fa-check-circle tw-text-indigo-500 tw-text-sm"></i>
                      </div>
                    </button>
                  </div>

                  <button 
                    @click="initiateQuickPayment"
                    :disabled="!paymentAmount || paymentAmount <= 0 || !selectedGateway || loading"
                    class="tw-w-full tw-py-5 tw-bg-gradient-to-r tw-from-indigo-600 tw-to-violet-600 hover:tw-from-indigo-500 hover:tw-to-violet-500 disabled:tw-opacity-50 disabled:tw-cursor-not-allowed tw-text-white tw-font-black tw-text-lg tw-rounded-2xl tw-transition-all tw-border-0 tw-shadow-xl tw-shadow-indigo-600/20"
                  >
                    <span v-if="loading"><i class="fas fa-circle-notch tw-animate-spin tw-mr-2"></i>Generating...</span>
                    <span v-else>Proceed to Pay</span>
                  </button>
                </div>

                <!-- Custom QR View -->
                <div v-else-if="paymentStatus === 'manual_qr'" class="tw-py-4 tw-text-center">
                   <h4 class="tw-text-white tw-font-black tw-text-lg tw-mb-6">Scan to Pay ₹{{ formatAmount(paymentAmount) }}</h4>
                   
                   <div class="tw-space-y-6">
                      <div v-for="(img, idx) in customQrImages" :key="idx" class="tw-bg-white tw-p-4 tw-rounded-3xl tw-inline-block">
                        <img :src="img" class="tw-w-64 tw-h-auto tw-rounded-xl" />
                      </div>
                   </div>

                   <div class="tw-mt-10 tw-bg-slate-900/50 tw-border tw-border-slate-800 tw-rounded-3xl tw-p-6 tw-text-left">
                      <div class="tw-flex tw-items-center tw-gap-4 tw-mb-4 tw-text-amber-400">
                        <i class="fas fa-info-circle"></i>
                        <span class="tw-text-xs tw-font-black tw-uppercase">Verification Required</span>
                      </div>
                      <p class="tw-text-slate-400 tw-text-xs tw-leading-relaxed tw-mb-0">
                        This is a manual QR system. Once you complete the payment, our team will verify it. This TRX is tracked under your history.
                      </p>
                   </div>

                   <div class="tw-flex tw-gap-4 tw-mt-8">
                      <button @click="resetForm" class="tw-flex-1 tw-py-4 tw-bg-slate-800 hover:tw-bg-slate-700 tw-text-white tw-rounded-2xl tw-font-black tw-transition-all tw-border-0">I have paid</button>
                      <button @click="paymentStatus = 'input'" class="tw-flex-1 tw-py-4 tw-bg-red-500/10 tw-text-red-500 hover:tw-bg-red-500/20 tw-rounded-2xl tw-font-black tw-transition-all tw-border-0">Cancel</button>
                   </div>
                </div>

                <!-- Processing State -->
                <div v-else-if="paymentStatus === 'processing'" class="tw-py-12 tw-text-center">
                  <div class="tw-relative tw-w-20 tw-h-20 tw-mx-auto tw-mb-8">
                    <div class="tw-absolute tw-inset-0 tw-border-4 tw-border-indigo-500/20 tw-rounded-full"></div>
                    <div class="tw-absolute tw-inset-0 tw-border-4 tw-border-indigo-500 tw-border-t-transparent tw-rounded-full tw-animate-spin"></div>
                  </div>
                  <h4 class="tw-text-white tw-font-black tw-text-xl tw-mb-2">Verifying Payment...</h4>
                  <p class="tw-text-slate-500 tw-text-sm">Ref: ₹{{ formatAmount(paymentAmount) }}</p>
                </div>

                <!-- Success State -->
                <div v-else-if="paymentStatus === 'success'" class="tw-py-8 tw-text-center">
                  <div class="tw-w-24 tw-h-24 tw-bg-indigo-500/10 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-8 tw-text-indigo-400">
                    <i class="fas fa-check-circle tw-text-5xl tw-animate-bounce"></i>
                  </div>
                  <h4 class="tw-font-black tw-text-white tw-text-2xl tw-mb-2">Recharge Success!</h4>
                  <p class="tw-text-slate-400 tw-mb-8">
                    ₹{{ formatAmount(paymentAmount) }} added to your wallet successfully.
                  </p>
                  
                  <div class="tw-bg-white/5 tw-border tw-border-white/5 tw-rounded-2xl tw-p-5 tw-mb-8 tw-text-left">
                     <div class="tw-flex tw-justify-between tw-mb-2">
                        <span class="tw-text-slate-500 tw-text-xs">Transaction ID</span>
                        <span class="tw-text-white tw-font-mono tw-text-xs">{{ lastTrx }}</span>
                     </div>
                     <div class="tw-flex tw-justify-between">
                        <span class="tw-text-slate-500 tw-text-xs">Quick Payment Balance</span>
                        <span class="tw-text-indigo-400 tw-font-black tw-text-sm">₹{{ formatAmount(userBalance) }}</span>
                     </div>
                  </div>

                  <button @click="resetForm" class="tw-w-full tw-py-4 tw-bg-slate-800 hover:tw-bg-slate-700 tw-text-white tw-font-black tw-rounded-2xl tw-transition-all tw-border-0">Make Another Deposit</button>
                </div>

                <!-- Error State -->
                <div v-else-if="paymentStatus === 'failed'" class="tw-py-8 tw-text-center">
                  <div class="tw-w-20 tw-h-20 tw-bg-rose-500/10 tw-rounded-3xl tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-8 tw-text-rose-500">
                    <i class="fas fa-exclamation-triangle tw-text-3xl"></i>
                  </div>
                  <h4 class="tw-font-black tw-text-white tw-text-2xl tw-mb-3">Verification Failed</h4>
                  <p class="tw-text-slate-400 tw-mb-10">{{ errorMessage }}</p>
                  <button @click="paymentStatus = 'input'" class="tw-w-full tw-py-4 tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-font-black tw-rounded-2xl tw-transition-all tw-border-0">Try Again</button>
                </div>
              </div>
           </div>
        </div>

        <!-- Right Column: History -->
        <div class="lg:tw-col-span-7">
           <div class="tw-bg-[#0f172a] tw-rounded-[40px] tw-border tw-border-slate-800/60 tw-shadow-2xl tw-overflow-hidden">
              <div class="tw-p-8 tw-border-b tw-border-slate-800 tw-flex tw-justify-between tw-items-center">
                 <div>
                    <h3 class="tw-text-white tw-text-lg tw-font-black tw-m-0">Recent Quick Payments</h3>
                    <p class="tw-text-slate-500 tw-text-xs tw-m-0">History of your custom QR generations</p>
                 </div>
                 <button @click="fetchHistory" class="tw-p-3 tw-bg-white/5 tw-rounded-xl tw-text-slate-400 hover:tw-text-white tw-transition-all">
                    <i class="fas fa-sync-alt" :class="{'tw-animate-spin': historyLoading}"></i>
                 </button>
              </div>

              <div class="tw-p-4">
                 <div v-if="historyLoading && !paymentHistory.length" class="tw-py-20 tw-text-center">
                    <div class="tw-w-10 tw-h-10 tw-border-4 tw-border-indigo-500/20 tw-border-t-indigo-500 tw-rounded-full tw-animate-spin tw-mx-auto"></div>
                 </div>
                 
                 <div v-else-if="!paymentHistory.length" class="tw-py-24 tw-text-center">
                    <div class="tw-w-16 tw-h-16 tw-bg-white/5 tw-rounded-2xl tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-4">
                       <i class="fas fa-history tw-text-2xl tw-text-slate-700"></i>
                    </div>
                    <p class="tw-text-slate-500 tw-text-sm">No quick payment history found.</p>
                 </div>

                 <div v-else class="tw-space-y-3">
                    <div v-for="item in paymentHistory" :key="item.trx" class="tw-bg-white/[0.03] tw-border tw-border-white/5 tw-rounded-2xl tw-p-5 tw-flex tw-items-center tw-justify-between hover:tw-bg-white/[0.05] tw-transition-all">
                       <div class="tw-flex tw-items-center tw-gap-4">
                          <div :class="`tw-w-12 tw-h-12 tw-rounded-xl tw-flex tw-items-center tw-justify-center ${getStatusClass(item.status)}`">
                             <i :class="getStatusIcon(item.status)"></i>
                          </div>
                          <div>
                             <div class="tw-text-white tw-font-bold tw-text-sm">₹{{ formatAmount(item.amount) }}</div>
                             <div class="tw-text-[10px] tw-text-slate-600 tw-font-mono tw-mt-0.5">TRX: {{ item.trx }}</div>
                          </div>
                       </div>
                       <div class="tw-text-right">
                          <div :class="`tw-text-[10px] tw-font-black tw-uppercase tw-tracking-widest ${getStatusTextClass(item.status)}`">
                             {{ getStatusText(item.status) }}
                          </div>
                          <div class="tw-text-[10px] tw-text-slate-500 tw-mt-1">{{ formatDate(item.created_at) }}</div>
                       </div>
                    </div>
                 </div>
              </div>

              <div class="tw-p-6 tw-bg-white/[0.02] tw-text-center">
                 <p class="tw-text-[10px] tw-text-slate-600 tw-m-0 tw-uppercase tw-font-bold tw-tracking-widest">Showing last 10 transactions</p>
              </div>
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
  name: 'QuickPayment',
  components: {
    DashboardLayout
  },
  setup() {
    const route = useRoute()
    const router = useRouter()
    
    const paymentStatus = ref('input') // input, processing, success, failed, manual_qr
    const paymentAmount = ref('')
    const selectedGateway = ref('')
    const loading = ref(false)
    const errorMessage = ref('')
    
    const userBalance = ref(0)
    const paymentHistory = ref([])
    const availableGateways = ref([])
    const customQrImages = ref([])
    const historyLoading = ref(false)
    const lastTrx = ref('')

    const formatAmount = (amount) => {
      if (!amount && amount !== 0) return '0.00'
      return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    }

    const formatDate = (dateString) => {
      const date = new Date(dateString)
      return date.toLocaleString('en-IN', {
        month: 'short',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
      })
    }

    const getGatewayIcon = (alias) => {
       const a = (alias || '').toLowerCase()
       if (a.includes('watch')) return 'fas fa-bolt'
       if (a.includes('simply')) return 'fas fa-credit-card'
       if (a.includes('rupee')) return 'fas fa-indian-rupee-sign'
       if (a.includes('custom')) return 'fas fa-qrcode'
       return 'fas fa-wallet'
    }

    const fetchHistory = async () => {
      historyLoading.value = true
      try {
        const res = await api.get('/special-agent/payment/history')
        if (res.data.status === 'success') {
          userBalance.value = res.data.data.balance || 0
          paymentHistory.value = res.data.data.history || []
          availableGateways.value = res.data.data.gateways || []

          // Auto-select first gateway if none selected
          if (!selectedGateway.value && availableGateways.value.length > 0) {
            selectedGateway.value = availableGateways.value[0].alias
          }

          // Store custom QR images if available
          const manual = availableGateways.value.find(g => g.alias === 'custom_qr')
          if (manual && manual.qr_images) {
            customQrImages.value = manual.qr_images
          }
        }
      } catch (err) {
        console.error('History fetch error:', err)
      } finally {
        historyLoading.value = false
      }
    }

    const initiateQuickPayment = async () => {
      if (!paymentAmount.value || paymentAmount.value <= 0) return
      
      loading.value = true
      try {
        const res = await api.post('/special-agent/payment/initiate', {
          amount: paymentAmount.value,
          gateway: selectedGateway.value
        })
        
        if (res.data.status === 'success') {
          if (res.data.data.is_manual) {
             paymentStatus.value = 'manual_qr'
             // URL will have the TRX if we need to track
          } else if (res.data.data.payment_url) {
             window.location.href = res.data.data.payment_url
          }
        } else {
          throw new Error(res.data.message?.error?.[0] || 'Initiation failed')
        }
      } catch (err) {
        errorMessage.value = err.response?.data?.message?.error?.[0] || err.message
        paymentStatus.value = 'failed'
      } finally {
        loading.value = false
      }
    }

    const checkPaymentStatus = async () => {
      const trx = route.query.watchpay_trx || route.query.simplypay_trx || route.query.rupeerush_trx
      const gateway = route.query.simplypay_trx ? 'simplypay' : (route.query.rupeerush_trx ? 'rupeerush' : 'watchpay')
      
      // If returning from a manual QR initiation via URL redirection
      if (route.query.method === 'custom_qr') {
        paymentStatus.value = 'manual_qr'
        paymentAmount.value = route.query.amount || ''
        fetchHistory()
        return
      }

      if (trx) {
        lastTrx.value = trx
        paymentStatus.value = 'processing'
        paymentAmount.value = route.query.amount || ''
        
        let attempts = 0
        const maxAttempts = 20
        
        while (attempts < maxAttempts) {
          try {
            const res = await api.post('/special-agent/payment/confirm', {
              trx: trx,
              gateway: gateway
            })
            
            if (res.data.status === 'success') {
              paymentStatus.value = 'success'
              await fetchHistory()
              return
            }
          } catch (e) { }
          
          attempts++
          await new Promise(r => setTimeout(r, 4000))
        }
        
        errorMessage.value = "Payment confirmation timed out. If you paid, it will reflect in your balance shortly."
        paymentStatus.value = 'failed'
        fetchHistory()
      } else {
        fetchHistory()
      }
    }

    const resetForm = () => {
      paymentStatus.value = 'input'
      paymentAmount.value = ''
      router.replace('/user/quick-payment')
      fetchHistory() // Refresh to show new pending if manual
    }

    const getStatusClass = (status) => {
       if (status == 1) return 'tw-bg-emerald-500/10 tw-text-emerald-500'
       if (status == 2) return 'tw-bg-rose-500/10 tw-text-rose-500'
       return 'tw-bg-amber-500/10 tw-text-amber-500'
    }

    const getStatusIcon = (status) => {
       if (status == 1) return 'fas fa-check'
       if (status == 2) return 'fas fa-times'
       return 'fas fa-clock'
    }

    const getStatusText = (status) => {
       if (status == 1) return 'Success'
       if (status == 2) return 'Failed'
       return 'Pending'
    }

    const getStatusTextClass = (status) => {
       if (status == 1) return 'tw-text-emerald-400'
       if (status == 2) return 'tw-text-rose-400'
       return 'tw-text-amber-400'
    }

    onMounted(checkPaymentStatus)

    return {
      paymentStatus,
      paymentAmount,
      selectedGateway,
      loading,
      errorMessage,
      userBalance,
      paymentHistory,
      availableGateways,
      customQrImages,
      historyLoading,
      lastTrx,
      formatAmount,
      formatDate,
      getGatewayIcon,
      fetchHistory,
      initiateQuickPayment,
      resetForm,
      getStatusClass,
      getStatusIcon,
      getStatusText,
      getStatusTextClass
    }
  }
}
</script>

<style scoped>
.tw-animation-slow { animation-duration: 3s; }

@media (max-width: 768px) {
  /* Layout gaps */
  .tw-grid.tw-gap-8 { gap: 1rem !important; }
  .tw-min-h-\[85vh\] { min-height: auto !important; padding: 1rem 0.5rem !important; }

  /* Balance Card */
  .tw-bg-gradient-to-br { padding: 1.25rem !important; border-radius: 1.5rem !important; margin-bottom: 1rem !important; }
  .tw-text-4xl { font-size: 1.75rem !important; }
  .tw-text-7xl { font-size: 3rem !important; }
  .tw-p-8.tw-opacity-10 { padding: 1.5rem !important; }
  .tw-text-xs.tw-font-black { font-size: 10px !important; }
  
  /* Generator Card Header */
  .tw-bg-\[\#0f172a\].tw-rounded-\[40px\] { border-radius: 1.5rem !important; }
  .tw-p-8.sm\:tw-p-10 { padding: 1.25rem !important; }
  .tw-w-14.tw-h-14 { width: 3rem !important; height: 3rem !important; border-radius: 0.85rem !important; }
  .tw-w-14.tw-h-14 i { font-size: 1.25rem !important; }
  h2.tw-text-xl { font-size: 1.1rem !important; }
  .tw-mb-8 { margin-bottom: 1.25rem !important; }
  
  /* Inputs */
  .tw-text-2xl { font-size: 1.25rem !important; }
  input.tw-py-5 { padding: 0.75rem 0.75rem 0.75rem 2.5rem !important; border-radius: 0.85rem !important; }
  .tw-absolute.tw-left-6 { left: 1rem !important; font-size: 1.1rem !important; }
  label.tw-text-\[11px\] { font-size: 10px !important; margin-bottom: 0.5rem !important; }

  /* Buttons */
  .tw-py-5 { padding: 0.85rem !important; font-size: 1rem !important; border-radius: 1rem !important; }
  .tw-grid-cols-2 { gap: 0.75rem !important; }
  .tw-p-5.tw-rounded-2xl { padding: 0.75rem !important; border-radius: 1rem !important; }
  .tw-p-5.tw-rounded-2xl i { font-size: 1.25rem !important; margin-bottom: 0.5rem !important; }

  /* History Card */
  .tw-bg-\[\#0f172a\].tw-rounded-\[40px\].tw-overflow-hidden { border-radius: 1.5rem !important; }
  .tw-p-8.tw-border-b { padding: 1.25rem !important; }
  h3.tw-text-lg { font-size: 1.1rem !important; }
  
  /* History Items */
  .tw-p-5.tw-flex.tw-items-center { padding: 0.75rem 1rem !important; border-radius: 1rem !important; }
  .tw-w-12.tw-h-12 { width: 2.5rem !important; height: 2.5rem !important; border-radius: 0.75rem !important; }
  .tw-w-12.tw-h-12 i { font-size: 0.9rem !important; }
  .tw-text-white.tw-font-bold.tw-text-sm { font-size: 0.9rem !important; }
  .tw-text-\[10px\] { font-size: 9px !important; }
  .tw-gap-4 { gap: 0.85rem !important; }
}

@media (max-width: 480px) {
  .tw-text-4xl { font-size: 1.5rem !important; }
  .tw-text-2xl { font-size: 1.15rem !important; }
  .tw-p-5.tw-rounded-2xl { padding: 0.6rem !important; }
  .tw-p-8.sm\:tw-p-10 { padding: 1rem !important; }
}
</style>
