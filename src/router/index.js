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

const routes = [
  // Public Routes
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
  
  // Vendor Routes (with Layout)
  {
    path: '/vendor',
    component: VendorLayout,
    meta: { requiresVendor: true },
    children: [
      { path: 'dashboard', name: 'VendorDashboard', component: VendorDashboard },
      { path: 'products', name: 'VendorProducts', component: VendorProducts },
      { path: 'orders', name: 'VendorOrders', component: VendorOrders },
    ]
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router