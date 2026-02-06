<template>
  <Layout>
    <router-view />
  </Layout>
</template>

<script>
import Layout from './components/Layout.vue'

export default {
  name: 'App',
  components: {
    Layout
  }
}
</script>

<style>
/* All styles are loaded from external CSS files in index.html */
</style>
