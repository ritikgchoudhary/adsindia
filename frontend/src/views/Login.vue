<template>
  <section class="account" style="padding-top: 160px;">
    <div class="account-inner">
      <div class="container">
        <div class="row gy-4 justify-content-center">
          <!-- Centered Login Form -->
          <div class="col-xl-6 col-lg-7 col-md-9">
            <div class="account-form login-centered-form">
              <router-link to="/" class="account-logo d-block text-center mb-4">
                <img :src="siteLogo" alt="logo">
              </router-link>
              <div class="account-form__content">
                <span class="account-form__badge">{{ loginContent?.heading || 'Welcome Back' }}</span>
                <h4 class="account-form__title">{{ loginContent?.subheading || 'Login to your account' }}</h4>
              </div>

              <form @submit.prevent="handleLogin" class="verify-gcaptcha">
                <div class="row">
                  <div class="col-sm-12">
                    <div class="form-group">
                      <label class="form--label">Email</label>
                      <input 
                        v-model="form.email" 
                        type="email" 
                        inputmode="email"
                        name="email" 
                        class="form--control" 
                        required
                        autocomplete="email"
                      >
                    </div>
                  </div>

                  <div class="col-sm-12">
                    <div class="form-group">
                      <label for="password" class="form--label">Password</label>
                      <div class="position-relative">
                        <input 
                          id="password" 
                          v-model="form.password"
                          :type="showPassword ? 'text' : 'password'" 
                          name="password" 
                          class="form-control form--control" 
                          required
                        >
                        <span 
                          class="password-show-hide fas toggle-password"
                          :class="showPassword ? 'fa-eye' : 'fa-eye-slash'"
                          @click="togglePassword"
                        ></span>
                      </div>
                    </div>
                  </div>

                  <div class="col-sm-12">
                    <!-- Captcha will be loaded here -->
                    <div id="captcha-container"></div>
                  </div>

                  <div class="col-sm-12">
                    <div class="d-flex flex-wrap justify-content-between">
                      <div class="form--check">
                        <input 
                          class="form-check-input" 
                          type="checkbox" 
                          name="remember" 
                          id="remember"
                          v-model="form.remember"
                        >
                        <label class="form-check-label" for="remember">Remember Me</label>
                      </div>
                      <router-link to="/forgot-password" class="forgot-password text--base">
                        Forgot your password?
                      </router-link>
                    </div>
                  </div>
                </div>
                <button 
                  type="submit" 
                  id="recaptcha" 
                  class="btn btn--base w-100 btn--lg mt-3"
                  :disabled="loading"
                >
                  Submit
                </button>
              </form>

              <!-- Social Login -->
              <SocialLogin />

              <div class="mt-3" v-if="registrationEnabled">
                <div class="have-account text-center">
                  <p class="have-account__text">
                    Don't have an account?
                    <router-link to="/register" class="have-account__link">
                      Create an account
                    </router-link>
                  </p>
                </div>
              </div>
            </div>
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
  name: 'Login',
  components: {
    SocialLogin
  },
  setup() {
    const router = useRouter()
    const form = ref({
      email: '',
      password: '',
      remember: false
    })
    const loading = ref(false)
    const error = ref('')
    const showPassword = ref(false)
    const siteLogo = ref('/assets/images/logo_icon/logo.png?v=' + new Date().getTime())
    const loginContent = ref(null)
    const aboutData = ref(null)
    const registrationEnabled = ref(true)

    const togglePassword = () => {
      showPassword.value = !showPassword.value
    }

    const handleLogin = async () => {
      loading.value = true
      error.value = ''
      
      try {
        const payload = {
          email: form.value.email,
          password: form.value.password,
          remember: !!form.value.remember
        }
        const response = await authService.login(payload)
        if (response.status === 'success') {
          if (window.notify) {
            window.notify('success', 'Login successful!')
          }
          router.push('/dashboard')
        } else {
          error.value = response.message || 'Login failed'
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
        // Load login content and about data
        const [loginRes, aboutRes, settingsRes] = await Promise.all([
          appService.getSections('login'),
          appService.getSections('about'),
          appService.getGeneralSettings()
        ])
        
        loginContent.value = loginRes.data?.content?.data_values || null
        aboutData.value = aboutRes.data?.content?.data_values || null
        registrationEnabled.value = settingsRes.data?.registration !== false
        
        // Load captcha if needed
        if (typeof window.loadReCaptcha === 'function') {
          window.loadReCaptcha()
        }
      } catch (error) {
        console.error('Error loading login page data:', error)
      }
    })

    return {
      form,
      loading,
      error,
      showPassword,
      siteLogo,
      loginContent,
      aboutData,
      registrationEnabled,
      togglePassword,
      handleLogin
    }
  }
}
</script>

<style scoped>
@media (max-width: 767px) {
  .account { padding-top: 60px !important; }
  .login-centered-form { padding: 1.5rem !important; border-radius: 1.5rem !important; }
  .account-form__badge { font-size: 0.75rem !important; padding: 4px 12px !important; }
  .account-form__title { font-size: 1.25rem !important; margin-bottom: 1rem !important; }
  .form--label { font-size: 0.8rem !important; }
  .form--control { font-size: 0.9rem !important; padding: 0.65rem 1rem !important; border-radius: 0.75rem !important; }
  .btn--base.btn--lg { padding: 10px !important; font-size: 0.95rem !important; }
  .have-account__text { font-size: 0.85rem !important; }
  .account-logo img { max-height: 45px !important; }
}
</style>
