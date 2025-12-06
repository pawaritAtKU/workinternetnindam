// Product Detail Functions

let currentProductId = null;

// Load product detail
async function loadProductDetail(productId) {
    const container = document.getElementById('productDetailContent');
    if (!container) return;
    
    currentProductId = productId;
    container.innerHTML = '<div class="loading">กำลังโหลด...</div>';
    
    try {
        const response = await apiRequest(getApiUrl('product') + `?id=${productId}`);
        
        if (response.success && response.data) {
            displayProductDetail(response.data);
        } else {
            throw new Error('ไม่พบสินค้า');
        }
    } catch (error) {
        container.innerHTML = `<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><h3>เกิดข้อผิดพลาด: ${error.message}</h3></div>`;
    }
}

// Display product detail
function displayProductDetail(product) {
    const container = document.getElementById('productDetailContent');
    if (!container) return;
    
    const stockQuantity = product.stock_quantity || 0;
    const isOutOfStock = stockQuantity <= 0;
    const stockStatus = isOutOfStock ? 'สินค้าหมด' : `เหลือ ${stockQuantity} ชิ้น`;
    const stockClass = isOutOfStock ? 'out-of-stock' : 'in-stock';
    
    // Get category name
    const categoryName = getCategoryName(product.category_id || '');
    
    container.innerHTML = `
        <div class="product-detail-container">
            <div class="product-detail-grid">
                <div class="product-detail-image">
                    <img src="${product.image_url || 'assets/images/placeholder.svg'}" 
                         alt="${product.name}" 
                         onerror="this.onerror=null; this.src='assets/images/placeholder.svg';">
                </div>
                
                <div class="product-detail-info">
                    <h1 class="product-detail-title">${product.name || 'สินค้า'}</h1>
                    
                    <div class="product-detail-price">
                        <span class="price">${formatPrice(product.price || 0)}</span>
                    </div>
                    
                    <div class="product-detail-meta">
                        <div class="meta-item">
                            <i class="fas fa-th-large"></i>
                            <span><strong>หมวดหมู่:</strong> ${categoryName}</span>
                        </div>
                        <div class="meta-item ${stockClass}">
                            <i class="fas fa-box"></i>
                            <span><strong>สถานะ:</strong> ${stockStatus}</span>
                        </div>
                        ${product.store_id ? `
                        <div class="meta-item">
                            <i class="fas fa-store"></i>
                            <span><strong>ร้านค้า:</strong> ${product.store_name || 'ร้านค้า'}</span>
                        </div>
                        ` : ''}
                    </div>
                    
                    ${product.description ? `
                    <div class="product-detail-description">
                        <h3><i class="fas fa-info-circle"></i> รายละเอียดสินค้า</h3>
                        <p>${product.description}</p>
                    </div>
                    ` : ''}
                    
                    <div class="product-detail-actions">
                        ${!isOutOfStock ? `
                        <button class="btn btn-primary btn-large" onclick="addToCartFromDetail(${product.id}, '${product.name}', ${product.price}, '${product.image_url || ''}')">
                            <i class="fas fa-cart-plus"></i> เพิ่มลงตะกร้า
                        </button>
                        ` : `
                        <button class="btn btn-secondary btn-large" disabled>
                            <i class="fas fa-times-circle"></i> สินค้าหมด
                        </button>
                        `}
                    </div>
                </div>
            </div>
        </div>
    `;
}

// Get category name
function getCategoryName(categoryId) {
    const categories = {
        'electronics': 'อิเล็กทรอนิกส์',
        'clothing': 'เสื้อผ้า',
        'books': 'หนังสือ',
        'home': 'ของใช้ในบ้าน',
        'sports': 'กีฬา',
        'general': 'ของใช้ทั่วไป'
    };
    return categories[categoryId] || categoryId || 'ไม่ระบุ';
}

// Add to cart from detail page
async function addToCartFromDetail(productId, name, price, imageUrl) {
    const user = getUser();
    
    if (!user || !user.id) {
        showToast('กรุณาเข้าสู่ระบบก่อน', 'error');
        showPage('login');
        return;
    }
    
    try {
        const formData = new URLSearchParams();
        formData.append('action', 'add');
        formData.append('user_id', user.id);
        formData.append('product_id', productId);
        formData.append('name', name);
        formData.append('price', price);
        formData.append('image_url', imageUrl || '');
        formData.append('qty', 1);
        
        const response = await fetch(getApiUrl('cart') + '?action=add', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${getToken()}`,
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: formData.toString()
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('เพิ่มสินค้าลงตะกร้าสำเร็จ', 'success');
            if (typeof updateCartBadge === 'function') {
                updateCartBadge();
            }
        } else {
            throw new Error(data.error || 'ไม่สามารถเพิ่มสินค้าได้');
        }
    } catch (error) {
        showToast(error.message, 'error');
    }
}

// Global functions
window.loadProductDetail = loadProductDetail;
window.addToCartFromDetail = addToCartFromDetail;

