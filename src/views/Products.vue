<template>
  <div class="products-page">
    <div class="container-custom">
      <!-- Page Header -->
      <div class="page-header">
        <h1 class="page-title">All Products</h1>
        <p class="page-subtitle">Discover our wide range of products from trusted vendors</p>
      </div>

      <!-- Filter & Search Section -->
      <div class="filter-section">
        <div class="filter-grid">
          <!-- Search -->
          <div class="search-box">
            <i class="bi bi-search"></i>
            <input 
              type="text" 
              v-model="searchQuery" 
              placeholder="Search products..."
              @input="applyFilters"
            />
          </div>

          <!-- Category Filter -->
          <div class="filter-group">
            <select v-model="selectedCategory" @change="applyFilters">
              <option value="">All Categories</option>
              <option v-for="category in categories" :key="category" :value="category">
                {{ category }}
              </option>
            </select>
          </div>

          <!-- Sort By -->
          <div class="filter-group">
            <select v-model="sortBy" @change="applyFilters">
              <option value="default">Sort By</option>
              <option value="price-low">Price: Low → High</option>
              <option value="price-high">Price: High → Low</option>
              <option value="newest">Newest First</option>
              <option value="rating">Highest Rated</option>
              <option value="popular">Best Selling</option>
            </select>
          </div>

          <!-- Price Range -->
          <div class="price-range">
            <span class="price-label">Price Range</span>
            <div class="price-inputs">
              <input 
                type="number" 
                v-model="minPrice" 
                placeholder="Min"
                @input="applyFilters"
              />
              <span class="price-separator">-</span>
              <input 
                type="number" 
                v-model="maxPrice" 
                placeholder="Max"
                @input="applyFilters"
              />
            </div>
          </div>
        </div>

        <!-- Filter Results Info -->
        <div class="filter-results">
          <span>Showing {{ filteredProducts.length }} products</span>
          <button v-if="hasActiveFilters" class="clear-filters" @click="clearFilters">
            <i class="bi bi-x-circle"></i> Clear All Filters
          </button>
        </div>
      </div>

      <!-- Products Grid -->
      <div v-if="filteredProducts.length > 0" class="products-grid">
        <ProductCard 
          v-for="product in filteredProducts" 
          :key="product.id"
          :product="product"
          @add-to-cart="handleAddToCart"
          @wishlist-toggle="handleWishlistToggle"
          @view-product="handleViewProduct"
        />
      </div>

      <!-- No Products Found -->
      <div v-else class="no-products">
        <div class="empty-state">
          <i class="bi bi-search" style="font-size: 4rem; color: var(--text-muted);"></i>
          <h3>No Products Found</h3>
          <p>Try adjusting your filters or search terms</p>
          <button class="btn-primary-modern" @click="clearFilters">Clear All Filters</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import ProductCard from '../components/ProductCard.vue'

const router = useRouter()
const route = useRoute()

// ===== FILTER STATE =====
const searchQuery = ref('')
const selectedCategory = ref('')
const sortBy = ref('default')
const minPrice = ref('')
const maxPrice = ref('')

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
    description: 'Premium wireless headphones with active noise cancellation.',
    stock: 'In Stock',
    features: ['Active Noise Cancellation', '30 Hour Battery', 'Bluetooth 5.0']
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
    image: 'https://images.unsplash.com/photo-1551028719-00167b16eac5?q=80&w=435&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
    isNew: false,
    discount: null,
    description: 'Genuine leather jacket with premium stitching.',
    stock: 'In Stock',
    features: ['Genuine Leather', 'Classic Design', 'Multiple Colors']
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
    image: 'https://images.unsplash.com/photo-1565452344518-47faca79dc69?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8NHx8Q29mZmVlJTIwTWFrZXJ8ZW58MHx8MHx8fDA%3D',
    isNew: true,
    discount: 19,
    description: 'Programmable coffee maker with smart features.',
    stock: 'In Stock',
    features: ['Programmable', 'Temperature Control', 'Smart Features']
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
    image: 'https://images.unsplash.com/photo-1660844817855-3ecc7ef21f12?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8NHx8Rml0bmVzcyUyMFNtYXJ0JTIwV2F0Y2h8ZW58MHx8MHx8fDA%3D',
    isNew: false,
    discount: 20,
    description: 'Advanced fitness tracker with heart rate monitor.',
    stock: 'In Stock',
    features: ['Heart Rate Monitor', 'GPS Tracking', 'Water Resistant']
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
    image: 'https://plus.unsplash.com/premium_photo-1673356302067-aac3b545a362?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8OXx8T3JnYW5pYyUyMENvdHRvbiUyMFQtU2hpcnR8ZW58MHx8MHx8fDA%3D',
    isNew: true,
    discount: null,
    description: 'Comfortable organic cotton t-shirt.',
    stock: 'In Stock',
    features: ['Organic Cotton', 'Multiple Colors', 'Comfortable Fit']
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
    image: 'https://images.unsplash.com/photo-1703332795377-65ccc6818232?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MXx8RS1SZWFkZXIlMjBQYXBlcndoaXRlfGVufDB8fDB8fHww',
    isNew: false,
    discount: 12,
    description: 'Waterproof e-reader with built-in light.',
    stock: 'In Stock',
    features: ['Waterproof', 'Built-in Light', 'Weeks of Battery']
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
    image: 'https://images.unsplash.com/photo-1674660346036-4b3df3f07cca?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8NHx8UHJvZmVzc2lvbmFsJTIwS25pZmUlMjBTZXR8ZW58MHx8MHx8fDA%3D',
    isNew: false,
    discount: null,
    description: 'Professional 5-piece kitchen knife set.',
    stock: 'In Stock',
    features: ['Stainless Steel', '5-Piece Set', 'Wooden Storage']
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
    image: 'https://images.unsplash.com/photo-1633381638729-27f730955c23?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8M3x8V2lyZWxlc3MlMjBDaGFyZ2luZyUyMFBhZHxlbnwwfHwwfHx8MA%3D%3D',
    isNew: true,
    discount: 40,
    description: 'Fast wireless charging pad for all devices.',
    stock: 'In Stock',
    features: ['Fast Charging', 'Qi-Compatible', 'Sleek Design']
  },
])

// ===== CATEGORIES =====
const categories = ref([
  'Electronics', 'Fashion', 'Home & Living'
])

// ===== FILTERED PRODUCTS =====
const filteredProducts = computed(() => {
  let products = [...allProducts.value]

  // Search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    products = products.filter(p => 
      p.name.toLowerCase().includes(query) ||
      p.vendor.toLowerCase().includes(query) ||
      p.category.toLowerCase().includes(query)
    )
  }

  // Category filter
  if (selectedCategory.value) {
    products = products.filter(p => p.category === selectedCategory.value)
  }

  // Price range filter
  if (minPrice.value) {
    products = products.filter(p => p.price >= Number(minPrice.value))
  }
  if (maxPrice.value) {
    products = products.filter(p => p.price <= Number(maxPrice.value))
  }

  // Sorting
  switch (sortBy.value) {
    case 'price-low':
      products.sort((a, b) => a.price - b.price)
      break
    case 'price-high':
      products.sort((a, b) => b.price - a.price)
      break
    case 'newest':
      products.sort((a, b) => (a.isNew === b.isNew) ? 0 : a.isNew ? -1 : 1)
      break
    case 'rating':
      products.sort((a, b) => b.rating - a.rating)
      break
    case 'popular':
      products.sort((a, b) => b.reviews - a.reviews)
      break
    default:
      break
  }

  return products
})

// ===== COMPUTED HELPERS =====
const hasActiveFilters = computed(() => {
  return searchQuery.value || 
         selectedCategory.value || 
         sortBy.value !== 'default' || 
         minPrice.value || 
         maxPrice.value
})

// ===== METHODS =====
const applyFilters = () => {}

const clearFilters = () => {
  searchQuery.value = ''
  selectedCategory.value = ''
  sortBy.value = 'default'
  minPrice.value = ''
  maxPrice.value = ''
}

// ===== EVENT HANDLERS =====
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

// ===== LIFECYCLE =====
onMounted(() => {
  if (route.query.search) {
    searchQuery.value = route.query.search
  }
  if (route.query.category) {
    selectedCategory.value = route.query.category
  }
})
</script>

<style scoped>
.products-page {
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

/* ===== FILTER SECTION ===== */
.filter-section {
  background: var(--bg-card);
  border-radius: var(--radius);
  padding: 24px;
  border: 1px solid var(--border-color);
  margin-bottom: 40px;
  box-shadow: var(--shadow);
}

.filter-grid {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1.5fr;
  gap: 16px;
  align-items: end;
}

.search-box {
  position: relative;
}

.search-box i {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-muted);
}

.search-box input {
  padding: 12px 16px 12px 44px;
  border: 1px solid var(--border-color);
  border-radius: var(--radius-sm);
  background: var(--bg-secondary);
  color: var(--text-primary);
  width: 100%;
  font-size: 1rem;
  transition: var(--transition);
}

.search-box input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.filter-group select {
  padding: 12px 16px;
  border: 1px solid var(--border-color);
  border-radius: var(--radius-sm);
  background: var(--bg-secondary);
  color: var(--text-primary);
  width: 100%;
  font-size: 1rem;
  cursor: pointer;
  transition: var(--transition);
}

.filter-group select:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.price-range {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.price-label {
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--text-secondary);
}

.price-inputs {
  display: flex;
  align-items: center;
  gap: 8px;
}

.price-inputs input {
  padding: 10px 12px;
  border: 1px solid var(--border-color);
  border-radius: var(--radius-sm);
  background: var(--bg-secondary);
  color: var(--text-primary);
  width: 100%;
  font-size: 0.95rem;
  transition: var(--transition);
}

.price-inputs input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.price-separator {
  color: var(--text-muted);
}

.filter-results {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 16px;
  padding-top: 16px;
  border-top: 1px solid var(--border-color);
}

.filter-results span {
  color: var(--text-secondary);
  font-size: 0.95rem;
}

.clear-filters {
  background: none;
  border: none;
  color: #ef4444;
  cursor: pointer;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  gap: 6px;
  transition: var(--transition);
}

.clear-filters:hover {
  color: #dc2626;
  transform: scale(1.02);
}

/* ===== PRODUCTS GRID ===== */
.products-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 24px;
}

/* ===== NO PRODUCTS ===== */
.no-products {
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
  .filter-grid {
    grid-template-columns: 1fr 1fr;
  }
  
  .products-grid {
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
  }
}

@media (max-width: 768px) {
  .products-page {
    padding: 20px 0 60px;
  }

  .page-title {
    font-size: 2rem;
  }

  .filter-grid {
    grid-template-columns: 1fr;
    gap: 12px;
  }

  .filter-section {
    padding: 16px;
  }

  .products-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
  }

  .filter-results {
    flex-direction: column;
    gap: 8px;
    text-align: center;
  }
}

@media (max-width: 480px) {
  .products-grid {
    grid-template-columns: 1fr 1fr;
    gap: 12px;
  }

  .page-title {
    font-size: 1.5rem;
  }

  .page-subtitle {
    font-size: 0.95rem;
  }

  .search-box input {
    padding: 10px 12px 10px 38px;
    font-size: 0.9rem;
  }

  .filter-group select {
    padding: 10px 12px;
    font-size: 0.9rem;
  }

  .price-inputs input {
    padding: 8px 10px;
    font-size: 0.85rem;
  }
}
</style>