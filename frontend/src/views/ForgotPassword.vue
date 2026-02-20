<template>
  <section class="account">
    <div class="account-inner">
      <div class="container">
        <div class="row gy-4 flex-wrap-reverse justify-content-center">
          <div class="col-lg-6 d-xl-block d-none pe-xl-5">
            <router-link to="/" class="account-logo">
              <img :src="siteLogo" alt="logo">
            </router-link>
          </div>
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
                <span class="account-form__badge">Reset Password</span>
                <h4 class="account-form__title">Forgot your password?</h4>
              </div>
              <form @submit.prevent="handleSubmit" v-if="!codeSent">
                <div class="row">
                  <div class="col-sm-12">
                    <div class="form-group">
                      <label class="form--label">Email Address</label>
                      <input v-model="form.email" type="email" class="form--control" required>
                    </div>
                  </div>
                  <div class="col-sm-12 mt-4">
                    <button type="submit" class="btn btn--base w-100 btn--lg" :disabled="loading">
                      Send Reset Code
                    </button>
                  </div>
                </div>
              </form>
              <form @submit.prevent="handleVerifyCode" v-else-if="!codeVerified">
                <div class="row">
                  <div class="col-sm-12">
                    <div class="form-group">
                      <label class="form--label">Verification Code</label>
                      <input v-model="form.code" type="text" class="form--control" required>
                    </div>
                  </div>
                  <div class="col-sm-12 mt-4">
                    <button type="submit" class="btn btn--base w-100 btn--lg" :disabled="loading">
                      Verify Code
                    </button>
                  </div>
                </div>
              </form>
              <form @submit.prevent="handleReset" v-else>
                <div class="row">
                  <div class="col-sm-12">
                    <div class="form-group">
                      <label class="form--label">New Password</label>
                      <input v-model="form.password" type="password" class="form--control" required>
                    </div>
                  </div>
                  <div class="col-sm-12">
                    <div class="form-group">
                      <label class="form--label">Confirm Password</label>
                      <input v-model="form.password_confirmation" type="password" class="form--control" required>
                    </div>
                  </div>
                  <div class="col-sm-12 mt-4">
                    <button type="submit" class="btn btn--base w-100 btn--lg" :disabled="loading">
                      Reset Password
                    </button>
                  </div>
                </div>
              </form>
              <div class="mt-3">
                <div class="have-account text-center">
                  <p class="have-account__text">
                    Remember your password?
                    <router-link to="/login" class="have-account__link">Login Now</router-link>
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
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'

export default {
  name: 'ForgotPassword',
  setup() {
    const router = useRouter()
    const form = ref({
      email: '',
      code: '',
      password: '',
      password_confirmation: ''
    })
    const loading = ref(false)
    const codeSent = ref(false)
    const codeVerified = ref(false)
    const siteLogo = ref('/assets/images/logo_icon/logo.png?v=' + new Date().getTime())

    const handleSubmit = async () => {
      loading.value = true
      try {
        const response = await api.post('/password/email', { email: form.value.email })
        if (response.data.status === 'success') {
          codeSent.value = true
          if (window.notify) {
            window.notify('success', 'Verification code sent to your email')
          }
        }
      } catch (error) {
        if (window.notify) {
          window.notify('error', error.response?.data?.message || 'Failed to send code')
        }
      } finally {
        loading.value = false
      }
    }

    const handleVerifyCode = async () => {
      loading.value = true
      try {
        const response = await api.post('/password/verify-code', {
          email: form.value.email,
          code: form.value.code
        })
        if (response.data.status === 'success') {
          codeVerified.value = true
          if (window.notify) {
            window.notify('success', 'Code verified successfully')
          }
        }
      } catch (error) {
        if (window.notify) {
          window.notify('error', error.response?.data?.message || 'Invalid code')
        }
      } finally {
        loading.value = false
      }
    }

    const handleReset = async () => {
      if (form.value.password !== form.value.password_confirmation) {
        if (window.notify) {
          window.notify('error', 'Passwords do not match')
        }
        return
      }
      loading.value = true
      try {
        const response = await api.post('/password/reset', {
          email: form.value.email,
          code: form.value.code,
          password: form.value.password,
          password_confirmation: form.value.password_confirmation
        })
        if (response.data.status === 'success') {
          if (window.notify) {
            window.notify('success', 'Password reset successfully')
          }
          router.push('/login')
        }
      } catch (error) {
        if (window.notify) {
          window.notify('error', error.response?.data?.message || 'Failed to reset password')
        }
      } finally {
        loading.value = false
      }
    }

    return {
      form,
      loading,
      codeSent,
      codeVerified,
      siteLogo,
      handleSubmit,
      handleVerifyCode,
      handleReset
    }
  }
}
</script>

<style>
/* Styles are loaded from external CSS files */
</style>
