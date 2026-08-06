<template>
  <div class="admin-categories">
    <div class="page-header">
      <h1>Categories</h1>
      <p class="text-muted">Manage product categories</p>
      <button class="btn btn-primary" @click="showAddModal = true">
        <i class="bi bi-plus"></i> Add Category
      </button>
    </div>

    <!-- Categories Table -->
    <div class="table-wrapper">
      <div v-if="loading" class="loading-state">Loading categories...</div>
      
      <table v-else-if="categories.length > 0" class="data-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Slug</th>
            <th>Products</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="category in categories" :key="category.id">
            <td>#{{ category.id }}</td>
            <td>{{ category.name }}</td>
            <td>{{ category.slug }}</td>
            <td>{{ category.products_count || 0 }}</td>
            <td>{{ formatDate(category.created_at) }}</td>
            <td>
              <button class="btn-action edit" @click="editCategory(category)">
                <i class="bi bi-pencil"></i>
              </button>
              <button class="btn-action delete" @click="deleteCategory(category)">
                <i class="bi bi-trash"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-else-if="!loading" class="empty-state">
        <i class="bi bi-folder"></i>
        <p>No categories found</p>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <CategoryModal 
      v-if="showAddModal || showEditModal"
      :category="editingCategory"
      @close="closeModal"
      @saved="onCategorySaved"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import CategoryModal from '@/components/admin/CategoryModal.vue'

const categories = ref([])
const loading = ref(true)
const showAddModal = ref(false)
const showEditModal = ref(false)
const editingCategory = ref(null)

const loadCategories = async () => {
  loading.value = true
  try {
    const token = localStorage.getItem('token')
    const response = await axios.get('http://localhost:8000/api/admin/categories', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })
    
    if (response.data.success) {
      categories.value = response.data.data.data || []
    }
  } catch (error) {
    console.error('Error loading categories:', error)
  } finally {
    loading.value = false
  }
}

const editCategory = (category) => {
  editingCategory.value = category
  showEditModal.value = true
}

const deleteCategory = async (category) => {
  if (!confirm(`Delete "${category.name}"?`)) return
  
  try {
    const token = localStorage.getItem('token')
    await axios.delete(`http://localhost:8000/api/admin/categories/${category.id}`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })
    alert('✅ Category deleted!')
    loadCategories()
  } catch (error) {
    console.error('Error deleting category:', error)
    alert('❌ Failed to delete category')
  }
}

const closeModal = () => {
  showAddModal.value = false
  showEditModal.value = false
  editingCategory.value = null
}

const onCategorySaved = () => {
  closeModal()
  loadCategories()
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  })
}

onMounted(() => {
  loadCategories()
})
</script>

<style scoped>
.admin-categories {
  max-width: 1400px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
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
  margin: 4px 0 0;
}

.btn {
  padding: 8px 20px;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.btn-primary {
  background: #667eea;
  color: white;
}

.btn-primary:hover {
  background: #5a67d8;
}

.table-wrapper {
  background: white;
  border-radius: 12px;
  border: 1px solid #e5e7eb;
  overflow: hidden;
  margin-top: 20px;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th {
  padding: 14px 16px;
  text-align: left;
  background: #f9fafb;
  font-weight: 600;
  font-size: 13px;
  color: #6b7280;
  border-bottom: 1px solid #e5e7eb;
}

.data-table td {
  padding: 14px 16px;
  border-bottom: 1px solid #f3f4f6;
}

.btn-action {
  padding: 4px 8px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  margin-right: 4px;
  transition: all 0.2s ease;
}

.btn-action.edit {
  background: #e0e7ff;
  color: #4f46e5;
}

.btn-action.edit:hover {
  background: #c7d2fe;
}

.btn-action.delete {
  background: #fee2e2;
  color: #dc2626;
}

.btn-action.delete:hover {
  background: #fecaca;
}

.loading-state {
  text-align: center;
  padding: 40px;
  color: #6b7280;
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #6b7280;
}

.empty-state i {
  font-size: 48px;
  display: block;
  margin-bottom: 12px;
  color: #d1d5db;
}

@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    align-items: stretch;
  }
  
  .data-table {
    font-size: 13px;
  }
  
  .data-table th,
  .data-table td {
    padding: 10px 12px;
  }
}
</style>