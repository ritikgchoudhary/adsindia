<template>
  <div class="admin-login-page">
    <div class="login-container">
      <div class="login-card">
        <div class="login-header">
          <img :src="logoUrl" alt="Logo" class="login-logo mb-3" style="height: 60px;">
          <h2>Admin Login</h2>
          <p>Sign in to access admin panel</p>
        </div>
        <form @submit.prevent="handleLogin">
          <div class="form-group">
            <label>Username</label>
            <input type="text" v-model="formData.username" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Password</label>
            <input type="password" v-model="formData.password" class="form-control" required>
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
  name: 'AdminLogin',
  setup() {
    const router = useRouter()
    const loading = ref(false)
    const logoUrl = ref('/assets/images/logo_icon/logo.png?v=' + new Date().getTime())
    const formData = ref({
      username: '',
      password: ''
    })

    const handleLogin = async () => {
      loading.value = true
      try {
        const response = await api.post('/admin/login', formData.value)
        if (response.data.status === 'success') {
          // Extract token from response
          const token = response.data.data?.token || response.data.data?.admin?.token || response.data.token
          if (token) {
            localStorage.setItem('admin_token', token)
            if (window.notify) {
              window.notify('success', 'Login successful')
            }
            router.push('/admin/dashboard')
          } else {
            throw new Error('Token not received')
          }
        } else {
          throw new Error(response.data.message?.error?.[0] || 'Login failed')
        }
      } catch (error) {
        console.error('Login error:', error)
        const errorMessage = error.response?.data?.message?.error?.[0] || 
                            error.response?.data?.message || 
                            error.message || 
                            'Login failed'
        if (window.notify) {
          window.notify('error', errorMessage)
        }
      } finally {
        loading.value = false
      }
    }

    return {
      formData,
      loading,
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
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 20px;
}

.login-container {
  width: 100%;
  max-width: 400px;
}

.login-card {
  background: white;
  border-radius: 15px;
  padding: 40px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
}

.login-header {
  text-align: center;
  margin-bottom: 30px;
}

.login-header h2 {
  font-weight: 700;
  color: #2d3748;
  margin-bottom: 10px;
}

.login-header p {
  color: #718096;
  margin: 0;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  font-weight: 600;
  color: #4a5568;
  margin-bottom: 8px;
  display: block;
}

.form-control {
  width: 100%;
  padding: 12px 15px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 16px;
}

.form-control:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.btn-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
