<template>
  <DashboardLayout page-title="Account & KYC">
    <div class="row">
      <div class="col-lg-12">
        <!-- Step Progress Indicator -->
        <div class="card custom--card mb-4 border-0 shadow-sm" style="border-radius: 15px;">
          <div class="card-body p-4">
            <div class="row align-items-center">
              <div class="col-md-4 text-center">
                <div class="step-indicator" :class="{ 'active': currentStep >= 1, 'completed': currentStep > 1 }">
                  <div class="step-number">{{ currentStep > 1 ? '✓' : '1' }}</div>
                  <div class="step-label mt-2">Bank Details</div>
                </div>
              </div>
              <div class="col-md-4 text-center">
                <div class="step-indicator" :class="{ 'active': currentStep >= 2, 'completed': currentStep > 2 }">
                  <div class="step-number">{{ currentStep > 2 ? '✓' : '2' }}</div>
                  <div class="step-label mt-2">KYC Documents</div>
                </div>
              </div>
              <div class="col-md-4 text-center">
                <div class="step-indicator" :class="{ 'active': currentStep >= 3, 'completed': kycStatus === 1 }">
                  <div class="step-number">{{ kycStatus === 1 ? '✓' : '3' }}</div>
                  <div class="step-label mt-2">Verification</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Step 1: Bank Details -->
        <div v-if="currentStep === 1" class="card custom--card mb-4 border-0 shadow-sm" style="border-radius: 15px;">
          <div class="card-header bg-transparent border-0 pb-0" style="border-radius: 15px 15px 0 0;">
            <h5 class="mb-0" style="font-weight: 700; color: #2d3748;">
              <i class="fas fa-university me-2 text-primary"></i>Step 1: Bank Details
            </h5>
          </div>
          <div class="card-body pt-3">
            <form @submit.prevent="submitBankDetails">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label" style="font-weight: 600; color: #4a5568;">Account Holder Name <span class="text-danger">*</span></label>
                  <input type="text" v-model="bankDetails.account_holder_name" class="form-control" style="border-radius: 10px; padding: 12px;" required>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label" style="font-weight: 600; color: #4a5568;">Bank Name <span class="text-danger">*</span></label>
                  <input type="text" v-model="bankDetails.bank_name" class="form-control" style="border-radius: 10px; padding: 12px;" required>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label" style="font-weight: 600; color: #4a5568;">Account Number <span class="text-danger">*</span></label>
                  <input type="text" v-model="bankDetails.account_number" class="form-control" style="border-radius: 10px; padding: 12px;" required>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label" style="font-weight: 600; color: #4a5568;">IFSC Code <span class="text-danger">*</span></label>
                  <input type="text" v-model="bankDetails.ifsc_code" class="form-control" style="border-radius: 10px; padding: 12px; text-transform: uppercase;" maxlength="11" required>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label" style="font-weight: 600; color: #4a5568;">Bank Registered No. <span class="text-danger">*</span></label>
                  <input type="text" v-model="bankDetails.bank_registered_no" class="form-control" style="border-radius: 10px; padding: 12px;" required>
                </div>
              </div>
              <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-lg px-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 12px; font-weight: 600;">
                  <i class="fas fa-arrow-right me-2"></i>Next Step
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Step 2: KYC Documents -->
        <div v-if="currentStep === 2" class="card custom--card mb-4 border-0 shadow-sm" style="border-radius: 15px;">
          <div class="card-header bg-transparent border-0 pb-0" style="border-radius: 15px 15px 0 0;">
            <h5 class="mb-0" style="font-weight: 700; color: #2d3748;">
              <i class="fas fa-id-card me-2 text-primary"></i>Step 2: KYC Documents
            </h5>
          </div>
          <div class="card-body pt-3">
            <form @submit.prevent="submitKYC">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label" style="font-weight: 600; color: #4a5568;">Aadhaar Number <span class="text-danger">*</span></label>
                  <input type="text" v-model="kycData.aadhaar_number" class="form-control" style="border-radius: 10px; padding: 12px;" maxlength="12" pattern="[0-9]{12}" required>
                  <small class="text-muted">Enter 12-digit Aadhaar number</small>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label" style="font-weight: 600; color: #4a5568;">Aadhaar Document <span class="text-danger">*</span></label>
                  <input type="file" @change="handleFileChange('aadhaar', $event)" class="form-control" style="border-radius: 10px; padding: 12px;" accept="image/*,.pdf" required>
                  <small class="text-muted">Upload Aadhaar card (Image or PDF)</small>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label" style="font-weight: 600; color: #4a5568;">PAN Card Number <span class="text-danger">*</span></label>
                  <input type="text" v-model="kycData.pan_number" class="form-control" style="border-radius: 10px; padding: 12px; text-transform: uppercase;" maxlength="10" pattern="[A-Z]{5}[0-9]{4}[A-Z]{1}" required>
                  <small class="text-muted">Enter 10-character PAN number</small>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label" style="font-weight: 600; color: #4a5568;">PAN Card Document <span class="text-danger">*</span></label>
                  <input type="file" @change="handleFileChange('pan', $event)" class="form-control" style="border-radius: 10px; padding: 12px;" accept="image/*,.pdf" required>
                  <small class="text-muted">Upload PAN card (Image or PDF)</small>
                </div>
              </div>
              <div class="d-flex justify-content-between mt-4">
                <button type="button" @click="currentStep = 1" class="btn btn-lg px-5" style="background: #e2e8f0; color: #4a5568; border: none; border-radius: 12px; font-weight: 600;">
                  <i class="fas fa-arrow-left me-2"></i>Previous
                </button>
                <button type="submit" class="btn btn-lg px-5" :disabled="!canSubmitKYC || processing" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 12px; font-weight: 600;">
                  <i class="fas fa-paper-plane me-2"></i>{{ processing ? 'Processing...' : 'Submit KYC' }}
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Step 3: Verification Status -->
        <div v-if="currentStep === 3" class="card custom--card mb-4 border-0 shadow-sm" style="border-radius: 15px;">
          <div class="card-body text-center p-5">
            <div v-if="kycStatus === 2" class="mb-4">
              <div class="d-inline-block p-4 rounded-circle mb-3" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <i class="fas fa-clock fa-4x text-white"></i>
              </div>
              <h3 class="mb-3" style="color: #2d3748; font-weight: 700;">KYC Under Review</h3>
              <p class="text-muted mb-4">Your KYC application has been submitted successfully and is pending admin review.</p>
              <div class="alert alert-info" style="border-radius: 12px;">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Payment Status:</strong> ₹{{ formatAmount(kycFee) }} has been deducted from your balance.
              </div>
            </div>
            <div v-else-if="kycStatus === 1" class="mb-4">
              <div class="d-inline-block p-4 rounded-circle mb-3" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <i class="fas fa-check-circle fa-4x text-white"></i>
              </div>
              <h3 class="mb-3" style="color: #2d3748; font-weight: 700;">KYC Verified!</h3>
              <p class="text-muted mb-4">Congratulations! Your KYC has been verified by admin.</p>
            </div>
            <div v-else-if="kycStatus === 3" class="mb-4">
              <div class="d-inline-block p-4 rounded-circle mb-3" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);">
                <i class="fas fa-times-circle fa-4x text-white"></i>
              </div>
              <h3 class="mb-3" style="color: #2d3748; font-weight: 700;">KYC Rejected</h3>
              <p class="text-muted mb-4" v-if="kycRejectionReason">{{ kycRejectionReason }}</p>
              <button @click="currentStep = 1" class="btn btn-lg px-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 12px; font-weight: 600;">
                <i class="fas fa-redo me-2"></i>Re-submit KYC
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Payment Modal -->
    <div v-if="showPaymentModal" class="modal fade show d-block" style="background: rgba(0,0,0,0.5);" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 15px; border: none;">
          <div class="modal-header border-0 pb-0" style="border-radius: 15px 15px 0 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <h5 class="modal-title text-white" style="font-weight: 700;">
              <i class="fas fa-credit-card me-2"></i>KYC Payment Required
            </h5>
            <button type="button" class="btn-close btn-close-white" @click="showPaymentModal = false"></button>
          </div>
          <div class="modal-body p-4">
            <div class="text-center mb-4">
              <div class="d-inline-block p-3 rounded-circle mb-3" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <i class="fas fa-rupee-sign fa-3x text-white"></i>
              </div>
              <h4 class="mb-2" style="font-weight: 700; color: #2d3748;">Pay KYC Fee</h4>
              <p class="text-muted mb-3">Complete your KYC verification by paying the required fee</p>
              <div class="alert alert-info" style="border-radius: 12px;">
                <h3 class="mb-0" style="font-weight: 700; color: #667eea;">
                  {{ currencySymbol }}{{ formatAmount(kycFee) }}
                </h3>
                <small class="text-muted">This amount will be deducted from your balance</small>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label" style="font-weight: 600;">Current Balance</label>
              <div class="input-group">
                <span class="input-group-text" style="background: #f7fafc; border-radius: 10px 0 0 10px;">{{ currencySymbol }}</span>
                <input type="text" :value="formatAmount(userBalance)" class="form-control" style="border-radius: 0 10px 10px 0;" readonly>
              </div>
            </div>
            <div v-if="userBalance < kycFee" class="alert alert-warning mb-3" style="border-radius: 12px;">
              <i class="fas fa-exclamation-triangle me-2"></i>
              <strong>Insufficient Balance!</strong> Please add funds to your account.
            </div>
          </div>
          <div class="modal-footer border-0 pt-0">
            <button type="button" class="btn btn-secondary" @click="showPaymentModal = false" style="border-radius: 10px; font-weight: 600;">Cancel</button>
            <button type="button" class="btn btn-lg px-5" @click="processPayment" :disabled="userBalance < kycFee || processingPayment" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 10px; font-weight: 600;">
              <i class="fas fa-check me-2"></i>{{ processingPayment ? 'Processing...' : 'Pay & Continue' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import DashboardLayout from '../../components/DashboardLayout.vue'
import api from '../../services/api'

export default {
  name: 'AccountKYC',
  components: {
    DashboardLayout
  },
  setup() {
    const currentStep = ref(1)
    const bankDetails = ref({
      account_holder_name: '',
      bank_name: '',
      account_number: '',
      ifsc_code: '',
      bank_registered_no: ''
    })
    const kycData = ref({
      aadhaar_number: '',
      pan_number: ''
    })
    const kycFiles = ref({})
    const kycStatus = ref(0)
    const kycRejectionReason = ref('')
    const kycFee = ref(990) // Fixed ₹990 fee
    const currencySymbol = ref('₹')
    const userBalance = ref(0)
    const showPaymentModal = ref(false)
    const processing = ref(false)
    const processingPayment = ref(false)

    const canSubmitKYC = computed(() => {
      return kycData.value.aadhaar_number && 
             kycData.value.pan_number && 
             kycFiles.value.aadhaar && 
             kycFiles.value.pan
    })

    const formatAmount = (amount) => {
      if (!amount && amount !== 0) return '0.00'
      return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    }

    const handleFileChange = (type, event) => {
      kycFiles.value[type] = event.target.files[0]
    }

    const submitBankDetails = async () => {
      // Validate bank details
      if (!bankDetails.value.account_holder_name || 
          !bankDetails.value.bank_name || 
          !bankDetails.value.account_number || 
          !bankDetails.value.ifsc_code || 
          !bankDetails.value.bank_registered_no) {
        if (window.notify) {
          window.notify('error', 'Please fill all required bank details')
        }
        return
      }

      // Save bank details first
      try {
        const response = await api.post('/bank-details', bankDetails.value)
        if (response.data.status === 'success') {
          // Show payment modal
          showPaymentModal.value = true
        }
      } catch (error) {
        console.error('Error saving bank details:', error)
        if (window.notify) {
          window.notify('error', error.response?.data?.message || 'Failed to save bank details')
        }
      }
    }

    const processPayment = async () => {
      if (userBalance.value < kycFee.value) {
        if (window.notify) {
          window.notify('error', 'Insufficient balance. Please add funds to your account.')
        }
        return
      }

      processingPayment.value = true
      try {
        // Deduct KYC fee from balance
        const response = await api.post('/kyc-payment', {
          amount: kycFee.value
        })

        if (response.data.status === 'success') {
          showPaymentModal.value = false
          currentStep.value = 2 // Move to next step
          userBalance.value = response.data.data.balance || userBalance.value
          if (window.notify) {
            window.notify('success', `₹${formatAmount(kycFee.value)} deducted successfully. Please proceed with KYC documents.`)
          }
        }
      } catch (error) {
        console.error('Error processing payment:', error)
        if (window.notify) {
          window.notify('error', error.response?.data?.message || 'Payment failed. Please try again.')
        }
      } finally {
        processingPayment.value = false
      }
    }

    const submitKYC = async () => {
      if (!canSubmitKYC.value) {
        if (window.notify) {
          window.notify('error', 'Please fill all required fields and upload documents')
        }
        return
      }

      processing.value = true
      try {
        const formData = new FormData()
        formData.append('aadhaar_number', kycData.value.aadhaar_number)
        formData.append('pan_number', kycData.value.pan_number)
        formData.append('aadhaar_document', kycFiles.value.aadhaar)
        formData.append('pan_document', kycFiles.value.pan)

        const response = await api.post('/kyc-submit', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })

        if (response.data.status === 'success') {
          currentStep.value = 3
          kycStatus.value = 2 // Pending review
          if (window.notify) {
            window.notify('success', 'KYC application submitted successfully! Waiting for admin approval.')
          }
          fetchAccountData()
        }
      } catch (error) {
        console.error('Error submitting KYC:', error)
        const errorMsg = error.response?.data?.message?.[0] || error.response?.data?.message || 'Failed to submit KYC'
        if (window.notify) {
          window.notify('error', errorMsg)
        }
      } finally {
        processing.value = false
      }
    }

    const fetchAccountData = async () => {
      try {
        const response = await api.get('/account-kyc')
        if (response.data.status === 'success' && response.data.data) {
          const data = response.data.data
          bankDetails.value = data.bank_details || bankDetails.value
          kycStatus.value = data.kyc_status || 0
          kycRejectionReason.value = data.kyc_rejection_reason || ''
          userBalance.value = data.balance || 0
          currencySymbol.value = response.data.currency_symbol || '₹'
          
          // Set current step based on status
          if (kycStatus.value === 0) {
            currentStep.value = 1
          } else if (kycStatus.value === 2 || kycStatus.value === 1 || kycStatus.value === 3) {
            currentStep.value = 3
          } else {
            currentStep.value = 2
          }
        }
      } catch (error) {
        console.error('Error loading account data:', error)
      }
    }

    onMounted(() => {
      fetchAccountData()
    })

    return {
      currentStep,
      bankDetails,
      kycData,
      kycStatus,
      kycRejectionReason,
      kycFee,
      currencySymbol,
      userBalance,
      showPaymentModal,
      processing,
      processingPayment,
      canSubmitKYC,
      formatAmount,
      handleFileChange,
      submitBankDetails,
      processPayment,
      submitKYC
    }
  }
}
</script>

<style scoped>
.step-indicator {
  position: relative;
  display: inline-block;
}

.step-indicator .step-number {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: #e2e8f0;
  color: #718096;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  font-weight: 700;
  margin: 0 auto;
  transition: all 0.3s ease;
}

.step-indicator.active .step-number {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  transform: scale(1.1);
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.step-indicator.completed .step-number {
  background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
  color: white;
}

.step-label {
  font-weight: 600;
  color: #4a5568;
  font-size: 14px;
}

.step-indicator.active .step-label {
  color: #667eea;
  font-weight: 700;
}

.modal.show {
  display: block !important;
}
</style>
