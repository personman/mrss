$(function() {
    // Click login button to show form
    setupLoginButton()
})

function setupLoginButton()
{
    $('#loginButton').click(function() {
        $('#headerLoginForm').show()
        $('#loginButton').hide()

        return false
    })
}
