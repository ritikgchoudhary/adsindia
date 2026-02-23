<template>
  <DashboardLayout page-title="Change Password" :dark-theme="true">
    <div class="tw-grid tw-grid-cols-1 tw-justify-items-center">
      <div class="tw-w-full lg:tw-w-2/3 xl:tw-w-1/2">
        <div class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-slate-200 tw-overflow-hidden">
          <div class="tw-bg-slate-50 tw-p-6 tw-border-b tw-border-slate-200">
            <h5 class="tw-text-slate-900 tw-font-bold tw-text-lg tw-mb-1 tw-flex tw-items-center">
              <i class="fas fa-lock tw-mr-2 tw-text-indigo-600"></i>Change Password
            </h5>
            <p class="tw-text-slate-500 tw-text-sm tw-m-0">
              Update your account password. Use a strong password that you don’t use elsewhere.
            </p>
          </div>
          <div class="tw-p-6">
            <form @submit.prevent="handleSubmit" class="tw-flex tw-flex-col tw-gap-5">
              
              <div>
                <label class="tw-block tw-text-sm tw-font-bold tw-text-slate-700 tw-mb-2" for="current_password">Current Password</label>
                <div class="tw-relative">
                  <input
                    id="current_password"
                    v-model="form.current_password"
                    :type="showCurrentPassword ? 'text' : 'password'"
                    class="tw-w-full tw-pl-4 tw-pr-12 tw-py-3 tw-bg-white tw-border tw-border-slate-300 tw-rounded-xl focus:tw-outline-none focus:tw-border-indigo-500 focus:tw-ring-4 focus:tw-ring-indigo-500/10 tw-transition-all"
                    name="current_password"
                    placeholder="Enter your current password"
                    required
                    autocomplete="current-password"
                  >
                  <button 
                    type="button" 
                    class="tw-absolute tw-right-2 tw-top-1/2 -tw-translate-y-1/2 tw-w-9 tw-h-9 tw-flex tw-items-center tw-justify-center tw-text-slate-400 hover:tw-text-indigo-600 hover:tw-bg-indigo-50 tw-rounded-lg tw-transition-colors tw-border-0 tw-cursor-pointer" 
                    :aria-label="showCurrentPassword ? 'Hide password' : 'Show password'" 
                    @click="showCurrentPassword = !showCurrentPassword"
                  >
                    <i class="fas" :class="showCurrentPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                  </button>
                </div>
              </div>

              <div>
                <label class="tw-block tw-text-sm tw-font-bold tw-text-slate-700 tw-mb-2" for="password">New Password</label>
                <div class="tw-relative">
                  <input
                    id="password"
                    v-model="form.password"
                    :type="showPassword ? 'text' : 'password'"
                    class="tw-w-full tw-pl-4 tw-pr-12 tw-py-3 tw-bg-white tw-border tw-border-slate-300 tw-rounded-xl focus:tw-outline-none focus:tw-border-indigo-500 focus:tw-ring-4 focus:tw-ring-indigo-500/10 tw-transition-all"
                    name="password"
                    placeholder="Enter new password"
                    required
                    autocomplete="new-password"
                  >
                  <button 
                    type="button" 
                    class="tw-absolute tw-right-2 tw-top-1/2 -tw-translate-y-1/2 tw-w-9 tw-h-9 tw-flex tw-items-center tw-justify-center tw-text-slate-400 hover:tw-text-indigo-600 hover:tw-bg-indigo-50 tw-rounded-lg tw-transition-colors tw-border-0 tw-cursor-pointer" 
                    :aria-label="showPassword ? 'Hide password' : 'Show password'" 
                    @click="showPassword = !showPassword"
                  >
                    <i class="fas" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                  </button>
                </div>
              </div>

              <div>
                <label class="tw-block tw-text-sm tw-font-bold tw-text-slate-700 tw-mb-2" for="password_confirmation">Confirm New Password</label>
                <div class="tw-relative">
                  <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    :type="showConfirmPassword ? 'text' : 'password'"
                    class="tw-w-full tw-pl-4 tw-pr-12 tw-py-3 tw-bg-white tw-border tw-border-slate-300 tw-rounded-xl focus:tw-outline-none focus:tw-border-indigo-500 focus:tw-ring-4 focus:tw-ring-indigo-500/10 tw-transition-all"
                    name="password_confirmation"
                    placeholder="Confirm new password"
                    required
                    autocomplete="new-password"
                  >
                  <button 
                    type="button" 
                    class="tw-absolute tw-right-2 tw-top-1/2 -tw-translate-y-1/2 tw-w-9 tw-h-9 tw-flex tw-items-center tw-justify-center tw-text-slate-400 hover:tw-text-indigo-600 hover:tw-bg-indigo-50 tw-rounded-lg tw-transition-colors tw-border-0 tw-cursor-pointer" 
                    :aria-label="showConfirmPassword ? 'Hide password' : 'Show password'" 
                    @click="showConfirmPassword = !showConfirmPassword"
                  >
                    <i class="fas" :class="showConfirmPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                  </button>
                </div>
              </div>

              <div class="tw-mt-4">
                <button 
                  type="submit" 
                  class="tw-w-full tw-py-3.5 tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-font-bold tw-rounded-xl tw-shadow-lg tw-shadow-indigo-500/30 tw-transition-all tw-flex tw-items-center tw-justify-center tw-gap-2 disabled:tw-opacity-60 disabled:tw-cursor-not-allowed tw-border-0 tw-cursor-pointer" 
                  :disabled="submitting"
                >
                  <i v-if="submitting" class="fas fa-spinner fa-spin tw-mr-2"></i>
                  <i v-else class="fas fa-key tw-mr-2"></i>
                  {{ submitting ? 'Updating...' : 'Change Password' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script>
import { ref } from 'vue'
import DashboardLayout from '../../components/DashboardLayout.vue'
import api from '../../services/api'

export default {
  name: 'ChangePassword',
  components: {
    DashboardLayout
  },
  setup() {
    const form = ref({
      current_password: '',
      password: '',
      password_confirmation: ''
    })
    const showCurrentPassword = ref(false)
    const showPassword = ref(false)
    const showConfirmPassword = ref(false)
    const submitting = ref(false)

    const handleSubmit = async () => {
      submitting.value = true
      try {
        const response = await api.post('/change-password', form.value)
        if (response.data?.status === 'success') {
          if (window.notify) window.notify('success', 'Password changed successfully!')
          form.value = {
            current_password: '',
            password: '',
            password_confirmation: ''
          }
        }
      } catch (error) {
        console.error('Error changing password:', error)
        if (window.notify) window.notify('error', error.response?.data?.message || 'Failed to change password')
      } finally {
        submitting.value = false
      }
    }

    return {
      form,
      showCurrentPassword,
      showPassword,
      showConfirmPassword,
      submitting,
      handleSubmit
    }
  }
}
</script>
