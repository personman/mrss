$(function() {
    // Click login button to show form
    setupLoginButton()

    // Fancybox image hovers
    setupFancyboxHover()
})

function setupLoginButton()
{
    $('#loginButton').click(function() {
        $('#headerButtons').hide('drop', {direction: 'up'})
        $('#headerLoginForm').show('drop', {direction: 'down'})

        return false
    })
}

function setupFancyboxHover() {
    $('.fancybox').each(function() {
        var image = $(this).find('img')

        // Add the overlay
        var markup = '<span class="rollover"></span>'
        $(this).prepend(markup)
        var overlay = $(this).find(".rollover")


        // OPACITY OF BUTTON SET TO 0% and width and height set
        overlay
            .css("opacity", "0")
            .css('height', image.height())
            .css('width', image.width())


        // ON MOUSE OVER
        overlay.hover(
            function () {
                // SET OPACITY TO 70%
                $(this).stop().animate({
                    opacity: .5
                }, "fast");
            },

            // ON MOUSE OUT
            function () {
                // SET OPACITY BACK TO 0%
                $(this).stop().animate({
                    opacity: 0
                }, "fast");
            }
        );
    })

}
