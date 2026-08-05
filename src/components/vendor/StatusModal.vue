<template>
  <div class="modal-overlay" @click="$emit('close')">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h3>Update Order Status</h3>
        <button class="close-btn" @click="$emit('close')">✕</button>
      </div>
      <div class="modal-body">
        <p><strong>Order #{{ order?.order_number }}</strong></p>
        <p>Customer: {{ order?.user?.name }}</p>
        <p>Total: ${{ Number(order?.vendor_subtotal || order?.total || 0).toFixed(2) }}</p>
        
        <div class="form-group">
          <label>Status</label>
          <select v-model="selectedStatus" class="form-select">
            <option value="pending">Pending</option>
            <option value="processing">Processing</option>
            <option value="shipped">Shipped</option>
            <option value="delivered">Delivered</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>
        
        <div v-if="errorMessage" class="error-message">
          {{ errorMessage }}
        </div>
        
        <div class="modal-footer">
          <button class="btn-secondary" @click="$emit('close')">Cancel</button>
          <button class="btn-primary" @click="updateStatus" :disabled="loading">
            {{ loading ? 'Updating...' : 'Update Status' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

const props = defineProps({
  order: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'updated'])

const selectedStatus = ref(props.order?.status || 'pending')
const loading = ref(false)
const errorMessage = ref('')

const updateStatus = async () => {
  if (!props.order) return
  
  loading.value = true
  errorMessage.value = ''
  
  try {
    const token = localStorage.getItem('token')
    
    if (!token) {
      errorMessage.value = 'Please login again'
      loading.value = false
      return
    }
    
    // ✅ CORRECT URL: http://localhost:8000/api/vendor/orders/{id}/status
    const url = `http://localhost:8000/api/vendor/orders/${props.order.id}/status`
    
    console.log('📤 Updating status:', {
      url: url,
      order_id: props.order.id,
      status: selectedStatus.value
    })
    
    const response = await axios.put(
      url,
      { status: selectedStatus.value },
      {
        headers: { 
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        }
      }
    )
    
    console.log('✅ Status updated:', response.data)
    
    if (response.data.success) {
      alert('✅ Order status updated successfully!')
      emit('updated')
    } else {
      errorMessage.value = response.data.message || 'Failed to update status'
    }
  } catch (error) {
    console.error('❌ Failed to update status:', error)
    console.error('Response:', error.response?.data)
    
    if (error.response?.data?.message) {
      errorMessage.value = error.response.data.message
    } else if (error.response?.status === 404) {
      errorMessage.value = 'Order not found. Please refresh and try again.'
    } else if (error.response?.status === 403) {
      errorMessage.value = 'You are not authorized to update this order.'
    } else {
      errorMessage.value = 'Failed to update order status. Please try again.'
    }
  } finally {
    loading.value = false
  }
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
  max-width: 500px;
  width: 90%;
  padding: 24px;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
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

.modal-body p {
  margin: 8px 0;
}

.form-group {
  margin: 16px 0;
}

.form-group label {
  display: block;
  font-weight: 500;
  margin-bottom: 4px;
}

.form-select {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 14px;
}

.error-message {
  background: #fee2e2;
  color: #dc2626;
  padding: 12px;
  border-radius: 8px;
  font-size: 14px;
  margin: 12px 0;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 16px;
}

.btn-primary {
  padding: 8px 24px;
  background: #667eea;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
}

.btn-primary:hover:not(:disabled) {
  background: #5a67d8;
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
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