<template>
  <div class="register-page">
    <div class="container-custom">
      <div class="register-container">
        <!-- Register Card -->
        <div class="register-card">
          <div class="register-header">
            <h2>Create Account</h2>
            <p>Join ShopSphere today</p>
          </div>

          <form @submit.prevent="handleRegister" class="register-form">
            <!-- Name -->
            <div class="form-group">
              <label for="name">Full Name *</label>
              <div class="input-group">
                <i class="bi bi-person"></i>
                <input 
                  type="text" 
                  id="name" 
                  v-model="form.name" 
                  placeholder="Enter your full name"
                  required
                />
              </div>
              <p v-if="errors.name" class="error-text">{{ errors.name[0] }}</p>
            </div>

            <!-- Email -->
            <div class="form-group">
              <label for="email">Email Address *</label>
              <div class="input-group">
                <i class="bi bi-envelope"></i>
                <input 
                  type="email" 
                  id="email" 
                  v-model="form.email" 
                  placeholder="Enter your email"
                  required
                />
              </div>
              <p v-if="errors.email" class="error-text">{{ errors.email[0] }}</p>
            </div>

            <!-- Phone -->
            <div class="form-group">
              <label for="phone">Phone Number</label>
              <div class="input-group">
                <i class="bi bi-phone"></i>
                <input 
                  type="text" 
                  id="phone" 
                  v-model="form.phone" 
                  placeholder="Enter your phone number"
                />
              </div>
            </div>

            <!-- Password -->
            <div class="form-group">
              <label for="password">Password *</label>
              <div class="input-group">
                <i class="bi bi-lock"></i>
                <input 
                  :type="showPassword ? 'text' : 'password'" 
                  id="password" 
                  v-model="form.password" 
                  placeholder="Create a password (min 6 characters)"
                  required
                  minlength="6"
                />
                <button 
                  type="button" 
                  class="toggle-password" 
                  @click="showPassword = !showPassword"
                >
                  <i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                </button>
              </div>
              <p v-if="errors.password" class="error-text">{{ errors.password[0] }}</p>
            </div>

            <!-- Confirm Password -->
            <div class="form-group">
              <label for="password_confirmation">Confirm Password *</label>
              <div class="input-group">
                <i class="bi bi-lock"></i>
                <input 
                  type="password" 
                  id="password_confirmation" 
                  v-model="form.password_confirmation" 
                  placeholder="Confirm your password"
                  required
                />
              </div>
              <p v-if="errors.password_confirmation" class="error-text">{{ errors.password_confirmation[0] }}</p>
            </div>

            <!-- Role Selection -->
            <div class="form-group role-selection">
              <label>Register as</label>
              <div class="role-options">
                <label class="role-option" :class="{ active: form.role === 'customer' }">
                  <input type="radio" v-model="form.role" value="customer" />
                  <i class="bi bi-person"></i>
                  <span>Customer</span>
                </label>
                <label class="role-option" :class="{ active: form.role === 'vendor' }">
                  <input type="radio" v-model="form.role" value="vendor" />
                  <i class="bi bi-shop"></i>
                  <span>Vendor</span>
                </label>
              </div>
            </div>

            <!-- Vendor Specific Fields -->
            <div v-if="form.role === 'vendor'" class="vendor-fields">
              <div class="form-group">
                <label for="shop_name">Store Name *</label>
                <div class="input-group">
                  <i class="bi bi-shop"></i>
                  <input 
                    type="text" 
                    id="shop_name" 
                    v-model="form.shop_name" 
                    placeholder="Enter your store name"
                    required
                  />
                </div>
                <p v-if="errors.shop_name" class="error-text">{{ errors.shop_name[0] }}</p>
              </div>

              <div class="form-group">
                <label for="business_category">Business Category *</label>
                <div class="input-group">
                  <i class="bi bi-tag"></i>
                  <select 
                    id="business_category" 
                    v-model="form.business_category" 
                    class="form-select"
                    required
                  >
                    <option value="">Select Category</option>
                    <option value="Electronics">Electronics</option>
                    <option value="Fashion">Fashion</option>
                    <option value="Home & Living">Home & Living</option>
                    <option value="Beauty">Beauty</option>
                    <option value="Sports">Sports</option>
                    <option value="Books">Books</option>
                    <option value="Food">Food</option>
                    <option value="Other">Other</option>
                  </select>
                </div>
                <p v-if="errors.business_category" class="error-text">{{ errors.business_category[0] }}</p>
              </div>
            </div>

            <!-- Error Message -->
            <div v-if="errorMessage" class="error-message">
              <i class="bi bi-exclamation-circle"></i>
              {{ errorMessage }}
            </div>

            <!-- Submit Button -->
            <button type="submit" class="register-btn" :disabled="loading">
              <span v-if="loading">
                <i class="bi bi-arrow-repeat spin"></i> Creating Account...
              </span>
              <span v-else>
                <i class="bi bi-person-plus me-2"></i>
                {{ form.role === 'vendor' ? 'Register as Vendor' : 'Register as Customer' }}
              </span>
            </button>
          </form>

          <div class="divider">
            <span>or</span>
          </div>

          <!-- Login Link -->
          <div class="login-link">
            <p>Already have an account? <router-link to="/login">Sign In</router-link></p>
          </div>
        </div>

        <!-- Register Info Side -->
        <div class="register-info">
          <div class="info-content">
            <div class="info-icon">🛍️</div>
            <h3>Join ShopSphere</h3>
            <p>Create your account to get started</p>
            <ul class="benefits-list">
              <li>
                <i class="bi bi-check-circle-fill"></i>
                <span>Shop from trusted vendors</span>
              </li>
              <li>
                <i class="bi bi-check-circle-fill"></i>
                <span>Track your orders in real-time</span>
              </li>
              <li>
                <i class="bi bi-check-circle-fill"></i>
                <span>Get exclusive deals and discounts</span>
              </li>
              <li v-if="form.role === 'vendor'">
                <i class="bi bi-check-circle-fill"></i>
                <span>Sell your products online</span>
              </li>
              <li v-if="form.role === 'vendor'">
                <i class="bi bi-check-circle-fill"></i>
                <span>Reach thousands of customers</span>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watch } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()

// ===== STATE =====
const loading = ref(false)
const showPassword = ref(false)
const errorMessage = ref('')
const errors = reactive({})

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  phone: '',
  role: 'customer',
  shop_name: '',
  business_category: '',
})

// ===== METHODS =====
const handleRegister = async () => {
  // Reset errors
  errorMessage.value = ''
  Object.keys(errors).forEach(key => delete errors[key])

  loading.value = true

  try {
    // Prepare data
    const registerData = {
      name: form.name,
      email: form.email,
      password: form.password,
      password_confirmation: form.password_confirmation,
      phone: form.phone,
      role: form.role,
    }

    // Add vendor specific fields
    if (form.role === 'vendor') {
      registerData.shop_name = form.shop_name
      registerData.business_category = form.business_category
    }

    console.log('📤 Sending registration data:', registerData)

    const response = await axios.post('http://localhost:8000/api/register', registerData, {
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      }
    })

    console.log('📥 Registration response:', response.data)

    if (response.data.success) {
      // Save token and user data
      if (response.data.token) {
        localStorage.setItem('token', response.data.token)
        localStorage.setItem('user', JSON.stringify(response.data.user))
        window.dispatchEvent(new CustomEvent('auth-changed'))

        // Redirect based on role
        if (response.data.user?.role === 'vendor') {
          router.push('/vendor/dashboard')
        } else {
          router.push('/')
        }
      } else {
        // Registration without auto-login
        alert('Registration successful! Please login.')
        router.push('/login')
      }
    } else {
      errorMessage.value = response.data.message || 'Registration failed'
      if (response.data.errors) {
        Object.assign(errors, response.data.errors)
      }
    }
  } catch (error) {
    console.error('❌ Registration error:', error)
    console.error('Response:', error.response?.data)

    if (error.response) {
      if (error.response.status === 422) {
        if (error.response.data.errors) {
          Object.assign(errors, error.response.data.errors)
          const errorList = Object.values(error.response.data.errors).flat()
          errorMessage.value = errorList.join('\n')
        } else {
          errorMessage.value = 'Please check your input and try again.'
        }
      } else if (error.response.status === 409) {
        errorMessage.value = 'Email already registered. Please login.'
      } else {
        errorMessage.value = error.response.data?.message || 'Registration failed. Please try again.'
      }
    } else if (error.request) {
      errorMessage.value = 'Cannot connect to server. Please make sure the backend is running.'
    } else {
      errorMessage.value = 'An unexpected error occurred. Please try again.'
    }
  } finally {
    loading.value = false
  }
}

// ===== WATCHERS =====
watch(() => form.role, (newRole) => {
  if (newRole === 'customer') {
    form.shop_name = ''
    form.business_category = ''
  }
})
</script>

<style scoped>
.register-page {
  padding: 60px 0;
  background: var(--bg-primary);
  min-height: 100vh;
  display: flex;
  align-items: center;
}

.container-custom {
  width: 100%;
  max-width: 1280px;
  margin: 0 auto;
  padding: 0 20px;
}

.register-container {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 50px;
  align-items: start;
  max-width: 1100px;
  margin: 0 auto;
}

.register-card {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: var(--radius);
  padding: 40px;
  box-shadow: var(--shadow);
}

.register-header {
  text-align: center;
  margin-bottom: 32px;
}

.register-header h2 {
  font-size: 2rem;
  font-weight: 700;
  color: var(--text-primary);
  margin-bottom: 6px;
}

.register-header p {
  color: var(--text-secondary);
  font-size: 1rem;
}

.register-form {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-group label {
  font-size: 0.9rem;
  font-weight: 500;
  color: var(--text-secondary);
}

.input-group {
  position: relative;
  display: flex;
  align-items: center;
}

.input-group i:first-child {
  position: absolute;
  left: 14px;
  color: var(--text-muted);
  font-size: 1.1rem;
}

.input-group input,
.input-group select {
  width: 100%;
  padding: 12px 16px 12px 44px;
  border: 1px solid var(--border-color);
  border-radius: var(--radius-sm);
  background: var(--bg-secondary);
  color: var(--text-primary);
  font-size: 1rem;
  transition: var(--transition);
  font-family: inherit;
}

.input-group select {
  appearance: none;
  cursor: pointer;
}

.input-group input:focus,
.input-group select:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.input-group input::placeholder {
  color: var(--text-muted);
}

.toggle-password {
  position: absolute;
  right: 14px;
  background: none;
  border: none;
  color: var(--text-muted);
  cursor: pointer;
  padding: 4px;
  font-size: 1.1rem;
  transition: var(--transition);
}

.toggle-password:hover {
  color: var(--text-primary);
}

.error-text {
  color: #ef4444;
  font-size: 0.85rem;
  margin-top: 4px;
}

/* Role Selection */
.role-selection label {
  font-weight: 600;
  margin-bottom: 8px;
}

.role-options {
  display: flex;
  gap: 12px;
}

.role-option {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 12px;
  border: 2px solid var(--border-color);
  border-radius: var(--radius-sm);
  cursor: pointer;
  transition: var(--transition);
  background: var(--bg-secondary);
}

.role-option:hover {
  border-color: #667eea;
}

.role-option.active {
  border-color: #667eea;
  background: rgba(102, 126, 234, 0.08);
}

.role-option input[type="radio"] {
  display: none;
}

.role-option i {
  font-size: 1.3rem;
  color: #667eea;
}

.role-option span {
  font-weight: 500;
}

/* Vendor Fields */
.vendor-fields {
  border-top: 1px solid var(--border-color);
  padding-top: 16px;
  margin-top: 4px;
}

/* Buttons */
.register-btn {
  width: 100%;
  text-align: center;
  padding: 14px;
  background: var(--gradient-primary);
  color: white;
  border: none;
  border-radius: var(--radius-sm);
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: var(--transition);
}

.register-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4);
}

.register-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Error Messages */
.error-message {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 16px;
  background: #fee2e2;
  border: 1px solid #fecaca;
  border-radius: var(--radius-sm);
  color: #dc2626;
  font-size: 0.9rem;
  white-space: pre-line;
}

.error-message i {
  font-size: 1.2rem;
  flex-shrink: 0;
}

/* Divider */
.divider {
  display: flex;
  align-items: center;
  margin: 20px 0;
}

.divider::before,
.divider::after {
  content: '';
  flex: 1;
  height: 1px;
  background: var(--border-color);
}

.divider span {
  padding: 0 16px;
  color: var(--text-muted);
  font-size: 0.85rem;
  text-transform: uppercase;
}

/* Login Link */
.login-link {
  text-align: center;
  margin-top: 20px;
}

.login-link p {
  color: var(--text-secondary);
  font-size: 0.95rem;
}

.login-link a {
  color: #667eea;
  font-weight: 600;
  text-decoration: none;
}

.login-link a:hover {
  text-decoration: underline;
}

/* Register Info */
.register-info {
  background: var(--gradient-primary);
  border-radius: var(--radius);
  padding: 48px 40px;
  color: white;
  display: flex;
  align-items: center;
  min-height: 500px;
}

.info-content {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.info-icon {
  font-size: 3.5rem;
}

.info-content h3 {
  font-size: 1.8rem;
  font-weight: 700;
  margin: 0;
}

.info-content p {
  opacity: 0.9;
  font-size: 1.05rem;
  line-height: 1.6;
}

.benefits-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.benefits-list li {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 0.95rem;
  opacity: 0.95;
}

.benefits-list li i {
  color: #22c55e;
  font-size: 1.2rem;
}

/* Responsive */
@media (max-width: 1024px) {
  .register-container {
    grid-template-columns: 1fr;
    max-width: 600px;
  }

  .register-info {
    min-height: auto;
    padding: 32px 24px;
  }
}

@media (max-width: 768px) {
  .register-page {
    padding: 30px 0;
  }

  .register-card {
    padding: 24px 20px;
  }

  .register-header h2 {
    font-size: 1.6rem;
  }

  .role-options {
    flex-direction: column;
  }

  .register-info {
    padding: 24px 16px;
  }

  .info-content h3 {
    font-size: 1.5rem;
  }
}

@media (max-width: 480px) {
  .register-card {
    padding: 20px 16px;
  }

  .input-group input,
  .input-group select {
    font-size: 0.9rem;
    padding: 10px 14px 10px 38px;
  }

  .input-group i:first-child {
    font-size: 0.9rem;
    left: 12px;
  }

  .register-btn {
    padding: 12px;
    font-size: 0.95rem;
  }

  .register-info {
    padding: 20px 16px;
  }

  .info-content h3 {
    font-size: 1.2rem;
  }

  .benefits-list li {
    font-size: 0.85rem;
  }
}
</style>