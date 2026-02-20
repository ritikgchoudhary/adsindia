import axios from 'axios'
import { apiLoaderStart, apiLoaderStop } from './loader'

// Use relative URL in production, or VITE_API_URL if set
// In development, Vite proxy will handle /api -> localhost:8000
const getBaseURL = () => {
  if (import.meta.env.VITE_API_URL) {
    return import.meta.env.VITE_API_URL
  }
  // Use relative URL for production (same domain)
  // In development, Vite proxy handles /api -> localhost:8000
  return '/api'
}

const api = axios.create({
  baseURL: getBaseURL(),
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// Request interceptor to add auth token
api.interceptors.request.use(
  (config) => {
    // Global loader (skip if explicitly disabled)
    if (!config.__skipLoader) apiLoaderStart()

    // Check if it's an admin route
    const isAdminRoute = config.url?.startsWith('/admin') || config.url?.includes('/admin/')
    const tokenKey = isAdminRoute ? 'admin_token' : 'token'
    const token = localStorage.getItem(tokenKey)
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    apiLoaderStop()
    return Promise.reject(error)
  }
)

// Response interceptor to handle errors
api.interceptors.response.use(
  (response) => {
    apiLoaderStop()
    return response
  },
  (error) => {
    apiLoaderStop()
    if (error.response?.status === 401) {
      const isAdminRoute = error.config?.url?.startsWith('/admin') || error.config?.url?.includes('/admin/')
      if (isAdminRoute) {
        localStorage.removeItem('admin_token')
        window.location.href = (typeof window !== 'undefined' && window.location.pathname.startsWith('/master_admin')) ? '/master_admin/login' : '/admin/login'
      } else {
        localStorage.removeItem('token')
        window.location.href = '/login'
      }
    }
    return Promise.reject(error)
  }
)

export default api
