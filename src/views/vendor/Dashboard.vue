<template>
  <div class="dashboard-container">
    <div class="dashboard-header">
      <h1>Dashboard</h1>
      <p>Welcome back, {{ vendorName }}!</p>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon blue">📦</div>
        <div>
          <div class="stat-number">{{ Number(stats.total_products || 0) }}</div>
          <div class="stat-label">Total Products</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon green">🛒</div>
        <div>
          <div class="stat-number">{{ Number(stats.total_orders || 0) }}</div>
          <div class="stat-label">Total Orders</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon purple">💰</div>
        <div>
          <div class="stat-number">${{ Number(stats.total_revenue || 0).toFixed(2) }}</div>
          <div class="stat-label">Total Revenue</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon orange">⏳</div>
        <div>
          <div class="stat-number">{{ Number(stats.pending_orders || 0) }}</div>
          <div class="stat-label">Pending Orders</div>
        </div>
      </div>
    </div>

    <!-- Recent Orders -->
    <div class="section-card">
      <h2>Recent Orders</h2>
      <div v-if="recentOrders.length === 0" class="empty-state">
        <p>No orders yet</p>
      </div>
      <div v-else class="order-list">
        <div v-for="order in recentOrders" :key="order.id" class="order-item">
          <div class="order-info">
            <strong>#{{ order.id }}</strong>
            <span class="order-date">{{ formatDate(order.created_at) }}</span>
            <span class="status-badge" :class="'status-' + order.status">
              {{ order.status }}
            </span>
          </div>
          <div class="order-total">
            ${{ Number(order.total || 0).toFixed(2) }}
          </div>
        </div>
      </div>
    </div>

    <!-- Low Stock Alert -->
    <div v-if="lowStockProducts.length > 0" class="section-card alert-card">
      <h2>⚠️ Low Stock Alert</h2>
      <div v-for="product in lowStockProducts" :key="product.id" class="stock-item">
        <span class="product-name">{{ product.name }}</span>
        <span class="stock-count" :class="{ 'critical': Number(product.stock_quantity) <= 2 }">
          {{ Number(product.stock_quantity) }} units left
        </span>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
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

    console.log('📊 Dashboard response:', response.data)

    if (response.data.success) {
      const data = response.data.data
      vendorName.value = data.vendor?.shop_name || data.user?.name || 'Vendor'
      stats.value = data.stats || {}
      recentOrders.value = data.recent_orders || []
      lowStockProducts.value = data.low_stock_products || []
    }
  } catch (error) {
    console.error('Error loading dashboard:', error)
    console.error('Response:', error.response?.data)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadDashboard()
})
</script>

<style scoped>
.dashboard-container {
  padding: 24px;
  max-width: 1200px;
  margin: 0 auto;
}

.dashboard-header {
  margin-bottom: 30px;
}

.dashboard-header h1 {
  font-size: 28px;
  margin: 0 0 5px 0;
  color: #1a1a2e;
}

.dashboard-header p {
  color: #6b7280;
  margin: 0;
  font-size: 16px;
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
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 15px;
  transition: all 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.stat-icon {
  font-size: 28px;
  width: 50px;
  height: 50px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f3f4f6;
  border-radius: 10px;
}

.stat-icon.blue { background: rgba(102, 126, 234, 0.1); }
.stat-icon.green { background: rgba(52, 211, 153, 0.1); }
.stat-icon.purple { background: rgba(139, 92, 246, 0.1); }
.stat-icon.orange { background: rgba(251, 191, 36, 0.1); }

.stat-number {
  font-size: 24px;
  font-weight: 700;
  color: #1a1a2e;
}

.stat-label {
  font-size: 14px;
  color: #6b7280;
}

/* Section Card */
.section-card {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 20px;
}

.section-card h2 {
  font-size: 18px;
  margin: 0 0 15px 0;
  color: #1a1a2e;
}

/* Order List */
.order-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.order-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 15px;
  background: #f9fafb;
  border-radius: 8px;
}

.order-info {
  display: flex;
  align-items: center;
  gap: 15px;
  flex-wrap: wrap;
}

.order-date {
  color: #6b7280;
  font-size: 14px;
}

.status-badge {
  padding: 4px 12px;
  border-radius: 20px;
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

.order-total {
  font-weight: 600;
  color: #667eea;
}

/* Low Stock */
.alert-card {
  background: #fffbeb;
  border-color: #fef3c7;
}

.stock-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 0;
  border-bottom: 1px solid #fef3c7;
}

.stock-item:last-child {
  border-bottom: none;
}

.product-name {
  font-weight: 500;
  color: #1a1a2e;
}

.stock-count {
  font-weight: 600;
  color: #f59e0b;
}

.stock-count.critical {
  color: #ef4444;
}

/* Loading */
.loading-state {
  text-align: center;
  padding: 40px;
  color: #6b7280;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #e5e7eb;
  border-top-color: #667eea;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto 16px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.empty-state {
  text-align: center;
  padding: 30px;
  color: #6b7280;
}

.empty-state p {
  margin: 0;
}

/* Responsive */
@media (max-width: 1024px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .dashboard-container {
    padding: 16px;
  }
  
  .stats-grid {
    gap: 12px;
  }
  
  .stat-card {
    padding: 15px;
  }
  
  .stat-number {
    font-size: 20px;
  }
  
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

@media (max-width: 480px) {
  .stats-grid {
    grid-template-columns: 1fr 1fr;
    gap: 8px;
  }
  
  .stat-card {
    padding: 12px;
  }
  
  .stat-icon {
    font-size: 20px;
    width: 40px;
    height: 40px;
  }
  
  .stat-number {
    font-size: 18px;
  }
}
</style>