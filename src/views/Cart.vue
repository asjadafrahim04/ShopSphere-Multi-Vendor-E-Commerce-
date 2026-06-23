<template>
  <div class="cart-page">
    <div class="container-custom">
      <!-- Page Header -->
      <div class="page-header">
        <h1 class="page-title">Shopping Cart</h1>
        <p class="page-subtitle">{{ cartItems.length }} items in your cart</p>
      </div>

      <!-- Cart Content -->
      <div v-if="cartItems.length > 0" class="cart-content">
        <!-- Cart Items -->
        <div class="cart-items">
          <div v-for="item in cartItems" :key="item.id" class="cart-item">
            <div class="item-image" :style="{ background: item.imageBg || '#e8ecf1' }">
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
              <p class="item-vendor">{{ item.vendor }}</p>
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
          <div class="summary-row">
            <span>Subtotal</span>
            <span>${{ subtotal.toFixed(2) }}</span>
          </div>
          <div class="summary-row">
            <span>Shipping</span>
            <span>{{ shipping > 0 ? '$' + shipping.toFixed(2) : 'Free' }}</span>
          </div>
          <div class="summary-row">
            <span>Tax</span>
            <span>${{ tax.toFixed(2) }}</span>
          </div>
          <div class="summary-row total">
            <span>Total</span>
            <span>${{ total.toFixed(2) }}</span>
          </div>
          <button class="btn-primary-modern checkout-btn" @click="goToCheckout">
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
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// ===== CART STATE =====
const cartItems = ref([])

// ===== COMPUTED =====
const subtotal = computed(() => {
  return cartItems.value.reduce((sum, item) => sum + (item.price * item.quantity), 0)
})

const shipping = computed(() => {
  return subtotal.value > 100 ? 0 : 5.99
})

const tax = computed(() => {
  return subtotal.value * 0.08 // 8% tax
})

const total = computed(() => {
  return subtotal.value + shipping.value + tax.value
})

// ===== METHODS =====
const loadCart = () => {
  // Load from localStorage
  const savedCart = localStorage.getItem('shopsphere_cart')
  if (savedCart) {
    cartItems.value = JSON.parse(savedCart)
  } else {
    // Add some demo items if cart is empty
    cartItems.value = [
      {
        id: 1,
        name: 'Wireless Noise-Cancelling Headphones',
        vendor: 'TechShop',
        price: 49.99,
        originalPrice: 79.99,
        image: 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?q=80&w=870&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
        emoji: '🎧',
        imageBg: '#e8ecf1',
        quantity: 1
      },
      {
        id: 3,
        name: 'Smart Coffee Maker Pro',
        vendor: 'HomeGoods',
        price: 129.99,
        originalPrice: 159.99,
        image: 'https://images.unsplash.com/photo-1517668808822-9f02a4bcc53a?q=80&w=870&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
        emoji: '☕',
        imageBg: '#f0e8e0',
        quantity: 2
      }
    ]
    saveCart()
  }
}

const saveCart = () => {
  localStorage.setItem('shopsphere_cart', JSON.stringify(cartItems.value))
}

const updateQuantity = (productId, change) => {
  const item = cartItems.value.find(i => i.id === productId)
  if (item) {
    const newQuantity = item.quantity + change
    if (newQuantity >= 1) {
      item.quantity = newQuantity
      saveCart()
      // Update badge
      window.dispatchEvent(new Event('storage'))
      window.dispatchEvent(new CustomEvent('cart-updated'))
    }
  }
}

const removeItem = (productId) => {
  if (confirm('Remove this item from cart?')) {
    cartItems.value = cartItems.value.filter(i => i.id !== productId)
    saveCart()
    // Update badge
    window.dispatchEvent(new Event('storage'))
    window.dispatchEvent(new CustomEvent('cart-updated'))
  }
}

// ===== HANDLE IMAGE ERROR =====
const handleImageError = (e) => {
  e.target.style.display = 'none'
  // Show fallback emoji
  const parent = e.target.parentElement
  const fallback = document.createElement('span')
  fallback.textContent = '📦'
  fallback.style.fontSize = '2.5rem'
  parent.appendChild(fallback)
}

// ===== NAVIGATION METHODS =====
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

/* ===== CART CONTENT ===== */
.cart-content {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 40px;
}

/* ===== CART ITEMS ===== */
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

/* ===== CART SUMMARY ===== */
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

.summary-row {
  display: flex;
  justify-content: space-between;
  padding: 8px 0;
  color: var(--text-secondary);
}

.summary-row.total {
  font-size: 1.2rem;
  font-weight: 700;
  color: var(--text-primary);
  padding-top: 12px;
  border-top: 2px solid var(--border-color);
  margin-top: 8px;
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
}
</style>