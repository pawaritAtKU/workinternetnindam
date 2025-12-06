// Products Functions

// Make allProducts global
window.allProducts = [];
let allProducts = window.allProducts;

// Load products
async function loadProducts() {
    const container = document.getElementById('productsGrid');
    if (!container) return;
    
    container.innerHTML = '<div class="loading">กำลังโหลด...</div>';
    
    try {
        allProducts = await apiRequest(getApiUrl('products'));
        window.allProducts = allProducts; // Update global
        
        displayProducts(allProducts);
    } catch (error) {
        container.innerHTML = `<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><h3>เกิดข้อผิดพลาด: ${error.message}</h3></div>`;
    }
}

// Display products
function displayProducts(products) {
    const container = document.getElementById('productsGrid');
    if (!container) return;
    
    container.innerHTML = '';
    
    if (products.length === 0) {
        container.innerHTML = '<div class="empty-state"><i class="fas fa-box-open"></i><h3>ไม่พบสินค้า</h3></div>';
        return;
    }
    
    products.forEach(product => {
        if (typeof createProductCard === 'function') {
            container.appendChild(createProductCard(product));
        } else if (typeof window.createProductCard === 'function') {
            container.appendChild(window.createProductCard(product));
        }
    });
}

// Make displayProducts global
window.displayProducts = displayProducts;

// Search products
document.addEventListener('DOMContentLoaded', () => {
    const productSearch = document.getElementById('productSearch');
    const heroSearch = document.getElementById('heroSearch');
    
    if (productSearch) {
        productSearch.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase();
            const filtered = allProducts.filter(product => 
                product.name.toLowerCase().includes(query) ||
                (product.description && product.description.toLowerCase().includes(query))
            );
            displayProducts(filtered);
        });
    }
    
    if (heroSearch) {
        // Handle Enter key
        heroSearch.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                performSearch(heroSearch.value);
            }
        });
        
        // Handle search button click
        const heroSearchBtn = heroSearch.nextElementSibling || heroSearch.parentElement?.querySelector('.search-btn');
        if (heroSearchBtn) {
            heroSearchBtn.addEventListener('click', () => {
                performSearch(heroSearch.value);
            });
        }
    }
});

// Perform search function
function performSearch(query) {
    if (!query || !query.trim()) {
        showPage('products');
        return;
    }
    
    showPage('products');
    
    // Wait for products page to load
    setTimeout(() => {
        const productSearch = document.getElementById('productSearch');
        if (productSearch) {
            productSearch.value = query;
            productSearch.dispatchEvent(new Event('input'));
        }
    }, 100);
}

// Add to cart function (global)
async function addToCart(productId, name, price, imageUrl) {
    const user = getUser();
    
    if (!user || !user.id) {
        showToast('กรุณาเข้าสู่ระบบก่อน', 'error');
        showPage('login');
        return;
    }
    
    try {
        console.log('Adding to cart:', { productId, name, price, imageUrl, userId: user.id });
        
        const formData = new URLSearchParams();
        formData.append('action', 'add');
        formData.append('user_id', user.id);
        formData.append('product_id', productId);
        formData.append('name', name);
        formData.append('price', price);
        formData.append('image_url', imageUrl || '');
        formData.append('qty', 1);
        
        const url = getApiUrl('cart') + '?action=add';
        console.log('Sending to:', url);
        console.log('Form data:', formData.toString());
        
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${getToken()}`,
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: formData.toString()
        });
        
        console.log('Response status:', response.status);
        const responseText = await response.text();
        console.log('Response text:', responseText);
        
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (parseError) {
            console.error('JSON parse error:', parseError);
            throw new Error('Invalid response from server: ' + responseText.substring(0, 100));
        }
        
        console.log('Response data:', data);
        
        if (data.success) {
            showToast('เพิ่มสินค้าลงตะกร้าสำเร็จ', 'success');
            updateCartBadge();
            // Reload cart if on cart page
            if (typeof loadCart === 'function') {
                setTimeout(() => loadCart(), 500);
            }
        } else {
            throw new Error(data.error || 'ไม่สามารถเพิ่มสินค้าได้');
        }
    } catch (error) {
        console.error('Add to cart error:', error);
        showToast(error.message, 'error');
    }
}

// Update cart badge
async function updateCartBadge() {
    const user = getUser();
    if (!user || !user.id) return;
    
    try {
        const response = await fetch(getApiUrl('cart') + `?action=get&user_id=${user.id}`, {
            headers: {
                'Authorization': `Bearer ${getToken()}`
            }
        });
        
        const data = await response.json();
        
        if (data.success && data.cart) {
            const totalItems = data.cart.reduce((sum, item) => sum + (item.quantity || 1), 0);
            const badge = document.getElementById('cartBadge');
            if (badge) {
                badge.textContent = totalItems;
                badge.style.display = totalItems > 0 ? 'flex' : 'none';
            }
        }
    } catch (error) {
        console.error('Error updating cart badge:', error);
    }
}

// Initialize cart badge on load
document.addEventListener('DOMContentLoaded', () => {
    updateCartBadge();
});

