<template>
  <MasterAdminLayout>
    <div class="beta-hub-container">
      <!-- High-Tech Header -->
      <div class="beta-header">
         <div class="header-content">
            <div class="header-left">
               <div class="vial-icon">
                  <i class="fas fa-vial"></i>
                  <div class="vial-glow"></div>
               </div>
               <div>
                  <h1>Beta Features Hub</h1>
                  <p>Advanced feature management & experimental sandbox control.</p>
               </div>
            </div>
            <div class="header-right">
               <div class="beta-badge">
                  <span class="pulsing-dot"></span>
                  ADMIN LABS ACTIVE
               </div>
            </div>
         </div>
      </div>

      <!-- Tech Stats Dashboard -->
      <div class="tech-stats">
        <div class="stat-card">
          <div class="stat-icon"><i class="fas fa-microchip"></i></div>
          <div class="stat-info">
            <span class="label">Implementation</span>
            <span class="value">Phase 1/3</span>
          </div>
          <div class="progress-track"><div class="progress-bar" style="width: 33%"></div></div>
        </div>
        <div class="stat-card">
           <div class="stat-icon"><i class="fas fa-user-shield"></i></div>
           <div class="stat-info">
             <span class="label">Primary Tester</span>
             <span class="value">ADS1 Only</span>
           </div>
        </div>
        <div class="stat-card">
           <div class="stat-icon"><i class="fas fa-layer-group"></i></div>
           <div class="stat-info">
             <span class="label">Total Points</span>
             <span class="value">17 Points</span>
           </div>
        </div>
      </div>

      <!-- Tester Management -->
      <div class="tester-mgmt">
         <div class="card-inner">
            <div class="mgmt-header">
               <h2><i class="fas fa-user-shield"></i> Beta Tester Management</h2>
               <p>Quickly enable/disable beta access for users by their System ID.</p>
            </div>
            <div class="mgmt-form">
               <input v-model="searchId" type="number" placeholder="Enter User ID (e.g. 15009)" class="tech-input">
               <button @click="toggleAccess" :disabled="!searchId || loading" class="tg-btn">
                  <span v-if="loading">PROCESSING...</span>
                  <span v-else>TOGGLE BETA ACCESS</span>
               </button>
            </div>
         </div>
      </div>

      <!-- The Hub Grid -->
      <div class="hub-grid">
         <div v-for="point in betaPoints" :key="point.id" class="tech-card" :class="{ 'is-active': point.active }">
            <div class="card-edge"></div>
            <div class="card-inner">
               <div class="card-top">
                  <div class="point-tag">Point #{{ point.id }}</div>
                  <div class="status-tag" :class="point.status.toLowerCase()">{{ point.status }}</div>
               </div>
               
               <div class="feature-icon" :class="point.colorClass">
                  <i :class="point.icon"></i>
               </div>
               
               <h3>{{ point.name }}</h3>
               <p>{{ point.desc }}</p>

               <div class="card-footer">
                  <div class="module-access">
                     <span v-for="tag in point.tags" :key="tag" class="tag">{{ tag }}</span>
                  </div>
                  <router-link :to="point.configLink" class="config-trigger">
                     CONFIGURE <i class="fas fa-sliders-h"></i>
                  </router-link>
               </div>
            </div>
         </div>
      </div>
    </div>
  </MasterAdminLayout>
</template>

<script>
import MasterAdminLayout from '../../components/master_admin/MasterAdminLayout.vue'
import api from '../../services/api'

export default {
  name: 'BetaFeaturesHub',
  components: { MasterAdminLayout },
  data() {
    return {
      testerCount: 0,
      searchId: '',
      loading: false,
      betaPoints: [
        { id: 1, name: "VIP Membership", desc: "Monthly fee sub for 0% fee and priority payouts.", icon: "fas fa-crown", status: "BETA", colorClass: "indigo", configLink: "/master_admin/beta-features/vip", tags: ["Revenue", "Phase1"] },
        { id: 2, name: "Agent License", desc: "Professional one-time fee for high earners.", icon: "fas fa-user-tie", status: "DESIGN", colorClass: "emerald", configLink: "/master_admin/beta-features/extra", tags: ["License", "Phase2"] },
        { id: 3, name: "Instant Payout", desc: "Dynamic fee management for immediate transfers.", icon: "fas fa-bolt", status: "BETA", colorClass: "amber", configLink: "/master_admin/beta-features/instant", tags: ["UX", "Phase1"] },
        { id: 4, name: "Limit Upgrade", desc: "Purchasable expansion for daily withdrawal limits.", icon: "fas fa-arrow-up", status: "DESIGN", colorClass: "purple", configLink: "/master_admin/beta-features/extra", tags: ["Scale", "Phase2"] },
        { id: 5, name: "Withdraw Pass", desc: "6-month fee-avoidance pass for power users.", icon: "fas fa-calendar-check", status: "DESIGN", colorClass: "pink", configLink: "/master_admin/beta-features/extra", tags: ["Loyalty", "Phase2"] },
        { id: 6, name: "Verified Badge", desc: "Identity trust expansion via blue-tick system.", icon: "fas fa-check-circle", status: "BETA", colorClass: "blue", configLink: "/master_admin/beta-features/verified", tags: ["Trust", "Phase1"] },
        { id: 7, name: "KYC Fast Track", desc: "Priority verification queue for a premium fee.", icon: "fas fa-shipping-fast", status: "DESIGN", colorClass: "cyan", configLink: "/master_admin/beta-features/extra", tags: ["Service", "Phase2"] },
        { id: 8, name: "Account Recovery", desc: "Revenue model for reactivating inactive accounts.", icon: "fas fa-redo-alt", status: "DESIGN", colorClass: "red", configLink: "/master_admin/beta-features/extra", tags: ["System", "Phase2"] },
        { id: 9, name: "Ad Booster", desc: "Daily/weekly limits boost for active workers.", icon: "fas fa-rocket", status: "BETA", colorClass: "orange", configLink: "/master_admin/beta-features/booster", tags: ["Work", "Phase1"] },
        { id: 10, name: "Profit Booster", desc: "Temporary income multiplier (1.2x - 1.5x).", icon: "fas fa-chart-line", status: "DESIGN", colorClass: "violet", configLink: "/master_admin/beta-features/extra", tags: ["Boost", "Phase3"] },
        { id: 11, name: "Comm. Booster", desc: "Extra team commission % for limited duration.", icon: "fas fa-users-cog", status: "DESIGN", colorClass: "lime", configLink: "/master_admin/beta-features/extra", tags: ["Social", "Phase3"] },
        { id: 12, name: "Level Unlock", desc: "Passive income access to deeper network levels.", icon: "fas fa-layer-group", status: "DESIGN", colorClass: "teal", configLink: "/master_admin/beta-features/extra", tags: ["Growth", "Phase3"] },
        { id: 13, name: "Senior Agent", desc: "Advanced passive structure tier for builders.", icon: "fas fa-shield-alt", status: "DESIGN", colorClass: "blue", configLink: "/master_admin/beta-features/extra", tags: ["Rank", "Phase3"] },
        { id: 14, name: "Toolkit Store", desc: "Banners and sales scripts marketplace.", icon: "fas fa-box-open", status: "DESIGN", colorClass: "amber", configLink: "/master_admin/beta-features/extra", tags: ["Ads", "Phase3"] },
        { id: 15, name: "Masterclass", desc: "Locked educational videos for elite partners.", icon: "fas fa-video", status: "DESIGN", colorClass: "indigo", configLink: "/master_admin/beta-features/extra", tags: ["Edu", "Phase3"] },
        { id: 16, name: "Elite Training", desc: "Advanced technical earning strategies.", icon: "fas fa-key", status: "DESIGN", colorClass: "slate", configLink: "/master_admin/beta-features/extra", tags: ["Content", "Phase3"] },
        { id: 17, name: "Public Partner", desc: "Feature user profile on main landing page.", icon: "fas fa-star", status: "DESIGN", colorClass: "yellow", configLink: "/master_admin/beta-features/extra", tags: ["Promo", "Phase3"] },
      ]
    }
  },
  methods: {
    async fetchStats() {
       try {
         const res = await api.get('/admin/beta/summary')
         this.testerCount = res.data.data.tester_count || 0
       } catch (e) {}
    },
    async toggleAccess() {
       if (!this.searchId) return;
       this.loading = true;
       try {
         const res = await api.post('/admin/beta/toggle-access', { user_id: this.searchId });
         notify({ type: 'success', text: res.data.message });
         this.fetchStats();
         this.searchId = '';
       } catch (e) {
         notify({ type: 'error', text: e.response?.data?.message || 'Failed to update access' });
       } finally {
         this.loading = false;
       }
    }
  },
  mounted() {
    this.fetchStats()
  }
}
</script>

<style scoped>
.beta-hub-container {
  padding: 0 10px;
}

/* Header Styling */
.beta-header {
  background: linear-gradient(135deg, rgba(30, 41, 59, 0.4) 0%, rgba(15, 23, 42, 0.4) 100%);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 30px;
  padding: 40px;
  margin-bottom: 40px;
  position: relative;
  overflow: hidden;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: relative;
  z-index: 5;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 25px;
}

.vial-icon {
  width: 70px;
  height: 70px;
  background: rgba(99, 102, 241, 0.1);
  border: 2px solid rgba(99, 102, 241, 0.2);
  border-radius: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  color: #818cf8;
  position: relative;
}

.vial-glow {
  position: absolute;
  inset: -10px;
  background: rgba(99, 102, 241, 0.2);
  filter: blur(20px);
  border-radius: 50%;
  animation: pulse 2s infinite;
}

.header-left h1 {
  font-size: 2.5rem;
  font-weight: 950;
  color: white;
  margin: 0;
  letter-spacing: -1px;
}

.header-left p {
  color: #94a3b8;
  margin: 5px 0 0;
  font-size: 1.1rem;
}

.beta-badge {
  background: rgba(15, 23, 42, 0.6);
  border: 1px solid rgba(255, 255, 255, 0.1);
  padding: 10px 20px;
  border-radius: 100px;
  font-weight: 900;
  font-size: 0.75rem;
  color: #64748b;
  display: flex;
  align-items: center;
  gap: 10px;
  letter-spacing: 1px;
}

.pulsing-dot {
  width: 8px;
  height: 8px;
  background: #f59e0b;
  border-radius: 50%;
  box-shadow: 0 0 10px #f59e0b;
  animation: pulse 1.5s infinite;
}

/* Stats Cards */
.tester-mgmt {
  margin-bottom: 40px;
  background: rgba(30, 41, 59, 0.4);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 30px;
  padding: 30px;
  position: relative;
  overflow: hidden;
}

.mgmt-header h2 {
  font-size: 1.5rem;
  font-weight: 900;
  color: white;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 12px;
}

.mgmt-header p {
  color: #64748b;
  font-size: 0.9rem;
  margin: 5px 0 0;
}

.mgmt-form {
  display: flex;
  gap: 15px;
  margin-top: 25px;
  max-width: 600px;
}

.tech-input {
  flex-grow: 1;
  background: rgba(15, 23, 42, 0.6);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 12px 20px;
  color: white;
  font-weight: 700;
  outline: none;
  transition: 0.3s;
}

.tech-input:focus {
  border-color: #6366f1;
  box-shadow: 0 0 15px rgba(99, 102, 241, 0.2);
}

.tg-btn {
  background: #6366f1;
  color: white;
  border: none;
  border-radius: 12px;
  padding: 0 25px;
  font-weight: 900;
  font-size: 0.8rem;
  cursor: pointer;
  transition: 0.3s;
}

.tg-btn:hover:not(:disabled) {
  background: #4f46e5;
  transform: translateY(-2px);
  box-shadow: 0 10px 20px -10px #6366f1;
}

.tg-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Stats Cards */
.tech-stats {
  display: grid;
  grid-template-cols: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-bottom: 40px;
}

.stat-card {
  background: rgba(30, 41, 59, 0.4);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 24px;
  padding: 24px;
  display: flex;
  align-items: center;
  gap: 20px;
  position: relative;
}

.stat-icon {
  width: 50px;
  height: 50px;
  background: rgba(255, 255, 255, 0.03);
  border-radius: 15px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #6366f1;
  font-size: 1.2rem;
}

.stat-info .label {
  display: block;
  font-size: 0.7rem;
  font-weight: 800;
  color: #64748b;
  text-transform: uppercase;
  margin-bottom: 4px;
}

.stat-info .value {
  display: block;
  font-size: 1.5rem;
  font-weight: 900;
  color: white;
}

.progress-track {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: rgba(255,255,255,0.02);
  border-radius: 0 0 24px 24px;
  overflow: hidden;
}

.progress-bar {
  height: 100%;
  background: linear-gradient(90deg, #6366f1, #a855f7);
  box-shadow: 0 0 10px rgba(99, 102, 241, 0.5);
}

/* Hub Grid */
.hub-grid {
  display: grid;
  grid-template-cols: repeat(auto-fill, minmax(300px, 1fr));
  gap: 25px;
}

.tech-card {
  background: rgba(15, 23, 42, 0.3);
  border: 1px solid rgba(255, 255, 255, 0.03);
  border-radius: 30px;
  padding: 30px;
  position: relative;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  overflow: hidden;
}

.tech-card:hover {
  transform: translateY(-8px);
  background: rgba(30, 41, 59, 0.5);
  border-color: rgba(99, 102, 241, 0.2);
  box-shadow: 0 20px 40px -20px rgba(0, 0, 0, 0.5);
}

.card-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 25px;
}

.point-tag {
  font-size: 0.65rem;
  font-weight: 900;
  color: #475569;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.status-tag {
  font-size: 0.6rem;
  font-weight: 950;
  padding: 4px 10px;
  border-radius: 100px;
  background: rgba(255,255,255,0.05);
  color: #94a3b8;
}

.status-tag.beta {
  background: rgba(16, 185, 129, 0.1);
  color: #10b981;
}

.feature-icon {
  width: 60px;
  height: 60px;
  border-radius: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  margin-bottom: 20px;
  transition: 0.3s;
}

.tech-card:hover .feature-icon {
  transform: scale(1.1) rotate(-5deg);
}

/* Color Themes */
.indigo { background: rgba(99, 102, 241, 0.1); color: #818cf8; }
.emerald { background: rgba(16, 185, 129, 0.1); color: #34d399; }
.amber { background: rgba(245, 158, 11, 0.1); color: #fbbf24; }
.purple { background: rgba(168, 85, 247, 0.1); color: #c084fc; }
.pink { background: rgba(236, 72, 153, 0.1); color: #f472b6; }
.blue { background: rgba(59, 130, 246, 0.1); color: #60a5fa; }
.cyan { background: rgba(6, 182, 212, 0.1); color: #22d3ee; }
.red { background: rgba(239, 68, 68, 0.1); color: #f87171; }
.orange { background: rgba(249, 115, 22, 0.1); color: #fb923c; }
.violet { background: rgba(139, 92, 246, 0.1); color: #a78bfa; }
.lime { background: rgba(132, 204, 22, 0.1); color: #a3e635; }
.teal { background: rgba(20, 184, 166, 0.1); color: #2dd4bf; }
.yellow { background: rgba(234, 179, 8, 0.1); color: #facc15; }
.slate { background: rgba(71, 85, 105, 0.1); color: #94a3b8; }

.tech-card h3 {
  font-size: 1.25rem;
  font-weight: 800;
  color: white;
  margin: 0 0 10px;
}

.tech-card p {
  font-size: 0.8rem;
  color: #64748b;
  line-height: 1.6;
  margin: 0 0 30px;
  height: 48px;
  overflow: hidden;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
}

.card-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 20px;
  border-top: 1px solid rgba(255,255,255,0.03);
}

.module-access {
  display: flex;
  gap: 6px;
}

.tag {
  font-size: 0.6rem;
  font-weight: 800;
  color: #475569;
  background: rgba(255,255,255,0.02);
  padding: 3px 8px;
  border-radius: 6px;
}

.config-trigger {
  background: rgba(99, 102, 241, 0.1);
  color: #818cf8;
  padding: 8px 16px;
  border-radius: 12px;
  font-size: 0.7rem;
  font-weight: 900;
  text-decoration: none;
  transition: 0.2s;
  display: flex;
  align-items: center;
  gap: 8px;
}

.config-trigger:hover {
  background: #6366f1;
  color: white;
  transform: translateX(4px);
}

@keyframes pulse {
  0% { transform: scale(1); opacity: 0.5; }
  50% { transform: scale(1.2); opacity: 0.8; }
  100% { transform: scale(1); opacity: 0.5; }
}

@media (max-width: 768px) {
  .beta-header { padding: 30px; }
  .header-content { flex-direction: column; gap: 20px; align-items: flex-start; }
  .header-left h1 { font-size: 2rem; }
}
</style>
