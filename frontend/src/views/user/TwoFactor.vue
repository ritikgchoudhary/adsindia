<template>
  <DashboardLayout page-title="2FA Security" :dark-theme="true">
    <div class="tw-grid tw-grid-cols-1 tw-justify-items-center">
      <div class="tw-w-full lg:tw-w-10/12 xl:tw-w-8/12">
        <div class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-slate-200 tw-overflow-hidden">
          <div class="tw-bg-slate-50 tw-p-6 tw-border-b tw-border-slate-200">
            <h5 class="tw-text-slate-900 tw-font-bold tw-text-lg tw-mb-1 tw-flex tw-items-center">
              <i class="fas fa-shield-alt tw-mr-2 tw-text-indigo-600"></i>Two-Factor Authentication (2FA)
            </h5>
            <p class="tw-text-slate-500 tw-text-sm tw-m-0">
              {{ userTwoFactorEnabled ? '2FA is currently enabled. Enter the code from your authenticator app to disable it.' : 'Add an extra layer of security by enabling 2FA with Google Authenticator.' }}
            </p>
          </div>
          
          <div class="tw-p-6">
            
            <div v-if="loading" class="tw-flex tw-flex-col tw-items-center tw-justify-center tw-py-12">
              <i class="fas fa-spinner fa-spin tw-text-indigo-600 tw-text-3xl tw-mb-4"></i>
              <p class="tw-text-slate-500">Loading security settings...</p>
            </div>
            <div v-else>
              <!-- Setup: QR + Secret (when 2FA is off) -->
              <div v-if="!userTwoFactorEnabled" class="tw-mb-8">
                <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-8">
                  <div>
                    <div class="tw-p-4 tw-border tw-border-dashed tw-border-slate-300 tw-rounded-xl tw-bg-slate-50 tw-text-center">
                      <p class="tw-text-slate-500 tw-text-sm tw-mb-4">Scan this QR code in your Google Authenticator app.</p>
                      <div class="tw-flex tw-justify-center tw-items-center tw-min-h-[200px] tw-bg-white tw-rounded-lg tw-p-4 tw-border tw-border-slate-200">
                         <img v-if="qrCodeUrl" :src="qrCodeUrl" alt="2FA QR Code" class="tw-max-w-[180px] tw-h-auto">
                         <div v-else class="tw-flex tw-flex-col tw-items-center tw-justify-center tw-text-slate-400">
                            <i class="fas fa-qrcode tw-text-4xl tw-mb-2"></i>
                            <span class="tw-text-sm">Loading QR...</span>
                         </div>
                      </div>
                    </div>
                  </div>
                  
                  <div class="tw-flex tw-flex-col tw-justify-center">
                    <div class="tw-mb-6">
                      <label class="tw-block tw-text-sm tw-font-bold tw-text-slate-700 tw-mb-2">Setup key (manual entry)</label>
                      <div class="tw-flex">
                        <input
                          ref="secretInputRef"
                          type="text"
                          :value="secret"
                          class="tw-flex-1 tw-px-4 tw-py-3 tw-bg-slate-50 tw-border tw-border-slate-300 tw-border-r-0 tw-rounded-l-xl tw-text-slate-600 tw-font-mono tw-text-sm focus:tw-outline-none"
                          readonly>
                        <button type="button" class="tw-px-4 tw-bg-slate-100 hover:tw-bg-slate-200 tw-border tw-border-l-0 tw-border-slate-300 tw-rounded-r-xl tw-text-slate-600 hover:tw-text-indigo-600 tw-transition-colors tw-cursor-pointer" title="Copy key" @click="copySecret">
                          <i class="fas fa-copy"></i>
                        </button>
                      </div>
                    </div>
                    
                    <div class="tw-bg-indigo-50 tw-border tw-border-indigo-100 tw-rounded-xl tw-p-4">
                      <p class="tw-text-indigo-800 tw-text-sm tw-m-0 tw-leading-relaxed">
                        <i class="fas fa-info-circle tw-mr-1"></i>
                        Install
                        <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2" target="_blank" rel="noopener" class="tw-font-bold tw-text-indigo-600 hover:tw-underline">Google Authenticator</a>
                        on your device, then scan the QR code or enter the key above.
                      </p>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Enable / Disable Form -->
              <div class="tw-border-t tw-border-slate-200 tw-pt-6">
                <h6 class="tw-font-bold tw-text-slate-900 tw-mb-4">{{ userTwoFactorEnabled ? 'Disable 2FA' : 'Verify and enable 2FA' }}</h6>
                <form @submit.prevent="handleSubmit" class="tw-max-w-sm">
                  <input type="hidden" name="key" :value="secret">
                  <div class="tw-mb-4">
                     <label class="tw-block tw-text-sm tw-font-bold tw-text-slate-700 tw-mb-2" for="otp_code">Authenticator code (OTP)</label>
                     <input
                       id="otp_code"
                       v-model="otpCode"
                       type="text"
                       inputmode="numeric"
                       autocomplete="one-time-code"
                       class="tw-w-full tw-px-4 tw-py-3 tw-bg-white tw-border tw-border-slate-300 tw-rounded-xl focus:tw-outline-none focus:tw-border-indigo-500 focus:tw-ring-4 focus:tw-ring-indigo-500/10 tw-transition-all tw-tracking-widest tw-font-bold tw-text-center"
                       name="code"
                       placeholder="000 000"
                       maxlength="6"
                       required>
                  </div>
                  
                  <button type="submit" class="tw-px-8 tw-py-3 tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-font-bold tw-rounded-xl tw-shadow-lg tw-shadow-indigo-500/30 tw-transition-all tw-flex tw-items-center tw-justify-center tw-gap-2 disabled:tw-opacity-60 disabled:tw-cursor-not-allowed tw-border-0 tw-cursor-pointer" :disabled="submitting">
                    <i v-if="submitting" class="fas fa-spinner fa-spin tw-mr-2"></i>
                    <i v-else class="fas tw-mr-2" :class="userTwoFactorEnabled ? 'fa-ban' : 'fa-check'"></i>
                    {{ userTwoFactorEnabled ? 'Disable 2FA' : 'Enable 2FA' }}
                  </button>
                </form>
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
import DashboardLayout from '../../components/DashboardLayout.vue'
import api from '../../services/api'

export default {
  name: 'TwoFactor',
  components: {
    DashboardLayout
  },
  setup() {
    const qrCodeUrl = ref('')
    const secret = ref('')
    const otpCode = ref('')
    const userTwoFactorEnabled = ref(false)
    const loading = ref(true)
    const submitting = ref(false)
    const secretInputRef = ref(null)

    const copySecret = () => {
      const el = secretInputRef.value
      if (el) {
        el.select()
        el.setSelectionRange(0, 99999)
        try {
          navigator.clipboard.writeText(secret.value)
          if (window.notify) window.notify('success', 'Setup key copied!')
        } catch {
          document.execCommand('copy')
          if (window.notify) window.notify('success', 'Setup key copied!')
        }
        el.blur()
      }
    }

    const handleSubmit = async () => {
      submitting.value = true
      try {
        const endpoint = userTwoFactorEnabled.value ? '/twofactor/disable' : '/twofactor/enable'
        const response = await api.post(endpoint, {
          key: secret.value,
          code: otpCode.value
        })
        if (response.data?.status === 'success') {
          if (window.notify) window.notify('success', userTwoFactorEnabled.value ? '2FA disabled successfully!' : '2FA enabled successfully!')
          userTwoFactorEnabled.value = !userTwoFactorEnabled.value
          otpCode.value = ''
          if (!userTwoFactorEnabled.value) {
            await fetchTwoFactorData()
          }
        }
      } catch (error) {
        console.error('Error:', error)
        if (window.notify) window.notify('error', error.response?.data?.message || 'Failed to update 2FA')
      } finally {
        submitting.value = false
      }
    }

    const fetchTwoFactorData = async () => {
      loading.value = true
      try {
        const response = await api.get('/twofactor')
        if (response.data?.status === 'success' && response.data.data) {
          const data = response.data.data
          qrCodeUrl.value = data.qr_code_url || ''
          secret.value = data.secret || ''
          userTwoFactorEnabled.value = data.enabled || false
        }
      } catch (error) {
        console.error('Error loading 2FA data:', error)
      } finally {
        loading.value = false
      }
    }

    onMounted(() => {
      fetchTwoFactorData()
    })

    return {
      qrCodeUrl,
      secret,
      otpCode,
      userTwoFactorEnabled,
      loading,
      submitting,
      secretInputRef,
      copySecret,
      handleSubmit
    }
  }
}
</script>

<style scoped>
@media (max-width: 640px) {
  /* Header section */
  .tw-p-6 { padding: 1.15rem !important; }
  h5.tw-text-lg { font-size: 1.1rem !important; }
  p.tw-text-sm { font-size: 0.8rem !important; }
  
  /* QR Code section */
  .tw-grid.tw-grid-cols-1.md\:tw-grid-cols-2 { gap: 1.25rem !important; }
  .tw-p-4.tw-border { padding: 1rem !important; border-radius: 1rem !important; }
  .tw-min-h-\[200px\] { min-height: 150px !important; }
  img.tw-max-w-\[180px\] { max-width: 140px !important; }
  
  /* Inputs & Keys */
  label.tw-text-sm { font-size: 0.8rem !important; margin-bottom: 0.35rem !important; }
  input.tw-py-3 { padding: 0.65rem 0.85rem !important; font-size: 0.85rem !important; border-radius: 0.85rem 0 0 0.85rem !important; }
  .tw-rounded-r-xl { border-radius: 0 0.85rem 0.85rem 0 !important; }
  .tw-rounded-l-xl { border-radius: 0.85rem 0 0 0.85rem !important; }
  
  /* Info box */
  .tw-p-4.tw-bg-indigo-50 { padding: 0.85rem !important; border-radius: 0.85rem !important; }
  .tw-text-indigo-800.tw-text-sm { font-size: 0.75rem !important; }
  
  /* OTP Form */
  .tw-border-t.tw-border-slate-200.tw-pt-6 { pt: 4 !important; }
  h6.tw-text-slate-900 { font-size: 0.95rem !important; margin-bottom: 0.75rem !important; }
  input#otp_code { padding: 0.75rem !important; font-size: 1.15rem !important; border-radius: 0.85rem !important; }
  
  /* Submit button */
  .tw-px-8.tw-py-3 { padding: 0.75rem !important; width: 100% !important; font-size: 1rem !important; border-radius: 0.85rem !important; }
}
</style>
