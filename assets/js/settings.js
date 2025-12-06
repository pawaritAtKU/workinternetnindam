// Settings Functions

// Load settings page
async function loadSettings() {
    const container = document.getElementById('settingsContent');
    if (!container) return;
    
    const user = getUser();
    
    if (!user) {
        container.innerHTML = '<div class="empty-state"><i class="fas fa-user"></i><h3>กรุณาเข้าสู่ระบบ</h3><button class="btn btn-primary" onclick="showPage(\'login\')">เข้าสู่ระบบ</button></div>';
        return;
    }
    
    container.innerHTML = '<div class="loading">กำลังโหลด...</div>';
    
    try {
        // โหลดข้อมูลโปรไฟล์จาก API
        const response = await apiRequest(getApiUrl('profile'));
        
        if (response.success && response.user) {
            displaySettings(response.user);
        } else {
            // ถ้า API ไม่สำเร็จ ให้ใช้ข้อมูลจาก localStorage
            displaySettings(user);
        }
    } catch (error) {
        // ถ้ามี error ให้ใช้ข้อมูลจาก localStorage
        console.error('Error loading profile:', error);
        displaySettings(user);
    }
}

// Display settings page with profile
function displaySettings(user) {
    const container = document.getElementById('settingsContent');
    if (!container) return;
    
    container.innerHTML = `
        <div class="settings-container">
            <!-- Profile Section -->
            <div class="settings-section">
                <div class="section-header">
                    <h2><i class="fas fa-user"></i> ข้อมูลโปรไฟล์</h2>
                </div>
                
                <div class="profile-display">
                    <div class="profile-avatar-large">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3>${user.username || 'ผู้ใช้'}</h3>
                </div>
                
                <div class="profile-info-grid">
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-user"></i>
                            <span>ชื่อผู้ใช้</span>
                        </div>
                        <div class="info-value">${user.username || '-'}</div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-envelope"></i>
                            <span>อีเมล</span>
                        </div>
                        <div class="info-value">${user.email || '-'}</div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-id-card"></i>
                            <span>รหัสผู้ใช้</span>
                        </div>
                        <div class="info-value">#${user.id || '-'}</div>
                    </div>
                    
                    ${user.role === 'admin' ? `
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-shield-alt"></i>
                            <span>สิทธิ์</span>
                        </div>
                        <div class="info-value">
                            <span class="badge badge-admin">ผู้ดูแลระบบ</span>
                        </div>
                    </div>
                    ` : ''}
                </div>
            </div>
            
            <!-- Actions Section -->
            <div class="settings-section">
                <div class="section-header">
                    <h2><i class="fas fa-tasks"></i> การจัดการ</h2>
                </div>
                
                <div class="settings-actions">
                    <button class="action-card" onclick="showPage('profile')">
                        <div class="action-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="action-content">
                            <h3>ดูโปรไฟล์</h3>
                            <p>ดูข้อมูลส่วนตัวของคุณ</p>
                        </div>
                        <i class="fas fa-chevron-right action-arrow"></i>
                    </button>
                    
                    <button class="action-card" onclick="goToAddProduct()">
                        <div class="action-icon">
                            <i class="fas fa-plus-circle"></i>
                        </div>
                        <div class="action-content">
                            <h3>เพิ่มสินค้าเพื่อขาย</h3>
                            <p>เพิ่มสินค้าของคุณลงในระบบ</p>
                        </div>
                        <i class="fas fa-chevron-right action-arrow"></i>
                    </button>
                    
                    <button class="action-card" onclick="showPage('cart')">
                        <div class="action-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="action-content">
                            <h3>ตะกร้าสินค้า</h3>
                            <p>ดูสินค้าในตะกร้าของคุณ</p>
                        </div>
                        <i class="fas fa-chevron-right action-arrow"></i>
                    </button>
                    
                    ${user.role === 'admin' ? `
                    <button class="action-card" onclick="showPage('admin')">
                        <div class="action-icon">
                            <i class="fas fa-tools"></i>
                        </div>
                        <div class="action-content">
                            <h3>จัดการสินค้า</h3>
                            <p>จัดการสินค้าทั้งหมดในระบบ</p>
                        </div>
                        <i class="fas fa-chevron-right action-arrow"></i>
                    </button>
                    ` : ''}
                </div>
            </div>
            
            <!-- Account Section -->
            <div class="settings-section">
                <div class="section-header">
                    <h2><i class="fas fa-user-cog"></i> บัญชี</h2>
                </div>
                
                <div class="settings-actions">
                    <button class="action-card danger" onclick="confirmLogout()">
                        <div class="action-icon">
                            <i class="fas fa-sign-out-alt"></i>
                        </div>
                        <div class="action-content">
                            <h3>ออกจากระบบ</h3>
                            <p>ออกจากระบบและลบข้อมูลการเข้าสู่ระบบ</p>
                        </div>
                        <i class="fas fa-chevron-right action-arrow"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
}

// Confirm logout
function confirmLogout() {
    if (confirm('คุณต้องการออกจากระบบหรือไม่?')) {
        logout();
    }
}

// Go to add product form
function goToAddProduct() {
    showPage('profile');
    // รอให้ profile page โหลดเสร็จก่อน
    setTimeout(() => {
        if (typeof showAddProductForm === 'function') {
            showAddProductForm();
        } else {
            // ถ้ายังไม่มี function ให้โหลด profile ก่อน
            if (typeof loadProfile === 'function') {
                loadProfile();
                setTimeout(() => {
                    if (typeof showAddProductForm === 'function') {
                        showAddProductForm();
                    }
                }, 500);
            }
        }
    }, 200);
}

// Global functions
window.loadSettings = loadSettings;
window.confirmLogout = confirmLogout;
window.goToAddProduct = goToAddProduct;

