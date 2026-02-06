<template>
  <section class="account register" v-if="registrationEnabled">
    <div class="account-inner">
      <div class="container">
        <div class="row gy-4 flex-wrap-reverse justify-content-center">
          <!-- Left Side - Logo and About Image -->
          <div class="col-lg-6 d-xl-block d-none pe-xl-5">
            <router-link to="/" class="account-logo">
              <img :src="siteLogo" alt="logo">
            </router-link>
            <div class="about-thumb-wrapper" v-if="aboutData">
              <div class="left-thumb-wrapper">
                <div class="left-thumb"></div>
                <div class="border-shape"></div>
              </div>
              <div class="about-thumb-wrapper__thumb" v-if="aboutData.image">
                <img :src="aboutData.image" alt="Affiliate Image" />
              </div>
              <div class="thumb-text-wrapper">
                <span class="text base--outline" v-if="aboutData.tag_text_one">{{ aboutData.tag_text_one }}</span>
                <span class="text success--outline" v-if="aboutData.tag_text_two">{{ aboutData.tag_text_two }}</span>
                <span class="text warning--outline" v-if="aboutData.tag_text_three">{{ aboutData.tag_text_three }}</span>
                <span class="text success--outline" v-if="aboutData.tag_text_four">{{ aboutData.tag_text_four }}</span>
                <span class="text base--outline" v-if="aboutData.tag_text_five">{{ aboutData.tag_text_five }}</span>
              </div>
            </div>
          </div>

          <!-- Right Side - Register Form -->
          <div class="col-lg-6">
            <div class="right-thumb-wrapper">
              <div class="right-thumb"></div>
              <div class="border-shape"></div>
              <div class="bg-style"></div>
            </div>
            <div class="account-form">
              <router-link to="/" class="d-xl-none account-logo">
                <img :src="siteLogo" alt="logo">
              </router-link>
              <div class="account-form__content">
                <span class="account-form__badge">{{ registerContent?.heading || 'Create Account' }}</span>
                <h4 class="account-form__title">{{ registerContent?.subheading || 'Join us today' }}</h4>
              </div>
              <form @submit.prevent="handleRegister" class="verify-gcaptcha disableSubmission">
                <div class="row">
                  <div class="col-sm-6">
                    <div class="form-group">
                      <label class="form--label">First Name</label>
                      <input v-model="form.firstname" type="text" name="firstname" class="form--control" required>
                    </div>
                  </div>

                  <div class="col-sm-6">
                    <div class="form-group">
                      <label class="form--label">Last Name</label>
                      <input v-model="form.lastname" type="text" name="lastname" class="form--control" required>
                    </div>
                  </div>

                  <div class="col-sm-12">
                    <div class="form-group">
                      <label class="form--label">E-Mail Address</label>
                      <input 
                        v-model="form.email" 
                        type="email" 
                        name="email" 
                        class="form--control checkUser" 
                        required
                        @blur="checkUserExists"
                      >
                      <small v-if="userExists" class="text-danger">This email is already registered</small>
                    </div>
                  </div>

                  <div class="col-sm-6">
                    <div class="form-group">
                      <label class="form--label">Password</label>
                      <input 
                        v-model="form.password" 
                        type="password" 
                        name="password" 
                        class="form--control"
                        :class="{ 'secure-password': securePassword }"
                        required
                      >
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <div class="form-group">
                      <label class="form--label">Confirm Password</label>
                      <input 
                        v-model="form.password_confirmation" 
                        type="password" 
                        name="password_confirmation" 
                        class="form--control" 
                        required
                      >
                    </div>
                  </div>

                  <div class="col-sm-12">
                    <!-- Captcha will be loaded here -->
                    <div id="captcha-container"></div>
                  </div>

                  <div class="col-12" v-if="agreeRequired && policyPages.length">
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
                        I agree with
                        <a 
                          v-for="(policy, index) in policyPages" 
                          :key="policy.id"
                          :href="`/policy/${policy.slug}`" 
                          target="_blank"
                        >
                          {{ policy.title }}
                        </a>
                        <template v-if="index < policyPages.length - 1">,</template>
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
                      {{ loading ? 'Registering...' : 'Submit' }}
                    </button>
                  </div>

                  <!-- Social Login -->
                  <div class="col-sm-12">
                    <SocialLogin />
                  </div>

                  <div class="col-sm-12">
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
              </form>
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
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { authService } from '../services/authService'
import { appService } from '../services/appService'
import SocialLogin from '../components/SocialLogin.vue'

export default {
  name: 'Register',
  components: {
    SocialLogin
  },
  setup() {
    const router = useRouter()
    const form = ref({
      firstname: '',
      lastname: '',
      email: '',
      password: '',
      password_confirmation: '',
      agree: false
    })
    const loading = ref(false)
    const error = ref('')
    const userExists = ref(false)
    const siteLogo = ref('/assets/images/logo.png')
    const registerContent = ref(null)
    const aboutData = ref(null)
    const registrationEnabled = ref(true)
    const agreeRequired = ref(false)
    const policyPages = ref([])
    const securePassword = ref(false)

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

    const handleRegister = async () => {
      if (form.value.password !== form.value.password_confirmation) {
        if (window.notify) {
          window.notify('error', 'Passwords do not match')
        }
        return
      }

      loading.value = true
      error.value = ''
      
      try {
        const response = await authService.register(form.value)
        if (response.status === 'success') {
          if (window.notify) {
            window.notify('success', 'Registration successful!')
          }
          router.push('/dashboard')
        } else {
          error.value = response.message || 'Registration failed'
          if (window.notify) {
            window.notify('error', error.value)
          }
        }
      } catch (err) {
        error.value = err.response?.data?.message || 'An error occurred'
        if (window.notify) {
          window.notify('error', error.value)
        }
      } finally {
        loading.value = false
      }
    }

    onMounted(async () => {
      try {
        const [registerRes, aboutRes, settingsRes, policiesRes] = await Promise.all([
          appService.getSections('register'),
          appService.getSections('about'),
          appService.getGeneralSettings(),
          appService.getPolicies()
        ])
        
        registerContent.value = registerRes.data?.content?.data_values || null
        aboutData.value = aboutRes.data?.content?.data_values || null
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

    return {
      form,
      loading,
      error,
      userExists,
      siteLogo,
      registerContent,
      aboutData,
      registrationEnabled,
      agreeRequired,
      policyPages,
      securePassword,
      checkUserExists,
      handleRegister
    }
  }
}
</script>

<style>
/* Styles are loaded from external CSS files */
</style>
