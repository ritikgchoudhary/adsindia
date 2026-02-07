<template>
  <DashboardLayout page-title="Ads Work">
    <!-- Header Banner -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="card custom--card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 15px !important;">
          <div class="card-body p-4 text-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
              <div>
                <h3 class="mb-2" style="font-weight: 600;">
                  <i class="fas fa-video me-2"></i>Watch Ads & Earn Money
                </h3>
                <p class="mb-0 opacity-90">Complete video ads step by step to unlock more levels and earn rewards</p>
              </div>
              <div class="mt-3 mt-md-0">
                <div class="badge bg-white text-primary px-4 py-2" style="font-size: 16px; border-radius: 10px;">
                  <i class="fas fa-coins me-2"></i>Earn ₹5,000 - ₹6,000 per ad
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="row">
      <div class="col-12 text-center py-5">
        <div class="mb-4">
          <div class="spinner-border text-primary mb-3" role="status" style="width: 4rem; height: 4rem; border-width: 4px;">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
        <h5 class="mb-2" style="color: #2d3748; font-weight: 600;">Loading Ads...</h5>
        <p class="text-muted">Please wait while we prepare your ads</p>
      </div>
    </div>

    <!-- Current Ad (Step by Step) -->
    <div v-else-if="!loading && currentAd" class="row justify-content-center">
      <div class="col-lg-8 col-md-10">
        <!-- Progress Indicator -->
        <div class="card custom--card border-0 shadow-sm mb-4" style="border-radius: 15px; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);">
          <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
              <div>
                <h5 class="mb-1" style="color: #2d3748; font-weight: 700; font-size: 20px;">
                  <i class="fas fa-layer-group me-2" style="color: #667eea;"></i>Ad Level {{ currentAdIndex + 1 }} of {{ allAds.length }}
                </h5>
                <p class="mb-0 text-muted" style="font-size: 14px;">
                  <i class="fas fa-lock-open me-1"></i>Complete this ad to unlock the next level
                </p>
              </div>
              <div class="text-end mt-2 mt-md-0">
                <div class="badge" style="font-size: 18px; padding: 12px 24px; border-radius: 12px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-weight: 600;">
                  <i class="fas fa-trophy me-2"></i>Level {{ currentAdIndex + 1 }}
                </div>
              </div>
            </div>
            <div class="progress mb-3" style="height: 12px; border-radius: 15px; background: rgba(255,255,255,0.5);">
              <div class="progress-bar progress-bar-striped progress-bar-animated" 
                   role="progressbar" 
                   style="background: linear-gradient(90deg, #667eea 0%, #764ba2 100%); border-radius: 15px;"
                   :style="`width: ${((currentAdIndex + 1) / allAds.length) * 100}%`">
              </div>
            </div>
            <div class="d-flex justify-content-between align-items-center">
              <div class="d-flex align-items-center">
                <i class="fas fa-video me-2" style="color: #667eea;"></i>
                <small class="text-muted" style="font-weight: 500;">{{ currentAdIndex + 1 }} / {{ allAds.length }} Ads</small>
              </div>
              <div class="d-flex align-items-center">
                <i class="fas fa-check-circle me-2 text-success"></i>
                <small class="text-muted" style="font-weight: 500;">{{ watchedAds.length }} Completed</small>
              </div>
            </div>
          </div>
        </div>

        <!-- Current Ad Card -->
        <div class="card custom--card border-0 shadow-lg" style="border-radius: 20px; transition: all 0.3s ease; background: #fff; overflow: hidden;" @mouseenter="$event.currentTarget.style.transform = 'translateY(-8px)'; $event.currentTarget.style.boxShadow = '0 20px 40px rgba(0,0,0,0.15)'" @mouseleave="$event.currentTarget.style.transform = 'translateY(0)'; $event.currentTarget.style.boxShadow = '0 10px 30px rgba(0,0,0,0.1)'">
          <!-- Level Badge on Card -->
          <div class="position-absolute top-0 end-0 m-3" style="z-index: 10;">
            <div class="badge" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 8px 16px; border-radius: 20px; font-weight: 600; font-size: 14px;">
              Level {{ currentAdIndex + 1 }}
            </div>
          </div>
          
          <div class="card-body p-0">
            <div class="ad-item text-center">
              <!-- Video Thumbnail with Play Button -->
              <div class="ad-thumb position-relative mb-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px;">
                <div class="position-relative" style="border-radius: 15px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
                  <img :src="currentAd.image || '/assets/images/default-ad.jpg'" :alt="currentAd.title" class="img-fluid" style="width: 100%; height: 350px; object-fit: cover; display: block;">
                  <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" 
                       style="background: rgba(0,0,0,0.4); cursor: pointer; transition: all 0.3s;" 
                       @click="watchAd(currentAd)"
                       @mouseenter="$event.currentTarget.style.background = 'rgba(0,0,0,0.6)'"
                       @mouseleave="$event.currentTarget.style.background = 'rgba(0,0,0,0.4)'">
                    <div class="text-center text-white">
                      <div class="mb-3" style="background: rgba(255,255,255,0.2); border-radius: 50%; width: 100px; height: 100px; display: flex; align-items: center; justify-content: center; margin: 0 auto; transition: all 0.3s;" 
                           @mouseenter="$event.currentTarget.style.transform = 'scale(1.1)'; $event.currentTarget.style.background = 'rgba(255,255,255,0.3)'"
                           @mouseleave="$event.currentTarget.style.transform = 'scale(1)'; $event.currentTarget.style.background = 'rgba(255,255,255,0.2)'">
                        <i class="fas fa-play" style="font-size: 40px; margin-left: 5px;"></i>
                      </div>
                      <p class="mb-0" style="font-size: 16px; font-weight: 600;">Click to Watch</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Ad Details -->
              <div class="p-4">
                <h3 class="ad-title mb-2" style="color: #2d3748; font-weight: 700; font-size: 24px;">{{ currentAd.title }}</h3>
                <p class="text-muted mb-4" style="font-size: 15px; line-height: 1.6;">{{ currentAd.description }}</p>
                
                <!-- Info Cards -->
                <div class="row g-3 mb-4">
                  <div class="col-md-4">
                    <div class="p-3 border-0 shadow-sm rounded" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px !important; transition: all 0.3s;" @mouseenter="$event.currentTarget.style.transform = 'translateY(-3px)'" @mouseleave="$event.currentTarget.style.transform = 'translateY(0)'">
                      <div class="d-flex flex-column align-items-center text-white">
                        <i class="fas fa-clock fa-2x mb-2" style="opacity: 0.9;"></i>
                        <span class="small mb-1" style="opacity: 0.9;">Duration</span>
                        <strong style="font-size: 18px;">{{ currentAd.duration || 30 }} min</strong>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="p-3 border-0 shadow-sm rounded" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 12px !important; transition: all 0.3s;" @mouseenter="$event.currentTarget.style.transform = 'translateY(-3px)'" @mouseleave="$event.currentTarget.style.transform = 'translateY(0)'">
                      <div class="d-flex flex-column align-items-center text-white">
                        <i class="fas fa-coins fa-2x mb-2" style="opacity: 0.9;"></i>
                        <span class="small mb-1" style="opacity: 0.9;">Earning</span>
                        <strong style="font-size: 18px;">{{ currencySymbol }}{{ formatAmount(currentAd.earning) }}</strong>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="p-3 border-0 shadow-sm rounded" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); border-radius: 12px !important; transition: all 0.3s;" @mouseenter="$event.currentTarget.style.transform = 'translateY(-3px)'" @mouseleave="$event.currentTarget.style.transform = 'translateY(0)'">
                      <div class="d-flex flex-column align-items-center text-white">
                        <i class="fas fa-trophy fa-2x mb-2" style="opacity: 0.9;"></i>
                        <span class="small mb-1" style="opacity: 0.9;">Progress</span>
                        <strong style="font-size: 18px;">{{ currentAdIndex + 1 }}/{{ allAds.length }}</strong>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Status Alert -->
                <div v-if="watchedAds.includes(currentAd.id)" class="alert alert-success mb-4 border-0 shadow-sm" style="border-radius: 12px; background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; border-left: 4px solid #28a745;">
                  <i class="fas fa-check-circle me-2"></i><strong>Ad Completed!</strong> Earning added to your account.
                </div>
                
                <!-- Watch Button -->
                <button v-else-if="!currentAd.is_active" class="btn w-100 btn-lg" disabled style="border-radius: 12px; font-weight: 600; padding: 15px; background: #e2e8f0; color: #718096;">
                  <i class="fas fa-lock me-2"></i>Ad Not Available
                </button>
                <button v-else class="btn w-100 btn-lg text-white" @click="watchAd(currentAd)" style="border-radius: 12px; font-weight: 600; padding: 15px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; transition: all 0.3s; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);" @mouseenter="$event.currentTarget.style.transform = 'translateY(-2px)'; $event.currentTarget.style.boxShadow = '0 6px 20px rgba(102, 126, 234, 0.6)'" @mouseleave="$event.currentTarget.style.transform = 'translateY(0)'; $event.currentTarget.style.boxShadow = '0 4px 15px rgba(102, 126, 234, 0.4)'">
                  <i class="fas fa-play-circle me-2" style="font-size: 20px;"></i>Watch Ad Now - Level {{ currentAdIndex + 1 }}
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Next Ad Preview (if available) -->
        <div v-if="currentAdIndex < allAds.length - 1" class="card custom--card border-0 shadow-sm mt-4" style="border-radius: 15px; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); border: 2px dashed #cbd5e0;">
          <div class="card-body p-4 text-center">
            <div class="mb-3">
              <div class="d-inline-block p-3 rounded-circle" style="background: rgba(102, 126, 234, 0.1);">
                <i class="fas fa-lock fa-3x" style="color: #667eea;"></i>
              </div>
            </div>
            <h6 class="mb-2" style="color: #2d3748; font-weight: 600;">
              <i class="fas fa-arrow-down me-2 text-primary"></i>Next Level Locked
            </h6>
            <p class="mb-0 text-muted" style="font-size: 14px;">
              Complete <strong>Level {{ currentAdIndex + 1 }}</strong> to unlock <strong>"{{ allAds[currentAdIndex + 1]?.title }}"</strong>
            </p>
            <div class="mt-3">
              <span class="badge bg-primary" style="padding: 6px 12px; border-radius: 8px;">
                Level {{ currentAdIndex + 2 }} Coming Soon
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="!loading && !currentAd && allAds.length === 0" class="row">
      <div class="col-12">
        <div class="card custom--card border-0 shadow-sm" style="border-radius: 15px;">
          <div class="card-body text-center p-5">
            <div class="mb-4">
              <i class="fas fa-video fa-4x text-muted mb-3" style="opacity: 0.5;"></i>
            </div>
            <h4 class="mb-3" style="color: #2d3748; font-weight: 600;">
              <span v-if="!hasActivePackage">No Active Ad Plan</span>
              <span v-else>No Ads Available</span>
            </h4>
            <p class="text-muted mb-4" style="font-size: 16px;">
              <span v-if="!hasActivePackage">
                You need to purchase an Ad Plan to watch ads and earn money.
              </span>
              <span v-else>
                All ads have been watched today. Come back tomorrow for more ads!
              </span>
            </p>
            <div v-if="!hasActivePackage" class="alert alert-info" style="border-radius: 10px; background: #e0f2fe; border-color: #0ea5e9; color: #0c4a6e;">
              <i class="fas fa-info-circle me-2"></i>
              <strong>How it works:</strong> Purchase an Ad Plan, then watch video ads to earn ₹5,000 - ₹6,000 per ad!
            </div>
            <router-link v-if="!hasActivePackage" to="/user/ad-plans" class="btn btn--base btn-lg mt-4 px-5" style="border-radius: 10px; font-weight: 600;">
              <i class="fas fa-shopping-cart me-2"></i>Purchase Ad Plan
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
            <h5 class="modal-title">{{ currentAd && currentAd.title ? currentAd.title : 'Watch Ad' }}</h5>
            <button type="button" class="btn-close" @click="closeAdModal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div v-if="currentAd && adTimer > 0 && !videoCompleted" class="text-center">
              <div class="mb-4">
                <div class="progress" style="height: 30px; border-radius: 15px;">
                  <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                       role="progressbar" 
                       :style="`width: ${totalDuration > 0 ? ((totalDuration - adTimer) / totalDuration) * 100 : 0}%`">
                    {{ formatTime(adTimer) }} remaining
                  </div>
                </div>
                <p class="mt-2 mb-0" style="color: #4a5568;">
                  <i class="fas fa-info-circle me-2"></i>
                  Watch the complete video to earn {{ currencySymbol }}{{ formatAmount(currentAd.earning || 0) }}
                </p>
              </div>
              <!-- Video Player -->
              <div class="ad-video-container mb-4" style="position: relative; border-radius: 15px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
                <video 
                  v-if="currentAd && currentAd.video_url"
                  ref="videoPlayer"
                  :src="currentAd.video_url" 
                  controls 
                  autoplay
                  class="img-fluid"
                  style="width: 100%; max-height: 500px; background: #000; display: block;"
                  @timeupdate="onVideoTimeUpdate"
                  @ended="onVideoEnded"
                  @play="onVideoPlay"
                  @pause="onVideoPause"
                >
                  Your browser does not support the video tag.
                </video>
                <div v-else class="d-flex align-items-center justify-content-center" style="height: 400px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 15px;">
                  <div class="text-center text-white">
                    <div class="spinner-border mb-3" role="status"></div>
                    <p class="mb-0">Loading video...</p>
                  </div>
                </div>
                <div v-if="!isVideoPlaying && currentAd && currentAd.video_url" class="video-overlay d-flex align-items-center justify-content-center" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); border-radius: 15px; cursor: pointer; transition: all 0.3s;" @click="videoPlayer?.play()">
                  <div class="text-white text-center">
                    <div class="mb-3" style="background: rgba(255,255,255,0.2); border-radius: 50%; width: 100px; height: 100px; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                      <i class="fas fa-play" style="font-size: 40px; margin-left: 5px;"></i>
                    </div>
                    <p class="mb-0" style="font-size: 16px; font-weight: 600;">Click to start watching</p>
                  </div>
                </div>
              </div>
              
              <!-- Warning Alert -->
              <div class="alert alert-warning border-0 shadow-sm" style="border-radius: 12px; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-left: 4px solid #f59e0b;">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Important:</strong> Please watch the complete video (90%+) to earn. Do not close this window or skip the video.
              </div>
            </div>
            <div v-else-if="currentAd && videoCompleted" class="text-center py-4">
              <div class="mb-4">
                <div class="d-inline-block p-4 rounded-circle mb-3" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                  <i class="fas fa-check-circle fa-4x text-white"></i>
                </div>
              </div>
              <h3 class="mb-3" style="color: #2d3748; font-weight: 700;">Ad Watched Successfully!</h3>
              <div class="alert alert-success border-0 shadow-sm mb-4" style="border-radius: 15px; background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; border-left: 4px solid #059669;">
                <h5 class="mb-2">
                  <i class="fas fa-coins me-2"></i>
                  You Earned {{ currencySymbol }}{{ formatAmount(currentAd.earning || 0) }}
                </h5>
                <p class="mb-0">Earning has been added to your account balance.</p>
              </div>
              <div v-if="currentAdIndex < allAds.length - 1" class="alert alert-info border-0 mb-4" style="border-radius: 12px; background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);">
                <i class="fas fa-unlock me-2"></i>
                <strong>Level {{ currentAdIndex + 2 }} Unlocked!</strong> Watch the next ad to earn more.
              </div>
              <button class="btn btn-lg px-5 text-white" @click="closeAdModal" style="border-radius: 12px; font-weight: 600; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; padding: 12px 30px;">
                <i class="fas fa-check me-2"></i>Continue
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
    const allAds = ref([]) // Store all ads
    const currentAdIndex = ref(0) // Current ad index (level)
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
    const loading = ref(true)
    const hasActivePackage = ref(true)
    const watchedAds = ref([]) // Track watched ads

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
          // Mark ad as watched
          if (!watchedAds.value.includes(ad.id)) {
            watchedAds.value.push(ad.id)
          }
          
          if (window.notify) {
            window.notify('success', `Level ${currentAdIndex.value + 1} completed! You earned ${currencySymbol.value}${formatAmount(ad.earning)}!`)
          }
          
          // Close modal after 2 seconds and show next ad
          setTimeout(() => {
            closeAdModal()
            
            // Show next ad if available
            if (currentAdIndex.value < allAds.value.length - 1) {
              showNextAd()
              if (window.notify) {
                window.notify('info', `Level ${currentAdIndex.value + 1} unlocked! Watch the next ad to earn more.`)
              }
            } else {
              // All ads completed
              fetchAds() // Refresh to check if more ads available
            }
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
      loading.value = true
      try {
        const response = await api.get('/ads/work')
        console.log('Ads API Response:', response.data)
        
        if (response.data.status === 'success') {
          hasActivePackage.value = true
          // Response structure: { status: 'success', data: { data: [...], currency_symbol: '₹' } }
          const responseData = response.data.data || {}
          const adsList = responseData.data || []
          
          allAds.value = Array.isArray(adsList) ? adsList : []
          currencySymbol.value = responseData.currency_symbol || response.data.currency_symbol || '₹'
          
          console.log('Loaded ads:', allAds.value)
          console.log('Currency symbol:', currencySymbol.value)
          
          // Initialize: Show first ad only
          if (allAds.value.length > 0) {
            currentAdIndex.value = 0
            currentAd.value = allAds.value[0]
            ads.value = [allAds.value[0]] // Show only current ad
          } else {
            currentAd.value = null
            ads.value = []
            console.warn('No ads found in response')
          }
        } else {
          console.error('API returned error:', response.data)
          hasActivePackage.value = false
          allAds.value = []
          currentAd.value = null
          ads.value = []
        }
      } catch (error) {
        console.error('Error loading ads:', error)
        console.error('Error response:', error.response?.data)
        
        // Check if no active package
        if (error.response?.data?.remark === 'no_active_package') {
          hasActivePackage.value = false
          allAds.value = []
          currentAd.value = null
          ads.value = []
        } else {
          hasActivePackage.value = true
          const errorMsg = error.response?.data?.message?.error?.[0] || error.response?.data?.message || 'Failed to load ads. Please try again.'
          if (window.notify) {
            window.notify('error', errorMsg)
          }
        }
      } finally {
        loading.value = false
      }
    }

    const showNextAd = () => {
      if (currentAdIndex.value < allAds.value.length - 1) {
        currentAdIndex.value++
        currentAd.value = allAds.value[currentAdIndex.value]
        ads.value = [currentAd.value]
        console.log('Showing next ad:', currentAd.value)
      } else {
        // All ads completed
        currentAd.value = null
        console.log('All ads completed!')
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
      onVideoEnded,
      loading,
      hasActivePackage,
      currentAdIndex,
      allAds,
      watchedAds,
      showNextAd
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
