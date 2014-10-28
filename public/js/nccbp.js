$(function() {
    // Click login button to show form
    setupLoginButton()
})

function setupLoginButton()
{
    $('#loginButton').click(function() {
        $('#headerButtons').hide('drop', {direction: 'up'})
        $('#headerLoginForm').show('drop', {direction: 'down'})

        return false
    })
}
