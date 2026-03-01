<template>
  <div class="funnel-container d-flex flex-column align-items-center">
    <!-- Top Nav Placeholder -->
    <nav class="funnel-nav w-100 p-3">
      <div class="container d-flex justify-content-between align-items-center">
        <img :src="'/assets/images/logo_icon/logo.png'" alt="Logo" class="nav-logo" />
        <a :href="ctaUrl" class="btn btn-purchase-nav">Purchase Now</a>
      </div>
    </nav>
    
    <div class="container mobile-container flex-grow-1 mt-4">
      <div v-if="loading" class="text-center py-5">
        <div class="spinner-border text-primary" role="status"></div>
      </div>
      <div v-else class="glass-card zoomIn d-flex flex-column pb-4">
        
        <!-- FOMO Timer Box -->
        <div v-if="timeLeft > 0" class="timer-box text-center mb-4">
          <p class="timer-label"><i class="fas fa-clock text-warning me-1"></i> Offer Ends In</p>
          <div class="countdown d-flex justify-content-center gap-2 mt-2">
            <div class="time-block"><span>{{ hours }}</span><small>Hours</small></div>
            <div class="time-block"><span>{{ minutes }}</span><small>Mins</small></div>
            <div class="time-block"><span>{{ seconds }}</span><small>Secs</small></div>
          </div>
        </div>

        <div class="text-center mb-4">
          <!-- Dynamic Headings -->
          <h1 class="main-heading">
            {{ settings?.marketing_heading || 'Watch Ads. Earn Money. Simple as that.' }}
          </h1>
          <p class="sub-heading mt-3">
            {{ settings?.marketing_subheading || 'No Team Building. No Recruiting. Start making daily income from your phone right now.' }}
          </p>
        </div>

        <!-- Media Frame -->
        <div class="media-wrapper mb-4 text-center" v-if="settings?.video_url">
          <img v-if="isImage" :src="settings.video_url" class="img-fluid rounded shadow-lg media-content" alt="Offer Image">
          <video v-else-if="isVideoFile" :src="settings.video_url" class="w-100 rounded shadow-lg media-content" controls autoplay muted loop playsinline></video>
          <div v-else class="video-wrapper shadow-lg">
             <iframe 
               :src="settings.video_url" 
               frameborder="0" 
               allow="autoplay; encrypted-media" 
               allowfullscreen>
             </iframe>
          </div>
        </div>

        <!-- Marketing Features Grid -->
        <div class="features-grid mb-4">
          <div class="feat-box"><i class="fas fa-check-circle text-success me-2"></i> Watch Daily Ads</div>
          <div class="feat-box"><i class="fas fa-check-circle text-success me-2"></i> Generate Instant Income</div>
          <div class="feat-box"><i class="fas fa-check-circle text-success me-2"></i> Direct Bank Withdrawal</div>
          <div class="feat-box"><i class="fas fa-check-circle text-success me-2"></i> No Network Marketing</div>
        </div>

        <!-- Call To Action -->
        <div class="text-center mt-3 mb-2 px-2">
          <a :href="ctaUrl" class="btn btn-purchase w-100 glowing-btn pulse" @click="handlePurchase">
             <i class="fas fa-rocket me-2"></i> Join Now for ₹{{ formatPrice(settings?.discounted_price) || '2999.00' }}
          </a>
          <p class="small text-white-50 mt-3"><i class="fas fa-shield-alt text-success me-1"></i> 100% Secure Checkout</p>
        </div>
        <div class="text-center mt-4">
          <p class="small text-white-50">Sponsor: {{ sponsorName || sponsorCode }}</p>
        </div>

      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed, onUnmounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../../services/api';

export default {
  name: 'OfferFunnel',
  setup() {
    const route = useRoute();
    const router = useRouter();
    const loading = ref(true);
    const settings = ref(null);
    const sponsorName = ref('');
    const sponsorCode = ref(route.params.ref || 'System');
    const ctaUrlBase = ref('/register');
    
    // Timer state
    const timerHours = ref(2); // From DB
    const timeLeft = ref(0);
    let interval = null;

    const fetchSettings = async () => {
      loading.value = true;
      try {
        const res = await api.get(`/landing-page?ref=${sponsorCode.value}`);
        if(res.data.status === 'success') {
          settings.value = res.data.data.settings;
          sponsorName.value = res.data.data.sponsor_name || '';
          ctaUrlBase.value = res.data.data.cta_url || '/register';
          
          timerHours.value = parseInt(settings.value.timer_hours) || 2;
          startTimer();
        }
      } catch (err) {
        console.error('Failed to load funnel settings:', err);
      } finally {
        loading.value = false;
      }
    };

    const ctaUrl = computed(() => {
      // The backend sends a full vue route path like /register?pkg=2&pkg_discount=499&pkg_sig=xxxx...
      return ctaUrlBase.value;
    });

    const handlePurchase = (e) => {
      // Use router push for SPA routing
      e.preventDefault();
      if(ctaUrl.value) {
        router.push(ctaUrl.value);
      }
    };

    const startTimer = () => {
      // Create fake scarcity countdown using sessionStorage to persist across refreshes
      let endTime = sessionStorage.getItem(`fomo_end_${sponsorCode.value}`);
      const now = Math.floor(Date.now() / 1000);
      
      if (!endTime || now > parseInt(endTime)) {
        endTime = now + (timerHours.value * 3600);
        sessionStorage.setItem(`fomo_end_${sponsorCode.value}`, endTime);
      }
      
      timeLeft.value = parseInt(endTime) - now;
      
      interval = setInterval(() => {
        timeLeft.value--;
        if(timeLeft.value <= 0) {
          clearInterval(interval);
        }
      }, 1000);
    };

    const isImage = computed(() => {
        if(!settings.value?.video_url) return false;
        return /\.(jpg|jpeg|png|webp|gif|svg)$/i.test(settings.value.video_url);
    });

    const isVideoFile = computed(() => {
        if(!settings.value?.video_url) return false;
        return /\.(mp4|webm|ogg)$/i.test(settings.value.video_url);
    });

    const hours = computed(() => String(Math.floor(timeLeft.value / 3600)).padStart(2, '0'));
    const minutes = computed(() => String(Math.floor((timeLeft.value % 3600) / 60)).padStart(2, '0'));
    const seconds = computed(() => String(timeLeft.value % 60).padStart(2, '0'));

    onMounted(() => {
      document.title = "Special Offer - Join Now";
      fetchSettings();
    });

    onUnmounted(() => {
      if(interval) clearInterval(interval);
    });

    const formatPrice = (price) => {
        if(!price) return null;
        return parseFloat(price).toFixed(2);
    };

    return {
      loading,
      settings,
      sponsorName,
      sponsorCode,
      ctaUrl,
      handlePurchase,
      timeLeft,
      hours,
      minutes,
      seconds,
      isImage,
      isVideoFile,
      formatPrice
    };
  }
}
</script>

<style scoped>
.funnel-container {
  min-height: 100vh;
  width: 100vw;
  background-color: #0b0f19;
  background-image: radial-gradient(circle at center 20%, #1e293b 0%, #0b0f19 70%);
  position: relative;
  overflow-x: hidden;
  font-family: 'Inter', sans-serif;
}

.funnel-nav {
  background: rgba(15, 23, 42, 0.95);
  border-bottom: 1px solid rgba(255,255,255,0.05);
  box-shadow: 0 4px 20px rgba(0,0,0,0.5);
}
.nav-logo { height: 35px; object-fit: contain; }

.btn-purchase-nav {
  background: linear-gradient(135deg, #f59e0b, #d97706);
  color: #fff !important;
  font-weight: 700;
  padding: 8px 20px;
  border-radius: 8px;
  border: none;
  font-size: 0.9rem;
}

.mobile-container {
  max-width: 650px; /* wider for video */
  width: 100%;
  padding: 10px;
  z-index: 1;
}

/* Glassmorphism Card */
.glass-card {
  background: rgba(15, 23, 42, 0.6);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 20px;
  padding: 40px 30px;
  box-shadow: 0 30px 60px -15px rgba(0, 0, 0, 0.7);
}

.timer-box {
  background: rgba(0,0,0,0.3);
  padding: 15px;
  border-radius: 12px;
  border: 1px solid rgba(245, 158, 11, 0.2);
}
.timer-label { margin: 0; font-size: 0.85rem; color: #fff; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; }
.time-block {
  display: flex;
  flex-direction: column;
  background: rgba(255,255,255,0.05);
  border-radius: 8px;
  padding: 8px 15px;
  min-width: 65px;
}
.time-block span { font-size: 1.5rem; font-weight: 800; color: #f59e0b; line-height: 1; }
.time-block small { font-size: 0.65rem; color: rgba(255,255,255,0.6); text-transform: uppercase; margin-top: 4px; }

.main-heading { font-size: 2.2rem; font-weight: 900; color: #fff; line-height: 1.2; letter-spacing: -0.5px; }
.sub-heading { font-size: 1.1rem; color: rgba(255,255,255,0.7); line-height: 1.5; font-weight: 400; }

.video-wrapper {
  position: relative;
  padding-bottom: 56.25%; /* 16:9 */
  height: 0;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 20px 40px rgba(0,0,0,0.6);
  border: 2px solid rgba(255,255,255,0.1);
  background: #000;
}
.video-wrapper iframe {
  position: absolute;
  top: 0; left: 0;
  width: 100%; height: 100%;
}

.media-content {
  max-height: 400px;
  object-fit: cover;
  border: 2px solid rgba(255,255,255,0.1);
}

.features-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}
.feat-box {
  background: rgba(255,255,255,0.03);
  padding: 12px 15px;
  border-radius: 10px;
  border: 1px solid rgba(255,255,255,0.05);
  font-size: 0.9rem;
  color: #fff;
  display: flex;
  align-items: center;
}

/* Purchase Button */
.btn-purchase {
  background: linear-gradient(135deg, #f59e0b, #ea580c);
  color: #fff !important;
  font-weight: 800;
  font-size: 1.35rem;
  padding: 20px 20px;
  border-radius: 14px;
  border: none;
  box-shadow: 0 10px 25px -5px rgba(234, 88, 12, 0.4);
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
  text-transform: uppercase;
}

.pulse {
  animation: pulse-animation 2s infinite;
}

@keyframes pulse-animation {
  0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(234, 88, 12, 0.7); }
  70% { transform: scale(1.02); box-shadow: 0 0 0 15px rgba(234, 88, 12, 0); }
  100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(234, 88, 12, 0); }
}

.footer-dark { background: #070a11; border-top: 1px solid rgba(255,255,255,0.05); }

@media (max-width: 500px) {
  .glass-card { padding: 30px 15px; border-radius: 16px; border: 0;}
  .main-heading { font-size: 1.6rem; }
  .sub-heading { font-size: 0.95rem; }
  .features-grid { grid-template-columns: 1fr; gap: 8px;}
  .btn-purchase { font-size: 1.15rem; padding: 16px 20px; }
  .mobile-container{ padding: 5px; }
}
</style>
