<template>
  <div class="admin-dashboard">
    <div class="page-header">
      <h1>Dashboard</h1>
      <p class="text-muted">Welcome to the admin panel</p>
    </div>

    <div v-if="loading" class="loading-state">Loading...</div>

    <div v-else class="dashboard-content">
      <!-- Stats Cards -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon blue">👥</div>
          <div>
            <div class="stat-value">{{ stats.total_users }}</div>
            <div class="stat-label">Total Users</div>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon green">🏪</div>
          <div>
            <div class="stat-value">{{ stats.total_vendors }}</div>
            <div class="stat-label">Vendors</div>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon purple">📦</div>
          <div>
            <div class="stat-value">{{ stats.total_products }}</div>
            <div class="stat-label">Products</div>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon orange">🛒</div>
          <div>
            <div class="stat-value">{{ stats.total_orders }}</div>
            <div class="stat-label">Orders</div>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon red">⏳</div>
          <div>
            <div class="stat-value">{{ stats.pending_orders }}</div>
            <div class="stat-label">Pending Orders</div>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon gold">💰</div>
          <div>
            <div class="stat-value">${{ Number(stats.total_revenue || 0).toFixed(2) }}</div>
            <div class="stat-label">Total Revenue</div>
          </div>
        </div>
      </div>

      <!-- Recent Orders & Pending Vendors -->
      <div class="two-column">
        <!-- Recent Orders -->
        <div class="card">
          <h3>Recent Orders</h3>
          <div v-if="recentOrders.length === 0" class="empty-state">No orders yet</div>
          <div v-else class="order-list">
            <div v-for="order in recentOrders" :key="order.id" class="order-item">
              <span class="order-id">#{{ order.order_number }}</span>
              <span class="order-customer">{{ order.user?.name }}</span>
              <span class="order-total">${{ Number(order.total || 0).toFixed(2) }}</span>
              <span class="status-badge" :class="'status-' + order.status">
                {{ order.status }}
              </span>
            </div>
          </div>
        </div>

        <!-- Pending Vendors -->
        <div class="card">
          <h3>Pending Vendors</h3>
          <div v-if="pendingVendors.length === 0" class="empty-state">No pending vendors</div>
          <div v-else class="vendor-list">
            <div v-for="vendor in pendingVendors" :key="vendor.id" class="vendor-item">
              <span class="vendor-name">{{ vendor.user?.name }}</span>
              <span class="vendor-email">{{ vendor.user?.email }}</span>
              <span class="vendor-store">{{ vendor.shop_name }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const loading = ref(true)
const stats = ref({})
const recentOrders = ref([])
const pendingVendors = ref([])

const loadDashboard = async () => {
  try {
    const token = localStorage.getItem('token')
    const response = await axios.get('http://localhost:8000/api/admin/dashboard', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })
    
    if (response.data.success) {
      const data = response.data.data
      stats.value = data.stats
      recentOrders.value = data.recent_orders || []
      pendingVendors.value = data.pending_vendors || []
    }
  } catch (error) {
    console.error('Error loading dashboard:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadDashboard()
})
</script>

<style scoped>
.admin-dashboard {
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

.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
  margin: 24px 0;
}

.stat-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  border: 1px solid #e5e7eb;
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
  border-radius: 10px;
}

.stat-icon.blue { background: rgba(102, 126, 234, 0.1); }
.stat-icon.green { background: rgba(52, 211, 153, 0.1); }
.stat-icon.purple { background: rgba(139, 92, 246, 0.1); }
.stat-icon.orange { background: rgba(251, 191, 36, 0.1); }
.stat-icon.red { background: rgba(239, 68, 68, 0.1); }
.stat-icon.gold { background: rgba(245, 158, 11, 0.1); }

.stat-value {
  font-size: 24px;
  font-weight: 700;
  color: #1a1a2e;
}

.stat-label {
  font-size: 14px;
  color: #6b7280;
}

.two-column {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  border: 1px solid #e5e7eb;
}

.card h3 {
  font-size: 16px;
  font-weight: 600;
  margin: 0 0 16px 0;
}

.order-item,
.vendor-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
  border-bottom: 1px solid #f3f4f6;
}

.order-item:last-child,
.vendor-item:last-child {
  border-bottom: none;
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

.loading-state {
  text-align: center;
  padding: 40px;
  color: #6b7280;
}

.empty-state {
  text-align: center;
  padding: 20px;
  color: #6b7280;
}

@media (max-width: 1024px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .two-column {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
}
</style>