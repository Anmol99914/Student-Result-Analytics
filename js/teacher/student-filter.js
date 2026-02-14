// student-filter.js - Dedicated filter functionality
console.log('📁 Student filter JS loaded');

(function() {
    'use strict';
    
    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initFilter);
    } else {
        initFilter();
    }
    
    function initFilter() {
        console.log('🔧 Initializing student filter');
        
        const filterButtons = document.querySelectorAll('.filter-btn');
        const searchInput = document.getElementById('search-student');
        const tableRows = document.querySelectorAll('#students-table tbody tr');
        
        if (filterButtons.length === 0) {
            console.warn('No filter buttons found');
            return;
        }
        
        if (tableRows.length === 0) {
            console.warn('No table rows found');
            return;
        }
        
        console.log(`Found ${filterButtons.length} buttons and ${tableRows.length} rows`);
        
        // Filter by class
        filterButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                const filterValue = this.dataset.filter;
                console.log('Filter clicked:', filterValue);
                
                // Update active state
                filterButtons.forEach(btn => {
                    btn.classList.remove('btn-primary', 'active');
                    btn.classList.add('btn-outline-secondary');
                });
                this.classList.add('btn-primary', 'active');
                this.classList.remove('btn-outline-secondary');
                
                // Apply filter
                applyFilters();
            });
        });
        
        // Search functionality
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                console.log('Searching:', this.value);
                applyFilters();
            });
            
            searchInput.addEventListener('search', function() {
                // Handle clear button click
                applyFilters();
            });
        }
        
        function applyFilters() {
            const activeFilter = document.querySelector('.filter-btn.active')?.dataset.filter || 'all';
            const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';
            
            let visibleCount = 0;
            
            tableRows.forEach(row => {
                // Get class ID from data attribute
                const classId = row.getAttribute('data-class-id');
                const classFilterValue = classId ? 'class-' + classId : '';
                
                // Get text content from cells
                const cells = row.querySelectorAll('td');
                if (cells.length < 6) return;
                
                const idCell = cells[0]?.textContent.toLowerCase() || '';
                const nameCell = cells[1]?.textContent.toLowerCase() || '';
                const emailCell = cells[2]?.textContent.toLowerCase() || '';
                
                // Check class filter
                const matchesClass = activeFilter === 'all' || classFilterValue === activeFilter;
                
                // Check search filter
                let matchesSearch = true;
                if (searchTerm !== '') {
                    matchesSearch = nameCell.includes(searchTerm) || 
                                   idCell.includes(searchTerm) || 
                                   emailCell.includes(searchTerm);
                }
                
                // Show/hide row
                if (matchesClass && matchesSearch) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            console.log(`Visible rows: ${visibleCount}/${tableRows.length}`);
        }
        
        // Initial filter to show all
        applyFilters();
    }
})();