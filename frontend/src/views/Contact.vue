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
              <h4 class="contact-wrapper__title">{{ contactData?.title || 'Contact Us' }}</h4>

              <form @submit.prevent="handleSubmit" class="contact-form">
                <div class="row">
                  <div class="col-sm-6">
                    <div class="form-group">
                      <label class="form--label">Name</label>
                      <input v-model="form.name" type="text" name="name" class="form--control" required>
                    </div>
                  </div>

                  <div class="col-sm-6">
                    <div class="form-group">
                      <label class="form--label">Email</label>
                      <input v-model="form.email" type="email" name="email" class="form--control" required>
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
                    <p class="contact-item__desc">{{ contactData?.address || '' }}</p>
                  </div>
                </div>
                <div class="contact-item">
                  <span class="contact-item__icon"><i class="las la-envelope"></i></span>
                  <div class="contact-item__content">
                    <h6 class="contact-item__title">Email address</h6>
                    <p class="contact-item__desc">
                      <a :href="`mailto:${contactData?.email_address || ''}`" class="link">
                        {{ contactData?.email_address || '' }}
                      </a>
                    </p>
                  </div>
                </div>
                <div class="contact-item bg-success">
                  <span class="contact-item__icon"><i class="las la-phone-alt"></i></span>
                  <div class="contact-item__content">
                    <h6 class="contact-item__title">Phone number</h6>
                    <p class="contact-item__desc">
                      <a :href="`tel:${contactData?.contact_number || ''}`" class="link">
                        {{ contactData?.contact_number || '' }}
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

    onMounted(async () => {
      try {
        const response = await appService.getSections('contact_us')
        contactData.value = response.data?.content?.data_values || null
      } catch (error) {
        console.error('Error loading contact data:', error)
      }
    })

    const handleSubmit = async () => {
      loading.value = true
      try {
        const response = await appService.submitContact(form.value)
        if (response.status === 'success') {
          if (window.notify) {
            window.notify('success', response.message || 'Message sent successfully!')
          }
          form.value = { name: '', email: '', subject: '', message: '' }
        } else {
          if (window.notify) {
            window.notify('error', response.message || 'Failed to send message')
          }
        }
      } catch (error) {
        if (window.notify) {
          window.notify('error', error.response?.data?.message || 'An error occurred')
        }
      } finally {
        loading.value = false
      }
    }

    return {
      form,
      loading,
      contactData,
      handleSubmit
    }
  }
}
</script>
