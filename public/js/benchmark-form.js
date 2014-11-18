$(function() {

    // Show the equation field only when Computed is the chosen inputType
    toggleEquationDisplay()
    $('#control-group-computed input').change(function() {
        toggleEquationDisplay()
    })
})

function toggleEquationDisplay()
{
    var computedLength = $('#control-group-computed input').length;
    var computed = $('#control-group-computed input').get(computedLength- 1).checked;
    var equationControl = $('#control-group-equation, #control-group-computeAfter')

    if (computed) {
        equationControl.show()
    } else {
        equationControl.hide()
    }
}
