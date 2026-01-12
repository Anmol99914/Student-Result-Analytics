// verified-results.js - JavaScript for verified results functionality

class VerifiedResults {
    constructor() {
        this.currentPage = 1;
        this.faculty = '';
        this.semester = '';
        this.init();
    }
    
    init() {
        console.log('VerifiedResults initialized');
        this.bindEvents();
        this.loadStats();
    }
    
    bindEvents() {
        // Filter form submission
        const filterForm = document.getElementById('verified-filter-form');
        if (filterForm) {
            filterForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.faculty = document.getElementById('filter-faculty').value;
                this.semester = document.getElementById('filter-semester').value;
                this.loadPage(1);
            });
        }
        
        // Clear filters button
        const clearBtn = document.getElementById('clear-filters');
        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                document.getElementById('filter-faculty').value = '';
                document.getElementById('filter-semester').value = '';
                this.faculty = '';
                this.semester = '';
                this.loadPage(1);
            });
        }
        
        // Back to pending button
        const backBtn = document.querySelector('[onclick="loadResultsVerification()"]');
        if (backBtn) {
            backBtn.addEventListener('click', (e) => {
                e.preventDefault();
                if (typeof loadResultsVerification === 'function') {
                    loadResultsVerification();
                }
            });
        }
    }
    
    loadPage(page = 1) {
        this.currentPage = page;
        
        // Build URL with parameters
        let url = `Results/view_verified.php?page=${page}`;
        if (this.faculty) url += `&faculty=${encodeURIComponent(this.faculty)}`;
        if (this.semester) url += `&semester=${this.semester}`;
        
        console.log('Loading verified page:', url);
        
        // Use global loadPage function if available
        if (typeof loadPage === 'function') {
            loadPage(url);
        } else {
            // Fallback AJAX call
            this.fetchPage(url);
        }
    }
    
    fetchPage(url) {
        const mainContent = document.getElementById('main-content');
        if (!mainContent) return;
        
        // Show loading
        mainContent.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-success" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Loading verified results...</p>
            </div>
        `;
        
        fetch(url)
            .then(response => response.text())
            .then(html => {
                mainContent.innerHTML = html;
                this.init(); // Re-initialize for new content
            })
            .catch(error => {
                console.error('Error loading verified results:', error);
                mainContent.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        Error loading verified results. Please try again.
                    </div>
                `;
            });
    }
    
    loadStats() {
        // Load additional stats if needed
        fetch('Results/get_verification_stats.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.updateStats(data);
                }
            })
            .catch(error => console.warn('Stats load error:', error));
    }
    
    updateStats(data) {
        // Update any dynamic stats on the page
        const totalVerified = document.getElementById('total-verified-count');
        if (totalVerified && data.total_verified) {
            totalVerified.textContent = data.total_verified;
        }
    }
    
    // Public method to load a specific page
    static loadPage(page, faculty = '', semester = '') {
        const instance = new VerifiedResults();
        instance.faculty = faculty;
        instance.semester = semester;
        instance.loadPage(page);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    // Only initialize if we're on the verified results page
    if (document.getElementById('verified-results-page')) {
        window.verifiedResults = new VerifiedResults();
    }
});

// Global function for pagination links
window.loadVerifiedPage = function(page) {
    if (window.verifiedResults) {
        window.verifiedResults.loadPage(page);
    } else {
        // Create new instance
        const vr = new VerifiedResults();
        vr.loadPage(page);
    }
};

// Global function to load verified results
window.loadVerifiedResults = function() {
    console.log('Loading verified results...');
    if (typeof loadPage === 'function') {
        loadPage('Results/view_verified.php');
    } else {
        // Fallback
        const vr = new VerifiedResults();
        vr.loadPage(1);
    }
};