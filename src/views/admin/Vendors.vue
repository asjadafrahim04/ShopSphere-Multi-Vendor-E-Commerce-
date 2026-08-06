<template>
  <div class="admin-vendors">
    <div class="page-header">
      <h1>Vendor Management</h1>
      <p class="text-muted">Manage all vendors and approve requests</p>
    </div>

    <!-- Filters -->
    <div class="filters-bar">
      <div class="search-box">
        <i class="bi bi-search"></i>
        <input 
          v-model="filters.search" 
          placeholder="Search vendors..." 
          @input="applyFilters"
        />
      </div>
      <div class="filter-group">
        <select v-model="filters.status" @change="applyFilters">
          <option value="">All Status</option>
          <option value="pending">Pending</option>
          <option value="approved">Approved</option>
        </select>
      </div>
      <button class="btn btn-primary" @click="resetFilters">Reset</button>
    </div>

    <!-- Vendors Table -->
    <div class="table-wrapper">
      <div v-if="loading" class="loading-state">Loading vendors...</div>
      
      <table v-else-if="vendors.length > 0" class="data-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Store Name</th>
            <th>Owner</th>
            <th>Email</th>
            <th>Category</th>
            <th>Status</th>
            <th>Joined</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="vendor in vendors" :key="vendor.id">
            <td>#{{ vendor.id }}</td>
            <td><strong>{{ vendor.shop_name }}</strong></td>
            <td>{{ vendor.user?.name }}</td>
            <td>{{ vendor.user?.email }}</td>
            <td>{{ vendor.business_category || 'N/A' }}</td>
            <td>
              <span class="status-badge" :class="vendor.is_approved ? 'approved' : 'pending'">
                {{ vendor.is_approved ? '✅ Approved' : '⏳ Pending' }}
              </span>
            </td>
            <td>{{ formatDate(vendor.created_at) }}</td>
            <td>
              <button v-if="!vendor.is_approved" class="btn-action approve" @click="approveVendor(vendor)">
                <i class="bi bi-check-circle"></i> Approve
              </button>
              <button v-else class="btn-action suspend" @click="suspendVendor(vendor)">
                <i class="bi bi-pause-circle"></i> Suspend
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-else-if="!loading" class="empty-state">
        <i class="bi bi-shop"></i>
        <p>No vendors found</p>
      </div>
    </div>

    <!-- Pagination -->
    <Pagination 
      v-if="pagination.last_page > 1"
      :current-page="pagination.current_page"
      :total-pages="pagination.last_page"
      @page-change="changePage"
    />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import axios from 'axios'
import Pagination from '@/components/Pagination.vue'

const vendors = ref([])
const loading = ref(true)

const filters = reactive({
  search: '',
  status: ''
})

const pagination = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0
})

const loadVendors = async () => {
  loading.value = true
  try {
    const token = localStorage.getItem('token')
    const response = await axios.get('http://localhost:8000/api/admin/vendors', {
      params: {
        page: pagination.current_page,
        ...filters
      },
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })
    
    if (response.data.success) {
      const data = response.data.data
      vendors.value = data.data || []
      pagination.current_page = data.current_page
      pagination.last_page = data.last_page
      pagination.per_page = data.per_page
      pagination.total = data.total
    }
  } catch (error) {
    console.error('Error loading vendors:', error)
  } finally {
    loading.value = false
  }
}

const approveVendor = async (vendor) => {
  try {
    const token = localStorage.getItem('token')
    await axios.put(`http://localhost:8000/api/admin/vendors/${vendor.id}/approve`, {}, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })
    vendor.is_approved = true
    alert('✅ Vendor approved!')
  } catch (error) {
    console.error('Error approving vendor:', error)
    alert('❌ Failed to approve vendor')
  }
}

const suspendVendor = async (vendor) => {
  if (!confirm(`Suspend ${vendor.shop_name}?`)) return
  
  try {
    const token = localStorage.getItem('token')
    await axios.put(`http://localhost:8000/api/admin/vendors/${vendor.id}/suspend`, {}, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })
    vendor.is_approved = false
    alert('✅ Vendor suspended!')
  } catch (error) {
    console.error('Error suspending vendor:', error)
    alert('❌ Failed to suspend vendor')
  }
}

const applyFilters = () => {
  pagination.current_page = 1
  loadVendors()
}

const resetFilters = () => {
  filters.search = ''
  filters.status = ''
  applyFilters()
}

const changePage = (page) => {
  pagination.current_page = page
  loadVendors()
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  })
}

onMounted(() => {
  loadVendors()
})
</script>

<style scoped>
.admin-vendors {
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

.filters-bar {
  display: flex;
  gap: 16px;
  margin: 20px 0;
  flex-wrap: wrap;
}

.search-box {
  flex: 1;
  min-width: 200px;
  display: flex;
  align-items: center;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 8px 16px;
}

.search-box i {
  color: #9ca3af;
  margin-right: 8px;
}

.search-box input {
  flex: 1;
  border: none;
  outline: none;
  font-size: 14px;
  background: transparent;
}

.filter-group select {
  padding: 8px 16px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  background: white;
  font-size: 14px;
  outline: none;
}

.btn {
  padding: 8px 20px;
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

.table-wrapper {
  background: white;
  border-radius: 12px;
  border: 1px solid #e5e7eb;
  overflow: hidden;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th {
  padding: 14px 16px;
  text-align: left;
  background: #f9fafb;
  font-weight: 600;
  font-size: 13px;
  color: #6b7280;
  border-bottom: 1px solid #e5e7eb;
}

.data-table td {
  padding: 14px 16px;
  border-bottom: 1px solid #f3f4f6;
}

.status-badge {
  padding: 2px 12px;
  border-radius: 50px;
  font-size: 12px;
  font-weight: 600;
}

.status-badge.approved {
  background: #d1fae5;
  color: #059669;
}

.status-badge.pending {
  background: #fef3c7;
  color: #d97706;
}

.btn-action {
  padding: 6px 14px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 13px;
  font-weight: 600;
  transition: all 0.2s ease;
}

.btn-action.approve {
  background: #d1fae5;
  color: #059669;
}

.btn-action.approve:hover {
  background: #a7f3d0;
}

.btn-action.suspend {
  background: #fee2e2;
  color: #dc2626;
}

.btn-action.suspend:hover {
  background: #fecaca;
}

.loading-state {
  text-align: center;
  padding: 40px;
  color: #6b7280;
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #6b7280;
}

.empty-state i {
  font-size: 48px;
  display: block;
  margin-bottom: 12px;
  color: #d1d5db;
}

@media (max-width: 768px) {
  .filters-bar {
    flex-direction: column;
  }
  
  .data-table {
    font-size: 13px;
  }
  
  .data-table th,
  .data-table td {
    padding: 10px 12px;
  }
}
</style>