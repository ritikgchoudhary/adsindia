<template>
  <link v-if="colorCssUrl" :href="colorCssUrl" rel="stylesheet" />
</template>

<script>
import { ref, onMounted } from 'vue'
import { appService } from '../services/appService'

export default {
  name: 'DynamicColorCSS',
  setup() {
    const colorCssUrl = ref('')

    onMounted(async () => {
      try {
        const response = await appService.getGeneralSettings()
        const baseColor = response.data?.base_color || '#667eea'
        const secondColor = response.data?.secondary_color || '#764ba2'
        colorCssUrl.value = `/assets/templates/basic/css/color.php?color=${encodeURIComponent(baseColor)}&secondColor=${encodeURIComponent(secondColor)}`
      } catch (error) {
        console.error('Error loading color CSS:', error)
        // Fallback
        colorCssUrl.value = '/assets/templates/basic/css/color.php?color=#667eea&secondColor=#764ba2'
      }
    })

    return {
      colorCssUrl
    }
  }
}
</script>
