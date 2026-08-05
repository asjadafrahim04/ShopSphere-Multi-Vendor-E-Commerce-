<template>
  <div class="product-detail-page">
    <div class="container-custom">
      <!-- Back Button -->
      <button class="back-btn" @click="goBack">
        <i class="bi bi-arrow-left"></i> Back to Products
      </button>

      <!-- Loading State -->
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p>Loading product details...</p>
      </div>

      <!-- Product Detail -->
      <div v-else-if="product" class="product-detail-grid">
        <!-- Product Image Gallery -->
        <div class="product-image-section">
          <div class="main-image">
            <img 
              :src="mainImage" 
              :alt="product.name"
              class="main-product-img"
              @error="handleImageError"
            />
            <span v-if="product.discount_percentage" class="discount-badge">
              -{{ product.discount_percentage }}%
            </span>
          </div>
          <div v-if="product.images && product.images.length > 1" class="thumbnail-grid">
            <div 
              v-for="(image, index) in product.images" 
              :key="index"
              class="thumbnail"
              :class="{ active: currentImageIndex === index }"
              @click="selectImage(index)"
            >
              <img 
                :src="'http://localhost:8000/storage/' + image.image_path" 
                :alt="product.name"
                class="thumbnail-img"
                @error="handleThumbnailError"
              />
            </div>
          </div>
        </div>

        <!-- Product Info -->
        <div class="product-info-section">
          <div class="product-vendor">
            <i class="bi bi-shop"></i>
            {{ getVendorName() }}
          </div>
          <h1 class="product-name">{{ product.name }}</h1>
          
          <!-- Rating -->
          <div class="product-rating">
            <span class="stars">
              <i v-for="n in 5" :key="n" :class="n <= Math.floor(product.rating || 0) ? 'bi bi-star-fill' : 'bi bi-star'"></i>
            </span>
            <span class="rating-count">({{ product.reviews_count || 0 }} reviews)</span>
          </div>

          <!-- Price -->
          <div class="product-price">
            <span class="current-price">${{ product.price }}</span>
            <span v-if="product.compare_price" class="original-price">${{ product.compare_price }}</span>
            <span v-if="product.discount_percentage" class="discount-badge">-{{ product.discount_percentage }}%</span>
          </div>

          <!-- Stock Status -->
          <div class="stock-status" :class="{ 'in-stock': product.stock_quantity > 0, 'out-of-stock': product.stock_quantity <= 0 }">
            <i :class="product.stock_quantity > 0 ? 'bi bi-check-circle-fill' : 'bi bi-x-circle-fill'"></i>
            {{ product.stock_quantity > 0 ? `In Stock (${product.stock_quantity} available)` : 'Out of Stock' }}
          </div>

          <!-- Description -->
          <div class="product-description">
            <h4>Description</h4>
            <p>{{ product.description || 'No description available for this product.' }}</p>
          </div>

          <!-- Product Features -->
          <div v-if="productFeatures.length > 0" class="product-features">
            <h4>Features</h4>
            <ul>
              <li v-for="(feature, index) in productFeatures" :key="index">
                <i class="bi bi-check-circle-fill"></i> {{ feature }}
              </li>
            </ul>
          </div>

          <!-- Add to Cart Section -->
          <div class="add-to-cart-section">
            <div class="quantity-selector">
              <button @click="decreaseQuantity" :disabled="quantity <= 1">
                <i class="bi bi-dash"></i>
              </button>
              <span>{{ quantity }}</span>
              <button @click="increaseQuantity" :disabled="quantity >= product.stock_quantity">
                <i class="bi bi-plus"></i>
              </button>
            </div>
            <button 
              class="btn-primary-modern add-to-cart-btn" 
              @click="addToCart"
              :disabled="product.stock_quantity <= 0 || isAddingToCart"
            >
              <i class="bi bi-cart-plus me-2"></i>
              {{ isAddingToCart ? 'Adding...' : (product.stock_quantity > 0 ? 'Add to Cart' : 'Out of Stock') }}
            </button>
            <button 
              class="wishlist-btn-large" 
              @click="toggleWishlist" 
              :class="{ 'wishlisted': isWishlisted }"
            >
              <i :class="isWishlisted ? 'bi bi-heart-fill' : 'bi bi-heart'"></i>
              {{ isWishlisted ? 'Remove from Wishlist' : 'Add to Wishlist' }}
            </button>
          </div>

          <!-- Product Meta -->
          <div class="product-meta">
            <div class="meta-item">
              <i class="bi bi-tag"></i>
              <span>SKU: <strong>{{ product.sku || 'N/A' }}</strong></span>
            </div>
            <div class="meta-item">
              <i class="bi bi-folder"></i>
              <span>Category: <strong>{{ product.category?.name || 'Uncategorized' }}</strong></span>
            </div>
            <div class="meta-item">
              <i class="bi bi-clock"></i>
              <span>Added: <strong>{{ formatDate(product.created_at) }}</strong></span>
            </div>
          </div>

          <!-- Share Section -->
          <div class="share-section">
            <span>Share:</span>
            <button class="share-btn" @click="shareProduct('facebook')">
              <i class="bi bi-facebook"></i>
            </button>
            <button class="share-btn" @click="shareProduct('twitter')">
              <i class="bi bi-twitter-x"></i>
            </button>
            <button class="share-btn" @click="shareProduct('whatsapp')">
              <i class="bi bi-whatsapp"></i>
            </button>
            <button class="share-btn" @click="copyProductLink">
              <i class="bi bi-link-45deg"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Product Not Found -->
      <div v-else class="not-found">
        <div class="empty-state">
          <i class="bi bi-exclamation-triangle" style="font-size: 4rem; color: var(--text-muted);"></i>
          <h3>Product Not Found</h3>
          <p>The product you're looking for doesn't exist or has been removed.</p>
          <p class="debug-info" v-if="debugInfo">{{ debugInfo }}</p>
          <button class="btn-primary-modern" @click="goBack">
            <i class="bi bi-arrow-left me-2"></i>Go Back
          </button>
        </div>
      </div>

      <!-- Related Products -->
      <section v-if="relatedProducts.length > 0" class="related-products">
        <h2 class="section-title">You Might Also Like</h2>
        <div class="products-grid">
          <ProductCard 
            v-for="product in relatedProducts" 
            :key="product.id"
            :product="product"
            @add-to-cart="handleAddToCart"
            @wishlist-toggle="handleWishlistToggle"
            @view-product="goToProduct"
          />
        </div>
      </section>

      <!-- Toast Notification -->
      <div v-if="toast.show" class="toast-notification" :class="toast.type">
        <i :class="toast.icon"></i>
        {{ toast.message }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import ProductCard from '../components/ProductCard.vue'
import { cartApi, wishlistApi } from '@/services/api'

const router = useRouter()
const route = useRoute()

// ===== STATE =====
const product = ref(null)
const relatedProducts = ref([])
const loading = ref(true)
const quantity = ref(1)
const currentImageIndex = ref(0)
const isWishlisted = ref(false)
const isAddingToCart = ref(false)
const debugInfo = ref('')

// ===== TOAST =====
const toast = ref({
  show: false,
  message: '',
  type: 'success',
  icon: 'bi bi-check-circle-fill'
})

// ===== COMPUTED =====
const mainImage = computed(() => {
  if (!product.value) return ''
  
  if (product.value.images && product.value.images.length > 0) {
    const image = product.value.images[currentImageIndex.value]
    if (image) {
      return 'http://localhost:8000/storage/' + image.image_path
    }
  }
  
  return 'https://via.placeholder.com/600x600?text=No+Image'
})

const productFeatures = computed(() => {
  if (!product.value) return []
  
  // Try to get features from attributes or specifications
  if (product.value.attributes) {
    if (typeof product.value.attributes === 'object') {
      return Object.values(product.value.attributes)
    }
    if (typeof product.value.attributes === 'string') {
      try {
        const parsed = JSON.parse(product.value.attributes)
        if (Array.isArray(parsed)) return parsed
        if (typeof parsed === 'object') return Object.values(parsed)
      } catch (e) {}
    }
  }
  
  // Default features based on product name
  const defaultFeatures = [
    'High Quality Material',
    'Durable Construction',
    'Reliable Performance',
    'Easy to Use',
    'Great Value'
  ]
  return defaultFeatures
})

// ===== METHODS =====

// Fetch product from API
const fetchProduct = async () => {
  loading.value = true
  const productId = route.params.id
  
  // Debug info
  console.log('🔍 Fetching product ID:', productId)
  console.log('🔍 URL:', `http://localhost:8000/api/products/${productId}`)
  
  // Show debug info on page
  debugInfo.value = `Fetching product ID: ${productId}`
  
  try {
    const response = await fetch(`http://localhost:8000/api/products/${productId}`, {
      headers: {
        'Accept': 'application/json'
      }
    })
    
    console.log('📡 Response status:', response.status)
    
    const data = await response.json()
    
    console.log('📦 API Response:', data)
    console.log('📦 Data success:', data.success)
    console.log('📦 Product data:', data.data)
    
    if (data.success && data.data) {
      product.value = data.data
      quantity.value = 1
      currentImageIndex.value = 0
      debugInfo.value = ''
      await checkWishlistStatus()
      await fetchRelatedProducts()
    } else {
      console.error('❌ API returned error:', data.message || 'Product not found')
      debugInfo.value = `Error: ${data.message || 'Product not found'}`
      product.value = null
    }
  } catch (error) {
    console.error('❌ Error fetching product:', error)
    debugInfo.value = `Error: ${error.message}`
    product.value = null
  } finally {
    loading.value = false
  }
}

// Fetch related products
const fetchRelatedProducts = async () => {
  if (!product.value) return
  
  try {
    const response = await fetch(
      `http://localhost:8000/api/products?category_id=${product.value.category_id}&limit=4`
    )
    const data = await response.json()
    
    if (data.success) {
      relatedProducts.value = data.data.data
        .filter(p => p.id !== product.value.id)
        .slice(0, 4)
    }
  } catch (error) {
    console.error('Error fetching related products:', error)
  }
}

// Check wishlist status
const checkWishlistStatus = async () => {
  if (!product.value) return
  
  const token = localStorage.getItem('token')
  if (!token) {
    // Check localStorage
    const wishlist = JSON.parse(localStorage.getItem('shopsphere_wishlist') || '[]')
    isWishlisted.value = wishlist.some(item => item.id === product.value.id)
    return
  }
  
  try {
    const response = await wishlistApi.checkInWishlist(product.value.id)
    if (response.data.success) {
      isWishlisted.value = response.data.data.in_wishlist
    }
  } catch (error) {
    console.error('Error checking wishlist:', error)
    // Fallback to localStorage
    const wishlist = JSON.parse(localStorage.getItem('shopsphere_wishlist') || '[]')
    isWishlisted.value = wishlist.some(item => item.id === product.value.id)
  }
}

// Get vendor name
const getVendorName = () => {
  if (!product.value) return 'ShopSphere'
  
  if (product.value.vendor) {
    if (typeof product.value.vendor === 'object') {
      return product.value.vendor.shop_name || product.value.vendor.name || 'ShopSphere'
    }
    return product.value.vendor
  }
  return 'ShopSphere'
}

// Format date
const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('en-US', {
    day: '2-digit',
    month: 'long',
    year: 'numeric'
  })
}

// Image gallery
const selectImage = (index) => {
  currentImageIndex.value = index
}

const handleImageError = (event) => {
  event.target.src = 'https://via.placeholder.com/600x600?text=No+Image'
}

const handleThumbnailError = (event) => {
  event.target.src = 'https://via.placeholder.com/100x100?text=No+Image'
}

// Navigation
const goBack = () => {
  router.back()
}

const goToProduct = (product) => {
  router.push(`/product/${product.id}`)
}

// Quantity
const increaseQuantity = () => {
  if (quantity.value < (product.value?.stock_quantity || 0)) {
    quantity.value++
  }
}

const decreaseQuantity = () => {
  if (quantity.value > 1) {
    quantity.value--
  }
}

// Add to cart
const addToCart = async () => {
  if (isAddingToCart.value || !product.value || product.value.stock_quantity <= 0) return
  
  isAddingToCart.value = true
  
  try {
    const token = localStorage.getItem('token')
    const productId = product.value.id
    const quantityToAdd = quantity.value
    
    if (token) {
      const response = await cartApi.addToCart(productId, quantityToAdd)
      if (response.data.success) {
        showToast('Product added to cart!', 'success', 'bi bi-check-circle-fill')
        await updateCartCount()
      }
    } else {
      // Guest cart
      let cart = JSON.parse(localStorage.getItem('shopsphere_cart') || '[]')
      const existingItem = cart.find(item => item.id === productId)
      
      if (existingItem) {
        existingItem.quantity += quantityToAdd
      } else {
        cart.push({
          ...product.value,
          quantity: quantityToAdd,
          image: mainImage.value
        })
      }
      
      localStorage.setItem('shopsphere_cart', JSON.stringify(cart))
      showToast('Product added to cart!', 'success', 'bi bi-check-circle-fill')
      await updateCartCount()
    }
  } catch (error) {
    console.error('Error adding to cart:', error)
    showToast('Failed to add to cart. Please try again.', 'error', 'bi bi-exclamation-circle-fill')
  } finally {
    isAddingToCart.value = false
  }
}

// Update cart count
const updateCartCount = async () => {
  try {
    const token = localStorage.getItem('token')
    let count = 0
    
    if (token) {
      const response = await cartApi.getCartTotal()
      count = response.data.data?.count || 0
    } else {
      const cart = JSON.parse(localStorage.getItem('shopsphere_cart') || '[]')
      count = cart.reduce((sum, item) => sum + item.quantity, 0)
    }
    
    window.dispatchEvent(new CustomEvent('cart-updated', { 
      detail: { count } 
    }))
  } catch (error) {
    console.error('Error updating cart count:', error)
  }
}

// Toggle wishlist
const toggleWishlist = async () => {
  if (!product.value) return
  
  const token = localStorage.getItem('token')
  
  if (!token) {
    showToast('Please login to add to wishlist', 'error', 'bi bi-exclamation-circle-fill')
    router.push('/login')
    return
  }
  
  try {
    if (isWishlisted.value) {
      await wishlistApi.removeFromWishlist(product.value.id)
      isWishlisted.value = false
      showToast('Removed from wishlist', 'info', 'bi bi-heart')
    } else {
      await wishlistApi.addToWishlist(product.value.id)
      isWishlisted.value = true
      showToast('Added to wishlist!', 'success', 'bi bi-heart-fill')
    }
    
    // Update wishlist count
    const response = await wishlistApi.getWishlist()
    const count = response.data.data?.count || 0
    window.dispatchEvent(new CustomEvent('wishlist-updated', { 
      detail: { count } 
    }))
  } catch (error) {
    console.error('Error toggling wishlist:', error)
    showToast('Failed to update wishlist', 'error', 'bi bi-exclamation-circle-fill')
  }
}

// Share functions
const shareProduct = (platform) => {
  const url = window.location.href
  const text = `Check out ${product.value?.name} on ShopSphere!`
  
  const shareUrls = {
    facebook: `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`,
    twitter: `https://twitter.com/intent/tweet?text=${encodeURIComponent(text)}&url=${encodeURIComponent(url)}`,
    whatsapp: `https://api.whatsapp.com/send?text=${encodeURIComponent(text + ' ' + url)}`
  }
  
  window.open(shareUrls[platform], '_blank', 'width=600,height=400')
}

const copyProductLink = () => {
  navigator.clipboard.writeText(window.location.href)
  showToast('Link copied to clipboard!', 'success', 'bi bi-check-circle-fill')
}

// Toast notification
const showToast = (message, type = 'success', icon = 'bi bi-check-circle-fill') => {
  toast.value = {
    show: true,
    message,
    type,
    icon
  }
  
  setTimeout(() => {
    toast.value.show = false
  }, 3000)
}

// Event handlers for ProductCard
const handleAddToCart = (product) => {
  console.log('Add to cart from related:', product)
}

const handleWishlistToggle = ({ productId, isWishlisted }) => {
  console.log('Wishlist toggled:', productId, isWishlisted)
}

// ===== LIFECYCLE =====
onMounted(() => {
  console.log('🔄 ProductDetail mounted, ID:', route.params.id)
  fetchProduct()
})

// Watch route changes
watch(() => route.params.id, (newId, oldId) => {
  console.log('🔄 Route changed from', oldId, 'to', newId)
  fetchProduct()
})
</script>

<style scoped>
.product-detail-page {
  padding: 40px 0 80px;
  background: var(--bg-primary);
  min-height: 100vh;
}

.container-custom {
  max-width: 1280px;
  margin: 0 auto;
  padding: 0 20px;
}

/* ===== BACK BUTTON ===== */
.back-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: none;
  border: none;
  color: var(--text-secondary);
  cursor: pointer;
  font-size: 1rem;
  padding: 8px 0;
  margin-bottom: 30px;
  transition: var(--transition);
}

.back-btn:hover {
  color: var(--text-primary);
  transform: translateX(-4px);
}

/* ===== LOADING STATE ===== */
.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 400px;
  gap: 20px;
}

.spinner {
  width: 60px;
  height: 60px;
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

/* ===== PRODUCT NOT FOUND ===== */
.not-found {
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
  font-size: 1.8rem;
  color: var(--text-primary);
  margin: 0;
}

.empty-state p {
  color: var(--text-muted);
  font-size: 1.05rem;
}

.debug-info {
  font-size: 0.85rem;
  color: #ef4444;
  background: #fee2e2;
  padding: 8px 16px;
  border-radius: 8px;
  margin: 0;
}

/* ===== PRODUCT DETAIL GRID ===== */
.product-detail-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 60px;
  margin-bottom: 60px;
  background: var(--bg-card);
  border-radius: var(--radius);
  padding: 40px;
  border: 1px solid var(--border-color);
}

/* ===== IMAGE SECTION ===== */
.product-image-section {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.main-image {
  position: relative;
  height: 450px;
  border-radius: var(--radius);
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg-secondary);
  border: 1px solid var(--border-color);
  overflow: hidden;
}

.main-product-img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.discount-badge {
  position: absolute;
  top: 16px;
  left: 16px;
  background: #ef4444;
  color: white;
  padding: 6px 16px;
  border-radius: 50px;
  font-weight: 700;
  font-size: 16px;
}

.thumbnail-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 12px;
}

.thumbnail {
  height: 80px;
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid transparent;
  cursor: pointer;
  transition: var(--transition);
  background: var(--bg-secondary);
  border-color: var(--border-color);
  overflow: hidden;
}

.thumbnail-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.thumbnail:hover {
  border-color: #667eea;
}

.thumbnail.active {
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
}

/* ===== INFO SECTION ===== */
.product-info-section {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.product-vendor {
  font-size: 0.9rem;
  color: #667eea;
  font-weight: 500;
}

.product-vendor i {
  margin-right: 8px;
}

.product-name {
  font-size: 2.5rem;
  font-weight: 700;
  color: var(--text-primary);
  margin: 0;
  line-height: 1.2;
}

.product-rating {
  display: flex;
  align-items: center;
  gap: 12px;
}

.product-rating .stars {
  display: flex;
  gap: 4px;
  color: #f59e0b;
  font-size: 1.1rem;
}

.rating-count {
  color: var(--text-muted);
}

.product-price {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 12px 0;
  border-top: 1px solid var(--border-color);
  border-bottom: 1px solid var(--border-color);
}

.current-price {
  font-size: 2.5rem;
  font-weight: 700;
  color: #667eea;
}

.original-price {
  font-size: 1.5rem;
  color: var(--text-muted);
  text-decoration: line-through;
}

.discount-badge {
  background: #ef4444;
  color: white;
  padding: 4px 14px;
  border-radius: 50px;
  font-weight: 700;
  font-size: 1rem;
}

.stock-status {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  font-weight: 500;
}

.stock-status.in-stock {
  color: #10b981;
}

.stock-status.out-of-stock {
  color: #ef4444;
}

.product-description h4,
.product-features h4 {
  font-size: 1.1rem;
  font-weight: 600;
  color: var(--text-primary);
  margin-bottom: 8px;
}

.product-description p {
  color: var(--text-secondary);
  line-height: 1.7;
}

.product-features ul {
  list-style: none;
  padding: 0;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
}

.product-features ul li {
  display: flex;
  align-items: center;
  gap: 8px;
  color: var(--text-secondary);
  font-size: 0.95rem;
}

.product-features ul li i {
  color: #22c55e;
}

/* ===== ADD TO CART SECTION ===== */
.add-to-cart-section {
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
  align-items: center;
  padding: 16px 0;
  border-top: 1px solid var(--border-color);
  border-bottom: 1px solid var(--border-color);
}

.quantity-selector {
  display: flex;
  align-items: center;
  border: 1px solid var(--border-color);
  border-radius: var(--radius-sm);
  overflow: hidden;
}

.quantity-selector button {
  width: 40px;
  height: 40px;
  border: none;
  background: var(--bg-secondary);
  color: var(--text-primary);
  font-size: 1.2rem;
  cursor: pointer;
  transition: var(--transition);
}

.quantity-selector button:hover:not(:disabled) {
  background: var(--bg-primary);
}

.quantity-selector button:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

.quantity-selector span {
  width: 40px;
  text-align: center;
  font-weight: 600;
  font-size: 1.1rem;
}

.add-to-cart-btn {
  padding: 12px 40px;
  flex: 1;
}

.add-to-cart-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.wishlist-btn-large {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 24px;
  border: 2px solid var(--border-color);
  border-radius: 50px;
  background: transparent;
  color: var(--text-primary);
  font-weight: 600;
  cursor: pointer;
  transition: var(--transition);
}

.wishlist-btn-large:hover {
  border-color: #ef4444;
  color: #ef4444;
}

.wishlist-btn-large.wishlisted {
  border-color: #ef4444;
  background: #ef4444;
  color: white;
}

.wishlist-btn-large.wishlisted:hover {
  background: #dc2626;
  border-color: #dc2626;
}

/* ===== PRODUCT META ===== */
.product-meta {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 8px;
  color: var(--text-secondary);
}

.meta-item i {
  font-size: 1.1rem;
  width: 20px;
}

/* ===== SHARE SECTION ===== */
.share-section {
  display: flex;
  align-items: center;
  gap: 8px;
  padding-top: 12px;
  border-top: 1px solid var(--border-color);
}

.share-section span {
  font-size: 14px;
  color: var(--text-muted);
}

.share-btn {
  width: 36px;
  height: 36px;
  border: 1px solid var(--border-color);
  border-radius: 50%;
  background: transparent;
  color: var(--text-secondary);
  cursor: pointer;
  transition: var(--transition);
  display: flex;
  align-items: center;
  justify-content: center;
}

.share-btn:hover {
  background: #667eea;
  color: white;
  border-color: #667eea;
}

/* ===== RELATED PRODUCTS ===== */
.related-products {
  margin-top: 60px;
  border-top: 1px solid var(--border-color);
  padding-top: 40px;
}

.related-products .section-title {
  font-size: 2rem;
  font-weight: 700;
  color: var(--text-primary);
  margin-bottom: 30px;
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 24px;
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
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
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

/* ===== RESPONSIVE ===== */
@media (max-width: 1024px) {
  .product-detail-grid {
    grid-template-columns: 1fr;
    gap: 30px;
    padding: 24px;
  }
  
  .products-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .product-detail-page {
    padding: 20px 0 60px;
  }
  
  .product-detail-grid {
    padding: 16px;
  }
  
  .main-image {
    height: 300px;
  }
  
  .product-name {
    font-size: 2rem;
  }
  
  .current-price {
    font-size: 2rem;
  }
  
  .add-to-cart-section {
    flex-direction: column;
  }
  
  .add-to-cart-btn {
    width: 100%;
  }
  
  .wishlist-btn-large {
    width: 100%;
    justify-content: center;
  }
  
  .product-features ul {
    grid-template-columns: 1fr;
  }
  
  .products-grid {
    grid-template-columns: 1fr 1fr;
    gap: 16px;
  }
  
  .toast-notification {
    bottom: 16px;
    right: 16px;
    left: 16px;
    padding: 12px 16px;
    font-size: 14px;
  }
}

@media (max-width: 480px) {
  .main-image {
    height: 220px;
  }
  
  .product-name {
    font-size: 1.5rem;
  }
  
  .current-price {
    font-size: 1.5rem;
  }
  
  .original-price {
    font-size: 1.1rem;
  }
  
  .thumbnail {
    height: 60px;
  }
  
  .products-grid {
    grid-template-columns: 1fr 1fr;
    gap: 12px;
  }
  
  .quantity-selector button {
    width: 34px;
    height: 34px;
  }
  
  .product-detail-grid {
    padding: 12px;
  }
}
</style>