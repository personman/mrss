var savedCharts = {}
var editor;
var secondBenchmarkControls;
var currentLetter;

$(function() {
    cloneBenchmark2();
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

    peerGroupChanged();
    $('#peerGroup').change(function() {
        peerGroupChanged();
    })

    repopulateColleges();
});

function setUpSelects()
{
    // Clear extra benchmark selects. These will be added back by js as needed
    $('.extraBenchmark').parents('.control-group').remove()


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


    $('#benchmark1, #benchmark2, #benchmark3').chosen({search_contains: true})

    //var multiControls = getMultiControls()

    //peerGroup.before(multiControls)


    showExtraBechmarks();

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
    var width = $('#control-group-width')
    var peerGroup = $('#control-group-peerGroup')
    var makePeerCohort = $('#control-group-makePeerCohort')
    var colleges = $('#control-group-colleges')
    var textEditor = $('#text-editor')
    var chart = $('#chart')
    var footnotes = $('.custom-report-footnotes')
    var percentiles = $('#control-group-percentiles')
    var startYear = $('#control-group-startYear')
    var endYear = $('#control-group-endYear')
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
        width.show()
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
        width.show()
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
        width.show()
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
        startYear.show()
        endYear.show()
        hideMine.show()
        hideNational.show()
        percentiles.show()
        makePeerCohort.show()
        width.show()

        //etMultiTrendHiddenValue());

        if (getMultiTrendHiddenValue()) {
            addSecondBenchmarkButtonClicked(benchmark2);
        } else if (!$('.extraBenchmarks').length) {
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
        width.show()
        populateDefaultBreakpoints()
    }

    // Peer comparison
    if (chartType == 'peer') {
        title.show();
        subtitle.show()
        benchmark2.show()
        system.show()
        yearField.show()
        peerGroup.show()
        width.show()
        benchmark2.find('label').text(benchmarkLabel)
        //benchmark2.show()
        $('.extraBenchmark').show();

        if (getMultiTrendHiddenValue()) {
            //addSecondBenchmarkButtonClicked(benchmark2);
            placeAddSecondBenchmarkButton(benchmark2);
        } else {
            placeAddSecondBenchmarkButton(benchmark2);
        }

    }
}

function showExtraBechmarks()
{
    var benchmark2 = $('#control-group-benchmark2')

    while (dbColumn = getSelectedValue()) {
        displayFilteredSecondBenchmarkSelect(benchmark2, dbColumn)
    }
}

// @todo: handle edit (currently selects all peers even if you previously narrowed it
function peerGroupChanged()
{
    var colleges = $('#control-group-colleges')
    var peerGroupAverage = $('#control-group-peerGroupAverage')
    var peerSelect = $('#peerGroup')
    var selectedPeerGroup = peerSelect.val()
    var chartType = $('#inputType').val()

    if (selectedPeerGroup && colleges.find('input').length && chartType == 'line') {
        colleges.show()

        // Select peer members
        var members = peerMembers[selectedPeerGroup]
        colleges.find('input').each(function(i, e) {
            var college = $(e).parents('label')
            var collegeId = $(e).val().toString()
            var collegeInput = college.find('input')[0]
            if ($.inArray(collegeId, members) != -1) {
                if (!peerWasUnchecked(selectedPeerGroup, collegeId)) {
                    collegeInput.checked = true
                } else {
                    collegeInput.checked = false
                }

                college.show()
            } else {
                collegeInput.checked = false
                college.hide()
            }
        })
    } else {
        colleges.hide()
    }

    if (selectedPeerGroup && chartType == 'line') {
        peerGroupAverage.show()
    } else {
        peerGroupAverage.hide()
    }
}

function peerWasUnchecked(peerGroupId, collegeId)
{
    var wasUnchecked = false

    formData = getFormData();

    if (formData) {
        //console.log(formData)
        var originalPeerGroup = formData['peerGroup'].toString()
        var originalPeers = formData['colleges']

        if (originalPeerGroup == peerGroupId) {
            if ($.inArray(collegeId, originalPeers) == -1) {
                wasUnchecked = true
            }
        }
    }

    return wasUnchecked
}

function getFormData()
{
    var values = {};
    $.each($('#explore').serializeArray(), function(i, field) {
        values[field.name] = field.value;
    });

    return values;
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

        if (allBlank && !hasChartPreview()) {
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

function hasChartPreview()
{
    return $('#chart svg').length
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

function getMultiControls()
{
    var id = 'multiControls'
    var multiControls = $('#' + id)
    if (!multiControls.length) {
        multiControls = $('<div/>').attr('id', id)//.css('border', '2px solid red')

        // Place it
        var peerGroup = $('#control-group-peerGroup')
        peerGroup.before(multiControls)
    }

    return multiControls
}

function placeAddSecondBenchmarkButton(benchmark)
{
    var id = getSecondBenchmarkButtonId();
    var button = $('<a>', {class: 'btn btn-default btn-xs', id: id, href: '#', style: 'margin-left: 16px'});
    var buttonLabel = 'Add a Benchmark'
    if (benchmarkLabel) {
        buttonLabel = buttonLabel.replace('Benchmark', ucwords(benchmarkLabel));
    }
    button.text(buttonLabel);

    button.click(function() {
        addSecondBenchmarkButtonClicked(benchmark);
        return false;
    });

    var multiControls = getMultiControls()
    multiControls.append(button);
}

function addSecondBenchmarkButtonClicked(benchmark)
{
    // Remove any existing UI for this
    $('#secondBenchmarkButtonRemove').remove()


    //console.log(letter)
    var value = getSelectedValue();
    displayFilteredSecondBenchmarkSelect(benchmark, value);
    //removeAddSecondBenchmarkButton();
    //placeRemoveSecondBenchmarkButton(benchmark);

    setMultiTrendHiddenValue(true)

    // If we're on a line chart and they've added a benchmark, remove the button
    if ($('#inputType').val() == 'line') {
        $('#multiControls').hide()
    }
}

function setMultiTrendHiddenValue(value)
{
    $('#multiTrend').val(value);
}


function placeRemoveSecondBenchmarkButton(benchmark)
{
    var id = getSecondBenchmarkButtonId() + 'Remove';

    var button = $('<a>', {class: 'btn btn-danger btn-xs', id: id, href: '#', style: 'margin-left: 16px'});
    button.text('Remove a Benchmark');
    if (benchmarkLabel) {
        button.text(button.text().replace('Benchmark', ucwords(benchmarkLabel)));
    }

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

function displayFilteredSecondBenchmarkSelect(benchmarkSelect, value)
{

    //var letter = getMultiTrendHiddenValue();
    var letter = currentLetter;
    //console.log('letter from multitrend hidden:' + letter)
    if (!letter) {
        letter = 'a'
    } else {
        letter = String.fromCharCode(letter.charCodeAt() + 1)
    }

    currentLetter = letter


    //var benchmarkOneContainer = benchmarkSelect.closest('.control-group');
    var multiControls = getMultiControls();

    //var value = getSelectedValue();

    var newBenchmarkContainer = getNewBenchmarkContainer(letter, value);

    //console.log('placing remove button...')
    var removeButton = $('<a/>').addClass('btn btn-danger').css('margin-left', '5px').html('X').click(function() {
        $(this).parents('.control-group').remove()
        if ($('#inputType').val() == 'line') {
            $('#multiControls').show()
            //$('#benchmark3').val('')

            setMultiTrendHiddenValue(false)
        }
    })
    newBenchmarkContainer.find('.controls').append(removeButton)


    multiControls.before(newBenchmarkContainer);

    newBenchmarkContainer.find('select').chosen({search_contains: true});
}

function getSelectedValue()
{
    if (typeof selectedExtraBenchmarks == 'object') {
        selectedExtraBenchmarks = jQuery.makeArray(selectedExtraBenchmarks)
    }
    //console.log(selectedExtraBenchmarks)

    var value = null;
    if (selectedExtraBenchmarks.length) {
        value = selectedExtraBenchmarks.shift();
    }

    return value
}

function getNewBenchmarkContainer(letter, value)
{
    // Change the label and select name
    var inputType = getCurrentInputType()
    //var secondBenchmarkControls = $('#control-group-benchmark2').clone();
    var newBenchmarkContainer = secondBenchmarkControls.clone(true, true);

    var label = 'Benchmark' //+ letter
    if (benchmarkLabel) {
        label = label.replace('Benchmark', ucwords(benchmarkLabel));
    }

    //console.log(value)
    if (!value) {
        value = $('#benchmark3').val()
    }

    //console.log(value)

    newBenchmarkContainer = filterSecondBenchmarkSelect(newBenchmarkContainer, inputType);

    newBenchmarkContainer.addClass('extraBenchmark')

    newBenchmarkContainer.attr('id', 'control-group-benchmark2' + letter);
    newBenchmarkContainer.find('label').text(label);
    newBenchmarkContainer.find('.controls').attr('id', 'controls-benchmark2' + letter);
    newBenchmarkContainer.find('.chosen-container').attr('id', 'benchmark2' + letter + '_chosen');
    newBenchmarkContainer.find('select').attr('name', 'benchmark2' + letter).attr('id', 'benchmark2' + letter);

    newBenchmarkContainer.find('select').val(value);
    newBenchmarkContainer.find('select').change(function()
    {
        var selected = $(this).val();
        // Copy to benchmark3
        $('#benchmark3').val(selected);
    });

    return newBenchmarkContainer;
}

function getCurrentInputType()
{
    var dbColumn = $('#benchmark2').val()
    //console.log(dbColumn)
    var inputType = benchmarksByInputType[dbColumn]

    return inputType
}

function cloneBenchmark2()
{
    secondBenchmarkControls = $('#control-group-benchmark2').clone(true, true);
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

    //setMultiTrendHiddenValue(false)

    $('#secondBenchmarkButtonRemove').remove()
}

function getMultiTrendHiddenValue()
{
    var value = $('#multiTrend').val();
    if (value === 'false') {
        value = false
    }
    return value
}

function benchmarkChanged()
{
    // If we're in multitrend mode, reset the 2nd benchmark when the 1st changes
    if (getMultiTrendHiddenValue()) {
        //addSecondBenchmarkButtonClicked($('#control-group-benchmark2'))
    }

    var percentScale = $('#control-group-percentScaleZoom')
    if (getCurrentInputType() == 'percent') {
        percentScale.show()
    } else {
        percentScale.hide()
    }
}

function repopulateColleges()
{
    var hasData = (typeof originalFormData['colleges'] != 'undefined' && originalFormData['colleges'] != null)

    if (hasData && originalFormData['colleges'].length) {
        $("input:checkbox[name='colleges[]']").each(function(i, e) {
            if (originalFormData['colleges'].includes($(this).val())) {
                $(this)[0].checked = true;
            }
        })
    }
}
