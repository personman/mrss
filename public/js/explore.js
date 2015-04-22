$(function() {

    setUpSelects()

    // Chart type
    updateFormForChartType()
    $('#inputType').change(function() {
        updateFormForChartType()
    })
})

function setUpSelects()
{
    $('#benchmark1, #benchmark2, #benchmark3').chosen({search_contains: true})
}

function updateFormForChartType()
{
    var chartType = $('#inputType').val()
    var sizeField = $('#control-group-benchmark3')

    if (chartType == 'bubble') {
        sizeField.slideDown()
    } else {
        sizeField.slideUp();
    }

}
