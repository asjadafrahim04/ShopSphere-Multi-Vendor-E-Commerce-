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
              v-model="filters.search" 
              placeholder="Search products..."
              @input="applyFilters"
            />
          </div>

          <!-- Category Filter -->
          <div class="filter-group">
            <select v-model="filters.category_id" @change="applyFilters">
              <option value="">All Categories</option>
              <option v-for="category in categories" :key="category.id" :value="category.id">
                {{ category.name }}
              </option>
            </select>
          </div>

          <!-- Sort By -->
          <div class="filter-group">
            <select v-model="filters.sort" @change="applyFilters">
              <option value="newest">Newest First</option>
              <option value="price-low">Price: Low → High</option>
              <option value="price-high">Price: High → Low</option>
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
                v-model="filters.min_price" 
                placeholder="Min"
                @input="applyFilters"
              />
              <span class="price-separator">-</span>
              <input 
                type="number" 
                v-model="filters.max_price" 
                placeholder="Max"
                @input="applyFilters"
              />
            </div>
          </div>
        </div>

        <!-- Filter Results Info -->
        <div class="filter-results">
          <span>Showing {{ products.length }} products</span>
          <button v-if="hasActiveFilters" class="clear-filters" @click="clearFilters">
            <i class="bi bi-x-circle"></i> Clear All Filters
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p>Loading products...</p>
      </div>

      <!-- Products Grid -->
      <div v-else-if="products.length > 0" class="products-grid">
        <ProductCard 
          v-for="product in products" 
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

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="pagination-section">
        <button 
          class="pagination-btn" 
          :disabled="pagination.current_page <= 1"
          @click="goToPage(pagination.current_page - 1)"
        >
          <i class="bi bi-chevron-left"></i>
        </button>
        <span class="pagination-info">
          Page {{ pagination.current_page }} of {{ pagination.last_page }}
        </span>
        <button 
          class="pagination-btn" 
          :disabled="pagination.current_page >= pagination.last_page"
          @click="goToPage(pagination.current_page + 1)"
        >
          <i class="bi bi-chevron-right"></i>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { productApi, categoryApi } from '@/services/api'
import ProductCard from '@/components/ProductCard.vue'

const router = useRouter()
const route = useRoute()

// ===== STATE =====
const products = ref([])
const categories = ref([])
const loading = ref(true)
const error = ref(null)

// ===== FILTERS =====
const filters = ref({
  search: '',
  category_id: '',
  sort: 'newest',
  min_price: '',
  max_price: '',
})

// ===== PAGINATION =====
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 0,
})

// ===== COMPUTED =====
const hasActiveFilters = computed(() => {
  return filters.value.search || 
         filters.value.category_id || 
         filters.value.sort !== 'newest' || 
         filters.value.min_price || 
         filters.value.max_price
})

// ===== METHODS =====
const loadCategories = async () => {
  try {
    const response = await categoryApi.getCategories()
    if (response.data.success) {
      categories.value = response.data.data
    }
  } catch (error) {
    console.error('Error loading categories:', error)
  }
}

const loadProducts = async () => {
  loading.value = true
  error.value = null

  try {
    // Build query parameters
    const params = {
      page: pagination.value.current_page,
      per_page: 20,
    }

    if (filters.value.search) {
      params.search = filters.value.search
    }
    if (filters.value.category_id) {
      params.category_id = filters.value.category_id
    }
    if (filters.value.sort) {
      params.sort = filters.value.sort
    }
    if (filters.value.min_price) {
      params.min_price = filters.value.min_price
    }
    if (filters.value.max_price) {
      params.max_price = filters.value.max_price
    }

    console.log('Fetching products with params:', params)

    const response = await productApi.getProducts(params)
    
    if (response.data.success) {
      const productData = response.data.data
      products.value = productData.data || []
      pagination.value = {
        current_page: productData.current_page || 1,
        last_page: productData.last_page || 1,
        per_page: productData.per_page || 20,
        total: productData.total || 0,
      }
    }
  } catch (error) {
    console.error('Error loading products:', error)
    error.value = 'Failed to load products. Please try again.'
    products.value = []
  } finally {
    loading.value = false
  }
}

const applyFilters = () => {
  pagination.value.current_page = 1
  loadProducts()
  updateUrl()
}

const clearFilters = () => {
  filters.value = {
    search: '',
    category_id: '',
    sort: 'newest',
    min_price: '',
    max_price: '',
  }
  pagination.value.current_page = 1
  loadProducts()
  updateUrl()
}

const goToPage = (page) => {
  if (page < 1 || page > pagination.value.last_page) return
  pagination.value.current_page = page
  loadProducts()
  updateUrl()
}

const updateUrl = () => {
  const query = {}
  if (filters.value.search) query.search = filters.value.search
  if (filters.value.category_id) query.category = filters.value.category_id
  if (filters.value.sort !== 'newest') query.sort = filters.value.sort
  if (filters.value.min_price) query.min_price = filters.value.min_price
  if (filters.value.max_price) query.max_price = filters.value.max_price
  if (pagination.value.current_page > 1) query.page = pagination.value.current_page
  
  router.replace({ query })
}

const handleAddToCart = (product) => {
  console.log('Added to cart:', product)
}

const handleWishlistToggle = ({ productId, isWishlisted }) => {
  console.log('Wishlist toggled:', productId, isWishlisted)
}

const handleViewProduct = (product) => {
  router.push(`/product/${product.id}`)
}

// ===== WATCHERS =====
watch(() => route.query, (newQuery) => {
  // Sync filters from URL
  if (newQuery.search) filters.value.search = newQuery.search
  if (newQuery.category) filters.value.category_id = newQuery.category
  if (newQuery.sort) filters.value.sort = newQuery.sort
  if (newQuery.min_price) filters.value.min_price = newQuery.min_price
  if (newQuery.max_price) filters.value.max_price = newQuery.max_price
  if (newQuery.page) pagination.value.current_page = parseInt(newQuery.page)
  
  loadProducts()
}, { immediate: false })

// ===== LIFECYCLE =====
onMounted(() => {
  // Load categories first
  loadCategories()
  
  // Load products
  loadProducts()
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

/* ===== PAGINATION ===== */
.pagination-section {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 16px;
  margin-top: 40px;
}

.pagination-btn {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  border: 1px solid var(--border-color);
  background: var(--bg-card);
  color: var(--text-primary);
  cursor: pointer;
  transition: var(--transition);
  display: flex;
  align-items: center;
  justify-content: center;
}

.pagination-btn:hover:not(:disabled) {
  background: var(--gradient-primary);
  color: white;
  border-color: transparent;
}

.pagination-btn:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

.pagination-info {
  color: var(--text-secondary);
  font-size: 0.95rem;
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

  .price-inputs {
    flex-wrap: nowrap;
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