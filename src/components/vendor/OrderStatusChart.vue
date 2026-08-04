<template>
  <div class="chart-card">
    <h3>Order Status</h3>
    
    <div v-if="data.length === 0" class="chart-empty">
      <i class="bi bi-pie-chart"></i>
      <p>No orders to display</p>
    </div>
    
    <div v-else class="status-list">
      <div 
        v-for="item in data" 
        :key="item.status"
        class="status-item"
      >
        <div class="status-label">
          <span class="status-dot" :class="'dot-' + item.status"></span>
          <span class="status-name">{{ item.status }}</span>
          <span class="status-count">{{ item.count }}</span>
        </div>
        <div class="status-bar">
          <div 
            class="status-progress"
            :class="'progress-' + item.status"
            :style="{ width: getPercentage(item.count) + '%' }"
          ></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  data: {
    type: Array,
    default: () => []
  }
})

const total = computed(() => {
  return props.data.reduce((sum, item) => sum + item.count, 0)
})

const getPercentage = (count) => {
  if (total.value === 0) return 0
  return (count / total.value) * 100
}
</script>

<style scoped>
.chart-card {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 20px;
}

.chart-card h3 {
  font-size: 1rem;
  font-weight: 600;
  color: #1a1a2e;
  margin: 0 0 16px 0;
}

.status-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.status-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.status-label {
  display: flex;
  align-items: center;
  gap: 8px;
}

.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  display: inline-block;
  flex-shrink: 0;
}

.status-dot.dot-pending {
  background: #f59e0b;
}

.status-dot.dot-processing {
  background: #3b82f6;
}

.status-dot.dot-shipped {
  background: #8b5cf6;
}

.status-dot.dot-delivered {
  background: #10b981;
}

.status-dot.dot-cancelled {
  background: #ef4444;
}

.status-name {
  font-size: 0.85rem;
  color: #1a1a2e;
  flex: 1;
  text-transform: capitalize;
}

.status-count {
  font-size: 0.85rem;
  font-weight: 600;
  color: #6b7280;
}

.status-bar {
  width: 100%;
  height: 6px;
  background: #f3f4f6;
  border-radius: 3px;
  overflow: hidden;
}

.status-progress {
  height: 100%;
  border-radius: 3px;
  transition: width 0.8s ease;
}

.status-progress.progress-pending {
  background: #f59e0b;
}

.status-progress.progress-processing {
  background: #3b82f6;
}

.status-progress.progress-shipped {
  background: #8b5cf6;
}

.status-progress.progress-delivered {
  background: #10b981;
}

.status-progress.progress-cancelled {
  background: #ef4444;
}

.chart-empty {
  text-align: center;
  padding: 20px;
  color: #6b7280;
}

.chart-empty i {
  font-size: 2rem;
  opacity: 0.5;
  display: block;
  margin-bottom: 8px;
}

.chart-empty p {
  margin: 0;
}
</style>