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
        var row = $(this).parent().parent().next()
        row.toggle()
        row.toggleClass('chartVisible')
        return false
    })

    // Show all charts
    $('.showAllCharts').click(function() {
        if ($(this).attr('title').search('Hide') == -1) {
            $('.nationalReportChart').show().addClass('chartVisible')
            $(this).attr('title', 'Hide all charts')
        } else {
            $('.nationalReportChart').hide().removeClass('chartVisible')
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


        // Add data definition when exporting
        // Get the chart size
        var width = chart.chartWidth
        var totalPadding = 50
        width = width - totalPadding

        var dataDef = chart.options.dataDefinition

        var add = Math.ceil(dataDef.length / 106) * 35 // 106 = line width, 35 = line height

        if (dataDef) {
            var definition = chart.renderer.label(dataDef)
                .css({
                    width: width + 'px',
                    color: '#BBB',
                    fontSize: '10px'
                })
                .attr({
                    'padding': 10
                })
                .add();

            var newY = add - 15

            definition.align(Highcharts.extend(definition.getBBox(), {
                x: 10,
                y: newY,
                align: 'left',
                verticalAlign: 'bottom'
            }), null, 'spacingBox')


             // Add some height
             var height = chart.chartHeight
             var width = chart.chartWidth
             height = height + add
             chart.setSize(width, height)

             // add some spacingBottom: done in Report.php
        }
    }
}
