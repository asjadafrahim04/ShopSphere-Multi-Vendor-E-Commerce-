import axios from 'axios'

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

const api = axios.create({
    baseURL: API_URL,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
})

// ===== REQUEST INTERCEPTOR =====
api.interceptors.request.use(
    config => {
        const token = localStorage.getItem('token')
        if (token) {
            config.headers.Authorization = `Bearer ${token}`
        }
        return config
    },
    error => {
        return Promise.reject(error)
    }
)

// ===== RESPONSE INTERCEPTOR =====
api.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 401) {
            localStorage.removeItem('token')
            localStorage.removeItem('user')
            localStorage.removeItem('shopsphere_cart')
            localStorage.removeItem('shopsphere_wishlist')
            window.dispatchEvent(new CustomEvent('auth-changed'))
            if (!window.location.pathname.includes('/login') && 
                !window.location.pathname.includes('/register')) {
                window.location.href = '/login'
            }
        }
        return Promise.reject(error)
    }
)

// ============================================================
// ===== AUTH API METHODS =====
// ============================================================
export const authApi = {
    register: (userData) => api.post('/register', userData),
    login: (credentials) => api.post('/login', credentials),
    firebaseLogin: (firebaseData) => api.post('/firebase-login', firebaseData),
    logout: () => api.post('/logout'),
    getUser: () => api.get('/user'),
    updateProfile: (data) => api.put('/profile', data),
    changePassword: (data) => api.post('/change-password', data),
    forgotPassword: (email) => api.post('/forgot-password', { email }),
    resetPassword: (data) => api.post('/reset-password', data),
}

// ============================================================
// ===== PRODUCT API METHODS =====
// ============================================================
export const productApi = {
    getProducts: (params = {}) => api.get('/products', { params }),
    getFeatured: () => api.get('/products/featured'),
    getProduct: (id) => api.get(`/products/${id}`),
    searchProducts: (query) => api.get('/products/search', { params: { q: query } }),
    getProductsByCategory: (categoryId) => api.get(`/products/category/${categoryId}`),
}

// ============================================================
// ===== CATEGORY API METHODS =====
// ============================================================
export const categoryApi = {
    getCategories: () => api.get('/categories'),
    getCategory: (id) => api.get(`/categories/${id}`),
    getCategoryProducts: (id) => api.get(`/categories/${id}/products`),
}

// ============================================================
// ===== CART API METHODS =====
// ============================================================
export const cartApi = {
    getCart: () => api.get('/cart'),
    addToCart: (productId, quantity = 1) => 
        api.post('/cart/add', { product_id: productId, quantity }),
    updateCartItem: (itemId, quantity) => 
        api.put(`/cart/update/${itemId}`, { quantity }),
    removeCartItem: (itemId) => 
        api.delete(`/cart/remove/${itemId}`),
    clearCart: () => 
        api.delete('/cart/clear'),
    getCartTotal: () => 
        api.get('/cart/total'),
}

// ============================================================
// ===== WISHLIST API METHODS =====
// ============================================================
export const wishlistApi = {
    getWishlist: () => api.get('/wishlist'),
    addToWishlist: (productId) => 
        api.post(`/wishlist/add/${productId}`),  // 
    removeFromWishlist: (productId) => 
        api.delete(`/wishlist/remove/${productId}`),
    checkInWishlist: (productId) => 
        api.get(`/wishlist/check/${productId}`),
}

// ============================================================
// ===== ORDER API METHODS =====
// ============================================================
export const orderApi = {
    getOrders: () => api.get('/orders'),
    getOrder: (id) => api.get(`/orders/${id}`),
    createOrder: (orderData) => api.post('/orders', orderData),
    cancelOrder: (id) => api.put(`/orders/${id}/cancel`),
    trackOrder: (id) => api.get(`/orders/track/${id}`),
}

// ============================================================
// ===== REVIEW API METHODS =====
// ============================================================
export const reviewApi = {
    getProductReviews: (productId) => api.get(`/products/${productId}/reviews`),
    createReview: (data) => api.post('/reviews', data),
    updateReview: (id, data) => api.put(`/reviews/${id}`, data),
    deleteReview: (id) => api.delete(`/reviews/${id}`),
}

// ============================================================
// ===== VENDOR API METHODS =====
// ============================================================
export const vendorApi = {
    // Public Vendor Routes
    getVendors: () => api.get('/vendors'),
    getVendor: (id) => api.get(`/vendors/${id}`),
    getVendorProducts: (id) => api.get(`/vendors/${id}/products`),
    
    // Protected Vendor Routes (Requires Vendor Role)
    getDashboard: () => api.get('/vendor/dashboard'),
    getSalesReport: () => api.get('/vendor/sales-report'),
    
    // Product Management
    getVendorProductsList: () => api.get('/vendor/products'),
    createProduct: (productData) => api.post('/vendor/products', productData),
    updateProduct: (id, productData) => api.put(`/vendor/products/${id}`, productData),
    deleteProduct: (id) => api.delete(`/vendor/products/${id}`),
    
    // Order Management
    getVendorOrders: () => api.get('/vendor/orders'),
    updateOrderStatus: (id, status) => 
        api.put(`/vendor/orders/${id}/status`, { status }),
    
    // Coupon Management
    getCoupons: () => api.get('/vendor/coupons'),
    createCoupon: (data) => api.post('/vendor/coupons', data),
    updateCoupon: (id, data) => api.put(`/vendor/coupons/${id}`, data),
    deleteCoupon: (id) => api.delete(`/vendor/coupons/${id}`),
}

// ============================================================
// ===== ADMIN API METHODS =====
// ============================================================
export const adminApi = {
    // Dashboard & Analytics
    getDashboard: () => api.get('/admin/dashboard'),
    getAnalytics: () => api.get('/admin/analytics'),
    
    // User Management
    getUsers: () => api.get('/admin/users'),
    updateUserRole: (id, role) => api.put(`/admin/users/${id}/role`, { role }),
    updateUserStatus: (id, status) => api.put(`/admin/users/${id}/status`, { status }),
    deleteUser: (id) => api.delete(`/admin/users/${id}`),
    
    // Vendor Management
    getVendors: () => api.get('/admin/vendors'),
    getPendingVendors: () => api.get('/admin/vendors/pending'),
    approveVendor: (id) => api.put(`/admin/vendors/${id}/approve`),
    suspendVendor: (id) => api.put(`/admin/vendors/${id}/suspend`),
    
    // Order Management
    getOrders: () => api.get('/admin/orders'),
    updateOrderStatus: (id, status) => 
        api.put(`/admin/orders/${id}/status`, { status }),
    
    // Category Management
    createCategory: (data) => api.post('/admin/categories', data),
    updateCategory: (id, data) => api.put(`/admin/categories/${id}`, data),
    deleteCategory: (id) => api.delete(`/admin/categories/${id}`),
    
    // Payment Management
    getPayments: () => api.get('/admin/payments'),
    getPaymentDetails: (id) => api.get(`/admin/payments/${id}`),
}

export default api