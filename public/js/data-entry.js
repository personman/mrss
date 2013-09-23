var formErrorMessages = {}

$(function() {
    // Some special formatting for form 9
    $('#control-group-institutional_demographics_companies_less_than_50').before(
        '<h3>Companies by employee size:</h3>' +
            '<p>Companies by size (Use the US Economic Census, County Business Patterns to obtain these data)</p>'
    )

    // Type hints:
    // Dollars
    $('input.input-dollars').wrap('<div class="input-prepend" />')
    $('input.input-dollars').before('<span class="add-on">$</span>')

    $('input.input-wholedollars').wrap('<div class="input-prepend" />')
    $('input.input-wholedollars').before('<span class="add-on">$</span>')

    // Percentage
    $('input.input-percent, input.input-wholepercent').wrap('<div class="input-append" />')
    $('input.input-percent, input.input-wholepercent').after('<span class="add-on">%</span>')


    // Wrap selected input in well to highlight it
    if (!$('.data-entry-grid').length) {
        $('.form-horizontal input, .form-horizontal select')
            .focus(function(){
                $(this).parents('.control-group').addClass('focus');
            }).blur(
            function(){
                $(this).parents('.control-group').removeClass('focus');
            });
    }


    // Show grid help text
    var submitClicked = false
    $('.data-entry-grid input')
        .focus(function() {
            var helpRow = $(this).parents('tr').next()
            var helpType = getHelpType($(this))
            helpRow.find('div').hide()

            var helpDiv = helpRow.find('.' + helpType + '-help')

            if (helpDiv.html() && helpDiv.html().trim().length) {
                helpDiv.show()
                helpRow.show()
            }


        }).blur(function() {
            if (!submitClicked && !$(this).attr('id').search('other_specify')) {
                $(this).parents('tr').next().hide()
            }
        });

    // This prevents the hiding of the help text when clicking a button
    // That was causing the button to jump away from the click.
    $('.btn').mousedown(function() {
        submitClicked = true
    })

    // Managerial grid page
    if ($('.data-entry-grid').length) {

        // Totals for managerial grid
        updateGridTotals()
        $('.data-entry-grid input').change(function() {
            updateGridTotals()
        })

        // Specify fields: only show them when there's a value in other
        showOrHideSpecifyFields()
        $('.other-field').change(function() {
            showOrHideSpecifyFields()
        })
    }

    // Demographics
    if ($('#race-ethnicity').length) {
        updateRaceTotal()
        $('#race-ethnicity input').change(function() {
            updateRaceTotal()
        })
    }

    // Workforce revenue heading
    if ($('#control-group-revenue_federal').length) {
        $('#control-group-revenue_federal').before("<h3 class='heading'>Revenue Base</h3><h4 class='subheading'>Public Sources</h4>")
        $('#control-group-revenue_contract_training').before("<h3 class='heading'>Gross Revenue</h3>")
    }

    // Let's allow for some ad-hoc form validation
    $('form').submit(function() {

        if (Object.keys(formErrorMessages).length) {
            var message;
            message = ''
            for (var prop in formErrorMessages) {
                message = message + formErrorMessages[prop] + '  '
            }

            alert(message)
            return false
        }
    })
})

function updateGridTotals()
{
    var columns = ['full', 'part', 'other']
    for (i in columns) {
        var column = columns[i]
        // The inputs in the column:
        var inputs = $('.data-entry-grid td.' + column + '-value input')
        var total = 0
        inputs.each(function(i, e) {
            value = parseFloat($(e).val())
            if (!value) {
                value = 0
            }
            total = total + value
        })

        // Show the total
        var totalTd = $('#' + column + '-time-total')
        totalTd.html(total + '%')

        // Handle errors
        if (total > 100) {
            totalTd.addClass('error')
            totalTd.html(totalTd.html() + "<br>Total should be 100% or less.")
            formErrorMessages['percent' + column] = 'Total of percentages should be 100% or less'
        } else {
            totalTd.removeClass('error')
            delete formErrorMessages['percent' + column]
        }

    }
}

function showOrHideSpecifyFields()
{
    // Which other fields have a non-zero value?
    fields = ['full', 'part', 'other']
    activeFields = [];

    for (i in fields) {
        field = fields[i]
        selector = '.' + field + '-value.other-field input'
        value = $(selector).val()

        value = parseFloat(value)

        if (value) {
            activeFields.push(field)
        }
    }

    // If there are no values for other, hide the whole specify row
    if (activeFields.length == 0) {
        $('#specify-other').hide()
    } else {
        $('#specify-other').show()
    }
}


function getHelpType(element)
{
    td = element.parents('td.grid-value')

    if (td.length) {
        helpType = td.attr('id').split('-').shift()

        return helpType
    }
}

function updateRaceTotal()
{
    var total = 0
    $('#race-ethnicity input').each(function() {

        var percent = parseFloat($(this).val())

        if (isNaN(percent)) {
            percent = 0
        }

        total = total + percent
    })

    // Validation
    if (total > 100) {
        $('#race-ethnicity-total').addClass('error')
        formErrorMessages.race = "Race/ethnicity percentages must total to 100% or less"
    } else {
        $('#race-ethnicity-total').removeClass('error')
        delete formErrorMessages.race
    }

    $('#race-ethnicity-total-value').html(total)

}
