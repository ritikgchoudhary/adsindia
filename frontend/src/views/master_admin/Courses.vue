<template>
  <MasterAdminLayout page-title="Courses & Video Links">
    <div class="ma-courses">
      <!-- Error loading courses -->
      <div v-if="loadError" class="ma-card mb-4" style="border-left: 4px solid #ef4444;">
        <div class="ma-card__body d-flex align-items-center justify-content-between flex-wrap gap-3">
          <div>
            <strong class="text-danger"><i class="fas fa-exclamation-circle me-2"></i>{{ loadError }}</strong>
            <p class="text-muted mb-0 mt-1 small">Ensure you are logged in as Master Admin. The courses API is at /api/admin/course</p>
          </div>
          <button type="button" class="ma-btn ma-btn--primary" @click="fetchCourses">
            <i class="fas fa-sync-alt me-1"></i> Retry
          </button>
        </div>
      </div>

      <!-- Stats Row -->
      <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
          <div class="ma-kpi ma-kpi--indigo">
            <div class="ma-kpi__icon"><i class="fas fa-graduation-cap"></i></div>
            <div>
              <div class="ma-kpi__val">{{ courses.length }}</div>
              <div class="ma-kpi__lbl">Total Courses</div>
            </div>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="ma-kpi ma-kpi--green">
            <div class="ma-kpi__icon"><i class="fas fa-check-circle"></i></div>
            <div>
              <div class="ma-kpi__val">{{ activeCount }}</div>
              <div class="ma-kpi__lbl">Active</div>
            </div>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="ma-kpi ma-kpi--slate">
            <div class="ma-kpi__icon"><i class="fas fa-pause-circle"></i></div>
            <div>
              <div class="ma-kpi__val">{{ inactiveCount }}</div>
              <div class="ma-kpi__lbl">Inactive</div>
            </div>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="ma-kpi ma-kpi--amber">
            <div class="ma-kpi__icon"><i class="fas fa-link"></i></div>
            <div>
              <div class="ma-kpi__val">{{ withVideoCount }}</div>
              <div class="ma-kpi__lbl">With Video URL</div>
            </div>
          </div>
        </div>
      </div>

      <div class="ma-card mb-4">
        <div class="ma-card__header ma-card__header--gradient">
          <div>
            <h5 class="ma-card__title"><i class="fas fa-play-circle me-2"></i>Courses (Packages)</h5>
            <p class="ma-card__subtitle">Update course video links. These courses appear on User → Packages / User → Courses.</p>
          </div>
          <div class="ma-header-actions">
            <router-link to="/admin/courses/create" class="ma-btn ma-btn--primary ma-btn--glow">
              <i class="fas fa-plus me-1"></i> Add Course
            </router-link>
            <button type="button" class="ma-btn ma-btn--secondary" @click="fetchCourses">
              <i class="fas fa-sync-alt me-1"></i> Refresh
            </button>
            <span class="ma-card__count">{{ courses.length }} courses</span>
          </div>
        </div>
        <div class="table-responsive ma-table-wrapper">
          <table class="ma-table">
            <thead>
              <tr>
                <th>#</th>
                <th>Course Name</th>
                <th>Package</th>
                <th>Category</th>
                <th>Video URL</th>
                <th>Duration</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading">
                <td colspan="8" class="ma-table__loading"><div class="ma-spinner"></div></td>
              </tr>
              <tr v-else-if="courses.length === 0">
                <td colspan="8" class="ma-table__empty">
                  <i class="fas fa-graduation-cap"></i>
                  <p>No courses found</p>
                </td>
              </tr>
              <tr v-else v-for="(c, i) in courses" :key="c.id" class="ma-table-row">
                <td>{{ i + 1 }}</td>
                <td>
                  <strong>{{ c.name }}</strong>
                </td>
                <td>
                  <span class="ma-badge ma-badge--info">{{ getPackageName(c) }}</span>
                </td>
                <td>{{ c.category || '–' }}</td>
                <td>
                  <span class="ma-video-url" :title="c.video_url || 'No link'">
                    {{ truncateUrl(c.video_url) }}
                  </span>
                </td>
                <td>{{ c.duration || '–' }}</td>
                <td>
                  <span class="ma-badge" :class="c.status === 1 ? 'ma-badge--success' : 'ma-badge--muted'">
                    {{ c.status === 1 ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td>
                  <div class="ma-action-row">
                    <button type="button" class="ma-btn-action ma-btn-action--primary" @click="openEdit(c)" title="Quick edit (video + details)">
                      <i class="fas fa-pen"></i> Quick Edit
                    </button>
                    <router-link :to="`/admin/courses/edit/${c.id}`" class="ma-btn-action ma-btn-action--outline" title="Full edit page">
                      <i class="fas fa-edit"></i> Full Edit
                    </router-link>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Edit Course Modal -->
      <div v-if="showModal" class="ma-modal-backdrop" @click.self="closeEdit">
        <div class="ma-modal ma-modal--wide">
          <div class="ma-modal__header">
            <div class="ma-modal__title">
              <div class="ma-modal__title-icon"><i class="fas fa-video"></i></div>
              <div>
                <div class="ma-modal__title-top">Edit Course</div>
                <div class="ma-modal__title-sub">Video link & details</div>
              </div>
            </div>
            <button type="button" class="ma-modal__close" @click="closeEdit" aria-label="Close">&times;</button>
          </div>
          <div class="ma-modal__body">
            <form v-if="form" @submit.prevent="saveCourse" class="ma-form">
              <div class="ma-edit-card ma-edit-card--indigo">
                <div class="ma-edit-card__title"><i class="fas fa-heading me-1"></i>Basics</div>
                <div class="ma-form-group">
                  <label class="ma-form-label">Course name <span class="text-danger">*</span></label>
                  <input v-model="form.name" type="text" class="ma-form-input" required maxlength="255" placeholder="Course name">
                </div>
                <div class="ma-form-group">
                  <label class="ma-form-label">Description</label>
                  <textarea v-model="form.description" class="ma-form-input" rows="3" placeholder="Short description"></textarea>
                </div>
              </div>

              <div class="ma-edit-card ma-edit-card--emerald">
                <div class="ma-edit-card__title"><i class="fas fa-link me-1"></i>Video Link</div>
                <div class="ma-form-group">
                  <label class="ma-form-label">Video URL</label>
                  <input v-model="form.video_url" type="url" class="ma-form-input" maxlength="500" placeholder="https://... (YouTube, Vimeo, or direct video link)">
                  <small class="text-muted">Direct video link or embed URL. Leave empty to remove.</small>
                </div>
              </div>

              <div class="row g-3">
                <div class="col-md-6">
                  <div class="ma-edit-card ma-edit-card--slate">
                    <div class="ma-edit-card__title"><i class="fas fa-clock me-1"></i>Duration</div>
                    <div class="ma-form-group mb-0">
                      <label class="ma-form-label">Duration</label>
                      <input v-model="form.duration" type="text" class="ma-form-input" maxlength="50" placeholder="e.g. 10 hours">
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="ma-edit-card ma-edit-card--violet">
                    <div class="ma-edit-card__title"><i class="fas fa-tags me-1"></i>Category</div>
                    <div class="ma-form-group mb-0">
                      <label class="ma-form-label">Category</label>
                      <input v-model="form.category" type="text" class="ma-form-input" maxlength="100" placeholder="e.g. Video Editing">
                    </div>
                  </div>
                </div>
              </div>
              <div class="row g-3">
                <div class="col-md-4">
                  <div class="ma-edit-card ma-edit-card--amber">
                    <div class="ma-edit-card__title"><i class="fas fa-rupee-sign me-1"></i>Price</div>
                    <div class="ma-form-group mb-0">
                      <label class="ma-form-label">Price</label>
                      <input v-model.number="form.price" type="number" step="0.01" min="0" class="ma-form-input" required>
                    </div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="ma-edit-card ma-edit-card--blue">
                    <div class="ma-edit-card__title"><i class="fas fa-box-open me-1"></i>Package</div>
                    <div class="ma-form-group mb-0">
                      <label class="ma-form-label">Package (Course Plan)</label>
                      <select v-model.number="form.required_course_plan_id" class="ma-form-input" required>
                        <option v-for="pkg in packages" :key="pkg.id" :value="pkg.id">{{ pkg.name }} (Level {{ pkg.level }})</option>
                        <option v-if="packages.length === 0" value="1">– Load packages –</option>
                      </select>
                      <small class="text-muted">Is course kis package me dikhega (User → Packages)</small>
                    </div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="ma-edit-card ma-edit-card--green">
                    <div class="ma-edit-card__title"><i class="fas fa-toggle-on me-1"></i>Status</div>
                    <div class="ma-form-group mb-0">
                      <label class="ma-form-label">Status</label>
                      <select v-model.number="form.status" class="ma-form-input">
                        <option :value="1">Active</option>
                        <option :value="0">Inactive</option>
                      </select>
                    </div>
                  </div>
                </div>
              </div>
              <div class="ma-modal__footer">
                <button type="button" class="ma-btn ma-btn--secondary" @click="closeEdit">Cancel</button>
                <button type="submit" class="ma-btn ma-btn--primary">
                  <i class="fas fa-save me-1"></i>
                  Save
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </MasterAdminLayout>
</template>

<script>
import { ref, onMounted, computed } from 'vue'
import MasterAdminLayout from '../../components/master_admin/MasterAdminLayout.vue'
import api from '../../services/api'

export default {
  name: 'MasterAdminCourses',
  components: { MasterAdminLayout },
  setup() {
    const courses = ref([])
    const packages = ref([])
    const loading = ref(true)
    const showModal = ref(false)
    const form = ref(null)
    const saving = ref(false)

    const getPackageName = (c) => {
      if (c.package_name) return c.package_name
      const planId = c.required_course_plan_id ?? 1
      const p = packages.value.find(pkg => pkg.id === planId)
      return p ? p.name : ('Plan #' + planId)
    }

    const truncateUrl = (url) => {
      if (!url) return '–'
      if (url.length <= 50) return url
      return url.slice(0, 47) + '...'
    }

    const loadError = ref('')

    const activeCount = computed(() => (courses.value || []).filter(c => Number(c?.status) === 1).length)
    const inactiveCount = computed(() => (courses.value || []).filter(c => Number(c?.status) !== 1).length)
    const withVideoCount = computed(() => (courses.value || []).filter(c => (c?.video_url || '').trim().length > 0).length)

    const getApiErrorMessage = (payloadOrError) => {
      const d = payloadOrError?.response?.data ?? payloadOrError?.data ?? payloadOrError
      if (!d) return 'Request failed'
      if (typeof d === 'string') return d
      const msg = d.message
      if (Array.isArray(msg) && msg.length) return String(msg[0])
      if (typeof msg === 'string' && msg.trim()) return msg
      if (msg && typeof msg === 'object' && Array.isArray(msg.error) && msg.error.length) return String(msg.error[0])
      const errors = d.errors
      if (errors && typeof errors === 'object') {
        const firstKey = Object.keys(errors)[0]
        const firstVal = firstKey ? errors[firstKey] : null
        if (Array.isArray(firstVal) && firstVal.length) return String(firstVal[0])
      }
      return String(d.remark || 'Request failed')
    }

    const fetchPackages = async () => {
      try {
        const res = await api.get('/admin/packages/all')
        const data = res.data?.data ?? res.data
        const list = data?.packages ?? (Array.isArray(data) ? data : [])
        packages.value = Array.isArray(list) ? list : []
      } catch (e) {
        packages.value = []
      }
    }

    const fetchCourses = async () => {
      loading.value = true
      loadError.value = ''
      try {
        const res = await api.get('/admin/course')
        const data = res.data
        if (data?.status !== 'success') {
          courses.value = []
          loadError.value = getApiErrorMessage(data) || 'Failed to load courses'
          return
        }
        const list = data?.data ?? data?.courses ?? (Array.isArray(data) ? data : [])
        courses.value = Array.isArray(list) ? list : []
      } catch (e) {
        console.error(e)
        courses.value = []
        loadError.value = getApiErrorMessage(e) || (e.message || 'Failed to load courses. Check login.')
      } finally {
        loading.value = false
      }
    }

    const openEdit = async (course) => {
      try {
        if (!packages.value || packages.value.length === 0) {
          await fetchPackages()
        }
        const res = await api.get(`/admin/course/edit/${course.id}`)
        const d = res.data?.data ?? res.data
        form.value = {
          id: d.id,
          name: d.name ?? '',
          slug: d.slug ?? '',
          description: d.description ?? '',
          video_url: d.video_url ?? '',
          duration: d.duration ?? '',
          category: d.category ?? '',
          price: d.price ?? 0,
          required_package_id: d.required_package_id ?? 1,
          required_course_plan_id: d.required_course_plan_id ?? (packages.value[0]?.id ?? 1),
          students_count: d.students_count ?? 0,
          status: d.status ?? 1,
          sort_order: d.sort_order ?? 0,
          is_recommended: d.is_recommended ?? false,
          affiliate_commission_percent: d.affiliate_commission_percent ?? 0,
          affiliate_commission_fixed: d.affiliate_commission_fixed ?? 0,
        }
        showModal.value = true
      } catch (e) {
        console.error(e)
        if (window.notify) window.notify('error', getApiErrorMessage(e) || 'Could not load course details.')
      }
    }

    const closeEdit = () => {
      showModal.value = false
      form.value = null
    }

    const saveCourse = async () => {
      if (!form.value) return
      saving.value = true
      try {
        const payload = {
          name: form.value.name,
          slug: form.value.slug || null,
          description: form.value.description || null,
          video_url: form.value.video_url || null,
          duration: form.value.duration || null,
          category: form.value.category || null,
          price: Number(form.value.price) || 0,
          required_package_id: Number(form.value.required_package_id) || 1,
          required_course_plan_id: form.value.required_course_plan_id ? Number(form.value.required_course_plan_id) : 1,
          students_count: Number(form.value.students_count) || 0,
          status: Number(form.value.status) ?? 1,
          sort_order: Number(form.value.sort_order) || 0,
          is_recommended: !!form.value.is_recommended,
          affiliate_commission_percent: Number(form.value.affiliate_commission_percent) || 0,
          affiliate_commission_fixed: Number(form.value.affiliate_commission_fixed) || 0,
        }
        await api.post(`/admin/course/update/${form.value.id}`, payload)
        closeEdit()
        await fetchCourses()
        if (window.notify) window.notify('success', 'Course updated successfully')
      } catch (e) {
        console.error(e)
        if (window.notify) window.notify('error', getApiErrorMessage(e) || 'Failed to save course.')
      } finally {
        saving.value = false
      }
    }

    onMounted(async () => {
      await fetchPackages()
      await fetchCourses()
    })

    return {
      courses,
      packages,
      getPackageName,
      loading,
      loadError,
      activeCount,
      inactiveCount,
      withVideoCount,
      showModal,
      form,
      saving,
      truncateUrl,
      openEdit,
      closeEdit,
      saveCourse,
      fetchCourses,
    }
  },
}
</script>

<style scoped>
.ma-header-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; justify-content: flex-end; }
.ma-btn--glow { box-shadow: 0 10px 30px rgba(99,102,241,0.25); }

.ma-kpi{
  display:flex; align-items:center; gap:12px;
  border-radius: 18px;
  padding: 14px 16px;
  border: 1px solid rgba(255,255,255,0.08);
  background: rgba(30,41,59,0.75);
  transition: transform .2s ease, box-shadow .2s ease, background .2s ease;
}
.ma-kpi:hover{ transform: translateY(-2px); box-shadow: 0 10px 28px rgba(0,0,0,0.35); background: rgba(30,41,59,0.9); }
.ma-kpi__icon{
  width: 44px; height: 44px; border-radius: 14px;
  display:flex; align-items:center; justify-content:center;
  color: #fff; flex: 0 0 auto;
}
.ma-kpi__val{ font-size: 1.35rem; font-weight: 800; color: #f1f5f9; line-height: 1.1; }
.ma-kpi__lbl{ font-size: .78rem; font-weight: 700; color: rgba(255,255,255,0.55); }
.ma-kpi--indigo .ma-kpi__icon{ background: linear-gradient(135deg,#6366f1,#8b5cf6); }
.ma-kpi--green .ma-kpi__icon{ background: linear-gradient(135deg,#10b981,#059669); }
.ma-kpi--slate .ma-kpi__icon{ background: linear-gradient(135deg,#64748b,#334155); }
.ma-kpi--amber .ma-kpi__icon{ background: linear-gradient(135deg,#f59e0b,#d97706); }

.ma-action-row { display: flex; gap: 8px; flex-wrap: wrap; }

.ma-table__loading, .ma-table__empty {
  padding: 3rem 1rem !important;
  text-align: center;
  color: #94a3b8;
}
.ma-table__empty i { font-size: 2.5rem; margin-bottom: 0.75rem; display: block; color: #475569; }
.ma-table__empty p { margin: 0; }
.ma-table__loading { display: flex; align-items: center; justify-content: center; gap: 0.75rem; }
.ma-spinner {
  width: 24px;
  height: 24px;
  border: 3px solid rgba(255,255,255,0.2);
  border-top-color: #6366f1;
  border-radius: 50%;
  animation: ma-spin 0.8s linear infinite;
}
@keyframes ma-spin { to { transform: rotate(360deg); } }

.ma-video-url {
  font-size: 0.85rem;
  color: #94a3b8;
  max-width: 200px;
  display: inline-block;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.ma-badge {
  padding: 4px 10px;
  border-radius: 9999px;
  font-size: 0.8rem;
  font-weight: 600;
}
.ma-badge--success { background: rgba(34, 197, 94, 0.2); color: #22c55e; }
.ma-badge--muted { background: rgba(148, 163, 184, 0.2); color: #94a3b8; }
.ma-badge--info { background: rgba(99, 102, 241, 0.25); color: #a5b4fc; }
.ma-btn-action {
  padding: 6px 12px;
  border-radius: 8px;
  font-size: 0.85rem;
  font-weight: 500;
  border: none;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  text-decoration: none;
}
.ma-btn-action--primary { background: rgba(99, 102, 241, 0.2); color: #818cf8; }
.ma-btn-action--primary:hover { background: rgba(99, 102, 241, 0.35); }
.ma-btn-action--outline{
  background: rgba(255,255,255,0.06);
  color: rgba(255,255,255,0.8);
  border: 1px solid rgba(255,255,255,0.10);
}
.ma-btn-action--outline:hover{ background: rgba(255,255,255,0.10); }
.ma-modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
  padding: 20px;
}
.ma-modal {
  background: rgba(15,23,42,0.92);
  border-radius: 16px;
  max-width: 560px;
  width: 100%;
  max-height: 90vh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
  border: 1px solid rgba(255,255,255,0.10);
}
.ma-modal--wide { max-width: 640px; }
.ma-modal__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px 24px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  background: linear-gradient(135deg, rgba(99,102,241,0.22) 0%, rgba(139,92,246,0.18) 100%);
}
.ma-modal__title { display:flex; align-items:center; gap:12px; }
.ma-modal__title-icon{
  width: 42px; height: 42px; border-radius: 14px;
  display:flex; align-items:center; justify-content:center;
  background: rgba(255,255,255,0.10);
  border: 1px solid rgba(255,255,255,0.10);
  color: #fff;
}
.ma-modal__title-top{ font-size: 1.05rem; font-weight: 800; color: #f1f5f9; line-height: 1.1; }
.ma-modal__title-sub{ font-size: 0.8rem; color: rgba(255,255,255,0.65); margin-top: 2px; }
.ma-modal__close {
  background: none;
  border: none;
  color: #94a3b8;
  font-size: 1.5rem;
  cursor: pointer;
  line-height: 1;
}
.ma-modal__body { padding: 24px; overflow-y: auto; }
.ma-form-group { margin-bottom: 1rem; }
.ma-form-label { display: block; margin-bottom: 6px; color: #cbd5e1; font-size: 0.9rem; }
.ma-form-input {
  width: 100%;
  padding: 10px 14px;
  border-radius: 10px;
  border: 1px solid rgba(255, 255, 255, 0.15);
  background: rgba(15, 23, 42, 0.6);
  color: #f1f5f9;
  font-size: 0.95rem;
}
.ma-form-input:focus {
  outline: none;
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
}
.ma-modal__footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 20px;
  padding-top: 20px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}
.ma-btn { padding: 10px 20px; border-radius: 10px; font-weight: 600; border: none; cursor: pointer; }
.ma-btn--primary { background: #6366f1; color: white; }
.ma-btn--secondary { background: rgba(255, 255, 255, 0.1); color: #e2e8f0; }

.ma-edit-card{
  border-radius: 16px;
  padding: 14px 14px 6px;
  margin-bottom: 12px;
  border: 1px solid rgba(255,255,255,0.10);
  background: rgba(30,41,59,0.45);
}
.ma-edit-card__title{
  font-weight: 800;
  font-size: 0.9rem;
  margin-bottom: 10px;
  color: #e2e8f0;
  display:flex;
  align-items:center;
  gap:6px;
}
.ma-edit-card--indigo{ border-left: 4px solid rgba(99,102,241,0.9); }
.ma-edit-card--emerald{ border-left: 4px solid rgba(16,185,129,0.9); }
.ma-edit-card--slate{ border-left: 4px solid rgba(148,163,184,0.7); }
.ma-edit-card--violet{ border-left: 4px solid rgba(139,92,246,0.85); }
.ma-edit-card--amber{ border-left: 4px solid rgba(245,158,11,0.9); }
.ma-edit-card--blue{ border-left: 4px solid rgba(59,130,246,0.9); }
.ma-edit-card--green{ border-left: 4px solid rgba(34,197,94,0.9); }
</style>
