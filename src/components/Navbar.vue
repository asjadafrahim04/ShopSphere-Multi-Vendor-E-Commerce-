<template>
  <nav class="navbar-modern">
    <div class="container-custom">
      <div class="navbar-content">
        <!-- Logo -->
        <router-link to="/" class="navbar-logo">
          <span class="logo-icon">🛒</span>
          ShopSphere
        </router-link>

        <!-- Search Bar - Desktop -->
        <div class="search-bar">
          <i class="bi bi-search"></i>
          <input 
            type="text" 
            v-model="searchQuery" 
            placeholder="Search for products..."
            @keyup.enter="performSearch"
            @focus="showSearchSuggestions = true"
            @blur="hideSearchSuggestions"
          />
          <button class="search-btn" @click="performSearch">
            <i class="bi bi-arrow-right"></i>
          </button>
          <div v-if="showSearchSuggestions && searchQuery.length > 0" class="search-suggestions">
            <div v-if="filteredSuggestions.length > 0">
              <div 
                v-for="product in filteredSuggestions" 
                :key="product.id"
                class="suggestion-item"
                @mousedown.prevent="goToProduct(product.id)"
              >
                <img :src="product.image" :alt="product.name" v-if="product.image" />
                <span v-else class="suggestion-emoji">{{ product.emoji || '📦' }}</span>
                <div class="suggestion-info">
                  <span class="suggestion-name">{{ product.name }}</span>
                  <span class="suggestion-vendor">{{ product.vendor }}</span>
                </div>
                <span class="suggestion-price">${{ product.price }}</span>
              </div>
            </div>
            <div v-else class="no-suggestions">
              <i class="bi bi-search"></i>
              <span>No products found</span>
            </div>
          </div>
        </div>

        <!-- Nav Links -->
        <div class="navbar-links">
          <router-link to="/" class="nav-link">Home</router-link>
          <router-link to="/products" class="nav-link">Products</router-link>
          <router-link to="/about" class="nav-link">About</router-link>
        </div>

        <!-- Actions -->
        <div class="navbar-actions">
          <!-- Dark Mode Toggle -->
          <button class="action-btn theme-toggle" @click="toggleTheme" :title="isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode'">
            <i :class="isDark ? 'bi bi-sun-fill' : 'bi bi-moon-fill'"></i>
          </button>
          
          <!-- Wishlist Button -->
          <button class="action-btn" @click="goToWishlist" title="Wishlist">
            <i class="bi bi-heart"></i>
          </button>

          <!-- Cart Button with Badge -->
          <button class="action-btn cart-btn" @click="goToCart" title="Shopping Cart">
            <i class="bi bi-cart3"></i>
            <span v-if="cartCount > 0" class="cart-badge">{{ cartCount }}</span>
          </button>

          <!-- Orders Button -->
          <button class="action-btn orders-btn" @click="goToOrders" title="My Orders">
            <i class="bi bi-box"></i>
          </button>

          <!-- Profile / Sign In Button -->
          <div v-if="isLoggedIn" class="user-menu">
            <button class="btn-primary-modern profile-btn" @click="goToProfile">
              <i class="bi bi-person me-1"></i>
              <span class="user-name">{{ userName }}</span>
            </button>
          </div>
          <router-link v-else to="/login" class="btn-primary-modern signin-btn">
            Sign In
          </router-link>
        </div>

        <!-- Mobile Toggle -->
        <button class="mobile-toggle" @click="isMenuOpen = !isMenuOpen">
          <span></span>
          <span></span>
          <span></span>
        </button>
      </div>

      <!-- Mobile Search -->
      <div class="mobile-search">
        <div class="search-bar mobile">
          <i class="bi bi-search"></i>
          <input 
            type="text" 
            v-model="searchQuery" 
            placeholder="Search for products..."
            @keyup.enter="performSearch"
          />
          <button class="search-btn" @click="performSearch">
            <i class="bi bi-arrow-right"></i>
          </button>
        </div>
      </div>

      <!-- Mobile Menu -->
      <div class="mobile-menu" v-show="isMenuOpen">
        <router-link to="/" class="mobile-link" @click="isMenuOpen = false">Home</router-link>
        <router-link to="/products" class="mobile-link" @click="isMenuOpen = false">Products</router-link>
        <router-link to="/about" class="mobile-link" @click="isMenuOpen = false">About</router-link>
        
        <!-- Mobile Cart Link -->
        <router-link to="/cart" class="mobile-link" @click="isMenuOpen = false">
          <i class="bi bi-cart3 me-2"></i>Cart
          <span v-if="cartCount > 0" class="mobile-badge">{{ cartCount }}</span>
        </router-link>

        <!-- Mobile Wishlist Link -->
        <router-link to="/wishlist" class="mobile-link" @click="isMenuOpen = false">
          <i class="bi bi-heart me-2"></i>Wishlist
        </router-link>

        <!-- Mobile Orders Link -->
        <router-link to="/orders" class="mobile-link" @click="isMenuOpen = false">
          <i class="bi bi-box me-2"></i>My Orders
        </router-link>

        <!-- Mobile Theme Toggle -->
        <button class="mobile-link mobile-theme-toggle" @click="toggleTheme">
          <i :class="isDark ? 'bi bi-sun-fill' : 'bi bi-moon-fill'"></i>
          {{ isDark ? 'Light Mode' : 'Dark Mode' }}
        </button>

        <!-- Mobile Profile / Login -->
        <div v-if="isLoggedIn" class="mobile-user-section">
          <router-link to="/profile" class="mobile-link" @click="isMenuOpen = false">
            <i class="bi bi-person me-2"></i>{{ userName }}
          </router-link>
          <button class="mobile-link mobile-logout" @click="logout">
            <i class="bi bi-box-arrow-right me-2"></i>Logout
          </button>
        </div>
        <router-link v-else to="/login" class="mobile-link mobile-login" @click="isMenuOpen = false">
          Sign In
        </router-link>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import { useRouter } from 'vue-router'
import { cartApi } from '@/services/api'

const router = useRouter()
const isMenuOpen = ref(false)
const isDark = ref(false)
const cartCount = ref(0)
const isLoggedIn = ref(false)
const userName = ref('')
const searchQuery = ref('')
const showSearchSuggestions = ref(false)

// ===== ALL PRODUCTS FOR SEARCH =====
const allProducts = ref([
  { id: 1, name: 'Wireless Noise-Cancelling Headphones', vendor: 'TechShop', price: 49.99, image: null, emoji: '🎧' },
  { id: 2, name: 'Premium Leather Jacket', vendor: 'FashionHub', price: 89.99, image: null, emoji: '🧥' },
  { id: 3, name: 'Smart Coffee Maker Pro', vendor: 'HomeGoods', price: 129.99, image: null, emoji: '☕' },
  { id: 4, name: 'Fitness Smart Watch', vendor: 'GadgetWorld', price: 199.99, image: null, emoji: '⌚' },
])

const filteredSuggestions = computed(() => {
  if (!searchQuery.value) return []
  const query = searchQuery.value.toLowerCase()
  return allProducts.value
    .filter(p => p.name.toLowerCase().includes(query) || p.vendor.toLowerCase().includes(query))
    .slice(0, 5)
})

// ===== THEME FUNCTIONS =====
const getInitialTheme = () => {
  const savedTheme = localStorage.getItem('theme')
  if (savedTheme) return savedTheme === 'dark'
  return window.matchMedia('(prefers-color-scheme: dark)').matches
}

const toggleTheme = () => {
  isDark.value = !isDark.value
  applyTheme(isDark.value)
}

const applyTheme = (dark) => {
  const html = document.documentElement
  if (dark) {
    html.classList.add('dark')
    html.classList.remove('light')
    localStorage.setItem('theme', 'dark')
  } else {
    html.classList.add('light')
    html.classList.remove('dark')
    localStorage.setItem('theme', 'light')
  }
}

// ===== AUTH FUNCTIONS =====
const checkAuth = () => {
  const user = localStorage.getItem('user')
  const token = localStorage.getItem('token')
  
  if (token && user) {
    try {
      const userData = JSON.parse(user)
      isLoggedIn.value = true
      userName.value = userData.name || 'User'
    } catch (e) {
      isLoggedIn.value = false
      userName.value = ''
    }
  } else {
    isLoggedIn.value = false
    userName.value = ''
  }
}

const logout = () => {
  if (confirm('Are you sure you want to logout?')) {
    localStorage.removeItem('token')
    localStorage.removeItem('user')
    localStorage.removeItem('shopsphere_cart')
    isLoggedIn.value = false
    userName.value = ''
    cartCount.value = 0
    window.dispatchEvent(new CustomEvent('auth-changed'))
    router.push('/')
  }
}

// ===== CART FUNCTIONS =====
const updateCartCount = async () => {
  try {
    const token = localStorage.getItem('token')
    if (token) {
      const response = await cartApi.getCartTotal()
      if (response.data.success) {
        cartCount.value = response.data.data.count || 0
        return
      }
    }
    // Fallback to localStorage
    const savedCart = localStorage.getItem('shopsphere_cart')
    if (savedCart) {
      const items = JSON.parse(savedCart)
      cartCount.value = items.reduce((sum, item) => sum + item.quantity, 0)
    } else {
      cartCount.value = 0
    }
  } catch (error) {
    // Fallback to localStorage
    const savedCart = localStorage.getItem('shopsphere_cart')
    if (savedCart) {
      const items = JSON.parse(savedCart)
      cartCount.value = items.reduce((sum, item) => sum + item.quantity, 0)
    } else {
      cartCount.value = 0
    }
  }
}

// ===== NAVIGATION FUNCTIONS =====
const goToCart = () => router.push('/cart')
const goToWishlist = () => router.push('/wishlist')
const goToOrders = () => router.push('/orders')
const goToProfile = () => router.push('/profile')

// ===== SEARCH FUNCTIONS =====
const performSearch = () => {
  if (searchQuery.value.trim()) {
    showSearchSuggestions.value = false
    router.push({ path: '/products', query: { search: searchQuery.value.trim() } })
  }
}

const goToProduct = (productId) => {
  showSearchSuggestions.value = false
  searchQuery.value = ''
  router.push(`/product/${productId}`)
}

const hideSearchSuggestions = () => {
  setTimeout(() => { showSearchSuggestions.value = false }, 200)
}

// ===== WATCHERS =====
watch(isDark, () => applyTheme(isDark.value))

// ===== LIFECYCLE =====
onMounted(() => {
  isDark.value = getInitialTheme()
  applyTheme(isDark.value)
  checkAuth()
  updateCartCount()
  
  window.addEventListener('storage', () => { updateCartCount(); checkAuth() })
  window.addEventListener('cart-updated', (event) => {
    if (event.detail?.count !== undefined) {
      cartCount.value = event.detail.count
    } else {
      updateCartCount()
    }
  })
  window.addEventListener('auth-changed', checkAuth)
})
</script>

<style scoped>
.navbar-modern {
  background: var(--bg-primary);
  border-bottom: 1px solid var(--border-color);
  padding: 12px 0;
  position: sticky;
  top: 0;
  z-index: 1000;
  transition: var(--transition);
}

.navbar-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
}

.navbar-logo {
  font-size: 1.5rem;
  font-weight: 700;
  text-decoration: none;
  color: var(--text-primary);
  display: flex;
  align-items: center;
  gap: 8px;
}

.logo-icon {
  font-size: 1.8rem;
}

/* ===== SEARCH BAR ===== */
.search-bar {
  position: relative;
  flex: 1;
  max-width: 500px;
  display: flex;
  align-items: center;
  background: var(--bg-secondary);
  border: 2px solid var(--border-color);
  border-radius: 50px;
  transition: var(--transition);
}

.search-bar:focus-within {
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.search-bar i {
  position: absolute;
  left: 16px;
  color: var(--text-muted);
  font-size: 1.1rem;
}

.search-bar input {
  flex: 1;
  padding: 10px 16px 10px 44px;
  border: none;
  background: transparent;
  color: var(--text-primary);
  font-size: 0.95rem;
  outline: none;
}

.search-bar input::placeholder {
  color: var(--text-muted);
}

.search-btn {
  background: var(--gradient-primary);
  border: none;
  color: white;
  width: 38px;
  height: 38px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: var(--transition);
  margin-right: 4px;
  flex-shrink: 0;
}

.search-btn:hover {
  transform: scale(1.05);
}

.search-suggestions {
  position: absolute;
  top: calc(100% + 8px);
  left: 0;
  right: 0;
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: var(--radius);
  box-shadow: var(--shadow-hover);
  max-height: 400px;
  overflow-y: auto;
  z-index: 1001;
  padding: 8px 0;
}

.suggestion-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 16px;
  cursor: pointer;
  transition: var(--transition);
}

.suggestion-item:hover {
  background: var(--bg-secondary);
}

.suggestion-item img {
  width: 40px;
  height: 40px;
  border-radius: var(--radius-sm);
  object-fit: cover;
}

.suggestion-emoji {
  font-size: 1.8rem;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg-secondary);
  border-radius: var(--radius-sm);
}

.suggestion-info {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.suggestion-name {
  font-weight: 500;
  color: var(--text-primary);
  font-size: 0.95rem;
}

.suggestion-vendor {
  font-size: 0.8rem;
  color: var(--text-muted);
}

.suggestion-price {
  font-weight: 600;
  color: #667eea;
  font-size: 0.95rem;
}

.no-suggestions {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 16px;
  color: var(--text-muted);
}

.no-suggestions i {
  font-size: 1.2rem;
}

/* ===== MOBILE SEARCH ===== */
.mobile-search {
  display: none;
  padding: 8px 0 4px;
}

.search-bar.mobile {
  max-width: 100%;
}

.search-bar.mobile .search-btn {
  width: 34px;
  height: 34px;
}

/* ===== NAV LINKS ===== */
.navbar-links {
  display: flex;
  align-items: center;
  gap: 8px;
}

.nav-link {
  padding: 8px 20px;
  border-radius: 50px;
  text-decoration: none;
  color: var(--text-secondary);
  font-weight: 500;
  transition: var(--transition);
}

.nav-link:hover {
  color: var(--text-primary);
  background: var(--bg-secondary);
}

.nav-link.router-link-active {
  color: #667eea;
  background: rgba(102, 126, 234, 0.1);
}

/* ===== ACTIONS ===== */
.navbar-actions {
  display: flex;
  align-items: center;
  gap: 8px;
}

.action-btn {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  border: none;
  background: transparent;
  color: var(--text-secondary);
  font-size: 1.1rem;
  cursor: pointer;
  transition: var(--transition);
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
}

.action-btn:hover {
  background: var(--bg-secondary);
  color: var(--text-primary);
}

.orders-btn {
  font-size: 1rem;
}

/* ===== USER MENU ===== */
.user-menu {
  display: flex;
  align-items: center;
}

.profile-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 6px 16px !important;
  font-size: 13px !important;
  white-space: nowrap;
}

.user-name {
  max-width: 100px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.signin-btn {
  padding: 6px 20px !important;
  font-size: 13px !important;
}

/* ===== CART BADGE ===== */
.cart-btn {
  position: relative;
}

.cart-badge {
  position: absolute;
  top: -4px;
  right: -4px;
  background: #ef4444;
  color: white;
  font-size: 9px;
  font-weight: 700;
  min-width: 18px;
  height: 18px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.1); }
  100% { transform: scale(1); }
}

/* ===== THEME TOGGLE ===== */
.theme-toggle {
  font-size: 1.2rem;
  color: var(--text-primary);
}

.theme-toggle:hover {
  background: var(--bg-secondary);
  transform: rotate(30deg);
}

/* ===== MOBILE ===== */
.mobile-toggle {
  display: none;
  flex-direction: column;
  gap: 4px;
  background: none;
  border: none;
  cursor: pointer;
  padding: 4px;
}

.mobile-toggle span {
  width: 24px;
  height: 2px;
  background: var(--text-primary);
  border-radius: 2px;
  transition: var(--transition);
}

.mobile-menu {
  display: none;
  flex-direction: column;
  padding: 16px 0;
  gap: 4px;
}

.mobile-link {
  padding: 10px 16px;
  border-radius: var(--radius-sm);
  text-decoration: none;
  color: var(--text-secondary);
  font-weight: 500;
  transition: var(--transition);
  background: none;
  border: none;
  text-align: left;
  font-size: 0.95rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 10px;
  width: 100%;
}

.mobile-link:hover {
  background: var(--bg-secondary);
  color: var(--text-primary);
}

.mobile-link.router-link-active {
  color: #667eea;
  background: rgba(102, 126, 234, 0.1);
}

.mobile-badge {
  background: #ef4444;
  color: white;
  font-size: 11px;
  font-weight: 700;
  padding: 1px 10px;
  border-radius: 50px;
  margin-left: auto;
}

.mobile-theme-toggle {
  display: flex;
  align-items: center;
  gap: 10px;
}

.mobile-theme-toggle i {
  font-size: 1.2rem;
}

.mobile-user-section {
  border-top: 1px solid var(--border-color);
  margin-top: 8px;
  padding-top: 8px;
}

.mobile-logout {
  color: #ef4444 !important;
}

.mobile-logout:hover {
  background: rgba(239, 68, 68, 0.1) !important;
  color: #dc2626 !important;
}

.mobile-login {
  background: var(--gradient-primary);
  color: white !important;
  text-align: center;
  justify-content: center;
}

.mobile-login:hover {
  opacity: 0.9;
  color: white !important;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
  .navbar-links {
    display: none;
  }
  
  .navbar-actions .btn-primary-modern {
    display: none;
  }
  
  .mobile-toggle {
    display: flex;
  }
  
  .mobile-menu {
    display: flex;
  }
  
  .mobile-search {
    display: block;
  }
  
  .search-bar:not(.mobile) {
    display: none;
  }
}

@media (min-width: 769px) {
  .mobile-toggle,
  .mobile-menu,
  .mobile-search {
    display: none !important;
  }
}

/* ===== DARK MODE ===== */
html.dark .cart-badge {
  background: #ef4444;
}

html.dark .mobile-badge {
  background: #ef4444;
}

html.dark .mobile-logout {
  color: #f87171 !important;
}

html.dark .mobile-logout:hover {
  background: rgba(239, 68, 68, 0.15) !important;
  color: #fca5a5 !important;
}
</style>