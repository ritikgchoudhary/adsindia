<template>
  <MasterAdminLayout page-title="Courses & Video Links">
      <!-- Glassmorphic Header -->
      <div class="tw-bg-slate-900/50 tw-backdrop-blur-xl tw-border tw-border-white/10 tw-rounded-3xl tw-p-8 tw-mb-8 tw-flex tw-flex-col md:tw-flex-row tw-justify-between tw-items-center tw-gap-6">
        <div>
          <h1 class="tw-text-3xl tw-font-black tw-text-white tw-flex tw-items-center tw-gap-3 tw-m-0">
            <span class="tw-p-3 tw-bg-indigo-500/10 tw-rounded-2xl tw-text-indigo-400">
              <i class="fas fa-play-circle"></i>
            </span>
            Course Management
          </h1>
          <p class="tw-text-slate-400 tw-mt-2 tw-text-sm tw-font-medium">Maintain your database of video courses and package associations.</p>
        </div>
        <div class="tw-flex tw-items-center tw-gap-4">
          <button @click="fetchCourses" class="tw-p-4 tw-bg-white/5 hover:tw-bg-white/10 tw-border tw-border-white/10 tw-rounded-2xl tw-text-white tw-transition-all active:tw-scale-95">
            <i class="fas fa-sync-alt" :class="{ 'tw-animate-spin': loading }"></i>
          </button>
          <router-link to="/admin/courses/create" class="tw-group tw-px-6 tw-py-3.5 tw-bg-gradient-to-r tw-from-indigo-600 tw-to-violet-600 tw-text-white tw-font-black tw-rounded-2xl tw-no-underline tw-flex tw-items-center tw-gap-3 tw-transition-all hover:tw-scale-105 hover:tw-shadow-xl hover:tw-shadow-indigo-500/30">
            <i class="fas fa-plus tw-text-xs"></i>
            <span>Create New Course</span>
            <i class="fas fa-arrow-right tw-text-[10px] tw-opacity-50 tw-transition-transform group-hover:tw-translate-x-1"></i>
          </router-link>
        </div>
      </div>

      <!-- Stats Row -->
      <div class="tw-grid tw-grid-cols-2 lg:tw-grid-cols-4 tw-gap-6 tw-mb-8">
        <div v-for="stat in [
          { label: 'Total Courses', val: courses.length, icon: 'fa-graduation-cap', color: 'tw-from-indigo-500 tw-to-blue-600', bg: 'tw-bg-indigo-500/10' },
          { label: 'Active Status', val: activeCount, icon: 'fa-check-circle', color: 'tw-from-emerald-400 tw-to-teal-600', bg: 'tw-bg-emerald-500/10' },
          { label: 'Paused/Draft', val: inactiveCount, icon: 'fa-pause-circle', color: 'tw-from-slate-400 tw-to-slate-600', bg: 'tw-bg-slate-500/10' },
          { label: 'Hyperlinked', val: withVideoCount, icon: 'fa-link', color: 'tw-from-amber-400 tw-to-orange-600', bg: 'tw-bg-amber-500/10' }
        ]" :key="stat.label" class="tw-bg-slate-900/50 tw-backdrop-blur-xl tw-border tw-border-white/10 tw-rounded-3xl tw-p-6 tw-group hover:tw-bg-slate-900/80 tw-transition-all">
          <div class="tw-flex tw-justify-between tw-items-start tw-mb-4">
            <div :class="`tw-w-12 tw-h-12 ${stat.bg} tw-rounded-2xl tw-flex tw-items-center tw-justify-center tw-transition-transform group-hover:tw-scale-110`">
              <i :class="`fas ${stat.icon} tw-text-xl tw-bg-gradient-to-br ${stat.color} tw-bg-clip-text tw-text-transparent`"></i>
            </div>
            <div class="tw-w-1.5 tw-h-1.5 tw-bg-white/20 tw-rounded-full"></div>
          </div>
          <div class="tw-text-2xl tw-font-black tw-text-white tw-mb-1">{{ stat.val }}</div>
          <div class="tw-text-slate-500 tw-text-xs tw-font-bold tw-uppercase tw-tracking-widest">{{ stat.label }}</div>
        </div>
      </div>

      <!-- Main Content Card -->
      <div class="tw-bg-slate-900/50 tw-backdrop-blur-xl tw-border tw-border-white/10 tw-rounded-[2.5rem] tw-overflow-hidden tw-relative">
        <div class="tw-p-8 tw-border-b tw-border-white/5 tw-flex tw-justify-between tw-items-center">
          <h5 class="tw-text-xl tw-font-black tw-text-white tw-m-0 tw-flex tw-items-center tw-gap-3">
             <i class="fas fa-list-ul tw-text-indigo-400"></i> Course Registry
          </h5>
          <div class="tw-flex tw-items-center tw-gap-3">
             <div class="tw-px-4 tw-py-1.5 tw-bg-indigo-500/10 tw-border tw-border-indigo-500/20 tw-rounded-full tw-text-[10px] tw-text-indigo-400 tw-font-black tw-uppercase tw-tracking-tighter">
                Live Catalog
             </div>
          </div>
        </div>

        <div class="tw-overflow-x-auto">
          <table class="tw-w-full tw-border-collapse">
            <thead>
              <tr class="tw-bg-white/[0.02]">
                <th class="tw-px-8 tw-py-6 tw-text-left tw-text-[11px] tw-font-black tw-text-slate-500 tw-uppercase tw-tracking-widest">#</th>
                <th class="tw-px-8 tw-py-6 tw-text-left tw-text-[11px] tw-font-black tw-text-slate-500 tw-uppercase tw-tracking-widest">Course Details</th>
                <th class="tw-px-8 tw-py-6 tw-text-left tw-text-[11px] tw-font-black tw-text-slate-500 tw-uppercase tw-tracking-widest">Package Allocation</th>
                <th class="tw-px-8 tw-py-6 tw-text-left tw-text-[11px] tw-font-black tw-text-slate-500 tw-uppercase tw-tracking-widest">Digital Content</th>
                <th class="tw-px-8 tw-py-6 tw-text-left tw-text-[11px] tw-font-black tw-text-slate-500 tw-uppercase tw-tracking-widest">Status</th>
                <th class="tw-px-8 tw-py-6 tw-text-right tw-text-[11px] tw-font-black tw-text-slate-500 tw-uppercase tw-tracking-widest">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading">
                <td colspan="6" class="tw-py-20 text-center">
                  <div class="tw-inline-flex tw-flex-col tw-items-center tw-gap-4">
                    <div class="tw-w-12 tw-h-12 tw-border-4 tw-border-indigo-500/20 tw-border-t-indigo-500 tw-rounded-full tw-animate-spin"></div>
                    <span class="tw-text-slate-500 tw-text-sm tw-font-bold tw-uppercase">Syncing Data...</span>
                  </div>
                </td>
              </tr>
              <tr v-else-if="courses.length === 0" class="tw-border-b tw-border-white/5">
                <td colspan="6" class="tw-py-24 text-center">
                  <div class="tw-w-20 tw-h-20 tw-bg-white/5 tw-rounded-3xl tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-6">
                    <i class="fas fa-ghost tw-text-4xl tw-text-slate-700"></i>
                  </div>
                  <h4 class="tw-text-white tw-font-black tw-text-xl tw-mb-2">No Records Found</h4>
                  <p class="tw-text-slate-500 tw-text-sm">Start your catalog by adding your first course.</p>
                </td>
              </tr>
              <tr v-else v-for="(c, i) in courses" :key="c.id" class="tw-group hover:tw-bg-white/[0.03] tw-transition-all tw-border-b tw-border-white/5 last:tw-border-0">
                <td class="tw-px-8 tw-py-6 tw-text-slate-600 tw-text-sm tw-font-bold">{{ i + 1 }}</td>
                <td class="tw-px-8 tw-py-6">
                  <div class="tw-flex tw-flex-col">
                    <span class="tw-text-white tw-font-black tw-text-base group-hover:tw-text-indigo-400 tw-transition-colors">{{ c.name }}</span>
                    <span class="tw-text-slate-500 tw-text-[10px] tw-uppercase tw-font-bold tw-mt-1">{{ c.category || 'Standard' }} • {{ c.duration || 'Flexible' }}</span>
                  </div>
                </td>
                <td class="tw-px-8 tw-py-6">
                  <span class="tw-px-3 tw-py-1.5 tw-bg-indigo-500/10 tw-border tw-border-indigo-500/20 tw-rounded-xl tw-text-indigo-300 tw-text-[11px] tw-font-black tw-uppercase">
                    {{ getPackageName(c) }}
                  </span>
                </td>
                <td class="tw-px-8 tw-py-6">
                  <div class="tw-flex tw-items-center tw-gap-3">
                    <div class="tw-w-8 tw-h-8 tw-bg-white/5 tw-rounded-lg tw-flex tw-items-center tw-justify-center">
                      <i class="fas fa-video tw-text-xs" :class="c.video_url ? 'tw-text-emerald-400' : 'tw-text-slate-600'"></i>
                    </div>
                    <span class="tw-text-slate-400 tw-text-xs tw-font-mono tw-max-w-[150px] tw-truncate">
                      {{ c.video_url || 'No link added' }}
                    </span>
                  </div>
                </td>
                <td class="tw-px-8 tw-py-6">
                  <div class="tw-flex tw-items-center tw-gap-2">
                    <div :class="`tw-w-2 tw-h-2 tw-rounded-full ${c.status === 1 ? 'tw-bg-emerald-400 tw-animate-pulse' : 'tw-bg-slate-500'}`"></div>
                    <span :class="`${c.status === 1 ? 'tw-text-emerald-400' : 'tw-text-slate-500'} tw-text-[10px] tw-font-black tw-uppercase tw-tracking-tighter text-glow`">
                      {{ c.status === 1 ? 'Published' : 'Paused' }}
                    </span>
                  </div>
                </td>
                <td class="tw-px-8 tw-py-6 tw-text-right">
                  <div class="tw-flex tw-items-center tw-justify-end tw-gap-2">
                    <button @click="openEdit(c)" class="tw-w-10 tw-h-10 tw-bg-white/5 hover:tw-bg-indigo-500/20 tw-border tw-border-white/10 hover:tw-border-indigo-500/30 tw-rounded-xl tw-text-slate-400 hover:tw-text-indigo-400 tw-transition-all" title="Quick Update">
                      <i class="fas fa-bolt tw-text-sm"></i>
                    </button>
                    <router-link :to="`/admin/courses/edit/${c.id}`" class="tw-w-10 tw-h-10 tw-bg-white/5 hover:tw-bg-violet-500/20 tw-border tw-border-white/10 hover:tw-border-violet-500/30 tw-rounded-xl tw-text-slate-400 hover:tw-text-violet-400 tw-transition-all tw-flex tw-items-center tw-justify-center" title="Advanced Edit">
                      <i class="fas fa-cog tw-text-sm"></i>
                    </router-link>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Premium Edit Modal -->
      <div v-if="showModal" class="tw-fixed tw-inset-0 tw-z-[100] tw-flex tw-items-center tw-justify-center tw-px-4">
        <div class="tw-absolute tw-inset-0 tw-bg-black/80 tw-backdrop-blur-md" @click="closeEdit"></div>
        <div class="tw-bg-slate-900 tw-border tw-border-white/10 tw-rounded-[2.5rem] tw-shadow-2xl tw-w-full tw-max-w-2xl tw-relative tw-z-10 tw-overflow-hidden tw-animate-fade-in-up">
          <div class="tw-bg-gradient-to-br tw-from-indigo-500 tw-to-purple-700 tw-p-8">
            <div class="tw-flex tw-justify-between tw-items-center">
              <div class="tw-flex tw-items-center tw-gap-4">
                <div class="tw-w-14 tw-h-14 tw-bg-white/10 tw-backdrop-blur-xl tw-rounded-2xl tw-flex tw-items-center tw-justify-center tw-border tw-border-white/10">
                  <i class="fas fa-pen-nib tw-text-white tw-text-2xl"></i>
                </div>
                <div>
                   <h3 class="tw-text-white tw-font-black tw-text-2xl tw-m-0">Quick Course Update</h3>
                   <p class="tw-text-indigo-100/60 tw-text-[10px] tw-font-black tw-uppercase tw-tracking-widest tw-mt-1">Synchronizing Catalog State</p>
                </div>
              </div>
              <button @click="closeEdit" class="tw-w-10 tw-h-10 tw-bg-white/5 hover:tw-bg-white/10 tw-rounded-xl tw-text-white tw-transition-all">
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>
          
          <div class="tw-p-8 tw-max-h-[70vh] tw-overflow-y-auto custom-scrollbar">
            <form v-if="form" @submit.prevent="saveCourse" class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-6">
              <!-- Grid Layout for Inputs -->
              <div class="tw-col-span-full">
                <label class="tw-text-slate-500 tw-text-[10px] tw-font-black tw-uppercase tw-tracking-widest tw-mb-2 tw-block">Course Title</label>
                <input v-model="form.name" type="text" class="tw-w-full tw-bg-white/5 tw-border tw-border-white/10 tw-rounded-2xl tw-px-5 tw-py-4 tw-text-white tw-font-bold focus:tw-border-indigo-500 tw-outline-none tw-transition-all" required>
              </div>

              <div class="tw-col-span-full">
                <label class="tw-text-slate-500 tw-text-[10px] tw-font-black tw-uppercase tw-tracking-widest tw-mb-2 tw-block">Video Content Link</label>
                <div class="tw-relative">
                   <div class="tw-absolute tw-left-5 tw-top-1/2 tw--translate-y-1/2 tw-text-indigo-400">
                      <i class="fas fa-link"></i>
                   </div>
                   <input v-model="form.video_url" type="url" class="tw-w-full tw-bg-white/5 tw-border tw-border-white/10 tw-rounded-2xl tw-pl-12 tw-pr-5 tw-py-4 tw-text-indigo-300 tw-font-mono tw-text-sm focus:tw-border-indigo-500 tw-outline-none tw-transition-all" placeholder="https://youtube.com/embed/...">
                </div>
              </div>

              <div>
                <label class="tw-text-slate-500 tw-text-[10px] tw-font-black tw-uppercase tw-tracking-widest tw-mb-2 tw-block">Niche / Category</label>
                <input v-model="form.category" type="text" class="tw-w-full tw-bg-white/5 tw-border tw-border-white/10 tw-rounded-2xl tw-px-5 tw-py-4 tw-text-white tw-font-bold focus:tw-border-indigo-500 tw-outline-none tw-transition-all">
              </div>

              <div>
                <label class="tw-text-slate-500 tw-text-[10px] tw-font-black tw-uppercase tw-tracking-widest tw-mb-2 tw-block">Duration Estimate</label>
                <input v-model="form.duration" type="text" class="tw-w-full tw-bg-white/5 tw-border tw-border-white/10 tw-rounded-2xl tw-px-5 tw-py-4 tw-text-white tw-font-bold focus:tw-border-indigo-500 tw-outline-none tw-transition-all">
              </div>

              <div>
                <label class="tw-text-slate-500 tw-text-[10px] tw-font-black tw-uppercase tw-tracking-widest tw-mb-2 tw-block">Assigned Package</label>
                <select v-model.number="form.required_course_plan_id" class="tw-w-full tw-bg-white/5 tw-border tw-border-white/10 tw-rounded-2xl tw-px-5 tw-py-4 tw-text-white tw-font-bold focus:tw-border-indigo-500 tw-outline-none tw-transition-all">
                   <option v-for="pkg in packages" :key="pkg.id" :value="pkg.id">{{ pkg.name }} (Lvl {{ pkg.level }})</option>
                </select>
              </div>

              <div>
                <label class="tw-text-slate-500 tw-text-[10px] tw-font-black tw-uppercase tw-tracking-widest tw-mb-2 tw-block">Visibility Status</label>
                <select v-model.number="form.status" class="tw-w-full tw-bg-white/5 tw-border tw-border-white/10 tw-rounded-2xl tw-px-5 tw-py-4 tw-text-white tw-font-bold focus:tw-border-indigo-500 tw-outline-none tw-transition-all">
                   <option :value="1">Public (Active)</option>
                   <option :value="0">Draft (Inactive)</option>
                </select>
              </div>

              <div class="tw-col-span-full tw-pt-6 tw-flex tw-gap-4">
                <button type="submit" class="tw-flex-1 tw-py-4 tw-bg-gradient-to-r tw-from-indigo-600 tw-to-purple-600 tw-text-white tw-font-black tw-rounded-2xl tw-transition-all active:tw-scale-[0.98] hover:tw-shadow-xl hover:tw-shadow-indigo-500/20 tw-flex tw-items-center tw-justify-center tw-gap-3" :disabled="saving">
                  <span v-if="!saving">Commit Changes</span>
                  <span v-else>Processing...</span>
                  <i v-if="!saving" class="fas fa-paper-plane tw-text-xs"></i>
                  <div v-else class="tw-w-4 tw-h-4 tw-border-2 tw-border-white/30 tw-border-t-white tw-rounded-full tw-animate-spin"></div>
                </button>
                <button type="button" @click="closeEdit" class="tw-px-8 tw-py-4 tw-bg-white/5 hover:tw-bg-white/10 tw-text-slate-400 tw-font-bold tw-rounded-2xl tw-transition-all">
                  Cancel
                </button>
              </div>
            </form>
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
.text-glow {
  text-shadow: 0 0 10px rgba(16, 185, 129, 0.4);
}
.custom-scrollbar::-webkit-scrollbar { width: 5px; }
.custom-scrollbar::-webkit-scrollbar-track { background: rgba(255,255,255,0.02); }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.2); }
</style>
