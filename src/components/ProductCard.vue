<template>
  <div class="product-card-modern" @click="viewProduct">
    <!-- Product Image -->
    <div class="product-image-wrapper">
      <div class="product-image">
        <img 
          v-if="product.image" 
          :src="product.image" 
          :alt="product.name"
          loading="lazy"
          @error="handleImageError"
          class="product-img"
        />
        <span v-else class="product-emoji">{{ product.emoji || '📦' }}</span>
      </div>
      <!-- Badges -->
      <span v-if="product.isNew" class="product-badge new">New</span>
      <span v-if="product.discount" class="product-badge discount">-{{ product.discount }}%</span>
      <!-- Wishlist Button -->
      <button class="wishlist-btn" @click.stop="toggleWishlist" :class="{ 'wishlisted': isWishlisted }">
        <i :class="isWishlisted ? 'bi bi-heart-fill' : 'bi bi-heart'"></i>
      </button>
    </div>

    <!-- Product Info -->
    <div class="product-info">
      <div class="product-vendor">{{ product.vendor }}</div>
      <h5 class="product-title">{{ product.name }}</h5>
      
      <!-- Rating -->
      <div class="product-rating">
        <span class="stars">
          <i v-for="n in 5" :key="n" :class="n <= Math.floor(product.rating) ? 'bi bi-star-fill' : 'bi bi-star'"></i>
        </span>
        <span class="rating-count">({{ product.reviews || 0 }})</span>
      </div>

      <!-- Price -->
      <div class="product-price">
        <span class="current-price">${{ product.price }}</span>
        <span v-if="product.originalPrice" class="original-price">${{ product.originalPrice }}</span>
      </div>

      <!-- Add to Cart Button -->
      <button class="btn-primary-modern add-to-cart" @click.stop="addToCart">
        <i class="bi bi-cart-plus me-2"></i>Add to Cart
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { cartApi } from '@/services/api'

const props = defineProps({
  product: {
    type: Object,
    required: true,
    default: () => ({
      id: 1,
      name: 'Product Name',
      vendor: 'Vendor Name',
      price: 49.99,
      originalPrice: null,
      rating: 4.5,
      reviews: 0,
      image: null,
      emoji: '📦',
      isNew: false,
      discount: null,
      imageBg: '#e8ecf1'
    })
  }
})

const isWishlisted = ref(false)
const imageError = ref(false)
const isAddingToCart = ref(false)

// ===== CHECK IF PRODUCT IS IN WISHLIST =====
const checkWishlistStatus = () => {
  const wishlist = JSON.parse(localStorage.getItem('shopsphere_wishlist') || '[]')
  isWishlisted.value = wishlist.some(item => item.id === props.product.id)
}

// ===== TOGGLE WISHLIST =====
const toggleWishlist = () => {
  isWishlisted.value = !isWishlisted.value
  
  let wishlist = JSON.parse(localStorage.getItem('shopsphere_wishlist') || '[]')
  
  if (isWishlisted.value) {
    const exists = wishlist.some(item => item.id === props.product.id)
    if (!exists) {
      // Store the FULL product data including image
      wishlist.push({
        ...props.product,
        image: props.product.image || null
      })
      localStorage.setItem('shopsphere_wishlist', JSON.stringify(wishlist))
      alert('❤️ Added to wishlist!')
    }
  } else {
    wishlist = wishlist.filter(item => item.id !== props.product.id)
    localStorage.setItem('shopsphere_wishlist', JSON.stringify(wishlist))
    alert('💔 Removed from wishlist!')
  }
  
  emit('wishlist-toggle', { productId: props.product.id, isWishlisted: isWishlisted.value })
}

// ===== ADD TO CART (API Integration) =====
const addToCart = async () => {
  // Prevent multiple clicks
  if (isAddingToCart.value) return
  
  isAddingToCart.value = true

  try {
    // Check if user is logged in
    const token = localStorage.getItem('token')
    
    if (token) {
      // Logged in - use API
      const response = await cartApi.addToCart(props.product.id, 1)
      
      if (response.data.success) {
        // Update cart count in navbar
        window.dispatchEvent(new CustomEvent('cart-updated', { 
          detail: { count: response.data.data.cart_count } 
        }))
        
        // Show success message
        alert(`🛒 Added "${props.product.name}" to cart!`)
        
        // Emit event to parent
        emit('add-to-cart', props.product)
      }
    } else {
      // Not logged in - use localStorage (fallback)
      let cart = JSON.parse(localStorage.getItem('shopsphere_cart') || '[]')
      const existingItem = cart.find(item => item.id === props.product.id)
      
      if (existingItem) {
        existingItem.quantity += 1
      } else {
        cart.push({
          ...props.product,
          quantity: 1,
          image: props.product.image || null
        })
      }
      
      localStorage.setItem('shopsphere_cart', JSON.stringify(cart))
      
      // Update badge
      window.dispatchEvent(new Event('storage'))
      window.dispatchEvent(new CustomEvent('cart-updated'))
      
      const message = existingItem ? `Added another "${props.product.name}" to cart!` : `Added "${props.product.name}" to cart!`
      alert(`🛒 ${message}`)
      emit('add-to-cart', props.product)
    }
  } catch (error) {
    console.error('Error adding to cart:', error)
    
    // If API fails, fallback to localStorage
    let cart = JSON.parse(localStorage.getItem('shopsphere_cart') || '[]')
    const existingItem = cart.find(item => item.id === props.product.id)
    
    if (existingItem) {
      existingItem.quantity += 1
    } else {
      cart.push({
        ...props.product,
        quantity: 1,
        image: props.product.image || null
      })
    }
    
    localStorage.setItem('shopsphere_cart', JSON.stringify(cart))
    window.dispatchEvent(new Event('storage'))
    window.dispatchEvent(new CustomEvent('cart-updated'))
    
    const message = existingItem ? `Added another "${props.product.name}" to cart!` : `Added "${props.product.name}" to cart!`
    alert(`🛒 ${message}`)
    emit('add-to-cart', props.product)
  } finally {
    isAddingToCart.value = false
  }
}

const viewProduct = () => {
  emit('view-product', props.product)
}

// ===== HANDLE IMAGE ERROR =====
const handleImageError = (e) => {
  imageError.value = true
  e.target.style.display = 'none'
  // Show fallback emoji
  const parent = e.target.parentElement
  const fallback = document.createElement('span')
  fallback.className = 'product-emoji'
  fallback.textContent = props.product.emoji || '📦'
  parent.appendChild(fallback)
}

const emit = defineEmits(['wishlist-toggle', 'add-to-cart', 'view-product'])

// ===== LIFECYCLE =====
onMounted(() => {
  checkWishlistStatus()
})
</script>

<style scoped>
.product-card-modern {
  background: var(--bg-card);
  border-radius: var(--radius);
  overflow: hidden;
  border: 1px solid var(--border-color);
  transition: var(--transition);
  cursor: pointer;
  position: relative;
}

.product-card-modern:hover {
  transform: translateY(-8px);
  box-shadow: var(--shadow-hover);
  border-color: #667eea;
}

.product-image-wrapper {
  position: relative;
  padding: 16px;
  background: var(--bg-secondary);
}

.product-image {
  height: 200px;
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: var(--transition);
  overflow: hidden;
  background: var(--bg-secondary);
}

.product-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.product-card-modern:hover .product-img {
  transform: scale(1.05);
}

.product-emoji {
  font-size: 4rem;
  transition: var(--transition);
}

.product-card-modern:hover .product-emoji {
  transform: scale(1.1);
}

.product-badge {
  position: absolute;
  top: 24px;
  left: 24px;
  padding: 4px 14px;
  border-radius: 50px;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: white;
  z-index: 2;
}

.product-badge.new {
  background: var(--gradient-accent);
}

.product-badge.discount {
  background: #ef4444;
}

.wishlist-btn {
  position: absolute;
  top: 24px;
  right: 24px;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  border: none;
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(10px);
  color: #ef4444;
  font-size: 1.1rem;
  cursor: pointer;
  transition: var(--transition);
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  z-index: 2;
}

@media (prefers-color-scheme: dark) {
  .wishlist-btn {
    background: rgba(26, 25, 50, 0.9);
  }
}

.wishlist-btn:hover {
  transform: scale(1.1);
  background: #ef4444;
  color: white;
}

.wishlist-btn.wishlisted {
  background: #ef4444;
  color: white;
}

.wishlist-btn.wishlisted:hover {
  background: #dc2626;
}

.wishlist-btn .bi-heart-fill {
  color: #ef4444;
}

.wishlist-btn.wishlisted .bi-heart-fill {
  color: white;
}

.wishlist-btn:hover .bi-heart-fill {
  color: white;
}

.product-info {
  padding: 16px 20px 20px;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.product-vendor {
  font-size: 0.8rem;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.product-title {
  font-size: 1rem;
  font-weight: 600;
  color: var(--text-primary);
  margin: 0;
  line-height: 1.3;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
  max-height: 2.6em;
}

.product-rating {
  display: flex;
  align-items: center;
  gap: 6px;
  margin: 4px 0;
}

.stars {
  display: flex;
  gap: 2px;
  color: #f59e0b;
  font-size: 0.8rem;
}

.stars .bi-star {
  color: var(--border-color);
}

.rating-count {
  font-size: 0.8rem;
  color: var(--text-muted);
}

.product-price {
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

.add-to-cart {
  width: 100%;
  text-align: center;
  padding: 10px;
  font-size: 14px;
}

/* ===== DARK MODE ===== */
@media (prefers-color-scheme: dark) {
  .current-price {
    color: #8b5cf6;
  }
  
  .product-card-modern:hover {
    border-color: #8b5cf6;
  }
}

/* ===== RESPONSIVE ===== */
@media (max-width: 576px) {
  .product-image {
    height: 150px;
  }
  
  .product-emoji {
    font-size: 3rem;
  }
  
  .product-title {
    font-size: 0.9rem;
  }
  
  .current-price {
    font-size: 1.1rem;
  }
  
  .product-info {
    padding: 12px 14px 16px;
  }
  
  .product-badge {
    top: 16px;
    left: 16px;
    font-size: 10px;
    padding: 3px 10px;
  }
  
  .wishlist-btn {
    top: 16px;
    right: 16px;
    width: 32px;
    height: 32px;
    font-size: 0.9rem;
  }
}
</style>