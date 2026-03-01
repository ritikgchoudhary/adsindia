<template>
  <MasterAdminLayout page-title="Landing Page Config">
    <div class="glass-card mb-4 premium-glass" v-if="!loading">
                <div class="card-body p-sm-5">
                  <form @submit.prevent="updateSettings">
                    
                    <div class="glass-section mb-5 p-4 rounded-4 position-relative overflow-hidden">
                       <div class="glow-bg-success"></div>
                       <h5 class="section-title text-success"><i class="fas fa-tag"></i> Active Offer Package (For All Links)</h5>
                       <div class="row g-4 mt-1">
                         <div class="col-md-6">
                           <label class="form-label text-white-50 form-required fw-semibold">System Base Package</label>
                           <select v-model="form.active_package_id" class="form-control form-control-dark custom-select">
                             <option v-for="pkg in packages" :key="pkg.id" :value="pkg.id">
                               {{ pkg.name }} (Original {{ currencySymbol }}{{ formatAmount(pkg.price) }})
                             </option>
                           </select>
                           <small class="text-muted"><i class="fas fa-info-circle"></i> This package is attached specifically on checkout button.</small>
                         </div>
  
                         <div class="col-md-6">
                           <label class="form-label text-warning fw-semibold"><i class="fas fa-gifts"></i> Discounted Sales Price (₹)</label>
                           <input type="number" v-model="form.discounted_price" class="form-control form-control-dark border-warning custom-input" placeholder="e.g. 499">
                           <small class="text-warning"><i class="fas fa-exclamation-triangle"></i> Leave blank to use original package price.</small>
                         </div>
  
                         <div class="col-md-6">
                           <label class="form-label text-white-50 fw-semibold"><i class="fas fa-hourglass-half text-danger"></i> FOMO Countdown Timer (Hours)</label>
                           <input type="number" v-model.number="form.timer_hours" class="form-control form-control-dark custom-input" placeholder="e.g. 2">
                           <small class="text-muted"><i class="fas fa-info-circle"></i> Number of hours constraint shown on page per user connection.</small>
                         </div>
                       </div>
                    </div>

                    <div class="glass-section mb-5 p-4 rounded-4 position-relative overflow-hidden">
                       <div class="glow-bg-primary"></div>
                       <h5 class="section-title text-primary"><i class="fas fa-desktop"></i> Direct Purchase (Main Offer) Page Content</h5>
                       <div class="row g-4 mt-1">
                         <div class="col-md-12">
                           <label class="form-label text-white-50 fw-semibold">Sales Video URL (YouTube Embed Link) Or File Upload</label>
                           <div class="media-input-wrapper p-3 rounded-3 border border-secondary mb-2">
                             <div class="d-flex flex-column flex-md-row gap-3">
                                <input type="url" v-model="form.video_url" class="form-control form-control-dark custom-input" placeholder="https://www.youtube.com/embed/your_video_id">
                                <div class="d-flex align-items-center justify-content-center px-4 py-2 border rounded text-muted fw-bold bg-darker">OR</div>
                                <input type="file" @change="handleFileUpload" class="form-control form-control-dark custom-input" accept="image/*,video/*">
                             </div>
                           </div>
                           <small class="text-muted d-block mt-2"><i class="fas fa-info-circle"></i> Leave URL blank if you are uploading a file. Provide either a video url or upload a media file.</small>
                         </div>
                       </div>
                    </div>

                    <div class="d-flex justify-content-end mb-2 pt-3 border-top border-secondary">
                       <button type="submit" class="btn btn-purchase px-5 py-3 fw-bold shadow-lg" :disabled="saving">
                         <span v-if="!saving"><i class="fas fa-save me-2"></i> Deploy Funnel Update To All App Links</span>
                         <span v-else><div class="spinner-border spinner-border-sm me-2" role="status"></div> Updating Network...</span>
                       </button>
                    </div>

                  </form>
                </div>
              </div>
              
              <div v-else class="text-center py-5">
                 <div class="spinner-border text-primary" role="status"></div>
                 <p class="text-white mt-3">Loading system records...</p>
              </div>

  </MasterAdminLayout>
</template>

<script>
import { ref, onMounted } from 'vue';
import MasterAdminLayout from '../../components/master_admin/MasterAdminLayout.vue';
import adminApi from '../../services/api';

export default {
  name: 'LandingPageSettings',
  components: {
    MasterAdminLayout
  },
  setup() {
    const loading = ref(true);
    const saving = ref(false);
    const packages = ref([]);
    const currencySymbol = ref('₹');
    
    const form = ref({
      video_url: '',
      media_file: null,
      active_package_id: '',
      discounted_price: '',
      timer_hours: 2,
    });

    const fetchAllData = async () => {
      try {
        // Fetch Packages
        const pkgRes = await adminApi.get('/packages'); // Corrected from /admin/packages/all
        if (pkgRes.data.status === 'success') {
           const data = pkgRes.data.data;
           packages.value = data?.data || data || [];
           currencySymbol.value = data?.currency_symbol || '₹';
        }
      } catch (e) { 
        console.error('Packages error', e); 
      }

      try {
        // Fetch Current LP Settings
        const lpRes = await adminApi.get('/landing-page');
        if (lpRes.data.status === 'success') {
          const s = lpRes.data.data.settings;
          form.value = {
            video_url: s.video_url || '',
            media_file: null,
            active_package_id: s.active_package_id || (packages.value.length > 0 ? packages.value[0].id : ''),
            discounted_price: s.discounted_price || '',
            timer_hours: s.timer_hours || 2,
          };
        }
      } catch (err) {
        if(window.notify) window.notify('error', 'Failed to load landing page config.');
      } finally {
        loading.value = false;
      }
    };

    const handleFileUpload = (e) => {
      const file = e.target.files[0];
      if (file) {
        form.value.media_file = file;
      } else {
        form.value.media_file = null;
      }
    };

    const updateSettings = async () => {
       saving.value = true;
       try {
         const formData = new FormData();
         if(form.value.active_package_id) formData.append('active_package_id', form.value.active_package_id);
         if(form.value.discounted_price) formData.append('discounted_price', form.value.discounted_price);
         if(form.value.timer_hours) formData.append('timer_hours', form.value.timer_hours);
         
         if (form.value.video_url) formData.append('video_url', form.value.video_url);
         if (form.value.media_file) {
             formData.append('media_file', form.value.media_file);
         }

         const res = await adminApi.post('/admin/landing-page/update', formData);
         
         if(res.data.status === 'success') {
           if(window.notify) window.notify('success', 'Landing Page Network Updated! Active Affiliates links are synced.');
           fetchAllData();
         }
       } catch (err) {
         console.error('Update Failed', err);
         const errorMsg = err.response?.data?.message?.error?.[0] || err.message || 'Update Failed.';
         if(window.notify) window.notify('error', errorMsg);
       } finally {
         saving.value = false;
       }
    };

    const formatAmount = (amt) => {
      return parseFloat(amt).toFixed(2);
    };

    onMounted(() => {
      document.title = "Landing Page Settings | Master Admin";
      fetchAllData();
    });

    return {
      loading,
      saving,
      packages,
      currencySymbol,
      form,
      handleFileUpload,
      formatAmount,
      updateSettings
    };
  }
}
</script>

<style scoped>
.form-control-dark {
  background-color: rgba(15, 23, 42, 0.8) !important;
  border: 1px solid rgba(255, 255, 255, 0.1) !important;
  color: #fff !important;
}
.custom-input, .custom-select {
  border-radius: 12px !important;
  padding: 12px 15px !important;
  transition: all 0.3s ease;
  box-shadow: inset 0 2px 4px rgba(0,0,0,0.5);
}
.form-control-dark:focus {
  background-color: rgba(30, 41, 59, 0.95) !important;
  border-color: rgba(59, 130, 246, 0.5) !important;
  box-shadow: 0 0 15px rgba(59, 130, 246, 0.3) !important;
}
.form-required::after { content: '*'; color: #ea580c; margin-left: 4px; }

/* Futuristic UI Elements */
.premium-glass {
  background: rgba(15, 23, 42, 0.6) !important;
  backdrop-filter: blur(25px) !important;
  -webkit-backdrop-filter: blur(25px) !important;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7) !important;
  border: 1px solid rgba(255, 255, 255, 0.08) !important;
  border-radius: 20px !important;
}
.glass-section {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.05);
  box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}
.glow-bg-success {
  position: absolute; top: -50px; left: -50px; width: 150px; height: 150px;
  background: radial-gradient(circle, rgba(16,185,129,0.15) 0%, rgba(0,0,0,0) 70%); border-radius: 50%; pointer-events: none;
}
.glow-bg-primary {
  position: absolute; top: -50px; right: -50px; width: 150px; height: 150px;
  background: radial-gradient(circle, rgba(59,130,246,0.15) 0%, rgba(0,0,0,0) 70%); border-radius: 50%; pointer-events: none;
}
.bg-darker { background: rgba(0,0,0,0.3); }

.section-title { font-size: 1.25rem; font-weight: 800; border-bottom: 2px dashed rgba(255,255,255,0.08); padding-bottom: 15px; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 0.5px;}

.btn-purchase {
  background: linear-gradient(135deg, #3b82f6, #2563eb) !important;
  color: #fff !important;
  font-weight: 800 !important;
  font-size: 1.15rem !important;
  border: none !important;
  border-radius: 14px !important;
  box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.4) !important;
  transition: all 0.3s ease !important;
  position: relative;
  overflow: hidden;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.btn-purchase:hover {
  transform: translateY(-2px);
  box-shadow: 0 15px 30px -5px rgba(37, 99, 235, 0.6) !important;
}

select option {
  background-color: #0f172a;
  color: #fff;
}
</style>
