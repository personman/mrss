/** JS for CMS pages */
$(function() {
    // Is this the member homepage?
    if ($('.member-home-primary-button').length) {
        // Is the renew button in the menu?
        if ($('.renew-nav').length) {
            // Ok, we need to replace the data entry button with a renew button
            $('.member-home-primary-button').html('Renew').attr('href', '/renew')
        }
    }
})
