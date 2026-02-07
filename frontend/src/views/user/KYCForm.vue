<template>
  <DashboardLayout page-title="KYC Form">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <div v-if="kycFee > 0" class="alert alert--info mb-4">
            <div class="alert__icon">
              <i class="fas fa-info-circle"></i>
            </div>
            <div class="alert__content">
              <h4 class="alert__title">KYC Fee</h4>
              <p class="alert__desc">
                KYC application fee: {{ currencySymbol }}{{ formatAmount(kycFee) }}
              </p>
            </div>
          </div>
          
          <div class="card custom--card">
            <div class="card-body">
              <form @submit.prevent="handleSubmit" enctype="multipart/form-data">
                <div v-for="(field, index) in kycFields" :key="index" :class="`col-md-${field.width || 12}`" class="form-group">
                  <label class="form--label">
                    {{ field.name }}
                    <span v-if="field.required" class="text--danger">*</span>
                    <span v-if="field.instruction" data-bs-toggle="tooltip" :title="field.instruction">
                      <i class="fas fa-info-circle"></i>
                    </span>
                  </label>
                  
                  <!-- Text, Email, URL, Number -->
                  <input v-if="['text', 'email', 'url', 'number'].includes(field.type)" 
                         :type="field.type" 
                         v-model="formData[field.label]" 
                         class="form--control" 
                         :required="field.required"
                         :step="field.type === 'number' ? 'any' : undefined">
                  
                  <!-- Date, Time, DateTime -->
                  <input v-else-if="['date', 'time', 'datetime'].includes(field.type)" 
                         :type="field.type === 'datetime' ? 'datetime-local' : field.type" 
                         v-model="formData[field.label]" 
                         class="form--control" 
                         :required="field.required">
                  
                  <!-- Textarea -->
                  <textarea v-else-if="field.type === 'textarea'" 
                            v-model="formData[field.label]" 
                            class="form--control" 
                            :required="field.required"
                            rows="4"></textarea>
                  
                  <!-- Select -->
                  <select v-else-if="field.type === 'select'" 
                          v-model="formData[field.label]" 
                          class="form--control select2" 
                          :required="field.required"
                          :id="`select-${index}`">
                    <option value="">Select {{ field.name }}</option>
                    <option v-for="option in field.options" :key="option" :value="option">{{ option }}</option>
                  </select>
                  
                  <!-- Radio -->
                  <div v-else-if="field.type === 'radio'" class="d-flex gap-3 flex-wrap">
                    <div v-for="option in field.options" :key="option" class="form-check">
                      <input class="form-check-input" 
                             type="radio" 
                             :name="field.label" 
                             :id="`${field.label}_${option}`"
                             :value="option"
                             v-model="formData[field.label]"
                             :required="field.required">
                      <label class="form-check-label" :for="`${field.label}_${option}`">{{ option }}</label>
                    </div>
                  </div>
                  
                  <!-- Checkbox -->
                  <div v-else-if="field.type === 'checkbox'" class="d-flex gap-3 flex-wrap">
                    <div v-for="option in field.options" :key="option" class="form-check">
                      <input class="form-check-input" 
                             type="checkbox" 
                             :name="`${field.label}[]`" 
                             :id="`${field.label}_${option}`"
                             :value="option"
                             v-model="formData[field.label]"
                             :required="field.required">
                      <label class="form-check-label" :for="`${field.label}_${option}`">{{ option }}</label>
                    </div>
                  </div>
                  
                  <!-- File -->
                  <input v-else-if="field.type === 'file'" 
                         type="file" 
                         @change="handleFileChange(field.label, $event)" 
                         class="form--control" 
                         :accept="field.accept" 
                         :required="field.required">
                </div>
                
                <div class="form-group">
                  <button type="submit" class="btn btn--base w-100" :disabled="submitting">
                    <span v-if="submitting">Submitting...</span>
                    <span v-else>Submit</span>
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import DashboardLayout from '../../components/DashboardLayout.vue'
import api from '../../services/api'

export default {
  name: 'KYCForm',
  components: {
    DashboardLayout
  },
  setup() {
    const router = useRouter()
    const kycFields = ref([])
    const formData = ref({})
    const files = ref({})
    const submitting = ref(false)
    const kycFee = ref(0)
    const currencySymbol = ref('₹')

    const formatAmount = (amount) => {
      if (!amount && amount !== 0) return '0.00'
      const num = parseFloat(amount) || 0
      return num.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    }

    const handleFileChange = (fieldLabel, event) => {
      if (event.target.files && event.target.files[0]) {
        files.value[fieldLabel] = event.target.files[0]
      }
    }

    const handleSubmit = async () => {
      submitting.value = true
      try {
        const formDataToSend = new FormData()
        
        // Add form fields (use label as key, not name)
        Object.keys(formData.value).forEach(key => {
          const value = formData.value[key]
          if (value !== null && value !== undefined && value !== '') {
            if (Array.isArray(value)) {
              // For checkbox arrays
              value.forEach(v => {
                formDataToSend.append(key + '[]', v)
              })
            } else {
              formDataToSend.append(key, value)
            }
          }
        })
        
        // Add files
        Object.keys(files.value).forEach(key => {
          if (files.value[key]) {
            formDataToSend.append(key, files.value[key])
          }
        })

        const response = await api.post('/kyc-submit', formDataToSend, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })

        if (response.data.status === 'success') {
          if (window.iziToast) {
            window.iziToast.success({
              title: 'Success',
              message: response.data.message?.[0] || 'KYC submitted successfully!',
              position: 'topRight'
            })
          }
          router.push('/user/kyc-data')
        } else {
          if (window.iziToast) {
            window.iziToast.error({
              title: 'Error',
              message: response.data.message?.[0] || 'Failed to submit KYC',
              position: 'topRight'
            })
          }
        }
      } catch (error) {
        console.error('Error submitting KYC:', error)
        const errorMessage = error.response?.data?.message?.[0] || error.response?.data?.message || 'Failed to submit KYC'
        if (window.iziToast) {
          window.iziToast.error({
            title: 'Error',
            message: errorMessage,
            position: 'topRight'
          })
        }
      } finally {
        submitting.value = false
      }
    }

    const fetchKYCForm = async () => {
      try {
        const response = await api.get('/kyc-form')
        if (response.data.status === 'success') {
          kycFields.value = response.data.data?.fields || []
          kycFee.value = response.data.data?.kyc_fee || 0
          currencySymbol.value = response.data.data?.currency_symbol || '₹'
          
          // Initialize form data with empty values using label as key
          kycFields.value.forEach(field => {
            if (field.type === 'checkbox') {
              formData.value[field.label] = []
            } else {
              formData.value[field.label] = ''
            }
          })
        } else {
          // Handle error response
          if (response.data.status === 'error') {
            const errorMsg = response.data.message?.[0] || 'Failed to load KYC form'
            if (window.iziToast) {
              window.iziToast.error({
                title: 'Error',
                message: errorMsg,
                position: 'topRight'
              })
            }
            if (errorMsg.includes('already KYC verified') || errorMsg.includes('under review')) {
              router.push('/user/dashboard')
            }
          }
        }
      } catch (error) {
        console.error('Error loading KYC form:', error)
        if (window.iziToast) {
          window.iziToast.error({
            title: 'Error',
            message: 'Failed to load KYC form',
            position: 'topRight'
          })
        }
      }
    }

    onMounted(() => {
      fetchKYCForm()
      // Initialize select2 after fields are loaded
      if (window.jQuery) {
        setTimeout(() => {
          window.jQuery('.select2').select2()
          // Initialize tooltips
          if (window.bootstrap) {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            tooltipTriggerList.map(function (tooltipTriggerEl) {
              return new window.bootstrap.Tooltip(tooltipTriggerEl)
            })
          }
        }, 300)
      }
    })

    return {
      kycFields,
      formData,
      files,
      submitting,
      kycFee,
      currencySymbol,
      formatAmount,
      handleFileChange,
      handleSubmit
    }
  }
}
</script>

<style scoped>
.select2+.select2-container .select2-selection__rendered {
  line-height: unset;
}

.select2-container--default .select2-selection--single {
  border-width: 1px !important;
  border-radius: 12px !important;
  border-color: var(--select2-border) !important;
}

.select2-container--open .select2-selection.select2-selection--single,
.select2-container--open .select2-selection.select2-selection--multiple {
  border-radius: 12px !important;
}

.select2-container--default .select2-selection--single {
  padding: 16px 24px !important;
}

.select2+.select2-container .select2-selection__rendered {
  padding-right: 0px !important;
  padding-left: 0px !important;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
  top: 28px !important;
}

.select2-results__option.select2-results__option--selected,
.select2-results__option--selectable,
.select2-container--default .select2-results__option--disabled {
  border-bottom-color: hsl(var(--border-color)) !important;
}

.select2-results__option.select2-results__option--selected,
.select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
  color: hsl(var(--white)) !important;
  background-color: hsl(var(--base)) !important;
}

.select2-results__option.select2-results__option--selected,
.select2-results__option--selectable,
.select2-container--default .select2-results__option--disabled {
  border-bottom-color: hsl(var(--white)/0.2) !important;
}

.select2-container--default.select2-container--focus .select2-selection--single {
  outline: none !important;
  box-shadow: none !important;
  border-color: hsl(var(--base)) !important;
}
</style>
