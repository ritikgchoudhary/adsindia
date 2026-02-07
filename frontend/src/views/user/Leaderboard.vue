<template>
  <DashboardLayout page-title="Leaderboard">
    <div class="row">
      <div class="col-12 mb-4">
        <div class="card custom--card">
          <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-trophy me-2"></i>Top Earners Leaderboard</h5>
          </div>
          <div class="card-body">
            <div class="d-flex gap-3 mb-4">
              <button 
                class="btn" 
                :class="selectedType === 'weekly' ? 'btn--base' : 'btn-outline--base'"
                @click="changeType('weekly')">
                Weekly Earning
              </button>
              <button 
                class="btn" 
                :class="selectedType === 'monthly' ? 'btn--base' : 'btn-outline--base'"
                @click="changeType('monthly')">
                Monthly Earning
              </button>
              <button 
                class="btn" 
                :class="selectedType === 'alltime' ? 'btn--base' : 'btn-outline--base'"
                @click="changeType('alltime')">
                All Time Earning
              </button>
            </div>

            <div class="table-responsive">
              <table class="table table--responsive--lg">
                <thead>
                  <tr>
                    <th>Rank</th>
                    <th>Username</th>
                    <th>Earning</th>
                  </tr>
                </thead>
                <tbody>
                  <template v-for="(entry, index) in leaderboard" :key="entry?.rank || index">
                    <tr v-if="entry && entry.rank" 
                      :class="{ 'table-warning': index < 3 }">
                    <td>
                      <span v-if="index === 0" class="badge badge--warning">
                        <i class="fas fa-trophy me-1"></i>1st
                      </span>
                      <span v-else-if="index === 1" class="badge badge--secondary">
                        <i class="fas fa-medal me-1"></i>2nd
                      </span>
                      <span v-else-if="index === 2" class="badge badge--warning">
                        <i class="fas fa-award me-1"></i>3rd
                      </span>
                      <span v-else>#{{ entry.rank }}</span>
                    </td>
                    <td>{{ entry.username }}</td>
                    <td class="text-success">
                      <strong>{{ currencySymbol }}{{ formatAmount(entry.earning) }}</strong>
                    </td>
                  </tr>
                  </template>
                  <tr v-if="leaderboard.length === 0">
                    <td colspan="3" class="text-center text-muted">No data available</td>
                  </tr>
                </tbody>
              </table>
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
  name: 'Leaderboard',
  components: {
    DashboardLayout
  },
  setup() {
    const leaderboard = ref([])
    const selectedType = ref('weekly')
    const currencySymbol = ref('₹')

    const formatAmount = (amount) => {
      if (!amount && amount !== 0) return '0.00'
      return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    }

    const changeType = (type) => {
      selectedType.value = type
      fetchLeaderboard()
    }

    const fetchLeaderboard = async () => {
      try {
        const response = await api.get('/leaderboard', {
          params: { type: selectedType.value }
        })
        if (response.data.status === 'success') {
          leaderboard.value = response.data.data || []
          currencySymbol.value = response.data.currency_symbol || '₹'
        }
      } catch (error) {
        console.error('Error loading leaderboard:', error)
      }
    }

    onMounted(() => {
      fetchLeaderboard()
    })

    return {
      leaderboard,
      selectedType,
      currencySymbol,
      formatAmount,
      changeType
    }
  }
}
</script>
