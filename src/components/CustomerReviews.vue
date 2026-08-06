<template>
  <div class="customer-reviews">
    <h3>Customer Reviews</h3>
    
    <!-- Rating Summary -->
    <div class="rating-summary">
      <div class="average-rating">
        <span class="rating-number">{{ averageRating.toFixed(1) }}</span>
        <div class="stars">
          <i v-for="n in 5" :key="n" :class="n <= Math.floor(averageRating) ? 'bi bi-star-fill' : 'bi bi-star'"></i>
        </div>
        <span class="review-count">{{ totalReviews }} reviews</span>
      </div>
      
      <div class="rating-breakdown">
        <div v-for="i in 5" :key="i" class="rating-row">
          <span class="rating-label">{{ i }} ★</span>
          <div class="rating-bar">
            <div class="rating-fill" :style="{ width: getPercentage(i) + '%' }"></div>
          </div>
          <span class="rating-count">{{ ratingBreakdown[i] || 0 }}</span>
        </div>
      </div>
    </div>

    <!-- Write Review - Only show if not reviewed -->
    <div v-if="isLoggedIn && !hasReviewed" class="write-review">
      <h4>Write a Review</h4>
      <form @submit.prevent="submitReview">
        <div class="form-group">
          <label>Your Rating</label>
          <div class="rating-input">
            <i 
              v-for="n in 5" 
              :key="n" 
              :class="n <= reviewForm.rating ? 'bi bi-star-fill' : 'bi bi-star'"
              @click="reviewForm.rating = n"
              class="star-input"
            ></i>
            <span class="rating-text">{{ getRatingText(reviewForm.rating) }}</span>
          </div>
        </div>
        <div class="form-group">
          <label>Your Review</label>
          <textarea 
            v-model="reviewForm.comment" 
            rows="4" 
            placeholder="Share your experience with this product..."
            class="review-textarea"
          ></textarea>
        </div>
        <button type="submit" class="btn-primary" :disabled="submitting || reviewForm.rating === 0">
          {{ submitting ? 'Submitting...' : 'Submit Review' }}
        </button>
      </form>
    </div>
    
    <!-- Already Reviewed Message -->
    <div v-else-if="isLoggedIn && hasReviewed" class="already-reviewed">
      <i class="bi bi-check-circle-fill"></i>
      <p>You have already reviewed this product.</p>
      <span class="reviewed-badge">✓ Reviewed</span>
    </div>
    
    <div v-else class="login-to-review">
      <p><router-link to="/login">Login</router-link> to write a review</p>
    </div>

    <!-- Reviews List -->
    <div class="reviews-list">
      <div v-if="loading" class="loading-reviews">
        <div class="spinner-small"></div>
        <span>Loading reviews...</span>
      </div>
      <div v-else-if="reviews.length === 0" class="empty-reviews">
        <i class="bi bi-chat-dots"></i>
        <p>No reviews yet. Be the first to review this product!</p>
      </div>
      <div v-else v-for="review in reviews" :key="review.id" class="review-item">
        <div class="review-header">
          <div class="reviewer-info">
            <span class="reviewer-name">{{ review.user?.name || 'Anonymous' }}</span>
            <div class="review-stars">
              <i v-for="n in 5" :key="n" :class="n <= review.rating ? 'bi bi-star-fill' : 'bi bi-star'"></i>
            </div>
            <span class="review-date">{{ formatDate(review.created_at) }}</span>
          </div>
          <span v-if="review.is_verified" class="verified-badge">
            <i class="bi bi-check-circle-fill"></i> Verified Purchase
          </span>
        </div>
        <p class="review-comment">{{ review.comment }}</p>
        <button 
          v-if="canDelete(review)" 
          class="delete-review-btn" 
          @click="deleteReview(review.id)"
        >
          <i class="bi bi-trash"></i> Delete
        </button>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="pagination.last_page > 1" class="reviews-pagination">
      <button 
        @click="loadReviews(pagination.current_page - 1)" 
        :disabled="pagination.current_page <= 1"
      >
        ← Previous
      </button>
      <span>Page {{ pagination.current_page }} of {{ pagination.last_page }}</span>
      <button 
        @click="loadReviews(pagination.current_page + 1)" 
        :disabled="pagination.current_page >= pagination.last_page"
      >
        Next →
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
  productId: {
    type: [String, Number],
    required: true
  }
})

// ===== STATE =====
const reviews = ref([])
const loading = ref(true)
const submitting = ref(false)
const averageRating = ref(0)
const totalReviews = ref(0)
const ratingBreakdown = ref({})
const isLoggedIn = ref(false)
const hasReviewed = ref(false)

const reviewForm = ref({
  rating: 0,
  comment: ''
})

const pagination = ref({
  current_page: 1,
  last_page: 1
})

// ===== METHODS =====
const loadReviews = async (page = 1) => {
  loading.value = true
  try {
    const response = await fetch(`http://localhost:8000/api/products/${props.productId}/reviews?page=${page}`)
    const data = await response.json()
    
    if (data.success) {
      reviews.value = data.data.reviews.data || []
      averageRating.value = data.data.average_rating || 0
      totalReviews.value = data.data.total_reviews || 0
      ratingBreakdown.value = data.data.rating_breakdown || {}
      pagination.value.current_page = data.data.reviews.current_page || 1
      pagination.value.last_page = data.data.reviews.last_page || 1
      
      // Check if user already reviewed
      checkIfReviewed()
    }
  } catch (error) {
    console.error('Error loading reviews:', error)
  } finally {
    loading.value = false
  }
}

const checkIfReviewed = () => {
  const user = JSON.parse(localStorage.getItem('user') || 'null')
  if (!user) {
    hasReviewed.value = false
    return
  }
  
  hasReviewed.value = reviews.value.some(review => review.user_id === user.id)
}

const getPercentage = (rating) => {
  if (totalReviews.value === 0) return 0
  return ((ratingBreakdown.value[rating] || 0) / totalReviews.value) * 100
}

const getRatingText = (rating) => {
  const texts = ['', 'Terrible 😡', 'Poor 😕', 'Average 😐', 'Good 😊', 'Excellent 🤩']
  return texts[rating] || ''
}

const submitReview = async () => {
  if (reviewForm.value.rating === 0) {
    alert('Please select a rating')
    return
  }
  
  submitting.value = true
  try {
    const token = localStorage.getItem('token')
    const response = await axios.post('http://localhost:8000/api/reviews', {
      product_id: props.productId,
      rating: reviewForm.value.rating,
      comment: reviewForm.value.comment
    }, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      }
    })
    
    if (response.data.success) {
      alert('✅ Review submitted successfully!')
      reviewForm.value = { rating: 0, comment: '' }
      hasReviewed.value = true
      loadReviews()
    }
  } catch (error) {
    console.error('❌ Error submitting review:', error)
    alert(error.response?.data?.message || 'Failed to submit review')
  } finally {
    submitting.value = false
  }
}

const deleteReview = async (reviewId) => {
  if (!confirm('Are you sure you want to delete this review?')) return
  
  try {
    const token = localStorage.getItem('token')
    await axios.delete(`http://localhost:8000/api/reviews/${reviewId}`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })
    alert('✅ Review deleted!')
    hasReviewed.value = false
    loadReviews()
  } catch (error) {
    console.error('Error deleting review:', error)
    alert('Failed to delete review')
  }
}

const canDelete = (review) => {
  const user = JSON.parse(localStorage.getItem('user') || 'null')
  return user && (user.id === review.user_id || user.role === 'admin')
}

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('en-US', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  })
}

const checkLoginStatus = () => {
  const token = localStorage.getItem('token')
  isLoggedIn.value = !!token
}

// ===== WATCH =====
watch(() => props.productId, () => {
  loadReviews()
})

// ===== LIFECYCLE =====
onMounted(() => {
  checkLoginStatus()
  loadReviews()
  
  window.addEventListener('auth-changed', checkLoginStatus)
  window.addEventListener('storage', checkLoginStatus)
})
</script>

<style scoped>
.customer-reviews {
  margin-top: 40px;
  padding-top: 30px;
  border-top: 2px solid var(--border-color);
}

.customer-reviews h3 {
  font-size: 22px;
  font-weight: 700;
  color: var(--text-primary);
  margin-bottom: 20px;
}

/* Rating Summary */
.rating-summary {
  display: grid;
  grid-template-columns: 1fr 2fr;
  gap: 30px;
  background: var(--bg-secondary);
  padding: 24px;
  border-radius: 12px;
  margin-bottom: 30px;
}

.average-rating {
  text-align: center;
}

.rating-number {
  font-size: 48px;
  font-weight: 700;
  color: #667eea;
  display: block;
}

.stars {
  display: flex;
  gap: 4px;
  justify-content: center;
  color: #f59e0b;
  font-size: 20px;
  margin: 4px 0;
}

.stars .bi-star {
  color: var(--border-color);
}

.review-count {
  color: var(--text-muted);
  font-size: 14px;
}

.rating-breakdown {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.rating-row {
  display: flex;
  align-items: center;
  gap: 10px;
}

.rating-label {
  font-size: 13px;
  min-width: 40px;
  color: var(--text-secondary);
}

.rating-bar {
  flex: 1;
  height: 8px;
  background: var(--border-color);
  border-radius: 4px;
  overflow: hidden;
}

.rating-fill {
  height: 100%;
  background: #f59e0b;
  border-radius: 4px;
  transition: width 0.5s ease;
}

.rating-count {
  font-size: 13px;
  color: var(--text-muted);
  min-width: 30px;
}

/* Write Review */
.write-review {
  background: var(--bg-secondary);
  padding: 24px;
  border-radius: 12px;
  margin-bottom: 30px;
}

.write-review h4 {
  font-size: 18px;
  font-weight: 600;
  color: var(--text-primary);
  margin-bottom: 16px;
}

.form-group {
  margin-bottom: 16px;
}

.form-group label {
  display: block;
  font-weight: 500;
  color: var(--text-primary);
  margin-bottom: 6px;
}

.rating-input {
  display: flex;
  align-items: center;
  gap: 10px;
}

.star-input {
  font-size: 30px;
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
  min-height: 100px;
  background: var(--bg-card);
  color: var(--text-primary);
  transition: all 0.2s ease;
}

.review-textarea:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
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

/* Already Reviewed */
.already-reviewed {
  text-align: center;
  padding: 20px;
  background: #d1fae5;
  border-radius: 12px;
  margin-bottom: 30px;
  border: 1px solid #a7f3d0;
}

.already-reviewed i {
  font-size: 32px;
  color: #059669;
  display: block;
  margin-bottom: 8px;
}

.already-reviewed p {
  color: #065f46;
  margin-bottom: 8px;
}

.reviewed-badge {
  display: inline-block;
  padding: 4px 16px;
  background: #059669;
  color: white;
  border-radius: 50px;
  font-weight: 600;
  font-size: 14px;
}

/* Login to review */
.login-to-review {
  text-align: center;
  padding: 20px;
  background: var(--bg-secondary);
  border-radius: 12px;
  margin-bottom: 30px;
}

.login-to-review a {
  color: #667eea;
  font-weight: 600;
  text-decoration: none;
}

.login-to-review a:hover {
  text-decoration: underline;
}

/* Reviews List */
.reviews-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.loading-reviews {
  display: flex;
  align-items: center;
  gap: 12px;
  justify-content: center;
  padding: 30px;
  color: var(--text-muted);
}

.spinner-small {
  width: 24px;
  height: 24px;
  border: 3px solid var(--border-color);
  border-top-color: #667eea;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.empty-reviews {
  text-align: center;
  padding: 40px 20px;
  color: var(--text-muted);
}

.empty-reviews i {
  font-size: 48px;
  display: block;
  margin-bottom: 12px;
  color: var(--border-color);
}

.empty-reviews p {
  font-size: 16px;
}

.review-item {
  background: var(--bg-card);
  padding: 16px 20px;
  border-radius: 10px;
  border: 1px solid var(--border-color);
  transition: all 0.2s ease;
}

.review-item:hover {
  box-shadow: var(--shadow-hover);
}

.review-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  flex-wrap: wrap;
  gap: 8px;
  margin-bottom: 8px;
}

.reviewer-info {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.reviewer-name {
  font-weight: 600;
  color: var(--text-primary);
}

.review-stars {
  display: flex;
  gap: 2px;
  color: #f59e0b;
  font-size: 14px;
}

.review-stars .bi-star {
  color: var(--border-color);
}

.review-date {
  font-size: 13px;
  color: var(--text-muted);
}

.verified-badge {
  font-size: 12px;
  color: #059669;
  font-weight: 600;
  background: #d1fae5;
  padding: 2px 10px;
  border-radius: 50px;
}

.verified-badge i {
  margin-right: 4px;
}

.review-comment {
  margin: 8px 0 0 0;
  color: var(--text-secondary);
  line-height: 1.6;
}

.delete-review-btn {
  margin-top: 8px;
  background: none;
  border: none;
  color: #ef4444;
  cursor: pointer;
  font-size: 13px;
  padding: 4px 8px;
  border-radius: 4px;
  transition: all 0.2s ease;
}

.delete-review-btn:hover {
  background: #fee2e2;
}

/* Pagination */
.reviews-pagination {
  display: flex;
  justify-content: center;
  gap: 16px;
  margin-top: 20px;
  align-items: center;
}

.reviews-pagination button {
  padding: 8px 16px;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  background: var(--bg-card);
  color: var(--text-primary);
  cursor: pointer;
  transition: all 0.2s ease;
}

.reviews-pagination button:hover:not(:disabled) {
  background: #667eea;
  color: white;
  border-color: #667eea;
}

.reviews-pagination button:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.reviews-pagination span {
  color: var(--text-secondary);
  font-size: 14px;
}

/* Dark Mode */
html.dark .already-reviewed {
  background: #1a3a2a;
  border-color: #2d4a3a;
}

html.dark .already-reviewed p {
  color: #6ee7b7;
}

html.dark .verified-badge {
  background: #1a3a2a;
  color: #34d399;
}

html.dark .review-item {
  border-color: #2d2b4e;
}

html.dark .rating-summary {
  background: #1a1932;
}

html.dark .write-review {
  background: #1a1932;
}

html.dark .review-textarea {
  background: #0f0e17;
  border-color: #2d2b4e;
  color: #e5e7eb;
}

html.dark .review-textarea:focus {
  border-color: #667eea;
}

html.dark .rating-bar {
  background: #2d2b4e;
}

html.dark .review-stars .bi-star {
  color: #2d2b4e;
}

html.dark .empty-reviews i {
  color: #2d2b4e;
}

/* Responsive */
@media (max-width: 768px) {
  .rating-summary {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  
  .rating-number {
    font-size: 36px;
  }
  
  .review-header {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .verified-badge {
    margin-top: 4px;
  }
}

@media (max-width: 480px) {
  .rating-input {
    flex-wrap: wrap;
  }
  
  .star-input {
    font-size: 24px;
  }
  
  .reviewer-info {
    flex-direction: column;
    align-items: flex-start;
    gap: 4px;
  }
}
</style>