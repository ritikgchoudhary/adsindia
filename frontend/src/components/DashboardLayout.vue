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
      // Fix scrolling issue - ensure page can scroll
      setTimeout(() => {
        // Fix body and html
        document.body.style.overflow = 'auto'
        document.body.style.height = 'auto'
        document.body.style.position = 'relative'
        document.documentElement.style.overflow = 'auto'
        document.documentElement.style.height = 'auto'
        
        // Fix dashboard elements
        const dashboardBody = document.querySelector('.dashboard-body')
        if (dashboardBody) {
          dashboardBody.style.overflow = 'visible'
          dashboardBody.style.overflowY = 'visible'
          dashboardBody.style.height = 'auto'
          dashboardBody.style.minHeight = 'auto'
          dashboardBody.style.maxHeight = 'none'
          dashboardBody.style.position = 'relative'
        }
        
        const dashboardRight = document.querySelector('.dashboard__right')
        if (dashboardRight) {
          dashboardRight.style.overflow = 'visible'
          dashboardRight.style.overflowY = 'visible'
          dashboardRight.style.overflowX = 'hidden'
          dashboardRight.style.height = 'auto'
          dashboardRight.style.position = 'relative'
        }
        
        const dashboard = document.querySelector('.dashboard')
        if (dashboard) {
          dashboard.style.overflow = 'visible'
          dashboard.style.height = 'auto'
          dashboard.style.position = 'relative'
        }
        
        const dashboardInner = document.querySelector('.dashboard__inner')
        if (dashboardInner) {
          dashboardInner.style.overflow = 'visible'
          dashboardInner.style.height = 'auto'
        }
        
        const containerFluid = document.querySelector('.container-fluid')
        if (containerFluid) {
          containerFluid.style.overflow = 'visible'
          containerFluid.style.height = 'auto'
        }
      }, 200)
      
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
