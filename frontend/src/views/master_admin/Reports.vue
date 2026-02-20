<template>
  <MasterAdminLayout page-title="Reports">
    <div class="ma-reports">
      <div class="ma-card">
        <div class="ma-card__header ma-card__header--gradient">
          <div>
            <h5 class="ma-card__title"><i class="fas fa-chart-bar me-2"></i>Reports & Analytics</h5>
            <p class="ma-card__subtitle">View detailed reports and analytics</p>
          </div>
        </div>
        <div class="ma-card__body">
          <p class="text-center text-muted">Reports page - Coming soon</p>
        </div>
      </div>
    </div>
  </MasterAdminLayout>
</template>

<script>
import MasterAdminLayout from '../../components/master_admin/MasterAdminLayout.vue'

export default {
  name: 'MasterAdminReports',
  components: { MasterAdminLayout }
}
</script>

<style scoped>
.ma-reports { animation: maFade 0.4s ease-out; }
@keyframes maFade { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
</style>
