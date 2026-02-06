<template>
  <section class="account">
    <div class="account-inner">
      <div class="container">
        <div class="row gy-4 flex-wrap-reverse justify-content-between">
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

          <!-- Right Side - Login Form -->
          <div class="col-xl-6 ps-xl-5">
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
                <span class="account-form__badge">{{ loginContent?.heading || 'Welcome Back' }}</span>
                <h4 class="account-form__title">{{ loginContent?.subheading || 'Login to your account' }}</h4>
              </div>

              <form @submit.prevent="handleLogin" class="verify-gcaptcha">
                <div class="row">
                  <div class="col-sm-12">
                    <div class="form-group">
                      <label class="form--label">Username or Email</label>
                      <input 
                        v-model="form.username" 
                        type="text" 
                        name="username" 
                        class="form--control" 
                        required
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
                  {{ loading ? 'Logging in...' : 'Submit' }}
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
      username: '',
      password: '',
      remember: false
    })
    const loading = ref(false)
    const error = ref('')
    const showPassword = ref(false)
    const siteLogo = ref('/assets/images/logo.png')
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
        const response = await authService.login(form.value)
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

<style>
/* Styles are loaded from external CSS files */
</style>
