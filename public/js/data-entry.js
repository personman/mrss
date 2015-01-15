var formErrorMessages = {}

$(function() {
    setupHelpBlocks()

    // Type hints:
    // Dollars
    $('input.input-dollars').addClass('form-control')
    $('input.input-dollars').wrap('<div class="input-prepend input-group" />')
    $('input.input-dollars').before('<span class="add-on input-group-addon">$</span>')

    $('input.input-wholedollars').addClass('form-control')
    $('input.input-wholedollars').wrap('<div class="input-prepend input-group" />')
    $('input.input-wholedollars').before('<span class="add-on input-group-addon">$</span>')

    // Percentage
    $('input.input-percent, input.input-wholepercent').addClass('form-control')
    $('input.input-percent, input.input-wholepercent').wrap('<div class="input-append input-group" />')
    $('input.input-percent, input.input-wholepercent').after('<span class="add-on input-group-addon">%</span>')


    // Wrap selected input in well to highlight it
    //if (!$('.data-entry-grid').length) {
        $('.form-horizontal input, .form-horizontal select')
            .focus(function(){
                $(this).parents('.control-group').addClass('focus');
            }).blur(
            function(){
                $(this).parents('.control-group').removeClass('focus');
            });
    //}


    // Show grid help text
    var submitClicked = false
    $('.data-entry-grid input')
        .focus(function() {
            var helpRow = $(this).parents('tr').next()
            var helpType = getHelpType($(this))
            helpRow.find('div').hide()

            var helpDiv = helpRow.find('.' + helpType + '-help')

            // Hide help label
            helpDiv.find('h5').hide()

            if (helpDiv && helpDiv.html() && helpDiv.html().trim().length) {
                helpDiv.show()
                helpRow.show()
            }
        }).blur(function() {
            if (!submitClicked && $(this).attr('id').search('other_specify') == -1) {
                $(this).parents('tr').next().hide()
            }
        });

    // This prevents the hiding of the help text when clicking a button
    // That was causing the button to jump away from the click.
    $('.btn').mousedown(function() {
        submitClicked = true
    })

    // Managerial grid page
    if ($('.data-entry-grid.managerial').length) {

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

    addWorkForceCustomizations()


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


    // Alert when deleting a subobservation
    $('.academicUnits .deleteLink').click(function() {
        return confirm(
            'Are you sure you want to delete this academic unit? Your data for' +
                ' the academic unit will be permanently deleted.'
        )
    })

    // Highlight the input whose id is in the url, if any
    if (hash = window.location.hash) {
        if (input = $(hash)) {
            input.focus();
            input.css('border', '1px solid #0088cc')
        }
    }

})

function updateGridTotals()
{
    var columns = ['full', 'part']
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
        if (total != 100) {
            totalTd.addClass('error')
            totalTd.html(totalTd.html() + "<br>Total should be<br> 100%.")
            formErrorMessages['percent' + column] = 'Total of percentages should be 100%'
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

    total = Math.round(total);

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

function addWorkForceCustomizations()
{
    // Workforce revenue heading, form 5
    if ($('#control-group-revenue_federal').length) {
        $('#control-group-revenue_federal').before("<h3 class='heading'>Revenue Base</h3><h4 class='subheading'>Public Sources</h4>")
        $('#control-group-revenue_contract_training').before("<h3 class='heading'>Gross Revenue</h3>")
        $('#control-group-revenue_total').before("<h3 class='heading'>Total Gross Revenue</h3>")
    }


    // Form 6 expenditures headings
    if ($('#control-group-expenditures_salaries').length) {
        $('#control-group-expenditures_salaries').before("<h3 class='heading'>Total Expenditures For</h3>")
    }

    if ($('#control-group-expenditures_contract_training').length) {
        $('#control-group-expenditures_contract_training').before("<h3 class='heading'>Expenditures For</h3>")
    }

    if ($('#control-group-expenditures_total').length) {
        $('#control-group-expenditures_total').before("<h3 class='heading'>Total Expenditures</h3>")
    }

    if ($('#control-group-expenditures_overhead').length) {
        $('#control-group-expenditures_overhead').before("<h3 class='heading'>Overhead</h3>")
    }


    // Form 7
    if ($('#control-group-retained_revenue_contract_training').length) {
        $('#control-group-retained_revenue_contract_training').before("<h3 class='heading'>Retained Revenue For</h3>")
    }
    if ($('#control-group-retained_revenue_total').length) {
        $('#control-group-retained_revenue_total').before("<h3 class='heading'>Total Retained Revenue</h3>")
    }

    // Some special formatting for form 9
    if ($('#control-group-institutional_demographics_credit_enrollment').length) {
        $('#control-group-institutional_demographics_credit_enrollment').before("<h3 class='heading'>Campus Characteristics</h3>")
    }

    if ($('#control-group-institutional_demographics_total_population').length) {
        $('#control-group-institutional_demographics_total_population').before("<h3 class='heading'>Service Area Characteristics</h3><p class='heading-notes'>Use your institution's legal definition of service area and most recent U.S. Census estimates.</p>")
    }

    if ($('#control-group-institutional_demographics_credentials_awarded').length) {
        $('#control-group-institutional_demographics_credentials_awarded').before("<h3 class='heading'>Credentials Awarded</h3>")
    }

    $('#control-group-institutional_demographics_companies_less_than_50').before(
        '<h4 class="subheading">Companies by Employee Size</h4>' +
            '<p class="subheading-notes">Companies by size (Use the US Economic Census, County Business Patterns to obtain these data)</p>'
    )

    if ($('#control-group-enrollment_information_duplicated_enrollment').length) {
        $('label.control-label').each(function() {
            label = $(this).html()
            label = label.replace(
                '(for the 2012/2013 fiscal year)',
                '<br><span class="lightLabel">(for the 2012/2013 fiscal year)</span>'
            )
            $(this).html(label)
        })
    }


}


// Because IE doesn't support trim() prior to version 9
if(typeof String.prototype.trim !== 'function') {
    String.prototype.trim = function() {
        return this.replace(/^\s+|\s+$/g, '');
    }
}

function setupHelpBlocks()
{
    // Hide empty help-blocks
    $('.form-horizontal .help-block').each(function(i, e) {
        var val = $(this).html()
        if (!val) {
            $(this).remove()
        } else {
            // Move help-blocks up one level in the dom
            $(this).appendTo($(this).parent().parent())

            // Change the appearance if there's no definition (just last year's value)
            var html = $(this).html()
            if (html.substr(-8) == '</span> ') {
                $(this).css('background', 'transparent').css('border', 'transparent')
            }

        }
    })

    // Help blocks (data definitions)
    updateHelpBlocks()
    $('#dataDefinitions').change(function() {
        updateHelpBlocks()

        // Save the setting for the user
        var value = $('#dataDefinitions').val()
        $.post('/account/definitions', {definitions: value}, function(data) {
            // Nothing to do
        })
    })
}

function updateHelpBlocks()
{
    var value = $('#dataDefinitions').val()

    if (value == 'show') {
        $('.help-block').show()
    } else if (value == 'hide') {
        $('.help-block').hide()
    } else {
        // Hide, then activate focus
        $('.help-block').hide()

        $('input, select, textarea').focus(function() {
            // Add the if statement in case the setting changes
            var value = $('#dataDefinitions').val()
            if (value == 'active') {
                // Hide any previous help text first
                $('.help-block').hide()
                $(this).parents('.control-group').find('.help-block').show()
            }
        })
    }
}
