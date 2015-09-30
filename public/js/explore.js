var savedCharts = {}
var editor

$(function() {

    setUpSelects();
    setUpTextarea();

    // Chart type
    updateFormForChartType()
    $('#inputType').change(function() {
        updateFormForChartType()
    })

    $('#explore').submit(function() {
        return exploreFormSubmit()
    })
})

function setUpSelects()
{
    $('#benchmark1, #benchmark2, #benchmark3').chosen({search_contains: true})
}

function setUpTextarea()
{
    editor = CKEDITOR.replace(
        'report-item-text-editor',
        {
            allowedContent: true,
            contentCss: '/css/index.css',
            // Simplify:
            removeButtons: 'Anchor,Subscript,Superscript,Strike,Table,Maximize,HorizontalRule,Styles'
        }
    )
}

function updateFormForChartType()
{
    var chartType = $('#inputType').val()
    var title = $('#control-group-title')
    var subtitle = $('#control-group-subtitle')
    var sizeField = $('#control-group-benchmark3')
    var benchmark1 = $('#control-group-benchmark1')
    var benchmark2 = $('#control-group-benchmark2')
    var yearField = $('#control-group-years')
    var peerGroup = $('#control-group-peerGroup')
    var textEditor = $('#text-editor')
    var chart = $('#chart')
    var footnotes = $('.custom-report-footnotes')
    var percentiles = $('#control-group-percentiles')
    var hideMine = $('#control-group-hideMine')
    var hideNational = $('#control-group-hideNational')
    var previewButton = $('#previewButton')

    // Hide all by default
    $('#explore .control-group').hide()
    $('#control-group-inputType, #control-group-submitButton, #control-group-previewButton, #control-group-cancelButton').show()

    // Text
    if (chartType == 'text') {
        textEditor.slideDown()
        chart.slideUp()
        footnotes.slideUp()
        //title.show()
        previewButton.hide()
    } else {
        textEditor.slideUp()
        chart.slideDown()
        footnotes.slideDown()
        previewButton.show()
    }

    // Buble
    if (chartType == 'bubble') {
        title.show()
        subtitle.show()
        benchmark1.show()
        benchmark2.show()
        sizeField.show()
        yearField.show()
        peerGroup.show()
        hideMine.show()
        hideNational.show()
    }

    // Scatter
    if (chartType == 'scatter') {
        title.show()
        subtitle.show()
        benchmark1.show()
        benchmark2.show()
        yearField.show()
        peerGroup.show()
        hideMine.show()
        hideNational.show()
    }

    // Line
    if (chartType == 'line') {
        title.show()
        subtitle.show()
        //yearField.show()
        benchmark2.show()
        peerGroup.show()
        hideMine.show()
        hideNational.show()
        percentiles.show()
        populateDefaultBreakpoints([50])
    }

    // Percentile bar
    if (chartType == 'bar') {
        title.show()
        subtitle.show()
        benchmark1.show()
        yearField.show()
        percentiles.show()
        populateDefaultBreakpoints()
    }
}

function exploreFormSubmit()
{
    // If type is text, copy text from ckeditor to main form
    if ($('#inputType').val() == 'text') {
        var text = editor.getData()

        $('#text-content').html(text)
    }

    return true
}

function postChart(id)
{
    if (savedCharts[id]) {
        post_to_url('/reports/explore', {'id': id})
    }

    return false
}

function post(path, params, method) {
    method = method || "post"; // Set method to post by default if not specified.

    // The rest of this code assumes you are not using a library.
    // It can be made less wordy if you use one.
    var form = document.createElement("form");
    form.setAttribute("method", method);
    form.setAttribute("action", path);

    for(var key in params) {
        if(params.hasOwnProperty(key)) {
            var hiddenField = document.createElement("input");
            hiddenField.setAttribute("type", "hidden");
            hiddenField.setAttribute("name", key);
            hiddenField.setAttribute("value", params[key]);

            form.appendChild(hiddenField);
        }
    }

    document.body.appendChild(form);
    form.submit();
}

function post_to_url(path, params, method) {
    method = method || "post"; // Set method to post by default, if not specified.

    // The rest of this code assumes you are not using a library.
    // It can be made less wordy if you use one.
    var form = document.createElement("form");
    form.setAttribute("method", method);
    form.setAttribute("action", path);

    var addField = function( key, value ){
        var hiddenField = document.createElement("input");
        hiddenField.setAttribute("type", "hidden");
        hiddenField.setAttribute("name", key);
        hiddenField.setAttribute("value", value );

        form.appendChild(hiddenField);
    };

    for(var key in params) {
        if(params.hasOwnProperty(key)) {
            if( params[key] instanceof Array ){
                for(var i = 0; i < params[key].length; i++){
                    addField( key, params[key][i] )
                }
            }
            else{
                addField( key, params[key] );
            }
        }
    }

    document.body.appendChild(form);
    form.submit();
}


function populateDefaultBreakpoints(breakPoints)
{
    var percentileInputs = $('#control-group-percentiles input')

    if (!breakPoints) {
        breakPoints = getDefaultBreakpoints()
    }

    if (typeof breakPoints != 'undefined') {
        // First, check to see if the checkboxes are blank
        var allBlank = true
        percentileInputs.each(function() {
            if (this.checked) {
                allBlank = false
            }
        })

        if (allBlank) {
            percentileInputs.each(function() {
                var input = $(this)

                if ($.inArray(parseInt(input.val()), breakPoints) > -1) {
                    input[0].checked = true
                } else {
                    input[0].checked = false
                }
            })
        }
    }
}

function getDefaultBreakpoints()
{
    // Convert to integers
    converted = []
    if (typeof defaultBreakpoints != 'undefined') {
        for (var i in defaultBreakpoints) {
            converted.push(parseInt(defaultBreakpoints[i]))
        }
    }

    return converted
}
