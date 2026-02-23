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
      <!-- Decorative background orbs -->
      <div class="courses-bg-orb courses-bg-orb--1"></div>
      <div class="courses-bg-orb courses-bg-orb--2"></div>

      <div class="container position-relative" style="z-index: 2;">
        <div class="section-heading text-center mb-5">
          <span class="courses-section-label">
            <i class="fas fa-graduation-cap me-2"></i>SKILL DEVELOPMENT
          </span>
          <h2 class="section-heading__title mt-3">Our <span class="courses-title-highlight">Courses</span></h2>
          <p class="section-heading__desc">Choose a plan to unlock premium courses and earn industry-recognized certificates</p>
          <div class="courses-title-divider"></div>
        </div>

        <div v-if="plansLoading" class="tw-flex tw-justify-center tw-py-20">
          <div class="courses-loader">
            <div class="courses-loader__ring"></div>
            <span class="courses-loader__text">Loading Plans...</span>
          </div>
        </div>

        <div v-else-if="coursePlans.length > 0" class="row g-4 justify-content-center">
          <div v-for="(plan, idx) in coursePlans" :key="plan.id" class="col-lg-4 col-md-6">
            <div class="course-plan-card" :class="{ 'course-plan-card--featured': idx === 1 }">
              <!-- Featured badge -->
              <div v-if="idx === 1" class="course-plan-card__featured-badge">
                <i class="fas fa-star me-1"></i>Most Popular
              </div>

              <!-- Glowing border effect -->
              <div class="course-plan-card__glow"></div>

              <div class="course-plan-card__top">
                <!-- Icon -->
                <div class="course-plan-card__icon">
                  <i :class="getPlanIcon(idx)"></i>
                </div>
                <div class="course-plan-card__name">{{ plan.name }}</div>
                <div class="course-plan-card__price">
                  <span class="cur">{{ currencySymbol }}</span>{{ formatAmount(plan.price) }}
                  <span class="course-plan-card__price-period">/lifetime</span>
                </div>
                <div class="course-plan-card__meta">
                  <span class="course-plan-card__meta-item">
                    <i class="fas fa-infinity"></i>Lifetime Access
                  </span>
                  <span class="course-plan-card__meta-item">
                    <i class="fas fa-book-open"></i>{{ plan.courses_count }} Courses
                  </span>
                  <span class="course-plan-card__meta-item">
                    <i class="fas fa-certificate"></i>Certificate
                  </span>
                </div>
              </div>

              <div class="course-plan-card__body">
                <p class="course-plan-card__desc">{{ plan.description || 'Unlock premium courses with this plan and accelerate your career growth.' }}</p>

                <!-- Feature list -->
                <ul class="course-plan-card__features">
                  <li><i class="fas fa-check-circle"></i> Lifetime course access</li>
                  <li><i class="fas fa-check-circle"></i> Completion certificate</li>
                  <li><i class="fas fa-check-circle"></i> Expert instructors</li>
                  <li><i class="fas fa-check-circle"></i> Study materials included</li>
                </ul>

                <div class="course-plan-card__actions">
                  <router-link
                    v-if="isAuthenticated"
                    :to="{ path: '/user/packages', query: { from_home_buy: 1 } }"
                    class="course-plan-card__btn"
                  >
                    <i class="fas fa-shopping-cart me-2"></i>Buy Plan
                    <span class="course-plan-card__btn-shine"></span>
                  </router-link>
                  <router-link
                    v-else
                    to="/register"
                    class="course-plan-card__btn"
                  >
                    <i class="fas fa-user-plus me-2"></i>Get Started
                    <span class="course-plan-card__btn-shine"></span>
                  </router-link>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div v-else class="courses-empty-state">
          <div class="courses-empty-state__icon">
            <i class="fas fa-book-open"></i>
          </div>
          <h5 class="courses-empty-state__title">Plans Coming Soon</h5>
          <p class="courses-empty-state__text">Our course plans will appear here once available. Stay tuned!</p>
        </div>

        <!-- Bottom CTA -->
        <div class="courses-cta text-center mt-5">
          <router-link to="/register" class="courses-cta__link">
            <i class="fas fa-user-plus me-2"></i>Join Now to Browse All Courses
            <i class="fas fa-arrow-right ms-2"></i>
          </router-link>
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

    const getPlanIcon = (idx) => {
      const icons = ['fas fa-seedling', 'fas fa-rocket', 'fas fa-crown']
      return icons[idx] || 'fas fa-star'
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
      getPlanIcon,
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
  overflow: hidden;
}

/* Background orbs */
.courses-bg-orb {
  position: absolute;
  border-radius: 50%;
  pointer-events: none;
  z-index: 1;
  filter: blur(80px);
}
.courses-bg-orb--1 {
  width: 500px;
  height: 500px;
  background: radial-gradient(circle, hsl(var(--base) / 0.14) 0%, transparent 70%);
  top: -100px;
  left: -120px;
}
.courses-bg-orb--2 {
  width: 400px;
  height: 400px;
  background: radial-gradient(circle, hsl(var(--base-two) / 0.10) 0%, transparent 70%);
  bottom: -80px;
  right: -80px;
}

/* Section label pill */
.courses-section-label {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  background: hsl(var(--base) / 0.12);
  border: 1px solid hsl(var(--base) / 0.3);
  color: hsl(var(--base));
  font-size: 0.78rem;
  font-weight: 700;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  padding: 6px 16px;
  border-radius: 999px;
  backdrop-filter: blur(6px);
}

.courses-inline-section .section-heading__title {
  font-size: 2.5rem;
  font-weight: 800;
  font-family: 'Montserrat', sans-serif;
}

/* Theme-colour highlight on "Courses" word */
.courses-title-highlight {
  background: linear-gradient(90deg, hsl(var(--base)), hsl(var(--base-two)));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.courses-inline-section .section-heading__desc {
  font-size: 1rem;
  opacity: 0.7;
  max-width: 560px;
  margin: 0.75rem auto 0;
  line-height: 1.7;
}

/* Animated divider */
.courses-title-divider {
  width: 60px;
  height: 3px;
  background: linear-gradient(90deg, hsl(var(--base)), hsl(var(--base-two)));
  border-radius: 999px;
  margin: 1.25rem auto 0;
}

/* Custom loader */
.courses-loader {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 14px;
  padding: 60px 0;
}
.courses-loader__ring {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  border: 3px solid hsl(var(--base) / 0.2);
  border-top-color: hsl(var(--base));
  animation: coursesLoaderSpin 0.8s linear infinite;
}
@keyframes coursesLoaderSpin {
  to { transform: rotate(360deg); }
}
.courses-loader__text {
  font-size: 0.88rem;
  color: rgba(255,255,255,0.5);
  font-weight: 600;
  letter-spacing: 0.5px;
}

/* ── Course Plan Cards ── */
.course-plan-card {
  position: relative;
  background: hsl(var(--card-bg));
  border-radius: 22px;
  border: 1px solid hsl(var(--base) / 0.18);
  overflow: hidden;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
  transition: transform 0.35s cubic-bezier(.4,0,.2,1), box-shadow 0.35s cubic-bezier(.4,0,.2,1), border-color 0.35s ease;
  height: 100%;
  display: flex;
  flex-direction: column;
}
.course-plan-card:hover {
  transform: translateY(-10px);
  box-shadow: 0 20px 60px rgba(0,0,0,0.5), 0 0 40px hsl(var(--base) / 0.15);
  border-color: hsl(var(--base) / 0.45);
}

/* Featured card (middle) */
.course-plan-card--featured {
  border-color: hsl(var(--base)) !important;
  background: hsl(var(--card-bg));
  box-shadow: 0 12px 50px rgba(0,0,0,0.5), 0 0 60px hsl(var(--base) / 0.2);
  transform: translateY(-6px);
}
.course-plan-card--featured:hover {
  transform: translateY(-16px);
  box-shadow: 0 24px 70px rgba(0,0,0,0.55), 0 0 80px hsl(var(--base) / 0.28);
}

/* Featured badge */
.course-plan-card__featured-badge {
  position: absolute;
  top: -1px;
  left: 50%;
  transform: translateX(-50%);
  background: linear-gradient(90deg, hsl(var(--base)), hsl(var(--base-two)));
  color: #fff;
  font-size: 0.74rem;
  font-weight: 700;
  letter-spacing: 0.5px;
  padding: 5px 16px;
  border-radius: 0 0 12px 12px;
  z-index: 10;
  white-space: nowrap;
  box-shadow: 0 4px 12px hsl(var(--base) / 0.4);
}

/* Glow overlay on hover */
.course-plan-card__glow {
  position: absolute;
  inset: 0;
  border-radius: 22px;
  background: radial-gradient(ellipse at top center, hsl(var(--base) / 0.08) 0%, transparent 65%);
  pointer-events: none;
  opacity: 0;
  transition: opacity 0.4s ease;
  z-index: 0;
}
.course-plan-card:hover .course-plan-card__glow {
  opacity: 1;
}

/* Top area */
.course-plan-card__top {
  position: relative;
  padding: 28px 24px 22px;
  background: linear-gradient(160deg, hsl(var(--base) / 0.10) 0%, hsl(var(--base-two) / 0.06) 100%);
  border-bottom: 1px solid hsl(var(--base) / 0.15);
  z-index: 1;
}

/* Plan icon */
.course-plan-card__icon {
  width: 52px;
  height: 52px;
  border-radius: 14px;
  background: linear-gradient(135deg, hsl(var(--base)), hsl(var(--base-two)));
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 16px;
  box-shadow: 0 6px 20px hsl(var(--base) / 0.4);
  font-size: 1.3rem;
  color: #fff;
}

.course-plan-card__name {
  font-weight: 800;
  font-size: 1.15rem;
  color: rgba(255, 255, 255, 0.95);
  margin-bottom: 10px;
  font-family: 'Montserrat', sans-serif;
  letter-spacing: 0.2px;
}

.course-plan-card__price {
  font-weight: 900;
  font-size: 2.2rem;
  color: hsl(var(--base));
  line-height: 1.1;
  display: flex;
  align-items: baseline;
  gap: 4px;
  flex-wrap: wrap;
  font-family: 'Montserrat', sans-serif;
}
.course-plan-card__price .cur {
  font-size: 1.15rem;
  margin-right: 2px;
  color: hsl(var(--base-two));
  font-weight: 700;
}
.course-plan-card__price-period {
  font-size: 0.82rem;
  color: rgba(255,255,255,0.4);
  font-weight: 500;
  margin-left: 2px;
  align-self: flex-end;
  margin-bottom: 4px;
}

/* Meta chips */
.course-plan-card__meta {
  margin-top: 14px;
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}
.course-plan-card__meta-item {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  background: hsl(var(--base) / 0.12);
  border: 1px solid hsl(var(--base) / 0.25);
  color: hsl(var(--base));
  font-size: 0.78rem;
  font-weight: 600;
  padding: 4px 10px;
  border-radius: 999px;
  letter-spacing: 0.2px;
}
.course-plan-card__meta-item i {
  font-size: 0.75rem;
  opacity: 0.9;
}

/* Body */
.course-plan-card__body {
  position: relative;
  padding: 22px 24px 24px;
  flex: 1;
  display: flex;
  flex-direction: column;
  z-index: 1;
}

.course-plan-card__desc {
  margin: 0 0 18px;
  color: rgba(255, 255, 255, 0.6);
  font-size: 0.92rem;
  line-height: 1.65;
}

/* Features list */
.course-plan-card__features {
  list-style: none;
  padding: 0;
  margin: 0 0 20px;
  display: flex;
  flex-direction: column;
  gap: 9px;
  flex: 1;
}
.course-plan-card__features li {
  display: flex;
  align-items: center;
  gap: 8px;
  color: rgba(255, 255, 255, 0.75);
  font-size: 0.9rem;
}
.course-plan-card__features li i {
  color: hsl(var(--base));
  font-size: 0.88rem;
  flex-shrink: 0;
}

/* CTA Button with shine animation */
.course-plan-card__btn {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  padding: 14px 20px;
  border-radius: 14px;
  background: linear-gradient(90deg, hsl(var(--base-d-100)), hsl(var(--base-two)));
  color: #fff !important;
  font-weight: 800;
  font-size: 0.95rem;
  letter-spacing: 0.3px;
  text-decoration: none;
  border: none;
  cursor: pointer;
  overflow: hidden;
  box-shadow: 0 6px 24px hsl(var(--base) / 0.4);
  transition: transform 0.25s ease, box-shadow 0.25s ease;
}
.course-plan-card__btn:hover {
  transform: scale(1.02);
  box-shadow: 0 10px 32px hsl(var(--base) / 0.55);
  color: #fff !important;
}
/* Shine sweep effect */
.course-plan-card__btn-shine {
  position: absolute;
  top: 0;
  left: -75%;
  width: 50%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.25), transparent);
  transform: skewX(-20deg);
  animation: btnShine 2.4s infinite;
}
@keyframes btnShine {
  0%   { left: -75%; }
  60%  { left: 130%; }
  100% { left: 130%; }
}

/* Empty state */
.courses-empty-state {
  text-align: center;
  padding: 60px 20px;
}
.courses-empty-state__icon {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: hsl(var(--base) / 0.1);
  border: 1px solid hsl(var(--base) / 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 20px;
  font-size: 1.8rem;
  color: hsl(var(--base));
}
.courses-empty-state__title {
  color: rgba(255,255,255,0.85);
  font-weight: 700;
  margin-bottom: 8px;
  font-size: 1.2rem;
}
.courses-empty-state__text {
  color: rgba(255,255,255,0.45);
  font-size: 0.92rem;
}

/* Bottom Courses CTA link */
.courses-cta__link {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  color: hsl(var(--base));
  font-weight: 700;
  font-size: 0.95rem;
  text-decoration: none;
  border: 1px solid hsl(var(--base) / 0.3);
  padding: 10px 24px;
  border-radius: 999px;
  background: hsl(var(--base) / 0.08);
  transition: all 0.25s ease;
  letter-spacing: 0.3px;
}
.courses-cta__link:hover {
  background: hsl(var(--base) / 0.16);
  border-color: hsl(var(--base) / 0.55);
  color: hsl(var(--base));
  text-decoration: none;
  transform: translateY(-2px);
  box-shadow: 0 4px 20px hsl(var(--base) / 0.2);
}

@media (max-width: 768px) {
  .courses-inline-section .section-heading__title {
    font-size: 1.9rem;
  }
  .course-plan-card--featured {
    transform: translateY(0);
  }
}

/* ── Contact Inline Section ── */
.contact-inline-section {
  position: relative;
}

.py-80 { padding-top: 80px; padding-bottom: 80px; }
</style>
