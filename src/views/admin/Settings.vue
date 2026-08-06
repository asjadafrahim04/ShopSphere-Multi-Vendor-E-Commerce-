<template>
  <div class="admin-settings">
    <div class="page-header">
      <h1>System Settings</h1>
      <p class="text-muted">Configure your store settings</p>
    </div>

    <div class="settings-grid">
      <!-- General Settings -->
      <div class="card">
        <h3>General Settings</h3>
        <form @submit.prevent="updateSettings">
          <div class="form-group">
            <label>Store Name</label>
            <input v-model="settings.site_name" type="text" class="form-input" />
          </div>
          <div class="form-group">
            <label>Currency</label>
            <select v-model="settings.currency" class="form-input">
              <option value="USD">USD ($)</option>
              <option value="BDT">BDT (৳)</option>
              <option value="EUR">EUR (€)</option>
              <option value="GBP">GBP (£)</option>
            </select>
          </div>
          <button type="submit" class="btn btn-primary">Save Settings</button>
        </form>
      </div>

      <!-- Commission & Fees -->
      <div class="card">
        <h3>Commission & Fees</h3>
        <form @submit.prevent="updateSettings">
          <div class="form-group">
            <label>Commission Rate (%)</label>
            <input v-model.number="settings.commission_rate" type="number" min="0" max="100" class="form-input" />
          </div>
          <div class="form-group">
            <label>Tax Rate (%)</label>
            <input v-model.number="settings.tax_rate" type="number" min="0" max="100" class="form-input" />
          </div>
          <button type="submit" class="btn btn-primary">Save Settings</button>
        </form>
      </div>

      <!-- Shipping Settings -->
      <div class="card">
        <h3>Shipping Settings</h3>
        <form @submit.prevent="updateSettings">
          <div class="form-group">
            <label>Shipping Rate ($)</label>
            <input v-model.number="settings.shipping_rate" type="number" min="0" class="form-input" />
          </div>
          <div class="form-group">
            <label>Free Shipping Threshold ($)</label>
            <input v-model.number="settings.free_shipping_threshold" type="number" min="0" class="form-input" />
          </div>
          <button type="submit" class="btn btn-primary">Save Settings</button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const loading = ref(true)
const settings = ref({
  site_name: 'ShopSphere',
  currency: 'USD',
  commission_rate: 10,
  tax_rate: 8,
  shipping_rate: 10,
  free_shipping_threshold: 100
})

const loadSettings = async () => {
  try {
    const token = localStorage.getItem('token')
    const response = await axios.get('http://localhost:8000/api/admin/settings', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })
    
    if (response.data.success) {
      settings.value = response.data.data
    }
  } catch (error) {
    console.error('Error loading settings:', error)
  } finally {
    loading.value = false
  }
}

const updateSettings = async () => {
  try {
    const token = localStorage.getItem('token')
    const response = await axios.put('http://localhost:8000/api/admin/settings', settings.value, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })
    
    if (response.data.success) {
      alert('✅ Settings updated successfully!')
    }
  } catch (error) {
    console.error('Error updating settings:', error)
    alert('❌ Failed to update settings')
  }
}

onMounted(() => {
  loadSettings()
})
</script>

<style scoped>
.admin-settings {
  max-width: 1200px;
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

.settings-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: 24px;
  margin-top: 20px;
}

.card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  border: 1px solid #e5e7eb;
}

.card h3 {
  font-size: 18px;
  font-weight: 600;
  margin: 0 0 16px 0;
  color: #1a1a2e;
}

.form-group {
  margin-bottom: 16px;
}

.form-group label {
  display: block;
  font-weight: 500;
  font-size: 14px;
  color: #1a1a2e;
  margin-bottom: 4px;
}

.form-input {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 14px;
}

.form-input:focus {
  outline: none;
  border-color: #667eea;
}

.btn {
  padding: 8px 24px;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-primary {
  background: #667eea;
  color: white;
}

.btn-primary:hover {
  background: #5a67d8;
}

@media (max-width: 768px) {
  .settings-grid {
    grid-template-columns: 1fr;
  }
}
</style>