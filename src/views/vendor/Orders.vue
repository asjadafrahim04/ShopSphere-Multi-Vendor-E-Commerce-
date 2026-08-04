<template>
  <div class="vendor-orders">
    <div class="page-header">
      <h1>Orders</h1>
      <p>Manage your customer orders</p>
    </div>

    <div class="orders-wrapper">
      <div v-if="loading" class="loading-state">Loading orders...</div>
      <div v-else-if="orders.length > 0" class="orders-list">
        <div v-for="order in orders" :key="order.id" class="order-card">
          <div class="order-header">
            <div class="order-info">
              <span class="order-number">#{{ order.order_number }}</span>
              <span class="order-date">{{ formatDate(order.created_at) }}</span>
              <span class="customer-info">
                <i class="bi bi-person"></i>
                {{ order.user?.name || 'Guest' }}
              </span>
              <span class="order-total">${{ order.total.toFixed(2) }}</span>
            </div>
            <div class="order-actions">
              <select v-model="order.status" @change="updateStatus(order.id, order.status)" class="status-select">
                <option value="pending">Pending</option>
                <option value="processing">Processing</option>
                <option value="shipped">Shipped</option>
                <option value="delivered">Delivered</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>
          </div>
          <div class="order-items">
            <div v-for="item in order.items" :key="item.id" class="order-item">
              <span class="item-name">{{ item.name || item.product?.name }}</span>
              <span class="item-qty">Qty: {{ item.quantity }}</span>
              <span class="item-price">${{ (item.price * item.quantity).toFixed(2) }}</span>
            </div>
          </div>
        </div>
      </div>
      <div v-else class="empty-state">
        <i class="bi bi-inbox"></i>
        <p>No orders yet</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { vendorApi } from '@/services/api'

const orders = ref([])
const loading = ref(true)

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('en-BD', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  })
}

const loadOrders = async () => {
  loading.value = true
  try {
    const response = await vendorApi.getVendorOrders()
    if (response.data.success) {
      orders.value = response.data.data.data || []
    }
  } catch (error) {
    console.error('Error loading orders:', error)
  } finally {
    loading.value = false
  }
}

const updateStatus = async (orderId, status) => {
  try {
    await vendorApi.updateOrderStatus(orderId, status)
    alert('Order status updated!')
  } catch (error) {
    alert('Failed to update status.')
    loadOrders()
  }
}

onMounted(() => {
  loadOrders()
})
</script>

<style scoped>
.vendor-orders {
  padding: 24px;
}

.page-header {
  margin-bottom: 24px;
}

.page-header h1 {
  font-size: 2rem;
  font-weight: 700;
  color: var(--text-primary);
}

.page-header p {
  color: var(--text-secondary);
}

.orders-wrapper {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: var(--radius);
  overflow: hidden;
}

.orders-list {
  padding: 16px;
}

.order-card {
  background: var(--bg-secondary);
  border-radius: var(--radius-sm);
  padding: 16px;
  margin-bottom: 12px;
}

.order-card:last-child {
  margin-bottom: 0;
}

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 12px;
  margin-bottom: 12px;
}

.order-info {
  display: flex;
  align-items: center;
  gap: 16px;
  flex-wrap: wrap;
}

.order-number {
  font-weight: 600;
  color: var(--text-primary);
}

.order-date {
  color: var(--text-muted);
  font-size: 0.85rem;
}

.customer-info {
  color: var(--text-secondary);
  font-size: 0.9rem;
}

.order-total {
  font-weight: 700;
  color: #667eea;
}

.status-select {
  padding: 6px 12px;
  border: 1px solid var(--border-color);
  border-radius: var(--radius-sm);
  background: var(--bg-card);
  color: var(--text-primary);
  font-size: 0.85rem;
  cursor: pointer;
}

.order-items {
  border-top: 1px solid var(--border-color);
  padding-top: 12px;
}

.order-item {
  display: flex;
  justify-content: space-between;
  padding: 4px 0;
  font-size: 0.9rem;
  color: var(--text-secondary);
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

@media (max-width: 768px) {
  .vendor-orders {
    padding: 16px;
  }

  .order-header {
    flex-direction: column;
    align-items: stretch;
  }

  .order-info {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }

  .order-items {
    font-size: 0.85rem;
  }
}
</style>