$(function() {
    // Update the form on document ready
    updateColleges();
    updateBenchmarks();

    // And again if the year changes
    $('#reportingPeriod').change(function() {
        updateColleges();
        updateBenchmarks();
    })

    $('select#benchmarks').change(function() {
        updateColleges();
    })
})

function updateColleges()
{
    $('#peers').empty();

    var year = $('#reportingPeriod').val();

    if (!year) {
        return false;
    }

    var benchmarkIds = $('select#benchmarks').val();
    if (!benchmarkIds) {
        return false;
    }

    benchmarkIds = benchmarkIds.join(',');

    // Show the loading message
    var loadingOption = $('<option></option>');
    loadingOption.attr('value', '').text('Loading...');
    $('#peers').append(loadingOption);


    // Fetch the available peer colleges
    url = '/reports/peer-colleges/' + year + '?benchmarks=' + benchmarkIds;
    $.get(url, function(result) {
        var colleges = result.colleges
        if (typeof colleges == 'undefined') {
            return false;
        }

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

        addSavedPeerGroups()
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

function addSavedPeerGroups()
{
    if (peerGroups.length && !$('#savedPeerGroups').length) {
        $('#controls-peers').prepend('<div id="savedPeerGroups"><strong>Saved Peer Groups</strong><ul></ul></div>')

        // Populate the ul
        for (var i in peerGroups) {
            var group = peerGroups[i]
            var name = group.name
            $('#savedPeerGroups ul').append('<li><a href="#" id="peerGroup-' + i + '">' + name + '</a></li>')
        }

        // Bind clicks
        $('#savedPeerGroups ul li a').click(function() {
            var key = $(this).attr('id').split('-').pop()
            var group = peerGroups[key]

            selectPeerGroup(group)

            return false
        })

    }
}

function selectPeerGroup(group)
{
    // Loop over the options, selecting any that belong to the group
    var selectBox = $('#peers')
    selectBox.val('')

    for (var i in selectBox[0].options) {
        // Make sure the index is an int
        if (Math.floor(i) == i) {
            var option = $(selectBox[0].options[i])

            var value = option.attr('value')
            if (value) {
                // Check to see if the option's value is in the peer group
                if ($.inArray(value, group.peers) > -1) {
                    option.attr('selected', 'selected')
                }
            }

        }
    }

    // Put the name in the name field
    $('#controls-name input').val(group.name)
}

