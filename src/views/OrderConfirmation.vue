<template>
  <div class="order-confirmation">
    <div class="container-custom">
      <!-- Loading State -->
      <div v-if="isLoading" class="loading-state">
        <div class="spinner"></div>
        <p>Loading order details...</p>
      </div>

      <!-- Order Details -->
      <div v-else-if="order" class="confirmation-content">
        <div class="success-icon">
          <i class="bi bi-check-circle-fill"></i>
        </div>

        <h1>Order Placed Successfully! 🎉</h1>
        <p class="order-number">Order #{{ order.order_number }}</p>
        <p class="thank-you">Thank you for your purchase!</p>

        <!-- Order Details -->
        <div class="order-details">
          <h3>Order Summary</h3>
          <div class="detail-row">
            <span>Order Date:</span>
            <span>{{ formatDate(order.created_at) }}</span>
          </div>
          <div class="detail-row">
            <span>Payment Method:</span>
            <span>{{ getPaymentLabel(order.payment_method) }}</span>
          </div>
          <div class="detail-row">
            <span>Status:</span>
            <span class="status-pending">Pending</span>
          </div>
          <div class="detail-row total">
            <span>Total:</span>
            <span>${{ parseFloat(order.total).toFixed(2) }}</span>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
          <button class="btn-primary-modern" @click="continueShopping">
            <i class="bi bi-arrow-left me-2"></i>Continue Shopping
          </button>
          <button class="btn-outline-modern" @click="viewOrders">
            <i class="bi bi-list-ul me-2"></i>View My Orders
          </button>
        </div>
      </div>

      <!-- Order Not Found -->
      <div v-else class="not-found">
        <i class="bi bi-exclamation-triangle" style="font-size: 4rem; color: var(--text-muted);"></i>
        <h3>Order Not Found</h3>
        <p>The order you're looking for doesn't exist.</p>
        <button class="btn-primary-modern" @click="continueShopping">
          <i class="bi bi-arrow-left me-2"></i>Continue Shopping
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { orderApi } from '@/services/api'

const router = useRouter()
const route = useRoute()

const isLoading = ref(true)
const order = ref(null)

// ===== PAYMENT LABELS =====
const paymentLabels = {
  bkash: 'bKash',
  nagad: 'Nagad',
  rocket: 'Rocket (DBBL)',
  cod: 'Cash on Delivery',
  card: 'Credit/Debit Card'
}

// ===== METHODS =====
const loadOrder = async () => {
  console.log('🔄 Loading order...')
  const orderId = Number(route.params.id)
  console.log('📌 Order ID:', orderId)
  
  isLoading.value = true

  try {
    const token = localStorage.getItem('token')
    console.log('🔑 Token:', token ? '✅ exists' : '❌ missing')
    
    if (token) {
      console.log('📡 Fetching from API...')
      const response = await orderApi.getOrder(orderId)
      console.log('📦 Response:', response.data)
      
      if (response.data.success) {
        order.value = response.data.data
        console.log('✅ Order loaded:', order.value)
      }
    }
  } catch (error) {
    console.error('❌ Error:', error)
  } finally {
    isLoading.value = false
  }
}

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('en-BD', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getPaymentLabel = (method) => {
  return paymentLabels[method] || method || 'N/A'
}

const continueShopping = () => {
  router.push('/products')
}

const viewOrders = () => {
  router.push('/orders')
}

// ===== LIFECYCLE =====
onMounted(() => {
  console.log('🚀 Component mounted')
  loadOrder()
})
</script>

<style scoped>
.order-confirmation {
  padding: 80px 0;
  background: var(--bg-primary);
  min-height: 100vh;
  display: flex;
  align-items: center;
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

.confirmation-content {
  max-width: 600px;
  margin: 0 auto;
  text-align: center;
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: var(--radius);
  padding: 48px;
}

.success-icon {
  font-size: 5rem;
  color: #22c55e;
  margin-bottom: 16px;
}

.confirmation-content h1 {
  font-size: 2rem;
  font-weight: 700;
  color: var(--text-primary);
  margin-bottom: 8px;
}

.order-number {
  font-size: 1.1rem;
  color: var(--text-secondary);
  font-weight: 600;
}

.thank-you {
  color: var(--text-muted);
  margin: 8px 0 24px;
}

.order-details {
  text-align: left;
  background: var(--bg-secondary);
  border-radius: var(--radius-sm);
  padding: 20px;
  margin: 20px 0;
}

.order-details h3 {
  font-size: 1.1rem;
  font-weight: 600;
  color: var(--text-primary);
  margin-bottom: 12px;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  padding: 6px 0;
  color: var(--text-secondary);
  border-bottom: 1px solid var(--border-color);
}

.detail-row:last-child {
  border-bottom: none;
}

.detail-row.total {
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--text-primary);
  border-top: 2px solid var(--border-color);
  padding-top: 12px;
  margin-top: 4px;
}

.status-pending {
  background: #fef3c7;
  color: #d97706;
  padding: 2px 12px;
  border-radius: 50px;
  font-size: 0.8rem;
  font-weight: 600;
}

.action-buttons {
  display: flex;
  gap: 12px;
  margin-top: 24px;
}

.action-buttons .btn-primary-modern,
.action-buttons .btn-outline-modern {
  flex: 1;
  text-align: center;
  padding: 12px;
}

.not-found {
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
}

.not-found h3 {
  font-size: 1.8rem;
  color: var(--text-primary);
  margin: 0;
}

.not-found p {
  color: var(--text-muted);
  font-size: 1.05rem;
}

@media (max-width: 576px) {
  .order-confirmation {
    padding: 40px 0;
  }

  .confirmation-content {
    padding: 24px;
  }

  .success-icon {
    font-size: 3.5rem;
  }

  .confirmation-content h1 {
    font-size: 1.5rem;
  }

  .action-buttons {
    flex-direction: column;
  }

  .detail-row {
    flex-direction: column;
    gap: 4px;
  }
}
</style>