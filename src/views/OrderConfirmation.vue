<template>
  <div class="order-confirmation">
    <div class="container-custom">
      <div class="confirmation-content">
        <!-- Success Icon -->
        <div class="success-icon">
          <i class="bi bi-check-circle-fill"></i>
        </div>

        <h1>Order Placed Successfully!</h1>
        <p class="order-number">Order #{{ order?.orderNumber || 'N/A' }}</p>
        <p class="thank-you">Thank you for your purchase. We'll send you a confirmation email shortly.</p>

        <!-- Order Details -->
        <div v-if="order" class="order-details">
          <h3>Order Summary</h3>
          <div class="detail-row">
            <span>Order Date:</span>
            <span>{{ order.date }}</span>
          </div>
          <div class="detail-row">
            <span>Payment Method:</span>
            <span>{{ order.paymentMethod }}</span>
          </div>
          <div class="detail-row">
            <span>Shipping Address:</span>
            <span>{{ formatAddress(order.shipping) }}</span>
          </div>
          <div class="detail-row total">
            <span>Total:</span>
            <span>${{ order.total.toFixed(2) }}</span>
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
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'

const router = useRouter()
const route = useRoute()

const order = ref(null)

const loadOrder = () => {
  const orderId = Number(route.params.id)
  const orders = JSON.parse(localStorage.getItem('shopsphere_orders') || '[]')
  order.value = orders.find(o => o.id === orderId)
  
  if (!order.value) {
    router.push('/products')
  }
}

const formatAddress = (shipping) => {
  if (!shipping) return 'N/A'
  return `${shipping.fullName}, ${shipping.address}, ${shipping.city}, ${shipping.state} ${shipping.zip}, ${shipping.country}`
}

const continueShopping = () => {
  router.push('/products')
}

const viewOrders = () => {
  router.push('/orders')
}

onMounted(() => {
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
}

.detail-row.total {
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--text-primary);
  border-top: 1px solid var(--border-color);
  padding-top: 12px;
  margin-top: 8px;
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
}
</style>