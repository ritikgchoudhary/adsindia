<template>
  <DashboardLayout page-title="2FA Security">
    <div class="card custom--card">
      <div class="card-body">
        <div class="row gy-4">
          <div v-if="!userTwoFactorEnabled" class="col-xxl-3 col-lg-5 col-md-6">
            <div class="setting-wrapper">
              <p class="text-white mb-3">
                Use the QR code or setup key on your Google Authenticator app to add your account.
              </p>
              <div class="qr-code text-center border border-dashed p-2 rounded">
                <img class="mx-auto" :src="qrCodeUrl" alt="QR" width="200">
              </div>
            </div>
          </div>

          <div v-if="!userTwoFactorEnabled" class="col-xxl-4 col-lg-5 col-md-6">
            <div class="setting-wrapper__right">
              <div class="form-group mb-3">
                <label class="form--label text-white fw-bold">Setup Key</label>
                <div class="input-group">
                  <input type="text" :value="secret" class="form-control form--control referralURL" readonly>
                  <button type="button" class="btn bg--base text-white" @click="copySecret" style="border-radius: 0 5px 5px 0;">
                    <i class="fas fa-copy"></i>
                  </button>
                </div>
              </div>

              <p class="text-white mt-3">
                Google Authenticator is a multifactor app for mobile devices. It generates timed codes used during the 2-step verification process. To use Google Authenticator, install the Google Authenticator application on your mobile device.
                <a class="text--base" href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2&hl=en" target="_blank">Download</a>
              </p>
            </div>
          </div>

          <div class="col-12 mt-5">
            <h5 v-if="userTwoFactorEnabled" class="text-white mb-3">Disable 2FA Security</h5>
            <h5 v-else class="text-white mb-3">Enable 2FA Security</h5>
            <form @submit.prevent="handleSubmit" class="twofa-enable-wrapper">
              <input type="hidden" name="key" :value="secret">
              <div class="form-group mb-3">
                <label class="form--label text-white" :class="{ 'fw-bold': !userTwoFactorEnabled }">
                  Google Authenticator OTP
                </label>
                <input type="text" v-model="otpCode" class="form--control" name="code" required>
              </div>
              <button type="submit" class="btn btn--base w-100">Submit</button>
            </form>
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

    const copySecret = () => {
      const copyText = document.querySelector(".referralURL")
      copyText.select()
      copyText.setSelectionRange(0, 99999)
      document.execCommand("copy")
      copyText.blur()
      
      if (window.notify) {
        window.notify('success', 'Setup key copied!')
      }
    }

    const handleSubmit = async () => {
      try {
        const endpoint = userTwoFactorEnabled.value ? '/twofactor/disable' : '/twofactor/enable'
        const response = await api.post(endpoint, {
          key: secret.value,
          code: otpCode.value
        })

        if (response.data.status === 'success') {
          if (window.notify) {
            window.notify('success', userTwoFactorEnabled.value ? '2FA disabled successfully!' : '2FA enabled successfully!')
          }
          userTwoFactorEnabled.value = !userTwoFactorEnabled.value
          otpCode.value = ''
          if (!userTwoFactorEnabled.value) {
            fetchTwoFactorData()
          }
        }
      } catch (error) {
        console.error('Error:', error)
        if (window.notify) {
          window.notify('error', error.response?.data?.message || 'Failed to update 2FA')
        }
      }
    }

    const fetchTwoFactorData = async () => {
      try {
        const response = await api.get('/twofactor')
        if (response.data.status === 'success' && response.data.data) {
          qrCodeUrl.value = response.data.data.qr_code_url || ''
          secret.value = response.data.data.secret || ''
          userTwoFactorEnabled.value = response.data.data.enabled || false
        }
      } catch (error) {
        console.error('Error loading 2FA data:', error)
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
      copySecret,
      handleSubmit
    }
  }
}
</script>

<style scoped>
.qr-code img {
  max-width: 100%;
  height: auto;
}

.border-dashed {
  border-style: dashed !important;
}

.twofa-enable-wrapper {
  background: linear-gradient(to bottom, #000000, #051d35);
  padding: 25px;
  border-radius: 12px;
}
</style>
