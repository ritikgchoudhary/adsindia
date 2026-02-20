<template>
  <div class="home-page" id="home">
    <!-- Banner Section -->
    <BannerSection />

    <!-- Dynamic Sections -->
    <template v-for="(section, index) in sections" :key="section + '-' + index">
      <component
        v-if="getSectionComponent(section)"
        :is="getSectionComponent(section)"
      />
    </template>

    <!-- Courses Plans Section (In-page) -->
    <section id="courses" class="courses-inline-section py-80">
      <div class="container">
        <div class="section-heading text-center mb-4">
          <h2 class="section-heading__title">Our Courses</h2>
          <p class="section-heading__desc">Choose a plan to unlock courses and earn certificates</p>
        </div>

        <div v-if="plansLoading" class="tw-flex tw-justify-center tw-py-20">
          <div class="tw-w-10 tw-h-10 tw-border-4 tw-border-indigo-500 tw-border-t-transparent tw-rounded-full tw-animate-spin"></div>
        </div>

        <div v-else-if="coursePlans.length > 0" class="row g-4 justify-content-center">
          <div v-for="plan in coursePlans" :key="plan.id" class="col-lg-4 col-md-6">
            <div class="course-plan-card">
              <div class="course-plan-card__top">
                <div class="course-plan-card__name">{{ plan.name }}</div>
                <div class="course-plan-card__price">
                  <span class="cur">{{ currencySymbol }}</span>{{ formatAmount(plan.price) }}
                </div>
                <div class="course-plan-card__meta">
                  <span><i class="fas fa-calendar-alt me-1"></i>Lifetime</span>
                  <span><i class="fas fa-book-open me-1"></i>{{ plan.courses_count }} courses</span>
                </div>
              </div>
              <div class="course-plan-card__body">
                <p class="course-plan-card__desc">{{ plan.description || 'Unlock courses with this plan.' }}</p>
                <div class="course-plan-card__actions">
                  <router-link
                    v-if="isAuthenticated"
                    :to="{ path: '/user/packages', query: { from_home_buy: 1 } }"
                    class="btn btn--base w-100"
                  >
                    <i class="fas fa-shopping-cart me-2"></i>Buy Plan
                  </router-link>
                  <router-link
                    v-else
                    to="/register"
                    class="btn btn--base w-100"
                  >
                    <i class="fas fa-user-plus me-2"></i>Register to Buy
                  </router-link>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div v-else class="text-center py-4">
          <p class="mb-0 text-muted">Plans will appear here once added.</p>
        </div>
      </div>
    </section>

    <!-- Contact Section (In-page) -->
    <section id="contact" class="contact-inline-section py-80">
      <div class="container position-relative">
        <div class="contact-section__left"><div class="shape-bg-one"></div></div>
        <div class="contact-section__right"><div class="shape-bg-two"></div></div>

        <div class="contact-main-container">
          <div class="row gy-4 justify-content-center">
            <div class="col-12">
              <div class="contact-wrapper">
                <span class="contact-wrapper__subtitle">DROP YOUR MESSAGES</span>
                <h4 class="contact-wrapper__title">{{ contactInfo?.title ?? 'Contact Us' }}</h4>

                <form @submit.prevent="handleContactSubmit" class="contact-form verify-gcaptcha">
                  <div class="row">
                    <div class="col-sm-6">
                      <div class="form-group">
                        <label class="form--label">Name</label>
                        <input v-model="contactForm.name" type="text" class="form--control" required>
                      </div>
                    </div>
                    <div class="col-sm-6">
                      <div class="form-group">
                        <label class="form--label">Email</label>
                        <input v-model="contactForm.email" type="email" class="form--control" required>
                      </div>
                    </div>
                    <div class="col-sm-12">
                      <div class="form-group">
                        <label class="form--label">Subject</label>
                        <input v-model="contactForm.subject" type="text" class="form--control" required>
                      </div>
                    </div>
                    <div class="col-sm-12">
                      <div class="form-group">
                        <label class="form--label">Message</label>
                        <textarea v-model="contactForm.message" class="form--control" rows="4" required></textarea>
                      </div>
                    </div>
                    <div class="col-sm-12">
                      <button class="btn--base btn btn--lg w-100" type="submit" :disabled="contactLoading">
                        Submit
                      </button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { appService } from '../services/appService'
import api from '../services/api'
import BannerSection from '../components/sections/BannerSection.vue'
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

    // ── Course Plans data ──
    const coursePlans = ref([])
    const plansLoading = ref(true)
    const currencySymbol = ref('₹')
    const isAuthenticated = ref(false)

    // ── Contact data ──
    const contactForm = ref({ name: '', email: '', subject: '', message: '' })
    const contactLoading = ref(false)
    const contactInfo = ref(null)

    const getSectionComponent = (sectionName) => {
      const componentMap = {
        'banner': 'BannerSection',
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
      'benefit_section', 'testimonials', 'cta_section',
      'faq_section', 'blog', 'partner_section'
    ]

    // ── Course plan helpers ──
    const formatAmount = (amount) => {
      if (!amount && amount !== 0) return '0.00'
      return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    }

    const fetchCoursePlans = async () => {
      plansLoading.value = true
      try {
        const response = await api.get('/public/course-plans')
        const res = response.data
        if (res?.status !== 'success') { coursePlans.value = []; return }
        const payload = res.data && typeof res.data === 'object' ? res.data : {}
        const list = Array.isArray(payload.data) ? payload.data : []
        coursePlans.value = list
        currencySymbol.value = payload.currency_symbol || currencySymbol.value || '₹'
      } catch (e) {
        coursePlans.value = []
      } finally {
        plansLoading.value = false
      }
    }

    // ── Contact helpers ──
    const fetchContactInfo = async () => {
      try {
        const response = await appService.getSections('contact_us.content')
        const content = appService.getSectionContent(response)
        if (content) {
          contactInfo.value = {
            title: content.title || content.data_values?.title || '',
            address: content.address || content.data_values?.address || '',
            email_address: content.email_address || content.data_values?.email_address || '',
            contact_number: content.contact_number || content.data_values?.contact_number || ''
          }
        }
      } catch (e) { console.error('Error loading contact:', e) }
    }

    const handleContactSubmit = async () => {
      contactLoading.value = true
      try {
        const response = await appService.submitContact(contactForm.value)
        const msg = response.message
        const successMsg = (msg?.success && msg.success[0]) || (Array.isArray(msg) ? msg[0] : msg) || 'Ticket created successfully!'
        const errorMsg = (msg?.error && (Array.isArray(msg.error) ? msg.error[0] : msg.error)) || (Array.isArray(msg) ? msg[0] : msg) || 'Failed to send message'
        if (response.status === 'success') {
          if (window.notify) window.notify('success', successMsg)
          contactForm.value = { name: contactForm.value.name, email: contactForm.value.email, subject: '', message: '' }
        } else {
          if (window.notify) window.notify('error', errorMsg)
        }
      } catch (error) {
        const data = error.response?.data
        const errMsg = data?.message
        const text = Array.isArray(errMsg) ? errMsg[0] : errMsg || 'An error occurred'
        if (window.notify) window.notify('error', text)
      } finally {
        contactLoading.value = false
      }
    }

    onMounted(async () => {
      const token = localStorage.getItem('token')
      isAuthenticated.value = !!token

      // Load sections, plans, and contact in parallel
      try {
        const [sectionsResp] = await Promise.all([
          appService.getSections(),
          fetchCoursePlans(),
          fetchContactInfo()
        ])

        const data = sectionsResp?.data || sectionsResp
        let secs = data?.secs ?? data?.data
        if (secs && typeof secs === 'string') { try { secs = JSON.parse(secs) } catch (e) {} }
        if (secs && typeof secs === 'object' && !Array.isArray(secs)) secs = Object.values(secs)
        if (Array.isArray(secs) && secs.length) {
          sections.value = secs.filter(s => typeof s === 'string')
          if (sections.value.length) return
        }
        if (Array.isArray(data?.data)) {
          const arr = data.data
          sections.value = arr.every(s => typeof s === 'string')
            ? arr
            : arr.map(s => (typeof s === 'string' ? s : s?.data_keys?.split?.('.')?.[0] || s)).filter(Boolean)
          if (sections.value.length) return
        }
        sections.value = defaultSections
      } catch (error) {
        console.error('Error loading sections:', error)
        sections.value = defaultSections
      }

      // Auto-fill user contact info if logged in
      if (token) {
        try {
          const res = await api.get('/user-info')
          const user = res.data?.data ?? res.data
          if (user) {
            if (user.fullname) contactForm.value.name = user.fullname
            else if (user.firstname || user.lastname) contactForm.value.name = [user.firstname, user.lastname].filter(Boolean).join(' ').trim()
            if (user.email) contactForm.value.email = user.email
          }
        } catch (e) { /* ignore */ }
      }

      // Handle hash scroll on page load (e.g. /#courses)
      setTimeout(() => {
        const hash = window.location.hash?.replace('#', '')
        if (hash && hash !== 'home') {
          const el = document.getElementById(hash)
          if (el) {
            const headerHeight = document.getElementById('header')?.offsetHeight || 80
            const top = el.getBoundingClientRect().top + window.scrollY - headerHeight
            window.scrollTo({ top, behavior: 'smooth' })
          }
        }
      }, 500)
    })

    return {
      sections,
      getSectionComponent,
      coursePlans,
      plansLoading,
      currencySymbol,
      isAuthenticated,
      formatAmount,
      contactForm,
      contactLoading,
      contactInfo,
      handleContactSubmit
    }
  }
}
</script>

<style scoped>
.home-page {
  overflow-x: clip;
  width: 100%;
  min-height: 0;
}

/* ── Courses Plans Inline Section ── */
.courses-inline-section {
  position: relative;
}
.courses-inline-section .section-heading__title {
  font-size: 2.25rem;
  font-weight: 800;
}
.courses-inline-section .section-heading__desc {
  font-size: 1rem;
  opacity: 0.7;
  max-width: 520px;
  margin: 0.5rem auto 0;
}

/* ── Course Plan Cards ── */
.course-plan-card{
  background: #fff;
  border-radius: 18px;
  border: 1px solid rgba(15,23,42,0.08);
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(2,6,23,0.08);
  transition: transform .2s ease, box-shadow .2s ease;
}
.course-plan-card:hover{
  transform: translateY(-4px);
  box-shadow: 0 14px 40px rgba(2,6,23,0.12);
}
.course-plan-card__top{
  padding: 18px 18px 14px;
  background: linear-gradient(135deg, rgba(79,70,229,0.10), rgba(139,92,246,0.10));
  border-bottom: 1px solid rgba(99,102,241,0.16);
}
.course-plan-card__name{
  font-weight: 800;
  font-size: 1.1rem;
  color: #0f172a;
  margin-bottom: 8px;
}
.course-plan-card__price{
  font-weight: 900;
  font-size: 1.8rem;
  color: #4f46e5;
  line-height: 1.1;
}
.course-plan-card__price .cur{
  font-size: 1rem;
  margin-right: 4px;
  color: #6366f1;
}
.course-plan-card__meta{
  margin-top: 10px;
  display: flex;
  gap: 14px;
  flex-wrap: wrap;
  color: #475569;
  font-weight: 600;
  font-size: .9rem;
}
.course-plan-card__body{
  padding: 16px 18px 18px;
}
.course-plan-card__desc{
  margin: 0 0 14px;
  color: #475569;
  font-size: .95rem;
  line-height: 1.5;
}
.course-plan-card__actions .btn{
  border-radius: 14px;
  padding: 12px 14px;
  font-weight: 800;
}

/* ── Contact Inline Section ── */
.contact-inline-section {
  position: relative;
}

.py-80 { padding-top: 80px; padding-bottom: 80px; }
</style>
