/** @type {import('tailwindcss').Config} */
export default {
  // Scan user panel + admin + master admin SPA views/components
  content: [
    './index.html',
    './src/components/DashboardLayout.vue',
    './src/components/dashboard/**/*.vue',
    './src/components/admin/**/*.vue',
    './src/components/master_admin/**/*.vue',
    './src/views/Dashboard.vue',
    './src/views/user/**/*.vue',
    './src/views/admin/**/*.vue',
    './src/views/master_admin/**/*.vue',
  ],
  prefix: 'tw-',
  // Do not apply Tailwind preflight/reset – rest of site (landing, admin) unchanged
  corePlugins: {
    preflight: false,
  },
  theme: {
    extend: {},
  },
  plugins: [],
}
