<template>
  <div class="recent-section">
    <div class="section-header">
      <h2>Recent Orders</h2>
      <span class="order-count">{{ orders.length }} orders</span>
    </div>
    
    <div v-if="orders.length > 0" class="order-list">
      <div 
        v-for="order in orders" 
        :key="order.id" 
        class="order-item"
        @click="$emit('view-order', order.id)"
      >
        <div class="order-info">
          <span class="order-number">#{{ order.order_number || order.id }}</span>
          <span class="order-date">{{ formatDate(order.created_at) }}</span>
          <span class="order-status" :class="'status-' + order.status">
            {{ order.status }}
          </span>
        </div>
        <div class="order-total">
          ${{ (order.total || 0).toFixed(2) }}
        </div>
      </div>
    </div>
    
    <div v-else class="empty-state">
      <i class="bi bi-inbox"></i>
      <p>No orders yet</p>
      <span class="empty-sub">Your orders will appear here</span>
    </div>
  </div>
</template>

<script setup>
defineProps({
  orders: {
    type: Array,
    default: () => []
  },
  formatDate: {
    type: Function,
    required: true
  }
})

defineEmits(['view-order'])
</script>

<style scoped>
.recent-section {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 20px;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.section-header h2 {
  font-size: 1.1rem;
  font-weight: 600;
  color: #1a1a2e;
  margin: 0;
}

.order-count {
  font-size: 0.85rem;
  color: #6b7280;
  background: #f3f4f6;
  padding: 2px 12px;
  border-radius: 50px;
}

.order-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 0;
  border-bottom: 1px solid #f3f4f6;
  cursor: pointer;
  transition: all 0.2s ease;
}

.order-item:hover {
  padding-left: 8px;
  background: #f9fafb;
  margin: 0 -8px;
  padding-right: 8px;
  border-radius: 6px;
}

.order-item:last-child {
  border-bottom: none;
}

.order-info {
  display: flex;
  align-items: center;
  gap: 16px;
  flex-wrap: wrap;
}

.order-number {
  font-weight: 600;
  color: #1a1a2e;
}

.order-date {
  color: #6b7280;
  font-size: 0.85rem;
}

.order-status {
  padding: 2px 12px;
  border-radius: 50px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}

.order-total {
  font-weight: 600;
  color: #667eea;
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

.empty-state {
  text-align: center;
  padding: 30px 20px;
  color: #6b7280;
}

.empty-state i {
  font-size: 2.5rem;
  display: block;
  margin-bottom: 12px;
  opacity: 0.5;
}

.empty-state p {
  font-weight: 500;
  margin: 0;
}

.empty-sub {
  font-size: 0.85rem;
  opacity: 0.7;
}

@media (max-width: 768px) {
  .order-item {
    flex-direction: column;
    align-items: stretch;
    gap: 8px;
  }
  
  .order-info {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }
}
</style>