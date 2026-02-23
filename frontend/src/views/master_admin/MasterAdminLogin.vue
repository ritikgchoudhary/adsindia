<template>
  <div class="admin-login-page">
    <div class="login-container">
      <div class="login-card">
        <div class="login-header">
          <img :src="logoUrl" alt="Logo" class="login-logo mb-3" style="height: 60px;">
          <h2>Master Admin</h2>
          <p>Sign in to access master admin panel</p>
        </div>
        <form @submit.prevent="handleLogin">
          <div v-if="errorMsg" class="alert alert-danger mb-3 py-2 small">
            {{ errorMsg }}
          </div>
          <div class="form-group">
            <label>Username</label>
            <input type="text" v-model="formData.username" class="form-control" required autocomplete="username">
          </div>
          <div class="form-group">
            <label>Password</label>
            <input type="password" v-model="formData.password" class="form-control" required autocomplete="current-password">
          </div>
          <button type="submit" class="btn btn-primary w-100" :disabled="loading">
            {{ loading ? 'Logging in...' : 'Login' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../services/api'

export default {
  name: 'MasterAdminLogin',
  setup() {
    const router = useRouter()
    const loading = ref(false)
    const errorMsg = ref('')
    const logoUrl = ref('/assets/images/logo_icon/logo.png?v=' + new Date().getTime())
    const formData = ref({
      username: '',
      password: ''
    })

    const handleLogin = async () => {
      errorMsg.value = ''
      loading.value = true
      try {
        const response = await api.post('/admin/login', {
          username: formData.value.username.trim(),
          password: formData.value.password
        })
        const data = response?.data ?? {}
        const status = data.status
        const payload = data.data ?? data

        if (status === 'success') {
          const token = payload.token ?? payload.admin?.token ?? data.token
          if (token) {
            localStorage.setItem('admin_token', token)
            if (window.notify) window.notify('success', 'Login successful')
            router.push('/master_admin/dashboard')
          } else {
            errorMsg.value = 'Token not received. Please try again.'
          }
        } else {
          const msg = data.message?.error?.[0] ?? data.message?.error ?? (Array.isArray(data.message) ? data.message[0] : data.message) ?? 'Login failed'
          errorMsg.value = typeof msg === 'string' ? msg : 'Invalid username or password'
        }
      } catch (error) {
        console.error('Login error:', error)
        const res = error.response?.data
        const msg = res?.message?.error?.[0] ?? res?.message?.error ?? (Array.isArray(res?.message) ? res?.message[0] : res?.message)
        errorMsg.value = msg || error.message || 'Network error. Please try again.'
        if (window.notify) window.notify('error', errorMsg.value)
      } finally {
        loading.value = false
      }
    }

    return {
      formData,
      loading,
      errorMsg,
      handleLogin
    }
  }
}
</script>

<style scoped>
.admin-login-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
  padding: 20px;
}

.login-container {
  width: 100%;
  max-width: 400px;
}

.login-card {
  background: #1e293b;
  border-radius: 16px;
  padding: 40px;
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.4);
  border: 1px solid rgba(255, 255, 255, 0.08);
}

.login-header {
  text-align: center;
  margin-bottom: 30px;
}

.login-header h2 {
  font-weight: 700;
  color: #f1f5f9;
  margin-bottom: 10px;
}

.login-header p {
  color: #94a3b8;
  margin: 0;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  font-weight: 600;
  color: #cbd5e1;
  margin-bottom: 8px;
  display: block;
}

.form-control {
  width: 100%;
  padding: 12px 15px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 10px;
  font-size: 16px;
  background: #0f172a;
  color: #f1f5f9;
}

.form-control:focus {
  outline: none;
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
}

.btn-primary {
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  border: none;
  color: white;
  padding: 12px;
  border-radius: 8px;
  font-weight: 600;
  font-size: 16px;
  cursor: pointer;
  transition: transform 0.2s;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
