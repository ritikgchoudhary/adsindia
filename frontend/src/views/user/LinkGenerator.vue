<template>
  <DashboardLayout page-title="Funnel Link Generator" :dark-theme="true">
    <div class="glass-card mb-4" v-if="!loading">
            <div class="card-header border-0 bg-transparent pb-0 mb-3 text-center">
              <h4 class="card-title text-white mb-2"><i class="fas fa-link me-2 text-warning"></i> Funnel Link Generator</h4>
              <p class="text-white-50 small mb-0">Share these automated landing pages with your clients to skyrocket your conversions!</p>
            </div>
            
            <div class="card-body">
              
              <!-- Offer Funnel (Direct Purchase) -->
              <div class="link-box p-4 rounded-4 mb-4" style="background: rgba(var(--bs-primary-rgb), 0.1); border: 1px solid rgba(var(--bs-primary-rgb), 0.2);">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h5 class="text-primary mb-0"><i class="fas fa-rocket me-2"></i> Direct Purchase Landing Page</h5>
                  <span class="badge bg-primary-soft text-primary">High Converting</span>
                </div>
                <p class="text-muted small mb-3">
                  This single page has a Video, Timer, and direct checkout link that auto-locks you as the sponsor and applies today's active package and discount!
                </p>

                <div class="row align-items-center">
                  <div class="col-md-9 mb-3 mb-md-0">
                    <div class="input-group">
                      <span class="input-group-text bg-dark border-secondary text-muted"><i class="fas fa-globe"></i></span>
                      <input type="text" class="form-control bg-dark text-white border-secondary fw-bold" :value="offerLink" readonly>
                      <button class="btn btn-primary" type="button" @click="copyToClipboard(offerLink, 'Offer Funnel Link Copied! 🚀')">
                        <i class="fas fa-copy me-1"></i> Copy Link
                      </button>
                    </div>
                  </div>
                  <div class="col-md-3 text-md-end text-start">
                    <a :href="offerLink" target="_blank" class="text-primary small text-decoration-none border-bottom border-primary pb-1">Test My Link <i class="fas fa-external-link-alt ms-1"></i></a>
                  </div>
                </div>
              </div>

              <!-- WhatsApp Funnel (Lead Generation) -->
              <div class="link-box p-4 rounded-4" style="background: rgba(37, 211, 102, 0.05); border: 1px solid rgba(37, 211, 102, 0.2);">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h5 class="text-success mb-0"><i class="fab fa-whatsapp me-2"></i> WhatsApp Auto-Prospect Page</h5>
                  <span class="badge bg-success-soft text-success">Lead Generation</span>
                </div>
                <p class="text-muted small mb-3">
                  This page asks users to click "WhatsApp Now". When they click it, their pre-written inquiry message will be sent directly to your registered Mobile Number!
                </p>

                <div class="row align-items-center">
                  <div class="col-md-9 mb-3 mb-md-0">
                    <div class="input-group">
                      <span class="input-group-text bg-dark border-secondary text-muted"><i class="fab fa-whatsapp"></i></span>
                      <input type="text" class="form-control bg-dark text-white border-secondary fw-bold" :value="computedWaLink" readonly>
                      <button class="btn btn-success" type="button" @click="copyToClipboard(computedWaLink, 'WhatsApp Funnel Copied! 📲')">
                        <i class="fas fa-copy me-1"></i> Copy Link
                      </button>
                    </div>
                  </div>
                  <div class="col-md-3 text-md-end text-start">
                    <a :href="computedWaLink" target="_blank" class="text-success small text-decoration-none border-bottom border-success pb-1">Test Page <i class="fas fa-external-link-alt ms-1"></i></a>
                  </div>
                </div>
                
                <div class="mt-3">
                    <label class="form-label text-white-50 small mb-1"><i class="fas fa-phone me-1"></i> Custom Phone (Optional)</label>
                    <div class="input-group input-group-sm w-auto d-inline-flex border border-secondary rounded overflow-hidden">
                       <span class="input-group-text bg-dark border-0 text-muted">+91</span>
                       <input type="text" v-model="customPhone" class="form-control bg-dark border-0 text-white" placeholder="Enter 10 digit number" maxlength="10" @input="customPhone = customPhone.replace(/[^0-9]/g, '')">
                    </div>
                    <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">If your whatsapp number is different than registered number, add it here before copying.</small>
                </div>
              </div>
              
            </div>
          </div>
          
          <div v-else class="text-center py-5">
             <div class="spinner-border text-primary" role="status"></div>
             <p class="text-white mt-3">Generating your funnels...</p>
          </div>
  </DashboardLayout>
</template>

<script>
import { ref, onMounted, computed } from 'vue';
import DashboardLayout from '../../components/DashboardLayout.vue';
import api from '../../services/api';

export default {
  name: 'LinkGenerator',
  components: {
    DashboardLayout
  },
  setup() {
    const loading = ref(true);
    const offerLink = ref('');
    const waLink = ref('');
    const customPhone = ref('');

    const fetchLinks = async () => {
      try {
        const res = await api.get('/my-landing-links');
        if (res.data.status === 'success') {
          offerLink.value = res.data.data.offer_link;
          waLink.value = res.data.data.wa_link;
        }
      } catch (err) {
        if(window.notify) window.notify('error', 'Failed to generate funnel links');
      } finally {
        loading.value = false;
      }
    };

    const copyToClipboard = async (text, successMsg) => {
      try {
        if (navigator?.clipboard?.writeText) {
          await navigator.clipboard.writeText(text)
        } else {
          const ta = document.createElement('textarea')
          ta.value = text
          ta.style.position = 'fixed'
          ta.style.opacity = '0'
          document.body.appendChild(ta)
          ta.focus()
          ta.select()
          document.execCommand('copy')
          document.body.removeChild(ta)
        }
        if(window.notify) window.notify('success', successMsg);
      } catch (err) {
        if(window.notify) window.notify('error', 'Failed to copy link');
      }
    };

    onMounted(() => {
      document.title = "ADS SKILL INDIA";
      fetchLinks();
    });

    const computedWaLink = computed(() => {
        if(!waLink.value) return '';
        if(customPhone.value && customPhone.value.length === 10) {
            const url = new URL(waLink.value);
            url.searchParams.set('phone', customPhone.value);
            return url.toString();
        }
        return waLink.value;
    });

    return {
      loading,
      offerLink,
      waLink,
      customPhone,
      computedWaLink,
      copyToClipboard
    };
  }
}
</script>

<style scoped>
.glass-card {
  background: rgba(15, 23, 42, 0.6);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 20px;
  overflow: hidden;
}

.bg-primary-soft { background: rgba(var(--bs-primary-rgb), 0.15) !important; }
.bg-success-soft { background: rgba(37, 211, 102, 0.15) !important; color: #25D366 !important; }

.input-group-text { border-right: none; }
.form-control[readonly] { background-color: #1a1e26 !important; border-left: none;}

.btn-primary { border-radius: 0 8px 8px 0; }
.btn-success { background: linear-gradient(135deg, #128C7E, #25D366); border: none; border-radius: 0 8px 8px 0;}

@media (max-width: 576px) {
    .btn-primary, .btn-success {
        padding-left: 10px; padding-right: 10px; font-size: 0.85rem;
    }
}
</style>
