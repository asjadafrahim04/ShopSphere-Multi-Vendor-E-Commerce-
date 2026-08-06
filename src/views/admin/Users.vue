<template>
  <div class="admin-users">
    <div class="page-header">
      <h1>Users Management</h1>
      <p class="text-muted">Manage all users</p>
    </div>

    <!-- Filters -->
    <div class="filters-bar">
      <div class="search-box">
        <i class="bi bi-search"></i>
        <input 
          v-model="filters.search" 
          placeholder="Search users..." 
          @input="applyFilters"
        />
      </div>
      <div class="filter-group">
        <select v-model="filters.role" @change="applyFilters">
          <option value="">All Roles</option>
          <option value="customer">Customer</option>
          <option value="vendor">Vendor</option>
          <option value="admin">Admin</option>
        </select>
      </div>
      <button class="btn btn-primary" @click="resetFilters">Reset</button>
    </div>

    <!-- Users Table -->
    <div class="table-wrapper">
      <div v-if="loading" class="loading-state">Loading users...</div>
      
      <table v-else-if="users.length > 0" class="data-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Joined</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in users" :key="user.id">
            <td>#{{ user.id }}</td>
            <td>{{ user.name }}</td>
            <td>{{ user.email }}</td>
            <td>
              <select v-model="user.role" @change="updateRole(user)" class="role-select">
                <option value="customer">Customer</option>
                <option value="vendor">Vendor</option>
                <option value="admin">Admin</option>
              </select>
            </td>
            <td>
              <span class="status-badge" :class="user.is_active ? 'active' : 'inactive'">
                {{ user.is_active ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td>{{ formatDate(user.created_at) }}</td>
            <td>
              <button class="btn-action toggle" @click="toggleStatus(user)">
                <i :class="user.is_active ? 'bi bi-pause-circle' : 'bi bi-play-circle'"></i>
              </button>
              <button class="btn-action delete" @click="deleteUser(user)">
                <i class="bi bi-trash"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-else-if="!loading" class="empty-state">
        <i class="bi bi-people"></i>
        <p>No users found</p>
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

const users = ref([])
const loading = ref(true)

const filters = reactive({
  search: '',
  role: ''
})

const pagination = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0
})

const loadUsers = async () => {
  loading.value = true
  try {
    const token = localStorage.getItem('token')
    const response = await axios.get('http://localhost:8000/api/admin/users', {
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
      users.value = data.data || []
      pagination.current_page = data.current_page
      pagination.last_page = data.last_page
      pagination.per_page = data.per_page
      pagination.total = data.total
    }
  } catch (error) {
    console.error('Error loading users:', error)
  } finally {
    loading.value = false
  }
}

const updateRole = async (user) => {
  try {
    const token = localStorage.getItem('token')
    await axios.put(`http://localhost:8000/api/admin/users/${user.id}/role`, {
      role: user.role
    }, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })
    alert('✅ User role updated!')
  } catch (error) {
    console.error('Error updating role:', error)
    alert('❌ Failed to update role')
  }
}

const toggleStatus = async (user) => {
  try {
    const token = localStorage.getItem('token')
    await axios.put(`http://localhost:8000/api/admin/users/${user.id}/status`, {}, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })
    user.is_active = !user.is_active
    alert(`✅ User ${user.is_active ? 'activated' : 'deactivated'}!`)
  } catch (error) {
    console.error('Error toggling status:', error)
    alert('❌ Failed to update status')
  }
}

const deleteUser = async (user) => {
  if (!confirm(`Are you sure you want to delete ${user.name}?`)) return
  
  try {
    const token = localStorage.getItem('token')
    await axios.delete(`http://localhost:8000/api/admin/users/${user.id}`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })
    alert('✅ User deleted!')
    loadUsers()
  } catch (error) {
    console.error('Error deleting user:', error)
    alert('❌ Failed to delete user')
  }
}

const applyFilters = () => {
  pagination.current_page = 1
  loadUsers()
}

const resetFilters = () => {
  filters.search = ''
  filters.role = ''
  applyFilters()
}

const changePage = (page) => {
  pagination.current_page = page
  loadUsers()
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  })
}

onMounted(() => {
  loadUsers()
})
</script>

<style scoped>
.admin-users {
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

.role-select {
  padding: 4px 8px;
  border: 1px solid #e5e7eb;
  border-radius: 4px;
  font-size: 13px;
  outline: none;
}

.role-select:focus {
  border-color: #667eea;
}

.status-badge {
  padding: 2px 12px;
  border-radius: 50px;
  font-size: 12px;
  font-weight: 600;
}

.status-badge.active {
  background: #d1fae5;
  color: #059669;
}

.status-badge.inactive {
  background: #fee2e2;
  color: #dc2626;
}

.btn-action {
  padding: 4px 8px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  margin-right: 4px;
  transition: all 0.2s ease;
}

.btn-action.toggle {
  background: #e0e7ff;
  color: #4f46e5;
}

.btn-action.toggle:hover {
  background: #c7d2fe;
}

.btn-action.delete {
  background: #fee2e2;
  color: #dc2626;
}

.btn-action.delete:hover {
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