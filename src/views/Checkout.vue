<template>
  <div class="checkout-page">
    <div class="container-custom">
      <!-- Page Header -->
      <div class="page-header">
        <h1 class="page-title">Checkout</h1>
        <p class="page-subtitle">Complete your order</p>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="loading-state">
        <div class="spinner"></div>
        <p>Loading your cart...</p>
      </div>

      <!-- Checkout Content -->
      <div v-else-if="cartItems.length > 0" class="checkout-grid">
        <!-- Left: Billing & Shipping -->
        <div class="checkout-form">
          <!-- Shipping Information -->
          <div class="form-section">
            <h3>
              <i class="bi bi-geo-alt-fill" style="color: #667eea;"></i>
              Shipping Information
            </h3>
            <div class="form-row">
              <div class="form-group">
                <label>Full Name</label>
                <input type="text" v-model="shippingInfo.fullName" placeholder="John Doe" required />
              </div>
              <div class="form-group">
                <label>Email</label>
                <input type="email" v-model="shippingInfo.email" placeholder="john@example.com" required />
              </div>
            </div>
            <div class="form-group">
              <label>Phone Number</label>
              <input type="tel" v-model="shippingInfo.phone" placeholder="017XXXXXXXX" required />
            </div>
            <div class="form-group">
              <label>Address</label>
              <input type="text" v-model="shippingInfo.address" placeholder="House #, Road #, Area" required />
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>City</label>
                <select v-model="shippingInfo.city" required>
                  <option value="">Select City</option>
                  <option value="Dhaka">Dhaka</option>
                  <option value="Chattogram">Chattogram</option>
                  <option value="Khulna">Khulna</option>
                  <option value="Rajshahi">Rajshahi</option>
                  <option value="Sylhet">Sylhet</option>
                  <option value="Barishal">Barishal</option>
                  <option value="Rangpur">Rangpur</option>
                  <option value="Mymensingh">Mymensingh</option>
                  <option value="Other">Other</option>
                </select>
              </div>
              <div class="form-group">
                <label>District</label>
                <select v-model="shippingInfo.district" required>
                  <option value="">Select District</option>
                  <option value="Dhaka">Dhaka</option>
                  <option value="Gazipur">Gazipur</option>
                  <option value="Narayanganj">Narayanganj</option>
                  <option value="Chattogram">Chattogram</option>
                  <option value="Cox's Bazar">Cox's Bazar</option>
                  <option value="Khulna">Khulna</option>
                  <option value="Rajshahi">Rajshahi</option>
                  <option value="Sylhet">Sylhet</option>
                  <option value="Barishal">Barishal</option>
                  <option value="Rangpur">Rangpur</option>
                  <option value="Mymensingh">Mymensingh</option>
                  <option value="Other">Other</option>
                </select>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Postal Code</label>
                <input type="text" v-model="shippingInfo.postalCode" placeholder="1207" />
              </div>
              <div class="form-group">
                <label>Delivery Area</label>
                <select v-model="shippingInfo.deliveryArea" required>
                  <option value="">Select Delivery Area</option>
                  <option value="Inside Dhaka">Inside Dhaka City</option>
                  <option value="Outside Dhaka">Outside Dhaka</option>
                  <option value="Other City">Other City</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Payment Method -->
          <div class="form-section payment-section">
            <h3>
              <i class="bi bi-credit-card-fill" style="color: #667eea;"></i>
              Payment Method
            </h3>
            
            <div class="payment-options">
              <!-- Mobile Banking -->
              <div class="payment-group">
                <label class="payment-group-label">
                  <i class="bi bi-phone"></i> Mobile Banking
                </label>
                <div class="payment-grid">
                  <div 
                    class="payment-option" 
                    :class="{ active: paymentMethod === 'bkash' }"
                    @click="paymentMethod = 'bkash'"
                  >
                    <input type="radio" id="bkash" value="bkash" v-model="paymentMethod" />
                    <label for="bkash">
                      <span class="payment-icon">📱</span>
                      <span>bKash</span>
                    </label>
                  </div>
                  <div 
                    class="payment-option" 
                    :class="{ active: paymentMethod === 'nagad' }"
                    @click="paymentMethod = 'nagad'"
                  >
                    <input type="radio" id="nagad" value="nagad" v-model="paymentMethod" />
                    <label for="nagad">
                      <span class="payment-icon">📱</span>
                      <span>Nagad</span>
                    </label>
                  </div>
                  <div 
                    class="payment-option" 
                    :class="{ active: paymentMethod === 'rocket' }"
                    @click="paymentMethod = 'rocket'"
                  >
                    <input type="radio" id="rocket" value="rocket" v-model="paymentMethod" />
                    <label for="rocket">
                      <span class="payment-icon">📱</span>
                      <span>Rocket</span>
                    </label>
                  </div>
                </div>
              </div>

              <!-- Cash on Delivery -->
              <div class="payment-group">
                <label class="payment-group-label">
                  <i class="bi bi-cash"></i> Cash on Delivery
                </label>
                <div class="payment-grid">
                  <div 
                    class="payment-option cod-option" 
                    :class="{ active: paymentMethod === 'cod' }"
                    @click="paymentMethod = 'cod'"
                  >
                    <input type="radio" id="cod" value="cod" v-model="paymentMethod" />
                    <label for="cod">
                      <span class="payment-icon-large">💵</span>
                      <span>Cash on Delivery</span>
                    </label>
                  </div>
                </div>
              </div>

              <!-- Cards -->
              <div class="payment-group">
                <label class="payment-group-label">
                  <i class="bi bi-credit-card"></i> Credit / Debit Cards
                </label>
                <div class="payment-grid">
                  <div 
                    class="payment-option card-option" 
                    :class="{ active: paymentMethod === 'card' }"
                    @click="paymentMethod = 'card'"
                  >
                    <input type="radio" id="card" value="card" v-model="paymentMethod" />
                    <label for="card">
                      <span class="card-icons">💳</span>
                      <span>Visa / Mastercard / Amex</span>
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Error Message -->
          <div v-if="errorMessage" class="error-message">
            <i class="bi bi-exclamation-circle"></i>
            {{ errorMessage }}
          </div>

          <!-- Place Order Button -->
          <button class="btn-primary-modern place-order-btn" @click="placeOrder" :disabled="isSubmitting">
            <span v-if="isSubmitting">
              <i class="bi bi-arrow-repeat spin"></i> Placing Order...
            </span>
            <span v-else>
              <i class="bi bi-check-circle me-2"></i>Place Order
            </span>
          </button>
        </div>

        <!-- Right: Order Summary -->
        <div class="order-summary">
          <h3>Order Summary</h3>
          
          <!-- Cart Items -->
          <div class="summary-items">
            <div v-for="item in cartItems" :key="item.id" class="summary-item">
              <div class="item-info">
                <div class="item-image">
                  <img 
                    v-if="item.image" 
                    :src="item.image" 
                    :alt="item.name"
                    class="summary-item-img"
                    loading="lazy"
                    @error="handleImageError"
                  />
                  <span v-else class="item-emoji">{{ item.emoji || '📦' }}</span>
                </div>
                <div class="item-details">
                  <span class="item-name">{{ item.name }}</span>
                  <span class="item-quantity">Qty: {{ item.quantity }}</span>
                </div>
              </div>
              <span class="item-price">${{ (item.price * item.quantity).toFixed(2) }}</span>
            </div>
          </div>

          <!-- Totals -->
          <div class="summary-totals">
            <div class="total-row">
              <span>Subtotal</span>
              <span>${{ subtotal.toFixed(2) }}</span>
            </div>
            <div class="total-row">
              <span>Shipping</span>
              <span>{{ shippingCost > 0 ? '$' + shippingCost.toFixed(2) : 'Free' }}</span>
            </div>
            <div class="total-row">
              <span>Tax (8%)</span>
              <span>${{ tax.toFixed(2) }}</span>
            </div>
            <div class="total-row grand-total">
              <span>Total</span>
              <span>${{ total.toFixed(2) }}</span>
            </div>
          </div>

          <!-- Delivery Info -->
          <div class="delivery-info">
            <div class="info-item">
              <i class="bi bi-truck"></i>
              <span>Estimated Delivery: 2-5 business days</span>
            </div>
            <div class="info-item">
              <i class="bi bi-arrow-return-left"></i>
              <span>Easy returns within 7 days</span>
            </div>
          </div>

          <p class="secure-checkout">
            <i class="bi bi-lock-fill"></i> Secure checkout
          </p>
        </div>
      </div>

      <!-- Empty Cart -->
      <div v-else class="empty-cart">
        <div class="empty-state">
          <i class="bi bi-cart3" style="font-size: 5rem; color: var(--text-muted);"></i>
          <h3>Your Cart is Empty</h3>
          <p>Add some items to your cart before checking out.</p>
          <button class="btn-primary-modern" @click="continueShopping">
            <i class="bi bi-arrow-left me-2"></i>Continue Shopping
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { orderApi, cartApi } from '@/services/api'

const router = useRouter()

// ===== STATE =====
const cartItems = ref([])
const isLoading = ref(true)
const isSubmitting = ref(false)
const errorMessage = ref('')

// ===== SHIPPING INFO =====
const shippingInfo = ref({
  fullName: '',
  email: '',
  phone: '',
  address: '',
  city: '',
  district: '',
  postalCode: '',
  deliveryArea: ''
})

// ===== PAYMENT =====
const paymentMethod = ref('cod')

// ===== COMPUTED =====
const subtotal = computed(() => {
  return cartItems.value.reduce((sum, item) => sum + (item.price * item.quantity), 0)
})

const shippingCost = computed(() => {
  if (shippingInfo.value.deliveryArea === 'Inside Dhaka') {
    return 60
  } else if (shippingInfo.value.deliveryArea === 'Outside Dhaka') {
    return 120
  } else if (shippingInfo.value.deliveryArea === 'Other City') {
    return 100
  }
  return subtotal.value > 100 ? 0 : 5.99
})

const tax = computed(() => {
  return subtotal.value * 0.08
})

const total = computed(() => {
  return subtotal.value + shippingCost.value + tax.value
})

// ===== METHODS =====
const loadCart = async () => {
  isLoading.value = true
  try {
    const token = localStorage.getItem('token')
    if (token) {
      const response = await cartApi.getCart()
      if (response.data.success) {
        const cartData = response.data.data
        cartItems.value = cartData.items || []
      }
    } else {
      // Fallback to localStorage
      const savedCart = localStorage.getItem('shopsphere_cart')
      if (savedCart) {
        cartItems.value = JSON.parse(savedCart)
      }
    }
  } catch (error) {
    console.error('Error loading cart:', error)
    // Fallback to localStorage
    const savedCart = localStorage.getItem('shopsphere_cart')
    if (savedCart) {
      cartItems.value = JSON.parse(savedCart)
    }
  } finally {
    isLoading.value = false
  }
}

const placeOrder = async () => {
  // Validate shipping info
  const required = ['fullName', 'email', 'phone', 'address', 'city', 'district', 'deliveryArea']
  const missing = required.filter(field => !shippingInfo.value[field])
  
  if (missing.length > 0) {
    errorMessage.value = 'Please fill in all shipping fields'
    return
  }

  if (!paymentMethod.value) {
    errorMessage.value = 'Please select a payment method'
    return
  }

  isSubmitting.value = true
  errorMessage.value = ''

  try {
    const token = localStorage.getItem('token')
    
    if (token) {
      // Logged in - use API
      const response = await orderApi.createOrder({
        shipping_address: {
          full_name: shippingInfo.value.fullName,
          email: shippingInfo.value.email,
          phone: shippingInfo.value.phone,
          address: shippingInfo.value.address,
          city: shippingInfo.value.city,
          district: shippingInfo.value.district,
          postal_code: shippingInfo.value.postalCode,
          delivery_area: shippingInfo.value.deliveryArea,
        },
        payment_method: paymentMethod.value,
        notes: ''
      })

      if (response.data.success) {
        // Clear cart
        localStorage.removeItem('shopsphere_cart')
        window.dispatchEvent(new Event('storage'))
        window.dispatchEvent(new CustomEvent('cart-updated'))
        
        // Redirect to order confirmation
        router.push(`/order-confirmation/${response.data.data.order.id}`)
      }
    } else {
      // Not logged in - use localStorage (fallback)
      const order = {
        id: Date.now(),
        items: cartItems.value,
        shipping: shippingInfo.value,
        paymentMethod: paymentMethod.value,
        subtotal: subtotal.value,
        shippingCost: shippingCost.value,
        tax: tax.value,
        total: total.value,
        status: 'pending',
        date: new Date().toLocaleDateString('en-BD', { day: '2-digit', month: '2-digit', year: 'numeric' }),
        orderNumber: 'SPH-' + Date.now().toString().slice(-6) + '-' + Math.random().toString(36).substr(2, 4).toUpperCase()
      }

      const orders = JSON.parse(localStorage.getItem('shopsphere_orders') || '[]')
      orders.push(order)
      localStorage.setItem('shopsphere_orders', JSON.stringify(orders))

      localStorage.removeItem('shopsphere_cart')
      window.dispatchEvent(new Event('storage'))
      window.dispatchEvent(new CustomEvent('cart-updated'))

      router.push(`/order-confirmation/${order.id}`)
    }
  } catch (error) {
    console.error('Error placing order:', error)
    errorMessage.value = error.response?.data?.message || 'Failed to place order. Please try again.'
  } finally {
    isSubmitting.value = false
  }
}

const handleImageError = (e) => {
  e.target.style.display = 'none'
  const parent = e.target.parentElement
  const fallback = document.createElement('span')
  fallback.className = 'item-emoji'
  fallback.textContent = '📦'
  parent.appendChild(fallback)
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
/* All existing styles remain the same */
.checkout-page {
  padding: 40px 0 80px;
  background: var(--bg-primary);
  min-height: 100vh;
}

.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 400px;
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

.checkout-grid {
  display: grid;
  grid-template-columns: 1.5fr 1fr;
  gap: 40px;
}

.form-section {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: var(--radius);
  padding: 24px;
  margin-bottom: 24px;
}

.form-section h3 {
  font-size: 1.2rem;
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

.form-group input,
.form-group select {
  width: 100%;
  padding: 12px 16px;
  border: 1px solid var(--border-color);
  border-radius: var(--radius-sm);
  background: var(--bg-secondary);
  color: var(--text-primary);
  font-size: 1rem;
  transition: var(--transition);
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.payment-section {
  border: 2px solid var(--border-color);
}

.payment-options {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.payment-group {
  border: 1px solid var(--border-color);
  border-radius: var(--radius-sm);
  padding: 16px;
  background: var(--bg-secondary);
}

.payment-group-label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.8rem;
  font-weight: 600;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 12px;
}

.payment-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 10px;
}

.payment-option {
  display: flex;
  align-items: center;
  padding: 12px 14px;
  border-radius: var(--radius-sm);
  cursor: pointer;
  transition: var(--transition);
  border: 2px solid transparent;
  background: var(--bg-card);
}

.payment-option:hover {
  border-color: var(--border-color);
  transform: translateY(-2px);
}

.payment-option.active {
  border-color: #667eea;
  background: rgba(102, 126, 234, 0.05);
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.payment-option input[type="radio"] {
  display: none;
}

.payment-option label {
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 10px;
  font-weight: 500;
  color: var(--text-primary);
  font-size: 0.9rem;
  width: 100%;
}

.payment-icon {
  font-size: 1.3rem;
}

.payment-icon-large {
  font-size: 2rem;
}

.card-option {
  grid-column: 1 / -1;
}

.cod-option {
  grid-column: 1 / -1;
}

.error-message {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 16px;
  background: #fee2e2;
  border: 1px solid #fecaca;
  border-radius: var(--radius-sm);
  color: #dc2626;
  font-size: 0.9rem;
  margin-bottom: 16px;
}

.error-message i {
  font-size: 1.2rem;
  flex-shrink: 0;
}

.place-order-btn {
  width: 100%;
  text-align: center;
  padding: 16px;
  font-size: 1.1rem;
}

.place-order-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.order-summary {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: var(--radius);
  padding: 24px;
  height: fit-content;
  position: sticky;
  top: 100px;
}

.order-summary h3 {
  font-size: 1.2rem;
  font-weight: 600;
  color: var(--text-primary);
  margin-bottom: 20px;
  padding-bottom: 12px;
  border-bottom: 1px solid var(--border-color);
}

.summary-items {
  max-height: 300px;
  overflow-y: auto;
  margin-bottom: 16px;
}

.summary-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
  border-bottom: 1px solid var(--border-color);
}

.summary-item:last-child {
  border-bottom: none;
}

.item-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.item-image {
  width: 50px;
  height: 50px;
  border-radius: var(--radius-sm);
  overflow: hidden;
  background: var(--bg-secondary);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.summary-item-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.item-emoji {
  font-size: 1.5rem;
}

.item-details {
  display: flex;
  flex-direction: column;
}

.item-name {
  font-size: 0.95rem;
  font-weight: 500;
  color: var(--text-primary);
}

.item-quantity {
  font-size: 0.8rem;
  color: var(--text-muted);
}

.item-price {
  font-weight: 600;
  color: var(--text-primary);
}

.summary-totals {
  border-top: 1px solid var(--border-color);
  padding-top: 16px;
}

.total-row {
  display: flex;
  justify-content: space-between;
  padding: 6px 0;
  color: var(--text-secondary);
}

.total-row.grand-total {
  font-size: 1.2rem;
  font-weight: 700;
  color: var(--text-primary);
  padding-top: 12px;
  border-top: 2px solid var(--border-color);
  margin-top: 8px;
}

.delivery-info {
  margin: 16px 0;
  padding: 12px;
  background: var(--bg-secondary);
  border-radius: var(--radius-sm);
}

.info-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 4px 0;
  color: var(--text-secondary);
  font-size: 0.9rem;
}

.info-item i {
  color: #667eea;
  font-size: 1.1rem;
}

.secure-checkout {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  margin-top: 16px;
  color: var(--text-muted);
  font-size: 0.9rem;
}

.secure-checkout i {
  color: #22c55e;
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

.spin {
  animation: spin 1s linear infinite;
}

@media (max-width: 1024px) {
  .checkout-grid {
    grid-template-columns: 1fr;
  }

  .order-summary {
    position: static;
  }
}

@media (max-width: 768px) {
  .checkout-page {
    padding: 20px 0 60px;
  }

  .page-title {
    font-size: 2rem;
  }

  .form-row {
    grid-template-columns: 1fr;
  }

  .form-section {
    padding: 16px;
  }

  .payment-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 480px) {
  .payment-group {
    padding: 8px;
  }

  .payment-option {
    padding: 10px;
  }

  .summary-item {
    flex-wrap: wrap;
    gap: 8px;
  }
}
</style>