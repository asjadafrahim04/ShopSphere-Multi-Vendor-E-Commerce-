<template>
  <div class="modal-overlay" @click="$emit('close')">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h2>{{ isEditing ? 'Edit Category' : 'Add New Category' }}</h2>
        <button class="close-btn" @click="$emit('close')">✕</button>
      </div>

      <form @submit.prevent="saveCategory" class="modal-body">
        <!-- Name -->
        <div class="form-group">
          <label>Category Name <span class="required">*</span></label>
          <input 
            v-model="form.name" 
            type="text" 
            required
            placeholder="Enter category name"
            class="form-input"
            :class="{ 'error': errors.name }"
          />
          <p v-if="errors.name" class="error-text">{{ errors.name[0] }}</p>
        </div>

        <!-- Description -->
        <div class="form-group">
          <label>Description</label>
          <textarea 
            v-model="form.description" 
            rows="3"
            placeholder="Enter category description"
            class="form-input"
          ></textarea>
        </div>

        <!-- Icon -->
        <div class="form-group">
          <label>Icon Class</label>
          <input 
            v-model="form.icon" 
            type="text"
            placeholder="bi bi-laptop"
            class="form-input"
          />
          <p class="hint-text">Bootstrap Icon class (e.g., bi bi-laptop, bi bi-bag)</p>
        </div>

        <!-- Error Message -->
        <div v-if="errorMessage" class="error-message">
          {{ errorMessage }}
        </div>

        <!-- Buttons -->
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="$emit('close')">Cancel</button>
          <button type="submit" class="btn btn-primary" :disabled="loading">
            {{ loading ? 'Saving...' : (isEditing ? 'Update Category' : 'Create Category') }}
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
  category: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'saved'])

// ===== STATE =====
const loading = ref(false)
const errorMessage = ref('')
const errors = reactive({})

const isEditing = computed(() => !!props.category)

const form = reactive({
  name: '',
  description: '',
  icon: ''
})

// ===== METHODS =====
const saveCategory = async () => {
  loading.value = true
  errorMessage.value = ''
  Object.keys(errors).forEach(key => delete errors[key])

  try {
    const token = localStorage.getItem('token')
    
    if (!token) {
      errorMessage.value = 'Please login again'
      loading.value = false
      return
    }

    const url = isEditing.value 
      ? `http://localhost:8000/api/admin/categories/${props.category.id}`
      : 'http://localhost:8000/api/admin/categories'
    
    const method = isEditing.value ? 'put' : 'post'
    
    const response = await axios[method](url, form, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      }
    })
    
    if (response.data.success) {
      alert(isEditing.value ? '✅ Category updated!' : '✅ Category created!')
      emit('saved')
      setTimeout(() => emit('close'), 500)
    } else {
      errorMessage.value = response.data.message || 'Failed to save category'
    }
  } catch (error) {
    console.error('Error saving category:', error)
    
    if (error.response?.data?.errors) {
      Object.assign(errors, error.response.data.errors)
      errorMessage.value = 'Please fix the validation errors.'
    } else if (error.response?.status === 401) {
      errorMessage.value = 'Session expired. Please login again.'
    } else {
      errorMessage.value = error.response?.data?.message || 'Failed to save category. Please try again.'
    }
  } finally {
    loading.value = false
  }
}

const populateForm = () => {
  if (props.category) {
    form.name = props.category.name || ''
    form.description = props.category.description || ''
    form.icon = props.category.icon || ''
  }
}

// ===== LIFECYCLE =====
onMounted(() => {
  populateForm()
})
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}

.modal-content {
  background: white;
  border-radius: 12px;
  max-width: 500px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
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
  border-radius: 12px 12px 0 0;
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

.hint-text {
  font-size: 12px;
  color: #6b7280;
  margin-top: 4px;
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

textarea.form-input {
  resize: vertical;
  min-height: 80px;
  font-family: inherit;
}

.error-text {
  color: #ef4444;
  font-size: 12px;
  margin-top: 4px;
}

.error-message {
  background: #fee2e2;
  color: #dc2626;
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
  font-size: 14px;
}

.btn-primary {
  background: #667eea;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #5a67d8;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-secondary {
  background: #e5e7eb;
  color: #1a1a2e;
}

.btn-secondary:hover {
  background: #d1d5db;
}

/* Responsive */
@media (max-width: 768px) {
  .modal-content {
    margin: 10px;
    max-height: 95vh;
  }
  
  .modal-header {
    padding: 16px 20px;
  }
  
  .modal-body {
    padding: 16px 20px;
  }
  
  .modal-footer {
    flex-direction: column;
  }
  
  .modal-footer .btn {
    width: 100%;
    justify-content: center;
  }
}
</style>