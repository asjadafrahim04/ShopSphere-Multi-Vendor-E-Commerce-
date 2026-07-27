import axios from 'axios'

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

const api = axios.create({
    baseURL: API_URL,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
})

// Add token to requests
api.interceptors.request.use(config => {
    const token = localStorage.getItem('token')
    if (token) {
        config.headers.Authorization = `Bearer ${token}`
    }
    return config
})

// Handle response errors
api.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 401) {
            localStorage.removeItem('token')
            localStorage.removeItem('user')
            window.dispatchEvent(new CustomEvent('auth-changed'))
            if (!window.location.pathname.includes('/login')) {
                window.location.href = '/login'
            }
        }
        return Promise.reject(error)
    }
)

// ===== CART API METHODS =====
export const cartApi = {
    // Get cart
    getCart: () => api.get('/cart'),
    
    // Add item to cart
    addToCart: (productId, quantity = 1) => 
        api.post('/cart/add', { product_id: productId, quantity }),
    
    // Update cart item quantity
    updateCartItem: (itemId, quantity) => 
        api.put(`/cart/update/${itemId}`, { quantity }),
    
    // Remove item from cart
    removeCartItem: (itemId) => 
        api.delete(`/cart/remove/${itemId}`),
    
    // Clear cart
    clearCart: () => 
        api.delete('/cart/clear'),
    
    // Get cart total
    getCartTotal: () => 
        api.get('/cart/total')
}

export default api