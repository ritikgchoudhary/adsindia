<template>
  <MasterAdminLayout page-title="Influencer Program">
    <div class="tw-space-y-6">
      <div class="tw-flex tw-flex-col md:tw-flex-row tw-gap-3 tw-items-start md:tw-items-center tw-justify-between">
        <div>
          <div class="tw-text-white tw-font-black tw-text-xl">Influencer Program Admin</div>
          <div class="tw-text-white/60 tw-text-sm">Upload APK, view drafts/submissions, generate prefilled invite links.</div>
        </div>
        <div class="tw-flex tw-gap-2 tw-flex-wrap">
          <button class="ma-btn" :class="tab === 'applications' ? 'ma-btn--primary' : ''" @click="tab = 'applications'">
            <i class="fas fa-inbox me-1"></i> Applications
          </button>
          <button class="ma-btn" :class="tab === 'invite' ? 'ma-btn--primary' : ''" @click="tab = 'invite'">
            <i class="fas fa-link me-1"></i> Invite Link
          </button>
          <button class="ma-btn" :class="tab === 'apk' ? 'ma-btn--primary' : ''" @click="tab = 'apk'">
            <i class="fas fa-android me-1"></i> APK Settings
          </button>
          <button class="ma-btn" type="button" @click="refresh">
            <i class="fas fa-rotate me-1"></i> Refresh
          </button>
        </div>
      </div>

      <!-- APK SETTINGS -->
      <div v-if="tab === 'apk'" class="ma-card">
        <div class="ma-card__header">
          <div>
            <h5 class="ma-card__title mb-1"><i class="fas fa-android me-2"></i>APK Upload</h5>
            <p class="ma-card__subtitle mb-0">Upload the latest APK. The public form will automatically use this link.</p>
          </div>
        </div>
        <div class="ma-card__body">
          <div class="row g-3 align-items-end">
            <div class="col-md-5">
              <label class="form--label">Current APK URL</label>
              <input id="apkUrl" class="form--control" :value="settings.apk_url || ''" readonly />
            </div>
            <div class="col-md-2">
              <button class="ma-btn" type="button" @click="copy('apkUrl')">
                <i class="fas fa-copy me-1"></i> Copy
              </button>
            </div>
            <div class="col-md-5">
              <label class="form--label">Current Version</label>
              <input class="form--control" :value="settings.apk_version || ''" readonly />
            </div>
          </div>

          <div class="row g-3 align-items-end mt-2">
            <div class="col-md-5">
              <label class="form--label">Upload APK (.apk)</label>
              <input ref="apkFile" type="file" class="form--control" accept=".apk" />
            </div>
            <div class="col-md-3">
              <label class="form--label">Version (optional)</label>
              <input v-model="uploadVersion" class="form--control" placeholder="e.g. 1.0.1" />
            </div>
            <div class="col-md-4 d-grid">
              <button class="ma-btn ma-btn--primary" type="button" :disabled="uploading" @click="uploadApk">
                <i v-if="uploading" class="fas fa-spinner fa-spin me-1"></i>
                <i v-else class="fas fa-cloud-arrow-up me-1"></i>
                Upload
              </button>
            </div>
          </div>

          <div class="tw-mt-3 tw-text-white/60 tw-text-sm">
            Public page: <a class="tw-text-indigo-300" href="/patner-program.php" target="_blank" rel="noopener noreferrer">`/patner-program.php`</a>
          </div>
        </div>
      </div>

      <!-- INVITE LINK -->
      <div v-if="tab === 'invite'" class="ma-card">
        <div class="ma-card__header">
          <div>
            <h5 class="ma-card__title mb-1"><i class="fas fa-link me-2"></i>Generate Prefilled Invite Link</h5>
            <p class="ma-card__subtitle mb-0">Prefill Instagram ID or other links, then copy and send to the user.</p>
          </div>
        </div>
        <div class="ma-card__body">
          <div class="row g-3 align-items-end">
            <div class="col-md-3">
              <label class="form--label">Platform</label>
              <select v-model="invite.platform" class="form--control">
                <option value="">(Optional)</option>
                <option value="instagram">Instagram</option>
                <option value="facebook">Facebook</option>
                <option value="youtube">YouTube</option>
                <option value="other">Other</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form--label">Instagram Handle</label>
              <input v-model="invite.instagram_handle" class="form--control" placeholder="@username" />
            </div>
            <div class="col-md-3">
              <label class="form--label">Facebook Link</label>
              <input v-model="invite.facebook_link" class="form--control" placeholder="https://facebook.com/..." />
            </div>
            <div class="col-md-3">
              <label class="form--label">YouTube Link</label>
              <input v-model="invite.youtube_link" class="form--control" placeholder="https://youtube.com/..." />
            </div>
          </div>

          <div class="row g-3 align-items-end mt-1">
            <div class="col-md-3">
              <label class="form--label">Other Platform Name</label>
              <input v-model="invite.other_platform_name" class="form--control" placeholder="e.g. Moj" />
            </div>
            <div class="col-md-6">
              <label class="form--label">Other Platform Link</label>
              <input v-model="invite.other_platform_link" class="form--control" placeholder="https://..." />
            </div>
            <div class="col-md-3 d-grid">
              <button class="ma-btn ma-btn--primary" type="button" :disabled="inviteLoading" @click="generateInvite">
                <i v-if="inviteLoading" class="fas fa-spinner fa-spin me-1"></i>
                <i v-else class="fas fa-wand-magic-sparkles me-1"></i>
                Generate Link
              </button>
            </div>
          </div>

          <div v-if="inviteResult.url" class="tw-mt-4 tw-bg-white/5 tw-border tw-border-white/10 tw-rounded-2xl tw-p-4">
            <div class="tw-flex tw-flex-col md:tw-flex-row tw-gap-2 tw-items-start md:tw-items-center tw-justify-between">
              <div>
                <div class="tw-text-white tw-font-black">Invite Link</div>
                <div class="tw-text-white/60 tw-text-xs">Lead Key: <span class="tw-text-white/80">{{ inviteResult.lead_key }}</span></div>
              </div>
            </div>
            <div class="tw-flex tw-gap-2 tw-mt-2 tw-flex-col md:tw-flex-row">
              <input id="inviteUrl" class="form--control tw-text-xs" :value="inviteResult.url" readonly />
              <button class="ma-btn" type="button" @click="copy('inviteUrl')">
                <i class="fas fa-copy me-1"></i> Copy
              </button>
              <a class="ma-btn" :href="inviteResult.url" target="_blank" rel="noopener noreferrer">
                <i class="fas fa-arrow-up-right-from-square me-1"></i> Open
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- APPLICATIONS -->
      <div v-if="tab === 'applications'" class="ma-card">
        <div class="ma-card__header">
          <div class="d-flex align-items-center justify-content-between w-100 gap-2 flex-wrap">
            <div>
              <h5 class="ma-card__title mb-1"><i class="fas fa-inbox me-2"></i>Applications & Draft Leads</h5>
              <p class="ma-card__subtitle mb-0">Drafts appear as users progress (even before final submit).</p>
            </div>
            <div class="tw-flex tw-gap-2 tw-flex-wrap">
              <input v-model="filters.q" class="form--control" style="min-width: 220px;" placeholder="Search name/phone/email/lead key" />
              <select v-model="filters.draft" class="form--control" style="width: 160px;">
                <option value="">All</option>
                <option value="1">Drafts only</option>
                <option value="0">Submitted only</option>
              </select>
              <select v-model="filters.status" class="form--control" style="width: 170px;">
                <option value="">All statuses</option>
                <option value="0">Pending</option>
                <option value="1">Contacted</option>
                <option value="2">Approved</option>
                <option value="3">Rejected</option>
              </select>
              <button class="ma-btn" type="button" @click="fetchRows">
                <i class="fas fa-filter me-1"></i> Apply
              </button>
            </div>
          </div>
        </div>

        <div class="ma-card__body">
          <div v-if="rows.length === 0" class="tw-text-center tw-py-10 tw-text-white/60">
            No entries found.
          </div>

          <div v-else class="tw-overflow-x-auto">
            <table class="table table--responsive--lg">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Type</th>
                  <th>Name</th>
                  <th>Phone</th>
                  <th>Platform</th>
                  <th>Payment</th>
                  <th>Status</th>
                  <th>Step</th>
                  <th>Screenshot</th>
                  <th>Created</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="r in rows" :key="r.id">
                  <td>#{{ r.id }}</td>
                  <td>
                    <span class="badge" :class="r.is_draft ? 'bg-warning text-dark' : 'bg-success'">
                      {{ r.is_draft ? 'Draft' : 'Submitted' }}
                    </span>
                  </td>
                  <td class="fw-bold">{{ r.name }}</td>
                  <td>{{ r.phone || '-' }}</td>
                  <td>{{ r.platform || '-' }}</td>
                  <td>{{ r.payment_method || '-' }}</td>
                  <td>
                    <span class="badge" :class="statusBadge(r.status)">{{ statusText(r.status) }}</span>
                  </td>
                  <td>{{ r.last_step || 0 }}/4</td>
                  <td>
                    <span class="badge" :class="r.has_screenshot ? 'bg-info' : 'bg-secondary'">
                      {{ r.has_screenshot ? 'Yes' : 'No' }}
                    </span>
                  </td>
                  <td class="small">{{ r.created_at }}</td>
                  <td>
                    <button class="ma-btn ma-btn--primary" type="button" @click="openDetail(r.id)">
                      View
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Detail Modal -->
          <div v-if="detail" class="tw-fixed tw-inset-0 tw-bg-black/60 tw-z-[2000] tw-flex tw-items-center tw-justify-center tw-p-4" @click.self="detail = null">
            <div class="tw-w-full tw-max-w-3xl tw-bg-slate-900 tw-border tw-border-white/10 tw-rounded-2xl tw-overflow-hidden">
              <div class="tw-p-4 tw-border-b tw-border-white/10 tw-flex tw-items-center tw-justify-between">
                <div class="tw-text-white tw-font-black">Application #{{ detail.id }}</div>
                <button class="tw-w-10 tw-h-10 tw-rounded-xl tw-border tw-border-white/10 tw-bg-white/5 tw-text-white" @click="detail = null">
                  <i class="fas fa-times"></i>
                </button>
              </div>
              <div class="tw-p-4 tw-space-y-4">
                <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-3">
                  <div class="tw-bg-white/5 tw-border tw-border-white/10 tw-rounded-xl tw-p-3">
                    <div class="tw-text-white/60 tw-text-xs">Lead Key</div>
                    <div class="tw-text-white tw-text-sm tw-font-bold tw-break-all">{{ detail.lead_key || '-' }}</div>
                  </div>
                  <div class="tw-bg-white/5 tw-border tw-border-white/10 tw-rounded-xl tw-p-3">
                    <div class="tw-text-white/60 tw-text-xs">Type</div>
                    <div class="tw-text-white tw-text-sm tw-font-bold">{{ detail.is_draft ? 'Draft' : 'Submitted' }}</div>
                  </div>
                  <div class="tw-bg-white/5 tw-border tw-border-white/10 tw-rounded-xl tw-p-3">
                    <div class="tw-text-white/60 tw-text-xs">Name</div>
                    <div class="tw-text-white tw-text-sm tw-font-bold">{{ detail.name }}</div>
                  </div>
                  <div class="tw-bg-white/5 tw-border tw-border-white/10 tw-rounded-xl tw-p-3">
                    <div class="tw-text-white/60 tw-text-xs">Phone</div>
                    <div class="tw-text-white tw-text-sm tw-font-bold">{{ detail.phone || '-' }}</div>
                  </div>
                </div>

                <div class="tw-bg-white/5 tw-border tw-border-white/10 tw-rounded-xl tw-p-3">
                  <div class="tw-flex tw-items-center tw-justify-between tw-gap-2 tw-flex-wrap">
                    <div>
                      <div class="tw-text-white/60 tw-text-xs">Status</div>
                      <div class="tw-text-white tw-text-sm tw-font-bold">{{ statusText(detail.status) }}</div>
                    </div>
                    <div class="tw-flex tw-gap-2 tw-flex-wrap">
                      <select v-model="detailEdit.status" class="form--control" style="width: 180px;">
                        <option value="0">Pending</option>
                        <option value="1">Contacted</option>
                        <option value="2">Approved</option>
                        <option value="3">Rejected</option>
                      </select>
                      <button class="ma-btn ma-btn--primary" type="button" @click="saveDetail" :disabled="savingDetail">
                        <i v-if="savingDetail" class="fas fa-spinner fa-spin me-1"></i>
                        Save
                      </button>
                      <a v-if="detail.screenshot_url" class="ma-btn" :href="detail.screenshot_url" target="_blank" rel="noopener noreferrer">
                        <i class="fas fa-image me-1"></i> Screenshot
                      </a>
                    </div>
                  </div>
                  <div class="tw-mt-3">
                    <label class="tw-text-white/60 tw-text-xs tw-font-bold">Admin Notes</label>
                    <textarea v-model="detailEdit.admin_notes" class="form--control" rows="3" placeholder="Notes..."></textarea>
                  </div>
                </div>

                <div class="tw-bg-black/30 tw-border tw-border-white/10 tw-rounded-xl tw-p-3">
                  <div class="tw-text-white/60 tw-text-xs tw-font-bold tw-mb-2">Data (JSON)</div>
                  <pre class="tw-text-white/80 tw-text-xs tw-whitespace-pre-wrap tw-break-words">{{ pretty(detail.data) }}</pre>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </MasterAdminLayout>
</template>

<script>
import { ref, reactive, onMounted } from 'vue'
import MasterAdminLayout from '../../components/master_admin/MasterAdminLayout.vue'
import api from '../../services/api'

export default {
  name: 'InfluencerProgram',
  components: { MasterAdminLayout },
  setup () {
    const tab = ref('applications')
    const settings = reactive({ apk_url: '', apk_version: '' })
    const uploadVersion = ref('')
    const uploading = ref(false)
    const apkFile = ref(null)

    const rows = ref([])
    const filters = reactive({ q: '', status: '', draft: '' })

    const invite = reactive({
      platform: '',
      instagram_handle: '',
      facebook_link: '',
      youtube_link: '',
      other_platform_name: '',
      other_platform_link: ''
    })
    const inviteLoading = ref(false)
    const inviteResult = reactive({ url: '', lead_key: '' })

    const detail = ref(null)
    const detailEdit = reactive({ status: '0', admin_notes: '' })
    const savingDetail = ref(false)

    const copy = (id) => {
      try {
        const el = document.getElementById(id)
        if (!el) return
        el.select()
        document.execCommand('copy')
      } catch (e) {}
    }

    const pretty = (obj) => {
      try { return JSON.stringify(obj || {}, null, 2) } catch (e) { return String(obj || '') }
    }

    const statusText = (s) => {
      const n = Number(s)
      if (n === 1) return 'Contacted'
      if (n === 2) return 'Approved'
      if (n === 3) return 'Rejected'
      return 'Pending'
    }
    const statusBadge = (s) => {
      const n = Number(s)
      if (n === 1) return 'bg-primary'
      if (n === 2) return 'bg-success'
      if (n === 3) return 'bg-danger'
      return 'bg-secondary'
    }

    const fetchSettings = async () => {
      const res = await api.get('/admin/influencer/settings')
      const d = res?.data?.data || {}
      settings.apk_url = d.apk_url || ''
      settings.apk_version = d.apk_version || ''
    }

    const uploadApk = async () => {
      const file = apkFile.value?.files?.[0]
      if (!file) {
        window.notify?.('error', 'Please choose an APK file.')
        return
      }
      uploading.value = true
      try {
        const fd = new FormData()
        fd.append('apk', file)
        if (uploadVersion.value) fd.append('apk_version', uploadVersion.value)
        const res = await api.post('/admin/influencer/settings/apk', fd, {
          headers: { 'Content-Type': 'multipart/form-data' }
        })
        const d = res?.data?.data || {}
        settings.apk_url = d.apk_url || settings.apk_url
        settings.apk_version = d.apk_version || settings.apk_version
        uploadVersion.value = ''
        if (apkFile.value) apkFile.value.value = ''
        window.notify?.('success', 'APK uploaded.')
      } catch (e) {
        window.notify?.('error', 'APK upload failed.')
      } finally {
        uploading.value = false
      }
    }

    const fetchRows = async () => {
      const res = await api.get('/admin/influencer/applications', { params: { ...filters } })
      rows.value = res?.data?.data?.rows || []
    }

    const generateInvite = async () => {
      inviteLoading.value = true
      try {
        const res = await api.post('/admin/influencer/invite', invite)
        inviteResult.url = res?.data?.data?.url || ''
        inviteResult.lead_key = res?.data?.data?.lead_key || ''
      } catch (e) {
        window.notify?.('error', 'Invite link failed.')
      } finally {
        inviteLoading.value = false
      }
    }

    const openDetail = async (id) => {
      const res = await api.get(`/admin/influencer/applications/${id}`)
      detail.value = res?.data?.data || null
      detailEdit.status = String(detail.value?.status ?? '0')
      detailEdit.admin_notes = String(detail.value?.admin_notes ?? '')
    }

    const saveDetail = async () => {
      if (!detail.value) return
      savingDetail.value = true
      try {
        await api.post(`/admin/influencer/applications/${detail.value.id}`, {
          status: Number(detailEdit.status),
          admin_notes: detailEdit.admin_notes
        })
        window.notify?.('success', 'Saved.')
        await openDetail(detail.value.id)
        await fetchRows()
      } catch (e) {
        window.notify?.('error', 'Save failed.')
      } finally {
        savingDetail.value = false
      }
    }

    const refresh = async () => {
      await Promise.all([fetchSettings(), fetchRows()])
    }

    onMounted(async () => {
      await refresh()
    })

    return {
      tab,
      settings,
      uploadVersion,
      uploading,
      apkFile,
      rows,
      filters,
      invite,
      inviteLoading,
      inviteResult,
      detail,
      detailEdit,
      savingDetail,
      copy,
      pretty,
      statusText,
      statusBadge,
      fetchRows,
      fetchSettings,
      uploadApk,
      generateInvite,
      openDetail,
      saveDetail,
      refresh
    }
  }
}
</script>

