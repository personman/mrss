$(function() {
    // Show equation
    $('.showOutlierEquation').click(function() {
        $(this).parent().find('.outlierEquation').show()
        $(this).css('display', 'none !important')
        $(this).remove()
        return false
    })
})
