<template>
  <DashboardLayout page-title="Certificates">
    <div class="row">
      <div class="col-12 mb-4">
        <div class="card custom--card">
          <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-certificate me-2"></i>Available Certificates</h5>
          </div>
          <div class="card-body">
            <p class="text-muted">Apply for paid certificates to showcase your skills and achievements.</p>
          </div>
        </div>
      </div>

      <div v-for="certificate in certificates" :key="certificate?.id || Math.random()" class="col-lg-4 col-md-6 mb-4" v-if="certificate && certificate.id">
        <div class="card custom--card">
          <div class="card-body text-center">
            <div class="certificate-icon mb-3">
              <i class="fas fa-certificate fa-4x text-warning"></i>
            </div>
            <h5 class="mb-2">{{ certificate.name }}</h5>
            <p class="text-muted mb-3">{{ certificate.description }}</p>
            <div class="mb-3">
              <h4 class="text-primary">{{ currencySymbol }}{{ formatAmount(certificate.price) }}</h4>
            </div>
            <button class="btn btn--base w-100" @click="applyCertificate(certificate)" :disabled="certificate.is_applied">
              <span v-if="certificate.is_applied">
                <i class="fas fa-check me-2"></i>Applied
              </span>
              <span v-else>
                <i class="fas fa-paper-plane me-2"></i>Apply Now
              </span>
            </button>
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
  name: 'Certificates',
  components: {
    DashboardLayout
  },
  setup() {
    const certificates = ref([])
    const currencySymbol = ref('₹')

    const formatAmount = (amount) => {
      if (!amount && amount !== 0) return '0.00'
      return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    }

    const applyCertificate = async (certificate) => {
      if (!certificate || !certificate.id || certificate.is_applied) return

      if (!confirm(`Are you sure you want to apply for ${certificate.name} for ${currencySymbol.value}${formatAmount(certificate.price)}?`)) {
        return
      }

      try {
        const response = await api.post('/certificates/apply', { certificate_id: certificate.id })
        if (response.data.status === 'success') {
          if (window.notify) {
            window.notify('success', 'Certificate application submitted successfully!')
          }
          fetchCertificates()
        }
      } catch (error) {
        console.error('Error applying certificate:', error)
        if (window.notify) {
          window.notify('error', error.response?.data?.message || 'Failed to apply certificate')
        }
      }
    }

    const fetchCertificates = async () => {
      try {
        const response = await api.get('/certificates')
        if (response.data.status === 'success') {
          certificates.value = response.data.data || []
          currencySymbol.value = response.data.currency_symbol || '₹'
        }
      } catch (error) {
        console.error('Error loading certificates:', error)
      }
    }

    onMounted(() => {
      fetchCertificates()
    })

    return {
      certificates,
      currencySymbol,
      formatAmount,
      applyCertificate
    }
  }
}
</script>
