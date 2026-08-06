<template>
  <div class="admin-layout">
    <!-- Sidebar -->
    <aside class="admin-sidebar">
      <div class="sidebar-brand">
        <h2>🛍️ ShopSphere</h2>
        <span class="brand-sub">Admin Panel</span>
      </div>
      
      <nav class="sidebar-nav">
        <router-link to="/admin/dashboard" class="nav-link" :class="{ active: $route.path === '/admin/dashboard' }">
          <span class="nav-icon">📊</span>
          <span class="nav-text">Dashboard</span>
        </router-link>
        
        <router-link to="/admin/users" class="nav-link" :class="{ active: $route.path === '/admin/users' }">
          <span class="nav-icon">👥</span>
          <span class="nav-text">Users</span>
        </router-link>
        
        <router-link to="/admin/vendors" class="nav-link" :class="{ active: $route.path === '/admin/vendors' }">
          <span class="nav-icon">🏪</span>
          <span class="nav-text">Vendors</span>
        </router-link>
        
        <router-link to="/admin/orders" class="nav-link" :class="{ active: $route.path === '/admin/orders' }">
          <span class="nav-icon">📦</span>
          <span class="nav-text">Orders</span>
        </router-link>
        
        <router-link to="/admin/categories" class="nav-link" :class="{ active: $route.path === '/admin/categories' }">
          <span class="nav-icon">📂</span>
          <span class="nav-text">Categories</span>
        </router-link>
        
        <router-link to="/admin/settings" class="nav-link" :class="{ active: $route.path === '/admin/settings' }">
          <span class="nav-icon">⚙️</span>
          <span class="nav-text">Settings</span>
        </router-link>
        
        <a href="#" @click.prevent="handleLogout" class="nav-link logout">
          <span class="nav-icon">🚪</span>
          <span class="nav-text">Logout</span>
        </a>
      </nav>
    </aside>

    <!-- Main Content -->
    <main class="admin-main">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'

const router = useRouter()

const handleLogout = () => {
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  router.push('/login')
}
</script>

<style scoped>
.admin-layout {
  display: flex;
  min-height: 100vh;
  background: #f0f2f5;
}

.admin-sidebar {
  width: 250px;
  background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);
  color: white;
  position: fixed;
  height: 100vh;
  left: 0;
  top: 0;
  overflow-y: auto;
  z-index: 100;
}

.sidebar-brand {
  padding: 24px 20px 20px;
  border-bottom: 1px solid rgba(255,255,255,0.08);
}

.sidebar-brand h2 {
  margin: 0;
  font-size: 20px;
  color: #667eea;
}

.brand-sub {
  display: block;
  font-size: 12px;
  color: rgba(255,255,255,0.5);
  margin-top: 4px;
  letter-spacing: 1px;
  text-transform: uppercase;
}

.sidebar-nav {
  padding: 16px 12px;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.nav-link {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 12px 16px;
  color: rgba(255,255,255,0.6);
  text-decoration: none;
  border-radius: 10px;
  transition: all 0.3s ease;
  cursor: pointer;
  font-size: 15px;
}

.nav-link:hover {
  background: rgba(255,255,255,0.06);
  color: white;
}

.nav-link.active {
  background: rgba(102, 126, 234, 0.2);
  color: #667eea;
}

.nav-link .nav-icon {
  font-size: 20px;
  width: 28px;
  text-align: center;
  flex-shrink: 0;
}

.nav-link.logout {
  margin-top: 20px;
  border-top: 1px solid rgba(255,255,255,0.06);
  padding-top: 20px;
  color: rgba(239, 68, 68, 0.7);
}

.nav-link.logout:hover {
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
}

.admin-main {
  margin-left: 250px;
  flex: 1;
  min-height: 100vh;
  padding: 24px;
}

/* Scrollbar */
.admin-sidebar::-webkit-scrollbar {
  width: 4px;
}

.admin-sidebar::-webkit-scrollbar-track {
  background: transparent;
}

.admin-sidebar::-webkit-scrollbar-thumb {
  background: rgba(255,255,255,0.2);
  border-radius: 4px;
}

@media (max-width: 768px) {
  .admin-sidebar {
    width: 70px;
  }
  
  .sidebar-brand h2,
  .brand-sub,
  .nav-text {
    display: none;
  }
  
  .admin-main {
    margin-left: 70px;
    padding: 16px;
  }
  
  .nav-link {
    justify-content: center;
    padding: 14px;
  }
  
  .nav-link .nav-icon {
    width: auto;
  }
}
</style>