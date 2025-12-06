// Admin Functions

let editingProductId = null;

// Load admin page
async function loadAdmin() {
    const container = document.getElementById('adminContent');
    if (!container) return;
    
    container.innerHTML = '<div class="loading">กำลังโหลด...</div>';
    
    try {
        // ใช้ getMyProducts เพื่อดึงเฉพาะสินค้าของร้านตัวเอง (หรือทั้งหมดถ้าเป็น admin)
        const token = getToken();
        if (!token) {
            container.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><h3>กรุณาเข้าสู่ระบบ</h3></div>';
            return;
        }
        
        const response = await fetch(getApiUrl('getMyProducts'), {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });
        
        if (!response.ok) {
            throw new Error('ไม่สามารถโหลดข้อมูลสินค้าได้');
        }
        
        const products = await response.json();
        displayAdminProducts(products);
    } catch (error) {
        container.innerHTML = `<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><h3>เกิดข้อผิดพลาด: ${error.message}</h3></div>`;
    }
}

// Display admin products
function displayAdminProducts(products) {
    const container = document.getElementById('adminContent');
    if (!container) return;
    
    if (products.length === 0) {
        container.innerHTML = '<div class="empty-state"><i class="fas fa-box-open"></i><h3>ยังไม่มีสินค้า</h3></div>';
        return;
    }
    
    let html = '<div class="products-grid">';
    
    products.forEach(product => {
        html += `
            <div class="product-card">
                <img src="${product.image_url || 'assets/images/placeholder.svg'}" alt="${product.name}" class="product-image" onerror="this.onerror=null; this.src='assets/images/placeholder.svg';">
                <div class="product-info">
                    <h3 class="product-name">${product.name || 'สินค้า'}</h3>
                    <div class="product-price">${formatPrice(product.price || 0)}</div>
                    <div class="product-actions">
                        <button class="btn btn-primary" onclick="editProduct(${product.id})">
                            <i class="fas fa-edit"></i> แก้ไข
                        </button>
                        <button class="btn btn-danger" onclick="deleteProduct(${product.id})">
                            <i class="fas fa-trash"></i> ลบ
                        </button>
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
}

// Initialize admin forms
document.addEventListener('DOMContentLoaded', () => {
    const adminAddProduct = document.getElementById('adminAddProduct');
    const addProductBtn = document.getElementById('addProductBtn');
    const productForm = document.getElementById('productForm');
    const cancelProductForm = document.getElementById('cancelProductForm');
    const productImage = document.getElementById('productImage');
    
    // Open add product form
    if (adminAddProduct) {
        adminAddProduct.addEventListener('click', () => {
            editingProductId = null;
            openProductForm();
        });
    }
    
    if (addProductBtn) {
        addProductBtn.addEventListener('click', () => {
            editingProductId = null;
            openProductForm();
        });
    }
    
    // Cancel form
    if (cancelProductForm) {
        cancelProductForm.addEventListener('click', () => {
            closeModal('productFormModal');
            resetProductForm();
        });
    }
    
    // Image preview
    if (productImage) {
        productImage.addEventListener('change', (e) => {
            const file = e.target.files[0];
            const preview = document.getElementById('productImagePreview');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    preview.innerHTML = `<img src="${e.target.result}" style="max-width: 200px; margin-top: 1rem; border-radius: 8px;">`;
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Submit product form
    if (productForm) {
        productForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            await saveProduct();
        });
    }
});

// Default categories for fallback
const defaultCategoriesList = [
    { id: 'electronics', name: 'อิเล็กทรอนิกส์' },
    { id: 'clothing', name: 'เสื้อผ้า' },
    { id: 'books', name: 'หนังสือ' },
    { id: 'home', name: 'ของใช้ในบ้าน' },
    { id: 'sports', name: 'กีฬา' },
    { id: 'general', name: 'ของใช้ทั่วไป' }
];

// Populate categories directly (fallback function)
function populateCategoriesDirectly(selectId) {
    const select = document.getElementById(selectId);
    if (!select) {
        console.warn('Category select not found:', selectId);
        return false;
    }
    
    // Always populate - clear first then add options
    select.innerHTML = '<option value="">เลือกหมวดหมู่</option>';
    
    // Add all default categories
    defaultCategoriesList.forEach(category => {
        const option = document.createElement('option');
        option.value = category.id;
        option.textContent = category.name;
        select.appendChild(option);
    });
    
    // Force browser to update
    select.style.display = 'none';
    select.offsetHeight; // Trigger reflow
    select.style.display = '';
    
    return true;
}

// Open product form
function openProductForm() {
    const modal = document.getElementById('productFormModal');
    const formTitle = document.getElementById('formModalTitle');
    
    if (!modal) {
        console.error('Product form modal not found');
        return;
    }
    
    // Open modal first
    modal.classList.add('active');
    
    // Wait for modal to be visible, then populate categories
    setTimeout(() => {
        const select = document.getElementById('productCategory');
        if (!select) {
            console.error('Product category select not found');
            return;
        }
        
        // Always ensure categories are populated
        populateCategoriesDirectly('productCategory');
        
        // Set form title and reset
        if (editingProductId) {
            formTitle.textContent = 'แก้ไขสินค้า';
            loadProductForEdit(editingProductId);
        } else {
            formTitle.textContent = 'เพิ่มสินค้า';
            resetProductForm();
        }
    }, 50);
}

// Load product for edit
async function loadProductForEdit(productId) {
    try {
        const product = await apiRequest(getApiUrl('product') + `?id=${productId}`);
        
        if (product.success && product.data) {
            const p = product.data;
            document.getElementById('productName').value = p.name || '';
            document.getElementById('productPrice').value = p.price || '';
            document.getElementById('productCategory').value = p.category_id || '';
            document.getElementById('productDescription').value = p.description || '';
            document.getElementById('productStockQuantity').value = p.stock_quantity || 0;
            
            if (p.image_url) {
                document.getElementById('productImagePreview').innerHTML = 
                    `<img src="${p.image_url}" style="max-width: 200px; margin-top: 1rem; border-radius: 8px;">`;
            }
        }
    } catch (error) {
        showToast('ไม่สามารถโหลดข้อมูลสินค้าได้', 'error');
    }
}

// Save product
async function saveProduct() {
    const formData = new FormData();
    formData.append('name', document.getElementById('productName').value);
    formData.append('price', document.getElementById('productPrice').value);
    formData.append('category_id', document.getElementById('productCategory').value);
    formData.append('description', document.getElementById('productDescription').value);
    formData.append('stock_quantity', document.getElementById('productStockQuantity').value || 0);
    
    const imageFile = document.getElementById('productImage').files[0];
    if (imageFile) {
        formData.append('image', imageFile);
    }
    
    try {
        let url = getApiUrl('createProduct');
        if (editingProductId) {
            url = getApiUrl('updateProduct');
            formData.append('id', editingProductId);
        }
        
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${getToken()}`
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast(editingProductId ? 'แก้ไขสินค้าสำเร็จ' : 'เพิ่มสินค้าสำเร็จ', 'success');
            closeModal('productFormModal');
            resetProductForm();
            loadAdmin();
            
            // Reload products if on products page
            if (typeof loadProducts === 'function') {
                loadProducts();
            }
        } else {
            throw new Error(data.error || 'ไม่สามารถบันทึกได้');
        }
    } catch (error) {
        showToast(error.message, 'error');
    }
}

// Edit product
async function editProduct(productId) {
    editingProductId = productId;
    openProductForm();
}

// Delete product
async function deleteProduct(productId) {
    if (!confirm('คุณต้องการลบสินค้านี้หรือไม่?')) {
        return;
    }
    
    try {
        const formData = new URLSearchParams();
        formData.append('id', productId);
        
        const response = await fetch(getApiUrl('deleteProduct'), {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${getToken()}`,
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('ลบสินค้าสำเร็จ', 'success');
            loadAdmin();
            
            // Reload products if on products page
            if (typeof loadProducts === 'function') {
                loadProducts();
            }
        } else {
            throw new Error(data.message || 'ไม่สามารถลบได้');
        }
    } catch (error) {
        showToast(error.message, 'error');
    }
}

// Reset product form
function resetProductForm() {
    // Reset individual fields - DO NOT use form.reset() as it clears select options
    const productName = document.getElementById('productName');
    if (productName) productName.value = '';
    
    const productPrice = document.getElementById('productPrice');
    if (productPrice) productPrice.value = '';
    
    const productDescription = document.getElementById('productDescription');
    if (productDescription) productDescription.value = '';
    
    const productStockQuantity = document.getElementById('productStockQuantity');
    if (productStockQuantity) productStockQuantity.value = 0;
    
    const productImage = document.getElementById('productImage');
    if (productImage) productImage.value = '';
    
    const productImagePreview = document.getElementById('productImagePreview');
    if (productImagePreview) productImagePreview.innerHTML = '';
    
    // Reset category select value only - keep all options
    const productCategory = document.getElementById('productCategory');
    if (productCategory) {
        productCategory.value = '';
        // Ensure categories exist
        if (productCategory.options.length <= 1) {
            populateCategoriesDirectly('productCategory');
        }
    }
    
    editingProductId = null;
}

// Global functions
window.loadAdmin = loadAdmin;
window.editProduct = editProduct;
window.deleteProduct = deleteProduct;

