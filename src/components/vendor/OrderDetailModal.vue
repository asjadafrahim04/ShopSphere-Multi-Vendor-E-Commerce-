<template>
  <div class="modal-overlay" @click="$emit('close')">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h3>Order Details</h3>
        <button class="close-btn" @click="$emit('close')">✕</button>
      </div>
      
      <div v-if="order" class="modal-body">
        <!-- Order Info -->
        <div class="order-info">
          <div><strong>Order #:</strong> {{ order.order_number }}</div>
          <div><strong>Date:</strong> {{ formatDate(order.created_at) }}</div>
          <div><strong>Status:</strong> 
            <span class="status-badge" :class="'status-' + order.status">{{ order.status }}</span>
          </div>
          <div><strong>Payment:</strong> {{ order.payment_method || 'N/A' }}</div>
        </div>

        <!-- Customer Info -->
        <div class="section">
          <h4>Customer Details</h4>
          <div><strong>Name:</strong> {{ order.user?.name }}</div>
          <div><strong>Email:</strong> {{ order.user?.email }}</div>
          <div><strong>Phone:</strong> {{ order.shipping_address?.phone || 'N/A' }}</div>
          <div><strong>Address:</strong> {{ getAddress() }}</div>
        </div>

        <!-- Items -->
        <div class="section">
          <h4>Items</h4>
          <table class="items-table">
            <thead>
              <tr>
                <th>Product</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Total</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in order.items" :key="item.id">
                <td>{{ item.product?.name }}</td>
                <td>{{ item.quantity }}</td>
                <td>${{ item.price }}</td>
                <td>${{ item.total }}</td>
              </tr>
            </tbody>
            <tfoot>
              <tr>
                <td colspan="3" class="text-right"><strong>Subtotal:</strong></td>
                <td>${{ order.vendor_subtotal?.toFixed(2) || order.subtotal?.toFixed(2) || 0 }}</td>
              </tr>
              <tr>
                <td colspan="3" class="text-right"><strong>Shipping:</strong></td>
                <td>${{ order.shipping_cost?.toFixed(2) || 0 }}</td>
              </tr>
              <tr>
                <td colspan="3" class="text-right"><strong>Tax:</strong></td>
                <td>${{ order.tax?.toFixed(2) || 0 }}</td>
              </tr>
              <tr>
                <td colspan="3" class="text-right"><strong>Total:</strong></td>
                <td class="total">${{ order.total?.toFixed(2) || 0 }}</td>
              </tr>
            </tfoot>
          </table>
        </div>

        <div class="modal-footer">
          <button class="btn-secondary" @click="$emit('close')">Close</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  order: {
    type: Object,
    default: null
  }
})

defineEmits(['close'])

const formatDate = (date) => {
  return new Date(date).toLocaleString('en-US', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getAddress = () => {
  const addr = props.order?.shipping_address
  if (!addr) return 'N/A'
  return `${addr.address}, ${addr.city}, ${addr.district}`
}
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 12px;
  max-width: 700px;
  width: 95%;
  max-height: 90vh;
  overflow-y: auto;
  padding: 24px;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
  padding-bottom: 12px;
  border-bottom: 1px solid #e5e7eb;
}

.modal-header h3 {
  margin: 0;
}

.close-btn {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #6b7280;
}

.order-info {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
  padding: 12px;
  background: #f9fafb;
  border-radius: 8px;
  margin-bottom: 16px;
}

.section {
  margin-bottom: 20px;
}

.section h4 {
  font-weight: 600;
  margin: 0 0 8px 0;
  color: #1a1a2e;
}

.items-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

.items-table th {
  text-align: left;
  padding: 8px 12px;
  background: #f9fafb;
  border-bottom: 1px solid #e5e7eb;
}

.items-table td {
  padding: 8px 12px;
  border-bottom: 1px solid #f3f4f6;
}

.items-table tfoot td {
  border-bottom: none;
  padding: 6px 12px;
}

.text-right {
  text-align: right;
}

.total {
  font-weight: 700;
  color: #667eea;
}

.status-badge {
  padding: 2px 10px;
  border-radius: 50px;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
}

.status-pending { background: #fef3c7; color: #d97706; }
.status-processing { background: #dbeafe; color: #2563eb; }
.status-shipped { background: #e0e7ff; color: #4f46e5; }
.status-delivered { background: #d1fae5; color: #059669; }
.status-cancelled { background: #fee2e2; color: #dc2626; }

.modal-footer {
  display: flex;
  justify-content: flex-end;
  margin-top: 16px;
  padding-top: 16px;
  border-top: 1px solid #e5e7eb;
}

.btn-secondary {
  padding: 8px 24px;
  background: #e5e7eb;
  color: #1a1a2e;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
}

.btn-secondary:hover {
  background: #d1d5db;
}
</style>