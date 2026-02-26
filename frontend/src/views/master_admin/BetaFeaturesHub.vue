<template>
  <MasterAdminLayout>
    <div class="beta-hub">
      <div class="tw-flex tw-items-center tw-justify-between tw-mb-8">
        <div>
          <h2 class="tw-text-2xl tw-font-bold tw-text-white tw-flex tw-items-center tw-gap-3">
            <i class="fas fa-vial tw-text-indigo-400"></i> Beta Features Hub
          </h2>
          <p class="tw-text-slate-400 tw-mt-1">Manage experimental features visible only to Admin/Beta testers.</p>
        </div>
        <div class="tw-bg-amber-500/10 tw-text-amber-500 tw-px-4 tw-py-2 tw-rounded-xl tw-border tw-border-amber-500/20 tw-text-xs tw-font-bold">
          <i class="fas fa-exclamation-triangle tw-mr-1"></i> ADMIN ONLY VIEW
        </div>
      </div>

      <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 lg:tw-grid-cols-3 xl:tw-grid-cols-4 tw-gap-4 md:tw-gap-6">
        <!-- Point #1 -->
        <div class="beta-card">
          <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
            <div class="tw-w-10 tw-h-10 tw-bg-indigo-500/10 tw-rounded-xl tw-flex tw-items-center tw-justify-center tw-text-indigo-400">
              <i class="fas fa-crown"></i>
            </div>
            <span class="tw-text-[10px] tw-font-bold tw-text-slate-600 tw-uppercase">Point #1</span>
          </div>
          <h3 class="tw-text-white tw-font-bold">VIP Membership</h3>
          <p class="tw-text-xs tw-text-slate-400 tw-mt-2">Monthly subscription for zero fee and priority payouts.</p>
          <div class="tw-mt-6 tw-flex tw-items-center tw-justify-between">
            <span class="tw-text-xs tw-font-bold tw-text-emerald-500 uppercase">Live: Beta</span>
            <router-link to="/master_admin/beta-features/vip" class="beta-btn">Configure</router-link>
          </div>
        </div>

        <!-- Point #2 -->
        <div class="beta-card">
          <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
            <div class="tw-w-10 tw-h-10 tw-bg-blue-500/10 tw-rounded-xl tw-flex tw-items-center tw-justify-center tw-text-blue-400">
              <i class="fas fa-user-tie"></i>
            </div>
            <span class="tw-text-[10px] tw-font-bold tw-text-slate-600 tw-uppercase">Point #2</span>
          </div>
          <h3 class="tw-text-white tw-font-bold">Agent License</h3>
          <p class="tw-text-xs tw-text-slate-400 tw-mt-2">Professional Agent Plan for advanced high earners.</p>
          <div class="tw-mt-6 tw-flex tw-items-center tw-justify-between">
            <span class="tw-text-xs tw-font-bold tw-text-slate-500 uppercase">Status: Designing</span>
            <router-link to="/master_admin/beta-features/extra" class="beta-btn">Configure</router-link>
          </div>
        </div>

        <!-- Point #3 -->
        <div class="beta-card">
          <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
            <div class="tw-w-10 tw-h-10 tw-bg-emerald-500/10 tw-rounded-xl tw-flex tw-items-center tw-justify-center tw-text-emerald-400">
              <i class="fas fa-bolt"></i>
            </div>
            <span class="tw-text-[10px] tw-font-bold tw-text-slate-600 tw-uppercase">Point #3</span>
          </div>
          <h3 class="tw-text-white tw-font-bold">Instant Payout</h3>
          <p class="tw-text-xs tw-text-slate-400 tw-mt-2">Charge extra fees for immediate withdrawals.</p>
          <div class="tw-mt-6 tw-flex tw-items-center tw-justify-between">
            <span class="tw-text-xs tw-font-bold tw-text-emerald-500 uppercase">Live: Beta</span>
            <router-link to="/master_admin/beta-features/instant" class="beta-btn">Configure</router-link>
          </div>
        </div>

        <!-- Point #4 -->
        <div class="beta-card">
          <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
            <div class="tw-w-10 tw-h-10 tw-bg-purple-500/10 tw-rounded-xl tw-flex tw-items-center tw-justify-center tw-text-purple-400">
              <i class="fas fa-arrow-up"></i>
            </div>
            <span class="tw-text-[10px] tw-font-bold tw-text-slate-600 tw-uppercase">Point #4</span>
          </div>
          <h3 class="tw-text-white tw-font-bold">Limit Upgrade</h3>
          <p class="tw-text-xs tw-text-slate-400 tw-mt-2">Purchasable withdrawal daily limit increase pass.</p>
          <div class="tw-mt-6 tw-flex tw-items-center tw-justify-between">
            <span class="tw-text-xs tw-font-bold tw-text-slate-500 uppercase">Status: Designing</span>
            <router-link to="/master_admin/beta-features/extra" class="beta-btn">Configure</router-link>
          </div>
        </div>

        <!-- Point #5 -->
        <div class="beta-card">
          <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
            <div class="tw-w-10 tw-h-10 tw-bg-amber-500/10 tw-rounded-xl tw-flex tw-items-center tw-justify-center tw-text-amber-400">
              <i class="fas fa-calendar-check"></i>
            </div>
            <span class="tw-text-[10px] tw-font-bold tw-text-slate-600 tw-uppercase">Point #5</span>
          </div>
          <h3 class="tw-text-white tw-font-bold">Withdrawal Pass</h3>
          <p class="tw-text-xs tw-text-slate-400 tw-mt-2">Fee-free withdrawal for 6 months (One-time pass).</p>
          <div class="tw-mt-6 tw-flex tw-items-center tw-justify-between">
            <span class="tw-text-xs tw-font-bold tw-text-slate-500 uppercase">Status: Designing</span>
            <router-link to="/master_admin/beta-features/extra" class="beta-btn">Configure</router-link>
          </div>
        </div>

        <!-- Point #6 -->
        <div class="beta-card">
          <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
            <div class="tw-w-10 tw-h-10 tw-bg-blue-600/10 tw-rounded-xl tw-flex tw-items-center tw-justify-center tw-text-blue-500">
              <i class="fas fa-check-circle"></i>
            </div>
            <span class="tw-text-[10px] tw-font-bold tw-text-slate-600 tw-uppercase">Point #6</span>
          </div>
          <h3 class="tw-text-white tw-font-bold">Verified Badge</h3>
          <p class="tw-text-xs tw-text-slate-400 tw-mt-2">Blue tick for referral trust expansion.</p>
          <div class="tw-mt-6 tw-flex tw-items-center tw-justify-between">
            <span class="tw-text-xs tw-font-bold tw-text-emerald-500 uppercase">Live: Beta</span>
            <router-link to="/master_admin/beta-features/verified" class="beta-btn">Configure</router-link>
          </div>
        </div>

        <!-- Point #7 -->
        <div class="beta-card">
          <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
            <div class="tw-w-10 tw-h-10 tw-bg-pink-500/10 tw-rounded-xl tw-flex tw-items-center tw-justify-center tw-text-pink-400">
              <i class="fas fa-shipping-fast"></i>
            </div>
            <span class="tw-text-[10px] tw-font-bold tw-text-slate-600 tw-uppercase">Point #7</span>
          </div>
          <h3 class="tw-text-white tw-font-bold">KYC Fast Track</h3>
          <p class="tw-text-xs tw-text-slate-400 tw-mt-2">Skip the queue for ₹249–₹399. Approval in 60m.</p>
          <div class="tw-mt-6 tw-flex tw-items-center tw-justify-between">
            <span class="tw-text-xs tw-font-bold tw-text-slate-500 uppercase">Status: Designing</span>
            <router-link to="/master_admin/beta-features/extra" class="beta-btn">Configure</router-link>
          </div>
        </div>

        <!-- Point #8 -->
        <div class="beta-card">
          <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
            <div class="tw-w-10 tw-h-10 tw-bg-red-500/10 tw-rounded-xl tw-flex tw-items-center tw-justify-center tw-text-red-400">
              <i class="fas fa-redo-alt"></i>
            </div>
            <span class="tw-text-[10px] tw-font-bold tw-text-slate-600 tw-uppercase">Point #8</span>
          </div>
          <h3 class="tw-text-white tw-font-bold">Account Reactival</h3>
          <p class="tw-text-xs tw-text-slate-400 tw-mt-2">Recovery fee (₹199–₹500) for inactive users.</p>
          <div class="tw-mt-6 tw-flex tw-items-center tw-justify-between">
            <span class="tw-text-xs tw-font-bold tw-text-slate-500 uppercase">Status: Designing</span>
            <router-link to="/master_admin/beta-features/extra" class="beta-btn">Configure</router-link>
          </div>
        </div>

        <!-- Point #9 -->
        <div class="beta-card">
          <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
            <div class="tw-w-10 tw-h-10 tw-bg-orange-500/10 tw-rounded-xl tw-flex tw-items-center tw-justify-center tw-text-orange-400">
              <i class="fas fa-rocket"></i>
            </div>
            <span class="tw-text-[10px] tw-font-bold tw-text-slate-600 tw-uppercase">Point #9</span>
          </div>
          <h3 class="tw-text-white tw-font-bold">Ad Booster</h3>
          <p class="tw-text-xs tw-text-slate-400 tw-mt-2">Daily/Weekly pass to increase ad limits.</p>
          <div class="tw-mt-6 tw-flex tw-items-center tw-justify-between">
            <span class="tw-text-xs tw-font-bold tw-text-emerald-500 uppercase">Live: Beta</span>
            <router-link to="/master_admin/beta-features/booster" class="beta-btn">Configure</router-link>
          </div>
        </div>

        <!-- Point #10 -->
        <div class="beta-card">
          <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
            <div class="tw-w-10 tw-h-10 tw-bg-cyan-500/10 tw-rounded-xl tw-flex tw-items-center tw-justify-center tw-text-cyan-400">
              <i class="fas fa-chart-line"></i>
            </div>
            <span class="tw-text-[10px] tw-font-bold tw-text-slate-600 tw-uppercase">Point #10</span>
          </div>
          <h3 class="tw-text-white tw-font-bold">Profit Booster</h3>
          <p class="tw-text-xs tw-text-slate-400 tw-mt-2">Temporary earning boost (1.2x – 1.5x) for 7 days.</p>
          <div class="tw-mt-6 tw-flex tw-items-center tw-justify-between">
            <span class="tw-text-xs tw-font-bold tw-text-slate-500 uppercase">Status: Designing</span>
            <router-link to="/master_admin/beta-features/extra" class="beta-btn">Configure</router-link>
          </div>
        </div>

        <!-- Point #11 -->
        <div class="beta-card">
           <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
            <div class="tw-w-10 tw-h-10 tw-bg-pink-500/10 tw-rounded-xl tw-flex tw-items-center tw-justify-center tw-text-pink-400">
              <i class="fas fa-users-cog"></i>
            </div>
            <span class="tw-text-[10px] tw-font-bold tw-text-slate-600 tw-uppercase">Point #11</span>
          </div>
          <h3 class="tw-text-white tw-font-bold">Comm. Booster</h3>
          <p class="tw-text-xs tw-text-slate-400 tw-mt-2">Extra 5–10% commission from team for 30 days.</p>
          <div class="tw-mt-6 tw-flex tw-items-center tw-justify-between">
            <span class="tw-text-xs tw-font-bold tw-text-slate-500 uppercase">Status: Designing</span>
            <router-link to="/master_admin/beta-features/extra" class="beta-btn">Configure</router-link>
          </div>
        </div>

        <!-- Point #12 -->
        <div class="beta-card">
          <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
            <div class="tw-w-10 tw-h-10 tw-bg-indigo-600/10 tw-rounded-xl tw-flex tw-items-center tw-justify-center tw-text-indigo-400">
              <i class="fas fa-layer-group"></i>
            </div>
            <span class="tw-text-[10px] tw-font-bold tw-text-slate-600 tw-uppercase">Point #12</span>
          </div>
          <h3 class="tw-text-white tw-font-bold">Level Unlock</h3>
          <p class="tw-text-xs tw-text-slate-400 tw-mt-2">Unlock Level 2 & 3 passive income access.</p>
          <div class="tw-mt-6 tw-flex tw-items-center tw-justify-between">
            <span class="tw-text-xs tw-font-bold tw-text-slate-500 uppercase">Status: Designing</span>
            <router-link to="/master_admin/beta-features/extra" class="beta-btn">Configure</router-link>
          </div>
        </div>

        <!-- Point #13 -->
        <div class="beta-card">
           <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
            <div class="tw-w-10 tw-h-10 tw-bg-emerald-600/10 tw-rounded-xl tw-flex tw-items-center tw-justify-center tw-text-emerald-400">
              <i class="fas fa-shield-alt"></i>
            </div>
            <span class="tw-text-[10px] tw-font-bold tw-text-slate-600 tw-uppercase">Point #13</span>
          </div>
          <h3 class="tw-text-white tw-font-bold">Senior Agent</h3>
          <p class="tw-text-xs tw-text-slate-400 tw-mt-2">Advanced passive structure for team building.</p>
          <div class="tw-mt-6 tw-flex tw-items-center tw-justify-between">
            <span class="tw-text-xs tw-font-bold tw-text-slate-500 uppercase">Status: Designing</span>
            <router-link to="/master_admin/beta-features/extra" class="beta-btn">Configure</router-link>
          </div>
        </div>

        <!-- Point #14 -->
        <div class="beta-card">
          <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
            <div class="tw-w-10 tw-h-10 tw-bg-orange-600/10 tw-rounded-xl tw-flex tw-items-center tw-justify-center tw-text-orange-500">
              <i class="fas fa-box-open"></i>
            </div>
            <span class="tw-text-[10px] tw-font-bold tw-text-slate-600 tw-uppercase">Point #14</span>
          </div>
          <h3 class="tw-text-white tw-font-bold">Toolkit Store</h3>
          <p class="tw-text-xs tw-text-slate-400 tw-mt-2">Purchase ready-made banners & sales scripts.</p>
          <div class="tw-mt-6 tw-flex tw-items-center tw-justify-between">
            <span class="tw-text-xs tw-font-bold tw-text-slate-500 uppercase">Status: Designing</span>
            <router-link to="/master_admin/beta-features/extra" class="beta-btn">Configure</router-link>
          </div>
        </div>

        <!-- Point #15 -->
        <div class="beta-card">
          <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
            <div class="tw-w-10 tw-h-10 tw-bg-violet-600/10 tw-rounded-xl tw-flex tw-items-center tw-justify-center tw-text-violet-500">
              <i class="fas fa-video"></i>
            </div>
            <span class="tw-text-[10px] tw-font-bold tw-text-slate-600 tw-uppercase">Point #15</span>
          </div>
          <h3 class="tw-text-white tw-font-bold">Masterclass</h3>
          <p class="tw-text-xs tw-text-slate-400 tw-mt-2">Special earning / affiliate masterclasses.</p>
          <div class="tw-mt-6 tw-flex tw-items-center tw-justify-between">
            <span class="tw-text-xs tw-font-bold tw-text-slate-500 uppercase">Status: Designing</span>
            <router-link to="/master_admin/beta-features/extra" class="beta-btn">Configure</router-link>
          </div>
        </div>

        <!-- Point #16 -->
        <div class="beta-card">
          <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
            <div class="tw-w-10 tw-h-10 tw-bg-red-600/10 tw-rounded-xl tw-flex tw-items-center tw-justify-center tw-text-red-500">
              <i class="fas fa-key"></i>
            </div>
            <span class="tw-text-[10px] tw-font-bold tw-text-slate-600 tw-uppercase">Point #16</span>
          </div>
          <h3 class="tw-text-white tw-font-bold">Elite Training</h3>
          <p class="tw-text-xs tw-text-slate-400 tw-mt-2">Locked advanced earning videos.</p>
          <div class="tw-mt-6 tw-flex tw-items-center tw-justify-between">
            <span class="tw-text-xs tw-font-bold tw-text-slate-500 uppercase">Status: Designing</span>
            <router-link to="/master_admin/beta-features/extra" class="beta-btn">Configure</router-link>
          </div>
        </div>

        <!-- Point #17 -->
        <div class="beta-card">
          <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
            <div class="tw-w-10 tw-h-10 tw-bg-purple-600/10 tw-rounded-xl tw-flex tw-items-center tw-justify-center tw-text-purple-400">
              <i class="fas fa-star"></i>
            </div>
            <span class="tw-text-[10px] tw-font-bold tw-text-slate-600 tw-uppercase">Point #17</span>
          </div>
          <h3 class="tw-text-white tw-font-bold">Featured Partner</h3>
          <p class="tw-text-xs tw-text-slate-400 tw-mt-2">Display profile on public page for more referrals.</p>
          <div class="tw-mt-6 tw-flex tw-items-center tw-justify-between">
            <span class="tw-text-xs tw-font-bold tw-text-slate-500 uppercase">Status: Designing</span>
            <router-link to="/master_admin/beta-features/extra" class="beta-btn">Configure</router-link>
          </div>
        </div>
      </div>
    </div>
  </MasterAdminLayout>
</template>

<script>
import MasterAdminLayout from '../../components/master_admin/MasterAdminLayout.vue'

export default {
  name: 'BetaFeaturesHub',
  components: { MasterAdminLayout }
}
</script>

<style scoped>
.beta-card {
  background: rgba(30, 41, 59, 0.4);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 24px;
  padding: 24px;
  transition: all 0.3s ease;
}
.beta-card:hover:not(.is-locked) {
  transform: translateY(-5px);
  border-color: rgba(99, 102, 241, 0.3);
  background: rgba(30, 41, 59, 0.6);
}
.beta-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
}
.status-badge {
  font-size: 10px;
  font-weight: 800;
  padding: 4px 10px;
  border-radius: 100px;
  background: rgba(255,255,255,0.05);
  color: #64748b;
}
.status-badge.active {
  background: rgba(16, 185, 129, 0.1);
  color: #10b981;
}
.beta-btn {
  background: rgba(99, 102, 241, 0.1);
  border: 1px solid rgba(99, 102, 241, 0.2);
  color: #818cf8;
  padding: 6px 16px;
  border-radius: 10px;
  font-size: 0.8rem;
  font-weight: 700;
  text-decoration: none;
  transition: 0.2s;
}
.beta-btn:hover {
  background: #6366f1;
  color: white;
}
.is-locked {
  opacity: 0.6;
  filter: grayscale(1);
}
</style>
