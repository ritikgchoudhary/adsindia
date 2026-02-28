<template>
  <DashboardLayout page-title="Dashboard" :dark-theme="true">
    <div class="tw-w-full tw-min-h-[400px] tw-pb-8">
      
      <!-- KYC Alerts -->
      <div v-if="user.kv === 0 && user.kyc_rejection_reason" class="tw-mb-6">
        <div class="tw-bg-rose-500/10 tw-border tw-border-rose-500/20 tw-border-l-[4px] tw-border-l-rose-500 tw-rounded-2xl tw-p-4 sm:tw-p-5 tw-shadow-sm">
          <div class="tw-flex tw-flex-col sm:tw-flex-row sm:tw-items-center tw-gap-3 sm:tw-gap-4">
            <div class="tw-flex tw-items-center tw-gap-3 tw-mb-2 sm:tw-mb-0">
              <div class="tw-w-10 tw-h-10 sm:tw-w-12 sm:tw-h-12 tw-rounded-xl tw-bg-rose-500/20 tw-flex tw-items-center tw-justify-center tw-flex-shrink-0">
                <i class="fa-solid fa-circle-exclamation tw-text-rose-400 tw-text-xl sm:tw-text-2xl"></i>
              </div>
              <h4 class="tw-text-white tw-font-extrabold tw-text-base sm:tw-text-xl tw-m-0 sm:tw-hidden">KYC Rejected</h4>
            </div>
            
            <div class="tw-flex-1 tw-min-w-0">
              <h4 class="tw-text-white tw-font-extrabold tw-text-lg sm:tw-text-xl tw-mb-1 tw-hidden sm:tw-block">KYC Documents Rejected</h4>
              <p class="tw-text-slate-300 tw-font-medium tw-leading-snug sm:tw-leading-relaxed tw-text-xs sm:tw-text-sm tw-mb-2 sm:tw-mb-0">
                {{ kycContent?.reject || 'Your KYC documents have been rejected. Please review the reason and resubmit.' }}
              </p>
            </div>
            
            <div class="tw-flex tw-items-center tw-gap-2 tw-mt-2 sm:tw-mt-0">
              <button 
                class="tw-flex-1 sm:tw-flex-none tw-inline-flex tw-items-center tw-justify-center tw-gap-2 tw-px-4 tw-py-2 tw-bg-indigo-600/20 hover:tw-bg-indigo-600/40 tw-text-indigo-400 tw-border tw-border-indigo-500/30 tw-font-semibold tw-text-xs sm:tw-text-sm tw-rounded-lg tw-transition-colors tw-cursor-pointer" 
                @click="showKYCRejectionModal = true"
              >
                <i class="fas fa-info-circle"></i> Reason
              </button>
              <router-link 
                to="/user/account-kyc?tab=documents"
                class="tw-flex-1 sm:tw-flex-none tw-inline-flex tw-items-center tw-justify-center tw-gap-2 tw-px-4 tw-py-2 tw-bg-rose-600 hover:tw-bg-rose-700 tw-text-white tw-font-semibold tw-text-xs sm:tw-text-sm tw-rounded-lg tw-transition-colors tw-no-underline tw-border-0"
              >
                <i class="fas fa-redo"></i> Re-submit
              </router-link>
            </div>
          </div>
        </div>
      </div>

      <div v-else-if="user.kv === 2" class="tw-mb-6">
        <div class="tw-bg-amber-500/10 tw-border tw-border-amber-500/20 tw-border-l-[4px] tw-border-l-amber-500 tw-rounded-2xl tw-p-4 sm:tw-p-5 tw-shadow-sm">
          <div class="tw-flex tw-flex-col sm:tw-flex-row sm:tw-items-center tw-gap-3 sm:tw-gap-4">
            <div class="tw-flex tw-items-center tw-gap-3 tw-mb-2 sm:tw-mb-0">
              <div class="tw-w-10 tw-h-10 sm:tw-w-12 sm:tw-h-12 tw-rounded-xl tw-bg-amber-500/20 tw-flex tw-items-center tw-justify-center tw-flex-shrink-0">
                <i class="fas fa-exclamation-triangle tw-text-amber-500 tw-text-xl sm:tw-text-2xl"></i>
              </div>
              <h4 class="tw-text-white tw-font-extrabold tw-text-base sm:tw-text-xl tw-m-0 sm:tw-hidden">KYC Pending</h4>
            </div>
            
            <div class="tw-flex-1 tw-min-w-0">
              <h4 class="tw-text-white tw-font-extrabold tw-text-lg sm:tw-text-xl tw-mb-1 tw-hidden sm:tw-block">KYC Verification Pending</h4>
              <p class="tw-text-slate-300 tw-font-medium tw-leading-snug sm:tw-leading-relaxed tw-text-xs sm:tw-text-sm tw-mb-2 sm:tw-mb-0 tw-max-w-full">
                {{ kycContent?.pending || 'Your KYC verification is being reviewed. We might need some additional information. You will get an email update soon.' }}
              </p>
            </div>
            
            <div class="tw-flex tw-items-center tw-gap-2 tw-mt-2 sm:tw-mt-0">
              <router-link 
                to="/user/account-kyc" 
                class="tw-flex-1 sm:tw-flex-none tw-inline-flex tw-items-center tw-justify-center tw-gap-2 tw-px-4 tw-py-2 tw-bg-amber-500 hover:tw-bg-amber-600 tw-text-white tw-font-semibold tw-text-xs sm:tw-text-sm tw-rounded-lg tw-transition-colors tw-no-underline tw-border-0"
              >
                <i class="fas fa-eye"></i> View KYC
              </router-link>
            </div>
          </div>
        </div>
      </div>

      <!-- New Profile & Earnings Section -->
      <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-3 tw-gap-4 md:tw-gap-8 tw-mb-6 md:tw-mb-8">
        <!-- Premium Beta Profile Card -->
        <div v-if="user.beta_access" class="lg:tw-col-span-1 border-glow-anim">
          <div class="tw-h-full tw-bg-[#0f172a] tw-rounded-[32px] tw-p-6 sm:tw-p-8 tw-text-center tw-shadow-[0_0_30px_rgba(6,182,212,0.15)] tw-border-2 tw-border-cyan-500/30 hover:tw-border-cyan-400/60 tw-transition-all tw-duration-500 tw-flex tw-flex-col tw-items-center tw-justify-center tw-relative tw-overflow-hidden">

            <!-- High-tech subtle dot background & glow -->
            <div class="tw-absolute tw-inset-0 tw-opacity-[0.03]" style="background-image: radial-gradient(#38bdf8 1px, transparent 1px); background-size: 20px 20px;"></div>
            <div class="tw-absolute -tw-top-20 -tw-left-20 tw-w-64 tw-h-64 tw-bg-cyan-500 tw-rounded-full tw-blur-[100px] tw-opacity-20 tw-pointer-events-none"></div>
            <div class="tw-absolute -tw-bottom-20 -tw-right-20 tw-w-64 tw-h-64 tw-bg-blue-600 tw-rounded-full tw-blur-[100px] tw-opacity-20 tw-pointer-events-none"></div>

            <!-- UID -->
            <h2 class="tw-text-slate-200 tw-font-bold tw-text-xl sm:tw-text-2xl tw-mb-4 tw-uppercase tw-tracking-[0.2em] tw-flex tw-items-center tw-justify-center tw-gap-2 tw-relative tw-z-10">
              <i class="fas fa-fingerprint tw-text-cyan-400 tw-text-sm tw-opacity-70"></i>
              {{ referralCode || user.uid || user.id || 'USER' }}
            </h2>
            
            <!-- Profile Image -->
            <div class="tw-relative tw-mb-6 tw-z-10 tw-mt-2">
              <div class="tw-w-28 tw-h-28 sm:tw-w-44 sm:tw-h-44 tw-rounded-full tw-border-[2px] tw-border-cyan-500/50 tw-p-1 tw-mx-auto tw-bg-transparent tw-flex tw-items-center tw-justify-center tw-relative">
                <div class="tw-absolute tw-inset-0 tw-rounded-full tw-border tw-border-cyan-500/20 tw-animate-ping tw-opacity-20"></div>
                <div class="tw-w-full tw-h-full tw-rounded-full tw-overflow-hidden tw-bg-slate-900 shadow-inner">
                  <img 
                    v-if="user.image"
                    :src="user.image" 
                    alt="Profile" 
                    class="tw-w-full tw-h-full tw-object-cover"
                  >
                  <img 
                    v-else
                    :src="profileAvatarUrl" 
                    alt="Profile" 
                    class="tw-w-full tw-h-full tw-object-cover tw-opacity-90 grayscale-[20%]"
                  >
                </div>
              </div>
              <!-- Green status dot -->
              <div class="tw-absolute tw-bottom-2 tw-right-2 sm:tw-bottom-3 sm:tw-right-3 tw-bg-emerald-400 tw-w-4 tw-h-4 sm:tw-w-5 sm:tw-h-5 tw-rounded-full tw-border-[3px] tw-border-[#0f172a] tw-shadow-[0_0_10px_rgba(52,211,153,0.5)]" title="Active"></div>
            </div>

            <!-- Full Name -->
            <h1 class="tw-text-white tw-font-black tw-text-2xl sm:tw-text-3xl tw-mb-6 tw-drop-shadow-lg tw-leading-tight tw-relative tw-z-10">
              {{ displayName }}
            </h1>

            <!-- Badge -->
            <div class="tw-relative tw-inline-block tw-z-10">
              <div class="tw-bg-gradient-to-r tw-from-cyan-500 tw-to-blue-600 tw-text-white tw-px-10 tw-py-3 tw-rounded-xl tw-font-bold tw-text-xs sm:tw-text-sm tw-shadow-[0_10px_20px_-10px_rgba(6,182,212,0.5)] tw-tracking-[0.15em] tw-uppercase tw-border tw-border-white/10">
                ADS SKILL INDIA
              </div>
            </div>
          </div>
        </div>

        <!-- Classic Profile Card (Live / Non-Beta) -->
        <div v-else class="lg:tw-col-span-1">
          <div class="tw-h-full tw-bg-gradient-to-b tw-from-yellow-400 tw-to-yellow-600 tw-rounded-[32px] tw-p-6 sm:tw-p-8 tw-text-center tw-shadow-2xl tw-border-2 tw-border-white/50 tw-flex tw-flex-col tw-items-center tw-justify-center">
            <!-- UID -->
            <h2 class="tw-text-white tw-font-bold tw-text-xl sm:tw-text-2xl tw-mb-4 tw-drop-shadow-lg tw-uppercase tw-tracking-widest">
              {{ referralCode || user.uid || user.id || 'USER' }}
            </h2>
            
            <!-- Profile Image -->
            <div class="tw-relative tw-mb-6">
              <div class="tw-w-28 tw-h-28 sm:tw-w-44 sm:tw-h-44 tw-rounded-full tw-border-[4px] sm:tw-border-[6px] tw-border-white tw-shadow-xl tw-overflow-hidden tw-mx-auto tw-bg-indigo-500 tw-flex tw-items-center tw-justify-center">
                <img 
                  v-if="user.image"
                  :src="user.image" 
                  alt="Profile" 
                  class="tw-w-full tw-h-full tw-object-cover"
                >
                <img 
                  v-else
                  :src="profileAvatarUrl" 
                  alt="Profile" 
                  class="tw-w-full tw-h-full tw-object-cover"
                >
              </div>
              <div class="tw-absolute tw-bottom-2 tw-right-2 tw-bg-green-500 tw-w-4 tw-h-4 tw-rounded-full tw-border-[2px] tw-border-white tw-shadow-lg" title="Active"></div>
            </div>

            <!-- Full Name -->
            <h1 class="tw-text-white tw-font-bold tw-text-xl sm:tw-text-3xl tw-mb-6 tw-drop-shadow-xl tw-leading-tight">
              {{ displayName }}
            </h1>

            <!-- Badge -->
            <div class="tw-bg-gradient-to-r tw-from-blue-600 tw-to-blue-500 tw-text-white tw-px-8 tw-py-2.5 tw-rounded-xl tw-font-bold tw-text-xs sm:tw-text-sm tw-shadow-xl tw-tracking-widest tw-uppercase">
              ADS SKILL INDIA
            </div>
          </div>
        </div>

        <!-- Earnings Stack -->
        <div class="lg:tw-col-span-2 tw-flex tw-flex-col tw-gap-2 sm:tw-gap-4">
          <!-- Today Earnings -->
          <div class="tw-bg-gradient-to-r tw-from-cyan-400 tw-to-cyan-500 tw-p-3 sm:tw-p-5 tw-rounded-xl sm:tw-rounded-2xl tw-flex tw-items-center tw-justify-between tw-text-white tw-shadow-lg tw-relative tw-overflow-hidden">
            <div class="tw-text-2xl sm:tw-text-4xl tw-font-serif tw-opacity-40">₹</div>
            <div class="tw-text-right">
              <div class="tw-text-xl sm:tw-text-3xl tw-font-black tw-leading-none">₹{{ formatAmount(earnTodayAnim) }}</div>
              <div class="tw-text-[8px] sm:tw-text-[10px] tw-font-bold tw-uppercase tw-tracking-widest tw-mt-1 sm:tw-mt-2 tw-opacity-90">Today's Earning</div>
            </div>
          </div>

          <!-- 7 Days Earnings -->
          <div class="tw-bg-gradient-to-r tw-from-blue-500 tw-to-blue-600 tw-p-3 sm:tw-p-5 tw-rounded-xl sm:tw-rounded-2xl tw-flex tw-items-center tw-justify-between tw-text-white tw-shadow-lg tw-relative tw-overflow-hidden">
            <div class="tw-text-2xl sm:tw-text-4xl tw-font-serif tw-opacity-40">₹</div>
            <div class="tw-text-right">
              <div class="tw-text-xl sm:tw-text-3xl tw-font-black tw-leading-none">₹{{ formatAmount(earnLast7DaysAnim) }}</div>
              <div class="tw-text-[8px] sm:tw-text-[10px] tw-font-bold tw-uppercase tw-tracking-widest tw-mt-1 sm:tw-mt-2 tw-opacity-90">Last 7 Days Earning</div>
            </div>
          </div>

          <!-- 30 Days Earnings -->
          <div class="tw-bg-gradient-to-r tw-from-blue-700 tw-to-blue-800 tw-p-3 sm:tw-p-5 tw-rounded-xl sm:tw-rounded-2xl tw-flex tw-items-center tw-justify-between tw-text-white tw-shadow-lg tw-relative tw-overflow-hidden">
            <div class="tw-text-2xl sm:tw-text-4xl tw-font-serif tw-opacity-40">₹</div>
            <div class="tw-text-right">
              <div class="tw-text-xl sm:tw-text-3xl tw-font-black tw-leading-none">₹{{ formatAmount(earnLast30DaysAnim) }}</div>
              <div class="tw-text-[8px] sm:tw-text-[10px] tw-font-bold tw-uppercase tw-tracking-widest tw-mt-1 sm:tw-mt-2 tw-opacity-90">Last 30 Days Earning</div>
            </div>
          </div>

          <!-- Total Earnings -->
          <div class="tw-bg-gradient-to-r tw-from-[#2e1065] tw-to-[#0f172a] tw-p-3 sm:tw-p-5 tw-rounded-xl sm:tw-rounded-2xl tw-flex tw-items-center tw-justify-between tw-text-white tw-shadow-lg tw-relative tw-overflow-hidden">
            <div class="tw-text-2xl sm:tw-text-4xl tw-font-serif tw-opacity-40">₹</div>
            <div class="tw-text-right">
              <div class="tw-text-xl sm:tw-text-3xl tw-font-black tw-leading-none">₹{{ formatAmount(earnTotalAnim) }}</div>
              <div class="tw-text-[8px] sm:tw-text-[10px] tw-font-bold tw-uppercase tw-tracking-widest tw-mt-1 sm:tw-mt-2 tw-opacity-90">All Time Earning</div>
            </div>
          </div>
        </div>
      </div>

      <div class="tw-grid tw-grid-cols-1 xl:tw-grid-cols-12 tw-gap-6">
        <!-- Main Stats & Table -->
        <div class="xl:tw-col-span-9 lg:tw-col-span-8">
          <!-- Balance Widgets -->
          <div class="tw-grid tw-grid-cols-2 md:tw-grid-cols-2 xl:tw-grid-cols-4 tw-gap-3 sm:tw-gap-6 tw-mb-6">
            <div 
              v-for="(stat, index) in [
                { label: 'Balance', value: widget.balance, icon: 'fas fa-wallet', from: 'tw-from-indigo-500', to: 'tw-to-violet-600' },
                { label: 'Affiliate Income', value: widget.total_earning, icon: 'fas fa-tags', from: 'tw-from-pink-500', to: 'tw-to-rose-600' },
                { label: 'Ads Income', value: widget.ads_income || 0, icon: 'fas fa-video', from: 'tw-from-blue-500', to: 'tw-to-azure-600' },
                { label: 'Withdrawal', value: widget.total_withdraw, icon: 'fas fa-hand-holding-usd', from: 'tw-from-emerald-500', to: 'tw-to-green-600' }
              ]" 
              :key="index"
            >
              <div 
                class="tw-p-3 sm:tw-p-5 tw-h-full tw-rounded-xl sm:tw-rounded-2xl tw-border tw-border-white/10 tw-text-white tw-transition-all tw-duration-200 hover:-tw-translate-y-1 hover:tw-shadow-xl tw-bg-gradient-to-br"
                :class="`${stat.from} ${stat.to}`"
              >
                <div class="tw-w-8 tw-h-8 sm:tw-w-12 sm:tw-h-12 tw-rounded-lg sm:tw-rounded-xl tw-bg-white/20 tw-flex tw-items-center tw-justify-center tw-text-sm sm:tw-text-xl tw-mb-2 sm:tw-mb-3">
                   <i :class="stat.icon"></i>
                </div>
                <span class="tw-text-[9px] sm:tw-text-xs tw-font-bold tw-uppercase tw-opacity-90 tw-tracking-wide">{{ stat.label }}</span>
                <h3 class="tw-text-base sm:tw-text-2xl tw-font-extrabold tw-mt-1 tw-mb-0">
                  {{ currencySymbol }}{{
                    stat.label === 'Balance'
                      ? formatAmount(widgetBalanceAnim)
                      : stat.label === 'Affiliate Income'
                        ? formatAmount(widgetTotalEarningAnim)
                        : stat.label === 'Ads Income'
                          ? formatAmount(widgetAdsIncomeAnim)
                          : formatAmount(widgetTotalWithdrawAnim)
                  }}
                </h3>
              </div>
            </div>
          </div>

          <!-- Transaction Table -->
          <div class="tw-bg-slate-800 tw-rounded-2xl tw-shadow-sm tw-border tw-border-slate-700 tw-overflow-hidden">
            <div class="tw-p-5 tw-border-b tw-border-slate-700 tw-bg-slate-900/50">
              <h5 class="tw-text-white tw-font-bold tw-text-lg tw-mb-0 tw-flex tw-items-center">
                <i class="fas fa-history tw-mr-2"></i>Latest Transactions
              </h5>
            </div>
            <!-- Mobile: Card list -->
            <div class="tw-p-4 tw-space-y-3 md:tw-hidden">
              <div
                v-for="trx in transactions"
                :key="trx?.id || trx?.trx || Math.random()"
                class="tw-rounded-xl tw-border tw-border-white/10 tw-bg-slate-900/40 tw-p-4 tw-backdrop-blur-sm"
              >
                <div class="tw-flex tw-items-start tw-justify-between tw-gap-3">
                  <div class="tw-min-w-0">
                    <div class="tw-flex tw-items-center tw-gap-2 tw-mb-2">
                      <span class="tw-font-mono tw-text-[11px] tw-bg-slate-800 tw-px-2 tw-py-1 tw-rounded tw-text-slate-200 tw-border tw-border-white/10">
                        {{ trx.trx }}
                      </span>
                      <span class="tw-text-[11px] tw-text-slate-400">{{ trx.created_at_human }}</span>
                    </div>
                    <div class="tw-text-xs tw-text-slate-300 tw-font-semibold">
                      {{ formatDateTime(trx.created_at) }}
                    </div>
                  </div>
                  <div class="tw-text-right tw-shrink-0">
                    <div class="tw-text-sm tw-font-extrabold" :class="trx.trx_type === '+' ? 'tw-text-emerald-400' : 'tw-text-red-400'">
                      {{ trx.trx_type }}{{ currencySymbol }}{{ formatAmount(trx.amount) }}
                    </div>
                    <div class="tw-text-[11px] tw-text-slate-400">
                      Bal: {{ currencySymbol }}{{ formatAmount(trx.post_balance) }}
                    </div>
                  </div>
                </div>
                <div class="tw-mt-3 tw-text-xs tw-text-slate-400 tw-leading-relaxed tw-line-clamp-2">
                  {{ trx.details }}
                </div>
              </div>

              <div v-if="transactions.length === 0" class="tw-px-2 tw-py-10 tw-text-center">
                <div class="tw-text-slate-600 tw-text-5xl tw-mb-3"><i class="fas fa-inbox"></i></div>
                <p class="tw-text-slate-400 tw-font-semibold">No transactions found</p>
              </div>
            </div>

            <!-- Desktop: Table -->
            <div class="tw-overflow-x-auto tw-hidden md:tw-block">
              <table class="tw-w-full tw-text-sm tw-border-collapse">
                <thead>
                  <tr class="tw-bg-slate-900/30">
                    <th class="tw-px-5 tw-py-3.5 tw-text-left tw-text-slate-400 tw-font-semibold tw-border-b tw-border-slate-700">Transaction ID</th>
                    <th class="tw-px-5 tw-py-3.5 tw-text-left tw-text-slate-400 tw-font-semibold tw-border-b tw-border-slate-700">Date & Time</th>
                    <th class="tw-px-5 tw-py-3.5 tw-text-left tw-text-slate-400 tw-font-semibold tw-border-b tw-border-slate-700">Amount</th>
                    <th class="tw-px-5 tw-py-3.5 tw-text-left tw-text-slate-400 tw-font-semibold tw-border-b tw-border-slate-700">Balance</th>
                    <th class="tw-px-5 tw-py-3.5 tw-text-left tw-text-slate-400 tw-font-semibold tw-border-b tw-border-slate-700">Details</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="trx in transactions" :key="trx?.id || Math.random()" class="hover:tw-bg-slate-700/50 tw-transition-colors">
                    <td class="tw-px-5 tw-py-3.5 tw-border-b tw-border-slate-700 tw-text-slate-300">
                      <span class="tw-font-mono tw-text-xs tw-bg-slate-700 tw-px-2 tw-py-1 tw-rounded tw-text-slate-300">{{ trx.trx }}</span>
                    </td>
                    <td class="tw-px-5 tw-py-3.5 tw-border-b tw-border-slate-700">
                      <div class="tw-font-semibold tw-text-slate-200">{{ formatDateTime(trx.created_at) }}</div>
                      <div class="tw-text-xs tw-text-slate-500">{{ trx.created_at_human }}</div>
                    </td>
                    <td class="tw-px-5 tw-py-3.5 tw-border-b tw-border-slate-700">
                      <span class="tw-font-bold" :class="trx.trx_type === '+' ? 'tw-text-emerald-400' : 'tw-text-red-400'">
                        {{ trx.trx_type }}{{ currencySymbol }}{{ formatAmount(trx.amount) }}
                      </span>
                    </td>
                    <td class="tw-px-5 tw-py-3.5 tw-border-b tw-border-slate-700">
                      <span class="tw-font-bold tw-text-slate-200">{{ currencySymbol }}{{ formatAmount(trx.post_balance) }}</span>
                    </td>
                    <td class="tw-px-5 tw-py-3.5 tw-border-b tw-border-slate-700 tw-text-slate-400 tw-text-xs">{{ trx.details }}</td>
                  </tr>
                  <tr v-if="transactions.length === 0">
                    <td colspan="5" class="tw-px-5 tw-py-12 tw-text-center">
                      <div class="tw-text-slate-600 tw-text-5xl tw-mb-3"><i class="fas fa-inbox"></i></div>
                      <p class="tw-text-slate-500 tw-font-semibold">No transactions found</p>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Right Sidebar -->
        <div class="xl:tw-col-span-3 lg:tw-col-span-4">
          <div class="tw-flex tw-flex-col tw-gap-6 sticky-sidebar">
            <div class="tw-p-5 tw-rounded-2xl tw-border tw-border-white/10 tw-text-white tw-bg-gradient-to-br tw-from-indigo-500 tw-to-violet-600 tw-text-center">
              <div class="tw-w-12 tw-h-12 tw-rounded-xl tw-bg-white/20 tw-flex tw-items-center tw-justify-center tw-text-xl tw-mx-auto tw-mb-4">
                <i class="fas fa-wallet"></i>
              </div>
              <p class="tw-text-xs tw-font-bold tw-uppercase tw-opacity-90 tw-tracking-wide tw-mb-2">Available Balance</p>
              <h2 class="tw-text-3xl tw-font-extrabold tw-mb-5">{{ currencySymbol }}{{ formatAmount(widgetBalanceAnim) }}</h2>
              <router-link to="/user/withdraw" class="tw-w-full tw-py-4 tw-bg-gradient-to-r tw-from-amber-400 tw-to-orange-500 tw-text-black tw-font-extrabold tw-rounded-xl tw-no-underline hover:tw-shadow-xl hover:tw-brightness-110 tw-transition-all tw-duration-200 tw-flex tw-items-center tw-justify-center tw-gap-2 tw-border-0">
                <i class="fas fa-hand-holding-usd"></i> Withdraw Money <i class="fas fa-arrow-right tw-text-xs"></i>
              </router-link>
            </div>

            <div class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-slate-200 tw-p-5">
              <h6 class="tw-text-slate-800 tw-font-bold tw-mb-4 tw-flex tw-items-center">
                <i class="fas fa-lightbulb tw-mr-2"></i>Suggest for you
              </h6>
              <div class="tw-text-center tw-py-8">
                <div class="tw-text-slate-300 tw-text-4xl tw-mb-2"><i class="fas fa-folder-open"></i></div>
                <p class="tw-text-slate-500 tw-font-medium tw-mb-0">No campaigns found</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- KYC Modal (Custom Tailwind) -->
    <div v-if="showKYCRejectionModal" class="tw-fixed tw-inset-0 tw-z-[100] tw-flex tw-items-center tw-justify-center tw-px-4">
      <div class="tw-absolute tw-inset-0 tw-bg-black/80 tw-backdrop-blur-md" @click="showKYCRejectionModal = false"></div>
      <div class="tw-bg-[#0f172a] tw-border tw-border-white/10 tw-rounded-2xl tw-shadow-2xl tw-w-full tw-max-w-md tw-relative tw-z-10 tw-overflow-hidden">
        <div class="tw-p-4 sm:tw-p-5 tw-border-b tw-border-white/10 tw-flex tw-justify-between tw-items-center">
          <h5 class="tw-text-white tw-font-bold tw-text-base sm:tw-text-lg tw-m-0">
             <i class="fas fa-info-circle tw-text-rose-500 tw-mr-2"></i>Rejection Reason
          </h5>
          <button @click="showKYCRejectionModal = false" class="tw-text-slate-400 hover:tw-text-white tw-text-xl tw-bg-transparent tw-border-0 tw-cursor-pointer tw-transition-colors">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="tw-p-5 sm:tw-p-6">
          <div class="tw-p-4 tw-bg-rose-500/10 tw-border tw-border-rose-500/20 tw-rounded-xl tw-text-white tw-font-medium tw-text-sm sm:tw-text-base tw-leading-relaxed">
            {{ user.kyc_rejection_reason }}
          </div>
        </div>
        <div class="tw-p-4 sm:tw-p-5 tw-pt-0">
          <button 
            type="button" 
            class="tw-w-full tw-py-3 tw-rounded-xl tw-bg-gradient-to-r tw-from-slate-700 tw-to-slate-800 hover:tw-from-slate-600 hover:tw-to-slate-700 tw-text-white tw-font-bold tw-transition-all tw-border-0 tw-cursor-pointer"
            @click="showKYCRejectionModal = false"
          >
            Got it
          </button>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import DashboardLayout from '../components/DashboardLayout.vue'
import { userService } from '../services/userService'

export default {
  name: 'Dashboard',
  components: {
    DashboardLayout
  },
  setup() {
    const router = useRouter()
    const loading = ref(false)

    // Count-up animated values (0 -> target)
    const widgetBalanceAnim = ref(0)
    const widgetTotalEarningAnim = ref(0)
    const widgetAdsIncomeAnim = ref(0)
    const widgetTotalWithdrawAnim = ref(0)
    const earnTodayAnim = ref(0)
    const earnLast7DaysAnim = ref(0)
    const earnLast30DaysAnim = ref(0)
    const earnTotalAnim = ref(0)

    // Track animation sequences per ref so multiple widgets can animate simultaneously.
    const animSeqMap = new WeakMap()
    const animateTo = (targetRef, to, duration = 1000) => {
      const seq = (animSeqMap.get(targetRef) || 0) + 1
      animSeqMap.set(targetRef, seq)
      const toNum = Number(to) || 0
      const start = (typeof performance !== 'undefined' && performance.now) ? performance.now() : Date.now()
      const step = (now) => {
        if (animSeqMap.get(targetRef) !== seq) return
        const t = (typeof now === 'number') ? now : ((typeof performance !== 'undefined' && performance.now) ? performance.now() : Date.now())
        const p = Math.min(1, (t - start) / duration)
        // easeOutCubic
        const eased = 1 - Math.pow(1 - p, 3)
        targetRef.value = toNum * eased
        if (p < 1) requestAnimationFrame(step)
        else targetRef.value = toNum // Ensure exact final value
      }
      requestAnimationFrame(step)

      // Fail-safe: if something cancels the animation and it stays at 0, force final value.
      setTimeout(() => {
        if (animSeqMap.get(targetRef) !== seq) return
        if (toNum !== 0 && Number(targetRef.value || 0) === 0) {
          targetRef.value = toNum
        }
      }, Math.max(0, duration) + 150)
    }

    const widget = ref({
      balance: 0,
      total_earning: 0,
      ads_income: 0,
      total_withdraw: 0
    })
    const earnings = ref({
      today: 0,
      last7Days: 0,
      last30Days: 0,
      total: 0
    })
    const transactions = ref([])
    const user = ref({})
    const kycContent = ref(null)
    const currencySymbol = ref('₹')
    const showKYCRejectionModal = ref(false)

    // Load cached data immediately
    try {
      const cached = localStorage.getItem('dashboard_data_cache')
      if (cached) {
        const parsed = JSON.parse(cached)
        widget.value = parsed.widget || widget.value
        earnings.value = parsed.earnings || earnings.value
        transactions.value = parsed.transactions || transactions.value
        user.value = parsed.user || user.value
        kycContent.value = parsed.kyc_content || kycContent.value
        currencySymbol.value = parsed.currency_symbol || '₹'
        
        // Show initial values without animation for instant feel
        widgetBalanceAnim.value = widget.value.balance
        widgetTotalEarningAnim.value = widget.value.total_earning
        widgetAdsIncomeAnim.value = widget.value.ads_income || 0
        widgetTotalWithdrawAnim.value = widget.value.total_withdraw
        earnTodayAnim.value = earnings.value.today
        earnLast7DaysAnim.value = earnings.value.last7Days
        earnLast30DaysAnim.value = earnings.value.last30Days
        earnTotalAnim.value = earnings.value.total
      }
    } catch (e) {
      console.warn('Dashboard cache load failed', e)
    }

    const referralCode = computed(() => {
      const id = Number(user.value?.id)
      if (!id || Number.isNaN(id)) return ''
      return `ADS${id}`
    })

    const copyReferralCode = async () => {
      const code = referralCode.value
      if (!code) return
      try {
        if (navigator?.clipboard?.writeText) {
          await navigator.clipboard.writeText(code)
        } else {
          const el = document.createElement('textarea')
          el.value = code
          el.setAttribute('readonly', '')
          el.style.position = 'absolute'
          el.style.left = '-9999px'
          document.body.appendChild(el)
          el.select()
          document.execCommand('copy')
          document.body.removeChild(el)
        }
        if (window.notify) window.notify('success', 'Referral code copied')
      } catch (e) {
        if (window.notify) window.notify('error', 'Could not copy referral code')
      }
    }

    const displayName = computed(() => {
      const u = user.value
      if (u.firstname || u.lastname) return [u.firstname, u.lastname].filter(Boolean).join(' ').trim()
      if (u.fullname) return u.fullname
      if (u.username) return u.username
      if (u.email) return u.email.split('@')[0] || '—'
      return 'User'
    })

    // Avatar: user image else cartoon PNG
    const avatarTs = Date.now()
    const profileAvatarUrl = computed(() => {
      const u = user.value || {}
      if (u.image) return u.image
      // Use stable timestamp per session
      return `/assets/images/cartoon-avatar.png?v=${avatarTs}`
    })
    
    const displayUsername = computed(() => {
      const u = user.value
      if (u.username) return `@${u.username}`
      if (u.email) return `@${u.email.split('@')[0]}`
      if (u.fullname) return u.fullname
      if (u.firstname || u.lastname) return [u.firstname, u.lastname].filter(Boolean).join(' ').trim()
      return '—'
    })
    const displayPhone = computed(() => {
      const u = user.value
      return u.mobile || u.phone || '—'
    })

    const formatAmount = (amount) => {
      if (!amount && amount !== 0) return '0.00'
      // Support both numbers and Vue refs (for count-up animation)
      const raw = (amount && typeof amount === 'object' && 'value' in amount) ? amount.value : amount
      const num = parseFloat(raw) || 0
      return num.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    }

    const formatDateTime = (dateString) => {
      if (!dateString) return '-'
      const date = new Date(dateString)
      const year = date.getFullYear()
      const month = String(date.getMonth() + 1).padStart(2, '0')
      const day = String(date.getDate()).padStart(2, '0')
      const hour12 = date.getHours() % 12 || 12
      const minutes = String(date.getMinutes()).padStart(2, '0')
      const ampm = date.getHours() >= 12 ? 'PM' : 'AM'
      return `${year}-${month}-${day} ${hour12}:${minutes} ${ampm}`
    }

    const fetchDashboardData = async () => {
      try {
        const response = await userService.getDashboard()
        const wrapper =
          (response && typeof response === 'object' && 'status' in response && 'data' in response)
            ? response
            : (response?.data && typeof response.data === 'object' && 'status' in response.data && 'data' in response.data)
              ? response.data
              : null

        const isSuccess = (wrapper?.status ?? response?.status) === 'success'
        const finalPayload = wrapper ? wrapper.data : (response?.data ?? response)

        if (isSuccess && finalPayload) {
          // Save to cache
          localStorage.setItem('dashboard_data_cache', JSON.stringify(finalPayload))

          widget.value = finalPayload.widget || {}
          const w = finalPayload.widget || {}
          const defaultEarnings = {
            today: 0,
            last7Days: 0,
            last30Days: 0,
            total: Number(w.total_earning) + Number(w.ads_income || 0) || 0
          }
          earnings.value = finalPayload.earnings || defaultEarnings
          transactions.value = finalPayload.transactions || []
          user.value = finalPayload.user || {}
          kycContent.value = finalPayload.kyc_content
          const symbol = finalPayload.currency_symbol || '₹'
          currencySymbol.value = symbol === 'Rs' ? '₹' : symbol

          // Run count-up animations
          animateTo(widgetBalanceAnim, widget.value?.balance ?? 0)
          animateTo(widgetTotalEarningAnim, widget.value?.total_earning ?? 0)
          animateTo(widgetAdsIncomeAnim, widget.value?.ads_income ?? 0)
          animateTo(widgetTotalWithdrawAnim, widget.value?.total_withdraw ?? 0)
          animateTo(earnTodayAnim, earnings.value?.today ?? 0)
          animateTo(earnLast7DaysAnim, earnings.value?.last7Days ?? 0)
          animateTo(earnLast30DaysAnim, earnings.value?.last30Days ?? 0)
          animateTo(earnTotalAnim, earnings.value?.total ?? 0)
        }
      } catch (error) {
        console.error('Error loading dashboard:', error)
        if (error.response?.status === 401) {
          router.push('/user/login')
        }
      } finally {
        loading.value = false
      }
    }

    onMounted(() => {
      fetchDashboardData()
      document.body.style.overflow = 'auto'
    })

    return {
      widget,
      earnings,
      widgetBalanceAnim,
      widgetTotalEarningAnim,
      widgetAdsIncomeAnim,
      widgetTotalWithdrawAnim,
      earnTodayAnim,
      earnLast7DaysAnim,
      earnLast30DaysAnim,
      earnTotalAnim,
      transactions,
      user,
      referralCode,
      copyReferralCode,
      displayName,
      profileAvatarUrl,
      displayUsername,
      displayPhone,
      kycContent,
      currencySymbol,
      formatAmount,
      formatDateTime,
      showKYCRejectionModal
    }
  }
}
</script>

<style scoped>
@media (max-width: 640px) {
  /* KYC Alerts */
  .tw-rounded-2xl { border-radius: 1.15rem !important; }
  .tw-p-5 { padding: 1rem !important; }
  h4.tw-text-xl { font-size: 1.1rem !important; }
  p.tw-text-slate-700 { font-size: 0.85rem !important; margin-bottom: 0.85rem !important; }
  .tw-px-4.tw-py-2 { padding: 0.6rem 1rem !important; font-size: 0.8rem !important; border-radius: 0.75rem !important; }

  /* Profile Card & UID */
  .tw-rounded-\[24px\] { border-radius: 1.5rem !important; }
  h2.tw-text-lg { font-size: 1.1rem !important; margin-bottom: 1rem !important; }
  .tw-w-16.tw-h-16 { width: 6rem !important; height: 6rem !important; margin-bottom: 1rem !important; border-width: 3px !important; }
  h1.tw-text-base { font-size: 1.25rem !important; margin-bottom: 1rem !important; }
  .tw-px-3.tw-py-1 { padding: 0.45rem 1rem !important; font-size: 11px !important; border-radius: 0.85rem !important; }

  /* Earnings Stack (Cyan/Blue cards) */
  .tw-gap-2 { gap: 0.65rem !important; }
  .tw-p-3.sm\:tw-p-5 { padding: 1rem 1.25rem !important; border-radius: 1.25rem !important; }
  .tw-text-2xl.sm\:tw-text-4xl.tw-font-serif { font-size: 1.75rem !important; }
  .tw-text-xl.tw-font-black { font-size: 1.4rem !important; }
  .tw-text-\[8px\].sm\:tw-text-\[10px\] { font-size: 8px !important; margin-top: 0.35rem !important; }

  /* Grid Stats (Wallet, Income, etc.) */
  .tw-grid.tw-gap-3 { gap: 0.85rem !important; }
  .tw-w-8.tw-h-8 { width: 2.75rem !important; height: 2.75rem !important; margin-bottom: 0.65rem !important; }
  .tw-w-8.tw-h-8 i { font-size: 1.15rem !important; }
  .tw-text-\[9px\] { font-size: 9px !important; }
  .tw-text-base.sm\:tw-text-2xl.tw-font-extrabold { font-size: 1.15rem !important; }

  /* Transactions Header & Cards */
  .tw-p-5.tw-border-b { padding: 1rem !important; }
  .tw-p-5 h5.tw-text-lg { font-size: 1.1rem !important; }
  .tw-p-4.tw-space-y-3 { padding: 1rem !important; }
  .tw-p-4.tw-backdrop-blur-sm { padding: 1rem !important; border-radius: 1.15rem !important; }
  .tw-font-mono.tw-text-\[11px\] { font-size: 10px !important; }
  .tw-text-\[11px\].tw-text-slate-400 { font-size: 10px !important; }
  .tw-text-sm.tw-font-extrabold { font-size: 1rem !important; }
  .tw-text-xs.tw-text-slate-300 { font-size: 0.8rem !important; }
  .tw-text-xs.tw-text-slate-400.tw-leading-relaxed { font-size: 0.8rem !important; }

  /* Sidebar Wallet Card */
  .tw-from-indigo-500.tw-to-violet-600.tw-text-center { padding: 1.35rem !important; border-radius: 1.5rem !important; }
  .tw-w-12.tw-h-12.tw-mx-auto { width: 3rem !important; height: 3rem !important; margin-bottom: 0.75rem !important; }
  .tw-text-3xl.tw-font-extrabold.tw-mb-5 { font-size: 1.85rem !important; margin-bottom: 1.25rem !important; }
  .tw-py-4.tw-bg-gradient-to-r { padding: 0.85rem !important; font-size: 1rem !important; border-radius: 1rem !important; }
}
@keyframes borderGlow {
  0% { box-shadow: 0 0 10px rgba(6, 182, 212, 0.2), inset 0 0 10px rgba(6, 182, 212, 0.1); }
  50% { box-shadow: 0 0 25px rgba(6, 182, 212, 0.5), inset 0 0 20px rgba(6, 182, 212, 0.2); }
  100% { box-shadow: 0 0 10px rgba(6, 182, 212, 0.2), inset 0 0 10px rgba(6, 182, 212, 0.1); }
}
.border-glow-anim > div {
  animation: borderGlow 3s infinite alternate ease-in-out;
}
</style>
