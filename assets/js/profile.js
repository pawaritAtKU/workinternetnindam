// Profile Functions

// Load profile
async function loadProfile() {
    const container = document.getElementById('profileContent');
    if (!container) return;
    
    const user = getUser();
    
    if (!user) {
        container.innerHTML = '<div class="empty-state"><i class="fas fa-user"></i><h3>กรุณาเข้าสู่ระบบ</h3><button class="btn btn-primary" onclick="showPage(\'login\')">เข้าสู่ระบบ</button></div>';
        return;
    }
    
    container.innerHTML = '<div class="loading">กำลังโหลด...</div>';
    
    try {
        const response = await apiRequest(getApiUrl('profile'));
        
        if (response.success && response.user) {
            displayProfile(response.user);
        } else {
            throw new Error('ไม่สามารถโหลดข้อมูลได้');
        }
    } catch (error) {
        container.innerHTML = `<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><h3>เกิดข้อผิดพลาด: ${error.message}</h3></div>`;
    }
}

// Display profile
async function displayProfile(user) {
    const container = document.getElementById('profileContent');
    if (!container) return;
    
    // Load store info if exists
    let storeInfo = null;
    try {
        const storeResponse = await apiRequest(getApiUrl('stores'));
        if (storeResponse.success && storeResponse.store) {
            storeInfo = storeResponse.store;
        }
    } catch (error) {
        console.log('No store found or error loading store');
    }
    
    container.innerHTML = `
        <div class="profile-container">
            <div class="profile-header">
                <div class="profile-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <h2>${user.username || 'ผู้ใช้'}</h2>
            </div>
            
            <div class="profile-info">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> ชื่อผู้ใช้</label>
                    <input type="text" value="${user.username || ''}" disabled>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> อีเมล</label>
                    <input type="email" value="${user.email || ''}" disabled>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-id-card"></i> รหัสผู้ใช้</label>
                    <input type="text" value="#${user.id || ''}" disabled>
                </div>
                
                ${user.role === 'admin' ? '<div class="form-group"><label><i class="fas fa-shield-alt"></i> สิทธิ์</label><input type="text" value="ผู้ดูแลระบบ" disabled></div>' : ''}
                
                ${storeInfo ? `
                <div class="form-group">
                    <label><i class="fas fa-store"></i> ร้านค้า</label>
                    <input type="text" value="${storeInfo.store_name || ''}" disabled>
                </div>
                ` : ''}
            </div>
            
            <div class="profile-actions">
                ${!storeInfo ? `
                <button class="btn btn-secondary btn-block" onclick="showCreateStoreForm()">
                    <i class="fas fa-store"></i> สร้างร้านค้า
                </button>
                ` : ''}
                <button class="btn btn-primary btn-block" onclick="showAddProductForm()">
                    <i class="fas fa-plus"></i> เพิ่มสินค้าเพื่อขาย
                </button>
            </div>
        </div>
    `;
}

// Show create store form
async function showCreateStoreForm() {
    const container = document.getElementById('profileContent');
    if (!container) return;
    
    container.innerHTML = `
        <div class="add-product-container">
            <div class="form-header">
                <h2><i class="fas fa-store"></i> สร้างร้านค้า</h2>
                <button class="btn btn-secondary" onclick="loadProfile()">
                    <i class="fas fa-arrow-left"></i> กลับ
                </button>
            </div>
            
            <form id="createStoreForm" class="product-form">
                <div class="form-group">
                    <label><i class="fas fa-store"></i> ชื่อร้านค้า *</label>
                    <input type="text" id="storeName" required placeholder="กรอกชื่อร้านค้าของคุณ">
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-align-left"></i> คำอธิบายร้านค้า</label>
                    <textarea id="storeDescription" rows="3" placeholder="อธิบายเกี่ยวกับร้านค้าของคุณ..."></textarea>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-map-marker-alt"></i> ที่อยู่ร้านค้า</label>
                    <textarea id="storeAddress" rows="2" placeholder="ที่อยู่ร้านค้า"></textarea>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-phone"></i> เบอร์โทรศัพท์</label>
                    <input type="tel" id="storePhone" placeholder="เบอร์โทรศัพท์ร้านค้า">
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> อีเมลร้านค้า</label>
                    <input type="email" id="storeEmail" placeholder="อีเมลร้านค้า">
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="loadProfile()">
                        ยกเลิก
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> สร้างร้านค้า
                    </button>
                </div>
            </form>
        </div>
    `;
    
    // Handle form submit
    const form = document.getElementById('createStoreForm');
    if (form) {
        form.addEventListener('submit', handleCreateStoreSubmit);
    }
}

// Handle create store form submit
async function handleCreateStoreSubmit(e) {
    e.preventDefault();
    
    const storeName = document.getElementById('storeName').value.trim();
    const storeDescription = document.getElementById('storeDescription').value.trim();
    const storeAddress = document.getElementById('storeAddress').value.trim();
    const storePhone = document.getElementById('storePhone').value.trim();
    const storeEmail = document.getElementById('storeEmail').value.trim();
    
    if (!storeName) {
        showToast('กรุณากรอกชื่อร้านค้า', 'error');
        return;
    }
    
    try {
        const response = await apiRequest(getApiUrl('stores'), {
            method: 'POST',
            body: JSON.stringify({
                store_name: storeName,
                store_description: storeDescription,
                store_address: storeAddress,
                store_phone: storePhone,
                store_email: storeEmail
            })
        });
        
        if (response.success) {
            showToast('สร้างร้านค้าสำเร็จ!', 'success');
            loadProfile();
        } else {
            throw new Error(response.message || 'ไม่สามารถสร้างร้านค้าได้');
        }
    } catch (error) {
        showToast(error.message, 'error');
    }
}

// Show add product form
async function showAddProductForm() {
    const container = document.getElementById('profileContent');
    if (!container) return;
    
    // Check if user has store
    let storeInfo = null;
    try {
        const storeResponse = await apiRequest(getApiUrl('stores'));
        if (storeResponse.success && storeResponse.store) {
            storeInfo = storeResponse.store;
        }
    } catch (error) {
        console.log('Error loading store');
    }
    
    if (!storeInfo) {
        showToast('กรุณาสร้างร้านค้าก่อนเพิ่มสินค้า', 'error');
        showCreateStoreForm();
        return;
    }
    
    container.innerHTML = `
        <div class="add-product-container">
            <div class="form-header">
                <h2><i class="fas fa-plus-circle"></i> เพิ่มสินค้าเพื่อขาย</h2>
                <button class="btn btn-secondary" onclick="loadProfile()">
                    <i class="fas fa-arrow-left"></i> กลับ
                </button>
            </div>
            
            <form id="addProductForm" class="product-form" enctype="multipart/form-data">
                <div class="form-group">
                    <label><i class="fas fa-tag"></i> ชื่อสินค้า *</label>
                    <input type="text" id="addProductName" required>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-dollar-sign"></i> ราคา (บาท) *</label>
                    <input type="number" id="addProductPrice" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-th-large"></i> หมวดหมู่ *</label>
                    <select id="addProductCategory" required>
                        <option value="">เลือกหมวดหมู่</option>
                        <option value="electronics">อิเล็กทรอนิกส์</option>
                        <option value="clothing">เสื้อผ้า</option>
                        <option value="books">หนังสือ</option>
                        <option value="home">ของใช้ในบ้าน</option>
                        <option value="sports">กีฬา</option>
                        <option value="general">ของใช้ทั่วไป</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-box"></i> จำนวนสินค้า (Stock) *</label>
                    <input type="number" id="addProductStock" min="0" value="0" required>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-align-left"></i> รายละเอียดสินค้า</label>
                    <textarea id="addProductDescription" rows="4" placeholder="อธิบายรายละเอียดสินค้า..."></textarea>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-image"></i> รูปภาพสินค้า</label>
                    <input type="file" id="addProductImage" accept="image/*">
                    <div id="addProductImagePreview" style="margin-top: 1rem;"></div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="loadProfile()">
                        ยกเลิก
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> เพิ่มสินค้า
                    </button>
                </div>
            </form>
        </div>
    `;
    
    // Preview image
    const imageInput = document.getElementById('addProductImage');
    const imagePreview = document.getElementById('addProductImagePreview');
    
    if (imageInput && imagePreview) {
        imageInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    imagePreview.innerHTML = `<img src="${e.target.result}" style="max-width: 300px; border-radius: 8px; margin-top: 1rem;">`;
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Handle form submit
    const form = document.getElementById('addProductForm');
    if (form) {
        form.addEventListener('submit', handleAddProductSubmit);
    }
}

// Handle add product form submit
async function handleAddProductSubmit(e) {
    e.preventDefault();
    
    const name = document.getElementById('addProductName').value.trim();
    const price = document.getElementById('addProductPrice').value;
    const category = document.getElementById('addProductCategory').value;
    const stock = document.getElementById('addProductStock').value;
    const description = document.getElementById('addProductDescription').value.trim();
    const imageFile = document.getElementById('addProductImage').files[0];
    
    if (!name || !price || !category) {
        showToast('กรุณากรอกข้อมูลให้ครบถ้วน', 'error');
        return;
    }
    
    if (stock < 0) {
        showToast('จำนวนสินค้าต้องมากกว่าหรือเท่ากับ 0', 'error');
        return;
    }
    
    try {
        // Get store info
        const storeResponse = await apiRequest(getApiUrl('stores'));
        if (!storeResponse.success || !storeResponse.store) {
            throw new Error('ไม่พบร้านค้า กรุณาสร้างร้านค้าก่อน');
        }
        
        const formData = new FormData();
        formData.append('name', name);
        formData.append('price', price);
        formData.append('category_id', category);
        formData.append('stock_quantity', stock);
        formData.append('store_id', storeResponse.store.id);
        formData.append('description', description);
        if (imageFile) {
            formData.append('image', imageFile);
        }
        
        const response = await fetch(getApiUrl('createProduct'), {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${getToken()}`
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('เพิ่มสินค้าสำเร็จ!', 'success');
            loadProfile();
            
            // รีโหลดสินค้าในหน้า products
            if (typeof loadProducts === 'function') {
                loadProducts();
            }
        } else {
            throw new Error(data.error || 'ไม่สามารถเพิ่มสินค้าได้');
        }
    } catch (error) {
        showToast(error.message, 'error');
    }
}

// Global functions
window.loadProfile = loadProfile;
window.showAddProductForm = showAddProductForm;
window.handleAddProductSubmit = handleAddProductSubmit;
window.showCreateStoreForm = showCreateStoreForm;
window.handleCreateStoreSubmit = handleCreateStoreSubmit;

