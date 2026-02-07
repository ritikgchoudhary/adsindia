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
            <div v-if="adTimer > 0 && !videoCompleted" class="text-center">
              <div class="mb-4">
                <div class="progress" style="height: 30px; border-radius: 15px;">
                  <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                       role="progressbar" 
                       :style="`width: ${((totalDuration - adTimer) / totalDuration) * 100}%`">
                    {{ formatTime(adTimer) }} remaining
                  </div>
                </div>
                <p class="mt-2 mb-0" style="color: #4a5568;">
                  <i class="fas fa-info-circle me-2"></i>
                  Watch the complete video to earn {{ currencySymbol }}{{ formatAmount(currentAd?.earning) }}
                </p>
              </div>
              <div class="ad-video-container mb-3" style="position: relative;">
                <video 
                  ref="videoPlayer"
                  :src="currentAd?.video_url" 
                  controls 
                  autoplay
                  class="img-fluid rounded"
                  style="width: 100%; max-height: 500px; background: #000;"
                  @timeupdate="onVideoTimeUpdate"
                  @ended="onVideoEnded"
                  @play="onVideoPlay"
                  @pause="onVideoPause"
                >
                  Your browser does not support the video tag.
                </video>
                <div v-if="!isVideoPlaying" class="video-overlay d-flex align-items-center justify-content-center" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); border-radius: 8px;">
                  <div class="text-white text-center">
                    <i class="fas fa-play-circle fa-3x mb-3"></i>
                    <p class="mb-0">Click play to start watching</p>
                  </div>
                </div>
              </div>
              <div class="alert alert-warning" style="border-radius: 10px;">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Important:</strong> Please watch the complete video. Do not close this window or skip the video.
              </div>
            </div>
            <div v-else-if="videoCompleted" class="text-center">
              <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
              <h4 style="color: #2d3748;">Ad Watched Successfully!</h4>
              <p class="text-success mb-3" style="font-size: 18px; font-weight: 600;">
                <i class="fas fa-coins me-2"></i>
                You earned {{ currencySymbol }}{{ formatAmount(currentAd?.earning) }}
              </p>
              <p class="text-muted mb-4">Earning has been added to your account balance.</p>
              <button class="btn btn--base btn-lg px-5" @click="closeAdModal" style="border-radius: 10px; font-weight: 600;">
                <i class="fas fa-check me-2"></i>Close
              </button>
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
    const totalDuration = ref(0)
    const timerInterval = ref(null)
    const currencySymbol = ref('₹')
    const videoPlayer = ref(null)
    const isVideoPlaying = ref(false)
    const videoCompleted = ref(false)
    const watchStartTime = ref(null)
    const watchDuration = ref(0)

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
      totalDuration.value = ad.duration_seconds || (ad.duration || 30) * 60
      adTimer.value = totalDuration.value
      videoCompleted.value = false
      watchDuration.value = 0
      watchStartTime.value = Date.now()
      showAdModal.value = true
      isVideoPlaying.value = false

      // Start timer
      timerInterval.value = setInterval(() => {
        if (isVideoPlaying.value && videoPlayer.value && !videoPlayer.value.paused) {
          // Track watch duration only when video is playing
          watchDuration.value++
        }
        
        // Countdown timer
        if (adTimer.value > 0) {
          adTimer.value--
        }
      }, 1000)
    }

    const onVideoPlay = () => {
      isVideoPlaying.value = true
      if (!watchStartTime.value) {
        watchStartTime.value = Date.now()
      }
    }

    const onVideoPause = () => {
      isVideoPlaying.value = false
    }

    const onVideoTimeUpdate = () => {
      if (videoPlayer.value) {
        const currentTime = videoPlayer.value.currentTime
        const duration = videoPlayer.value.duration
        
        // Update watch duration based on video current time
        watchDuration.value = Math.floor(currentTime)
        
        // Check if video is near completion (90% watched)
        if (duration > 0 && currentTime >= duration * 0.9) {
          // Video is 90% complete, allow completion
          if (adTimer.value <= 10) { // Last 10 seconds
            adTimer.value = 0
            onVideoEnded()
          }
        }
      }
    }

    const onVideoEnded = () => {
      if (videoPlayer.value) {
        watchDuration.value = Math.floor(videoPlayer.value.duration)
      }
      videoCompleted.value = true
      if (timerInterval.value) {
        clearInterval(timerInterval.value)
        timerInterval.value = null
      }
      // Complete the ad
      completeAd(currentAd.value)
    }

    const completeAd = async (ad) => {
      if (!ad || !ad.id) return
      try {
        const response = await api.post('/ads/complete', { 
          ad_id: ad.id,
          watch_duration: watchDuration.value || totalDuration.value,
          ad_url: ad.video_url || ad.image
        })
        if (response.data.status === 'success') {
          if (window.notify) {
            window.notify('success', `You earned ${currencySymbol.value}${formatAmount(ad.earning)}!`)
          }
          // Close modal after 2 seconds
          setTimeout(() => {
            closeAdModal()
            fetchAds()
          }, 2000)
        }
      } catch (error) {
        console.error('Error completing ad:', error)
        videoCompleted.value = false
        if (window.notify) {
          const errorMsg = error.response?.data?.message?.error?.[0] || error.response?.data?.message || 'Failed to complete ad'
          window.notify('error', errorMsg)
        }
      }
    }

    const closeAdModal = () => {
      if (timerInterval.value) {
        clearInterval(timerInterval.value)
        timerInterval.value = null
      }
      if (videoPlayer.value) {
        videoPlayer.value.pause()
        videoPlayer.value.currentTime = 0
      }
      showAdModal.value = false
      currentAd.value = null
      adTimer.value = 0
      totalDuration.value = 0
      videoCompleted.value = false
      isVideoPlaying.value = false
      watchStartTime.value = null
      watchDuration.value = 0
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
      totalDuration,
      currencySymbol,
      videoPlayer,
      isVideoPlaying,
      videoCompleted,
      formatAmount,
      formatTime,
      watchAd,
      closeAdModal,
      onVideoPlay,
      onVideoPause,
      onVideoTimeUpdate,
      onVideoEnded
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

.ad-video-container {
  position: relative;
  background: #000;
  border-radius: 8px;
  overflow: hidden;
}

.ad-video-container video {
  display: block;
  width: 100%;
  max-height: 500px;
}

.video-overlay {
  cursor: pointer;
  transition: opacity 0.3s;
}

.video-overlay:hover {
  opacity: 0.8;
}

.progress {
  background-color: #e2e8f0;
  border-radius: 15px;
  overflow: hidden;
}
</style>
