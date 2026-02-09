<template>
  <DashboardLayout page-title="Withdraw Money">
    <div class="row justify-content-center">
      <div class="col-lg-9">
        <form @submit.prevent="handleSubmit" class="withdraw-form">
          <div class="gateway-card">
            <div class="row justify-content-center gy-sm-4 gy-3">
              <div class="col-lg-6">
                <div class="payment-system-list is-scrollable gateway-option-list">
                  <div v-if="isLoadingMethods" class="text-center p-4">
                    <i class="fas fa-spinner fa-spin fa-2x mb-3" style="color: #667eea;"></i>
                    <p class="text-muted">Loading withdrawal methods...</p>
                  </div>
                  <template v-else-if="withdrawMethods.length === 0">
                    <div class="text-center p-4">
                      <i class="fas fa-exclamation-circle fa-2x mb-3" style="color: #ffc107;"></i>
                      <p class="text-muted">No withdrawal methods available at the moment.</p>
                    </div>
                  </template>
                  <template v-else>
                    <label v-for="(method, index) in withdrawMethods" :key="method?.id || index" 
                           :for="`method-${method.id}`"
                           class="payment-item" 
                           :class="{ 'd-none': index > 4 }">
                      <div class="payment-item__info">
                        <span class="payment-item__check"></span>
                        <span class="payment-item__name">{{ method.name }}</span>
                      </div>
                      <div class="payment-item__thumb">
                        <img class="payment-item__thumb-img" :src="method.image" alt="payment-thumb">
                      </div>
                      <input class="payment-item__radio gateway-input" 
                             :id="`method-${method.id}`" 
                             hidden
                             type="radio" 
                             name="method_code" 
                             :value="method.id"
                             v-model="selectedMethod"
                             @change="onMethodChange(method)">
                    </label>
                    <button v-if="withdrawMethods.length > 4" type="button" class="payment-item__btn more-gateway-option" @click="showAllMethods">
                      <p class="payment-item__btn-text">Show All Payment Options</p>
                      <span class="payment-item__btn__icon"><i class="fas fa-chevron-down"></i></span>
                    </button>
                  </template>
                </div>
              </div>
              <div class="col-lg-6">
                <div class="payment-system-list p-3">
                  <div class="deposit-info mb-3">
                    <div class="deposit-info__title mb-2">
                      <p class="text mb-0">Amount</p>
                    </div>
                    <div class="deposit-info__input">
                      <div class="deposit-info__input-group input-group">
                        <span class="deposit-info__input-group-text">{{ currencySymbol }}</span>
                        <input type="text" v-model="amount" class="form-control form--control amount" name="amount" placeholder="00.00" autocomplete="off" @input="calculateAmount">
                      </div>
                    </div>
                  </div>

                  <hr class="my-3">

                  <div class="deposit-info mb-3">
                    <div class="deposit-info__title">
                      <p class="text has-icon mb-1">Limit</p>
                    </div>
                    <div class="deposit-info__input">
                      <p class="text"><span class="gateway-limit">{{ gatewayLimit }}</span></p>
                    </div>
                  </div>

                  <div class="deposit-info mb-3">
                    <div class="deposit-info__title">
                      <p class="text has-icon mb-1">
                        Withdrawal Fee (18%)
                        <span data-bs-toggle="tooltip" title="18% withdrawal fee will be deducted from your withdrawal amount" class="proccessing-fee-info">
                          <i class="las la-info-circle"></i>
                        </span>
                      </p>
                    </div>
                    <div class="deposit-info__input">
                      <p class="text">
                        {{ currencySymbol }}<span class="processing-fee">{{ processingFee }}</span> {{ currencyText }}
                      </p>
                    </div>
                  </div>
                  <div class="deposit-info mb-3">
                    <div class="deposit-info__title">
                      <p class="text mb-1">Available Balance</p>
                    </div>
                    <div class="deposit-info__input">
                      <p class="text">
                        <strong>{{ currencySymbol }}{{ formatAmount(availableBalance) }}</strong>
                      </p>
                    </div>
                  </div>

                  <div class="deposit-info total-amount pt-3 mb-3">
                    <div class="deposit-info__title">
                      <p class="text">Receivable</p>
                    </div>
                    <div class="deposit-info__input">
                      <p class="text">
                        {{ currencySymbol }}<span class="final-amount">{{ finalAmount }}</span> {{ currencyText }}
                      </p>
                    </div>
                  </div>

                  <button type="submit" class="btn btn--base w-100 mb-3" :disabled="!canSubmit || isLoading">
                    <span v-if="isLoading">
                      <i class="fas fa-spinner fa-spin me-2"></i>Processing...
                    </span>
                    <span v-else>Confirm Withdraw</span>
                  </button>

                  <div class="info-text pt-2">
                    <p class="text">
                      Safely withdraw your funds using our highly secure process and various withdrawal method
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- KYC Verification Error Modal -->
    <div v-if="showKYCErrorModal" class="modal fade show d-block" style="background: rgba(0,0,0,0.5); z-index: 9999;" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 15px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
          <div class="modal-header border-0 pb-0" style="border-radius: 15px 15px 0 0; background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);">
            <h5 class="modal-title text-white" style="font-weight: 700;">
              <i class="fas fa-exclamation-triangle me-2"></i>KYC Verification Required
            </h5>
            <button type="button" class="btn-close btn-close-white" @click="showKYCErrorModal = false"></button>
          </div>
          <div class="modal-body p-4">
            <div class="text-center mb-4">
              <div class="d-inline-block p-4 rounded-circle mb-3" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);">
                <i class="fas fa-user-shield fa-3x text-white"></i>
              </div>
              <h4 class="mb-2" style="font-weight: 700; color: #2d3748;">KYC Verification Required</h4>
              <p class="text-muted mb-3" style="font-size: 15px;">
                {{ kycErrorMessage || 'You are unable to withdraw due to KYC verification. Please complete your KYC verification to proceed with withdrawals.' }}
              </p>
            </div>
            <div class="alert alert-warning" style="border-radius: 12px; background: #fff3cd; border-color: #ffc107;">
              <div class="d-flex align-items-start">
                <i class="fas fa-info-circle me-2 mt-1" style="color: #856404;"></i>
                <div>
                  <strong style="color: #856404;">Why KYC is Required?</strong>
                  <p class="mb-0 mt-1" style="color: #856404; font-size: 14px;">
                    KYC (Know Your Customer) verification helps us ensure the security of your account and comply with financial regulations. Complete your KYC to unlock all withdrawal features.
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer border-0 pt-0">
            <button type="button" class="btn btn-secondary" @click="showKYCErrorModal = false" style="border-radius: 10px; font-weight: 600; padding: 10px 20px;">
              <i class="fas fa-times me-2"></i>Close
            </button>
            <router-link to="/user/account-kyc" class="btn btn-lg px-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 10px; font-weight: 600; padding: 10px 20px; text-decoration: none;"
              @click="showKYCErrorModal = false">
              <i class="fas fa-user-check me-2"></i>Complete KYC Now
            </router-link>
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
  name: 'Withdraw',
  components: {
    DashboardLayout
  },
  setup() {
    const router = useRouter()
    const withdrawMethods = ref([])
    const selectedMethod = ref(null)
    const selectedMethodData = ref(null)
    const amount = ref('')
    const currencySymbol = ref('₹')
    const currencyText = ref('INR')
    const gatewayLimit = ref('0.00 - 0.00')
    const processingFee = ref('0.00')
    const finalAmount = ref('0.00')
    const availableBalance = ref(0)
    const withdrawalFeePercent = ref(18) // 18% withdrawal fee
    const showKYCErrorModal = ref(false)
    const kycErrorMessage = ref('')
    const isLoading = ref(false)
    const isLoadingMethods = ref(true)

    const canSubmit = computed(() => {
      if (!selectedMethodData.value || !amount.value) return false
      const amt = parseFloat(amount.value) || 0
      const maxAmount = parseFloat(availableBalance.value) || 0 // Allow total balance withdrawal
      const minLimit = parseFloat(selectedMethodData.value.min_limit) || 0
      return amt >= minLimit && amt <= maxAmount && amt > 0
    })

    const onMethodChange = (method) => {
      selectedMethodData.value = method
      calculateAmount()
    }

    const formatAmount = (amount) => {
      if (!amount && amount !== 0) return '0.00'
      return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    }

    const calculateAmount = () => {
      // Always show limit if method is selected
      if (selectedMethodData.value) {
        const minAmount = parseFloat(selectedMethodData.value.min_limit) || 0
        const maxAmount = parseFloat(selectedMethodData.value.max_limit) || 0
        gatewayLimit.value = `${minAmount.toFixed(2)} - ${maxAmount.toFixed(2)}`
      } else {
        gatewayLimit.value = '0.00 - 0.00'
      }

      // If method not selected, can't calculate
      if (!selectedMethodData.value) {
        processingFee.value = '0.00'
        finalAmount.value = '0.00'
        return
      }

      // Parse amount and validate
      const amt = parseFloat(amount.value) || 0
      
      // Don't calculate if amount is 0, invalid, or empty
      if (!amount.value || 
          amount.value === '' || 
          amount.value === '0' || 
          amount.value === '0.00' || 
          amount.value === '00.00' ||
          amt <= 0 || 
          isNaN(amt)) {
        processingFee.value = '0.00'
        finalAmount.value = '0.00'
        return
      }

      // Calculate 18% withdrawal fee
      const withdrawalFee = (amt / 100) * withdrawalFeePercent.value
      const methodPercentCharge = parseFloat(selectedMethodData.value.percent_charge) || 0
      const methodFixedCharge = parseFloat(selectedMethodData.value.fixed_charge) || 0
      const methodPercentChargeAmount = (amt / 100) * methodPercentCharge
      const totalCharge = withdrawalFee + methodPercentChargeAmount + methodFixedCharge
      const totalAmount = Math.max(0, amt - totalCharge) // Ensure non-negative

      processingFee.value = totalCharge.toFixed(2)
      finalAmount.value = totalAmount.toFixed(2)
    }

    const showAllMethods = () => {
      const paymentList = document.querySelector(".gateway-option-list")
      const items = paymentList.querySelectorAll(".payment-item")
      items.forEach(item => item.classList.remove("d-none"))
      document.querySelector(".more-gateway-option").classList.add('d-none')
    }

    const setMaxAmount = () => {
      const balance = parseFloat(availableBalance.value) || 0
      if (balance > 0) {
        // Set amount value
        amount.value = balance.toFixed(2)
        // Force Vue to update and then calculate
        setTimeout(() => {
          calculateAmount()
        }, 100)
      } else {
        if (window.iziToast) {
          window.iziToast.warning({
            title: 'Warning',
            message: 'No balance available for withdrawal',
            position: 'topRight'
          })
        }
      }
    }

    const handleSubmit = async () => {
      if (!canSubmit.value || isLoading.value) return

      isLoading.value = true
      const amt = parseFloat(amount.value) || 0
      const withdrawalFee = (amt / 100) * withdrawalFeePercent.value
      const methodPercentCharge = parseFloat(selectedMethodData.value?.percent_charge) || 0
      const methodFixedCharge = parseFloat(selectedMethodData.value?.fixed_charge) || 0
      const methodPercentChargeAmount = (amt / 100) * methodPercentCharge
      const totalCharge = withdrawalFee + methodPercentChargeAmount + methodFixedCharge
      const totalAmount = amt - totalCharge

      // Show 18% withdrawal fee payment confirmation
      const confirmed = confirm(
        `Withdrawal Request\n\n` +
        `Withdrawal Amount: ${currencySymbol.value}${formatAmount(amt)}\n` +
        `Withdrawal Fee (18%): ${currencySymbol.value}${formatAmount(withdrawalFee)}\n` +
        `Processing Fee: ${currencySymbol.value}${formatAmount(methodPercentChargeAmount + methodFixedCharge)}\n` +
        `Total Charges: ${currencySymbol.value}${formatAmount(totalCharge)}\n\n` +
        `You will receive: ${currencySymbol.value}${formatAmount(totalAmount)}\n\n` +
        `You need to pay ${currencySymbol.value}${formatAmount(withdrawalFee)} (18% fee) first to proceed.\n\n` +
        `Do you want to continue?`
      )
      
      if (!confirmed) return

      try {
        // Step 1: Create withdrawal request
        const response = await api.post('/withdraw-request', {
          method_code: selectedMethod.value,
          amount: amt
        })

        if (response.data.status === 'success') {
          const withdrawData = response.data.data
          
          // Step 2: Show payment confirmation for 18% fee
          const payConfirmed = confirm(
            `Pay Withdrawal Fee\n\n` +
            `Withdrawal Amount: ${currencySymbol.value}${formatAmount(amt)}\n` +
            `Withdrawal Fee (18%): ${currencySymbol.value}${formatAmount(withdrawalFee)}\n\n` +
            `This fee will be deducted from your balance.\n` +
            `After payment, your withdrawal request will be submitted for review.\n\n` +
            `Do you want to pay ${currencySymbol.value}${formatAmount(withdrawalFee)} now?`
          )

          if (!payConfirmed) {
            if (window.iziToast) {
              window.iziToast.info({
                title: 'Info',
                message: 'Withdrawal request created. You can pay the fee later from withdrawal history.',
                position: 'topRight'
              })
            }
            router.push('/user/withdraw/history')
            isLoading.value = false
            return
          }

          // Step 3: Pay the 18% fee
          try {
            const feeResponse = await api.post('/withdraw-request/pay-fee', {
              trx: withdrawData.trx
            })

            if (feeResponse.data.status === 'success') {
              if (window.iziToast) {
                window.iziToast.success({
                  title: 'Success',
                  message: feeResponse.data.message?.[0] || 'Withdrawal fee paid successfully! Your withdrawal request has been submitted for review.',
                  position: 'topRight'
                })
              }
              router.push('/user/withdraw/history')
            } else {
              isLoading.value = false
            }
          } catch (feeError) {
            isLoading.value = false
            console.error('Error paying withdrawal fee:', feeError)
            
            // Check if it's a KYC verification error
            const feeErrorResponse = feeError.response?.data
            console.log('Fee error response:', feeErrorResponse) // Debug log
            
            // Check for KYC error - format: { remark: 'kyc_verification', status: 'error', message: { error: [...] } }
            const isKYCError = feeErrorResponse?.remark === 'kyc_verification' || 
                              (feeErrorResponse?.status === 'error' && 
                               feeErrorResponse?.message?.error && 
                               Array.isArray(feeErrorResponse.message.error) &&
                               feeErrorResponse.message.error.some(msg => 
                                 typeof msg === 'string' && (
                                   msg.toLowerCase().includes('kyc') || 
                                   msg.toLowerCase().includes('verification')
                                 )
                               ))
            
            if (isKYCError) {
              // Show KYC error modal
              const errorMsg = Array.isArray(feeErrorResponse?.message?.error) 
                ? feeErrorResponse.message.error[0] 
                : (feeErrorResponse?.message?.error || feeErrorResponse?.message || 'You are unable to withdraw due to KYC verification. Please complete your KYC verification to proceed.')
              
              kycErrorMessage.value = errorMsg
              showKYCErrorModal.value = true
            } else {
              // Show regular error toast
              const feeErrorMsg = Array.isArray(feeErrorResponse?.message?.error) 
                ? feeErrorResponse.message.error[0] 
                : (feeErrorResponse?.message?.[0] || feeErrorResponse?.message || 'Failed to pay withdrawal fee')
              if (window.iziToast) {
                window.iziToast.error({
                  title: 'Error',
                  message: feeErrorMsg,
                  position: 'topRight'
                })
              }
            }
          }
        }
      } catch (error) {
        isLoading.value = false
        console.error('Error submitting withdraw:', error)
        
        // Check if it's a KYC verification error
        const errorResponse = error.response?.data
        console.log('Error response:', errorResponse) // Debug log
        
        // Check for KYC error - format: { remark: 'kyc_verification', status: 'error', message: { error: [...] } }
        const isKYCError = errorResponse?.remark === 'kyc_verification' || 
                          (errorResponse?.status === 'error' && 
                           errorResponse?.message?.error && 
                           Array.isArray(errorResponse.message.error) &&
                           errorResponse.message.error.some(msg => 
                             typeof msg === 'string' && (
                               msg.toLowerCase().includes('kyc') || 
                               msg.toLowerCase().includes('verification')
                             )
                           ))
        
        if (isKYCError) {
          // Show KYC error modal
          const errorMsg = Array.isArray(errorResponse?.message?.error) 
            ? errorResponse.message.error[0] 
            : (errorResponse?.message?.error || errorResponse?.message || 'You are unable to withdraw due to KYC verification. Please complete your KYC verification to proceed.')
          
          kycErrorMessage.value = errorMsg
          showKYCErrorModal.value = true
        } else {
          // Show regular error toast
          const errorMsg = Array.isArray(errorResponse?.message?.error) 
            ? errorResponse.message.error[0] 
            : (errorResponse?.message?.[0] || errorResponse?.message || 'Failed to submit withdraw request')
          if (window.iziToast) {
            window.iziToast.error({
              title: 'Error',
              message: errorMsg,
              position: 'topRight'
            })
          }
        }
      }
    }

    const fetchWithdrawMethods = async () => {
      isLoadingMethods.value = true
      try {
        // Fetch withdraw methods and dashboard data in parallel
        const [methodsResponse, balanceResponse] = await Promise.all([
          api.get('/withdraw-method').catch(err => {
            console.error('Error fetching withdraw methods:', err)
            return { data: { status: 'error', data: [] } }
          }),
          api.get('/dashboard').catch(err => {
            console.error('Error fetching dashboard:', err)
            return { data: { status: 'error', data: {} } }
          })
        ])
        
        // Handle withdraw methods response
        let methodsLoaded = false
        if (methodsResponse.data?.status === 'success' || methodsResponse.data?.remark === 'withdraw_methods') {
          const methods = methodsResponse.data.data || []
          withdrawMethods.value = Array.isArray(methods) ? methods : []
          methodsLoaded = true
          
          if (withdrawMethods.value.length > 0) {
            const firstMethod = withdrawMethods.value[0]
            if (firstMethod && firstMethod.id) {
              selectedMethod.value = firstMethod.id
              selectedMethodData.value = firstMethod
            }
          }
        } else {
          console.warn('Withdraw methods response format unexpected:', methodsResponse.data)
          // Try to extract methods from different response formats
          if (methodsResponse.data?.data && Array.isArray(methodsResponse.data.data)) {
            withdrawMethods.value = methodsResponse.data.data
            methodsLoaded = true
            if (withdrawMethods.value.length > 0) {
              const firstMethod = withdrawMethods.value[0]
              if (firstMethod && firstMethod.id) {
                selectedMethod.value = firstMethod.id
                selectedMethodData.value = firstMethod
              }
            }
          }
        }

        // Handle dashboard/balance response
        let balanceLoaded = false
        if (balanceResponse.data?.status === 'success') {
          const dashboardData = balanceResponse.data.data
          if (dashboardData) {
            // Extract balance from widget
            availableBalance.value = dashboardData.widget?.balance ?? 
                                     dashboardData.balance ?? 
                                     0
            
            // Extract currency symbol
            currencySymbol.value = dashboardData.currency_symbol ?? '₹'
            
            // If balance is still 0, try to get from user object
            if (availableBalance.value === 0 && dashboardData.user?.balance !== undefined) {
              availableBalance.value = dashboardData.user.balance
            }
            balanceLoaded = true
          }
        } else {
          console.warn('Dashboard response format unexpected:', balanceResponse.data)
        }
        
        // Set amount to total balance if balance is loaded (even if methods failed)
        if (balanceLoaded && availableBalance.value > 0) {
          amount.value = availableBalance.value.toFixed(2)
          // Calculate after ensuring method is selected
          setTimeout(() => {
            if (selectedMethodData.value) {
              calculateAmount()
            } else if (withdrawMethods.value.length > 0) {
              // If method wasn't selected, try to select first one
              const firstMethod = withdrawMethods.value[0]
              if (firstMethod && firstMethod.id) {
                selectedMethod.value = firstMethod.id
                selectedMethodData.value = firstMethod
                calculateAmount()
              }
            }
          }, 300)
        }
      } catch (error) {
        console.error('Error loading withdraw methods:', error)
        
        // Check if it's a KYC verification error
        const errorResponse = error.response?.data
        
        // Check for KYC error - format: { remark: 'kyc_verification', status: 'error', message: { error: [...] } }
        const isKYCError = errorResponse?.remark === 'kyc_verification' || 
                          (errorResponse?.status === 'error' && 
                           errorResponse?.message?.error && 
                           Array.isArray(errorResponse.message.error) &&
                           errorResponse.message.error.some(msg => 
                             typeof msg === 'string' && (
                               msg.toLowerCase().includes('kyc') || 
                               msg.toLowerCase().includes('verification')
                             )
                           ))
        
        if (isKYCError) {
          // Show KYC error modal
          const errorMsg = Array.isArray(errorResponse?.message?.error) 
            ? errorResponse.message.error[0] 
            : (errorResponse?.message?.error || errorResponse?.message || 'You are unable to withdraw due to KYC verification. Please complete your KYC verification to proceed.')
          
          kycErrorMessage.value = errorMsg
          showKYCErrorModal.value = true
        } else {
          // Show general error notification
          const errorMsg = errorResponse?.message?.error?.[0] || 
                          errorResponse?.message?.[0] || 
                          errorResponse?.message || 
                          'Failed to load withdrawal methods. Please try again.'
          
          if (window.iziToast) {
            window.iziToast.error({
              title: 'Error',
              message: errorMsg,
              position: 'topRight'
            })
          }
        }
      }
    }

    // Watch for amount and method changes to auto-calculate
    watch([amount, selectedMethodData], () => {
      if (selectedMethodData.value) {
        calculateAmount()
      }
    }, { deep: true })

    // Check KYC status and redirect if not verified
    const checkKYCStatus = async () => {
      try {
        const response = await api.get('/dashboard')
        if (response.data?.status === 'success' && response.data.data?.user) {
          const user = response.data.data.user
          // kv: 0 = KYC_UNVERIFIED, 1 = KYC_VERIFIED, 2 = KYC_PENDING, 3 = KYC_REJECTED
          // Only allow if KYC is verified (kv === 1)
          if (user.kv !== 1) {
            // Show notification and redirect to KYC page
            if (window.iziToast) {
              window.iziToast.warning({
                title: 'KYC Verification Required',
                message: 'Please complete your KYC verification to withdraw funds.',
                position: 'topRight'
              })
            }
            // Redirect to KYC page
            router.push('/user/account-kyc')
            return false
          }
        }
        return true
      } catch (error) {
        console.error('Error checking KYC status:', error)
        // If error, still allow access but show warning
        return true
      }
    }

    onMounted(async () => {
      // Check KYC first before loading methods
      const canAccess = await checkKYCStatus()
      if (!canAccess) {
        return // Redirected, don't continue
      }
      
      fetchWithdrawMethods()
      if (window.jQuery) {
        setTimeout(() => {
          window.jQuery('[data-bs-toggle="tooltip"]').tooltip()
        }, 100)
      }
    })

    return {
      withdrawMethods,
      selectedMethod,
      amount,
      currencySymbol,
      currencyText,
      gatewayLimit,
      processingFee,
      finalAmount,
      availableBalance,
      formatAmount,
      canSubmit,
      onMethodChange,
      calculateAmount,
      showAllMethods,
      handleSubmit,
      showKYCErrorModal,
      kycErrorMessage,
      isLoading,
      isLoadingMethods
    }
  }
}
</script>
