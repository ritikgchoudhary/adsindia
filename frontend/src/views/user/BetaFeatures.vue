<template>
  <div class="tw-min-h-screen tw-bg-slate-950 tw-text-white tw-pb-20">
    <!-- Premium Header -->
    <div class="tw-relative tw-overflow-hidden tw-bg-slate-900/50 tw-backdrop-blur-2xl tw-border-b tw-border-white/5">
      <div class="tw-absolute tw-top-0 tw-left-1/2 -tw-translate-x-1/2 tw-w-full tw-h-full tw-opacity-20 tw-pointer-events-none">
        <div class="tw-absolute tw-top-0 tw-left-1/4 tw-w-64 tw-h-64 tw-bg-indigo-600 tw-rounded-full tw-blur-[120px]"></div>
        <div class="tw-absolute tw-bottom-0 tw-right-1/4 tw-w-64 tw-h-64 tw-bg-blue-600 tw-rounded-full tw-blur-[120px]"></div>
      </div>
      
      <div class="tw-max-w-7xl tw-mx-auto tw-px-6 tw-py-12 tw-relative tw-z-10">
        <div class="tw-flex tw-flex-col md:tw-flex-row tw-items-center tw-justify-between tw-gap-8">
          <div class="tw-text-center md:tw-text-left">
            <div class="tw-flex tw-items-center tw-justify-center md:tw-justify-start tw-gap-3 tw-mb-4">
              <span class="tw-bg-indigo-500/10 tw-text-indigo-400 tw-text-[10px] tw-font-black tw-uppercase tw-tracking-[0.2em] tw-px-3 tw-py-1 tw-rounded-full tw-border tw-border-indigo-500/20">
                Exclusive Beta Access
              </span>
              <span class="tw-bg-amber-500/10 tw-text-amber-500 tw-text-[10px] tw-font-black tw-uppercase tw-tracking-[0.2em] tw-px-3 tw-py-1 tw-rounded-full tw-border tw-border-amber-500/20">
                Phase 1 Active
              </span>
            </div>
            <h1 class="tw-text-4xl md:tw-text-6xl tw-font-black tw-tracking-tight tw-mb-4 tw-bg-gradient-to-r tw-from-white tw-via-white tw-to-white/40 tw-bg-clip-text tw-text-transparent">
              Account Upgrades
            </h1>
            <p class="tw-text-slate-400 tw-text-lg tw-max-w-xl">
              Unlock professional tools, priority features, and exclusive badges designed to skyrocket your earnings.
            </p>
          </div>
          
          <div class="tw-bg-white/5 tw-backdrop-blur-xl tw-border tw-border-white/10 tw-rounded-[2.5rem] tw-p-8 tw-flex tw-items-center tw-gap-6 tw-shadow-2xl">
             <div class="tw-w-16 tw-h-16 tw-bg-indigo-600 tw-rounded-2xl tw-flex tw-items-center tw-justify-center tw-text-3xl tw-shadow-lg tw-shadow-indigo-600/20">
                <i class="fas fa-gem"></i>
             </div>
             <div>
                <div class="tw-text-slate-500 tw-text-[10px] tw-font-bold tw-uppercase">Your Status</div>
                <div class="tw-text-xl tw-font-black">BETA TESTER</div>
             </div>
          </div>
        </div>
      </div>
    </div>

    <div class="tw-max-w-7xl tw-mx-auto tw-px-6 tw-py-16">
      <!-- Feature Sections -->
      <div v-for="section in sections" :key="section.title" class="tw-mb-20 last:tw-mb-0">
        <div class="tw-flex tw-items-center tw-gap-4 tw-mb-10">
          <h2 class="tw-text-2xl tw-font-black tw-uppercase tw-tracking-widest tw-text-white/40">{{ section.title }}</h2>
          <div class="tw-h-px tw-flex-grow tw-bg-gradient-to-r tw-from-white/10 tw-to-transparent"></div>
        </div>

        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-3 tw-gap-8">
          <div v-for="item in section.items" :key="item.id" 
               class="upgrade-card group" 
               :class="{ 'is-locked': item.status === 'Locked', 'is-active': item.is_active }">
            
            <div class="tw-relative tw-z-10 tw-h-full tw-flex tw-flex-col">
              <div class="tw-flex tw-items-start tw-justify-between tw-mb-8">
                <div class="tw-w-14 tw-h-14 tw-rounded-2xl tw-flex tw-items-center tw-justify-center tw-text-2xl tw-transition-all group-hover:tw-scale-110" :class="item.iconBg">
                  <i :class="item.icon"></i>
                </div>
                <div v-if="item.status" class="tw-px-3 tw-py-1 tw-rounded-full tw-text-[9px] tw-font-black tw-uppercase tw-tracking-widest" :class="statusClass(item.status)">
                  {{ item.status }}
                </div>
              </div>

              <h3 class="tw-text-xl tw-font-black tw-mb-2 tw-text-white group-hover:tw-text-indigo-400 tw-transition-colors">{{ item.name }}</h3>
              <p class="tw-text-slate-400 tw-text-sm tw-mb-8 tw-line-clamp-2">{{ item.desc }}</p>

              <div class="tw-mt-auto">
                <div class="tw-flex tw-items-center tw-justify-between tw-mb-6">
                  <div v-if="item.price" class="tw-flex tw-flex-col">
                    <span class="tw-text-[10px] tw-font-black tw-text-slate-500 tw-uppercase">Setup Fee</span>
                    <span class="tw-text-xl tw-font-black">₹{{ item.price }}</span>
                  </div>
                  <div v-else class="tw-text-[10px] tw-font-black tw-text-slate-500 tw-uppercase">Coming Soon</div>
                </div>

                <router-link v-if="item.link && item.status !== 'Locked'" :to="item.link" class="upgrade-btn">
                  View Details
                  <i class="fas fa-arrow-right tw-ml-2 tw-text-[10px]"></i>
                </router-link>
                <button v-else disabled class="upgrade-btn tw-opacity-20 tw-cursor-not-allowed">
                  Unavailable
                </button>
              </div>
            </div>

            <!-- Decorative Glow -->
            <div class="tw-absolute -tw-inset-1 tw-bg-gradient-to-r tw-opacity-0 group-hover:tw-opacity-10 dark:tw-opacity-0 dark:group-hover:tw-opacity-20 tw-blur-xl tw-transition tw-duration-500" :class="item.glowColor"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from '../../services/api'

export default {
  name: 'BetaFeatures',
  data() {
    return {
      settings: {},
      sections: [
        {
          title: "Phase 1: Performance",
          items: [
            { 
              id: 1, 
              name: "VIP Membership", 
              desc: "Zero withdrawal fees and priority instant payouts for active earners.",
              icon: "fas fa-crown", 
              iconBg: "tw-bg-indigo-500/10 tw-text-indigo-400",
              glowColor: "tw-from-indigo-500 tw-to-purple-500",
              status: "Live",
              price: "199",
              link: "/user/vip-membership"
            },
            { 
              id: 6, 
              name: "Verified Badge", 
              desc: "The elite blue tick to establish maximum trust with your direct referrals.",
              icon: "fas fa-check-circle", 
              iconBg: "tw-bg-blue-500/10 tw-text-blue-400",
              glowColor: "tw-from-blue-500 tw-to-indigo-500",
              status: "Live",
              price: "99",
              link: "/user/verified-badge"
            },
            { 
              id: 9, 
              name: "Daily Ad Booster", 
              desc: "Unlock extra daily ad limits to increase your daily potential reward.",
              icon: "fas fa-rocket", 
              iconBg: "tw-bg-orange-500/10 tw-text-orange-400",
              glowColor: "tw-from-orange-500 tw-to-red-500",
              status: "Live",
              price: "29",
              link: "/user/ad-booster"
            },
            { 
              id: 3, 
              name: "Instant Wi-Fee", 
              desc: "Enable instant payout functionality with dynamic fee management.",
              icon: "fas fa-bolt", 
              iconBg: "tw-bg-amber-500/10 tw-text-amber-400",
              glowColor: "tw-from-amber-500 tw-to-orange-500",
              status: "Active",
              price: "50",
              link: "/user/withdraw"
            }
          ]
        },
        {
          title: "Phase 2: Management",
          items: [
            { 
              id: 2, 
              name: "Agent License", 
              desc: "Become a certified professional agent with higher commission rates.",
              icon: "fas fa-user-tie", 
              iconBg: "tw-bg-emerald-500/10 tw-text-emerald-400",
              glowColor: "tw-from-emerald-500 tw-to-teal-500",
              status: "Locked",
              price: "2000"
            },
            { 
              id: 7, 
              name: "KYC Fast Track", 
              desc: "Skip the verification queue and get approved in under 60 minutes.",
              icon: "fas fa-id-card", 
              iconBg: "tw-bg-pink-500/10 tw-text-pink-400",
              glowColor: "tw-from-pink-500 tw-to-purple-500",
              status: "Locked",
              price: "249"
            },
            { 
              id: 4, 
              name: "Limit Upgrade", 
              desc: "Permanently increase your daily withdrawal limit by ₹10,000.",
              icon: "fas fa-arrow-circle-up", 
              iconBg: "tw-bg-violet-500/10 tw-text-violet-400",
              glowColor: "tw-from-violet-500 tw-to-indigo-500",
              status: "Locked",
              price: "499"
            }
          ]
        },
        {
          title: "Phase 3: Elite Content",
          items: [
            { 
              id: 15, 
              name: "Masterclass Access", 
              desc: "Video training from top 1% earners on marketing and expansion.",
              icon: "fas fa-video", 
              iconBg: "tw-bg-cyan-500/10 tw-text-cyan-400",
              glowColor: "tw-from-cyan-500 tw-to-blue-500",
              status: "Locked",
              price: "999"
            },
            { 
              id: 17, 
              name: "Featured Partner", 
              desc: "Get your referral link promoted on our global public landing page.",
              icon: "fas fa-star", 
              iconBg: "tw-bg-rose-500/10 tw-text-rose-400",
              glowColor: "tw-from-rose-500 tw-to-red-500",
              status: "Locked",
              price: "1500"
            }
          ]
        }
      ]
    }
  },
  methods: {
    statusClass(status) {
      if (status === 'Live') return 'tw-bg-emerald-500/10 tw-text-emerald-500 tw-border tw-border-emerald-500/20'
      if (status === 'Active') return 'tw-bg-blue-500/10 tw-text-blue-500 tw-border tw-border-blue-500/20'
      return 'tw-bg-white/5 tw-text-slate-500 tw-border tw-border-white/5'
    },
    async fetchSettings() {
       try {
         const res = await api.get('/beta-settings');
         if (res.data?.data) {
           const s = res.data.data;
           // Dynamic price update for Phase 1
           this.sections[0].items.forEach(item => {
             if (item.id === 1) item.price = s.vip?.price || '199';
             if (item.id === 6) item.price = s.verified?.price || '99';
             if (item.id === 9) item.price = s.booster?.daily_price || '29';
             if (item.id === 3) item.price = s.instant?.fee || '50';
           });
         }
       } catch (e) {}
    }
  },
  mounted() {
    this.fetchSettings()
  }
}
</script>

<style scoped>
.upgrade-card {
  background: rgba(15, 23, 42, 0.4);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 2.5rem;
  padding: 2.5rem;
  position: relative;
  overflow: hidden;
  transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex;
  flex-direction: column;
}

.upgrade-card:hover {
  transform: translateY(-8px);
  background: rgba(15, 23, 42, 0.7);
  border-color: rgba(99, 102, 241, 0.3);
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
}

.upgrade-card.is-locked {
  filter: grayscale(0.8);
  opacity: 0.7;
}

.upgrade-card.is-locked:hover {
  transform: none;
  filter: grayscale(1);
}

.upgrade-btn {
  width: 100%;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.08);
  color: white;
  padding: 1rem;
  border-radius: 1.25rem;
  font-weight: 800;
  font-size: 0.85rem;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s;
  text-decoration: none;
}

.upgrade-card:hover .upgrade-btn {
  background: white;
  color: black;
  border-color: white;
}

.is-locked .upgrade-btn {
  background: rgba(255, 255, 255, 0.05);
  border-color: transparent;
  color: rgba(255, 255, 255, 0.2);
}
</style>
