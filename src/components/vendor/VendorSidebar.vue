<template>
  <aside class="vendor-sidebar">
    <div class="sidebar-header">
      <h2>Vendor Dashboard</h2>
    </div>
    
    <nav class="sidebar-nav">
      <router-link to="/vendor/dashboard" class="nav-link">
        <i class="bi bi-grid"></i>
        Dashboard
      </router-link>
      
      <router-link to="/vendor/products" class="nav-link">
        <i class="bi bi-box"></i>
        Products
      </router-link>
      
      <router-link to="/vendor/orders" class="nav-link">
        <i class="bi bi-cart"></i>
        Orders
      </router-link>
      
      <router-link to="/vendor/profile" class="nav-link">
        <i class="bi bi-person"></i>
        Profile
      </router-link>
      
      <a href="#" @click.prevent="logout" class="nav-link logout">
        <i class="bi bi-box-arrow-right"></i>
        Logout
      </a>
    </nav>
  </aside>
</template>

<script setup>
import { useRouter } from 'vue-router'

const router = useRouter()

const logout = () => {
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  router.push('/login')
}
</script>

<style scoped>
.vendor-sidebar {
  width: 250px;
  min-height: 100vh;
  background: #1a1a2e;
  color: white;
  padding: 20px 0;
  position: fixed;
  left: 0;
  top: 0;
}

.sidebar-header {
  padding: 0 20px 20px;
  border-bottom: 1px solid rgba(255,255,255,0.1);
}

.sidebar-header h2 {
  font-size: 1.2rem;
  margin: 0;
  color: #667eea;
}

.sidebar-nav {
  padding: 20px 0;
}

.nav-link {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 20px;
  color: rgba(255,255,255,0.7);
  text-decoration: none;
  transition: all 0.3s ease;
}

.nav-link:hover,
.nav-link.router-link-active {
  background: rgba(102, 126, 234, 0.1);
  color: white;
  border-right: 3px solid #667eea;
}

.nav-link i {
  font-size: 1.2rem;
  width: 24px;
}

.nav-link.logout {
  margin-top: 20px;
  border-top: 1px solid rgba(255,255,255,0.1);
  padding-top: 20px;
  color: #ef4444;
}

.nav-link.logout:hover {
  background: rgba(239, 68, 68, 0.1);
  border-right-color: #ef4444;
}
</style>