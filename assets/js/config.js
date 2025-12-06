// API Configuration
const API_CONFIG = {
    // baseUrl: 'http://nindam.sytes.net/std6530251065/workinternetnindam/api/',
    baseUrl: 'http://localhost/workinternetnindam/api/', // สำหรับ local development
    
    endpoints: {
        login: 'login.php',
        register: 'register.php',
        products: 'getProducts.php',
        getMyProducts: 'getMyProducts.php',
        product: 'product.php',
        cart: 'cartApi.php',
        profile: 'profile.php',
        search: 'search.php',
        createProduct: 'createProduct.php',
        updateProduct: 'updateProduct.php',
        deleteProduct: 'deleteProduct.php',
        stores: 'stores.php',
        orders: 'orders.php',
        categories: 'categories.php'
    }
};

// Get API URL
function getApiUrl(endpoint) {
    return API_CONFIG.baseUrl + API_CONFIG.endpoints[endpoint];
}

// Get stored token
function getToken() {
    return localStorage.getItem('token');
}

// Set token
function setToken(token) {
    localStorage.setItem('token', token);
}

// Remove token
function removeToken() {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
}

// Get user data
function getUser() {
    const userStr = localStorage.getItem('user');
    return userStr ? JSON.parse(userStr) : null;
}

// Set user data
function setUser(user) {
    localStorage.setItem('user', JSON.stringify(user));
}

// Remove user data
function removeUser() {
    localStorage.removeItem('user');
}

// Show toast notification
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.className = `toast ${type} show`;
    
    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}

// Format price
function formatPrice(price) {
    return new Intl.NumberFormat('th-TH', {
        style: 'currency',
        currency: 'THB'
    }).format(price);
}

// Make API request
async function apiRequest(url, options = {}) {
    const token = getToken();
    
    const defaultOptions = {
        headers: {
            'Content-Type': 'application/json',
        }
    };
    
    if (token) {
        defaultOptions.headers['Authorization'] = `Bearer ${token}`;
    }
    
    const finalOptions = {
        ...defaultOptions,
        ...options,
        headers: {
            ...defaultOptions.headers,
            ...(options.headers || {})
        }
    };
    
    try {
        // Log request for debugging
        console.log('Making API request:', {
            url: url,
            method: finalOptions.method || 'GET',
            hasBody: !!finalOptions.body
        });
        
        const response = await fetch(url, finalOptions);
        
        // Check for network errors
        if (!response) {
            throw new Error('ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้ กรุณาตรวจสอบการเชื่อมต่ออินเทอร์เน็ต');
        }
        
        // Get response text first to check if it's valid JSON
        const responseText = await response.text();
        
        // Check if response is empty
        if (!responseText || responseText.trim() === '') {
            throw new Error('Server returned empty response');
        }
        
        // Try to parse as JSON
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (jsonError) {
            // If it's not JSON, check if it's an HTML error page
            if (responseText.trim().startsWith('<!DOCTYPE') || responseText.trim().startsWith('<html')) {
                throw new Error('Server returned HTML error page. Please check server logs.');
            }
            // Otherwise, show the actual response text for debugging
            console.error('Invalid JSON response:', responseText.substring(0, 200));
            throw new Error('Server returned invalid JSON response. Please check server configuration.');
        }
        
        // If response is not OK (4xx, 5xx), throw error
        if (!response.ok) {
            throw new Error(data.message || data.error || 'เกิดข้อผิดพลาด');
        }
        
        // Return data (even if success is false, let the caller handle it)
        // This allows login/register to check response.success themselves
        return data;
    } catch (error) {
        console.error('API Error:', error);
        
        // Handle specific error types
        if (error.name === 'TypeError' && error.message.includes('Failed to fetch')) {
            // Check if it's ERR_BLOCKED_BY_CLIENT
            const isBlockedByClient = error.message.includes('ERR_BLOCKED_BY_CLIENT') || 
                                     error.message.includes('blocked') ||
                                     (typeof chrome !== 'undefined' && chrome.runtime);
            
            let errorMsg = 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้\n\n';
            
            if (isBlockedByClient) {
                errorMsg += '⚠️ ถูกบล็อกโดย Ad blocker หรือ Browser Extension\n\n';
                errorMsg += 'วิธีแก้ไข:\n';
                errorMsg += '1. ปิด Ad blocker (เช่น uBlock Origin, AdBlock Plus)\n';
                errorMsg += '2. เพิ่ม localhost เป็น exception ใน Ad blocker\n';
                errorMsg += '3. ลองใช้ Incognito/Private mode (Ctrl+Shift+N)\n';
                errorMsg += '4. ลองใช้ browser อื่น (Chrome, Firefox, Edge)\n';
                errorMsg += '5. ตรวจสอบว่า Laragon/Apache ทำงานอยู่\n';
                errorMsg += '6. เปิด http://localhost/workinternetnindam/api/register.php ใน browser\n';
            } else {
                errorMsg += 'กรุณาลอง:\n';
                errorMsg += '1. ตรวจสอบว่า Laragon/Apache ทำงานอยู่\n';
                errorMsg += '2. เปิด http://localhost/workinternetnindam/api/register.php ใน browser\n';
                errorMsg += '3. ปิด Ad blocker หรือ browser extension\n';
                errorMsg += '4. ลองใช้ Incognito/Private mode\n';
                errorMsg += '5. ตรวจสอบ Console (F12) สำหรับ error เพิ่มเติม';
            }
            
            throw new Error(errorMsg);
        }
        
        // If it's already an Error object, throw it
        if (error instanceof Error) {
            throw error;
        }
        
        // Otherwise wrap it
        throw new Error(error.message || 'เกิดข้อผิดพลาดในการเชื่อมต่อ');
    }
}

