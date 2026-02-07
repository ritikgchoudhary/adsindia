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
      // Initialize jQuery scripts
      if (window.jQuery) {
        const $ = window.jQuery
        
        // Language selector
        $(".langSel").on("change", function() {
          window.location.href = "/change/" + $(this).val();
        });

        // Form label attributes
        let inputElements = $('[type=text],[type=password],select,textarea');
        $.each(inputElements, function(index, element) {
          element = $(element);
          element.closest('.form-group').find('label').attr('for', element.attr('name'));
          element.attr('id', element.attr('name'));
        });

        // Required fields
        $.each($('input:not([type=checkbox]):not([type=hidden]), select, textarea'), function(i, element) {
          if (element.hasAttribute('required')) {
            $(element).closest('.form-group').find('label').addClass('required');
          }
        });

        // Select2
        $('.select2').each(function() {
          $(this).select2();
        });

        $('.select2-basic').each(function() {
          $(this).select2({
            dropdownParent: $(this).closest('.select2-parent')
          });
        });

        // Responsive tables
        Array.from(document.querySelectorAll('table')).forEach(table => {
          let heading = table.querySelectorAll('thead tr th');
          Array.from(table.querySelectorAll('tbody tr')).forEach((row) => {
            Array.from(row.querySelectorAll('td')).forEach((colum, i) => {
              colum.setAttribute('data-label', heading[i].innerText)
            });
          });
        });

        // Bootstrap tooltips
        document.addEventListener('DOMContentLoaded', function() {
          const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
          tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
          });
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
