<template>
  <div class="dashboard-content">
    <!-- Header -->
    <div class="page-header">
      <div>
        <h1>Dashboard</h1>
        <p class="text-muted">Welcome back, {{ vendorName }}!</p>
      </div>
      <button class="btn-primary" @click="goToProducts">
        <span>+</span> Add Product
      </button>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon-wrapper blue">
          <span class="stat-icon">📦</span>
        </div>
        <div class="stat-info">
          <span class="stat-value">{{ stats.total_products || 0 }}</span>
          <span class="stat-label">Total Products</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon-wrapper green">
          <span class="stat-icon">🛒</span>
        </div>
        <div class="stat-info">
          <span class="stat-value">{{ stats.total_orders || 0 }}</span>
          <span class="stat-label">Total Orders</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon-wrapper purple">
          <span class="stat-icon">💰</span>
        </div>
        <div class="stat-info">
          <span class="stat-value">${{ (stats.total_revenue || 0).toFixed(2) }}</span>
          <span class="stat-label">Total Revenue</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon-wrapper orange">
          <span class="stat-icon">⏳</span>
        </div>
        <div class="stat-info">
          <span class="stat-value">{{ stats.pending_orders || 0 }}</span>
          <span class="stat-label">Pending Orders</span>
        </div>
      </div>
    </div>

    <!-- Recent Orders & Low Stock -->
    <div class="two-column">
      <!-- Recent Orders -->
      <div class="card">
        <div class="card-header">
          <h3>Recent Orders</h3>
          <span class="badge">{{ recentOrders.length }}</span>
        </div>
        <div v-if="loading" class="loading-spinner">Loading orders...</div>
        <div v-else-if="recentOrders.length === 0" class="empty-state">
          <span>📭</span>
          <p>No orders yet</p>
        </div>
        <div v-else class="order-list">
          <div v-for="order in recentOrders.slice(0, 5)" :key="order.id" class="order-item">
            <div class="order-left">
              <span class="order-id">#{{ order.id }}</span>
              <span class="order-date">{{ formatDate(order.created_at) }}</span>
              <span class="status-badge" :class="'status-' + (order.status || 'pending')">
                {{ order.status || 'pending' }}
              </span>
            </div>
            <span class="order-amount">${{ (order.total || 0).toFixed(2) }}</span>
          </div>
        </div>
        <div v-if="recentOrders.length > 5" class="view-all">
          <router-link to="/vendor/orders">View All Orders →</router-link>
        </div>
      </div>

      <!-- Low Stock -->
      <div class="card">
        <div class="card-header">
          <h3>⚠️ Low Stock Alert</h3>
          <span class="badge danger">{{ lowStockProducts.length }}</span>
        </div>
        <div v-if="loading" class="loading-spinner">Checking stock...</div>
        <div v-else-if="lowStockProducts.length === 0" class="empty-state success">
          <span>✅</span>
          <p>All products have sufficient stock</p>
        </div>
        <div v-else class="stock-list">
          <div v-for="product in lowStockProducts.slice(0, 5)" :key="product.id" class="stock-item">
            <span class="stock-name">{{ product.name }}</span>
            <span class="stock-count" :class="{ critical: product.stock_quantity <= 2 }">
              {{ product.stock_quantity }} left
            </span>
          </div>
        </div>
        <div v-if="lowStockProducts.length > 5" class="view-all">
          <router-link to="/vendor/products">View All Products →</router-link>
        </div>
      </div>
    </div>

    <!-- Loading Overlay -->
    <div v-if="loading" class="loading-overlay">
      <div class="loader"></div>
      <p>Loading dashboard...</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const vendorName = ref('Vendor')
const stats = ref({})
const recentOrders = ref([])
const lowStockProducts = ref([])
const loading = ref(true)

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('en-US', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  })
}

const goToProducts = () => {
  router.push('/vendor/products')
}

const loadDashboard = async () => {
  loading.value = true
  try {
    const token = localStorage.getItem('token')
    const response = await axios.get('http://localhost:8000/api/vendor/dashboard', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })

    if (response.data.success) {
      const data = response.data.data
      vendorName.value = data.vendor?.shop_name || data.user?.name || 'Vendor'
      stats.value = data.stats || {}
      recentOrders.value = data.recent_orders || []
      lowStockProducts.value = data.low_stock_products || []
    }
  } catch (error) {
    console.error('Error loading dashboard:', error)
    // Mock data for testing
    stats.value = {
      total_products: 12,
      total_orders: 8,
      total_revenue: 1249.95,
      pending_orders: 3
    }
    recentOrders.value = [
      { id: 1001, status: 'pending', total: 99.99, created_at: new Date() },
      { id: 1002, status: 'processing', total: 249.99, created_at: new Date(Date.now() - 3600000) },
      { id: 1003, status: 'shipped', total: 149.99, created_at: new Date(Date.now() - 86400000) },
      { id: 1004, status: 'delivered', total: 59.99, created_at: new Date(Date.now() - 172800000) },
      { id: 1005, status: 'pending', total: 89.99, created_at: new Date(Date.now() - 259200000) }
    ]
    lowStockProducts.value = [
      { id: 1, name: 'iPhone 15 Case', stock_quantity: 3 },
      { id: 2, name: 'USB-C Cable', stock_quantity: 1 },
      { id: 3, name: 'Screen Protector', stock_quantity: 5 }
    ]
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadDashboard()
})
</script>

<style scoped>
.dashboard-content {
  max-width: 1400px;
  margin: 0 auto;
}

/* Page Header */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
  flex-wrap: wrap;
  gap: 15px;
}

.page-header h1 {
  font-size: 28px;
  font-weight: 700;
  margin: 0;
  color: #1a1a2e;
}

.text-muted {
  color: #6b7280;
  margin: 4px 0 0 0;
}

.btn-primary {
  background: #667eea;
  color: white;
  border: none;
  padding: 10px 24px;
  border-radius: 8px;
  font-weight: 600;
  font-size: 15px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-primary:hover {
  background: #5a67d8;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-primary span {
  font-size: 20px;
  font-weight: 400;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
  margin-bottom: 30px;
}

.stat-card {
  background: white;
  border-radius: 12px;
  padding: 20px 24px;
  display: flex;
  align-items: center;
  gap: 16px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.06);
  border: 1px solid #e5e7eb;
  transition: all 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 25px rgba(0,0,0,0.08);
}

.stat-icon-wrapper {
  width: 50px;
  height: 50px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.stat-icon-wrapper.blue { background: rgba(102, 126, 234, 0.1); }
.stat-icon-wrapper.green { background: rgba(52, 211, 153, 0.1); }
.stat-icon-wrapper.purple { background: rgba(139, 92, 246, 0.1); }
.stat-icon-wrapper.orange { background: rgba(251, 191, 36, 0.1); }

.stat-icon {
  font-size: 24px;
}

.stat-info {
  display: flex;
  flex-direction: column;
}

.stat-value {
  font-size: 24px;
  font-weight: 700;
  color: #1a1a2e;
  line-height: 1.2;
}

.stat-label {
  font-size: 14px;
  color: #6b7280;
}

/* Two Column */
.two-column {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 20px;
  margin-bottom: 20px;
}

/* Card */
.card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  border: 1px solid #e5e7eb;
  box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.card-header h3 {
  font-size: 16px;
  font-weight: 600;
  margin: 0;
  color: #1a1a2e;
}

.badge {
  background: #f3f4f6;
  color: #6b7280;
  padding: 2px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
}

.badge.danger {
  background: #fee2e2;
  color: #ef4444;
}

/* Order List */
.order-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.order-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 12px;
  background: #f9fafb;
  border-radius: 8px;
  transition: all 0.2s ease;
}

.order-item:hover {
  background: #f3f4f6;
}

.order-left {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.order-id {
  font-weight: 600;
  color: #1a1a2e;
  font-size: 14px;
}

.order-date {
  color: #6b7280;
  font-size: 13px;
}

.status-badge {
  padding: 2px 10px;
  border-radius: 20px;
  font-size: 11px;
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

.order-amount {
  font-weight: 600;
  color: #667eea;
}

/* Stock List */
.stock-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.stock-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 12px;
  background: #fffbeb;
  border-radius: 8px;
  border-left: 3px solid #f59e0b;
}

.stock-name {
  font-weight: 500;
  color: #1a1a2e;
}

.stock-count {
  font-weight: 600;
  color: #f59e0b;
  font-size: 14px;
}

.stock-count.critical {
  color: #ef4444;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 30px 20px;
  color: #6b7280;
}

.empty-state span {
  font-size: 32px;
  display: block;
  margin-bottom: 8px;
}

.empty-state p {
  margin: 0;
}

.empty-state.success span {
  color: #34d399;
}

.view-all {
  margin-top: 12px;
  text-align: center;
}

.view-all a {
  color: #667eea;
  text-decoration: none;
  font-size: 14px;
  font-weight: 500;
}

.view-all a:hover {
  text-decoration: underline;
}

/* Loading */
.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255,255,255,0.8);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  z-index: 999;
}

.loader {
  width: 40px;
  height: 40px;
  border: 3px solid #e5e7eb;
  border-top-color: #667eea;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.loading-overlay p {
  margin-top: 12px;
  color: #6b7280;
}

.loading-spinner {
  text-align: center;
  padding: 20px;
  color: #6b7280;
}

/* Responsive */
@media (max-width: 1024px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .two-column {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    align-items: stretch;
  }
  
  .stats-grid {
    gap: 12px;
  }
  
  .stat-card {
    padding: 16px;
  }
  
  .stat-value {
    font-size: 20px;
  }
  
  .order-left {
    flex-wrap: wrap;
  }
}

@media (max-width: 480px) {
  .stats-grid {
    grid-template-columns: 1fr 1fr;
    gap: 8px;
  }
  
  .stat-card {
    padding: 12px;
    gap: 10px;
  }
  
  .stat-icon-wrapper {
    width: 40px;
    height: 40px;
  }
  
  .stat-icon {
    font-size: 18px;
  }
  
  .stat-value {
    font-size: 16px;
  }
}
</style>