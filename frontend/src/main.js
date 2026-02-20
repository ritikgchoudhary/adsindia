import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import { storeRefFromUrl } from './services/referralStore'

/* Master Admin global styles */
import './assets/master-admin-global.css'
/* Tailwind – used only in user panel components (content scoped in tailwind.config.js) */
import './assets/user-panel-tailwind.css'

// Fix: allow page scroll on load (remove template body lock that can block scroll)
if (typeof document !== 'undefined') {
  document.body.classList.remove('scroll-hide-sm', 'scroll-hide')
  document.body.style.overflow = ''
  document.body.style.position = ''
  document.documentElement.style.overflow = ''
}

// Referral persistence: if user lands with ?ref=ADSxxxx, store it
storeRefFromUrl()

const app = createApp(App)

app.use(createPinia())
app.use(router)

const assetBase = (typeof import.meta !== 'undefined' && import.meta.env?.VITE_ASSET_BASE) ? import.meta.env.VITE_ASSET_BASE.replace(/\/$/, '') : ''
app.config.globalProperties.$getImage = (path, filename) => {
  if (!filename && filename !== 0) return assetBase + '/assets/images/default.png';
  const f = String(filename).trim();
  if (!f) return assetBase + '/assets/images/default.png';
  if (f.startsWith('http')) return f;

  let p
  if (path === 'campaign') {
    const name = f.startsWith('thumb_') ? f : 'thumb_' + f;
    p = `/assets/images/campaign/${name}`;
  } else if (path === 'category') {
    p = `/assets/images/language/${f}`;
  } else if (path === 'logo') {
    p = `/assets/images/logo_icon/${f}`;
  } else if (!path.includes('/')) {
    p = `/assets/images/frontend/${path}/${f}`;
  } else {
    p = `/assets/${path}/${f}`;
  }
  return assetBase + p;
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
