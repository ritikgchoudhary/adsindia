<template>
  <DashboardLayout page-title="Open New Ticket" :dark-theme="true">
    <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-12 tw-gap-6">
      <div class="md:tw-col-span-12 xl:tw-col-span-9">
        <div class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-slate-200 tw-overflow-hidden">
          <div class="tw-bg-slate-50 tw-p-6 tw-border-b tw-border-slate-200">
            <h5 class="tw-text-slate-900 tw-font-bold tw-text-lg tw-mb-1 tw-flex tw-items-center">
              <i class="fas fa-edit tw-mr-2 tw-text-indigo-600"></i>Open New Ticket
            </h5>
            <p class="tw-text-slate-500 tw-text-sm tw-m-0">
              Describe your issue and we’ll get back to you as soon as possible.
            </p>
          </div>
          <div class="tw-p-6">
            <form @submit.prevent="handleSubmit" enctype="multipart/form-data" class="tw-flex tw-flex-col tw-gap-6">
              <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-6">
                <div>
                  <label class="tw-block tw-text-sm tw-font-bold tw-text-slate-700 tw-mb-2">Subject</label>
                  <input type="text" v-model="form.subject" placeholder="Brief subject of your ticket" class="tw-w-full tw-px-4 tw-py-3 tw-bg-white tw-border tw-border-slate-300 tw-rounded-xl focus:tw-outline-none focus:tw-border-indigo-500 focus:tw-ring-4 focus:tw-ring-indigo-500/10 tw-transition-all" required>
                </div>
                <div>
                  <label class="tw-block tw-text-sm tw-font-bold tw-text-slate-700 tw-mb-2">Priority</label>
                  <select v-model="form.priority" class="tw-w-full tw-px-4 tw-py-3 tw-bg-white tw-border tw-border-slate-300 tw-rounded-xl focus:tw-outline-none focus:tw-border-indigo-500 focus:tw-ring-4 focus:tw-ring-indigo-500/10 tw-transition-all" required>
                    <option value="3">High</option>
                    <option value="2">Medium</option>
                    <option value="1">Low</option>
                  </select>
                </div>
              </div>
              
              <div>
                <label class="tw-block tw-text-sm tw-font-bold tw-text-slate-700 tw-mb-2">Message</label>
                <textarea v-model="form.message" rows="6" placeholder="Describe your issue in detail..." class="tw-w-full tw-px-4 tw-py-3 tw-bg-white tw-border tw-border-slate-300 tw-rounded-xl focus:tw-outline-none focus:tw-border-indigo-500 focus:tw-ring-4 focus:tw-ring-indigo-500/10 tw-transition-all tw-resize-y" required></textarea>
              </div>

              <div>
                <div class="tw-bg-slate-50 tw-border tw-border-dashed tw-border-slate-300 tw-rounded-xl tw-p-6">
                  <div class="tw-flex tw-justify-between tw-items-center tw-mb-4">
                    <label class="tw-text-sm tw-font-bold tw-text-slate-700 tw-m-0">Attachments</label>
                    <button type="button" class="tw-text-indigo-600 tw-bg-indigo-50 hover:tw-bg-indigo-100 tw-font-bold tw-text-sm tw-px-3 tw-py-1.5 tw-rounded-lg tw-transition-colors tw-border-0 tw-cursor-pointer disabled:tw-opacity-50 disabled:tw-cursor-not-allowed" @click="addAttachment" :disabled="attachments.length >= 5">
                      <i class="fas fa-plus tw-mr-1"></i> Add File
                    </button>
                  </div>
                  
                  <div class="tw-space-y-3" v-if="attachments.length > 0">
                    <div v-for="(file, index) in attachments" :key="index" class="tw-flex tw-items-center tw-gap-3">
                      <div class="tw-flex-1 tw-relative">
                        <input type="file" @change="handleFileChange(index, $event)" class="tw-w-full tw-text-sm tw-text-slate-500 file:tw-mr-4 file:tw-py-2 file:tw-px-4 file:tw-rounded-lg file:tw-border-0 file:tw-text-xs file:tw-font-bold file:tw-bg-indigo-50 file:tw-text-indigo-700 hover:file:tw-bg-indigo-100" accept=".jpeg,.jpg,.png,.pdf,.doc,.docx">
                      </div>
                      <button type="button" class="tw-bg-rose-50 tw-text-rose-600 hover:tw-bg-rose-100 tw-w-9 tw-h-9 tw-rounded-lg tw-flex tw-items-center tw-justify-center tw-transition-colors tw-border-0 tw-cursor-pointer" @click="removeFile(index)" title="Remove">
                        <i class="fas fa-times"></i>
                      </button>
                    </div>
                  </div>
                  <div v-else class="tw-text-center tw-py-4 tw-text-slate-400 tw-text-sm">
                    No files attached. Click "Add File" to attach screenshots or documents.
                  </div>
                  
                  <p class="tw-text-slate-400 tw-text-xs tw-mt-4 tw-mb-0">
                    Max 5 files · 2MB each · Allowed: .jpg, .jpeg, .png, .pdf, .doc, .docx
                  </p>
                </div>
              </div>

              <div>
                <button type="submit" class="tw-px-8 tw-py-3.5 tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-font-bold tw-rounded-xl tw-shadow-lg tw-shadow-indigo-500/30 tw-transition-all tw-flex tw-items-center tw-justify-center tw-gap-2 tw-border-0 tw-cursor-pointer">
                  <i class="fas fa-paper-plane"></i> Submit Ticket
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
      
      <div class="md:tw-col-span-12 xl:tw-col-span-3">
        <div class="tw-bg-indigo-900 tw-text-white tw-rounded-2xl tw-shadow-lg tw-p-6 tw-overflow-hidden tw-relative">
          <div class="tw-absolute tw-top-0 tw-right-0 -tw-mt-4 -tw-mr-4 tw-w-24 tw-h-24 tw-bg-white/10 tw-rounded-full tw-blur-2xl"></div>
          <div class="tw-absolute tw-bottom-0 tw-left-0 -tw-mb-4 -tw-ml-4 tw-w-20 tw-h-20 tw-bg-indigo-500/20 tw-rounded-full tw-blur-xl"></div>
          
          <h5 class="tw-font-bold tw-text-lg tw-mb-4 tw-flex tw-items-center tw-relative tw-z-10">
            <i class="fas fa-lightbulb tw-mr-2 tw-text-yellow-400"></i>Tips
          </h5>
          <ul class="tw-space-y-4 tw-relative tw-z-10 tw-text-indigo-100 tw-text-sm">
            <li class="tw-flex tw-items-start tw-gap-3">
              <i class="fas fa-check-circle tw-mt-1 tw-text-indigo-400"></i>
              <span>Be specific about the issue you are facing.</span>
            </li>
            <li class="tw-flex tw-items-start tw-gap-3">
              <i class="fas fa-check-circle tw-mt-1 tw-text-indigo-400"></i>
              <span>Attach screenshots if possible to help us understand better.</span>
            </li>
            <li class="tw-flex tw-items-start tw-gap-3">
              <i class="fas fa-check-circle tw-mt-1 tw-text-indigo-400"></i>
              <span>Check our FAQ section before opening a ticket.</span>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import DashboardLayout from '../../components/DashboardLayout.vue'
import api from '../../services/api'

export default {
  name: 'OpenTicket',
  components: {
    DashboardLayout
  },
  setup() {
    const router = useRouter()
    const form = ref({
      subject: '',
      priority: '2',
      message: ''
    })
    const attachments = ref([])

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

    const handleSubmit = async () => {
      try {
        const formData = new FormData()
        formData.append('subject', form.value.subject)
        formData.append('priority', form.value.priority)
        formData.append('message', form.value.message)
        attachments.value.forEach((file, index) => {
          if (file) {
            formData.append(`attachments[${index}]`, file)
          }
        })

        const response = await api.post('/ticket/create', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })

        if (response.data.status === 'success') {
          if (window.notify) {
            window.notify('success', 'Ticket created successfully!')
          }
          router.push('/user/ticket')
        }
      } catch (error) {
        console.error('Error creating ticket:', error)
        if (window.notify) {
          window.notify('error', error.response?.data?.message || 'Failed to create ticket')
        }
      }
    }

    return {
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
