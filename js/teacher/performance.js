// /Student_Result_Analytics/js/teacher/performance.js
(function() {
    console.log('performance.js loaded');
    
    // Define renderPerformance FIRST
    function renderPerformance() {
        console.log('renderPerformance called');
        
        if (!window.classData) {
            console.log('No class data yet');
            return;
        }
        
        const data = window.classData;
        console.log('Rendering performance with data:', data);
        
        // Check if we're on a performance page
        if (!document.querySelector('.performance-stats') && !document.querySelector('#chart')) {
            console.log('Not on performance page, skipping');
            return;
        }
        
        // Calculate stats
        const marks = data.students.map(s => s.marks);
        const validMarks = marks.filter(m => m > 0);
        const avg = validMarks.length ? (validMarks.reduce((a,b) => a + b, 0) / validMarks.length).toFixed(1) : 0;
        const pass = validMarks.filter(m => m >= 40).length;
        const passRate = validMarks.length ? ((pass / validMarks.length) * 100).toFixed(0) : 0;
        const max = validMarks.length ? Math.max(...validMarks) : 0;
        const min = validMarks.length ? Math.min(...validMarks) : 0;
        
        // Update stats if they exist
        updateStatElement('avg-mark', avg);
        updateStatElement('pass-rate', passRate + '%');
        updateStatElement('max-mark', max);
        updateStatElement('min-mark', min);
        
        // Create chart
        if (validMarks.length > 0 && typeof Highcharts !== 'undefined') {
            createChart(data);
        }
    }
    
    // Helper functions
    function updateStatElement(id, value) {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = value;
        }
    }
    
    function createChart(data) {
        const chartElement = document.getElementById('chart');
        if (!chartElement) return;
        
        // Colors for chart
        const colors = data.students.map(s => {
            if (s.marks >= 80) return '#28a745';
            if (s.marks >= 60) return '#ffc107';
            if (s.marks >= 40) return '#fd7e14';
            if (s.marks > 0) return '#dc3545';
            return '#6c757d';
        });
        
        Highcharts.chart('chart', {
            chart: { type: 'column' },
            title: { text: 'Student Performance' },
            xAxis: { 
                categories: data.students.map(s => s.name),
                labels: { rotation: -30, style: { fontSize: '12px' } }
            },
            yAxis: {
                min: 0,
                max: 100,
                title: { text: 'Marks' },
                plotLines: [{
                    value: 40,
                    color: '#ffc107',
                    dashStyle: 'dash',
                    width: 2,
                    label: { text: 'Passing Mark', style: { color: '#666' } }
                }]
            },
            series: [{
                name: 'Marks',
                data: data.students.map(s => s.marks),
                colorByPoint: true,
                colors: colors
            }],
            credits: { enabled: false }
        });
        
        console.log('Chart created');
    }
    
    // Initialize when DOM is ready
    function initPerformance() {
        console.log('Initializing performance...');
        
        if (!window.classData) {
            console.log('No class data yet, will try again in 500ms');
            setTimeout(checkForData, 500);
            return;
        }
        
        renderPerformance();
    }
    
    function checkForData() {
        if (window.classData) {
            console.log('Class data found, rendering...');
            renderPerformance();
        } else {
            console.log('Still no class data');
        }
    }
    
    // Set up event listeners for initial load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPerformance);
    } else {
        initPerformance();
    }
    
    // Also listen for page loads via AJAX
    window.addEventListener('pageLoaded', initPerformance);
    
    // Make functions globally available
    window.renderPerformance = renderPerformance;
    window.initializePerformanceChart = function() {
        console.log('initializePerformanceChart called');
        if (window.classData) {
            renderPerformance();
        }
    };
    
    console.log('performance.js initialized, renderPerformance available:', typeof renderPerformance);
})();