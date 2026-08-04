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
          <!-- For vendors, show Dashboard link -->
          <router-link v-if="isVendor" to="/vendor/dashboard" class="nav-link vendor-nav-link">
            📊 Dashboard
          </router-link>
          <router-link v-else to="/" class="nav-link" exact>Home</router-link>
          
          <router-link to="/products" class="nav-link">Products</router-link>
          <router-link to="/about" class="nav-link">About</router-link>
          
          <!-- Vendor Quick Link -->
          <router-link v-if="isVendor" to="/vendor/products" class="nav-link vendor-nav-link">
            📦 My Store
          </router-link>
        </div>

        <!-- Actions -->
        <div class="navbar-actions">
          <!-- Dark Mode Toggle -->
          <button class="action-btn theme-toggle" @click="toggleTheme" :title="isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode'">
            <i :class="isDark ? 'bi bi-sun-fill' : 'bi bi-moon-fill'"></i>
          </button>
          
          <!-- Wishlist Button with Badge -->
          <button class="action-btn wishlist-btn-nav" @click="goToWishlist" title="Wishlist" style="position: relative;">
            <i class="bi bi-heart"></i>
            <span v-if="wishlistCount > 0" class="wishlist-badge">{{ wishlistCount }}</span>
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
        <button class="mobile-toggle" @click="isMenuOpen = !isMenuOpen" aria-label="Toggle menu">
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
        <!-- For vendors, show Dashboard first -->
        <router-link v-if="isVendor" to="/vendor/dashboard" class="mobile-link" @click="isMenuOpen = false">
          📊 Dashboard
        </router-link>
        <router-link v-else to="/" class="mobile-link" @click="isMenuOpen = false">Home</router-link>
        
        <router-link to="/products" class="mobile-link" @click="isMenuOpen = false">Products</router-link>
        <router-link to="/about" class="mobile-link" @click="isMenuOpen = false">About</router-link>
        
        <!-- Vendor Store Link -->
        <router-link v-if="isVendor" to="/vendor/products" class="mobile-link" @click="isMenuOpen = false">
          📦 My Store
        </router-link>
        
        <div class="mobile-divider"></div>
        
        <!-- Mobile Cart Link -->
        <router-link to="/cart" class="mobile-link" @click="isMenuOpen = false">
          <i class="bi bi-cart3 me-2"></i>Cart
          <span v-if="cartCount > 0" class="mobile-badge">{{ cartCount }}</span>
        </router-link>

        <!-- Mobile Wishlist Link -->
        <router-link to="/wishlist" class="mobile-link" @click="isMenuOpen = false">
          <i class="bi bi-heart me-2"></i>Wishlist
          <span v-if="wishlistCount > 0" class="mobile-badge">{{ wishlistCount }}</span>
        </router-link>

        <!-- Mobile Orders Link -->
        <router-link to="/orders" class="mobile-link" @click="isMenuOpen = false">
          <i class="bi bi-box me-2"></i>My Orders
        </router-link>

        <div class="mobile-divider"></div>

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
          <button class="mobile-link mobile-logout" @click="handleLogout">
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
import { ref, onMounted, onUnmounted, computed, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { cartApi, wishlistApi } from '@/services/api'

const router = useRouter()
const route = useRoute()

// ===== STATE =====
const isMenuOpen = ref(false)
const isDark = ref(false)
const cartCount = ref(0)
const wishlistCount = ref(0)
const isLoggedIn = ref(false)
const isVendor = ref(false)
const userName = ref('')
const searchQuery = ref('')
const showSearchSuggestions = ref(false)
const allProducts = ref([])

// ===== COMPUTED =====
const filteredSuggestions = computed(() => {
  if (!searchQuery.value) return []
  const query = searchQuery.value.toLowerCase()
  return allProducts.value
    .filter(p => p.name?.toLowerCase().includes(query) || p.vendor?.toLowerCase().includes(query))
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
  const token = localStorage.getItem('token')
  const user = localStorage.getItem('user')
  
  if (token && user) {
    try {
      const userData = JSON.parse(user)
      isLoggedIn.value = true
      isVendor.value = userData.role === 'vendor' || userData.role === 'admin'
      userName.value = userData.name || userData.store_name || 'User'
    } catch (e) {
      isLoggedIn.value = false
      isVendor.value = false
      userName.value = ''
    }
  } else {
    isLoggedIn.value = false
    isVendor.value = false
    userName.value = ''
  }
}

const handleLogout = () => {
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  localStorage.removeItem('shopsphere_cart')
  localStorage.removeItem('shopsphere_wishlist')
  isLoggedIn.value = false
  isVendor.value = false
  userName.value = ''
  cartCount.value = 0
  wishlistCount.value = 0
  isMenuOpen.value = false
  window.dispatchEvent(new CustomEvent('auth-changed'))
  router.push('/login')
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
      cartCount.value = items.reduce((sum, item) => sum + (item.quantity || 0), 0)
    } else {
      cartCount.value = 0
    }
  } catch (error) {
    const savedCart = localStorage.getItem('shopsphere_cart')
    if (savedCart) {
      const items = JSON.parse(savedCart)
      cartCount.value = items.reduce((sum, item) => sum + (item.quantity || 0), 0)
    } else {
      cartCount.value = 0
    }
  }
}

// ===== WISHLIST FUNCTIONS =====
const updateWishlistCount = async () => {
  try {
    const token = localStorage.getItem('token')
    if (token) {
      const response = await wishlistApi.getWishlist()
      if (response.data.success) {
        wishlistCount.value = response.data.data.count || response.data.data.length || 0
        return
      }
    }
    const savedWishlist = localStorage.getItem('shopsphere_wishlist')
    if (savedWishlist) {
      const items = JSON.parse(savedWishlist)
      wishlistCount.value = items.length
    } else {
      wishlistCount.value = 0
    }
  } catch (error) {
    const savedWishlist = localStorage.getItem('shopsphere_wishlist')
    if (savedWishlist) {
      const items = JSON.parse(savedWishlist)
      wishlistCount.value = items.length
    } else {
      wishlistCount.value = 0
    }
  }
}

// ===== NAVIGATION FUNCTIONS =====
const goToCart = () => {
  isMenuOpen.value = false
  router.push('/cart')
}
const goToWishlist = () => {
  isMenuOpen.value = false
  router.push('/wishlist')
}
const goToOrders = () => {
  isMenuOpen.value = false
  router.push('/orders')
}
const goToProfile = () => {
  isMenuOpen.value = false
  router.push('/profile')
}

// ===== SEARCH FUNCTIONS =====
const performSearch = () => {
  if (searchQuery.value.trim()) {
    showSearchSuggestions.value = false
    isMenuOpen.value = false
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

// ===== LOAD PRODUCTS FOR SEARCH =====
const loadProducts = async () => {
  try {
    const response = await fetch('http://localhost:8000/api/products')
    if (response.ok) {
      const data = await response.json()
      allProducts.value = data.data || []
    }
  } catch (error) {
    // Fallback products
    allProducts.value = [
      { id: 1, name: 'Wireless Headphones', vendor: 'TechShop', price: 49.99, emoji: '🎧' },
      { id: 2, name: 'Leather Jacket', vendor: 'FashionHub', price: 89.99, emoji: '🧥' },
      { id: 3, name: 'Coffee Maker Pro', vendor: 'HomeGoods', price: 129.99, emoji: '☕' },
      { id: 4, name: 'Smart Watch', vendor: 'GadgetWorld', price: 199.99, emoji: '⌚' },
    ]
  }
}

// ===== LIFECYCLE =====
onMounted(() => {
  isDark.value = getInitialTheme()
  applyTheme(isDark.value)
  checkAuth()
  updateCartCount()
  updateWishlistCount()
  loadProducts()
  
  // Listen for events
  window.addEventListener('auth-changed', () => {
    checkAuth()
    updateCartCount()
    updateWishlistCount()
  })
  
  window.addEventListener('cart-updated', () => {
    updateCartCount()
  })
  
  window.addEventListener('wishlist-updated', () => {
    updateWishlistCount()
  })
  
  window.addEventListener('storage', () => {
    checkAuth()
    updateCartCount()
    updateWishlistCount()
  })
})

onUnmounted(() => {
  window.removeEventListener('auth-changed', checkAuth)
  window.removeEventListener('cart-updated', updateCartCount)
  window.removeEventListener('wishlist-updated', updateWishlistCount)
  window.removeEventListener('storage', checkAuth)
})

// Watch route changes to update active states
watch(() => route.path, () => {
  // Close mobile menu on route change
  isMenuOpen.value = false
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

.container-custom {
  max-width: 1280px;
  margin: 0 auto;
  padding: 0 20px;
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
  flex-shrink: 0;
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
  gap: 4px;
}

.nav-link {
  padding: 8px 16px;
  border-radius: 50px;
  text-decoration: none;
  color: var(--text-secondary);
  font-weight: 500;
  transition: var(--transition);
  font-size: 14px;
}

.nav-link:hover {
  color: var(--text-primary);
  background: var(--bg-secondary);
}

.nav-link.router-link-active {
  color: #667eea;
  background: rgba(102, 126, 234, 0.1);
}

.nav-link.vendor-nav-link {
  color: #667eea;
  background: rgba(102, 126, 234, 0.06);
}

.nav-link.vendor-nav-link:hover {
  background: rgba(102, 126, 234, 0.12);
}

/* ===== ACTIONS ===== */
.navbar-actions {
  display: flex;
  align-items: center;
  gap: 6px;
  flex-shrink: 0;
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

/* ===== WISHLIST BADGE ===== */
.wishlist-btn-nav {
  position: relative;
}

.wishlist-badge {
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

.mobile-toggle:hover span {
  background: #667eea;
}

.mobile-menu {
  display: none;
  flex-direction: column;
  padding: 16px 0;
  gap: 4px;
  border-top: 1px solid var(--border-color);
  margin-top: 8px;
}

.mobile-divider {
  height: 1px;
  background: var(--border-color);
  margin: 8px 0;
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
@media (max-width: 992px) {
  .navbar-links {
    display: none;
  }
}

@media (max-width: 768px) {
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
  
  .container-custom {
    padding: 0 16px;
  }
  
  .navbar-actions .action-btn {
    width: 34px;
    height: 34px;
    font-size: 1rem;
  }
  
  .profile-btn {
    padding: 4px 12px !important;
    font-size: 12px !important;
  }
  
  .profile-btn .user-name {
    max-width: 60px;
  }
}

@media (min-width: 769px) {
  .mobile-toggle,
  .mobile-menu,
  .mobile-search {
    display: none !important;
  }
}

@media (max-width: 480px) {
  .navbar-logo {
    font-size: 1.2rem;
  }
  
  .logo-icon {
    font-size: 1.4rem;
  }
  
  .navbar-actions .action-btn {
    width: 30px;
    height: 30px;
    font-size: 0.9rem;
  }
  
  .cart-badge,
  .wishlist-badge {
    min-width: 16px;
    height: 16px;
    font-size: 8px;
    top: -3px;
    right: -3px;
  }
  
  .search-bar.mobile input {
    font-size: 0.85rem;
    padding: 8px 12px 8px 36px;
  }
  
  .search-bar.mobile i {
    font-size: 0.9rem;
    left: 12px;
  }
  
  .search-bar.mobile .search-btn {
    width: 30px;
    height: 30px;
  }
}

/* ===== DARK MODE ===== */
html.dark .cart-badge {
  background: #ef4444;
}

html.dark .wishlist-badge {
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