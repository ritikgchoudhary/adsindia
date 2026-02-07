<template>
  <DashboardLayout page-title="Account & KYC">
    <div class="row">
      <div class="col-lg-8">
        <div class="card custom--card mb-4">
          <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-user-shield me-2"></i>Personal Details</h5>
          </div>
          <div class="card-body">
            <form @submit.prevent="updatePersonalDetails">
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form--label">First Name</label>
                    <input type="text" v-model="personalDetails.firstname" class="form--control" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form--label">Last Name</label>
                    <input type="text" v-model="personalDetails.lastname" class="form--control" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form--label">Email</label>
                    <input type="email" v-model="personalDetails.email" class="form--control" disabled>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form--label">Mobile</label>
                    <input type="text" v-model="personalDetails.mobile" class="form--control" disabled>
                  </div>
                </div>
                <div class="col-md-12">
                  <div class="form-group">
                    <label class="form--label">Address</label>
                    <input type="text" v-model="personalDetails.address" class="form--control">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="form--label">City</label>
                    <input type="text" v-model="personalDetails.city" class="form--control">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="form--label">State</label>
                    <input type="text" v-model="personalDetails.state" class="form--control">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="form--label">Zip Code</label>
                    <input type="text" v-model="personalDetails.zip" class="form--control">
                  </div>
                </div>
              </div>
              <button type="submit" class="btn btn--base">Update Personal Details</button>
            </form>
          </div>
        </div>

        <div class="card custom--card mb-4">
          <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-university me-2"></i>Bank Details</h5>
          </div>
          <div class="card-body">
            <form @submit.prevent="updateBankDetails">
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form--label">Account Holder Name</label>
                    <input type="text" v-model="bankDetails.account_holder_name" class="form--control" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form--label">Account Number</label>
                    <input type="text" v-model="bankDetails.account_number" class="form--control" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form--label">IFSC Code</label>
                    <input type="text" v-model="bankDetails.ifsc_code" class="form--control" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form--label">Bank Name</label>
                    <input type="text" v-model="bankDetails.bank_name" class="form--control" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form--label">Bank Registered No.</label>
                    <input type="text" v-model="bankDetails.bank_registered_no" class="form--control">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form--label">Branch Name</label>
                    <input type="text" v-model="bankDetails.branch_name" class="form--control">
                  </div>
                </div>
              </div>
              <button type="submit" class="btn btn--base">Update Bank Details</button>
            </form>
          </div>
        </div>

        <div class="card custom--card mb-4">
          <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-id-card me-2"></i>KYC Documents</h5>
          </div>
          <div class="card-body">
            <form @submit.prevent="submitKYC" enctype="multipart/form-data">
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form--label">Aadhaar Number</label>
                    <input type="text" v-model="kycData.aadhaar_number" class="form--control" maxlength="12" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form--label">Aadhaar Document</label>
                    <input type="file" @change="handleFileChange('aadhaar', $event)" class="form--control" accept="image/*,.pdf" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form--label">PAN Number</label>
                    <input type="text" v-model="kycData.pan_number" class="form--control" maxlength="10" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form--label">PAN Document</label>
                    <input type="file" @change="handleFileChange('pan', $event)" class="form--control" accept="image/*,.pdf" required>
                  </div>
                </div>
              </div>
              
              <div class="alert alert-info mb-3">
                <i class="fas fa-info-circle me-2"></i>
                <strong>KYC Fee:</strong> {{ currencySymbol }}{{ formatAmount(kycFee) }} will be deducted from your balance.
              </div>

              <button type="submit" class="btn btn--base" :disabled="!canSubmitKYC">
                <i class="fas fa-paper-plane me-2"></i>Submit KYC Application
              </button>
            </form>
          </div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="card custom--card">
          <div class="card-header">
            <h5 class="mb-0">KYC Status</h5>
          </div>
          <div class="card-body">
            <div v-if="kycStatus === 0" class="alert alert-warning">
              <i class="fas fa-exclamation-triangle me-2"></i>
              <strong>Status:</strong> Not Verified
              <p class="mb-0 mt-2">Please submit your KYC documents to verify your account.</p>
            </div>
            <div v-else-if="kycStatus === 1" class="alert alert-success">
              <i class="fas fa-check-circle me-2"></i>
              <strong>Status:</strong> Verified
            </div>
            <div v-else-if="kycStatus === 2" class="alert alert-info">
              <i class="fas fa-clock me-2"></i>
              <strong>Status:</strong> Pending Review
            </div>
            <div v-else-if="kycStatus === 3" class="alert alert-danger">
              <i class="fas fa-times-circle me-2"></i>
              <strong>Status:</strong> Rejected
              <p class="mb-0 mt-2" v-if="kycRejectionReason">{{ kycRejectionReason }}</p>
            </div>
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
    const personalDetails = ref({
      firstname: '',
      lastname: '',
      email: '',
      mobile: '',
      address: '',
      city: '',
      state: '',
      zip: ''
    })
    const bankDetails = ref({
      account_holder_name: '',
      account_number: '',
      ifsc_code: '',
      bank_name: '',
      bank_registered_no: '',
      branch_name: ''
    })
    const kycData = ref({
      aadhaar_number: '',
      pan_number: ''
    })
    const kycFiles = ref({})
    const kycStatus = ref(0)
    const kycRejectionReason = ref('')
    const kycFee = ref(0)
    const currencySymbol = ref('₹')

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

    const updatePersonalDetails = async () => {
      try {
        const response = await api.post('/profile-setting', personalDetails.value)
        if (response.data.status === 'success') {
          if (window.notify) {
            window.notify('success', 'Personal details updated successfully!')
          }
        }
      } catch (error) {
        console.error('Error updating personal details:', error)
        if (window.notify) {
          window.notify('error', error.response?.data?.message || 'Failed to update personal details')
        }
      }
    }

    const updateBankDetails = async () => {
      try {
        const response = await api.post('/bank-details', bankDetails.value)
        if (response.data.status === 'success') {
          if (window.notify) {
            window.notify('success', 'Bank details updated successfully!')
          }
        }
      } catch (error) {
        console.error('Error updating bank details:', error)
        if (window.notify) {
          window.notify('error', error.response?.data?.message || 'Failed to update bank details')
        }
      }
    }

    const submitKYC = async () => {
      if (!canSubmitKYC.value) return

      // Show 990 fee confirmation popup
      if (kycFee.value > 0) {
        const confirmed = confirm(
          `KYC Application Fee: ${currencySymbol.value}${formatAmount(kycFee.value)}\n\n` +
          `This amount will be deducted from your balance.\n\n` +
          `Do you want to proceed?`
        )
        if (!confirmed) return
      }

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
          if (window.iziToast) {
            window.iziToast.success({
              title: 'Success',
              message: 'KYC application submitted successfully!',
              position: 'topRight'
            })
          }
          fetchAccountData()
        }
      } catch (error) {
        console.error('Error submitting KYC:', error)
        const errorMsg = error.response?.data?.message?.[0] || error.response?.data?.message || 'Failed to submit KYC'
        if (window.iziToast) {
          window.iziToast.error({
            title: 'Error',
            message: errorMsg,
            position: 'topRight'
          })
        }
      }
    }

    const fetchAccountData = async () => {
      try {
        const response = await api.get('/account-kyc')
        if (response.data.status === 'success' && response.data.data) {
          const data = response.data.data
          personalDetails.value = data.personal_details || personalDetails.value
          bankDetails.value = data.bank_details || bankDetails.value
          kycStatus.value = data.kyc_status || 0
          kycRejectionReason.value = data.kyc_rejection_reason || ''
          kycFee.value = data.kyc_fee || 0
          currencySymbol.value = response.data.currency_symbol || '₹'
        }
      } catch (error) {
        console.error('Error loading account data:', error)
      }
    }

    onMounted(() => {
      fetchAccountData()
    })

    return {
      personalDetails,
      bankDetails,
      kycData,
      kycStatus,
      kycRejectionReason,
      kycFee,
      currencySymbol,
      canSubmitKYC,
      formatAmount,
      handleFileChange,
      updatePersonalDetails,
      updateBankDetails,
      submitKYC
    }
  }
}
</script>
