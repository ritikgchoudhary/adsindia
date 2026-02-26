import { createRouter, createWebHistory } from 'vue-router'
import { routeLoaderStart, routeLoaderStop, routeLoaderReset, loaderReset } from '../services/loader'
import { getStoredRef, setStoredRef } from '../services/referralStore'
import Home from '../views/Home.vue'
import Login from '../views/Login.vue'
import Register from '../views/Register.vue'
import Dashboard from '../views/Dashboard.vue'
import Campaigns from '../views/Campaigns.vue'
import CampaignDetails from '../views/CampaignDetails.vue'
import Contact from '../views/Contact.vue'
import Blogs from '../views/Blogs.vue'
import BlogDetails from '../views/BlogDetails.vue'
import AllCourses from '../views/AllCourses.vue'

const routes = [
  {
    path: '/',
    name: 'Home',
    component: Home
  },
  {
    path: '/login',
    name: 'Login',
    component: Login
  },
  {
    path: '/user/login',
    name: 'UserLogin',
    component: Login
  },
  {
    path: '/register',
    name: 'Register',
    component: Register
  },
  {
    path: '/user/register',
    name: 'UserRegister',
    component: Register
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: Dashboard,
    meta: { requiresAuth: true }
  },
  {
    path: '/user/dashboard',
    name: 'UserDashboard',
    component: Dashboard,
    meta: { requiresAuth: true }
  },
  {
    path: '/user/home',
    name: 'UserHome',
    component: Dashboard,
    meta: { requiresAuth: true }
  },
  {
    path: '/campaigns',
    name: 'Campaigns',
    component: Campaigns
  },
  {
    path: '/campaign/:slug',
    name: 'CampaignDetails',
    component: CampaignDetails
  },
  {
    path: '/contact',
    name: 'Contact',
    redirect: '/#contact'
  },
  {
    path: '/blogs',
    name: 'Blogs',
    component: Blogs
  },
  {
    path: '/blog/:slug',
    name: 'BlogDetails',
    component: BlogDetails
  },
  {
    path: '/courses',
    name: 'AllCourses',
    component: AllCourses
  },
  {
    path: '/policy/:slug',
    name: 'Policy',
    component: () => import('../views/Policy.vue')
  },
  {
    path: '/user/password/reset',
    name: 'PasswordReset',
    component: () => import('../views/ForgotPassword.vue')
  },
  {
    path: '/user/password/request',
    name: 'PasswordRequest',
    component: () => import('../views/ForgotPassword.vue')
  },
  {
    path: '/user/logout',
    name: 'UserLogout',
    beforeEnter: async (to, from, next) => {
      try {
        const token = localStorage.getItem('token')
        if (token) {
          // Call logout API
          const response = await fetch('/api/logout', {
            method: 'POST',
            headers: {
              'Authorization': `Bearer ${token}`,
              'Content-Type': 'application/json'
            }
          })
          localStorage.removeItem('token')
        }
      } catch (error) {
        console.error('Logout error:', error)
      }
      next({ name: 'Home' })
    }
  },
  // User Dashboard Pages
  {
    path: '/user/conversion-log',
    name: 'ConversionLog',
    component: () => import('../views/user/ConversionLog.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/user/profile-setting',
    name: 'ProfileSetting',
    component: () => import('../views/user/ProfileSetting.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/user/change-password',
    name: 'ChangePassword',
    component: () => import('../views/user/ChangePassword.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/user/twofactor',
    name: 'TwoFactor',
    component: () => import('../views/user/TwoFactor.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/user/withdraw',
    name: 'Withdraw',
    component: () => import('../views/user/Withdraw.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/user/withdraw/history',
    name: 'WithdrawHistory',
    component: () => import('../views/user/WithdrawHistory.vue'),
    meta: { requiresAuth: true }
  },
  /* {
    path: '/user/deposit/history',
    name: 'DepositHistory',
    component: () => import('../views/user/DepositHistory.vue'),
    meta: { requiresAuth: true }
  }, */
  {
    path: '/user/kyc-form',
    name: 'KYCForm',
    component: () => import('../views/user/KYCForm.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/user/kyc-data',
    name: 'KYCData',
    component: () => import('../views/user/KYCData.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/user/ads-work',
    name: 'AdsWork',
    component: () => import('../views/user/AdsWork.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/user/account-kyc',
    name: 'AccountKYC',
    component: () => import('../views/user/AccountKYC.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/user/packages',
    name: 'Packages',
    component: () => import('../views/user/Packages.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/user/payment-redirect',
    name: 'PaymentRedirect',
    component: () => import('../views/user/PaymentRedirect.vue'),
    meta: { requiresAuth: false }
  },
  {
    path: '/user/package-payment',
    redirect: '/user/packages'
  },
  {
    path: '/user/upgrade-package',
    redirect: '/user/packages'
  },
  {
    path: '/user/ad-plans',
    name: 'AdPlans',
    component: () => import('../views/user/AdPlans.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/user/ad-plans/payment',
    redirect: (to) => ({
      path: '/user/payment-redirect',
      query: { ...to.query, flow: 'ad_plan' }
    })
  },
  {
    path: '/register/payment',
    redirect: (to) => ({
      path: '/user/payment-redirect',
      query: { ...to.query, flow: 'registration' }
    })
  },
  {
    path: '/user/courses',
    name: 'Courses',
    component: () => import('../views/user/Courses.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/user/courses/:id',
    name: 'CourseDetail',
    component: () => import('../views/user/CourseDetail.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/user/referral',
    name: 'Referral',
    component: () => import('../views/user/Referral.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/user/affiliate-income',
    name: 'AffiliateIncome',
    component: () => import('../views/user/AffiliateIncome.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/user/affiliate-withdraw',
    name: 'AffiliateWithdraw',
    component: () => import('../views/user/AffiliateWithdraw.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/user/affiliate-withdraw/history',
    name: 'AffiliateWithdrawHistory',
    component: () => import('../views/user/AffiliateWithdrawHistory.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/user/partner-program',
    name: 'PartnerProgram',
    component: () => import('../views/user/PartnerProgram.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/user/certificates',
    name: 'Certificates',
    component: () => import('../views/user/Certificates.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/user/customer-support',
    name: 'CustomerSupport',
    component: () => import('../views/user/CustomerSupport.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/user/leaderboard',
    name: 'Leaderboard',
    component: () => import('../views/user/Leaderboard.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/user/quick-payment',
    name: 'QuickPayment',
    component: () => import('../views/user/QuickPayment.vue'),
    meta: { requiresAuth: true }
  },
  { path: '/user/vip-membership', name: 'UserVipMembership', component: () => import('../views/user/VipMembership.vue'), meta: { requiresAuth: true } },
  { path: '/user/verified-badge', name: 'UserVerifiedBadge', component: () => import('../views/user/VerifiedBadge.vue'), meta: { requiresAuth: true } },
  { path: '/user/ad-booster', name: 'UserAdBooster', component: () => import('../views/user/AdBooster.vue'), meta: { requiresAuth: true } },
  // Admin Routes
  {
    path: '/admin/login',
    name: 'AdminLogin',
    component: () => import('../views/admin/AdminLogin.vue'),
    meta: { requiresAdminAuth: false }
  },
  {
    path: '/admin/dashboard',
    name: 'AdminDashboard',
    component: () => import('../views/admin/Dashboard.vue'),
    meta: { requiresAdminAuth: true }
  },
  {
    path: '/admin/courses',
    name: 'AdminCourses',
    component: () => import('../views/admin/Courses.vue'),
    meta: { requiresAdminAuth: true }
  },
  {
    path: '/admin/packages',
    name: 'AdminPackages',
    component: () => import('../views/admin/packages/Packages.vue'),
    meta: { requiresAdminAuth: true }
  },
  {
    path: '/admin/packages/create',
    name: 'AdminPackageCreate',
    component: () => import('../views/admin/packages/CreatePackage.vue'),
    meta: { requiresAdminAuth: true }
  },
  {
    path: '/admin/packages/edit/:id',
    name: 'AdminPackageEdit',
    component: () => import('../views/admin/packages/EditPackage.vue'),
    meta: { requiresAdminAuth: true }
  },
  {
    path: '/admin/courses/create',
    name: 'AdminCourseCreate',
    component: () => import('../views/admin/CourseCreate.vue'),
    meta: { requiresAdminAuth: true }
  },
  {
    path: '/admin/courses/edit/:id',
    name: 'AdminCourseEdit',
    component: () => import('../views/admin/CourseEdit.vue'),
    meta: { requiresAdminAuth: true }
  },
  { path: '/master_admin', redirect: '/master_admin/dashboard' },
  { path: '/master_admin/login', name: 'MasterAdminLogin', component: () => import('../views/master_admin/MasterAdminLogin.vue'), meta: { requiresMasterAdminAuth: false } },
  { path: '/master_admin/dashboard', name: 'MasterAdminDashboard', component: () => import('../views/master_admin/Dashboard.vue'), meta: { requiresMasterAdminAuth: true } },
  { path: '/master_admin/users', name: 'MasterAdminUsers', component: () => import('../views/master_admin/Users.vue'), meta: { requiresMasterAdminAuth: true } },
  { path: '/master_admin/orders', name: 'MasterAdminOrders', component: () => import('../views/master_admin/Orders.vue'), meta: { requiresMasterAdminAuth: true } },
  { path: '/master_admin/deposits', name: 'MasterAdminDeposits', component: () => import('../views/master_admin/Deposits.vue'), meta: { requiresMasterAdminAuth: true } },
  { path: '/master_admin/withdrawals', name: 'MasterAdminWithdrawals', component: () => import('../views/master_admin/Withdrawals.vue'), meta: { requiresMasterAdminAuth: true } },
  { path: '/master_admin/transactions', name: 'MasterAdminTransactions', component: () => import('../views/master_admin/Transactions.vue'), meta: { requiresMasterAdminAuth: true } },
  { path: '/master_admin/commissions', name: 'MasterAdminCommissions', component: () => import('../views/master_admin/CommissionManagement.vue'), meta: { requiresMasterAdminAuth: true } },
  { path: '/master_admin/account-ledger', name: 'MasterAdminAccountLedger', component: () => import('../views/master_admin/AccountLedger.vue'), meta: { requiresMasterAdminAuth: true } },
  { path: '/master_admin/special-links', name: 'MasterAdminSpecialLinks', component: () => import('../views/master_admin/SpecialLinks.vue'), meta: { requiresMasterAdminAuth: true } },
  { path: '/master_admin/kyc', name: 'MasterAdminKYC', component: () => import('../views/master_admin/KYC.vue'), meta: { requiresMasterAdminAuth: true } },
  { path: '/master_admin/reports', name: 'MasterAdminReports', component: () => import('../views/master_admin/Reports.vue'), meta: { requiresMasterAdminAuth: true } },
  { path: '/master_admin/admins', name: 'MasterAdminAdmins', component: () => import('../views/master_admin/Admins.vue'), meta: { requiresMasterAdminAuth: true } },
  { path: '/master_admin/settings', name: 'MasterAdminSettings', component: () => import('../views/master_admin/Settings.vue'), meta: { requiresMasterAdminAuth: true } },
  { path: '/master_admin/courses', name: 'MasterAdminCourses', component: () => import('../views/master_admin/Courses.vue'), meta: { requiresMasterAdminAuth: true } },
  { path: '/master_admin/gateways', name: 'MasterAdminGateways', component: () => import('../views/master_admin/Gateways.vue'), meta: { requiresMasterAdminAuth: true } },
  { path: '/master_admin/customer-support', name: 'MasterAdminCustomerSupport', component: () => import('../views/master_admin/CustomerSupport.vue'), meta: { requiresMasterAdminAuth: true } },
  { path: '/master_admin/email-settings', name: 'MasterAdminEmailSettings', component: () => import('../views/master_admin/EmailSettings.vue'), meta: { requiresMasterAdminAuth: true } },
  { path: '/master_admin/beta-features', name: 'MasterAdminBetaFeatures', component: () => import('../views/master_admin/BetaFeaturesHub.vue'), meta: { requiresMasterAdminAuth: true } },
  { path: '/master_admin/beta-features/vip', name: 'MasterAdminBetaVIP', component: () => import('../views/master_admin/BetaVIP.vue'), meta: { requiresMasterAdminAuth: true } },
  { path: '/master_admin/beta-features/verified', name: 'MasterAdminBetaVerified', component: () => import('../views/master_admin/BetaVerified.vue'), meta: { requiresMasterAdminAuth: true } },
  { path: '/master_admin/beta-features/booster', name: 'MasterAdminBetaBooster', component: () => import('../views/master_admin/BetaBooster.vue'), meta: { requiresMasterAdminAuth: true } },
  { path: '/master_admin/beta-features/instant', name: 'MasterAdminBetaInstant', component: () => import('../views/master_admin/BetaInstant.vue'), meta: { requiresMasterAdminAuth: true } },
  { path: '/master_admin/beta-features/extra', name: 'MasterAdminBetaExtra', component: () => import('../views/master_admin/BetaExtra.vue'), meta: { requiresMasterAdminAuth: true } }
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (to.hash) {
      return new Promise((resolve) => {
        setTimeout(() => {
          resolve({ el: to.hash, behavior: 'smooth', top: 80 })
        }, 350)
      })
    }
    if (savedPosition) return savedPosition
    return { top: 0 }
  }
})

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')
  const adminToken = localStorage.getItem('admin_token')

  // Persist referral code from URL anywhere in the site.
  // (So user can browse and later register with ref attached automatically.)
  try {
    const ref = to?.query?.ref
    if (ref) setStoredRef(ref)
  } catch (_) { }

  // Auto-attach stored ref on register routes if missing
  try {
    const storedRef = getStoredRef()
    const isRegister = to?.path === '/register' || to?.path === '/user/register'
    if (isRegister && storedRef && !to?.query?.ref) {
      next({
        path: to.path,
        query: { ...(to.query || {}), ref: storedRef },
        hash: to.hash,
      })
      return
    }
  } catch (_) { }

  // Master Admin routes (same token as admin)
  if (to.meta.requiresMasterAdminAuth) {
    if (!adminToken) {
      next({ name: 'MasterAdminLogin' })
    } else {
      next()
    }
    return
  }
  if (to.name === 'MasterAdminLogin' && adminToken) {
    next({ name: 'MasterAdminDashboard' })
    return
  }
  // Admin routes
  if (to.meta.requiresAdminAuth) {
    if (!adminToken) {
      next({ name: 'AdminLogin' })
    } else {
      next()
    }
    return
  }
  if (to.name === 'AdminLogin' && adminToken) {
    next({ name: 'AdminDashboard' })
    return
  }
  // User routes
  if (to.meta.requiresAuth && !token) {
    next({ name: 'Login' })
  } else {
    next()
  }
})

// Show loader only when navigation is resolving (avoids redirect-stuck)
router.beforeResolve((to, from, next) => {
  if (to.fullPath !== from.fullPath) {
    routeLoaderReset()
    routeLoaderStart()
  }
  next()
})

router.afterEach(() => {
  routeLoaderStop()
})

router.onError(() => {
  // ensure loader isn't stuck
  routeLoaderReset()
  loaderReset()
})

export default router
