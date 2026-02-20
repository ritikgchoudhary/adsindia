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
              type="button"
              class="period-btn"
              :class="{ 'active': selectedType === 'weekly' }"
              @click="changeType('weekly')">
              <i class="fas fa-calendar-week"></i>
              <span>Weekly</span>
            </button>
            <button
              type="button"
              class="period-btn"
              :class="{ 'active': selectedType === 'monthly' }"
              @click="changeType('monthly')">
              <i class="fas fa-calendar-alt"></i>
              <span>Monthly</span>
            </button>
            <button
              type="button"
              class="period-btn"
              :class="{ 'active': selectedType === 'alltime' }"
              @click="changeType('alltime')">
              <i class="fas fa-infinity"></i>
              <span>All Time</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Top 3 Podium -->
      <div v-if="false && !loading && leaderboard.length >= 3" class="podium-section">
        <div class="podium-container">
          <!-- 2nd Place -->
          <div class="podium-item second-place" :class="{ 'animate-in': !loading }">
            <div class="podium-rank-badge silver">
              <i class="fas fa-medal"></i>
            </div>
            <div class="podium-avatar">
              <span>{{ (leaderboard[1]?.username || '?').charAt(0).toUpperCase() }}</span>
            </div>
            <div class="podium-username">{{ leaderboard[1]?.username || '–' }}</div>
            <div class="podium-earning">{{ currencySymbol }}{{ formatAmount(leaderboard[1]?.earning) }}</div>
            <div class="podium-base silver-base">
              <div class="podium-rank">2</div>
            </div>
          </div>

          <!-- 1st Place -->
          <div class="podium-item first-place" :class="{ 'animate-in': !loading }">
            <div class="podium-rank-badge gold">
              <i class="fas fa-crown"></i>
            </div>
            <div class="podium-avatar champion">
              <span>{{ (leaderboard[0]?.username || '?').charAt(0).toUpperCase() }}</span>
              <div class="champion-glow"></div>
            </div>
            <div class="podium-username champion-name">{{ leaderboard[0]?.username || '–' }}</div>
            <div class="podium-earning champion-earning">{{ currencySymbol }}{{ formatAmount(leaderboard[0]?.earning) }}</div>
            <div class="podium-base gold-base">
              <div class="podium-rank">1</div>
            </div>
          </div>

          <!-- 3rd Place -->
          <div class="podium-item third-place" :class="{ 'animate-in': !loading }">
            <div class="podium-rank-badge bronze">
              <i class="fas fa-award"></i>
            </div>
            <div class="podium-avatar">
              <span>{{ (leaderboard[2]?.username || '?').charAt(0).toUpperCase() }}</span>
            </div>
            <div class="podium-username">{{ leaderboard[2]?.username || '–' }}</div>
            <div class="podium-earning">{{ currencySymbol }}{{ formatAmount(leaderboard[2]?.earning) }}</div>
            <div class="podium-base bronze-base">
              <div class="podium-rank">3</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Current User Card -->
      <div v-if="currentUser" class="current-user-card">
        <div class="user-card-content">
          <div class="user-info-section">
            <div class="user-rank-badge">
              <span class="rank-number">#{{ currentUser.rank }}</span>
            </div>
            <div class="user-details">
              <div class="user-label">Your Position</div>
              <div class="tw-flex tw-items-center tw-gap-3">
                <img
                  :src="currentUser.image"
                  alt="Profile"
                  class="tw-w-10 tw-h-10 tw-rounded-full tw-object-cover tw-bg-slate-800 tw-border tw-border-white/10"
                  loading="lazy"
                />
                <div class="tw-flex tw-flex-col tw-leading-tight">
                  <div class="user-name">{{ currentUser.name || currentUser.username || '—' }}</div>
                  <div class="tw-text-xs tw-text-white/60">@{{ currentUser.username }}</div>
                </div>
              </div>
            </div>
          </div>
          <div class="user-earning-section">
            <div class="earning-label">Your Earnings</div>
            <div class="earning-amount">
              {{ currencySymbol }}{{ formatAmount(currentUser.earning) }}
            </div>
          </div>
        </div>
      </div>

      <!-- Leaderboard List -->
      <div class="leaderboard-list-card">
        <div class="list-header">
          <h3 class="list-title">
            <i class="fas fa-list-ol"></i>
            Full Rankings
          </h3>
          <button
            v-if="leaderboard.length > 10"
            type="button"
            class="tw-inline-flex tw-items-center tw-gap-2 tw-px-4 tw-py-2 tw-rounded-xl tw-bg-white/5 hover:tw-bg-white/10 tw-border tw-border-white/10 tw-text-white tw-text-sm tw-font-bold tw-transition-colors"
            @click="showAll = !showAll"
          >
            <i class="fas" :class="showAll ? 'fa-compress-arrows-alt' : 'fa-expand-arrows-alt'"></i>
            {{ showAll ? 'Show Top 10' : 'Show All' }}
          </button>
        </div>


        <div v-if="loading" class="loading-state">
          <div class="loading-spinner">
            <i class="fas fa-spinner fa-spin"></i>
          </div>
          <div class="loading-text">Loading Rankings...</div>
        </div>

        <div v-else-if="leaderboard.length === 0" class="empty-state">
          <div class="empty-icon">
            <i class="fas fa-chart-line"></i>
          </div>
          <h4 class="empty-title">No Rankings Yet</h4>
          <p class="empty-text">Be the first to earn and claim your spot!</p>
        </div>

        <div v-else class="tw-overflow-x-auto tw-p-5">
          <table class="tw-w-full tw-text-sm tw-border-collapse">
            <thead>
              <tr class="tw-bg-slate-950/40 tw-border-b tw-border-white/10">
                <th class="tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-bold tw-uppercase tw-text-slate-300 tw-tracking-wider">User</th>
                <th class="tw-px-4 tw-py-3 tw-text-right tw-text-xs tw-font-bold tw-uppercase tw-text-slate-300 tw-tracking-wider">Earning</th>
              </tr>
            </thead>
            <tbody class="tw-divide-y tw-divide-white/10">
              <tr
                v-for="(entry, index) in displayedLeaderboard"
                :key="entry?.rank ?? index"
                v-show="entry && entry.username"
                class="hover:tw-bg-white/5 tw-transition-colors"
              >
                <td class="tw-px-4 tw-py-3">
                  <div class="tw-flex tw-items-center tw-gap-3">
                    <div class="tw-w-10 tw-text-center tw-font-extrabold tw-text-slate-300">
                      #{{ entry.rank }}
                    </div>
                    <img
                      :src="entry.image"
                      alt="Profile"
                      class="tw-w-10 tw-h-10 tw-rounded-full tw-object-cover tw-bg-slate-800 tw-border tw-border-white/10"
                      loading="lazy"
                    />
                    <div class="tw-flex tw-flex-col">
                      <div class="tw-font-bold tw-text-slate-100">
                        {{ entry.name || entry.username || '–' }}
                      </div>
                      <div class="tw-text-xs tw-text-slate-400">@{{ entry.username }}</div>
                    </div>
                  </div>
                </td>
                <td class="tw-px-4 tw-py-3 tw-text-right">
                  <div class="tw-font-extrabold tw-text-emerald-300">
                    {{ currencySymbol }}{{ formatAmount(entry.earning) }}
                  </div>
                  <div class="tw-text-[11px] tw-text-slate-400">
                    Ads: {{ currencySymbol }}{{ formatAmount(entry.ads_income) }} • Conv: {{ currencySymbol }}{{ formatAmount(entry.conversion_income) }}
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
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
    const selectedType = ref('weekly')
    const currencySymbol = ref('₹')
    const loading = ref(true)
    const showAll = ref(false)

    const displayedLeaderboard = computed(() => {
      const rows = Array.isArray(leaderboard.value) ? leaderboard.value : []
      return showAll.value ? rows : rows.slice(0, 10)
    })

    const formatAmount = (amount) => {
      if (amount == null || amount === '') return '0.00'
      return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    }

    const changeType = (type) => {
      selectedType.value = type
      showAll.value = false
      fetchLeaderboard()
    }

    const fetchLeaderboard = async () => {
      loading.value = true
      try {
        const response = await api.get('/leaderboard', {
          params: { type: selectedType.value }
        })
        if (response.data?.status === 'success') {
          const payload = response.data.data || {}
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
      displayedLeaderboard,
      currentUser,
      selectedType,
      currencySymbol,
      loading,
      showAll,
      formatAmount,
      changeType
    }
  }
}
</script>

<style scoped>
/* Container */
.leaderboard-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0;
}

/* Header Section */
.leaderboard-header {
  background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(168, 85, 247, 0.1) 100%);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 24px;
  padding: 2rem;
  margin-bottom: 2rem;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
}

.header-content {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.header-title-section {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.title-wrapper {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.trophy-icon-wrapper {
  width: 64px;
  height: 64px;
  background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
  border-radius: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  color: white;
  box-shadow: 0 8px 24px rgba(251, 191, 36, 0.4);
  animation: float 3s ease-in-out infinite;
}

@keyframes float {
  0%, 100% { transform: translateY(0px); }
  50% { transform: translateY(-10px); }
}

.main-title {
  font-size: 2.5rem;
  font-weight: 800;
  background: linear-gradient(135deg, #ffffff 0%, #e0e7ff 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin: 0;
  line-height: 1.2;
}

.subtitle {
  color: rgba(255, 255, 255, 0.6);
  font-size: 1rem;
  margin: 0.5rem 0 0 0;
}

/* Period Selector */
.period-selector {
  display: flex;
  gap: 0.75rem;
  background: rgba(0, 0, 0, 0.3);
  padding: 0.5rem;
  border-radius: 16px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  flex-wrap: wrap;
}

.period-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 12px;
  background: transparent;
  color: rgba(255, 255, 255, 0.5);
  font-weight: 600;
  font-size: 0.95rem;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
}

.period-btn i {
  font-size: 1rem;
}

.period-btn::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(99, 102, 241, 0.2) 0%, rgba(168, 85, 247, 0.2) 100%);
  opacity: 0;
  transition: opacity 0.3s;
}

.period-btn:hover {
  color: rgba(255, 255, 255, 0.8);
  transform: translateY(-2px);
}

.period-btn:hover::before {
  opacity: 1;
}

.period-btn.active {
  background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
  color: white;
  box-shadow: 0 4px 16px rgba(99, 102, 241, 0.4);
}

.period-btn.active::before {
  opacity: 0;
}

/* Podium Section */
.podium-section {
  margin: 3rem 0;
  padding: 3rem 1rem;
  background: linear-gradient(135deg, rgba(99, 102, 241, 0.05) 0%, rgba(168, 85, 247, 0.05) 100%);
  border-radius: 24px;
  border: 1px solid rgba(255, 255, 255, 0.05);
}

.podium-container {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 2rem;
  max-width: 900px;
  margin: 0 auto;
  align-items: flex-end;
}

.podium-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  opacity: 0;
  transform: translateY(30px);
}

.podium-item.animate-in {
  animation: slideUp 0.6s cubic-bezier(0.4, 0, 0.2, 1) forwards;
}

.podium-item.first-place {
  animation-delay: 0.2s;
  order: 2;
}

.podium-item.second-place {
  animation-delay: 0.1s;
  order: 1;
}

.podium-item.third-place {
  animation-delay: 0.3s;
  order: 3;
}

@keyframes slideUp {
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.podium-rank-badge {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
  position: relative;
  z-index: 2;
}

.podium-rank-badge.gold {
  background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
  color: white;
  animation: pulse 2s ease-in-out infinite;
}

.podium-rank-badge.silver {
  background: linear-gradient(135deg, #cbd5e1 0%, #94a3b8 100%);
  color: white;
}

.podium-rank-badge.bronze {
  background: linear-gradient(135deg, #fb923c 0%, #ea580c 100%);
  color: white;
}

@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.1); }
}

.podium-avatar {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  font-weight: 700;
  color: white;
  border: 4px solid rgba(255, 255, 255, 0.1);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
  position: relative;
}

.podium-avatar.champion {
  width: 100px;
  height: 100px;
  font-size: 2.5rem;
  border: 4px solid #fbbf24;
}

.champion-glow {
  position: absolute;
  inset: -10px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(251, 191, 36, 0.3) 0%, transparent 70%);
  animation: glow 2s ease-in-out infinite;
  z-index: -1;
}

@keyframes glow {
  0%, 100% { opacity: 0.5; transform: scale(1); }
  50% { opacity: 1; transform: scale(1.1); }
}

.podium-username {
  font-weight: 700;
  font-size: 1rem;
  color: rgba(255, 255, 255, 0.9);
  text-align: center;
}

.champion-name {
  font-size: 1.25rem;
  background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.podium-earning {
  font-weight: 800;
  font-size: 1.25rem;
  color: #10b981;
  text-align: center;
}

.champion-earning {
  font-size: 1.5rem;
}

.podium-base {
  width: 100%;
  padding: 2rem 1rem 1.5rem;
  border-radius: 16px 16px 0 0;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-top: 1rem;
  position: relative;
  overflow: hidden;
}

.podium-base::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(180deg, rgba(255, 255, 255, 0.1) 0%, transparent 100%);
}

.gold-base {
  background: linear-gradient(135deg, rgba(251, 191, 36, 0.2) 0%, rgba(245, 158, 11, 0.2) 100%);
  border: 2px solid rgba(251, 191, 36, 0.3);
  min-height: 180px;
}

.silver-base {
  background: linear-gradient(135deg, rgba(203, 213, 225, 0.2) 0%, rgba(148, 163, 184, 0.2) 100%);
  border: 2px solid rgba(203, 213, 225, 0.3);
  min-height: 140px;
}

.bronze-base {
  background: linear-gradient(135deg, rgba(251, 146, 60, 0.2) 0%, rgba(234, 88, 12, 0.2) 100%);
  border: 2px solid rgba(251, 146, 60, 0.3);
  min-height: 100px;
}

.podium-rank {
  font-size: 3rem;
  font-weight: 900;
  color: rgba(255, 255, 255, 0.3);
  position: relative;
  z-index: 1;
}

/* Current User Card */
.current-user-card {
  background: linear-gradient(135deg, rgba(99, 102, 241, 0.15) 0%, rgba(168, 85, 247, 0.15) 100%);
  backdrop-filter: blur(20px);
  border: 2px solid rgba(99, 102, 241, 0.3);
  border-radius: 20px;
  padding: 1.5rem 2rem;
  margin-bottom: 2rem;
  box-shadow: 0 8px 32px rgba(99, 102, 241, 0.2);
}

.user-card-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 2rem;
}

.user-info-section {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.user-rank-badge {
  width: 60px;
  height: 60px;
  background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 16px rgba(99, 102, 241, 0.4);
}

.rank-number {
  font-size: 1.5rem;
  font-weight: 900;
  color: white;
}

.user-details {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.user-label {
  font-size: 0.875rem;
  color: rgba(255, 255, 255, 0.5);
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.user-name {
  font-size: 1.25rem;
  font-weight: 700;
  color: white;
}

.user-earning-section {
  text-align: right;
}

.earning-label {
  font-size: 0.875rem;
  color: rgba(255, 255, 255, 0.5);
  font-weight: 600;
  margin-bottom: 0.25rem;
}

.earning-amount {
  font-size: 2rem;
  font-weight: 900;
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

/* Leaderboard List Card */
.leaderboard-list-card {
  background: rgba(0, 0, 0, 0.3);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 24px;
  overflow: hidden;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
}

.list-header {
  padding: 1.5rem 2rem;
  background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(168, 85, 247, 0.1) 100%);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.list-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: white;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.list-title i {
  color: #6366f1;
}

/* Loading & Empty States */
.loading-state,
.empty-state {
  padding: 4rem 2rem;
  text-align: center;
}

.loading-spinner {
  font-size: 3rem;
  color: #6366f1;
  margin-bottom: 1rem;
}

.loading-text {
  color: rgba(255, 255, 255, 0.6);
  font-weight: 600;
  font-size: 1.125rem;
}

.empty-icon {
  width: 80px;
  height: 80px;
  background: rgba(99, 102, 241, 0.1);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1.5rem;
  font-size: 2.5rem;
  color: rgba(255, 255, 255, 0.3);
}

.empty-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: white;
  margin: 0 0 0.5rem 0;
}

.empty-text {
  color: rgba(255, 255, 255, 0.5);
  font-size: 1rem;
  margin: 0;
}

/* Rankings List */
.rankings-list {
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.ranking-item {
  display: grid;
  grid-template-columns: 100px 1fr auto;
  align-items: center;
  gap: 1.5rem;
  padding: 1.25rem 1.5rem;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 16px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.ranking-item:hover {
  background: rgba(255, 255, 255, 0.06);
  border-color: rgba(99, 102, 241, 0.3);
  transform: translateX(8px);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
}

.ranking-item.is-current-user {
  background: linear-gradient(135deg, rgba(99, 102, 241, 0.15) 0%, rgba(168, 85, 247, 0.15) 100%);
  border: 2px solid rgba(99, 102, 241, 0.4);
  box-shadow: 0 4px 24px rgba(99, 102, 241, 0.2);
}

.ranking-item.is-top-three {
  background: rgba(255, 255, 255, 0.05);
}

.ranking-position {
  display: flex;
  justify-content: center;
}

.position-badge {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border-radius: 12px;
  font-weight: 700;
  font-size: 1rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.gold-badge {
  background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
  color: white;
}

.silver-badge {
  background: linear-gradient(135deg, #cbd5e1 0%, #94a3b8 100%);
  color: white;
}

.bronze-badge {
  background: linear-gradient(135deg, #fb923c 0%, #ea580c 100%);
  color: white;
}

.default-badge {
  background: rgba(255, 255, 255, 0.1);
  color: rgba(255, 255, 255, 0.7);
}

.ranking-user {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.user-avatar-small {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 1.125rem;
  color: white;
  border: 2px solid rgba(255, 255, 255, 0.1);
}

.username-text {
  font-weight: 600;
  font-size: 1.125rem;
  color: rgba(255, 255, 255, 0.9);
}

.ranking-earning {
  text-align: right;
}

.earning-value {
  font-weight: 800;
  font-size: 1.25rem;
  color: #10b981;
}

/* Responsive Design */
@media (max-width: 1024px) {
  .podium-container {
    gap: 1.5rem;
  }
  
  .podium-avatar {
    width: 70px;
    height: 70px;
    font-size: 1.75rem;
  }
  
  .podium-avatar.champion {
    width: 90px;
    height: 90px;
    font-size: 2.25rem;
  }
}

@media (max-width: 768px) {
  .header-content {
    gap: 1rem;
  }
  
  .main-title {
    font-size: 2rem;
  }
  
  .trophy-icon-wrapper {
    width: 56px;
    height: 56px;
    font-size: 1.75rem;
  }
  
  .period-selector {
    width: 100%;
  }
  
  .period-btn {
    flex: 1;
    justify-content: center;
    padding: 0.625rem 1rem;
    font-size: 0.875rem;
  }
  
  .podium-container {
    grid-template-columns: 1fr;
    gap: 2rem;
  }
  
  .podium-item {
    order: unset !important;
  }
  
  .podium-base {
    min-height: 120px !important;
  }
  
  .user-card-content {
    flex-direction: column;
    align-items: flex-start;
    gap: 1.5rem;
  }
  
  .user-earning-section {
    text-align: left;
    width: 100%;
  }
  
  .ranking-item {
    grid-template-columns: 80px 1fr;
    gap: 1rem;
    padding: 1rem;
  }
  
  .ranking-earning {
    grid-column: 2;
    text-align: left;
    margin-top: 0.5rem;
  }
  
  .position-badge {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
  }
  
  .username-text {
    font-size: 1rem;
  }
  
  .earning-value {
    font-size: 1.125rem;
  }
}

@media (max-width: 480px) {
  .leaderboard-header {
    padding: 1.5rem;
  }
  
  .title-wrapper {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }
  
  .main-title {
    font-size: 1.75rem;
  }
  
  .period-btn span {
    display: none;
  }
  
  .period-btn i {
    font-size: 1.25rem;
  }
  
  .current-user-card {
    padding: 1.25rem 1.5rem;
  }
  
  .earning-amount {
    font-size: 1.5rem;
  }
  
  .list-header {
    padding: 1.25rem 1.5rem;
  }
  
  .list-title {
    font-size: 1.25rem;
  }
}
</style>
