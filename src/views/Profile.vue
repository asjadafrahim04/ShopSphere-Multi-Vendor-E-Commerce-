<template>
  <div class="profile-page">
    <div class="container-custom">
      <!-- Page Header -->
      <div class="page-header">
        <h1 class="page-title">My Profile</h1>
        <p class="page-subtitle">Manage your account settings</p>
      </div>

      <!-- Profile Content -->
      <div class="profile-grid">
        <!-- Left: Profile Sidebar -->
        <div class="profile-sidebar">
          <div class="profile-card">
            <div class="profile-avatar">
              <div class="avatar-circle">
                <span>{{ userInitials }}</span>
              </div>
            </div>
            <h3 class="profile-name">{{ userInfo.name || 'Guest User' }}</h3>
            <p class="profile-email">{{ userInfo.email || 'guest@example.com' }}</p>
            <div class="profile-stats">
              <div class="stat">
                <span class="stat-number">{{ orders.length }}</span>
                <span class="stat-label">Orders</span>
              </div>
              <div class="stat">
                <span class="stat-number">{{ wishlistCount }}</span>
                <span class="stat-label">Wishlist</span>
              </div>
              <div class="stat">
                <span class="stat-number">{{ cartCount }}</span>
                <span class="stat-label">Cart Items</span>
              </div>
            </div>
            <button class="btn-outline-modern logout-btn" @click="logout">
              <i class="bi bi-box-arrow-right me-2"></i>Logout
            </button>
          </div>

          <!-- Quick Links -->
          <div class="quick-links">
            <router-link to="/orders" class="quick-link">
              <i class="bi bi-box"></i>
              <span>My Orders</span>
              <i class="bi bi-chevron-right"></i>
            </router-link>
            <router-link to="/wishlist" class="quick-link">
              <i class="bi bi-heart"></i>
              <span>Wishlist</span>
              <i class="bi bi-chevron-right"></i>
            </router-link>
            <router-link to="/cart" class="quick-link">
              <i class="bi bi-cart3"></i>
              <span>Shopping Cart</span>
              <i class="bi bi-chevron-right"></i>
            </router-link>
          </div>
        </div>

        <!-- Right: Profile Settings -->
        <div class="profile-settings">
          <!-- Personal Information -->
          <div class="settings-section">
            <h3>
              <i class="bi bi-person-fill" style="color: #667eea;"></i>
              Personal Information
            </h3>
            <form @submit.prevent="updateProfile">
              <div class="form-row">
                <div class="form-group">
                  <label>Full Name</label>
                  <input type="text" v-model="userInfo.name" placeholder="Enter your full name" />
                </div>
                <div class="form-group">
                  <label>Email</label>
                  <input type="email" v-model="userInfo.email" placeholder="Enter your email" />
                </div>
              </div>
              <div class="form-row">
                <div class="form-group">
                  <label>Phone Number</label>
                  <input type="tel" v-model="userInfo.phone" placeholder="Enter phone number" />
                </div>
                <div class="form-group">
                  <label>Date of Birth</label>
                  <input type="date" v-model="userInfo.dob" />
                </div>
              </div>
              <div class="form-group">
                <label>Address</label>
                <input type="text" v-model="userInfo.address" placeholder="Enter your address" />
              </div>
              <button type="submit" class="btn-primary-modern update-btn">
                <i class="bi bi-check-circle me-2"></i>Update Profile
              </button>
            </form>
          </div>

          <!-- Change Password -->
          <div class="settings-section">
            <h3>
              <i class="bi bi-shield-lock-fill" style="color: #667eea;"></i>
              Change Password
            </h3>
            <form @submit.prevent="changePassword">
              <div class="form-group">
                <label>Current Password</label>
                <input type="password" v-model="passwordData.current" placeholder="Enter current password" />
              </div>
              <div class="form-row">
                <div class="form-group">
                  <label>New Password</label>
                  <input type="password" v-model="passwordData.new" placeholder="Enter new password" />
                </div>
                <div class="form-group">
                  <label>Confirm Password</label>
                  <input type="password" v-model="passwordData.confirm" placeholder="Confirm new password" />
                </div>
              </div>
              <button type="submit" class="btn-primary-modern update-btn">
                <i class="bi bi-key me-2"></i>Change Password
              </button>
            </form>
          </div>

          <!-- Recent Orders -->
          <div class="settings-section">
            <h3>
              <i class="bi bi-clock-history" style="color: #667eea;"></i>
              Recent Orders
            </h3>
            <div v-if="recentOrders.length > 0" class="recent-orders">
              <div v-for="order in recentOrders" :key="order.id" class="recent-order">
                <div class="order-info">
                  <span class="order-number">#{{ order.orderNumber }}</span>
                  <span class="order-date">{{ order.date }}</span>
                </div>
                <div class="order-status">
                  <span :class="['status-badge', getStatusClass(order.status)]">
                    {{ getStatusLabel(order.status) }}
                  </span>
                </div>
                <span class="order-total">${{ order.total.toFixed(2) }}</span>
              </div>
            </div>
            <div v-else class="no-orders">
              <p>No recent orders</p>
              <router-link to="/products" class="btn-outline-modern">
                Start Shopping
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// ===== STATE =====
const userInfo = ref({
  name: 'John Doe',
  email: 'john@example.com',
  phone: '+880 17XXXXXXXX',
  dob: '1990-01-01',
  address: '123 Main Street, Dhaka, Bangladesh'
})

const passwordData = ref({
  current: '',
  new: '',
  confirm: ''
})

const orders = ref([])
const cartCount = ref(0)
const wishlistCount = ref(0)

// ===== COMPUTED =====
const userInitials = computed(() => {
  const name = userInfo.value.name || 'Guest User'
  const parts = name.split(' ')
  if (parts.length >= 2) {
    return parts[0][0] + parts[1][0]
  }
  return name.substring(0, 2).toUpperCase()
})

const recentOrders = computed(() => {
  return [...orders.value].sort((a, b) => b.id - a.id).slice(0, 3)
})

// ===== METHODS =====
const loadData = () => {
  // Load user info from localStorage
  const savedUser = localStorage.getItem('shopsphere_user')
  if (savedUser) {
    userInfo.value = JSON.parse(savedUser)
  }

  // Load orders
  const savedOrders = localStorage.getItem('shopsphere_orders')
  if (savedOrders) {
    orders.value = JSON.parse(savedOrders)
  }

  // Load cart count
  const savedCart = localStorage.getItem('shopsphere_cart')
  if (savedCart) {
    const items = JSON.parse(savedCart)
    cartCount.value = items.reduce((sum, item) => sum + item.quantity, 0)
  }

  // Load wishlist count
  const savedWishlist = localStorage.getItem('shopsphere_wishlist')
  if (savedWishlist) {
    const items = JSON.parse(savedWishlist)
    wishlistCount.value = items.length
  }
}

const updateProfile = () => {
  localStorage.setItem('shopsphere_user', JSON.stringify(userInfo.value))
  alert('✅ Profile updated successfully!')
}

const changePassword = () => {
  if (passwordData.value.new !== passwordData.value.confirm) {
    alert('❌ New passwords do not match!')
    return
  }
  if (passwordData.value.new.length < 6) {
    alert('❌ Password must be at least 6 characters!')
    return
  }
  alert('✅ Password changed successfully!')
  passwordData.value = { current: '', new: '', confirm: '' }
}

const getStatusLabel = (status) => {
  const labels = {
    pending: 'Pending',
    processing: 'Processing',
    shipped: 'Shipped',
    delivered: 'Delivered',
    cancelled: 'Cancelled'
  }
  return labels[status] || status
}

const getStatusClass = (status) => {
  const classes = {
    pending: 'status-pending',
    processing: 'status-processing',
    shipped: 'status-shipped',
    delivered: 'status-delivered',
    cancelled: 'status-cancelled'
  }
  return classes[status] || 'status-pending'
}

const logout = () => {
  if (confirm('Are you sure you want to logout?')) {
    // Clear user session (demo)
    localStorage.removeItem('shopsphere_user')
    alert('👋 Logged out successfully!')
    router.push('/login')
  }
}

// ===== LIFECYCLE =====
onMounted(() => {
  loadData()
})
</script>

<style scoped>
.profile-page {
  padding: 40px 0 80px;
  background: var(--bg-primary);
  min-height: 100vh;
}

/* ===== PAGE HEADER ===== */
.page-header {
  margin-bottom: 40px;
}

.page-title {
  font-size: 2.5rem;
  font-weight: 700;
  color: var(--text-primary);
  margin-bottom: 8px;
}

.page-subtitle {
  color: var(--text-secondary);
  font-size: 1.1rem;
}

/* ===== PROFILE GRID ===== */
.profile-grid {
  display: grid;
  grid-template-columns: 320px 1fr;
  gap: 30px;
}

/* ===== PROFILE SIDEBAR ===== */
.profile-sidebar {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.profile-card {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: var(--radius);
  padding: 32px 24px;
  text-align: center;
}

.profile-avatar {
  display: flex;
  justify-content: center;
  margin-bottom: 16px;
}

.avatar-circle {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  background: var(--gradient-primary);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2.5rem;
  font-weight: 700;
  color: white;
}

.profile-name {
  font-size: 1.3rem;
  font-weight: 600;
  color: var(--text-primary);
  margin: 0 0 4px;
}

.profile-email {
  color: var(--text-muted);
  font-size: 0.95rem;
  margin-bottom: 16px;
}

.profile-stats {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 8px;
  padding: 16px 0;
  border-top: 1px solid var(--border-color);
  border-bottom: 1px solid var(--border-color);
  margin-bottom: 16px;
}

.stat {
  display: flex;
  flex-direction: column;
}

.stat-number {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--text-primary);
}

.stat-label {
  font-size: 0.8rem;
  color: var(--text-muted);
}

.logout-btn {
  width: 100%;
  text-align: center;
  padding: 10px;
  border-color: #ef4444;
  color: #ef4444;
}

.logout-btn:hover {
  background: #ef4444;
  color: white;
}

/* ===== QUICK LINKS ===== */
.quick-links {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: var(--radius);
  overflow: hidden;
}

.quick-link {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 20px;
  text-decoration: none;
  color: var(--text-secondary);
  transition: var(--transition);
  border-bottom: 1px solid var(--border-color);
}

.quick-link:last-child {
  border-bottom: none;
}

.quick-link:hover {
  background: var(--bg-secondary);
  color: var(--text-primary);
}

.quick-link i:first-child {
  font-size: 1.2rem;
  color: #667eea;
}

.quick-link i:last-child {
  margin-left: auto;
  color: var(--text-muted);
}

/* ===== SETTINGS SECTION ===== */
.settings-section {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: var(--radius);
  padding: 24px;
  margin-bottom: 24px;
}

.settings-section h3 {
  font-size: 1.1rem;
  font-weight: 600;
  color: var(--text-primary);
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 10px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.form-group {
  margin-bottom: 16px;
}

.form-group label {
  display: block;
  font-size: 0.9rem;
  font-weight: 500;
  color: var(--text-secondary);
  margin-bottom: 6px;
}

.form-group input {
  width: 100%;
  padding: 12px 16px;
  border: 1px solid var(--border-color);
  border-radius: var(--radius-sm);
  background: var(--bg-secondary);
  color: var(--text-primary);
  font-size: 1rem;
  transition: var(--transition);
}

.form-group input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.update-btn {
  width: 100%;
  text-align: center;
}

/* ===== RECENT ORDERS ===== */
.recent-orders {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.recent-order {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 16px;
  background: var(--bg-secondary);
  border-radius: var(--radius-sm);
  flex-wrap: wrap;
  gap: 10px;
}

.order-info {
  display: flex;
  align-items: center;
  gap: 16px;
}

.order-number {
  font-weight: 600;
  color: var(--text-primary);
  font-size: 0.9rem;
}

.order-date {
  color: var(--text-muted);
  font-size: 0.85rem;
}

.order-total {
  font-weight: 700;
  color: #667eea;
}

/* ===== STATUS BADGE ===== */
.status-badge {
  padding: 2px 12px;
  border-radius: 50px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.status-pending {
  background: #fef3c7;
  color: #d97706;
}

.status-processing {
  background: #dbeafe;
  color: #2563eb;
}

.status-shipped {
  background: #e0e7ff;
  color: #4f46e5;
}

.status-delivered {
  background: #d1fae5;
  color: #059669;
}

.status-cancelled {
  background: #fee2e2;
  color: #dc2626;
}

.no-orders {
  text-align: center;
  padding: 20px;
  color: var(--text-muted);
}

.no-orders .btn-outline-modern {
  margin-top: 10px;
}

/* ===== DARK MODE ===== */
@media (prefers-color-scheme: dark) {
  .status-pending {
    background: rgba(251, 191, 36, 0.2);
    color: #fbbf24;
  }
  
  .status-processing {
    background: rgba(59, 130, 246, 0.2);
    color: #60a5fa;
  }
  
  .status-shipped {
    background: rgba(99, 102, 241, 0.2);
    color: #818cf8;
  }
  
  .status-delivered {
    background: rgba(52, 211, 153, 0.2);
    color: #34d399;
  }
  
  .status-cancelled {
    background: rgba(239, 68, 68, 0.2);
    color: #f87171;
  }
}

/* ===== RESPONSIVE ===== */
@media (max-width: 1024px) {
  .profile-grid {
    grid-template-columns: 1fr;
  }

  .profile-sidebar {
    order: 2;
  }

  .profile-settings {
    order: 1;
  }
}

@media (max-width: 768px) {
  .profile-page {
    padding: 20px 0 60px;
  }

  .page-title {
    font-size: 2rem;
  }

  .form-row {
    grid-template-columns: 1fr;
  }

  .profile-card {
    padding: 24px 16px;
  }

  .avatar-circle {
    width: 80px;
    height: 80px;
    font-size: 2rem;
  }

  .recent-order {
    flex-direction: column;
    align-items: stretch;
    text-align: center;
  }

  .order-info {
    justify-content: center;
  }

  .order-status {
    text-align: center;
  }
}

@media (max-width: 480px) {
  .profile-stats {
    grid-template-columns: 1fr 1fr 1fr;
  }

  .settings-section {
    padding: 16px;
  }
}
</style>