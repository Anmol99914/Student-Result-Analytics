// File: PHP_Files/student/js/results.js
// Student Results Page JavaScript

(function() {
    console.log('Results.js initializing...');
    
    function initResultsPage() {
        // Initialize tooltips
        const tooltips = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltips.map(t => new bootstrap.Tooltip(t));
        console.log('Results page initialized');
    }
    
    // Run when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initResultsPage);
    } else {
        initResultsPage();
    }
})();