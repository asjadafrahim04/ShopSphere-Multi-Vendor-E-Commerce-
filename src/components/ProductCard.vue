<template>
  <div class="product-card-modern" @click="viewProduct">
    <!-- Product Image -->
    <div class="product-image-wrapper">
      <div class="product-image">
        <!-- ✅ FIX: Check for images array from API -->
        <img 
          v-if="getProductImage()" 
          :src="getProductImage()" 
          :alt="product.name"
          loading="lazy"
          @error="handleImageError"
          class="product-img"
        />
        <span v-else class="product-emoji">{{ product.emoji || '📦' }}</span>
      </div>
      <!-- Badges -->
      <span v-if="product.is_new" class="product-badge new">New</span>
      <span v-if="product.discount_percentage" class="product-badge discount">-{{ product.discount_percentage }}%</span>
      <!-- Wishlist Button -->
      <button class="wishlist-btn" @click.stop="toggleWishlist" :class="{ 'wishlisted': isWishlisted }">
        <i :class="isWishlisted ? 'bi bi-heart-fill' : 'bi bi-heart'"></i>
      </button>
    </div>

    <!-- Product Info -->
    <div class="product-info">
      <div class="product-vendor">{{ getVendorName() }}</div>
      <h5 class="product-title">{{ product.name }}</h5>
      
      <!-- Rating -->
      <div class="product-rating">
        <span class="stars">
          <i v-for="n in 5" :key="n" :class="n <= Math.floor(product.rating || 0) ? 'bi bi-star-fill' : 'bi bi-star'"></i>
        </span>
        <span class="rating-count">({{ product.reviews_count || 0 }})</span>
      </div>

      <!-- Price -->
      <div class="product-price">
        <span class="current-price">${{ product.price }}</span>
        <span v-if="product.compare_price" class="original-price">${{ product.compare_price }}</span>
      </div>

      <!-- Stock Status -->
      <div class="product-stock" :class="{ 'out-of-stock': product.stock_quantity <= 0 }">
        {{ product.stock_quantity > 0 ? '✅ In Stock' : '❌ Out of Stock' }}
      </div>

      <!-- Add to Cart Button -->
      <button 
        class="btn-primary-modern add-to-cart" 
        @click.stop="addToCart"
        :disabled="product.stock_quantity <= 0 || isAddingToCart"
      >
        <i class="bi bi-cart-plus me-2"></i>
        {{ product.stock_quantity > 0 ? 'Add to Cart' : 'Out of Stock' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { cartApi, wishlistApi } from '@/services/api'

const props = defineProps({
  product: {
    type: Object,
    required: true,
    default: () => ({
      id: 1,
      name: 'Product Name',
      vendor: null,
      price: 49.99,
      compare_price: null,
      rating: 4.5,
      reviews_count: 0,
      images: [],
      emoji: '📦',
      is_new: false,
      discount_percentage: null,
      stock_quantity: 10
    })
  }
})

const isWishlisted = ref(false)
const imageError = ref(false)
const isAddingToCart = ref(false)
const isTogglingWishlist = ref(false)

// ===== GET PRODUCT IMAGE =====
const getProductImage = () => {
  // Check if product has images array with data
  if (props.product.images && props.product.images.length > 0) {
    const imagePath = props.product.images[0].image_path
    if (imagePath) {
      // If path already has http, use it directly
      if (imagePath.startsWith('http')) {
        return imagePath
      }
      // Otherwise, prepend the storage URL
      return `http://localhost:8000/storage/${imagePath}`
    }
  }
  
  // Check if product has direct image property (fallback for mock data)
  if (props.product.image) {
    if (props.product.image.startsWith('http')) {
      return props.product.image
    }
    return `http://localhost:8000/storage/${props.product.image}`
  }
  
  return null
}

// ===== GET VENDOR NAME =====
const getVendorName = () => {
  // If vendor is an object with name or shop_name
  if (props.product.vendor) {
    if (typeof props.product.vendor === 'object') {
      return props.product.vendor.shop_name || props.product.vendor.name || 'ShopSphere'
    }
    if (typeof props.product.vendor === 'string') {
      return props.product.vendor
    }
  }
  // If vendor_id exists but no vendor object
  if (props.product.vendor_id) {
    return 'Vendor #' + props.product.vendor_id
  }
  return 'ShopSphere'
}

// ===== CHECK IF PRODUCT IS IN WISHLIST =====
const checkWishlistStatus = async () => {
  const token = localStorage.getItem('token')
  
  if (token) {
    try {
      const response = await wishlistApi.checkInWishlist(props.product.id)
      if (response.data.success) {
        isWishlisted.value = response.data.data.in_wishlist
        return
      }
    } catch (error) {
      console.error('Error checking wishlist status via API:', error)
    }
  }
  
  const wishlist = JSON.parse(localStorage.getItem('shopsphere_wishlist') || '[]')
  isWishlisted.value = wishlist.some(item => item.id === props.product.id)
}

// ===== TOGGLE WISHLIST =====
const toggleWishlist = async () => {
  if (isTogglingWishlist.value) return
  isTogglingWishlist.value = true

  const token = localStorage.getItem('token')
  
  if (token) {
    try {
      if (isWishlisted.value) {
        const response = await wishlistApi.removeFromWishlist(props.product.id)
        if (response.data.success) {
          isWishlisted.value = false
          await updateWishlistCountAndDispatch()
        }
      } else {
        const response = await wishlistApi.addToWishlist(props.product.id)
        if (response.data.success) {
          isWishlisted.value = true
          await updateWishlistCountAndDispatch()
        }
      }
    } catch (error) {
      console.error('Error toggling wishlist via API:', error)
      toggleWishlistLocal()
    }
  } else {
    toggleWishlistLocal()
  }
  
  isTogglingWishlist.value = false
}

// ===== UPDATE WISHLIST COUNT =====
const updateWishlistCountAndDispatch = async () => {
  try {
    const token = localStorage.getItem('token')
    if (token) {
      const response = await wishlistApi.getWishlist()
      const count = response.data.data?.count || 0
      window.dispatchEvent(new CustomEvent('wishlist-updated', { 
        detail: { count: count } 
      }))
    } else {
      const items = JSON.parse(localStorage.getItem('shopsphere_wishlist') || '[]')
      window.dispatchEvent(new CustomEvent('wishlist-updated', { 
        detail: { count: items.length } 
      }))
    }
  } catch (error) {
    const items = JSON.parse(localStorage.getItem('shopsphere_wishlist') || '[]')
    window.dispatchEvent(new CustomEvent('wishlist-updated', { 
      detail: { count: items.length } 
    }))
  }
  window.dispatchEvent(new Event('storage'))
}

// ===== TOGGLE WISHLIST (LocalStorage Fallback) =====
const toggleWishlistLocal = () => {
  isWishlisted.value = !isWishlisted.value
  
  let wishlist = JSON.parse(localStorage.getItem('shopsphere_wishlist') || '[]')
  
  if (isWishlisted.value) {
    const exists = wishlist.some(item => item.id === props.product.id)
    if (!exists) {
      wishlist.push({
        ...props.product,
        image: getProductImage()
      })
      localStorage.setItem('shopsphere_wishlist', JSON.stringify(wishlist))
    }
  } else {
    wishlist = wishlist.filter(item => item.id !== props.product.id)
    localStorage.setItem('shopsphere_wishlist', JSON.stringify(wishlist))
  }
  
  const items = JSON.parse(localStorage.getItem('shopsphere_wishlist') || '[]')
  window.dispatchEvent(new CustomEvent('wishlist-updated', { 
    detail: { count: items.length } 
  }))
  window.dispatchEvent(new Event('storage'))
}

// ===== UPDATE CART COUNT =====
const updateCartCountAndDispatch = async () => {
  try {
    const token = localStorage.getItem('token')
    if (token) {
      const response = await cartApi.getCartTotal()
      const count = response.data.data?.count || 0
      window.dispatchEvent(new CustomEvent('cart-updated', { 
        detail: { count: count } 
      }))
    } else {
      const items = JSON.parse(localStorage.getItem('shopsphere_cart') || '[]')
      const count = items.reduce((sum, item) => sum + item.quantity, 0)
      window.dispatchEvent(new CustomEvent('cart-updated', { 
        detail: { count: count } 
      }))
    }
  } catch (error) {
    const items = JSON.parse(localStorage.getItem('shopsphere_cart') || '[]')
    const count = items.reduce((sum, item) => sum + item.quantity, 0)
    window.dispatchEvent(new CustomEvent('cart-updated', { 
      detail: { count: count } 
    }))
  }
  window.dispatchEvent(new Event('storage'))
}

// ===== ADD TO CART =====
const addToCart = async () => {
  if (isAddingToCart.value || props.product.stock_quantity <= 0) return
  isAddingToCart.value = true

  try {
    const token = localStorage.getItem('token')
    
    if (token) {
      const response = await cartApi.addToCart(props.product.id, 1)
      if (response.data.success) {
        await updateCartCountAndDispatch()
        // Show success feedback
        const btn = document.querySelector('.add-to-cart')
        if (btn) {
          const originalText = btn.innerHTML
          btn.innerHTML = '✅ Added!'
          setTimeout(() => {
            btn.innerHTML = originalText
          }, 1500)
        }
      }
    } else {
      let cart = JSON.parse(localStorage.getItem('shopsphere_cart') || '[]')
      const existingItem = cart.find(item => item.id === props.product.id)
      
      if (existingItem) {
        existingItem.quantity += 1
      } else {
        cart.push({
          ...props.product,
          quantity: 1,
          image: getProductImage()
        })
      }
      
      localStorage.setItem('shopsphere_cart', JSON.stringify(cart))
      await updateCartCountAndDispatch()
      
      const btn = document.querySelector('.add-to-cart')
      if (btn) {
        const originalText = btn.innerHTML
        btn.innerHTML = '✅ Added!'
        setTimeout(() => {
          btn.innerHTML = originalText
        }, 1500)
      }
    }
  } catch (error) {
    console.error('Error adding to cart:', error)
    // Fallback to localStorage
    let cart = JSON.parse(localStorage.getItem('shopsphere_cart') || '[]')
    const existingItem = cart.find(item => item.id === props.product.id)
    
    if (existingItem) {
      existingItem.quantity += 1
    } else {
      cart.push({
        ...props.product,
        quantity: 1,
        image: getProductImage()
      })
    }
    
    localStorage.setItem('shopsphere_cart', JSON.stringify(cart))
    await updateCartCountAndDispatch()
  } finally {
    isAddingToCart.value = false
  }
}

const viewProduct = () => {
  emit('view-product', props.product)
}

const handleImageError = (e) => {
  imageError.value = true
  e.target.style.display = 'none'
  const parent = e.target.parentElement
  const fallback = document.createElement('span')
  fallback.className = 'product-emoji'
  fallback.textContent = props.product.emoji || '📦'
  parent.appendChild(fallback)
}

const emit = defineEmits(['wishlist-toggle', 'add-to-cart', 'view-product'])

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
  background: #10b981;
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
  margin: 4px 0 4px;
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

.product-stock {
  font-size: 0.8rem;
  color: #10b981;
  margin-bottom: 4px;
}

.product-stock.out-of-stock {
  color: #ef4444;
}

.add-to-cart {
  width: 100%;
  text-align: center;
  padding: 10px;
  font-size: 14px;
}

.add-to-cart:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

@media (prefers-color-scheme: dark) {
  .current-price {
    color: #8b5cf6;
  }
  
  .product-card-modern:hover {
    border-color: #8b5cf6;
  }
}

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