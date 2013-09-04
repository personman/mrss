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

    // Percentage
    $('input.input-percent').wrap('<div class="input-append" />')
    $('input.input-percent').after('<span class="add-on">%</span>')


    // Wrap selected input in well to highlight it
    if (!$('.data-entry-grid').length) {
        $('.form-horizontal input, .form-horizontal select')
            .focus(function(){
                $(this).parents('.control-group').addClass('well');
            }).blur(
            function(){
                $(this).parents('.control-group').removeClass('well');
            });
    }


    // Show grid help text
    $('.data-entry-grid input')
        .focus(function() {
            $(this).parents('tr').next().show()
        }).blur(function() {
            $(this).parents('tr').next().hide()
        });


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
})

function updateGridTotals()
{
    var columns = ['full', 'part', 'other']
    for (i in columns) {
        // The inputs in the column:
        var inputs = $('.data-entry-grid td.' + columns[i] + '-value input')
        var total = 0
        inputs.each(function(i, e) {
            value = parseFloat($(e).val())
            if (!value) {
                value = 0
            }
            total = total + value
        })

        // Show the total
        var totalTd = $('#' + columns[i] + '-time-total')
        totalTd.html(total + '%')

        // Handle errors
        if (total > 100) {
            totalTd.addClass('error')
            totalTd.html(totalTd.html() + "<br>Total should be 100% or less.")
        } else {
            totalTd.removeClass('error')
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
