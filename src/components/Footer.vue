<template>
  <footer class="footer-modern">
    <div class="container-custom">
      <!-- Footer Top -->
      <div class="footer-top">
        <!-- Brand Section -->
        <div class="footer-brand">
          <div class="brand-logo">
            <span class="logo-icon">🛒</span>
            <span class="brand-name">ShopSphere</span>
          </div>
          <p class="brand-description">
            Bangladesh's premier multi-vendor e-commerce marketplace. 
            Connecting buyers and sellers across the country with trust, 
            quality, and amazing deals.
          </p>
          <div class="social-links">
            <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
            <a href="#" aria-label="Twitter"><i class="bi bi-twitter-x"></i></a>
            <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
            <a href="#" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
            <a href="#" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
          </div>
        </div>

        <!-- Shop Links -->
        <div class="footer-links">
          <h4>SHOP</h4>
          <ul>
            <li><router-link to="/products">All Products</router-link></li>
            <li><router-link to="/products?category=Electronics">Electronics</router-link></li>
            <li><router-link to="/products?category=Fashion">Fashion</router-link></li>
            <li><router-link to="/products?category=Home & Living">Home & Living</router-link></li>
            <li><router-link to="/products?category=Beauty">Beauty</router-link></li>
          </ul>
        </div>

        <!-- Account Links -->
        <div class="footer-links">
          <h4>ACCOUNT</h4>
          <ul>
            <li><router-link to="/login">Login</router-link></li>
            <li><router-link to="/register">Register</router-link></li>
            <li><router-link to="/profile">My Profile</router-link></li>
            <li><router-link to="/orders">My Orders</router-link></li>
            <li><router-link to="/wishlist">Wishlist</router-link></li>
            <li><router-link to="/cart">Cart</router-link></li>
          </ul>
        </div>

        <!-- Vendor Links -->
        <div class="footer-links">
          <h4>VENDOR</h4>
          <ul>
            <li><a href="#" @click.prevent="becomeVendor">Become Vendor</a></li>
            <li><a href="#" @click.prevent="vendorDashboard">Vendor Dashboard</a></li>
            <li><a href="#" @click.prevent="adminPanel">Admin Panel</a></li>
            <li><a href="#" @click.prevent="vendorPolicy">Vendor Policy</a></li>
          </ul>
        </div>

        <!-- Support Links -->
        <div class="footer-links">
          <h4>SUPPORT</h4>
          <ul>
            <li><a href="#" @click.prevent="helpCenter">Help Center</a></li>
            <li><a href="#" @click.prevent="trackOrder">Track Order</a></li>
            <li><a href="#" @click.prevent="returns">Returns</a></li>
            <li><a href="#" @click.prevent="privacyPolicy">Privacy Policy</a></li>
            <li><a href="#" @click.prevent="termsOfService">Terms of Service</a></li>
          </ul>
        </div>
      </div>

      <!-- Footer Bottom -->
      <div class="footer-bottom">
        <div class="footer-copyright">
          <p>© 2026 ShopSphere. All rights reserved. Made with Asjad Afrahim.</p>
        </div>
        <div class="payment-methods">
          <span class="payment-icon bkash">bKash</span>
          <span class="payment-icon nagad">Nagad</span>
          <span class="payment-icon visa">VISA</span>
          <span class="payment-icon mc">MC</span>
          <span class="payment-icon cod">COD</span>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup>
const becomeVendor = () => {
  alert('🚀 Vendor registration coming soon!')
}

const vendorDashboard = () => {
  alert('📊 Vendor Dashboard coming soon!')
}

const adminPanel = () => {
  alert('🔧 Admin Panel coming soon!')
}

const vendorPolicy = () => {
  alert('📋 Vendor Policy coming soon!')
}

const helpCenter = () => {
  alert('💬 Help Center coming soon!')
}

const trackOrder = () => {
  alert('📦 Track Order coming soon!')
}

const returns = () => {
  alert('🔄 Returns policy coming soon!')
}

const privacyPolicy = () => {
  alert('🔒 Privacy Policy coming soon!')
}

const termsOfService = () => {
  alert('📜 Terms of Service coming soon!')
}
</script>

<style scoped>
.footer-modern {
  background: var(--bg-secondary);
  border-top: 1px solid var(--border-color);
  padding: 60px 0 20px;
  margin-top: auto;
}

/* ===== FOOTER TOP ===== */
.footer-top {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1fr 1fr;
  gap: 40px;
  margin-bottom: 40px;
  padding-bottom: 40px;
  border-bottom: 1px solid var(--border-color);
}

/* ===== BRAND SECTION ===== */
.footer-brand {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.brand-logo {
  display: flex;
  align-items: center;
  gap: 10px;
}

.logo-icon {
  font-size: 2rem;
}

.brand-name {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--text-primary);
}

.brand-description {
  color: var(--text-secondary);
  font-size: 0.95rem;
  line-height: 1.7;
  max-width: 350px;
}

.social-links {
  display: flex;
  gap: 12px;
  margin-top: 4px;
}

.social-links a {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 38px;
  height: 38px;
  border-radius: 50%;
  background: var(--bg-card);
  color: var(--text-secondary);
  font-size: 1.1rem;
  transition: var(--transition);
  border: 1px solid var(--border-color);
  text-decoration: none;
}

.social-links a:hover {
  background: var(--gradient-primary);
  color: white;
  border-color: transparent;
  transform: translateY(-3px);
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

/* ===== FOOTER LINKS ===== */
.footer-links h4 {
  font-size: 0.85rem;
  font-weight: 700;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 1px;
  margin-bottom: 16px;
}

.footer-links ul {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.footer-links ul li a {
  color: var(--text-secondary);
  text-decoration: none;
  font-size: 0.95rem;
  transition: var(--transition);
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.footer-links ul li a:hover {
  color: var(--text-primary);
  transform: translateX(4px);
}

.footer-links ul li a i {
  font-size: 0.9rem;
  color: #667eea;
}

/* ===== FOOTER BOTTOM ===== */
.footer-bottom {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 16px;
}

.footer-copyright p {
  color: var(--text-muted);
  font-size: 0.9rem;
  margin: 0;
}

.heart {
  color: #ef4444;
  display: inline-block;
  animation: heartbeat 1.5s ease-in-out infinite;
}

@keyframes heartbeat {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.2); }
}

/* ===== PAYMENT METHODS ===== */
.payment-methods {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.payment-icon {
  padding: 4px 14px;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border: 1px solid var(--border-color);
  color: var(--text-secondary);
  transition: var(--transition);
  cursor: default;
}

.payment-icon:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow);
}

.payment-icon.bkash {
  background: #E2136E;
  color: white;
  border-color: #E2136E;
}

.payment-icon.nagad {
  background: #FF6B00;
  color: white;
  border-color: #FF6B00;
}

.payment-icon.visa {
  background: #1A1F71;
  color: white;
  border-color: #1A1F71;
}

.payment-icon.mc {
  background: #EB001B;
  color: white;
  border-color: #EB001B;
}

.payment-icon.cod {
  background: #059669;
  color: white;
  border-color: #059669;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 1024px) {
  .footer-top {
    grid-template-columns: 1fr 1fr 1fr;
    gap: 30px;
  }

  .footer-brand {
    grid-column: 1 / -1;
  }

  .brand-description {
    max-width: 100%;
  }
}

@media (max-width: 768px) {
  .footer-modern {
    padding: 40px 0 20px;
  }

  .footer-top {
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    margin-bottom: 30px;
    padding-bottom: 30px;
  }

  .footer-brand {
    grid-column: 1 / -1;
  }

  .social-links {
    justify-content: flex-start;
  }

  .footer-bottom {
    flex-direction: column;
    text-align: center;
    gap: 12px;
  }

  .payment-methods {
    justify-content: center;
  }
}

@media (max-width: 480px) {
  .footer-top {
    grid-template-columns: 1fr;
    gap: 20px;
  }

  .footer-links h4 {
    margin-bottom: 10px;
  }

  .footer-links ul {
    gap: 6px;
  }

  .social-links {
    justify-content: center;
  }

  .brand-description {
    text-align: center;
  }

  .brand-logo {
    justify-content: center;
  }

  .payment-methods {
    gap: 8px;
  }

  .payment-icon {
    font-size: 0.65rem;
    padding: 3px 10px;
  }

  .footer-copyright p {
    font-size: 0.8rem;
  }
}
</style>