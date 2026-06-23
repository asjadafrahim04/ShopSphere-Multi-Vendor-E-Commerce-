<template>
  <div class="product-detail-page">
    <div class="container-custom">
      <!-- Back Button -->
      <button class="back-btn" @click="goBack">
        <i class="bi bi-arrow-left"></i> Back to Products
      </button>

      <!-- Loading State -->
      <div v-if="isLoading" class="loading-state">
        <div class="spinner"></div>
        <p>Loading product details...</p>
      </div>

      <!-- Product Detail -->
      <div v-else-if="product" class="product-detail-grid">
        <!-- Product Image -->
        <div class="product-image-section">
          <div class="main-image">
            <img 
              v-if="product.image" 
              :src="product.image" 
              :alt="product.name"
              class="main-product-img"
              @error="handleImageError"
            />
          </div>
          <div class="thumbnail-grid">
            <div 
              v-for="i in 4" 
              :key="i" 
              class="thumbnail"
              @click="changeMainImage(i)"
            >
              <img 
                v-if="product.image" 
                :src="product.image" 
                :alt="product.name"
                class="thumbnail-img"
                @error="handleImageError"
              />
            </div>
          </div>
        </div>

        <!-- Product Info -->
        <div class="product-info-section">
          <div class="product-vendor">{{ product.vendor }}</div>
          <h1 class="product-name">{{ product.name }}</h1>
          
          <!-- Rating -->
          <div class="product-rating">
            <span class="stars">
              <i v-for="n in 5" :key="n" :class="n <= Math.floor(product.rating) ? 'bi bi-star-fill' : 'bi bi-star'"></i>
            </span>
            <span class="rating-count">{{ product.rating }} ({{ product.reviews }} reviews)</span>
          </div>

          <!-- Price -->
          <div class="product-price">
            <span class="current-price">${{ product.price }}</span>
            <span v-if="product.originalPrice" class="original-price">${{ product.originalPrice }}</span>
            <span v-if="product.discount" class="discount-badge">-{{ product.discount }}%</span>
          </div>

          <!-- Description -->
          <div class="product-description">
            <h4>Description</h4>
            <p>{{ product.description || 'No description available for this product.' }}</p>
          </div>

          <!-- Product Features -->
          <div class="product-features">
            <h4>Features</h4>
            <ul>
              <li v-for="feature in productFeatures" :key="feature">
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
              <button @click="increaseQuantity">
                <i class="bi bi-plus"></i>
              </button>
            </div>
            <button class="btn-primary-modern add-to-cart-btn" @click="addToCart">
              <i class="bi bi-cart-plus me-2"></i>Add to Cart
            </button>
            <button class="wishlist-btn-large" @click="toggleWishlist" :class="{ 'wishlisted': isWishlisted }">
              <i :class="isWishlisted ? 'bi bi-heart-fill' : 'bi bi-heart'"></i>
              {{ isWishlisted ? 'Remove from Wishlist' : 'Add to Wishlist' }}
            </button>
          </div>

          <!-- Product Meta -->
          <div class="product-meta">
            <div class="meta-item">
              <i class="bi bi-tag"></i>
              <span>Category: <strong>{{ product.category || 'Uncategorized' }}</strong></span>
            </div>
            <div class="meta-item">
              <i class="bi bi-box"></i>
              <span>Stock: <strong>{{ product.stock || 'In Stock' }}</strong></span>
            </div>
          </div>
        </div>
      </div>

      <!-- Product Not Found -->
      <div v-else class="not-found">
        <div class="empty-state">
          <i class="bi bi-exclamation-triangle" style="font-size: 4rem; color: var(--text-muted);"></i>
          <h3>Product Not Found</h3>
          <p>The product you're looking for doesn't exist or has been removed.</p>
          <button class="btn-primary-modern" @click="goBack">
            <i class="bi bi-arrow-left me-2"></i>Go Back
          </button>
        </div>
      </div>

      <!-- Related Products -->
      <section v-if="relatedProducts.length > 0 && product" class="related-products">
        <h2 class="section-title">Related Products</h2>
        <div class="products-grid">
          <ProductCard 
            v-for="product in relatedProducts" 
            :key="product.id"
            :product="product"
            @add-to-cart="handleAddToCart"
            @wishlist-toggle="handleWishlistToggle"
            @view-product="handleViewProduct"
          />
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import ProductCard from '../components/ProductCard.vue'

const router = useRouter()
const route = useRoute()

// ===== STATE =====
const isLoading = ref(true)
const quantity = ref(1)
const isWishlisted = ref(false)
const currentImage = ref(0)

// ===== ALL PRODUCTS DATA WITH REAL IMAGES =====
const allProducts = ref([
  { 
    id: 1, 
    name: 'Wireless Noise-Cancelling Headphones', 
    vendor: 'TechShop', 
    category: 'Electronics',
    price: 49.99,
    originalPrice: 79.99,
    rating: 4.8, 
    reviews: 234,
    image: 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?q=80&w=870&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
    isNew: true,
    discount: 38,
    description: 'Premium wireless headphones with active noise cancellation and 30hr battery life. Perfect for travel, work, and everyday use.',
    stock: 'In Stock',
    features: ['Active Noise Cancellation', '30 Hour Battery', 'Bluetooth 5.0', 'Comfortable Fit', 'Built-in Microphone']
  },
  { 
    id: 2, 
    name: 'Premium Leather Jacket', 
    vendor: 'FashionHub', 
    category: 'Fashion',
    price: 89.99,
    originalPrice: null,
    rating: 4.5, 
    reviews: 189,
    image: 'https://images.unsplash.com/photo-1551028719-00167b16eac5?q=80&w=870&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
    isNew: false,
    discount: null,
    description: 'Genuine leather jacket with premium stitching and classic design. Made from high-quality materials for durability and style.',
    stock: 'In Stock',
    features: ['Genuine Leather', 'Classic Design', 'Multiple Colors', 'Premium Stitching', 'Durable Material']
  },
  { 
    id: 3, 
    name: 'Smart Coffee Maker Pro', 
    vendor: 'HomeGoods', 
    category: 'Home & Living',
    price: 129.99,
    originalPrice: 159.99,
    rating: 4.7, 
    reviews: 312,
    image: 'https://images.unsplash.com/photo-1517668808822-9f02a4bcc53a?q=80&w=870&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
    isNew: true,
    discount: 19,
    description: 'Programmable coffee maker with smart features and temperature control. Brew the perfect cup every time.',
    stock: 'In Stock',
    features: ['Programmable', 'Temperature Control', 'Smart Features', 'Large Capacity', 'Auto Shut-off']
  },
  { 
    id: 4, 
    name: 'Fitness Smart Watch', 
    vendor: 'GadgetWorld', 
    category: 'Electronics',
    price: 199.99,
    originalPrice: 249.99,
    rating: 4.9, 
    reviews: 456,
    image: 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?q=80&w=870&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
    isNew: false,
    discount: 20,
    description: 'Advanced fitness tracker with heart rate monitor and GPS tracking. Monitor your health and fitness goals.',
    stock: 'In Stock',
    features: ['Heart Rate Monitor', 'GPS Tracking', 'Water Resistant', 'Long Battery Life', 'Sleep Tracking']
  },
  { 
    id: 5, 
    name: 'Organic Cotton T-Shirt', 
    vendor: 'FashionHub', 
    category: 'Fashion',
    price: 24.99,
    originalPrice: null,
    rating: 4.2, 
    reviews: 78,
    image: 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?q=80&w=870&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
    isNew: true,
    discount: null,
    description: 'Comfortable organic cotton t-shirt available in multiple colors. Sustainable fashion for everyday wear.',
    stock: 'In Stock',
    features: ['Organic Cotton', 'Multiple Colors', 'Comfortable Fit', 'Eco-Friendly', 'Durable Material']
  },
  { 
    id: 6, 
    name: 'E-Reader Paperwhite', 
    vendor: 'TechShop', 
    category: 'Electronics',
    price: 139.99,
    originalPrice: 159.99,
    rating: 4.6, 
    reviews: 267,
    image: 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?q=80&w=870&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
    isNew: false,
    discount: 12,
    description: 'Waterproof e-reader with built-in light and weeks of battery life. Carry your entire library with you.',
    stock: 'In Stock',
    features: ['Waterproof', 'Built-in Light', 'Weeks of Battery', 'Large Storage', 'Glare-Free Display']
  },
  { 
    id: 7, 
    name: 'Professional Knife Set', 
    vendor: 'HomeGoods', 
    category: 'Home & Living',
    price: 79.99,
    originalPrice: null,
    rating: 4.4, 
    reviews: 145,
    image: 'https://images.unsplash.com/photo-1593618998160-e34014e67546?q=80&w=870&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
    isNew: false,
    discount: null,
    description: 'Professional 5-piece kitchen knife set with wooden storage block. High-quality stainless steel blades.',
    stock: 'In Stock',
    features: ['Stainless Steel', '5-Piece Set', 'Wooden Storage', 'Sharp Blades', 'Ergonomic Handles']
  },
  { 
    id: 8, 
    name: 'Wireless Charging Pad', 
    vendor: 'GadgetWorld', 
    category: 'Electronics',
    price: 29.99,
    originalPrice: 49.99,
    rating: 4.3, 
    reviews: 198,
    image: 'https://images.unsplash.com/photo-1586953208448-b3a1fdd0a1c5?q=80&w=870&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
    isNew: true,
    discount: 40,
    description: 'Fast wireless charging pad compatible with all Qi-enabled devices. Sleek design for your desk or nightstand.',
    stock: 'In Stock',
    features: ['Fast Charging', 'Qi-Compatible', 'Sleek Design', 'LED Indicator', 'Overcharge Protection']
  },
])

// ===== COMPUTED =====
const productId = computed(() => Number(route.params.id))
const product = computed(() => {
  return allProducts.value.find(p => p.id === productId.value)
})

const productFeatures = computed(() => {
  return product.value?.features || ['High Quality', 'Durable', 'Reliable']
})

const relatedProducts = computed(() => {
  if (!product.value) return []
  return allProducts.value.filter(p => 
    p.id !== product.value.id && p.category === product.value.category
  ).slice(0, 4)
})

// ===== CHECK WISHLIST STATUS =====
const checkWishlistStatus = () => {
  if (!product.value) return
  const wishlist = JSON.parse(localStorage.getItem('shopsphere_wishlist') || '[]')
  isWishlisted.value = wishlist.some(item => item.id === product.value.id)
}

// ===== METHODS =====
const goBack = () => {
  router.back()
}

const increaseQuantity = () => {
  quantity.value++
}

const decreaseQuantity = () => {
  if (quantity.value > 1) {
    quantity.value--
  }
}

const changeMainImage = (index) => {
  currentImage.value = index
}

const handleImageError = (e) => {
  e.target.style.display = 'none'
}

// ===== ADD TO CART =====
const addToCart = () => {
  let cart = JSON.parse(localStorage.getItem('shopsphere_cart') || '[]')
  const existingItem = cart.find(item => item.id === product.value.id)
  
  if (existingItem) {
    existingItem.quantity += quantity.value
  } else {
    cart.push({
      ...product.value,
      quantity: quantity.value,
      image: product.value.image || null
    })
  }
  
  localStorage.setItem('shopsphere_cart', JSON.stringify(cart))
  window.dispatchEvent(new Event('storage'))
  window.dispatchEvent(new CustomEvent('cart-updated'))
  
  const message = existingItem 
    ? `Added ${quantity.value} more "${product.value.name}" to cart!` 
    : `Added "${product.value.name}" (${quantity.value}) to cart!`
  alert(`🛒 ${message}`)
  
  quantity.value = 1
}

// ===== TOGGLE WISHLIST =====
const toggleWishlist = () => {
  isWishlisted.value = !isWishlisted.value
  
  let wishlist = JSON.parse(localStorage.getItem('shopsphere_wishlist') || '[]')
  
  if (isWishlisted.value) {
    const exists = wishlist.some(item => item.id === product.value.id)
    if (!exists) {
      wishlist.push({
        ...product.value,
        image: product.value.image || null
      })
      localStorage.setItem('shopsphere_wishlist', JSON.stringify(wishlist))
      alert('❤️ Added to wishlist!')
    }
  } else {
    wishlist = wishlist.filter(item => item.id !== product.value.id)
    localStorage.setItem('shopsphere_wishlist', JSON.stringify(wishlist))
    alert('💔 Removed from wishlist!')
  }
}

const handleAddToCart = (product) => {
  alert(`🛒 Added "${product.name}" to cart!`)
}

const handleWishlistToggle = ({ productId, isWishlisted }) => {
  const message = isWishlisted ? 'added to' : 'removed from'
  alert(`❤️ Product ${message} wishlist!`)
}

const handleViewProduct = (product) => {
  router.push(`/product/${product.id}`)
}

// ===== LIFECYCLE =====
onMounted(() => {
  setTimeout(() => {
    isLoading.value = false
    if (!product.value) {
      // Product not found
    } else {
      checkWishlistStatus()
    }
  }, 500)
})
</script>

<style scoped>
.product-detail-page {
  padding: 40px 0 80px;
  background: var(--bg-primary);
  min-height: 100vh;
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

/* ===== PRODUCT DETAIL GRID ===== */
.product-detail-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 60px;
  margin-bottom: 60px;
}

/* ===== IMAGE SECTION ===== */
.product-image-section {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.main-image {
  height: 500px;
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
  object-fit: cover;
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
  transform: scale(1.02);
}

/* ===== INFO SECTION ===== */
.product-info-section {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.product-vendor {
  font-size: 0.9rem;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 1px;
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
  padding: 20px 0;
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

/* ===== RESPONSIVE ===== */
@media (max-width: 1024px) {
  .products-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .product-detail-grid {
    grid-template-columns: 1fr;
    gap: 30px;
  }

  .main-image {
    height: 350px;
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
}

@media (max-width: 480px) {
  .product-detail-page {
    padding: 20px 0 60px;
  }

  .main-image {
    height: 250px;
  }

  .product-name {
    font-size: 1.5rem;
  }

  .current-price {
    font-size: 1.5rem;
  }

  .thumbnail {
    height: 60px;
  }

  .products-grid {
    grid-template-columns: 1fr 1fr;
    gap: 12px;
  }
}
</style>