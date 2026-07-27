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
        api.post(`/wishlist/add/${productId}`),
    removeFromWishlist: (productId) => 
        api.delete(`/wishlist/remove/${productId}`),
    checkInWishlist: (productId) => 
        api.get(`/wishlist/check/${productId}`),
}

export default api