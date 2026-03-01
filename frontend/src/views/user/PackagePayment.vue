<template>
  <DashboardLayout page-title="Payment Gateway" :dark-theme="true">
    <div class="row justify-content-center">
      <div class="col-lg-6 col-md-8">
        <div class="card custom--card border-0 shadow-sm" style="border-radius: 15px;">
          <div class="card-body p-5 text-center">
          


            <!-- Gateway Selection -->
            <div v-if="paymentStatus === 'selecting_gateway'">
              <div class="mb-4 text-center">
                <h4 class="mb-3" style="color: #2d3748; font-weight: 600;">Select Payment Gateway</h4>
                <p class="text-muted mb-4">Choose a gateway to complete your payment of {{ currencySymbol }}{{ formatAmount(paymentAmount) }}</p>
                
                <div class="tw-grid tw-grid-cols-1 tw-gap-4 tw-mt-8">
                  <!-- SimplyPay Option (Recommended) -->
                  <div 
                    @click="selectedGateway = 'simplypay'"
                    class="tw-p-5 tw-bg-[#f8fafc] tw-border-2 tw-rounded-2xl tw-cursor-pointer tw-transition-all tw-duration-300 tw-flex tw-items-center tw-justify-between tw-relative tw-overflow-hidden tw-group"
                    :class="selectedGateway === 'simplypay' ? 'tw-border-emerald-500 tw-bg-emerald-50' : 'tw-border-slate-100 hover:tw-border-slate-200'"
                  >
                    <!-- Badge for Recommended -->
                    <div class="tw-absolute tw-top-0 tw-right-0 tw-bg-emerald-500 tw-text-[8px] tw-text-white tw-font-black tw-px-3 tw-py-1 tw-rounded-bl-xl tw-uppercase tw-tracking-widest">Recommended</div>

                    <div class="tw-flex tw-items-center">
                      <div class="tw-w-14 tw-h-14 tw-bg-white tw-rounded-2xl tw-flex tw-items-center tw-justify-center tw-mr-5 tw-shadow-md tw-transition-transform group-hover:tw-scale-110">
                        <i class="fas fa-bolt tw-text-emerald-500 tw-text-xl"></i>
                      </div>
                      <div class="tw-text-left">
                        <div class="tw-font-black tw-text-slate-800 tw-text-lg">SimplyPay</div>
                        <div class="tw-text-xs tw-text-emerald-600 tw-font-bold">Instant & Most Reliable</div>
                      </div>
                    </div>
                    <div v-if="selectedGateway === 'simplypay'" class="tw-text-emerald-500">
                      <i class="fas fa-check-circle tw-text-xl"></i>
                    </div>
                    <div v-else class="tw-text-slate-300">
                      <i class="fas fa-chevron-right"></i>
                    </div>
                  </div>

                  <!-- WatchPay Option -->
                  <div 
                    @click="selectedGateway = 'watchpay'"
                    class="tw-p-5 tw-bg-[#f8fafc] tw-border-2 tw-rounded-2xl tw-cursor-pointer tw-transition-all tw-duration-300 tw-flex tw-items-center tw-justify-between tw-group"
                    :class="selectedGateway === 'watchpay' ? 'tw-border-indigo-600 tw-bg-indigo-50' : 'tw-border-slate-100 hover:tw-border-slate-200'"
                  >
                    <div class="tw-flex tw-items-center">
                      <div class="tw-w-14 tw-h-14 tw-bg-white tw-rounded-2xl tw-flex tw-items-center tw-justify-center tw-mr-5 tw-shadow-md tw-transition-transform group-hover:tw-scale-110">
                        <i class="fas fa-eye tw-text-indigo-600 tw-text-xl"></i>
                      </div>
                      <div class="tw-text-left">
                        <div class="tw-font-black tw-text-slate-800 tw-text-lg">WatchPay</div>
                        <div class="tw-text-xs tw-text-slate-500 tw-font-bold">Secure UPI Payment</div>
                      </div>
                    </div>
                    <div v-if="selectedGateway === 'watchpay'" class="tw-text-indigo-600">
                      <i class="fas fa-check-circle tw-text-xl"></i>
                    </div>
                    <div v-else class="tw-text-slate-300">
                      <i class="fas fa-chevron-right"></i>
                    </div>
                  </div>
                </div>

                <div class="tw-mt-8">
                  <button 
                    class="btn btn--base btn-lg tw-w-full" 
                    @click="initiatePayment" 
                    :disabled="!selectedGateway || processingPayment"
                    style="border-radius: 10px; font-weight: 600;"
                  >
                    <i class="fas" :class="processingPayment ? 'fa-spinner fa-spin' : 'fa-arrow-right'"></i>
                    {{ processingPayment ? 'Redirecting...' : 'Continue to Payment' }}
                  </button>
                </div>
              </div>
            </div>

            <!-- Payment Processing -->
            <div v-if="paymentStatus === 'processing'">
              <div class="mb-4">
                <h4 class="mb-3" style="color: #2d3748; font-weight: 600;">Processing Payment...</h4>
                <p class="text-muted mb-4">Please wait while we process your payment</p>
                <div class="tw-flex tw-justify-center tw-mb-4">
                  <div class="tw-w-16 tw-h-16 tw-border-4 tw-border-indigo-600 tw-border-t-transparent tw-rounded-full tw-animate-spin"></div>
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

            <!-- Gateway Info -->
            <div v-if="paymentStatus === 'processing'" class="mt-4">
              <div class="alert alert-info" role="alert" style="border-radius: 10px; background: #e0f2fe; border-color: #0ea5e9; color: #0c4a6e;">
                <small>
                  <i class="fas fa-info-circle me-2"></i>
                  <strong>WatchPay:</strong> You will be redirected to complete payment. After payment, we’ll confirm automatically.
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
    const selectedGateway = ref('')
    const processingPayment = ref(false)

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
        transactionId.value = route.query.watchpay_trx || route.query.simplypay_trx || route.query.rupeerush_trx || route.query.trx || ''

        if (!packageId.value) {
          paymentStatus.value = 'failed'
          errorMessage.value = 'Invalid package ID'
          return
        }

        // If we have a trx (returned from Gateway), confirm payment
        if (transactionId.value) {
          paymentStatus.value = 'processing'
          const gateway = route.query.simplypay_trx ? 'simplypay' : (route.query.rupeerush_trx ? 'rupeerush' : 'watchpay')
          const maxTries = 12
          for (let i = 0; i < maxTries; i++) {
            try {
              const paymentResponse = await api.post('/packages/payment/dummy', {
                trx: transactionId.value,
                package_id: parseInt(packageId.value),
                gateway: gateway
              })
              if (paymentResponse.data.status === 'success') {
                paymentStatus.value = 'success'
                if (window.notify) window.notify('success', 'Payment completed successfully!')
                return
              }
            } catch (e) {
              // ignore and retry
            }
            await new Promise(r => setTimeout(r, 1500))
          }
          paymentStatus.value = 'failed'
          errorMessage.value = 'Payment not verified yet. Please wait and try again.'
          return
        }

        // NO TRX? Then show selection UI
        paymentStatus.value = 'selecting_gateway'

      } catch (error) {
        console.error('Payment initiation error:', error)
        paymentStatus.value = 'failed'
        errorMessage.value = error.response?.data?.message?.error?.[0] || error.response?.data?.message || 'Failed to initiate payment'
        if (window.notify) {
          window.notify('error', errorMessage.value)
        }
      }
    }

    const initiatePayment = async () => {
      if (!selectedGateway.value) return
      processingPayment.value = true
      try {
        const response = await api.post('/packages/purchase', {
          package_id: packageId.value,
          payment_method: 'gateway',
          gateway: selectedGateway.value
        })
        if (response.data.status === 'success' && response.data.data?.payment_url) {
          window.location.href = response.data.data.payment_url
          return
        }
        throw new Error(response.data.message?.error?.[0] || 'Failed to initiate payment')
      } catch (error) {
        console.error('Payment initiation error:', error)
        errorMessage.value = error.response?.data?.message?.error?.[0] || error.message || 'Failed to initiate payment'
        paymentStatus.value = 'failed'
        if (window.notify) window.notify('error', errorMessage.value)
      } finally {
        processingPayment.value = false
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
      selectedGateway,
      processingPayment,
      formatAmount,
      retryPayment,
      goBack,
      goToPackages,
      initiatePayment
    }
  }
}
</script>

<style scoped>
.progress {
  background-color: #e2e8f0;
}
@media (max-width: 640px) {
  .card-body.p-5 { padding: 1.5rem !important; }
  h4.mb-3 { font-size: 1.25rem !important; }
  p.text-muted { font-size: 0.85rem !important; }
  
  /* Gateway Cards */
  .tw-p-4 { padding: 0.85rem !important; border-radius: 1rem !important; }
  .tw-w-12.tw-h-12 { width: 2.5rem !important; height: 2.5rem !important; margin-right: 0.75rem !important; border-radius: 0.65rem !important; }
  .tw-w-12.tw-h-12 i { font-size: 1.15rem !important; }
  .tw-font-bold.tw-text-slate-900 { font-size: 0.95rem !important; }
  .tw-text-xs.tw-text-slate-500 { font-size: 10px !important; }
  
  /* Buttons */
  .btn-lg { padding: 0.75rem 1rem !important; font-size: 0.95rem !important; border-radius: 0.85rem !important; }
  .px-5 { padding-left: 1.5rem !important; padding-right: 1.5rem !important; }
  
  /* Alert box */
  .alert { padding: 0.85rem !important; border-radius: 0.85rem !important; }
  .alert p { font-size: 0.8rem !important; }
  
  /* Success circle */
  .fa-5x { font-size: 3.5rem !important; }
}
</style>
