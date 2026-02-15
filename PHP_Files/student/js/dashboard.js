// File: PHP_Files/student/js/dashboard.js - Updated with new functionality

document.addEventListener('DOMContentLoaded', function() {
    console.log('Student Dashboard loaded');
    
    // Initialize dashboard
    initDashboard();
    
    // so that we can load homepage immediately bro:)
    loadHomePage();

    
    // Load home page by default if no content
    if (!document.querySelector('#main-content').innerHTML.trim()) {
        loadPage('../pages/home.php');
    }
});

function loadHomePage() {
    // Check if main-content is empty, then load home
    const mainContent = document.getElementById('main-content');
    if (mainContent && mainContent.innerHTML.trim() === '') {
        loadPage('../pages/home.php');
        
        // Set home link as active
        document.querySelectorAll('.ajax-link').forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === '../pages/home.php') {
                link.classList.add('active');
            }
        });
    }
}

/**
 * Set active navigation based on current URL
 */
function setActivePage() {
    const path = window.location.pathname;
    const page = path.split('/').pop() || 'home.php';
    
    // Remove active class from all links
    document.querySelectorAll('.ajax-link').forEach(link => {
        link.classList.remove('active');
    });
    
    // Add active class to current page link
    const activeLink = document.querySelector(`.ajax-link[href*="${page}"]`);
    if (activeLink) {
        activeLink.classList.add('active');
    }
}

/**
 * Initialize dashboard functionality
 */
function initDashboard() {
    // Set up navigation links
    setupNavigation();
    
    // Handle logout confirmation
    const logoutLinks = document.querySelectorAll('a[href*="logout"]');
    logoutLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (this.getAttribute('href').includes('logout')) {
                e.preventDefault();
                if (confirm('Are you sure you want to logout?')) {
                    window.location.href = this.getAttribute('href');
                }
            }
        });
    });
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Update last login time
    updateLastLoginTime();
}

/**
 * Update last login display
 */
function updateLastLoginTime() {
    const lastLoginElement = document.querySelector('.last-login-time');
    if (lastLoginElement) {
        const now = new Date();
        const timeString = now.toLocaleTimeString('en-US', { 
            hour: '2-digit', 
            minute: '2-digit',
            hour12: true 
        });
        lastLoginElement.textContent = timeString;
    }
}

/**
 * Load page content
 */
function loadPage(url) {
    console.log('Loading:', url);
    
    // Show loading
    showLoading();
    
    fetch(url)
        .then(response => {
            if (!response.ok) throw new Error('Failed to load');
            return response.text();
        })
        .then(html => {
            document.getElementById('main-content').innerHTML = html;
            hideLoading();
            
            // Update active link
            document.querySelectorAll('.ajax-link').forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === url) {
                    link.classList.add('active');
                }
            });
            
            // Initialize any page-specific scripts
            initPageScripts(url);
        })
        .catch(error => {
            console.error('Page load error:', error);
            document.getElementById('main-content').innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    Failed to load page. Please try again.
                </div>
            `;
            hideLoading();
        });
}

// Initialize page-specific scripts

function initPageScripts(pageUrl) {
    const page = pageUrl.split('/').pop();
    
    switch(page) {
        case 'home.php':
            initHomePage();
            break;
        case 'profile.php':
            initProfilePage();
            break;
        case 'results.php':
            initResultsPage();
            break;
        case 'payments.php':
            initPaymentsPage();
            break;
    }
    
    // Update page title
    updatePageTitle(page);
}

/**
 * Update page title in browser tab
 */
function updatePageTitle(page) {
    const titles = {
        'home.php': 'Dashboard',
        'profile.php': 'My Profile',
        'results.php': 'Academic Results',
        'payments.php': 'Fee Payments'
    };
    
    if (titles[page]) {
        document.title = `Student Portal - ${titles[page]}`;
    }
}

/**
 * Initialize home page
 */
function initHomePage() {
    console.log('Home page initialized');
    
    // Add event listeners for home page buttons
    document.querySelectorAll('#main-content .btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (this.classList.contains('stretched-link')) {
                // Handle card links
                const link = this.closest('.card').querySelector('a.stretched-link');
                if (link) {
                    loadPage(link.getAttribute('href'));
                }
            }
        });
    });
}

/**
 * Initialize profile page
 */
// File: PHP_Files/student/js/dashboard.js
/**
 * Initialize profile page
 */
function initProfilePage() {
    console.log('Profile page initialized');
    
    // Find email element by checking its content
    const formControls = document.querySelectorAll('.form-control.bg-light');
    let emailElement = null;
    
    formControls.forEach(element => {
        if (element.textContent && element.textContent.includes('@')) {
            emailElement = element;
        }
    });
    
    if (emailElement) {
        emailElement.addEventListener('click', function() {
            const email = this.textContent.trim();
            navigator.clipboard.writeText(email)
                .then(() => {
                    showToast('Email copied to clipboard!', 'success');
                })
                .catch(err => {
                    console.error('Failed to copy: ', err);
                    showToast('Failed to copy email', 'error');
                });
        });
        emailElement.style.cursor = 'pointer';
        emailElement.title = 'Click to copy';
        
        // Add visual feedback on hover
        emailElement.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#f0f8ff';
            this.style.borderColor = '#0d6efd';
        });
        
        emailElement.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
            this.style.borderColor = '';
        });
    }
}

/**
 * Initialize results page (placeholder)
 */
function initResultsPage() {
    console.log('Results page initialized');
    // Will be implemented in Phase 1
}

/**
 * Initialize payments page (placeholder)
 */
function initPaymentsPage() {
    console.log('Payments page initialized');
    // Will be implemented in Phase 1
}

/**
 * Show toast notification
 */
function showToast(message, type = 'info') {
    const toastContainer = document.getElementById('toast-container') || createToastContainer();
    
    const toastId = 'toast-' + Date.now();
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.id = toastId;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    toastContainer.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast, { delay: 3000 });
    bsToast.show();
    
    toast.addEventListener('hidden.bs.toast', function () {
        this.remove();
    });
}

/**
 * Create toast container if it doesn't exist
 */
function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'toast-container position-fixed top-0 end-0 p-3';
    container.style.zIndex = '9999';
    document.body.appendChild(container);
    return container;
}