<template>
  <div class="orders-page">
    <div class="container-custom">
      <!-- Page Header -->
      <div class="page-header">
        <h1 class="page-title">My Orders</h1>
        <p class="page-subtitle">Track and manage your orders</p>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="loading-state">
        <div class="spinner"></div>
        <p>Loading your orders...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="error-state">
        <i class="bi bi-exclamation-circle" style="font-size: 3rem; color: #ef4444;"></i>
        <p>{{ error }}</p>
        <button class="btn-primary-modern" @click="loadOrders">Try Again</button>
      </div>

      <!-- Orders List -->
      <div v-else-if="orders.length > 0" class="orders-list">
        <div v-for="order in sortedOrders" :key="order.id" class="order-card">
          <!-- Order Header -->
          <div class="order-header">
            <div class="order-info">
              <span class="order-number">Order #{{ order.order_number }}</span>
              <span class="order-date">
                <i class="bi bi-calendar3"></i>
                {{ formatDate(order.created_at) }}
              </span>
            </div>
            <div class="order-status">
              <span :class="['status-badge', getStatusClass(order.status)]">
                {{ getStatusLabel(order.status) }}
              </span>
            </div>
          </div>

          <!-- Order Items -->
          <div class="order-items">
            <div v-for="item in order.items" :key="item.id" class="order-item">
              <div class="item-details">
                <span class="item-name">{{ item.name || item.product?.name || 'Product' }}</span>
                <span class="item-quantity">Qty: {{ item.quantity }}</span>
              </div>
              <div class="item-actions">
                <span class="item-price">${{ (item.price * item.quantity).toFixed(2) }}</span>
                
                <!-- ✅ WRITE REVIEW BUTTON - Only for delivered orders -->
                <button 
                  v-if="order.status === 'delivered' && !hasReviewed(item.product_id)"
                  class="btn-review" 
                  @click="openReviewModal(order, item)"
                >
                  <i class="bi bi-star"></i> Write Review
                </button>
                
                <!-- ✅ Already Reviewed Badge -->
                <span v-else-if="order.status === 'delivered' && hasReviewed(item.product_id)" class="reviewed-badge">
                  <i class="bi bi-check-circle-fill"></i> Reviewed
                </span>
              </div>
            </div>
          </div>

          <!-- Order Footer -->
          <div class="order-footer">
            <div class="order-total">
              <span>Total:</span>
              <span class="total-amount">${{ parseFloat(order.total).toFixed(2) }}</span>
            </div>
            <div class="order-actions">
              <button class="btn-outline-modern" @click="viewOrderDetails(order.id)">
                <i class="bi bi-eye me-2"></i>View Details
              </button>
              <button v-if="order.status === 'pending'" class="btn-outline-modern" @click="cancelOrder(order.id)">
                <i class="bi bi-x-circle me-2"></i>Cancel Order
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty Orders -->
      <div v-else class="empty-orders">
        <div class="empty-state">
          <i class="bi bi-box" style="font-size: 5rem; color: var(--text-muted);"></i>
          <h3>No Orders Yet</h3>
          <p>You haven't placed any orders yet. Start shopping now!</p>
          <button class="btn-primary-modern" @click="continueShopping">
            <i class="bi bi-arrow-left me-2"></i>Start Shopping
          </button>
        </div>
      </div>
    </div>

    <!-- Review Modal -->
    <ReviewModal
      v-if="showReviewModal"
      :order="selectedOrder"
      :product="selectedProduct"
      @close="showReviewModal = false"
      @submitted="onReviewSubmitted"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { orderApi } from '@/services/api'
import ReviewModal from '@/components/ReviewModal.vue'

const router = useRouter()

// ===== STATE =====
const orders = ref([])
const isLoading = ref(true)
const error = ref(null)
const showReviewModal = ref(false)
const selectedOrder = ref(null)
const selectedProduct = ref(null)
const reviewedProducts = ref([])

// ===== COMPUTED =====
const sortedOrders = computed(() => {
  return [...orders.value].sort((a, b) => b.id - a.id)
})

// ===== METHODS =====
const loadOrders = async () => {
  isLoading.value = true
  error.value = null
  
  try {
    const token = localStorage.getItem('token')
    
    if (!token) {
      loadFromLocalStorage()
      isLoading.value = false
      return
    }
    
    const response = await orderApi.getOrders()
    
    if (response.data.success) {
      orders.value = response.data.data || []
      await loadReviewedProducts()
    } else {
      loadFromLocalStorage()
    }
  } catch (err) {
    console.error('Error loading orders:', err)
    error.value = 'Failed to load orders. Please try again.'
    loadFromLocalStorage()
  } finally {
    isLoading.value = false
  }
}

const loadFromLocalStorage = () => {
  const savedOrders = localStorage.getItem('shopsphere_orders')
  if (savedOrders) {
    orders.value = JSON.parse(savedOrders)
  } else {
    orders.value = []
  }
}

const loadReviewedProducts = async () => {
  try {
    const token = localStorage.getItem('token')
    const response = await axios.get('http://localhost:8000/api/user/reviews', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })
    if (response.data.success) {
      reviewedProducts.value = response.data.data.map(r => r.product_id)
    }
  } catch (error) {
    console.error('Error loading reviewed products:', error)
  }
}

const hasReviewed = (productId) => {
  return reviewedProducts.value.includes(productId)
}

const openReviewModal = (order, item) => {
  selectedOrder.value = order
  selectedProduct.value = item.product
  showReviewModal.value = true
}

const onReviewSubmitted = () => {
  showReviewModal.value = false
  loadReviewedProducts()
  alert('✅ Review submitted successfully!')
}

const cancelOrder = async (orderId) => {
  if (!confirm('Are you sure you want to cancel this order?')) return

  try {
    const token = localStorage.getItem('token')
    
    if (token) {
      const response = await orderApi.cancelOrder(orderId)
      if (response.data.success) {
        alert('Order cancelled successfully!')
        await loadOrders()
        return
      }
    }
    
    const order = orders.value.find(o => o.id === orderId)
    if (order) {
      order.status = 'cancelled'
      localStorage.setItem('shopsphere_orders', JSON.stringify(orders.value))
      alert('Order cancelled successfully!')
    }
  } catch (error) {
    console.error('Error cancelling order:', error)
    alert('Failed to cancel order. Please try again.')
  }
}

const viewOrderDetails = (orderId) => {
  router.push(`/order-confirmation/${orderId}`)
}

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('en-BD', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  })
}

const getStatusLabel = (status) => {
  const labels = {
    pending: 'Pending',
    processing: 'Processing',
    shipped: 'Shipped',
    delivered: 'Delivered',
    cancelled: 'Cancelled',
    refunded: 'Refunded'
  }
  return labels[status] || status
}

const getStatusClass = (status) => {
  const classes = {
    pending: 'status-pending',
    processing: 'status-processing',
    shipped: 'status-shipped',
    delivered: 'status-delivered',
    cancelled: 'status-cancelled',
    refunded: 'status-refunded'
  }
  return classes[status] || 'status-pending'
}

const continueShopping = () => {
  router.push('/products')
}

// ===== LIFECYCLE =====
onMounted(() => {
  loadOrders()
})
</script>

<style scoped>
.orders-page {
  padding: 40px 0 80px;
  background: var(--bg-primary);
  min-height: 100vh;
}

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

.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 300px;
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

.error-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 300px;
  gap: 16px;
  text-align: center;
}

.error-state p {
  color: var(--text-secondary);
  font-size: 1.05rem;
}

.orders-list {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.order-card {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: var(--radius);
  overflow: hidden;
  transition: var(--transition);
}

.order-card:hover {
  box-shadow: var(--shadow-hover);
}

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  background: var(--bg-secondary);
  border-bottom: 1px solid var(--border-color);
  flex-wrap: wrap;
  gap: 12px;
}

.order-info {
  display: flex;
  align-items: center;
  gap: 20px;
  flex-wrap: wrap;
}

.order-number {
  font-weight: 600;
  color: var(--text-primary);
  font-size: 1rem;
}

.order-date {
  color: var(--text-muted);
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  gap: 6px;
}

.status-badge {
  padding: 4px 16px;
  border-radius: 50px;
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.status-pending {
  background: #fef3c7;
  color: #d97706;
}

.status-processing {
  background: #dbeafe;
  color: #2563eb;
}

.status-shipped {
  background: #e0e7ff;
  color: #4f46e5;
}

.status-delivered {
  background: #d1fae5;
  color: #059669;
}

.status-cancelled {
  background: #fee2e2;
  color: #dc2626;
}

.status-refunded {
  background: #f3e8ff;
  color: #7c3aed;
}

.order-items {
  padding: 16px 20px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.order-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
  border-bottom: 1px solid var(--border-color);
}

.order-item:last-child {
  border-bottom: none;
}

.item-details {
  display: flex;
  flex-direction: column;
}

.item-name {
  font-weight: 500;
  color: var(--text-primary);
}

.item-quantity {
  font-size: 0.8rem;
  color: var(--text-muted);
}

.item-price {
  font-weight: 600;
  color: var(--text-primary);
}

.item-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}

.btn-review {
  padding: 4px 12px;
  background: #667eea;
  border: none;
  border-radius: 4px;
  color: white;
  font-size: 11px;
  font-weight: 600;
  cursor: pointer;
  transition: var(--transition);
  display: flex;
  align-items: center;
  gap: 4px;
  white-space: nowrap;
}

.btn-review:hover {
  background: #5a67d8;
  transform: translateY(-1px);
}

.btn-review i {
  font-size: 12px;
}

.reviewed-badge {
  padding: 4px 12px;
  background: #d1fae5;
  color: #059669;
  border-radius: 4px;
  font-size: 11px;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 4px;
  white-space: nowrap;
}

.reviewed-badge i {
  font-size: 12px;
}

.order-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  border-top: 1px solid var(--border-color);
  background: var(--bg-secondary);
  flex-wrap: wrap;
  gap: 12px;
}

.order-total {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 1rem;
}

.order-total span:first-child {
  color: var(--text-secondary);
}

.total-amount {
  font-weight: 700;
  color: #667eea;
  font-size: 1.1rem;
}

.order-actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.order-actions .btn-outline-modern {
  padding: 8px 16px;
  font-size: 0.85rem;
}

.empty-orders {
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

/* Dark Mode */
html.dark .btn-review {
  background: #667eea;
}

html.dark .btn-review:hover {
  background: #5a67d8;
}

html.dark .reviewed-badge {
  background: #1a3a2a;
  color: #34d399;
}

@media (prefers-color-scheme: dark) {
  .status-pending {
    background: rgba(251, 191, 36, 0.2);
    color: #fbbf24;
  }
  .status-processing {
    background: rgba(59, 130, 246, 0.2);
    color: #60a5fa;
  }
  .status-shipped {
    background: rgba(99, 102, 241, 0.2);
    color: #818cf8;
  }
  .status-delivered {
    background: rgba(52, 211, 153, 0.2);
    color: #34d399;
  }
  .status-cancelled {
    background: rgba(239, 68, 68, 0.2);
    color: #f87171;
  }
  .status-refunded {
    background: rgba(192, 132, 252, 0.2);
    color: #a78bfa;
  }
}

@media (max-width: 768px) {
  .orders-page {
    padding: 20px 0 60px;
  }

  .page-title {
    font-size: 2rem;
  }

  .order-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .order-footer {
    flex-direction: column;
    align-items: stretch;
  }

  .order-actions {
    flex-direction: column;
  }

  .order-actions .btn-outline-modern {
    width: 100%;
    text-align: center;
  }

  .order-info {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }
  
  .order-item {
    flex-wrap: wrap;
  }
  
  .item-actions {
    flex-wrap: wrap;
    justify-content: flex-start;
  }
}

@media (max-width: 480px) {
  .order-item {
    flex-wrap: wrap;
    gap: 4px;
  }

  .item-price {
    margin-left: auto;
  }
  
  .item-actions {
    width: 100%;
    justify-content: flex-start;
  }
}
</style>