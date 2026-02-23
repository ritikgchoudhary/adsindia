<template>
  <DashboardLayout page-title="Support Tickets" :dark-theme="true">
    <div class="tw-grid tw-grid-cols-1 tw-gap-8">
      
      <div class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-slate-200 tw-overflow-hidden">
        <div class="tw-bg-slate-50 tw-p-5 tw-border-b tw-border-slate-200 tw-flex tw-justify-between tw-items-center">
          <h5 class="tw-text-slate-900 tw-font-bold tw-text-lg tw-m-0 tw-flex tw-items-center">
            <i class="fas fa-ticket-alt tw-mr-2 tw-text-indigo-600"></i>Support Tickets
          </h5>
          <router-link to="/user/ticket/open" class="tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-font-bold tw-text-sm tw-px-4 tw-py-2 tw-rounded-xl tw-shadow-lg tw-shadow-indigo-500/20 tw-transition-all tw-flex tw-items-center tw-no-underline">
            <i class="fas fa-plus tw-mr-2"></i>Open New Ticket
          </router-link>
        </div>
        
        <div class="tw-p-0">
          <div class="tw-overflow-x-auto">
            <table class="tw-w-full tw-text-left tw-border-collapse">
              <thead>
                <tr class="tw-bg-slate-50 tw-border-b tw-border-slate-200">
                  <th class="tw-p-4 tw-text-xs tw-font-bold tw-text-slate-500 tw-uppercase tw-tracking-wider">Subject</th>
                  <th class="tw-p-4 tw-text-xs tw-font-bold tw-text-slate-500 tw-uppercase tw-tracking-wider">Status</th>
                  <th class="tw-p-4 tw-text-xs tw-font-bold tw-text-slate-500 tw-uppercase tw-tracking-wider">Priority</th>
                  <th class="tw-p-4 tw-text-xs tw-font-bold tw-text-slate-500 tw-uppercase tw-tracking-wider">Last Reply</th>
                  <th class="tw-p-4 tw-text-xs tw-font-bold tw-text-slate-500 tw-uppercase tw-tracking-wider tw-text-right">Action</th>
                </tr>
              </thead>
              <tbody class="tw-divide-y tw-divide-slate-100">
                <tr v-for="support in supports" :key="support?.id || support?.ticket" v-show="support && (support.id || support.ticket)" class="hover:tw-bg-slate-50/50 tw-transition-colors">
                  <td class="tw-p-4">
                    <router-link :to="`/user/ticket/${support.ticket}`" class="tw-font-bold tw-text-indigo-600 hover:tw-text-indigo-700 hover:tw-underline tw-no-underline tw-transition-colors">
                      [Ticket#{{ support.ticket }}] {{ support.subject }}
                    </router-link>
                  </td>
                  <td class="tw-p-4">
                    <div v-html="support.status_badge" class="[&_.badge]:tw-px-2.5 [&_.badge]:tw-py-1 [&_.badge]:tw-rounded-lg [&_.badge]:tw-text-xs [&_.badge]:tw-font-bold"></div>
                  </td>
                  <td class="tw-p-4">
                    <span v-if="support.priority == 1" class="tw-inline-block tw-px-2.5 tw-py-1 tw-rounded-lg tw-text-xs tw-font-bold tw-bg-slate-100 tw-text-slate-600">Low</span>
                    <span v-else-if="support.priority == 2" class="tw-inline-block tw-px-2.5 tw-py-1 tw-rounded-lg tw-text-xs tw-font-bold tw-bg-amber-100 tw-text-amber-700">Medium</span>
                    <span v-else-if="support.priority == 3" class="tw-inline-block tw-px-2.5 tw-py-1 tw-rounded-lg tw-text-xs tw-font-bold tw-bg-rose-100 tw-text-rose-700">High</span>
                    <span v-else class="tw-text-slate-400">–</span>
                  </td>
                  <td class="tw-p-4">
                    <span class="tw-text-sm tw-text-slate-500">{{ support.last_reply_human || '–' }}</span>
                  </td>
                  <td class="tw-p-4 tw-text-right">
                    <router-link :to="`/user/ticket/${support.ticket}`" class="tw-inline-flex tw-items-center tw-justify-center tw-w-9 tw-h-9 tw-rounded-lg tw-bg-indigo-50 tw-text-indigo-600 hover:tw-bg-indigo-600 hover:tw-text-white tw-transition-colors" title="View ticket">
                      <i class="fas fa-eye"></i>
                    </router-link>
                  </td>
                </tr>
                
                <tr v-if="!loading && supports.length === 0">
                   <td colspan="5" class="tw-p-10 tw-text-center">
                      <div class="tw-w-16 tw-h-16 tw-mx-auto tw-bg-slate-50 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-text-slate-300 tw-mb-4">
                         <i class="fas fa-ticket-alt tw-text-3xl"></i>
                      </div>
                      <h5 class="tw-text-slate-900 tw-font-bold tw-text-base tw-mb-2">No support tickets found</h5>
                      <p class="tw-text-slate-500 tw-text-sm tw-mb-6">Create a new ticket if you need assistance.</p>
                      <router-link to="/user/ticket/open" class="tw-inline-flex tw-items-center tw-justify-center tw-px-6 tw-py-2.5 tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-font-bold tw-rounded-xl tw-shadow-lg tw-shadow-indigo-500/20 tw-transition-all tw-no-underline">
                        <i class="fas fa-plus tw-mr-2"></i> Open New Ticket
                      </router-link>
                   </td>
                </tr>
                
              </tbody>
            </table>
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
  name: 'SupportTickets',
  components: {
    DashboardLayout
  },
  setup() {
    const supports = ref([])
    const loading = ref(true)

    const fetchTickets = async () => {
      loading.value = true
      try {
        const response = await api.get('/ticket')
        if (response.data?.status === 'success') {
          const data = response.data.data
          const raw = data?.tickets
          supports.value = Array.isArray(raw) ? raw : (raw?.data ?? [])
        }
      } catch (error) {
        console.error('Error loading tickets:', error)
        supports.value = []
      } finally {
        loading.value = false
      }
    }

    onMounted(() => {
      fetchTickets()
    })

    return {
      supports,
      loading
    }
  }
}
</script>
