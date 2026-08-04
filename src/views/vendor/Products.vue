<template>
  <div class="vendor-products">
    <!-- Header -->
    <div class="page-header">
      <div>
        <h1>My Products</h1>
        <p class="text-muted">Manage your product inventory</p>
      </div>
      <button class="btn btn-primary" @click="showAddModal = true">
        <span>+</span> Add Product
      </button>
    </div>

    <!-- Products Grid -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Loading products...</p>
    </div>

    <div v-else-if="products.length === 0" class="empty-state">
      <span>📦</span>
      <h3>No Products Yet</h3>
      <p>Start by adding your first product to your store.</p>
      <button class="btn btn-primary" @click="showAddModal = true">
        Add Your First Product
      </button>
    </div>

    <div v-else class="products-grid">
      <div v-for="product in products" :key="product.id" class="product-card">
        <div class="product-image">
          <img 
            v-if="product.images && product.images.length > 0" 
            :src="'http://localhost:8000/storage/' + product.images[0].image_path"
            :alt="product.name"
          />
          <div v-else class="no-image">📦</div>
          <span class="product-status" :class="{ active: product.is_active, inactive: !product.is_active }">
            {{ product.is_active ? 'Active' : 'Inactive' }}
          </span>
        </div>
        <div class="product-info">
          <h3>{{ product.name }}</h3>
          <p class="product-description">{{ product.description?.substring(0, 60) }}...</p>
          <div class="product-meta">
            <span class="product-price">${{ product.price }}</span>
            <span class="product-stock">Stock: {{ product.stock_quantity }}</span>
          </div>
          <div class="product-actions">
            <button class="btn btn-sm btn-secondary" @click="editProduct(product)">Edit</button>
            <button class="btn btn-sm btn-danger" @click="deleteProduct(product.id)">Delete</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Add/Edit Product Modal -->
    <ProductModal 
      v-if="showAddModal || showEditModal"
      :product="editingProduct"
      @close="closeModal"
      @saved="onProductSaved"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import ProductModal from '@/components/vendor/ProductModal.vue'

const products = ref([])
const loading = ref(true)
const showAddModal = ref(false)
const showEditModal = ref(false)
const editingProduct = ref(null)

const loadProducts = async () => {
  loading.value = true
  try {
    const token = localStorage.getItem('token')
    const response = await axios.get('http://localhost:8000/api/vendor/products', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })
    
    if (response.data.success) {
      products.value = response.data.data.data || response.data.data || []
    }
  } catch (error) {
    console.error('Error loading products:', error)
    // Mock data for testing
    products.value = [
      { 
        id: 1, 
        name: 'Sample Product 1', 
        description: 'This is a sample product description',
        price: 99.99,
        stock_quantity: 10,
        is_active: true,
        images: []
      },
      { 
        id: 2, 
        name: 'Sample Product 2', 
        description: 'Another sample product',
        price: 149.99,
        stock_quantity: 5,
        is_active: true,
        images: []
      }
    ]
  } finally {
    loading.value = false
  }
}

const editProduct = (product) => {
  editingProduct.value = product
  showEditModal.value = true
}

const deleteProduct = async (id) => {
  if (!confirm('Are you sure you want to delete this product?')) return
  
  try {
    const token = localStorage.getItem('token')
    await axios.delete(`http://localhost:8000/api/vendor/products/${id}`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })
    await loadProducts()
  } catch (error) {
    console.error('Error deleting product:', error)
    alert('Failed to delete product')
  }
}

const closeModal = () => {
  showAddModal.value = false
  showEditModal.value = false
  editingProduct.value = null
}

const onProductSaved = () => {
  closeModal()
  loadProducts()
}

onMounted(() => {
  loadProducts()
})
</script>

<style scoped>
.vendor-products {
  padding: 24px;
  max-width: 1400px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
  flex-wrap: wrap;
  gap: 16px;
}

.page-header h1 {
  font-size: 28px;
  font-weight: 700;
  margin: 0;
  color: #1a1a2e;
}

.text-muted {
  color: #6b7280;
  margin: 4px 0 0 0;
}

.btn {
  padding: 10px 20px;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.btn-primary {
  background: #667eea;
  color: white;
}

.btn-primary:hover {
  background: #5a67d8;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-sm {
  padding: 6px 12px;
  font-size: 12px;
}

.btn-secondary {
  background: #e5e7eb;
  color: #1a1a2e;
}

.btn-secondary:hover {
  background: #d1d5db;
}

.btn-danger {
  background: #ef4444;
  color: white;
}

.btn-danger:hover {
  background: #dc2626;
}

/* Loading */
.loading-state {
  text-align: center;
  padding: 60px 20px;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #e5e7eb;
  border-top-color: #667eea;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.loading-state p {
  margin-top: 16px;
  color: #6b7280;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 60px 20px;
  background: white;
  border-radius: 12px;
  border: 1px solid #e5e7eb;
}

.empty-state span {
  font-size: 48px;
  display: block;
  margin-bottom: 16px;
}

.empty-state h3 {
  font-size: 20px;
  color: #1a1a2e;
  margin: 0 0 8px 0;
}

.empty-state p {
  color: #6b7280;
  margin: 0 0 20px 0;
}

/* Products Grid */
.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 24px;
}

.product-card {
  background: white;
  border-radius: 12px;
  border: 1px solid #e5e7eb;
  overflow: hidden;
  transition: all 0.3s ease;
}

.product-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 25px rgba(0,0,0,0.08);
}

.product-image {
  position: relative;
  height: 200px;
  background: #f3f4f6;
  display: flex;
  align-items: center;
  justify-content: center;
}

.product-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.no-image {
  font-size: 48px;
  color: #9ca3af;
}

.product-status {
  position: absolute;
  top: 12px;
  right: 12px;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
}

.product-status.active {
  background: #d1fae5;
  color: #059669;
}

.product-status.inactive {
  background: #fee2e2;
  color: #dc2626;
}

.product-info {
  padding: 16px;
}

.product-info h3 {
  font-size: 16px;
  font-weight: 600;
  margin: 0 0 4px 0;
  color: #1a1a2e;
}

.product-description {
  font-size: 14px;
  color: #6b7280;
  margin: 0 0 12px 0;
}

.product-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.product-price {
  font-size: 18px;
  font-weight: 700;
  color: #667eea;
}

.product-stock {
  font-size: 14px;
  color: #6b7280;
}

.product-actions {
  display: flex;
  gap: 8px;
}

.product-actions .btn {
  flex: 1;
  justify-content: center;
}

/* Responsive */
@media (max-width: 768px) {
  .vendor-products {
    padding: 16px;
  }
  
  .page-header {
    flex-direction: column;
    align-items: stretch;
  }
  
  .products-grid {
    grid-template-columns: 1fr 1fr;
    gap: 12px;
  }
}

@media (max-width: 480px) {
  .products-grid {
    grid-template-columns: 1fr;
  }
}
</style>