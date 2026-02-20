<template>
  <div class="contact-section mb-120 mt-60">
    <div class="container position-relative">
      <div class="contact-section__left">
        <div class="shape-bg-one"></div>
      </div>
      <div class="contact-section__right">
        <div class="shape-bg-two"></div>
      </div>

      <div class="contact-main-container">
        <div class="row gy-4 justify-content-center">
          <div class="col-xxl-9 col-lg-8">
            <div class="contact-wrapper">
              <span class="contact-wrapper__subtitle">DROP YOUR MESSAGES</span>
              <h4 class="contact-wrapper__title">{{ contactData?.title ?? 'Contact Us' }}</h4>

              <form @submit.prevent="handleSubmit" class="contact-form verify-gcaptcha">
                <div class="row">
                  <div class="col-sm-6">
                    <div class="form-group">
                      <label class="form--label">Name</label>
                      <input v-model="form.name" type="text" name="name" class="form--control" :readonly="userReadonly" required>
                    </div>
                  </div>

                  <div class="col-sm-6">
                    <div class="form-group">
                      <label class="form--label">Email</label>
                      <input v-model="form.email" type="email" name="email" class="form--control" :readonly="userReadonly" required>
                    </div>
                  </div>

                  <div class="col-sm-12">
                    <div class="form-group">
                      <label for="subject" class="form--label">Subject</label>
                      <input v-model="form.subject" type="text" name="subject" class="form--control" required>
                    </div>
                  </div>

                  <div class="col-sm-12">
                    <div class="form-group">
                      <label for="message" class="form--label">Message</label>
                      <textarea v-model="form.message" name="message" class="form--control" rows="4" required></textarea>
                    </div>
                  </div>

                  <div class="col-sm-12">
                    <button class="btn--base btn btn--lg w-100" type="submit" :disabled="loading">
                      {{ loading ? 'Submitting...' : 'Submit' }}
                    </button>
                  </div>
                </div>
              </form>
            </div>
          </div>

          <div class="col-xxl-3 col-lg-4">
            <div class="contact-sidebar">
              <span class="contact-sidebar__subtitle">CONTACT INFO</span>
              <div class="contact-item-wrapper">
                <div class="contact-item bg-base-two">
                  <span class="contact-item__icon"><i class="las la-map-marker-alt"></i></span>
                  <div class="contact-item__content">
                    <h6 class="contact-item__title">Office address</h6>
                    <p class="contact-item__desc">{{ contactData?.address ?? '' }}</p>
                  </div>
                </div>
                <div class="contact-item">
                  <span class="contact-item__icon"><i class="las la-envelope"></i></span>
                  <div class="contact-item__content">
                    <h6 class="contact-item__title">Email address</h6>
                    <p class="contact-item__desc">
                      <a :href="`mailto:${contactData?.email_address ?? ''}`" class="link">
                        {{ contactData?.email_address ?? '' }}
                      </a>
                    </p>
                  </div>
                </div>
                <div class="contact-item bg-success">
                  <span class="contact-item__icon"><i class="las la-phone-alt"></i></span>
                  <div class="contact-item__content">
                    <h6 class="contact-item__title">Phone number</h6>
                    <p class="contact-item__desc">
                      <a :href="`tel:${contactData?.contact_number ?? ''}`" class="link">
                        {{ contactData?.contact_number ?? '' }}
                      </a>
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import api from '../services/api'
import { appService } from '../services/appService'

export default {
  name: 'Contact',
  setup() {
    const form = ref({
      name: '',
      email: '',
      subject: '',
      message: ''
    })
    const loading = ref(false)
    const contactData = ref(null)
    const userReadonly = ref(false)

    onMounted(async () => {
      try {
        const response = await appService.getSections('contact_us.content')
        const content = appService.getSectionContent(response)
        if (content) {
          // Handle both nested data_values and flat structure
          contactData.value = {
            title: content.title || content.data_values?.title || '',
            address: content.address || content.data_values?.address || '',
            email_address: content.email_address || content.data_values?.email_address || '',
            contact_number: content.contact_number || content.data_values?.contact_number || ''
          }
        }
      } catch (error) {
        console.error('Error loading contact data:', error)
      }
      
      // Auto-fill user data if logged in
      if (typeof localStorage !== 'undefined' && localStorage.getItem('token')) {
        try {
          const res = await api.get('/user-info')
          const user = res.data?.data ?? res.data
          if (user) {
            if (user.fullname) {
              form.value.name = user.fullname
            } else if (user.firstname || user.lastname) {
              form.value.name = [user.firstname, user.lastname].filter(Boolean).join(' ').trim()
            }
            if (user.email) {
              form.value.email = user.email
              userReadonly.value = true
            }
          }
        } catch (error) {
          console.error('Error loading user info:', error)
        }
      }
    })

    const handleSubmit = async () => {
      loading.value = true
      try {
        const response = await appService.submitContact(form.value)
        const msg = response.message
        const successMsg = (msg?.success && msg.success[0]) || (Array.isArray(msg) ? msg[0] : msg) || 'Ticket created successfully!'
        const errorMsg = (msg?.error && (Array.isArray(msg.error) ? msg.error[0] : msg.error)) || (Array.isArray(msg) ? msg[0] : msg) || 'Failed to send message'
        if (response.status === 'success') {
          if (window.notify) window.notify('success', successMsg)
          form.value = { name: form.value.name, email: form.value.email, subject: '', message: '' }
        } else {
          if (window.notify) window.notify('error', errorMsg)
        }
      } catch (error) {
        const data = error.response?.data
        const errMsg = data?.message
        const text = Array.isArray(errMsg) ? errMsg[0] : errMsg || 'An error occurred'
        if (window.notify) window.notify('error', text)
      } finally {
        loading.value = false
      }
    }

    return {
      form,
      loading,
      contactData,
      userReadonly,
      handleSubmit
    }
  }
}
</script>
