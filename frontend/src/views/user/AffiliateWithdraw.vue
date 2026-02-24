<template>
  <DashboardLayout page-title="Affiliate Withdraw" :dark-theme="true">
    <div class="tw-min-h-screen tw-py-4 sm:tw-py-8 tw-px-3 sm:tw-px-4">
      <div class="tw-max-w-5xl tw-mx-auto">
        <!-- Header Section -->
        <div class="tw-mb-4 sm:tw-mb-8 tw-flex tw-flex-col md:tw-flex-row md:tw-items-center tw-justify-between tw-gap-3 sm:tw-gap-4">
          <div>
            <h1 class="tw-text-xl sm:tw-text-3xl tw-font-extrabold tw-text-white tw-tracking-tight tw-mb-1 sm:tw-mb-2">Affiliate Withdrawal</h1>
            <p class="tw-text-slate-400 tw-text-[11px] sm:tw-text-sm">Transfer your earnings to your bank account securely.</p>
          </div>
          <router-link to="/user/affiliate-income" class="tw-group tw-flex tw-items-center tw-justify-center tw-gap-2 tw-px-4 tw-py-2 tw-bg-white/5 hover:tw-bg-white/10 tw-border tw-border-white/10 tw-rounded-xl tw-text-white tw-font-semibold tw-transition-all tw-no-underline tw-text-xs sm:tw-text-base">
            <i class="fas fa-arrow-left tw-text-[10px] sm:tw-text-xs tw-transition-transform group-hover:tw--translate-x-1"></i>
            <span>Back to Income</span>
          </router-link>
        </div>

        <form @submit.prevent="handleSubmit" class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-3 tw-gap-8">
          <!-- Left: Selection & Tools (2/3 width) -->
          <div class="lg:tw-col-span-2 tw-space-y-6">
            <!-- Method Selection (Moved to Top) -->
            <div class="tw-bg-slate-900/50 tw-backdrop-blur-xl tw-border tw-border-white/10 tw-rounded-2xl sm:tw-rounded-3xl tw-p-4 sm:tw-p-8">
              <div class="tw-flex tw-items-center tw-justify-between tw-mb-4 sm:tw-mb-6">
                <div class="tw-flex tw-items-center tw-gap-2 sm:tw-gap-3">
                  <div class="tw-w-8 tw-h-8 sm:tw-w-10 sm:tw-h-10 tw-bg-indigo-500/20 tw-rounded-lg sm:tw-rounded-xl tw-flex tw-items-center tw-justify-center">
                    <i class="fas fa-university tw-text-indigo-400 tw-text-sm sm:tw-text-base"></i>
                  </div>
                  <h2 class="tw-text-base sm:tw-text-xl tw-font-bold tw-text-white tw-m-0">Select Destination</h2>
                </div>
                <span class="tw-hidden md:tw-block tw-px-3 tw-py-1 tw-bg-white/5 tw-rounded-full tw-text-[10px] tw-text-slate-400 tw-font-bold tw-uppercase">KYC Verified Required</span>
              </div>

              <!-- Loading State -->
              <div v-if="isLoadingMethods" class="tw-flex tw-flex-col tw-items-center tw-justify-center tw-py-12">
                <div class="tw-relative tw-w-16 tw-h-16">
                  <div class="tw-absolute tw-inset-0 tw-border-4 tw-border-indigo-500/20 tw-rounded-full"></div>
                  <div class="tw-absolute tw-inset-0 tw-border-4 tw-border-indigo-500 tw-border-t-transparent tw-rounded-full tw-animate-spin"></div>
                </div>
                <p class="tw-text-slate-400 tw-text-sm tw-mt-4">Loading secure methods...</p>
              </div>

              <div v-else-if="withdrawMethods.length === 0" class="tw-text-center tw-py-12 tw-bg-white/5 tw-rounded-2xl tw-border tw-border-dashed tw-border-white/10">
                <div class="tw-w-16 tw-h-16 tw-bg-amber-500/10 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-4">
                  <i class="fas fa-exclamation-triangle tw-text-amber-500 tw-text-2xl"></i>
                </div>
                <p class="tw-text-white tw-font-semibold">No methods found</p>
                <p class="tw-text-slate-500 tw-text-sm tw-max-w-xs tw-mx-auto">We couldn't find any available withdrawal methods for your region.</p>
              </div>

              <div v-else class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-3 sm:tw-gap-4">
                <label
                  v-for="(method, index) in withdrawMethods"
                  :key="method?.id || index"
                  class="tw-group tw-relative tw-cursor-pointer tw-transition-all tw-duration-300"
                >
                  <input
                    type="radio"
                    name="method_code"
                    :value="method.id"
                    v-model="selectedMethod"
                    @change="onMethodChange(method)"
                    class="tw-hidden"
                  >
                  <div 
                    class="tw-bg-white/5 tw-border-2 tw-rounded-xl sm:tw-rounded-2xl tw-p-3 sm:tw-p-5 tw-flex tw-items-center tw-gap-3 sm:tw-gap-4 tw-transition-all tw-duration-300 group-hover:tw-bg-white/10"
                    :class="selectedMethod === method.id ? 'tw-border-indigo-500 tw-bg-indigo-500/5 tw-shadow-lg tw-shadow-indigo-500/10' : 'tw-border-white/5'"
                  >
                    <div class="tw-relative tw-w-10 tw-h-10 sm:tw-w-14 sm:tw-h-14 tw-bg-white tw-rounded-lg sm:tw-rounded-xl tw-p-1.5 sm:tw-p-2 tw-flex tw-items-center tw-justify-center tw-shadow-sm">
                      <img
                        :src="method.image || fallbackMethodIcon"
                        :alt="method.name"
                        class="tw-w-full tw-h-full tw-object-contain"
                        @error="onMethodImageError"
                      >
                    </div>
                    <div class="tw-flex-1">
                      <span class="tw-block tw-text-white tw-font-bold tw-text-xs sm:tw-text-sm">{{ method.name }}</span>
                      <span class="tw-block tw-text-slate-400 tw-text-[8px] sm:tw-text-[10px] tw-mt-0.5">Secure Payout</span>
                    </div>
                    <div class="tw-w-5 tw-h-5 tw-rounded-full tw-border-2 tw-flex tw-items-center tw-justify-center tw-transition-all"
                      :class="selectedMethod === method.id ? 'tw-bg-indigo-500 tw-border-indigo-500' : 'tw-border-white/20'"
                    >
                      <i v-if="selectedMethod === method.id" class="fas fa-check tw-text-white tw-text-[8px]"></i>
                    </div>
                  </div>
                </label>
              </div>
            </div>

            <!-- Balance Card (Now Below Selection) -->
            <div class="tw-relative tw-overflow-hidden tw-rounded-2xl sm:tw-rounded-3xl tw-bg-gradient-to-br tw-from-slate-800 tw-to-slate-900 tw-p-5 sm:tw-p-8 tw-shadow-2xl tw-border tw-border-white/5">
              <div class="tw-relative tw-z-10">
                <div class="tw-flex tw-justify-between tw-items-start tw-mb-4 sm:tw-mb-8">
                  <div class="tw-w-10 tw-h-10 sm:tw-w-12 sm:tw-h-12 tw-bg-indigo-500/20 tw-backdrop-blur-md tw-rounded-xl tw-flex tw-items-center tw-justify-center">
                    <i class="fas fa-wallet tw-text-indigo-400 tw-text-base sm:tw-text-xl"></i>
                  </div>
                  <div class="tw-text-right">
                    <span class="tw-text-white/60 tw-text-[10px] sm:tw-text-xs tw-font-bold tw-uppercase tw-tracking-widest">Affiliate Wallet</span>
                    <div class="tw-flex tw-items-center tw-gap-1 tw-justify-end tw-mt-0.5">
                      <div class="tw-w-1.5 tw-h-1.5 tw-bg-emerald-400 tw-rounded-full tw-animate-pulse"></div>
                      <span class="tw-text-emerald-400 tw-text-[8px] sm:tw-text-[10px] tw-font-bold tw-uppercase">Active</span>
                    </div>
                  </div>
                </div>
                
                <div class="tw-mb-2">
                  <span class="tw-text-white/80 tw-text-xs sm:tw-text-sm tw-font-medium">Total Withdrawable</span>
                  <div class="tw-flex tw-items-baseline tw-gap-2 tw-mt-1">
                    <span class="tw-text-white tw-text-2xl sm:tw-text-5xl tw-font-black">{{ currencySymbol }}{{ formatAmount(availableBalance) }}</span>
                  </div>
                </div>
                
                <div class="tw-mt-4 sm:tw-mt-6 tw-pt-4 sm:tw-pt-6 tw-border-t tw-border-white/10 tw-flex tw-items-center tw-justify-between tw-gap-4">
                  <p class="tw-text-slate-400 tw-text-[10px] sm:tw-text-xs tw-max-w-[180px] sm:tw-max-w-[250px] tw-m-0">Processed within 24-48 business hours.</p>
                  <div class="tw-flex tw-items-center tw-gap-1.5 tw-shrink-0">
                    <i class="fas fa-shield-alt tw-text-indigo-400/50 tw-text-lg sm:tw-text-2xl"></i>
                    <span class="tw-text-[8px] sm:tw-text-[10px] tw-text-slate-500 tw-font-black tw-uppercase tw-tracking-widest tw-whitespace-nowrap">Secure Gateway</span>
                  </div>
                </div>
              </div>
              
              <!-- Decorative elements -->
              <div class="tw-absolute tw-top-[-20%] tw-right-[-10%] tw-w-64 tw-h-64 tw-bg-indigo-500/5 tw-rounded-full tw-blur-3xl"></div>
              <div class="tw-absolute tw-bottom-[-10%] tw-left-[-5%] tw-w-48 tw-h-48 tw-bg-white/5 tw-rounded-full tw-blur-2xl"></div>
            </div>
          </div>

          <!-- Right: Summary (1/3 width) -->
          <div class="tw-space-y-6">
            <div class="tw-bg-slate-900/50 tw-backdrop-blur-xl tw-border tw-border-white/10 tw-rounded-3xl tw-p-6 tw-sticky tw-top-8">
              <h3 class="tw-text-lg tw-font-bold tw-text-white tw-mb-6 tw-flex tw-items-center tw-gap-2">
                <i class="fas fa-file-invoice-dollar tw-text-indigo-400"></i>
                Withdrawal Summary
              </h3>

              <div class="tw-space-y-4 tw-mb-8">
                <div class="tw-flex tw-justify-between tw-items-center tw-pb-3 tw-border-b tw-border-white/5">
                  <span class="tw-text-slate-400 tw-text-sm">Request Amount</span>
                  <span class="tw-text-white tw-font-bold">{{ currencySymbol }}{{ formatAmount(availableBalance) }}</span>
                </div>
                <div class="tw-flex tw-justify-between tw-items-center tw-pb-3 tw-border-b tw-border-white/5">
                  <span class="tw-text-slate-400 tw-text-sm">Service Fee</span>
                  <span class="tw-text-rose-400 tw-font-bold">- {{ currencySymbol }}{{ processingFee }}</span>
                </div>
                <div class="tw-flex tw-justify-between tw-items-center tw-pb-3 tw-border-b tw-border-white/5">
                  <span class="tw-text-slate-400 tw-text-sm">Handling Charges</span>
                  <span class="tw-text-rose-400 tw-font-bold">Free</span>
                </div>
                
                <div class="tw-pt-2 tw-mt-4 tw-p-4 tw-bg-indigo-500/10 tw-rounded-2xl tw-border tw-border-indigo-500/20">
                  <div class="tw-flex tw-justify-between tw-items-center">
                    <span class="tw-text-indigo-200 tw-text-xs tw-font-bold tw-uppercase">Net Payout</span>
                    <span class="tw-text-white tw-text-2xl tw-font-black">{{ currencySymbol }}{{ finalAmount }}</span>
                  </div>
                </div>
              </div>

              <div class="tw-bg-blue-500/10 tw-border tw-border-blue-500/20 tw-rounded-2xl tw-p-4 tw-mb-6">
                <div class="tw-flex tw-gap-3">
                  <i class="fas fa-info-circle tw-text-blue-400 tw-mt-1"></i>
                  <p class="tw-text-blue-100 tw-text-[11px] tw-m-0 tw-leading-relaxed">
                    <strong>Zero Tax Benefit:</strong> Affiliate withdrawals are exempt from 18% GST deduction. Enjoy your full earnings!
                  </p>
                </div>
              </div>

              <button
                type="submit"
                class="tw-group tw-w-full tw-relative tw-overflow-hidden tw-py-4 tw-rounded-2xl tw-font-black tw-text-white tw-text-lg tw-transition-all tw-duration-300 disabled:tw-opacity-50 disabled:tw-cursor-not-allowed tw-flex tw-items-center tw-justify-center tw-gap-3"
                :class="canSubmit && !isLoading ? 'tw-bg-gradient-to-r tw-from-indigo-600 tw-to-violet-600 hover:tw-shadow-xl hover:tw-shadow-indigo-500/40 hover:tw-scale-[1.02]' : 'tw-bg-white/10 tw-text-white/30'"
                :disabled="!canSubmit || isLoading"
              >
                <span class="tw-relative tw-z-10">{{ isLoading ? 'Verifying...' : 'Submit Request' }}</span>
                <i v-if="!isLoading" class="fas fa-paper-plane tw-text-sm tw-relative tw-z-10 tw-transition-transform group-hover:tw-translate-x-1 group-hover:tw--translate-y-1"></i>
                <div v-else class="tw-w-5 tw-h-5 tw-border-2 tw-border-white/30 tw-border-t-white tw-rounded-full tw-animate-spin"></div>
                
                <!-- Shine animation -->
                <div class="tw-absolute tw-top-0 tw-left-[-100%] tw-w-full tw-h-full tw-bg-gradient-to-r tw-from-transparent tw-via-white/20 tw-to-transparent tw-skew-x-[-25deg] tw-transition-all tw-duration-1000 group-hover:tw-left-[100%]"></div>
              </button>

              <div class="tw-mt-8 tw-text-center">
                <router-link to="/user/affiliate-withdraw/history" class="tw-text-xs tw-font-bold tw-text-indigo-400 hover:tw-text-indigo-300 tw-no-underline tw-flex tw-items-center tw-justify-center tw-gap-2">
                  <i class="fas fa-history"></i>
                  <span>View Withdrawal History</span>
                </router-link>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- KYC Error Modal (Modernized) -->
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

    <!-- Withdrawal Limit Error Modal (Premium Design) -->
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
        </div>
      </div>
    </div>

    <!-- Withdrawal Confirmation Modal (Premium Design) -->
    <div v-if="showConfirmModal" class="tw-fixed tw-inset-0 tw-z-[100] tw-flex tw-items-center tw-justify-center tw-px-4">
      <div class="tw-absolute tw-inset-0 tw-bg-black/80 tw-backdrop-blur-md" @click="showConfirmModal = false"></div>
      <div class="tw-bg-slate-900 tw-border tw-border-white/10 tw-rounded-[2.5rem] tw-shadow-2xl tw-w-full tw-max-w-md tw-relative tw-z-10 tw-overflow-hidden tw-animate-fade-in-up">
        <div class="tw-bg-gradient-to-br tw-from-indigo-500 tw-to-purple-700 tw-p-10 tw-text-center">
          <div class="tw-w-20 tw-h-20 tw-bg-white/10 tw-backdrop-blur-xl tw-rounded-3xl tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-6 tw-shadow-inner tw-border tw-border-white/10">
            <i class="fas fa-paper-plane tw-text-white tw-text-4xl"></i>
          </div>
          <h3 class="tw-text-white tw-font-black tw-text-2xl tw-mb-2">Confirm Request</h3>
          <p class="tw-text-indigo-100 tw-text-sm tw-opacity-80">Review your affiliate payout</p>
        </div>
        
        <div class="tw-p-10">
          <div class="tw-bg-white/5 tw-rounded-3xl tw-p-6 tw-mb-8 tw-border tw-border-white/5">
            <div class="tw-space-y-4">
              <div class="tw-flex tw-justify-between tw-items-center">
                <span class="tw-text-slate-400 tw-text-xs tw-font-bold tw-uppercase tw-tracking-widest">Affiliate Pay</span>
                <span class="tw-text-white tw-font-black">{{ currencySymbol }}{{ formatAmount(availableBalance) }}</span>
              </div>
              <div class="tw-flex tw-justify-between tw-items-center">
                <span class="tw-text-slate-400 tw-text-xs tw-font-bold tw-uppercase tw-tracking-widest">Payout Method</span>
                <span class="tw-text-indigo-400 tw-font-black tw-uppercase">{{ selectedMethodData?.name }}</span>
              </div>
              <div class="tw-h-px tw-bg-white/10 tw-my-2"></div>
              <div class="tw-flex tw-justify-between tw-items-center">
                <span class="tw-text-slate-400 tw-text-xs tw-font-bold tw-uppercase tw-tracking-widest">Processing Fee</span>
                <span class="tw-text-rose-400 tw-font-black">{{ currencySymbol }}{{ processingFee }}</span>
              </div>
              <div class="tw-bg-emerald-500/10 tw-p-4 tw-rounded-2xl tw-mt-4 tw-border tw-border-emerald-500/20">
                <div class="tw-flex tw-justify-between tw-items-center">
                  <span class="tw-text-emerald-300 tw-text-xs tw-font-black tw-uppercase tw-tracking-widest">Net Payout</span>
                  <span class="tw-text-emerald-400 tw-text-xl tw-font-black">{{ currencySymbol }}{{ finalAmount }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="tw-flex tw-flex-col tw-gap-4">
            <button 
              @click="confirmAndSubmit"
              class="tw-w-full tw-py-4 tw-bg-gradient-to-r tw-from-indigo-600 tw-to-violet-600 hover:tw-shadow-xl hover:tw-shadow-indigo-500/20 tw-text-white tw-font-black tw-rounded-2xl tw-flex tw-items-center tw-justify-center tw-gap-3 tw-transition-all hover:tw-scale-[1.02]"
            >
              <span>Verify & Submit</span>
              <i class="fas fa-check-circle"></i>
            </button>
            <button 
              @click="showConfirmModal = false"
              class="tw-w-full tw-py-4 tw-bg-white/5 hover:tw-bg-white/10 tw-text-slate-400 tw-font-bold tw-rounded-2xl tw-transition-all"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import DashboardLayout from '../../components/DashboardLayout.vue'
import api from '../../services/api'

export default {
  name: 'AffiliateWithdraw',
  components: { DashboardLayout },
  setup() {
    const router = useRouter()
    const withdrawMethods = ref([])
    const selectedMethod = ref(null)
    const selectedMethodData = ref(null)
    const currencySymbol = ref('₹')
    const processingFee = ref('0.00')
    const finalAmount = ref('0.00')
    const availableBalance = ref(0)
    const showKYCErrorModal = ref(false)
    const kycErrorMessage = ref('')
    const isLoading = ref(false)
    const isLoadingMethods = ref(true)
    const showConfirmModal = ref(false)
    const showLimitErrorModal = ref(false)
    const limitErrorMessage = ref('')
    const fallbackMethodIcon = '/assets/images/default.png'

    const onMethodImageError = (e) => {
      try {
        if (!e?.target) return
        e.target.onerror = null
        e.target.src = fallbackMethodIcon
      } catch (_) {}
    }

    const canSubmit = computed(() => {
      if (!selectedMethodData.value) return false
      const balance = parseFloat(availableBalance.value) || 0
      return balance > 0
    })

    const formatAmount = (amount) => {
      if (!amount && amount !== 0) return '0.00'
      return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    }

    const calculateAmount = () => {
      const amt = parseFloat(availableBalance.value) || 0
      if (!selectedMethodData.value || amt <= 0) {
        processingFee.value = '0.00'
        finalAmount.value = '0.00'
        return
      }

      const methodPercentCharge = parseFloat(selectedMethodData.value.percent_charge) || 0
      const methodFixedCharge = parseFloat(selectedMethodData.value.fixed_charge) || 0
      const methodPercentChargeAmount = (amt / 100) * methodPercentCharge
      const totalCharge = methodPercentChargeAmount + methodFixedCharge
      const totalAmount = Math.max(0, amt - totalCharge)

      processingFee.value = totalCharge.toFixed(2)
      finalAmount.value = totalAmount.toFixed(2)
    }

    const onMethodChange = (method) => {
      selectedMethodData.value = method
      calculateAmount()
    }

    const handleError = (error) => {
      console.error('Affiliate Withdraw Error:', error)
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

    const fetchWithdrawMethodsAndBalance = async () => {
      isLoadingMethods.value = true
      try {
        const methodsRes = await api.get('/affiliate/withdraw-method').catch((e) => ({ data: e.response?.data || { status: 'error' } }))
        const payload = methodsRes?.data || methodsRes
        const isKycRequired = payload?.remark === 'kyc_required' ||
          (payload?.status === 'error' && payload?.message?.[0]?.toLowerCase?.().includes('kyc'))
        if (isKycRequired) {
          isLoadingMethods.value = false
          router.replace('/user/account-kyc')
          return
        }

        const affRes = await api.get('/affiliate-income').catch(() => ({ data: { data: {} } }))

        if (methodsRes?.data?.data) {
          withdrawMethods.value = Array.isArray(methodsRes.data.data) ? methodsRes.data.data : []
          if (withdrawMethods.value.length > 0) {
            selectedMethod.value = withdrawMethods.value[0].id
            selectedMethodData.value = withdrawMethods.value[0]
          }
        }

        const d = affRes?.data?.data || {}
        availableBalance.value = d.affiliate_balance ?? 0
        currencySymbol.value = d.currency_symbol ?? '₹'
        setTimeout(calculateAmount, 200)
      } catch (e) {
        const err = e.response?.data
        if (err?.remark === 'kyc_required' || (err?.message && String(err.message).toLowerCase().includes('kyc'))) {
          router.replace('/user/account-kyc')
          return
        }
      } finally {
        isLoadingMethods.value = false
      }
    }

    const handleSubmit = async () => {
      if (isLoading.value) return

      // Limit validation
      if (selectedMethodData.value) {
        const balance = parseFloat(availableBalance.value) || 0
        const min = parseFloat(selectedMethodData.value.min_limit) || 10000
        const max = parseFloat(selectedMethodData.value.max_limit) || 1000000
        
        if (balance < min || balance > max) {
          limitErrorMessage.value = `To withdraw your affiliate earnings, your balance must be between ${currencySymbol.value}${formatAmount(min)} and ${currencySymbol.value}${formatAmount(max)}. Keep refering and earning to reach this goal!`
          showLimitErrorModal.value = true
          return
        }
      }

      if (!canSubmit.value) return
      showConfirmModal.value = true
    }

    const confirmAndSubmit = async () => {
      showConfirmModal.value = false
      isLoading.value = true
      const amt = parseFloat(availableBalance.value) || 0

      try {
        const response = await api.post('/affiliate/withdraw-request', {
          method_code: selectedMethod.value,
          amount: amt
        })

        if (response.data.status === 'success') {
          if (window.iziToast) window.iziToast.success({ title: 'Success', message: 'Withdrawal request submitted for review.', position: 'topRight' })
          router.push('/user/affiliate-withdraw/history')
        } else {
          isLoading.value = false
        }
      } catch (error) {
        isLoading.value = false
        handleError(error)
      }
    }

    onMounted(() => {
      fetchWithdrawMethodsAndBalance()
    })

    watch([availableBalance, selectedMethodData], () => {
      calculateAmount()
    })

    return {
      withdrawMethods,
      selectedMethod,
      selectedMethodData,
      currencySymbol,
      processingFee,
      finalAmount,
      availableBalance,
      canSubmit,
      isLoading,
      isLoadingMethods,
      showKYCErrorModal,
      kycErrorMessage,
      showConfirmModal,
      showLimitErrorModal,
      limitErrorMessage,
      onMethodChange,
      handleSubmit,
      confirmAndSubmit,
      formatAmount,
      fallbackMethodIcon,
      onMethodImageError,
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
  /* Header */
  h1.tw-text-xl { font-size: 1.15rem !important; }
  .tw-mb-4.sm\:tw-mb-8 { margin-bottom: 1.25rem !important; }
  
  /* Select Destination section */
  .tw-p-4.sm\:tw-p-8 { padding: 0.85rem !important; }
  h2.tw-text-base { font-size: 0.95rem !important; }
  .tw-w-8.tw-h-8 { width: 2.25rem !important; height: 2.25rem !important; }
  
  /* Method Grid -> 1 column (long names/UPI IDs need space) */
  .tw-grid.tw-grid-cols-1.md\:tw-grid-cols-2 {
    grid-template-columns: 1fr !important;
    gap: 0.75rem !important;
  }
  .tw-p-3.sm\:tw-p-5 { padding: 0.75rem !important; border-radius: 1rem !important; }
  .tw-w-10.tw-h-10 { width: 2.5rem !important; height: 2.5rem !important; }
  span.tw-text-xs.sm\:tw-text-sm { font-size: 0.75rem !important; line-height: 1.2; }
  
  /* Wallet Card */
  .tw-p-5.sm\:tw-p-8 { padding: 1rem !important; border-radius: 1.25rem !important; }
  h3.tw-text-base { font-size: 0.95rem !important; }
  .tw-text-2xl.sm\:tw-text-5xl { font-size: 1.65rem !important; }
  .tw-text-white\/80.tw-text-xs { font-size: 0.75rem !important; }
  .tw-mt-4.sm\:tw-mt-6 { margin-top: 1rem !important; padding-top: 0.75rem !important; }
  
  /* Summary Box */
  .tw-p-6.tw-sticky { padding: 0.85rem !important; border-radius: 1.25rem !important; }
  h3.tw-text-lg { font-size: 1rem !important; margin-bottom: 0.75rem !important; }
  .tw-space-y-4 { space-y: 2 !important; margin-bottom: 1rem !important; }
  .tw-text-white.tw-text-2xl { font-size: 1.25rem !important; }
  .tw-p-4.tw-bg-indigo-500\/10 { padding: 0.75rem !important; border-radius: 1rem !important; }
  
  /* Info box summary */
  .tw-p-4.tw-mb-6 { padding: 0.75rem !important; border-radius: 1rem !important; margin-bottom: 1rem !important; }
  
  /* Submit button */
  button.tw-py-4 { padding-top: 0.75rem !important; padding-bottom: 0.75rem !important; font-size: 1rem !important; border-radius: 1rem !important; }
  
  /* Back button */
  .tw-px-4.tw-py-2.tw-bg-white\/5 { padding: 0.5rem 0.75rem !important; border-radius: 0.85rem !important; }
}

@media (max-width: 400px) {
  .tw-grid.tw-grid-cols-1.md\:tw-grid-cols-2 { gap: 0.5rem !important; }
  .tw-text-2xl.sm\:tw-text-5xl { font-size: 1.4rem !important; }
}
</style>

