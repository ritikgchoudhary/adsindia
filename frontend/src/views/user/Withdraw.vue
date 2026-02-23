<template>
  <DashboardLayout page-title="Withdraw Money" :dark-theme="true">
    <div class="tw-flex tw-justify-center tw-px-3 sm:tw-px-0">
      <div class="tw-w-full lg:tw-w-3/4 xl:tw-w-2/3">
        <form @submit.prevent="handleSubmit">
          <div class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-slate-200 tw-overflow-hidden">
            <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-2 lg:tw-max-h-[800px]">
              
              <!-- Left Side: Payment Methods -->
              <div class="tw-p-4 sm:tw-p-6 tw-border-b lg:tw-border-b-0 lg:tw-border-r tw-border-slate-200 lg:tw-overflow-y-auto custom-scrollbar lg:tw-max-h-[600px] lg:tw-h-auto">
                <h5 class="tw-text-slate-800 tw-font-bold tw-text-lg tw-mb-4">Select Withdrawal Method</h5>
                                <div v-if="isLoadingMethods" class="tw-flex tw-flex-col tw-items-center tw-justify-center tw-py-12">
                   <i class="fas fa-spinner fa-spin tw-text-indigo-600 tw-text-3xl tw-mb-3"></i>
                   <p class="tw-text-slate-500 tw-text-sm">Loading methods...</p>
                </div>

                <div v-else-if="withdrawMethods.length === 0" class="tw-text-center tw-py-12 tw-bg-amber-50 tw-rounded-xl tw-border tw-border-amber-100">
                  <i class="fas fa-exclamation-circle tw-text-amber-500 tw-text-4xl tw-mb-3"></i>
                  <p class="tw-text-slate-700 tw-font-semibold">No withdrawal methods available.</p>
                  <p class="tw-text-slate-500 tw-text-sm">Please check back later or contact support.</p>
                </div>

                <div v-else class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-4">
                  <label 
                    v-for="(method, index) in withdrawMethods" 
                    :key="`${method?.id || 'm'}-${method?.payout_type || index}`"
                    class="tw-relative tw-cursor-pointer tw-bg-slate-50 tw-border-2 tw-rounded-xl tw-p-4 tw-transition-all hover:tw-bg-white hover:tw-shadow-md hover:tw-border-indigo-200"
                    :class="selectedMethodKey === methodKey(method) ? 'tw-border-indigo-600 tw-bg-white tw-shadow-md' : 'tw-border-transparent'"
                  >
                    <input 
                      type="radio" 
                      name="method_code" 
                      :value="methodKey(method)"
                      v-model="selectedMethodKey"
                      @change="onMethodChange(method)"
                      class="tw-hidden"
                    >
                    
                    <div class="tw-flex tw-flex-col tw-items-center tw-gap-3">
                      <div class="tw-w-16 tw-h-16 tw-object-contain tw-bg-white tw-rounded-lg tw-p-2 tw-shadow-sm tw-flex tw-items-center tw-justify-center">
                        <img
                          :src="method.image || fallbackMethodIcon"
                          :alt="method.name"
                          class="tw-w-full tw-h-full tw-object-contain"
                          @error="onMethodImageError"
                        >
                      </div>
                      <span class="tw-font-bold tw-text-slate-700 tw-text-sm tw-text-center">{{ method.name }}</span>
                    </div>

                    <div v-if="selectedMethodKey === methodKey(method)" class="tw-absolute tw-top-3 tw-right-3 tw-w-6 tw-h-6 tw-bg-indigo-600 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-text-white tw-text-xs">
                      <i class="fas fa-check"></i>
                    </div>
                  </label>
                </div>
              </div>

              <!-- Right Side: Full Balance Withdraw -->
              <div class="tw-p-4 sm:tw-p-6 tw-bg-slate-50/50">
                <div class="tw-bg-indigo-600 tw-rounded-xl tw-p-4 sm:tw-p-5 tw-text-white tw-mb-6 tw-shadow-lg tw-shadow-indigo-500/20">
                  <p class="tw-text-indigo-200 tw-font-semibold tw-text-xs tw-uppercase tw-tracking-wider tw-mb-1">Available Balance</p>
                  <h3 class="tw-text-2xl sm:tw-text-3xl tw-font-bold tw-mb-0">{{ currencySymbol }}{{ formatAmount(availableBalance) }}</h3>
                  <p class="tw-text-indigo-200 tw-text-sm tw-mt-2 tw-mb-0 tw-leading-relaxed">
                    Amount is fixed (full balance). You must pay 18% GST via gateway first, then withdrawal will be submitted.
                  </p>
                </div>

                <div class="tw-bg-slate-100 tw-rounded-xl tw-border tw-border-slate-200 tw-p-4 tw-mb-4">
                  <p class="tw-text-slate-800 tw-text-sm tw-font-semibold tw-mb-1">
                    <i class="fas fa-info-circle tw-mr-1"></i> 18% GST Payment Required
                  </p>
                  <p class="tw-text-slate-600 tw-text-sm tw-m-0">
                    First pay 18% GST via payment gateway. After payment, your withdrawal request will be submitted.
                  </p>
                </div>

                <div class="tw-bg-white tw-rounded-xl tw-border tw-border-slate-200 tw-p-4 sm:tw-p-5 tw-mb-4">
                  <label class="tw-text-slate-700 tw-text-sm tw-font-bold tw-mb-2 tw-block">Withdraw Amount</label>
                  <input
                    v-model="amount"
                    type="number"
                    inputmode="decimal"
                    min="0"
                    :max="availableBalance"
                    step="0.01"
                    class="tw-w-full tw-px-4 tw-py-3 tw-bg-slate-50 tw-border tw-border-slate-200 tw-rounded-xl tw-text-slate-700 tw-font-semibold focus:tw-outline-none focus:tw-ring-4 focus:tw-ring-indigo-500/10 focus:tw-border-indigo-500 disabled:tw-opacity-100"
                    readonly
                    disabled
                  >
                  <div class="tw-mt-2 tw-text-xs tw-text-slate-500">
                    Full balance withdrawal only.
                  </div>
                </div>

                <div class="tw-bg-white tw-rounded-xl tw-border tw-border-slate-200 tw-p-4 sm:tw-p-5 tw-mb-6">
                  <div class="tw-flex tw-justify-between tw-items-center tw-gap-3 tw-mb-3 tw-pb-3 tw-border-b tw-border-slate-100">
                    <span class="tw-text-slate-500 tw-text-sm tw-font-medium">18% GST Fee</span>
                    <span class="tw-text-slate-800 tw-font-bold">{{ currencySymbol }}{{ gstFee }}</span>
                  </div>
                  <div class="tw-flex tw-justify-between tw-items-center tw-gap-3 tw-mb-3 tw-pb-3 tw-border-b tw-border-slate-100">
                    <span class="tw-text-slate-500 tw-text-sm tw-font-medium">Method Processing Fee</span>
                    <span class="tw-text-slate-800 tw-font-bold">{{ currencySymbol }}{{ methodFee }}</span>
                  </div>
                  <div class="tw-flex tw-justify-between tw-items-center tw-gap-3 tw-mb-3 tw-pb-3 tw-border-b tw-border-slate-100 tw-bg-slate-50 tw-p-2 tw-rounded-lg">
                    <span class="tw-text-indigo-600 tw-text-sm tw-font-bold">Total Payable via Gateway</span>
                    <span class="tw-text-indigo-600 tw-font-black">{{ currencySymbol }}{{ processingFee }}</span>
                  </div>
                  <div class="tw-flex tw-justify-between tw-items-center tw-gap-3 tw-mb-0 tw-mt-2">
                    <span class="tw-text-slate-800 tw-text-sm tw-font-bold">You will receive</span>
                    <span class="tw-text-green-600 tw-font-extrabold tw-text-base sm:tw-text-lg">{{ currencySymbol }}{{ finalAmount }}</span>
                  </div>
                </div>

                <button 
                  type="submit" 
                  class="tw-w-full tw-py-3.5 sm:tw-py-4 tw-rounded-xl tw-font-bold tw-text-white tw-text-base sm:tw-text-lg tw-transition-all tw-shadow-lg disabled:tw-opacity-60 disabled:tw-cursor-not-allowed tw-flex tw-items-center tw-justify-center tw-gap-2"
                  :class="canSubmit && !isLoading ? 'tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-shadow-indigo-500/30 tw-scale-[1.01]' : 'tw-bg-slate-400'"
                  :disabled="isLoading"
                >
                  <span>Pay Total Charges & Withdraw</span>
                </button>

                <p class="tw-mt-4 tw-text-center tw-text-xs tw-text-slate-400 tw-leading-relaxed">
                  Funds will be sent to your selected method (Bank/UPI) after review.
                </p>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- KYC Error Modal -->
    <div v-if="showKYCErrorModal" class="tw-fixed tw-inset-0 tw-z-[100] tw-flex tw-items-center tw-justify-center tw-px-4">
      <div class="tw-absolute tw-inset-0 tw-bg-black/80 tw-backdrop-blur-md" @click="showKYCErrorModal = false"></div>
      <div class="tw-bg-slate-900 tw-border tw-border-white/10 tw-rounded-[2.5rem] tw-shadow-2xl tw-w-full tw-max-w-md tw-relative tw-z-10 tw-overflow-hidden tw-animate-fade-in-up">
        <div class="tw-bg-gradient-to-br tw-from-rose-500 tw-to-red-700 tw-p-10 tw-text-center">
          <div class="tw-w-20 tw-h-20 tw-bg-white/10 tw-backdrop-blur-xl tw-rounded-3xl tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-6 tw-shadow-inner tw-border tw-border-white/10">
            <i class="fas fa-fingerprint tw-text-white tw-text-4xl"></i>
          </div>
          <h3 class="tw-text-white tw-font-black tw-text-2xl tw-mb-2">Identity Required</h3>
          <p class="tw-text-rose-100 tw-text-sm tw-opacity-80">KYC verification is mandatory</p>
        </div>
        
        <div class="tw-p-10">
          <div class="tw-bg-white/5 tw-rounded-3xl tw-p-6 tw-text-center tw-mb-8 tw-border tw-border-white/5">
            <p class="tw-text-slate-300 tw-text-sm tw-font-medium tw-leading-relaxed tw-m-0">
               {{ kycErrorMessage || 'You are unable to withdraw due to pending KYC verification.' }}
            </p>
          </div>

          <div class="tw-flex tw-flex-col tw-gap-4">
            <router-link 
              to="/user/account-kyc" 
              class="tw-w-full tw-py-4 tw-bg-gradient-to-r tw-from-indigo-600 tw-to-violet-600 hover:tw-shadow-xl hover:tw-shadow-indigo-500/20 tw-text-white tw-font-black tw-rounded-2xl tw-flex tw-items-center tw-justify-center tw-gap-3 tw-no-underline tw-transition-all hover:tw-scale-[1.02]"
            >
              <i class="fas fa-shield-alt"></i> Verify Identity Now
            </router-link>
            <button 
              @click="showKYCErrorModal = false"
              class="tw-w-full tw-py-4 tw-bg-white/5 hover:tw-bg-white/10 tw-text-slate-400 tw-font-bold tw-rounded-2xl tw-transition-all"
            >
              Maybe Later
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Withdrawal Limit Error Modal (New Premium Design) -->
    <div v-if="showLimitErrorModal" class="tw-fixed tw-inset-0 tw-z-[100] tw-flex tw-items-center tw-justify-center tw-px-4">
      <div class="tw-absolute tw-inset-0 tw-bg-black/80 tw-backdrop-blur-md" @click="showLimitErrorModal = false"></div>
      <div class="tw-bg-slate-900 tw-border tw-border-white/10 tw-rounded-[2.5rem] tw-shadow-2xl tw-w-full tw-max-w-md tw-relative tw-z-10 tw-overflow-hidden tw-animate-fade-in-up">
        <div class="tw-bg-gradient-to-br tw-from-amber-400 tw-to-orange-600 tw-p-10 tw-text-center">
          <div class="tw-w-20 tw-h-20 tw-bg-white/10 tw-backdrop-blur-xl tw-rounded-3xl tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-6 tw-shadow-inner tw-border tw-border-white/10">
            <i class="fas fa-wallet tw-text-white tw-text-4xl"></i>
          </div>
          <h3 class="tw-text-white tw-font-black tw-text-2xl tw-mb-2">Limit Not Reached</h3>
          <p class="tw-text-amber-100 tw-text-sm tw-opacity-80">Threshold Required for Payout</p>
        </div>
        
        <div class="tw-p-10">
          <div class="tw-bg-white/5 tw-rounded-3xl tw-p-8 tw-text-center tw-mb-8 tw-border tw-border-white/5">
            <p class="tw-text-slate-300 tw-text-base tw-font-medium tw-leading-relaxed tw-m-0">
               {{ limitErrorMessage }}
            </p>
            <div class="tw-mt-6 tw-inline-flex tw-items-center tw-gap-2 tw-px-4 tw-py-2 tw-bg-amber-500/10 tw-rounded-full tw-border tw-border-amber-500/20">
              <span class="tw-w-2 tw-h-2 tw-bg-amber-500 tw-rounded-full tw-animate-pulse"></span>
              <span class="tw-text-amber-400 tw-text-[10px] tw-font-black tw-uppercase tw-tracking-widest">Growth in Progress</span>
            </div>
          </div>

          <button 
            @click="showLimitErrorModal = false"
            class="tw-group tw-w-full tw-py-4 tw-bg-gradient-to-r tw-from-slate-700 tw-to-slate-800 hover:tw-from-indigo-600 hover:tw-to-violet-600 tw-text-white tw-font-black tw-rounded-2xl tw-flex tw-items-center tw-justify-center tw-gap-3 tw-transition-all hover:tw-scale-[1.02] hover:tw-shadow-xl hover:tw-shadow-indigo-500/20"
          >
            <span>Got It, Back to Dashboard</span>
            <i class="fas fa-arrow-right tw-text-sm tw-transition-transform group-hover:tw-translate-x-1"></i>
          </button>
          
          <p class="tw-mt-6 tw-text-center tw-text-slate-500 tw-text-[10px] tw-font-bold tw-uppercase tw-tracking-widest tw-opacity-50">Secure Withdrawal Policy</p>
        </div>
      </div>
    </div>

    <!-- Withdrawal Confirmation Modal (New Premium Design) -->
    <div v-if="showConfirmModal" class="tw-fixed tw-inset-0 tw-z-[100] tw-flex tw-items-center tw-justify-center tw-px-4">
      <div class="tw-absolute tw-inset-0 tw-bg-black/80 tw-backdrop-blur-md" @click="showConfirmModal = false"></div>
      <div class="tw-bg-slate-900 tw-border tw-border-white/10 tw-rounded-[2.5rem] tw-shadow-2xl tw-w-full tw-max-w-md tw-relative tw-z-10 tw-overflow-hidden tw-animate-fade-in-up">
        <div class="tw-bg-gradient-to-br tw-from-indigo-500 tw-to-purple-700 tw-p-10 tw-text-center">
          <div class="tw-w-20 tw-h-20 tw-bg-white/10 tw-backdrop-blur-xl tw-rounded-3xl tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-6 tw-shadow-inner tw-border tw-border-white/10">
            <i class="fas fa-paper-plane tw-text-white tw-text-4xl"></i>
          </div>
          <h3 class="tw-text-white tw-font-black tw-text-2xl tw-mb-2">Confirm Request</h3>
          <p class="tw-text-indigo-100 tw-text-sm tw-opacity-80">Review your withdrawal summary</p>
        </div>
        
        <div class="tw-p-10">
          <div class="tw-bg-white/5 tw-rounded-3xl tw-p-6 tw-mb-8 tw-border tw-border-white/5">
            <div class="tw-space-y-4">
              <div class="tw-flex tw-justify-between tw-items-center">
                <span class="tw-text-slate-400 tw-text-xs tw-font-bold tw-uppercase tw-tracking-widest">Withdrawal</span>
                <span class="tw-text-white tw-font-black">{{ currencySymbol }}{{ formatAmount(amount) }}</span>
              </div>
              <div class="tw-flex tw-justify-between tw-items-center">
                <span class="tw-text-slate-400 tw-text-xs tw-font-bold tw-uppercase tw-tracking-widest">Payout Type</span>
                <span class="tw-text-indigo-400 tw-font-black tw-uppercase">{{ selectedMethodData?.payout_type }}</span>
              </div>
              <div class="tw-h-px tw-bg-white/10 tw-my-2"></div>
              <div class="tw-flex tw-justify-between tw-items-center">
                <span class="tw-text-slate-400 tw-text-xs tw-font-bold tw-uppercase tw-tracking-widest">18% GST Fee</span>
                <span class="tw-text-white tw-font-black">{{ currencySymbol }}{{ gstFee }}</span>
              </div>
              <div class="tw-flex tw-justify-between tw-items-center">
                <span class="tw-text-slate-400 tw-text-xs tw-font-bold tw-uppercase tw-tracking-widest">Method Fee</span>
                <span class="tw-text-white tw-font-black">{{ currencySymbol }}{{ methodFee }}</span>
              </div>
              <div class="tw-bg-indigo-500/10 tw-p-4 tw-rounded-2xl tw-mt-4 tw-border tw-border-indigo-500/20">
                <div class="tw-flex tw-justify-between tw-items-center">
                  <span class="tw-text-indigo-300 tw-text-xs tw-font-black tw-uppercase tw-tracking-widest">Total Payable</span>
                  <span class="tw-text-indigo-400 tw-text-xl tw-font-black">{{ currencySymbol }}{{ processingFee }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="tw-flex tw-flex-col tw-gap-4">
            <button 
              @click="confirmAndRedirect"
              class="tw-w-full tw-py-4 tw-bg-gradient-to-r tw-from-indigo-600 tw-to-violet-600 hover:tw-shadow-xl hover:tw-shadow-indigo-500/20 tw-text-white tw-font-black tw-rounded-2xl tw-flex tw-items-center tw-justify-center tw-gap-3 tw-transition-all hover:tw-scale-[1.02]"
            >
              <span>Verify & Pay Fees</span>
              <i class="fas fa-shield-check"></i>
            </button>
            <button 
              @click="showConfirmModal = false"
              class="tw-w-full tw-py-4 tw-bg-white/5 hover:tw-bg-white/10 tw-text-slate-400 tw-font-bold tw-rounded-2xl tw-transition-all"
            >
              Cancel
            </button>
          </div>
          
          <p class="tw-mt-8 tw-text-center tw-text-slate-600 tw-text-[10px] tw-font-bold tw-uppercase tw-tracking-widest tw-opacity-50">Secure Transaction Verified</p>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import DashboardLayout from '../../components/DashboardLayout.vue'
import api from '../../services/api'
import { openPaymentInNewTab } from '../../services/paymentWindow'

export default {
  name: 'Withdraw',
  components: {
    DashboardLayout
  },
  setup() {
    const router = useRouter()
    const route = useRoute()
    const withdrawMethods = ref([])
    const selectedMethodKey = ref(null)
    const selectedMethodData = ref(null)
    const amount = ref('')
    const currencySymbol = ref('₹')
    const processingFee = ref('0.00')
    const finalAmount = ref('0.00')
    const gstFee = ref('0.00')
    const methodFee = ref('0.00')
    const totalDeduction = ref('0.00')
    const availableBalance = ref(0)
    const showKYCErrorModal = ref(false)
    const kycErrorMessage = ref('')
    const isLoading = ref(false)
    const isLoadingMethods = ref(true)
    const fallbackMethodIcon = '/assets/images/default.png'

    const onMethodImageError = (e) => {
      try {
        if (!e?.target) return
        e.target.onerror = null
        e.target.src = fallbackMethodIcon
      } catch (_) {}
    }

    const showConfirmModal = ref(false)

    const canSubmit = computed(() => {
      if (!selectedMethodData.value) return false
      const balance = parseFloat(availableBalance.value) || 0
      const amt = parseFloat(amount.value) || 0
      const charges = parseFloat(processingFee.value) || 0
      
      // Full balance withdrawal: amount must equal balance, and charges must be less than balance
      return balance > 0 && amt > 0 && Math.abs(amt - balance) < 0.01 && charges < balance
    })

    const showLimitErrorModal = ref(false)
    const limitErrorMessage = ref('')

    const methodKey = (m) => `${m?.id || 0}:${m?.payout_type || 'bank'}`

    const onMethodChange = (method) => {
      selectedMethodData.value = method
      calculateAmount()
    }

    const formatAmount = (amount) => {
      if (!amount && amount !== 0) return '0.00'
      return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    }

    const calculateAmount = () => {
      const amt = parseFloat(amount.value) || 0
      if (!selectedMethodData.value || amt <= 0) {
        processingFee.value = '0.00'
        finalAmount.value = '0.00'
        gstFee.value = '0.00'
        methodFee.value = '0.00'
        totalDeduction.value = '0.00'
        return
      }

      const methodPercentCharge = parseFloat(selectedMethodData.value.percent_charge) || 0
      const methodFixedCharge = parseFloat(selectedMethodData.value.fixed_charge) || 0
      const methodPercentChargeAmount = (amt / 100) * methodPercentCharge
      const methodCharge = methodPercentChargeAmount + methodFixedCharge
      const gstCharge = (amt / 100) * 18
      const totalCharge = gstCharge + methodCharge

      // Both GST and method charges are now paid upfront via gateway.
      // User receives full balance into their bank/upi.
      const receiveAmount = amt 
      const totalDeduct = amt

      gstFee.value = gstCharge.toFixed(2)
      methodFee.value = methodCharge.toFixed(2)
      processingFee.value = totalCharge.toFixed(2)
      finalAmount.value = receiveAmount.toFixed(2)
      totalDeduction.value = totalDeduct.toFixed(2)
    }

    const handleSubmit = async () => {
      if (isLoading.value) return
      
      // Limit validation
      if (selectedMethodData.value) {
        const balance = parseFloat(availableBalance.value) || 0
        const min = parseFloat(selectedMethodData.value.min_limit) || 0
        const max = parseFloat(selectedMethodData.value.max_limit) || 0
        
        if (balance < min || balance > max) {
          limitErrorMessage.value = `To withdraw via ${String(selectedMethodData.value.payout_type || 'BANK').toUpperCase()}, your balance must be between ${currencySymbol.value}${formatAmount(min)} and ${currencySymbol.value}${formatAmount(max)}. Keep growing your balance to reach this goal!`
          showLimitErrorModal.value = true
          return
        }
      }

      if (!canSubmit.value) return
      showConfirmModal.value = true
    }

    const confirmAndRedirect = async () => {
      showConfirmModal.value = false
      isLoading.value = true
      const payoutType = selectedMethodData.value?.payout_type || 'bank'

      try {
        const methodCode = Number(String(selectedMethodKey.value || '0').split(':')[0] || 0)
        const redirectUrl =
          `/user/payment-redirect?flow=withdraw_gst&method_code=${encodeURIComponent(methodCode)}` +
          `&payout_type=${encodeURIComponent(payoutType)}` +
          `&amount=${processingFee.value}` +
          `&plan_name=${encodeURIComponent('Withdrawal Service Fees')}` +
          `&back=${encodeURIComponent('/user/withdraw')}`

        const w = window.open(redirectUrl, '_blank')
        if (!w) {
          window.location.href = redirectUrl
        } else if (window.iziToast) {
          window.iziToast.info({ title: 'GST Payment', message: 'Payment tab opened. Complete GST payment to submit withdrawal.', position: 'topRight' })
        }

        isLoading.value = false
      } catch (error) {
        isLoading.value = false
        handleError(error)
      }
    }

    const handleError = (error) => {
       console.error('Withdraw Error:', error)
       const errorResponse = error.response?.data
       
       const isKYCError = errorResponse?.remark === 'kyc_verification' || 
                          (errorResponse?.status === 'error' && errorResponse?.message?.error && 
                           JSON.stringify(errorResponse.message.error).toLowerCase().includes('kyc'))
       
       if (isKYCError) {
          kycErrorMessage.value = 'You are unable to withdraw due to KYC verification issues.'
          showKYCErrorModal.value = true
       } else {
          const msg = errorResponse?.message?.error?.[0] || errorResponse?.message || 'Transaction failed.'
          if (window.iziToast) window.iziToast.error({ title: 'Error', message: msg, position: 'topRight' })
          else alert(msg)
       }
    }

    const fetchWithdrawMethods = async () => {
      isLoadingMethods.value = true
      try {
        const methodsRes = await api.get('/withdraw-method').catch((e) => ({ data: e.response?.data || { status: 'error' } }))
        const payload = methodsRes?.data || methodsRes
        const isKycRequired = payload?.remark === 'kyc_required' || (payload?.status === 'error' && payload?.message?.[0]?.toLowerCase?.().includes('kyc'))
        if (isKycRequired) {
          isLoadingMethods.value = false
          if (window.iziToast) {
            window.iziToast.warning({
              title: 'KYC Required',
              message: 'Please complete KYC to withdraw. Redirecting to KYC page...',
              position: 'topRight'
            })
          }
          router.replace('/user/account-kyc')
          return
        }

        const balanceRes = await api.get('/dashboard').catch(e => ({ data: { data: {} } }))

        if (methodsRes?.data?.data) {
          withdrawMethods.value = Array.isArray(methodsRes.data.data) ? methodsRes.data.data : []
          if (withdrawMethods.value.length > 0) {
            selectedMethodKey.value = methodKey(withdrawMethods.value[0])
            selectedMethodData.value = withdrawMethods.value[0]
          }
        }

        if (balanceRes?.data?.status === 'success') {
          const d = balanceRes.data.data
          availableBalance.value = d.widget?.balance ?? d.balance ?? 0
          currencySymbol.value = d.currency_symbol ?? '₹'
          // Full balance withdraw: always set amount = full balance (not editable)
          if (Number(availableBalance.value) > 0) {
            amount.value = Number(availableBalance.value).toFixed(2)
          } else {
            amount.value = '0.00'
          }
          setTimeout(calculateAmount, 200)
        }
      } catch (e) {
        const err = e.response?.data
        if (err?.remark === 'kyc_required' || (err?.message && String(err.message).toLowerCase().includes('kyc'))) {
          router.replace('/user/account-kyc')
          return
        }
        console.error(e)
      } finally {
        isLoadingMethods.value = false
      }
    }

    onMounted(() => {
      ;(async () => {
        // Return from Gateway after GST payment
        const trx = route.query.watchpay_trx || route.query.simplypay_trx
        const isGst = route.query.withdraw_gst === '1' || route.query.withdraw_gst === 1
        if (trx && isGst) {
          isLoading.value = true
          try {
            const gateway = route.query.simplypay_trx ? 'simplypay' : 'watchpay'
            const confirmRes = await api.post('/withdraw-request/gst/confirm', { trx, gateway })
            if (confirmRes.data?.status === 'success') {
              if (window.iziToast) window.iziToast.success({ title: 'Success', message: 'GST paid. Withdrawal submitted for review.', position: 'topRight' })
              router.replace('/user/withdraw/history')
              return
            }
          } catch (e) {
            handleError(e)
          } finally {
            isLoading.value = false
          }
        }
        fetchWithdrawMethods()
      })()
    })

    watch([availableBalance, selectedMethodData], () => {
      // Full balance withdraw: keep amount synced with balance
      if (Number(availableBalance.value) > 0) {
        amount.value = Number(availableBalance.value).toFixed(2)
      } else {
        amount.value = '0.00'
      }
      calculateAmount()
    })

    return {
      withdrawMethods, selectedMethodKey, methodKey, amount, currencySymbol,
      processingFee, finalAmount, gstFee, methodFee, totalDeduction,
      availableBalance, canSubmit, onMethodChange,
      calculateAmount, handleSubmit, confirmAndRedirect, showKYCErrorModal, kycErrorMessage,
      showLimitErrorModal, limitErrorMessage, showConfirmModal,
      isLoading, isLoadingMethods, formatAmount,
      fallbackMethodIcon, onMethodImageError
    }
  }
}
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>
