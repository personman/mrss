var savedCharts = {}
var editor;
var secondBenchmarkControls;

$(function() {
    setUpSelects();
    setUpTextarea();

    // Chart type
    updateFormForChartType()
    $('#inputType').change(function() {
        updateFormForChartType()
    });

    $('#explore').submit(function() {
        return exploreFormSubmit()
    })

    benchmarkChanged()
    $('#benchmark2').change(function() {
        benchmarkChanged()
    })
});

function setUpSelects()
{
    /*$('#benchmark1, #benchmark2, #benchmark3').each(function(i, e) {
        $(e).find('option').each(function(i2, e2) {
            if (!e2.disabled) {
                console.log($(e2).text());
                //$(e2).text("  " + $(e2).text());
                $(e2).html($.parseHTML($(e2).text()));
                console.log($(e2).text());
                console.log($(e2).html());
            }
        })
    })*/

    cloneBenchmark2();
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
    var system = $('#control-group-system')
    var peerGroup = $('#control-group-peerGroup')
    var makePeerCohort = $('#control-group-makePeerCohort')
    var textEditor = $('#text-editor')
    var chart = $('#chart')
    var footnotes = $('.custom-report-footnotes')
    var percentiles = $('#control-group-percentiles')
    var hideMine = $('#control-group-hideMine')
    var hideNational = $('#control-group-hideNational')
    var previewButton = $('#previewButton')
    var regression = $('#control-group-regression')
    var percentScaleZoom = $('#control-group-percentScaleZoom')

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

    // Bubble
    if (chartType == 'bubble') {
        title.show()
        subtitle.show()
        benchmark1.show()
        benchmark2.show()
        sizeField.show()
        system.show()
        yearField.show()
        peerGroup.show()
        hideMine.show()
        hideNational.show()
        regression.show()
    }

    // Scatter
    if (chartType == 'scatter') {
        title.show()
        subtitle.show()
        benchmark1.show()
        benchmark2.show()
        system.show()
        yearField.show()
        peerGroup.show()
        hideMine.show()
        hideNational.show()
        regression.show()
    }

    // Line
    if (chartType == 'line') {
        title.show()
        subtitle.show()
        //yearField.show()
        benchmark2.find('label').text(benchmarkLabel)
        benchmark2.show()
        system.show()
        peerGroup.show()
        hideMine.show()
        hideNational.show()
        percentiles.show()
        makePeerCohort.show()

        if (getMultiTrendHiddenValue()) {
            addSecondBenchmarkButtonClicked(benchmark2);
        } else {
            placeAddSecondBenchmarkButton(benchmark2);
        }

        populateDefaultBreakpoints([50])
    } else {
        removeAddSecondBenchmarkButton();
        benchmark2.find('label').text('Y Axis')
    }

    // Percentile bar
    if (chartType == 'bar') {
        title.show()
        subtitle.show()
        benchmark1.show()
        system.show()
        yearField.show()
        percentiles.show()
        populateDefaultBreakpoints()
    }

    // Peer comparison
    if (chartType == 'peer') {
        title.show();
        subtitle.show()
        benchmark1.show()
        system.show()
        yearField.show()
        peerGroup.show()
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
    var converted = [];
    if (typeof defaultBreakpoints != 'undefined') {
        for (var i in defaultBreakpoints) {
            converted.push(parseInt(defaultBreakpoints[i]))
        }
    }

    return converted
}

function placeAddSecondBenchmarkButton(benchmark)
{
    var id = getSecondBenchmarkButtonId();
    var button = $('<a>', {class: 'btn btn-default btn-xs', id: id, href: '#', style: 'margin-left: 16px'});
    var buttonLabel = 'Add a Second Benchmark'
    if (benchmarkLabel) {
        buttonLabel = buttonLabel.replace('Benchmark', ucwords(benchmarkLabel));
    }
    button.text(buttonLabel);

    button.click(function() {
        addSecondBenchmarkButtonClicked(benchmark);
        return false;
    });

    benchmark.after(button);
}

function addSecondBenchmarkButtonClicked(benchmark)
{
    // Remove any existing UI for this
    $('#secondBenchmarkButtonRemove, #control-group-benchmark2a').remove()

    displayFilteredSecondBenchmarkSelect(benchmark);
    removeAddSecondBenchmarkButton();
    placeRemoveSecondBenchmarkButton(benchmark);
    setMultiTrendHiddenValue(true)
}

function setMultiTrendHiddenValue(value)
{
    $('#multiTrend').val(value);
}


function placeRemoveSecondBenchmarkButton(benchmark)
{
    var id = getSecondBenchmarkButtonId() + 'Remove';

    var button = $('<a>', {class: 'btn btn-default btn-xs', id: id, href: '#', style: 'margin-left: 16px'});
    button.text('Remove Second Benchmark');

    button.click(function() {
        removeSecondBenchmarkSelect();
        placeAddSecondBenchmarkButton(benchmark);
        removeRemoveSecondBenchmarkButton();
        $('#multiTrend').val(false);
        return false;
    });

    benchmark.next().after(button);

}

function removeSecondBenchmarkSelect()
{
    $('#control-group-benchmark2a').remove();
}

function removeRemoveSecondBenchmarkButton()
{
    var id = getSecondBenchmarkButtonId() + 'Remove';
    $('#' + id).remove();
}

function getSecondBenchmarkButtonId()
{
    return 'secondBenchmarkButton';
}

function displayFilteredSecondBenchmarkSelect(benchmarkSelect)
{
    var benchmarkOneContainer = benchmarkSelect.closest('.control-group');
    var benchmarkTwoContainer = secondBenchmarkControls.clone();
    var inputType = getCurrentInputType()

    benchmarkTwoContainer = filterSecondBenchmarkSelect(benchmarkTwoContainer, inputType);

    // Change the label and select name
    benchmarkTwoContainer.attr('id', 'control-group-benchmark2a');
    benchmarkTwoContainer.find('label').text('Second Benchmark');
    benchmarkTwoContainer.find('.controls').attr('id', 'controls-benchmark2a');
    benchmarkTwoContainer.find('.chosen-container').attr('id', 'benchmark2a_chosen');
    benchmarkTwoContainer.find('select').attr('name', 'benchmark2a').attr('id', 'benchmark2a');

    benchmarkTwoContainer.find('select').val($('#benchmark3').val());
    benchmarkTwoContainer.find('select').change(function()
    {
        var selected = $(this).val();
        // Copy to benchmark3
        $('#benchmark3').val(selected);
    });




    benchmarkOneContainer.after(benchmarkTwoContainer);

    benchmarkTwoContainer.find('select').chosen({search_contains: true});

}

function getCurrentInputType()
{
    var dbColumn = $('#benchmark2').val()
    var inputType = benchmarksByInputType[dbColumn]

    return inputType
}

function cloneBenchmark2()
{
    secondBenchmarkControls = $('#control-group-benchmark2').clone();
}

/**
 * Needs to know the inputTypes of all benchmarks
 * @param benchmarkTwoContainer
 */
function filterSecondBenchmarkSelect(benchmarkTwoContainer, inputType)
{

    var benchmarkOneInputType
    var options = benchmarkTwoContainer.find('select').find('option');
    //console.log(options.length)

    options.each(function(i, e) {
        //console.log($(e).val())
        if (benchmarksByInputType[$(e).val()] != inputType) {
            $(e).remove()
        }

    })

    return benchmarkTwoContainer;
}

function removeAddSecondBenchmarkButton()
{
    var id = getSecondBenchmarkButtonId();

    $('#' + id).remove();

    setMultiTrendHiddenValue(false)

    $('#secondBenchmarkButtonRemove').remove()
}

function getMultiTrendHiddenValue()
{
    return $('#multiTrend').val() == 'true';
}

function benchmarkChanged()
{
    // If we're in multitrend mode, reset the 2nd benchmark when the 1st changes
    if (getMultiTrendHiddenValue()) {
        addSecondBenchmarkButtonClicked($('#control-group-benchmark2'))
    }

    var percentScale = $('#control-group-percentScaleZoom')
    if (getCurrentInputType() == 'percent') {
        percentScale.show()
    } else {
        percentScale.hide()
    }
}
