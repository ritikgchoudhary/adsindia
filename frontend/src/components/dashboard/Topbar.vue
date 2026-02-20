<template>
  <header class="tw-fixed tw-top-0 tw-left-0 tw-w-full tw-z-40 tw-bg-slate-900 tw-border-b tw-border-slate-700 tw-py-3 tw-px-4 md:tw-px-6 tw-transition-all tw-duration-300 lg:tw-pl-[280px]">
    <div class="tw-flex tw-justify-between tw-items-center tw-w-full">
      <div class="tw-flex tw-items-center tw-gap-4">
        <!-- Sidebar Toggle (Mobile) -->
        <button 
          class="lg:tw-hidden tw-text-slate-300 hover:tw-text-white tw-text-2xl tw-transition-colors tw-bg-transparent tw-border-0 tw-cursor-pointer" 
          @click="$emit('toggle-sidebar')"
        >
          <i class="fas fa-bars"></i>
        </button>
        
        <!-- Logo (Mobile) -->
        <router-link to="/" class="tw-flex tw-items-center tw-gap-2 tw-no-underline lg:tw-hidden">
          <img v-if="siteLogo" :src="siteLogo" alt="ADS SKILL INDIA" class="tw-h-8 tw-w-auto" @error="siteLogo = null">
          <span v-else class="tw-text-white tw-font-bold tw-text-lg tracking-tight">Ads Skill India</span>
        </router-link>
      </div>

      <!-- User Dropdown -->
      <div class="tw-relative" ref="dropdownRef">
        <div class="tw-flex tw-items-center tw-gap-3 tw-cursor-pointer" @click="toggleDropdown">
          <div class="tw-text-right tw-hidden md:tw-block">
            <p class="tw-text-white tw-font-medium tw-text-sm tw-mb-0.5 tw-leading-tight">Hello, {{ userFullname }}</p>
            <p class="tw-text-slate-400 tw-text-xs tw-m-0">User Panel</p>
          </div>
          <div class="tw-w-10 tw-h-10 tw-rounded-full tw-overflow-hidden tw-border-2 tw-border-slate-600 tw-transition-all hover:tw-border-indigo-500">
            <img :src="userImage" alt="User" class="tw-w-full tw-h-full tw-object-cover">
          </div>
        </div>

        <transition
          enter-active-class="tw-transition tw-ease-out tw-duration-100"
          enter-from-class="tw-transform tw-opacity-0 tw-scale-95"
          enter-to-class="tw-transform tw-opacity-100 tw-scale-100"
          leave-active-class="tw-transition tw-ease-in tw-duration-75"
          leave-from-class="tw-transform tw-opacity-100 tw-scale-100"
          leave-to-class="tw-transform tw-opacity-0 tw-scale-95"
        >
          <div v-show="showDropdown" class="tw-absolute tw-right-0 tw-top-full tw-mt-2 tw-w-56 tw-bg-slate-800 tw-border tw-border-slate-700 tw-rounded-xl tw-shadow-xl tw-overflow-hidden tw-z-50">
            <ul class="tw-p-2 tw-list-none tw-m-0">
              <li>
                <router-link to="/user/profile-setting" class="tw-flex tw-items-center tw-gap-3 tw-px-4 tw-py-2.5 tw-text-slate-300 tw-rounded-lg hover:tw-bg-slate-700 hover:tw-text-white tw-no-underline tw-transition-colors">
                  <i class="far fa-user-circle tw-w-5"></i>
                  <span class="tw-text-sm tw-font-medium">Profile Setting</span>
                </router-link>
              </li>
              <li>
                <router-link to="/user/change-password" class="tw-flex tw-items-center tw-gap-3 tw-px-4 tw-py-2.5 tw-text-slate-300 tw-rounded-lg hover:tw-bg-slate-700 hover:tw-text-white tw-no-underline tw-transition-colors">
                  <i class="fas fa-key tw-w-5"></i>
                  <span class="tw-text-sm tw-font-medium">Change Password</span>
                </router-link>
              </li>
              <li>
                <router-link to="/user/twofactor" class="tw-flex tw-items-center tw-gap-3 tw-px-4 tw-py-2.5 tw-text-slate-300 tw-rounded-lg hover:tw-bg-slate-700 hover:tw-text-white tw-no-underline tw-transition-colors">
                  <i class="fas fa-shield-alt tw-w-5"></i>
                  <span class="tw-text-sm tw-font-medium">2FA Security</span>
                </router-link>
              </li>
              <li class="tw-border-t tw-border-slate-700 tw-my-1"></li>
              <li>
                <a href="#" @click.prevent="handleLogout" class="tw-flex tw-items-center tw-gap-3 tw-px-4 tw-py-2.5 tw-text-red-400 tw-rounded-lg hover:tw-bg-red-500/10 hover:tw-text-red-300 tw-no-underline tw-transition-colors">
                  <i class="fas fa-sign-out-alt tw-w-5"></i>
                  <span class="tw-text-sm tw-font-medium">Logout</span>
                </a>
              </li>
            </ul>
          </div>
        </transition>
      </div>
    </div>
  </header>
</template>

<script>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { authService } from '../../services/authService'
import { userService } from '../../services/userService'

export default {
  name: 'Topbar',
  setup(_, { emit }) {
    const router = useRouter()
    const userFullname = ref('User')
    const userImage = ref('/assets/images/default.png')
    const siteLogo = ref('/assets/images/logo_icon/logo.png?v=' + new Date().getTime())
    const showDropdown = ref(false)
    const dropdownRef = ref(null)

    const toggleDropdown = () => {
      showDropdown.value = !showDropdown.value
    }

    const handleClickOutside = (event) => {
      if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        showDropdown.value = false
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

    const fetchUserInfo = async () => {
      try {
        const response = await userService.getUserInfo()
        if (response.status === 'success') {
          const data = response.data?.data || response.data
          if (data) {
             const name = data.fullname || [data.firstname, data.lastname].filter(Boolean).join(' ')
             if (name) userFullname.value = name
             if(data.image) userImage.value = data.image
          }
        }
      } catch (error) {
        console.error('Error loading user info:', error)
      }
    }

    onMounted(() => {
      fetchUserInfo()
      document.addEventListener('click', handleClickOutside)
    })

    onUnmounted(() => {
      document.removeEventListener('click', handleClickOutside)
    })

    return {
      userFullname,
      userImage,
      siteLogo,
      showDropdown,
      toggleDropdown,
      dropdownRef,
      handleLogout
    }
  },
  emits: ['toggle-sidebar']
}
</script>
