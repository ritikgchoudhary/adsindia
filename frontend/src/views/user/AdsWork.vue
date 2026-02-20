<template>
  <DashboardLayout page-title="Ads Work" :dark-theme="true">
    <!-- Loading State -->
    <div v-if="loading" class="tw-flex tw-justify-center tw-py-20">
       <div class="tw-w-10 tw-h-10 tw-border-4 tw-border-indigo-500 tw-border-t-transparent tw-rounded-full tw-animate-spin"></div>
    </div>

    <!-- Active Plan & Ads -->
    <div v-else-if="!loading && allAds.length > 0" class="tw-flex tw-flex-col tw-gap-8">
      
      <!-- Active Plan Info Card -->
      <div v-if="activePackage" class="tw-bg-gradient-to-br tw-from-indigo-600 tw-to-violet-600 tw-rounded-2xl tw-shadow-xl tw-p-6 tw-text-white tw-border tw-border-white/10">
        <div class="tw-flex tw-flex-col md:tw-flex-row tw-justify-between tw-items-start md:tw-items-center tw-gap-6">
          <div class="tw-flex-1">
            <h5 class="tw-text-2xl tw-font-bold tw-mb-2 tw-flex tw-items-center">
              <i class="fas fa-box tw-mr-3"></i>
              {{ isNewUserOffer ? 'New User Offer' : 'Your Active Plan' }}: {{ activePackage.name }}
            </h5>
            <p v-if="isNewUserOffer" class="tw-text-indigo-100 tw-text-sm tw-mb-4 tw-leading-relaxed tw-max-w-2xl">
              <strong>Welcome!</strong> Watch the 2 starter ads below to earn ₹10,000.
              Each ad is <strong>30 minutes</strong> (total <strong>60 minutes</strong>). After completing both, you must buy an <strong>Ad Plan</strong> to continue earning daily.
            </p>
            <p v-else class="tw-text-indigo-100 tw-text-sm tw-mb-4 tw-leading-relaxed tw-max-w-2xl">
              Watch the ads below to earn money. Complete each ad in sequence to unlock the next one. Each ad takes 1 minute to watch.
            </p>
            
            <div class="tw-flex tw-flex-wrap tw-gap-4">
              <div class="tw-bg-white/10 tw-rounded-lg tw-px-3 tw-py-2 tw-flex tw-items-center tw-backdrop-blur-sm">
                <i class="fas fa-calendar-day tw-mr-2 tw-text-indigo-200"></i>
                <span class="tw-text-sm">{{ isNewUserOffer ? 'Ads in offer:' : 'Daily Limit:' }} <strong>{{ activePackage.daily_limit }}</strong> ads</span>
              </div>
              <div class="tw-bg-white/10 tw-rounded-lg tw-px-3 tw-py-2 tw-flex tw-items-center tw-backdrop-blur-sm">
                <i class="fas fa-eye tw-mr-2 tw-text-indigo-200"></i>
                <span class="tw-text-sm">Watched: <strong>{{ activePackage.today_views || 0 }}</strong></span>
              </div>
              <div class="tw-bg-white/10 tw-rounded-lg tw-px-3 tw-py-2 tw-flex tw-items-center tw-backdrop-blur-sm">
                <i class="fas fa-clock tw-mr-2 tw-text-indigo-200"></i>
                <span class="tw-text-sm">Remaining: <strong>{{ activePackage.remaining_ads || 0 }}</strong> ads</span>
              </div>
            </div>
          </div>
          
          <div class="tw-flex tw-flex-col tw-gap-3 tw-w-full md:tw-w-auto">
             <div v-if="!isNewUserOffer" class="tw-flex tw-flex-col tw-items-end tw-gap-3">
                <span class="tw-bg-white tw-text-indigo-600 tw-px-4 tw-py-1.5 tw-rounded-lg tw-font-bold tw-text-sm tw-inline-flex tw-items-center">
                  <i class="fas fa-check-circle tw-mr-2"></i> Plan Active
                </span>
                <router-link to="/user/ad-plans" class="tw-px-6 tw-py-3 tw-bg-gradient-to-r tw-from-pink-500 tw-to-rose-500 hover:tw-from-pink-600 hover:tw-to-rose-600 tw-text-white tw-font-bold tw-rounded-xl tw-shadow-lg tw-shadow-pink-500/30 tw-transition-all tw-flex tw-items-center tw-justify-center tw-no-underline">
                  <i class="fas fa-arrow-up tw-mr-2"></i> Upgrade Plan
                </router-link>
             </div>
             <div v-else class="tw-flex tw-flex-wrap tw-gap-3">
                <router-link to="/user/account-kyc" class="tw-px-5 tw-py-2.5 tw-bg-white/20 hover:tw-bg-white/30 tw-text-white tw-font-bold tw-rounded-xl tw-border-2 tw-border-white/50 tw-no-underline tw-transition-all tw-flex-1 tw-text-center">
                  <i class="fas fa-id-card tw-mr-2"></i> KYC
                </router-link>
                <router-link to="/user/ad-plans" class="tw-px-5 tw-py-2.5 tw-bg-gradient-to-r tw-from-pink-500 tw-to-rose-500 hover:tw-from-pink-600 hover:tw-to-rose-600 tw-text-white tw-font-bold tw-rounded-xl tw-shadow-lg tw-shadow-pink-500/30 tw-no-underline tw-transition-all tw-flex-1 tw-text-center">
                  <i class="fas fa-shopping-cart tw-mr-2"></i> Ad Plans
                </router-link>
             </div>
          </div>
        </div>
      </div>

      <!-- Progress Summary -->
      <div class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-slate-200 tw-p-6">
        <div class="tw-flex tw-flex-col md:tw-flex-row tw-justify-between tw-items-start md:tw-items-center tw-mb-4 tw-gap-4">
          <div>
            <h5 class="tw-text-xl tw-font-bold tw-text-slate-900 tw-mb-1 tw-flex tw-items-center">
              <i class="fas fa-th tw-mr-2 tw-text-indigo-500"></i> Ads Grid (5 per row)
            </h5>
            <p class="tw-text-slate-500 tw-text-sm tw-m-0">
              Complete each ad to unlock the next one. Click on any unlocked ad to watch.
            </p>
          </div>
          <div class="tw-flex tw-gap-3">
            <span class="tw-bg-indigo-100 tw-text-indigo-700 tw-px-3 tw-py-1.5 tw-rounded-lg tw-text-sm tw-font-bold">
               <i class="fas fa-video tw-mr-2"></i>{{ allAds.length }} Ads
            </span>
            <span class="tw-bg-emerald-100 tw-text-emerald-700 tw-px-3 tw-py-1.5 tw-rounded-lg tw-text-sm tw-font-bold">
               <i class="fas fa-check-circle tw-mr-2"></i>{{ watchedAds.length }} Completed
            </span>
          </div>
        </div>
        
        <!-- Progress Bar -->
        <div class="tw-w-full tw-bg-slate-100 tw-rounded-full tw-h-3 tw-overflow-hidden">
          <div 
            class="tw-bg-gradient-to-r tw-from-indigo-500 tw-to-purple-600 tw-h-3 tw-rounded-full tw-transition-all tw-duration-500"
            :style="`width: ${allAds.length > 0 ? (watchedAds.length / allAds.length) * 100 : 0}%`"
          ></div>
        </div>
      </div>

      <!-- Ads Grid -->
      <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 md:tw-grid-cols-3 lg:tw-grid-cols-4 xl:tw-grid-cols-5 tw-gap-6">
        <template v-for="(ad, index) in allAds" :key="ad?.id || Math.random()">
          <div 
            v-if="ad && ad.id"
            class="tw-relative tw-rounded-2xl tw-overflow-hidden tw-transition-all tw-aspect-video tw-shadow-sm tw-group"
            :class="index <= currentUnlockedIndex && (ad.is_active || isAdWatched(ad)) ? 'tw-cursor-pointer hover:tw-shadow-xl hover:-tw-translate-y-1' : 'tw-cursor-not-allowed tw-opacity-60'"
            @click="index <= currentUnlockedIndex && ad.is_active && !isAdWatched(ad) ? watchAd(ad) : null"
          >
            <!-- Background/Thumbnail -->
            <div class="tw-w-full tw-h-full tw-bg-slate-900">
               <img 
                 :src="ad.image || '/assets/images/default-ad.jpg'" 
                 :alt="ad.title" 
                 class="tw-w-full tw-h-full tw-object-cover"
                 :class="index > currentUnlockedIndex ? 'tw-brightness-50' : 'tw-brightness-90 group-hover:tw-brightness-100'"
                 @error="$event.target.src = '/assets/images/default-ad.jpg'"
               >
            </div>

            <!-- Lock Overlay -->
            <div v-if="index > currentUnlockedIndex" class="tw-absolute tw-inset-0 tw-flex tw-flex-col tw-items-center tw-justify-center tw-bg-black/60 tw-text-white z-10">
               <i class="fas fa-lock tw-text-3xl tw-mb-2"></i>
               <span class="tw-text-xs tw-font-bold tw-uppercase tw-tracking-widest">Locked</span>
            </div>

            <!-- Watched Overlay -->
            <div v-if="isAdWatched(ad)" class="tw-absolute tw-inset-0 tw-flex tw-items-center tw-justify-center tw-bg-emerald-500/20 tw-backdrop-blur-[1px] z-10">
               <div class="tw-bg-white/90 tw-rounded-full tw-p-3 tw-shadow-lg">
                 <i class="fas fa-check tw-text-2xl tw-text-emerald-500"></i>
               </div>
               <div class="tw-absolute tw-top-2 tw-right-2 tw-bg-emerald-500 tw-text-white tw-text-[10px] tw-font-bold tw-uppercase tw-px-2 tw-py-1 tw-rounded-md">
                 Done
               </div>
            </div>

            <!-- Play Button (Active & Unwatched) -->
            <div v-if="index <= currentUnlockedIndex && !isAdWatched(ad)" class="tw-absolute tw-inset-0 tw-flex tw-items-center tw-justify-center tw-bg-black/20 group-hover:tw-bg-black/10 tw-transition-all">
               <div class="tw-w-14 tw-h-14 tw-bg-white/20 tw-backdrop-blur-sm tw-rounded-full tw-flex tw-items-center tw-justify-center tw-group-hover:tw-scale-110 tw-transition-transform tw-border-2 tw-border-white/50">
                  <i class="fas fa-play tw-text-white tw-text-xl tw-ml-1"></i>
               </div>
            </div>

          </div>
        </template>
      </div>

    </div>

    <!-- Empty State / No Plan -->
    <div v-else-if="!loading && allAds.length === 0" class="tw-flex tw-justify-center tw-py-10">
      <div class="tw-w-full tw-max-w-2xl tw-bg-white tw-rounded-3xl tw-shadow-lg tw-p-8 tw-text-center tw-border tw-border-slate-100">
        <div class="tw-mb-6">
          <div class="tw-w-20 tw-h-20 tw-bg-slate-50 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mx-auto">
            <i class="fas fa-video-slash tw-text-4xl tw-text-slate-300"></i>
          </div>
        </div>
        <h4 class="tw-text-2xl tw-font-bold tw-text-slate-900 tw-mb-2">
          <span v-if="!hasActivePackage">No Active Ad Plan</span>
          <span v-else>No Ads Available</span>
        </h4>
        <p class="tw-text-slate-500 tw-mb-8 tw-text-base tw-leading-relaxed">
          <span v-if="!hasActivePackage">
            {{ noPackageMessage || 'You need to purchase an Ad Plan to watch ads and earn money.' }}
          </span>
          <span v-else>
            All ads have been watched today. Come back tomorrow for more ads!
          </span>
        </p>
        
        <div v-if="!hasActivePackage">
          <div class="tw-bg-blue-50 tw-border tw-border-blue-100 tw-rounded-xl tw-p-4 tw-mb-8 tw-text-blue-800 tw-text-sm">
            <i class="fas fa-info-circle tw-mr-2"></i>
            <strong>Plans:</strong> ₹2999, ₹4999, ₹7499, ₹9999 – Purchase an Ad Plan to watch ads and earn!
          </div>
          <router-link to="/user/ad-plans" class="tw-inline-flex tw-items-center tw-px-8 tw-py-4 tw-bg-gradient-to-r tw-from-indigo-600 tw-to-violet-600 hover:tw-from-indigo-700 hover:tw-to-violet-700 tw-text-white tw-font-bold tw-rounded-xl tw-shadow-lg tw-shadow-indigo-500/30 tw-transition-transform hover:-tw-translate-y-1 tw-no-underline">
            <i class="fas fa-shopping-cart tw-mr-2"></i> Purchase Ad Plan
          </router-link>
        </div>
      </div>
    </div>

    <!-- Watch Ad Modal -->
    <div v-if="showAdModal" class="tw-fixed tw-inset-0 tw-z-[100] tw-flex tw-items-center tw-justify-center tw-px-4 tw-py-4">
      <div class="tw-absolute tw-inset-0 tw-bg-black/90 tw-backdrop-blur-sm" @click="closeAdModal"></div>
      <div class="tw-bg-white tw-rounded-2xl tw-shadow-2xl tw-w-full tw-max-w-4xl tw-relative tw-z-10 tw-overflow-hidden tw-animate-fade-in-up tw-flex tw-flex-col tw-max-h-[90vh]">
        
        <!-- Header -->
        <div class="tw-p-4 tw-bg-slate-900 tw-text-white tw-flex tw-justify-between tw-items-center">
           <h5 class="tw-m-0 tw-font-bold tw-text-lg">Watching Ad</h5>
           <button @click="closeAdModal" class="tw-bg-white/10 hover:tw-bg-white/20 tw-rounded-lg tw-w-8 tw-h-8 tw-flex tw-items-center tw-justify-center tw-text-white tw-transition-colors tw-border-0">
             <i class="fas fa-times"></i>
           </button>
        </div>

        <!-- Video Area -->
        <div class="tw-bg-black tw-relative tw-flex-1 tw-flex tw-items-center tw-justify-center tw-min-h-[300px] sm:tw-min-h-[400px]">
          <div v-if="currentAd && adTimer > 0 && !videoCompleted" class="tw-w-full tw-h-full tw-relative">
             <video 
               v-if="currentAd && currentAd.video_url"
               ref="videoPlayer"
               :src="currentAd.video_url"
               @error="handleVideoError"
               @loadeddata="handleVideoLoaded" 
               autoplay
               muted
               playsinline
               webkit-playsinline="true"
               preload="auto"
               class="tw-w-full tw-h-full tw-object-contain tw-max-h-[60vh]"
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
             
             <div v-else class="tw-absolute tw-inset-0 tw-flex tw-flex-col tw-items-center tw-justify-center tw-text-white">
                <p>Video will appear here</p>
             </div>

             <!-- Controls Overlay -->
             <div v-if="currentAd && currentAd.video_url" class="tw-absolute tw-bottom-0 tw-left-0 tw-w-full tw-p-6 tw-bg-gradient-to-t tw-from-black/80 tw-to-transparent tw-flex tw-justify-between tw-items-end">
                <div class="tw-text-white tw-font-mono tw-text-xl tw-font-bold tw-bg-black/40 tw-px-4 tw-py-2 tw-rounded-lg tw-backdrop-blur-sm">
                  <i class="fas fa-clock tw-mr-2 tw-text-indigo-400"></i>{{ formatTime(adTimer) }}
                </div>
                <button 
                  class="tw-w-14 tw-h-14 tw-rounded-full tw-bg-white tw-text-indigo-600 tw-flex tw-items-center tw-justify-center tw-shadow-lg hover:tw-scale-110 tw-transition-transform tw-border-0"
                  @click="togglePlayPause"
                >
                  <i v-if="isVideoPlaying" class="fas fa-pause tw-text-xl"></i>
                  <i v-else class="fas fa-play tw-ml-1 tw-text-xl"></i>
                </button>
             </div>

             <!-- Pause Overlay -->
             <div v-if="!isVideoPlaying && currentAd && currentAd.video_url" class="tw-absolute tw-inset-0 tw-bg-black/40 tw-flex tw-items-center tw-justify-center tw-cursor-pointer" @click="togglePlayPause">
                <div class="tw-w-20 tw-h-20 tw-bg-white/20 tw-backdrop-blur-md tw-rounded-full tw-flex tw-items-center tw-justify-center tw-border-2 tw-border-white/50 hover:tw-scale-110 tw-transition-transform">
                   <i class="fas fa-play tw-text-white tw-text-3xl tw-ml-2"></i>
                </div>
             </div>
          </div>

          <!-- Success State -->
          <div v-else-if="currentAd && videoCompleted" class="tw-text-center tw-p-10 tw-bg-white tw-rounded-xl tw-max-w-md tw-mx-auto">
             <div class="tw-w-20 tw-h-20 tw-bg-emerald-100 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-6">
               <i class="fas fa-check tw-text-4xl tw-text-emerald-500"></i>
             </div>
             <h3 class="tw-text-2xl tw-font-bold tw-text-slate-900 tw-mb-2">Ad Watched!</h3>
             <p class="tw-text-slate-500 tw-mb-6">You have successfully completed this ad.</p>
             
             <div class="tw-bg-emerald-50 tw-border tw-border-emerald-100 tw-rounded-xl tw-p-4 tw-mb-6">
                <div class="tw-text-emerald-800 tw-font-bold tw-text-lg">
                   + {{ currencySymbol }}{{ formatAmount(earnedAmount || currentAd.earning || 0) }}
                </div>
                <div class="tw-text-emerald-600 tw-text-xs">Added to balance</div>
             </div>
             
             <button 
               @click="closeAdModal"
               class="tw-w-full tw-py-3 tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-font-bold tw-rounded-xl tw-shadow-lg tw-shadow-indigo-500/30 tw-transition-all tw-border-0"
             >
               Continue
             </button>
          </div>
        </div>
      </div>
    </div>

  </DashboardLayout>
</template>

<script>
import { ref, onMounted, onUnmounted, nextTick } from 'vue'
import DashboardLayout from '../../components/DashboardLayout.vue'
import api from '../../services/api'

export default {
  name: 'AdsWork',
  components: {
    DashboardLayout
  },
  setup() {
    const ads = ref([])
    const allAds = ref([]) 
    const currentAdIndex = ref(0) 
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
    const watchedAds = ref([]) 
    const activePackage = ref(null) 
    const currentUnlockedIndex = ref(0) 
    const isNewUserOffer = ref(false) 
    const noPackageMessage = ref('') 
    const earnedAmount = ref(0)

    const formatAmount = (amount) => {
      if (!amount && amount !== 0) return '0.00'
      return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    }

    const formatTime = (seconds) => {
      const mins = Math.floor(seconds / 60)
      const secs = seconds % 60
      return `${mins}:${secs.toString().padStart(2, '0')}`
    }

    const isAdWatched = (ad) => {
      if (!ad || !ad.id) return false
      const isWatchedInArray = watchedAds.value.includes(ad.id)
      const isWatchedFromBackend = ad.is_watched === true || ad.is_watched === 1 || ad.is_watched === 'true' || ad.is_watched === '1'
      return isWatchedInArray || isWatchedFromBackend
    }

    const watchAd = (ad) => {
      if (!ad || !ad.id) return
      currentAd.value = ad
      totalDuration.value = Number(ad.duration_seconds || 60)
      adTimer.value = totalDuration.value
      videoCompleted.value = false
      watchDuration.value = 0
      watchStartTime.value = Date.now()
      showAdModal.value = true
      isVideoPlaying.value = false
      earnedAmount.value = 0

      nextTick(() => {
        if (videoPlayer.value) {
          videoPlayer.value.addEventListener('loadedmetadata', () => {
            const videoDuration = videoPlayer.value.duration
            if (videoDuration > 0 && videoDuration < totalDuration.value) {
              videoPlayer.value.loop = true
            }
            // Keep required duration from plan
            adTimer.value = totalDuration.value
          })
        }
      })

      timerInterval.value = setInterval(() => {
        if (isVideoPlaying.value && videoPlayer.value && !videoPlayer.value.paused) {
          watchDuration.value++
        } else if (videoPlayer.value && !videoPlayer.value.paused) {
          watchDuration.value++
        }
        
        if (watchStartTime.value) {
          const elapsedSeconds = Math.floor((Date.now() - watchStartTime.value) / 1000)
          watchDuration.value = Math.max(watchDuration.value, elapsedSeconds)
        }
        
        if (adTimer.value > 0) {
          adTimer.value--
        }
        
        const minWatchTime = Math.floor((totalDuration.value || 60) * 0.9)
        
        if (!videoCompleted.value && adTimer.value <= 0) {
          if (watchDuration.value >= minWatchTime) {
            videoCompleted.value = true
            if (timerInterval.value) {
              clearInterval(timerInterval.value)
              timerInterval.value = null
            }
            if (currentAd.value && currentAd.value.id) {
              completeAd(currentAd.value)
            }
          } else {
             // Not enough time watched
             videoCompleted.value = true 
             if (timerInterval.value) {
                clearInterval(timerInterval.value)
                timerInterval.value = null
             }
             if (window.notify) window.notify('error', 'Please watch the complete video to earn reward.')
          }
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
        
        if (watchDuration.value > 0 && currentTime > watchDuration.value + 2) {
          videoPlayer.value.currentTime = watchDuration.value
          return
        }
        
        if (isVideoPlaying.value && currentTime >= watchDuration.value) {
          watchDuration.value = Math.floor(currentTime)
        }
        
        const minWatchTime = Math.floor((totalDuration.value || 60) * 0.9)
        const videoWatchedTime = Math.floor(currentTime || 0)
        
        if (!videoCompleted.value && adTimer.value <= 0) {
          if (watchDuration.value >= minWatchTime || videoWatchedTime >= minWatchTime) {
            videoCompleted.value = true
            if (timerInterval.value) {
              clearInterval(timerInterval.value)
              timerInterval.value = null
            }
            if (currentAd.value && currentAd.value.id) {
              completeAd(currentAd.value)
            }
          }
        }
      }
    }

    const onVideoSeeked = (event) => {
      if (videoPlayer.value && watchDuration.value > 0) {
        const currentTime = videoPlayer.value.currentTime
        if (currentTime > watchDuration.value + 1) {
          videoPlayer.value.currentTime = watchDuration.value
        }
      }
    }

    const onVideoSeeking = (event) => {
      if (videoPlayer.value && watchDuration.value > 0) {
        const currentTime = videoPlayer.value.currentTime
        if (currentTime > watchDuration.value + 1) {
          videoPlayer.value.currentTime = watchDuration.value
        }
      }
    }

    const onVideoLoaded = () => {
      if (videoPlayer.value) {
        videoPlayer.value.addEventListener('contextmenu', (e) => e.preventDefault())
        videoPlayer.value.addEventListener('keydown', (e) => {
          if (['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown'].includes(e.key)) {
            e.preventDefault()
          }
        })
      }
    }

    const togglePlayPause = () => {
      if (videoPlayer.value) {
        if (videoPlayer.value.paused) {
          videoPlayer.value.play().catch(err => {
            console.error(err)
          })
        } else {
          videoPlayer.value.pause()
        }
      }
    }

    const handleVideoError = (event) => {
      const el = videoPlayer.value
      const code = el?.error?.code
      console.error('Video error:', { code, event, src: currentAd.value?.video_url })

      // Try switching to a known-good fallback video URL
      const fallbacks = [
        'https://www.w3schools.com/html/mov_bbb.mp4',
        'https://interactive-examples.mdn.mozilla.net/media/cc0-videos/flower.mp4',
        'https://filesamples.com/samples/video/mp4/sample_640x360.mp4',
      ]
      const currentSrc = currentAd.value?.video_url
      const nextSrc = fallbacks.find(u => u && u !== currentSrc)
      if (currentAd.value && nextSrc) {
        currentAd.value.video_url = nextSrc
        nextTick(() => {
          if (videoPlayer.value) {
            try {
              videoPlayer.value.load()
              videoPlayer.value.play().catch(() => {})
            } catch (e) {
              // ignore
            }
          }
        })
      } else if (el) {
        // As a last resort, reload same source once
        setTimeout(() => {
          if (videoPlayer.value) videoPlayer.value.load()
        }, 1000)
      }
    }

    const handleVideoLoaded = () => {
      console.log('Video loaded successfully')
    }

    const onVideoEnded = () => {
      if (videoCompleted.value) return

      const requiredWatchTime = Number(totalDuration.value || 60)
      const minWatchTime = requiredWatchTime * 0.9 
      
      if (watchDuration.value < minWatchTime && adTimer.value > 5) {
        return
      }
      
      if (watchDuration.value >= minWatchTime || adTimer.value <= 0) {
        videoCompleted.value = true
        if (timerInterval.value) {
          clearInterval(timerInterval.value)
          timerInterval.value = null
        }
        completeAd(currentAd.value)
      }
    }

    const completeAd = async (ad) => {
      if (!ad || !ad.id) return
      
      const minWatchDuration = Math.floor((totalDuration.value || 60) * 0.9)
      const finalWatchDuration = Math.max(watchDuration.value || 0, minWatchDuration)
      
      try {
        const response = await api.post('/ads/complete', { 
          ad_id: ad.id,
          watch_duration: finalWatchDuration,
          ad_url: ad.video_url || ad.image
        })
        
        if (response.data.status === 'success') {
          if (!watchedAds.value.includes(ad.id)) {
            watchedAds.value.push(ad.id)
          }
          
          const earned = response.data?.data?.earning ?? ad.earning ?? 0
          earnedAmount.value = earned

          if (window.notify) {
            window.notify('success', `Ad completed! You earned ${currencySymbol.value}${formatAmount(earned)}!`)
          }
          
          const currentIndex = allAds.value.findIndex(a => a.id === ad.id)
          
          if (currentIndex !== -1) {
            const nextIndex = currentIndex + 1
            if (nextIndex < allAds.value.length) {
              currentUnlockedIndex.value = nextIndex
            }
          }
          
          setTimeout(() => {
            closeAdModal()
            fetchAds().then(() => {
              const watchedCount = watchedAds.value.length
              if (watchedCount > 0) {
                const newUnlockedIndex = watchedCount
                if (newUnlockedIndex < allAds.value.length) {
                  currentUnlockedIndex.value = newUnlockedIndex
                } else {
                  currentUnlockedIndex.value = allAds.value.length - 1
                }
              } else {
                currentUnlockedIndex.value = 0
              }
            })
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
      earnedAmount.value = 0
    }

    const fetchAds = async () => {
      loading.value = true
      try {
        const response = await api.get('/ads/work')
        
        if (response.data.status === 'success') {
          hasActivePackage.value = true
          const responseData = response.data.data || {}
          const adsList = responseData.data || []
          
          allAds.value = Array.isArray(adsList) ? adsList : []
          currencySymbol.value = responseData.currency_symbol || response.data.currency_symbol || '₹'
          activePackage.value = responseData.active_package || null
          isNewUserOffer.value = !!responseData.is_new_user_offer
          noPackageMessage.value = ''
          
          if (allAds.value.length > 0) {
            ads.value = allAds.value 
            watchedAds.value = []
            
            let highestWatchedIndex = -1
            
            for (let i = 0; i < allAds.value.length; i++) {
              const isWatched = allAds.value[i].is_watched === true || 
                               allAds.value[i].is_watched === 1 || 
                               allAds.value[i].is_watched === 'true' || 
                               allAds.value[i].is_watched === '1'
              if (isWatched) {
                watchedAds.value.push(allAds.value[i].id)
                highestWatchedIndex = i
              }
            }
            
            // Backend provides next_ad_id (sequence). Convert to unlocked index (0-based).
            // If next_ad_id is null (daily limit reached / offer completed), keep all cards unlocked so watched cards don't look locked.
            const nextAdIdRaw = responseData?.active_package?.next_ad_id
            const newUnlockedIndex = nextAdIdRaw ? Math.max(0, Number(nextAdIdRaw) - 1) : Math.max(0, allAds.value.length - 1)
            currentUnlockedIndex.value = newUnlockedIndex
            
            if (currentUnlockedIndex.value < 0) {
              currentUnlockedIndex.value = 0
            }
            
            if (currentUnlockedIndex.value >= allAds.value.length) {
              currentUnlockedIndex.value = allAds.value.length - 1
            }
            
            currentAd.value = null
          } else {
            currentAd.value = null
            ads.value = []
            currentUnlockedIndex.value = 0
            watchedAds.value = []
          }
        } else {
          hasActivePackage.value = false
          allAds.value = []
          currentAd.value = null
          ads.value = []
          activePackage.value = null
        }
      } catch (error) {
        if (error.response?.data?.remark === 'no_active_package') {
          hasActivePackage.value = false
          allAds.value = []
          currentAd.value = null
          ads.value = []
          activePackage.value = null
          isNewUserOffer.value = false
          const msg = error.response?.data?.message?.error?.[0] || error.response?.data?.message
          noPackageMessage.value = (Array.isArray(msg) ? msg[0] : msg) || 'Purchase an ad plan (₹2999–₹9999) to continue earning.'
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
      } else {
        currentAd.value = null
      }
    }

    onMounted(() => {
      fetchAds()
      // Scroll handling moved to pure Tailwind classes, no JS enforcement needed
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
      isAdWatched,
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
      handleVideoError,
      handleVideoLoaded,
      loading,
      hasActivePackage,
      currentAdIndex,
      allAds,
      watchedAds,
      showNextAd,
      activePackage,
      currentUnlockedIndex,
      isNewUserOffer,
      noPackageMessage,
      earnedAmount
    }
  }
}
</script>
