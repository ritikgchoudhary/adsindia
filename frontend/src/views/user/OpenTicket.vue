<template>
  <DashboardLayout page-title="Open New Ticket">
    <div class="container">
      <div class="row justify-content-center mt-4">
        <div class="col-md-12">
          <div class="card custom--card">
            <div class="card-body">
              <form @submit.prevent="handleSubmit" enctype="multipart/form-data">
                <div class="row">
                  <div class="form-group col-md-6">
                    <label class="form--label">Subject</label>
                    <input type="text" v-model="form.subject" name="subject" class="form--control" required>
                  </div>
                  <div class="form-group col-md-6">
                    <label class="form--label">Priority</label>
                    <select v-model="form.priority" name="priority" class="form--control select2" data-minimum-results-for-search="-1" required>
                      <option value="3">High</option>
                      <option value="2">Medium</option>
                      <option value="1">Low</option>
                    </select>
                  </div>
                  <div class="col-12 form-group">
                    <label class="form--label">Message</label>
                    <textarea v-model="form.message" id="inputMessage" rows="6" class="form--control" required></textarea>
                  </div>
                  <div class="col-md-9">
                    <button type="button" class="btn btn--dark btn--sm addAttachment my-2" @click="addAttachment">
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
                      <i class="las la-paper-plane"></i> Submit
                    </button>
                  </div>
                </div>
              </form>
            </div>
          </div>
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

<style scoped>
.select2+.select2-container .select2-selection__rendered {
  line-height: unset;
}

.select2-container--default .select2-selection--single {
  border-width: 1px !important;
  border-radius: 12px !important;
  border-color: var(--select2-border) !important;
}

.select2-container--open .select2-selection.select2-selection--single,
.select2-container--open .select2-selection.select2-selection--multiple {
  border-radius: 12px !important;
}

.select2-container--default .select2-selection--single {
  padding: 16px 24px !important;
}

.select2+.select2-container .select2-selection__rendered {
  padding-right: 0px !important;
  padding-left: 0px !important;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
  top: 28px !important;
}

.select2-results__option.select2-results__option--selected,
.select2-results__option--selectable,
.select2-container--default .select2-results__option--disabled {
  border-bottom-color: hsl(var(--border-color)) !important;
}

.select2-results__option.select2-results__option--selected,
.select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
  color: hsl(var(--white)) !important;
  background-color: hsl(var(--base)) !important;
}

.select2-results__option.select2-results__option--selected,
.select2-results__option--selectable,
.select2-container--default .select2-results__option--disabled {
  border-bottom-color: hsl(var(--white)/0.2) !important;
}

.select2-container--default.select2-container--focus .select2-selection--single {
  outline: none !important;
  box-shadow: none !important;
  border-color: hsl(var(--base)) !important;
}
</style>
