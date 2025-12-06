// Main Application Controller

// Initialize app
document.addEventListener('DOMContentLoaded', () => {
    initNavigation();
    checkAuth();
    loadFeaturedProducts();
});

// Navigation
function initNavigation() {
    const navLinks = document.querySelectorAll('.nav-link[data-page]');
    const userMenuItems = document.querySelectorAll('.user-menu-item[data-page]');
    const navToggle = document.getElementById('navToggle');
    const navMenu = document.getElementById('navMenu');
    
    // Handle main navigation links
    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const page = link.getAttribute('data-page');
            showPage(page);
            
            // Update active nav link
            navLinks.forEach(l => l.classList.remove('active'));
            link.classList.add('active');
            
            // Close mobile menu
            closeMobileMenu();
        });
    });
    
    // Handle user menu items (profile, admin)
    userMenuItems.forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            const page = item.getAttribute('data-page');
            if (page) {
                showPage(page);
                
                // Close user menu
                const userMenu = document.getElementById('userMenu');
                if (userMenu) {
                    userMenu.classList.remove('active');
                }
                
                // Close mobile menu
                closeMobileMenu();
            }
        });
    });
    
    // Mobile menu toggle - use the global toggleMobileMenu function
    // The onclick handler in HTML will handle this
    
    // Logout
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', (e) => {
            e.preventDefault();
            logout();
        });
    }
    
    // Toggle user menu - use event delegation
    const navUser = document.querySelector('.nav-user');
    if (navUser) {
        // Use event delegation for user-info click
        navUser.addEventListener('click', (e) => {
            const userInfo = e.target.closest('.user-info');
            if (userInfo) {
                e.stopPropagation();
                const userMenu = document.getElementById('userMenu');
                if (userMenu && userMenu.style.display !== 'none') {
                    userMenu.classList.toggle('active');
                }
            }
        });
        
        // Close user menu when clicking outside
        document.addEventListener('click', (e) => {
            if (navUser && !navUser.contains(e.target)) {
                const userMenu = document.getElementById('userMenu');
                if (userMenu) {
                    userMenu.classList.remove('active');
                }
            }
        });
    }
}

// Page navigation
function showPage(pageId) {
    // Hide all pages
    document.querySelectorAll('.page').forEach(page => {
        page.classList.remove('active');
    });
    
    // Show selected page
    const page = document.getElementById(pageId);
    if (page) {
        page.classList.add('active');
        
        // Load page-specific content
        switch(pageId) {
            case 'home':
                loadFeaturedProducts();
                break;
            case 'products':
                loadProducts();
                break;
            case 'categories':
                // Categories page is static, no need to load
                break;
            case 'cart':
                loadCart();
                break;
            case 'shipping':
                // Shipping form is static
                break;
            case 'payment':
                loadPayment();
                break;
            case 'settings':
                loadSettings();
                break;
            case 'profile':
                loadProfile();
                break;
            case 'admin':
                if (checkAdmin()) {
                    loadAdmin();
                }
                break;
            case 'dashboard':
                if (checkAdmin()) {
                    loadDashboard();
                }
                break;
            case 'product-detail':
                // Product detail will be loaded via URL parameter
                break;
            case 'login':
                // Login page is static, but update cart badge if logged in
                if (getUser()) {
                    updateCartBadge();
                }
                break;
        }
    }
    
    // Update cart badge when navigating
    if (getUser()) {
        updateCartBadge();
    }
}

// Check authentication
function checkAuth() {
    const user = getUser();
    const token = getToken();
    
    // Check token storage
    console.log('Token check:', {
        hasToken: !!token,
        tokenLength: token ? token.length : 0,
        hasUser: !!user,
        userData: user
    });
    
    if (user && token) {
        // User is logged in
        const loginBtn = document.getElementById('loginBtn');
        const navUserMenu = document.getElementById('navUserMenu');
        const userName = document.getElementById('userName');
        
        if (loginBtn) {
            loginBtn.style.display = 'none';
        }
        if (navUserMenu) {
            navUserMenu.style.display = 'flex';
        }
        if (userName) userName.textContent = user.username || user.email;
        
        // Check if admin
        console.log('User role check:', user.role, 'is admin:', user.role === 'admin');
        if (user.role === 'admin') {
            const adminLink = document.getElementById('adminLink');
            const adminManageLink = document.getElementById('adminManageLink');
            const addProductBtn = document.getElementById('addProductBtn');
            
            console.log('Admin links found:', {
                adminLink: !!adminLink,
                adminManageLink: !!adminManageLink,
                addProductBtn: !!addProductBtn
            });
            
            if (adminLink) {
                adminLink.style.display = 'flex';
                console.log('Admin link displayed');
            }
            if (adminManageLink) {
                adminManageLink.style.display = 'flex';
                console.log('Admin manage link displayed');
            }
            if (addProductBtn) {
                addProductBtn.style.display = 'block';
            }
        } else {
            console.log('User is not admin, role:', user.role);
        }
    } else {
        // User is not logged in
        const loginBtn = document.getElementById('loginBtn');
        const navUserMenu = document.getElementById('navUserMenu');
        
        if (loginBtn) {
            loginBtn.style.display = 'flex';
        }
        if (navUserMenu) {
            navUserMenu.style.display = 'none';
        }
    }
}

// Check if user is admin
function checkAdmin() {
    const user = getUser();
    if (!user || user.role !== 'admin') {
        showToast('คุณไม่มีสิทธิ์เข้าถึงหน้านี้', 'error');
        showPage('home');
        return false;
    }
    return true;
}

// Logout
function logout() {
    if (confirm('คุณต้องการออกจากระบบหรือไม่?')) {
        removeToken();
        removeUser();
        checkAuth();
        closeMobileMenu();
        showPage('home');
        showToast('ออกจากระบบสำเร็จ', 'success');
    }
}

// Load featured products
async function loadFeaturedProducts() {
    const container = document.getElementById('featuredProducts');
    if (!container) return;
    
    try {
        const products = await apiRequest(getApiUrl('products'));
        const featured = products.slice(0, 6); // Show first 6 products
        
        container.innerHTML = '';
        
        if (featured.length === 0) {
            container.innerHTML = '<div class="empty-state"><i class="fas fa-box-open"></i><h3>ยังไม่มีสินค้า</h3></div>';
            return;
        }
        
        featured.forEach(product => {
            container.appendChild(createProductCard(product));
        });
    } catch (error) {
        container.innerHTML = `<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><h3>เกิดข้อผิดพลาด: ${error.message}</h3></div>`;
    }
}

// Create product card
function createProductCard(product) {
    const card = document.createElement('div');
    card.className = 'product-card';
    
    const stockQuantity = product.stock_quantity || 0;
    const isOutOfStock = stockQuantity <= 0;
    const stockBadge = isOutOfStock ? '<span class="stock-badge out-of-stock">สินค้าหมด</span>' : '';
    
    card.innerHTML = `
        <div class="product-image-container">
            <img src="${product.image_url || 'assets/images/placeholder.svg'}" alt="${product.name}" class="product-image" onerror="this.onerror=null; this.src='assets/images/placeholder.svg';">
            ${stockBadge}
        </div>
        <div class="product-info">
            <h3 class="product-name">${product.name || 'สินค้า'}</h3>
            <div class="product-price">${formatPrice(product.price || 0)}</div>
            <div class="product-actions">
                <button class="btn btn-secondary" onclick="viewProductDetail(${product.id})">
                    <i class="fas fa-eye"></i> ดูรายละเอียด
                </button>
                ${!isOutOfStock ? `
                <button class="btn btn-primary" onclick="addToCart(${product.id}, '${product.name}', ${product.price}, '${product.image_url || ''}')">
                    <i class="fas fa-cart-plus"></i> เพิ่มลงตะกร้า
                </button>
                ` : `
                <button class="btn btn-secondary" disabled>
                    <i class="fas fa-times-circle"></i> สินค้าหมด
                </button>
                `}
            </div>
        </div>
    `;
    
    return card;
}

// View product detail
function viewProductDetail(productId) {
    showPage('product-detail');
    if (typeof loadProductDetail === 'function') {
        loadProductDetail(productId);
    }
}

// Show product modal
function showProductModal(product) {
    const modal = document.getElementById('productModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalBody = document.getElementById('modalBody');
    
    modalTitle.textContent = product.name || 'สินค้า';
    modalBody.innerHTML = `
        <img src="${product.image_url || 'assets/images/placeholder.svg'}" alt="${product.name}" style="width: 100%; border-radius: 8px; margin-bottom: 1rem;" onerror="this.onerror=null; this.src='assets/images/placeholder.svg';">
        <div style="margin-bottom: 1rem;">
            <h3 style="color: var(--primary-color); font-size: 2rem; margin-bottom: 0.5rem;">${formatPrice(product.price || 0)}</h3>
            ${product.description ? `<p style="color: var(--text-light);">${product.description}</p>` : ''}
        </div>
        <button class="btn btn-primary btn-block" onclick="addToCart(${product.id}, '${product.name}', ${product.price}, '${product.image_url || ''}'); closeModal('productModal');">
            <i class="fas fa-cart-plus"></i> เพิ่มลงตะกร้า
        </button>
    `;
    
    modal.classList.add('active');
}

// Close modal
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
    }
}

// Modal close handlers
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.modal-close').forEach(closeBtn => {
        closeBtn.addEventListener('click', () => {
            closeBtn.closest('.modal').classList.remove('active');
        });
    });
    
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', (e) => {
            // ป้องกันไม่ให้ปิด modal เมื่อคลิกที่ modal-content หรือ elements ภายใน
            if (e.target === modal || e.target.classList.contains('modal')) {
                modal.classList.remove('active');
            }
        });
    });
});

// Mobile menu toggle
function toggleMobileMenu() {
    console.log('toggleMobileMenu called');
    const navMenu = document.getElementById('navMenu');
    const overlay = document.getElementById('menuOverlay');
    const navToggleIcon = document.getElementById('navToggleIcon');
    
    console.log('Elements found:', { navMenu: !!navMenu, overlay: !!overlay, navToggleIcon: !!navToggleIcon });
    
    if (navMenu) {
        const isActive = navMenu.classList.toggle('active');
        console.log('Menu active state:', isActive);
        
        // Change icon between hamburger and X
        if (navToggleIcon) {
            if (isActive) {
                navToggleIcon.classList.remove('fa-bars');
                navToggleIcon.classList.add('fa-times');
            } else {
                navToggleIcon.classList.remove('fa-times');
                navToggleIcon.classList.add('fa-bars');
            }
        }
    } else {
        console.error('navMenu element not found!');
    }
    
    if (overlay) {
        overlay.classList.toggle('active');
    }
}

// Close mobile menu
function closeMobileMenu() {
    const navMenu = document.getElementById('navMenu');
    const overlay = document.getElementById('menuOverlay');
    const navToggleIcon = document.getElementById('navToggleIcon');
    
    if (navMenu) {
        navMenu.classList.remove('active');
    }
    
    if (overlay) {
        overlay.classList.remove('active');
    }
    
    // Reset icon to hamburger
    if (navToggleIcon) {
        navToggleIcon.classList.remove('fa-times');
        navToggleIcon.classList.add('fa-bars');
    }
}

// Database check function removed - no longer needed

// Global functions
window.showPage = showPage;
window.closeModal = closeModal;
window.addToCart = addToCart;
window.createProductCard = createProductCard;
window.displayProducts = displayProducts;
window.viewProductDetail = viewProductDetail;
window.toggleMobileMenu = toggleMobileMenu;
window.closeMobileMenu = closeMobileMenu;
window.logout = logout;

