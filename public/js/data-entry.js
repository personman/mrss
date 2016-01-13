var formErrorMessages = {}

$(function() {
    setupHelpBlocks()

    trackFormChanges()

    setupForm2Totals();

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
            'Are you sure you want to delete this academic division? Your data for' +
                ' the academic division will be permanently deleted.'
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
        if (total != 100 && total != 0) {
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
    workforceShowHideOtherSetup()
    workforceRevenueTotal()
    workforceFormFormatting()
}

function workforceFormFormatting()
{
    // Workforce revenue heading, form 5
    if ($('#control-group-revenue_federal').length) {
        $('#control-group-revenue_federal').before("<h4 class='subheading'>Public Sources</h4>")
    }

    // Some special formatting for form 9
    $('#control-group-institutional_demographics_companies_less_than_50').before(
        '<h4 class="subheading">Companies by Employee Size</h4>' +
            '<p class="subheading-notes">Companies by size (Use the US Economic Census, County Business Patterns to obtain these data).  Note:  Should add to total service area companies.</p>'
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

function workforceRevenueTotal()
{
    var earnedRevGroup = $('#control-group-revenue_earned_revenue');

    if (earnedRevGroup.length) {
        earnedRevGroup.after('<div class="control-group">' +
            '<div class="control-label">Total</div>' +
            '<div class="controls"><div id="revTotal"></div>' +
            '</div>' +
            '<p class="help-block" style="display:block">Total should be 100%.</p>' +
            '</div>')

        workforceUpdateRevenueTotal()

        var selector = workforceGetRevenueSelector();
        $(selector).keyup(function() {
            workforceUpdateRevenueTotal()
        })
    }
}

function workforceUpdateRevenueTotal()
{
    var total = 0;

    var selector = workforceGetRevenueSelector();

    $(selector).each(function(i, e) {
        var val = parseFloat($(e).val())
        if (val) {
            total = total + val
        }
    });

    var color = 'red';
    if (total == 100) {
        color = 'green';
    }

    total = '<span style="color: ' + color + '">' + total + '%</span>'

    $('#revTotal').html(total)
}

function workforceGetRevenueSelector()
{
    var selectors = [];
    var fields = ['controls-revenue_federal', 'controls-revenue_state', 'controls-revenue_local', 'controls-revenue_grants', 'controls-revenue_earned_revenue'];
    for (var i in fields) {
        var field = fields[i];

        selectors.push('#' + field + ' input');
    }

    return selectors.join(', ')
}

function workforceShowHideOtherSetup()
{

    var configs = getWorkForceOtherFields()

    for (i in configs) {
        var config = configs[i]

        // The id for the whole control group
        var specifyId = config.specifyId;

        // Id for the controls
        var other = config.other;

        // Is the field present?
        var otherField = $('#' + other + ' input');
        if (otherField.length) {
            var foundField = otherField;
            var foundSpecifyId = specifyId;

            //  Run it on ready
            workforceShowHideOther(foundField, specifyId);

            // And on keyup
            otherField.keyup(function() {
                workforceShowHideOther(foundField, foundSpecifyId)
            })
        }

    }
}

function getWorkForceOtherFields()
{
    return [
        {
            specifyId: 'control-group-revenue_other_specify',
            other: 'controls-revenue_other'
        },
        {
            specifyId: 'control-group-expenditures_other_specify',
            other: 'controls-expenditures_other'
        },
        {
            specifyId: 'control-group-net_revenue_other_specify',
            other: 'controls-net_revenue_other'
        }
    ]
}

function workforceShowHideOther(otherField, specifyId)
{
    var val = otherField.val()
    if (val > 0) {
        // Show it
        $('#' + specifyId).show()
    } else {
        // hide it
        $('#' + specifyId).hide()
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
            if (html.substr(-11) == '</span><br>') {
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
    var value = getDataDefinitionSetting()

    if (value == 'show') {
        $('.help-block').show()
    } else if (value == 'hide') {
        $('.help-block').hide()
    } else {
        // Hide, then activate focus
        $('.help-block').hide()

        $('input, select, textarea').focus(function() {
            // Add the if statement in case the setting changes
            var value = getDataDefinitionSetting()
            if (value == 'active') {
                // Hide any previous help text first
                $('.help-block').hide()
                $(this).parents('.control-group').find('.help-block').show()
            }
        })
    }
}

function getDataDefinitionSetting()
{
    var value = $('#dataDefinitions').val()

    if (!value) {
        value = 'active'
    }

    return value
}


function trackFormChanges()
{
    // Keep track of whether the form has changed
    $('form.form-horizontal :input').change(function() {
        $(this).closest('form').data('changed', true)
    })

    // When the form navigation is clicked, offer to save any changes
    $('.form-nav a').click(function() {
        var form = $('form.form-horizontal')
        if (form.data('changed')) {
            if (confirm('You have unsaved changes. Would you like to save them before proceeding to the selected form? Select "OK" to save or "Cancel" to discard your changes.')) {
                // Add the redirect
                var redirect = $(this).attr('href')
                $('<input>').attr({type: 'hidden', name: 'redirect', value: redirect}).appendTo(form)

                // Submit the form
                form.submit()

                return false
            }
        }
    })
}

function updateSalaryTotals()
{
    $('.column-total').each(function() {
        var totalTd = $(this);
        var id = totalTd.attr('id')

        // Convert the id into the class selector for finding the inputs to sum
        var idParts = id.split('_');
        var selector = '.' + idParts.join('.') + ' input';
        var sum = 0;
        totalTd.parents('table').find(selector).each(function(i, e) {
            if (totalTd.hasClass('apply-conversion')) {
                var converted = applyConversionFactor($(e));
            } else {
                var converted = $(e).val();
            }

            sum += Number(converted);
        });

        sum = Math.round(sum);

        $(this).html(sum);
    });

    // Now update the max fields
    updateMaxFields();
}


function applyConversionFactor(element)
{
    var newValue = element.val();

    var td = element.parents('td');

    // Check to see if the field should get converted
    var classesToConvert = getClassesToConvert();
    for (c in classesToConvert) {
        var theClass = classesToConvert[c];

        var classes = theClass.split(' ');
        var matchesAllClasses = true;

        for (d in classes) {
            var classToCheck = classes[d];

            if (!td.hasClass(classToCheck)) {
                matchesAllClasses = false;
                break;
            }
        }

        // If this is true, actually perform the conversion
        if (matchesAllClasses) {
            conversionFactor = getConversionFactor();
            newValue = newValue * conversionFactor;
        }

    }

    return newValue;
}

function getConversionFactor()
{
    if (typeof conversionFactor == 'undefined' || !conversionFactor) {
        conversionFactor = 1;
    }

    return Number(conversionFactor);
}

function getClassesToConvert()
{
    return [
        // Form 2
        'salaries month-12',
        // Form 3
        'exp 12-month retirement',
        'exp 12-month fica',
        'exp 12-month group-life',
        'exp 12-month workers-comp'
    ];
}

function updateMaxFields()
{
    $('.column-max').each(function() {
        var totalTd = $(this);
        var id = totalTd.attr('id');

        // Convert the id into the class selector for finding the inputs to sum
        var idParts = id.split('_');
        var selector = '.max-target.' + idParts.join('.');

        var max = 0;
        totalTd.parents('table').find(selector).each(function(i, e) {
            var maxTargetValue = Number($(this).html());

            if (maxTargetValue > max) {
                max = maxTargetValue;
            }
        })

        $(this).html(max);
    })


    $('.column-input-max').each(function() {
        var totalTd = $(this);
        var id = totalTd.attr('id');

        // Convert the id into the class selector for finding the inputs to sum
        var idParts = id.split('_');
        var selector = '.' + idParts.join('.') + ' input';

        var max = 0;
        totalTd.parents('table').find(selector).each(function(i, e) {
            var maxTargetValue = Number($(this).val());

            if (maxTargetValue > max) {
                max = maxTargetValue;
            }
        })

        $(this).html(max);
    })
}


// For NCCBP
function setupForm2Totals()
{
    // Are we on the correct page?
    if ($('#ft_f_yminus3_degr_cert').length == 0) {
        return false;
    }

    var configs = getForm2TotalConfig();
    var spanClass = 'data-entry-total-replace';

    // Clear any previous totals
    $('.' + spanClass).remove();

    for (var i in configs) {
        config = configs[i];

        var totalId = config['total'];
        var sources = config['sources'];

        // Calculate the total of two fields
        var total = 0;
        for (var si in sources) {
            var sourceId = sources[si];
            var sourceField = $('#' + sourceId);
            var val = sourceField.val();
            if (val == '') {
                val = 0;
            }

            total = total + parseInt(val);

            // Now bind to the change event for this field
            sourceField.change(function() {
                setupForm2Totals();
            })
        }

        // Replace the input with a span showing the total
        var input = $('#' + totalId);
        var span = $('<span>');
        span.addClass(spanClass)

        input.after(span);

        // Show the total in the span
        span.html(total.toString());

        // And populate the value to the hidden input
        input.val(total.toString());
        input.hide();
    }
}

function getForm2TotalConfig()
{
    var config = [
        {
            total: 'ft_f_yminus3_degr_cert',
            sources: ['ft_f_yminus3_degr_not_transf', 'ft_f_yminus3_degr_and_transf']
        },
        {
            total: 'ft_f_yminus4_degr_cert',
            sources: ['ft_f_yminus4_degr_not_transf', 'ft_f_yminus4_degr_and_transf']
        },
        {
            total: 'pt_f_yminus4_degr_cert',
            sources: ['pt_f_yminus4_degr_not_transf', 'pt_f_yminus4_degr_and_transf']
        },
        {
            total: 'ft_yminus7_degr',
            sources: ['ft_f_yminus7_degr_not_transf', 'ft_yminus7_degr_and_tranf']
        },
        {
            total: 'pt_yminus7_degr',
            sources: ['pt_f_yminus7_degr_not_transf', 'pt_yminus7_degr_and_tranf']
        }
    ];

    return config;
}
