<template>
  <div id="app">
    <!-- Navbar appears on every page -->
    <Navbar />
    
    <!-- This is where page content goes -->
    <main class="min-vh-100">
      <router-view />
    </main>
    
    <!-- Footer appears on every page -->
    <Footer />
  </div>
</template>

<script setup>
import Navbar from './components/Navbar.vue'
import Footer from './components/Footer.vue'
</script>