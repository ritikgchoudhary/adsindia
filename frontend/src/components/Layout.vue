<template>
  <div class="app-layout">
    <!-- Dynamic Color CSS -->
    <DynamicColorCSS />

    <div class="body-overlay"></div>
    <div class="sidebar-overlay"></div>
    <!-- Scroll Top Removed -->

    <!-- Header - Hide on dashboard or if meta says noHeader -->
    <Header v-if="!isDashboardRoute && !route.meta.noHeader" />

    <!-- Main Content -->
    <main>
      <slot />
    </main>

    <!-- Footer - Hide on dashboard or if meta says noFooter -->
    <Footer v-if="!isDashboardRoute && !route.meta.noFooter" />

    <!-- Cookie Notice -->
    <CookieNotice v-if="showCookieNotice && !isDashboardRoute && !route.meta.noFooter" />
  </div>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import Header from './Header.vue'
import Footer from './Footer.vue'
import CookieNotice from './CookieNotice.vue'
import DynamicColorCSS from './DynamicColorCSS.vue'

export default {
  name: 'Layout',
  components: {
    Header,
    Footer,
    CookieNotice,
    DynamicColorCSS
  },
  setup() {
    const route = useRoute()
    const showCookieNotice = ref(false)

    // Check if current route is a dashboard route (or admin / master_admin – no header/footer)
    const isDashboardRoute = computed(() => {
      const path = route.path
      return path.startsWith('/dashboard') || path.startsWith('/user/') ||
        path.startsWith('/admin') || path.startsWith('/master_admin') ||
        path.startsWith('/offer') || path.startsWith('/wa')
    })

    // Add body class for master admin - enables global CSS overrides
    const isMasterAdminRoute = computed(() => route.path.startsWith('/master_admin'))
    
    // Add body class for user dashboard - enables global CSS overrides
    const isUserDashboardRoute = computed(() => route.path.startsWith('/user/') || route.path === '/dashboard')

    onMounted(() => {
      // Ensure landing page can scroll: remove any body scroll lock from template CSS
      if (!route.path.startsWith('/dashboard') && !route.path.startsWith('/user/') && !route.path.startsWith('/admin') && !route.path.startsWith('/master_admin')) {
        document.body.classList.remove('scroll-hide-sm', 'scroll-hide')
        document.body.style.overflow = ''
        document.body.style.position = ''
        document.documentElement.style.overflow = ''
      }


      // Initialize jQuery scripts
      if (window.jQuery) {
        const $ = window.jQuery
        
        // Fixed header logic
        $(window).on("scroll", function () {
          if ($(window).scrollTop() >= 100) {
            $(".header").addClass("fixed-header");
          } else {
            $(".header").removeClass("fixed-header");
          }
        });

        // Language selector
        $(".langSel").on("change", function() {
          window.location.href = "/change/" + $(this).val();
        });

        // Sidebar and Overlay
        $(".header-button").on("click", function () {
          $(".body-overlay").toggleClass("show");
        });
        $(".body-overlay").on("click", function () {
          $(".header-button").trigger("click");
          $(this).removeClass("show");
        });
        
        // Dashboard bar icon
        $(document).on("click", ".dashboard-body__bar-icon", function () {
            $(".sidebar-menu").addClass("show-sidebar");
            $(".sidebar-overlay").addClass("show");
        });
        $(document).on("click", ".sidebar-menu__close, .sidebar-overlay", function () {
            $(".sidebar-menu").removeClass("show-sidebar");
            $(".sidebar-overlay").removeClass("show");
        });
      }

      // Check cookie notice
      if (!getCookie('gdpr_cookie')) {
        setTimeout(() => {
          showCookieNotice.value = true
        }, 2000)
      }
    })

    const getCookie = (name) => {
      const value = `; ${document.cookie}`;
      const parts = value.split(`; ${name}=`);
      if (parts.length === 2) return parts.pop().split(';').shift();
    }

    // Watch route to add/remove master-admin body class
    watch(isMasterAdminRoute, (val) => {
      if (val) {
        document.body.classList.add('master-admin-route')
      } else {
        document.body.classList.remove('master-admin-route')
      }
    }, { immediate: true })
    
    // Watch route to add/remove user-dashboard body class
    watch(isUserDashboardRoute, (val) => {
      if (val) {
        document.body.classList.add('user-dashboard-route')
      } else {
        document.body.classList.remove('user-dashboard-route')
      }
    }, { immediate: true })

    return {
      showCookieNotice,
      isDashboardRoute,
      route
    }
  }
}
</script>
