// Payment Functions

let shippingData = null;

// Initialize shipping form
document.addEventListener('DOMContentLoaded', () => {
    const shippingForm = document.getElementById('shippingForm');
    if (shippingForm) {
        shippingForm.addEventListener('submit', (e) => {
            e.preventDefault();
            handleShippingSubmit();
        });
    }
});

// Handle shipping form submit
function handleShippingSubmit() {
    const shippingInfo = {
        name: document.getElementById('shippingName').value,
        phone: document.getElementById('shippingPhone').value,
        address: document.getElementById('shippingAddress').value,
        province: document.getElementById('shippingProvince').value,
        postcode: document.getElementById('shippingPostcode').value
    };
    
    // เก็บข้อมูลการจัดส่ง
    shippingData = shippingInfo;
    
    // ไปหน้าชำระเงิน
    showPage('payment');
    loadPayment();
}

// Load payment page
async function loadPayment() {
    const container = document.getElementById('paymentContent');
    if (!container) return;
    
    const user = getUser();
    if (!user || !user.id) {
        container.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><h3>กรุณาเข้าสู่ระบบก่อน</h3></div>';
        return;
    }
    
    container.innerHTML = '<div class="loading">กำลังโหลด...</div>';
    
    try {
        // ดึงข้อมูลตะกร้า
        const response = await fetch(getApiUrl('cart') + `?action=get&user_id=${user.id}`, {
            headers: {
                'Authorization': `Bearer ${getToken()}`
            }
        });
        
        const data = await response.json();
        
        if (!data.success || !data.cart || data.cart.length === 0) {
            container.innerHTML = '<div class="empty-state"><i class="fas fa-shopping-cart"></i><h3>ตะกร้าสินค้าว่าง</h3><button class="btn btn-primary" onclick="showPage(\'products\')">เลือกสินค้า</button></div>';
            return;
        }
        
        // คำนวณยอดรวม
        let subtotal = 0;
        data.cart.forEach(item => {
            subtotal += (item.price || 0) * (item.quantity || 1);
        });
        
        const shippingFee = 50; // ค่าจัดส่ง
        const total = subtotal + shippingFee;
        
        // แสดงข้อมูลการชำระเงิน
        let html = `
            <div class="payment-container">
                <div class="payment-section">
                    <h2><i class="fas fa-shopping-cart"></i> สรุปคำสั่งซื้อ</h2>
                    <div class="order-items">
        `;
        
        data.cart.forEach(item => {
            const itemTotal = (item.price || 0) * (item.quantity || 1);
            html += `
                <div class="order-item">
                    <img src="${item.image_url || 'assets/images/placeholder.svg'}" alt="${item.name}" onerror="this.onerror=null; this.src='assets/images/placeholder.svg';">
                    <div class="order-item-info">
                        <h4>${item.name || 'สินค้า'}</h4>
                        <p>จำนวน: ${item.quantity || 1} ชิ้น</p>
                    </div>
                    <div class="order-item-price">${formatPrice(itemTotal)}</div>
                </div>
            `;
        });
        
        html += `
                    </div>
                    <div class="order-summary">
                        <div class="summary-row">
                            <span>ยอดรวมสินค้า</span>
                            <span>${formatPrice(subtotal)}</span>
                        </div>
                        <div class="summary-row">
                            <span>ค่าจัดส่ง</span>
                            <span>${formatPrice(shippingFee)}</span>
                        </div>
                        <div class="summary-row total">
                            <span>รวมทั้งสิ้น</span>
                            <span>${formatPrice(total)}</span>
                        </div>
                    </div>
                </div>
                
                <div class="payment-section">
                    <h2><i class="fas fa-truck"></i> ข้อมูลการจัดส่ง</h2>
                    <div class="shipping-info">
        `;
        
        if (shippingData) {
            html += `
                <p><strong>ชื่อ:</strong> ${shippingData.name}</p>
                <p><strong>โทรศัพท์:</strong> ${shippingData.phone}</p>
                <p><strong>ที่อยู่:</strong> ${shippingData.address}</p>
                <p><strong>จังหวัด:</strong> ${shippingData.province}</p>
                <p><strong>รหัสไปรษณีย์:</strong> ${shippingData.postcode}</p>
            `;
        } else {
            html += '<p class="error">ไม่พบข้อมูลการจัดส่ง กรุณากลับไปกรอกข้อมูลใหม่</p>';
        }
        
        html += `
                    </div>
                    <button class="btn btn-secondary" onclick="showPage('shipping')">
                        <i class="fas fa-edit"></i> แก้ไขที่อยู่
                    </button>
                </div>
                
                <div class="payment-section">
                    <h2><i class="fas fa-credit-card"></i> วิธีการชำระเงิน</h2>
                    <div class="payment-methods">
                        <label class="payment-method">
                            <input type="radio" name="paymentMethod" value="cod" checked>
                            <div class="payment-method-content">
                                <i class="fas fa-money-bill-wave"></i>
                                <span>เก็บเงินปลายทาง (COD)</span>
                            </div>
                        </label>
                        <label class="payment-method">
                            <input type="radio" name="paymentMethod" value="bank">
                            <div class="payment-method-content">
                                <i class="fas fa-university"></i>
                                <span>โอนเงินผ่านธนาคาร</span>
                            </div>
                        </label>
                    </div>
                </div>
                
                <div class="payment-actions">
                    <button class="btn btn-secondary" onclick="showPage('cart')">
                        <i class="fas fa-arrow-left"></i> กลับ
                    </button>
                    <button class="btn btn-primary btn-large" onclick="confirmPayment()">
                        <i class="fas fa-check"></i> ยืนยันการสั่งซื้อ
                    </button>
                </div>
            </div>
        `;
        
        container.innerHTML = html;
    } catch (error) {
        container.innerHTML = `<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><h3>เกิดข้อผิดพลาด: ${error.message}</h3></div>`;
    }
}

// Confirm payment
async function confirmPayment() {
    if (!shippingData) {
        showToast('กรุณากรอกข้อมูลการจัดส่งก่อน', 'error');
        showPage('shipping');
        return;
    }
    
    const user = getUser();
    if (!user || !user.id) {
        showToast('กรุณาเข้าสู่ระบบก่อน', 'error');
        return;
    }
    
    const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked')?.value || 'cod';
    
    if (!confirm('ยืนยันการสั่งซื้อ?')) {
        return;
    }
    
    try {
        // Get cart items
        const cartResponse = await fetch(getApiUrl('cart') + `?action=get&user_id=${user.id}`, {
            headers: {
                'Authorization': `Bearer ${getToken()}`
            }
        });
        
        const cartData = await cartResponse.json();
        
        if (!cartData.success || !cartData.cart || cartData.cart.length === 0) {
            showToast('ตะกร้าสินค้าว่าง', 'error');
            showPage('cart');
            return;
        }
        
        // Prepare order items
        const orderItems = cartData.cart.map(item => {
            // ตรวจสอบ product_id จากหลายที่
            const productId = item.product_id || item.productId || item.id;
            if (!productId) {
                console.error('Missing product_id in cart item:', item);
            }
            return {
                product_id: productId,
                name: item.name,
                price: parseFloat(item.price || 0),
                quantity: parseInt(item.quantity || 1)
            };
        });
        
        // Create order
        const orderData = {
            items: orderItems,
            shipping_name: shippingData.name,
            shipping_phone: shippingData.phone,
            shipping_address: shippingData.address,
            shipping_province: shippingData.province,
            shipping_postcode: shippingData.postcode,
            payment_method: paymentMethod,
            shipping_fee: 50.00
        };
        
        const orderResponse = await apiRequest(getApiUrl('orders'), {
            method: 'POST',
            body: JSON.stringify(orderData)
        });
        
        if (orderResponse.success) {
            showToast('สั่งซื้อสำเร็จ! เลขที่คำสั่งซื้อ: ' + orderResponse.order_number, 'success');
            
            // Clear shipping data
            shippingData = null;
            
            // กลับไปหน้าแรก
            setTimeout(() => {
                showPage('home');
                if (typeof updateCartBadge === 'function') {
                    updateCartBadge();
                }
            }, 2000);
        } else {
            throw new Error(orderResponse.message || 'ไม่สามารถสร้างคำสั่งซื้อได้');
        }
        
    } catch (error) {
        showToast('เกิดข้อผิดพลาด: ' + error.message, 'error');
    }
}

// Global functions
window.loadPayment = loadPayment;
window.confirmPayment = confirmPayment;

