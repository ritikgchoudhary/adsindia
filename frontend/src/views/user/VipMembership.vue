<template>
  <div class="tw-min-h-screen tw-bg-slate-950 tw-text-white tw-pb-20">
    <!-- Header -->
    <div class="tw-bg-slate-900/50 tw-backdrop-blur-xl tw-border-b tw-border-white/5 tw-sticky tw-top-0 tw-z-30">
      <div class="tw-max-w-7xl tw-mx-auto tw-px-4 tw-h-16 tw-flex tw-items-center tw-justify-between">
        <h1 class="tw-text-lg tw-font-bold tw-flex tw-items-center tw-gap-2">
          <i class="fas fa-crown tw-text-indigo-400"></i> VIP Membership
        </h1>
        <div class="tw-bg-indigo-500/10 tw-text-indigo-400 tw-px-3 tw-py-1 tw-rounded-full tw-text-[10px] tw-font-bold tw-uppercase">
          BETA PREVIEW
        </div>
      </div>
    </div>

    <div class="tw-max-w-4xl tw-mx-auto tw-px-4 tw-py-8">
      <!-- Status Card if Active -->
      <div v-if="subscription" class="tw-bg-gradient-to-br tw-from-indigo-600 tw-to-violet-700 tw-rounded-[2rem] tw-p-8 tw-mb-12 tw-relative tw-overflow-hidden tw-shadow-2xl tw-shadow-indigo-500/20">
        <div class="tw-absolute tw-top-0 tw-right-0 tw-w-64 tw-h-64 tw-bg-white/10 tw-rounded-full tw-blur-3xl -tw-mr-32 -tw-mt-32"></div>
         <div class="tw-relative tw-z-10">
          <div class="tw-flex tw-items-center tw-justify-between tw-mb-6">
            <span class="tw-bg-white/20 tw-backdrop-blur-md tw-text-white tw-px-4 tw-py-1.5 tw-rounded-full tw-text-xs tw-font-bold">YOUR STATUS</span>
             <i class="fas fa-crown tw-text-4xl tw-text-white/30"></i>
          </div>
          <h2 class="tw-text-4xl tw-font-black tw-text-white tw-mb-2">VIP PRIME MEMBER</h2>
          <p class="tw-text-indigo-100 tw-text-sm">Active until: <span class="tw-font-bold">{{ subscription.expires_at }}</span></p>
          
          <div class="tw-grid tw-grid-cols-2 tw-gap-4 tw-mt-10">
            <div class="tw-bg-black/20 tw-backdrop-blur-md tw-p-4 tw-rounded-2xl tw-border tw-border-white/10">
               <div class="tw-text-indigo-200 tw-text-[10px] tw-font-bold tw-uppercase tw-mb-1">Benefits Active</div>
               <div class="tw-text-white tw-font-bold">0% Withdrawal Fee</div>
            </div>
            <div class="tw-bg-black/20 tw-backdrop-blur-md tw-p-4 tw-rounded-2xl tw-border tw-border-white/10">
               <div class="tw-text-indigo-200 tw-text-[10px] tw-font-bold tw-uppercase tw-mb-1">Payout Priority</div>
               <div class="tw-text-white tw-font-bold">INSTANT Mode On</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Plans -->
      <div v-if="!subscription" class="tw-text-center tw-mb-12">
        <h2 class="tw-text-3xl tw-font-black tw-text-white tw-mb-4">Unlock Premium Benefits</h2>
        <p class="tw-text-slate-400">Save more and get paid faster with VIP Identity.</p>
      </div>

      <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-8">
        <div v-for="plan in plans" :key="plan.id" class="plan-card" :class="{ 'is-active': subscription?.plan_id === plan.id }">
          <div class="tw-flex tw-justify-between tw-items-start tw-mb-6">
            <div>
              <h3 class="tw-text-xl tw-font-bold tw-text-white">{{ plan.name }}</h3>
              <p class="tw-text-slate-400 tw-text-xs">Validity: {{ plan.months }} Months</p>
            </div>
            <div class="tw-text-right">
              <div class="tw-text-2xl tw-font-black tw-text-white">₹{{ plan.price }}</div>
              <div class="tw-text-[10px] tw-text-slate-500 tw-font-bold">ONE TIME PAYMENT</div>
            </div>
          </div>

          <div class="tw-space-y-4 tw-mb-8">
            <div class="tw-flex tw-items-center tw-gap-3">
              <div class="tw-w-6 tw-h-6 tw-bg-indigo-500/10 tw-text-indigo-400 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-text-[10px]">
                <i class="fas fa-check"></i>
              </div>
              <span class="tw-text-sm tw-text-white">{{ plan.withdrawal_fee_discount }}% Withdrawal Fee Discount</span>
            </div>
            <div class="tw-flex tw-items-center tw-gap-3">
               <div class="tw-w-6 tw-h-6 tw-bg-indigo-500/10 tw-text-indigo-400 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-text-[10px]">
                <i class="fas fa-check"></i>
              </div>
              <span class="tw-text-sm tw-text-white">Priority Withdrawal Processing</span>
            </div>
            <div class="tw-flex tw-items-center tw-gap-3">
               <div class="tw-w-6 tw-h-6 tw-bg-indigo-500/10 tw-text-indigo-400 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-text-[10px]">
                <i class="fas fa-check"></i>
              </div>
              <span class="tw-text-sm tw-text-white">Verified VIP Crown Badge</span>
            </div>
             <div class="tw-flex tw-items-center tw-gap-3">
               <div class="tw-w-6 tw-h-6 tw-bg-indigo-500/10 tw-text-indigo-400 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-text-[10px]">
                <i class="fas fa-check"></i>
              </div>
              <span class="tw-text-sm tw-text-white">24/7 Dedicated Support Link</span>
            </div>
          </div>

          <button v-if="!subscription" @click="subscribe(plan.id)" class="tw-w-full tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-py-4 tw-rounded-2xl tw-font-bold tw-transition-all tw-shadow-lg tw-shadow-indigo-500/20 active:tw-scale-95">
            Get VIP Prime Now
          </button>
          <div v-else-if="subscription.plan_id === plan.id" class="tw-w-full tw-bg-emerald-500/10 tw-text-emerald-500 tw-py-4 tw-rounded-2xl tw-font-bold tw-text-center tw-border tw-border-emerald-500/20">
            CURRENTLY ACTIVE
          </div>
        </div>
      </div>

      <!-- Processing Overlay -->
      <div v-if="isProcessing" class="tw-fixed tw-inset-0 tw-z-[100] tw-bg-slate-950/80 tw-backdrop-blur-xl tw-flex tw-items-center tw-justify-center">
         <div class="tw-text-center">
            <div class="tw-w-20 tw-h-20 tw-border-4 tw-border-indigo-500 tw-border-t-transparent tw-rounded-full tw-animate-spin tw-mx-auto tw-mb-6"></div>
            <h3 class="tw-text-2xl tw-font-black">ACTIVATING VIP...</h3>
            <p class="tw-text-slate-400 tw-mt-2">Connecting to secure gateway</p>
         </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from '../../services/api'
import { notify } from '@kyvg/vue3-notification'

export default {
  name: 'VipMembership',
  data() {
    return {
      plans: [],
      subscription: null,
      isProcessing: false
    }
  },
  methods: {
    async fetchData() {
       try {
         const res = await api.get('/vip/info')
         this.plans = res.data.data.plans
         this.subscription = res.data.data.subscription
       } catch (e) {
         notify({ type: 'error', text: 'Failed to fetch VIP data' })
       }
    },
    async subscribe(planId) {
      if (!confirm('Are you sure you want to purchase this VIP plan?')) return
      
      this.isProcessing = true
      try {
        await api.post('/vip/subscribe', { plan_id: planId })
        // Artificial delay for "Premium feel"
        setTimeout(() => {
          this.isProcessing = false
          notify({ type: 'success', text: 'VIP Membership Activated!' })
          this.fetchData()
        }, 1500)
      } catch (e) {
         this.isProcessing = false
         notify({ type: 'error', text: e.response?.data?.message || 'Subscription failed' })
      }
    }
  },
  mounted() {
    this.fetchData()
  }
}
</script>

<style scoped>
.plan-card {
  background: rgba(30, 41, 59, 0.4);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 2rem;
  padding: 32px;
  position: relative;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.plan-card:hover {
  background: rgba(30, 41, 59, 0.6);
  border-color: rgba(99, 102, 241, 0.3);
  transform: translateY(-4px);
}
.plan-card.is-active {
  border-color: #10b981;
  background: rgba(16, 185, 129, 0.05);
}
</style>
