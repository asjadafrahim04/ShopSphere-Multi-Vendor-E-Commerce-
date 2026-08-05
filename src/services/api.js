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
    getCartCount: () =>
        api.get('/cart/count'),
}

// ============================================================
// ===== WISHLIST API METHODS =====
// ============================================================
export const wishlistApi = {
    getWishlist: () => api.get('/wishlist'),
    addToWishlist: (productId) => 
        api.post(`/wishlist/add/${productId}`),
    removeFromWishlist: (productId) => 
        api.delete(`/wishlist/remove/${productId}`),
    checkInWishlist: (productId) => 
        api.get(`/wishlist/check/${productId}`),
    getWishlistCount: () =>
        api.get('/wishlist/count'),
    clearWishlist: () =>
        api.delete('/wishlist/clear'),
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
    getOrderInvoice: (id) => api.get(`/orders/invoice/${id}`),
    returnOrder: (id, data) => api.post(`/orders/${id}/return`, data),
}

// ============================================================
// ===== REVIEW API METHODS =====
// ============================================================
export const reviewApi = {
    getProductReviews: (productId) => api.get(`/products/${productId}/reviews`),
    createReview: (data) => api.post('/reviews', data),
    updateReview: (id, data) => api.put(`/reviews/${id}`, data),
    deleteReview: (id) => api.delete(`/reviews/${id}`),
    markHelpful: (id) => api.post(`/reviews/${id}/helpful`),
    reportReview: (id) => api.post(`/reviews/${id}/report`),
}

// ============================================================
// ===== PAYMENT API METHODS =====
// ============================================================
export const paymentApi = {
    createPayment: (data) => api.post('/payments/create', data),
    verifyPayment: (data) => api.post('/payments/verify', data),
    getPaymentMethods: () => api.get('/payments/methods'),
    getPaymentHistory: () => api.get('/payments/history'),
    refundPayment: (id, data) => api.post(`/payments/refund/${id}`, data),
}

// ============================================================
// ===== SHIPPING API METHODS =====
// ============================================================
export const shippingApi = {
    getShippingMethods: () => api.get('/shipping/methods'),
    calculateShipping: (data) => api.post('/shipping/calculate', data),
    trackShipment: (trackingNumber) => api.get(`/shipping/track/${trackingNumber}`),
}

// ============================================================
// ===== COUPON API METHODS =====
// ============================================================
export const couponApi = {
    validateCoupon: (code) => api.get('/coupons/validate', { params: { code } }),
    getCoupons: () => api.get('/coupons'),
    getCoupon: (code) => api.get(`/coupons/${code}`),
    applyCoupon: (code) => api.post('/coupons/apply', { code }),
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
    getStats: () => api.get('/vendor/stats'),
    getSalesReport: (params = {}) => api.get('/vendor/sales-report', { params }),
    getOrderAnalytics: (params = {}) => api.get('/vendor/order-analytics', { params }),
    
    // Product Management
    getVendorProductsList: (params = {}) => api.get('/vendor/products', { params }),
    createProduct: (productData) => {
        if (productData instanceof FormData) {
            return api.post('/vendor/products', productData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            })
        }
        return api.post('/vendor/products', productData)
    },
    updateProduct: (id, productData) => {
        if (productData instanceof FormData) {
            productData.append('_method', 'PUT')
            return api.post(`/vendor/products/${id}`, productData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            })
        }
        return api.put(`/vendor/products/${id}`, productData)
    },
    deleteProduct: (id) => api.delete(`/vendor/products/${id}`),
    duplicateProduct: (id) => api.post(`/vendor/products/${id}/duplicate`),
    toggleProductStatus: (id) => api.put(`/vendor/products/${id}/toggle-status`),
    getLowStockProducts: (params = {}) => api.get('/vendor/low-stock', { params }),
    updateProductStock: (id, quantity) => 
        api.put(`/vendor/products/${id}/stock`, { stock_quantity: quantity }),
    deleteProductImage: (imageId) => api.delete(`/vendor/products/image/${imageId}`),
    
    // Order Management
    getVendorOrders: (params = {}) => api.get('/vendor/orders', { params }),
    getVendorOrderDetails: (id) => api.get(`/vendor/orders/${id}`),
    updateOrderStatus: (id, status) => 
        api.put(`/vendor/orders/${id}/status`, { status }),
    acceptOrder: (id) => api.put(`/vendor/orders/${id}/accept`),
    rejectOrder: (id) => api.put(`/vendor/orders/${id}/reject`),
    
    // Coupon Management
    getVendorCoupons: () => api.get('/vendor/coupons'),
    createVendorCoupon: (data) => api.post('/vendor/coupons', data),
    updateVendorCoupon: (id, data) => api.put(`/vendor/coupons/${id}`, data),
    deleteVendorCoupon: (id) => api.delete(`/vendor/coupons/${id}`),
    toggleVendorCoupon: (id) => api.put(`/vendor/coupons/${id}/toggle`),
    
    // Payouts
    getPayouts: () => api.get('/vendor/payouts'),
    requestPayout: (data) => api.post('/vendor/payouts/request', data),
    getPayoutHistory: () => api.get('/vendor/payouts/history'),
    
    // Analytics
    getProductAnalytics: () => api.get('/vendor/analytics/products'),
    getCustomerAnalytics: () => api.get('/vendor/analytics/customers'),
    getRevenueAnalytics: () => api.get('/vendor/analytics/revenue'),
    
    // Profile
    getVendorProfile: () => api.get('/vendor/profile'),
    updateVendorProfile: (data) => api.put('/vendor/profile', data),
    uploadVendorLogo: (logo) => {
        const formData = new FormData()
        formData.append('logo', logo)
        return api.post('/vendor/profile/logo', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        })
    },
}

// ============================================================
// ===== ADMIN API METHODS =====
// ============================================================
export const adminApi = {
    // Dashboard & Analytics
    getDashboard: () => api.get('/admin/dashboard'),
    getAnalytics: () => api.get('/admin/analytics'),
    getOverview: () => api.get('/admin/overview'),
    
    // User Management
    getUsers: (params = {}) => api.get('/admin/users', { params }),
    getUserDetails: (id) => api.get(`/admin/users/${id}`),
    createUser: (data) => api.post('/admin/users', data),
    updateUser: (id, data) => api.put(`/admin/users/${id}`, data),
    updateUserRole: (id, role) => api.put(`/admin/users/${id}/role`, { role }),
    updateUserStatus: (id, status) => api.put(`/admin/users/${id}/status`, { status }),
    deleteUser: (id) => api.delete(`/admin/users/${id}`),
    
    // Vendor Management
    getVendors: (params = {}) => api.get('/admin/vendors', { params }),
    getVendorDetails: (id) => api.get(`/admin/vendors/${id}`),
    getPendingVendors: () => api.get('/admin/vendors/pending'),
    approveVendor: (id) => api.put(`/admin/vendors/${id}/approve`),
    suspendVendor: (id) => api.put(`/admin/vendors/${id}/suspend`),
    updateVendorCommission: (id, rate) => 
        api.put(`/admin/vendors/${id}/commission`, { commission_rate: rate }),
    deleteVendor: (id) => api.delete(`/admin/vendors/${id}`),
    getVendorAnalytics: (id) => api.get(`/admin/vendors/${id}/analytics`),
    
    // Product Management (Admin)
    getProducts: (params = {}) => api.get('/admin/products', { params }),
    getProductDetails: (id) => api.get(`/admin/products/${id}`),
    updateProductStatus: (id, status) => 
        api.put(`/admin/products/${id}/status`, { status }),
    featureProduct: (id, featured) => 
        api.put(`/admin/products/${id}/feature`, { is_featured: featured }),
    deleteProduct: (id) => api.delete(`/admin/products/${id}`),
    getPendingProducts: () => api.get('/admin/products/pending'),
    
    // Order Management (Admin)
    getOrders: (params = {}) => api.get('/admin/orders', { params }),
    getOrderDetails: (id) => api.get(`/admin/orders/${id}`),
    updateOrderStatus: (id, status) => 
        api.put(`/admin/orders/${id}/status`, { status }),
    deleteOrder: (id) => api.delete(`/admin/orders/${id}`),
    getOrderAnalytics: () => api.get('/admin/orders/analytics'),
    
    // Category Management (Admin)
    createCategory: (data) => api.post('/admin/categories', data),
    updateCategory: (id, data) => api.put(`/admin/categories/${id}`, data),
    deleteCategory: (id) => api.delete(`/admin/categories/${id}`),
    reorderCategory: (id, order) => 
        api.put(`/admin/categories/${id}/reorder`, { order }),
    uploadCategoryImage: (id, image) => {
        const formData = new FormData()
        formData.append('image', image)
        return api.post(`/admin/categories/${id}/image`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        })
    },
    
    // Payment Management
    getPayments: (params = {}) => api.get('/admin/payments', { params }),
    getPaymentDetails: (id) => api.get(`/admin/payments/${id}`),
    updatePaymentStatus: (id, status) => 
        api.put(`/admin/payments/${id}/status`, { status }),
    refundPayment: (id, data) => api.post(`/admin/payments/${id}/refund`, data),
    getPaymentAnalytics: () => api.get('/admin/payments/analytics'),
    
    // Coupon Management (Admin)
    getCoupons: (params = {}) => api.get('/admin/coupons', { params }),
    createCoupon: (data) => api.post('/admin/coupons', data),
    updateCoupon: (id, data) => api.put(`/admin/coupons/${id}`, data),
    deleteCoupon: (id) => api.delete(`/admin/coupons/${id}`),
    toggleCoupon: (id) => api.put(`/admin/coupons/${id}/toggle`),
    
    // Settings
    getSettings: () => api.get('/admin/settings'),
    updateSettings: (data) => api.put('/admin/settings', data),
    updatePaymentSettings: (data) => api.put('/admin/settings/payment', data),
    updateShippingSettings: (data) => api.put('/admin/settings/shipping', data),
    updateEmailSettings: (data) => api.put('/admin/settings/email', data),
    
    // Reports
    getSalesReport: (params = {}) => api.get('/admin/reports/sales', { params }),
    getRevenueReport: (params = {}) => api.get('/admin/reports/revenue', { params }),
    getProductReport: (params = {}) => api.get('/admin/reports/products', { params }),
    getUserReport: (params = {}) => api.get('/admin/reports/users', { params }),
    exportReport: (params = {}) => api.get('/admin/reports/export', { params }),
}

export default api