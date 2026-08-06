<template>
  <div class="admin-orders">
    <div class="page-header">
      <h1>Orders Management</h1>
      <p class="text-muted">View and manage all orders</p>
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
      </div>
      <button class="btn btn-primary" @click="resetFilters">Reset</button>
    </div>

    <!-- Orders Table -->
    <div class="table-wrapper">
      <div v-if="loading" class="loading-state">Loading orders...</div>
      
      <table v-else-if="orders.length > 0" class="data-table">
        <thead>
          <tr>
            <th>Order #</th>
            <th>Customer</th>
            <th>Items</th>
            <th>Total</th>
            <th>Status</th>
            <th>Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="order in orders" :key="order.id">
            <td><strong>#{{ order.order_number }}</strong></td>
            <td>{{ order.user?.name }}</td>
            <td>{{ order.items?.length || 0 }}</td>
            <td class="order-total">${{ Number(order.total || 0).toFixed(2) }}</td>
            <td>
              <span class="status-badge" :class="'status-' + order.status">
                {{ order.status }}
              </span>
            </td>
            <td>{{ formatDate(order.created_at) }}</td>
            <td>
              <select v-model="order.status" @change="updateStatus(order)" class="status-select">
                <option value="pending">Pending</option>
                <option value="processing">Processing</option>
                <option value="shipped">Shipped</option>
                <option value="delivered">Delivered</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-else-if="!loading" class="empty-state">
        <i class="bi bi-inbox"></i>
        <p>No orders found</p>
      </div>
    </div>

    <!-- Pagination -->
    <Pagination 
      v-if="pagination.last_page > 1"
      :current-page="pagination.current_page"
      :total-pages="pagination.last_page"
      @page-change="changePage"
    />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import axios from 'axios'
import Pagination from '@/components/Pagination.vue'

// ❌ NO ReviewModal import here - Admin doesn't need it

const orders = ref([])
const loading = ref(true)

const filters = reactive({
  search: '',
  status: ''
})

const pagination = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0
})

const loadOrders = async () => {
  loading.value = true
  try {
    const token = localStorage.getItem('token')
    const response = await axios.get('http://localhost:8000/api/admin/orders', {
      params: {
        page: pagination.current_page,
        ...filters
      },
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })
    
    if (response.data.success) {
      const data = response.data.data
      orders.value = data.data || []
      pagination.current_page = data.current_page
      pagination.last_page = data.last_page
      pagination.per_page = data.per_page
      pagination.total = data.total
    }
  } catch (error) {
    console.error('Error loading orders:', error)
  } finally {
    loading.value = false
  }
}

const updateStatus = async (order) => {
  if (!confirm(`Change order ${order.order_number} status to ${order.status}?`)) {
    // Revert the select value
    await loadOrders()
    return
  }
  
  try {
    const token = localStorage.getItem('token')
    await axios.put(`http://localhost:8000/api/admin/orders/${order.id}/status`, {
      status: order.status
    }, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })
    alert('✅ Order status updated!')
    await loadOrders()
  } catch (error) {
    console.error('Error updating order:', error)
    alert('❌ Failed to update order status')
    await loadOrders()
  }
}

const applyFilters = () => {
  pagination.current_page = 1
  loadOrders()
}

const resetFilters = () => {
  filters.search = ''
  filters.status = ''
  applyFilters()
}

const changePage = (page) => {
  pagination.current_page = page
  loadOrders()
}

const formatDate = (date) => {
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
.admin-orders {
  max-width: 1400px;
  margin: 0 auto;
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

.filters-bar {
  display: flex;
  gap: 16px;
  margin: 20px 0;
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

.filter-group select {
  padding: 8px 16px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  background: white;
  font-size: 14px;
  outline: none;
}

.btn {
  padding: 8px 20px;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-primary {
  background: #667eea;
  color: white;
}

.btn-primary:hover {
  background: #5a67d8;
}

.table-wrapper {
  background: white;
  border-radius: 12px;
  border: 1px solid #e5e7eb;
  overflow: hidden;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th {
  padding: 14px 16px;
  text-align: left;
  background: #f9fafb;
  font-weight: 600;
  font-size: 13px;
  color: #6b7280;
  border-bottom: 1px solid #e5e7eb;
}

.data-table td {
  padding: 14px 16px;
  border-bottom: 1px solid #f3f4f6;
}

.order-total {
  font-weight: 600;
  color: #667eea;
}

.status-badge {
  padding: 2px 12px;
  border-radius: 50px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
}

.status-pending { background: #fef3c7; color: #d97706; }
.status-processing { background: #dbeafe; color: #2563eb; }
.status-shipped { background: #e0e7ff; color: #4f46e5; }
.status-delivered { background: #d1fae5; color: #059669; }
.status-cancelled { background: #fee2e2; color: #dc2626; }

.status-select {
  padding: 4px 8px;
  border: 1px solid #e5e7eb;
  border-radius: 4px;
  font-size: 13px;
  outline: none;
}

.status-select:focus {
  border-color: #667eea;
}

.loading-state {
  text-align: center;
  padding: 40px;
  color: #6b7280;
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #6b7280;
}

.empty-state i {
  font-size: 48px;
  display: block;
  margin-bottom: 12px;
  color: #d1d5db;
}

/* Dark Mode */
html.dark .admin-orders .page-header h1 {
  color: #ffffff;
}

html.dark .admin-orders .text-muted {
  color: #9ca3af;
}

html.dark .admin-orders .search-box {
  background: #1a1932;
  border-color: #2d2b4e;
}

html.dark .admin-orders .search-box input {
  color: #e5e7eb;
  background: transparent;
}

html.dark .admin-orders .filter-group select {
  background: #1a1932;
  border-color: #2d2b4e;
  color: #e5e7eb;
}

html.dark .admin-orders .table-wrapper {
  background: #1a1932;
  border-color: #2d2b4e;
}

html.dark .admin-orders .data-table {
  background: #1a1932;
}

html.dark .admin-orders .data-table th {
  background: #0f0e17;
  color: #9ca3af;
  border-color: #2d2b4e;
}

html.dark .admin-orders .data-table td {
  color: #e5e7eb;
  border-color: #2d2b4e;
}

html.dark .admin-orders .data-table tr:hover {
  background: #2d2b4e;
}

html.dark .admin-orders .status-select {
  background: #1a1932;
  border-color: #2d2b4e;
  color: #e5e7eb;
}

html.dark .admin-orders .status-select option {
  background: #1a1932;
}

html.dark .admin-orders .empty-state {
  color: #9ca3af;
}

html.dark .admin-orders .empty-state i {
  color: #4a4770;
}

@media (max-width: 768px) {
  .filters-bar {
    flex-direction: column;
  }
  
  .data-table {
    font-size: 13px;
  }
  
  .data-table th,
  .data-table td {
    padding: 10px 12px;
  }
}
</style>