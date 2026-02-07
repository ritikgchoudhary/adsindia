<template>
  <DashboardLayout page-title="Customer Support">
    <div class="row">
      <div class="col-lg-4 col-md-6 mb-4">
        <div class="card custom--card text-center">
          <div class="card-body">
            <div class="support-icon mb-3">
              <i class="fab fa-telegram fa-4x text-primary"></i>
            </div>
            <h5 class="mb-3">Telegram Support</h5>
            <p class="text-muted mb-3">Get instant help via Telegram</p>
            <a :href="supportLinks.telegram" target="_blank" class="btn btn--base w-100">
              <i class="fab fa-telegram me-2"></i>Join Telegram
            </a>
          </div>
        </div>
      </div>

      <div class="col-lg-4 col-md-6 mb-4">
        <div class="card custom--card text-center">
          <div class="card-body">
            <div class="support-icon mb-3">
              <i class="fab fa-whatsapp fa-4x text-success"></i>
            </div>
            <h5 class="mb-3">WhatsApp Support</h5>
            <p class="text-muted mb-3">Chat with us on WhatsApp</p>
            <a :href="supportLinks.whatsapp" target="_blank" class="btn btn-success w-100">
              <i class="fab fa-whatsapp me-2"></i>Chat on WhatsApp
            </a>
          </div>
        </div>
      </div>

      <div class="col-lg-4 col-md-6 mb-4">
        <div class="card custom--card text-center">
          <div class="card-body">
            <div class="support-icon mb-3">
              <i class="fas fa-comments fa-4x text-info"></i>
            </div>
            <h5 class="mb-3">Live Chat</h5>
            <p class="text-muted mb-3">24/7 Live chat support</p>
            <button class="btn btn--base w-100" @click="openLiveChat">
              <i class="fas fa-comments me-2"></i>Start Live Chat
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="row mt-4">
      <div class="col-12">
        <div class="card custom--card">
          <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-headset me-2"></i>Support Tickets</h5>
          </div>
          <div class="card-body">
            <p class="text-muted mb-3">You can also create a support ticket for your queries.</p>
            <router-link to="/user/ticket/open" class="btn btn--base">
              <i class="fas fa-plus me-2"></i>Create Support Ticket
            </router-link>
            <router-link to="/user/ticket" class="btn btn-outline--base ms-2">
              <i class="fas fa-list me-2"></i>View My Tickets
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script>
import { ref, onMounted } from 'vue'
import DashboardLayout from '../../components/DashboardLayout.vue'
import api from '../../services/api'

export default {
  name: 'CustomerSupport',
  components: {
    DashboardLayout
  },
  setup() {
    const supportLinks = ref({
      telegram: 'https://t.me/your_support',
      whatsapp: 'https://wa.me/your_number'
    })

    const openLiveChat = () => {
      // Implement live chat widget opening
      if (window.Tawk_API) {
        window.Tawk_API.maximize()
      } else {
        alert('Live chat is currently unavailable. Please use Telegram or WhatsApp support.')
      }
    }

    const fetchSupportLinks = async () => {
      try {
        const response = await api.get('/customer-support/links')
        if (response.data.status === 'success' && response.data.data) {
          supportLinks.value = response.data.data
        }
      } catch (error) {
        console.error('Error loading support links:', error)
      }
    }

    onMounted(() => {
      fetchSupportLinks()
    })

    return {
      supportLinks,
      openLiveChat
    }
  }
}
</script>
