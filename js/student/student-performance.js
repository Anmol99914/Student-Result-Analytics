// student-performance.js - DEBUG VERSION
console.log('🔍 1. student-performance.js loaded - starting debug');

(function() {
    console.log('🔍 2. Self-executing function started');
    
    // Check if Highcharts is loaded
    if (typeof Highcharts === 'undefined') {
        console.error('Highcharts not loaded!');
    } else {
        console.log('Highcharts loaded, version:', Highcharts.version);
    }
    
    // Check for student data
    console.log('3. Checking window.studentData:', window.studentData);
    
    if (!window.studentData) {
        console.log('No studentData yet, will retry...');
        setTimeout(arguments.callee, 500);
        return;
    }
    
    console.log('4. studentData found:', window.studentData);
    console.log('   Semester data:', window.studentData.semester);
    console.log('   Subject data:', window.studentData.subject);
    
    // Check DOM elements
    setTimeout(function() {
        console.log('5. Checking DOM elements...');
        
        const chartEl = document.getElementById('performanceChart');
        console.log('   Chart element:', chartEl ? 'Found' : ' Not found');
        
        const avgEl = document.getElementById('avg-mark');
        console.log('   avg-mark element:', avgEl ? 'Found' : 'Not found');
        
        const buttons = document.querySelectorAll('.btn-outline-primary');
        console.log('   Buttons found:', buttons.length);
        
        if (!chartEl) {
            console.error('Chart element missing!');
            return;
        }
        
        console.log('6. Setting up buttons...');
        buttons.forEach(btn => {
            btn.addEventListener('click', function() {
                console.log('Button clicked:', this.dataset.view);
                buttons.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                renderView(this.dataset.view);
            });
        });
        
        // Try to render with semester data
        console.log('7. Attempting initial render...');
        renderView('semester');
        
    }, 500);
    
    function renderView(view) {
        console.log('8. renderView called with:', view);
        
        const viewData = window.studentData[view];
        console.log('   View data:', viewData);
        
        const chartContainer = document.getElementById('performanceChart');
        console.log('   Chart container:', chartContainer);
        
        if (!viewData || viewData.length === 0) {
            console.log('No data for view:', view);
            if (chartContainer) {
                chartContainer.innerHTML = '<div class="text-center py-5"><i class="bi bi-bar-chart display-1 text-muted"></i><h5 class="mt-3">No Data Available</h5></div>';
            }
            
            // Reset stats
            const avgEl = document.getElementById('avg-mark');
            const passEl = document.getElementById('pass-rate');
            const maxEl = document.getElementById('max-mark');
            const minEl = document.getElementById('min-mark');
            
            if (avgEl) avgEl.textContent = '-';
            if (passEl) passEl.textContent = '-';
            if (maxEl) maxEl.textContent = '-';
            if (minEl) minEl.textContent = '-';
            return;
        }
        
        // Calculate stats
        console.log('9. Calculating stats...');
        const percentages = viewData.map(item => item.percentage);
        console.log('   Percentages:', percentages);
        
        const validPercentages = percentages.filter(p => p > 0);
        console.log('   Valid percentages:', validPercentages);
        
        const avg = validPercentages.length ? 
            (validPercentages.reduce((a,b) => a + b, 0) / validPercentages.length).toFixed(1) : 0;
        const pass = validPercentages.filter(p => p >= 40).length;
        const passRate = validPercentages.length ? 
            ((pass / validPercentages.length) * 100).toFixed(0) : 0;
        const max = validPercentages.length ? Math.max(...validPercentages) : 0;
        const min = validPercentages.length ? Math.min(...validPercentages) : 0;
        
        console.log('   Stats - Avg:', avg, 'PassRate:', passRate, 'Max:', max, 'Min:', min);
        
        // Update stats
        document.getElementById('avg-mark').textContent = avg;
        document.getElementById('pass-rate').textContent = passRate + '%';
        document.getElementById('max-mark').textContent = max;
        document.getElementById('min-mark').textContent = min;
        
        // Update chart title
        const titleEl = document.getElementById('chartTitle');
        if (titleEl) {
            titleEl.textContent = view === 'semester' ? 'Semester-wise Performance' : 'Subject-wise Performance';
        }
        
        // Colors for chart
        const colors = viewData.map(item => {
            if (item.percentage >= 80) return '#28a745';
            if (item.percentage >= 60) return '#ffc107';
            if (item.percentage >= 40) return '#fd7e14';
            if (item.percentage > 0) return '#dc3545';
            return '#6c757d';
        });
        
        console.log('10. Creating Highcharts with data:', {
            categories: viewData.map(item => item.name),
            data: viewData.map(item => item.percentage),
            colors: colors
        });
        
        // Create chart
        try {
            Highcharts.chart('performanceChart', {
                chart: {
                    type: view === 'semester' ? 'column' : 'bar'
                },
                title: {
                    text: null
                },
                xAxis: {
                    categories: viewData.map(item => item.name),
                    labels: {
                        rotation: view === 'semester' ? -30 : 0,
                        style: { fontSize: '12px' }
                    }
                },
                yAxis: {
                    min: 0,
                    max: 100,
                    title: {
                        text: 'Percentage (%)'
                    },
                    plotLines: [{
                        value: 40,
                        color: '#ffc107',
                        dashStyle: 'dash',
                        width: 2,
                        label: {
                            text: 'Passing Mark',
                            style: { color: '#666' }
                        }
                    }]
                },
                series: [{
                    name: view === 'semester' ? 'Average Percentage' : 'Percentage',
                    data: viewData.map(item => item.percentage),
                    colorByPoint: true,
                    colors: colors
                }],
                credits: { enabled: false }
            });
            console.log('11. Highcharts rendered successfully');
        } catch (e) {
            console.error('Highcharts error:', e);
        }
    }
})();