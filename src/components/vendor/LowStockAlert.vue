<template>
  <div class="alert-section">
    <div class="section-header">
      <h2>
        <i class="bi bi-exclamation-triangle-fill text-warning"></i> 
        Low Stock Alert
      </h2>
      <span class="alert-count">{{ products.length }} products</span>
    </div>
    
    <div class="product-list">
      <div 
        v-for="product in products" 
        :key="product.id" 
        class="product-item"
        @click="$emit('view-product', product.id)"
      >
        <div class="product-info">
          <span class="product-name">{{ product.name }}</span>
          <span class="product-sku" v-if="product.sku">SKU: {{ product.sku }}</span>
        </div>
        <span class="product-stock" :class="{ 'critical': product.stock_quantity <= 2 }">
          {{ product.stock_quantity }} units left
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  products: {
    type: Array,
    required: true
  }
})

defineEmits(['view-product'])
</script>

<style scoped>
.alert-section {
  background: white;
  border: 1px solid #fef3c7;
  border-radius: 12px;
  padding: 20px;
  background: #fffbeb;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.section-header h2 {
  font-size: 1.1rem;
  font-weight: 600;
  color: #1a1a2e;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 8px;
}

.text-warning {
  color: #f59e0b;
}

.alert-count {
  font-size: 0.85rem;
  color: #6b7280;
  background: white;
  padding: 2px 12px;
  border-radius: 50px;
}

.product-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 12px;
  background: white;
  border-radius: 8px;
  margin-bottom: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.product-item:hover {
  transform: translateX(4px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.product-item:last-child {
  margin-bottom: 0;
}

.product-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.product-name {
  font-weight: 500;
  color: #1a1a2e;
}

.product-sku {
  font-size: 0.75rem;
  color: #6b7280;
}

.product-stock {
  font-size: 0.85rem;
  font-weight: 600;
  color: #f59e0b;
  padding: 4px 12px;
  background: #fef3c7;
  border-radius: 50px;
}

.product-stock.critical {
  color: #ef4444;
  background: #fee2e2;
}

@media (max-width: 768px) {
  .product-item {
    flex-direction: column;
    align-items: stretch;
    gap: 8px;
  }
  
  .product-stock {
    align-self: flex-start;
  }
}
</style>