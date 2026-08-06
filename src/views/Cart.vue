<template>
  <div class="cart-page">
    <div class="container-custom">
      <!-- Page Header -->
      <div class="page-header">
        <h1 class="page-title">Shopping Cart</h1>
        <p class="page-subtitle">{{ cartItems.length }} items in your cart</p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p>Loading your cart...</p>
      </div>

      <!-- Cart Content -->
      <div v-else-if="cartItems.length > 0" class="cart-content">
        <!-- Cart Items -->
        <div class="cart-items">
          <div v-for="item in cartItems" :key="item.id" class="cart-item">
            <div class="item-image" :style="{ background: '#e8ecf1' }">
              <img 
                v-if="item.image" 
                :src="item.image" 
                :alt="item.name"
                class="cart-item-img"
                loading="lazy"
                @error="handleImageError"
              />
              <span v-else>{{ item.emoji || '📦' }}</span>
            </div>
            
            <div class="item-details">
              <h4>{{ item.name }}</h4>
              <p class="item-vendor">{{ item.vendor || 'ShopSphere' }}</p>
              <div class="item-price">
                <span class="current-price">${{ item.price }}</span>
                <span v-if="item.originalPrice" class="original-price">${{ item.originalPrice }}</span>
              </div>
            </div>

            <div class="item-actions">
              <div class="quantity-control">
                <button @click="updateQuantity(item.id, -1)" :disabled="item.quantity <= 1">
                  <i class="bi bi-dash"></i>
                </button>
                <span>{{ item.quantity }}</span>
                <button @click="updateQuantity(item.id, 1)">
                  <i class="bi bi-plus"></i>
                </button>
              </div>
              <div class="item-total">
                ${{ (item.price * item.quantity).toFixed(2) }}
              </div>
              <button class="remove-btn" @click="removeItem(item.id)">
                <i class="bi bi-trash3"></i>
              </button>
            </div>
          </div>
        </div>

        <!-- Cart Summary -->
        <div class="cart-summary">
          <h3>Order Summary</h3>
          
          <!-- ✅ COUPON CODE SECTION -->
          <div class="coupon-section">
            <div class="coupon-input">
              <input 
                v-model="couponCode" 
                placeholder="Enter coupon code" 
                :disabled="couponApplied"
                class="coupon-input-field"
              />
              <button 
                @click="applyCoupon" 
                :disabled="couponApplied || !couponCode || loading"
                class="coupon-btn"
              >
                {{ couponApplied ? 'Applied ✓' : 'Apply' }}
              </button>
            </div>
            <div v-if="couponMessage" class="coupon-message" :class="couponSuccess ? 'success' : 'error'">
              {{ couponMessage }}
            </div>
            <div v-if="couponApplied" class="coupon-applied">
              <span class="coupon-code-display">🎫 {{ couponCode.toUpperCase() }}</span>
              <button @click="removeCoupon" class="remove-coupon-btn">✕</button>
            </div>
          </div>

          <div class="summary-row">
            <span>Subtotal</span>
            <span>${{ subtotal.toFixed(2) }}</span>
          </div>
          
          <!-- ✅ Show discount if applied -->
          <div v-if="discount > 0" class="summary-row discount">
            <span>Discount</span>
            <span class="discount-amount">-${{ discount.toFixed(2) }}</span>
          </div>

          <div class="summary-row">
            <span>Shipping</span>
            <span>{{ shipping > 0 ? '$' + shipping.toFixed(2) : 'Free' }}</span>
          </div>
          <div class="summary-row">
            <span>Tax (8%)</span>
            <span>${{ tax.toFixed(2) }}</span>
          </div>
          
          <div class="summary-divider"></div>
          
          <div class="summary-row total">
            <span>Total</span>
            <span>${{ total.toFixed(2) }}</span>
          </div>
          
          <button class="btn-primary-modern checkout-btn" @click="goToCheckout" :disabled="loading">
            <i class="bi bi-credit-card me-2"></i>Proceed to Checkout
          </button>
          <button class="btn-outline-modern continue-btn" @click="continueShopping">
            <i class="bi bi-arrow-left me-2"></i>Continue Shopping
          </button>
        </div>
      </div>

      <!-- Empty Cart -->
      <div v-else class="empty-cart">
        <div class="empty-state">
          <i class="bi bi-cart3" style="font-size: 5rem; color: var(--text-muted);"></i>
          <h3>Your Cart is Empty</h3>
          <p>Looks like you haven't added any items to your cart yet.</p>
          <button class="btn-primary-modern" @click="continueShopping">
            <i class="bi bi-arrow-left me-2"></i>Start Shopping
          </button>
        </div>
      </div>
    </div>

    <!-- Toast Notification -->
    <div v-if="toast.show" class="toast-notification" :class="toast.type">
      <i :class="toast.icon"></i>
      {{ toast.message }}
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { cartApi } from '@/services/api'

const router = useRouter()

// ===== STATE =====
const cartItems = ref([])
const loading = ref(true)
const error = ref(null)

// ===== COUPON STATE =====
const couponCode = ref('')
const couponApplied = ref(false)
const couponMessage = ref('')
const couponSuccess = ref(false)
const discount = ref(0)

// ===== TOAST STATE =====
const toast = ref({
  show: false,
  message: '',
  type: 'success',
  icon: 'bi bi-check-circle-fill'
})

// ===== COMPUTED =====
const subtotal = computed(() => {
  return cartItems.value.reduce((sum, item) => sum + (item.price * item.quantity), 0)
})

const shipping = computed(() => {
  return subtotal.value > 100 ? 0 : 5.99
})

const tax = computed(() => {
  return (subtotal.value - discount.value) * 0.08
})

const total = computed(() => {
  return (subtotal.value - discount.value) + shipping.value + tax.value
})

// ===== TOAST METHODS =====
const showToast = (message, type = 'success', icon = 'bi bi-check-circle-fill') => {
  toast.value = { show: true, message, type, icon }
  setTimeout(() => { toast.value.show = false }, 3000)
}

// ===== COUPON METHODS =====
const applyCoupon = async () => {
  if (!couponCode.value) return
  
  try {
    const token = localStorage.getItem('token')
    
    // Validate coupon
    const response = await axios.post('http://localhost:8000/api/coupons/validate', {
      code: couponCode.value,
      subtotal: subtotal.value
    }, {
      headers: {
        'Authorization': token ? `Bearer ${token}` : '',
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      }
    })
    
    if (response.data.success) {
      const data = response.data.data
      couponApplied.value = true
      couponSuccess.value = true
      discount.value = data.discount_amount
      couponMessage.value = `✅ Coupon applied! You saved $${data.discount_amount.toFixed(2)}`
      showToast(`Coupon applied! You saved $${data.discount_amount.toFixed(2)}`, 'success', 'bi bi-tag-fill')
    }
  } catch (error) {
    couponSuccess.value = false
    couponMessage.value = error.response?.data?.message || 'Invalid coupon code'
    discount.value = 0
    showToast(couponMessage.value, 'error', 'bi bi-exclamation-circle-fill')
  }
}

const removeCoupon = () => {
  couponApplied.value = false
  couponCode.value = ''
  couponMessage.value = ''
  discount.value = 0
  showToast('Coupon removed', 'info', 'bi bi-info-circle')
}

// ===== CART METHODS =====
const loadCart = async () => {
  loading.value = true
  error.value = null
  
  try {
    const token = localStorage.getItem('token')
    const response = await cartApi.getCart()
    
    if (response.data.success) {
      const cartData = response.data.data
      cartItems.value = cartData.items.map(item => ({
        id: item.id,
        product_id: item.product_id,
        name: item.name,
        price: item.price,
        quantity: item.quantity,
        image: item.image || null,
        vendor: item.vendor || 'ShopSphere',
        total: item.total
      }))
      
      // Reset coupon if cart changed
      if (couponApplied.value) {
        removeCoupon()
      }
      
      // Update cart count in navbar
      window.dispatchEvent(new CustomEvent('cart-updated', { 
        detail: { count: cartData.count } 
      }))
    }
  } catch (err) {
    console.error('Error loading cart:', err)
    error.value = 'Failed to load cart. Please try again.'
    if (err.response?.status === 401) {
      router.push('/login')
    }
  } finally {
    loading.value = false
  }
}

const updateQuantity = async (itemId, change) => {
  const item = cartItems.value.find(i => i.id === itemId)
  if (!item) return
  
  const newQuantity = item.quantity + change
  if (newQuantity < 1) return
  
  try {
    const response = await cartApi.updateCartItem(itemId, newQuantity)
    if (response.data.success) {
      // Remove coupon if applied
      if (couponApplied.value) {
        removeCoupon()
      }
      await loadCart()
      window.dispatchEvent(new CustomEvent('cart-updated'))
    }
  } catch (err) {
    console.error('Error updating quantity:', err)
    alert(err.response?.data?.message || 'Failed to update cart')
  }
}

const removeItem = async (itemId) => {
  if (!confirm('Remove this item from cart?')) return
  
  try {
    const response = await cartApi.removeCartItem(itemId)
    if (response.data.success) {
      if (couponApplied.value) {
        removeCoupon()
      }
      await loadCart()
      window.dispatchEvent(new CustomEvent('cart-updated'))
    }
  } catch (err) {
    console.error('Error removing item:', err)
    alert('Failed to remove item from cart')
  }
}

const handleImageError = (e) => {
  e.target.style.display = 'none'
  const parent = e.target.parentElement
  const fallback = document.createElement('span')
  fallback.textContent = '📦'
  fallback.style.fontSize = '2.5rem'
  parent.appendChild(fallback)
}

const goToCheckout = () => {
  router.push('/checkout')
}

const continueShopping = () => {
  router.push('/products')
}

// ===== LIFECYCLE =====
onMounted(() => {
  loadCart()
})
</script>

<style scoped>
.cart-page {
  padding: 40px 0 80px;
  background: var(--bg-primary);
  min-height: 100vh;
}

.container-custom {
  max-width: 1280px;
  margin: 0 auto;
  padding: 0 20px;
}

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

.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 300px;
  gap: 20px;
}

.spinner {
  width: 48px;
  height: 48px;
  border: 4px solid var(--border-color);
  border-top: 4px solid #667eea;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.cart-content {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 40px;
}

.cart-items {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.cart-item {
  display: grid;
  grid-template-columns: 80px 1fr auto;
  gap: 20px;
  align-items: center;
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: var(--radius);
  padding: 16px;
  transition: var(--transition);
}

.cart-item:hover {
  box-shadow: var(--shadow);
}

.item-image {
  width: 80px;
  height: 80px;
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2.5rem;
  background: var(--bg-secondary);
  overflow: hidden;
}

.cart-item-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.item-details {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.item-details h4 {
  font-size: 1.05rem;
  font-weight: 600;
  color: var(--text-primary);
  margin: 0;
}

.item-vendor {
  font-size: 0.85rem;
  color: var(--text-muted);
}

.item-price {
  display: flex;
  align-items: center;
  gap: 10px;
}

.current-price {
  font-size: 1.1rem;
  font-weight: 600;
  color: #667eea;
}

.original-price {
  font-size: 0.9rem;
  color: var(--text-muted);
  text-decoration: line-through;
}

.item-actions {
  display: flex;
  align-items: center;
  gap: 16px;
}

.quantity-control {
  display: flex;
  align-items: center;
  border: 1px solid var(--border-color);
  border-radius: var(--radius-sm);
  overflow: hidden;
}

.quantity-control button {
  width: 32px;
  height: 32px;
  border: none;
  background: var(--bg-secondary);
  color: var(--text-primary);
  font-size: 1rem;
  cursor: pointer;
  transition: var(--transition);
}

.quantity-control button:hover:not(:disabled) {
  background: var(--bg-primary);
}

.quantity-control button:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

.quantity-control span {
  width: 32px;
  text-align: center;
  font-weight: 600;
}

.item-total {
  font-size: 1.1rem;
  font-weight: 600;
  color: var(--text-primary);
  min-width: 70px;
  text-align: right;
}

.remove-btn {
  background: none;
  border: none;
  color: #ef4444;
  font-size: 1.2rem;
  cursor: pointer;
  transition: var(--transition);
  padding: 8px;
}

.remove-btn:hover {
  transform: scale(1.1);
  color: #dc2626;
}

.cart-summary {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: var(--radius);
  padding: 24px;
  height: fit-content;
  position: sticky;
  top: 100px;
}

.cart-summary h3 {
  font-size: 1.3rem;
  font-weight: 700;
  color: var(--text-primary);
  margin-bottom: 20px;
  padding-bottom: 12px;
  border-bottom: 1px solid var(--border-color);
}

/* ===== COUPON SECTION ===== */
.coupon-section {
  background: #f9fafb;
  padding: 16px;
  border-radius: 8px;
  margin-bottom: 16px;
}

.coupon-input {
  display: flex;
  gap: 8px;
}

.coupon-input-field {
  flex: 1;
  padding: 10px 14px;
  border: 2px dashed #d1d5db;
  border-radius: 8px;
  font-size: 14px;
  outline: none;
  transition: all 0.2s ease;
  background: white;
}

.coupon-input-field:focus {
  border-color: #667eea;
}

.coupon-input-field:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.coupon-btn {
  padding: 10px 20px;
  background: #667eea;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.coupon-btn:hover:not(:disabled) {
  background: #5a67d8;
}

.coupon-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.coupon-message {
  margin-top: 8px;
  font-size: 14px;
  padding: 8px 12px;
  border-radius: 6px;
}

.coupon-message.success {
  background: #d1fae5;
  color: #059669;
}

.coupon-message.error {
  background: #fee2e2;
  color: #dc2626;
}

.coupon-applied {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 8px;
  padding: 8px 12px;
  background: #d1fae5;
  border-radius: 6px;
}

.coupon-code-display {
  font-weight: 600;
  color: #059669;
  text-transform: uppercase;
}

.remove-coupon-btn {
  background: none;
  border: none;
  color: #dc2626;
  cursor: pointer;
  font-size: 16px;
  padding: 0 4px;
}

.remove-coupon-btn:hover {
  color: #ef4444;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  padding: 8px 0;
  color: var(--text-secondary);
}

.summary-row.discount {
  color: #059669;
}

.discount-amount {
  font-weight: 600;
}

.summary-row.total {
  font-size: 1.2rem;
  font-weight: 700;
  color: var(--text-primary);
  padding-top: 12px;
  border-top: 2px solid var(--border-color);
  margin-top: 8px;
}

.summary-divider {
  border-top: 1px solid var(--border-color);
  margin: 8px 0;
}

.checkout-btn {
  width: 100%;
  text-align: center;
  margin-top: 16px;
}

.continue-btn {
  width: 100%;
  text-align: center;
  margin-top: 10px;
}

.empty-cart {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 400px;
}

.empty-state {
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
}

.empty-state h3 {
  font-size: 1.5rem;
  color: var(--text-primary);
  margin: 0;
}

.empty-state p {
  color: var(--text-muted);
  font-size: 1.05rem;
}

/* ===== TOAST ===== */
.toast-notification {
  position: fixed;
  bottom: 30px;
  right: 30px;
  padding: 16px 24px;
  border-radius: var(--radius-sm);
  color: white;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 12px;
  z-index: 9999;
  animation: slideUp 0.3s ease;
  box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}

.toast-notification.success {
  background: #10b981;
}

.toast-notification.error {
  background: #ef4444;
}

.toast-notification.info {
  background: #3b82f6;
}

.toast-notification i {
  font-size: 20px;
}

@keyframes slideUp {
  from {
    transform: translateY(100px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

/* Responsive */
@media (max-width: 1024px) {
  .cart-content {
    grid-template-columns: 1fr;
  }
  
  .cart-summary {
    position: static;
  }
}

@media (max-width: 768px) {
  .cart-page {
    padding: 20px 0 60px;
  }

  .page-title {
    font-size: 2rem;
  }

  .cart-item {
    grid-template-columns: 60px 1fr;
    gap: 12px;
    padding: 12px;
  }

  .item-image {
    width: 60px;
    height: 60px;
    font-size: 2rem;
  }

  .item-actions {
    grid-column: 1 / -1;
    justify-content: space-between;
    padding-top: 8px;
    border-top: 1px solid var(--border-color);
  }

  .item-total {
    min-width: auto;
  }
  
  .coupon-input {
    flex-direction: column;
  }
  
  .coupon-btn {
    width: 100%;
  }
}

@media (max-width: 480px) {
  .cart-item {
    grid-template-columns: 50px 1fr;
    padding: 10px;
  }

  .item-image {
    width: 50px;
    height: 50px;
    font-size: 1.5rem;
  }

  .item-details h4 {
    font-size: 0.95rem;
  }

  .current-price {
    font-size: 1rem;
  }

  .quantity-control button {
    width: 28px;
    height: 28px;
    font-size: 0.9rem;
  }

  .quantity-control span {
    width: 28px;
  }

  .item-total {
    font-size: 1rem;
  }
  
  .toast-notification {
    bottom: 16px;
    right: 16px;
    left: 16px;
    padding: 12px 16px;
    font-size: 14px;
  }
}
</style>