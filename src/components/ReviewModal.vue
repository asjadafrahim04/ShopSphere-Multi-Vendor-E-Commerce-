<template>
  <div class="modal-overlay" @click="$emit('close')">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h2>Write a Review</h2>
        <button class="close-btn" @click="$emit('close')">✕</button>
      </div>

      <div class="modal-body">
        <!-- Product Info -->
        <div class="product-info">
          <div class="product-image">
            <img 
              v-if="product?.images?.length > 0" 
              :src="'http://localhost:8000/storage/' + product.images[0].image_path" 
              :alt="product.name"
            />
            <span v-else>📦</span>
          </div>
          <div class="product-details">
            <h3>{{ product?.name }}</h3>
            <p class="vendor">{{ product?.vendor?.shop_name || 'ShopSphere' }}</p>
            <p class="order-info-text">Order #{{ order?.order_number }}</p>
          </div>
        </div>

        <!-- Rating -->
        <div class="form-group">
          <label>Your Rating <span class="required">*</span></label>
          <div class="rating-input">
            <i 
              v-for="n in 5" 
              :key="n" 
              :class="[
                n <= form.rating ? 'bi bi-star-fill' : 'bi bi-star',
                'star-input',
                { 'hover': hoverRating >= n }
              ]"
              @click="form.rating = n"
              @mouseenter="hoverRating = n"
              @mouseleave="hoverRating = 0"
            ></i>
            <span class="rating-text">{{ getRatingText(form.rating) }}</span>
          </div>
        </div>

        <!-- Comment -->
        <div class="form-group">
          <label>Your Review</label>
          <textarea 
            v-model="form.comment" 
            rows="5" 
            placeholder="Share your experience with this product..."
            class="review-textarea"
          ></textarea>
        </div>

        <!-- Error Message -->
        <div v-if="errorMessage" class="error-message">
          <i class="bi bi-exclamation-circle"></i>
          {{ errorMessage }}
        </div>

        <!-- Buttons -->
        <div class="modal-footer">
          <button class="btn-secondary" @click="$emit('close')">Cancel</button>
          <button 
            class="btn-primary" 
            @click="submitReview" 
            :disabled="submitting || form.rating === 0"
          >
            <span v-if="submitting">
              <i class="bi bi-arrow-repeat spin"></i> Submitting...
            </span>
            <span v-else>
              <i class="bi bi-star me-2"></i>Submit Review
            </span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import axios from 'axios'

const props = defineProps({
  order: {
    type: Object,
    default: null
  },
  product: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'submitted'])

const form = reactive({
  rating: 0,
  comment: ''
})

const hoverRating = ref(0)
const submitting = ref(false)
const errorMessage = ref('')

const getRatingText = (rating) => {
  const texts = ['', 'Terrible 😡', 'Poor 😕', 'Average 😐', 'Good 😊', 'Excellent 🤩']
  return texts[rating] || ''
}

const submitReview = async () => {
  if (form.rating === 0) {
    errorMessage.value = 'Please select a rating'
    return
  }

  submitting.value = true
  errorMessage.value = ''

  try {
    const token = localStorage.getItem('token')
    const response = await axios.post('http://localhost:8000/api/reviews', {
      product_id: props.product?.id,
      rating: form.rating,
      comment: form.comment
    }, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      }
    })

    if (response.data.success) {
      emit('submitted')
      setTimeout(() => emit('close'), 1000)
    }
  } catch (error) {
    console.error('❌ Review error:', error)
    errorMessage.value = error.response?.data?.message || 'Failed to submit review. Please try again.'
  } finally {
    submitting.value = false
  }
}
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  padding: 20px;
}

.modal-content {
  background: var(--bg-card);
  border-radius: var(--radius);
  max-width: 560px;
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
  border-bottom: 1px solid var(--border-color);
}

.modal-header h2 {
  font-size: 1.3rem;
  font-weight: 700;
  margin: 0;
  color: var(--text-primary);
}

.close-btn {
  background: none;
  border: none;
  font-size: 24px;
  color: var(--text-muted);
  cursor: pointer;
  padding: 4px 8px;
  border-radius: 6px;
  transition: var(--transition);
}

.close-btn:hover {
  background: var(--bg-secondary);
  color: var(--text-primary);
}

.modal-body {
  padding: 24px;
}

.product-info {
  display: flex;
  gap: 16px;
  padding: 16px;
  background: var(--bg-secondary);
  border-radius: 8px;
  margin-bottom: 20px;
}

.product-image {
  width: 64px;
  height: 64px;
  border-radius: 8px;
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  flex-shrink: 0;
}

.product-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.product-image span {
  font-size: 2rem;
}

.product-details h3 {
  font-size: 1rem;
  font-weight: 600;
  margin: 0 0 4px 0;
  color: var(--text-primary);
}

.product-details .vendor {
  font-size: 0.85rem;
  color: var(--text-muted);
  margin: 0;
}

.product-details .order-info-text {
  font-size: 0.8rem;
  color: var(--text-muted);
  margin: 4px 0 0 0;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  font-weight: 600;
  color: var(--text-primary);
  margin-bottom: 8px;
}

.required {
  color: #ef4444;
}

.rating-input {
  display: flex;
  align-items: center;
  gap: 8px;
}

.star-input {
  font-size: 32px;
  color: var(--border-color);
  cursor: pointer;
  transition: all 0.2s ease;
}

.star-input.bi-star-fill {
  color: #f59e0b;
}

.star-input:hover {
  transform: scale(1.15);
}

.rating-text {
  font-size: 14px;
  font-weight: 500;
  color: var(--text-muted);
  margin-left: 8px;
}

.review-textarea {
  width: 100%;
  padding: 12px 16px;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  font-size: 14px;
  resize: vertical;
  min-height: 120px;
  background: var(--bg-card);
  color: var(--text-primary);
  transition: all 0.2s ease;
  font-family: inherit;
}

.review-textarea:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.error-message {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px;
  background: #fee2e2;
  border: 1px solid #fecaca;
  border-radius: 8px;
  color: #dc2626;
  font-size: 14px;
  margin-bottom: 16px;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding-top: 16px;
  border-top: 1px solid var(--border-color);
}

.btn-primary {
  padding: 10px 24px;
  background: #667eea;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-primary:hover:not(:disabled) {
  background: #5a67d8;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-secondary {
  padding: 10px 24px;
  background: var(--bg-secondary);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  color: var(--text-secondary);
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-secondary:hover {
  background: var(--bg-primary);
}

.spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Dark Mode */
html.dark .review-textarea {
  background: #0f0e17;
  border-color: #2d2b4e;
  color: #e5e7eb;
}

html.dark .review-textarea:focus {
  border-color: #667eea;
}

html.dark .product-info {
  background: #0f0e17;
}

@media (max-width: 480px) {
  .modal-content {
    margin: 10px;
  }

  .modal-header {
    padding: 16px 20px;
  }

  .modal-body {
    padding: 16px 20px;
  }

  .star-input {
    font-size: 28px;
  }

  .modal-footer {
    flex-direction: column;
  }

  .modal-footer .btn-primary,
  .modal-footer .btn-secondary {
    width: 100%;
    justify-content: center;
  }
}
</style>