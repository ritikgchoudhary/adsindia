<template>
  <DashboardLayout page-title="Withdraw Money">
    <div class="row justify-content-center">
      <div class="col-lg-9">
        <form @submit.prevent="handleSubmit" class="withdraw-form">
          <div class="gateway-card">
            <div class="row justify-content-center gy-sm-4 gy-3">
              <div class="col-lg-6">
                <div class="payment-system-list is-scrollable gateway-option-list">
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
                </div>
              </div>
              <div class="col-lg-6">
                <div class="payment-system-list p-3">
                  <div class="deposit-info mb-3">
                    <div class="deposit-info__title">
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

                  <button type="submit" class="btn btn--base w-100 mb-3" :disabled="!canSubmit">
                    Confirm Withdraw
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
  </DashboardLayout>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
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

    const canSubmit = computed(() => {
      if (!selectedMethodData.value || !amount.value) return false
      const amt = parseFloat(amount.value) || 0
      return amt >= selectedMethodData.value.min_limit && amt <= selectedMethodData.value.max_limit
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
      if (!selectedMethodData.value || !amount.value) {
        gatewayLimit.value = '0.00 - 0.00'
        processingFee.value = '0.00'
        finalAmount.value = '0.00'
        return
      }

      const amt = parseFloat(amount.value) || 0
      const minAmount = parseFloat(selectedMethodData.value.min_limit) || 0
      const maxAmount = parseFloat(selectedMethodData.value.max_limit) || 0
      
      gatewayLimit.value = `${minAmount.toFixed(2)} - ${maxAmount.toFixed(2)}`

      // Calculate 18% withdrawal fee
      const withdrawalFee = (amt / 100) * withdrawalFeePercent.value
      const methodPercentCharge = parseFloat(selectedMethodData.value.percent_charge) || 0
      const methodFixedCharge = parseFloat(selectedMethodData.value.fixed_charge) || 0
      const methodPercentChargeAmount = (amt / 100) * methodPercentCharge
      const totalCharge = withdrawalFee + methodPercentChargeAmount + methodFixedCharge
      const totalAmount = amt - totalCharge

      processingFee.value = totalCharge.toFixed(2)
      finalAmount.value = totalAmount.toFixed(2)
    }

    const showAllMethods = () => {
      const paymentList = document.querySelector(".gateway-option-list")
      const items = paymentList.querySelectorAll(".payment-item")
      items.forEach(item => item.classList.remove("d-none"))
      document.querySelector(".more-gateway-option").classList.add('d-none')
    }

    const handleSubmit = async () => {
      if (!canSubmit.value) return

      const amt = parseFloat(amount.value) || 0
      const withdrawalFee = (amt / 100) * withdrawalFeePercent.value
      const methodPercentCharge = parseFloat(selectedMethodData.value?.percent_charge) || 0
      const methodFixedCharge = parseFloat(selectedMethodData.value?.fixed_charge) || 0
      const methodPercentChargeAmount = (amt / 100) * methodPercentCharge
      const totalCharge = withdrawalFee + methodPercentChargeAmount + methodFixedCharge
      const totalAmount = amt - totalCharge

      // Show 18% withdrawal fee confirmation popup
      const confirmed = confirm(
        `Withdrawal Confirmation\n\n` +
        `Withdrawal Amount: ${currencySymbol.value}${formatAmount(amt)}\n` +
        `Withdrawal Fee (18%): ${currencySymbol.value}${formatAmount(withdrawalFee)}\n` +
        `Processing Fee: ${currencySymbol.value}${formatAmount(methodPercentChargeAmount + methodFixedCharge)}\n` +
        `Total Charges: ${currencySymbol.value}${formatAmount(totalCharge)}\n\n` +
        `You will receive: ${currencySymbol.value}${formatAmount(totalAmount)}\n\n` +
        `Do you want to proceed with this withdrawal?`
      )
      
      if (!confirmed) return

      try {
        const response = await api.post('/withdraw-request', {
          method_code: selectedMethod.value,
          amount: amt
        })

        if (response.data.status === 'success') {
          if (window.iziToast) {
            window.iziToast.success({
              title: 'Success',
              message: response.data.message?.[0] || 'Withdrawal request submitted successfully!',
              position: 'topRight'
            })
          }
          router.push('/user/withdraw/history')
        }
      } catch (error) {
        console.error('Error submitting withdraw:', error)
        const errorMsg = error.response?.data?.message?.[0] || error.response?.data?.message || 'Failed to submit withdraw request'
        if (window.iziToast) {
          window.iziToast.error({
            title: 'Error',
            message: errorMsg,
            position: 'topRight'
          })
        }
      }
    }

    const fetchWithdrawMethods = async () => {
      try {
        const [methodsResponse, balanceResponse] = await Promise.all([
          api.get('/withdraw-method'),
          api.get('/user/dashboard')
        ])
        
        if (methodsResponse.data.status === 'success') {
          withdrawMethods.value = methodsResponse.data.data || []
          if (withdrawMethods.value.length > 0 && withdrawMethods.value[0] && withdrawMethods.value[0].id) {
            selectedMethod.value = withdrawMethods.value[0].id
            selectedMethodData.value = withdrawMethods.value[0]
            calculateAmount()
          }
        }

        if (balanceResponse.data.status === 'success' && balanceResponse.data.data) {
          availableBalance.value = balanceResponse.data.data.widget?.balance || 0
          currencySymbol.value = balanceResponse.data.data.currency_symbol || '₹'
        }
      } catch (error) {
        console.error('Error loading withdraw methods:', error)
      }
    }

    onMounted(() => {
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
      handleSubmit
    }
  }
}
</script>
