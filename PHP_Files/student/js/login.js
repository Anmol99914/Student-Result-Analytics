// File: PHP_Files/student/js/login.js
// Student Login Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('studentForm');
    const loginBtn = document.getElementById('loginBtn');
    const rememberCheckbox = document.getElementById('remember');
    
    if (!form) return;
    
    // Auto-focus on Student ID field
    document.getElementById('username').focus();
    
    // Floating labels - Improved version
    const floatingInputs = document.querySelectorAll('.floating-label .form-control');
    floatingInputs.forEach(input => {
        // Set placeholder to empty string for floating labels
        input.setAttribute('placeholder', ' ');
        
        // Check if input has value on page load
        if (input.value.trim() !== '') {
            input.parentElement.classList.add('has-value');
            input.nextElementSibling.classList.add('active');
        }
        
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
            this.nextElementSibling.classList.add('active');
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
            if (this.value.trim() === '') {
                this.nextElementSibling.classList.remove('active');
                this.parentElement.classList.remove('has-value');
            } else {
                this.parentElement.classList.add('has-value');
            }
        });
        
        // Auto-uppercase for Student ID
        if (input.id === 'username') {
            input.addEventListener('input', function() {
                this.value = this.value.toUpperCase();
                if (this.value.trim() !== '') {
                    this.nextElementSibling.classList.add('active');
                    this.parentElement.classList.add('has-value');
                }
            });
        }
    });
    
    // Student ID format validation
    const studentIdInput = document.getElementById('username');
    studentIdInput.addEventListener('input', function() {
        // Auto-uppercase
        this.value = this.value.toUpperCase();
        
        // Accept: 3+ letters + 2-3 numbers (e.g., BCA01, BCA001, BSC10, BSC.CSIT10)
        const isValid = /^[A-Z]{3,}(\.?[A-Z]{0,4})?\d{2,3}$/.test(this.value);
        
        if (this.value.length > 0 && !isValid) {
            this.setCustomValidity('Error! Format: 3+ letters + 2-3 numbers (e.g., BCA01, BCA001, BSC10, BSC.CSIT10)');
            
            // Update the invalid feedback message
            const feedbackDiv = this.closest('.mb-4')?.querySelector('.invalid-feedback');
            if (feedbackDiv) {
                feedbackDiv.textContent = 'Format: 3+ letters + 2-3 numbers (e.g., BCA01, BCA001, BSC.CSIT10)';
            }
        } else {
            this.setCustomValidity('');
        }
    });
    
    // Form submission
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Get values
        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value.trim();
        
        // Remove previous validation states
        document.getElementById('username').classList.remove('is-invalid');
        document.getElementById('password').classList.remove('is-invalid');
        form.classList.remove('was-validated');
        
        let isValid = true;
        
        // Check if fields are empty FIRST
        if (username === '') {
            document.getElementById('username').classList.add('is-invalid');
            document.getElementById('username').setCustomValidity('Please enter your Student ID');
            
            // Update feedback message
            const feedbackDiv = document.getElementById('username').closest('.mb-4')?.querySelector('.invalid-feedback');
            if (feedbackDiv) {
                feedbackDiv.textContent = 'Please enter your Student ID';
            }
            isValid = false;
        }
        
        if (password === '') {
            document.getElementById('password').classList.add('is-invalid');
            document.getElementById('password').setCustomValidity('Please enter your password');
            
            // Update feedback message
            const feedbackDiv = document.getElementById('password').closest('.mb-4')?.querySelector('.invalid-feedback');
            if (feedbackDiv) {
                feedbackDiv.textContent = 'Please enter your password';
            }
            isValid = false;
        }
        
        // If empty fields, show error and stop
        if (!isValid) {
            form.classList.add('was-validated');
            return;
        }
        
        // Check format (only if not empty)
        const formatValid = /^[A-Z]{3,}(\.?[A-Z]{0,4})?\d{2,3}$/.test(username.toUpperCase());
        if (!formatValid) {
            document.getElementById('username').classList.add('is-invalid');
            document.getElementById('username').setCustomValidity('Format: 3+ letters + 2-3 numbers (e.g., BCA01, BCA001, BSC.CSIT10)');
            
            // Update feedback message
            const feedbackDiv = document.getElementById('username').closest('.mb-4')?.querySelector('.invalid-feedback');
            if (feedbackDiv) {
                feedbackDiv.textContent = 'Format: 3+ letters + 2-3 numbers (e.g., BCA01, BCA001, BSC.CSIT10)';
            }
            
            form.classList.add('was-validated');
            return;
        }
        
        // Show loading state
        const originalText = loginBtn.innerHTML;
        loginBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Logging in...';
        loginBtn.disabled = true;
        loginBtn.style.opacity = '0.8';
        
        try {
            const formData = new FormData(form);
            
            // Make login request
            const response = await fetch('../api/login_validate.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });
            
            if (response.redirected) {
                // Redirect to dashboard
                window.location.href = response.url;
            } else {
                const result = await response.text();
                
                // Check if login was successful
                if (result.includes('Location:')) {
                    // PHP redirected - follow it
                    window.location.href = '../pages/dashboard.php';
                } else {
                    // Show error
                    window.location.href = 'login.php?error=invalid';
                }
            }
        } catch (error) {
            console.error('Login error:', error);
            window.location.href = 'login.php?error=network';
        } finally {
            // Reset button after delay
            setTimeout(() => {
                loginBtn.innerHTML = originalText;
                loginBtn.disabled = false;
                loginBtn.style.opacity = '1';
            }, 2000);
        }
    });
    
    // Button hover effects
    loginBtn.addEventListener('mouseenter', function() {
        if (!this.disabled) {
            this.style.transform = 'translateY(-2px)';
        }
    });
    
    loginBtn.addEventListener('mouseleave', function() {
        if (!this.disabled) {
            this.style.transform = 'translateY(0)';
        }
    });
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Enter to submit (unless in textarea)
        if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
            if (!e.ctrlKey && !e.shiftKey) {
                form.dispatchEvent(new Event('submit'));
            }
        }
    });
    
    // Remember me functionality
    if (rememberCheckbox) {
        // Check if credentials are saved
        const savedUsername = localStorage.getItem('student_username');
        const savedRemember = localStorage.getItem('student_remember') === 'true';
        
        if (savedRemember && savedUsername) {
            studentIdInput.value = savedUsername;
            rememberCheckbox.checked = true;
            studentIdInput.nextElementSibling.classList.add('active');
        }
        
        // Save on change
        rememberCheckbox.addEventListener('change', function() {
            if (this.checked && studentIdInput.value) {
                localStorage.setItem('student_username', studentIdInput.value);
                localStorage.setItem('student_remember', 'true');
            } else {
                localStorage.removeItem('student_username');
                localStorage.removeItem('student_remember');
            }
        });
    }
});