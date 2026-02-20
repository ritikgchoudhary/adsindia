<template>
  <div 
    class="tw-w-[280px] tw-fixed tw-left-0 tw-top-0 tw-h-screen tw-z-50 tw-flex tw-flex-col tw-bg-slate-900 tw-border-r tw-border-slate-800 tw-transition-transform tw-duration-300 tw-overflow-hidden lg:tw-translate-x-0"
    :class="isOpen ? 'tw-translate-x-0' : '-tw-translate-x-full'"
  >
    <!-- Close Button (Mobile) -->
    <button 
      class="lg:tw-hidden tw-absolute tw-top-4 tw-right-4 tw-w-9 tw-h-9 tw-flex tw-items-center tw-justify-center tw-bg-slate-800 tw-text-slate-400 hover:tw-text-white tw-rounded-lg tw-transition-all tw-border-0 tw-cursor-pointer tw-z-10"
      @click="$emit('close')"
    >
      <i class="fas fa-times"></i>
    </button>

    <!-- Logo -->
    <div class="tw-px-6 tw-py-5 tw-border-b tw-border-slate-800 tw-bg-slate-900/50">
      <router-link to="/user/dashboard" class="tw-flex tw-items-center tw-justify-center tw-no-underline">
        <img v-if="siteLogo" :src="siteLogo" alt="Logo" class="tw-h-14 tw-w-auto tw-object-contain" @error="onLogoError">
        <span v-else class="tw-text-white tw-font-bold tw-text-xl tw-tracking-wide">Ads Skill India</span>
      </router-link>
    </div>

    <!-- Menu List -->
    <ul class="tw-flex-1 tw-overflow-y-auto tw-px-3 tw-py-4 tw-m-0 tw-list-none hover:tw-overflow-y-auto custom-scrollbar">
      
      <!-- Dashboard -->
      <li class="tw-mb-1">
        <router-link 
          to="/user/dashboard" 
          class="tw-flex tw-items-center tw-gap-3 tw-px-4 tw-py-3 tw-rounded-xl tw-font-medium tw-text-[15px] tw-transition-all tw-no-underline"
          :class="isActive('/user/dashboard') ? 'tw-bg-indigo-500/10 tw-text-indigo-400 tw-border-l-2 tw-border-indigo-500' : 'tw-text-slate-400 hover:tw-bg-slate-800 hover:tw-text-slate-200 tw-border-l-2 tw-border-transparent'"
        >
          <span class="tw-w-6 tw-text-center"><i class="fas fa-border-all"></i></span>
          <span class="tw-flex-1">Dashboard</span>
        </router-link>
      </li>

      <!-- Ads (Dropdown) -->
      <li class="tw-mb-1">
        <a 
          href="#" 
          class="tw-flex tw-items-center tw-justify-between tw-px-4 tw-py-3 tw-rounded-xl tw-font-medium tw-text-[15px] tw-transition-all tw-cursor-pointer tw-no-underline"
          :class="isDropdownActive(['/user/ads-work', '/user/ad-plans']) ? 'tw-bg-indigo-500/10 tw-text-indigo-400' : 'tw-text-slate-400 hover:tw-bg-slate-800 hover:tw-text-slate-200'"
          @click.prevent="toggleSubmenu('ads')"
        >
          <div class="tw-flex tw-items-center tw-gap-3">
            <span class="tw-w-6 tw-text-center"><i class="fas fa-video"></i></span>
            <span>Ads</span>
          </div>
          <i class="fas fa-chevron-down tw-text-xs tw-transition-transform" :class="{ 'tw-rotate-180': openSubmenus.includes('ads') }"></i>
        </a>
        <div v-show="openSubmenus.includes('ads')" class="tw-pl-4 tw-mt-1 tw-ml-4 tw-border-l tw-border-slate-800">
          <ul class="tw-list-none tw-p-0 tw-m-0">
            <li class="tw-mb-1">
              <router-link to="/user/ads-work" class="tw-block tw-px-4 tw-py-2 tw-rounded-lg tw-text-sm tw-font-medium tw-no-underline tw-transition-colors" :class="isActive('/user/ads-work') ? 'tw-text-indigo-400 tw-bg-indigo-500/10' : 'tw-text-slate-500 hover:tw-text-slate-300 hover:tw-bg-slate-800/50'">Ads Work</router-link>
            </li>
            <li class="tw-mb-1">
              <router-link to="/user/ad-plans" class="tw-block tw-px-4 tw-py-2 tw-rounded-lg tw-text-sm tw-font-medium tw-no-underline tw-transition-colors" :class="isActive('/user/ad-plans') ? 'tw-text-indigo-400 tw-bg-indigo-500/10' : 'tw-text-slate-500 hover:tw-text-slate-300 hover:tw-bg-slate-800/50'">Ad Plans</router-link>
            </li>
          </ul>
        </div>
      </li>

      <!-- Partner Program -->
      <li class="tw-mb-1">
        <router-link 
          to="/user/partner-program" 
          class="tw-flex tw-items-center tw-gap-3 tw-px-4 tw-py-3 tw-rounded-xl tw-font-medium tw-text-[15px] tw-transition-all tw-no-underline"
          :class="isActive('/user/partner-program') ? 'tw-bg-indigo-500/10 tw-text-indigo-400 tw-border-l-2 tw-border-indigo-500' : 'tw-text-slate-400 hover:tw-bg-slate-800 hover:tw-text-slate-200 tw-border-l-2 tw-border-transparent'"
        >
          <span class="tw-w-6 tw-text-center"><i class="fas fa-handshake"></i></span>
          <span class="tw-flex-1">Partner Program</span>
        </router-link>
      </li>

      <!-- Account & KYC -->
      <li class="tw-mb-1">
        <router-link 
          to="/user/account-kyc" 
          class="tw-flex tw-items-center tw-gap-3 tw-px-4 tw-py-3 tw-rounded-xl tw-font-medium tw-text-[15px] tw-transition-all tw-no-underline"
          :class="isActive(['/user/account-kyc', '/user/kyc-form', '/user/kyc-data']) ? 'tw-bg-indigo-500/10 tw-text-indigo-400 tw-border-l-2 tw-border-indigo-500' : 'tw-text-slate-400 hover:tw-bg-slate-800 hover:tw-text-slate-200 tw-border-l-2 tw-border-transparent'"
        >
          <span class="tw-w-6 tw-text-center"><i class="fas fa-user-shield"></i></span>
          <span class="tw-flex-1">Account & KYC</span>
        </router-link>
      </li>

      <!-- Course Packages -->
      <li class="tw-mb-1">
        <a 
          href="#" 
          class="tw-flex tw-items-center tw-justify-between tw-px-4 tw-py-3 tw-rounded-xl tw-font-medium tw-text-[15px] tw-transition-all tw-cursor-pointer tw-no-underline"
          :class="isDropdownActive(['/user/courses', '/user/packages']) ? 'tw-bg-indigo-500/10 tw-text-indigo-400' : 'tw-text-slate-400 hover:tw-bg-slate-800 hover:tw-text-slate-200'"
          @click.prevent="toggleSubmenu('courses')"
        >
          <div class="tw-flex tw-items-center tw-gap-3">
            <span class="tw-w-6 tw-text-center"><i class="fas fa-graduation-cap"></i></span>
            <span>Courses</span>
          </div>
          <i class="fas fa-chevron-down tw-text-xs tw-transition-transform" :class="{ 'tw-rotate-180': openSubmenus.includes('courses') }"></i>
        </a>
        <div v-show="openSubmenus.includes('courses')" class="tw-pl-4 tw-mt-1 tw-ml-4 tw-border-l tw-border-slate-800">
          <ul class="tw-list-none tw-p-0 tw-m-0">
            <li class="tw-mb-1">
              <router-link to="/user/courses" class="tw-block tw-px-4 tw-py-2 tw-rounded-lg tw-text-sm tw-font-medium tw-no-underline tw-transition-colors" :class="isActive('/user/courses') ? 'tw-text-indigo-400 tw-bg-indigo-500/10' : 'tw-text-slate-500 hover:tw-text-slate-300 hover:tw-bg-slate-800/50'">
                <i class="fas fa-play-circle tw-mr-2"></i>Courses
              </router-link>
            </li>
            <li class="tw-mb-1">
              <router-link to="/user/packages" class="tw-block tw-px-4 tw-py-2 tw-rounded-lg tw-text-sm tw-font-medium tw-no-underline tw-transition-colors" :class="isActive('/user/packages') ? 'tw-text-indigo-400 tw-bg-indigo-500/10' : 'tw-text-slate-500 hover:tw-text-slate-300 hover:tw-bg-slate-800/50'">
                <i class="fas fa-box-open tw-mr-2"></i>Course Packages
              </router-link>
            </li>
          </ul>
        </div>
      </li>

      <!-- My Team (Dropdown) -->
      <li class="tw-mb-1">
        <a 
          href="#" 
          class="tw-flex tw-items-center tw-justify-between tw-px-4 tw-py-3 tw-rounded-xl tw-font-medium tw-text-[15px] tw-transition-all tw-cursor-pointer tw-no-underline"
          :class="isDropdownActive(teamMenuPaths) ? 'tw-bg-indigo-500/10 tw-text-indigo-400' : 'tw-text-slate-400 hover:tw-bg-slate-800 hover:tw-text-slate-200'"
          @click.prevent="toggleSubmenu('team')"
        >
          <div class="tw-flex tw-items-center tw-gap-3">
            <span class="tw-w-6 tw-text-center"><i class="fas fa-users"></i></span>
            <span>My Team</span>
          </div>
          <i class="fas fa-chevron-down tw-text-xs tw-transition-transform" :class="{ 'tw-rotate-180': openSubmenus.includes('team') }"></i>
        </a>
        <div v-show="openSubmenus.includes('team')" class="tw-pl-4 tw-mt-1 tw-ml-4 tw-border-l tw-border-slate-800">
          <ul class="tw-list-none tw-p-0 tw-m-0">
            <li class="tw-mb-1">
              <router-link to="/user/referral" class="tw-block tw-px-4 tw-py-2 tw-rounded-lg tw-text-sm tw-font-medium tw-no-underline tw-transition-colors" :class="isActive('/user/referral') ? 'tw-text-indigo-400 tw-bg-indigo-500/10' : 'tw-text-slate-500 hover:tw-text-slate-300 hover:tw-bg-slate-800/50'">Referral</router-link>
            </li>
            <li v-if="isAgent" class="tw-mb-1">
              <router-link to="/user/affiliate-income" class="tw-block tw-px-4 tw-py-2 tw-rounded-lg tw-text-sm tw-font-medium tw-no-underline tw-transition-colors" :class="isActive('/user/affiliate-income') ? 'tw-text-indigo-400 tw-bg-indigo-500/10' : 'tw-text-slate-500 hover:tw-text-slate-300 hover:tw-bg-slate-800/50'">Affiliate Income</router-link>
            </li>
            <li v-if="isAgent" class="tw-mb-1">
              <router-link to="/user/affiliate-withdraw" class="tw-block tw-px-4 tw-py-2 tw-rounded-lg tw-text-sm tw-font-medium tw-no-underline tw-transition-colors" :class="isActive('/user/affiliate-withdraw') && !isActive('/user/affiliate-withdraw/history') ? 'tw-text-indigo-400 tw-bg-indigo-500/10' : 'tw-text-slate-500 hover:tw-text-slate-300 hover:tw-bg-slate-800/50'">
                Affiliate Withdraw
              </router-link>
            </li>
            <li v-if="isAgent" class="tw-mb-1">
              <router-link to="/user/affiliate-withdraw/history" class="tw-block tw-px-4 tw-py-2 tw-rounded-lg tw-text-sm tw-font-medium tw-no-underline tw-transition-colors" :class="isActive('/user/affiliate-withdraw/history') ? 'tw-text-indigo-400 tw-bg-indigo-500/10' : 'tw-text-slate-500 hover:tw-text-slate-300 hover:tw-bg-slate-800/50'">
                Affiliate Withdraw Log
              </router-link>
            </li>
          </ul>
        </div>
      </li>

      <!-- Certificates -->
      <li class="tw-mb-1">
        <router-link 
          to="/user/certificates" 
          class="tw-flex tw-items-center tw-gap-3 tw-px-4 tw-py-3 tw-rounded-xl tw-font-medium tw-text-[15px] tw-transition-all tw-no-underline"
          :class="isActive('/user/certificates') ? 'tw-bg-indigo-500/10 tw-text-indigo-400 tw-border-l-2 tw-border-indigo-500' : 'tw-text-slate-400 hover:tw-bg-slate-800 hover:tw-text-slate-200 tw-border-l-2 tw-border-transparent'"
        >
          <span class="tw-w-6 tw-text-center"><i class="fas fa-certificate"></i></span>
          <span class="tw-flex-1">Certificates</span>
        </router-link>
      </li>

      <!-- Withdraw (Dropdown) -->
      <li class="tw-mb-1">
        <a 
          href="#" 
          class="tw-flex tw-items-center tw-justify-between tw-px-4 tw-py-3 tw-rounded-xl tw-font-medium tw-text-[15px] tw-transition-all tw-cursor-pointer tw-no-underline"
          :class="isDropdownActive(['/user/withdraw', '/user/withdraw/history', '/user/deposit/history']) ? 'tw-bg-indigo-500/10 tw-text-indigo-400' : 'tw-text-slate-400 hover:tw-bg-slate-800 hover:tw-text-slate-200'"
          @click.prevent="toggleSubmenu('withdraw')"
        >
          <div class="tw-flex tw-items-center tw-gap-3">
            <span class="tw-w-6 tw-text-center"><i class="fas fa-hand-holding-usd"></i></span>
            <span>Withdraw</span>
          </div>
          <i class="fas fa-chevron-down tw-text-xs tw-transition-transform" :class="{ 'tw-rotate-180': openSubmenus.includes('withdraw') }"></i>
        </a>
        <div v-show="openSubmenus.includes('withdraw')" class="tw-pl-4 tw-mt-1 tw-ml-4 tw-border-l tw-border-slate-800">
          <ul class="tw-list-none tw-p-0 tw-m-0">
            <li class="tw-mb-1">
              <router-link to="/user/withdraw" class="tw-block tw-px-4 tw-py-2 tw-rounded-lg tw-text-sm tw-font-medium tw-no-underline tw-transition-colors" :class="isActive('/user/withdraw') && !isActive('/user/withdraw/history') ? 'tw-text-indigo-400 tw-bg-indigo-500/10' : 'tw-text-slate-500 hover:tw-text-slate-300 hover:tw-bg-slate-800/50'">Withdraw Money</router-link>
            </li>
            <li class="tw-mb-1">
              <router-link to="/user/withdraw/history" class="tw-block tw-px-4 tw-py-2 tw-rounded-lg tw-text-sm tw-font-medium tw-no-underline tw-transition-colors" :class="isActive('/user/withdraw/history') ? 'tw-text-indigo-400 tw-bg-indigo-500/10' : 'tw-text-slate-500 hover:tw-text-slate-300 hover:tw-bg-slate-800/50'">Withdraw Log</router-link>
            </li>
            <li class="tw-mb-1">
              <router-link to="/user/deposit/history" class="tw-block tw-px-4 tw-py-2 tw-rounded-lg tw-text-sm tw-font-medium tw-no-underline tw-transition-colors" :class="isActive('/user/deposit/history') ? 'tw-text-indigo-400 tw-bg-indigo-500/10' : 'tw-text-slate-500 hover:tw-text-slate-300 hover:tw-bg-slate-800/50'">Deposit History</router-link>
            </li>
          </ul>
        </div>
      </li>

      <!-- Leaderboard -->
      <li class="tw-mb-1">
        <router-link 
          to="/user/leaderboard" 
          class="tw-flex tw-items-center tw-gap-3 tw-px-4 tw-py-3 tw-rounded-xl tw-font-medium tw-text-[15px] tw-transition-all tw-no-underline"
          :class="isActive('/user/leaderboard') ? 'tw-bg-indigo-500/10 tw-text-indigo-400 tw-border-l-2 tw-border-indigo-500' : 'tw-text-slate-400 hover:tw-bg-slate-800 hover:tw-text-slate-200 tw-border-l-2 tw-border-transparent'"
        >
          <span class="tw-w-6 tw-text-center"><i class="fas fa-trophy"></i></span>
          <span class="tw-flex-1">Leaderboard</span>
        </router-link>
      </li>

      <!-- Customer Support -->
      <li class="tw-mb-1">
        <router-link 
          to="/user/customer-support" 
          class="tw-flex tw-items-center tw-gap-3 tw-px-4 tw-py-3 tw-rounded-xl tw-font-medium tw-text-[15px] tw-transition-all tw-no-underline"
          :class="isActive('/user/customer-support') ? 'tw-bg-indigo-500/10 tw-text-indigo-400 tw-border-l-2 tw-border-indigo-500' : 'tw-text-slate-400 hover:tw-bg-slate-800 hover:tw-text-slate-200 tw-border-l-2 tw-border-transparent'"
        >
          <span class="tw-w-6 tw-text-center"><i class="fas fa-headset"></i></span>
          <span class="tw-flex-1">Customer Support</span>
        </router-link>
      </li>

      <!-- Password Change -->
      <li class="tw-mb-1">
        <router-link 
          to="/user/change-password" 
          class="tw-flex tw-items-center tw-gap-3 tw-px-4 tw-py-3 tw-rounded-xl tw-font-medium tw-text-[15px] tw-transition-all tw-no-underline"
          :class="isActive('/user/change-password') ? 'tw-bg-indigo-500/10 tw-text-indigo-400 tw-border-l-2 tw-border-indigo-500' : 'tw-text-slate-400 hover:tw-bg-slate-800 hover:tw-text-slate-200 tw-border-l-2 tw-border-transparent'"
        >
          <span class="tw-w-6 tw-text-center"><i class="fas fa-lock"></i></span>
          <span class="tw-flex-1">Password Change</span>
        </router-link>
      </li>

      <!-- 2FA Security -->
      <li class="tw-mb-1">
        <router-link 
          to="/user/twofactor" 
          class="tw-flex tw-items-center tw-gap-3 tw-px-4 tw-py-3 tw-rounded-xl tw-font-medium tw-text-[15px] tw-transition-all tw-no-underline"
          :class="isActive('/user/twofactor') ? 'tw-bg-indigo-500/10 tw-text-indigo-400 tw-border-l-2 tw-border-indigo-500' : 'tw-text-slate-400 hover:tw-bg-slate-800 hover:tw-text-slate-200 tw-border-l-2 tw-border-transparent'"
        >
          <span class="tw-w-6 tw-text-center"><i class="fas fa-shield-alt"></i></span>
          <span class="tw-flex-1">2FA Security</span>
        </router-link>
      </li>

      <!-- Logout -->
      <li class="tw-mt-4 tw-pt-4 tw-border-t tw-border-slate-800">
        <a 
          href="#" 
          @click.prevent="handleLogout" 
          class="tw-flex tw-items-center tw-gap-3 tw-px-4 tw-py-3 tw-rounded-xl tw-font-medium tw-text-[15px] tw-text-red-400 hover:tw-bg-red-500/10 hover:tw-text-red-300 tw-transition-all tw-border-l-2 tw-border-transparent tw-no-underline"
        >
          <span class="tw-w-6 tw-text-center"><i class="fas fa-sign-out-alt"></i></span>
          <span class="tw-flex-1">Logout</span>
        </a>
      </li>
    </ul>
  </div>
</template>

<script>
import { ref, onMounted, watch, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { authService } from '../../services/authService'
import api from '../../services/api'

export default {
  name: 'UserSidebar',
  props: {
    isOpen: {
      type: Boolean,
      default: false
    }
  },
  emits: ['close'],
  setup() {
    const router = useRouter()
    const route = useRoute()
    const siteLogo = ref('/assets/images/logo_icon/logo.png?v=' + new Date().getTime())
    const openSubmenus = ref([])
    const isAgent = ref(false)

    const onLogoError = () => { siteLogo.value = null }

    const isActive = (path) => {
      if (Array.isArray(path)) {
        return path.some(p => route.path === p || route.path.startsWith(p + '/'))
      }
      return route.path === path || route.path.startsWith(path + '/')
    }

    const isDropdownActive = (paths) => {
      return paths.some(path => isActive(path))
    }

    const toggleSubmenu = (menu) => {
      if (openSubmenus.value.includes(menu)) {
        openSubmenus.value = openSubmenus.value.filter(m => m !== menu)
      } else {
        openSubmenus.value.push(menu)
      }
    }

    const teamMenuPaths = computed(() => {
      return isAgent.value
        ? ['/user/referral', '/user/affiliate-income', '/user/affiliate-withdraw', '/user/affiliate-withdraw/history']
        : ['/user/referral']
    })

    const fetchUserInfo = async () => {
      try {
        const res = await api.get('/user-info', { __skipLoader: true })
        const d = res?.data?.data || res?.data
        isAgent.value = !!(d?.is_agent)
        // Ensure submenu visibility if user is already on an agent-only page
        if (isDropdownActive(teamMenuPaths.value) && !openSubmenus.value.includes('team')) {
          openSubmenus.value.push('team')
        }
      } catch (_) {
        // non-blocking; default false
      }
    }

    const handleLogout = async () => {
      try {
        await authService.logout()
        router.push('/')
      } catch (error) {
        console.error('Logout error:', error)
        router.push('/')
      }
    }

    watch(() => route.path, () => {
      // Logic to auto-open could go here if needed, but manual control is often better in Vue
      // We can also close sidebar on mobile if route changes
    })

    onMounted(() => {
       fetchUserInfo()
       // Pre-open menus based on current route
       if (isDropdownActive(['/user/ads-work', '/user/ad-plans'])) toggleSubmenu('ads')
       if (isDropdownActive(['/user/courses', '/user/packages'])) toggleSubmenu('courses')
       if (isDropdownActive(teamMenuPaths.value)) toggleSubmenu('team')
       if (isDropdownActive(['/user/withdraw', '/user/withdraw/history', '/user/deposit/history'])) toggleSubmenu('withdraw')
    })

    return {
      siteLogo,
      handleLogout,
      isActive,
      isDropdownActive,
      onLogoError,
      openSubmenus,
      toggleSubmenu,
      isAgent,
      teamMenuPaths
    }
  }
}
</script>

<style scoped>
/* Custom scrollbar for sidebar */
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: rgba(255, 255, 255, 0.1);
  border-radius: 20px;
}
</style>
