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

    <!-- Step-by-Step Ads -->
    <div v-else-if="!loading && allAds.length > 0" class="row">
      <!-- Active Plan Info -->
      <div v-if="activePackage" class="col-12 mb-4">
        <div class="card custom--card border-0 shadow-sm" style="border-radius: 15px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
          <div class="card-body p-4 text-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
              <div>
                <h5 class="mb-2" style="font-weight: 700; font-size: 22px;">
                  <i class="fas fa-box me-2"></i>Your Active Plan: {{ activePackage.name }}
                </h5>
                <div class="d-flex flex-wrap gap-3 mt-2">
                  <div class="d-flex align-items-center">
                    <i class="fas fa-calendar-day me-2"></i>
                    <span style="font-size: 14px;">Daily Limit: <strong>{{ activePackage.daily_limit }}</strong> ads</span>
                  </div>
                  <div class="d-flex align-items-center">
                    <i class="fas fa-eye me-2"></i>
                    <span style="font-size: 14px;">Today Watched: <strong>{{ activePackage.today_views || 0 }}</strong></span>
                  </div>
                  <div class="d-flex align-items-center">
                    <i class="fas fa-clock me-2"></i>
                    <span style="font-size: 14px;">Remaining: <strong>{{ activePackage.remaining_ads || 0 }}</strong> ads</span>
                  </div>
                </div>
              </div>
              <div class="mt-3 mt-md-0 text-end">
                <div class="badge bg-white text-primary px-4 py-2" style="font-size: 16px; border-radius: 12px; font-weight: 600;">
                  <i class="fas fa-check-circle me-2"></i>Plan Active
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Progress Summary -->
      <div class="col-12 mb-4">
        <div class="card custom--card border-0 shadow-sm" style="border-radius: 15px; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);">
          <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
              <div>
                <h5 class="mb-1" style="color: #2d3748; font-weight: 700; font-size: 20px;">
                  <i class="fas fa-layer-group me-2" style="color: #667eea;"></i>Step-by-Step Ads
                </h5>
                <p class="mb-0 text-muted" style="font-size: 14px;">
                  Complete each ad to unlock the next one
                </p>
              </div>
              <div class="d-flex align-items-center gap-3 mt-2 mt-md-0">
                <div class="text-center">
                  <div class="badge" style="font-size: 16px; padding: 10px 20px; border-radius: 12px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-weight: 600;">
                    <i class="fas fa-video me-2"></i>{{ allAds.length }} Ads
                  </div>
                </div>
                <div class="text-center">
                  <div class="badge bg-success" style="font-size: 16px; padding: 10px 20px; border-radius: 12px; font-weight: 600;">
                    <i class="fas fa-check-circle me-2"></i>{{ watchedAds.length }} Completed
                  </div>
                </div>
              </div>
            </div>
            <div class="progress mt-3" style="height: 12px; border-radius: 15px; background: rgba(255,255,255,0.5);">
              <div class="progress-bar progress-bar-striped progress-bar-animated" 
                   role="progressbar" 
                   style="background: linear-gradient(90deg, #667eea 0%, #764ba2 100%); border-radius: 15px;"
                   :style="`width: ${allAds.length > 0 ? (watchedAds.length / allAds.length) * 100 : 0}%`">
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Step-by-Step Ads List -->
      <div class="col-12">
        <div class="row g-4">
          <template v-for="(ad, index) in allAds" :key="ad?.id || Math.random()">
            <div v-if="ad && ad.id" class="col-12">
              <div class="card custom--card border-0 shadow-lg" 
                   :class="{ 'opacity-50': index > currentUnlockedIndex }"
                   style="border-radius: 20px; transition: all 0.3s ease; background: #fff; overflow: hidden; position: relative;"
                   :style="index <= currentUnlockedIndex ? 'cursor: pointer;' : 'cursor: not-allowed;'"
                   @click="index <= currentUnlockedIndex && !watchedAds.includes(ad.id) ? watchAd(ad) : null"
                   @mouseenter="index <= currentUnlockedIndex ? $event.currentTarget.style.transform = 'translateY(-5px)' : null" 
                   @mouseleave="index <= currentUnlockedIndex ? $event.currentTarget.style.transform = 'translateY(0)' : null">
                
                <!-- Lock Overlay for locked ads -->
                <div v-if="index > currentUnlockedIndex" class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="background: rgba(0,0,0,0.7); z-index: 100; border-radius: 20px;">
                  <div class="text-center text-white">
                    <i class="fas fa-lock fa-4x mb-3"></i>
                    <h5 class="mb-2">Ad Locked</h5>
                    <p class="mb-0">Complete previous ads to unlock</p>
                  </div>
                </div>

                <!-- Step Number Badge -->
                <div class="position-absolute top-0 start-0 m-3" style="z-index: 10;">
                  <div class="badge" 
                       :style="index <= currentUnlockedIndex ? 'background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);' : 'background: #718096;'"
                       style="color: white; padding: 10px 20px; border-radius: 20px; font-weight: 600; font-size: 16px;">
                    <i v-if="index < currentUnlockedIndex" class="fas fa-check-circle me-2"></i>
                    <i v-else-if="index === currentUnlockedIndex" class="fas fa-play-circle me-2"></i>
                    <i v-else class="fas fa-lock me-2"></i>
                    Step {{ index + 1 }} {{ index === currentUnlockedIndex ? '(Current)' : index < currentUnlockedIndex ? '(Completed)' : '(Locked)' }}
                  </div>
                </div>

                <!-- Watched Badge -->
                <div v-if="watchedAds.includes(ad.id)" class="position-absolute top-0 end-0 m-3" style="z-index: 10;">
                  <div class="badge bg-success" style="padding: 10px 20px; border-radius: 20px; font-weight: 600; font-size: 14px;">
                    <i class="fas fa-check-circle me-1"></i>Completed
                  </div>
                </div>

                <!-- Video Thumbnail -->
                <div class="position-relative" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px;">
                  <div class="position-relative" style="border-radius: 15px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.3); aspect-ratio: 16/9;">
                    <img :src="ad.image || '/assets/images/default-ad.jpg'" :alt="ad.title" class="img-fluid" style="width: 100%; height: 100%; object-fit: cover; display: block; filter: brightness(index > currentUnlockedIndex ? 0.5 : 1);">
                    <div v-if="index <= currentUnlockedIndex && !watchedAds.includes(ad.id)" class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" 
                         style="background: rgba(0,0,0,0.4); transition: all 0.3s; cursor: pointer;" 
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
                <div class="card-body p-4">
                  <h4 class="ad-title mb-2" style="color: #2d3748; font-weight: 700; font-size: 22px;">{{ ad.title }}</h4>
                  <p class="text-muted mb-4" style="font-size: 15px; line-height: 1.6;">{{ ad.description }}</p>
                  
                  <!-- Info Cards -->
                  <div class="row g-3 mb-4">
                    <div class="col-md-4">
                      <div class="p-3 border-0 shadow-sm rounded text-center" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px !important;">
                        <div class="text-white">
                          <i class="fas fa-clock fa-2x mb-2"></i>
                          <div style="font-size: 14px; font-weight: 600;">{{ ad.duration || 30 }} minutes</div>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="p-3 border-0 shadow-sm rounded text-center" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 12px !important;">
                        <div class="text-white">
                          <i class="fas fa-coins fa-2x mb-2"></i>
                          <div style="font-size: 14px; font-weight: 600;">{{ currencySymbol }}{{ formatAmount(ad.earning) }}</div>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="p-3 border-0 shadow-sm rounded text-center" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); border-radius: 12px !important;">
                        <div class="text-white">
                          <i class="fas fa-list-ol fa-2x mb-2"></i>
                          <div style="font-size: 14px; font-weight: 600;">Step {{ index + 1 }}/{{ allAds.length }}</div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Status -->
                  <div v-if="watchedAds.includes(ad.id)" class="alert alert-success mb-0 border-0" style="border-radius: 12px; background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; padding: 15px;">
                    <i class="fas fa-check-circle me-2"></i><strong>Completed!</strong> Earning added to your account.
                  </div>
                  <button v-else-if="index > currentUnlockedIndex" class="btn w-100 btn-lg" disabled style="border-radius: 12px; font-weight: 600; padding: 15px; background: #e2e8f0; color: #718096;">
                    <i class="fas fa-lock me-2"></i>Locked - Complete Previous Ads First
                  </button>
                  <button v-else-if="!ad.is_active" class="btn w-100 btn-lg" disabled style="border-radius: 12px; font-weight: 600; padding: 15px; background: #e2e8f0; color: #718096;">
                    <i class="fas fa-lock me-2"></i>Not Available
                  </button>
                  <button v-else class="btn w-100 btn-lg text-white" style="border-radius: 12px; font-weight: 600; padding: 15px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; transition: all 0.3s; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);" @mouseenter="$event.currentTarget.style.transform = 'translateY(-2px)'; $event.currentTarget.style.boxShadow = '0 6px 20px rgba(102, 126, 234, 0.6)'" @mouseleave="$event.currentTarget.style.transform = 'translateY(0)'; $event.currentTarget.style.boxShadow = '0 4px 15px rgba(102, 126, 234, 0.4)'">
                    <i class="fas fa-play-circle me-2" style="font-size: 20px;"></i>Watch Ad Now - Step {{ index + 1 }}
                  </button>
                </div>
              </div>
            </div>
          </template>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="!loading && allAds.length === 0" class="row">
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
                  autoplay
                  class="img-fluid"
                  style="width: 100%; max-height: 500px; background: #000; display: block;"
                  @timeupdate="onVideoTimeUpdate"
                  @ended="onVideoEnded"
                  @play="onVideoPlay"
                  @pause="onVideoPause"
                  @seeked="onVideoSeeked"
                  @seeking="onVideoSeeking"
                  @loadedmetadata="onVideoLoaded"
                >
                  Your browser does not support the video tag.
                </video>
                <div v-else class="d-flex align-items-center justify-content-center" style="height: 400px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 15px;">
                  <div class="text-center text-white">
                    <div class="spinner-border mb-3" role="status"></div>
                    <p class="mb-0">Loading video...</p>
                  </div>
                </div>
                
                <!-- Custom Controls Overlay -->
                <div v-if="currentAd && currentAd.video_url" class="position-absolute bottom-0 start-0 w-100 p-3" style="background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.4) 50%, transparent 100%); z-index: 10;">
                  <div class="d-flex justify-content-center align-items-center gap-3">
                    <!-- Play/Pause Button -->
                    <button 
                      class="btn btn-light btn-lg rounded-circle" 
                      style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; border: none; box-shadow: 0 4px 10px rgba(0,0,0,0.3);"
                      @click="togglePlayPause"
                    >
                      <i v-if="isVideoPlaying" class="fas fa-pause"></i>
                      <i v-else class="fas fa-play" style="margin-left: 2px;"></i>
                    </button>
                  </div>
                </div>
                
                <!-- Play Overlay (when paused) -->
                <div v-if="!isVideoPlaying && currentAd && currentAd.video_url" class="video-overlay d-flex align-items-center justify-content-center" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); border-radius: 15px; cursor: pointer; transition: all 0.3s; z-index: 5;" @click="togglePlayPause">
                  <div class="text-white text-center">
                    <div class="mb-3" style="background: rgba(255,255,255,0.2); border-radius: 50%; width: 100px; height: 100px; display: flex; align-items: center; justify-content: center; margin: 0 auto; transition: all 0.3s;" 
                         @mouseenter="$event.currentTarget.style.transform = 'scale(1.1)'; $event.currentTarget.style.background = 'rgba(255,255,255,0.3)'"
                         @mouseleave="$event.currentTarget.style.transform = 'scale(1)'; $event.currentTarget.style.background = 'rgba(255,255,255,0.2)'">
                      <i class="fas fa-play" style="font-size: 40px; margin-left: 5px;"></i>
                    </div>
                    <p class="mb-0" style="font-size: 16px; font-weight: 600;">Click to play</p>
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
    const activePackage = ref(null) // Store active package info
    const currentUnlockedIndex = ref(0) // Track which ad is currently unlocked

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
        
        // Prevent seeking forward - if user tries to skip, reset to last valid position
        if (watchDuration.value > 0 && currentTime > watchDuration.value + 2) {
          // User tried to skip forward, reset to last valid position
          videoPlayer.value.currentTime = watchDuration.value
          if (window.notify) {
            window.notify('warning', 'Skipping forward is not allowed. Please watch the complete video.')
          }
          return
        }
        
        // Update watch duration based on video current time (only if playing forward)
        if (isVideoPlaying.value && currentTime >= watchDuration.value) {
          watchDuration.value = Math.floor(currentTime)
        }
        
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

    const onVideoSeeked = (event) => {
      // Prevent seeking - reset to last valid position
      if (videoPlayer.value && watchDuration.value > 0) {
        const currentTime = videoPlayer.value.currentTime
        if (currentTime > watchDuration.value + 1) {
          videoPlayer.value.currentTime = watchDuration.value
          if (window.notify) {
            window.notify('warning', 'Skipping is not allowed. Please watch the complete video.')
          }
        }
      }
    }

    const onVideoSeeking = (event) => {
      // Prevent seeking while user is dragging
      if (videoPlayer.value && watchDuration.value > 0) {
        const currentTime = videoPlayer.value.currentTime
        if (currentTime > watchDuration.value + 1) {
          videoPlayer.value.currentTime = watchDuration.value
        }
      }
    }

    const onVideoLoaded = () => {
      // Disable right-click context menu and keyboard shortcuts
      if (videoPlayer.value) {
        videoPlayer.value.addEventListener('contextmenu', (e) => e.preventDefault())
        // Prevent keyboard shortcuts for seeking
        videoPlayer.value.addEventListener('keydown', (e) => {
          // Prevent arrow keys, space (except for play/pause), etc.
          if (['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown'].includes(e.key)) {
            e.preventDefault()
          }
        })
      }
    }

    const togglePlayPause = () => {
      if (videoPlayer.value) {
        if (videoPlayer.value.paused) {
          videoPlayer.value.play()
        } else {
          videoPlayer.value.pause()
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
          
          // Close modal after 2 seconds and unlock next ad
          setTimeout(() => {
            closeAdModal()
            
            // Unlock next ad if available
            const currentIndex = allAds.value.findIndex(a => a.id === ad.id)
            if (currentIndex !== -1 && currentIndex < allAds.value.length - 1) {
              // Unlock next ad
              currentUnlockedIndex.value = currentIndex + 1
              if (window.notify) {
                window.notify('success', `Next ad unlocked! You can now watch ad ${currentIndex + 2}.`)
              }
            } else {
              // All ads completed
              if (window.notify) {
                window.notify('success', 'Congratulations! You have completed all ads for today.')
              }
            }
            
            // Refresh ads to update watched status
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
      loading.value = true
      try {
        const response = await api.get('/ads/work')
        console.log('Ads API Response:', response.data)
        
        if (response.data.status === 'success') {
          hasActivePackage.value = true
          // Response structure: { status: 'success', data: { data: [...], currency_symbol: '₹', active_package: {...} } }
          const responseData = response.data.data || {}
          const adsList = responseData.data || []
          
          allAds.value = Array.isArray(adsList) ? adsList : []
          currencySymbol.value = responseData.currency_symbol || response.data.currency_symbol || '₹'
          activePackage.value = responseData.active_package || null
          
          console.log('Loaded ads:', allAds.value)
          console.log('Active package:', activePackage.value)
          console.log('Currency symbol:', currencySymbol.value)
          
          // Initialize: Step-by-step mode - only first ad unlocked
          if (allAds.value.length > 0) {
            ads.value = allAds.value // Store all ads
            currentUnlockedIndex.value = 0 // First ad is unlocked
            currentAd.value = null
          } else {
            currentAd.value = null
            ads.value = []
            currentUnlockedIndex.value = 0
            console.warn('No ads found in response')
          }
        } else {
          console.error('API returned error:', response.data)
          hasActivePackage.value = false
          allAds.value = []
          currentAd.value = null
          ads.value = []
          activePackage.value = null
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
          activePackage.value = null
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
      onVideoSeeked,
      onVideoSeeking,
      onVideoLoaded,
      togglePlayPause,
      loading,
      hasActivePackage,
      currentAdIndex,
      allAds,
      watchedAds,
      showNextAd,
      activePackage,
      currentUnlockedIndex
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
