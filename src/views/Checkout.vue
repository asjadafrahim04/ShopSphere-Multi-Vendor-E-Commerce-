<template>
  <div class="checkout-page">
    <div class="container-custom">
      <!-- Page Header -->
      <div class="page-header">
        <h1 class="page-title">Checkout</h1>
        <p class="page-subtitle">Complete your order</p>
      </div>

      <!-- Checkout Content -->
      <div v-if="cartItems.length > 0" class="checkout-grid">
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
            
            <!-- Payment Options -->
            <div class="payment-options">
              <!-- Mobile Banking -->
              <div class="payment-group">
                <label class="payment-group-label">
                  <i class="bi bi-phone"></i> Mobile Banking
                </label>
                <div class="payment-grid">
                  <!-- bKash -->
                  <div 
                    class="payment-option" 
                    :class="{ active: paymentMethod === 'bkash' }"
                    @click="paymentMethod = 'bkash'"
                  >
                    <input type="radio" id="bkash" value="bkash" v-model="paymentMethod" />
                    <label for="bkash">
                      <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ccircle cx='50' cy='50' r='45' fill='%23E2136E'/%3E%3Ctext x='50' y='58' font-family='Arial' font-size='28' font-weight='bold' fill='white' text-anchor='middle'%3EbKash%3C/text%3E%3C/svg%3E" alt="bKash" class="payment-logo" />
                      <span>bKash</span>
                    </label>
                  </div>

                  <!-- Nagad -->
                  <div 
                    class="payment-option" 
                    :class="{ active: paymentMethod === 'nagad' }"
                    @click="paymentMethod = 'nagad'"
                  >
                    <input type="radio" id="nagad" value="nagad" v-model="paymentMethod" />
                    <label for="nagad">
                      <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ccircle cx='50' cy='50' r='45' fill='%23FF6B00'/%3E%3Ctext x='50' y='58' font-family='Arial' font-size='22' font-weight='bold' fill='white' text-anchor='middle'%3ENagad%3C/text%3E%3C/svg%3E" alt="Nagad" class="payment-logo" />
                      <span>Nagad</span>
                    </label>
                  </div>

                  <!-- Rocket -->
                  <div 
                    class="payment-option" 
                    :class="{ active: paymentMethod === 'rocket' }"
                    @click="paymentMethod = 'rocket'"
                  >
                    <input type="radio" id="rocket" value="rocket" v-model="paymentMethod" />
                    <label for="rocket">
                      <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ccircle cx='50' cy='50' r='45' fill='%23F26522'/%3E%3Ctext x='50' y='55' font-family='Arial' font-size='18' font-weight='bold' fill='white' text-anchor='middle'%3ERocket%3C/text%3E%3C/svg%3E" alt="Rocket" class="payment-logo" />
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
                      <div class="payment-label">
                        <span class="payment-name">Cash on Delivery</span>
                        <span class="payment-sub">Pay when you receive</span>
                      </div>
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
                      <div class="card-logos">
                        <span class="card-brand visa">Visa</span>
                        <span class="card-brand mastercard">Mastercard</span>
                        <span class="card-brand amex">Amex</span>
                      </div>
                      <div class="payment-label">
                        <span class="payment-name">Credit / Debit Card</span>
                        <span class="payment-sub">Visa, Mastercard, Amex</span>
                      </div>
                    </label>
                  </div>
                </div>
              </div>
            </div>

            <!-- Mobile Banking Details -->
            <div v-if="['bkash', 'nagad', 'rocket'].includes(paymentMethod)" class="payment-details">
              <div class="payment-info-box">
                <div class="payment-header">
                  <img 
                    :src="`data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ccircle cx='50' cy='50' r='45' fill='${paymentMethod === 'bkash' ? '%23E2136E' : paymentMethod === 'nagad' ? '%23FF6B00' : '%23F26522'}'/%3E%3Ctext x='50' y='58' font-family='Arial' font-size='${paymentMethod === 'bkash' ? '28' : '22'}' font-weight='bold' fill='white' text-anchor='middle'%3E${paymentMethod === 'bkash' ? 'bKash' : paymentMethod === 'nagad' ? 'Nagad' : 'Rocket'}%3C/text%3E%3C/svg%3E`" 
                    :alt="paymentMethod" 
                    class="detail-logo" 
                  />
                  <h4>Pay with {{ paymentMethod === 'bkash' ? 'bKash' : paymentMethod === 'nagad' ? 'Nagad' : 'Rocket' }}</h4>
                </div>
                <p>Please send the amount to the following number:</p>
                <div class="merchant-number">
                  <span class="label">Merchant {{ paymentMethod.toUpperCase() }} Number:</span>
                  <span class="number">{{ merchantNumbers[paymentMethod] }}</span>
                  <button class="copy-btn" @click="copyNumber(paymentMethod)">
                    <i class="bi bi-copy"></i>
                  </button>
                </div>
                <div class="form-group">
                  <label>Your {{ paymentMethod.toUpperCase() }} Number</label>
                  <input type="text" v-model="mobileBanking.number" :placeholder="`Enter your ${paymentMethod} number`" />
                </div>
                <div class="form-group">
                  <label>Transaction ID</label>
                  <input type="text" v-model="mobileBanking.transactionId" placeholder="Enter transaction ID" />
                </div>
                <div class="form-group">
                  <label>Amount to Send</label>
                  <input type="text" :value="'$' + total.toFixed(2)" disabled class="amount-display" />
                </div>
              </div>
            </div>

            <!-- Card Details -->
            <div v-if="paymentMethod === 'card'" class="payment-details">
              <div class="payment-info-box card-box">
                <div class="payment-header">
                  <span class="payment-icon-large">💳</span>
                  <h4>Card Details</h4>
                </div>
                <div class="form-group">
                  <label>Card Number</label>
                  <input type="text" v-model="cardDetails.number" placeholder="1234 5678 9012 3456" />
                </div>
                <div class="form-row">
                  <div class="form-group">
                    <label>Expiry Date</label>
                    <input type="text" v-model="cardDetails.expiry" placeholder="MM/YY" />
                  </div>
                  <div class="form-group">
                    <label>CVV</label>
                    <input type="password" v-model="cardDetails.cvv" placeholder="123" />
                  </div>
                </div>
                <div class="form-group">
                  <label>Name on Card</label>
                  <input type="text" v-model="cardDetails.name" placeholder="John Doe" />
                </div>
                <div class="card-brands-accepted">
                  <span class="accepted-label">We accept:</span>
                  <span class="card-brand visa">Visa</span>
                  <span class="card-brand mastercard">Mastercard</span>
                  <span class="card-brand amex">Amex</span>
                </div>
              </div>
            </div>

            <!-- COD Details -->
            <div v-if="paymentMethod === 'cod'" class="payment-details">
              <div class="payment-info-box cod-box">
                <div class="cod-icon">💵</div>
                <h4>Cash on Delivery</h4>
                <p>Pay when you receive your package at your doorstep.</p>
                <div class="cod-note">
                  <i class="bi bi-info-circle"></i>
                  <span>No additional payment required now. Pay in cash when the delivery arrives.</span>
                </div>
                <div class="cod-benefits">
                  <div class="benefit">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>No advance payment</span>
                  </div>
                  <div class="benefit">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>Inspect before payment</span>
                  </div>
                  <div class="benefit">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>100% secure</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Place Order Button -->
          <button class="btn-primary-modern place-order-btn" @click="placeOrder">
            <i class="bi bi-check-circle me-2"></i>Place Order
          </button>
        </div>

        <!-- Right: Order Summary -->
        <div class="order-summary">
          <h3>Order Summary</h3>
          
          <!-- Cart Items with Images -->
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
              <span>{{ shipping > 0 ? '$' + shipping.toFixed(2) : 'Free' }}</span>
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

const router = useRouter()

// ===== STATE =====
const cartItems = ref([])

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
const mobileBanking = ref({
  number: '',
  transactionId: ''
})
const cardDetails = ref({
  number: '',
  expiry: '',
  cvv: '',
  name: ''
})

// ===== MERCHANT NUMBERS =====
const merchantNumbers = {
  bkash: '017XXXXXXXX',
  nagad: '016XXXXXXXX',
  rocket: '018XXXXXXXX'
}

// ===== COMPUTED =====
const subtotal = computed(() => {
  return cartItems.value.reduce((sum, item) => sum + (item.price * item.quantity), 0)
})

const shipping = computed(() => {
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
  return subtotal.value + shipping.value + tax.value
})

// ===== METHODS =====
const loadCart = () => {
  const savedCart = localStorage.getItem('shopsphere_cart')
  if (savedCart) {
    cartItems.value = JSON.parse(savedCart)
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

const copyNumber = (method) => {
  const number = merchantNumbers[method]
  navigator.clipboard.writeText(number)
  alert(`📋 Copied ${method.toUpperCase()} number: ${number}`)
}

const placeOrder = () => {
  const required = ['fullName', 'email', 'phone', 'address', 'city', 'district', 'deliveryArea']
  const missing = required.filter(field => !shippingInfo.value[field])
  
  if (missing.length > 0) {
    alert('Please fill in all shipping fields')
    return
  }

  if (!paymentMethod.value) {
    alert('Please select a payment method')
    return
  }

  if (['bkash', 'nagad', 'rocket'].includes(paymentMethod.value)) {
    if (!mobileBanking.value.number) {
      alert(`Please enter your ${paymentMethod.value} number`)
      return
    }
    if (!mobileBanking.value.transactionId) {
      alert('Please enter the transaction ID')
      return
    }
  }

  if (paymentMethod.value === 'card') {
    if (!cardDetails.value.number || !cardDetails.value.expiry || !cardDetails.value.cvv || !cardDetails.value.name) {
      alert('Please fill in all card details')
      return
    }
  }

  const paymentLabels = {
    bkash: 'bKash',
    nagad: 'Nagad',
    rocket: 'Rocket (DBBL)',
    cod: 'Cash on Delivery',
    card: 'Credit/Debit Card'
  }

  const order = {
    id: Date.now(),
    items: cartItems.value,
    shipping: shippingInfo.value,
    paymentMethod: paymentMethod.value,
    paymentDetails: paymentMethod.value === 'cod' ? null : {
      mobileBanking: paymentMethod.value !== 'card' ? mobileBanking.value : null,
      card: paymentMethod.value === 'card' ? cardDetails.value : null
    },
    subtotal: subtotal.value,
    shippingCost: shipping.value,
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

  const paymentLabel = paymentLabels[paymentMethod.value] || paymentMethod.value
  alert(`🎉 Order placed successfully!\n\nOrder #${order.orderNumber}\nPayment Method: ${paymentLabel}\nTotal: $${order.total.toFixed(2)}\n\nWe'll send you a confirmation email shortly.`)
  
  router.push(`/order-confirmation/${order.id}`)
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
.checkout-page {
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

/* ===== CHECKOUT GRID ===== */
.checkout-grid {
  display: grid;
  grid-template-columns: 1.5fr 1fr;
  gap: 40px;
}

/* ===== FORM SECTION ===== */
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

.form-group input:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.amount-display {
  font-weight: 700;
  color: #667eea !important;
}

/* ===== PAYMENT OPTIONS ===== */
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

.payment-logo {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  object-fit: contain;
}

.payment-icon-large {
  font-size: 2rem;
}

.payment-label {
  display: flex;
  flex-direction: column;
}

.payment-name {
  font-weight: 600;
  color: var(--text-primary);
}

.payment-sub {
  font-size: 0.75rem;
  color: var(--text-muted);
  font-weight: 400;
}

/* ===== COD OPTION ===== */
.cod-option {
  grid-column: 1 / -1;
}

.cod-option label {
  justify-content: center;
}

/* ===== CARD OPTION ===== */
.card-option {
  grid-column: 1 / -1;
}

.card-option label {
  flex-direction: column;
  align-items: flex-start;
  gap: 6px;
}

.card-logos {
  display: flex;
  gap: 8px;
}

.card-brand {
  padding: 2px 10px;
  border-radius: 4px;
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
}

.card-brand.visa {
  background: #1a1f71;
  color: white;
}

.card-brand.mastercard {
  background: #eb001b;
  color: white;
}

.card-brand.amex {
  background: #006fcf;
  color: white;
}

/* ===== PAYMENT DETAILS ===== */
.payment-details {
  margin-top: 16px;
}

.payment-info-box {
  background: var(--bg-secondary);
  border-radius: var(--radius-sm);
  padding: 20px;
  border: 1px solid var(--border-color);
}

.payment-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 16px;
}

.detail-logo {
  width: 40px;
  height: 40px;
  border-radius: 8px;
}

.payment-info-box h4 {
  font-size: 1.05rem;
  font-weight: 600;
  color: var(--text-primary);
  margin: 0;
}

.payment-info-box p {
  color: var(--text-secondary);
  font-size: 0.95rem;
  margin-bottom: 12px;
}

.merchant-number {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-sm);
  padding: 12px 16px;
  margin-bottom: 16px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 8px;
}

.merchant-number .label {
  color: var(--text-secondary);
  font-size: 0.9rem;
}

.merchant-number .number {
  font-weight: 700;
  color: #667eea;
  font-size: 1.1rem;
  font-family: monospace;
  letter-spacing: 1px;
}

.copy-btn {
  background: none;
  border: none;
  color: #667eea;
  cursor: pointer;
  font-size: 1.1rem;
  padding: 4px 8px;
  border-radius: 4px;
  transition: var(--transition);
}

.copy-btn:hover {
  background: rgba(102, 126, 234, 0.1);
}

/* ===== CARD DETAILS ===== */
.card-box {
  background: linear-gradient(135deg, #1a1a2e, #16213e);
  color: white;
}

.card-box .form-group label {
  color: rgba(255, 255, 255, 0.7);
}

.card-box input {
  background: rgba(255, 255, 255, 0.1);
  border-color: rgba(255, 255, 255, 0.2);
  color: white;
}

.card-box input::placeholder {
  color: rgba(255, 255, 255, 0.4);
}

.card-box input:focus {
  border-color: #667eea;
  background: rgba(255, 255, 255, 0.15);
}

.card-brands-accepted {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 12px;
  padding-top: 12px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.accepted-label {
  color: rgba(255, 255, 255, 0.6);
  font-size: 0.8rem;
}

/* ===== COD DETAILS ===== */
.cod-box {
  text-align: center;
  background: rgba(34, 197, 94, 0.05);
  border-color: rgba(34, 197, 94, 0.2);
}

.cod-icon {
  font-size: 3rem;
  margin-bottom: 8px;
}

.cod-note {
  display: flex;
  align-items: center;
  gap: 8px;
  background: rgba(34, 197, 94, 0.1);
  border-radius: var(--radius-sm);
  padding: 12px 16px;
  color: var(--text-secondary);
  font-size: 0.95rem;
  margin: 12px 0;
}

.cod-note i {
  color: #22c55e;
  font-size: 1.2rem;
}

.cod-benefits {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 8px;
  margin-top: 12px;
}

.benefit {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 12px;
  background: var(--bg-card);
  border-radius: var(--radius-sm);
  font-size: 0.85rem;
  color: var(--text-secondary);
}

.benefit i {
  color: #22c55e;
}

/* ===== PLACE ORDER BUTTON ===== */
.place-order-btn {
  width: 100%;
  text-align: center;
  padding: 16px;
  font-size: 1.1rem;
}

/* ===== ORDER SUMMARY ===== */
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

/* ===== SUMMARY ITEMS WITH IMAGES ===== */
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

/* ===== SUMMARY TOTALS ===== */
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

/* ===== DELIVERY INFO ===== */
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

/* ===== SECURE CHECKOUT ===== */
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

/* ===== EMPTY CART ===== */
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

/* ===== RESPONSIVE ===== */
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

  .cod-benefits {
    grid-template-columns: 1fr;
  }

  .card-logos {
    flex-wrap: wrap;
  }

  .merchant-number {
    flex-direction: column;
    text-align: center;
  }

  .item-image {
    width: 40px;
    height: 40px;
  }
}

@media (max-width: 480px) {
  .payment-group {
    padding: 8px;
  }

  .payment-option {
    padding: 10px;
  }

  .payment-info-box {
    padding: 14px;
  }

  .cod-note {
    flex-direction: column;
    text-align: center;
  }

  .summary-item {
    flex-wrap: wrap;
    gap: 8px;
  }
}
</style>