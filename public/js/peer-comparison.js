$(function() {
    // Update the form on document ready
    // Comment these out so we can send the initial values with the form instead of
    // loading them async
    //updateColleges();
    //updateBenchmarks();

    // And again if the year changes
    $('#reportingPeriod').change(function() {
        updateColleges();
        updateBenchmarks();
    })

    $('select#benchmarks').change(function() {
        updateColleges();
    })

    //$('#benchmarks, #peers').chosen()
})

function updateColleges()
{
    // Cancel any previous ajax request
    if (typeof request != 'undefined') {
        request.abort()
    }

    // Hold on to any selected peers so we can restore them
    var selectedValues = $('#peers').val()
    if (!selectedValues) {
        selectedValues = []
    }

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
    var request = $.get(url, function(result) {
        var colleges = result.colleges
        if (typeof colleges == 'undefined') {
            return false;
        }

        // Find the college select box (multi)
        var select = $('#peers');

        var selectedValues2 = select.val()
        if (!selectedValues2) {
            selectedValues2 = []
        }

        // Empty the select
        select.empty();

        // Add the options
        $.each(colleges, function(key, value) {
            var option = $('<option></option>')
            option.attr('value', value.id).text(value.name);

            select.append(option)
        })

        // Restore selected values
        selectedValues = selectedValues.concat(selectedValues2)
        selectedValues = $.unique(selectedValues)
        select.val(selectedValues)

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
        var benchmarkGroups = result.benchmarkGroups
        if (typeof benchmarkGroups == 'undefined') {
            return false;
        }

        // Find the college select box (multi)
        var select = $('#benchmarks');

        // Empty the select
        select.empty();

        // Add the options
        $.each(benchmarkGroups, function(benchmarkGroup, benchmarks) {
            var optgroup = $('<optgroup></optgroup>')
            optgroup.attr('label', benchmarkGroup)

            $.each(benchmarks, function(key, value) {
                var option = $('<option></option>')
                option.attr('value', value.id).text(value.name);
                optgroup.append(option)
            })

            select.append(optgroup)
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
            $('#savedPeerGroups ul').append(
                '<li>' +
                    '<a href="#" class="choosePeerGroup" id="peerGroup-' + i + '">' + name + '</a> ' +
                    '<span class="deleteLink"><a href="#">[delete]</a></span>' +
                '</li>'
            )
        }

        // Bind clicks
        $('#savedPeerGroups ul li a.choosePeerGroup').click(function() {
            var key = $(this).attr('id').split('-').pop()
            var group = peerGroups[key]

            selectPeerGroup(group)

            return false
        })

        // Bind delete
        $('#savedPeerGroups .deleteLink a').click(function() {
            if (confirm('Are you sure you want to delete this peer group?')) {
                var key = $(this).parent().parent().find('a.choosePeerGroup')
                    .attr('id').split('-').pop()
                var group = peerGroups[key]
                var url = '/reports/delete-peer'
                var data = {peerGroup: group.id}
                $.post(url, data, function() {

                })
                $(this).parent().parent().remove()
            }

            return false
        })

    }
}

function selectPeerGroup(group)
{
    var peers = $.map(group.peers, Number)

    // Loop over the options, selecting any that belong to the group
    var selectBox = $('#peers')
    selectBox.val('')

    for (var i in selectBox[0].options) {
        // Make sure the index is an int
        if (Math.floor(i) == i) {
            var option = $(selectBox[0].options[i])

            var value = parseInt(option.attr('value'))
            if (value) {
                // Check to see if the option's value is in the peer group
                if ($.inArray(value, peers) > -1) {
                    //option.attr('selected', 'selected')
                    // This works in IE10 and 11. above doesn't
                    option[0].selected = 'selected'
                }
            }

        }
    }

    // Put the name in the name field
    $('#controls-name input').val(group.name)

    // Hide the peer selection element and peer group name element
    $('#control-group-peers, #control-group-name').hide()

    // Show a message about the selected peer group
    var newControls = '<div class="control-group" id="control-group-selected-name">' +
    '<label class="control-label">Selected Peer Group</label>' +
    '<div class="controls">' + group.name + ' <span class="deleteLink">' +
    '<a href="#">[change]</a></span></div>' +
    '</div></div>';
    $('#control-group-peers').before(newControls)

    // Handle the change button
    $('#control-group-selected-name a').click(function() {
        $('#control-group-selected-name').remove()
        $('#control-group-peers, #control-group-name').show()
        $('#controls-name input').val('')
    })
}

