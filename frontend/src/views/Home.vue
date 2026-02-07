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

    onMounted(async () => {
      try {
        const response = await appService.getSections()
        // The API returns data wrapped in a 'data' property
        if (response.data?.data) {
          sections.value = response.data.data
        } else if (response.data && Array.isArray(response.data)) {
          // Fallback if data is directly the array
          sections.value = response.data.map(s => s.data_keys.split('.')[0])
        }
      } catch (error) {
        console.error('Error loading sections:', error)
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
