<template>
  <DashboardLayout page-title="Affiliate Withdraw" :dark-theme="true">
    <div class="tw-flex tw-justify-center">
      <div class="tw-w-full lg:tw-w-3/4 xl:tw-w-2/3">
        <form @submit.prevent="handleSubmit">
          <div class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-slate-200 tw-overflow-hidden">
            <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-2 tw-max-h-[800px]">

              <!-- Left: Methods -->
              <div class="tw-p-6 tw-border-b lg:tw-border-b-0 lg:tw-border-r tw-border-slate-200 tw-overflow-y-auto custom-scrollbar tw-max-h-[600px] lg:tw-h-auto">
                <div class="tw-flex tw-items-center tw-justify-between tw-gap-3 tw-mb-4">
                  <h5 class="tw-text-slate-800 tw-font-bold tw-text-lg tw-mb-0">Select Withdrawal Method</h5>
                  <router-link to="/user/affiliate-income" class="tw-text-sm tw-font-bold tw-text-indigo-600 tw-no-underline hover:tw-underline">
                    Back
                  </router-link>
                </div>


                <!-- Loading State -->
                <div v-if="isLoadingMethods" class="tw-flex tw-justify-center tw-py-12">
                  <div class="tw-w-10 tw-h-10 tw-border-4 tw-border-indigo-500 tw-border-t-transparent tw-rounded-full tw-animate-spin"></div>
                </div>

                <div v-else-if="withdrawMethods.length === 0" class="tw-text-center tw-py-12 tw-bg-amber-50 tw-rounded-xl tw-border tw-border-amber-100">
                  <i class="fas fa-exclamation-circle tw-text-amber-500 tw-text-4xl tw-mb-3"></i>
                  <p class="tw-text-slate-700 tw-font-semibold">No withdrawal methods available.</p>
                  <p class="tw-text-slate-500 tw-text-sm">Please check back later or contact support.</p>
                </div>

                <div v-else class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-4">
                  <label
                    v-for="(method, index) in withdrawMethods"
                    :key="method?.id || index"
                    class="tw-relative tw-cursor-pointer tw-bg-slate-50 tw-border-2 tw-rounded-xl tw-p-4 tw-transition-all hover:tw-bg-white hover:tw-shadow-md hover:tw-border-indigo-200"
                    :class="selectedMethod === method.id ? 'tw-border-indigo-600 tw-bg-white tw-shadow-md' : 'tw-border-transparent'"
                  >
                    <input
                      type="radio"
                      name="method_code"
                      :value="method.id"
                      v-model="selectedMethod"
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
                    <div v-if="selectedMethod === method.id" class="tw-absolute tw-top-3 tw-right-3 tw-w-6 tw-h-6 tw-bg-indigo-600 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-text-white tw-text-xs">
                      <i class="fas fa-check"></i>
                    </div>
                  </label>
                </div>
              </div>

              <!-- Right: Full wallet withdraw + fee -->
              <div class="tw-p-6 tw-bg-slate-50/50">
                <div class="tw-bg-emerald-600 tw-rounded-xl tw-p-5 tw-text-white tw-mb-6 tw-shadow-lg tw-shadow-emerald-500/20">
                  <p class="tw-text-emerald-100 tw-font-semibold tw-text-xs tw-uppercase tw-tracking-wider tw-mb-1">Withdrawable Amount (affiliate wallet)</p>
                  <h3 class="tw-text-3xl tw-font-bold tw-mb-0">{{ currencySymbol }}{{ formatAmount(availableBalance) }}</h3>
                  <p class="tw-text-emerald-100 tw-text-sm tw-mt-2 tw-mb-0">Your entire affiliate wallet balance will be withdrawn at once.</p>
                </div>

                <div class="tw-bg-slate-100 tw-rounded-xl tw-border tw-border-slate-200 tw-p-4 tw-mb-4">
                  <p class="tw-text-slate-800 tw-text-sm tw-font-semibold tw-mb-1">
                    <i class="fas fa-info-circle tw-mr-1"></i> No 18% GST on affiliate withdrawals
                  </p>
                  <p class="tw-text-slate-600 tw-text-sm tw-m-0">
                    Affiliate users can withdraw directly. Only method charges (if any) may apply.
                  </p>
                </div>

                <div class="tw-bg-white tw-rounded-xl tw-border tw-border-slate-200 tw-p-5 tw-mb-6">
                  <div class="tw-flex tw-justify-between tw-items-center tw-mb-3 tw-pb-3 tw-border-b tw-border-slate-100">
                    <span class="tw-text-slate-500 tw-text-sm tw-font-medium">Method Charges (if any)</span>
                    <span class="tw-text-slate-800 tw-font-bold">{{ currencySymbol }}{{ processingFee }}</span>
                  </div>
                  <div class="tw-flex tw-justify-between tw-items-center">
                    <span class="tw-text-slate-800 tw-text-sm tw-font-bold">You will receive</span>
                    <span class="tw-text-green-600 tw-font-extrabold tw-text-lg">{{ currencySymbol }}{{ finalAmount }}</span>
                  </div>
                </div>

                <button
                  type="submit"
                  class="tw-w-full tw-py-4 tw-rounded-xl tw-font-bold tw-text-white tw-text-lg tw-transition-all tw-shadow-lg disabled:tw-opacity-60 disabled:tw-cursor-not-allowed tw-flex tw-items-center tw-justify-center tw-gap-2"
                  :class="canSubmit && !isLoading ? 'tw-bg-emerald-600 hover:tw-bg-emerald-700 tw-shadow-emerald-500/30' : 'tw-bg-slate-400'"
                  :disabled="!canSubmit || isLoading"
                >
                  <span>Withdraw (Affiliate)</span>
                </button>

                <p class="tw-mt-4 tw-text-center tw-text-xs tw-text-slate-400 tw-leading-relaxed">
                  Funds will be sent to your KYC bank account after admin review.
                </p>

                <div class="tw-mt-4 tw-text-center">
                  <router-link to="/user/affiliate-withdraw/history" class="tw-text-sm tw-font-bold tw-text-indigo-600 tw-no-underline hover:tw-underline">
                    View Affiliate Withdraw History
                  </router-link>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- KYC Error Modal -->
    <div v-if="showKYCErrorModal" class="tw-fixed tw-inset-0 tw-z-[60] tw-flex tw-items-center tw-justify-center tw-px-4">
      <div class="tw-absolute tw-inset-0 tw-bg-black/60 tw-backdrop-blur-sm" @click="showKYCErrorModal = false"></div>
      <div class="tw-bg-white tw-rounded-2xl tw-shadow-2xl tw-w-full tw-max-w-md tw-relative tw-z-10 tw-overflow-hidden tw-animate-fade-in-up">
        <div class="tw-bg-gradient-to-r tw-from-red-500 tw-to-rose-600 tw-p-6 tw-text-center">
          <div class="tw-w-16 tw-h-16 tw-bg-white/20 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-3">
            <i class="fas fa-user-shield tw-text-white tw-text-3xl"></i>
          </div>
          <h3 class="tw-text-white tw-font-bold tw-text-xl tw-mb-1">KYC Verification Required</h3>
          <p class="tw-text-red-100 tw-text-sm">Complete your verification to withdraw affiliate funds</p>
        </div>

        <div class="tw-p-6">
          <div class="tw-bg-red-50 tw-border tw-border-red-100 tw-rounded-xl tw-p-4 tw-text-center tw-mb-6">
            <p class="tw-text-red-800 tw-text-sm tw-font-medium tw-leading-relaxed">
              {{ kycErrorMessage || 'You are unable to withdraw due to pending KYC verification.' }}
            </p>
          </div>

          <div class="tw-flex tw-flex-col tw-gap-3">
            <router-link
              to="/user/account-kyc"
              class="tw-w-full tw-py-3 tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-font-semibold tw-rounded-xl tw-flex tw-items-center tw-justify-center tw-gap-2 tw-no-underline tw-transition-colors"
            >
              <i class="fas fa-user-check"></i> Complete KYC Now
            </router-link>
            <button
              @click="showKYCErrorModal = false"
              class="tw-w-full tw-py-3 tw-bg-slate-100 hover:tw-bg-slate-200 tw-text-slate-700 tw-font-semibold tw-rounded-xl tw-transition-colors"
            >
              Close
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
      if (!canSubmit.value || isLoading.value) return
      isLoading.value = true

      const amt = parseFloat(availableBalance.value) || 0
      const methodPercentCharge = parseFloat(selectedMethodData.value?.percent_charge) || 0
      const methodFixedCharge = parseFloat(selectedMethodData.value?.fixed_charge) || 0
      const methodPercentChargeAmount = (amt / 100) * methodPercentCharge
      const totalCharge = methodPercentChargeAmount + methodFixedCharge
      const totalAmount = amt - totalCharge

      const confirmed = confirm(
        `Affiliate Wallet Withdrawal\n\n` +
        `Withdrawal Amount: ${currencySymbol.value}${formatAmount(amt)}\n` +
        `Method Charges: ${currencySymbol.value}${formatAmount(methodPercentChargeAmount + methodFixedCharge)}\n` +
        `Total Charges: ${currencySymbol.value}${formatAmount(totalCharge)}\n\n` +
        `You will receive: ${currencySymbol.value}${formatAmount(totalAmount)}\n\n` +
        `Do you want to continue?`
      )

      if (!confirmed) {
        isLoading.value = false
        return
      }

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
      onMethodChange,
      handleSubmit,
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
</style>

