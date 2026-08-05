<template>
  <div class="vendor-orders">
    <div class="page-header">
      <div>
        <h1>Orders</h1>
        <p class="text-muted">Manage your orders</p>
      </div>
    </div>

    <!-- Debug Info -->
    <div v-if="debugInfo" class="debug-info">
      {{ debugInfo }}
    </div>

    <!-- Filters -->
    <div class="filters-bar">
      <div class="search-box">
        <i class="bi bi-search"></i>
        <input 
          v-model="filters.search" 
          placeholder="Search orders..." 
          @input="applyFilters"
        />
      </div>
      <div class="filter-group">
        <select v-model="filters.status" @change="applyFilters">
          <option value="">All Status</option>
          <option value="pending">Pending</option>
          <option value="processing">Processing</option>
          <option value="shipped">Shipped</option>
          <option value="delivered">Delivered</option>
          <option value="cancelled">Cancelled</option>
        </select>
        <select v-model="filters.sort" @change="applyFilters">
          <option value="newest">Newest First</option>
          <option value="oldest">Oldest First</option>
          <option value="total_high">Highest Total</option>
          <option value="total_low">Lowest Total</option>
        </select>
      </div>
    </div>

    <!-- Orders Table -->
    <div class="orders-table-wrapper">
      <div v-if="loading" class="loading-state">Loading orders...</div>
      
      <table v-else-if="orders.length > 0" class="orders-table">
        <thead>
          <tr>
            <th>Order #</th>
            <th>Customer</th>
            <th>Items</th>
            <th>Total</th>
            <th>Status</th>
            <th>Date</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="order in orders" :key="order.id">
            <td class="order-number">#{{ order.order_number || order.id }}</td>
            <td>
              <div class="customer-info">
                <div class="customer-name">{{ order.user?.name || 'N/A' }}</div>
                <div class="customer-email">{{ order.user?.email || 'N/A' }}</div>
              </div>
            </td>
            <td>{{ order.vendor_items_count || order.items?.length || 0 }}</td>
            <td class="order-total">${{ Number(order.vendor_subtotal || order.total || 0).toFixed(2) }}</td>
            <td>
              <span class="status-badge" :class="'status-' + (order.status || 'pending')">
                {{ order.status || 'N/A' }}
              </span>
            </td>
            <td>{{ formatDate(order.created_at) }}</td>
            <td>
              <button class="btn-action view" @click="viewOrder(order.id)">
                <i class="bi bi-eye"></i>
              </button>
              <button class="btn-action edit" @click="openStatusModal(order)">
                <i class="bi bi-pencil"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-else-if="!loading" class="empty-state">
        <i class="bi bi-inbox"></i>
        <p>No orders found</p>
        <p class="empty-sub">Orders will appear here once customers purchase your products.</p>
      </div>
    </div>

    <!-- Pagination -->
    <Pagination 
      v-if="pagination.last_page > 1"
      :current-page="pagination.current_page"
      :total-pages="pagination.last_page"
      @page-change="changePage"
    />

    <!-- Status Update Modal -->
    <StatusModal
      v-if="isStatusModalOpen"
      :order="selectedOrder"
      @close="isStatusModalOpen = false"
      @updated="onStatusUpdated"
    />

    <!-- Order Details Modal -->
    <OrderDetailModal
      v-if="isDetailModalOpen"
      :order="selectedOrder"
      @close="isDetailModalOpen = false"
    />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import axios from 'axios'
import Pagination from '@/components/Pagination.vue'
import StatusModal from '@/components/vendor/StatusModal.vue'
import OrderDetailModal from '@/components/vendor/OrderDetailModal.vue'

const orders = ref([])
const loading = ref(true)
const isStatusModalOpen = ref(false)
const isDetailModalOpen = ref(false)
const selectedOrder = ref(null)
const debugInfo = ref('')

const filters = reactive({
  search: '',
  status: '',
  sort: 'newest'
})

const pagination = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0
})

const loadOrders = async () => {
  loading.value = true
  debugInfo.value = 'Loading orders...'
  
  try {
    const token = localStorage.getItem('token')
    
    if (!token) {
      debugInfo.value = '❌ No token found. Please login again.'
      loading.value = false
      return
    }
    
    console.log('🔍 Fetching orders with token:', token.substring(0, 20) + '...')
    
    // ✅ FIX: Use full URL with port 8000
    const response = await axios.get('http://localhost:8000/api/vendor/orders', {
      params: {
        page: pagination.current_page,
        search: filters.search || undefined,
        status: filters.status || undefined,
        sort: filters.sort || undefined
      },
      headers: { 
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })
    
    console.log('📦 Orders response:', response.data)
    debugInfo.value = `✅ Response received: ${response.status}`
    
    if (response.data.success) {
      const data = response.data.data
      orders.value = data.data || []
      pagination.current_page = data.current_page || 1
      pagination.last_page = data.last_page || 1
      pagination.per_page = data.per_page || 15
      pagination.total = data.total || 0
      
      console.log('✅ Orders loaded:', orders.value.length)
      debugInfo.value = `✅ Loaded ${orders.value.length} orders`
    } else {
      debugInfo.value = '❌ API returned error: ' + (response.data.message || 'Unknown error')
    }
  } catch (error) {
    console.error('❌ Error loading orders:', error)
    console.error('Response:', error.response?.data)
    
    if (error.response?.status === 401) {
      debugInfo.value = '❌ Authentication failed. Please login again.'
    } else if (error.response?.status === 404) {
      debugInfo.value = '❌ API endpoint not found. Please check backend is running on port 8000.'
    } else {
      debugInfo.value = `❌ Error: ${error.response?.data?.message || error.message}`
    }
  } finally {
    loading.value = false
  }
}

const applyFilters = () => {
  pagination.current_page = 1
  loadOrders()
}

const changePage = (page) => {
  pagination.current_page = page
  loadOrders()
}

const viewOrder = (id) => {
  const order = orders.value.find(o => o.id === id)
  selectedOrder.value = order
  isDetailModalOpen.value = true
}

const openStatusModal = (order) => {
  selectedOrder.value = order
  isStatusModalOpen.value = true
}

const onStatusUpdated = () => {
  isStatusModalOpen.value = false
  loadOrders()
}

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('en-US', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  })
}

onMounted(() => {
  loadOrders()
})
</script>

<style scoped>
.vendor-orders {
  padding: 24px;
  max-width: 1400px;
  margin: 0 auto;
}

.page-header {
  margin-bottom: 24px;
}

.page-header h1 {
  font-size: 28px;
  font-weight: 700;
  margin: 0;
  color: #1a1a2e;
}

.text-muted {
  color: #6b7280;
  margin: 4px 0 0;
}

.debug-info {
  background: #f3f4f6;
  padding: 8px 16px;
  border-radius: 8px;
  font-size: 13px;
  color: #6b7280;
  margin-bottom: 16px;
  font-family: monospace;
}

.filters-bar {
  display: flex;
  gap: 16px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}

.search-box {
  flex: 1;
  min-width: 200px;
  display: flex;
  align-items: center;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 8px 16px;
}

.search-box i {
  color: #9ca3af;
  margin-right: 8px;
}

.search-box input {
  flex: 1;
  border: none;
  outline: none;
  font-size: 14px;
  background: transparent;
}

.filter-group {
  display: flex;
  gap: 12px;
}

.filter-group select {
  padding: 8px 16px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  background: white;
  font-size: 14px;
  outline: none;
}

.orders-table-wrapper {
  background: white;
  border-radius: 12px;
  border: 1px solid #e5e7eb;
  overflow: hidden;
}

.orders-table {
  width: 100%;
  border-collapse: collapse;
}

.orders-table th {
  padding: 14px 16px;
  text-align: left;
  background: #f9fafb;
  font-weight: 600;
  font-size: 13px;
  color: #6b7280;
  border-bottom: 1px solid #e5e7eb;
}

.orders-table td {
  padding: 14px 16px;
  border-bottom: 1px solid #f3f4f6;
  font-size: 14px;
  color: #1a1a2e;
}

.orders-table tr:last-child td {
  border-bottom: none;
}

.orders-table tr:hover {
  background: #f9fafb;
}

.order-number {
  font-weight: 600;
  color: #667eea;
}

.customer-info {
  display: flex;
  flex-direction: column;
}

.customer-name {
  font-weight: 500;
}

.customer-email {
  font-size: 12px;
  color: #6b7280;
}

.order-total {
  font-weight: 600;
  color: #667eea;
}

.status-badge {
  padding: 4px 12px;
  border-radius: 50px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
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

.btn-action {
  padding: 4px 8px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  margin-right: 4px;
  transition: all 0.2s ease;
}

.btn-action.view {
  background: #e0e7ff;
  color: #4f46e5;
}

.btn-action.view:hover {
  background: #c7d2fe;
}

.btn-action.edit {
  background: #d1fae5;
  color: #059669;
}

.btn-action.edit:hover {
  background: #a7f3d0;
}

.loading-state {
  text-align: center;
  padding: 40px;
  color: #6b7280;
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
}

.empty-state i {
  font-size: 48px;
  color: #d1d5db;
  display: block;
  margin-bottom: 12px;
}

.empty-state p {
  margin: 0 0 4px;
  color: #6b7280;
}

.empty-sub {
  font-size: 13px;
  color: #9ca3af;
}

@media (max-width: 768px) {
  .filters-bar {
    flex-direction: column;
  }
  
  .filter-group {
    flex-wrap: wrap;
  }
  
  .orders-table-wrapper {
    overflow-x: auto;
  }
  
  .orders-table {
    font-size: 13px;
  }
  
  .orders-table th,
  .orders-table td {
    padding: 10px 12px;
  }
}
</style>