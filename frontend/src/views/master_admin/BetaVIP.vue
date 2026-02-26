<template>
  <MasterAdminLayout>
    <div class="beta-vip">
      <div class="tw-flex tw-items-center tw-justify-between tw-mb-8">
        <div>
          <router-link to="/master_admin/beta-features" class="tw-text-indigo-400 tw-text-sm tw-no-underline hover:tw-underline tw-mb-2 tw-inline-block">
            <i class="fas fa-arrow-left tw-mr-1"></i> Back to Hub
          </router-link>
          <h2 class="tw-text-2xl tw-font-bold tw-text-white tw-flex tw-items-center tw-gap-3">
             <i class="fas fa-crown tw-text-indigo-400"></i> VIP Membership Settings
          </h2>
        </div>
        <button @click="openModal()" class="tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-px-6 tw-py-2.5 tw-rounded-xl tw-font-bold tw-transition-all tw-flex tw-items-center tw-gap-2">
          <i class="fas fa-plus"></i> Create New Plan
        </button>
      </div>

      <div class="tw-grid tw-grid-cols-1 tw-gap-6">
        <div class="tw-bg-slate-900/40 tw-backdrop-blur-xl tw-border tw-border-white/10 tw-rounded-3xl tw-overflow-hidden">
          <div class="tw-overflow-x-auto">
            <table class="tw-w-full tw-text-left tw-border-collapse">
              <thead>
                <tr class="tw-bg-white/5">
                  <th class="tw-px-6 tw-py-4 tw-text-slate-400 tw-font-bold tw-text-xs tw-uppercase tw-tracking-wider">Plan Name</th>
                  <th class="tw-px-6 tw-py-4 tw-text-slate-400 tw-font-bold tw-text-xs tw-uppercase tw-tracking-wider">Price</th>
                  <th class="tw-px-6 tw-py-4 tw-text-slate-400 tw-font-bold tw-text-xs tw-uppercase tw-tracking-wider">Duration</th>
                  <th class="tw-px-6 tw-py-4 tw-text-slate-400 tw-font-bold tw-text-xs tw-uppercase tw-tracking-wider">Benefits</th>
                  <th class="tw-px-6 tw-py-4 tw-text-slate-400 tw-font-bold tw-text-xs tw-uppercase tw-tracking-wider">Status</th>
                  <th class="tw-px-6 tw-py-4 tw-text-slate-400 tw-font-bold tw-text-xs tw-uppercase tw-tracking-wider">Action</th>
                </tr>
              </thead>
              <tbody class="tw-divide-y tw-divide-white/5">
                <tr v-for="plan in plans" :key="plan.id" class="hover:tw-bg-white/[0.02] tw-transition-colors">
                  <td class="tw-px-6 tw-py-4">
                    <span class="tw-text-white tw-font-bold">{{ plan.name }}</span>
                  </td>
                  <td class="tw-px-6 tw-py-4">
                    <span class="tw-text-emerald-400 tw-font-bold">₹{{ plan.price }}</span>
                  </td>
                  <td class="tw-px-6 tw-py-4">
                    <span class="tw-text-slate-300">{{ plan.months }} Months</span>
                  </td>
                  <td class="tw-px-6 tw-py-4">
                    <div class="tw-flex tw-flex-wrap tw-gap-2">
                      <span class="tw-bg-indigo-500/10 tw-text-indigo-400 tw-px-2 tw-py-1 tw-rounded-lg tw-text-[10px] tw-font-bold">
                        {{ plan.withdrawal_fee_discount }}% Fee Off
                      </span>
                      <span v-if="plan.priority_payout" class="tw-bg-amber-500/10 tw-text-amber-500 tw-px-2 tw-py-1 tw-rounded-lg tw-text-[10px] tw-font-bold">
                        Priority Payout
                      </span>
                    </div>
                  </td>
                  <td class="tw-px-6 tw-py-4">
                    <span :class="plan.enabled ? 'tw-text-emerald-500' : 'tw-text-red-500'" class="tw-text-xs tw-font-bold">
                      {{ plan.enabled ? 'Active' : 'Disabled' }}
                    </span>
                  </td>
                  <td class="tw-px-6 tw-py-4">
                    <div class="tw-flex tw-items-center tw-gap-3">
                      <button @click="openModal(plan)" class="tw-text-indigo-400 hover:tw-text-indigo-300 tw-transition-colors">
                        <i class="fas fa-edit"></i>
                      </button>
                      <button @click="togglePlan(plan.id)" :class="plan.enabled ? 'tw-text-red-400' : 'tw-text-emerald-400'">
                        <i :class="plan.enabled ? 'fas fa-toggle-on' : 'fas fa-toggle-off'"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Plan Modal -->
      <div v-if="showModal" class="tw-fixed tw-inset-0 tw-z-[100] tw-flex tw-items-center tw-justify-center tw-p-4 tw-bg-black/80 tw-backdrop-blur-sm">
        <div class="tw-bg-slate-900 tw-border tw-border-white/10 tw-rounded-3xl tw-w-full tw-max-w-md tw-overflow-hidden tw-animate-in tw-fade-in tw-zoom-in tw-duration-300">
          <div class="tw-px-6 tw-py-5 tw-border-b tw-border-white/5 tw-flex tw-items-center tw-justify-between">
            <h3 class="tw-text-lg tw-font-bold tw-text-white">{{ editingPlan.id ? 'Edit Plan' : 'Create New Plan' }}</h3>
            <button @click="showModal = false" class="tw-text-slate-500 hover:tw-text-white">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <div class="tw-p-6 tw-space-y-4">
            <div>
              <label class="tw-block tw-text-xs tw-font-bold tw-text-slate-500 tw-uppercase tw-mb-2">Plan Name</label>
              <input v-model="editingPlan.name" type="text" class="tw-w-full tw-bg-white/5 tw-border tw-border-white/10 tw-rounded-xl tw-px-4 tw-py-3 tw-text-white focus:tw-border-indigo-500 tw-outline-none" placeholder="VIP Gold">
            </div>
            <div class="tw-grid tw-grid-cols-2 tw-gap-4">
              <div>
                <label class="tw-block tw-text-xs tw-font-bold tw-text-slate-500 tw-uppercase tw-mb-2">Price (₹)</label>
                <input v-model="editingPlan.price" type="number" class="tw-w-full tw-bg-white/5 tw-border tw-border-white/10 tw-rounded-xl tw-px-4 tw-py-3 tw-text-white focus:tw-border-indigo-500 tw-outline-none" placeholder="199">
              </div>
              <div>
                <label class="tw-block tw-text-xs tw-font-bold tw-text-slate-500 tw-uppercase tw-mb-2">Months</label>
                <input v-model="editingPlan.months" type="number" class="tw-w-full tw-bg-white/5 tw-border tw-border-white/10 tw-rounded-xl tw-px-4 tw-py-3 tw-text-white focus:tw-border-indigo-500 tw-outline-none" placeholder="1">
              </div>
            </div>
             <div>
              <label class="tw-block tw-text-xs tw-font-bold tw-text-slate-500 tw-uppercase tw-mb-2">Fee Discount (%)</label>
              <input v-model="editingPlan.withdrawal_fee_discount" type="number" class="tw-w-full tw-bg-white/5 tw-border tw-border-white/10 tw-rounded-xl tw-px-4 tw-py-3 tw-text-white focus:tw-border-indigo-500 tw-outline-none" placeholder="100">
            </div>
            <div class="tw-flex tw-items-center tw-gap-3">
              <input v-model="editingPlan.priority_payout" type="checkbox" id="priority" class="tw-w-5 tw-h-5 tw-rounded tw-bg-white/5 tw-border-white/10 tw-text-indigo-600">
              <label for="priority" class="tw-text-sm tw-text-slate-300">Enable Priority Payout</label>
            </div>
          </div>
          <div class="tw-px-6 tw-py-5 tw-bg-white/5 tw-flex tw-gap-3">
            <button @click="savePlan" class="tw-flex-1 tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-py-3 tw-rounded-xl tw-font-bold tw-transition-all">
              {{ editingPlan.id ? 'Update Plan' : 'Create Plan' }}
            </button>
            <button @click="showModal = false" class="tw-px-6 tw-bg-white/5 hover:tw-bg-white/10 tw-text-white tw-py-3 tw-rounded-xl tw-font-bold tw-transition-all border-0">
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>
  </MasterAdminLayout>
</template>

<script>
import MasterAdminLayout from '../../components/master_admin/MasterAdminLayout.vue'
import api from '../../services/api'
import { notify } from '@kyvg/vue3-notification'

export default {
  name: 'BetaVIP',
  components: { MasterAdminLayout },
  data() {
    return {
      plans: [],
      showModal: false,
      editingPlan: {
        id: null,
        name: '',
        price: 0,
        months: 1,
        withdrawal_fee_discount: 100,
        priority_payout: true
      }
    }
  },
  methods: {
    async fetchPlans() {
      try {
        const res = await api.get('/admin/beta/vip/plans')
        this.plans = res.data.data.plans
      } catch (e) {
        notify({ type: 'error', text: 'Failed to fetch plans' })
      }
    },
    openModal(plan = null) {
      if (plan) {
        this.editingPlan = { ...plan }
      } else {
        this.editingPlan = { id: null, name: '', price: 199, months: 1, withdrawal_fee_discount: 100, priority_payout: true }
      }
      this.showModal = true
    },
    async savePlan() {
      try {
        await api.post('/admin/beta/vip/plans/save', this.editingPlan)
        notify({ type: 'success', text: 'Plan saved successfully' })
        this.showModal = false
        this.fetchPlans()
      } catch (e) {
        notify({ type: 'error', text: e.response?.data?.message || 'Failed to save plan' })
      }
    },
    async togglePlan(id) {
      try {
        await api.post('/admin/beta/vip/plans/toggle', { id })
        this.fetchPlans()
      } catch (e) {
        notify({ type: 'error', text: 'Update failed' })
      }
    }
  },
  mounted() {
    this.fetchPlans()
  }
}
</script>
