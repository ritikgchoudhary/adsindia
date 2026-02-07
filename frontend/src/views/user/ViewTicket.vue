<template>
  <DashboardLayout page-title="View Ticket">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-12">
          <div class="card custom--card">
            <div class="card-header card-header-bg d-flex flex-wrap justify-content-between align-items-center">
              <h5 class="text-white mb-0 d-flex align-items-center gap-2 flex-wrap">
                <span v-html="ticket.status_badge"></span>
                <span class="ms-1">[Ticket#{{ ticket.ticket }}] {{ ticket.subject }}</span>
              </h5>
              <button v-if="ticket.status != 3" class="btn btn--danger close-button btn--sm" type="button" @click="closeTicket">
                <i class="fas fa-lg fa-times"></i>
              </button>
            </div>
            <div class="card-body">
              <form @submit.prevent="handleReply" enctype="multipart/form-data" class="mt-3">
                <div class="row justify-content-between">
                  <div class="col-md-12">
                    <div class="form-group">
                      <textarea v-model="replyMessage" name="message" class="form-control form--control" rows="4" required></textarea>
                    </div>
                  </div>
                  <div class="col-md-9">
                    <button type="button" class="btn btn-dark btn--sm addAttachment my-2" @click="addAttachment">
                      <i class="fas fa-plus"></i> Add Attachment
                    </button>
                    <p class="mb-2"><span class="text--info">Max 5 files can be uploaded | Maximum upload size is 2MB | Allowed File Extensions: .jpg, .jpeg, .png, .pdf, .doc, .docx</span></p>
                    <div class="row fileUploadsContainer">
                      <div v-for="(file, index) in attachments" :key="index" class="col-lg-4 col-md-12 removeFileInput">
                        <div class="form-group">
                          <div class="input-group">
                            <input type="file" @change="handleFileChange(index, $event)" class="form-control form--control" accept=".jpeg,.jpg,.png,.pdf,.doc,.docx" required>
                            <button type="button" class="input-group-text removeFile bg--danger text-white border--danger" @click="removeFile(index)">
                              <i class="fas fa-times"></i>
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <button class="btn btn--base w-100 my-2" type="submit">
                      <i class="la la-fw la-lg la-reply"></i> Reply
                    </button>
                  </div>
                </div>
              </form>
            </div>
          </div>

          <div class="card custom--card mt-4">
            <div class="card-body">
              <template v-for="message in messages" :key="message?.id || Math.random()">
                <div v-if="message && message.id" 
                   class="row border my-3 py-3 mx-2"
                   :class="message.admin_id == 0 ? 'border-primary' : 'border-warning reply-bg'">
                <div class="col-md-3 border-end text-end">
                  <h5 class="my-3">{{ message.admin_id == 0 ? message.ticket?.fullname : message.admin?.name }}</h5>
                  <p v-if="message.admin_id != 0" class="lead text-muted">Staff</p>
                </div>
                <div class="col-md-9">
                  <p class="text-muted fw-bold my-3">
                    Posted on {{ formatDateTime(message.created_at) }}
                  </p>
                  <p>{{ message.message }}</p>
                  <div v-if="message.attachments && message.attachments.length > 0" class="mt-2">
                    <a v-for="(attachment, k) in message.attachments" :key="k" 
                       :href="attachment.download_url" 
                       class="me-3 text--base">
                      <i class="fa-regular fa-file"></i> Attachment {{ k + 1 }}
                    </a>
                  </div>
                </div>
              </div>
              </template>
              <div v-if="messages.length === 0" class="empty-message text-center">
                <h5 class="text-muted">No replies found here!</h5>
              </div>
            </div>
          </div>
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
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
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

<style scoped>
.reply-bg {
  background-color: #ffd96729;
}

.empty-message img {
  width: 120px;
  margin-bottom: 15px;
}
</style>
