<template>
  <div id="app">
    <!-- Only show Navbar & Footer on non-vendor and non-admin pages -->
    <template v-if="!isVendorRoute && !isAdminRoute">
      <Navbar />
      <main class="main-content">
        <router-view />
      </main>
      <Footer />
    </template>

    <!-- Vendor pages - show without navbar/footer -->
    <template v-else-if="isVendorRoute">
      <router-view />
    </template>

    <!-- Admin pages - show without navbar/footer -->
    <template v-else-if="isAdminRoute">
      <router-view />
    </template>

    <!-- Default fallback (should never reach here) -->
    <template v-else>
      <Navbar />
      <main class="main-content">
        <router-view />
      </main>
      <Footer />
    </template>
  </div>
</template>

<script setup>
import { computed, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Navbar from './components/Navbar.vue'
import Footer from './components/Footer.vue'

const route = useRoute()
const router = useRouter()

// ===== COMPUTED =====
// Check if current route is vendor section
const isVendorRoute = computed(() => {
  return route.path.startsWith('/vendor')
})

// Check if current route is admin section
const isAdminRoute = computed(() => {
  return route.path.startsWith('/admin')
})

// ===== HELPERS =====
// Check if user is a vendor
const isVendor = () => {
  const token = localStorage.getItem('token')
  const user = JSON.parse(localStorage.getItem('user') || 'null')
  return token && user?.role === 'vendor'
}

// Check if user is an admin
const isAdmin = () => {
  const token = localStorage.getItem('token')
  const user = JSON.parse(localStorage.getItem('user') || 'null')
  return token && user?.role === 'admin'
}

// Check if user is a customer
const isCustomer = () => {
  const token = localStorage.getItem('token')
  const user = JSON.parse(localStorage.getItem('user') || 'null')
  return token && user?.role === 'customer'
}

// ===== REDIRECTS =====
// Redirect vendor from homepage to dashboard
const redirectVendorFromHome = () => {
  if (route.path === '/' && isVendor()) {
    console.log('🔀 Vendor detected on homepage. Redirecting to vendor dashboard...')
    router.push('/vendor/dashboard')
    return true
  }
  return false
}

// Redirect admin from homepage to dashboard
const redirectAdminFromHome = () => {
  if (route.path === '/' && isAdmin()) {
    console.log('🔀 Admin detected on homepage. Redirecting to admin dashboard...')
    router.push('/admin/dashboard')
    return true
  }
  return false
}

// ===== WATCHERS =====
// Watch for route changes to handle vendor routing
watch(() => route.path, (newPath) => {
  // Check if admin is on homepage
  if (newPath === '/' && isAdmin()) {
    router.push('/admin/dashboard')
    return
  }

  // Check if vendor is on homepage
  if (newPath === '/' && isVendor()) {
    router.push('/vendor/dashboard')
    return
  }

  // If user is on vendor page but not authenticated
  if (newPath.startsWith('/vendor')) {
    const token = localStorage.getItem('token')
    if (!token) {
      router.push('/login')
      return
    }
    const user = JSON.parse(localStorage.getItem('user') || 'null')
    if (user?.role !== 'vendor') {
      router.push('/')
    }
  }

  // If user is on admin page but not authenticated
  if (newPath.startsWith('/admin')) {
    const token = localStorage.getItem('token')
    if (!token) {
      router.push('/login')
      return
    }
    const user = JSON.parse(localStorage.getItem('user') || 'null')
    if (user?.role !== 'admin') {
      router.push('/')
    }
  }
}, { immediate: true })

// ===== LIFECYCLE =====
onMounted(() => {
  redirectVendorFromHome()
  redirectAdminFromHome()
})
</script>

<style>
/* Global Styles */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
  background: #f3f4f6;
  color: #1a1a2e;
}

#app {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.main-content {
  flex: 1;
  min-height: calc(100vh - 130px);
}

/* Scrollbar Styles */
::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
  background: #667eea;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: #5a67d8;
}

/* Utility Classes */
.container {
  max-width: 1280px;
  margin: 0 auto;
  padding: 0 20px;
}

.text-center {
  text-align: center;
}

.text-muted {
  color: #6b7280;
}

.mt-1 { margin-top: 8px; }
.mt-2 { margin-top: 16px; }
.mt-3 { margin-top: 24px; }
.mt-4 { margin-top: 32px; }

.mb-1 { margin-bottom: 8px; }
.mb-2 { margin-bottom: 16px; }
.mb-3 { margin-bottom: 24px; }
.mb-4 { margin-bottom: 32px; }

/* Loading Spinner */
.spinner {
  display: inline-block;
  width: 40px;
  height: 40px;
  border: 3px solid #e5e7eb;
  border-top-color: #667eea;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Buttons */
.btn {
  padding: 10px 20px;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 14px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.btn-primary {
  background: #667eea;
  color: white;
}

.btn-primary:hover {
  background: #5a67d8;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
  background: #e5e7eb;
  color: #1a1a2e;
}

.btn-secondary:hover {
  background: #d1d5db;
}

.btn-danger {
  background: #ef4444;
  color: white;
}

.btn-danger:hover {
  background: #dc2626;
}

.btn-success {
  background: #10b981;
  color: white;
}

.btn-success:hover {
  background: #059669;
}

.btn-sm {
  padding: 6px 12px;
  font-size: 12px;
}

.btn-lg {
  padding: 14px 28px;
  font-size: 16px;
}

/* Cards */
.card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  border: 1px solid #e5e7eb;
  box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.card-title {
  font-size: 18px;
  font-weight: 600;
  color: #1a1a2e;
  margin: 0;
}

/* Badges */
.badge {
  padding: 2px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
}

.badge-primary {
  background: rgba(102, 126, 234, 0.1);
  color: #667eea;
}

.badge-success {
  background: rgba(16, 185, 129, 0.1);
  color: #10b981;
}

.badge-danger {
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
}

.badge-warning {
  background: rgba(251, 191, 36, 0.1);
  color: #f59e0b;
}

/* Responsive */
@media (max-width: 768px) {
  .container {
    padding: 0 16px;
  }
  
  .btn {
    padding: 8px 16px;
    font-size: 13px;
  }
}
</style>