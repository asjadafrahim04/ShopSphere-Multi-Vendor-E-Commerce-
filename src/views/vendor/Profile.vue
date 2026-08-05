<template>
  <div class="vendor-profile">
    <!-- Header -->
    <div class="profile-header">
      <div>
        <h1>Vendor Profile</h1>
        <p class="text-muted">Manage your store profile and settings</p>
      </div>
      <button class="btn btn-primary" @click="isEditing = !isEditing">
        <i class="bi bi-pencil"></i>
        {{ isEditing ? 'Cancel' : 'Edit Profile' }}
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Loading profile...</p>
    </div>

    <!-- Profile Content -->
    <div v-else-if="profile" class="profile-content">
      <!-- Profile Card -->
      <div class="profile-card">
        <div class="profile-avatar-section">
          <div class="avatar-wrapper">
            <img 
              :src="profile.avatar || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(profile.name) + '&background=667eea&color=fff&size=128'" 
              :alt="profile.name"
              class="profile-avatar"
            />
            <label v-if="isEditing" class="avatar-upload" for="avatar-upload">
              <i class="bi bi-camera"></i>
            </label>
            <input 
              id="avatar-upload"
              type="file" 
              accept="image/*"
              @change="uploadAvatar"
              style="display: none"
            />
          </div>
          <div class="profile-name-section">
            <h2>{{ profile.name }}</h2>
            <span class="role-badge vendor">Vendor</span>
            <span v-if="profile.vendor?.is_approved" class="role-badge approved">✓ Approved</span>
          </div>
        </div>

        <!-- Store Info -->
        <div class="store-info">
          <h3><i class="bi bi-shop"></i> Store Information</h3>
          <div class="info-grid">
            <div class="info-item">
              <label>Store Name</label>
              <p>{{ profile.vendor?.shop_name || 'N/A' }}</p>
            </div>
            <div class="info-item">
              <label>Store Description</label>
              <p>{{ profile.vendor?.shop_description || 'No description' }}</p>
            </div>
            <div class="info-item">
              <label>Store Phone</label>
              <p>{{ profile.vendor?.shop_phone || 'N/A' }}</p>
            </div>
            <div class="info-item">
              <label>Store Address</label>
              <p>{{ profile.vendor?.shop_address || 'N/A' }}</p>
            </div>
          </div>
        </div>

        <!-- Business Stats -->
        <div class="stats-section">
          <h3><i class="bi bi-graph-up"></i> Store Statistics</h3>
          <div class="stats-grid">
            <div class="stat-item">
              <span class="stat-value">{{ profile.vendor?.stats?.total_products || 0 }}</span>
              <span class="stat-label">Total Products</span>
            </div>
            <div class="stat-item">
              <span class="stat-value">{{ profile.vendor?.stats?.total_orders || 0 }}</span>
              <span class="stat-label">Total Orders</span>
            </div>
            <div class="stat-item">
              <span class="stat-value">${{ (profile.vendor?.total_revenue || 0).toFixed(2) }}</span>
              <span class="stat-label">Total Revenue</span>
            </div>
            <div class="stat-item">
              <span class="stat-value">{{ profile.vendor?.commission_rate || 0 }}%</span>
              <span class="stat-label">Commission Rate</span>
            </div>
          </div>
        </div>

        <!-- Edit Form -->
        <div v-if="isEditing" class="edit-form">
          <h3><i class="bi bi-pencil-square"></i> Edit Profile</h3>
          <form @submit.prevent="updateProfile">
            <div class="form-grid">
              <div class="form-group">
                <label>Full Name *</label>
                <input v-model="editForm.name" type="text" required class="form-input" />
              </div>
              <div class="form-group">
                <label>Phone</label>
                <input v-model="editForm.phone" type="text" class="form-input" />
              </div>
              <div class="form-group full-width">
                <label>Store Name *</label>
                <input v-model="editForm.shop_name" type="text" required class="form-input" />
              </div>
              <div class="form-group full-width">
                <label>Store Description</label>
                <textarea v-model="editForm.shop_description" rows="3" class="form-input"></textarea>
              </div>
              <div class="form-group">
                <label>Store Phone</label>
                <input v-model="editForm.shop_phone" type="text" class="form-input" />
              </div>
              <div class="form-group">
                <label>Store Address</label>
                <input v-model="editForm.shop_address" type="text" class="form-input" />
              </div>
            </div>

            <div v-if="updateError" class="error-message">{{ updateError }}</div>
            <div v-if="updateSuccess" class="success-message">{{ updateSuccess }}</div>

            <div class="form-actions">
              <button type="button" class="btn btn-secondary" @click="cancelEdit">Cancel</button>
              <button type="submit" class="btn btn-primary" :disabled="updating">
                {{ updating ? 'Saving...' : 'Save Changes' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Toast -->
    <div v-if="toast.show" class="toast-notification" :class="toast.type">
      <i :class="toast.icon"></i>
      {{ toast.message }}
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import axios from 'axios'

// ===== STATE =====
const profile = ref(null)
const loading = ref(true)
const isEditing = ref(false)
const updating = ref(false)
const updateError = ref('')
const updateSuccess = ref('')

const editForm = reactive({
  name: '',
  phone: '',
  shop_name: '',
  shop_description: '',
  shop_phone: '',
  shop_address: '',
})

const toast = ref({
  show: false,
  message: '',
  type: 'success',
  icon: 'bi bi-check-circle-fill'
})

// ===== LOAD PROFILE =====
const loadProfile = async () => {
  loading.value = true
  try {
    const token = localStorage.getItem('token')
    const response = await axios.get('http://localhost:8000/api/vendor/profile', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })

    if (response.data.success) {
      profile.value = response.data.data
      // Populate edit form
      editForm.name = profile.value.name || ''
      editForm.phone = profile.value.phone || ''
      editForm.shop_name = profile.value.vendor?.shop_name || ''
      editForm.shop_description = profile.value.vendor?.shop_description || ''
      editForm.shop_phone = profile.value.vendor?.shop_phone || ''
      editForm.shop_address = profile.value.vendor?.shop_address || ''
    }
  } catch (error) {
    console.error('Error loading profile:', error)
    showToast('Failed to load profile', 'error', 'bi bi-exclamation-circle-fill')
  } finally {
    loading.value = false
  }
}

// ===== UPDATE PROFILE =====
const updateProfile = async () => {
  updating.value = true
  updateError.value = ''
  updateSuccess.value = ''

  try {
    const token = localStorage.getItem('token')
    const response = await axios.put('http://localhost:8000/api/vendor/profile', editForm, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      }
    })

    if (response.data.success) {
      profile.value = response.data.data
      updateSuccess.value = 'Profile updated successfully!'
      showToast('Profile updated successfully!', 'success', 'bi bi-check-circle-fill')
      setTimeout(() => {
        isEditing.value = false
        updateSuccess.value = ''
      }, 1500)
    }
  } catch (error) {
    console.error('Error updating profile:', error)
    if (error.response?.data?.errors) {
      const errors = error.response.data.errors
      updateError.value = Object.values(errors).flat().join('\n')
    } else {
      updateError.value = error.response?.data?.message || 'Failed to update profile'
    }
    showToast('Failed to update profile', 'error', 'bi bi-exclamation-circle-fill')
  } finally {
    updating.value = false
  }
}

// ===== UPLOAD AVATAR =====
const uploadAvatar = async (event) => {
  const file = event.target.files[0]
  if (!file) return

  const formData = new FormData()
  formData.append('avatar', file)

  try {
    const token = localStorage.getItem('token')
    const response = await axios.post('http://localhost:8000/api/vendor/profile/logo', formData, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'multipart/form-data',
        'Accept': 'application/json'
      }
    })

    if (response.data.success) {
      profile.value = response.data.data
      showToast('Avatar updated!', 'success', 'bi bi-check-circle-fill')
    }
  } catch (error) {
    console.error('Error uploading avatar:', error)
    showToast('Failed to upload avatar', 'error', 'bi bi-exclamation-circle-fill')
  }
}

// ===== CANCEL EDIT =====
const cancelEdit = () => {
  isEditing.value = false
  // Reset form to original values
  if (profile.value) {
    editForm.name = profile.value.name || ''
    editForm.phone = profile.value.phone || ''
    editForm.shop_name = profile.value.vendor?.shop_name || ''
    editForm.shop_description = profile.value.vendor?.shop_description || ''
    editForm.shop_phone = profile.value.vendor?.shop_phone || ''
    editForm.shop_address = profile.value.vendor?.shop_address || ''
  }
  updateError.value = ''
  updateSuccess.value = ''
}

// ===== TOAST =====
const showToast = (message, type = 'success', icon = 'bi bi-check-circle-fill') => {
  toast.value = { show: true, message, type, icon }
  setTimeout(() => { toast.value.show = false }, 3000)
}

// ===== LIFECYCLE =====
onMounted(() => {
  loadProfile()
})
</script>

<style scoped>
.vendor-profile {
  padding: 24px;
  max-width: 1200px;
  margin: 0 auto;
}

/* Header */
.profile-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 30px;
  flex-wrap: wrap;
  gap: 16px;
}

.profile-header h1 {
  font-size: 28px;
  font-weight: 700;
  margin: 0;
  color: #1a1a2e;
}

.text-muted {
  color: #6b7280;
  margin: 4px 0 0;
}

.btn {
  padding: 10px 24px;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.btn-primary {
  background: #667eea;
  color: white;
}

.btn-primary:hover {
  background: #5a67d8;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
  background: #e5e7eb;
  color: #1a1a2e;
}

.btn-secondary:hover {
  background: #d1d5db;
}

/* Loading */
.loading-state {
  text-align: center;
  padding: 60px;
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

.loading-state p {
  color: #6b7280;
}

/* Profile Card */
.profile-card {
  background: white;
  border-radius: 12px;
  border: 1px solid #e5e7eb;
  padding: 32px;
}

/* Avatar */
.profile-avatar-section {
  display: flex;
  align-items: center;
  gap: 24px;
  padding-bottom: 24px;
  border-bottom: 1px solid #e5e7eb;
  margin-bottom: 24px;
  flex-wrap: wrap;
}

.avatar-wrapper {
  position: relative;
  width: 100px;
  height: 100px;
  flex-shrink: 0;
}

.profile-avatar {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid #667eea;
}

.avatar-upload {
  position: absolute;
  bottom: 0;
  right: 0;
  width: 32px;
  height: 32px;
  background: #667eea;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: white;
  border: 2px solid white;
  transition: all 0.3s ease;
}

.avatar-upload:hover {
  transform: scale(1.1);
  background: #5a67d8;
}

.profile-name-section h2 {
  font-size: 24px;
  font-weight: 700;
  margin: 0 0 8px 0;
  color: #1a1a2e;
}

.role-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 50px;
  font-size: 12px;
  font-weight: 600;
  margin-right: 8px;
}

.role-badge.vendor {
  background: #e0e7ff;
  color: #4f46e5;
}

.role-badge.approved {
  background: #d1fae5;
  color: #059669;
}

/* Store Info */
.store-info h3,
.stats-section h3,
.edit-form h3 {
  font-size: 18px;
  font-weight: 600;
  color: #1a1a2e;
  margin: 0 0 16px 0;
  display: flex;
  align-items: center;
  gap: 8px;
}

.info-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.info-item label {
  display: block;
  font-size: 13px;
  font-weight: 500;
  color: #6b7280;
  margin-bottom: 4px;
}

.info-item p {
  font-size: 15px;
  color: #1a1a2e;
  margin: 0;
}

/* Stats */
.stats-section {
  margin: 24px 0;
  padding: 24px 0;
  border-top: 1px solid #e5e7eb;
  border-bottom: 1px solid #e5e7eb;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
}

.stat-item {
  text-align: center;
}

.stat-value {
  display: block;
  font-size: 28px;
  font-weight: 700;
  color: #667eea;
}

.stat-label {
  font-size: 14px;
  color: #6b7280;
}

/* Edit Form */
.edit-form {
  margin-top: 24px;
  padding-top: 24px;
  border-top: 1px solid #e5e7eb;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.form-group.full-width {
  grid-column: 1 / -1;
}

.form-group label {
  display: block;
  font-size: 14px;
  font-weight: 500;
  color: #1a1a2e;
  margin-bottom: 4px;
}

.form-input {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 14px;
  transition: all 0.2s ease;
}

.form-input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

textarea.form-input {
  resize: vertical;
  min-height: 80px;
}

.error-message {
  background: #fee2e2;
  color: #dc2626;
  padding: 12px;
  border-radius: 8px;
  font-size: 14px;
  margin: 12px 0;
  white-space: pre-line;
}

.success-message {
  background: #d1fae5;
  color: #059669;
  padding: 12px;
  border-radius: 8px;
  font-size: 14px;
  margin: 12px 0;
}

.form-actions {
  display: flex;
  gap: 12px;
  margin-top: 16px;
}

/* Toast */
.toast-notification {
  position: fixed;
  bottom: 30px;
  right: 30px;
  padding: 16px 24px;
  border-radius: 8px;
  color: white;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 12px;
  z-index: 9999;
  animation: slideUp 0.3s ease;
  box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}

.toast-notification.success {
  background: #10b981;
}

.toast-notification.error {
  background: #ef4444;
}

.toast-notification i {
  font-size: 20px;
}

@keyframes slideUp {
  from { transform: translateY(100px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}

/* Responsive */
@media (max-width: 1024px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .profile-header {
    flex-direction: column;
  }
  
  .profile-avatar-section {
    flex-direction: column;
    text-align: center;
  }
  
  .info-grid {
    grid-template-columns: 1fr;
  }
  
  .stats-grid {
    grid-template-columns: 1fr 1fr;
  }
  
  .form-grid {
    grid-template-columns: 1fr;
  }
  
  .form-group.full-width {
    grid-column: 1;
  }
}

@media (max-width: 480px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
}
</style>