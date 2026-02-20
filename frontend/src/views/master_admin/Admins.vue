<template>
  <MasterAdminLayout page-title="Admins">
    <div class="master-page-card">
      <h5 class="mb-3">Manage Admins</h5>
      <p class="text-muted mb-0">Admin management section. Add backend API and list/create/edit admins here.</p>
    </div>
  </MasterAdminLayout>
</template>

<script>
import MasterAdminLayout from '../../components/master_admin/MasterAdminLayout.vue'
export default {
  name: 'MasterAdminAdmins',
  components: { MasterAdminLayout }
}
</script>

<style scoped>
.master-page-card {
  background: rgba(30, 41, 59, 0.6);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 16px;
  padding: 24px;
}
.master-page-card h5 { color: #f1f5f9; }
.master-page-card .text-muted { color: #94a3b8 !important; }
</style>
