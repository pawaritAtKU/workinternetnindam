// Categories Functions

// Show products by category
async function showCategoryProducts(categoryId) {
    showPage('products');
    
    const container = document.getElementById('productsGrid');
    if (!container) return;
    
    container.innerHTML = '<div class="loading">กำลังโหลด...</div>';
    
    try {
        // โหลดสินค้าทั้งหมดก่อน
        let products = [];
        try {
            products = await apiRequest(getApiUrl('products'));
        } catch (error) {
            console.error('Error loading products:', error);
            throw error;
        }
        
        // กรองสินค้าตามหมวดหมู่
        const filteredProducts = products.filter(product => {
            const productCategory = (product.category_id || product.category || '').toLowerCase();
            return productCategory === categoryId.toLowerCase();
        });
        
        // อัปเดต allProducts สำหรับ search
        if (typeof window.allProducts !== 'undefined') {
            window.allProducts = products;
        }
        
        // แสดงสินค้าที่กรองแล้ว
        if (typeof displayProducts === 'function') {
            displayProducts(filteredProducts);
        } else {
            // Fallback: แสดงสินค้าทั้งหมด
            container.innerHTML = '';
            filteredProducts.forEach(product => {
                if (typeof createProductCard === 'function') {
                    container.appendChild(createProductCard(product));
                }
            });
        }
        
        // Update search box
        const searchBox = document.getElementById('productSearch');
        if (searchBox) {
            searchBox.value = '';
        }
        
        showToast(`แสดงสินค้าหมวดหมู่: ${getCategoryName(categoryId)}`, 'success');
    } catch (error) {
        container.innerHTML = `<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><h3>เกิดข้อผิดพลาด: ${error.message}</h3></div>`;
    }
}

// Get category name in Thai
function getCategoryName(categoryId) {
    const categories = {
        'electronics': 'อิเล็กทรอนิกส์',
        'clothing': 'เสื้อผ้า',
        'books': 'หนังสือ',
        'home': 'ของใช้ในบ้าน',
        'sports': 'กีฬา',
        'general': 'ของใช้ทั่วไป'
    };
    return categories[categoryId] || categoryId;
}

// Global function
window.showCategoryProducts = showCategoryProducts;

