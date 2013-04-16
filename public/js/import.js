
var importStack
var importingAll = false

$(function() {
    $('button#importAll').click(function() {
        importAll()
    })

    // Import colleges
    $('button#import-colleges').click(function() {
        importingAll = false
        triggerImport('colleges')
    })

    // Import benchmarks
    $('button#import-benchmarks').click(function() {
        importingAll = false
        triggerImport('benchmarks')
    })

    // Import benchmark groups
    $('button#import-benchmarkGroups').click(function() {
        importingAll = false
        triggerImport('benchmarkGroups')
    })

    // Import observations
    $('button#import-observations').click(function() {
        importingAll = false
        triggerImport('observations')
    })

})

function importAll()
{
    importStack = ['colleges', 'benchmarkGroups', 'benchmarks', 'observations']
    importingAll = true

    nextImport()
}

function nextImport()
{
    if (importStack.length > 0) {
        type = importStack.shift()
        triggerImport(type)
    }
}

/**
 * Trigger an import and start polling for progress updates
 *
 * @param type
 */
function triggerImport(type)
{
    var intervalId

    resetProgressBar(type)

    url = '/import/trigger?type=' + type

    $.get(url, {}, function(data) {
        clearInterval(intervalId)
        completeProgressBar(type)
    })

    // Start polling for status
    intervalId = setInterval(function() {
        pollProgress(type)
    }, 500)
}

function pollProgress(type)
{
    pollProgressUrl = '/import/progress'
    $.get(pollProgressUrl, {}, function(data) {
        total = data.total
        processed = data.processed

        if (typeof total == 'undefined' || typeof total == 'null') {
            return false
        }

        // Update progress bar
        updateProgressBar(type, data.percentage)

        // info text
        infoText = processed + ' / ' + total

        // Multi-table imports should show what table we're currently on
        if (typeof data.currentTable != 'undefined') {
            currentTableCount = data.tableProcessed + 1
            tableCounts = " (" + currentTableCount + " / " + data.tableTotal + ")"
            infoText = "Table: " + data.currentTable + tableCounts + "<br>" + infoText
        }

        // Display the info
        info = $('#row-' + type + ' .progressInfo')
        info.html(infoText)
    })
}

function getProgressBar(type)
{
    return $('#row-' + type + ' .progress .bar')
}

function updateProgressBar(type, percentage)
{
    progressBar = getProgressBar(type)
    progressBar.css('width', percentage + '%')
}

function resetProgressBar(type)
{
    progressBar = getProgressBar(type)
    progressBar.css('width', '0%')

    info = $('#row-' + type + ' .progressInfo')
    info.html('')

    // Clear any complete icon
    completeDiv = $('#row-' + type + ' .progressComplete')
    completeDiv.html('')
}

function completeProgressBar(type)
{
    // Add a slight delay so the checkmark doesn't appear before the bar transitions
    // to complete
    setInterval(function() {
        updateProgressBar(type, 100)

        completeDiv = $('#row-' + type + ' .progressComplete')
        img = "<img src='/images/check-mark.png' alt='Complete' />"

        completeDiv.html(img)

        // Trigger another import?
        if (importingAll) {
            nextImport()
        }
    }, 1000)
}
