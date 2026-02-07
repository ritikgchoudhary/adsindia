<template>
  <DashboardLayout page-title="Change Password">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card custom--card">
          <div class="card-body">
            <form @submit.prevent="handleSubmit">
              <div class="form-group">
                <label class="form--label">Old Password</label>
                <div class="position-relative">
                  <input id="current_password" :type="showCurrentPassword ? 'text' : 'password'" v-model="form.current_password" class="form-control form--control" name="current_password" required>
                  <span class="password-show-hide fas" :class="showCurrentPassword ? 'fa-eye-slash' : 'fa-eye'" @click="showCurrentPassword = !showCurrentPassword" style="cursor: pointer;"></span>
                </div>
              </div>

              <div class="form-group">
                <label class="form--label">New Password</label>
                <div class="position-relative">
                  <input id="password" :type="showPassword ? 'text' : 'password'" v-model="form.password" class="form-control form--control" name="password" required>
                  <span class="password-show-hide fas" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'" @click="showPassword = !showPassword" style="cursor: pointer;"></span>
                </div>
              </div>

              <div class="form-group">
                <label class="form--label">Confirm Password</label>
                <div class="position-relative">
                  <input id="password_confirmation" :type="showConfirmPassword ? 'text' : 'password'" v-model="form.password_confirmation" class="form-control form--control" name="password_confirmation" required>
                  <span class="password-show-hide fas" :class="showConfirmPassword ? 'fa-eye-slash' : 'fa-eye'" @click="showConfirmPassword = !showConfirmPassword" style="cursor: pointer;"></span>
                </div>
              </div>

              <div class="mt-3">
                <button type="submit" class="btn btn--base w-100">Change Password</button>
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

    const handleSubmit = async () => {
      try {
        const response = await api.post('/change-password', form.value)
        if (response.data.status === 'success') {
          if (window.notify) {
            window.notify('success', 'Password changed successfully!')
          }
          form.value = {
            current_password: '',
            password: '',
            password_confirmation: ''
          }
        }
      } catch (error) {
        console.error('Error changing password:', error)
        if (window.notify) {
          window.notify('error', error.response?.data?.message || 'Failed to change password')
        }
      }
    }

    return {
      form,
      showCurrentPassword,
      showPassword,
      showConfirmPassword,
      handleSubmit
    }
  }
}
</script>

<style scoped>
.password-show-hide {
  position: absolute;
  right: 15px;
  top: 50%;
  transform: translateY(-50%);
  cursor: pointer;
}
</style>
