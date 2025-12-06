// Admin Dashboard Functions

// Load dashboard
async function loadDashboard() {
    const container = document.getElementById('dashboardContent');
    if (!container) return;
    
    const user = getUser();
    
    if (!user) {
        container.innerHTML = '<div class="empty-state"><i class="fas fa-user"></i><h3>กรุณาเข้าสู่ระบบ</h3></div>';
        return;
    }
    
    container.innerHTML = '<div class="loading">กำลังโหลด...</div>';
    
    try {
        // Load orders
        const ordersResponse = await apiRequest(getApiUrl('orders'));
        
        if (ordersResponse.success && ordersResponse.orders) {
            allOrders = ordersResponse.orders;
            displayDashboard(ordersResponse.orders);
        } else {
            allOrders = [];
            container.innerHTML = '<div class="empty-state"><i class="fas fa-shopping-bag"></i><h3>ยังไม่มีคำสั่งซื้อ</h3></div>';
        }
    } catch (error) {
        allOrders = [];
        container.innerHTML = `<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><h3>เกิดข้อผิดพลาด: ${error.message}</h3></div>`;
    }
}

// Display dashboard
function displayDashboard(orders) {
    const container = document.getElementById('dashboardContent');
    if (!container) return;
    
    // Count orders by status
    const statusCounts = {
        pending: 0,
        processing: 0,
        shipping: 0,
        completed: 0,
        cancelled: 0
    };
    
    let totalRevenue = 0;
    
    orders.forEach(order => {
        if (order.order_status) {
            const status = order.order_status.toLowerCase();
            if (statusCounts.hasOwnProperty(status)) {
                statusCounts[status]++;
            }
        }
        if (order.order_status === 'completed' || order.order_status === 'shipping') {
            totalRevenue += parseFloat(order.total_amount || 0);
        }
    });
    
    container.innerHTML = `
        <div class="dashboard-container">
            <!-- Statistics Cards -->
            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-icon pending">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <h3>${statusCounts.pending}</h3>
                        <p>รอการตรวจสอบ</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon processing">
                        <i class="fas fa-cog"></i>
                    </div>
                    <div class="stat-content">
                        <h3>${statusCounts.processing}</h3>
                        <p>กำลังจัดเตรียม</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon shipping">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div class="stat-content">
                        <h3>${statusCounts.shipping}</h3>
                        <p>กำลังจัดส่ง</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon completed">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3>${statusCounts.completed}</h3>
                        <p>สำเร็จ</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon revenue">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-content">
                        <h3>${formatPrice(totalRevenue)}</h3>
                        <p>รายได้รวม</p>
                    </div>
                </div>
            </div>
            
            <!-- Orders Table -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h2><i class="fas fa-list"></i> คำสั่งซื้อทั้งหมด</h2>
                    <div class="filter-buttons">
                        <button class="btn btn-sm ${!selectedStatus ? 'btn-primary' : 'btn-secondary'}" onclick="filterOrders(null)">ทั้งหมด</button>
                        <button class="btn btn-sm ${selectedStatus === 'pending' ? 'btn-primary' : 'btn-secondary'}" onclick="filterOrders('pending')">รอตรวจสอบ</button>
                        <button class="btn btn-sm ${selectedStatus === 'processing' ? 'btn-primary' : 'btn-secondary'}" onclick="filterOrders('processing')">กำลังจัดเตรียม</button>
                        <button class="btn btn-sm ${selectedStatus === 'shipping' ? 'btn-primary' : 'btn-secondary'}" onclick="filterOrders('shipping')">กำลังจัดส่ง</button>
                        <button class="btn btn-sm ${selectedStatus === 'completed' ? 'btn-primary' : 'btn-secondary'}" onclick="filterOrders('completed')">สำเร็จ</button>
                    </div>
                </div>
                
                <div class="orders-table-container">
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>เลขที่คำสั่งซื้อ</th>
                                <th>ลูกค้า</th>
                                <th>จำนวนสินค้า</th>
                                <th>ยอดรวม</th>
                                <th>สถานะ</th>
                                <th>วันที่สั่งซื้อ</th>
                                <th>จัดการ</th>
                            </tr>
                        </thead>
                        <tbody id="ordersTableBody">
                            ${renderOrdersTable(orders)}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
}

let selectedStatus = null;
let allOrders = [];

// Filter orders
function filterOrders(status) {
    selectedStatus = status;
    const filtered = status ? allOrders.filter(o => o.order_status === status) : allOrders;
    const tbody = document.getElementById('ordersTableBody');
    if (tbody) {
        tbody.innerHTML = renderOrdersTable(filtered);
    }
    loadDashboard(); // Reload to update filter buttons
}

// Render orders table
function renderOrdersTable(orders) {
    if (orders.length === 0) {
        return '<tr><td colspan="7" class="text-center">ไม่มีคำสั่งซื้อ</td></tr>';
    }
    
    return orders.map(order => {
        const orderDate = new Date(order.created_at).toLocaleDateString('th-TH');
        const statusClass = getStatusClass(order.order_status);
        const statusText = getStatusText(order.order_status);
        
        // Count items if not provided
        const itemCount = order.item_count || (order.items ? order.items.length : 0);
        
        return `
            <tr>
                <td><strong>${order.order_number || 'N/A'}</strong></td>
                <td>${order.shipping_name || 'N/A'}</td>
                <td>${itemCount} รายการ</td>
                <td><strong>${formatPrice(order.total_amount || 0)}</strong></td>
                <td><span class="status-badge ${statusClass}">${statusText}</span></td>
                <td>${orderDate}</td>
                <td>
                    <div class="action-buttons">
                        <button class="btn btn-sm btn-primary" onclick="viewOrderDetail(${order.id})">
                            <i class="fas fa-eye"></i> ดูรายละเอียด
                        </button>
                        ${order.order_status !== 'completed' && order.order_status !== 'cancelled' ? `
                        <button class="btn btn-sm btn-success" onclick="updateOrderStatus(${order.id}, 'next')">
                            <i class="fas fa-arrow-right"></i> อัปเดตสถานะ
                        </button>
                        ` : ''}
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

// Get status class
function getStatusClass(status) {
    const statusMap = {
        'pending': 'status-pending',
        'processing': 'status-processing',
        'shipping': 'status-shipping',
        'completed': 'status-completed',
        'cancelled': 'status-cancelled'
    };
    return statusMap[status?.toLowerCase()] || 'status-pending';
}

// Get status text
function getStatusText(status) {
    const statusMap = {
        'pending': 'รอการตรวจสอบ',
        'processing': 'กำลังจัดเตรียม',
        'shipping': 'กำลังจัดส่ง',
        'completed': 'สำเร็จ',
        'cancelled': 'ยกเลิก'
    };
    return statusMap[status?.toLowerCase()] || status;
}

// View order detail
async function viewOrderDetail(orderId) {
    try {
        const response = await apiRequest(getApiUrl('orders') + `?id=${orderId}`);
        
        if (response.success && response.orders && response.orders.length > 0) {
            const order = response.orders[0];
            showOrderDetailModal(order);
        } else {
            showToast('ไม่พบคำสั่งซื้อ', 'error');
        }
    } catch (error) {
        showToast(error.message, 'error');
    }
}

// Show order detail modal
function showOrderDetailModal(order) {
    const modal = document.getElementById('orderDetailModal');
    if (!modal) {
        // Create modal if doesn't exist
        const modalHTML = `
            <div class="modal" id="orderDetailModal">
                <div class="modal-content modal-large">
                    <span class="modal-close" onclick="closeModal('orderDetailModal')">&times;</span>
                    <div id="orderDetailContent"></div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    }
    
    const content = document.getElementById('orderDetailContent');
    content.dataset.orderId = order.id; // Store order ID for status buttons
    content.innerHTML = `
        <h2>รายละเอียดคำสั่งซื้อ #${order.order_number}</h2>
        
        <div class="order-detail-section">
            <h3><i class="fas fa-info-circle"></i> ข้อมูลคำสั่งซื้อ</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <label>เลขที่คำสั่งซื้อ:</label>
                    <span>${order.order_number}</span>
                </div>
                <div class="detail-item">
                    <label>สถานะ:</label>
                    <span class="status-badge ${getStatusClass(order.order_status)}">${getStatusText(order.order_status)}</span>
                </div>
                <div class="detail-item">
                    <label>ยอดรวม:</label>
                    <span>${formatPrice(order.total_amount || 0)}</span>
                </div>
                <div class="detail-item">
                    <label>วันที่สั่งซื้อ:</label>
                    <span>${new Date(order.created_at).toLocaleString('th-TH')}</span>
                </div>
            </div>
        </div>
        
        <div class="order-detail-section">
            <h3><i class="fas fa-truck"></i> ข้อมูลการจัดส่ง</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <label>ชื่อ-นามสกุล:</label>
                    <span>${order.shipping_name || '-'}</span>
                </div>
                <div class="detail-item">
                    <label>เบอร์โทรศัพท์:</label>
                    <span>${order.shipping_phone || '-'}</span>
                </div>
                <div class="detail-item full-width">
                    <label>ที่อยู่:</label>
                    <span>${order.shipping_address || '-'}</span>
                </div>
                <div class="detail-item">
                    <label>จังหวัด:</label>
                    <span>${order.shipping_province || '-'}</span>
                </div>
                <div class="detail-item">
                    <label>รหัสไปรษณีย์:</label>
                    <span>${order.shipping_postcode || '-'}</span>
                </div>
            </div>
        </div>
        
        <div class="order-detail-section">
            <h3><i class="fas fa-shopping-cart"></i> รายการสินค้า</h3>
            <table class="order-items-table">
                <thead>
                    <tr>
                        <th>สินค้า</th>
                        <th>ราคา</th>
                        <th>จำนวน</th>
                        <th>รวม</th>
                    </tr>
                </thead>
                <tbody>
                    ${order.items ? order.items.map(item => `
                        <tr>
                            <td>${item.product_name || 'N/A'}</td>
                            <td>${formatPrice(item.product_price || 0)}</td>
                            <td>${item.quantity || 0}</td>
                            <td>${formatPrice(item.subtotal || 0)}</td>
                        </tr>
                    `).join('') : '<tr><td colspan="4">ไม่มีรายการ</td></tr>'}
                </tbody>
            </table>
        </div>
        
        <div class="order-detail-actions">
            ${order.order_status !== 'completed' && order.order_status !== 'cancelled' ? `
            <div class="order-status-actions">
                <h3><i class="fas fa-cog"></i> จัดการสถานะ</h3>
                <div class="status-buttons">
                    ${getStatusButtons(order.order_status, order.id)}
                </div>
            </div>
            ` : ''}
            <button class="btn btn-secondary" onclick="closeModal('orderDetailModal')">
                ปิด
            </button>
        </div>
    `;
    
    document.getElementById('orderDetailModal').classList.add('active');
}

// Get status buttons based on current status
function getStatusButtons(currentStatus, orderId) {
    const statusOptions = {
        'pending': [
            { status: 'processing', label: 'กำลังจัดเตรียม', class: 'btn-primary', icon: 'fa-box' },
            { status: 'cancelled', label: 'ยกเลิก', class: 'btn-danger', icon: 'fa-times' }
        ],
        'processing': [
            { status: 'shipping', label: 'กำลังจัดส่ง', class: 'btn-info', icon: 'fa-truck' },
            { status: 'cancelled', label: 'ยกเลิก', class: 'btn-danger', icon: 'fa-times' }
        ],
        'shipping': [
            { status: 'completed', label: 'สำเร็จ', class: 'btn-success', icon: 'fa-check' }
        ]
    };
    
    const options = statusOptions[currentStatus?.toLowerCase()] || [];
    return options.map(opt => 
        `<button class="btn ${opt.class}" onclick="updateOrderStatus(${orderId}, '${opt.status}')">
            <i class="fas ${opt.icon}"></i> ${opt.label}
        </button>`
    ).join('');
}

// Update order status
async function updateOrderStatus(orderId, newStatus) {
    if (!confirm(`คุณต้องการเปลี่ยนสถานะเป็น "${getStatusText(newStatus)}" หรือไม่?`)) {
        return;
    }
    
    try {
        const updateResponse = await apiRequest(getApiUrl('orders'), {
            method: 'PUT',
            body: JSON.stringify({
                id: orderId,
                order_status: newStatus
            })
        });
        
        if (updateResponse.success) {
            showToast('อัปเดตสถานะสำเร็จ', 'success');
            closeModal('orderDetailModal');
            loadDashboard();
        } else {
            throw new Error(updateResponse.message || 'ไม่สามารถอัปเดตได้');
        }
    } catch (error) {
        showToast(error.message, 'error');
    }
}

// Global functions
window.loadDashboard = loadDashboard;
window.viewOrderDetail = viewOrderDetail;
window.updateOrderStatus = updateOrderStatus;
window.filterOrders = filterOrders;

