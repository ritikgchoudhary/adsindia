<template>
  <div class="dashboard position-relative">
    <div class="dashboard__inner flex-wrap">
      <UserSidebar />
      <div class="dashboard__right" :class="{ 'dashboard__right--dark': darkTheme }">
        <Topbar />
        <div class="container-fluid p-0">
          <div class="dashboard-body dashboard-body--content" :class="{ 'dashboard-body--dark': darkTheme }">
            <h4 class="page-header-title" :class="{ 'text-white': darkTheme }">{{ pageTitle }}</h4>
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
    },
    darkTheme: {
      type: Boolean,
      default: false
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
      const readabilityId = 'dashboard-readability'
      if (!document.getElementById(readabilityId)) {
        const readStyle = document.createElement('style')
        readStyle.id = readabilityId
        readStyle.textContent = `
          /* Override custom.css white text – main content must be dark on light bg */
          .dashboard .dashboard__right,
          .dashboard .dashboard-body {
            color: #1e293b !important;
          }
          .dashboard .dashboard-body .page-header-title,
          .dashboard .dashboard-body h4.page-header-title,
          .dashboard .dashboard-body h1,
          .dashboard .dashboard-body h2,
          .dashboard .dashboard-body h3,
          .dashboard .dashboard-body h4,
          .dashboard .dashboard-body h5,
          .dashboard .dashboard-body h6,
          .dashboard .dashboard-body .title,
          .dashboard .dashboard-body .section-main-title,
          .dashboard .dashboard-body .ads-overview-title {
            color: #0f172a !important;
            font-weight: 800 !important;
          }
          .dashboard .dashboard-body .section-sub-title,
          .dashboard .dashboard-body .ads-overview-subtitle {
            color: #1e293b !important;
            font-weight: 600 !important;
          }
          .dashboard .dashboard-body p,
          .dashboard .dashboard-body .small,
          .dashboard .dashboard-body .text-muted {
            color: #334155 !important;
          }
          .dashboard .dashboard-body .stat-label,
          .dashboard .dashboard-body .ads-card-label {
            color: #475569 !important;
          }
          .dashboard .dashboard-body .stat-value,
          .dashboard .dashboard-body .ads-card-value {
            color: #0f172a !important;
          }
          .dashboard .dashboard-body .premium-table th {
            color: #334155 !important;
          }
          .dashboard .dashboard-body .premium-table td,
          .dashboard .dashboard-body .dashboard-cell-strong {
            color: #1e293b !important;
          }
          .dashboard .dashboard-body .dashboard-cell-muted {
            color: #64748b !important;
          }
          .dashboard .dashboard-body .dashboard-empty-state {
            color: #475569 !important;
          }
          .dashboard .dashboard-body .btn-premium.btn-primary-premium {
            color: #ffffff !important;
          }
          .dashboard .dashboard-body .btn-withdraw {
            color: #4f46e5 !important;
          }
          .dashboard .dashboard-body a:not(.btn-withdraw):not(.btn-primary-premium).btn-premium {
            color: #0f172a !important;
          }
          .dashboard .dashboard-body .dashboard-section-heading {
            color: #0f172a !important;
          }
          .kyc-banner-wrapper .kyc-text-title {
            color: #0f172a !important;
          }
          .kyc-banner-wrapper .kyc-text-desc {
            color: #1e293b !important;
          }
          /* Cards & forms on all dashboard pages (Account KYC, etc.) */
          .dashboard .dashboard-body .card,
          .dashboard .dashboard-body .card-body,
          .dashboard .dashboard-body .card-header {
            color: #1e293b !important;
            background-color: #ffffff !important;
          }
          .dashboard .dashboard-body .card h1,
          .dashboard .dashboard-body .card h2,
          .dashboard .dashboard-body .card h3,
          .dashboard .dashboard-body .card h4,
          .dashboard .dashboard-body .card h5,
          .dashboard .dashboard-body .card h6,
          .dashboard .dashboard-body .card .form-label,
          .dashboard .dashboard-body .card label,
          .dashboard .dashboard-body .card p,
          .dashboard .dashboard-body .card small {
            color: #1e293b !important;
          }
          .dashboard .dashboard-body .card .form-control {
            color: #0f172a !important;
            background-color: #ffffff !important;
            border-color: #e2e8f0 !important;
          }
          .dashboard .dashboard-body .step-label {
            color: #475569 !important;
          }
          .dashboard .dashboard-body .account-kyc-heading,
          .dashboard .dashboard-body .account-kyc-label,
          .dashboard .dashboard-body .account-kyc-status-title {
            color: #0f172a !important;
          }
          .dashboard .dashboard-body .account-kyc-hint,
          .dashboard .dashboard-body .account-kyc-desc {
            color: #475569 !important;
          }
          .dashboard .dashboard-body .alert {
            color: #1e293b !important;
          }
          /* Dark theme dashboard – white text on dark bg, keep table/suggest/KYC readable */
          .dashboard__right--dark .dashboard-body {
            color: #ffffff !important;
          }
          .dashboard__right--dark .dashboard-body .page-header-title,
          .dashboard__right--dark .dashboard-body h1,
          .dashboard__right--dark .dashboard-body h2,
          .dashboard__right--dark .dashboard-body h3,
          .dashboard__right--dark .dashboard-body h4,
          .dashboard__right--dark .dashboard-body h5,
          .dashboard__right--dark .dashboard-body h6,
          .dashboard__right--dark .dashboard-body .dashboard-dark-title {
            color: #ffffff !important;
          }
          .dashboard__right--dark .dashboard-body .dashboard-dark-title i {
            color: #60a5fa !important;
          }
          .dashboard__right--dark .dashboard-body .dashboard-table-card .dashboard-dark-title,
          .dashboard__right--dark .dashboard-body .dashboard-table-card .dashboard-dark-title i {
            color: #0f172a !important;
          }
          .dashboard__right--dark .dashboard-body .dashboard-table-card th {
            color: #334155 !important;
          }
          .dashboard__right--dark .dashboard-body .dashboard-table-card td,
          .dashboard__right--dark .dashboard-body .dashboard-table-card .trx-date,
          .dashboard__right--dark .dashboard-body .dashboard-table-card .trx-balance {
            color: #1e293b !important;
          }
          .dashboard__right--dark .dashboard-body .dashboard-table-card .trx-date-muted,
          .dashboard__right--dark .dashboard-body .dashboard-table-card .trx-details,
          .dashboard__right--dark .dashboard-body .dashboard-table-card .suggest-empty {
            color: #64748b !important;
          }
          .dashboard__right--dark .dashboard-body .dashboard-table-card .suggest-icon {
            color: #94a3b8 !important;
          }
          .dashboard__right--dark .dashboard-body .dashboard-table-card .dashboard-view-all-link {
            color: #3b82f6 !important;
          }
          .dashboard__right--dark .dashboard-body .dashboard-table-card .dashboard-empty-state {
            color: #475569 !important;
          }
          .dashboard__right--dark .dashboard-body .gradient-card,
          .dashboard__right--dark .dashboard-body .gradient-card-label,
          .dashboard__right--dark .dashboard-body .gradient-card-value {
            color: #ffffff !important;
          }
          .dashboard__right--dark .kyc-banner-wrapper .kyc-text-title {
            color: #0f172a !important;
          }
          .dashboard__right--dark .kyc-banner-wrapper .kyc-text-desc {
            color: #1e293b !important;
          }
        `
        document.head.appendChild(readStyle)
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
/* Bulletproof Dashboard Layout Architecture */
:host {
  --sidebar-w: 280px;
}

.dashboard {
  font-family: 'Outfit', sans-serif !important;
  background-color: #f8fafc !important;
  min-height: 100vh !important;
  width: 100% !important;
  overflow: hidden !important; /* Contain everything */
  display: flex !important;
}

.dashboard__inner {
  display: flex !important;
  flex-direction: row !important;
  width: 100% !important;
  min-height: 100vh !important;
  flex-wrap: nowrap !important;
}

/* Sidebar: Fixed & Push */
:deep(.sidebar-menu) {
  width: 280px !important;
  min-width: 280px !important;
  max-width: 280px !important;
  flex: 0 0 280px !important;
  height: 100vh !important;
  position: sticky !important;
  top: 0 !important;
  z-index: 100 !important;
}

/* Content Area: Flexible but Contained - no margin (global main.css adds margin-left: 260px for old layout) */
.dashboard__right {
  flex: 1 !important;
  min-width: 0 !important; /* Allow shrinking */
  margin-left: 0 !important; /* Override main.css: remove gap between sidebar and content */
  background-color: #f8fafc !important;
  display: flex !important;
  flex-direction: column !important;
  height: 100vh !important;
  overflow-y: auto !important; /* Content scrolls independently */
  overflow-x: hidden !important;
}

.dashboard__right--dark {
  background: linear-gradient(180deg, #1e1b4b 0%, #312e81 35%, #1e293b 100%) !important;
}
.dashboard-body--dark .page-header-title.text-white {
  color: #ffffff !important;
  font-weight: 900 !important;
  font-size: 2rem !important;
}

.dashboard-body {
  padding: 3rem 2rem !important;
  width: 100% !important;
  flex: 1 !important;
}

.dashboard-body--content .page-header-title,
.page-header-title {
  font-weight: 900 !important;
  font-size: 2.25rem !important;
  color: #0f172a !important;
  margin-bottom: 2.5rem !important;
  letter-spacing: -0.04em !important;
  text-shadow: 0 0 0 #0f172a !important;
}

.dashboard-body--content {
  color: #1e293b !important;
}

/* Sidebar Logo: dark to match sidebar */
:deep(.sidebar-logo) {
  background: #1e293b !important;
  padding: 1.25rem 1.5rem !important;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
  margin-bottom: 1rem !important;
}
:deep(.sidebar-logo img) {
  filter: brightness(0) invert(1);
  opacity: 0.95;
}
:deep(.sidebar-logo .sidebar-logo-text) {
  color: #f1f5f9 !important;
}

/* Mobile: Overlay Mode */
@media (max-width: 991px) {
  :deep(.sidebar-menu) {
    position: fixed !important;
    left: -280px !important;
    top: 0 !important;
    z-index: 1000 !important;
    transition: left 0.3s ease !important;
  }
  
  :deep(.sidebar-menu.show-sidebar) {
    left: 0 !important;
  }

  .dashboard__right {
    height: auto !important;
    overflow-y: visible !important;
    width: 100% !important;
  }
}
</style>
