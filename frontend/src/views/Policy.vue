<template>
  <div class="policy-page">
    <div class="policy-container">
      <!-- Header Section -->
      <div class="policy-header">
        <div class="header-icon">
          <i class="fas fa-shield-alt"></i>
        </div>
        <h1 class="policy-title">{{ title }}</h1>
        <p class="policy-subtitle">Last updated: {{ new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) }}</p>
      </div>

      <!-- Content Card -->
      <div class="policy-card">
        <div class="policy-content" v-html="content"></div>
      </div>

      <!-- Back Button -->
      <div class="policy-footer">
        <router-link to="/" class="back-button">
          <i class="fas fa-arrow-left"></i>
          <span>Back to Home</span>
        </router-link>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import { appService } from '../services/appService'

export default {
  name: 'Policy',
  setup() {
    const route = useRoute()
    const policy = ref(null)
    const title = computed(() => policy.value?.data_values?.title ?? policy.value?.title ?? 'Policy')
    const content = computed(() => (
      policy.value?.data_values?.description ??
      policy.value?.data_values?.details ??
      policy.value?.description ??
      policy.value?.details ??
      ''
    ))

    onMounted(async () => {
      try {
        const res = await appService.getPolicy(route.params.slug)
        const data = res?.data ?? res
        policy.value = data
      } catch (e) {
        policy.value = null
      }
    })

    return { policy, title, content }
  }
}
</script>

<style scoped>
.policy-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
  /* Header is already in normal flow; keep top spacing compact */
  padding: 2.25rem 1rem 4rem;
  position: relative;
  overflow: hidden;
}

@media (max-width: 768px) {
  .policy-page {
    padding-top: 1.5rem;
  }
  .policy-header {
    margin-bottom: 2rem;
  }
  .policy-card {
    padding: 1.25rem;
    border-radius: 18px;
  }
  .policy-title {
    font-size: 2.15rem;
  }
  .header-icon {
    width: 64px;
    height: 64px;
    border-radius: 18px;
    font-size: 2rem;
  }
}

.policy-page::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 400px;
  background: radial-gradient(circle at 50% 0%, rgba(99, 102, 241, 0.15) 0%, transparent 70%);
  pointer-events: none;
}

.policy-container {
  max-width: 900px;
  margin: 0 auto;
  position: relative;
  z-index: 1;
}

/* Header Section */
.policy-header {
  text-align: center;
  margin-bottom: 3rem;
  animation: fadeInDown 0.6s ease-out;
}

@keyframes fadeInDown {
  from {
    opacity: 0;
    transform: translateY(-30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.header-icon {
  width: 80px;
  height: 80px;
  margin: 0 auto 1.5rem;
  background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
  border-radius: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2.5rem;
  color: white;
  box-shadow: 0 8px 32px rgba(99, 102, 241, 0.4);
  animation: float 3s ease-in-out infinite;
}

@keyframes float {
  0%, 100% { transform: translateY(0px); }
  50% { transform: translateY(-10px); }
}

.policy-title {
  font-size: 3rem;
  font-weight: 800;
  background: linear-gradient(135deg, #ffffff 0%, #e0e7ff 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin: 0 0 1rem 0;
  line-height: 1.2;
}

.policy-subtitle {
  color: rgba(255, 255, 255, 0.5);
  font-size: 1rem;
  font-weight: 500;
  margin: 0;
}

/* Content Card */
.policy-card {
  background: rgba(255, 255, 255, 0.03);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 24px;
  padding: 3rem;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
  animation: fadeInUp 0.6s ease-out 0.2s both;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.policy-content {
  color: rgba(255, 255, 255, 0.85);
  font-size: 1.0625rem;
  line-height: 1.8;
}

/* Content Styling */
.policy-content :deep(h1),
.policy-content :deep(h2),
.policy-content :deep(h3),
.policy-content :deep(h4),
.policy-content :deep(h5),
.policy-content :deep(h6) {
  color: white;
  font-weight: 700;
  margin-top: 2.5rem;
  margin-bottom: 1.25rem;
  line-height: 1.3;
}

.policy-content :deep(h1) {
  font-size: 2.25rem;
  background: linear-gradient(135deg, #ffffff 0%, #e0e7ff 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.policy-content :deep(h2) {
  font-size: 1.875rem;
  color: #e0e7ff;
  padding-bottom: 0.75rem;
  border-bottom: 2px solid rgba(99, 102, 241, 0.3);
}

.policy-content :deep(h3) {
  font-size: 1.5rem;
  color: #c7d2fe;
}

.policy-content :deep(h4) {
  font-size: 1.25rem;
  color: #a5b4fc;
}

.policy-content :deep(p) {
  margin-bottom: 1.5rem;
  color: rgba(255, 255, 255, 0.8);
}

.policy-content :deep(strong),
.policy-content :deep(b) {
  color: white;
  font-weight: 600;
}

.policy-content :deep(a) {
  color: #818cf8;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.3s;
}

.policy-content :deep(a:hover) {
  color: #a5b4fc;
  text-decoration: underline;
}

.policy-content :deep(ul),
.policy-content :deep(ol) {
  padding-left: 2rem;
  margin-bottom: 1.5rem;
}

.policy-content :deep(li) {
  margin-bottom: 0.75rem;
  color: rgba(255, 255, 255, 0.8);
  line-height: 1.7;
}

.policy-content :deep(li::marker) {
  color: #818cf8;
}

.policy-content :deep(blockquote) {
  border-left: 4px solid #6366f1;
  padding-left: 1.5rem;
  margin: 1.5rem 0;
  font-style: italic;
  color: rgba(255, 255, 255, 0.7);
  background: rgba(99, 102, 241, 0.05);
  padding: 1rem 1.5rem;
  border-radius: 0 12px 12px 0;
}

.policy-content :deep(code) {
  background: rgba(99, 102, 241, 0.1);
  color: #c7d2fe;
  padding: 0.25rem 0.5rem;
  border-radius: 6px;
  font-family: 'Courier New', monospace;
  font-size: 0.9em;
}

.policy-content :deep(pre) {
  background: rgba(0, 0, 0, 0.3);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 1.5rem;
  overflow-x: auto;
  margin-bottom: 1.5rem;
}

.policy-content :deep(pre code) {
  background: transparent;
  padding: 0;
}

.policy-content :deep(table) {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 1.5rem;
  background: rgba(0, 0, 0, 0.2);
  border-radius: 12px;
  overflow: hidden;
}

.policy-content :deep(th),
.policy-content :deep(td) {
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.policy-content :deep(th) {
  background: rgba(99, 102, 241, 0.2);
  color: white;
  font-weight: 600;
}

.policy-content :deep(tr:last-child td) {
  border-bottom: none;
}

.policy-content :deep(hr) {
  border: none;
  height: 2px;
  background: linear-gradient(90deg, transparent, rgba(99, 102, 241, 0.5), transparent);
  margin: 2.5rem 0;
}

/* Footer */
.policy-footer {
  margin-top: 3rem;
  text-align: center;
  animation: fadeInUp 0.6s ease-out 0.4s both;
}

.back-button {
  display: inline-flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem 2rem;
  background: linear-gradient(135deg, rgba(99, 102, 241, 0.2) 0%, rgba(168, 85, 247, 0.2) 100%);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 16px;
  color: white;
  font-weight: 600;
  font-size: 1rem;
  text-decoration: none;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
}

.back-button:hover {
  background: linear-gradient(135deg, rgba(99, 102, 241, 0.3) 0%, rgba(168, 85, 247, 0.3) 100%);
  transform: translateY(-2px);
  box-shadow: 0 6px 24px rgba(99, 102, 241, 0.3);
}

.back-button i {
  font-size: 1.125rem;
}

/* Responsive Design */
@media (max-width: 768px) {
  .policy-page {
    padding: 4rem 1rem 3rem;
  }

  .policy-title {
    font-size: 2rem;
  }

  .header-icon {
    width: 64px;
    height: 64px;
    font-size: 2rem;
  }

  .policy-card {
    padding: 2rem 1.5rem;
  }

  .policy-content {
    font-size: 1rem;
  }

  .policy-content :deep(h1) {
    font-size: 1.75rem;
  }

  .policy-content :deep(h2) {
    font-size: 1.5rem;
  }

  .policy-content :deep(h3) {
    font-size: 1.25rem;
  }

  .policy-content :deep(h4) {
    font-size: 1.125rem;
  }

  .back-button {
    padding: 0.875rem 1.5rem;
    font-size: 0.9375rem;
  }
}

@media (max-width: 480px) {
  .policy-page {
    padding: 3rem 0.75rem 2rem;
  }

  .policy-title {
    font-size: 1.75rem;
  }

  .policy-subtitle {
    font-size: 0.875rem;
  }

  .policy-card {
    padding: 1.5rem 1rem;
    border-radius: 16px;
  }

  .policy-content :deep(ul),
  .policy-content :deep(ol) {
    padding-left: 1.5rem;
  }

  .policy-content :deep(table) {
    font-size: 0.875rem;
  }

  .policy-content :deep(th),
  .policy-content :deep(td) {
    padding: 0.75rem;
  }
}
</style>
