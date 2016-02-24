var urlStack = [];
var originalTotal = 0;
var progressBar;

$(function() {
    setUpCalculation();
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
        console.log(originalTotal);


        // Build the url stack
        urlStack = [];
        for (var i in benchmarkIds) {
            var benchmarkId = benchmarkIds[i];

            urlStack.push(baseUrl + benchmarkId + '/' + year);
        }

        // Now the url stack is built. Kick it off.
        progressBar.parent().show();
        processUrlStack()
    })
}

// Take a url off the top of the stack, run the ajax, update the progress bar, call the function again
function processUrlStack()
{
    var url;

    if (url = urlStack.pop()) {
        // Send the benchmark id and year to the server
        $.get(url, function(data) {

            // Update the progress bar
            var remaining = urlStack.length;
            var completed = originalTotal - remaining;
            var completion = completed / originalTotal * 100;
            progressBar.css('width', completion + '%').attr('aria-valuenow', completion);
            progressBar.parent().parent().parent().parent().find('.progress-label')
                .html(Math.round(completion) + '%');

            // On to the next one...
            processUrlStack();
            //setTimeout(processUrlStack, 1000);
        });
    }
}
