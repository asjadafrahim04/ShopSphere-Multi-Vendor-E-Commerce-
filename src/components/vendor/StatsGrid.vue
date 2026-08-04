<template>
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-icon blue">
        <i class="bi bi-box-fill"></i>
      </div>
      <div class="stat-info">
        <span class="stat-number">{{ stats.total_products || 0 }}</span>
        <span class="stat-label">Total Products</span>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-icon green">
        <i class="bi bi-cart-fill"></i>
      </div>
      <div class="stat-info">
        <span class="stat-number">{{ stats.total_orders || 0 }}</span>
        <span class="stat-label">Total Orders</span>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-icon orange">
        <i class="bi bi-coin"></i>
      </div>
      <div class="stat-info">
        <span class="stat-number">${{ (stats.total_revenue || 0).toFixed(2) }}</span>
        <span class="stat-label">Total Revenue</span>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-icon red">
        <i class="bi bi-clock"></i>
      </div>
      <div class="stat-info">
        <span class="stat-number">{{ stats.pending_orders || 0 }}</span>
        <span class="stat-label">Pending Orders</span>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  stats: {
    type: Object,
    default: () => ({})
  }
})
</script>

<style scoped>
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
  margin-bottom: 32px;
}

.stat-card {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  transition: all 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  flex-shrink: 0;
}

.stat-icon.blue {
  background: rgba(102, 126, 234, 0.1);
  color: #667eea;
}

.stat-icon.green {
  background: rgba(52, 211, 153, 0.1);
  color: #34d399;
}

.stat-icon.orange {
  background: rgba(251, 191, 36, 0.1);
  color: #f59e0b;
}

.stat-icon.red {
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
}

.stat-info {
  display: flex;
  flex-direction: column;
}

.stat-number {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1a1a2e;
  line-height: 1.2;
}

.stat-label {
  font-size: 0.85rem;
  color: #6b7280;
}

@media (max-width: 1024px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .stats-grid {
    gap: 12px;
  }
  
  .stat-card {
    padding: 16px;
  }
  
  .stat-number {
    font-size: 1.2rem;
  }
}

@media (max-width: 480px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
}
</style>