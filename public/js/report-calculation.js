var urlStack = [];
var originalTotal = 0;
var progressBar;
var times = [];
var startTime;

$(function() {
    setUpCalculation();
    setUpCompute();
    setUpPercentiles();
});


function setUpCalculation()
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

            urlStack.push(url);
        }

        // Now the url stack is built. Kick it off.
        progressBar.parent().show();
        getProgressLabel().html('Starting...');
        processUrlStack();

        return false;
    })
}


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
// Take a url off the top of the stack, run the ajax, update the progress bar, call the function again
function processUrlStack()
{
    var url;

    if (url = urlStack.pop()) {
        // Send the benchmark id and year to the server
        startTimer();
        //console.log(url);

        $.get(url, function(data) {
            // Update the progress bar
            var remaining = urlStack.length;
            var completed = originalTotal - remaining;
            var completion = completed / originalTotal * 100;
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
