$(function() {
    // Colorize percentiles
    /*$('.percentileColor').each(function() {
        var td = $(this);
        var value = parseFloat(td.html());

        var breakPoints = [
            {max: 25, color: '#B30000'},
            {max: 75, color: '#000000'},
            {max: 100, color: '#00B300'}
        ];

        for (var i in breakPoints) {
            var point = breakPoints[i];

            if (value <= point.max) {
                td.css('color', point.color);
                break;
            }
        }
    })*/

    // Toggle charts on national report
    $('.openChart').click(function() {
        $(this).parent().parent().next().toggle()
        return false
    })

    // Show all charts
    $('.showAllCharts').click(function() {
        if ($(this).attr('title').search('Hide') == -1) {
            $('.nationalReportChart').show()
            $(this).attr('title', 'Hide all charts')
        } else {
            $('.nationalReportChart').hide()
            $(this).attr('title', 'Show all charts')
        }

        return false
    })
})

function loadChart(event, chart)
{
    // Enable data labels when exporting
    if (chart.options.chart.forExport) {
        var data = chart.series[0].data
        var dataWithLabels = []
        for (i in data) {
            var point = data[i]
            point.dataLabels.enabled = true
            dataWithLabels.push(point)
        }
        chart.series[0].setData(dataWithLabels)
        chart.redraw()

    }

}
