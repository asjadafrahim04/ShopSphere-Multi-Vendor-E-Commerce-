<template>
  <div class="login-page">
    <div class="container-custom">
      <div class="login-container">
        <!-- Login Card -->
        <div class="login-card">
          <div class="login-header">
            <h2>Welcome Back!</h2>
            <p>Sign in to your ShopSphere account</p>
          </div>

          <!-- Login Form -->
          <form @submit.prevent="handleLogin" class="login-form">
            <!-- Email -->
            <div class="form-group">
              <label for="email">Email Address</label>
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
            </div>

            <!-- Password -->
            <div class="form-group">
              <label for="password">Password</label>
              <div class="input-group">
                <i class="bi bi-lock"></i>
                <input 
                  :type="showPassword ? 'text' : 'password'" 
                  id="password" 
                  v-model="form.password" 
                  placeholder="Enter your password"
                  required
                />
                <button 
                  type="button" 
                  class="toggle-password" 
                  @click="showPassword = !showPassword"
                >
                  <i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                </button>
              </div>
            </div>

            <!-- Remember Me & Forgot Password -->
            <div class="form-options">
              <label class="checkbox-label">
                <input type="checkbox" v-model="form.remember" />
                <span>Remember me</span>
              </label>
              <a href="#" class="forgot-password" @click.prevent="forgotPassword">Forgot Password?</a>
            </div>

            <!-- Error Message -->
            <div v-if="errorMessage" class="error-message">
              <i class="bi bi-exclamation-circle"></i>
              {{ errorMessage }}
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn-primary-modern login-btn" :disabled="isLoading">
              <span v-if="isLoading">
                <i class="bi bi-arrow-repeat spin"></i> Signing In...
              </span>
              <span v-else>
                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
              </span>
            </button>
          </form>

          <!-- Divider -->
          <div class="divider">
            <span>or</span>
          </div>

          <!-- Social Login -->
          <div class="social-login">
            <button type="button" class="social-btn google" @click="googleLogin">
              <i class="bi bi-google"></i>
              <span>Continue with Google</span>
            </button>
            <button type="button" class="social-btn facebook" @click="facebookLogin">
              <i class="bi bi-facebook"></i>
              <span>Continue with Facebook</span>
            </button>
          </div>

          <!-- Register Link -->
          <div class="register-link">
            <p>Don't have an account? <router-link to="/register">Register</router-link></p>
          </div>
        </div>

        <!-- Login Info Side -->
        <div class="login-info">
          <div class="info-content">
            <div class="info-icon">
              <i class="bi bi-shop"></i>
            </div>
            <h3>Welcome to ShopSphere!</h3>
            <p>Sign in to access exclusive features:</p>
            <ul class="benefits-list">
              <li>
                <i class="bi bi-check-circle-fill"></i>
                <span>Track your orders in real-time</span>
              </li>
              <li>
                <i class="bi bi-check-circle-fill"></i>
                <span>Save items to your wishlist</span>
              </li>
              <li>
                <i class="bi bi-check-circle-fill"></i>
                <span>Get personalized recommendations</span>
              </li>
              <li>
                <i class="bi bi-check-circle-fill"></i>
                <span>Manage your profile and preferences</span>
              </li>
              <li>
                <i class="bi bi-check-circle-fill"></i>
                <span>Access exclusive deals and discounts</span>
              </li>
            </ul>
            <div class="trust-badge">
              <i class="bi bi-shield-check"></i>
              <span>Secure login with 256-bit encryption</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'

const router = useRouter()

// ===== STATE =====
const isLoading = ref(false)
const showPassword = ref(false)
const errorMessage = ref('')

const form = reactive({
  email: '',
  password: '',
  remember: false
})

// ===== METHODS =====
const handleLogin = async () => {
  // Validate
  if (!form.email || !form.password) {
    errorMessage.value = 'Please fill in all fields!'
    return
  }

  errorMessage.value = ''
  isLoading.value = true

  try {
    const response = await api.post('/login', {
      email: form.email,
      password: form.password
    })

    if (response.data.success) {
      // Save token and user data
      localStorage.setItem('token', response.data.token)
      localStorage.setItem('user', JSON.stringify(response.data.user))
      
      // Dispatch auth event
      window.dispatchEvent(new CustomEvent('auth-changed'))
      
      isLoading.value = false
      
      // Show success message
      alert('✅ Login successful! Welcome back, ' + response.data.user.name + '!')
      
      // Redirect to home
      router.push('/')
    }
  } catch (error) {
    isLoading.value = false
    
    if (error.response) {
      // Server responded with error
      if (error.response.status === 401) {
        errorMessage.value = 'Invalid email or password. Please try again.'
      } else if (error.response.status === 422) {
        // Validation errors
        const errors = error.response.data.errors
        if (errors) {
          errorMessage.value = Object.values(errors).flat()[0]
        } else {
          errorMessage.value = 'Please check your input and try again.'
        }
      } else {
        errorMessage.value = error.response.data?.message || 'Login failed. Please try again.'
      }
    } else if (error.request) {
      // No response from server
      errorMessage.value = 'Cannot connect to server. Please make sure the backend is running.'
    } else {
      // Other errors
      errorMessage.value = 'An unexpected error occurred. Please try again.'
    }
  }
}

const googleLogin = () => {
  alert('🔴 Google Login coming soon!')
}

const facebookLogin = () => {
  alert('🔵 Facebook Login coming soon!')
}

const forgotPassword = () => {
  if (form.email) {
    alert(`📧 Password reset link sent to ${form.email}`)
  } else {
    alert('📧 Please enter your email address to reset password.')
  }
}
</script>

<style scoped>
.login-page {
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

/* ===== LOGIN CONTAINER ===== */
.login-container {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 50px;
  align-items: start;
  max-width: 1100px;
  margin: 0 auto;
}

/* ===== LOGIN CARD ===== */
.login-card {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: var(--radius);
  padding: 40px;
  box-shadow: var(--shadow);
}

.login-header {
  text-align: center;
  margin-bottom: 32px;
}

.login-header h2 {
  font-size: 2rem;
  font-weight: 700;
  color: var(--text-primary);
  margin-bottom: 6px;
}

.login-header p {
  color: var(--text-secondary);
  font-size: 1rem;
}

/* ===== ERROR MESSAGE ===== */
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
}

.error-message i {
  font-size: 1.2rem;
  flex-shrink: 0;
}

/* ===== FORM ===== */
.login-form {
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

.input-group input {
  width: 100%;
  padding: 12px 16px 12px 44px;
  border: 1px solid var(--border-color);
  border-radius: var(--radius-sm);
  background: var(--bg-secondary);
  color: var(--text-primary);
  font-size: 1rem;
  transition: var(--transition);
}

.input-group input:focus {
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

/* ===== FORM OPTIONS ===== */
.form-options {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin: 4px 0;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  font-size: 0.9rem;
  color: var(--text-secondary);
}

.checkbox-label input[type="checkbox"] {
  width: 16px;
  height: 16px;
  accent-color: #667eea;
  cursor: pointer;
}

.forgot-password {
  color: #667eea;
  text-decoration: none;
  font-size: 0.9rem;
  font-weight: 500;
  transition: var(--transition);
}

.forgot-password:hover {
  text-decoration: underline;
}

/* ===== LOGIN BUTTON ===== */
.login-btn {
  width: 100%;
  text-align: center;
  padding: 14px;
  font-size: 1rem;
  margin-top: 4px;
}

.login-btn:disabled {
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

/* ===== DIVIDER ===== */
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

/* ===== SOCIAL LOGIN ===== */
.social-login {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.social-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  padding: 12px;
  border: 1px solid var(--border-color);
  border-radius: var(--radius-sm);
  background: var(--bg-secondary);
  color: var(--text-primary);
  font-size: 0.95rem;
  font-weight: 500;
  cursor: pointer;
  transition: var(--transition);
}

.social-btn i {
  font-size: 1.3rem;
}

.social-btn.google i {
  color: #ea4335;
}

.social-btn.facebook i {
  color: #1877f2;
}

.social-btn:hover {
  border-color: #667eea;
  background: var(--bg-card);
  transform: translateY(-2px);
  box-shadow: var(--shadow);
}

/* ===== REGISTER LINK ===== */
.register-link {
  text-align: center;
  margin-top: 20px;
}

.register-link p {
  color: var(--text-secondary);
  font-size: 0.95rem;
}

.register-link a {
  color: #667eea;
  font-weight: 600;
  text-decoration: none;
}

.register-link a:hover {
  text-decoration: underline;
}

/* ===== LOGIN INFO SIDE ===== */
.login-info {
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

.trust-badge {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-top: 8px;
  padding: 12px 16px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: var(--radius-sm);
  border: 1px solid rgba(255, 255, 255, 0.15);
}

.trust-badge i {
  font-size: 1.5rem;
  color: #22c55e;
}

.trust-badge span {
  font-size: 0.85rem;
  opacity: 0.9;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 1024px) {
  .login-container {
    grid-template-columns: 1fr;
    max-width: 600px;
  }

  .login-info {
    min-height: auto;
    padding: 32px 24px;
  }

  .info-content {
    gap: 14px;
  }

  .info-icon {
    font-size: 2.5rem;
  }

  .info-content h3 {
    font-size: 1.5rem;
  }
}

@media (max-width: 768px) {
  .login-page {
    padding: 30px 0;
  }

  .login-card {
    padding: 24px 20px;
  }

  .login-header h2 {
    font-size: 1.6rem;
  }

  .social-btn {
    padding: 10px;
    font-size: 0.9rem;
  }

  .benefits-list li {
    font-size: 0.9rem;
  }
}

@media (max-width: 480px) {
  .login-card {
    padding: 20px 16px;
  }

  .input-group input {
    padding: 10px 14px 10px 38px;
    font-size: 0.9rem;
  }

  .input-group i:first-child {
    font-size: 0.9rem;
    left: 12px;
  }

  .toggle-password {
    font-size: 0.9rem;
  }

  .login-btn {
    padding: 12px;
    font-size: 0.95rem;
  }

  .form-options {
    flex-direction: column;
    gap: 8px;
    align-items: flex-start;
  }

  .login-info {
    padding: 24px 16px;
  }

  .info-content h3 {
    font-size: 1.2rem;
  }

  .info-content p {
    font-size: 0.95rem;
  }

  .benefits-list li {
    font-size: 0.85rem;
  }

  .trust-badge {
    flex-direction: column;
    text-align: center;
  }
}
</style>