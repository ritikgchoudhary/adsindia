<template>
  <div class="tw-min-h-screen tw-bg-slate-950 tw-text-white tw-pb-20">
    <!-- Header -->
    <div class="tw-bg-slate-900/50 tw-backdrop-blur-xl tw-border-b tw-border-white/5 tw-sticky tw-top-0 tw-z-30">
      <div class="tw-max-w-7xl tw-mx-auto tw-px-4 tw-h-16 tw-flex tw-items-center tw-justify-between">
        <h1 class="tw-text-lg tw-font-bold tw-flex tw-items-center tw-gap-2">
          <i class="fas fa-rocket tw-text-orange-400"></i> Daily Ad Booster
        </h1>
        <div class="tw-bg-orange-500/10 tw-text-orange-400 tw-px-3 tw-py-1 tw-rounded-full tw-text-[10px] tw-font-bold tw-uppercase">
          Point #4
        </div>
      </div>
    </div>

    <div class="tw-max-w-xl tw-mx-auto tw-px-4 tw-py-12">
      <!-- Status Card -->
      <div v-if="isActive" class="tw-bg-orange-600 tw-rounded-3xl tw-p-8 tw-text-center tw-mb-8 tw-shadow-2xl tw-shadow-orange-600/20">
         <div class="tw-w-20 tw-h-20 tw-bg-white/20 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-4">
            <i class="fas fa-rocket tw-text-white tw-text-3xl"></i>
         </div>
         <h2 class="tw-text-white tw-font-black tw-text-2xl tw-mb-1">BOOSTER ACTIVE!</h2>
         <p class="tw-text-white/80 tw-text-xs">You have +5 extra ads unlocked today.</p>
         <div class="tw-mt-6 tw-inline-block tw-bg-black/20 tw-px-4 tw-py-2 tw-rounded-full tw-text-[10px] tw-font-bold tw-uppercase">
            Expires: {{ boosterExpiryHuman }}
         </div>
      </div>

      <div class="tw-bg-slate-900/40 tw-backdrop-blur-xl tw-border tw-border-white/10 tw-rounded-[2.5rem] tw-p-8 tw-shadow-2xl">
        <h2 class="tw-text-2xl tw-font-black tw-text-white tw-mb-2">Maximize Your Earnings</h2>
        <p class="tw-text-slate-400 tw-text-sm tw-mb-8">Don't let your daily limit stop you. Boost your account and watch extra ads every day.</p>

        <div class="tw-grid tw-grid-cols-1 tw-gap-4 tw-mb-8">
           <!-- Daily Plan -->
           <button @click="purchase('daily')" class="tw-bg-white/5 tw-border tw-border-white/10 tw-rounded-3xl tw-p-6 tw-text-left tw-transition-all hover:tw-border-orange-500/50 hover:tw-bg-orange-500/5 group">
              <div class="tw-flex tw-items-center tw-justify-between tw-mb-2">
                 <span class="tw-text-orange-400 tw-text-[10px] tw-font-bold tw-uppercase tw-tracking-widest">Single Burst</span>
                 <span class="tw-text-xl tw-font-black tw-text-white group-hover:tw-text-orange-400">₹{{ settings.daily_price }}</span>
              </div>
              <div class="tw-text-white tw-font-bold tw-text-lg">Daily Booster</div>
              <div class="tw-text-xs tw-text-slate-500 tw-mt-1">+5 Ads for 24 Hours</div>
           </button>

           <!-- Weekly Plan -->
           <button @click="purchase('weekly')" class="tw-bg-gradient-to-br tw-from-orange-500/10 tw-to-red-500/10 tw-border tw-border-orange-500/20 tw-rounded-3xl tw-p-6 tw-text-left tw-transition-all hover:tw-border-orange-500/50 group tw-relative tw-overflow-hidden">
              <div class="tw-absolute -tw-right-4 -tw-top-4 tw-opacity-10 tw-rotate-12">
                 <i class="fas fa-rocket tw-text-8xl"></i>
              </div>
              <div class="tw-flex tw-items-center tw-justify-between tw-mb-2">
                 <span class="tw-text-orange-400 tw-text-[10px] tw-font-bold tw-uppercase tw-tracking-widest">Power User</span>
                 <span class="tw-text-xl tw-font-black tw-text-white group-hover:tw-text-orange-400">₹{{ settings.weekly_price }}</span>
              </div>
              <div class="tw-text-white tw-font-bold tw-text-lg">Weekly Mega Booster</div>
              <div class="tw-text-xs tw-text-slate-500 tw-mt-1">+5 Ads/Day for 7 Days</div>
              <div class="tw-mt-4 tw-bg-orange-500 tw-text-white tw-text-[9px] tw-font-black tw-uppercase tw-px-2 tw-py-1 tw-rounded tw-inline-block">
                 BEST VALUE
              </div>
           </button>
        </div>

        <div class="tw-p-4 tw-bg-white/5 tw-rounded-2xl tw-flex tw-items-center tw-gap-4">
           <div class="tw-w-8 tw-h-8 tw-bg-emerald-500/10 tw-text-emerald-500 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-shrink-0">
              <i class="fas fa-info-circle tw-text-xs"></i>
           </div>
           <p class="tw-text-[10px] tw-text-slate-500 tw-m-0">Boosters apply instantly. Extra ads will appear on your Ads Work page immediately after purchase.</p>
        </div>
      </div>

      <!-- Processing Overlay -->
      <div v-if="isProcessing" class="tw-fixed tw-inset-0 tw-z-[100] tw-bg-slate-950/80 tw-backdrop-blur-xl tw-flex tw-items-center tw-justify-center">
         <div class="tw-text-center">
            <div class="tw-w-20 tw-h-20 tw-border-4 tw-border-orange-500 tw-border-t-transparent tw-rounded-full tw-animate-spin tw-mx-auto tw-mb-6"></div>
            <h3 class="tw-text-2xl tw-font-black">BOOSTING ACCOUNT...</h3>
            <p class="tw-text-slate-400 tw-mt-2">Unlocking extra ad limit</p>
         </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from '../../services/api'
import { notify } from '@kyvg/vue3-notification'

export default {
  name: 'AdBooster',
  data() {
    return {
      isActive: false,
      boosterExpiryHuman: '',
      isProcessing: false,
      settings: {
        daily_price: 29,
        weekly_price: 149
      }
    }
  },
  methods: {
    async fetchStatus() {
       try {
         const res = await api.get('/user-info')
         const user = res.data.data
         this.isActive = user.booster_expires_at && new Date(user.booster_expires_at) > new Date()
         this.boosterExpiryHuman = user.booster_expires_at || '-'
       } catch (e) {}

       try {
         const res = await api.get('/beta-settings')
         if (res.data?.data?.booster) {
           this.settings = res.data.data.booster
         }
       } catch (e) {}
    },
    async purchase(type) {
      const price = type === 'daily' ? this.settings.daily_price : this.settings.weekly_price
      if (!confirm(`Activate ${type} Booster for ₹${price}?`)) return
      
      this.isProcessing = true
      try {
        await api.post('/booster/purchase', { type })
        setTimeout(() => {
          this.isProcessing = false
          notify({ type: 'success', text: 'Ad Booster activated!' })
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
