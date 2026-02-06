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
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')
  
  if (to.meta.requiresAuth && !token) {
    next({ name: 'Login' })
  } else {
    next()
  }
})

export default router
