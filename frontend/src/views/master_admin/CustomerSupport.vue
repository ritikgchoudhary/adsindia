<template>
  <MasterAdminLayout page-title="Customer Support Links">
    <div class="ma-courses">
      <div class="ma-card mb-4">
        <div class="ma-card__header ma-card__header--gradient">
          <div>
            <h5 class="ma-card__title"><i class="fas fa-headset me-2"></i>Customer Support (User Panel)</h5>
            <p class="ma-card__subtitle">Set Telegram, WhatsApp and Live Chat links. These values appear on User → Customer Support page.</p>
          </div>
        </div>
        <div class="ma-card__body">
          <div v-if="loadError" class="alert alert-danger mb-0">
            <i class="fas fa-exclamation-circle me-2"></i>{{ loadError }}
            <button type="button" class="btn btn-sm btn-outline-danger ms-2" @click="fetchLinks">Retry</button>
          </div>

          <form v-else @submit.prevent="save" class="ma-form">
            <div class="ma-form-group">
              <label class="ma-form-label">Telegram Link</label>
              <input
                v-model="form.telegram_link"
                type="url"
                class="ma-form-input"
                placeholder="https://t.me/YourGroup"
              >
              <small class="text-muted">Full URL, e.g. https://t.me/AdsSkillIndia</small>
            </div>
            <div class="ma-form-group">
              <label class="ma-form-label">Telegram Group Link</label>
              <input
                v-model="form.telegram_group_link"
                type="url"
                class="ma-form-input"
                placeholder="https://t.me/YourTelegramGroup"
              >
              <small class="text-muted">This shows as a separate “Telegram Group” option in user panel.</small>
            </div>
            <div class="ma-form-group">
              <label class="ma-form-label">WhatsApp Link</label>
              <input
                v-model="form.whatsapp_link"
                type="url"
                class="ma-form-input"
                placeholder="https://wa.me/919876543210"
              >
              <small class="text-muted">Full URL with country code, e.g. https://wa.me/919876543210</small>
            </div>
            <div class="ma-form-group">
              <label class="ma-form-label">Live Chat URL</label>
              <input
                v-model="form.live_chat_url"
                type="url"
                class="ma-form-input"
                placeholder="https://tawk.to/chat/your-widget or leave empty"
              >
              <small class="text-muted">Optional. URL that opens when user clicks "Start Live Chat".</small>
            </div>
            <div class="ma-form-actions">
              <button type="submit" class="ma-btn ma-btn--primary">
                Save Support Links
              </button>
              <span v-if="saveMessage" class="ms-3" :class="saveSuccess ? 'text-success' : 'text-danger'">{{ saveMessage }}</span>
            </div>
          </form>
        </div>
      </div>
    </div>
  </MasterAdminLayout>
</template>

<script>
import { ref, reactive, onMounted } from 'vue'
import MasterAdminLayout from '../../components/master_admin/MasterAdminLayout.vue'
import api from '../../services/api'

export default {
  name: 'MasterAdminCustomerSupport',
  components: { MasterAdminLayout },
  setup() {
    const form = reactive({
      telegram_link: '',
      telegram_group_link: '',
      whatsapp_link: '',
      live_chat_url: ''
    })
    const loading = ref(true)
    const saving = ref(false)
    const loadError = ref('')
    const saveMessage = ref('')
    const saveSuccess = ref(false)

    const fetchLinks = async () => {
      loadError.value = ''
      loading.value = true
      try {
        const res = await api.get('/admin/support-links')
        if (res.data?.status === 'success' && res.data.data) {
          form.telegram_link = res.data.data.telegram_link || ''
          form.telegram_group_link = res.data.data.telegram_group_link || ''
          form.whatsapp_link = res.data.data.whatsapp_link || ''
          form.live_chat_url = res.data.data.live_chat_url || ''
        }
      } catch (e) {
        loadError.value = e.response?.data?.message?.[0] || e.message || 'Failed to load support links'
      } finally {
        loading.value = false
      }
    }

    const save = async () => {
      saving.value = true
      saveMessage.value = ''
      try {
        const res = await api.post('/admin/support-links', {
          telegram_link: form.telegram_link || '',
          telegram_group_link: form.telegram_group_link || '',
          whatsapp_link: form.whatsapp_link || '',
          live_chat_url: form.live_chat_url || ''
        })
        if (res.data?.status === 'success') {
          saveSuccess.value = true
          saveMessage.value = 'Saved successfully. User Customer Support page will show these values.'
          setTimeout(() => { saveMessage.value = '' }, 4000)
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

    onMounted(() => {
      fetchLinks()
    })

    return {
      form,
      loading,
      saving,
      loadError,
      saveMessage,
      saveSuccess,
      fetchLinks,
      save
    }
  }
}
</script>

<style scoped>
.ma-form { max-width: 600px; }
.ma-form-group { margin-bottom: 1.25rem; }
.ma-form-label { display: block; font-weight: 600; color: #e2e8f0; margin-bottom: 0.35rem; }
.ma-form-input {
  width: 100%;
  padding: 0.6rem 0.75rem;
  border-radius: 8px;
  border: 1px solid rgba(255,255,255,0.15);
  background: rgba(30, 41, 59, 0.5);
  color: #f1f5f9;
}
.ma-form-input::placeholder { color: #64748b; }
.ma-form-input:focus {
  outline: none;
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
}
.ma-form-actions { margin-top: 1.5rem; display: flex; align-items: center; flex-wrap: wrap; gap: 0.5rem; }
</style>
