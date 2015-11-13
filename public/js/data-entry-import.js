$(function() {
    setUpImportSpinners()
});

function setUpImportSpinners()
{
    $('form#import').submit(function() {
        $('#fieldset-submit input').hide()
        $('#fieldset-submit').html('<div class="loading"><img src="/img/loading.gif" /></div>')

        return true
    })

    $('#download a').click(function() {
        $('#download a').hide()
        $('#download').html('<div class="loading"><img src="/img/loading.gif" /></div>')
    })
}
