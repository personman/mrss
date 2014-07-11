// Since the chart view helper is fed a php-built json object, which can't contain functions,
// we need to set up anonymous formatter functions after the fact in js

function addFormatters(chartConfig)
{
    // get dataType
    var dataType = extractDataType(chartConfig)

    // yAxis
    if (typeof chartConfig.yAxis != 'undefined') {
        if (typeof chartConfig.yAxis.labels == 'undefined') {
            chartConfig.yAxis.labels = {}
        }

        chartConfig.yAxis.labels.formatter = function() {
            var formatted = Highcharts.numberFormat(this.value, 0, '', ',');

            formatted = addDataTypeLabel(formatted, dataType)

            return formatted
        }
    }

    // Your college
    $.each(chartConfig.series, function(i, series) {
        $.each(series, function(i2, dataPoints) {
            if (typeof dataPoints == 'object') {
                $.each(dataPoints, function(i3, dataPoint){

                    if (typeof dataPoint.dataLabels == 'object' && dataPoint.dataLabels.enabled) {
                        dataPoint.dataLabels.format = null
                        dataPoint.dataLabels.formatter = function()
                        {
                            var formatted = Highcharts.numberFormat(this.y, 0, '', ',');
                            formatted = addDataTypeLabel(formatted, dataType)
                            return formatted
                        }
                    }
                })
            }
        })
    })

    return chartConfig
}

function extractDataType(chartConfig)
{
    var dataType = ''

    if (typeof chartConfig.yAxis != 'undefined' && typeof chartConfig.yAxis.labels != 'undefined') {
        if (typeof chartConfig.yAxis.labels.format != 'undefined') {
            if (chartConfig.yAxis.labels.format.indexOf('%') > -1) {
                dataType = '%'
            } else if (chartConfig.yAxis.labels.format.indexOf('$') > -1) {
                dataType = '$'
            }
        }
    }

    return dataType
}

function addDataTypeLabel(value, dataType)
{
    if (dataType == '$') {
        value = '$' + value
    } else if (dataType == '%') {
        value = value + '%'
    }

    return value
}
