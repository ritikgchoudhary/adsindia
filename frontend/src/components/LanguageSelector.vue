<template>
  <div class="custom--dropdown" :class="{ open: isOpen }" ref="dropdown">
    <div class="custom--dropdown__selected dropdown-list__item" @click="toggleDropdown">
      <div class="icon thumb me-1">
        <img :src="currentLang?.image" :alt="currentLang?.name">
      </div>
      <span class="text">
        {{ currentLang?.name || 'Language' }}
      </span>
    </div>
    <ul class="dropdown-list">
      <li 
        v-for="lang in languages" 
        :key="lang.code" 
        class="dropdown-list__item" 
        :class="{ active: lang.code === currentLang?.code }"
        @click="selectLanguage(lang)"
      >
        <div class="thumb">
          <img :src="lang.image" :alt="lang.name">
          <span class="text">{{ lang.name }}</span>
        </div>
      </li>
    </ul>
  </div>
</template>

<script>
import { ref, onMounted, onUnmounted } from 'vue'

export default {
  name: 'LanguageSelector',
  setup() {
    const isOpen = ref(false)
    const languages = ref([
      { name: 'English', code: 'en', image: '/assets/images/language/63e481f08f0a11675919856.png' },
      // Add more languages as needed or fetch from API
    ])
    const currentLang = ref(languages.value[0])
    const dropdown = ref(null)

    const toggleDropdown = () => {
      isOpen.value = !isOpen.value
    }

    const selectLanguage = (lang) => {
      currentLang.value = lang
      isOpen.value = false
      // For now just console log, actual implementation would redirect or change locale
      console.log('Language changed to:', lang.code)
      window.location.href = `/change/${lang.code}`
    }

    const closeDropdown = (e) => {
      if (dropdown.value && !dropdown.value.contains(e.target)) {
        isOpen.value = false
      }
    }

    onMounted(() => {
      document.addEventListener('click', closeDropdown)
    })

    onUnmounted(() => {
      document.removeEventListener('click', closeDropdown)
    })

    return {
      isOpen,
      languages,
      currentLang,
      dropdown,
      toggleDropdown,
      selectLanguage
    }
  }
}
</script>

<style scoped>
/* Styles are provided by main.css */
</style>
