import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'

const app = createApp(App)

app.use(createPinia())
app.use(router)

app.mount('#app')

// Initialize notification system after DOM is ready
document.addEventListener('DOMContentLoaded', function() {
  // Make sure jQuery and iziToast are loaded
  if (typeof window.jQuery !== 'undefined' && typeof window.iziToast !== 'undefined') {
    window.notify = function(status, message) {
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
