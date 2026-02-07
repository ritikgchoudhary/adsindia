<template>
  <div class="app-layout">
    <!-- Dynamic Color CSS -->
    <DynamicColorCSS />
    
    <!-- Preloader -->
    <div class="preloader">
      <div class="lds-roller">
        <div></div>
        <div></div>
        <div></div>
        <div></div>
      </div>
    </div>
    
    <div class="body-overlay"></div>
    <div class="sidebar-overlay"></div>
    <a class="scroll-top"><i class="fas fa-angle-double-up"></i></a>

    <!-- Header - Hide on dashboard routes -->
    <Header v-if="!isDashboardRoute" />

    <!-- Main Content -->
    <main>
      <slot />
    </main>

    <!-- Footer - Hide on dashboard routes -->
    <Footer v-if="!isDashboardRoute" />

    <!-- Cookie Notice -->
    <CookieNotice v-if="showCookieNotice && !isDashboardRoute" />
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
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

    // Check if current route is a dashboard route
    const isDashboardRoute = computed(() => {
      const path = route.path
      return path.startsWith('/dashboard') || path.startsWith('/user/')
    })

    onMounted(() => {
      // Hide preloader after data is loaded (or after a delay)
      setTimeout(() => {
        const preloader = document.querySelector('.preloader')
        if (preloader) {
          preloader.style.display = 'none'
        }
      }, 500)

      // Initialize jQuery scripts
      if (window.jQuery) {
        const $ = window.jQuery
        
        // Scroll to Top logic
        const btn = $(".scroll-top");
        $(window).on("scroll", function () {
          if ($(window).scrollTop() >= 100) {
            $(".header").addClass("fixed-header");
            btn.addClass("show");
          } else {
            $(".header").removeClass("fixed-header");
            btn.removeClass("show");
          }
        });

        btn.on("click", function (e) {
          e.preventDefault();
          $("html, body").animate({ scrollTop: 0 }, "300");
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

    return {
      showCookieNotice,
      isDashboardRoute
    }
  }
}
</script>

<style>
/* Styles are loaded from external CSS files */
</style>
