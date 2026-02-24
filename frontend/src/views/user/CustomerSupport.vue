<template>
  <DashboardLayout page-title="Customer Support" :dark-theme="true">
    <div class="tw-grid tw-grid-cols-1 tw-gap-8">
      
      <!-- Intro Card -->
      <div class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-slate-200 tw-overflow-hidden">
        <div class="tw-bg-gradient-to-br tw-from-slate-50 tw-to-slate-100 tw-p-5 tw-border-b tw-border-slate-200 tw-relative">
          <div class="tw-absolute tw-top-0 tw-left-0 tw-w-full tw-h-1 tw-bg-gradient-to-r tw-from-indigo-600 tw-to-violet-600"></div>
          <h5 class="tw-text-slate-900 tw-font-bold tw-text-lg tw-mb-1 tw-flex tw-items-center">
            <i class="fas fa-headset tw-mr-2 tw-text-indigo-600"></i>Customer Support
          </h5>
          <p class="tw-text-slate-500 tw-text-sm tw-m-0">
            Get help via Telegram or live chat. We’re here to assist you.
          </p>
        </div>
      </div>

      <!-- Channel Cards -->
      <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-3 tw-gap-6">
        
        <!-- Telegram -->
        <div class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-slate-200 tw-p-6 tw-text-center tw-group hover:-tw-translate-y-1 hover:tw-shadow-xl tw-transition-all tw-duration-300">
          <div class="tw-w-16 tw-h-16 tw-mx-auto tw-mb-4 tw-rounded-2xl tw-flex tw-items-center tw-justify-center tw-bg-gradient-to-br tw-from-sky-500 tw-to-sky-600 tw-text-white tw-shadow-lg tw-shadow-sky-500/30 tw-transform group-hover:tw-scale-110 group-hover:tw-rotate-3 tw-transition-transform">
            <i class="fab fa-telegram tw-text-3xl"></i>
          </div>
          <h5 class="tw-text-slate-900 tw-font-bold tw-text-lg tw-mb-1">Telegram Support</h5>
          <p class="tw-text-slate-500 tw-text-sm tw-mb-6">Get instant help via Telegram</p>
          <a
            :href="telegramHref || '#'"
            :target="telegramHref ? '_blank' : '_self'"
            class="tw-block tw-w-full tw-py-3 tw-bg-gradient-to-r tw-from-sky-500 tw-to-sky-600 hover:tw-from-sky-600 hover:tw-to-sky-700 tw-text-white tw-font-bold tw-rounded-xl tw-shadow-lg tw-shadow-sky-500/20 tw-transition-all tw-no-underline disabled:tw-opacity-60 disabled:tw-cursor-not-allowed"
            :class="{ 'tw-pointer-events-none tw-opacity-60': !telegramHref }"
            @click="!telegramHref && $event.preventDefault()">
            <i class="fab fa-telegram tw-mr-2"></i>Join Telegram
          </a>
        </div>

        <!-- Telegram Group -->
        <div class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-slate-200 tw-p-6 tw-text-center tw-group hover:-tw-translate-y-1 hover:tw-shadow-xl tw-transition-all tw-duration-300">
          <div class="tw-w-16 tw-h-16 tw-mx-auto tw-mb-4 tw-rounded-2xl tw-flex tw-items-center tw-justify-center tw-bg-gradient-to-br tw-from-indigo-500 tw-to-violet-600 tw-text-white tw-shadow-lg tw-shadow-indigo-500/30 tw-transform group-hover:tw-scale-110 group-hover:-tw-rotate-3 tw-transition-transform">
            <i class="fas fa-users tw-text-3xl"></i>
          </div>
          <h5 class="tw-text-slate-900 tw-font-bold tw-text-lg tw-mb-1">Telegram Group</h5>
          <p class="tw-text-slate-500 tw-text-sm tw-mb-6">Join our official Telegram group</p>
          <a
            :href="telegramGroupHref || '#'"
            :target="telegramGroupHref ? '_blank' : '_self'"
            class="tw-block tw-w-full tw-py-3 tw-bg-gradient-to-r tw-from-indigo-600 tw-to-violet-600 hover:tw-from-indigo-700 hover:tw-to-violet-700 tw-text-white tw-font-bold tw-rounded-xl tw-shadow-lg tw-shadow-indigo-500/20 tw-transition-all tw-no-underline disabled:tw-opacity-60 disabled:tw-cursor-not-allowed"
            :class="{ 'tw-pointer-events-none tw-opacity-60': !telegramGroupHref }"
            @click="!telegramGroupHref && $event.preventDefault()">
            <i class="fas fa-users tw-mr-2"></i>Join Group
          </a>
        </div>

        <!-- WhatsApp (disabled as requested) -->
        <!--
        <div class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-slate-200 tw-p-6 tw-text-center tw-group hover:-tw-translate-y-1 hover:tw-shadow-xl tw-transition-all tw-duration-300">
          <div class="tw-w-16 tw-h-16 tw-mx-auto tw-mb-4 tw-rounded-2xl tw-flex tw-items-center tw-justify-center tw-bg-gradient-to-br tw-from-emerald-500 tw-to-emerald-600 tw-text-white tw-shadow-lg tw-shadow-emerald-500/30 tw-transform group-hover:tw-scale-110 group-hover:tw-rotate-3 tw-transition-transform">
            <i class="fab fa-whatsapp tw-text-3xl"></i>
          </div>
          <h5 class="tw-text-slate-900 tw-font-bold tw-text-lg tw-mb-1">WhatsApp Support</h5>
          <p class="tw-text-slate-500 tw-text-sm tw-mb-6">Chat with us on WhatsApp</p>
          <a
            :href="whatsappHref || '#'"
            :target="whatsappHref ? '_blank' : '_self'"
            class="tw-block tw-w-full tw-py-3 tw-bg-gradient-to-r tw-from-emerald-500 tw-to-emerald-600 hover:tw-from-emerald-600 hover:tw-to-emerald-700 tw-text-white tw-font-bold tw-rounded-xl tw-shadow-lg tw-shadow-emerald-500/20 tw-transition-all tw-no-underline disabled:tw-opacity-60 disabled:tw-cursor-not-allowed"
            :class="{ 'tw-pointer-events-none tw-opacity-60': !whatsappHref }"
            @click="!whatsappHref && $event.preventDefault()">
            <i class="fab fa-whatsapp tw-mr-2"></i>Chat on WhatsApp
          </a>
        </div>
        -->

        <!-- Live Chat -->
        <div class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-slate-200 tw-p-6 tw-text-center tw-group hover:-tw-translate-y-1 hover:tw-shadow-xl tw-transition-all tw-duration-300">
          <div class="tw-w-16 tw-h-16 tw-mx-auto tw-mb-4 tw-rounded-2xl tw-flex tw-items-center tw-justify-center tw-bg-gradient-to-br tw-from-indigo-600 tw-to-violet-600 tw-text-white tw-shadow-lg tw-shadow-indigo-500/30 tw-transform group-hover:tw-scale-110 group-hover:tw-rotate-3 tw-transition-transform">
            <i class="fas fa-comments tw-text-3xl"></i>
          </div>
          <h5 class="tw-text-slate-900 tw-font-bold tw-text-lg tw-mb-1">Live Chat</h5>
          <p class="tw-text-slate-500 tw-text-sm tw-mb-6">24/7 live chat support</p>
          <button 
            type="button" 
            class="tw-block tw-w-full tw-py-3 tw-bg-gradient-to-r tw-from-indigo-600 tw-to-violet-600 hover:tw-from-indigo-700 hover:tw-to-violet-700 tw-text-white tw-font-bold tw-rounded-xl tw-shadow-lg tw-shadow-indigo-500/20 tw-transition-all tw-border-0 tw-cursor-pointer" 
            @click="openLiveChat"
          >
            <i class="fas fa-comments tw-mr-2"></i>Start Live Chat
          </button>
        </div>

      </div>

      <!-- Submit Ticket (Form) -->
      <div class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-slate-200 tw-overflow-hidden">
        <div class="tw-bg-slate-50 tw-p-5 tw-border-b tw-border-slate-200">
          <div class="tw-flex tw-flex-col md:tw-flex-row md:tw-items-center md:tw-justify-between tw-gap-2">
            <h5 class="tw-text-slate-900 tw-font-bold tw-text-lg tw-m-0 tw-flex tw-items-center">
              <i class="fas fa-ticket-alt tw-mr-2 tw-text-indigo-600"></i>Submit a Support Ticket
            </h5>
            <router-link
              to="/user/ticket"
              class="tw-text-sm tw-font-bold tw-text-indigo-600 hover:tw-text-indigo-700 tw-no-underline"
            >
              View My Tickets →
            </router-link>
          </div>
          <p class="tw-text-slate-500 tw-text-sm tw-mt-2 tw-mb-0">
            Fill the form and submit. Our team will reply in your ticket inbox.
          </p>
        </div>

        <div class="tw-p-6">
          <form @submit.prevent="handleSubmit" class="tw-flex tw-flex-col tw-gap-5" enctype="multipart/form-data">
            <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-5">
              <div>
                <label class="tw-block tw-text-sm tw-font-bold tw-text-slate-700 tw-mb-2">Subject</label>
                <input
                  type="text"
                  v-model="form.subject"
                  class="tw-w-full tw-px-4 tw-py-3 tw-bg-white tw-border tw-border-slate-300 tw-rounded-xl focus:tw-outline-none focus:tw-border-indigo-500 focus:tw-ring-4 focus:tw-ring-indigo-500/10 tw-transition-all"
                  placeholder="Brief subject"
                  required
                >
              </div>
              <div>
                <label class="tw-block tw-text-sm tw-font-bold tw-text-slate-700 tw-mb-2">Priority</label>
                <select
                  v-model="form.priority"
                  class="tw-w-full tw-px-4 tw-py-3 tw-bg-white tw-border tw-border-slate-300 tw-rounded-xl tw-text-slate-900 focus:tw-outline-none focus:tw-border-indigo-500 focus:tw-ring-4 focus:tw-ring-indigo-500/10 tw-transition-all cs-ticket-select"
                  required
                >
                  <option value="3" class="cs-ticket-option">High</option>
                  <option value="2" class="cs-ticket-option">Medium</option>
                  <option value="1" class="cs-ticket-option">Low</option>
                </select>
              </div>
            </div>

            <div>
              <label class="tw-block tw-text-sm tw-font-bold tw-text-slate-700 tw-mb-2">Message</label>
              <textarea
                v-model="form.message"
                rows="6"
                class="tw-w-full tw-px-4 tw-py-3 tw-bg-white tw-border tw-border-slate-300 tw-rounded-xl focus:tw-outline-none focus:tw-border-indigo-500 focus:tw-ring-4 focus:tw-ring-indigo-500/10 tw-transition-all tw-resize-y"
                placeholder="Describe your issue..."
                required
              ></textarea>
            </div>

            <div class="tw-bg-slate-50 tw-border tw-border-dashed tw-border-slate-300 tw-rounded-xl tw-p-5">
              <div class="tw-flex tw-items-center tw-justify-between tw-gap-2 tw-flex-wrap">
                <div class="tw-text-sm tw-font-bold tw-text-slate-700">
                  Attachments (optional)
                  <span class="tw-text-slate-400 tw-text-xs tw-font-medium">max 5 files · 2MB each</span>
                </div>
                <button
                  type="button"
                  class="tw-text-indigo-600 tw-bg-indigo-50 hover:tw-bg-indigo-100 tw-font-bold tw-text-sm tw-px-3 tw-py-1.5 tw-rounded-lg tw-transition-colors tw-border-0 tw-cursor-pointer disabled:tw-opacity-50 disabled:tw-cursor-not-allowed"
                  @click="addAttachment"
                  :disabled="attachments.length >= 5"
                >
                  <i class="fas fa-plus tw-mr-1"></i>Add File
                </button>
              </div>

              <div class="tw-mt-4 tw-space-y-3" v-if="attachments.length > 0">
                <div v-for="(_, index) in attachments" :key="index" class="tw-flex tw-items-center tw-gap-3">
                  <input
                    type="file"
                    @change="handleFileChange(index, $event)"
                    class="tw-flex-1 tw-text-sm tw-text-slate-500 file:tw-mr-4 file:tw-py-2 file:tw-px-4 file:tw-rounded-lg file:tw-border-0 file:tw-text-xs file:tw-font-bold file:tw-bg-indigo-50 file:tw-text-indigo-700 hover:file:tw-bg-indigo-100"
                    accept=".jpeg,.jpg,.png,.pdf,.doc,.docx"
                  >
                  <button
                    type="button"
                    class="tw-bg-rose-50 tw-text-rose-600 hover:tw-bg-rose-100 tw-w-9 tw-h-9 tw-rounded-lg tw-flex tw-items-center tw-justify-center tw-transition-colors tw-border-0 tw-cursor-pointer"
                    @click="removeFile(index)"
                    title="Remove"
                  >
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>
              <div v-else class="tw-mt-4 tw-text-slate-400 tw-text-sm">
                No files attached.
              </div>
            </div>

            <div>
              <button
                type="submit"
                class="tw-w-full tw-py-3.5 tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-font-bold tw-rounded-xl tw-shadow-lg tw-shadow-indigo-500/30 tw-transition-all tw-flex tw-items-center tw-justify-center tw-gap-2 tw-border-0 tw-cursor-pointer disabled:tw-opacity-60 disabled:tw-cursor-not-allowed"
                :disabled="submitting"
              >
                <i v-if="submitting" class="fas fa-spinner fa-spin"></i>
                <i v-else class="fas fa-paper-plane"></i>
                {{ submitting ? 'Submitting...' : 'Submit Ticket' }}
              </button>
            </div>
          </form>
        </div>
      </div>

    </div>
  </DashboardLayout>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import DashboardLayout from '../../components/DashboardLayout.vue'
import api from '../../services/api'

export default {
  name: 'CustomerSupport',
  components: {
    DashboardLayout
  },
  setup() {
    const router = useRouter()
    const supportLinks = ref({
      telegram: '',
      telegram_group: '',
      whatsapp: '',
      live_chat_url: ''
    })

    // Ticket form
    const submitting = ref(false)
    const form = ref({
      subject: '',
      priority: '2',
      message: ''
    })
    const attachments = ref([])

    const normalizeUrl = (raw) => {
      const v = String(raw || '').trim()
      if (!v) return ''
      if (v.startsWith('http://') || v.startsWith('https://')) return v
      if (v.startsWith('//')) return 'https:' + v
      return 'https://' + v
    }

    const telegramHref = computed(() => {
      const raw = String(supportLinks.value.telegram || '').trim()
      if (!raw) return ''
      if (raw.startsWith('@')) return `https://t.me/${raw.replace(/^@+/, '')}`
      if (raw.startsWith('t.me/')) return `https://${raw}`
      if (raw.includes('t.me/')) return normalizeUrl(raw)
      return normalizeUrl(raw)
    })

    const telegramGroupHref = computed(() => {
      const raw = String(supportLinks.value.telegram_group || '').trim()
      if (!raw) return ''
      if (raw.startsWith('@')) return `https://t.me/${raw.replace(/^@+/, '')}`
      if (raw.startsWith('t.me/')) return `https://${raw}`
      if (raw.includes('t.me/')) return normalizeUrl(raw)
      return normalizeUrl(raw)
    })

    const whatsappHref = computed(() => {
      const raw = String(supportLinks.value.whatsapp || '').trim()
      if (!raw) return ''
      // If it's just a number, convert to wa.me
      if (/^[0-9 +()-]+$/.test(raw)) {
        const digits = raw.replace(/\D/g, '')
        if (!digits) return ''
        return `https://wa.me/${digits}`
      }
      if (raw.startsWith('wa.me/')) return `https://${raw}`
      if (raw.includes('wa.me/') || raw.includes('api.whatsapp.com/')) return normalizeUrl(raw)
      return normalizeUrl(raw)
    })

    const openLiveChat = () => {
      const url = supportLinks.value.live_chat_url
      if (url) {
        window.open(url, '_blank')
        return
      }
      if (typeof window.Tawk_API !== 'undefined' && window.Tawk_API.maximize) {
        window.Tawk_API.maximize()
      } else {
        if (window.notify) {
          window.notify('info', 'Live chat is currently unavailable. Please use Telegram or WhatsApp.')
        }
      }
    }

    const fetchSupportLinks = async () => {
      try {
        const response = await api.get('/customer-support/links')
        if (response.data?.status === 'success' && response.data.data) {
          supportLinks.value = { ...supportLinks.value, ...response.data.data }
        }
      } catch (error) {
        console.error('Error loading support links:', error)
      }
    }

    const addAttachment = () => {
      if (attachments.value.length < 5) attachments.value.push(null)
    }
    const removeFile = (index) => {
      attachments.value.splice(index, 1)
    }
    const handleFileChange = (index, event) => {
      attachments.value[index] = event?.target?.files?.[0] || null
    }

    const handleSubmit = async () => {
      if (submitting.value) return
      submitting.value = true
      try {
        const formData = new FormData()
        formData.append('subject', form.value.subject || '')
        formData.append('priority', form.value.priority || '2')
        formData.append('message', form.value.message || '')
        attachments.value.forEach((file, idx) => {
          if (file) formData.append(`attachments[${idx}]`, file)
        })

        const response = await api.post('/ticket/create', formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        })

        if (response.data?.status === 'success') {
          if (window.notify) window.notify('success', 'Ticket submitted successfully!')
          form.value = { subject: '', priority: '2', message: '' }
          attachments.value = []
          router.push('/user/ticket')
          return
        }
        if (window.notify) window.notify('error', response.data?.message?.[0] || response.data?.message || 'Failed to submit ticket')
      } catch (error) {
        console.error('Error submitting ticket:', error)
        const msg = error.response?.data?.message?.[0] || error.response?.data?.message || 'Failed to submit ticket'
        if (window.notify) window.notify('error', msg)
      } finally {
        submitting.value = false
      }
    }

    onMounted(() => {
      fetchSupportLinks()
    })

    return {
      supportLinks,
      telegramHref,
      telegramGroupHref,
      whatsappHref,
      openLiveChat,
      // ticket form
      submitting,
      form,
      attachments,
      addAttachment,
      removeFile,
      handleFileChange,
      handleSubmit
    }
  }
}
</script>

<style scoped>
/* Fix: on dark dashboard layout, select/option text can become white-on-white */
.cs-ticket-select {
  color: #0f172a !important; /* slate-900 */
}
.cs-ticket-select option,
.cs-ticket-option {
  color: #0f172a !important;
  background: #ffffff !important;
}

@media (max-width: 640px) {
  /* Intro Card */
  .tw-p-5 { padding: 0.85rem !important; }
  h5.tw-text-lg { font-size: 1rem !important; }
  p.tw-text-slate-500 { font-size: 0.75rem !important; }

  /* Channel Cards Grid */
  .tw-grid.tw-gap-6 { gap: 1rem !important; }
  .tw-p-6 { padding: 1.25rem !important; border-radius: 1.15rem !important; }
  .tw-w-16.tw-h-16 { width: 3.5rem !important; height: 3.5rem !important; margin-bottom: 0.75rem !important; border-radius: 1rem !important; }
  .tw-w-16.tw-h-16 i { font-size: 1.5rem !important; }
  h5.tw-text-slate-900 { font-size: 1rem !important; }
  p.tw-text-sm.tw-mb-6 { font-size: 0.75rem !important; margin-bottom: 1rem !important; }
  .tw-py-3 { padding: 0.65rem !important; font-size: 0.9rem !important; border-radius: 0.85rem !important; }

  /* Submit Ticket Section */
  .tw-p-6 { padding: 1rem !important; }
  .tw-p-5.tw-border-b { padding: 1rem !important; }
  h5.tw-text-lg i { font-size: 1.15rem !important; }
  p.tw-text-sm.tw-mt-2 { font-size: 0.75rem !important; }
  .tw-text-sm.tw-font-bold.tw-text-indigo-600 { font-size: 0.8rem !important; }

  /* Form Elements */
  .tw-gap-5 { gap: 0.75rem !important; }
  .tw-py-3 { padding: 0.65rem 0.85rem !important; font-size: 0.85rem !important; border-radius: 0.85rem !important; }
  label.tw-text-sm { font-size: 0.8rem !important; margin-bottom: 0.25rem !important; }
  textarea.tw-py-3 { padding: 0.75rem !important; font-size: 0.85rem !important; border-radius: 0.85rem !important; }

  /* Attachments Box */
  .tw-p-5.tw-bg-slate-50 { padding: 0.85rem !important; border-radius: 0.85rem !important; }
  .tw-text-sm.tw-font-bold.tw-text-slate-700 { font-size: 0.8rem !important; }
  .tw-px-3.tw-py-1\.5 { font-size: 0.75rem !important; padding: 0.4rem 0.65rem !important; border-radius: 0.5rem !important; }

  /* Submit Button */
  .tw-py-3\.5 { padding: 0.75rem !important; font-size: 1rem !important; border-radius: 0.85rem !important; }
}
</style>
