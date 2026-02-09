<template>
  <div class="home-page">
    <!-- Banner Section -->
    <BannerSection />

    <!-- Dynamic Sections -->
    <component 
      v-for="(section, index) in sections" 
      :key="index"
      :is="getSectionComponent(section)"
    />
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { appService } from '../services/appService'
import BannerSection from '../components/sections/BannerSection.vue'
import CounterSection from '../components/sections/CounterSection.vue'
import AboutSection from '../components/sections/AboutSection.vue'
import CampaignsSection from '../components/sections/CampaignsSection.vue'
import WhyChooseUsSection from '../components/sections/WhyChooseUsSection.vue'
import WorkProcessSection from '../components/sections/WorkProcessSection.vue'
import TestimonialsSection from '../components/sections/TestimonialsSection.vue'
import BenefitSection from '../components/sections/BenefitSection.vue'
import FAQSection from '../components/sections/FAQSection.vue'
import CTASection from '../components/sections/CTASection.vue'
import CategorySection from '../components/sections/CategorySection.vue'
import PartnerSection from '../components/sections/PartnerSection.vue'
import BlogSection from '../components/sections/BlogSection.vue'

export default {
  name: 'Home',
  components: {
    BannerSection,
    CounterSection,
    AboutSection,
    CampaignsSection,
    WhyChooseUsSection,
    WorkProcessSection,
    TestimonialsSection,
    BenefitSection,
    FAQSection,
    CTASection,
    CategorySection,
    PartnerSection,
    BlogSection
  },
  setup() {
    const sections = ref([])

    const getSectionComponent = (sectionName) => {
      const componentMap = {
        'banner': 'BannerSection',
        'counter_section': 'CounterSection',
        'about': 'AboutSection',
        'campaigns': 'CampaignsSection',
        'why_choose_us': 'WhyChooseUsSection',
        'work_process': 'WorkProcessSection',
        'testimonials': 'TestimonialsSection',
        'benefit_section': 'BenefitSection',
        'faq_section': 'FAQSection',
        'cta_section': 'CTASection',
        'category': 'CategorySection',
        'partner_section': 'PartnerSection',
        'blog': 'BlogSection'
      }
      return componentMap[sectionName] || null
    }

    const defaultSections = [
      'about', 'category', 'campaigns', 'work_process', 'why_choose_us',
      'benefit_section', 'counter_section', 'testimonials', 'cta_section',
      'faq_section', 'blog', 'partner_section'
    ]

    onMounted(async () => {
      try {
        const response = await appService.getSections()
        // API returns { data: { data: [...], secs: [...] } } or similar
        const data = response?.data || response
        const secs = data?.secs || data?.data
        if (Array.isArray(secs) && secs.length) {
          sections.value = secs
          return
        }
        if (secs && typeof secs === 'string') {
          try {
            sections.value = JSON.parse(secs)
            if (Array.isArray(sections.value) && sections.value.length) return
          } catch (e) {}
        }
        if (Array.isArray(data?.data)) {
          const arr = data.data
          sections.value = arr.every(s => typeof s === 'string')
            ? arr
            : arr.map(s => s?.data_keys?.split?.('.')?.[0] || s).filter(Boolean)
          if (sections.value.length) return
        }
        sections.value = defaultSections
      } catch (error) {
        console.error('Error loading sections:', error)
        sections.value = defaultSections
      }
    })

    return {
      sections,
      getSectionComponent
    }
  }
}
</script>

<style>
/* Styles loaded from external CSS */
</style>
