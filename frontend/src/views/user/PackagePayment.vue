<template>
  <DashboardLayout page-title="Payment Gateway">
    <div class="row justify-content-center">
      <div class="col-lg-6 col-md-8">
        <div class="card custom--card border-0 shadow-sm" style="border-radius: 15px;">
          <div class="card-body p-5 text-center">
            <!-- Payment Processing -->
            <div v-if="paymentStatus === 'processing'">
              <div class="mb-4">
                <div class="spinner-border text-primary mb-3" role="status" style="width: 4rem; height: 4rem;">
                  <span class="visually-hidden">Loading...</span>
                </div>
                <h4 class="mb-3" style="color: #2d3748; font-weight: 600;">Processing Payment...</h4>
                <p class="text-muted mb-4">Please wait while we process your payment</p>
                <div class="progress mb-3" style="height: 8px; border-radius: 10px;">
                  <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%"></div>
                </div>
                <p class="small text-muted">Amount: <strong style="color: #667eea;">{{ currencySymbol }}{{ formatAmount(paymentAmount) }}</strong></p>
                <p class="small text-muted">Package: <strong>{{ packageName }}</strong></p>
              </div>
            </div>

            <!-- Payment Success -->
            <div v-else-if="paymentStatus === 'success'">
              <div class="mb-4">
                <div class="mb-4">
                  <i class="fas fa-check-circle fa-5x text-success mb-3"></i>
                </div>
                <h4 class="mb-3" style="color: #2d3748; font-weight: 600;">Payment Done!</h4>
                <p class="text-muted mb-4">Your payment has been processed successfully</p>
                <div class="alert alert-success" role="alert" style="border-radius: 10px;">
                  <h6 class="alert-heading mb-2"><i class="fas fa-check-circle me-2"></i>Payment Successful</h6>
                  <p class="mb-2"><strong>Package:</strong> {{ packageName }}</p>
                  <p class="mb-2"><strong>Amount:</strong> {{ currencySymbol }}{{ formatAmount(paymentAmount) }}</p>
                  <p class="mb-0"><strong>Transaction ID:</strong> {{ transactionId }}</p>
                </div>
                <p class="text-muted mb-4">Your package has been upgraded successfully!</p>
                <button class="btn btn--base btn-lg px-5" @click="goToPackages" style="border-radius: 10px; font-weight: 600;">
                  <i class="fas fa-box me-2"></i>View Packages
                </button>
              </div>
            </div>

            <!-- Payment Failed -->
            <div v-else-if="paymentStatus === 'failed'">
              <div class="mb-4">
                <div class="mb-4">
                  <i class="fas fa-times-circle fa-5x text-danger mb-3"></i>
                </div>
                <h4 class="mb-3" style="color: #2d3748; font-weight: 600;">Payment Failed</h4>
                <p class="text-muted mb-4">{{ errorMessage || 'Payment could not be processed. Please try again.' }}</p>
                <button class="btn btn-outline-primary btn-lg px-5 me-3" @click="retryPayment" style="border-radius: 10px; font-weight: 600;">
                  <i class="fas fa-redo me-2"></i>Try Again
                </button>
                <button class="btn btn-secondary btn-lg px-5" @click="goBack" style="border-radius: 10px; font-weight: 600;">
                  <i class="fas fa-arrow-left me-2"></i>Go Back
                </button>
              </div>
            </div>

            <!-- Dummy Gateway Info -->
            <div v-if="paymentStatus === 'processing'" class="mt-4">
              <div class="alert alert-info" role="alert" style="border-radius: 10px; background: #e0f2fe; border-color: #0ea5e9; color: #0c4a6e;">
                <small>
                  <i class="fas fa-info-circle me-2"></i>
                  <strong>Dummy Gateway:</strong> This is a test payment gateway. In production, this will be replaced with a real payment gateway.
                </small>
              </div>
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
  name: 'PackagePayment',
  components: {
    DashboardLayout
  },
  setup() {
    const route = useRoute()
    const router = useRouter()
    const paymentStatus = ref('processing') // processing, success, failed
    const paymentAmount = ref(0)
    const packageName = ref('')
    const packageId = ref(null)
    const transactionId = ref('')
    const currencySymbol = ref('₹')
    const errorMessage = ref('')

    const formatAmount = (amount) => {
      if (!amount && amount !== 0) return '0.00'
      return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    }

    const processPayment = async () => {
      try {
        // Get package details from route query params
        packageId.value = route.query.package_id || route.params.package_id
        paymentAmount.value = parseFloat(route.query.amount) || 0
        packageName.value = route.query.package_name || 'Package'
        transactionId.value = route.query.trx || ''

        console.log('Payment page loaded with params:', {
          package_id: packageId.value,
          amount: paymentAmount.value,
          package_name: packageName.value,
          trx: transactionId.value
        })

        if (!packageId.value) {
          paymentStatus.value = 'failed'
          errorMessage.value = 'Invalid package ID'
          return
        }

        if (!transactionId.value) {
          // If no transaction ID, we need to initiate payment first
          console.log('No transaction ID, initiating payment...')
          const initResponse = await api.post('/packages/purchase', {
            package_id: packageId.value,
            payment_method: 'gateway'
          })

          console.log('Payment initiation response:', initResponse.data)

          if (initResponse.data.status === 'success' && initResponse.data.data?.trx) {
            transactionId.value = initResponse.data.data.trx
            paymentAmount.value = initResponse.data.data.amount || paymentAmount.value
            packageName.value = initResponse.data.data.package_name || packageName.value
          } else {
            paymentStatus.value = 'failed'
            errorMessage.value = initResponse.data.message?.error?.[0] || 'Failed to initiate payment'
            return
          }
        }

        // Simulate payment processing delay (2 seconds)
        setTimeout(async () => {
          try {
            // Process dummy payment
            console.log('Processing dummy payment with:', {
              trx: transactionId.value,
              package_id: packageId.value,
              status: 'success'
            })

            const paymentResponse = await api.post('/packages/payment/dummy', {
              trx: transactionId.value,
              package_id: parseInt(packageId.value),
              status: 'success' // Always success for dummy gateway
            })

            console.log('Payment response:', paymentResponse.data)

            if (paymentResponse.data.status === 'success') {
              paymentStatus.value = 'success'
              if (window.notify) {
                window.notify('success', 'Payment completed successfully!')
              }
            } else {
              paymentStatus.value = 'failed'
              errorMessage.value = paymentResponse.data.message?.error?.[0] || 'Payment failed'
            }
          } catch (error) {
            console.error('Payment processing error:', error)
            console.error('Error details:', error.response?.data)
            paymentStatus.value = 'failed'
            errorMessage.value = error.response?.data?.message?.error?.[0] || error.response?.data?.message || 'Payment processing failed'
            if (window.notify) {
              window.notify('error', errorMessage.value)
            }
          }
        }, 2000)
      } catch (error) {
        console.error('Payment initiation error:', error)
        console.error('Error details:', error.response?.data)
        paymentStatus.value = 'failed'
        errorMessage.value = error.response?.data?.message?.error?.[0] || error.response?.data?.message || 'Failed to initiate payment'
        if (window.notify) {
          window.notify('error', errorMessage.value)
        }
      }
    }

    const retryPayment = () => {
      paymentStatus.value = 'processing'
      errorMessage.value = ''
      processPayment()
    }

    const goBack = () => {
      router.push('/user/packages')
    }

    const goToPackages = () => {
      router.push('/user/packages')
    }

    onMounted(() => {
      processPayment()
    })

    return {
      paymentStatus,
      paymentAmount,
      packageName,
      transactionId,
      currencySymbol,
      errorMessage,
      formatAmount,
      retryPayment,
      goBack,
      goToPackages
    }
  }
}
</script>

<style scoped>
.progress {
  background-color: #e2e8f0;
}
</style>
