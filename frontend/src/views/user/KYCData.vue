<template>
  <DashboardLayout page-title="KYC Data">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-12">
          <div class="card custom--card">
            <div class="card-body">
              <ul v-if="kycData && kycData.length > 0" class="list-group">
                <li v-for="(item, index) in kycData" :key="index" 
                    v-show="item.value"
                    class="list-group-item d-flex justify-content-between align-items-center">
                  {{ item.name }}
                  <span>
                    <span v-if="item.type === 'checkbox'">{{ Array.isArray(item.value) ? item.value.join(', ') : item.value }}</span>
                    <a v-else-if="item.type === 'file'" :href="item.download_url" target="_blank">
                      <i class="fa-regular fa-file"></i> Attachment
                    </a>
                    <p v-else>{{ item.value }}</p>
                  </span>
                </li>
              </ul>
              <h5 v-else class="text-center mb-0">KYC data not found</h5>
            </div>
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
  name: 'KYCData',
  components: {
    DashboardLayout
  },
  setup() {
    const kycData = ref([])

    const fetchKYCData = async () => {
      try {
        const response = await api.get('/kyc-data')
        if (response.data.status === 'success') {
          kycData.value = response.data.data?.kyc_data || []
        }
      } catch (error) {
        console.error('Error loading KYC data:', error)
      }
    }

    onMounted(() => {
      fetchKYCData()
    })

    return {
      kycData
    }
  }
}
</script>
