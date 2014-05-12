$(function() {
    handleSameAsCheckbox()
    setUpAutocomplete()
})

// Add a checkbox to duplicate data from admin to data contacts
function handleSameAsCheckbox()
{
    var adminFieldset = $('#fieldset-adminContact')
    var dataFieldset = $('#fieldset-dataContact')
    var checkbox = $('#same-as-admin')
    var elementsToHide = dataFieldset.find('div.control-group')
        .not('#control-group-same-as-admin')

    // Run this once on ready and any time the checkbox changes
    hideElementsOnSameAs(checkbox, elementsToHide)
    checkbox.change(function() {
        hideElementsOnSameAs(checkbox, elementsToHide)
        copyValues(adminFieldset, dataFieldset)
    })

    // When the form is submitted and the checkbox is checked, copy values
    dataFieldset.parents('form').submit(function() {
        if (checkbox.is(':checked')) {
            copyValues(adminFieldset, dataFieldset)
        }

        return true
    })
}

function hideElementsOnSameAs(checkbox, elementsToHide)
{
    if (checkbox.is(':checked')) {
        elementsToHide.hide()
    } else {
        elementsToHide.show()
    }
}

function copyValues(from, to)
{
    // Find the inputs
    from.find('input, select').each(function(i, e) {
        fromId = $(this).attr('id')
        value = $(this).val()

        // Find the corresponding field
        toId = fromId.replace('admin', 'data')

        // Make the copy
        to.find('#' + toId).val(value)
    })
}

/**
 * As the user types the college name in the subscription form, offer autocomplete
 * options. When they select one, populate the ipeds, city and state.
 */
function setUpAutocomplete()
{
    $('#institution-name').autocomplete({
        source: '/ipeds-institutions/search',
        minLength: 3,
        select: function(event, ui) {
            ipeds = ui.item.ipeds
            address = ui.item.address
            city = ui.item.city
            state = ui.item.state
            zip = ui.item.zip

            $('#institution-ipdeds').val(ipeds)
            $('#institution-address').val(address)
            $('#institution-city').val(city)
            $('#institution-state').val(state)
            $('#institution-zip').val(zip)
        }
    })
}

/**
 * Fill out subscription form for testing
 */
function testSub()
{
    ipeds = Math.floor((Math.random() * 100000) + 900000)

    $('#institution-name').val('Test Community College');
    $('#institution-ipdeds').val(ipeds);
    $('#institution-address').val('123 Main');
    $('#institution-city').val('Adrian');
    $('#institution-state').val('MO');
    $('#institution-zip').val('64720')

    $('#adminContact-prefix').val('Mr.');
    $('#adminContact-firstName').val('Testy');
    $('#adminContact-lastName').val('Testerson');
    $('#adminContact-title').val('Chief Tester');
    $('#adminContact-phone').val('1231231234');
    $('#adminContact-email').val('personman2@gmail.com');
    $('#adminContact-emailConfirm').val('personman2@gmail.com');

    $('#same-as-admin').click();

    $('form#subscription').submit();
}
