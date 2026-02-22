<template>
  <section class="account register" v-if="registrationEnabled">
    <div class="account-inner">
      <div class="container">
        <div class="row gy-4 justify-content-center">
          <!-- Register Form - Full Width -->
          <div class="col-lg-10 col-xl-8">
            <div class="account-form">
              <div class="account-form__content text-center mb-4">
                <h2 class="account-form__title mb-2">{{ registerContent?.subheading || 'Join as an Affiliate' }}</h2>
              </div>

              <!-- Referrer/Affiliate Info is intentionally hidden on UI.
                   Sponsor ID is still auto-filled from URL. -->

              <!-- Step Progress Indicator (Desktop) -->
              <div class="registration-steps mb-4 d-none d-md-flex">
                <div class="step-indicator" :class="{ 'active': currentStep >= 1, 'completed': currentStep > 1 }">
                  <div class="step-number">{{ currentStep > 1 ? '✓' : '1' }}</div>
                  <div class="step-label">Personal Details</div>
                </div>
                <div class="step-indicator" :class="{ 'active': currentStep >= 2, 'completed': currentStep > 2 }">
                  <div class="step-number">{{ currentStep > 2 ? '✓' : '2' }}</div>
                  <div class="step-label">Payment</div>
                </div>
                <div class="step-indicator" :class="{ 'active': currentStep >= 3, 'completed': currentStep > 3 }">
                  <div class="step-number">{{ currentStep > 3 ? '✓' : '3' }}</div>
                  <div class="step-label">Complete</div>
            </div>
              </div>

              <!-- Step Progress Indicator (Mobile Linear) -->
              <div class="registration-steps-mobile mb-4 d-flex d-md-none">
                <div class="rstep" :class="{ active: currentStep >= 1, completed: currentStep > 1 }">
                  <div class="rstep-circle">{{ currentStep > 1 ? '✓' : '1' }}</div>
                  <div class="rstep-label">Details</div>
                </div>
                <div class="rstep-line" :class="{ completed: currentStep > 1 }"></div>
                <div class="rstep" :class="{ active: currentStep >= 2, completed: currentStep > 2 }">
                  <div class="rstep-circle">{{ currentStep > 2 ? '✓' : '2' }}</div>
                  <div class="rstep-label">Payment</div>
                </div>
                <div class="rstep-line" :class="{ completed: currentStep > 2 }"></div>
                <div class="rstep" :class="{ active: currentStep >= 3, completed: currentStep > 3 }">
                  <div class="rstep-circle">{{ currentStep > 3 ? '✓' : '3' }}</div>
                  <div class="rstep-label">Done</div>
                </div>
              </div>

              <!-- Step 1: Personal Details -->
              <form v-if="currentStep === 1" @submit.prevent="handleStep1" class="verify-gcaptcha disableSubmission">
                <div class="row">
                  <!-- Select Package -->
                  <div class="col-sm-12">
                    <div class="form-group">
                      <label class="form--label"><i class="fas fa-box me-2"></i>Select Package <span class="text-danger">*</span></label>
                      <!-- Locked package UI (avoid Select2 blank/disabled rendering issues) -->
                      <template v-if="isPkgLocked">
                        <input
                          type="text"
                          class="form--control register-readonly register-readonly--ok"
                          :value="lockedPackageLabel"
                          readonly
                        >
                        <!-- Keep the real value in a hidden input for form submission -->
                        <input type="hidden" name="pkg" :value="form.pkg || ''">
                      </template>
                      <template v-else>
                        <select v-model="form.pkg" name="pkg" class="form--control register-select2">
                          <option value="">-- Select Package --</option>
                          <option v-for="pkg in packages" :key="pkg.id" :value="pkg.id">
                            {{ pkg.name }} - {{ currencySymbol }}{{ formatAmount(pkg.price) }}
                          </option>
                        </select>
                      </template>
                      <small class="text-muted" v-if="!isPkgLocked">Choose a package to get started</small>
                      <small class="text-muted" v-else><i class="fas fa-lock me-1"></i>Package locked by referral link</small>
                    </div>
                  </div>

                  <!-- Sponsor ID -->
                  <div class="col-sm-6">
                    <div class="form-group">
                      <label class="form--label"><i class="fas fa-user-shield me-2"></i>Sponsor ID <span class="text-muted">(Optional)</span></label>
                      <input 
                        v-model="form.sponsor_id" 
                        type="text" 
                        name="sponsor_id" 
                        class="form--control" 
                        placeholder="Enter Sponsor ID (e.g. ADS15000)"
                        @blur="fetchSponsorInfo"
                        @input="handleSponsorInput"
                      >
                    </div>
                  </div>

                  <!-- Sponsor Name (Auto-filled) -->
                  <div class="col-sm-6">
                    <div class="form-group">
                      <label class="form--label"><i class="fas fa-user me-2"></i>Sponsor Name</label>
                      <input 
                        :value="sponsorInfo?.name || ''" 
                        type="text" 
                        class="form--control register-readonly" 
                        readonly
                        :class="{ 'register-readonly--ok': sponsorInfo }"
                        placeholder="Will be auto-filled from Sponsor ID"
                      >
                      <small v-if="sponsorInfo" class="text-success">
                        <i class="fas fa-check-circle me-1"></i>Sponsor verified
                      </small>
                      <small v-else-if="form.sponsor_id && !loadingSponsor" class="text-danger">
                        <i class="fas fa-exclamation-circle me-1"></i>Sponsor not found
                      </small>
                    </div>
                  </div>

                  <!-- Full Name -->
                  <div class="col-sm-12">
                    <div class="form-group">
                      <label class="form--label"><i class="fas fa-id-card me-2"></i>Full Name <span class="text-danger">*</span></label>
                      <input 
                        v-model="form.fullname" 
                        type="text" 
                        name="fullname" 
                        class="form--control" 
                        placeholder="Enter your full name"
                        required
                      >
                    </div>
                  </div>

                  <!-- Email -->
                  <div class="col-sm-12">
                    <div class="form-group">
                      <label class="form--label"><i class="fas fa-envelope me-2"></i>Email <span class="text-danger">*</span></label>
                      <input 
                        v-model="form.email" 
                        type="email" 
                        name="email" 
                        class="form--control checkUser" 
                        placeholder="Enter your email address"
                        required
                        @blur="checkUserExists"
                      >
                      <small v-if="userExists" class="text-danger">
                        <i class="fas fa-exclamation-circle me-1"></i>This email is already registered
                      </small>
                    </div>
                  </div>

                  <!-- Mobile -->
                  <div class="col-sm-12">
                    <div class="form-group">
                      <label class="form--label"><i class="fas fa-phone me-2"></i>Mobile <span class="text-danger">*</span></label>
                      <input 
                        v-model="form.mobile" 
                        type="tel" 
                        name="mobile" 
                        class="form--control" 
                        placeholder="Enter your mobile number"
                        required
                        pattern="[0-9]{10}"
                        maxlength="10"
                      >
                      <small class="text-muted">Enter 10-digit mobile number</small>
                    </div>
                  </div>

                  <!-- Select State -->
                  <div class="col-sm-12">
                    <div class="form-group">
                      <label class="form--label"><i class="fas fa-map-marker-alt me-2"></i>Select State <span class="text-danger">*</span></label>
                      <!-- Keep this as a native <select>.
                           Select2 can visually show a selection while the underlying <select> stays empty,
                           which triggers the browser "Please select an item in the list." validation. -->
                      <select v-model="form.state" name="state" class="form--control register-select2">
                        <option value="">-- Select State --</option>
                        <option v-for="state in indianStates" :key="state" :value="state">
                          {{ state }}
                        </option>
                      </select>
                    </div>
                  </div>

                  <!-- Password -->
                  <div class="col-sm-6">
                    <div class="form-group">
                      <label class="form--label"><i class="fas fa-lock me-2"></i>Password <span class="text-danger">*</span></label>
                      <input 
                        v-model="form.password" 
                        type="password" 
                        name="password" 
                        class="form--control"
                        :class="{ 'secure-password': securePassword }"
                        placeholder="Enter password"
                        required
                      >
                    </div>
                  </div>

                  <!-- Confirm Password -->
                  <div class="col-sm-6">
                    <div class="form-group">
                      <label class="form--label"><i class="fas fa-lock me-2"></i>Confirm Password <span class="text-danger">*</span></label>
                      <input 
                        v-model="form.password_confirmation" 
                        type="password" 
                        name="password_confirmation" 
                        class="form--control" 
                        placeholder="Confirm your password"
                        required
                      >
                    </div>
                  </div>

                  <div class="col-sm-12">
                    <!-- Captcha will be loaded here -->
                    <div id="captcha-container"></div>
                  </div>

                  <div class="col-12">
                    <div class="form--check">
                      <input
                        type="checkbox"
                        name="agree"
                        id="agree"
                        class="form-check-input"
                        v-model="form.agree"
                        required
                      >
                      <label class="form-check-label" for="agree">
                        I agree to the
                        <a href="/policy/terms-of-service" target="_blank">Terms of Service</a>
                        and
                        <a href="/policy/privacy-policy" target="_blank">Privacy Policy</a>
                      </label>
                    </div>
                  </div>

                  <div class="col-sm-12 mt-4">
                    <button 
                      type="submit" 
                      id="recaptcha" 
                      class="btn btn--base w-100 btn--lg"
                      :disabled="loading"
                    >
                      Next: Payment
                    </button>
                  </div>
                </div>
              </form>

              <!-- Step 2: Payment -->
              <div v-if="currentStep === 2" class="payment-step">
                <div class="payment-info-card mb-4">
                  <h5 class="payment-title"><i class="fas fa-credit-card me-2"></i>Package Payment</h5>
                  <div class="payment-amount">
                    <span class="payment-label">Amount to Pay:</span>
                    <span class="payment-value">{{ currencySymbol }}{{ registrationFee }}</span>
                  </div>
                  <p class="payment-description">Please complete the payment to proceed with registration.</p>
                </div>
                <button 
                  type="button" 
                  class="btn btn--base w-100 btn--lg mb-3"
                  @click="initiatePayment"
                  :disabled="paymentLoading"
                >
                  Pay {{ currencySymbol }}{{ registrationFee }} & Complete Registration
                </button>
                <button 
                  type="button" 
                  class="btn btn--secondary w-100"
                  @click="currentStep = 1"
                  :disabled="paymentLoading"
                >
                  <i class="fas fa-arrow-left me-2"></i>Back to Details
                    </button>
                  </div>

              <!-- Step 3: Processing/Complete -->
              <div v-if="currentStep === 3" class="processing-step text-center py-5">
                <div v-if="!registrationComplete" class="processing-content">
                  <h5>Registration</h5>
                  <p class="text-muted">Finalizing your account.</p>

                  <!-- Popup blocked fallback: show manual open link -->
                  <div v-if="paymentUrlRef" class="mt-4">
                    <p class="text-muted mb-2" v-if="popupBlocked">
                      Popup was blocked. Please use the button below to open payment in a new tab.
                    </p>
                    <div class="d-grid gap-2">
                      <button type="button" class="btn btn--base" @click="openPaymentPage">
                        Open Payment Page
                      </button>
                      <a :href="paymentUrlRef" target="_blank" rel="noopener noreferrer" class="btn btn--secondary">
                        Open in New Tab (Direct Link)
                      </a>
                      <button type="button" class="btn btn--secondary" @click="copyPaymentLink">
                        Copy Payment Link
                      </button>
                    </div>
                  </div>
                </div>
                <div v-else class="success-content">
                  <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                  <h5>Registration Successful!</h5>
                  <p class="text-muted">Your account is ready.</p>
                </div>
              </div>

              <!-- Social Login (only show on step 1) -->
              <div v-if="currentStep === 1" class="col-sm-12 mt-3">
                    <SocialLogin />
                  </div>

              <!-- Login Link (only show on step 1) -->
              <div v-if="currentStep === 1" class="col-sm-12">
                    <div class="have-account text-center mt-3">
                      <p class="have-account__text">
                        Already have an account?
                        <router-link to="/login" class="have-account__link">
                          Login Now
                        </router-link>
                      </p>
                    </div>
                  </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- User Exists Modal -->
    <div class="modal fade custom--modal" id="existModalCenter" tabindex="-1" role="dialog">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">You are with us</h5>
            <span type="button" class="close" data-bs-dismiss="modal"><i class="las la-times"></i></span>
          </div>
          <div class="modal-body">
            <h6 class="text-center mb-0">You already have an account please Login</h6>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn--danger btn--sm" data-bs-dismiss="modal">Close</button>
            <router-link to="/login" class="btn btn--base btn--sm">Login</router-link>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import { authService } from '../services/authService'
import { appService } from '../services/appService'
import api from '../services/api'
import SocialLogin from '../components/SocialLogin.vue'

export default {
  name: 'Register',
  components: {
    SocialLogin
  },
  setup() {
    const router = useRouter()
    const form = ref({
      fullname: '',
      email: '',
      mobile: '',
      state: '',
      password: '',
      password_confirmation: '',
      agree: false,
      sponsor_id: '',
      pkg: null,
      pkg_discount: null
    })
    const currentStep = ref(1)
    const loading = ref(false)
    const paymentLoading = ref(false)
    const error = ref('')
    const userExists = ref(false)
    const siteLogo = ref('/assets/images/logo_icon/logo.png?v=' + new Date().getTime())
    const defaultAboutImage = ref('/assets/images/frontend/about/default.jpg')
    const registerContent = ref(null)
    const aboutData = ref(null)
    const registrationEnabled = ref(true)
    const agreeRequired = ref(false)
    const policyPages = ref([])
    const securePassword = ref(false)
    const registrationToken = ref('')
    const paymentTrx = ref('')
    const paymentUrlRef = ref('')
    const popupBlocked = ref(false)
    let paymentPollInterval = null
    let paymentPollTimeout = null
    const registrationFee = ref(0)
    const currencySymbol = ref('₹')
    const registrationComplete = ref(false)
    const referrerInfo = ref(null)
    const loadingReferrer = ref(false)
    const sponsorInfo = ref(null)
    const loadingSponsor = ref(false)
    const packages = ref([])
    const lockedPkgId = ref(null)
    const pkgSig = ref('')
    const isPkgLocked = computed(() => !!lockedPkgId.value)
    const lockedPackageLabel = computed(() => {
      const pid = Number(form.value?.pkg)
      const p = (packages.value || []).find(x => Number(x?.id) === pid)
      if (p) return `${p.name} - ${currencySymbol.value}${formatAmount(p.price)}`
      if (pid) return `Selected package #${pid}`
      return '—'
    })
    const indianStates = ref([
      'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chhattisgarh',
      'Goa', 'Gujarat', 'Haryana', 'Himachal Pradesh', 'Jharkhand',
      'Karnataka', 'Kerala', 'Madhya Pradesh', 'Maharashtra', 'Manipur',
      'Meghalaya', 'Mizoram', 'Nagaland', 'Odisha', 'Punjab',
      'Rajasthan', 'Sikkim', 'Tamil Nadu', 'Telangana', 'Tripura',
      'Uttar Pradesh', 'Uttarakhand', 'West Bengal',
      'Andaman and Nicobar Islands', 'Chandigarh', 'Dadra and Nagar Haveli and Daman and Diu',
      'Delhi', 'Jammu and Kashmir', 'Ladakh', 'Lakshadweep', 'Puducherry'
    ])

    const getImageUrl = (imagePath) => {
      if (!imagePath) return defaultAboutImage.value
      
      // If already a full URL
      if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
        return imagePath
      }
      
      // If starts with /, use as is
      if (imagePath.startsWith('/')) {
        return imagePath
      }
      
      // Backend uses frontendImage helper: assets/images/frontend/{section}/{image}
      // So construct path accordingly
      if (imagePath.includes('frontend/')) {
        return `/assets/images/${imagePath}`
      }
      
      // Otherwise, use frontend path structure
      return `/assets/images/frontend/about/${imagePath}`
    }

    const handleImageError = (event) => {
      // Fallback to default image if main image fails to load
      const defaultImg = defaultAboutImage.value
      if (event.target.src !== defaultImg && !event.target.src.includes('placeholder')) {
        event.target.src = defaultImg
      } else {
        // Ultimate fallback - use placeholder
        event.target.src = 'https://via.placeholder.com/625x545?text=Affiliate+Image'
      }
    }

    const checkUserExists = async () => {
      if (!form.value.email) return
      try {
        // Check if user exists via API
        // This would be a custom endpoint
        userExists.value = false
      } catch (error) {
        console.error('Error checking user:', error)
      }
    }

    // Step 1: Validate registration data
    const handleStep1 = async () => {
      // Manual validation for selects
      if (!form.value.pkg) {
        if (window.notify) window.notify('error', 'Please select a package')
        return
      }
      if (!form.value.state) {
        if (window.notify) window.notify('error', 'Please select your state')
        return
      }

      if (userExists.value) {
        if (window.notify) window.notify('error', 'This email is already registered')
        return
      }

      // Validate sponsor only if user entered one
      if (form.value.sponsor_id && !sponsorInfo.value) {
        if (window.notify) {
          window.notify('error', 'Please enter a valid Sponsor ID')
        }
        return
      }

      if (!form.value.agree) {
        if (window.notify) {
          window.notify('error', 'Please accept the Terms of Service and Privacy Policy')
        }
        return
      }

      if (form.value.password !== form.value.password_confirmation) {
        if (window.notify) {
          window.notify('error', 'Passwords do not match')
        }
        return
      }

      // Split fullname into firstname and lastname for backend
      const nameParts = form.value.fullname.trim().split(' ')
      const firstname = nameParts[0] || ''
      const lastname = nameParts.slice(1).join(' ') || ''

      loading.value = true
      error.value = ''
      
      try {
        // Prepare data for backend (backend expects firstname, lastname, ref)
        const registrationData = {
          ...form.value,
          firstname: firstname,
          lastname: lastname,
          ref: (form.value.ref || form.value.sponsor_id || '').trim() || null,
          pkg_sig: pkgSig.value || null
        }

        const response = await api.post('/register/validate', registrationData)
        if (response.data.status === 'success') {
          registrationToken.value = response.data.data.registration_token
          registrationFee.value = response.data.data.registration_fee || 0
          currencySymbol.value = response.data.data.currency_symbol || '₹'
          currentStep.value = 2 // Move to payment step
          if (window.notify) {
            window.notify('success', 'Details validated. Please proceed to payment.')
          }
        } else {
          // If it's a validation error, the message might be an object of arrays
          const msg = response.data.message;
          let errorText = 'Validation failed';
          
          if (typeof msg === 'object') {
            errorText = Object.values(msg).flat()[0] || 'Validation failed';
          } else if (Array.isArray(msg)) {
            errorText = msg[0];
          } else if (typeof msg === 'string') {
            errorText = msg;
          }

          error.value = errorText;
          if (window.notify) {
            window.notify('error', errorText)
          }
        }
      } catch (err) {
        const msg = err.response?.data?.message;
        let errorText = 'An error occurred';

        if (typeof msg === 'object') {
          errorText = Object.values(msg).flat()[0] || 'An error occurred';
        } else if (Array.isArray(msg)) {
          errorText = msg[0];
        } else if (typeof msg === 'string') {
          errorText = msg;
        }

        error.value = errorText;
        if (window.notify) {
          window.notify('error', errorText)
        }
      } finally {
        loading.value = false
      }
    }

    // Step 2: Initiate payment
    const initiatePayment = async () => {
      if (!registrationToken.value) {
        if (window.notify) {
          window.notify('error', 'Please complete step 1 first')
        }
        return
      }

      // Persist token so we can complete after returning from gateway
      try {
        sessionStorage.setItem('reg_token', registrationToken.value)
      } catch (e) {}
      
      const redirectUrl = `/user/payment-redirect?flow=registration&registration_token=${encodeURIComponent(registrationToken.value)}&amount=${registrationFee.value}&plan_name=${encodeURIComponent(lockedPackageLabel.value || 'Registration Fee')}&back=${encodeURIComponent('/register')}`
      
      const w = window.open(redirectUrl, '_blank')
      if (!w) {
         router.push(redirectUrl)
      }
    }

    const openPaymentPage = () => {
      if (!paymentUrlRef.value) return
      const w = window.open(paymentUrlRef.value, '_blank', 'noopener,noreferrer')
      if (!w && window.notify) {
        window.notify('error', 'Popup blocked again. Please use the direct link button.')
      }
    }

    const copyPaymentLink = async () => {
      const url = paymentUrlRef.value
      if (!url) return
      try {
        if (navigator?.clipboard?.writeText) {
          await navigator.clipboard.writeText(url)
        } else {
          const ta = document.createElement('textarea')
          ta.value = url
          ta.style.position = 'fixed'
          ta.style.opacity = '0'
          document.body.appendChild(ta)
          ta.focus()
          ta.select()
          document.execCommand('copy')
          document.body.removeChild(ta)
        }
        if (window.notify) window.notify('success', 'Payment link copied')
      } catch (e) {
        if (window.notify) window.notify('error', 'Could not copy link')
      }
    }

    // Step 3: Complete registration after payment
    const completeRegistration = async () => {
      try {
        const response = await api.post('/register', {
          registration_token: registrationToken.value,
          payment_trx: paymentTrx.value
        })
        
        if (response.data.status === 'success') {
          registrationComplete.value = true
          
          // Store token
          if (response.data.data?.token) {
            localStorage.setItem('auth_token', response.data.data.token)
          }
          
          if (window.notify) {
            window.notify('success', 'Registration successful!')
          }
          
          // Redirect to dashboard after 2 seconds
          setTimeout(() => {
            router.push('/user/dashboard')
          }, 2000)
        } else {
          error.value = response.data.message?.[0] || 'Registration failed'
          currentStep.value = 2 // Go back to payment step
          if (window.notify) {
            window.notify('error', error.value)
          }
        }
      } catch (err) {
        error.value = err.response?.data?.message?.[0] || 'Registration failed'
        currentStep.value = 2 // Go back to payment step
        if (window.notify) {
          window.notify('error', error.value)
        }
      }
    }

    const formatAmount = (amount) => {
      if (!amount && amount !== 0) return '0.00'
      return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    }

    const fetchSponsorInfo = async () => {
      let sponsorId = form.value.sponsor_id?.trim()
      if (!sponsorId) {
        sponsorInfo.value = null
        form.value.ref = ''
        return
      }
      
      // Auto-prefix if missing
      if (/^\d+$/.test(sponsorId)) {
        sponsorId = 'ADS' + sponsorId
        form.value.sponsor_id = sponsorId
      }

      loadingSponsor.value = true
      try {
        const response = await api.get(`/register/referrer-info?ref=${sponsorId}`)
        if (response.data.status === 'success') {
          sponsorInfo.value = response.data.data
          // Use ADS ID internally for registration to be safe
          form.value.ref = sponsorInfo.value.ref_code || sponsorInfo.value.username
        } else {
          sponsorInfo.value = null
          form.value.ref = ''
        }
      } catch (error) {
        sponsorInfo.value = null
        form.value.ref = ''
      } finally {
        loadingSponsor.value = false
      }
    }

    const handleSponsorInput = () => {
      if (!form.value.sponsor_id) {
        sponsorInfo.value = null
        form.value.ref = ''
      }
    }

    const fetchReferrerInfo = async (refCode) => {
      if (!refCode) return
      
      loadingReferrer.value = true
      try {
        const response = await api.get(`/register/referrer-info?ref=${refCode}`)
        if (response.data.status === 'success') {
          referrerInfo.value = response.data.data
          // Auto-fill sponsor fields if ref from URL
          form.value.sponsor_id = response.data.data.ref_code || refCode
          sponsorInfo.value = response.data.data
          form.value.ref = refCode
        } else {
          referrerInfo.value = null
        }
      } catch (error) {
        referrerInfo.value = null
      } finally {
        loadingReferrer.value = false
      }
    }

    const fetchPackages = async () => {
      try {
        const response = await api.get('/packages')
        if (response.data.status === 'success') {
          const data = response.data.data
          const all = data?.data || data || []
          packages.value = all
          currencySymbol.value = data?.currency_symbol || '₹'

          // If package is locked via referral link, hide other packages
          if (lockedPkgId.value) {
            const keepId = Number(lockedPkgId.value)
            const filtered = (all || []).filter(p => Number(p?.id) === keepId)
            if (filtered.length) packages.value = filtered
          }
        }
      } catch (error) {
        console.error('Error loading packages:', error)
        packages.value = []
      }
    }

    const initSelect2 = () => {
      const $ = window.jQuery
      if (!$ || !$.fn || !$.fn.select2) return
      const $els = $('.register-select2:enabled')
      if (!$els || !$els.length) return
      $els.each(function () {
        const $el = $(this)
        try {
          if ($el.hasClass('select2-hidden-accessible')) {
            $el.select2('destroy')
          }
        } catch (e) {
          // ignore
        }
      })
      try {
        $els.each(function () {
          const $el = $(this)
          const name = $el.attr('name')
          const $parent = $el.closest('.account-form')
          
          $el.select2({
            width: '100%',
            dropdownAutoWidth: false,
            dropdownParent: $parent && $parent.length ? $parent : $(document.body),
          }).on('select2:select', function (e) {
            // Force update Vue model
            if (name && form.value.hasOwnProperty(name)) {
              form.value[name] = e.params.data.id
            }
          }).on('change', function () {
            // Backup update
            if (name && form.value.hasOwnProperty(name)) {
              const val = $(this).val()
              if (val !== form.value[name]) {
                form.value[name] = val
              }
            }
          })
        })
      } catch (e) {
        // ignore
      }
    }

    onMounted(async () => {
      // Route body class for global dropdown theming
      if (typeof document !== 'undefined') {
        document.body.classList.add('register-route')
      }

      // Returned from Gateway (payment page_url)
      const urlParamsQuery = new URLSearchParams(window.location.search)
      const returnedTrx = urlParamsQuery.get('watchpay_trx') || urlParamsQuery.get('simplypay_trx')
      
      if (returnedTrx) {
        currentStep.value = 3
        paymentTrx.value = returnedTrx
        try {
          const savedToken = sessionStorage.getItem('reg_token')
          if (savedToken) registrationToken.value = savedToken
        } catch (e) {}

        // Verify payment; if verified, complete registration
        try {
          const verify = await api.get(`/register/payment/dummy?trx=${encodeURIComponent(paymentTrx.value)}`)
          if (verify.data.status === 'success') {
            await completeRegistration()
          } else {
            currentStep.value = 2
            if (window.notify) window.notify('error', 'Payment not verified yet. Please wait and try again.')
          }
        } catch (e) {
          currentStep.value = 2
          if (window.notify) window.notify('error', 'Payment verification failed. Please try again.')
        }
      }

      // Get referral and package from URL
      const urlParams = new URLSearchParams(window.location.search)
      const refUsername = urlParams.get('ref') || ''
      const pkgId = urlParams.get('pkg') ? parseInt(urlParams.get('pkg')) : null
      const sig = urlParams.get('sig') || ''
      const disc = urlParams.get('disc') ? parseInt(urlParams.get('disc')) : null

      const shouldLockPkg = !!(pkgId && sig)
      
      form.value.ref = refUsername
      form.value.pkg = pkgId
      lockedPkgId.value = shouldLockPkg ? pkgId : null
      pkgSig.value = shouldLockPkg ? sig : ''
      form.value.pkg_discount = (shouldLockPkg && Number.isFinite(disc) && disc !== null && disc > 0) ? disc : null
      
      // Fetch referrer info if ref parameter exists
      if (refUsername) {
        await fetchReferrerInfo(refUsername)
      }

      // Fetch packages list
      await fetchPackages()
      await nextTick()
      initSelect2()
      // extra retry after render/layout settle (mobile browsers)
      setTimeout(() => initSelect2(), 350)

      try {
        const [registerRes, aboutRes, settingsRes, policiesRes] = await Promise.all([
          appService.getSections('register'),
          appService.getSections('about'),
          appService.getGeneralSettings(),
          appService.getPolicies()
        ])
        
        registerContent.value = appService.getSectionContent(registerRes) || null
        const aboutContent = appService.getSectionContent(aboutRes)
        
        // Format image path properly
        if (aboutContent && aboutContent.image) {
          // If image is already a full URL, use it as is
          if (aboutContent.image.startsWith('http://') || aboutContent.image.startsWith('https://')) {
            aboutData.value = aboutContent
          } else if (aboutContent.image.startsWith('/')) {
            // If it starts with /, it's already a relative path
            aboutData.value = aboutContent
          } else {
            // Otherwise, construct the path
            aboutData.value = {
              ...aboutContent,
              image: `/assets/images/about/${aboutContent.image}`
            }
          }
        } else {
          aboutData.value = aboutContent
        }
        
        console.log('About Data:', aboutData.value) // Debug log
        console.log('About Image:', aboutData.value?.image) // Debug log
        
        registrationEnabled.value = settingsRes.data?.registration !== false
        agreeRequired.value = settingsRes.data?.agree === true
        policyPages.value = policiesRes.data || []
        securePassword.value = settingsRes.data?.secure_password === true

        // Load secure password script if needed
        if (securePassword.value && !document.querySelector('script[src*="secure_password"]')) {
          const script = document.createElement('script')
          script.src = '/assets/global/js/secure_password.js'
          document.body.appendChild(script)
        }

        // Load captcha if needed
        if (typeof window.loadReCaptcha === 'function') {
          window.loadReCaptcha()
        }
      } catch (error) {
        console.error('Error loading register page data:', error)
      }
    })

    onUnmounted(() => {
      try {
        try { if (paymentPollInterval) clearInterval(paymentPollInterval) } catch (e) {}
        try { if (paymentPollTimeout) clearTimeout(paymentPollTimeout) } catch (e) {}
        if (typeof document !== 'undefined') {
          document.body.classList.remove('register-route')
        }
        const $ = window.jQuery
        if ($ && $.fn && $.fn.select2) {
          const $els = $('.register-select2')
          $els.each(function () {
            const $el = $(this)
            if ($el.hasClass('select2-hidden-accessible')) {
              $el.select2('destroy')
            }
          })
        }
      } catch (e) {
        // ignore
      }
    })

    return {
      form,
      currentStep,
      loading,
      paymentLoading,
      error,
      userExists,
      siteLogo,
      registerContent,
      aboutData,
      registrationEnabled,
      agreeRequired,
      policyPages,
      securePassword,
      registrationToken,
      paymentTrx,
      paymentUrlRef,
      popupBlocked,
      registrationFee,
      currencySymbol,
      registrationComplete,
      referrerInfo,
      loadingReferrer,
      sponsorInfo,
      loadingSponsor,
      packages,
      isPkgLocked,
      lockedPackageLabel,
      indianStates,
      formatAmount,
      getImageUrl,
      handleImageError,
      checkUserExists,
      handleStep1,
      initiatePayment,
      completeRegistration,
      openPaymentPage,
      copyPaymentLink,
      fetchReferrerInfo,
      fetchSponsorInfo,
      handleSponsorInput,
      fetchPackages
    }
  }
}
</script>

<style scoped>
/* Registration Steps */
.registration-steps {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding: 1rem;
  background: rgba(15, 23, 42, 0.6);
  border-radius: 12px;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.step-indicator {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  position: relative;
  opacity: 0.5;
  transition: all 0.3s ease;
}

.step-indicator::after {
  content: '';
  position: absolute;
  top: 20px;
  left: 60%;
  width: 80%;
  height: 2px;
  background: rgba(255, 255, 255, 0.2);
  z-index: 0;
}

.step-indicator:last-child::after {
  display: none;
}

.step-indicator.active {
  opacity: 1;
}

.step-indicator.completed::after {
  background: #10b981;
}

.step-number {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.2);
  color: rgba(255, 255, 255, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 1.1rem;
  margin-bottom: 0.5rem;
  position: relative;
  z-index: 1;
  transition: all 0.3s ease;
  border: 2px solid rgba(255, 255, 255, 0.2);
}

.step-indicator.active .step-number {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  color: #ffffff;
  box-shadow: 0 4px 16px rgba(59, 130, 246, 0.5);
  border-color: #60a5fa;
}

.step-indicator.completed .step-number {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: #ffffff;
  box-shadow: 0 4px 16px rgba(16, 185, 129, 0.5);
  border-color: #34d399;
}

/* Mobile linear steps */
.registration-steps-mobile {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  padding: 12px 10px;
  background: rgba(15, 23, 42, 0.6);
  border-radius: 12px;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.1);
}
.rstep {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  min-width: 64px;
  opacity: 0.55;
}
.rstep.active { opacity: 1; }
.rstep-circle {
  width: 32px;
  height: 32px;
  border-radius: 999px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  font-size: 0.95rem;
  background: rgba(255, 255, 255, 0.18);
  border: 2px solid rgba(255, 255, 255, 0.18);
  color: rgba(255, 255, 255, 0.75);
}
.rstep.active .rstep-circle {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  border-color: #60a5fa;
  color: #fff;
}
.rstep.completed .rstep-circle {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  border-color: #34d399;
  color: #fff;
}
.rstep-label {
  font-size: 0.72rem;
  font-weight: 700;
  color: rgba(255, 255, 255, 0.70);
  text-align: center;
  line-height: 1.05;
}
.rstep.active .rstep-label { color: #93c5fd; }
.rstep.completed .rstep-label { color: #6ee7b7; }
.rstep-line {
  height: 2px;
  flex: 1;
  background: rgba(255, 255, 255, 0.18);
  border-radius: 999px;
  margin-top: -14px; /* align with circles */
}
.rstep-line.completed {
  background: rgba(16, 185, 129, 0.75);
}

.step-label {
  font-size: 0.85rem;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.6);
  text-align: center;
}

.step-indicator.active .step-label {
  color: #60a5fa;
  font-weight: 700;
}

.step-indicator.completed .step-label {
  color: #34d399;
}

/* Payment Step */
.payment-step {
  animation: fadeIn 0.5s ease-in;
}

.payment-info-card {
  background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 50%, #2563eb 100%);
  border: 2px solid rgba(59, 130, 246, 0.4);
  border-radius: 16px;
  padding: 2rem;
  text-align: center;
  box-shadow: 0 8px 24px rgba(37, 99, 235, 0.3);
  position: relative;
  overflow: hidden;
}

.payment-info-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, transparent 100%);
  pointer-events: none;
}

.payment-title {
  font-weight: 800;
  font-size: 1.5rem;
  color: #ffffff;
  margin-bottom: 1.5rem;
  position: relative;
  z-index: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.payment-title i {
  color: #ffffff;
  font-size: 1.8rem;
}

.payment-amount {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 1rem;
  position: relative;
  z-index: 1;
}

.payment-label {
  font-size: 1rem;
  color: rgba(255, 255, 255, 0.9);
  font-weight: 600;
}

.payment-value {
  font-size: 3rem;
  font-weight: 900;
  color: #60a5fa;
  text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
  line-height: 1;
}

.payment-description {
  color: rgba(255, 255, 255, 0.85);
  font-size: 0.95rem;
  margin: 0;
  position: relative;
  z-index: 1;
}

/* Processing Step */
.processing-step {
  animation: fadeIn 0.5s ease-in;
  min-height: 300px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.processing-content h5 {
  font-weight: 700;
  color: rgba(255, 255, 255, 0.92);
  margin-bottom: 0.5rem;
}

.processing-content p {
  color: rgba(255, 255, 255, 0.65) !important;
}

.success-content h5 {
  font-weight: 700;
  color: #10b981;
  margin-bottom: 0.5rem;
}

.btn--secondary {
  background: #f1f5f9;
  color: #475569;
  border: 1px solid #e2e8f0;
}

.btn--secondary:hover {
  background: #e2e8f0;
  color: #334155;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Referrer Info Card */
.referrer-info-card {
  background: linear-gradient(135deg, #065f46 0%, #047857 50%, #059669 100%);
  border: 2px solid rgba(16, 185, 129, 0.4);
  border-radius: 14px;
  padding: 1.25rem;
  animation: slideIn 0.5s ease-out;
  box-shadow: 0 4px 16px rgba(5, 150, 105, 0.3);
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateX(-20px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

.referrer-info-content {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.referrer-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(10px);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #a7f3d0;
  font-size: 1.5rem;
  flex-shrink: 0;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.referrer-details {
  flex: 1;
  min-width: 0;
}

.referrer-label {
  font-size: 0.85rem;
  color: #a7f3d0;
  font-weight: 600;
  margin: 0;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.referrer-label i {
  color: #a7f3d0;
}

.referrer-name {
  font-size: 1.2rem;
  font-weight: 800;
  color: #ffffff;
  margin: 0;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.referrer-meta {
  font-size: 0.875rem;
  color: #ffffff;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.referrer-id {
  font-weight: 700;
  color: #ffffff;
}

.referrer-separator {
  color: #a7f3d0;
}

.referrer-username {
  font-weight: 600;
  color: #a7f3d0;
}

/* Dark Theme Background */
:deep(.account.register) {
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
  background-attachment: fixed;
  min-height: 100vh;
  padding-top: 1rem;
  padding-bottom: 2rem;
}

:deep(.account.register .container) {
  padding-top: 1rem;
}

:deep(.account-inner) {
  background: transparent;
}

:deep(.account-form) {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 20px;
  padding: 2rem 3rem;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
  max-width: 100%;
  margin: 0 auto;
}

:deep(.account-inner) {
  padding-top: 2rem;
  padding-bottom: 2rem;
}

:deep(.account-form__content) {
  margin-bottom: 1.5rem !important;
}

:deep(.account-form__title) {
  font-size: 2rem !important;
  font-weight: 800 !important;
  color: #ffffff !important;
  margin-bottom: 0.5rem !important;
  text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

:deep(.account-form__badge) {
  display: none;
}

:deep(.account-form__title),
:deep(.account-form__badge) {
  color: #ffffff;
}

:deep(.form--label) {
  color: rgba(255, 255, 255, 0.9);
}

:deep(.form--control) {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: #ffffff;
  backdrop-filter: blur(5px);
}

:deep(.form--control:focus) {
  background: rgba(255, 255, 255, 0.15);
  border-color: #3b82f6;
  color: #ffffff;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
}

:deep(.form--control::placeholder) {
  color: rgba(255, 255, 255, 0.5);
}

:deep(.form--control[readonly]) {
  background: rgba(16, 185, 129, 0.2);
  color: #ffffff;
  border-color: rgba(16, 185, 129, 0.4);
}

:deep(.text-muted) {
  color: rgba(255, 255, 255, 0.6) !important;
}

:deep(.btn--base) {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  border: none;
  color: #ffffff;
  box-shadow: 0 4px 16px rgba(59, 130, 246, 0.4);
}

:deep(.btn--base:hover) {
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
  box-shadow: 0 6px 20px rgba(59, 130, 246, 0.5);
  transform: translateY(-2px);
}

:deep(.btn--secondary) {
  background: rgba(255, 255, 255, 0.1);
  color: rgba(255, 255, 255, 0.9);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

:deep(.btn--secondary:hover) {
  background: rgba(255, 255, 255, 0.15);
  color: #ffffff;
  border-color: rgba(255, 255, 255, 0.3);
}

:deep(.have-account__text) {
  color: rgba(255, 255, 255, 0.8);
}

:deep(.have-account__link) {
  color: #60a5fa;
}

:deep(.have-account__link:hover) {
  color: #3b82f6;
}

@media (max-width: 768px) {
  .registration-steps {
    flex-direction: row;
    gap: 0.5rem;
    padding: 0.75rem;
  }
  
  .step-indicator::after {
    top: 16px;
    left: 60%;
    width: 80%;
    display: block;
  }

  .step-number {
    width: 32px;
    height: 32px;
    font-size: 0.95rem;
    margin-bottom: 0.35rem;
  }

  .step-label {
    font-size: 0.7rem;
    line-height: 1.1;
  }
  
  .payment-value {
    font-size: 2rem;
  }
  
  .referrer-info-content {
    flex-direction: column;
    text-align: center;
  }
  
  .referrer-meta {
    justify-content: center;
  }
}

/* Register-specific readonly input (avoid white field on dark UI) */
::deep(.register-readonly) {
  background: rgba(255, 255, 255, 0.08) !important;
  border-color: rgba(255, 255, 255, 0.18) !important;
  color: rgba(255, 255, 255, 0.9) !important;
}
::deep(.register-readonly.register-readonly--ok) {
  background: rgba(16, 185, 129, 0.20) !important;
  border-color: rgba(16, 185, 129, 0.45) !important;
  color: #ffffff !important;
}

/* Native selects in dark theme (fix white dropdown on phones) */
::deep(select.form--control) {
  background: rgba(255, 255, 255, 0.1) !important;
  color: #ffffff !important;
}
::deep(select.form--control option) {
  background: #0f172a !important;
  color: #ffffff !important;
}

/* Select2 overrides (some template scripts initialize select2, its dropdown becomes white by default) */
::deep(.select2-container--default .select2-selection--single),
::deep(.select2-container--default .select2-selection--multiple) {
  background: rgba(255, 255, 255, 0.1) !important;
  border: 1px solid rgba(255, 255, 255, 0.2) !important;
  color: #ffffff !important;
  border-radius: 12px !important;
}
::deep(.select2-container--default .select2-selection--single .select2-selection__rendered) {
  color: #ffffff !important;
}
::deep(.select2-dropdown) {
  background: #0f172a !important;
  border: 1px solid rgba(255, 255, 255, 0.2) !important;
}
::deep(.select2-results__option) {
  color: rgba(255, 255, 255, 0.9) !important;
}
::deep(.select2-container--default .select2-results__option--highlighted.select2-results__option--selectable) {
  background: #2563eb !important;
  color: #ffffff !important;
}
::deep(.select2-container--default .select2-results__option--selected) {
  background: rgba(37, 99, 235, 0.25) !important;
  color: #ffffff !important;
}

/* Select2 dropdown is appended to <body>, so we must style it globally even in scoped CSS */
:global(.select2-dropdown) {
  background: #0f172a !important;
  border: 1px solid rgba(255, 255, 255, 0.2) !important;
}
:global(.select2-results__option) {
  color: rgba(255, 255, 255, 0.9) !important;
}
:global(.select2-container--default .select2-results__option--highlighted.select2-results__option--selectable) {
  background: #2563eb !important;
  color: #ffffff !important;
}
:global(.select2-container--default .select2-results__option--selected) {
  background: rgba(37, 99, 235, 0.25) !important;
  color: #ffffff !important;
}
:global(.select2-container--default .select2-selection--single),
:global(.select2-container--default .select2-selection--multiple) {
  background: rgba(255, 255, 255, 0.1) !important;
  border: 1px solid rgba(255, 255, 255, 0.2) !important;
  color: #ffffff !important;
  border-radius: 12px !important;
}
:global(.select2-container--default .select2-selection--single .select2-selection__rendered) {
  color: #ffffff !important;
}

/* Register UI polish */
::deep(.form-group) {
  margin-bottom: 1rem;
}

/* Override base template controls for better touch UX */
::deep(.form--control) {
  border-radius: 14px;
  min-height: 48px;
  transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
}

::deep(.form--control:disabled) {
  opacity: 0.85;
  cursor: not-allowed;
  background: rgba(255, 255, 255, 0.06);
  border-color: rgba(255, 255, 255, 0.14);
  color: rgba(255, 255, 255, 0.70);
}

/* Modal (existing-user) dark styling */
::deep(.modal-content) {
  background: rgba(15, 23, 42, 0.96);
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 16px;
}
::deep(.modal-header),
::deep(.modal-footer) {
  border-color: rgba(255, 255, 255, 0.10);
}
::deep(.modal-title),
::deep(.modal-body),
::deep(.modal-header .close) {
  color: rgba(255, 255, 255, 0.92);
}
::deep(.modal-body h6) {
  color: rgba(255, 255, 255, 0.85);
}

/* Mobile spacing tweaks */
@media (max-width: 768px) {
  ::deep(.account-form) {
    padding: 1.25rem 1rem;
  }
}
</style>
