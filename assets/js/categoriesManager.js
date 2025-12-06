// Categories Manager Functions

let allCategories = [];

// Load categories
async function loadCategories() {
    try {
        const token = getToken();
        if (!token) {
            return [];
        }
        
        const response = await fetch(getApiUrl('categories'), {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });
        
        if (!response.ok) {
            throw new Error('ไม่สามารถโหลดหมวดหมู่ได้');
        }
        
        const data = await response.json();
        if (data.success && data.categories) {
            allCategories = data.categories;
            return allCategories;
        }
        return [];
    } catch (error) {
        console.error('Error loading categories:', error);
        return [];
    }
}

// Default categories (fallback)
const defaultCategories = [
    { id: 'electronics', name: 'อิเล็กทรอนิกส์' },
    { id: 'clothing', name: 'เสื้อผ้า' },
    { id: 'books', name: 'หนังสือ' },
    { id: 'home', name: 'ของใช้ในบ้าน' },
    { id: 'sports', name: 'กีฬา' },
    { id: 'general', name: 'ของใช้ทั่วไป' }
];

// Populate category dropdown
async function populateCategoryDropdown(selectId) {
    const select = document.getElementById(selectId);
    if (!select) {
        console.warn('Category dropdown not found:', selectId);
        return;
    }
    
    // Don't clear if already has options (preserve defaults)
    const currentValue = select.value;
    
    try {
        const categories = await loadCategories();
        
        // ถ้ามีข้อมูลจาก API ให้ใช้
        if (categories && categories.length > 0) {
            select.innerHTML = '<option value="">เลือกหมวดหมู่</option>';
            categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.id;
                option.textContent = category.name;
                select.appendChild(option);
            });
            // Restore previous value if it exists
            if (currentValue) {
                select.value = currentValue;
            }
            console.log('Loaded categories from API:', categories.length);
        } else {
            // ถ้าไม่มีข้อมูลจาก API ให้ใช้ default categories (ถ้ายังไม่มี)
            if (select.options.length <= 1) {
                select.innerHTML = '<option value="">เลือกหมวดหมู่</option>';
                defaultCategories.forEach(category => {
                    const option = document.createElement('option');
                    option.value = category.id;
                    option.textContent = category.name;
                    select.appendChild(option);
                });
                console.log('Using default categories');
            }
        }
    } catch (error) {
        console.error('Error loading categories, keeping defaults:', error);
        // Keep existing options (defaults) if API fails
        if (select.options.length <= 1) {
            select.innerHTML = '<option value="">เลือกหมวดหมู่</option>';
            defaultCategories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.id;
                option.textContent = category.name;
                select.appendChild(option);
            });
        }
    }
    
    return select;
}

// Global functions
window.loadCategories = loadCategories;
window.populateCategoryDropdown = populateCategoryDropdown;

