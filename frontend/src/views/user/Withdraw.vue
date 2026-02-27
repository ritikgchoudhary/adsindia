<template>
  <DashboardLayout page-title="Withdraw Money" :dark-theme="true">
    <div class="tw-flex tw-px-3 sm:tw-px-0">
      <div class="tw-w-full">
        <form @submit.prevent="handleSubmit">
          <div class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-slate-200">
            <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-2">
              
              <!-- Left Side: Payment Methods -->
              <div class="tw-p-4 sm:tw-p-6 tw-border-b lg:tw-border-b-0 lg:tw-border-r tw-border-slate-200">
                <h5 class="tw-text-slate-800 tw-font-bold tw-text-lg tw-mb-4">Select Withdrawal Method</h5>
                <div v-if="isLoadingMethods" class="tw-flex tw-flex-col tw-items-center tw-justify-center tw-py-12">
                   <i class="fas fa-spinner fa-spin tw-text-indigo-600 tw-text-3xl tw-mb-3"></i>
                   <p class="tw-text-slate-500 tw-text-sm">Loading methods...</p>
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
                <div class="tw-relative tw-overflow-hidden tw-rounded-2xl tw-bg-gradient-to-br tw-from-slate-800 tw-to-slate-900 tw-p-6 sm:tw-p-8 tw-shadow-2xl tw-border tw-border-white/5 tw-mb-6">
                  <div class="tw-relative tw-z-10">
                    <div class="tw-flex tw-justify-between tw-items-start tw-mb-4">
                      <div class="tw-w-10 tw-h-10 sm:tw-w-12 sm:tw-h-12 tw-bg-indigo-500/20 tw-backdrop-blur-md tw-rounded-xl tw-flex tw-items-center tw-justify-center">
                        <i class="fas fa-wallet tw-text-indigo-400 tw-text-base sm:tw-text-xl"></i>
                      </div>
                      <div class="tw-text-right">
                        <span class="tw-text-white/60 tw-text-[10px] sm:tw-text-xs tw-font-bold tw-uppercase tw-tracking-widest">Available Balance</span>
                        <div class="tw-flex tw-items-center tw-gap-1 tw-justify-end tw-mt-0.5">
                          <div class="tw-w-1.5 tw-h-1.5 tw-bg-emerald-400 tw-rounded-full tw-animate-pulse"></div>
                          <span class="tw-text-emerald-400 tw-text-[8px] sm:tw-text-[10px] tw-font-bold tw-uppercase">Secure</span>
                        </div>
                      </div>
                    </div>
                    
                    <div class="tw-mb-4">
                      <div class="tw-flex tw-items-baseline tw-gap-2">
                        <span class="tw-text-white tw-text-3xl sm:tw-text-5xl tw-font-black">{{ currencySymbol }}{{ formatAmount(availableBalance) }}</span>
                      </div>
                    </div>
                    
                    <div class="tw-pt-4 tw-border-t tw-border-white/10">
                      <p class="tw-text-slate-400 tw-text-[10px] sm:tw-text-xs tw-leading-relaxed tw-m-0">
                        Amount is fixed. Payout process begins after 18% GST verification via secure gateway.
                      </p>
                    </div>
                  </div>
                  <!-- Decorative blur -->
                  <div class="tw-absolute tw-top-[-20%] tw-right-[-10%] tw-w-64 tw-h-64 tw-bg-indigo-500/5 tw-rounded-full tw-blur-3xl"></div>
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

                <!-- Instant Payout Beta -->
                <div v-if="hasBetaAccess" class="tw-bg-indigo-50 tw-border tw-border-indigo-100 tw-rounded-xl tw-p-4 tw-mb-4 tw-relative tw-overflow-hidden">
                  <div class="tw-flex tw-items-center tw-justify-between tw-relative tw-z-10">
                    <div class="tw-flex tw-items-center tw-gap-3">
                      <div class="tw-w-10 tw-h-10 tw-bg-white tw-rounded-xl tw-flex tw-items-center tw-justify-center tw-shadow-sm">
                        <i class="fas fa-bolt tw-text-indigo-600"></i>
                      </div>
                      <div>
                        <h6 class="tw-text-indigo-900 tw-font-bold tw-text-sm tw-m-0">Instant Payout Premium</h6>
                        <p class="tw-text-indigo-500 tw-text-[10px] tw-m-0 tw-font-medium">Get paid in 1-2 hours instead of 48h</p>
                      </div>
                    </div>
                    <button type="button" @click="isInstantPayout = !isInstantPayout; calculateAmount()" class="tw-relative tw-inline-flex tw-h-6 tw-w-11 tw-flex-shrink-0 tw-cursor-pointer tw-rounded-full tw-border-2 tw-border-transparent tw-transition-colors tw-duration-200 tw-ease-in-out focus:tw-outline-none" :class="isInstantPayout ? 'tw-bg-indigo-600' : 'tw-bg-slate-200'">
                      <span class="tw-pointer-events-none tw-inline-block tw-h-5 tw-w-5 tw-transform tw-rounded-full tw-bg-white tw-shadow tw-ring-0 tw-transition tw-duration-200 tw-ease-in-out" :class="isInstantPayout ? 'tw-translate-x-5' : 'tw-translate-x-0'"></span>
                    </button>
                  </div>
                  <div v-if="isInstantPayout" class="tw-mt-3 tw-pt-3 tw-border-t tw-border-indigo-100 tw-text-[10px] tw-text-indigo-600 tw-font-bold">
                    <i class="fas fa-plus tw-mr-1"></i> ₹50.00 Instant Processing Fee Added
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
                  <div class="tw-flex tw-justify-between tw-items-center tw-gap-3 tw-mb-0 tw-mt-2" style="display: flex !important; justify-content: space-between !important; width: 100% !important; gap: 12px !important;">
                    <span class="tw-text-slate-800 tw-text-sm tw-font-bold tw-flex-1" style="text-align: left !important;">You will receive</span>
                    <span class="tw-text-green-600 tw-font-extrabold tw-text-base sm:tw-text-lg tw-flex-initial tw-whitespace-nowrap" style="text-align: right !important;">{{ currencySymbol }}{{ finalAmount }}</span>
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
                <div class="tw-flex tw-justify-between tw-items-center tw-w-full" style="display: flex !important; justify-content: space-between !important; width: 100% !important; gap: 12px !important;">
                  <span class="tw-text-indigo-300 tw-text-[8px] tw-font-black tw-uppercase tw-tracking-widest tw-flex-1" style="text-align: left !important;">Total Payable</span>
                  <span class="tw-text-indigo-400 tw-text-lg tw-font-black tw-whitespace-nowrap tw-flex-initial" style="text-align: right !important;">{{ currencySymbol }}{{ processingFee }}</span>
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
    const hasBetaAccess = ref(localStorage.getItem('beta_access') === 'true')
    const isInstantPayout = ref(false)
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
      const instantFee = isInstantPayout.value ? 50 : 0
      const totalCharge = gstCharge + methodCharge + instantFee

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
          `&is_priority=${isInstantPayout.value ? 1 : 0}` +
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
        // Fetch User info to sync Beta Access
        try {
          const res = await api.get('/user/info', { __skipLoader: true })
          const d = res.data?.data || res.data
          hasBetaAccess.value = !!(d?.beta_access)
          localStorage.setItem('beta_access', hasBetaAccess.value)
        } catch (e) {}

        // Return from Gateway after GST payment
        const trx = route.query.watchpay_trx || route.query.simplypay_trx || route.query.rupeerush_trx
        const isGst = route.query.withdraw_gst === '1' || route.query.withdraw_gst === 1
        if (trx && isGst) {
          isLoading.value = true
          try {
            const gateway = route.query.simplypay_trx ? 'simplypay' : (route.query.rupeerush_trx ? 'rupeerush' : 'watchpay')
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
      fallbackMethodIcon, onMethodImageError,
      hasBetaAccess, isInstantPayout
    }
  }
}
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

@media (max-width: 640px) {
  .tw-grid-cols-1.lg\:tw-grid-cols-2 { gap: 0; }
  .tw-bg-white.tw-rounded-2xl { border-radius: 1.25rem; }
  
  /* Header adjustments */
  h5.tw-text-lg { font-size: 1rem !important; margin-bottom: 0.75rem !important; }
  
  /* Method Grid 2 columns even on mobile */
  .tw-grid.tw-grid-cols-1.md\:tw-grid-cols-2 {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.65rem !important;
  }
  
  .tw-p-4.sm\:tw-p-6 { padding: 0.75rem !important; }
  
  /* Method card adjustments */
  .tw-relative.tw-p-4 { padding: 0.65rem !important; border-radius: 0.75rem !important; }
  .tw-w-16.tw-h-16 { width: 2.75rem !important; height: 2.75rem !important; padding: 0.35rem !important; border-radius: 0.65rem !important; }
  span.tw-text-sm { font-size: 0.75rem !important; }
  .tw-w-6.tw-h-6 { width: 1.25rem !important; height: 1.25rem !important; top: 0.5rem !important; right: 0.5rem !important; font-size: 10px !important; }
  
  /* New Redesigned Wallet Card - Mobile */
  .tw-bg-gradient-to-br.tw-from-slate-800 { padding: 1.25rem !important; margin-bottom: 1.25rem !important; border-radius: 1.25rem !important; }
  .tw-w-10.tw-h-10 { width: 2.25rem !important; height: 2.25rem !important; border-radius: 0.75rem !important; }
  .tw-text-3xl.sm\:tw-text-5xl { font-size: 2rem !important; line-height: 1 !important; margin: 0.25rem 0 !important; }
  .tw-text-white\/60 { font-size: 9px !important; }
  .tw-text-slate-400.tw-text-\[10px\] { font-size: 10px !important; margin-top: 0.75rem !important; opacity: 0.7; }
  
  /* Info box */
  .tw-bg-slate-100 { padding: 0.65rem 0.85rem !important; border-radius: 0.75rem; margin-bottom: 0.75rem !important; }
  .tw-text-sm.tw-font-semibold { font-size: 0.75rem !important; }
  .tw-text-slate-600.tw-text-sm { font-size: 0.65rem !important; line-height: 1.3; }
  
  /* Input box */
  .tw-bg-white.tw-rounded-xl.tw-p-4 { padding: 0.75rem !important; margin-bottom: 0.75rem !important; border-radius: 0.75rem; }
  .tw-text-sm.tw-font-bold.tw-mb-2 { font-size: 0.75rem !important; margin-bottom: 0.35rem !important; }
  input.tw-py-3 { padding: 0.5rem 0.75rem !important; font-size: 1rem !important; border-radius: 0.65rem !important; }
  .tw-mt-2.tw-text-xs { font-size: 10px !important; margin-top: 0.25rem !important; }
  
  /* Summary details */
  .tw-bg-white.tw-rounded-xl.tw-border.tw-p-4 { padding: 0.75rem !important; border-radius: 0.75rem; margin-bottom: 0.85rem !important; }
  .tw-text-sm.tw-font-medium { font-size: 0.7rem !important; }
  .tw-text-slate-800.tw-font-bold { font-size: 0.8rem !important; }
  .tw-p-2.tw-rounded-lg { padding: 0.5rem !important; margin-bottom: 0.5rem !important; }
  .tw-text-green-600 { font-size: 1rem !important; }
  .tw-mb-3.tw-pb-3 { margin-bottom: 0.5rem !important; padding-bottom: 0.5rem !important; }
  
  /* Submit Button */
  button.tw-py-3.5 { padding: 0.75rem !important; font-size: 0.9rem !important; border-radius: 0.75rem !important; }
  .tw-mt-4.tw-text-center.tw-text-xs { font-size: 9px !important; margin-top: 0.5rem !important; }

  /* Modals - Ultra-Sleek & Compact Mobile */
  .tw-rounded-\[2\.5rem\] { border-radius: 0.5rem !important; }
  .tw-w-full.tw-max-w-md { width: 95% !important; max-width: 290px !important; margin: auto !important; display: block !important; }
  
  /* Top Section - Slim & Simple (Targeting Confirm Modal specifically where possible) */
  .tw-bg-gradient-to-br.tw-from-indigo-500.tw-p-10 { 
    background: #1e293b !important; 
    padding: 0.5rem !important; 
    border-bottom: 2px solid rgba(99, 102, 241, 0.2);
    text-align: center !important;
  }
  
  /* Limit Error Modal - Keep its character but compact */
  .tw-from-amber-400.tw-p-10 {
    padding: 1rem !important;
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    text-align: center !important;
  }
  .tw-from-amber-400 .tw-w-20.tw-h-20 { 
    display: flex !important; 
    width: 2.5rem !important; 
    height: 2.5rem !important; 
    margin-bottom: 0.5rem !important; 
    border-radius: 0.75rem !important;
    margin-left: auto !important;
    margin-right: auto !important;
  }
  .tw-from-amber-400 .tw-text-4xl { font-size: 1.25rem !important; }
  .tw-from-amber-400 h3 { text-align: center !important; width: 100% !important; margin: 0 auto !important; }
  
  .tw-w-20.tw-h-20:not(.tw-from-amber-400 *) { display: none !important; }
  
  h3.tw-text-2xl { font-size: 0.85rem !important; margin: 0 !important; color: #fff !important; width: 100% !important; font-weight: 900 !important; text-align: center !important; }
  p.tw-text-sm.tw-opacity-80, .tw-p-10 p.tw-text-indigo-100 { display: none !important; } 
  
  /* Message Text Sizing & Centering */
  .tw-p-8.tw-text-center p.tw-text-base { font-size: 0.75rem !important; line-height: 1.4 !important; text-align: center !important; }
  .tw-p-10 { padding: 0.75rem !important; }
  .tw-p-8 { padding: 0.75rem !important; }
  .tw-mx-auto.tw-mb-6 { margin-left: auto !important; margin-right: auto !important; margin-bottom: 0.5rem !important; }
  .tw-text-center { text-align: center !important; }  
  /* Body Section Alignment */
  .tw-p-10:not(.tw-bg-gradient-to-br) { padding: 0.5rem 0.65rem !important; }
  .tw-p-6.tw-mb-8 { margin-bottom: 0.25rem !important; padding: 0.45rem !important; border-radius: 0.4rem !important; background: rgba(15,23,42,0.6) !important; }
  
  /* Target only the actual rows to reduce spacing */
  .tw-space-y-4 > div.tw-flex.tw-justify-between { 
    margin-top: 0.2rem !important; 
    width: 100% !important;
  }
  
  /* Reset the summary box internal flex */
  .tw-bg-indigo-500\/10 .tw-flex.tw-justify-between,
  .tw-bg-emerald-500\/10 .tw-flex.tw-justify-between {
    display: flex !important;
    justify-content: space-between !important;
    width: 100% !important;
    align-items: center !important;
  }
  
  .tw-text-xs.tw-font-bold.tw-uppercase { font-size: 6.5px !important; opacity: 0.6; letter-spacing: 0px !important; flex-shrink: 0 !important; }
  .tw-font-black { font-size: 10px !important; text-align: right !important; white-space: nowrap !important; }
  .tw-text-xl.tw-font-black { font-size: 0.95rem !important; text-align: right !important; color: #10b981 !important; white-space: nowrap !important; }

  /* Summary Box */
  .tw-bg-indigo-500\/10.tw-p-4 { padding: 0.4rem !important; margin-top: 0.35rem !important; border-radius: 0.4rem !important; }
  .tw-flex.tw-flex-col.tw-gap-4 { gap: 0.35rem !important; }
  
  /* Buttons Centering */
  .tw-py-4 { padding: 0.5rem !important; border-radius: 0.5rem !important; font-size: 0.8rem !important; width: 100% !important; height: auto !important; }
  .tw-mt-6 { margin-top: 0.4rem !important; }
  .tw-mb-8 { margin-bottom: 0.2rem !important; }
  
  /* Backdrops */
  .tw-absolute.tw-inset-0.tw-bg-black\/80 { background: rgba(0,0,0,0.85) !important; backdrop-filter: blur(2px) !important; }
  
  .tw-text-\[10px\] { font-size: 6.5px !important; opacity: 0.4; margin-top: 0.35rem !important; display: block !important; width: 100% !important; text-align: center !important; }
}

@media (max-width: 400px) {
  .tw-grid.tw-grid-cols-1.md\:tw-grid-cols-2 { gap: 0.15rem; }
}
</style>
