var urlStack = [];
var originalTotal = 0;
var progressBar;
var times = [];
var startTime;

$(function() {
    setUpOutlierCalculation();
    setUpCompute();
    setUpChangeCalculation();
    setUpChangePercentilesCalculation();
    setUpPercentiles();
    setUpSystems();
});


function setUpOutlierCalculation()
{
    var baseUrl = '/reports/calculate-outlier/';

    $('.calculate-outliers').click(function() {
        var button = $(this);
        var buttonId = button.attr('id');
        var year = buttonId.split('-').pop();

        progressBar = $('#outlier-progress-' + year + ' .progress-bar');

        // Get the benchmark Ids
        var benchmarkIds = benchmarks[year];

        originalTotal = benchmarkIds.length;

        // Build the url stack
        urlStack = [];
        for (var i in benchmarkIds) {
            var benchmarkId = benchmarkIds[i];

            var url = baseUrl + benchmarkId + '/' + year;

            if (i == 0) {
                url = url + '/last';
            } else if (i == benchmarkIds.length - 1) {
                url = url + '/first';
            }
            urlStack.push(url);
        }

        // Now the url stack is built. Kick it off.
        progressBar.parent().show();
        getProgressLabel().html('Starting...');
        processUrlStack();

        return false;
    })
}

// Computed benchmarks
function setUpCompute()
{
    var baseUrl = '/reports/compute-one/';

    $('.calculate-compute').click(function() {
        var button = $(this);
        var buttonId = button.attr('id');
        var year = buttonId.split('-').pop();

        progressBar = $('#compute-progress-' + year + ' .progress-bar');

        // Get the observation Ids
        var observationsYear = observations[year];

        originalTotal = observationsYear.length;

        // Build the url stack
        urlStack = [];
        for (var i in observationsYear) {
            var observation = observationsYear[i];

            var url = baseUrl + observation;

            if (i == 0) {
                url = url + '/last';
            } else if (i == observationsYear.length - 1) {
                url = url + '/first';
            }

            urlStack.push(url);
        }

        // Now the url stack is built. Kick it off.
        progressBar.parent().show();
        getProgressLabel().html('Starting...');
        processUrlStack();

        return false;
    })
}

// National report percentiles
function setUpPercentiles()
{
    var baseUrl = '/reports/calculate-one/';

    $('.calculate-percentile').click(function() {
        var button = $(this);
        var buttonId = button.attr('id');
        var year = buttonId.split('-').pop();

        progressBar = $('#percentile-progress-' + year + ' .progress-bar');

        // Get the benchmark Ids
        var benchmarkIds = benchmarks[year];

        originalTotal = benchmarkIds.length;

        // Build the url stack
        urlStack = [];
        for (var i in benchmarkIds) {
            var benchmarkId = benchmarkIds[i];

            var url = baseUrl + benchmarkId + '/' + year;

            if (i == 0) {
                url = url + '/last';
            } else if (i == benchmarkIds.length - 1) {
                url = url + '/first';
            }
            urlStack.push(url);
        }

        // Now the url stack is built. Kick it off.
        progressBar.parent().show();
        getProgressLabel().html('Starting...');
        processUrlStack();

        return false;
    })
}

function setUpChangeCalculation()
{
    var changeBaseUrl = '/reports/calculate-changes/';

    $('.calculate-changes').click(function() {
        var button = $(this);
        var buttonId = button.attr('id');
        var year = buttonId.split('-').pop();

        progressBar = $('#calculate-changes-' + year + ' .progress-bar');

        // Get the observation Ids
        var observationsYear = observations[year];

        originalTotal = observationsYear.length;

        // Build the url stack
        urlStack = [];
        for (var i in observationsYear) {
            var observation = observationsYear[i];

            var url = changeBaseUrl + observation + '/' + year;

            if (i == 0) {
                url = url + '/last';
            } else if (i == observationsYear.length - 1) {
                url = url + '/first';
            }

            urlStack.push(url);
        }

        // Now the url stack is built. Kick it off.
        progressBar.parent().show();
        getProgressLabel().html('Starting...');
        processUrlStack();

        return false;
    })
}

function setUpChangePercentilesCalculation()
{
    var baseUrl = '/reports/calculate-one-percent-change/';

    $('.calculate-changes-percentiles').click(function() {
        var button = $(this);
        var buttonId = button.attr('id');
        var year = buttonId.split('-').pop();

        progressBar = $('#calculate-changes-percentile-progress-' + year + ' .progress-bar');

        // Get the benchmark Ids
        var benchmarkIds = benchmarks[year];

        originalTotal = benchmarkIds.length;

        // Build the url stack
        urlStack = [];
        for (var i in benchmarkIds) {
            var benchmarkId = benchmarkIds[i];

            var url = baseUrl + benchmarkId + '/' + year;

            if (i == 0) {
                url = url + '/last';
            } else if (i == benchmarkIds.length - 1) {
                url = url + '/first';
            }
            urlStack.push(url);
        }

        // Now the url stack is built. Kick it off.
        progressBar.parent().show();
        getProgressLabel().html('Starting...');
        processUrlStack();

        return false;
    })
}





function setUpSystems()
{
    var baseUrl = '/reports/calculate-one-system/';

    $('.calculate-systems').click(function() {
        var button = $(this);
        var buttonId = button.attr('id');
        var year = buttonId.split('-').pop();
        var systemIdsForYear = systemIds[year];
        urlStack = [];

        // Get the benchmark Ids
        var benchmarkIds = benchmarks[year];

        var lastSystemId = systemIdsForYear.length - 1;
        var lastBenchmarkId = benchmarkIds.length - 1;

        for (var si in systemIdsForYear) {
            var systemId = systemIdsForYear[si];

            for (var bi in benchmarkIds) {
                var benchmarkId = benchmarkIds[bi];

                var url = baseUrl + systemId + '/' + benchmarkId + '/' + year;

                // First and last
                if (si == 0 && bi == 0) {
                    url = url + '/last';
                } else if ((si == lastSystemId) && (bi == lastBenchmarkId)) {
                    url = url + '/first';
                }



                urlStack.push(url);
            }
        }

        progressBar = $('#system-progress-' + year + ' .progress-bar');

        progressBar.parent().show();
        getProgressLabel().html('Starting...');
        processUrlStack();

        return false;
    })
}

// Take a url off the top of the stack, run the ajax, update the progress bar, call the function again
function processUrlStack()
{
    var url;

    if (originalTotal == 0) {
        originalTotal = urlStack.length;
    }

    if (url = urlStack.pop()) {
        // Send the benchmark id and year to the server
        startTimer();
        //console.log(url);

        if (window.console) {
            console.log("URL: " + url)
        }

        $.get(url, function(data) {
            // Update the progress bar
            var remaining = urlStack.length;
            var completed = originalTotal - remaining;
            var completion = completed / originalTotal * 100;

            /*debugger;*/

            if (window.console) {

                console.log("Original total: " + originalTotal);
                console.log("Remaining: " + remaining);
                console.log("Completed: " + completed);
                console.log("Completion: " + completion);
            }

            progressBar.css('width', completion + '%').attr('aria-valuenow', completion);
            getProgressLabel()
                .html(Math.round(completion) + '%');

            endTimer();

            // On to the next one...
            processUrlStack();
        });
    } else {
        getProgressLabel().html('Complete.')
    }
}

function getNow()
{
    return new Date().getTime();
}

function startTimer()
{
    startTime = getNow();
}

function endTimer()
{
    var elapsed = getNow() - startTime;

    //console.log(Math.round(elapsed / 1000) + ' seconds');
    times.push(elapsed);

    updateTimerDisplay();
}

function updateTimerDisplay()
{
    var html = getProgressLabel().html();

    //html = html + '<br>Average time: ' + Math.round(getAverageTime()) + ' seconds';
    html = html + '<br>' + Math.round(getTimeRemaining() / 60) + ' minutes remaining';

    getProgressLabel().html(html);
}

function getAverageTime()
{
    var sum = times.reduce(function(a, b) { return a + b});
    var average = sum / times.length;

    // Convert to seconds
    return average / 1000;
}

function getTimeRemaining()
{
    return getAverageTime() * urlStack.length;
}

function getProgressLabel()
{
    return progressBar.parent().parent().parent().find('.progress-label');
}
