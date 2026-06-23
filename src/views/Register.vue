<template>
  <div class="register-page">
    <div class="container-custom">
      <div class="register-container">
        <!-- Register Card -->
        <div class="register-card">
          <div class="register-header">
            <h2>Create Account</h2>
            <p>Join ShopSphere and start shopping or selling today</p>
          </div>

          <!-- Register Type Toggle -->
          <div class="register-type">
            <label>I want to register as:</label>
            <div class="type-toggle">
              <button 
                type="button" 
                class="type-btn" 
                :class="{ active: userType === 'customer' }"
                @click="userType = 'customer'"
              >
                <i class="bi bi-person"></i> Customer
                <span class="badge">Buy</span>
              </button>
              <button 
                type="button" 
                class="type-btn" 
                :class="{ active: userType === 'vendor' }"
                @click="userType = 'vendor'"
              >
                <i class="bi bi-store"></i> Vendor
                <span class="badge">Sell</span>
              </button>
            </div>
          </div>

          <!-- Register Form -->
          <form @submit.prevent="handleRegister" class="register-form">
            <!-- Common Fields -->
            <div class="form-group">
              <label for="fullName">Full Name</label>
              <div class="input-group">
                <i class="bi bi-person"></i>
                <input 
                  type="text" 
                  id="fullName" 
                  v-model="form.fullName" 
                  placeholder="Enter your full name"
                  required
                />
              </div>
            </div>

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

            <div class="form-group">
              <label for="phone">Phone Number</label>
              <div class="input-group">
                <i class="bi bi-phone"></i>
                <input 
                  type="tel" 
                  id="phone" 
                  v-model="form.phone" 
                  placeholder="+880 17XXXXXXXX"
                />
              </div>
            </div>

            <!-- Vendor Specific Fields -->
            <div v-if="userType === 'vendor'" class="vendor-fields">
              <div class="form-group">
                <label>Shop Name</label>
                <div class="input-group">
                  <i class="bi bi-shop"></i>
                  <input 
                    type="text" 
                    v-model="form.shopName" 
                    placeholder="Enter your shop name"
                    required
                  />
                </div>
              </div>
              <div class="form-group">
                <label>Business Category</label>
                <div class="input-group">
                  <i class="bi bi-tags"></i>
                  <select v-model="form.businessCategory" required>
                    <option value="">Select Category</option>
                    <option value="Electronics">Electronics</option>
                    <option value="Fashion">Fashion</option>
                    <option value="Home & Living">Home & Living</option>
                    <option value="Food">Food & Grocery</option>
                    <option value="Books">Books & Stationery</option>
                    <option value="Beauty">Beauty & Personal Care</option>
                    <option value="Other">Other</option>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label>Business Address</label>
                <div class="input-group">
                  <i class="bi bi-geo-alt"></i>
                  <input 
                    type="text" 
                    v-model="form.businessAddress" 
                    placeholder="Enter business address"
                  />
                </div>
              </div>
            </div>

            <!-- Password Fields -->
            <div class="form-group">
              <label for="password">Password</label>
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
            </div>

            <div class="form-group">
              <label for="confirmPassword">Confirm Password</label>
              <div class="input-group">
                <i class="bi bi-lock"></i>
                <input 
                  :type="showConfirmPassword ? 'text' : 'password'" 
                  id="confirmPassword" 
                  v-model="form.confirmPassword" 
                  placeholder="Confirm your password"
                  required
                  minlength="6"
                />
                <button 
                  type="button" 
                  class="toggle-password" 
                  @click="showConfirmPassword = !showConfirmPassword"
                >
                  <i :class="showConfirmPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                </button>
              </div>
            </div>

            <!-- Terms -->
            <div class="form-group terms-group">
              <label class="checkbox-label">
                <input type="checkbox" v-model="form.agreeTerms" required />
                <span>I agree to the <a href="#" @click.prevent="showTerms">Terms of Service</a> and <a href="#" @click.prevent="showPrivacy">Privacy Policy</a></span>
              </label>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn-primary-modern register-btn" :disabled="isLoading">
              <span v-if="isLoading">
                <i class="bi bi-arrow-repeat spin"></i> Creating Account...
              </span>
              <span v-else>
                <i class="bi bi-person-plus me-2"></i>
                {{ userType === 'vendor' ? 'Register as Vendor' : 'Create Account' }}
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

          <!-- Login Link -->
          <div class="login-link">
            <p>Already have an account? <router-link to="/login">Sign In</router-link></p>
          </div>
        </div>

        <!-- Register Info Side -->
        <div class="register-info">
          <div class="info-content">
            <div class="info-icon">
              <i class="bi bi-gift"></i>
            </div>
            <h3>{{ userType === 'vendor' ? 'Start Selling Today!' : 'Welcome to ShopSphere!' }}</h3>
            <p>{{ userType === 'vendor' ? 'Join thousands of vendors and grow your business.' : 'Create your account and enjoy exclusive benefits.' }}</p>
            <ul class="benefits-list">
              <li v-if="userType === 'customer'">
                <i class="bi bi-check-circle-fill"></i>
                <span>Exclusive deals and discounts</span>
              </li>
              <li v-if="userType === 'customer'">
                <i class="bi bi-check-circle-fill"></i>
                <span>Fast and secure checkout</span>
              </li>
              <li v-if="userType === 'customer'">
                <i class="bi bi-check-circle-fill"></i>
                <span>Track your orders easily</span>
              </li>
              <li v-if="userType === 'vendor'">
                <i class="bi bi-check-circle-fill"></i>
                <span>Reach thousands of customers</span>
              </li>
              <li v-if="userType === 'vendor'">
                <i class="bi bi-check-circle-fill"></i>
                <span>Easy product management</span>
              </li>
              <li v-if="userType === 'vendor'">
                <i class="bi bi-check-circle-fill"></i>
                <span>Sales analytics and reports</span>
              </li>
              <li>
                <i class="bi bi-check-circle-fill"></i>
                <span>24/7 customer support</span>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// ===== STATE =====
const isLoading = ref(false)
const showPassword = ref(false)
const showConfirmPassword = ref(false)
const userType = ref('customer')

const form = reactive({
  fullName: '',
  email: '',
  phone: '',
  password: '',
  confirmPassword: '',
  agreeTerms: false,
  // Vendor specific
  shopName: '',
  businessCategory: '',
  businessAddress: ''
})

// ===== METHODS =====
const handleRegister = () => {
  // Validate passwords match
  if (form.password !== form.confirmPassword) {
    alert('❌ Passwords do not match!')
    return
  }

  if (form.password.length < 6) {
    alert('❌ Password must be at least 6 characters!')
    return
  }

  if (!form.agreeTerms) {
    alert('❌ Please agree to the Terms of Service and Privacy Policy')
    return
  }

  // Validate vendor fields
  if (userType.value === 'vendor') {
    if (!form.shopName) {
      alert('❌ Please enter your shop name')
      return
    }
    if (!form.businessCategory) {
      alert('❌ Please select a business category')
      return
    }
  }

  isLoading.value = true

  // Simulate API call
  setTimeout(() => {
    const user = {
      type: userType.value,
      name: form.fullName,
      email: form.email,
      phone: form.phone,
      registered: new Date().toISOString(),
      ...(userType.value === 'vendor' && {
        shopName: form.shopName,
        businessCategory: form.businessCategory,
        businessAddress: form.businessAddress,
        isApproved: false // Vendor needs admin approval
      })
    }
    
    localStorage.setItem('shopsphere_user', JSON.stringify(user))
    window.dispatchEvent(new CustomEvent('auth-changed'))
    
    isLoading.value = false
    
    const message = userType.value === 'vendor' 
      ? '✅ Vendor registration submitted! Please wait for admin approval.' 
      : '✅ Registration successful! Welcome to ShopSphere!'
    
    alert(message)
    router.push(userType.value === 'vendor' ? '/' : '/profile')
  }, 1500)
}

const googleLogin = () => {
  alert('🔴 Google Login coming soon!')
}

const facebookLogin = () => {
  alert('🔵 Facebook Login coming soon!')
}

const showTerms = () => {
  alert('📜 Terms of Service: \n\n1. You must be 18+ to use this platform.\n2. All transactions are final.\n3. Users are responsible for their account security.\n4. Vendors must provide accurate product information.\n5. ShopSphere reserves the right to suspend accounts for violations.')
}

const showPrivacy = () => {
  alert('🔒 Privacy Policy: \n\n1. We collect your name, email, and phone number.\n2. Your data is used to process orders and improve services.\n3. We do not share your data with third parties.\n4. You can request data deletion at any time.\n5. Cookies are used for a better shopping experience.')
}
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

/* ===== REGISTER CONTAINER ===== */
.register-container {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 50px;
  align-items: start;
  max-width: 1100px;
  margin: 0 auto;
}

/* ===== REGISTER CARD ===== */
.register-card {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: var(--radius);
  padding: 40px;
  box-shadow: var(--shadow);
}

.register-header {
  text-align: center;
  margin-bottom: 24px;
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

/* ===== REGISTER TYPE TOGGLE ===== */
.register-type {
  margin-bottom: 24px;
}

.register-type label {
  display: block;
  font-size: 0.9rem;
  font-weight: 500;
  color: var(--text-secondary);
  margin-bottom: 8px;
}

.type-toggle {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
}

.type-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 12px 16px;
  border: 2px solid var(--border-color);
  border-radius: var(--radius-sm);
  background: var(--bg-secondary);
  color: var(--text-secondary);
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  transition: var(--transition);
}

.type-btn i {
  font-size: 1.2rem;
}

.type-btn .badge {
  background: var(--border-color);
  color: var(--text-muted);
  font-size: 0.65rem;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 50px;
  text-transform: uppercase;
}

.type-btn.active {
  border-color: #667eea;
  background: rgba(102, 126, 234, 0.1);
  color: var(--text-primary);
}

.type-btn.active .badge {
  background: #667eea;
  color: white;
}

.type-btn:hover:not(.active) {
  border-color: var(--text-muted);
}

/* ===== FORM ===== */
.register-form {
  display: flex;
  flex-direction: column;
  gap: 16px;
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
  appearance: none;
}

.input-group select {
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

/* ===== VENDOR FIELDS ===== */
.vendor-fields {
  padding: 16px;
  background: var(--bg-secondary);
  border-radius: var(--radius-sm);
  border: 1px solid var(--border-color);
}

.vendor-fields .form-group:last-child {
  margin-bottom: 0;
}

/* ===== TERMS ===== */
.terms-group {
  margin: 4px 0;
}

.checkbox-label {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  cursor: pointer;
  font-size: 0.9rem;
  color: var(--text-secondary);
}

.checkbox-label input[type="checkbox"] {
  width: 18px;
  height: 18px;
  margin-top: 2px;
  accent-color: #667eea;
  cursor: pointer;
  flex-shrink: 0;
}

.checkbox-label a {
  color: #667eea;
  text-decoration: none;
  font-weight: 500;
}

.checkbox-label a:hover {
  text-decoration: underline;
}

/* ===== REGISTER BUTTON ===== */
.register-btn {
  width: 100%;
  text-align: center;
  padding: 14px;
  font-size: 1rem;
  margin-top: 4px;
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

/* ===== LOGIN LINK ===== */
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

/* ===== REGISTER INFO SIDE ===== */
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

/* ===== RESPONSIVE ===== */
@media (max-width: 1024px) {
  .register-container {
    grid-template-columns: 1fr;
    max-width: 600px;
  }

  .register-info {
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
  .register-page {
    padding: 30px 0;
  }

  .register-card {
    padding: 24px 20px;
  }

  .register-header h2 {
    font-size: 1.6rem;
  }

  .social-btn {
    padding: 10px;
    font-size: 0.9rem;
  }

  .benefits-list li {
    font-size: 0.9rem;
  }

  .type-btn {
    padding: 10px 12px;
    font-size: 0.85rem;
  }
}

@media (max-width: 480px) {
  .register-card {
    padding: 20px 16px;
  }

  .input-group input,
  .input-group select {
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

  .register-btn {
    padding: 12px;
    font-size: 0.95rem;
  }

  .register-info {
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

  .type-toggle {
    grid-template-columns: 1fr;
  }
}
</style>