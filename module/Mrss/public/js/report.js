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
            $('.reportDetailRow').show().addClass('detailVisible')
            $(this).attr('title', 'Hide all charts')
        } else {
            $('.nationalReportChart').hide().removeClass('chartVisible')
            $('.reportDetailRow').hide().removeClass('detailVisible')
            $(this).attr('title', 'Show all charts')
        }

        return false
    })

    // Make tables sortable if they have the class sortable
    if ($('table.sortable').length) {
        $('table.sortable').dataTable({
            'bPaginate': false,
            'bLengthChange': false,
            'bFilter': false,
            'bInfo': false
        });
    }

    // Max national report detail expander
    setUpDetailExpander();

    // Add spinner for report item submits
    setUpExploreSpinner()

    setUpCancelAndPreview();
    fixChart()
})


function loadChart(event, chart)
{
    preventNegativeScale(chart);

    // Enable data labels when exporting
    if (chart.options.chart.forExport) {
        var data = chart.series[0].data
        var dataWithLabels = []
        var replaceData = true

        // Don't force labels for pie charts
        if (chart.options.chart.type == 'pie') {
            replaceData = false
        }

        // Also don't force labels if there are multiple series
        if (chart.series.length > 1) {
            replaceData = false
        }

        for (i in data) {
            var point = data[i]
            if (typeof point.dataLabels == 'undefined') {
                var labelConfig = {
                    enabled: true,
                    format: chart.options.tooltip.pointFormat.replace('point.y', 'y')
                }

                point.dataLabels = labelConfig
            }

            point.dataLabels.enabled = true
            dataWithLabels.push(point)
        }

        // This works without running setData and breaks now when setData is run. odd.
        if (false && replaceData) {
            chart.series[0].setData(dataWithLabels)
            chart.redraw()
        }


        // Add data definition when exporting
        // Get the chart size
        var width = chart.chartWidth
        var totalPadding = 50
        var lineHeight = 35 //35
        width = width - totalPadding

        var dataDef = chart.options.dataDefinition

        var add = 0
        if (dataDef) {
            add = Math.ceil(dataDef.length / 106) * lineHeight // 106 = line width, 35 = line height
        }

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

            var newY = add - getChartDefinitionOffset(chart)

            // Push the definition down for max charts
            if (chart.options.maximizingResources) {
                newY = newY + 10
            }

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

    function getChartDefinitionOffset(chart)
    {
        var offset = 15

        if (chart.options.peerComparison) {
            offset = -10
        }

        return offset
    }
}

function fixChart()
{
    setTimeout(function() {

        $("tspan").each(function () {
            var text = $(this).text();
            text = text.replace("$â€¦", "...");
            $(this).text(text);
        })
        },
        800
    )

}

function preventNegativeScale(chart)
{
    var extremes = chart.yAxis[0].getExtremes();

    if (extremes.min < 0 && extremes.dataMin >= 0) {
        chart.yAxis[0].setExtremes(0)
    }
}


function setUpDetailExpander()
{
    $('a.detailExpander').click(function() {
        $(this).parent().parent()
            .nextUntil('tr.topLevelBenchmark', 'tr.reportDetailRow')
            .not('.nationalReportChart')
            .toggle()
            .toggleClass('detailVisible')

        return false
    })
}

function setUpExploreSpinner()
{
    $('form#explore').submit(function() {
        $('#fieldset-buttons input').hide()
        $('#fieldset-buttons').html('<div class="loading"><img src="/img/loading.gif" /></div>')

        return true
    })
}

function setUpCancelAndPreview()
{
    $('#cancelButton').click(function() {
        $('#isCancel').val(1)
        return true
    })
    $('#previewButton').click(function() {
        $('#isPreview').val(1)
        return true
    })
}

function axisLabelFormatter(axis, formatter, otherAxis)
{
    var format = otherAxis.userOptions.labels.format;
    var formatted = formatter.call(axis);

    if (hasDecimal(axis.value)) {
        formatted = axis.value;

        if (format.slice(-1) == '%') {
            formatted += '%'
        }

        if (format.substring(0, 1) == '$') {
            formatted = '$' + formatted;
        }

    }


    return formatted;
}

function hasDecimal(number)
{
    return (Math.floor(number) != number);
}

function legendLabelFormatter(label, multiTrend)
{
    var formatted = label.name
//console.log(label.name)
    if (multiTrend) {
        var labelParts = label.name.split('|')
        //formatted = "<h4>" + labelParts[1] + "</h4><br>" + labelParts[0]
    }

    return formatted
}
