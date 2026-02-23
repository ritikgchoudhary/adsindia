<template>
  <DashboardLayout page-title="Conversion Log" :dark-theme="true">
    <div class="d-flex justify-content-end">
      <form @submit.prevent="handleSearch">
        <div class="input-group">
          <input type="search" v-model="searchQuery" name="search" class="form-control form--control" placeholder="Search by campaign">
          <button type="submit" class="input-group-text bg--base text-white border-0">
            <i class="las la-search"></i>
          </button>
        </div>
      </form>
    </div>
    <div class="campaign-table">
      <div class="card custom--card">
        <div class="card-body">
          <table class="table table--responsive--lg">
            <thead>
              <tr>
                <th>Campaign</th>
                <th>System Commission</th>
                <th>Affiliate Commission</th>
                <th>Paid</th>
                <th>Created At</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <template v-for="conversion in conversions" :key="conversion?.id || Math.random()">
                <tr v-if="conversion && conversion.id">
                <td>
                  {{ conversion.campaign?.title || '-' }}
                </td>
                <td>
                  {{ formatAmount(conversion.admin_commission) }}
                </td>
                <td>
                  {{ formatAmount(conversion.user_payout) }}
                </td>
                <td>
                  <span v-if="conversion.is_paid == 1" class="badge badge--success">Paid</span>
                  <span v-else class="badge badge--warning">Unpaid</span>
                </td>
                <td>
                  {{ formatDateTime(conversion.created_at) }}
                  <br>
                  <small>{{ conversion.created_at_human }}</small>
                </td>
                <td>
                  <button type="button" class="btn btn--sm btn-outline--base viewConversionBtn" @click="showConversionModal(conversion)">
                    <i class="las la-eye"></i> View
                  </button>
                </td>
                </tr>
              </template>
              <tr v-if="conversions.length === 0">
                <td colspan="100%" class="text-center text-muted">
                  No conversions found
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Conversion Details Modal -->
    <div class="modal fade custom--modal" :class="{ show: showModal }" id="conversionModal" tabindex="-1" role="dialog" v-if="showModal">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Conversion Details</h5>
            <button type="button" class="close cursor-pointer" @click="showModal = false" aria-label="Close">
              <i class="las la-times"></i>
            </button>
          </div>
          <div class="modal-body" v-if="selectedConversion">
            <div class="row gy-3">
              <div class="col-md-6">
                <div class="d-flex flex-column">
                  <span class="fw-bold">User</span>
                  <span class="mt-1">{{ selectedConversion.user?.username || '-' }}</span>
                </div>
              </div>
              <div class="col-md-6">
                <div class="d-flex flex-column">
                  <span class="fw-bold">Payout</span>
                  <span class="text-muted mt-1">{{ formatAmount(selectedConversion.user_payout) }} {{ currencyText }}</span>
                </div>
              </div>
              <div class="col-md-6">
                <div class="d-flex flex-column">
                  <span class="fw-bold">Admin Commission</span>
                  <span class="text-muted mt-1">{{ formatAmount(selectedConversion.admin_commission) }} {{ currencyText }}</span>
                </div>
              </div>
              <div class="col-md-6">
                <div class="d-flex flex-column">
                  <span class="fw-bold">IP Address</span>
                  <span class="text-muted mt-1">{{ selectedConversion.ip_address || '-' }}</span>
                </div>
              </div>
              <div class="col-md-6">
                <div class="d-flex flex-column">
                  <span class="fw-bold">Tracking Type</span>
                  <span class="text-muted mt-1">
                    {{ selectedConversion.tracking_type == 2 ? 'JS' : selectedConversion.tracking_type == 3 ? 'Server' : 'HTML' }}
                  </span>
                </div>
              </div>
              <div class="col-md-6">
                <div class="d-flex flex-column">
                  <span class="fw-bold">Paid Status</span>
                  <span class="text-muted mt-1">{{ selectedConversion.is_paid == 1 ? 'Paid' : 'Unpaid' }}</span>
                </div>
              </div>
              <div class="col-md-12">
                <div class="d-flex flex-column">
                  <span class="fw-bold">User Agent</span>
                  <div class="mt-2 p-3 rounded agent-box">{{ selectedConversion.user_agent || '-' }}</div>
                </div>
              </div>
              <div class="col-md-12">
                <div class="d-flex flex-column">
                  <span class="fw-bold">Details</span>
                  <div class="mt-2 p-3 rounded agent-box">{{ selectedConversion.details || '-' }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div v-if="showModal" class="modal-backdrop fade show" @click="showModal = false"></div>
  </DashboardLayout>
</template>

<script>
import { ref, onMounted } from 'vue'
import DashboardLayout from '../../components/DashboardLayout.vue'
import api from '../../services/api'

export default {
  name: 'ConversionLog',
  components: {
    DashboardLayout
  },
  setup() {
    const conversions = ref([])
    const searchQuery = ref('')
    const showModal = ref(false)
    const selectedConversion = ref(null)
    const currencyText = ref('USD')
    const loading = ref(false)

    const formatAmount = (amount) => {
      if (!amount && amount !== 0) return '0.00'
      return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    }

    const formatDateTime = (dateString) => {
      if (!dateString) return '-'
      const date = new Date(dateString)
      const year = date.getFullYear()
      const month = String(date.getMonth() + 1).padStart(2, '0')
      const day = String(date.getDate()).padStart(2, '0')
      const hour12 = date.getHours() % 12 || 12
      const minutes = String(date.getMinutes()).padStart(2, '0')
      const ampm = date.getHours() >= 12 ? 'PM' : 'AM'
      return `${year}-${month}-${day} ${hour12}:${minutes} ${ampm}`
    }

    const showConversionModal = (conversion) => {
      selectedConversion.value = conversion
      showModal.value = true
    }

    const handleSearch = () => {
      fetchConversions()
    }

    const fetchConversions = async () => {
      loading.value = true
      try {
        const params = {}
        if (searchQuery.value) {
          params.search = searchQuery.value
        }
        const response = await api.get('/conversion/log', { params })
        if (response.data.status === 'success') {
          conversions.value = response.data.data || []
        }
      } catch (error) {
        console.error('Error loading conversions:', error)
      } finally {
        loading.value = false
      }
    }

    onMounted(() => {
      fetchConversions()
    })

    return {
      conversions,
      searchQuery,
      showModal,
      selectedConversion,
      currencyText,
      formatAmount,
      formatDateTime,
      showConversionModal,
      handleSearch
    }
  }
}
</script>

<style scoped>
.agent-box {
  background-color: var(--bs-dark);
  border: 1px solid #e3e6f0;
  word-break: break-word;
  color: #e3e6f0;
  font-size: 14px;
}

.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 1040;
}

.modal.show {
  display: block;
  z-index: 1050;
}
</style>
