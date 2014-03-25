$(function() {
    // Update the form on document ready
    updateColleges();
    updateBenchmarks();

    // And again if the year changes
    $('#reportingPeriod').change(function() {
        updateColleges();
        updateBenchmarks();
    })
})

function updateColleges()
{
    var year = $('#reportingPeriod').val();

    if (!year) {
        return false;
    }

    // Show the loading message
    $('#peers').empty();
    var loadingOption = $('<option></option>');
    loadingOption.attr('value', '').text('Loading...');
    $('#peers').append(loadingOption);


    // Fetch the available peer colleges
    url = '/reports/peer-colleges/' + year;
    $.get(url, function(result) {
        var colleges = result.colleges
        if (typeof colleges == 'undefined') {
            return false;
        }

        //console.log(colleges);

        // Find the college select box (multi)
        var select = $('#peers');

        // Empty the select
        select.empty();

        // Add the options
        $.each(colleges, function(key, value) {
            var option = $('<option></option>')
            option.attr('value', value.id).text(value.name);

            select.append(option)
        })

    })
}

/**
 * @returns {boolean}
 */
function updateBenchmarks()
{
    var year = $('#reportingPeriod').val();

    if (!year) {
        return false;
    }

    // Show the loading message
    $('#benchmarks').empty();
    var loadingOption = $('<option></option>');
    loadingOption.attr('value', '').text('Loading...');
    $('#benchmarks').append(loadingOption);

    // Fetch the available benchmarks
    url = '/reports/peer-benchmarks/' + year;
    $.get(url, function(result) {
        var benchmarks = result.benchmarks
        if (typeof benchmarks == 'undefined') {
            return false;
        }

        //console.log(benchmarks);

        // Find the college select box (multi)
        var select = $('#benchmarks');

        // Empty the select
        select.empty();

        // Add the options
        $.each(benchmarks, function(key, value) {
            var option = $('<option></option>')
            option.attr('value', value.id).text(value.name);

            select.append(option)
        })

    })
}


