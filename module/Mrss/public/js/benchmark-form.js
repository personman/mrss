$(function() {

    // Show the equation field only when Computed is the selected
    toggleEquationDisplay()
    $('#control-group-computed input').change(function() {
        toggleEquationDisplay()
    })

    // Show the options field only when radio or checckboxes are the chosen inputType
    toggleOptionsDisplay()
    $('#inputType').change(function() {
        toggleOptionsDisplay()
    })

    validateEquation();
    $('#equation').keyup(function() {
        validateEquation();
    });
});

function toggleEquationDisplay()
{
    var computedLength = $('#control-group-computed input').length;
    var computed = $('#control-group-computed input').get(computedLength- 1).checked;
    var equationControl = $('#control-group-equation, #control-group-computeAfter, #control-group-computeIfValuesMissing')

    if (computed) {
        equationControl.show()
    } else {
        equationControl.hide()
    }
}

function toggleOptionsDisplay()
{
    var inputType = $('#inputType').val();

    var typeWithOptions = ['radio', 'checkboxes'];

    var showOptions = (typeWithOptions.indexOf(inputType) > -1);
    var optionControls = $('#control-group-options');

    if (showOptions) {
        optionControls.show()
    } else {
        optionControls.hide()
    }
}

function validateEquation()
{
    var url = '/benchmark/check-equation';

    var equation = $('#equation').val();

    var data = {equation: equation};

    $.post(url, data, function(results) {
        var message = '';
        var color;

        if (results.result) {
            message = 'Equation is valid.'
            color = 'green';
        } else {
            message = 'Invalid equation: ' + results.error;
            color = 'red';
        }

        $('#equationValidationMessage').css('color', color).html(message);
    });
    //console.log(equation);
}
