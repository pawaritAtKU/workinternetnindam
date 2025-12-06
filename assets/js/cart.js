// Cart Functions

// Load cart
async function loadCart() {
    const container = document.getElementById('cartContent');
    if (!container) return;
    
    const user = getUser();
    
    if (!user || !user.id) {
        container.innerHTML = '<div class="empty-state"><i class="fas fa-shopping-cart"></i><h3>กรุณาเข้าสู่ระบบเพื่อดูตะกร้าสินค้า</h3><button class="btn btn-primary" onclick="showPage(\'login\')">เข้าสู่ระบบ</button></div>';
        return;
    }
    
    container.innerHTML = '<div class="loading">กำลังโหลด...</div>';
    
    try {
        // Ensure user_id is a number
        const userId = parseInt(user.id);
        if (isNaN(userId) || userId <= 0) {
            console.error('Invalid user ID:', user.id);
            container.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><h3>ข้อมูลผู้ใช้ไม่ถูกต้อง</h3></div>';
            return;
        }
        
        const url = getApiUrl('cart') + `?action=get&user_id=${userId}`;
        console.log('Loading cart from:', url);
        console.log('User ID:', userId, '(type:', typeof userId, ')');
        console.log('User object:', user);
        console.log('Token:', getToken() ? 'Present' : 'Missing');
        
        const response = await fetch(url, {
            headers: {
                'Authorization': `Bearer ${getToken()}`
            }
        });
        
        console.log('Response status:', response.status);
        console.log('Response ok:', response.ok);
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('Response error:', errorText);
            throw new Error(`HTTP ${response.status}: ${errorText}`);
        }
        
        const responseText = await response.text();
        console.log('Response text:', responseText);
        
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (parseError) {
            console.error('JSON parse error:', parseError);
            console.error('Response text:', responseText);
            throw new Error('Invalid JSON response from server');
        }
        
        console.log('Cart data:', data);
        console.log('Cart items count:', data.cart ? data.cart.length : 0);
        
        if (data.success && data.cart && Array.isArray(data.cart) && data.cart.length > 0) {
            console.log('Displaying cart with', data.cart.length, 'items');
            displayCart(data.cart);
        } else {
            console.log('Cart is empty or invalid:', {
                success: data.success,
                hasCart: !!data.cart,
                isArray: Array.isArray(data.cart),
                length: data.cart ? data.cart.length : 0
            });
            container.innerHTML = '<div class="empty-state"><i class="fas fa-shopping-cart"></i><h3>ตะกร้าสินค้าว่าง</h3></div>';
        }
    } catch (error) {
        console.error('Cart load error:', error);
        container.innerHTML = `<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><h3>เกิดข้อผิดพลาด: ${error.message}</h3><p style="font-size: 0.9rem; color: #666;">ตรวจสอบ Console (F12) สำหรับรายละเอียด</p></div>`;
    }
}

// Display cart
function displayCart(cartItems) {
    console.log('displayCart called with items:', cartItems);
    const container = document.getElementById('cartContent');
    if (!container) {
        console.error('cartContent container not found!');
        return;
    }
    
    if (!Array.isArray(cartItems)) {
        console.error('cartItems is not an array:', cartItems);
        container.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><h3>ข้อมูลตะกร้าไม่ถูกต้อง</h3></div>';
        return;
    }
    
    if (cartItems.length === 0) {
        console.log('Cart is empty');
        container.innerHTML = '<div class="empty-state"><i class="fas fa-shopping-cart"></i><h3>ตะกร้าสินค้าว่าง</h3></div>';
        return;
    }
    
    console.log('Rendering', cartItems.length, 'cart items');
    
    let html = '<div class="cart-content">';
    
    let total = 0;
    
    cartItems.forEach(item => {
        const itemTotal = (item.price || 0) * (item.quantity || 1);
        total += itemTotal;
        
        html += `
            <div class="cart-item" data-id="${item.id}">
                <img src="${item.image_url || 'assets/images/placeholder.svg'}" alt="${item.name}" class="cart-item-image" onerror="this.onerror=null; this.src='assets/images/placeholder.svg';">
                <div class="cart-item-info">
                    <h3 class="cart-item-name">${item.name || 'สินค้า'}</h3>
                    <div class="cart-item-price">${formatPrice(item.price || 0)}</div>
                </div>
                <div class="cart-item-actions">
                    <div class="quantity-control">
                        <button class="quantity-btn" onclick="updateCartQuantity(${item.id}, ${(item.quantity || 1) - 1})">
                            <i class="fas fa-minus"></i>
                        </button>
                        <span class="quantity-value">${item.quantity || 1}</span>
                        <button class="quantity-btn" onclick="updateCartQuantity(${item.id}, ${(item.quantity || 1) + 1})">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    <button class="btn btn-danger" onclick="removeCartItem(${item.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
    });
    
    html += `
        <div class="cart-summary">
            <div class="cart-total">
                <span>รวมทั้งหมด</span>
                <span>${formatPrice(total)}</span>
            </div>
            <button class="btn btn-primary btn-block" onclick="checkout()">
                <i class="fas fa-credit-card"></i> ชำระเงิน
            </button>
        </div>
    </div>`;
    
    container.innerHTML = html;
}

// Update cart quantity
async function updateCartQuantity(cartId, newQuantity) {
    if (newQuantity < 1) {
        removeCartItem(cartId);
        return;
    }
    
    const user = getUser();
    if (!user || !user.id) return;
    
    try {
        const formData = new URLSearchParams();
        formData.append('id', cartId);
        formData.append('quantity', newQuantity);
        
        const response = await fetch(getApiUrl('cart').replace('cartApi.php', 'updateCart.php'), {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${getToken()}`
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('อัปเดตจำนวนสำเร็จ', 'success');
            loadCart();
            updateCartBadge();
        } else {
            throw new Error(data.error || 'ไม่สามารถอัปเดตได้');
        }
    } catch (error) {
        showToast(error.message, 'error');
    }
}

// Remove cart item
async function removeCartItem(cartId) {
    const user = getUser();
    if (!user || !user.id) return;
    
    if (!confirm('คุณต้องการลบสินค้านี้ออกจากตะกร้าหรือไม่?')) {
        return;
    }
    
    try {
        const response = await fetch(getApiUrl('cart') + `?action=remove&id=${cartId}`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${getToken()}`
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('ลบสินค้าออกจากตะกร้าสำเร็จ', 'success');
            loadCart();
            updateCartBadge();
        } else {
            throw new Error(data.error || 'ไม่สามารถลบได้');
        }
    } catch (error) {
        showToast(error.message, 'error');
    }
}

// Checkout - ไปหน้าที่อยู่จัดส่งก่อน
function checkout() {
    const user = getUser();
    if (!user || !user.id) {
        showToast('กรุณาเข้าสู่ระบบก่อน', 'error');
        showPage('login');
        return;
    }
    
    // ไปหน้าที่อยู่จัดส่ง
    showPage('shipping');
}

// Global functions
window.loadCart = loadCart;
window.updateCartQuantity = updateCartQuantity;
window.removeCartItem = removeCartItem;
window.checkout = checkout;

