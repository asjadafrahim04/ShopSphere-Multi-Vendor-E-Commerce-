<template>
  <div class="home-wrapper">
    <!-- ===== SWIPEABLE HERO BANNER ===== -->
    <section class="hero-banner-section">
      <div class="container-custom">
        <div class="hero-banner-wrapper">
          <div class="banner-container" @mouseenter="pauseAutoplay" @mouseleave="startAutoplay">
            <div 
              class="banner-track"
              :style="{ transform: `translateX(-${currentSlide * 100}%)` }"
            >
              <div 
                v-for="(slide, index) in banners" 
                :key="index"
                class="banner-slide"
              >
                <div class="banner-content" :style="{ backgroundImage: `url(${slide.image})` }">
                  <div class="banner-overlay"></div>
                  <div class="banner-left">
                    <div class="banner-tag">{{ slide.tag }}</div>
                    <h2 class="banner-title">{{ slide.title }}</h2>
                    <p class="banner-subtitle">{{ slide.subtitle }}</p>
                    <div class="banner-offer" v-if="slide.offer">
                      <span class="offer-badge">{{ slide.offer }}</span>
                    </div>
                    <button class="banner-btn" @click="goToProducts">
                      {{ slide.cta }} <i class="bi bi-arrow-right"></i>
                    </button>
                  </div>
                  <div class="banner-right"></div>
                </div>
              </div>
            </div>

            <button class="banner-arrow prev" @click="prevSlide">
              <i class="bi bi-chevron-left"></i>
            </button>
            <button class="banner-arrow next" @click="nextSlide">
              <i class="bi bi-chevron-right"></i>
            </button>

            <div class="banner-dots">
              <button 
                v-for="(slide, index) in banners" 
                :key="index"
                class="dot"
                :class="{ active: currentSlide === index }"
                @click="goToSlide(index)"
              ></button>
            </div>

            <div class="slide-counter">
              {{ currentSlide + 1 }} / {{ banners.length }}
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== CATEGORIES ===== -->
    <section class="section-modern">
      <div class="container-custom">
        <div class="section-header">
          <div>
            <span class="section-badge">Categories</span>
            <h2 class="section-title">Shop by Category</h2>
          </div>
          <button class="btn-outline-modern" @click="goToProducts">View All →</button>
        </div>
        <div class="categories-grid">
          <div 
            class="category-card-modern" 
            v-for="category in categories" 
            :key="category.id" 
            @click="filterByCategory(category.name)"
          >
            <div class="category-icon">
              <i :class="category.icon"></i>
            </div>
            <h5>{{ category.name }}</h5>
            <p>{{ category.count }} products</p>
            <span class="category-arrow">→</span>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== FEATURED PRODUCTS (REAL DATA FROM API) ===== -->
    <section class="section-modern section-featured">
      <div class="container-custom">
        <div class="section-header">
          <div>
            <span class="section-badge">Products</span>
            <h2 class="section-title">Featured Products</h2>
          </div>
          <button class="btn-outline-modern" @click="goToProducts">View All →</button>
        </div>
        
        <!-- Loading State -->
        <div v-if="loading" class="loading-grid">
          <div v-for="n in 4" :key="n" class="product-card-skeleton">
            <div class="skeleton-image"></div>
            <div class="skeleton-text"></div>
            <div class="skeleton-text short"></div>
          </div>
        </div>

        <!-- Products Grid -->
        <div v-else-if="featuredProducts.length > 0" class="products-grid">
          <ProductCard 
            v-for="product in featuredProducts" 
            :key="product.id"
            :product="product"
            @add-to-cart="handleAddToCart"
            @wishlist-toggle="handleWishlistToggle"
            @view-product="handleViewProduct"
          />
        </div>

        <!-- Empty State -->
        <div v-else class="empty-state">
          <span class="empty-icon">📦</span>
          <h3>No Products Available</h3>
          <p>Check back later for new products from our vendors.</p>
        </div>
      </div>
    </section>

    <!-- ===== TOP VENDORS ===== -->
    <section class="section-modern section-vendors">
      <div class="container-custom">
        <div class="section-header">
          <div>
            <span class="section-badge">Vendors</span>
            <h2 class="section-title">Top Vendors</h2>
            <p class="section-description">Shop from the most trusted sellers</p>
          </div>
          <button class="btn-outline-modern" @click="viewAllVendors">View All →</button>
        </div>
        <div class="vendors-grid">
          <div class="vendor-card" v-for="vendor in topVendors" :key="vendor.id">
            <div class="vendor-logo">
              <img 
                :src="vendor.logo" 
                :alt="vendor.name"
                loading="lazy"
              />
            </div>
            <h4 class="vendor-name">{{ vendor.name }}</h4>
            <p class="vendor-category">{{ vendor.category }}</p>
            <div class="vendor-stats">
              <span class="vendor-rating">
                <i class="bi bi-star-fill"></i>
                {{ vendor.rating }}
              </span>
              <span class="vendor-products">
                <i class="bi bi-box"></i>
                {{ vendor.products }}
              </span>
            </div>
            <button class="vendor-visit-btn" @click="visitVendor(vendor)">
              Visit Store <i class="bi bi-arrow-right"></i>
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== WHY CHOOSE US ===== -->
    <section class="section-modern section-why">
      <div class="container-custom">
        <div class="section-header text-center">
          <span class="section-badge">Why Choose Us</span>
          <h2 class="section-title">Why ShopSphere?</h2>
        </div>
        <div class="features-grid">
          <div class="feature-card-modern" v-for="feature in features" :key="feature.title">
            <div class="feature-icon">{{ feature.icon }}</div>
            <h5>{{ feature.title }}</h5>
            <p>{{ feature.description }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== CTA BANNER ===== -->
    <section class="cta-banner">
      <div class="container-custom">
        <div class="cta-content">
          <h2>Ready to Start Selling?</h2>
          <p>Join thousands of vendors and grow your business</p>
          <button class="btn-primary-modern" style="background: white; color: #667eea;" @click="goToVendor">
            <i class="bi bi-rocket me-2"></i>Get Started Today
          </button>
        </div>
      </div>
    </section>

    <!-- ===== WELCOME POPUP ===== -->
    <div v-if="showWelcomePopup" class="welcome-popup-overlay" @click.self="closePopup">
      <div class="welcome-popup">
        <button class="popup-close" @click="closePopup">
          <i class="bi bi-x-lg"></i>
        </button>
        
        <div class="popup-content">
          <div class="popup-icon">🎉</div>
          <h2>Welcome to ShopSphere!</h2>
          <p class="popup-subtitle">Bangladesh's premier multi-vendor marketplace</p>
          
          <div class="popup-offer">
            <div class="popup-offer-badge">🔥 Exclusive Offer</div>
            <div class="popup-offer-price">50% OFF</div>
            <p>On your first purchase</p>
            <div class="popup-offer-code">
              <span>FIRST50</span>
              <button class="copy-code-btn" @click="copyCode">
                <i class="bi bi-copy"></i>
              </button>
            </div>
          </div>

          <div class="popup-benefits">
            <div class="popup-benefit">
              <i class="bi bi-truck"></i>
              <span>Free Delivery</span>
            </div>
            <div class="popup-benefit">
              <i class="bi bi-shield-check"></i>
              <span>Secure Payment</span>
            </div>
            <div class="popup-benefit">
              <i class="bi bi-arrow-return-left"></i>
              <span>Easy Returns</span>
            </div>
          </div>

          <button class="btn-primary-modern popup-btn" @click="closePopup">
            <i class="bi bi-arrow-right me-2"></i>Start Shopping
          </button>
          
          <p class="popup-footer">Join 10,000+ happy customers</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import ProductCard from '../components/ProductCard.vue'

const router = useRouter()
const searchQuery = ref('')
const showWelcomePopup = ref(false)
const currentSlide = ref(0)
const autoplayInterval = ref(null)

// ===== PRODUCTS FROM API =====
const allProducts = ref([])
const loading = ref(true)

// ===== FETCH PRODUCTS FROM API =====
const fetchProducts = async () => {
  loading.value = true
  try {
    const response = await fetch('http://localhost:8000/api/products')
    const data = await response.json()
    
    console.log('✅ API Response:', data)
    
    if (data.success) {
      allProducts.value = data.data.data || []
      console.log('✅ Products loaded:', allProducts.value.length)
    } else {
      console.error('❌ API error:', data)
    }
  } catch (error) {
    console.error('❌ Error fetching products:', error)
  } finally {
    loading.value = false
  }
}

// ===== COMPUTED: Featured Products (First 4) =====
const featuredProducts = computed(() => {
  return allProducts.value.slice(0, 4)
})

// ===== COMPUTED: Total Products Count =====
const totalProducts = computed(() => {
  return allProducts.value.length
})

// ===== BANNER DATA =====
const banners = ref([
  {
    id: 1,
    tag: 'Flash Sale',
    title: 'Up to 50% Off',
    subtitle: 'On Electronics & Gadgets',
    offer: 'Limited Time Offer',
    cta: 'Shop Now',
    image: 'https://images.unsplash.com/photo-1498049794561-7780e7231661?w=1200&h=400&fit=crop&crop=center',
  },
  {
    id: 2,
    tag: 'Fashion Week',
    title: 'Premium Collection',
    subtitle: 'Latest trends in fashion',
    offer: 'Up to 40% Off',
    cta: 'Explore Fashion',
    image: 'https://images.unsplash.com/photo-1445205170230-053b83016050?w=1200&h=400&fit=crop&crop=center',
  },
  {
    id: 3,
    tag: 'Home & Living',
    title: 'Make Your Home',
    subtitle: 'Beautiful with our collection',
    offer: 'Up to 30% Off',
    cta: 'Shop Home',
    image: 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=1200&h=400&fit=crop&crop=center',
  },
  {
    id: 4,
    tag: 'Tech Deals',
    title: 'Smartphone Sale',
    subtitle: 'Latest models at best prices',
    offer: 'Up to 35% Off',
    cta: 'View Deals',
    image: 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=1200&h=400&fit=crop&crop=center',
  },
])

// ===== TOP VENDORS DATA =====
const topVendors = ref([
  {
    id: 1,
    name: 'TechHub Pro',
    category: 'Electronics',
    rating: 4.9,
    products: 256,
    logo: 'https://ui-avatars.com/api/?name=TechHub+Pro&background=667eea&color=fff&size=128&bold=true',
    email: 'techhubpro@shopsphere.com',
    store: 'https://shopsphere.com/techhubpro'
  },
  {
    id: 2,
    name: 'Fashion Forward',
    category: 'Fashion',
    rating: 4.8,
    products: 189,
    logo: 'https://ui-avatars.com/api/?name=Fashion+Forward&background=ec4899&color=fff&size=128&bold=true',
    email: 'fashionforward@shopsphere.com',
    store: 'https://shopsphere.com/fashionforward'
  },
  {
    id: 3,
    name: 'Home & Living BD',
    category: 'Home & Living',
    rating: 4.7,
    products: 134,
    logo: 'https://ui-avatars.com/api/?name=Home+%26+Living+BD&background=10b981&color=fff&size=128&bold=true',
    email: 'homeandliving@shopsphere.com',
    store: 'https://shopsphere.com/homeandliving'
  },
  {
    id: 4,
    name: 'Sports Gear BD',
    category: 'Sports & Fitness',
    rating: 4.6,
    products: 98,
    logo: 'https://ui-avatars.com/api/?name=Sports+Gear+BD&background=f59e0b&color=fff&size=128&bold=true',
    email: 'sportsgear@shopsphere.com',
    store: 'https://shopsphere.com/sportsgear'
  },
  {
    id: 5,
    name: 'Beauty & Care',
    category: 'Beauty & Personal Care',
    rating: 4.9,
    products: 167,
    logo: 'https://ui-avatars.com/api/?name=Beauty+%26+Care&background=ec4899&color=fff&size=128&bold=true',
    email: 'beautycare@shopsphere.com',
    store: 'https://shopsphere.com/beautycare'
  },
])

// ===== CATEGORIES DATA =====
const categories = ref([
  { id: 1, name: 'Electronics', icon: 'bi bi-laptop', count: 0 },
  { id: 2, name: 'Fashion', icon: 'bi bi-bag', count: 0 },
  { id: 3, name: 'Home & Living', icon: 'bi bi-house', count: 0 },
  { id: 4, name: 'Beauty', icon: 'bi bi-flower1', count: 0 },
  { id: 5, name: 'Sports', icon: 'bi bi-bicycle', count: 0 },
  { id: 6, name: 'Books', icon: 'bi bi-book', count: 0 },
  { id: 7, name: 'Toys & Kids', icon: 'bi bi-toy', count: 0 },
  { id: 8, name: 'Food & Grocery', icon: 'bi bi-cup-straw', count: 0 },
])

// ===== FEATURES DATA =====
const features = [
  { icon: '🛒', title: 'Wide Selection', description: 'Thousands of products from trusted vendors worldwide' },
  { icon: '💳', title: 'Easy Payments', description: 'Multiple payment options including bKash, Nagad, and COD' },
  { icon: '🔒', title: 'Secure Shopping', description: 'Your data and payments are always protected with us' },
  { icon: '🚀', title: 'Fast Delivery', description: 'Quick shipping to your doorstep with real-time tracking' },
  { icon: '🔄', title: 'Easy Returns', description: 'Hassle-free returns within 7 days of delivery' },
  { icon: '🎯', title: 'Best Prices', description: 'Competitive prices with exclusive deals and discounts' },
]

// ===== BANNER FUNCTIONS =====
const nextSlide = () => {
  currentSlide.value = (currentSlide.value + 1) % banners.value.length
}

const prevSlide = () => {
  currentSlide.value = (currentSlide.value - 1 + banners.value.length) % banners.value.length
}

const goToSlide = (index) => {
  currentSlide.value = index
}

const startAutoplay = () => {
  if (autoplayInterval.value) return
  autoplayInterval.value = setInterval(() => {
    nextSlide()
  }, 5000)
}

const pauseAutoplay = () => {
  if (autoplayInterval.value) {
    clearInterval(autoplayInterval.value)
    autoplayInterval.value = null
  }
}

// ===== POPUP FUNCTIONS =====
const checkAndShowPopup = () => {
  const hasVisited = localStorage.getItem('shopsphere_visited')
  if (!hasVisited) {
    setTimeout(() => {
      showWelcomePopup.value = true
    }, 800)
    localStorage.setItem('shopsphere_visited', 'true')
  }
}

const closePopup = () => {
  showWelcomePopup.value = false
}

const copyCode = () => {
  navigator.clipboard.writeText('FIRST50')
  alert('📋 Code "FIRST50" copied to clipboard!')
}

// ===== VENDOR FUNCTIONS =====
const viewAllVendors = () => {
  alert('🏪 View all vendors coming soon!')
}

const visitVendor = (vendor) => {
  alert(`🏪 Visit ${vendor.name} store\n📧 Email: ${vendor.email}\n🔗 Store: ${vendor.store}`)
}

// ===== NAVIGATION FUNCTIONS =====
const goToProducts = () => {
  router.push('/products')
}

const filterByCategory = (category) => {
  if (category === 'All') {
    router.push('/products')
  } else {
    router.push({
      path: '/products',
      query: { category: category }
    })
  }
}

const goToVendor = () => {
  router.push('/vendor/register')
}

// ===== PRODUCT EVENT HANDLERS =====
const handleAddToCart = (product) => {
  console.log('Added to cart:', product)
  alert(`🛒 Added "${product.name}" to cart!`)
}

const handleWishlistToggle = ({ productId, isWishlisted }) => {
  console.log('Wishlist toggled:', productId, isWishlisted)
  const message = isWishlisted ? 'added to' : 'removed from'
  alert(`❤️ Product ${message} wishlist!`)
}

const handleViewProduct = (product) => {
  console.log('View product:', product)
  router.push(`/product/${product.id}`)
}

// ===== ✅ FIX: REDIRECT VENDORS AWAY FROM HOMEPAGE =====
const checkVendorRedirect = () => {
  const token = localStorage.getItem('token')
  const user = JSON.parse(localStorage.getItem('user') || 'null')
  
  if (token && user?.role === 'vendor') {
    console.log('🔀 Vendor detected on homepage. Redirecting to dashboard...')
    router.push('/vendor/dashboard')
    return true
  }
  return false
}

// ===== ✅ FIX: WATCH FOR AUTH CHANGES =====
watch(() => localStorage.getItem('user'), () => {
  checkVendorRedirect()
})

// ===== LIFECYCLE =====
onMounted(() => {
  // ✅ Check for vendor redirect first
  if (checkVendorRedirect()) {
    return // Stop loading homepage
  }
  
  checkAndShowPopup()
  startAutoplay()
  fetchProducts()
})

onUnmounted(() => {
  pauseAutoplay()
})
</script>

<style scoped>
/* ===== ALL YOUR EXISTING STYLES REMAIN THE SAME ===== */
.home-wrapper {
  width: 100%;
  max-width: 100%;
  overflow-x: hidden;
}

/* ===== LOADING SKELETON ===== */
.loading-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 24px;
}

.product-card-skeleton {
  background: var(--bg-card);
  border-radius: var(--radius);
  padding: 16px;
  border: 1px solid var(--border-color);
}

.skeleton-image {
  width: 100%;
  height: 200px;
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
  border-radius: 8px;
}

.skeleton-text {
  height: 16px;
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
  border-radius: 4px;
  margin-top: 12px;
}

.skeleton-text.short {
  width: 60%;
}

@keyframes shimmer {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

/* ===== EMPTY STATE ===== */
.empty-state {
  text-align: center;
  padding: 60px 20px;
  background: var(--bg-card);
  border-radius: var(--radius);
  border: 1px solid var(--border-color);
}

.empty-icon {
  font-size: 64px;
  display: block;
  margin-bottom: 16px;
}

.empty-state h3 {
  font-size: 20px;
  color: var(--text-primary);
  margin-bottom: 8px;
}

.empty-state p {
  color: var(--text-muted);
}

/* ===== HERO BANNER ===== */
.hero-banner-section {
  padding: 20px 0 10px;
  background: var(--bg-primary);
}

.hero-banner-wrapper {
  position: relative;
  overflow: hidden;
  border-radius: var(--radius);
  box-shadow: var(--shadow);
}

.banner-container {
  position: relative;
  overflow: hidden;
  border-radius: var(--radius);
}

.banner-track {
  display: flex;
  transition: transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
  will-change: transform;
}

.banner-slide {
  min-width: 100%;
  flex-shrink: 0;
}

.banner-content {
  position: relative;
  display: grid;
  grid-template-columns: 1.2fr 0.8fr;
  gap: 30px;
  padding: 50px 60px;
  min-height: 340px;
  align-items: center;
  background-size: cover;
  background-position: center;
  overflow: hidden;
}

.banner-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(90deg, rgba(0, 0, 0, 0.75) 0%, rgba(0, 0, 0, 0.3) 60%, rgba(0, 0, 0, 0.05) 100%);
  z-index: 1;
}

.banner-left {
  display: flex;
  flex-direction: column;
  gap: 12px;
  z-index: 2;
  position: relative;
}

.banner-tag {
  display: inline-block;
  padding: 4px 16px;
  background: rgba(255, 255, 255, 0.12);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 50px;
  color: #fbbf24;
  font-size: 12px;
  font-weight: 600;
  width: fit-content;
  letter-spacing: 0.5px;
}

.banner-title {
  font-size: 3rem;
  font-weight: 800;
  color: #ffffff;
  line-height: 1.1;
  margin: 0;
  text-shadow: 0 2px 15px rgba(0, 0, 0, 0.4);
}

.banner-subtitle {
  font-size: 1.1rem;
  color: rgba(255, 255, 255, 0.85);
  margin: 0;
  text-shadow: 0 1px 10px rgba(0, 0, 0, 0.3);
}

.banner-offer {
  margin: 4px 0;
}

.offer-badge {
  display: inline-block;
  padding: 6px 20px;
  background: rgba(239, 68, 68, 0.2);
  border: 1px solid rgba(239, 68, 68, 0.25);
  border-radius: 50px;
  color: #f87171;
  font-size: 14px;
  font-weight: 600;
  backdrop-filter: blur(10px);
}

.banner-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 28px;
  background: white;
  border: none;
  border-radius: 50px;
  color: #1a1a2e;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  transition: var(--transition);
  width: fit-content;
}

.banner-btn:hover {
  transform: translateX(5px);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
}

.banner-btn i {
  transition: var(--transition);
}

.banner-btn:hover i {
  transform: translateX(4px);
}

.banner-right {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  position: relative;
  z-index: 2;
}

.banner-arrow {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  width: 44px;
  height: 44px;
  border-radius: 50%;
  border: none;
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(10px);
  color: white;
  font-size: 1.3rem;
  cursor: pointer;
  transition: var(--transition);
  z-index: 3;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
}

.banner-container:hover .banner-arrow {
  opacity: 1;
}

.banner-arrow:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: translateY(-50%) scale(1.05);
}

.banner-arrow.prev {
  left: 16px;
}

.banner-arrow.next {
  right: 16px;
}

.banner-dots {
  position: absolute;
  bottom: 16px;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  gap: 8px;
  z-index: 3;
}

.dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  border: none;
  background: rgba(255, 255, 255, 0.4);
  cursor: pointer;
  transition: var(--transition);
  padding: 0;
}

.dot.active {
  background: white;
  width: 28px;
  border-radius: 5px;
}

.dot:hover {
  background: rgba(255, 255, 255, 0.7);
}

.slide-counter {
  position: absolute;
  bottom: 16px;
  right: 20px;
  color: rgba(255, 255, 255, 0.7);
  font-size: 12px;
  font-weight: 500;
  z-index: 3;
  background: rgba(0, 0, 0, 0.3);
  padding: 3px 14px;
  border-radius: 50px;
  backdrop-filter: blur(10px);
}

/* ===== SECTIONS ===== */
.section-modern {
  padding: 80px 0;
}

.section-featured {
  background: #f8f9fc;
}

.section-vendors {
  background: #ffffff;
}

.section-why {
  background: #f8f9fc;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 40px;
  flex-wrap: wrap;
  gap: 16px;
}

.section-header.text-center {
  justify-content: center;
  text-align: center;
}

.section-badge {
  display: inline-block;
  padding: 4px 16px;
  background: rgba(102, 126, 234, 0.1);
  color: #667eea;
  border-radius: 50px;
  font-size: 13px;
  font-weight: 600;
  margin-bottom: 8px;
}

.section-title {
  font-size: 2.5rem;
  font-weight: 700;
  color: var(--text-primary);
  margin: 0;
}

.section-description {
  color: var(--text-muted);
  font-size: 1rem;
  margin-top: 4px;
}

/* ===== CATEGORIES ===== */
.categories-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 24px;
}

.category-card-modern {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: var(--radius);
  padding: 32px 24px;
  text-align: center;
  transition: var(--transition);
  cursor: pointer;
  position: relative;
  overflow: hidden;
}

.category-card-modern:hover {
  transform: translateY(-8px);
  box-shadow: var(--shadow-hover);
  border-color: #667eea;
}

.category-icon {
  font-size: 2.5rem;
  margin-bottom: 16px;
}

.category-card-modern h5 {
  font-size: 1.1rem;
  font-weight: 600;
  color: var(--text-primary);
  margin-bottom: 4px;
}

.category-card-modern p {
  color: var(--text-muted);
  font-size: 0.9rem;
}

.category-arrow {
  position: absolute;
  top: 16px;
  right: 16px;
  font-size: 1.2rem;
  opacity: 0;
  transition: var(--transition);
  color: #667eea;
}

.category-card-modern:hover .category-arrow {
  opacity: 1;
  transform: translateX(4px);
}

/* ===== PRODUCTS ===== */
.products-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 24px;
}

/* ===== VENDORS ===== */
.vendors-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 24px;
}

.vendor-card {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: var(--radius);
  padding: 24px 20px;
  text-align: center;
  transition: var(--transition);
}

.vendor-card:hover {
  transform: translateY(-6px);
  box-shadow: var(--shadow-hover);
  border-color: #667eea;
}

.vendor-logo {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  margin: 0 auto 12px;
  overflow: hidden;
  border: 3px solid var(--border-color);
  transition: var(--transition);
  background: var(--bg-secondary);
}

.vendor-card:hover .vendor-logo {
  border-color: #667eea;
}

.vendor-logo img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.vendor-name {
  font-size: 1rem;
  font-weight: 600;
  color: var(--text-primary);
  margin: 0 0 4px;
}

.vendor-category {
  font-size: 0.8rem;
  color: var(--text-muted);
  margin: 0 0 10px;
}

.vendor-stats {
  display: flex;
  justify-content: center;
  gap: 16px;
  margin-bottom: 12px;
}

.vendor-rating,
.vendor-products {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 0.8rem;
  color: var(--text-secondary);
}

.vendor-rating i {
  color: #f59e0b;
  font-size: 0.75rem;
}

.vendor-products i {
  color: #667eea;
  font-size: 0.75rem;
}

.vendor-visit-btn {
  width: 100%;
  padding: 8px 16px;
  background: var(--bg-secondary);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-sm);
  color: var(--text-primary);
  font-weight: 500;
  font-size: 0.85rem;
  cursor: pointer;
  transition: var(--transition);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
}

.vendor-visit-btn:hover {
  background: var(--gradient-primary);
  color: white;
  border-color: transparent;
}

.vendor-visit-btn i {
  transition: var(--transition);
}

.vendor-visit-btn:hover i {
  transform: translateX(3px);
}

/* ===== FEATURES ===== */
.features-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 24px;
}

.feature-card-modern {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: var(--radius);
  padding: 32px 24px;
  text-align: center;
  transition: var(--transition);
}

.feature-card-modern:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-hover);
}

.feature-icon {
  font-size: 3rem;
  margin-bottom: 16px;
}

.feature-card-modern h5 {
  font-size: 1.1rem;
  font-weight: 600;
  color: var(--text-primary);
  margin-bottom: 8px;
}

.feature-card-modern p {
  color: var(--text-muted);
  font-size: 0.95rem;
  line-height: 1.6;
}

/* ===== CTA BANNER ===== */
.cta-banner {
  background: var(--gradient-primary);
  padding: 80px 0;
  position: relative;
  overflow: hidden;
}

.cta-banner::before {
  content: '';
  position: absolute;
  top: -50%;
  right: -20%;
  width: 500px;
  height: 500px;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 50%;
}

.cta-content {
  text-align: center;
  color: white;
  position: relative;
  z-index: 1;
}

.cta-content h2 {
  font-size: 3rem;
  font-weight: 700;
  margin-bottom: 12px;
}

.cta-content p {
  font-size: 1.2rem;
  opacity: 0.9;
  margin-bottom: 24px;
}

.cta-content .btn-primary-modern {
  background: white;
  color: #667eea;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
}

.cta-content .btn-primary-modern:hover {
  transform: translateY(-2px) scale(1.02);
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
}

/* ===== WELCOME POPUP ===== */
.welcome-popup-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.7);
  backdrop-filter: blur(8px);
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  animation: fadeIn 0.4s ease;
}

.welcome-popup {
  background: var(--bg-card);
  border-radius: var(--radius);
  max-width: 500px;
  width: 90%;
  padding: 40px;
  position: relative;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 50px 100px rgba(0, 0, 0, 0.5);
  animation: slideUp 0.4s ease;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes slideUp {
  from { transform: translateY(50px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}

.popup-close {
  position: absolute;
  top: 16px;
  right: 16px;
  background: none;
  border: none;
  color: var(--text-muted);
  font-size: 1.2rem;
  cursor: pointer;
  transition: var(--transition);
  padding: 8px;
  border-radius: 50%;
}

.popup-close:hover {
  background: var(--bg-secondary);
  color: var(--text-primary);
}

.popup-content {
  text-align: center;
}

.popup-icon {
  font-size: 4rem;
  margin-bottom: 16px;
}

.popup-content h2 {
  font-size: 2rem;
  font-weight: 700;
  color: var(--text-primary);
  margin-bottom: 8px;
}

.popup-subtitle {
  color: var(--text-secondary);
  font-size: 1.05rem;
  margin-bottom: 24px;
}

.popup-offer {
  background: var(--gradient-primary);
  padding: 24px;
  border-radius: var(--radius-sm);
  color: white;
  margin-bottom: 24px;
}

.popup-offer-badge {
  display: inline-block;
  padding: 4px 16px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 50px;
  font-size: 13px;
  font-weight: 600;
  margin-bottom: 8px;
}

.popup-offer-price {
  font-size: 3rem;
  font-weight: 800;
  margin: 8px 0;
}

.popup-offer-code {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  background: rgba(255, 255, 255, 0.15);
  padding: 12px 20px;
  border-radius: 12px;
  margin-top: 12px;
  font-weight: 700;
  font-size: 1.2rem;
  letter-spacing: 4px;
}

.copy-code-btn {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  color: white;
  padding: 4px 12px;
  border-radius: 6px;
  cursor: pointer;
  transition: var(--transition);
  font-size: 1rem;
}

.copy-code-btn:hover {
  background: rgba(255, 255, 255, 0.3);
}

.popup-benefits {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 12px;
  margin-bottom: 24px;
}

.popup-benefit {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  padding: 12px;
  background: var(--bg-secondary);
  border-radius: var(--radius-sm);
}

.popup-benefit i {
  font-size: 1.5rem;
  color: #667eea;
}

.popup-benefit span {
  font-size: 0.8rem;
  color: var(--text-secondary);
  font-weight: 500;
}

.popup-btn {
  width: 100%;
  text-align: center;
  padding: 14px;
  font-size: 1.05rem;
}

.popup-footer {
  margin-top: 16px;
  color: var(--text-muted);
  font-size: 0.9rem;
}

/* ===== DARK MODE ===== */
html.dark .section-featured {
  background: #1a1932;
}

html.dark .section-vendors {
  background: #0f0e17;
}

html.dark .section-why {
  background: #1a1932;
}

html.light .section-featured {
  background: #f8f9fc;
}

html.light .section-vendors {
  background: #ffffff;
}

html.light .section-why {
  background: #f8f9fc;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 1024px) {
  .banner-content {
    padding: 40px;
    min-height: 280px;
  }
  
  .banner-title {
    font-size: 2.5rem;
  }
  
  .categories-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .vendors-grid {
    grid-template-columns: repeat(3, 1fr);
  }
  
  .products-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .features-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .loading-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .hero-banner-section {
    padding: 12px 0 6px;
  }
  
  .banner-content {
    grid-template-columns: 1fr;
    padding: 30px 24px;
    min-height: 280px;
    text-align: center;
  }
  
  .banner-overlay {
    background: linear-gradient(0deg, rgba(0, 0, 0, 0.8) 0%, rgba(0, 0, 0, 0.4) 50%, rgba(0, 0, 0, 0.2) 100%);
  }
  
  .banner-left {
    align-items: center;
  }
  
  .banner-tag {
    margin: 0 auto;
  }
  
  .banner-title {
    font-size: 2rem;
  }
  
  .banner-subtitle {
    font-size: 1rem;
  }
  
  .banner-btn {
    margin: 0 auto;
  }
  
  .banner-arrow {
    width: 36px;
    height: 36px;
    font-size: 1rem;
  }
  
  .banner-arrow.prev {
    left: 8px;
  }
  
  .banner-arrow.next {
    right: 8px;
  }
  
  .banner-container:hover .banner-arrow {
    opacity: 1;
  }
  
  .section-title {
    font-size: 2rem;
  }
  
  .categories-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
  }
  
  .vendors-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
  }
  
  .products-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
  }
  
  .features-grid {
    grid-template-columns: 1fr;
  }
  
  .cta-content h2 {
    font-size: 2rem;
  }
  
  .section-modern {
    padding: 50px 0;
  }
  
  .welcome-popup {
    padding: 30px 20px;
  }
  
  .popup-content h2 {
    font-size: 1.5rem;
  }
  
  .popup-offer-price {
    font-size: 2.5rem;
  }
  
  .popup-benefits {
    grid-template-columns: 1fr;
  }
  
  .loading-grid {
    grid-template-columns: 1fr 1fr;
    gap: 16px;
  }
}

@media (max-width: 480px) {
  .banner-content {
    padding: 20px 16px;
    min-height: 240px;
  }
  
  .banner-title {
    font-size: 1.6rem;
  }
  
  .banner-subtitle {
    font-size: 0.9rem;
  }
  
  .banner-btn {
    padding: 10px 20px;
    font-size: 13px;
  }
  
  .banner-arrow {
    width: 30px;
    height: 30px;
    font-size: 0.8rem;
  }
  
  .dot {
    width: 8px;
    height: 8px;
  }
  
  .dot.active {
    width: 20px;
  }
  
  .products-grid {
    grid-template-columns: 1fr 1fr;
    gap: 12px;
  }
  
  .categories-grid {
    grid-template-columns: 1fr 1fr;
    gap: 12px;
  }
  
  .vendors-grid {
    grid-template-columns: 1fr 1fr;
    gap: 12px;
  }
  
  .loading-grid {
    grid-template-columns: 1fr 1fr;
    gap: 12px;
  }
  
  .slide-counter {
    display: none;
  }
  
  .banner-offer .offer-badge {
    font-size: 12px;
    padding: 4px 14px;
  }
  
  .vendor-logo {
    width: 60px;
    height: 60px;
  }
  
  .vendor-name {
    font-size: 0.9rem;
  }
  
  .vendor-stats {
    flex-direction: column;
    gap: 4px;
  }
}
</style>