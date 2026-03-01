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
            <!-- Telegram Link -->
            <div class="ma-form-group ma-card-variant">
              <div class="tw-flex tw-items-center tw-justify-between tw-mb-2">
                <label class="ma-form-label tw-mb-0">Telegram Link</label>
                <label class="ma-switch">
                  <input type="checkbox" v-model="form.is_telegram_enabled">
                  <span class="ma-slider round"></span>
                </label>
              </div>
              <input
                v-model="form.telegram_link"
                type="url"
                class="ma-form-input"
                placeholder="https://t.me/YourGroup"
                :disabled="!form.is_telegram_enabled"
                :class="{ 'tw-opacity-50': !form.is_telegram_enabled }"
              >
              <small class="text-muted tw-block tw-mt-1">Full URL, e.g. https://t.me/AdsSkillIndia</small>
            </div>

            <!-- Telegram Group Link -->
            <div class="ma-form-group ma-card-variant">
              <div class="tw-flex tw-items-center tw-justify-between tw-mb-2">
                <label class="ma-form-label tw-mb-0">Telegram Group Link</label>
                <label class="ma-switch">
                  <input type="checkbox" v-model="form.is_telegram_group_enabled">
                  <span class="ma-slider round"></span>
                </label>
              </div>
              <input
                v-model="form.telegram_group_link"
                type="url"
                class="ma-form-input"
                placeholder="https://t.me/YourTelegramGroup"
                :disabled="!form.is_telegram_group_enabled"
                :class="{ 'tw-opacity-50': !form.is_telegram_group_enabled }"
              >
              <small class="text-muted tw-block tw-mt-1">Shows as a separate “Telegram Group” option.</small>
            </div>

            <!-- WhatsApp Link -->
            <div class="ma-form-group ma-card-variant">
              <div class="tw-flex tw-items-center tw-justify-between tw-mb-2">
                <label class="ma-form-label tw-mb-0">WhatsApp Link</label>
                <label class="ma-switch">
                  <input type="checkbox" v-model="form.is_whatsapp_enabled">
                  <span class="ma-slider round"></span>
                </label>
              </div>
              <input
                v-model="form.whatsapp_link"
                type="url"
                class="ma-form-input"
                placeholder="https://wa.me/919876543210"
                :disabled="!form.is_whatsapp_enabled"
                :class="{ 'tw-opacity-50': !form.is_whatsapp_enabled }"
              >
              <small class="text-muted tw-block tw-mt-1">Full URL with country code.</small>
            </div>

            <!-- Live Chat URL -->
            <div class="ma-form-group ma-card-variant">
              <div class="tw-flex tw-items-center tw-justify-between tw-mb-2">
                <label class="ma-form-label tw-mb-0">Live Chat URL (e.g. Tawk.to)</label>
                <label class="ma-switch">
                  <input type="checkbox" v-model="form.is_live_chat_enabled">
                  <span class="ma-slider round"></span>
                </label>
              </div>
              <input
                v-model="form.live_chat_url"
                type="url"
                class="ma-form-input"
                placeholder="https://tawk.to/chat/your-widget"
                :disabled="!form.is_live_chat_enabled"
                :class="{ 'tw-opacity-50': !form.is_live_chat_enabled }"
              >
              <small class="text-muted tw-block tw-mt-1">URL that opens when user clicks "Start Live Chat".</small>
            </div>

            <div class="ma-form-actions">
              <button type="submit" class="ma-btn ma-btn--primary tw-w-full sm:tw-w-auto">
                <i class="fas fa-save tw-mr-2"></i>Save Support Links
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
      is_telegram_enabled: true,
      telegram_group_link: '',
      is_telegram_group_enabled: true,
      whatsapp_link: '',
      is_whatsapp_enabled: true,
      live_chat_url: '',
      is_live_chat_enabled: true
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
          form.is_telegram_enabled = res.data.data.is_telegram_enabled !== false
          form.telegram_group_link = res.data.data.telegram_group_link || ''
          form.is_telegram_group_enabled = res.data.data.is_telegram_group_enabled !== false
          form.whatsapp_link = res.data.data.whatsapp_link || ''
          form.is_whatsapp_enabled = res.data.data.is_whatsapp_enabled !== false
          form.live_chat_url = res.data.data.live_chat_url || ''
          form.is_live_chat_enabled = res.data.data.is_live_chat_enabled !== false
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
          is_telegram_enabled: form.is_telegram_enabled,
          telegram_group_link: form.telegram_group_link || '',
          is_telegram_group_enabled: form.is_telegram_group_enabled,
          whatsapp_link: form.whatsapp_link || '',
          is_whatsapp_enabled: form.is_whatsapp_enabled,
          live_chat_url: form.live_chat_url || '',
          is_live_chat_enabled: form.is_live_chat_enabled
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

/* Switch CSS */
.ma-switch {
  position: relative;
  display: inline-block;
  width: 44px;
  height: 24px;
}
.ma-switch input { 
  opacity: 0;
  width: 0;
  height: 0;
}
.ma-slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #475569;
  transition: .4s;
}
.ma-slider:before {
  position: absolute;
  content: "";
  height: 18px;
  width: 18px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: .4s;
}
input:checked + .ma-slider {
  background-color: #6366f1;
}
input:focus + .ma-slider {
  box-shadow: 0 0 1px #6366f1;
}
input:checked + .ma-slider:before {
  transform: translateX(20px);
}
.ma-slider.round {
  border-radius: 34px;
}
.ma-slider.round:before {
  border-radius: 50%;
}
.ma-card-variant {
  background: rgba(15, 23, 42, 0.4);
  padding: 1.25rem;
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.05);
}
</style>
