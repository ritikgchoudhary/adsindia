<template>
  <div class="tw-min-h-screen tw-bg-slate-950 tw-text-white tw-pb-20">
    <!-- Header -->
    <div class="tw-bg-slate-900/50 tw-backdrop-blur-xl tw-border-b tw-border-white/5 tw-sticky tw-top-0 tw-z-30">
      <div class="tw-max-w-7xl tw-mx-auto tw-px-4 tw-h-16 tw-flex tw-items-center tw-justify-between">
        <h1 class="tw-text-lg tw-font-bold tw-flex tw-items-center tw-gap-2">
          <i class="fas fa-check-circle tw-text-blue-400"></i> Verified Badge
        </h1>
        <div class="tw-bg-blue-500/10 tw-text-blue-400 tw-px-3 tw-py-1 tw-rounded-full tw-text-[10px] tw-font-bold tw-uppercase">
          BETA PREVIEW
        </div>
      </div>
    </div>

    <div class="tw-max-w-xl tw-mx-auto tw-px-4 tw-py-12">
      <div class="tw-bg-slate-900/40 tw-backdrop-blur-xl tw-border tw-border-white/10 tw-rounded-[2.5rem] tw-p-8 tw-text-center tw-shadow-2xl">
        <div class="tw-relative tw-inline-block tw-mb-8">
            <div class="tw-w-24 tw-h-24 tw-bg-slate-800 tw-rounded-full tw-mx-auto tw-flex tw-items-center tw-justify-center tw-text-4xl tw-font-bold tw-text-indigo-400 tw-border-2 tw-border-indigo-500/20">
              <i class="fas fa-user"></i>
            </div>
            <div v-if="userVerified" class="tw-absolute -tw-bottom-1 -tw-right-1 tw-w-10 tw-h-10 tw-bg-blue-600 tw-rounded-full tw-border-4 tw-border-slate-900 tw-flex tw-items-center tw-justify-center tw-shadow-lg">
              <i class="fas fa-check tw-text-white tw-text-xs"></i>
            </div>
        </div>

        <h2 class="tw-text-2xl tw-font-black tw-text-white tw-mb-2">Verified Partner Status</h2>
        <p class="tw-text-slate-400 tw-text-sm tw-mb-8">Build instant trust and boost your referral conversions with a verified blue tick.</p>

        <div class="tw-bg-white/5 tw-rounded-3xl tw-p-6 tw-mb-8 tw-text-left tw-space-y-4">
           <div class="tw-flex tw-items-center tw-gap-4">
              <div class="tw-w-8 tw-h-8 tw-bg-blue-500/10 tw-text-blue-400 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-shrink-0">
                 <i class="fas fa-user-shield tw-text-xs"></i>
              </div>
              <div>
                 <div class="tw-text-white tw-font-bold tw-text-sm">Instant Trust</div>
                 <div class="tw-text-[10px] tw-text-slate-500">Show people you are a legitimate partner.</div>
              </div>
           </div>
            <div class="tw-flex tw-items-center tw-gap-4">
              <div class="tw-w-8 tw-h-8 tw-bg-blue-500/10 tw-text-blue-400 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-shrink-0">
                 <i class="fas fa-chart-line tw-text-xs"></i>
              </div>
              <div>
                 <div class="tw-text-white tw-font-bold tw-text-sm">higher Conversions</div>
                 <div class="tw-text-[10px] tw-text-slate-500">Verified users get 45% more direct referrals.</div>
              </div>
           </div>
        </div>

        <div v-if="!userVerified">
          <div class="tw-flex tw-items-center tw-justify-between tw-mb-6 tw-px-2">
             <span class="tw-text-slate-500 tw-font-bold tw-uppercase tw-text-[10px]">Activation Fee</span>
             <span class="tw-text-2xl tw-font-black tw-text-white">₹{{ settings.price }}</span>
          </div>

          <button @click="purchaseBadge" class="tw-w-full tw-bg-blue-600 hover:tw-bg-blue-700 tw-text-white tw-py-4 tw-rounded-2xl tw-font-bold tw-transition-all tw-shadow-xl tw-shadow-blue-500/20 active:tw-scale-95">
             Get Verified Now
          </button>
        </div>
        <div v-else class="tw-bg-emerald-500/10 tw-text-emerald-500 tw-py-4 tw-rounded-2xl tw-font-bold tw-border tw-border-emerald-500/20">
           <i class="fas fa-check-circle tw-mr-1"></i> YOU ARE VERIFIED
        </div>

        <p class="tw-text-[10px] tw-text-slate-600 tw-mt-6 tw-uppercase tw-font-bold tw-tracking-widest">Global Trust Standard</p>
      </div>

      <!-- Processing Overlay -->
      <div v-if="isProcessing" class="tw-fixed tw-inset-0 tw-z-[100] tw-bg-slate-950/80 tw-backdrop-blur-xl tw-flex tw-items-center tw-justify-center">
         <div class="tw-text-center">
            <div class="tw-w-20 tw-h-20 tw-border-4 tw-border-blue-500 tw-border-t-transparent tw-rounded-full tw-animate-spin tw-mx-auto tw-mb-6"></div>
            <h3 class="tw-text-2xl tw-font-black">VERIFYING STATUS...</h3>
            <p class="tw-text-slate-400 tw-mt-2">Assigning blue tick to your profile</p>
         </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from '../../services/api'
import { notify } from '@kyvg/vue3-notification'

export default {
  name: 'VerifiedBadge',
  data() {
    return {
      userVerified: false,
      isProcessing: false,
      settings: {
        price: 99
      }
    }
  },
  methods: {
    async fetchStatus() {
       try {
         const res = await api.get('/user-info')
         this.userVerified = res.data.data.verified_badge
       } catch (e) {}
       
       try {
         const res = await api.get('/beta-settings')
         if (res.data?.data?.verified) {
           this.settings = res.data.data.verified
         }
       } catch (e) {
          // Fallback handled by default data
       }
    },
    async purchaseBadge() {
      if (!confirm(`Activate your Verified Badge for ₹${this.settings.price}?`)) return
      this.isProcessing = true
      try {
        await api.post('/verified/purchase')
        setTimeout(() => {
          this.isProcessing = false
          notify({ type: 'success', text: 'Verified Badge Activated!' })
          this.fetchStatus()
        }, 1500)
      } catch (e) {
         this.isProcessing = false
         notify({ type: 'error', text: e.response?.data?.message || 'Purchase failed' })
      }
    }
  },
  mounted() {
    this.fetchStatus()
  }
}
</script>
