<template>
  <div class="dashboard position-relative">
    <div class="dashboard__inner flex-wrap">
      <UserSidebar />
      <div class="dashboard__right">
        <Topbar />
        <div class="container-fluid p-0">
          <div class="dashboard-body">
            <h4 class="title">{{ pageTitle }}</h4>
            <slot />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
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
    }
  },
  setup() {
    onMounted(() => {
      // Inject global style to force scrolling
      const styleId = 'dashboard-scroll-fix'
      if (!document.getElementById(styleId)) {
        const style = document.createElement('style')
        style.id = styleId
        style.textContent = `
          html, body {
            overflow-y: auto !important;
            overflow-x: hidden !important;
            height: auto !important;
            position: relative !important;
          }
          .dashboard, .dashboard__inner, .dashboard__right, .dashboard-body, .container-fluid {
            overflow-y: visible !important;
            overflow-x: hidden !important;
            height: auto !important;
            min-height: auto !important;
            max-height: none !important;
            position: relative !important;
          }
        `
        document.head.appendChild(style)
      }
      
      // Function to fix scrolling
      const fixScrolling = () => {
        // Fix body and html
        document.body.style.setProperty('overflow', 'auto', 'important')
        document.body.style.setProperty('height', 'auto', 'important')
        document.body.style.setProperty('position', 'relative', 'important')
        document.documentElement.style.setProperty('overflow', 'auto', 'important')
        document.documentElement.style.setProperty('height', 'auto', 'important')
        
        // Fix all dashboard elements
        const elements = [
          '.dashboard-body',
          '.dashboard__right',
          '.dashboard',
          '.dashboard__inner',
          '.container-fluid'
        ]
        
        elements.forEach(selector => {
          const elements = document.querySelectorAll(selector)
          elements.forEach(el => {
            el.style.setProperty('overflow-y', 'visible', 'important')
            el.style.setProperty('overflow-x', 'hidden', 'important')
            el.style.setProperty('height', 'auto', 'important')
            el.style.setProperty('min-height', 'auto', 'important')
            el.style.setProperty('max-height', 'none', 'important')
            el.style.setProperty('position', 'relative', 'important')
          })
        })
      }
      
      // Fix immediately
      fixScrolling()
      
      // Fix after delays to catch late-loading CSS
      setTimeout(fixScrolling, 100)
      setTimeout(fixScrolling, 500)
      setTimeout(fixScrolling, 1000)
      
      // Keep fixing periodically to prevent any CSS from overriding
      const fixInterval = setInterval(fixScrolling, 2000)
      
      // Stop after 10 seconds
      setTimeout(() => clearInterval(fixInterval), 10000)
      
      // Initialize dashboard scripts
      if (window.jQuery) {
        const $ = window.jQuery

        // Sidebar toggle
        $('.dashboard-body__bar-icon').on('click', function() {
          $('.sidebar-menu').addClass('show-sidebar')
          $('.sidebar-overlay').addClass('show')
        })

        $('.sidebar-menu__close, .sidebar-overlay').on('click', function() {
          $('.sidebar-menu').removeClass('show-sidebar')
          $('.sidebar-overlay').removeClass('show')
        })

        // Dropdown toggle
        $('.sidebar-menu-list__item.has-dropdown > a').on('click', function(e) {
          e.preventDefault()
          $(this).parent().toggleClass('active')
          $(this).next('.sidebar-submenu').slideToggle()
        })

        // User info dropdown
        $('.user-info__button').on('click', function() {
          $(this).closest('.user-info').find('.user-info-dropdown').toggleClass('show')
        })

        $(document).on('click', function(e) {
          if (!$(e.target).closest('.user-info').length) {
            $('.user-info-dropdown').removeClass('show')
          }
        })

        // Format state for select2
        function formatState(state) {
          if (!state.id) return state.text

          let gatewayData = $(state.element).data()

          return $(
            `<div class="d-flex gap-2">
              ${gatewayData.imageSrc ? `
                <div class="select2-image-wrapper">
                  <img class="select2-image" src="${gatewayData.imageSrc}">
                </div>` : ''
              }
              <div class="select2-content">
                <p class="select2-title">${gatewayData.title}</p>
                <p class="select2-subtitle">${gatewayData.subtitle}</p>
              </div>
            </div>`
          )
        }

        // Show filter button
        $('.showFilterBtn').on('click', function() {
          $('.responsive-filter-card').slideToggle()
        })
      }
    })

    return {}
  }
}
</script>

<style scoped>
/* Force scrolling to work - Override any global CSS */
:deep(.dashboard-body) {
  overflow-y: visible !important;
  overflow-x: hidden !important;
  height: auto !important;
  min-height: auto !important;
  max-height: none !important;
  position: relative !important;
}

:deep(.dashboard__right) {
  overflow-y: visible !important;
  overflow-x: hidden !important;
  height: auto !important;
  position: relative !important;
}

:deep(.dashboard) {
  overflow: visible !important;
  height: auto !important;
  position: relative !important;
}

:deep(.dashboard__inner) {
  overflow: visible !important;
  height: auto !important;
  position: relative !important;
}

:deep(.container-fluid) {
  overflow: visible !important;
  height: auto !important;
}

/* Ensure body and html can scroll */
:deep(html) {
  overflow-y: auto !important;
  overflow-x: hidden !important;
  height: auto !important;
}

:deep(body) {
  overflow-y: auto !important;
  overflow-x: hidden !important;
  height: auto !important;
  position: relative !important;
}
</style>
