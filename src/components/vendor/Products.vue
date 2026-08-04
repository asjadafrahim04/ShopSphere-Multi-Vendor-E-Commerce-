<template>
  <div class="vendor-products">
    <div class="header-actions">
      <h1>My Products</h1>
      <button class="btn-primary-modern" @click="showAddProduct = true">
        <i class="bi bi-plus-lg"></i> Add Product
      </button>
    </div>

    <!-- Products Table -->
    <div class="products-table-wrapper">
      <div v-if="loading" class="loading-state">Loading products...</div>
      <table v-else-if="products.length > 0" class="products-table">
        <thead>
          <tr>
            <th>Product</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="product in products" :key="product.id">
            <td>
              <div class="product-cell">
                <div class="product-thumb">
                  <img v-if="product.image" :src="product.image" :alt="product.name" />
                  <span v-else>📦</span>
                </div>
                <span class="product-name">{{ product.name }}</span>
              </div>
            </td>
            <td>${{ product.price }}</td>
            <td :class="{ 'low-stock': product.stock_quantity <= 5 }">
              {{ product.stock_quantity }}
            </td>
            <td>
              <span :class="['status-badge', 'status-' + product.status]">
                {{ product.status }}
              </span>
            </td>
            <td>
              <button class="action-btn edit" @click="editProduct(product)">
                <i class="bi bi-pencil"></i>
              </button>
              <button class="action-btn delete" @click="deleteProduct(product.id)">
                <i class="bi bi-trash"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-else class="empty-state">
        <i class="bi bi-box"></i>
        <p>No products yet. Start adding your first product!</p>
      </div>
    </div>

    <!-- Add/Edit Product Modal -->
    <div v-if="showAddProduct" class="modal-overlay" @click.self="showAddProduct = false">
      <div class="modal-content">
        <div class="modal-header">
          <h2>{{ editingProduct ? 'Edit Product' : 'Add Product' }}</h2>
          <button class="close-btn" @click="showAddProduct = false">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>
        <form @submit.prevent="saveProduct">
          <div class="form-group">
            <label>Product Name</label>
            <input type="text" v-model="productForm.name" required />
          </div>
          <div class="form-group">
            <label>Description</label>
            <textarea v-model="productForm.description" rows="3" required></textarea>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Price</label>
              <input type="number" step="0.01" v-model="productForm.price" required />
            </div>
            <div class="form-group">
              <label>Compare Price</label>
              <input type="number" step="0.01" v-model="productForm.compare_price" />
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Stock Quantity</label>
              <input type="number" v-model="productForm.stock_quantity" required />
            </div>
            <div class="form-group">
              <label>Category</label>
              <select v-model="productForm.category_id" required>
                <option value="">Select Category</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                  {{ cat.name }}
                </option>
              </select>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Status</label>
              <select v-model="productForm.status">
                <option value="draft">Draft</option>
                <option value="pending">Pending</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
            <div class="form-group">
              <label>Featured</label>
              <select v-model="productForm.is_featured">
                <option :value="0">No</option>
                <option :value="1">Yes</option>
              </select>
            </div>
          </div>
          <button type="submit" class="btn-primary-modern">
            {{ editingProduct ? 'Update' : 'Add' }} Product
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { vendorApi, categoryApi } from '@/services/api'

const products = ref([])
const categories = ref([])
const loading = ref(true)
const showAddProduct = ref(false)
const editingProduct = ref(null)

const productForm = ref({
  name: '',
  description: '',
  price: '',
  compare_price: '',
  stock_quantity: '',
  category_id: '',
  status: 'draft',
  is_featured: 0
})

const loadProducts = async () => {
  loading.value = true
  try {
    const response = await vendorApi.getVendorProductsList()
    if (response.data.success) {
      products.value = response.data.data.data || []
    }
  } catch (error) {
    console.error('Error loading products:', error)
  } finally {
    loading.value = false
  }
}

const loadCategories = async () => {
  try {
    const response = await categoryApi.getCategories()
    if (response.data.success) {
      categories.value = response.data.data || []
    }
  } catch (error) {
    console.error('Error loading categories:', error)
  }
}

const saveProduct = async () => {
  try {
    const data = { ...productForm.value }
    if (editingProduct.value) {
      await vendorApi.updateProduct(editingProduct.value.id, data)
      alert('Product updated successfully!')
    } else {
      await vendorApi.createProduct(data)
      alert('Product added successfully!')
    }
    showAddProduct.value = false
    editingProduct.value = null
    productForm.value = { name: '', description: '', price: '', compare_price: '', stock_quantity: '', category_id: '', status: 'draft', is_featured: 0 }
    loadProducts()
  } catch (error) {
    alert('Failed to save product. Please try again.')
  }
}

const editProduct = (product) => {
  editingProduct.value = product
  productForm.value = { ...product }
  showAddProduct.value = true
}

const deleteProduct = async (id) => {
  if (!confirm('Are you sure you want to delete this product?')) return
  try {
    await vendorApi.deleteProduct(id)
    alert('Product deleted!')
    loadProducts()
  } catch (error) {
    alert('Failed to delete product.')
  }
}

onMounted(() => {
  loadProducts()
  loadCategories()
})
</script>

<style scoped>
.vendor-products {
  padding: 24px;
}

.header-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.header-actions h1 {
  font-size: 2rem;
  font-weight: 700;
  color: var(--text-primary);
}

.products-table-wrapper {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: var(--radius);
  overflow: hidden;
}

.products-table {
  width: 100%;
  border-collapse: collapse;
}

.products-table th {
  text-align: left;
  padding: 12px 16px;
  background: var(--bg-secondary);
  color: var(--text-secondary);
  font-weight: 600;
  font-size: 0.85rem;
  text-transform: uppercase;
  border-bottom: 1px solid var(--border-color);
}

.products-table td {
  padding: 12px 16px;
  border-bottom: 1px solid var(--border-color);
  color: var(--text-primary);
}

.product-cell {
  display: flex;
  align-items: center;
  gap: 12px;
}

.product-thumb {
  width: 40px;
  height: 40px;
  border-radius: var(--radius-sm);
  background: var(--bg-secondary);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  overflow: hidden;
}

.product-thumb img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.product-name {
  font-weight: 500;
}

.low-stock {
  color: #ef4444;
  font-weight: 600;
}

.status-badge {
  padding: 2px 12px;
  border-radius: 50px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}

.status-active {
  background: #d1fae5;
  color: #059669;
}

.status-draft {
  background: #f3f4f6;
  color: #6b7280;
}

.status-pending {
  background: #fef3c7;
  color: #d97706;
}

.status-inactive {
  background: #fee2e2;
  color: #dc2626;
}

.action-btn {
  padding: 6px 10px;
  border: none;
  border-radius: var(--radius-sm);
  cursor: pointer;
  transition: var(--transition);
}

.action-btn.edit {
  background: rgba(102, 126, 234, 0.1);
  color: #667eea;
}

.action-btn.delete {
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
}

.action-btn:hover {
  transform: scale(1.05);
}

.loading-state {
  padding: 40px;
  text-align: center;
  color: var(--text-muted);
}

.empty-state {
  padding: 60px;
  text-align: center;
  color: var(--text-muted);
}

.empty-state i {
  font-size: 3rem;
  display: block;
  margin-bottom: 12px;
  opacity: 0.5;
}

/* Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  z-index: 999;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-content {
  background: var(--bg-card);
  border-radius: var(--radius);
  padding: 32px;
  max-width: 600px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.modal-header h2 {
  font-size: 1.5rem;
  font-weight: 700;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.2rem;
  cursor: pointer;
  color: var(--text-muted);
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.form-group {
  margin-bottom: 16px;
}

.form-group label {
  display: block;
  font-size: 0.9rem;
  font-weight: 500;
  color: var(--text-secondary);
  margin-bottom: 4px;
}

.form-group input,
.form-group textarea,
.form-group select {
  width: 100%;
  padding: 10px 14px;
  border: 1px solid var(--border-color);
  border-radius: var(--radius-sm);
  background: var(--bg-secondary);
  color: var(--text-primary);
  font-size: 1rem;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
  outline: none;
  border-color: #667eea;
}

@media (max-width: 768px) {
  .vendor-products {
    padding: 16px;
  }

  .header-actions {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }

  .products-table {
    font-size: 0.85rem;
  }

  .products-table th,
  .products-table td {
    padding: 8px 10px;
  }

  .product-thumb {
    width: 30px;
    height: 30px;
    font-size: 1.2rem;
  }

  .form-row {
    grid-template-columns: 1fr;
  }

  .modal-content {
    padding: 20px;
    width: 95%;
  }
}
</style>