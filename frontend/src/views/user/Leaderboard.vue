<template>
  <DashboardLayout page-title="Leaderboard" :dark-theme="true">
    <div class="leaderboard-container">
      <!-- Header Section -->
      <div class="leaderboard-header">
        <div class="header-content">
          <div class="header-title-section">
            <div class="title-wrapper">
              <div class="trophy-icon-wrapper">
                <i class="fas fa-trophy"></i>
              </div>
              <div>
                <h1 class="main-title">Top Earners</h1>
                <p class="subtitle">Compete with the best and climb to the top</p>
              </div>
            </div>
          </div>
          
          <!-- Period Selector -->
          <div class="period-selector">
            <button
              v-if="settings.show_today"
              type="button"
              class="period-btn"
              :class="{ 'active': selectedType === 'today' }"
              @click="changeType('today')">
              <span>Today</span>
            </button>
            <button
              v-if="settings.show_weekly"
              type="button"
              class="period-btn"
              :class="{ 'active': selectedType === 'weekly' }"
              @click="changeType('weekly')">
              <span>Weekly</span>
            </button>
            <button
              v-if="settings.show_monthly"
              type="button"
              class="period-btn"
              :class="{ 'active': selectedType === 'monthly' }"
              @click="changeType('monthly')">
              <span>Monthly</span>
            </button>
            <button
              v-if="settings.show_all_time"
              type="button"
              class="period-btn"
              :class="{ 'active': selectedType === 'alltime' }"
              @click="changeType('alltime')">
              <span>All Time</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Main Ranking List (Top 10) -->
      <div class="leaderboard-list-card animate-fade-in">
        <div v-if="loading" class="loading-state">
          <div class="loading-spinner">
            <i class="fas fa-circle-notch fa-spin"></i>
          </div>
          <div class="loading-text">Loading Leaderboard...</div>
        </div>

        <div v-else-if="leaderboard.length === 0" class="empty-state">
          <div class="empty-icon">
            <i class="fas fa-medal"></i>
          </div>
          <h4 class="empty-title">No Rankings Yet</h4>
          <p class="empty-text">Be the first to earn and claim the top spot!</p>
        </div>

        <div v-else class="tw-p-2 sm:tw-p-8">
          <div class="tw-flex tw-flex-col tw-gap-3 sm:tw-gap-4">
            <div
              v-for="(entry, index) in topTenLeaderboard"
              :key="entry?.rank ?? index"
              class="rank-row tw-group"
            >
              <div class="rank-row-content">
                <!-- Rank Number -->
                <div class="rank-pos-wrapper">
                   <div v-if="index === 0" class="rank-badge rank-gold">1</div>
                   <div v-else-if="index === 1" class="rank-badge rank-silver">2</div>
                   <div v-else-if="index === 2" class="rank-badge rank-bronze">3</div>
                   <div v-else class="rank-badge rank-default">{{ index + 1 }}</div>
                </div>

                <!-- User Profile -->
                <div class="rank-user-info">
                  <div class="rank-avatar-wrapper">
                    <img
                      :src="entry.image || '/assets/images/default-user.png'"
                      alt=""
                      class="rank-avatar"
                      @error="$event.target.src = '/assets/images/default-user.png'"
                    />
                    <div v-if="index < 3" class="rank-crown">
                      <i class="fas fa-crown"></i>
                    </div>
                  </div>
                  <div class="rank-name-wrapper">
                    <div class="rank-name">{{ entry.name }}</div>
                    <div v-if="index < 3" class="rank-status">Top Performer</div>
                  </div>
                </div>

                <!-- Earnings -->
                <div class="rank-earning">
                  <div class="earning-val">{{ currencySymbol }}{{ formatAmount(entry.earning) }}</div>
                  <div class="earning-lbl">Total Earned</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import DashboardLayout from '../../components/DashboardLayout.vue'
import api from '../../services/api'

export default {
  name: 'Leaderboard',
  components: {
    DashboardLayout
  },
  setup() {
    const leaderboard = ref([])
    const currentUser = ref(null)
    const selectedType = ref('') // Start empty, will be set from settings
    const currencySymbol = ref('₹')
    const loading = ref(true)
    const settings = ref({
      show_today: true,
      show_weekly: true,
      show_monthly: true,
      show_all_time: true
    })

    // Show only top 10 users
    const topTenLeaderboard = computed(() => {
      if (!Array.isArray(leaderboard.value)) return []
      return leaderboard.value.slice(0, 10).filter(entry => entry && entry.name)
    })

    const formatAmount = (amount) => {
      if (amount == null || amount === '') return '0.00'
      return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    }

    const changeType = (type) => {
      selectedType.value = type
      fetchLeaderboard()
    }

    const fetchLeaderboard = async () => {
      loading.value = true
      try {
        // If it's the first load (type is empty), fetch settings first
        const response = await api.get('/leaderboard', {
          params: { type: selectedType.value || 'alltime' } // fallback for first hit
        })
        
        if (response.data?.status === 'success') {
          const payload = response.data.data || {}
          
          if (payload.settings) {
            settings.value = payload.settings
            
            // If we are on first load (selectedType is empty)
            if (!selectedType.value) {
              const order = ['today', 'weekly', 'monthly', 'alltime']
              const fallback = order.find(t => {
                const k = (t === 'alltime' ? 'show_all_time' : `show_${t}`)
                return settings.value[k] === true || settings.value[k] === 1
              })
              selectedType.value = fallback || 'alltime'
              
              // If the fallback is different from what we just fetched ('alltime'), re-fetch
              if (selectedType.value !== 'alltime') {
                fetchLeaderboard()
                return
              }
            }
          }

          leaderboard.value = payload.rows || []
          currentUser.value = payload.current_user || null
          currencySymbol.value = payload.currency_symbol ?? response.data.currency_symbol ?? '₹'
          
        } else {
          leaderboard.value = []
        }
      } catch (error) {
        console.error('Error loading leaderboard:', error)
        leaderboard.value = []
      } finally {
        loading.value = false
      }
    }

    onMounted(() => {
      fetchLeaderboard()
    })

    return {
      leaderboard,
      topTenLeaderboard,
      currentUser,
      selectedType,
      currencySymbol,
      loading,
      settings,
      formatAmount,
      changeType
    }
  }
}
</script>

<style scoped>
.leaderboard-container {
  max-width: 1000px;
  margin: 0 auto;
  padding-bottom: 3rem;
}

/* Header Section */
.leaderboard-header {
  background: linear-gradient(135deg, rgba(99, 102, 241, 0.15) 0%, rgba(168, 85, 247, 0.15) 100%);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 32px;
  padding: 2.5rem;
  margin-bottom: 2.5rem;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
}

.header-content {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.title-wrapper {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.trophy-icon-wrapper {
  width: 70px;
  height: 70px;
  background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
  border-radius: 22px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2.25rem;
  color: white;
  box-shadow: 0 8px 30px rgba(251, 191, 36, 0.4);
}

.main-title {
  font-size: 2.75rem;
  font-weight: 900;
  color: white;
  margin: 0;
  letter-spacing: -1px;
}

.subtitle {
  color: rgba(255, 255, 255, 0.6);
  font-size: 1rem;
  font-weight: 500;
  margin: 4px 0 0 0;
}

/* Period Selector */
.period-selector {
  display: flex;
  gap: 8px;
  background: rgba(0, 0, 0, 0.4);
  padding: 6px;
  border-radius: 20px;
  border: 1px solid rgba(255, 255, 255, 0.05);
  align-self: flex-start;
}

.period-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 24px;
  border: none;
  border-radius: 16px;
  background: transparent;
  color: rgba(255, 255, 255, 0.5);
  font-weight: 700;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.period-btn.active {
  background: white;
  color: #1a1a1a;
  box-shadow: 0 4px 20px rgba(255, 255, 255, 0.2);
}

.period-btn:not(.active):hover {
  background: rgba(255, 255, 255, 0.05);
  color: white;
}

/* List Card */
.leaderboard-list-card {
  background: #0f172a;
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 32px;
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(0,0,0,0.5);
}

/* Rank Rows */
.rank-row {
  margin-bottom: 12px;
}

.rank-row-content {
  display: flex;
  align-items: center;
  padding: 1.25rem 1.75rem;
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.03);
  border-radius: 24px;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.rank-row:hover .rank-row-content {
  background: rgba(255, 255, 255, 0.05);
  border-color: rgba(99, 102, 241, 0.3);
  transform: scale(1.02);
}

/* Badges */
.rank-pos-wrapper {
  width: 50px;
  flex-shrink: 0;
}

.rank-badge {
  width: 36px;
  height: 36px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 950;
  font-size: 1.1rem;
}

.rank-gold { background: linear-gradient(135deg, #fbbf24 0%, #d97706 100%); color: white; }
.rank-silver { background: linear-gradient(135deg, #94a3b8 0%, #475569 100%); color: white; }
.rank-bronze { background: linear-gradient(135deg, #b45309 0%, #78350f 100%); color: white; }
.rank-default { border: 1.5px solid rgba(255,255,255,0.1); color: #64748b; }

/* User Details */
.rank-user-info {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 1.25rem;
  margin-left: 1rem;
}

.rank-avatar-wrapper {
  position: relative;
}

.rank-avatar {
  width: 54px;
  height: 54px;
  border-radius: 18px;
  object-fit: cover;
  border: 2px solid rgba(255,255,255,0.05);
  background: #1e293b;
}

.rank-crown {
  position: absolute;
  top: -10px;
  left: -10px;
  font-size: 1.25rem;
  color: #fbbf24;
  transform: rotate(-25deg);
  filter: drop-shadow(0 2px 4px rgba(0,0,0,0.4));
}

.rank-name {
  font-size: 1.15rem;
  font-weight: 800;
  color: white;
}

.rank-status {
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: #10b981;
  margin-top: 2px;
}

/* Earnings */
.rank-earning {
  text-align: right;
}

.earning-val {
  font-size: 1.5rem;
  font-weight: 950;
  color: #fbbf24;
  letter-spacing: -0.5px;
}

.earning-lbl {
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  color: #64748b;
  letter-spacing: 1px;
}

/* States */
.loading-state, .empty-state {
  padding: 6rem 0;
  text-align: center;
}

.loading-spinner { font-size: 3rem; color: #6366f1; margin-bottom: 1rem; }
.loading-text { color: #94a3b8; font-weight: 600; }

@media (max-width: 768px) {
  .leaderboard-header { padding: 1.25rem; margin-bottom: 1.5rem; border-radius: 24px; }
  .trophy-icon-wrapper { width: 50px; height: 50px; font-size: 1.5rem; border-radius: 14px; }
  .main-title { font-size: 1.75rem; }
  .subtitle { font-size: 0.85rem; }
  .period-btn { padding: 8px 16px; font-size: 0.8rem; }
  .leaderboard-list-card { border-radius: 20px; }
}

@media (max-width: 640px) {
  .rank-row-content { padding: 0.85rem 1rem; border-radius: 18px; }
  .rank-pos-wrapper { width: 32px; }
  .rank-badge { width: 28px; height: 28px; font-size: 0.85rem; border-radius: 8px; }
  .rank-user-info { gap: 0.75rem; margin-left: 0.5rem; }
  .rank-avatar { width: 40px; height: 40px; border-radius: 12px; }
  .rank-name { font-size: 0.95rem; }
  .rank-status { font-size: 0.6rem; letter-spacing: 0.5px; }
  .rank-earning .earning-val { font-size: 1.1rem; }
  .rank-earning .earning-lbl { font-size: 0.6rem; letter-spacing: 0.5px; }
  .rank-crown { font-size: 1rem; top: -8px; left: -8px; }
}

@media (max-width: 480px) {
  .leaderboard-container { padding-left: 0 !important; padding-right: 0 !important; }
  .leaderboard-header { padding: 0.75rem 0.5rem !important; border-radius: 16px !important; margin-bottom: 0.75rem !important; }
  .trophy-icon-wrapper { width: 32px; height: 32px; font-size: 1rem; border-radius: 8px; }
  .main-title { font-size: 1.1rem; }
  .subtitle { font-size: 0.65rem; }
  .period-selector { 
    width: 100%;
    padding: 4px; 
    border-radius: 14px; 
    align-self: stretch;
    gap: 4px;
  }
  .period-btn { 
    flex: 1;
    padding: 8px 4px; 
    font-size: 0.75rem; 
    border-radius: 10px; 
    justify-content: center;
  }
  
  .rank-row { margin-bottom: 6px; }
  .rank-row-content { 
    padding: 0.45rem 0.6rem; 
    border-radius: 12px; 
    background: rgba(255, 255, 255, 0.05); /* Slightly lighter for better contrast */
  }
  .rank-pos-wrapper { width: 22px; }
  .rank-badge { 
    width: auto; 
    height: auto; 
    font-size: 0.8rem; 
    background: transparent !important; 
    color: #94a3b8 !important; 
    border: none !important;
  }
  .rank-badge::after { content: "."; } /* Matching the "1." style from ref */
  
  .rank-user-info { gap: 0.5rem; margin-left: 0.25rem; }
  .rank-avatar { width: 32px; height: 32px; border-radius: 50%; } /* Circular avatar like ref */
  .rank-avatar-wrapper { display: flex; align-items: center; }
  .rank-crown { font-size: 0.8rem; top: -6px; left: -6px; }
  
  .rank-name { font-size: 0.75rem; font-weight: 700; color: #f8fafc; }
  .rank-earning .earning-val { font-size: 0.8rem; font-weight: 700; color: #fbbf24; }
  
  /* Strictly hide extra labels */
  .rank-status, .earning-lbl { display: none !important; }
}
</style>
