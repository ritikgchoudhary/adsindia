import { createRouter, createWebHistory } from 'vue-router'
import Home from '../views/Home.vue'
import Login from '../views/Login.vue'
import Register from '../views/Register.vue'
import Dashboard from '../views/Dashboard.vue'
import Campaigns from '../views/Campaigns.vue'
import CampaignDetails from '../views/CampaignDetails.vue'
import Contact from '../views/Contact.vue'
import Blogs from '../views/Blogs.vue'
import BlogDetails from '../views/BlogDetails.vue'

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
    component: Contact
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
    path: '/user/transactions',
    name: 'Transactions',
    component: () => import('../views/user/Transactions.vue'),
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
  {
    path: '/user/ticket',
    name: 'SupportTickets',
    component: () => import('../views/user/SupportTickets.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/user/ticket/open',
    name: 'OpenTicket',
    component: () => import('../views/user/OpenTicket.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/user/ticket/:ticket',
    name: 'ViewTicket',
    component: () => import('../views/user/ViewTicket.vue'),
    meta: { requiresAuth: true }
  },
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
          name: 'AdPlanPayment',
          component: () => import('../views/user/AdPlanPayment.vue'),
          meta: { requiresAuth: true }
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
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')
  const adminToken = localStorage.getItem('admin_token')
  
  // Admin routes
  if (to.meta.requiresAdminAuth) {
    if (!adminToken) {
      next({ name: 'AdminLogin' })
    } else {
      next()
    }
  }
  // If admin is logged in and tries to access login page, redirect to dashboard
  else if (to.name === 'AdminLogin' && adminToken) {
    next({ name: 'AdminDashboard' })
  }
  // User routes
  else if (to.meta.requiresAuth && !token) {
    next({ name: 'Login' })
  } else {
    next()
  }
})

export default router
