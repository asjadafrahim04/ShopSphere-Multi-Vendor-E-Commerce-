<template>
  <div class="chart-card">
    <div class="chart-header">
      <h3>Revenue Overview</h3>
      <div class="chart-total">
        <span class="label">Total Revenue</span>
        <span class="amount">${{ totalRevenue.toFixed(2) }}</span>
      </div>
    </div>
    
    <div v-if="data.length === 0" class="chart-empty">
      <i class="bi bi-bar-chart"></i>
      <p>No revenue data available</p>
    </div>
    
    <div v-else class="chart-container">
      <div class="bar-chart">
        <div 
          v-for="(item, index) in chartData" 
          :key="index"
          class="bar-wrapper"
        >
          <div class="bar-track">
            <div 
              class="bar-fill"
              :style="{ height: getBarHeight(item.value) + '%' }"
            >
              <span class="bar-value">${{ item.value.toFixed(0) }}</span>
            </div>
          </div>
          <div class="bar-label">{{ item.label }}</div>
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

const totalRevenue = computed(() => {
  return props.data.reduce((sum, item) => sum + (item.total || 0), 0)
})

const chartData = computed(() => {
  return props.data.map(item => ({
    label: item.month_name?.substring(0, 3) || item.label?.substring(0, 3) || `M${item.month}`,
    value: Number(item.total || 0)
  }))
})

const getBarHeight = (value) => {
  const max = Math.max(...chartData.value.map(item => item.value), 1)
  return Math.max((value / max) * 80, 5)
}
</script>

<style scoped>
.chart-card {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 20px;
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 20px;
  flex-wrap: wrap;
  gap: 12px;
}

.chart-header h3 {
  font-size: 1rem;
  font-weight: 600;
  color: #1a1a2e;
  margin: 0;
}

.chart-total {
  text-align: right;
}

.chart-total .label {
  display: block;
  font-size: 0.75rem;
  color: #6b7280;
}

.chart-total .amount {
  font-size: 1.25rem;
  font-weight: 700;
  color: #667eea;
}

.chart-container {
  height: 200px;
  position: relative;
  padding-top: 10px;
}

.bar-chart {
  display: flex;
  justify-content: space-around;
  align-items: flex-end;
  height: 100%;
  gap: 8px;
}

.bar-wrapper {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  height: 100%;
  min-width: 30px;
}

.bar-track {
  width: 100%;
  height: 85%;
  display: flex;
  align-items: flex-end;
  justify-content: center;
  background: #f3f4f6;
  border-radius: 4px 4px 0 0;
  position: relative;
  min-height: 30px;
}

.bar-fill {
  width: 70%;
  max-width: 35px;
  background: linear-gradient(180deg, #667eea, #764ba2);
  border-radius: 4px 4px 0 0;
  transition: height 0.6s ease;
  position: relative;
  min-height: 4px;
  display: flex;
  align-items: flex-start;
  justify-content: center;
  cursor: pointer;
}

.bar-fill:hover {
  background: linear-gradient(180deg, #5a67d8, #6b3fa0);
}

.bar-value {
  font-size: 0.6rem;
  color: white;
  font-weight: 600;
  padding-top: 4px;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.bar-fill:hover .bar-value {
  opacity: 1;
}

.bar-label {
  font-size: 0.7rem;
  color: #6b7280;
  margin-top: 6px;
  text-align: center;
  font-weight: 500;
}

.chart-empty {
  height: 200px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: #6b7280;
}

.chart-empty i {
  font-size: 2.5rem;
  opacity: 0.5;
  margin-bottom: 8px;
}

.chart-empty p {
  margin: 0;
}

@media (max-width: 768px) {
  .bar-wrapper {
    min-width: 20px;
  }
  
  .bar-fill {
    width: 60%;
    max-width: 25px;
  }
  
  .bar-label {
    font-size: 0.6rem;
  }
}
</style>