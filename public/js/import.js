
var importStack
var importingAll = false
var pollingDelay = 1200 // milliseconds between progress polls
var currentType

$(function() {
    $('button.import-button').each(function() {
        $(this).click(function() {
            importinAll = false
            type = $(this).attr('id').substr(7)

            triggerImport(type)
        })
    })

    $('button#importAll').click(function() {
        $(this)
            .attr('disabled', 'disabled')
            .addClass('disabled')
            .html('Loading...')

        importAll()
    })
})

function importAll()
{
    importingAll = true

    types = []
    $('button.import-button').each(function() {
        type = $(this).attr('id').substr(7)
        types.push(type);
    })

    importStack = types
    //triggerImport(types[0])
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
    currentType = type
    var year = $('#year').val()

    resetProgressBar(type)

    url = '/import/trigger'

    $.get(url, {type: type, year: year}, function(data) {
        // Start polling for status
        intervalId = setInterval(function() {
            pollProgress(type)
        }, pollingDelay)

        $('body').data('intervalId-' + type, intervalId)
    })
}

function pollProgress(type)
{
    pollProgressUrl = '/import/progress'
    $.get(pollProgressUrl, {type: type}, function(data) {
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

        // Is the import complete
        if (data.percentage == 100) {
            completeProgressBar(type)
        }
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
    // Stop polling
    intervalId = $('body').data('intervalId-' + type)
    if (typeof intervalId != 'undefined') {
        clearInterval(intervalId)
    } else {
        console.log('Unable to find intervalId for stopping polling.')
    }

    // Add a slight delay so the checkmark doesn't appear before the bar transitions
    // to complete
    setTimeout(function() {
        updateProgressBar(type, 100)

        completeDiv = $('#row-' + type + ' .progressComplete')
        img = "<img src='/images/check-mark.png' alt='Complete' />"

        completeDiv.html(img)

        // Trigger another import?
        if (importingAll) {
            setTimeout(function() {
                nextImport()
            }, 400)
        }
    }, 1000)
}
