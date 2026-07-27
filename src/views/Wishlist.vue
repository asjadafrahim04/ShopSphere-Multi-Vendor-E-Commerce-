<template>
  <div class="wishlist-page">
    <div class="container-custom">
      <!-- Page Header -->
      <div class="page-header">
        <h1 class="page-title">My Wishlist</h1>
        <p class="page-subtitle">{{ wishlistItems.length }} items saved</p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p>Loading your wishlist...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="error-state">
        <i class="bi bi-exclamation-circle" style="font-size: 3rem; color: #ef4444;"></i>
        <p>{{ error }}</p>
        <button class="btn-primary-modern" @click="loadWishlist">Try Again</button>
      </div>

      <!-- Wishlist Content -->
      <div v-else-if="wishlistItems.length > 0" class="wishlist-content">
        <div class="wishlist-grid">
          <div v-for="item in wishlistItems" :key="item.id" class="wishlist-item">
            <!-- Product Image -->
            <div class="item-image" :style="{ background: item.imageBg || '#e8ecf1' }">
              <img 
                v-if="item.image" 
                :src="item.image" 
                :alt="item.name"
                class="wishlist-item-img"
                loading="lazy"
                @error="handleImageError"
              />
              <span v-else class="item-emoji">{{ item.emoji || '📦' }}</span>
            </div>
            
            <!-- Product Details -->
            <div class="item-details">
              <div class="item-vendor">{{ item.vendor || 'ShopSphere' }}</div>
              <h4 class="item-name">{{ item.name }}</h4>
              
              <!-- Rating -->
              <div class="item-rating">
                <span class="stars">
                  <i v-for="n in 5" :key="n" :class="n <= Math.floor(item.rating) ? 'bi bi-star-fill' : 'bi bi-star'"></i>
                </span>
                <span class="rating-count">({{ item.reviews || 0 }})</span>
              </div>

              <!-- Price -->
              <div class="item-price">
                <span class="current-price">${{ item.price }}</span>
                <span v-if="item.originalPrice" class="original-price">${{ item.originalPrice }}</span>
              </div>

              <!-- Actions -->
              <div class="item-actions">
                <button class="btn-primary-modern add-to-cart-btn" @click="addToCart(item)">
                  <i class="bi bi-cart-plus me-2"></i>Add to Cart
                </button>
                <button class="remove-btn" @click="removeFromWishlist(item.product_id || item.id)">
                  <i class="bi bi-trash3"></i> Remove
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty Wishlist -->
      <div v-else class="empty-wishlist">
        <div class="empty-state">
          <i class="bi bi-heart" style="font-size: 5rem; color: var(--text-muted);"></i>
          <h3>Your Wishlist is Empty</h3>
          <p>Start saving your favorite products!</p>
          <button class="btn-primary-modern" @click="continueShopping">
            <i class="bi bi-arrow-left me-2"></i>Start Shopping
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { wishlistApi, cartApi } from '../services/api'

const router = useRouter()

// ===== STATE =====
const wishlistItems = ref([])
const loading = ref(true)
const error = ref(null)

// ===== METHODS =====
const loadWishlist = async () => {
  loading.value = true
  error.value = null
  
  try {
    // Check if user is logged in
    const token = localStorage.getItem('token')
    
    if (token) {
      // Logged in - use API
      const response = await wishlistApi.getWishlist()
      if (response.data.success) {
        wishlistItems.value = response.data.data.items.map(item => ({
          id: item.id,
          product_id: item.product_id,
          name: item.name,
          vendor: item.vendor || 'ShopSphere',
          price: item.price,
          originalPrice: item.compare_price || null,
          rating: item.rating || 0,
          reviews: item.reviews_count || 0,
          image: item.image || null,
          emoji: '📦',
          isNew: false,
          discount: item.discount_percentage || null,
          imageBg: '#e8ecf1',
        }))
      }
    } else {
      // Not logged in - use localStorage (fallback)
      const savedWishlist = localStorage.getItem('shopsphere_wishlist')
      if (savedWishlist) {
        wishlistItems.value = JSON.parse(savedWishlist)
      } else {
        wishlistItems.value = []
      }
    }
  } catch (err) {
    console.error('Error loading wishlist:', err)
    error.value = 'Failed to load wishlist. Please try again.'
    
    // Fallback to localStorage
    const savedWishlist = localStorage.getItem('shopsphere_wishlist')
    if (savedWishlist) {
      wishlistItems.value = JSON.parse(savedWishlist)
    }
  } finally {
    loading.value = false
  }
}

const addToCart = async (product) => {
  try {
    const token = localStorage.getItem('token')
    const productId = product.product_id || product.id
    
    if (token) {
      // Logged in - use API
      const response = await cartApi.addToCart(productId, 1)
      if (response.data.success) {
        alert(`🛒 Added "${product.name}" to cart!`)
        window.dispatchEvent(new CustomEvent('cart-updated', { 
          detail: { count: response.data.data.cart_count } 
        }))
      }
    } else {
      // Not logged in - use localStorage
      let cart = JSON.parse(localStorage.getItem('shopsphere_cart') || '[]')
      const existingItem = cart.find(item => item.id === productId)
      
      if (existingItem) {
        existingItem.quantity += 1
      } else {
        cart.push({
          ...product,
          id: productId,
          quantity: 1,
          image: product.image || null
        })
      }
      
      localStorage.setItem('shopsphere_cart', JSON.stringify(cart))
      window.dispatchEvent(new Event('storage'))
      window.dispatchEvent(new CustomEvent('cart-updated'))
      alert(`🛒 Added "${product.name}" to cart!`)
    }
  } catch (error) {
    console.error('Error adding to cart:', error)
    alert('Failed to add to cart. Please try again.')
  }
}

const removeFromWishlist = async (productId) => {
  if (!confirm('Remove this item from wishlist?')) return
  
  try {
    const token = localStorage.getItem('token')
    
    if (token) {
      // Logged in - use API
      const response = await wishlistApi.removeFromWishlist(productId)
      if (response.data.success) {
        wishlistItems.value = wishlistItems.value.filter(item => 
          (item.product_id || item.id) !== productId
        )
        alert('💔 Removed from wishlist!')
        window.dispatchEvent(new CustomEvent('wishlist-updated'))
      }
    } else {
      // Not logged in - use localStorage
      wishlistItems.value = wishlistItems.value.filter(item => 
        (item.product_id || item.id) !== productId
      )
      localStorage.setItem('shopsphere_wishlist', JSON.stringify(wishlistItems.value))
      alert('💔 Removed from wishlist!')
    }
  } catch (error) {
    console.error('Error removing from wishlist:', error)
    alert('Failed to remove from wishlist. Please try again.')
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
  loadWishlist()
  
  // Listen for wishlist updates from other components
  window.addEventListener('wishlist-updated', loadWishlist)
})
</script>

<style scoped>
.wishlist-page {
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

/* ===== LOADING STATE ===== */
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

.loading-state p {
  color: var(--text-secondary);
  font-size: 1.1rem;
}

/* ===== ERROR STATE ===== */
.error-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 300px;
  gap: 16px;
  text-align: center;
}

.error-state p {
  color: var(--text-secondary);
  font-size: 1.05rem;
}

/* ===== WISHLIST GRID ===== */
.wishlist-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 24px;
}

.wishlist-item {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: var(--radius);
  overflow: hidden;
  transition: var(--transition);
  display: flex;
  flex-direction: column;
}

.wishlist-item:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-hover);
}

/* ===== ITEM IMAGE ===== */
.item-image {
  height: 200px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg-secondary);
  border-bottom: 1px solid var(--border-color);
  overflow: hidden;
}

.wishlist-item-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.item-emoji {
  font-size: 4rem;
}

/* ===== ITEM DETAILS ===== */
.item-details {
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 8px;
  flex: 1;
}

.item-vendor {
  font-size: 0.8rem;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.item-name {
  font-size: 1.1rem;
  font-weight: 600;
  color: var(--text-primary);
  margin: 0;
  line-height: 1.3;
}

.item-rating {
  display: flex;
  align-items: center;
  gap: 8px;
}

.item-rating .stars {
  display: flex;
  gap: 2px;
  color: #f59e0b;
  font-size: 0.85rem;
}

.item-rating .stars .bi-star {
  color: var(--border-color);
}

.rating-count {
  font-size: 0.85rem;
  color: var(--text-muted);
}

.item-price {
  display: flex;
  align-items: center;
  gap: 10px;
  margin: 4px 0 8px;
}

.current-price {
  font-size: 1.3rem;
  font-weight: 700;
  color: #667eea;
}

.original-price {
  font-size: 0.9rem;
  color: var(--text-muted);
  text-decoration: line-through;
}

/* ===== ITEM ACTIONS ===== */
.item-actions {
  display: flex;
  gap: 12px;
  margin-top: auto;
  flex-wrap: wrap;
}

.add-to-cart-btn {
  flex: 1;
  text-align: center;
  padding: 10px 16px;
  font-size: 14px;
}

.remove-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 10px 16px;
  background: none;
  border: 2px solid #ef4444;
  border-radius: 50px;
  color: #ef4444;
  font-weight: 600;
  cursor: pointer;
  transition: var(--transition);
  font-size: 14px;
}

.remove-btn:hover {
  background: #ef4444;
  color: white;
}

/* ===== EMPTY WISHLIST ===== */
.empty-wishlist {
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

/* ===== DARK MODE ===== */
@media (prefers-color-scheme: dark) {
  .current-price {
    color: #8b5cf6;
  }
  
  .remove-btn {
    border-color: #ef4444;
    color: #ef4444;
  }
  
  .remove-btn:hover {
    background: #ef4444;
    color: white;
  }
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
  .wishlist-page {
    padding: 20px 0 60px;
  }

  .page-title {
    font-size: 2rem;
  }

  .wishlist-grid {
    grid-template-columns: 1fr 1fr;
    gap: 16px;
  }

  .item-image {
    height: 150px;
  }

  .item-emoji {
    font-size: 3rem;
  }

  .item-name {
    font-size: 1rem;
  }

  .current-price {
    font-size: 1.1rem;
  }

  .item-actions {
    flex-direction: column;
  }

  .add-to-cart-btn {
    width: 100%;
  }

  .remove-btn {
    width: 100%;
    justify-content: center;
  }
}

@media (max-width: 480px) {
  .wishlist-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }

  .item-image {
    height: 180px;
  }

  .item-emoji {
    font-size: 3.5rem;
  }
}
</style>