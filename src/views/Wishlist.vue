<template>
  <div class="wishlist-page">
    <div class="container-custom">
      <!-- Page Header -->
      <div class="page-header">
        <h1 class="page-title">My Wishlist</h1>
        <p class="page-subtitle">{{ wishlistItems.length }} items saved</p>
      </div>

      <!-- Wishlist Content -->
      <div v-if="wishlistItems.length > 0" class="wishlist-content">
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
              <div class="item-vendor">{{ item.vendor }}</div>
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
                <button class="remove-btn" @click="removeFromWishlist(item.id)">
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

const router = useRouter()

// ===== STATE =====
const wishlistItems = ref([])

// ===== METHODS =====
const loadWishlist = () => {
  const savedWishlist = localStorage.getItem('shopsphere_wishlist')
  if (savedWishlist) {
    wishlistItems.value = JSON.parse(savedWishlist)
  } else {
    // Add some demo items if wishlist is empty
    wishlistItems.value = [
      {
        id: 2,
        name: 'Premium Leather Jacket',
        vendor: 'FashionHub',
        price: 89.99,
        originalPrice: null,
        rating: 4.5,
        reviews: 189,
        image: 'https://images.unsplash.com/photo-1551028719-00167b16eac5?q=80&w=870&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
        emoji: '🧥',
        imageBg: '#f8f0e8',
        category: 'Fashion'
      },
      {
        id: 4,
        name: 'Fitness Smart Watch',
        vendor: 'GadgetWorld',
        price: 199.99,
        originalPrice: 249.99,
        rating: 4.9,
        reviews: 456,
        image: 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?q=80&w=870&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
        emoji: '⌚',
        imageBg: '#e0e8f0',
        category: 'Electronics'
      }
    ]
    saveWishlist()
  }
}

const saveWishlist = () => {
  localStorage.setItem('shopsphere_wishlist', JSON.stringify(wishlistItems.value))
}

const addToCart = (product) => {
  // Get existing cart
  let cart = JSON.parse(localStorage.getItem('shopsphere_cart') || '[]')
  
  // Check if product exists
  const existingItem = cart.find(item => item.id === product.id)
  
  if (existingItem) {
    existingItem.quantity += 1
  } else {
    cart.push({
      ...product,
      quantity: 1,
      image: product.image || null
    })
  }
  
  // Save to localStorage
  localStorage.setItem('shopsphere_cart', JSON.stringify(cart))
  
  // Update badge
  window.dispatchEvent(new Event('storage'))
  window.dispatchEvent(new CustomEvent('cart-updated'))
  
  alert(`🛒 Added "${product.name}" to cart!`)
}

const removeFromWishlist = (productId) => {
  if (confirm('Remove this item from wishlist?')) {
    wishlistItems.value = wishlistItems.value.filter(item => item.id !== productId)
    saveWishlist()
    alert('💔 Removed from wishlist!')
  }
}

const continueShopping = () => {
  router.push('/products')
}

// ===== HANDLE IMAGE ERROR =====
const handleImageError = (e) => {
  e.target.style.display = 'none'
  // Show fallback emoji
  const parent = e.target.parentElement
  const fallback = document.createElement('span')
  fallback.className = 'item-emoji'
  fallback.textContent = '📦'
  parent.appendChild(fallback)
}

// ===== LIFECYCLE =====
onMounted(() => {
  loadWishlist()
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