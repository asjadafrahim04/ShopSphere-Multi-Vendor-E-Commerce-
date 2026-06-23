<template>
  <div class="orders-page">
    <div class="container-custom">
      <!-- Page Header -->
      <div class="page-header">
        <h1 class="page-title">My Orders</h1>
        <p class="page-subtitle">Track and manage your orders</p>
      </div>

      <!-- Orders List -->
      <div v-if="orders.length > 0" class="orders-list">
        <div v-for="order in sortedOrders" :key="order.id" class="order-card">
          <!-- Order Header -->
          <div class="order-header">
            <div class="order-info">
              <span class="order-number">Order #{{ order.orderNumber }}</span>
              <span class="order-date">
                <i class="bi bi-calendar3"></i>
                {{ order.date }}
              </span>
            </div>
            <div class="order-status">
              <span :class="['status-badge', getStatusClass(order.status)]">
                {{ getStatusLabel(order.status) }}
              </span>
            </div>
          </div>

          <!-- Order Items with Images -->
          <div class="order-items">
            <div v-for="item in order.items" :key="item.id" class="order-item">
              <div class="item-image">
                <img 
                  v-if="item.image" 
                  :src="item.image" 
                  :alt="item.name"
                  class="order-item-img"
                  loading="lazy"
                  @error="handleImageError"
                />
                <span v-else class="item-emoji">{{ item.emoji || '📦' }}</span>
              </div>
              <div class="item-details">
                <span class="item-name">{{ item.name }}</span>
                <span class="item-quantity">Qty: {{ item.quantity }}</span>
              </div>
              <span class="item-price">${{ (item.price * item.quantity).toFixed(2) }}</span>
            </div>
          </div>

          <!-- Order Footer -->
          <div class="order-footer">
            <div class="order-total">
              <span>Total:</span>
              <span class="total-amount">${{ order.total.toFixed(2) }}</span>
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
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// ===== STATE =====
const orders = ref([])

// ===== COMPUTED =====
const sortedOrders = computed(() => {
  return [...orders.value].sort((a, b) => b.id - a.id)
})

// ===== METHODS =====
const loadOrders = () => {
  const savedOrders = localStorage.getItem('shopsphere_orders')
  if (savedOrders) {
    orders.value = JSON.parse(savedOrders)
  } else {
    // Add some demo orders with images
    orders.value = [
      {
        id: 1,
        orderNumber: 'SPH-123456-A7B9',
        date: '15/06/2024',
        total: 179.97,
        status: 'delivered',
        items: [
          { 
            id: 1, 
            name: 'Wireless Noise-Cancelling Headphones', 
            price: 49.99, 
            quantity: 1, 
            emoji: '🎧', 
            imageBg: '#e8ecf1',
            image: 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?q=80&w=870&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'
          },
          { 
            id: 2, 
            name: 'Premium Leather Jacket', 
            price: 89.99, 
            quantity: 1, 
            emoji: '🧥', 
            imageBg: '#f8f0e8',
            image: 'https://images.unsplash.com/photo-1551028719-00167b16eac5?q=80&w=870&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'
          },
          { 
            id: 3, 
            name: 'Smart Coffee Maker Pro', 
            price: 39.99, 
            quantity: 1, 
            emoji: '☕', 
            imageBg: '#f0e8e0',
            image: 'https://images.unsplash.com/photo-1517668808822-9f02a4bcc53a?q=80&w=870&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'
          }
        ]
      },
      {
        id: 2,
        orderNumber: 'SPH-789012-C3D5',
        date: '10/06/2024',
        total: 199.99,
        status: 'shipped',
        items: [
          { 
            id: 4, 
            name: 'Fitness Smart Watch', 
            price: 199.99, 
            quantity: 1, 
            emoji: '⌚', 
            imageBg: '#e0e8f0',
            image: 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?q=80&w=870&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'
          }
        ]
      },
      {
        id: 3,
        orderNumber: 'SPH-345678-E1F2',
        date: '05/06/2024',
        total: 129.99,
        status: 'pending',
        items: [
          { 
            id: 6, 
            name: 'E-Reader Paperwhite', 
            price: 129.99, 
            quantity: 1, 
            emoji: '📚', 
            imageBg: '#e8f0f8',
            image: 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?q=80&w=870&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'
          }
        ]
      }
    ]
    saveOrders()
  }
}

const saveOrders = () => {
  localStorage.setItem('shopsphere_orders', JSON.stringify(orders.value))
}

const handleImageError = (e) => {
  e.target.style.display = 'none'
  const parent = e.target.parentElement
  const fallback = document.createElement('span')
  fallback.className = 'item-emoji'
  fallback.textContent = '📦'
  parent.appendChild(fallback)
}

const getStatusLabel = (status) => {
  const labels = {
    pending: 'Pending',
    processing: 'Processing',
    shipped: 'Shipped',
    delivered: 'Delivered',
    cancelled: 'Cancelled'
  }
  return labels[status] || status
}

const getStatusClass = (status) => {
  const classes = {
    pending: 'status-pending',
    processing: 'status-processing',
    shipped: 'status-shipped',
    delivered: 'status-delivered',
    cancelled: 'status-cancelled'
  }
  return classes[status] || 'status-pending'
}

const viewOrderDetails = (orderId) => {
  router.push(`/orders/${orderId}`)
}

const cancelOrder = (orderId) => {
  if (confirm('Are you sure you want to cancel this order?')) {
    const order = orders.value.find(o => o.id === orderId)
    if (order) {
      order.status = 'cancelled'
      saveOrders()
      alert('Order cancelled successfully!')
    }
  }
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

/* ===== PAGE HEADER ===== */
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

/* ===== ORDERS LIST ===== */
.orders-list {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

/* ===== ORDER CARD ===== */
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

/* ===== ORDER HEADER ===== */
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

/* ===== STATUS BADGE ===== */
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

/* ===== ORDER ITEMS WITH IMAGES ===== */
.order-items {
  padding: 16px 20px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.order-item {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 8px 0;
  border-bottom: 1px solid var(--border-color);
}

.order-item:last-child {
  border-bottom: none;
}

.item-image {
  width: 50px;
  height: 50px;
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg-secondary);
  flex-shrink: 0;
  overflow: hidden;
}

.order-item-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.item-emoji {
  font-size: 1.5rem;
}

.item-details {
  flex: 1;
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

/* ===== ORDER FOOTER ===== */
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

/* ===== EMPTY ORDERS ===== */
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

/* ===== DARK MODE ===== */
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
}

/* ===== RESPONSIVE ===== */
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
}

@media (max-width: 480px) {
  .order-item {
    flex-wrap: wrap;
  }

  .item-image {
    width: 40px;
    height: 40px;
  }

  .item-emoji {
    font-size: 1.2rem;
  }

  .item-price {
    margin-left: auto;
  }
}
</style>