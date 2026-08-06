import { createRouter, createWebHistory } from 'vue-router'
import Home from '../views/Home.vue'
import Products from '../views/Products.vue'
import ProductDetail from '../views/ProductDetail.vue'
import Cart from '../views/Cart.vue'
import Wishlist from '../views/Wishlist.vue'
import Checkout from '../views/Checkout.vue'
import Orders from '../views/Orders.vue'
import Profile from '../views/Profile.vue'
import Login from '../views/Login.vue'
import Register from '../views/Register.vue'
import About from '../views/About.vue'
import OrderConfirmation from '../views/OrderConfirmation.vue'

// Vendor Views
import VendorLayout from '../views/vendor/Layout.vue'
import VendorDashboard from '../views/vendor/Dashboard.vue'
import VendorProducts from '../views/vendor/Products.vue'
import VendorOrders from '../views/vendor/Orders.vue'
import VendorProfile from '../views/vendor/Profile.vue'

// Admin Views
import AdminLayout from '../views/admin/Layout.vue'
import AdminDashboard from '../views/admin/Dashboard.vue'
import AdminUsers from '../views/admin/Users.vue'
import AdminVendors from '../views/admin/Vendors.vue'
import AdminOrders from '../views/admin/Orders.vue'
import AdminCategories from '../views/admin/Categories.vue'
import AdminSettings from '../views/admin/Settings.vue'

const routes = [
  // ===== PUBLIC ROUTES =====
  { path: '/', name: 'Home', component: Home },
  { path: '/products', name: 'Products', component: Products },
  { path: '/product/:id', name: 'ProductDetail', component: ProductDetail },
  { path: '/cart', name: 'Cart', component: Cart },
  { path: '/wishlist', name: 'Wishlist', component: Wishlist },
  { path: '/checkout', name: 'Checkout', component: Checkout },
  { path: '/orders', name: 'Orders', component: Orders },
  { path: '/profile', name: 'Profile', component: Profile },
  { path: '/login', name: 'Login', component: Login },
  { path: '/register', name: 'Register', component: Register },
  { path: '/about', name: 'About', component: About },
  { path: '/order-confirmation/:id', name: 'OrderConfirmation', component: OrderConfirmation },
  
  // ===== VENDOR ROUTES =====
  {
    path: '/vendor',
    component: VendorLayout,
    meta: { requiresVendor: true },
    children: [
      { path: 'dashboard', name: 'VendorDashboard', component: VendorDashboard },
      { path: 'products', name: 'VendorProducts', component: VendorProducts },
      { path: 'orders', name: 'VendorOrders', component: VendorOrders },
      { path: 'profile', name: 'VendorProfile', component: VendorProfile },
    ]
  },

  // ===== ADMIN ROUTES =====
  {
    path: '/admin',
    component: AdminLayout,
    meta: { requiresAdmin: true },
    children: [
      { path: 'dashboard', name: 'AdminDashboard', component: AdminDashboard },
      { path: 'users', name: 'AdminUsers', component: AdminUsers },
      { path: 'vendors', name: 'AdminVendors', component: AdminVendors },
      { path: 'orders', name: 'AdminOrders', component: AdminOrders },
      { path: 'categories', name: 'AdminCategories', component: AdminCategories },
      { path: 'settings', name: 'AdminSettings', component: AdminSettings },
    ]
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// ===== ✅ FIXED NAVIGATION GUARD =====
router.beforeEach((to, from, next) => {
  // Get user data from localStorage
  const token = localStorage.getItem('token')
  const userStr = localStorage.getItem('user')
  
  let user = null
  let isVendor = false
  let isAdmin = false
  
  if (userStr) {
    try {
      user = JSON.parse(userStr)
      // ✅ FIX: isVendor should only be true for 'vendor' role, not 'admin'
      isVendor = user?.role === 'vendor'
      isAdmin = user?.role === 'admin'
    } catch (e) {
      console.error('Error parsing user:', e)
    }
  }
  
  console.log('🔍 Route guard:', { 
    path: to.path, 
    hasToken: !!token, 
    isVendor, 
    isAdmin,
    userRole: user?.role 
  })
  
  // ===== VENDOR ROUTE PROTECTION =====
  if (to.path.startsWith('/vendor')) {
    if (!token) {
      console.log('🔒 Not logged in → redirecting to login')
      next('/login')
      return
    }
    if (!isVendor) {
      console.log('🚫 Not a vendor → redirecting to home')
      next('/')
      return
    }
  }
  
  // ===== ADMIN ROUTE PROTECTION =====
  if (to.path.startsWith('/admin')) {
    if (!token) {
      console.log('🔒 Not logged in → redirecting to login')
      next('/login')
      return
    }
    if (!isAdmin) {
      console.log('🚫 Not an admin → redirecting to home')
      next('/')
      return
    }
  }
  
  // ===== HOMEPAGE REDIRECT =====
  if (to.path === '/' && token) {
    if (isAdmin) {
      console.log('🔀 Admin on homepage → redirecting to admin dashboard')
      next('/admin/dashboard')
      return
    }
    if (isVendor) {
      console.log('🔀 Vendor on homepage → redirecting to vendor dashboard')
      next('/vendor/dashboard')
      return
    }
  }
  
  // ===== LOGIN/REGISTER REDIRECT =====
  if ((to.path === '/login' || to.path === '/register') && token) {
    if (isAdmin) {
      next('/admin/dashboard')
    } else if (isVendor) {
      next('/vendor/dashboard')
    } else {
      next('/')
    }
    return
  }
  
  next()
})

export default router