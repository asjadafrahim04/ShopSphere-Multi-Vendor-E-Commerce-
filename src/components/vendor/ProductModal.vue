<template>
  <div class="modal-overlay" @click="$emit('close')">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h2>{{ isEditing ? 'Edit Product' : 'Add New Product' }}</h2>
        <button class="close-btn" @click="$emit('close')">✕</button>
      </div>

      <!-- Validation Errors Summary -->
      <div v-if="Object.keys(errors).length > 0" class="validation-errors">
        <div class="error-title">⚠️ Please fix the following errors:</div>
        <ul>
          <li v-for="(error, field) in errors" :key="field">
            <strong>{{ formatFieldName(field) }}:</strong> {{ error.join(', ') }}
          </li>
        </ul>
      </div>

      <form @submit.prevent="saveProduct" class="modal-body">
        <!-- Name -->
        <div class="form-group">
          <label>Product Name <span class="required">*</span></label>
          <input 
            v-model="form.name" 
            type="text" 
            required
            placeholder="Enter product name"
            class="form-input"
            :class="{ 'error': errors.name }"
          />
          <p v-if="errors.name" class="error-text">{{ errors.name[0] }}</p>
        </div>

        <!-- Description -->
        <div class="form-group">
          <label>Description <span class="required">*</span></label>
          <textarea 
            v-model="form.description" 
            rows="4" 
            required
            placeholder="Enter product description (minimum 10 characters)"
            class="form-input"
            :class="{ 'error': errors.description }"
          ></textarea>
          <p v-if="errors.description" class="error-text">{{ errors.description[0] }}</p>
        </div>

        <!-- Price & Stock -->
        <div class="form-row">
          <div class="form-group">
            <label>Price ($) <span class="required">*</span></label>
            <input 
              v-model.number="form.price" 
              type="number" 
              step="0.01"
              required
              placeholder="0.00"
              class="form-input"
              :class="{ 'error': errors.price }"
              min="0.01"
            />
            <p v-if="errors.price" class="error-text">{{ errors.price[0] }}</p>
          </div>
          <div class="form-group">
            <label>Stock Quantity <span class="required">*</span></label>
            <input 
              v-model.number="form.stock_quantity" 
              type="number" 
              required
              placeholder="0"
              class="form-input"
              :class="{ 'error': errors.stock_quantity }"
              min="0"
            />
            <p v-if="errors.stock_quantity" class="error-text">{{ errors.stock_quantity[0] }}</p>
          </div>
        </div>

        <!-- Category & SKU -->
        <div class="form-row">
          <div class="form-group">
            <label>Category <span class="required">*</span></label>
            <select v-model="form.category_id" required class="form-input" :class="{ 'error': errors.category_id }">
              <option value="">Select Category</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                {{ cat.name }}
              </option>
            </select>
            <p v-if="errors.category_id" class="error-text">{{ errors.category_id[0] }}</p>
          </div>
          <div class="form-group">
            <label>SKU</label>
            <input 
              v-model="form.sku" 
              type="text" 
              placeholder="Leave empty for auto-generate"
              class="form-input"
              :class="{ 'error': errors.sku }"
            />
            <p v-if="errors.sku" class="error-text">{{ errors.sku[0] }}</p>
            <p class="hint-text">SKU will be auto-generated if left empty</p>
          </div>
        </div>

        <!-- Compare Price (Optional) -->
        <div class="form-group">
          <label>Compare Price (Optional)</label>
          <input 
            v-model.number="form.compare_price" 
            type="number" 
            step="0.01"
            placeholder="0.00"
            class="form-input"
            :class="{ 'error': errors.compare_price }"
            min="0"
          />
          <p v-if="errors.compare_price" class="error-text">{{ errors.compare_price[0] }}</p>
        </div>

        <!-- Images -->
        <div class="form-group">
          <label>Product Images</label>
          <input 
            type="file" 
            multiple 
            @change="handleImageUpload"
            accept="image/*"
            class="file-input"
            :class="{ 'error': errors.images }"
          />
          <p v-if="errors.images" class="error-text">{{ errors.images[0] }}</p>
          <div class="image-preview">
            <div v-for="(image, index) in form.images" :key="index" class="preview-item">
              <img :src="image.preview" />
              <button type="button" @click="removeImage(index)" class="remove-image">✕</button>
            </div>
          </div>
          <p class="hint-text">Supported formats: JPEG, PNG, JPG, GIF (Max 2MB each)</p>
        </div>

        <!-- Active Status -->
        <div class="form-group checkbox-group">
          <label class="checkbox-label">
            <input type="checkbox" v-model="form.is_active" />
            Product is Active
          </label>
        </div>

        <!-- Error Message -->
        <div v-if="errorMessage" class="error-message">
          {{ errorMessage }}
        </div>

        <!-- Success Message -->
        <div v-if="successMessage" class="success-message">
          ✅ {{ successMessage }}
        </div>

        <!-- Submit -->
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="$emit('close')">Cancel</button>
          <button type="submit" class="btn btn-primary" :disabled="loading">
            <span v-if="loading">
              <span class="spinner"></span> Saving...
            </span>
            <span v-else>
              {{ isEditing ? 'Update Product' : 'Save Product' }}
            </span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps({
  product: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'saved'])

// ===== STATE =====
const loading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const categories = ref([])
const errors = reactive({})

const isEditing = computed(() => !!props.product)

const form = reactive({
  name: '',
  description: '',
  price: 0,
  compare_price: null,
  stock_quantity: 0,
  sku: '',
  category_id: '',
  images: [],
  is_active: true,
  is_featured: false
})

// ===== HELPERS =====
const formatFieldName = (field) => {
  const names = {
    name: 'Product Name',
    description: 'Description',
    price: 'Price',
    compare_price: 'Compare Price',
    stock_quantity: 'Stock Quantity',
    category_id: 'Category',
    sku: 'SKU',
    images: 'Images'
  }
  return names[field] || field
}

// ===== LOAD CATEGORIES =====
const loadCategories = async () => {
  try {
    const token = localStorage.getItem('token')
    const response = await axios.get('http://localhost:8000/api/categories', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })
    categories.value = response.data.data || response.data || []
  } catch (error) {
    console.error('Error loading categories:', error)
    // Fallback categories
    categories.value = [
      { id: 1, name: 'Electronics' },
      { id: 2, name: 'Clothing' },
      { id: 3, name: 'Home & Garden' },
      { id: 4, name: 'Books' },
      { id: 5, name: 'Toys' }
    ]
  }
}

// ===== IMAGE HANDLING =====
const handleImageUpload = (event) => {
  const files = event.target.files
  const maxSize = 2 * 1024 * 1024 // 2MB
  
  for (let i = 0; i < files.length; i++) {
    const file = files[i]
    
    // Check file size
    if (file.size > maxSize) {
      errorMessage.value = `Image "${file.name}" is too large. Maximum size is 2MB.`
      continue
    }
    
    // Check file type
    if (!file.type.startsWith('image/')) {
      errorMessage.value = `"${file.name}" is not an image file.`
      continue
    }
    
    const reader = new FileReader()
    reader.onload = (e) => {
      form.images.push({
        file: file,
        preview: e.target.result
      })
    }
    reader.readAsDataURL(file)
  }
  
  // Reset the input
  event.target.value = ''
}

const removeImage = (index) => {
  form.images.splice(index, 1)
}

// ===== POPULATE FORM FOR EDITING =====
const populateForm = () => {
  if (props.product) {
    form.name = props.product.name || ''
    form.description = props.product.description || ''
    form.price = props.product.price || 0
    form.compare_price = props.product.compare_price || null
    form.stock_quantity = props.product.stock_quantity || 0
    form.sku = props.product.sku || ''
    form.category_id = props.product.category_id || ''
    form.is_active = props.product.is_active ?? true
    form.is_featured = props.product.is_featured ?? false
    
    if (props.product.images && props.product.images.length > 0) {
      form.images = props.product.images.map(img => ({
        preview: img.image_path ? 'http://localhost:8000/storage/' + img.image_path : '',
        file: null,
        existing: true,
        id: img.id
      }))
    }
  }
}

// ===== SAVE PRODUCT =====
const saveProduct = async () => {
  // Reset states
  loading.value = true
  errorMessage.value = ''
  successMessage.value = ''
  Object.keys(errors).forEach(key => delete errors[key])

  try {
    // Validate required fields
    if (!form.name || form.name.trim() === '') {
      throw new Error('Product name is required')
    }
    if (!form.description || form.description.trim().length < 10) {
      throw new Error('Description must be at least 10 characters')
    }
    if (!form.price || form.price <= 0) {
      throw new Error('Price must be greater than 0')
    }
    if (!form.category_id) {
      throw new Error('Please select a category')
    }
    if (form.stock_quantity === undefined || form.stock_quantity < 0) {
      throw new Error('Stock quantity is required and cannot be negative')
    }

    const formData = new FormData()
    
    // Add all form fields
    formData.append('name', form.name.trim())
    formData.append('description', form.description.trim())
    formData.append('price', form.price)
    formData.append('stock_quantity', form.stock_quantity)
    formData.append('category_id', form.category_id)
    formData.append('is_active', form.is_active ? '1' : '0')
    formData.append('is_featured', form.is_featured ? '1' : '0')
    
    // Optional fields
    if (form.compare_price && form.compare_price > 0) {
      formData.append('compare_price', form.compare_price)
    }
    if (form.sku && form.sku.trim() !== '') {
      formData.append('sku', form.sku.trim())
    }

    // Add images
    let hasNewImages = false
    form.images.forEach((image, index) => {
      if (image.file) {
        formData.append(`images[${index}]`, image.file)
        hasNewImages = true
      }
    })

    // For update, add _method
    if (isEditing.value) {
      formData.append('_method', 'PUT')
    }

    // Debug log
    console.log('📤 Sending data:')
    for (let pair of formData.entries()) {
      if (pair[0].startsWith('images[')) {
        console.log(pair[0] + ': [FILE] ' + (pair[1].name || ''))
      } else {
        console.log(pair[0] + ': ' + pair[1])
      }
    }

    const token = localStorage.getItem('token')
    if (!token) {
      throw new Error('Authentication required. Please login again.')
    }

    const url = isEditing.value 
      ? `http://localhost:8000/api/vendor/products/${props.product.id}`
      : 'http://localhost:8000/api/vendor/products'

    const response = await axios.post(url, formData, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'multipart/form-data',
        'Accept': 'application/json'
      }
    })

    if (response.data.success) {
      successMessage.value = response.data.message || 'Product saved successfully!'
      emit('saved')
      
      // Close modal after success
      setTimeout(() => {
        emit('close')
      }, 1500)
    } else {
      errorMessage.value = response.data.message || 'Failed to save product'
    }
  } catch (error) {
    console.error('❌ Save error:', error)
    
    if (error.response) {
      console.log('📄 Response data:', error.response.data)
      console.log('📊 Status:', error.response.status)
      
      if (error.response.status === 403) {
        errorMessage.value = error.response.data.message || 'Unauthorized. Please make sure you are logged in as a vendor.'
      } else if (error.response.status === 422) {
        // Validation errors
        const validationErrors = error.response.data.errors || {}
        Object.assign(errors, validationErrors)
        
        // Show specific error messages
        let errorMessages = []
        Object.keys(validationErrors).forEach(key => {
          errorMessages.push(`${formatFieldName(key)}: ${validationErrors[key].join(', ')}`)
        })
        errorMessage.value = 'Please fix the following errors:\n' + errorMessages.join('\n')
      } else if (error.response.status === 401) {
        errorMessage.value = 'Session expired. Please login again.'
        // Clear token and redirect to login
        localStorage.removeItem('token')
        setTimeout(() => {
          window.location.href = '/login'
        }, 2000)
      } else {
        errorMessage.value = error.response.data?.message || 'Failed to save product. Please try again.'
      }
    } else if (error.request) {
      errorMessage.value = 'Network error. Please check your internet connection and make sure the backend is running.'
    } else {
      errorMessage.value = error.message || 'An unexpected error occurred.'
    }
  } finally {
    loading.value = false
  }
}

// ===== LIFECYCLE =====
onMounted(() => {
  loadCategories()
  populateForm()
})
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}

.modal-content {
  background: white;
  border-radius: 12px;
  max-width: 600px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  animation: slideUp 0.3s ease;
}

@keyframes slideUp {
  from {
    transform: translateY(30px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-bottom: 1px solid #e5e7eb;
  position: sticky;
  top: 0;
  background: white;
  z-index: 1;
}

.modal-header h2 {
  font-size: 20px;
  font-weight: 700;
  margin: 0;
  color: #1a1a2e;
}

.close-btn {
  background: none;
  border: none;
  font-size: 24px;
  color: #6b7280;
  cursor: pointer;
  padding: 4px 8px;
  border-radius: 6px;
  transition: all 0.2s ease;
}

.close-btn:hover {
  background: #f3f4f6;
  color: #1a1a2e;
}

/* Validation Errors */
.validation-errors {
  background: #fee2e2;
  border: 1px solid #fecaca;
  border-radius: 8px;
  padding: 16px;
  margin: 16px 24px;
}

.validation-errors .error-title {
  font-weight: 600;
  color: #dc2626;
  margin-bottom: 8px;
}

.validation-errors ul {
  margin: 0;
  padding-left: 20px;
  color: #dc2626;
  font-size: 14px;
}

.validation-errors ul li {
  margin-bottom: 4px;
}

.modal-body {
  padding: 24px;
}

.form-group {
  margin-bottom: 16px;
}

.form-group label {
  display: block;
  font-size: 14px;
  font-weight: 500;
  color: #1a1a2e;
  margin-bottom: 4px;
}

.required {
  color: #ef4444;
}

.form-input {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 14px;
  transition: all 0.2s ease;
  background: white;
  color: #1a1a2e;
}

.form-input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-input.error {
  border-color: #ef4444;
  box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

.form-input::placeholder {
  color: #9ca3af;
}

.form-input:disabled {
  background: #f3f4f6;
  cursor: not-allowed;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.error-text {
  color: #ef4444;
  font-size: 12px;
  margin-top: 4px;
}

.hint-text {
  color: #6b7280;
  font-size: 12px;
  margin-top: 4px;
}

.file-input {
  display: block;
  width: 100%;
  padding: 8px;
  border: 2px dashed #e5e7eb;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.file-input:hover {
  border-color: #667eea;
}

.file-input.error {
  border-color: #ef4444;
}

.image-preview {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 8px;
}

.preview-item {
  position: relative;
  width: 80px;
  height: 80px;
  border-radius: 8px;
  overflow: hidden;
  border: 1px solid #e5e7eb;
}

.preview-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.remove-image {
  position: absolute;
  top: 2px;
  right: 2px;
  background: #ef4444;
  color: white;
  border: none;
  border-radius: 50%;
  width: 20px;
  height: 20px;
  font-size: 12px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.remove-image:hover {
  transform: scale(1.1);
}

.checkbox-group {
  display: flex;
  align-items: center;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  color: #1a1a2e;
}

.checkbox-label input[type="checkbox"] {
  width: 18px;
  height: 18px;
  accent-color: #667eea;
  cursor: pointer;
}

.error-message {
  background: #fee2e2;
  color: #dc2626;
  padding: 12px;
  border-radius: 8px;
  font-size: 14px;
  margin-bottom: 16px;
  white-space: pre-line;
}

.success-message {
  background: #d1fae5;
  color: #059669;
  padding: 12px;
  border-radius: 8px;
  font-size: 14px;
  margin-bottom: 16px;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding-top: 16px;
  border-top: 1px solid #e5e7eb;
  margin-top: 8px;
}

.btn {
  padding: 10px 24px;
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

.btn-primary:hover:not(:disabled) {
  background: #5a67d8;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.btn-secondary {
  background: #e5e7eb;
  color: #1a1a2e;
}

.btn-secondary:hover {
  background: #d1d5db;
}

.spinner {
  display: inline-block;
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Responsive */
@media (max-width: 768px) {
  .modal-content {
    max-width: 100%;
    margin: 10px;
    max-height: 95vh;
  }
  
  .form-row {
    grid-template-columns: 1fr;
  }
  
  .modal-header {
    padding: 16px 20px;
  }
  
  .modal-body {
    padding: 16px 20px;
  }
  
  .validation-errors {
    margin: 12px 16px;
  }
  
  .modal-footer {
    flex-direction: column-reverse;
  }
  
  .modal-footer .btn {
    width: 100%;
    justify-content: center;
  }
}

@media (max-width: 480px) {
  .preview-item {
    width: 60px;
    height: 60px;
  }
  
  .modal-header h2 {
    font-size: 18px;
  }
}
</style>