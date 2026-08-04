<template>
  <div class="dashboard-widget">
    <!-- Compact Header -->
    <div class="widget-header">
      <h3>
        <i class="bi bi-speedometer2"></i> 
        Dashboard Overview
      </h3>
      <span class="badge" v-if="lastUpdated">
        Updated: {{ formatTime(lastUpdated) }}
      </span>
    </div>

    <!-- Quick Stats -->
    <div class="quick-stats">
      <div 
        v-for="stat in quickStats" 
        :key="stat.label"
        class="quick-stat"
        :class="stat.color"
      >
        <div class="stat-icon">
          <i :class="stat.icon"></i>
        </div>
        <div class="stat-content">
          <span class="stat-value">{{ stat.value }}</span>
          <span class="stat-label">{{ stat.label }}</span>
        </div>
      </div>
    </div>

    <!-- Mini Chart -->
    <div class="mini-chart" v-if="showChart && chartData.length > 0">
      <div class="chart-header">
        <span class="chart-title">Revenue Trend</span>
        <span class="chart-total">${{ chartTotal }}</span>
      </div>
      <div class="chart-bars">
        <div 
          v-for="(item, index) in chartData" 
          :key="index"
          class="chart-bar-wrapper"
        >
          <div 
            class="chart-bar"
            :style="{ height: getBarHeight(item.total) + '%' }"
            :title="item.label + ': $' + item.total"
          ></div>
          <span class="chart-label">{{ item.shortLabel }}</span>
        </div>
      </div>
    </div>

    <!-- Recent Activity -->
    <div class="recent-activity" v-if="showRecent">
      <h4>Recent Activity</h4>
      <div v-if="activities.length === 0" class="empty-activity">
        <i class="bi bi-inbox"></i>
        <p>No recent activity</p>
      </div>
      <div v-else class="activity-list">
        <div 
          v-for="activity in displayedActivities" 
          :key="activity.id"
          class="activity-item"
        >
          <div class="activity-icon" :class="activity.type">
            <i :class="getActivityIcon(activity.type)"></i>
          </div>
          <div class="activity-content">
            <p class="activity-text">{{ activity.text }}</p>
            <span class="activity-time">{{ formatTime(activity.created_at) }}</span>
          </div>
        </div>
      </div>
      <button 
        v-if="activities.length > 3" 
        class="view-all-btn"
        @click="$emit('view-all')"
      >
        View All Activity
      </button>
    </div>

    <!-- Footer Actions -->
    <div class="widget-footer">
      <button class="footer-btn" @click="$emit('refresh')">
        <i class="bi bi-arrow-clockwise"></i> Refresh
      </button>
      <button class="footer-btn primary" @click="$emit('go-to-dashboard')">
        <i class="bi bi-grid"></i> Full Dashboard
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

// Props
const props = defineProps({
  // Data props
  stats: {
    type: Object,
    default: () => ({})
  },
  recentOrders: {
    type: Array,
    default: () => []
  },
  lowStockProducts: {
    type: Array,
    default: () => []
  },
  monthlyRevenue: {
    type: Array,
    default: () => []
  },
  topProducts: {
    type: Array,
    default: () => []
  },
  
  // Configuration props
  showChart: {
    type: Boolean,
    default: true
  },
  showRecent: {
    type: Boolean,
    default: true
  },
  compact: {
    type: Boolean,
    default: false
  },
  maxActivities: {
    type: Number,
    default: 3
  }
})

// Emits
const emit = defineEmits([
  'refresh',
  'view-all',
  'go-to-dashboard',
  'view-order',
  'view-product'
])

// State
const lastUpdated = ref(new Date())
const activities = ref([])

// Computed
const quickStats = computed(() => [
  {
    label: 'Products',
    value: props.stats.total_products || 0,
    icon: 'bi bi-box-fill',
    color: 'blue'
  },
  {
    label: 'Orders',
    value: props.stats.total_orders || 0,
    icon: 'bi bi-cart-fill',
    color: 'green'
  },
  {
    label: 'Revenue',
    value: '$' + (props.stats.total_revenue || 0).toFixed(2),
    icon: 'bi bi-coin',
    color: 'orange'
  },
  {
    label: 'Pending',
    value: props.stats.pending_orders || 0,
    icon: 'bi bi-clock',
    color: 'red'
  }
])

const chartData = computed(() => {
  if (!props.monthlyRevenue || props.monthlyRevenue.length === 0) {
    return []
  }
  
  // Get last 6 months
  const data = [...props.monthlyRevenue].slice(0, 6)
  
  return data.map(item => ({
    label: item.month_name || `Month ${item.month}`,
    shortLabel: (item.month_name || '').substring(0, 3),
    total: Number(item.total || 0),
    value: item.total || 0
  }))
})

const chartTotal = computed(() => {
  const total = chartData.value.reduce((sum, item) => sum + item.value, 0)
  return total.toFixed(2)
})

const displayedActivities = computed(() => {
  return activities.value.slice(0, props.maxActivities)
})

// Methods
const formatTime = (date) => {
  if (!date) return 'N/A'
  const d = new Date(date)
  const now = new Date()
  const diff = Math.floor((now - d) / 1000 / 60) // minutes
  
  if (diff < 1) return 'Just now'
  if (diff < 60) return diff + 'm ago'
  if (diff < 1440) return Math.floor(diff / 60) + 'h ago'
  return d.toLocaleDateString()
}

const getBarHeight = (value) => {
  const max = Math.max(...chartData.value.map(item => item.value), 1)
  return Math.max((value / max) * 80, 5)
}

const getActivityIcon = (type) => {
  const icons = {
    order: 'bi bi-cart-plus',
    product: 'bi bi-box',
    payment: 'bi bi-credit-card',
    review: 'bi bi-star',
    default: 'bi bi-bell'
  }
  return icons[type] || icons.default
}

// Build activities from data
const buildActivities = () => {
  const activities = []
  
  // Add recent orders as activities
  props.recentOrders.slice(0, 3).forEach(order => {
    activities.push({
      id: 'order-' + order.id,
      type: 'order',
      text: `New order #${order.order_number || order.id} - $${(order.total || 0).toFixed(2)}`,
      created_at: order.created_at,
      data: order
    })
  })
  
  // Add low stock as activities
  props.lowStockProducts.forEach(product => {
    activities.push({
      id: 'stock-' + product.id,
      type: 'product',
      text: `⚠️ ${product.name} is low on stock (${product.stock_quantity} left)`,
      created_at: product.updated_at || new Date(),
      data: product
    })
  })
  
  // Sort by date, newest first
  activities.sort((a, b) => {
    return new Date(b.created_at) - new Date(a.created_at)
  })
  
  return activities
}

// Lifecycle
onMounted(() => {
  activities.value = buildActivities()
  lastUpdated.value = new Date()
})

// Watch for prop changes
import { watch } from 'vue'
watch(() => [props.recentOrders, props.lowStockProducts], () => {
  activities.value = buildActivities()
}, { deep: true })
</script>

<style scoped>
.dashboard-widget {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
}

.dashboard-widget:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

/* Widget Header */
.widget-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  flex-wrap: wrap;
  gap: 8px;
}

.widget-header h3 {
  font-size: 1.1rem;
  font-weight: 600;
  color: #1a1a2e;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 8px;
}

.badge {
  background: #f3f4f6;
  color: #6b7280;
  font-size: 0.7rem;
  padding: 2px 10px;
  border-radius: 50px;
}

/* Quick Stats */
.quick-stats {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 12px;
  margin-bottom: 20px;
}

.quick-stat {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  border-radius: 8px;
  transition: all 0.2s ease;
  background: #f9fafb;
}

.quick-stat:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.stat-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  flex-shrink: 0;
}

.quick-stat.blue .stat-icon {
  background: rgba(102, 126, 234, 0.1);
  color: #667eea;
}

.quick-stat.green .stat-icon {
  background: rgba(52, 211, 153, 0.1);
  color: #34d399;
}

.quick-stat.orange .stat-icon {
  background: rgba(251, 191, 36, 0.1);
  color: #f59e0b;
}

.quick-stat.red .stat-icon {
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
}

.stat-content {
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.stat-value {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1a1a2e;
  line-height: 1.2;
}

.stat-label {
  font-size: 0.75rem;
  color: #6b7280;
}

/* Mini Chart */
.mini-chart {
  background: #f9fafb;
  border-radius: 8px;
  padding: 16px;
  margin-bottom: 20px;
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.chart-title {
  font-size: 0.85rem;
  font-weight: 500;
  color: #6b7280;
}

.chart-total {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1a1a2e;
}

.chart-bars {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  height: 60px;
  gap: 4px;
}

.chart-bar-wrapper {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
}

.chart-bar {
  width: 100%;
  max-width: 30px;
  background: #667eea;
  border-radius: 4px 4px 0 0;
  min-height: 4px;
  transition: height 0.5s ease;
  cursor: pointer;
}

.chart-bar:hover {
  background: #5a67d8;
}

.chart-label {
  font-size: 0.6rem;
  color: #6b7280;
}

/* Recent Activity */
.recent-activity {
  margin-bottom: 16px;
}

.recent-activity h4 {
  font-size: 0.95rem;
  font-weight: 600;
  color: #1a1a2e;
  margin: 0 0 12px 0;
}

.empty-activity {
  text-align: center;
  padding: 20px;
  color: #6b7280;
}

.empty-activity i {
  font-size: 2rem;
  display: block;
  margin-bottom: 8px;
  opacity: 0.5;
}

.activity-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.activity-item {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 8px 12px;
  border-radius: 6px;
  transition: background 0.2s ease;
  cursor: pointer;
}

.activity-item:hover {
  background: #f9fafb;
}

.activity-icon {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.9rem;
  flex-shrink: 0;
  margin-top: 2px;
}

.activity-icon.order {
  background: rgba(102, 126, 234, 0.1);
  color: #667eea;
}

.activity-icon.product {
  background: rgba(251, 191, 36, 0.1);
  color: #f59e0b;
}

.activity-icon.payment {
  background: rgba(52, 211, 153, 0.1);
  color: #34d399;
}

.activity-icon.review {
  background: rgba(245, 158, 11, 0.1);
  color: #f59e0b;
}

.activity-content {
  flex: 1;
  min-width: 0;
}

.activity-text {
  font-size: 0.85rem;
  color: #1a1a2e;
  margin: 0;
  word-break: break-word;
}

.activity-time {
  font-size: 0.7rem;
  color: #6b7280;
}

.view-all-btn {
  width: 100%;
  padding: 8px;
  background: transparent;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  color: #6b7280;
  font-size: 0.85rem;
  cursor: pointer;
  transition: all 0.2s ease;
  margin-top: 8px;
}

.view-all-btn:hover {
  background: #f9fafb;
  border-color: #d1d5db;
}

/* Widget Footer */
.widget-footer {
  display: flex;
  gap: 8px;
  padding-top: 16px;
  border-top: 1px solid #e5e7eb;
}

.footer-btn {
  flex: 1;
  padding: 8px 12px;
  background: transparent;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  color: #6b7280;
  font-size: 0.8rem;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
}

.footer-btn:hover {
  background: #f9fafb;
  border-color: #d1d5db;
}

.footer-btn.primary {
  background: #667eea;
  color: white;
  border-color: #667eea;
}

.footer-btn.primary:hover {
  background: #5a67d8;
  border-color: #5a67d8;
}

/* Responsive */
@media (max-width: 768px) {
  .quick-stats {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .widget-footer {
    flex-direction: column;
  }
}

@media (max-width: 480px) {
  .quick-stats {
    grid-template-columns: 1fr 1fr;
    gap: 8px;
  }
  
  .quick-stat {
    padding: 8px;
  }
  
  .stat-value {
    font-size: 0.95rem;
  }
}
</style>