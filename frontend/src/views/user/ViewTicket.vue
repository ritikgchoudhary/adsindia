<template>
  <DashboardLayout page-title="View Ticket" :dark-theme="true">
    <div class="tw-grid tw-grid-cols-1 tw-gap-6">
      <!-- Ticket Header -->
      <div class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-slate-200 tw-overflow-hidden">
        <div class="tw-bg-indigo-600 tw-p-5 tw-flex tw-flex-wrap tw-justify-between tw-items-center">
          <h5 class="tw-text-white tw-font-bold tw-text-lg tw-m-0 tw-flex tw-items-center tw-flex-wrap tw-gap-2">
            <span v-html="ticket.status_badge"></span>
            <span class="tw-opacity-90">[Ticket#{{ ticket.ticket }}] {{ ticket.subject }}</span>
          </h5>
          <button v-if="ticket.status != 3" class="tw-bg-red-500/20 hover:tw-bg-red-500/40 tw-text-white tw-w-9 tw-h-9 tw-rounded-lg tw-flex tw-items-center tw-justify-center tw-transition-colors tw-border-0 tw-cursor-pointer" @click="closeTicket" title="Close Ticket">
             <i class="fas fa-times"></i>
          </button>
        </div>
        
        <div class="tw-p-6">
          <form @submit.prevent="handleReply" enctype="multipart/form-data">
            <div class="tw-mb-4">
               <textarea v-model="replyMessage" name="message" class="tw-w-full tw-px-4 tw-py-3 tw-bg-white tw-border tw-border-slate-300 tw-rounded-xl focus:tw-outline-none focus:tw-border-indigo-500 focus:tw-ring-4 focus:tw-ring-indigo-500/10 tw-transition-all tw-resize-y" rows="4" placeholder="Type your reply here..." required></textarea>
            </div>
            
            <div class="tw-flex tw-flex-col md:tw-flex-row tw-justify-between tw-items-start tw-gap-4">
               <div class="tw-w-full md:tw-w-auto tw-flex-1">
                 <div class="tw-flex tw-items-center tw-gap-4 tw-mb-2">
                    <button type="button" class="tw-text-indigo-600 tw-bg-indigo-50 hover:tw-bg-indigo-100 tw-font-bold tw-text-sm tw-px-3 tw-py-1.5 tw-rounded-lg tw-transition-colors tw-border-0 tw-cursor-pointer disabled:tw-opacity-50 disabled:tw-cursor-not-allowed" @click="addAttachment" :disabled="attachments.length >= 5">
                      <i class="fas fa-plus tw-mr-1"></i> Add Attachment
                    </button>
                    <span class="tw-text-xs tw-text-slate-400">Max 5 files (2MB each)</span>
                 </div>
                 
                 <div class="tw-space-y-2">
                    <div v-for="(file, index) in attachments" :key="index" class="tw-flex tw-items-center tw-gap-2">
                       <div class="tw-relative tw-flex-1 md:tw-max-w-xs">
                         <input type="file" @change="handleFileChange(index, $event)" class="tw-w-full tw-text-xs tw-text-slate-500 file:tw-mr-2 file:tw-py-1 file:tw-px-2 file:tw-rounded-lg file:tw-border-0 file:tw-text-xs file:tw-font-bold file:tw-bg-indigo-50 file:tw-text-indigo-700 hover:file:tw-bg-indigo-100" accept=".jpeg,.jpg,.png,.pdf,.doc,.docx" required>
                       </div>
                       <button type="button" class="tw-w-7 tw-h-7 tw-rounded-md tw-bg-rose-50 tw-text-rose-600 hover:tw-bg-rose-100 tw-flex tw-items-center tw-justify-center tw-transition-colors tw-border-0 tw-cursor-pointer" @click="removeFile(index)">
                          <i class="fas fa-times tw-text-xs"></i>
                       </button>
                    </div>
                 </div>
               </div>
               
               <button type="submit" class="tw-px-6 tw-py-2.5 tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-font-bold tw-rounded-xl tw-shadow-lg tw-shadow-indigo-500/20 tw-transition-all tw-flex tw-items-center tw-justify-center tw-gap-2 tw-border-0 tw-cursor-pointer tw-w-full md:tw-w-auto">
                 <i class="fas fa-reply"></i> Reply
               </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Message History -->
      <div class="tw-space-y-6">
        <template v-for="message in messages" :key="message?.id || Math.random()">
           <div 
             v-if="message && message.id" 
             class="tw-flex tw-flex-col md:tw-flex-row tw-gap-4 tw-p-6 tw-rounded-2xl tw-shadow-sm tw-border"
             :class="message.admin_id == 0 ? 'tw-bg-white tw-border-slate-200' : 'tw-bg-amber-50/50 tw-border-amber-100'"
           >
              <!-- User Info (Left for User, Right for Admin in desktop view ideally, but keeping stacked is fine for simplicity) -->
              <div class="md:tw-w-48 tw-shrink-0 md:tw-border-r md:tw-border-slate-100 md:tw-pr-6">
                 <h5 class="tw-font-bold tw-text-slate-900 tw-text-base tw-mb-1">
                   {{ message.admin_id == 0 ? message.ticket?.fullname : message.admin?.name }}
                 </h5>
                 <p v-if="message.admin_id != 0" class="tw-text-xs tw-bg-indigo-100 tw-text-indigo-700 tw-px-2 tw-py-0.5 tw-rounded tw-inline-block tw-font-bold tw-uppercase tw-tracking-wide">Staff</p>
                 <p v-else class="tw-text-xs tw-bg-slate-100 tw-text-slate-600 tw-px-2 tw-py-0.5 tw-rounded tw-inline-block tw-font-bold tw-uppercase tw-tracking-wide">User</p>
                 
                 <div class="tw-mt-2 tw-text-xs tw-text-slate-400">
                   {{ formatDateTime(message.created_at) }}
                 </div>
              </div>
              
              <div class="tw-flex-1">
                 <div class="tw-prose tw-prose-sm tw-max-w-none tw-text-slate-600" v-html="message.message"></div>
                 
                 <div v-if="message.attachments && message.attachments.length > 0" class="tw-mt-4 tw-flex tw-flex-wrap tw-gap-3">
                   <a v-for="(attachment, k) in message.attachments" :key="k" 
                      :href="attachment.download_url" 
                      target="_blank"
                      class="tw-flex tw-items-center tw-gap-2 tw-bg-slate-100 hover:tw-bg-slate-200 tw-text-slate-700 tw-px-3 tw-py-2 tw-rounded-lg tw-text-sm tw-font-bold tw-transition-colors tw-no-underline">
                     <i class="fas fa-paperclip tw-text-indigo-500"></i> Attachment {{ k + 1 }}
                   </a>
                 </div>
              </div>
           </div>
        </template>
        
        <div v-if="messages.length === 0" class="tw-text-center tw-p-10 tw-bg-white tw-rounded-2xl tw-border tw-border-dashed tw-border-slate-300">
          <div class="tw-w-16 tw-h-16 tw-mx-auto tw-bg-slate-50 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-text-slate-300 tw-mb-3">
             <i class="fas fa-comments tw-text-2xl"></i>
          </div>
          <h5 class="tw-text-slate-500 tw-font-bold tw-text-base">No replies found here!</h5>
        </div>
      </div>

    </div>
  </DashboardLayout>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import DashboardLayout from '../../components/DashboardLayout.vue'
import api from '../../services/api'

export default {
  name: 'ViewTicket',
  components: {
    DashboardLayout
  },
  setup() {
    const route = useRoute()
    const router = useRouter()
    const ticket = ref({})
    const messages = ref([])
    const replyMessage = ref('')
    const attachments = ref([])

    const formatDateTime = (dateString) => {
      if (!dateString) return '-'
      const date = new Date(dateString)
      return date.toLocaleString('en-US', { 
        weekday: 'short', 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
      })
    }

    const addAttachment = () => {
      if (attachments.value.length < 5) {
        attachments.value.push(null)
      }
    }

    const removeFile = (index) => {
      attachments.value.splice(index, 1)
    }

    const handleFileChange = (index, event) => {
      attachments.value[index] = event.target.files[0]
    }

    const handleReply = async () => {
      try {
        const formData = new FormData()
        formData.append('message', replyMessage.value)
        attachments.value.forEach((file, index) => {
          if (file) {
            formData.append(`attachments[${index}]`, file)
          }
        })

        const response = await api.post(`/ticket/reply/${route.params.ticket}`, formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })

        if (response.data.status === 'success') {
          if (window.notify) {
            window.notify('success', 'Reply sent successfully!')
          }
          replyMessage.value = ''
          attachments.value = []
          fetchTicket()
        }
      } catch (error) {
        console.error('Error replying to ticket:', error)
        if (window.notify) {
          window.notify('error', error.response?.data?.message || 'Failed to send reply')
        }
      }
    }

    const closeTicket = async () => {
      if (!confirm('Are you sure to close this ticket?')) return

      try {
        const response = await api.post(`/ticket/close/${route.params.ticket}`)
        if (response.data.status === 'success') {
          if (window.notify) {
            window.notify('success', 'Ticket closed successfully!')
          }
          fetchTicket()
        }
      } catch (error) {
        console.error('Error closing ticket:', error)
        if (window.notify) {
          window.notify('error', error.response?.data?.message || 'Failed to close ticket')
        }
      }
    }

    const fetchTicket = async () => {
      try {
        const response = await api.get(`/ticket/view/${route.params.ticket}`)
        if (response.data.status === 'success') {
          ticket.value = response.data.data.ticket || {}
          messages.value = response.data.data.messages || []
        }
      } catch (error) {
        console.error('Error loading ticket:', error)
      }
    }

    onMounted(() => {
      fetchTicket()
    })

    return {
      ticket,
      messages,
      replyMessage,
      attachments,
      formatDateTime,
      addAttachment,
      removeFile,
      handleFileChange,
      handleReply,
      closeTicket
    }
  }
}
</script>
