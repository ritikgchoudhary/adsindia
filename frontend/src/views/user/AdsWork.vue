<template>
  <DashboardLayout page-title="Ads Work">
    <div class="row">
      <div class="col-12">
        <div class="card custom--card mb-4">
          <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-video me-2"></i>Watch Ads & Earn</h5>
          </div>
          <div class="card-body">
            <div class="alert alert-info">
              <i class="fas fa-info-circle me-2"></i>
              Watch ads completely to earn money. Each ad has a limited time (30 minutes).
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row gy-4">
      <div v-for="ad in ads" :key="ad?.id || Math.random()" class="col-lg-4 col-md-6" v-if="ad && ad.id">
        <div class="card custom--card">
          <div class="card-body">
            <div class="ad-item">
              <div class="ad-thumb mb-3">
                <img :src="ad.image || '/assets/images/default-ad.jpg'" :alt="ad.title" class="img-fluid rounded">
              </div>
              <h5 class="ad-title">{{ ad.title }}</h5>
              <p class="text-muted mb-3">{{ ad.description }}</p>
              
              <div class="ad-info mb-3">
                <div class="d-flex justify-content-between mb-2">
                  <span><i class="fas fa-clock me-1"></i>Duration:</span>
                  <strong>{{ ad.duration || 30 }} minutes</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                  <span><i class="fas fa-coins me-1"></i>Earning:</span>
                  <strong class="text-success">{{ currencySymbol }}{{ formatAmount(ad.earning) }}</strong>
                </div>
                <div v-if="ad.timer" class="d-flex justify-content-between">
                  <span><i class="fas fa-hourglass-half me-1"></i>Time Left:</span>
                  <strong class="text-warning">{{ formatTime(ad.timer) }}</strong>
                </div>
              </div>

              <div v-if="ad.is_watched" class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>Ad watched! Earning added to your account.
              </div>
              <button v-else-if="!ad.is_active" class="btn btn--base w-100" disabled>
                <i class="fas fa-lock me-2"></i>Ad Not Available
              </button>
              <button v-else class="btn btn--base w-100" @click="watchAd(ad)">
                <i class="fas fa-play me-2"></i>Watch Ad Now
              </button>
            </div>
          </div>
        </div>
      </div>

      <div v-if="ads.length === 0" class="col-12">
        <div class="card custom--card">
          <div class="card-body text-center">
            <i class="fas fa-video fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No ads available at the moment</h5>
            <p class="text-muted">Please check back later or purchase an Ad Plan to unlock more ads.</p>
            <router-link to="/user/ad-plans" class="btn btn--base mt-3">
              <i class="fas fa-shopping-cart me-2"></i>View Ad Plans
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <!-- Ad Watch Modal -->
    <div v-if="showAdModal" class="modal fade custom--modal show" style="display: block;" tabindex="-1">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ currentAd?.title }}</h5>
            <button type="button" class="btn-close" @click="closeAdModal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div v-if="adTimer > 0" class="text-center">
              <div class="mb-4">
                <div class="progress" style="height: 30px;">
                  <div class="progress-bar progress-bar-striped progress-bar-animated" 
                       role="progressbar" 
                       :style="`width: ${(adTimer / (currentAd?.duration || 30) * 60) * 100}%`">
                    {{ formatTime(adTimer) }} remaining
                  </div>
                </div>
              </div>
              <div class="ad-video-container mb-3">
                <img v-if="currentAd?.image" :src="currentAd.image" alt="Ad" class="img-fluid rounded">
                <div v-else class="ad-placeholder d-flex align-items-center justify-content-center" style="height: 300px; background: #f0f0f0;">
                  <i class="fas fa-video fa-3x text-muted"></i>
                </div>
              </div>
              <p class="text-muted">Please watch the ad completely. Do not close this window.</p>
            </div>
            <div v-else class="text-center">
              <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
              <h4>Ad Watched Successfully!</h4>
              <p class="text-success">You earned {{ currencySymbol }}{{ formatAmount(currentAd?.earning) }}</p>
              <button class="btn btn--base" @click="closeAdModal">Close</button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div v-if="showAdModal" class="modal-backdrop fade show" @click="closeAdModal"></div>
  </DashboardLayout>
</template>

<script>
import { ref, onMounted, onUnmounted } from 'vue'
import DashboardLayout from '../../components/DashboardLayout.vue'
import api from '../../services/api'

export default {
  name: 'AdsWork',
  components: {
    DashboardLayout
  },
  setup() {
    const ads = ref([])
    const showAdModal = ref(false)
    const currentAd = ref(null)
    const adTimer = ref(0)
    const timerInterval = ref(null)
    const currencySymbol = ref('₹')

    const formatAmount = (amount) => {
      if (!amount && amount !== 0) return '0.00'
      return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    }

    const formatTime = (seconds) => {
      const mins = Math.floor(seconds / 60)
      const secs = seconds % 60
      return `${mins}:${secs.toString().padStart(2, '0')}`
    }

    const watchAd = (ad) => {
      if (!ad || !ad.id) return
      currentAd.value = ad
      adTimer.value = (ad.duration || 30) * 60 // Convert minutes to seconds
      showAdModal.value = true

      timerInterval.value = setInterval(() => {
        adTimer.value--
        if (adTimer.value <= 0) {
          clearInterval(timerInterval.value)
          completeAd(ad)
        }
      }, 1000)
    }

    const completeAd = async (ad) => {
      if (!ad || !ad.id) return
      try {
        const response = await api.post('/ads/complete', { ad_id: ad.id })
        if (response.data.status === 'success') {
          if (window.notify) {
            window.notify('success', `You earned ${currencySymbol.value}${formatAmount(ad.earning)}!`)
          }
          fetchAds()
        }
      } catch (error) {
        console.error('Error completing ad:', error)
        if (window.notify) {
          window.notify('error', error.response?.data?.message || 'Failed to complete ad')
        }
      }
    }

    const closeAdModal = () => {
      if (timerInterval.value) {
        clearInterval(timerInterval.value)
        timerInterval.value = null
      }
      showAdModal.value = false
      currentAd.value = null
      adTimer.value = 0
    }

    const fetchAds = async () => {
      try {
        const response = await api.get('/ads/work')
        if (response.data.status === 'success') {
          ads.value = response.data.data || []
          currencySymbol.value = response.data.currency_symbol || '₹'
        }
      } catch (error) {
        console.error('Error loading ads:', error)
      }
    }

    onMounted(() => {
      fetchAds()
    })

    onUnmounted(() => {
      if (timerInterval.value) {
        clearInterval(timerInterval.value)
      }
    })

    return {
      ads,
      showAdModal,
      currentAd,
      adTimer,
      currencySymbol,
      formatAmount,
      formatTime,
      watchAd,
      closeAdModal
    }
  }
}
</script>

<style scoped>
.ad-item {
  text-align: center;
}

.ad-thumb img {
  width: 100%;
  height: 200px;
  object-fit: cover;
}

.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 1040;
}

.modal.show {
  display: block;
  z-index: 1050;
}
</style>
