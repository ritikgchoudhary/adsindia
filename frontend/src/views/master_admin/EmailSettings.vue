<template>
  <MasterAdminLayout page-title="Email Settings">
    <div class="ma-settings">
      <div class="ma-card mb-4">
        <div class="ma-card__header ma-card__header--gradient">
          <div>
            <h5 class="ma-card__title"><i class="fas fa-envelope me-2"></i>Email Configuration</h5>
            <p class="ma-card__subtitle">Configure how the system sends emails (SMTP Recommended).</p>
          </div>
        </div>
        <div class="ma-card__body">
          <div v-if="loadError" class="alert alert-danger mb-4">
            <i class="fas fa-exclamation-circle me-2"></i>{{ loadError }}
            <button type="button" class="btn btn-sm btn-outline-danger ms-2" @click="fetchSettings">Retry</button>
          </div>

          <form @submit.prevent="saveSettings" class="ma-form">
            <div class="ma-form-group">
              <label class="ma-form-label">Email From Address</label>
              <input
                v-model="form.email_from"
                type="email"
                class="ma-form-input"
                placeholder="support@adsskillindia.in"
                required
              >
              <small class="text-muted">Emails will be sent from this address.</small>
            </div>

            <div class="ma-form-group">
              <label class="ma-form-label">Send Method</label>
              <select v-model="form.mail_method" class="ma-form-input">
                <option value="php">PHP Mail (Default)</option>
                <option value="smtp">SMTP (Recommended)</option>
              </select>
            </div>

            <div v-if="form.mail_method === 'smtp'" class="smtp-fields mt-4 animate__animated animate__fadeIn">
              <div class="row g-3">
                <div class="col-md-9">
                  <div class="ma-form-group">
                    <label class="ma-form-label">SMTP Host</label>
                    <input v-model="form.smtp_host" type="text" class="ma-form-input" placeholder="e.g. mail.adsskillindia.in">
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="ma-form-group">
                    <label class="ma-form-label">Port</label>
                    <input v-model="form.smtp_port" type="text" class="ma-form-input" placeholder="465">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="ma-form-group">
                    <label class="ma-form-label">Username</label>
                    <input v-model="form.smtp_username" type="text" class="ma-form-input" placeholder="support@adsskillindia.in">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="ma-form-group">
                    <label class="ma-form-label">Password</label>
                    <input v-model="form.smtp_password" type="password" class="ma-form-input" placeholder="Enter password">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="ma-form-group">
                    <label class="ma-form-label">Encryption</label>
                    <select v-model="form.smtp_encryption" class="ma-form-input">
                      <option value="ssl">SSL (Port 465)</option>
                      <option value="tls">TLS (Port 587)</option>
                      <option value="">None</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <div class="ma-form-actions mt-4">
              <button type="submit" class="ma-btn ma-btn--primary" :disabled="saving">
                <i v-if="saving" class="fas fa-spinner fa-spin me-2"></i>
                Save Configuration
              </button>
              <span v-if="saveMessage" class="ms-3" :class="saveSuccess ? 'text-success' : 'text-danger'">
                <i :class="saveSuccess ? 'fas fa-check-circle' : 'fas fa-times-circle'" class="me-1"></i>
                {{ saveMessage }}
              </span>
            </div>
          </form>
        </div>
      </div>
    </div>
  </MasterAdminLayout>
</template>

<script>
import MasterAdminLayout from '../../components/master_admin/MasterAdminLayout.vue'
import { ref, reactive, onMounted } from 'vue'
import api from '../../services/api'

export default {
  name: 'MasterAdminEmailSettings',
  components: { MasterAdminLayout },
  setup() {
    const form = reactive({
      email_from: '',
      mail_method: 'php',
      smtp_host: '',
      smtp_port: '',
      smtp_username: '',
      smtp_password: '',
      smtp_encryption: 'ssl'
    })

    const saving = ref(false)
    const loadError = ref('')
    const saveMessage = ref('')
    const saveSuccess = ref(false)

    const fetchSettings = async () => {
      loadError.value = ''
      try {
        const res = await api.get('/admin/email-settings')
        if (res.data?.status === 'success' && res.data.data) {
          const data = res.data.data
          form.email_from = data.email_from || ''
          
          if (data.mail_config) {
            form.mail_method = data.mail_config.name || 'php'
            if (form.mail_method === 'smtp') {
              form.smtp_host = data.mail_config.host || ''
              form.smtp_port = data.mail_config.port || ''
              form.smtp_username = data.mail_config.username || ''
              form.smtp_password = data.mail_config.password || ''
              form.smtp_encryption = data.mail_config.enc || 'ssl'
            }
          }
        }
      } catch (e) {
        loadError.value = 'Failed to load email settings. ' + (e.response?.data?.message?.[0] || e.message)
      }
    }

    const saveSettings = async () => {
      saving.value = true
      saveMessage.value = ''
      try {
        const res = await api.post('/admin/email-settings', form)
        if (res.data?.status === 'success') {
          saveSuccess.value = true
          saveMessage.value = 'Settings saved successfully.'
          setTimeout(() => { saveMessage.value = '' }, 5000)
        } else {
          saveSuccess.value = false
          saveMessage.value = res.data?.message?.[0] || 'Save failed'
        }
      } catch (e) {
        saveSuccess.value = false
        saveMessage.value = e.response?.data?.message?.[0] || e.message || 'Failed to save'
      } finally {
        saving.value = false
      }
    }

    onMounted(fetchSettings)

    return {
      form,
      saving,
      loadError,
      saveMessage,
      saveSuccess,
      fetchSettings,
      saveSettings
    }
  }
}
</script>

<style scoped>
.ma-form { max-width: 800px; }
.ma-form-group { margin-bottom: 1.25rem; }
.ma-form-label { display: block; font-weight: 600; color: #e2e8f0; margin-bottom: 0.35rem; font-size: 0.9rem; }
.ma-form-input {
  width: 100%;
  padding: 0.75rem 0.9rem;
  border-radius: 10px;
  border: 1px solid rgba(255,255,255,0.15);
  background: rgba(30, 41, 59, 0.4);
  color: #f1f5f9;
  transition: all 0.2s;
}
.ma-form-input:focus {
  outline: none;
  border-color: #6366f1;
  background: rgba(30, 41, 59, 0.6);
  box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
}
.ma-btn {
  padding: 0.6rem 1.2rem;
  border-radius: 10px;
  font-weight: 600;
  transition: all 0.2s;
}
.ma-btn--primary {
  background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
  color: white;
  border: none;
}
.ma-btn--primary:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}
.ma-card {
  background: rgba(15, 23, 42, 0.6);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 20px;
  overflow: hidden;
}
.ma-card__header--gradient {
  padding: 1.5rem;
  background: linear-gradient(to right, rgba(99, 102, 241, 0.1), transparent);
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}
.ma-card__title { font-size: 1.1rem; font-weight: 700; color: white; margin: 0; }
.ma-card__subtitle { font-size: 0.85rem; color: rgba(255, 255, 255, 0.5); margin: 0.25rem 0 0 0; }
.ma-card__body { padding: 1.5rem; }
</style>
