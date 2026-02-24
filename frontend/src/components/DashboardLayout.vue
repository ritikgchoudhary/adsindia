<template>
  <div
    class="tw-min-h-screen tw-w-full tw-flex tw-font-sans tw-relative"
    :class="darkTheme ? 'tw-bg-slate-950 tw-text-white' : 'tw-bg-slate-100'"
  >
    <div class="tw-w-full tw-min-h-screen">
      <!-- Sidebar -->
      <UserSidebar 
        v-if="!noSidebar"
        :is-open="isSidebarOpen" 
        @close="isSidebarOpen = false" 
      />
      
      <!-- Overlay -->
      <div 
        v-if="isSidebarOpen && !noSidebar" 
        class="tw-fixed tw-inset-0 tw-bg-black/50 tw-z-40 lg:tw-hidden"
        @click="isSidebarOpen = false"
      ></div>

      <!-- Main Content -->
      <div
        class="tw-flex tw-flex-col tw-min-h-screen tw-overflow-x-hidden tw-transition-all tw-duration-300"
        :class="[
          darkTheme ? 'tw-bg-slate-950 tw-text-white' : 'tw-bg-slate-100',
          !noSidebar ? 'lg:tw-ml-[280px]' : 'tw-ml-0'
        ]"
      >
        <Topbar v-if="!noSidebar" @toggle-sidebar="isSidebarOpen = !isSidebarOpen" />
        <div v-else class="tw-h-[20px] lg:tw-h-[40px]"></div>

        <div class="tw-w-full tw-max-w-full tw-p-0 tw-mt-[0px]" :class="{ 'sm:tw-mt-[70px]': !noSidebar }">
          <div class="tw-px-4 tw-pt-3 tw-pb-4 sm:tw-p-6 tw-w-full tw-flex-1">
            <h4 
              v-if="pageTitle && !noSidebar"
              class="tw-text-xl sm:tw-text-2xl tw-font-bold tw-mb-4 sm:tw-mb-5 tw-text-slate-900" 
              :class="{ 'tw-text-white': darkTheme }"
            >
              {{ pageTitle }}
            </h4>
            <slot />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import UserSidebar from './dashboard/UserSidebar.vue'
import Topbar from './dashboard/Topbar.vue'

export default {
  name: 'DashboardLayout',
  components: {
    UserSidebar,
    Topbar
  },
  props: {
    pageTitle: {
      type: String,
      default: 'Dashboard'
    },
    noSidebar: {
      type: Boolean,
      default: false
    },
    darkTheme: {
      type: Boolean,
      default: false
    }
  },
  setup() {
    const isSidebarOpen = ref(false)
    const route = useRoute()

    // Close sidebar on mobile when route change
    watch(() => route.path, () => {
      isSidebarOpen.value = false
    })

    onMounted(() => {
      // KEEPING SCROLL FIX FOR NOW AS IT SEEMS CRITICAL FOR USER
      // But simplified
      document.body.style.overflow = 'auto'
      document.documentElement.style.overflow = 'auto'
      
      // Initialize Select2 if needed (jQuery support for legacy plugins)
      if (window.jQuery && window.jQuery.fn.select2) {
         // select2 init logic if needed
      }
    })

    return {
      isSidebarOpen
    }
  }
}
</script>
