<template>
  <DashboardLayout page-title="Withdraw Log">
    <div class="row justify-content-center mt-2">
      <div class="col-lg-12">
        <div class="d-flex justify-content-end">
          <form @submit.prevent="handleSearch">
            <div class="mb-3">
              <div class="input-group">
                <input type="search" v-model="searchQuery" name="search" class="form-control form--control" placeholder="Search by transactions">
                <button type="submit" class="input-group-text bg--base text-white border-0">
                  <i class="las la-search"></i>
                </button>
              </div>
            </div>
          </form>
        </div>
        <div class="campaign-table mt-3">
          <div class="card custom--card">
            <div class="card-body">
              <table class="table table--responsive--xl">
                <thead>
                  <tr>
                    <th>Gateway | Transaction</th>
                    <th class="text-center">Initiated</th>
                    <th class="text-center">Amount</th>
                    <th class="text-center">Conversion</th>
                    <th class="text-center">Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <template v-for="withdraw in withdraws" :key="withdraw?.id || Math.random()">
                    <tr v-if="withdraw && withdraw.id">
                    <td>
                      <div>
                        <span class="fw-bold text--base">{{ withdraw.method?.name }}</span><br>
                        <small>{{ withdraw.trx }}</small>
                      </div>
                    </td>
                    <td>
                      <div>
                        {{ formatDateTime(withdraw.created_at) }} <br>
                        {{ withdraw.created_at_human }}
                      </div>
                    </td>
                    <td>
                      <div>
                        {{ formatAmount(withdraw.amount) }} -
                        <span class="text--danger" data-bs-toggle="tooltip" title="Processing Charge">
                          {{ formatAmount(withdraw.charge) }}
                        </span>
                        <br>
                        <strong data-bs-toggle="tooltip" title="Amount after charge">
                          {{ formatAmount(withdraw.amount - withdraw.charge) }}
                        </strong>
                      </div>
                    </td>
                    <td>
                      <div>
                        {{ formatAmount(1) }} = {{ formatAmount(withdraw.rate, false) }} {{ withdraw.currency }}
                        <br>
                        <strong>{{ formatAmount(withdraw.final_amount, false) }} {{ withdraw.currency }}</strong>
                      </div>
                    </td>
                    <td>
                      <div v-html="withdraw.status_badge"></div>
                    </td>
                    <td>
                      <div>
                        <button class="btn btn--sm btn--base detailBtn" @click="showDetails(withdraw)">
                          <i class="la la-lg la-desktop"></i>
                        </button>
                      </div>
                    </td>
                    </tr>
                  </template>
                  <tr v-if="withdraws.length === 0">
                    <td class="text-muted text-center" colspan="100%">
                      <div>Data not found</div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Detail Modal -->
    <div id="detailModal" class="modal custom--modal fade" :class="{ show: showModal }" tabindex="-1" role="dialog" v-if="showModal">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Details</h5>
            <span type="button" class="close" @click="showModal = false" aria-label="Close">
              <i class="las la-times"></i>
            </span>
          </div>
          <div class="modal-body">
            <ul class="list-group userData">
              <li v-for="(info, index) in selectedWithdrawDetails" :key="index" class="list-group-item d-flex justify-content-between align-items-center">
                <span>{{ info.name }}</span>
                <span v-if="info.type !== 'file'">{{ info.value }}</span>
                <span v-else><a :href="info.value" target="_blank"><i class="fa-regular fa-file"></i> Attachment</a></span>
              </li>
            </ul>
            <div class="feedback" v-if="selectedWithdrawFeedback">
              <div class="my-3">
                <strong>Admin Feedback</strong>
                <p>{{ selectedWithdrawFeedback }}</p>
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
  name: 'WithdrawHistory',
  components: {
    DashboardLayout
  },
  setup() {
    const withdraws = ref([])
    const searchQuery = ref('')
    const showModal = ref(false)
    const selectedWithdrawDetails = ref([])
    const selectedWithdrawFeedback = ref('')

    const formatAmount = (amount, currencyFormat = true) => {
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

    const showDetails = (withdraw) => {
      selectedWithdrawDetails.value = withdraw.withdraw_information || []
      selectedWithdrawFeedback.value = withdraw.admin_feedback || ''
      showModal.value = true
    }

    const handleSearch = () => {
      fetchWithdraws()
    }

    const fetchWithdraws = async () => {
      try {
        const params = {}
        if (searchQuery.value) {
          params.search = searchQuery.value
        }
        const response = await api.get('/withdraw/history', { params })
        if (response.data.status === 'success') {
          withdraws.value = response.data.data || []
        }
      } catch (error) {
        console.error('Error loading withdraws:', error)
      }
    }

    onMounted(() => {
      fetchWithdraws()
      if (window.jQuery) {
        setTimeout(() => {
          window.jQuery('[data-bs-toggle="tooltip"]').tooltip()
        }, 100)
      }
    })

    return {
      withdraws,
      searchQuery,
      showModal,
      selectedWithdrawDetails,
      selectedWithdrawFeedback,
      formatAmount,
      formatDateTime,
      showDetails,
      handleSearch
    }
  }
}
</script>

<style scoped>
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
