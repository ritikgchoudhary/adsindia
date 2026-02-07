<template>
  <DashboardLayout page-title="Support Ticket">
    <div class="campaign-table">
      <div class="card custom--card">
        <div class="card-body">
          <table class="table table--responsive--lg">
            <thead>
              <tr>
                <th>Subject</th>
                <th>Status</th>
                <th>Priority</th>
                <th>Last Reply</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <template v-for="support in supports" :key="support?.id || Math.random()">
                <tr v-if="support && support.id">
                <td>
                  <router-link :to="`/user/ticket/${support.ticket}`" class="fw-bold">
                    [Ticket#{{ support.ticket }}] {{ support.subject }}
                  </router-link>
                </td>
                <td>
                  <span v-html="support.status_badge"></span>
                </td>
                <td>
                  <span v-if="support.priority == 1" class="badge badge--secondary">Low</span>
                  <span v-else-if="support.priority == 2" class="badge badge--warning">Medium</span>
                  <span v-else-if="support.priority == 3" class="badge badge--danger">High</span>
                </td>
                <td>{{ support.last_reply_human }}</td>
                <td>
                  <router-link :to="`/user/ticket/${support.ticket}`" class="btn btn--base btn--sm">
                    <i class="las la-lg la-desktop"></i>
                  </router-link>
                </td>
                </tr>
              </template>
              <tr v-if="supports.length === 0">
                <td colspan="100%" class="text-center">Data not found</td>
              </tr>
            </tbody>
          </table>
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
  name: 'SupportTickets',
  components: {
    DashboardLayout
  },
  setup() {
    const supports = ref([])

    const fetchTickets = async () => {
      try {
        const response = await api.get('/ticket')
        if (response.data.status === 'success') {
          supports.value = response.data.data || []
        }
      } catch (error) {
        console.error('Error loading tickets:', error)
      }
    }

    onMounted(() => {
      fetchTickets()
    })

    return {
      supports
    }
  }
}
</script>
