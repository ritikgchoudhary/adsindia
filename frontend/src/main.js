import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'

const app = createApp(App)

app.use(createPinia())
app.use(router)

app.config.globalProperties.$getImage = (path, filename) => {
  if (!filename) return '/assets/images/default.png';
  if (filename.startsWith('http')) return filename;

  // Handle some common aliases or direct paths
  if (path === 'campaign') return `/assets/images/campaign/${filename}`;
  if (path === 'category') return `/assets/images/language/${filename}`;
  if (path === 'logo') return `/assets/images/logo_icon/${filename}`;

  // Default to frontend section path if path is just the section name
  if (!path.includes('/')) {
    return `/assets/images/frontend/${path}/${filename}`;
  }

  // Otherwise use the path as provided
  return `/assets/${path}/${filename}`;
}

app.mount('#app')

// Initialize notification system after DOM is ready
document.addEventListener('DOMContentLoaded', function () {
  // Make sure jQuery and iziToast are loaded
  if (typeof window.jQuery !== 'undefined' && typeof window.iziToast !== 'undefined') {
    window.notify = function (status, message) {
      const colors = {
        success: '#28c76f',
        error: '#eb2222',
        warning: '#ff9f43',
        info: '#1e9ff2',
      };

      const icons = {
        success: 'fas fa-check-circle',
        error: 'fas fa-times-circle',
        warning: 'fas fa-exclamation-triangle',
        info: 'fas fa-exclamation-circle',
      };

      window.iziToast[status]({
        title: status.charAt(0).toUpperCase() + status.slice(1),
        message: message,
        position: "topRight",
        backgroundColor: '#1e1e1e',
        icon: icons[status],
        iconColor: colors[status],
        progressBarColor: colors[status],
        titleSize: '1rem',
        messageSize: '1rem',
        titleColor: '#ffffff',
        messageColor: '#cccccc',
        transitionIn: 'bounceInLeft',
        theme: 'dark'
      });
    }
  }
})
