<template>
  <div class="funnel-container d-flex align-items-center justify-content-center">
    <!-- Fluid background blobs -->
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>

    <div class="container mobile-container">
      <div v-if="loading" class="text-center">
        <div class="spinner-border text-primary" role="status"></div>
      </div>
      <div v-else class="glass-card zoomIn">
        
        <div class="text-center mb-4 mt-2">
          <!-- Main Logo/Profile (Using a placeholder or company logo) -->
          <div class="profile-avatar mb-3">
             <img :src="'/assets/images/logo_icon/logo.png'" alt="ADS Logo" class="logo-img" />
          </div>
          
          <!-- Dynamic Headings -->
          <h1 class="main-heading">
            <span class="highlight">{{ settings?.whatsapp_heading || 'Earn Money by just Watching Ads' }}</span>
          </h1>
          <p class="sub-heading mt-3">
            🔥 The world is changing fast. Start your daily income from your phone today! 
          </p>
        </div>

        <div class="text-center mt-5 mb-3">
          <a :href="waLink" target="_blank" class="btn btn-whatsapp w-100">
            <i class="fab fa-whatsapp me-2 text-xl"></i> WhatsApp Now
          </a>
        </div>
        
        <div class="text-center mt-4">
          <p class="small text-white-50">Sponsor: {{ sponsorName || sponsorCode }}</p>
        </div>

      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import api from '../../services/api';

export default {
  name: 'WhatsAppFunnel',
  setup() {
    const route = useRoute();
    const loading = ref(true);
    const settings = ref(null);
    const waNumber = ref('');
    const sponsorName = ref('');
    const prefilledMsg = ref('');
    const sponsorCode = ref(route.params.ref || 'System');
    const currentYear = new Date().getFullYear();

    const fetchSettings = async () => {
      loading.value = true;
      try {
        const res = await api.get(`/landing-page?ref=${sponsorCode.value}`);
        if(res.data.status === 'success') {
          settings.value = res.data.data.settings;
          waNumber.value = res.data.data.whatsapp_number || '';
          sponsorName.value = res.data.data.sponsor_name || '';
          prefilledMsg.value = res.data.data.prefilled_whatsapp_msg || 'Hi!';
        }
      } catch (err) {
        console.error('Failed to load funnel settings:', err);
      } finally {
        loading.value = false;
      }
    };

    const waLink = computed(() => {
      // Prioritize query param phone, then API sponsor phone
      let rawPhone = route.query.phone ? route.query.phone : waNumber.value;
      if (!rawPhone) return `https://wa.me/?text=${encodeURIComponent(prefilledMsg.value)}`;

      // Clean non-digits
      let digits = rawPhone.replace(/\D/g, '');
      
      // If starts with 91, assume it already has country code. Otherwise add 91 if it's 10 digits.
      if (digits.startsWith('91') && digits.length > 10) {
          return `https://wa.me/${digits}?text=${encodeURIComponent(prefilledMsg.value)}`;
      }
      
      // Ensure 10 digits for India
      if (digits.length > 10) digits = digits.slice(-10);
      
      return `https://wa.me/91${digits}?text=${encodeURIComponent(prefilledMsg.value)}`;
    });

    onMounted(() => {
      document.title = "WhatsApp - Fast Income";
      fetchSettings();
    });

    return {
      loading,
      settings,
      sponsorName,
      sponsorCode,
      waLink,
      currentYear
    };
  }
}
</script>

<style scoped>
.funnel-container {
  min-height: 100vh;
  width: 100vw;
  background-color: #0d1117;
  position: relative;
  overflow: hidden;
  font-family: 'Inter', sans-serif;
}

/* Fluid Gradients / Blobs */
.blob {
  position: absolute;
  filter: blur(80px);
  z-index: 0;
  border-radius: 50%;
  animation: float 10s infinite ease-in-out alternate;
}
.blob-1 {
  width: 300px;
  height: 300px;
  background: rgba(30, 215, 96, 0.4); /* WhatsApp green hint */
  top: -100px;
  left: -100px;
}
.blob-2 {
  width: 250px;
  height: 250px;
  background: rgba(100, 50, 255, 0.3); /* Purple hint */
  bottom: -50px;
  right: -50px;
  animation-delay: -5s;
}

@keyframes float {
  0% { transform: translateY(0) scale(1); }
  100% { transform: translateY(40px) scale(1.1); }
}

.mobile-container {
  max-width: 450px; /* strict mobile constraint */
  width: 100%;
  padding: 20px;
  z-index: 1;
}

/* Glassmorphism Card */
.glass-card {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(15px);
  -webkit-backdrop-filter: blur(15px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 24px;
  padding: 40px 30px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
}

.profile-avatar {
  background: rgba(255,255,255,0.1);
  width: 110px;
  height: 110px;
  margin: 0 auto;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid rgba(255,255,255,0.2);
  overflow: hidden;
}
.profile-avatar .logo-img {
  width: 80%;
  object-fit: contain;
}

.main-heading {
  font-size: 1.6rem;
  font-weight: 800;
  color: #fff;
  line-height: 1.3;
}
.main-heading .highlight {
  background: linear-gradient(135deg, #FFF, #A5B4FC);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.sub-heading {
  font-size: 0.95rem;
  color: rgba(255,255,255,0.8);
  line-height: 1.5;
}

/* WhatsApp Button */
.btn-whatsapp {
  background: linear-gradient(135deg, #128C7E, #25D366);
  color: #fff !important;
  font-weight: 700;
  font-size: 1.15rem;
  padding: 16px 20px;
  border-radius: 14px;
  border: none;
  box-shadow: 0 10px 20px -5px rgba(37, 211, 102, 0.4);
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}
.btn-whatsapp:hover {
  transform: translateY(-3px);
  box-shadow: 0 15px 25px -5px rgba(37, 211, 102, 0.6);
}

.zoomIn {
  animation: zoomIn 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

@keyframes zoomIn {
  0% { opacity: 0; transform: scale(0.9); }
  100% { opacity: 1; transform: scale(1); }
}

@media (max-width: 480px) {
  .glass-card { padding: 30px 20px; }
  .main-heading { font-size: 1.4rem; }
  .profile-avatar { width: 90px; height: 90px; }
}
</style>
