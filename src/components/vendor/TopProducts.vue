<template>
  <div class="top-products">
    <div class="section-header">
      <h2>
        <i class="bi bi-trophy"></i> 
        Top Selling Products
      </h2>
      <span class="product-count">{{ products.length }} products</span>
    </div>
    
    <div v-if="products.length === 0" class="empty-state">
      <i class="bi bi-box"></i>
      <p>No products sold yet</p>
    </div>
    
    <div v-else class="products-grid">
      <div 
        v-for="(product, index) in products" 
        :key="product.id"
        class="product-card"
        @click="$emit('view-product', product.id)"
      >
        <div class="product-rank">{{ index + 1 }}</div>
        <div class="product-image">
          <img 
            v-if="product.images && product.images.length > 0" 
            :src="'/storage/' + product.images[0].image_path" 
            :alt="product.name"
          />
          <div v-else class="no-image">
            <i class="bi bi-box"></i>
          </div>
        </div>
        <div class="product-details">
          <h4 class="product-name">{{ product.name }}</h4>
          <div class="product-meta">
            <span class="product-price">${{ product.price }}</span>
            <span class="product-sold">
              <i class="bi bi-cart"></i> {{ product.total_sold || 0 }} sold
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  products: {
    type: Array,
    default: () => []
  }
})

defineEmits(['view-product'])
</script>

<style scoped>
.top-products {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 20px;
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

.product-count {
  font-size: 0.85rem;
  color: #6b7280;
  background: #f3f4f6;
  padding: 2px 12px;
  border-radius: 50px;
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 16px;
}

.product-card {
  background: #f9fafb;
  border-radius: 8px;
  padding: 12px;
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
}

.product-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  background: white;
}

.product-rank {
  position: absolute;
  top: -8px;
  left: -8px;
  width: 24px;
  height: 24px;
  background: #667eea;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  font-weight: 700;
}

.product-image {
  width: 100%;
  height: 120px;
  border-radius: 6px;
  overflow: hidden;
  background: #f3f4f6;
  margin-bottom: 8px;
}

.product-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.no-image {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #9ca3af;
  font-size: 2rem;
}

.product-details {
  text-align: center;
}

.product-name {
  font-size: 0.9rem;
  font-weight: 500;
  color: #1a1a2e;
  margin: 0 0 4px 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.product-meta {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 12px;
  font-size: 0.85rem;
}

.product-price {
  font-weight: 600;
  color: #667eea;
}

.product-sold {
  color: #6b7280;
  display: flex;
  align-items: center;
  gap: 4px;
}

.empty-state {
  text-align: center;
  padding: 30px 20px;
  color: #6b7280;
}

.empty-state i {
  font-size: 2.5rem;
  display: block;
  margin-bottom: 8px;
  opacity: 0.5;
}

.empty-state p {
  margin: 0;
}

@media (max-width: 768px) {
  .products-grid {
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  }
}

@media (max-width: 480px) {
  .products-grid {
    grid-template-columns: 1fr 1fr;
    gap: 12px;
  }
}
</style>