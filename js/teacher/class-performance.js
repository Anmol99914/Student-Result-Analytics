// High Chart Stuffs:)

$(document).ready(function() {
    
    // Get data from PHP
    const performanceData = $('#performance-data').data();
    
    // Initialize all charts
    function initCharts() {
        performanceData.subjects.forEach(subject => {
            createStudentChart(subject);
        });
    }
    
    // Create Highcharts for a subject
    function createStudentChart(subject) {
        Highcharts.chart(`chart-${subject.id}`, {
            chart: { type: 'column' },
            title: { text: `${subject.name} - Student Performance` },
            xAxis: { categories: subject.studentNames },
            yAxis: {
                min: 0,
                max: 100,
                plotLines: [{
                    value: 40,
                    color: '#ffc107',
                    dashStyle: 'dash',
                    width: 2,
                    label: { text: 'Passing Marks' }
                }]
            },
            series: [{
                name: 'Marks',
                data: subject.studentMarks,
                colorByPoint: true,
                colors: subject.studentColors
            }],
            credits: { enabled: false }
        });
    }
    
    // Handle tab changes
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
        const target = $(e.target).data('bs-target');
        setTimeout(() => $(target).find('.highcharts-container').each(function() {
            $(this).highcharts().reflow();
        }), 100);
    });
    
    initCharts();
});