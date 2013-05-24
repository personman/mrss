$(function() {

    // Show the equation field only when Computed is the chosen inputType
    toggleEquationDisplay()
    $('#inputType').change(function() {
        toggleEquationDisplay()
    })
})

function toggleEquationDisplay()
{
    inputType = $('#inputType').val()
    equationControl = $('#control-group-equation')

    if (inputType == 'computed') {
        equationControl.show()
    } else {
        equationControl.hide()
    }
}
