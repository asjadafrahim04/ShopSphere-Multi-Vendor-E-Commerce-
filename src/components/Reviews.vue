<template>
  <div class="reviews-section">
    <h3>Customer Reviews</h3>
    
    <!-- Rating Summary -->
    <div class="rating-summary">
      <div class="average-rating">
        <span class="rating-number">{{ averageRating }}</span>
        <div class="stars">
          <i v-for="n in 5" :key="n" :class="n <= Math.floor(averageRating) ? 'bi bi-star-fill' : 'bi bi-star'"></i>
        </div>
        <span class="review-count">{{ totalReviews }} reviews</span>
      </div>
      
      <!-- Rating Breakdown -->
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

    <!-- Write Review -->
    <div v-if="isLoggedIn" class="write-review">
      <h4>Write a Review</h4>
      <form @submit.prevent="submitReview">
        <div class="form-group">
          <label>Rating</label>
          <div class="rating-input">
            <i 
              v-for="n in 5" 
              :key="n" 
              :class="n <= reviewForm.rating ? 'bi bi-star-fill' : 'bi bi-star'"
              @click="reviewForm.rating = n"
              class="star-input"
            ></i>
          </div>
        </div>
        <div class="form-group">
          <label>Comment</label>
          <textarea v-model="reviewForm.comment" rows="4" placeholder="Share your experience..."></textarea>
        </div>
        <button type="submit" class="btn-primary" :disabled="submitting">
          {{ submitting ? 'Submitting...' : 'Submit Review' }}
        </button>
      </form>
    </div>

    <!-- Reviews List -->
    <div class="reviews-list">
      <div v-if="loading" class="loading">Loading reviews...</div>
      <div v-else-if="reviews.length === 0" class="empty">No reviews yet</div>
      <div v-else v-for="review in reviews" :key="review.id" class="review-item">
        <div class="review-header">
          <span class="reviewer">{{ review.user?.name }}</span>
          <div class="stars">
            <i v-for="n in 5" :key="n" :class="n <= review.rating ? 'bi bi-star-fill' : 'bi bi-star'"></i>
          </div>
          <span class="review-date">{{ formatDate(review.created_at) }}</span>
          <span v-if="review.is_verified" class="verified-badge">✓ Verified Purchase</span>
        </div>
        <p class="review-comment">{{ review.comment }}</p>
        <button v-if="canDelete(review)" class="delete-review" @click="deleteReview(review.id)">Delete</button>
      </div>
    </div>

    <!-- Pagination -->
    <Pagination 
      v-if="pagination.last_page > 1"
      :current-page="pagination.current_page"
      :total-pages="pagination.last_page"
      @page-change="loadReviews"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import Pagination from './Pagination.vue'

const props = defineProps({
  productId: {
    type: Number,
    required: true
  }
})

const reviews = ref([])
const loading = ref(true)
const submitting = ref(false)
const averageRating = ref(0)
const totalReviews = ref(0)
const ratingBreakdown = ref({})
const isLoggedIn = ref(false)

const reviewForm = ref({
  rating: 0,
  comment: ''
})

const pagination = ref({
  current_page: 1,
  last_page: 1
})

const loadReviews = async (page = 1) => {
  loading.value = true
  try {
    const response = await fetch(`http://localhost:8000/api/products/${props.productId}/reviews?page=${page}`)
    const data = await response.json()
    
    if (data.success) {
      reviews.value = data.data.reviews.data
      averageRating.value = data.data.average_rating
      totalReviews.value = data.data.total_reviews
      ratingBreakdown.value = data.data.rating_breakdown
      pagination.value.current_page = data.data.reviews.current_page
      pagination.value.last_page = data.data.reviews.last_page
    }
  } catch (error) {
    console.error('Error loading reviews:', error)
  } finally {
    loading.value = false
  }
}

const getPercentage = (rating) => {
  if (totalReviews.value === 0) return 0
  return (ratingBreakdown.value[rating] || 0) / totalReviews.value * 100
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
        'Accept': 'application/json'
      }
    })
    
    if (response.data.success) {
      alert('✅ Review submitted!')
      reviewForm.value = { rating: 0, comment: '' }
      loadReviews()
    }
  } catch (error) {
    console.error('Error submitting review:', error)
    alert('Failed to submit review')
  } finally {
    submitting.value = false
  }
}

const deleteReview = async (reviewId) => {
  if (!confirm('Delete this review?')) return
  
  try {
    const token = localStorage.getItem('token')
    await axios.delete(`http://localhost:8000/api/reviews/${reviewId}`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })
    alert('✅ Review deleted!')
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
  return new Date(date).toLocaleDateString('en-US', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  })
}

onMounted(() => {
  loadReviews()
  const token = localStorage.getItem('token')
  isLoggedIn.value = !!token
})
</script>

<style scoped>
.reviews-section {
  margin-top: 40px;
  padding-top: 30px;
  border-top: 1px solid #e5e7eb;
}

.reviews-section h3 {
  font-size: 20px;
  font-weight: 700;
  margin-bottom: 20px;
}

.rating-summary {
  display: grid;
  grid-template-columns: 1fr 2fr;
  gap: 30px;
  background: #f9fafb;
  padding: 20px;
  border-radius: 12px;
  margin-bottom: 30px;
}

.average-rating {
  text-align: center;
}

.rating-number {
  font-size: 40px;
  font-weight: 700;
  color: #667eea;
}

.stars {
  display: flex;
  gap: 4px;
  justify-content: center;
  color: #f59e0b;
  font-size: 18px;
}

.stars .bi-star {
  color: #d1d5db;
}

.review-count {
  color: #6b7280;
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
}

.rating-bar {
  flex: 1;
  height: 8px;
  background: #e5e7eb;
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
  color: #6b7280;
  min-width: 30px;
}

.write-review {
  background: #f9fafb;
  padding: 20px;
  border-radius: 12px;
  margin-bottom: 30px;
}

.write-review h4 {
  font-weight: 600;
  margin-bottom: 16px;
}

.rating-input {
  display: flex;
  gap: 8px;
}

.star-input {
  font-size: 28px;
  color: #d1d5db;
  cursor: pointer;
  transition: all 0.2s ease;
}

.star-input.bi-star-fill {
  color: #f59e0b;
}

.star-input:hover {
  transform: scale(1.1);
}

.write-review textarea {
  width: 100%;
  padding: 12px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  resize: vertical;
  font-family: inherit;
}

.btn-primary {
  padding: 10px 24px;
  background: #667eea;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
}

.btn-primary:hover {
  background: #5a67d8;
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.reviews-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.review-item {
  background: #f9fafb;
  padding: 16px;
  border-radius: 8px;
}

.review-header {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.reviewer {
  font-weight: 600;
}

.review-date {
  font-size: 13px;
  color: #6b7280;
}

.verified-badge {
  font-size: 12px;
  color: #059669;
  font-weight: 600;
}

.review-comment {
  margin: 8px 0 0 0;
  color: #4b5563;
}

.delete-review {
  margin-top: 8px;
  background: none;
  border: none;
  color: #ef4444;
  cursor: pointer;
  font-size: 13px;
}

.delete-review:hover {
  text-decoration: underline;
}

.loading, .empty {
  text-align: center;
  padding: 20px;
  color: #6b7280;
}

@media (max-width: 768px) {
  .rating-summary {
    grid-template-columns: 1fr;
  }
}
</style>