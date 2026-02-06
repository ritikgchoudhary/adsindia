<template>
  <div class="counter-section my-120" v-if="counters.length">
    <div class="container">
      <div class="row g-4 justify-content-center counter-wrapper">
        <div class="col-xl-3 col-lg-4 col-sm-6 col-xsm-6" v-for="(counter, index) in counters" :key="index">
          <div class="counter-item">
            <div class="counter-item__number">
              <h3 class="counter-item__title">
                <span class="odometer" :data-odometer-final="counter.counter_value || 0">
                  0
                </span>
                {{ counter.counter_suffix || '' }}
              </h3>
            </div>
            <span class="counter-item__text">
              {{ counter.counter_title || '' }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, onUnmounted } from 'vue'
import { appService } from '../../services/appService'

export default {
  name: 'CounterSection',
  setup() {
    const counters = ref([])

    const initOdometer = () => {
      if (typeof window.jQuery !== 'undefined' && typeof window.odometer !== 'undefined') {
        const $ = window.jQuery
        $('.counter-item').each(function() {
          if ($(this).data('odometer-initialized')) return
          $(this).data('odometer-initialized', true)
          
          $(this).isInViewport(function(status) {
            if (status === 'entered') {
              document.querySelectorAll('.odometer').forEach((el) => {
                if (!el.dataset.odometerAnimated) {
                  el.innerHTML = el.getAttribute('data-odometer-final')
                  el.dataset.odometerAnimated = 'true'
                }
              })
            }
          })
        })
      }
    }

    onMounted(async () => {
      try {
        const response = await appService.getSections('counter_section.element')
        counters.value = response.data || []
        
        // Load odometer CSS and JS if not loaded
        if (!document.querySelector('link[href*="odometer.css"]')) {
          const link = document.createElement('link')
          link.rel = 'stylesheet'
          link.href = '/assets/templates/basic/css/odometer.css'
          document.head.appendChild(link)
        }

        if (!document.querySelector('script[src*="odometer"]')) {
          const scripts = [
            '/assets/templates/basic/js/odometer.min.js',
            '/assets/templates/basic/js/viewport.jquery.js'
          ]
          scripts.forEach((src, index) => {
            const script = document.createElement('script')
            script.src = src
            script.onload = () => {
              if (index === scripts.length - 1) {
                setTimeout(initOdometer, 100)
              }
            }
            document.body.appendChild(script)
          })
        } else {
          setTimeout(initOdometer, 100)
        }
      } catch (error) {
        console.error('Error loading counters:', error)
      }
    })

    return {
      counters
    }
  }
}
</script>
