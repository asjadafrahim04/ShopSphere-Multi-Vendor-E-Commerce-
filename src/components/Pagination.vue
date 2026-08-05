<template>
  <div class="pagination">
    <button 
      :disabled="currentPage <= 1"
      @click="$emit('page-change', currentPage - 1)"
    >
      ← Prev
    </button>
    <span>{{ currentPage }} / {{ totalPages }}</span>
    <button 
      :disabled="currentPage >= totalPages"
      @click="$emit('page-change', currentPage + 1)"
    >
      Next →
    </button>
  </div>
</template>

<script setup>
defineProps({
  currentPage: Number,
  totalPages: Number
})

defineEmits(['page-change'])
</script>

<style scoped>
.pagination {
  display: flex;
  justify-content: center;
  gap: 16px;
  margin-top: 20px;
  align-items: center;
}

.pagination button {
  padding: 8px 16px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  background: white;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s ease;
}

.pagination button:hover:not(:disabled) {
  background: #667eea;
  color: white;
  border-color: #667eea;
}

.pagination button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.pagination span {
  font-weight: 500;
  color: #1a1a2e;
}
</style>