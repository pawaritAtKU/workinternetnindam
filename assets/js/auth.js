// Authentication Functions

document.addEventListener('DOMContentLoaded', () => {
    initAuthTabs();
    initLoginForm();
    initRegisterForm();
});

// Initialize auth tabs
function initAuthTabs() {
    const authTabs = document.querySelectorAll('.auth-tab');
    
    authTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const tabName = tab.getAttribute('data-tab');
            
            // Update tab active state
            authTabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            
            // Update form visibility
            document.querySelectorAll('.auth-form').forEach(form => {
                form.classList.remove('active');
            });
            
            const form = document.getElementById(tabName + 'Form');
            if (form) {
                form.classList.add('active');
            }
        });
    });
}

// Initialize login form
function initLoginForm() {
    const loginForm = document.getElementById('loginForm');
    if (!loginForm) return;
    
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const identifierInput = document.getElementById('loginIdentifier');
        const passwordInput = document.getElementById('loginPassword');
        const errorDiv = document.getElementById('loginError');
        
        // Get and trim values
        const identifier = identifierInput ? identifierInput.value.trim() : '';
        const password = passwordInput ? passwordInput.value.trim() : '';
        
        // Clear previous errors
        if (errorDiv) {
            errorDiv.classList.remove('show');
            errorDiv.textContent = '';
        }
        
        // Validate inputs
        if (!identifier || !password) {
            if (errorDiv) {
                errorDiv.textContent = 'กรุณากรอกอีเมล/ชื่อผู้ใช้ และรหัสผ่าน';
                errorDiv.classList.add('show');
            }
            return;
        }
        
        // Show loading state
        const submitBtn = loginForm.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn ? submitBtn.innerHTML : '';
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> กำลังเข้าสู่ระบบ...';
        }
        
        try {
            const loginData = {
                email: identifier,
                username: identifier,
                password: password
            };
            
            console.log('Sending login request:', {
                url: getApiUrl('login'),
                identifier: identifier,
                hasPassword: !!password
            });
            
            const response = await apiRequest(getApiUrl('login'), {
                method: 'POST',
                body: JSON.stringify(loginData)
            });
            
            if (response.success && response.token) {
                setToken(response.token);
                if (response.user) {
                    setUser(response.user);
                }
                
                showToast('เข้าสู่ระบบสำเร็จ', 'success');
                
                // Update UI
                checkAuth();
                
                // Update cart badge
                if (typeof updateCartBadge === 'function') {
                    updateCartBadge();
                }
                
                // Redirect to home
                showPage('home');
                
                // Close login form
                loginForm.reset();
            } else {
                throw new Error(response.message || 'เข้าสู่ระบบไม่สำเร็จ');
            }
        } catch (error) {
            console.error('Login error:', error);
            errorDiv.textContent = error.message || 'เกิดข้อผิดพลาดในการเข้าสู่ระบบ';
            errorDiv.classList.add('show');
        } finally {
            // Reset button state
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
        }
    });
}

// Initialize register form
function initRegisterForm() {
    const registerForm = document.getElementById('registerForm');
    if (!registerForm) return;
    
    registerForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const usernameInput = document.getElementById('registerUsername');
        const emailInput = document.getElementById('registerEmail');
        const passwordInput = document.getElementById('registerPassword');
        const errorDiv = document.getElementById('registerError');
        
        // Get and trim values
        const username = usernameInput ? usernameInput.value.trim() : '';
        const email = emailInput ? emailInput.value.trim() : '';
        const password = passwordInput ? passwordInput.value.trim() : '';
        
        // Clear previous errors
        if (errorDiv) {
            errorDiv.classList.remove('show');
            errorDiv.textContent = '';
        }
        
        // Validate inputs
        if (!username || !email || !password) {
            if (errorDiv) {
                errorDiv.textContent = 'กรุณากรอกชื่อผู้ใช้ อีเมล และรหัสผ่าน';
                errorDiv.classList.add('show');
            }
            return;
        }
        
        // Validate password length
        if (password.length < 6) {
            if (errorDiv) {
                errorDiv.textContent = 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร';
                errorDiv.classList.add('show');
            }
            return;
        }
        
        // Validate email format
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            if (errorDiv) {
                errorDiv.textContent = 'รูปแบบอีเมลไม่ถูกต้อง';
                errorDiv.classList.add('show');
            }
            return;
        }
        
        // Show loading state
        const submitBtn = registerForm.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn ? submitBtn.innerHTML : '';
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> กำลังสมัครสมาชิก...';
        }
        
        try {
            const registerData = {
                username: username,
                email: email,
                password: password
            };
            
            console.log('Sending register request:', {
                url: getApiUrl('register'),
                username: username,
                email: email,
                hasPassword: !!password
            });
            
            const response = await apiRequest(getApiUrl('register'), {
                method: 'POST',
                body: JSON.stringify(registerData)
            });
            
            if (response.success && response.token) {
                setToken(response.token);
                if (response.user) {
                    setUser(response.user);
                }
                
                showToast('สมัครสมาชิกสำเร็จ', 'success');
                
                // Update UI
                checkAuth();
                
                // Update cart badge
                if (typeof updateCartBadge === 'function') {
                    updateCartBadge();
                }
                
                // Redirect to home
                showPage('home');
                
                // Close register form
                registerForm.reset();
            } else {
                throw new Error(response.message || 'สมัครสมาชิกไม่สำเร็จ');
            }
        } catch (error) {
            console.error('Register error:', error);
            console.error('Error details:', {
                name: error.name,
                message: error.message,
                stack: error.stack
            });
            
            if (errorDiv) {
                // Format error message for display (replace \n with <br>)
                const errorMessage = error.message || 'เกิดข้อผิดพลาดในการสมัครสมาชิก';
                errorDiv.innerHTML = errorMessage.replace(/\n/g, '<br>');
                errorDiv.classList.add('show');
            }
            
            // Show toast notification
            showToast('ไม่สามารถสมัครสมาชิกได้ กรุณาตรวจสอบ Console', 'error');
        } finally {
            // Reset button state
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
        }
    });
}

